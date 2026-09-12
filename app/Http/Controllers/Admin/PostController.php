<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Support\HtmlContent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PostController extends Controller
{
    public function index(): View
    {
        return view('admin.posts.index', [
            'posts' => Post::query()->latest()->paginate(20),
        ]);
    }

    public function create(): View
    {
        return view('admin.posts.form', ['post' => new Post(['is_published' => true, 'locale' => 'en'])]);
    }

    public function store(Request $request): RedirectResponse
    {
        Post::query()->create($this->validated($request));

        return redirect()->route('admin.posts.index')->with('status', 'Article published.');
    }

    public function edit(Post $post): View
    {
        return view('admin.posts.form', compact('post'));
    }

    public function update(Request $request, Post $post): RedirectResponse
    {
        $post->update($this->validated($request, $post->id));

        return redirect()->route('admin.posts.index')->with('status', 'Article updated.');
    }

    public function destroy(Post $post): RedirectResponse
    {
        $post->delete();

        return back()->with('status', 'Article deleted.');
    }

    protected function validated(Request $request, ?int $id = null): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:180'],
            'slug' => ['nullable', 'string', 'max:180', 'unique:posts,slug,'.($id ?: 'NULL')],
            'locale' => ['required', 'string', 'max:8'],
            'excerpt' => ['nullable', 'string', 'max:300'],
            'content' => ['required', 'string', 'max:200000'],
            'meta_title' => ['nullable', 'string', 'max:180'],
            'meta_description' => ['nullable', 'string', 'max:300'],
            'is_published' => ['sometimes', 'boolean'],
        ]);

        $data['is_published'] = $request->boolean('is_published');
        $data['content'] = HtmlContent::clean($data['content']);

        return $data;
    }
}
