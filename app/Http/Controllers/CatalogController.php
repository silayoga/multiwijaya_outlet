<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Listing;

class CatalogController extends Controller
{
    public function index()
    {
        $withListings = function ($query) {
            $query->active()->with('defaultPlan')->latest();
        };

        $hardwareCategories = Category::hardware()->where('is_active', true)
            ->orderBy('sort_order')
            ->with(['listings' => $withListings])
            ->get();

        $softwareCategories = Category::softwareService()->where('is_active', true)
            ->orderBy('sort_order')
            ->with(['listings' => $withListings])
            ->get();

        // Eager load already fetched every active listing per category in one
        // query each; trim to a handful per category here (no extra queries).
        foreach ([$hardwareCategories, $softwareCategories] as $group) {
            $group->each(fn (Category $category) => $category->setRelation('listings', $category->listings->take(4)));
        }

        $featured = Listing::active()->featured()
            ->with(['category', 'defaultPlan'])
            ->latest()
            ->limit(6)
            ->get();

        return view('catalog.index', compact('hardwareCategories', 'softwareCategories', 'featured'));
    }

    public function show(Category $category)
    {
        $category->load(['listings' => function ($query) {
            $query->active()->with('defaultPlan');
        }]);

        return view('catalog.show', compact('category'));
    }

    /**
     * Dedicated landing page for all software/service categories — framed as
     * recurring subscriptions rather than the flat one-time-purchase hardware
     * grid, since the buying decision is genuinely different.
     */
    public function softwareServices()
    {
        $categories = Category::softwareService()->where('is_active', true)
            ->orderBy('sort_order')
            ->with(['listings' => function ($query) {
                $query->active()->with('defaultPlan')->latest();
            }])
            ->get();

        return view('catalog.software-services', compact('categories'));
    }
}
