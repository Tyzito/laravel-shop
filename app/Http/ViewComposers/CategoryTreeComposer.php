<?php
namespace App\Http\ViewComposers;

use App\Services\CategoryService;
use Illuminate\View\View;

class CategoryTreeComposer
{
    protected $categoryService;

    public function __construct(CategoryService $categoryService)
    {
        $this->categoryService = $categoryService;
    }

    // 当渲染指定模板时，laravel 会调用 composer 方法
    public function compose(View $view)
    {
        $view->with('categoryTree', $this->categoryService->getCategoryTree());
    }
}
