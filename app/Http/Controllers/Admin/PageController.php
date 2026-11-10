<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Support\HtmlContent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PageController extends Controller
{
    public function index(): View
    {
        return view('admin.pages.index', [
            'pages' => Page::query()->latest()->paginate(20),
        ]);
    }

    public function create(): View
    {
        return view('admin.pages.form', ['page' => new Page([
            'is_published' => true,
            'locale' => 'en',
            'url_style' => 'prefixed',
            'show_in_header' => false,
            'show_in_footer' => true,
            'show_page_title' => true,
        ])]);
    }

    public function store(Request $request): RedirectResponse
    {
        Page::query()->create($this->validated($request));

        return redirect()->route('admin.pages.index')->with('status', 'Page created.');
    }

    public function edit(Page $page): View
    {
        return view('admin.pages.form', compact('page'));
    }

    public function update(Request $request, Page $page): RedirectResponse
    {
        $page->update($this->validated($request, $page->id));

        return redirect()->route('admin.pages.index')->with('status', 'Page updated.');
    }

    public function destroy(Page $page): RedirectResponse
    {
        $page->delete();

        return back()->with('status', 'Page deleted.');
    }

    /**
     * Parse an uploaded or pasted HTML document into page fields (does not save).
     */
    public function importHtml(Request $request): JsonResponse
    {
        $data = $request->validate([
            'html' => ['nullable', 'string', 'max:5000000'],
            'file' => ['nullable', 'file', 'max:5120', 'mimetypes:text/html,text/plain,application/octet-stream'],
        ]);

        $html = (string) ($data['html'] ?? '');
        $filename = null;

        if ($request->hasFile('file')) {
            $uploaded = $request->file('file');
            $extension = strtolower($uploaded->getClientOriginalExtension() ?: '');
            if (! in_array($extension, ['html', 'htm', 'txt', ''], true)) {
                throw ValidationException::withMessages([
                    'file' => 'Please upload an .html or .htm file.',
                ]);
            }
            $filename = $uploaded->getClientOriginalName();
            $html = (string) file_get_contents($uploaded->getRealPath());
        }

        if (trim($html) === '') {
            throw ValidationException::withMessages([
                'html' => 'Paste HTML or upload an .html file to import.',
            ]);
        }

        // Strip a UTF-8 BOM if present.
        $html = preg_replace('/^\xEF\xBB\xBF/', '', $html) ?? $html;

        $imported = HtmlContent::importDocument($html, $filename);

        if ($imported['content'] === '') {
            throw ValidationException::withMessages([
                'html' => 'No usable HTML content was found in that file.',
            ]);
        }

        return response()->json([
            'title' => $imported['title'],
            'slug' => $imported['slug'],
            'meta_title' => $imported['meta_title'],
            'meta_description' => $imported['meta_description'],
            'content' => $imported['content'],
            'show_page_title' => ! preg_match('/<h1\b/i', $imported['content']),
        ]);
    }

    protected function validated(Request $request, ?int $id = null): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:180'],
            'slug' => [
                'nullable',
                'string',
                'max:180',
                'alpha_dash',
                Rule::unique('pages', 'slug')
                    ->where(fn ($query) => $query->where('locale', $request->input('locale')))
                    ->ignore($id),
            ],
            'url_style' => ['required', Rule::in(['flat', 'prefixed'])],
            'locale' => ['required', 'string', 'max:8'],
            'content' => ['required', 'string', 'max:5000000'],
            'meta_title' => ['nullable', 'string', 'max:180'],
            'meta_description' => ['nullable', 'string', 'max:300'],
            'is_published' => ['sometimes', 'boolean'],
            'show_in_header' => ['sometimes', 'boolean'],
            'show_in_footer' => ['sometimes', 'boolean'],
            'show_page_title' => ['sometimes', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $data['slug'] = $data['slug'] ?: Str::slug($data['title']);
        $data['is_published'] = $request->boolean('is_published');
        $data['show_in_header'] = $request->boolean('show_in_header');
        $data['show_in_footer'] = $request->boolean('show_in_footer');
        $data['show_page_title'] = $request->boolean('show_page_title', true);
        $data['content'] = HtmlContent::clean($data['content']);

        if ($data['content'] === '') {
            throw ValidationException::withMessages([
                'content' => 'Page content is required. Import HTML or write content in the editor.',
            ]);
        }

        if ($data['url_style'] === 'flat' && in_array($data['slug'], Page::reservedSlugs(), true)) {
            throw ValidationException::withMessages([
                'slug' => 'That slug is already used by the site. Pick another slug, or use a /p/ URL.',
            ]);
        }

        return $data;
    }
}
