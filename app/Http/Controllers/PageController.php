<?php

namespace App\Http\Controllers;

use App\Models\Page;
use Illuminate\View\View;

class PageController extends Controller
{
    public function show(string $slug): View
    {
        $page = Page::query()
            ->published()
            ->where('slug', $slug)
            ->where(function ($query) {
                $query->where('locale', app()->getLocale())
                    ->orWhere('locale', 'en');
            })
            ->orderByRaw("locale = ? desc", [app()->getLocale()])
            ->firstOrFail();

        return view('pages.cms', compact('page'));
    }
}
