<?php

namespace App\Http\Controllers;

use App\Models\Page;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class PageController extends Controller
{
    public function show(Request $request, string $slug): View|RedirectResponse|Response
    {
        if (! in_array($slug, Page::indexableSlugs(), true)) {
            return response(view('errors.gone'), 410);
        }

        try {
            $page = Page::query()
                ->published()
                ->where('slug', $slug)
                ->where('locale', app()->getLocale())
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
