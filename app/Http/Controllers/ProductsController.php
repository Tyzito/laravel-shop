<?php

namespace App\Http\Controllers;

use App\Exceptions\InvalidRequestException;
use App\Models\Category;
use App\Models\OrderItem;
use App\Models\Product;
use App\SearchBuilders\ProductSearchBuilder;
use App\Services\ProductService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class ProductsController extends Controller
{
    public function index(Request $request)
    {
        $page = $request->input('page', 1);
        $perPage = 16;

        // 新建查询构造器对象，设置只搜索上架商品，设置分页
        $builder = (new ProductSearchBuilder())->onSale()->paginate($perPage, $page);

        // 类目筛选
        if ($request->input('category_id') && $category = Category::find($request->input('category_id'))) {
            $builder->category($category);
        }

        // 关键字
        if($search = $request->input('search', '')){
            // 将搜索词拆分成数组，过滤掉空项
            $keywords = array_filter(explode(' ', $search));

            $builder->keywords($keywords);
        }

        // 只有当用户有搜索关键词或者使用了类目筛选的时候才会做聚合
        if($search || isset($category)){
            $builder->aggregateProperties();
        }

        // 从用户请求的参数获取filters
        $propertyFilters = [];
        if($filterString = $request->input('filters')){
            $filterArray = explode('|', $filterString);
            foreach($filterArray as $filter){
                list($name, $value) = explode(':', $filter);
                // 将用户筛选的属性添加的数组中
                $propertyFilters[$name] = $value;

                // 添加到filter类型中
                $builder->propertyFilter($name, $value);
            }
        }

        // 是否有order提交
        if($order = $request->input('order', '')){
            // 是否以 asc 或 desc 结尾
            if(preg_match('/^(.+)_(asc|desc)$/', $order, $m)){
                if(in_array($m[1], ['price', 'sold_count', 'rating'])){
                    $builder->orderBy($m[1], $m[2]);
                }
            }
        }

        $result = app('es')->search($builder->getParams());

        $properties = [];
        // 如果返回的结果里 properties ，说明做了分面搜索
        if(isset($result['aggregations'])){
            // 使用 collect 将返回值转为集合
            $properties = collect($result['aggregations']['properties']['properties']['buckets'])
                ->map(function($bucket){
                    // 通过map方法取出我们需要的字段
                    return [
                        'key' => $bucket['key'],
                        'values' => collect($bucket['value']['buckets'])->pluck('key')->all(),
                    ];
                })
                ->filter(function($property) use($propertyFilters){
                    // 过滤掉只剩下的一个值 或者 已经在筛选条件里的属性
                    return count($property['values']) > 1 && !isset($propertyFilters[$property['key']]);
                });
        }

        $productIds = collect($result['hits']['hits'])->pluck('_id')->all();

        $products = Product::query()->byIds($productIds)->get();

        $pager = new LengthAwarePaginator($products, $result['hits']['total']['value'], $perPage, $page, [
            'path' => route('products.index', false)
        ]);


        /*// 创建一个查询构造器
        $builder = Product::query()->where('on_sale',true);
        // 判断是否提交 search 参数，如果有就赋值给 $search 变量
        // search 参数用来模糊搜索商品
        if($search = $request->input('search','')){
            $like = '%'.$search.'%';
            // 模糊搜索商品标题、商品详情、SKU 标题、SKU 描述
            $builder->where(function ($query) use($like){
                $query->where('title','like',$like)
                    ->orWhere('description','like',$like)
                    ->orWhereHas('skus',function ($query) use($like){
                        $query->where('title','like',$like)
                            ->orWhere('description','like',$like);
                });
            });
        }

        // 如果传入 category_id 字段，并且在数据库中有对应的类目
        if($request->input('category_id') && $category = Category::find($request->input('category_id'))){
            // 如果这是一个父类目
            if($category->is_directory){
                // 则筛选出该父类目下所有子类目的商品
                $builder->whereHas('category', function ($query) use($category){
                   $query->where('path', 'like', $category->path.$category->id.'-%');
                });
            }else{
                // 如果不是一个父类目，则直接筛选出此类目下的商品
                $builder->where('category_id', $category->id);
            }
        }

        // 判断是否提交 order 参数，如果有就赋值给 $order 变量
        // order 参数用来控制商品的排序规则
        if($order = $request->input('order','')){
            // 是否以 _asc 或者 _desc 结尾
            if(preg_match('/^(.+)_(asc|desc)$/',$order,$m)){
                if(in_array($m[1],['price','sold_count','rating'])){
                    $builder->orderBy($m[1],$m[2]);
                }
            }

        }

        $products = $builder->paginate(16);*/

        return view('products.index',[
            'products' => $pager,
            'filters' => [
                'search' => $search,
                'order' => $order
            ],
            'category' => $category ?? null,
            'properties' => $properties,
            'propertyFilters' => $propertyFilters,
        ]);
    }

    public function show(Product $product, Request $request, ProductService $service)
    {
        // 判断商品是否已上架，如果没有上架抛出异常
        if(!$product->on_sale){
            throw new InvalidRequestException('商品未上架');
        }

        $favored = false;

        // 用户未登录返回的是 null,已登录返回的是对应的用户对象
        if($user = $request->user()){
            // 从当前用户已收藏的商品中搜索 id 为当前商品 id 的商品,boolval() 函数把值转为布尔值
            $favored = boolval($user->favoriteProducts()->find($product->id));
        }

        $reviews = OrderItem::query()
            ->with('order.user','productSku')
            ->where('product_id',$product->id)
            ->whereNotNull('reviewed_at')
            ->orderBy('reviewed_at','desc')
            ->limit(10)
            ->get();

        $similarProductIds = $service->getSimilarProductIds($product, 4);

        // 根据 Elasticsearch 搜索出来的商品 ID 从数据库中读取商品数据
        $similarProducts = Product::query()->byIds($similarProductIds)->get();

        return view('products.show',['product' => $product, 'favored' => $favored, 'reviews' => $reviews, 'similar' => $similarProducts]);
    }

    // 收藏列表
    public function favorites(Request $request)
    {
        $products = $request->user()->favoriteProducts()->paginate(16);

        return view('products.favorites', ['products' => $products]);
    }

    // 收藏
    public function favor(Product $product, Request $request)
    {
        $user = $request->user();
        // 判断用户是否收藏了此商品，如果已经收藏则不做任何操作直接返回，否则通过 attach() 方法将当前用户和商品关联起来，attach() 可以是模型 id,也可以是模型对象本身
        if($user->favoriteProducts()->find($product->id)){
            return [];
        }

        $user->favoriteProducts()->attach($product);

        return [];
    }

    // 取消收藏
    public function disfavor(Product $product, Request $request)
    {
        $user = $request->user();

        // detach() 取消多对多关联
        $user->favoriteProducts()->detach($product);

        return [];
    }
}
