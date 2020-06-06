<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductsController extends Controller
{
    public function index(Request $request)
    {
        // 创建一个查询构造器
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

        $products = $builder->paginate(16);

        return view('products.index',[
            'products' => $products,
            'filters' => [
                'search' => $search,
                'order' => $order
            ]
        ]);
    }
}