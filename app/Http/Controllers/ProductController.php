<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\SiteSetting;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Schema;

class ProductController extends Controller
{
    public function index(): View|Response
    {
        if (! Schema::hasTable('products')) {
            abort(404);
        }

        $products = Product::query()
            ->published()
            ->orderByDesc('updated_at')
            ->paginate(12);

        return response()->view('products.index', [
            'products' => $products,
            'ecommerceEnabled' => SiteSetting::ecommerceEnabled(),
        ]);
    }

    public function show(Product $product): View|Response
    {
        if ($product->status !== 'published') {
            abort(404);
        }

        $relatedProducts = Product::query()
            ->published()
            ->whereKeyNot($product->id)
            ->orderByDesc('updated_at')
            ->limit(4)
            ->get();

        return response()->view('products.show', [
            'product' => $product,
            'relatedProducts' => $relatedProducts,
            'ecommerceEnabled' => SiteSetting::ecommerceEnabled(),
        ]);
    }
}
