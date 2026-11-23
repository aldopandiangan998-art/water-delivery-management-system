<?php

namespace App\Http\Controllers;

use App\Models\BusinessSetting;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Review;
use App\Models\ServiceArea;
use Illuminate\Http\Request;

class PublicController extends Controller
{
    public function index()
    {
        $categories = ProductCategory::where('is_active', true)->with(['products' => function ($q) {
            $q->where('is_active', true)->orderBy('price', 'asc');
        }])->get();

        $featuredProducts = Product::where('is_active', true)->orderBy('price', 'asc')->get();
        $serviceAreas = ServiceArea::where('is_active', true)->orderBy('delivery_fee', 'asc')->get();
        $reviews = Review::where('is_approved', true)->with('user', 'order')->latest()->take(6)->get();

        $settings = BusinessSetting::pluck('value', 'key')->toArray();

        return view('public.index', compact('categories', 'featuredProducts', 'serviceAreas', 'reviews', 'settings'));
    }

    public function product(Product $product)
    {
        if (! $product->is_active) {
            abort(404);
        }
        return view('public.product-detail', compact('product'));
    }

    public function serviceAreas()
    {
        $serviceAreas = ServiceArea::where('is_active', true)->get();
        return view('public.service-areas', compact('serviceAreas'));
    }
}
