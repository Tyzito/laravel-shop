<?php
namespace App\Admin\Controllers;

use App\Jobs\SyncOneProductToES;
use App\Models\Category;
use App\Models\Product;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;

abstract class CommonProductsController extends AdminController
{
    // 定义一个抽象方法，返回当前管理的商品类型
    abstract public function getProductType();

    protected function grid()
    {
        $grid = new Grid(new Product());
        // 筛选出当前类型的商品，默认ID倒序排序
        $grid->model()->where('type', $this->getProductType())->orderBy('id', 'desc');
        // 调用自定义的方法
        $this->customGrid($grid);

        $grid->actions(function ($actions){
            $actions->disableView();
            $actions->disableDelete();
        });

        $grid->tools(function ($tools){
            $tools->batch(function ($batch){
                $batch->disableDelete();
            });
        });

        return $grid;
    }

    // 定义一个抽象方法，各个类型的控制器将实现本方法来定义列表应该展示哪些字段
    abstract protected function customGrid(Grid $grid);

    protected function form()
    {
        $form = new Form(new Product());

        $form->hidden('type')->value($this->getProductType());

        // 创建一个输入框
        $form->text('title','商品名称')->rules('required');
        $form->text('long_title','商品长标题')->rules('required');

        // 添加一个类目字段，与之前类目管理类似，使用 Ajax 的方式搜索添加
        $form->select('category_id', '类目')->options(function ($id){
            $category = Category::find($id);
            if($category){
                return [$category->id => $category->full_name];
            }
        })->ajax('/admin/api/categories?is_directory=0');

        // 创建一个选择图片的框
        $form->image('image','封面图片')->rules('required|image');
        // 创建一个富文本编辑器
        $form->quill('description','商品描述')->rules('required');
        // 创建一组单选框
        $form->radio('on_sale','上架')->options(['1' => '是','0' => '否'])->default('0');

        // 调用自定义方法
        $this->customForm($form);

        // 直接添加一组关联模型
        $form->hasMany('skus','商品 SKU',function (Form\NestedForm $form){
            $form->text('title','SKU 名称')->rules('required');
            $form->text('description','SKU 描述')->rules('required');
            $form->text('price','单价')->rules('required|numeric|min:0.01');
            $form->text('stock','剩余库存')->rules('required|integer|min:0');
        });

        $form->hasMany('properties', '商品属性', function (Form\NestedForm $form){
            $form->text('name', '属性名')->rules('required');
            $form->text('value', '属性值')->rules('required');
        });

        // 定义事件回调，当模型即将保存时会触发这个回调
        $form->saving(function (Form $form){
            $form->model()->price = collect($form->input('skus'))->where(Form::REMOVE_FLAG_NAME,0)->min('price') ?: 0;
        });

        $form->saved(function (Form $form){
            $product = $form->model();
            dispatch(new SyncOneProductToES($product));
        });

        return $form;
    }

    // 定义一个抽象方法，各个类型的控制器将实现本方法来定义表单应该有哪些额外的字段
   abstract protected function customForm(Form $form);
}
