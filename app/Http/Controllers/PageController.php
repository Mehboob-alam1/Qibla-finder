<?php

namespace App\Http\Controllers;

use App\Models\Page;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\View\View;

class PageController extends Controller
{
    public function show(string $slug): View
    {
        try {
            $page = Page::query()
                ->published()
                ->where('slug', $slug)
                ->where(function ($query) {
                    $query->where('locale', app()->getLocale())
                        ->orWhere('locale', 'en');
                })
                ->orderByRaw('locale = ? desc', [app()->getLocale()])
                ->firstOrFail();
        } catch (ModelNotFoundException $e) {
            throw $e;
        } catch (\Throwable) {
            abort(404);
        }

        return view('pages.cms', compact('page'));
    }
}
