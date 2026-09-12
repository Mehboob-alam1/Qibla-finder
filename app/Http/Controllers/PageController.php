<?php

namespace App\Http\Controllers;

use App\Models\Page;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PageController extends Controller
{
    public function show(Request $request, string $slug): View|RedirectResponse
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

        $requested = '/'.ltrim($request->path(), '/');
        if ($requested !== $page->publicPath()) {
            return redirect($page->publicPath(), 301);
        }

        return view('pages.cms', compact('page'));
    }
}
