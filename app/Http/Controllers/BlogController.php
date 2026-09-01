<?php

namespace App\Http\Controllers;

use App\Models\Faq;
use App\Models\Post;
use Illuminate\View\View;

class BlogController extends Controller
{
    public function index(): View
    {
        $posts = Post::query()
            ->published()
            ->forLocale()
            ->latest('published_at')
            ->paginate(9);

        return view('pages.blog', compact('posts'));
    }

    public function show(string $slug): View
    {
        $post = Post::query()->published()->where('slug', $slug)->firstOrFail();
        $related = Post::query()
            ->published()
            ->forLocale()
            ->where('id', '!=', $post->id)
            ->latest('published_at')
            ->limit(3)
            ->get();

        return view('pages.blog-show', compact('post', 'related'));
    }

    public function faq(): View
    {
        $faqs = Faq::query()->published()->forLocale()->orderBy('sort_order')->get()->groupBy('category');

        return view('pages.faq', compact('faqs'));
    }
}
