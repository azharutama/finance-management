<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\CategoryFilterRequest;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Models\Category;
use App\Services\CategoryService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function __construct(
        private readonly CategoryService $categoryService
    ) {}

    public function index(CategoryFilterRequest $request): JsonResponse
    {
        $result = $this->categoryService->list($request->validated());

        if ($result instanceof LengthAwarePaginator) {
            return ApiResponse::paginated(
                $result,
                'Categories retrieved successfully.'
            );
        }

        return ApiResponse::success(
            $result,
            'Categories retrieved successfully.'
        );
    }

    public function store(StoreCategoryRequest $request): JsonResponse
    {
        $category = $this->categoryService->create($request->validated());

        return ApiResponse::created(
            $category,
            'Category created successfully.'
        );
    }

    public function show(Category $category): JsonResponse
    {
        $category->loadCount('chartOfAccounts');

        return ApiResponse::success(
            $category,
            'Category retrieved successfully.'
        );
    }

    public function update(UpdateCategoryRequest $request, Category $category): JsonResponse
    {
        $updated = $this->categoryService->update($category, $request->validated());

        return ApiResponse::success(
            $updated,
            'Category updated successfully.'
        );
    }

    public function destroy(Category $category): JsonResponse
    {
        $this->categoryService->delete($category);

        return ApiResponse::success(null, 'Category deleted successfully.');
    }
}
