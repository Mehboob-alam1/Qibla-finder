<?php

namespace App\Http\Controllers;

use App\Models\Faq;
use App\Models\Post;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\View\View;

class BlogController extends Controller
{
    public function index(): View
    {
        $posts = collect();

        try {
            $posts = Post::query()
                ->published()
                ->forLocale()
                ->latest('published_at')
                ->paginate(9);
        } catch (\Throwable) {
            $posts = new LengthAwarePaginator([], 0, 9);
        }

        return view('pages.blog', compact('posts'));
    }

    public function show(string $slug): View
    {
        try {
            $post = Post::query()->published()->where('slug', $slug)->firstOrFail();
            $related = Post::query()
                ->published()
                ->forLocale()
                ->where('id', '!=', $post->id)
                ->latest('published_at')
                ->limit(3)
                ->get();
        } catch (ModelNotFoundException $e) {
            throw $e;
        } catch (\Throwable) {
            abort(404);
        }

        return view('pages.blog-show', compact('post', 'related'));
    }

    public function faq(): View
    {
        $faqs = collect();

        try {
            $faqs = Faq::query()->published()->forLocale()->orderBy('sort_order')->get()->groupBy('category');
        } catch (\Throwable) {
            $faqs = collect();
        }

        return view('pages.faq', compact('faqs'));
    }
}
