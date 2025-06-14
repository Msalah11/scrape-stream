<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductFilterRequest;
use App\Http\Resources\ProductCollection;
use App\Models\Product;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class ProductController extends Controller
{
    use ApiResponse;

    /**
     * Get paginated and filtered products list
     *
     * @param ProductFilterRequest $request
     * @return JsonResponse
     */
    public function __invoke(ProductFilterRequest $request): JsonResponse
    {
        $products = $this->getFilteredProducts($request);
        
        return $this->successResponse(
            'Products retrieved successfully',
            new ProductCollection($products)
        );
    }
    
    /**
     * Apply filters and return paginated products
     *
     * @param ProductFilterRequest $request
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    private function getFilteredProducts(ProductFilterRequest $request)
    {
        $sort = $request->getSortParams();
        
        return Product::query()
            ->when(
                $request->filled('search'),
                fn($query) => $query->where('title', 'like', "%{$request->input('search')}%")
            )
            ->when(
                $request->filled('min_price'),
                fn($query) => $query->where('price', '>=', $request->input('min_price'))
            )
            ->when(
                $request->filled('max_price'),
                fn($query) => $query->where('price', '<=', $request->input('max_price'))
            )
            ->orderBy($sort['by'], $sort['dir'])
            ->paginate($request->getPerPage())
            ->appends($request->validated());
    }
}
