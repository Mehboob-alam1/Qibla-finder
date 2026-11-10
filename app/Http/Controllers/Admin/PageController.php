<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Support\HtmlContent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
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

    public function importForm(): View
    {
        return view('admin.pages.import', [
            'locales' => array_keys(config('qibla.locales', ['en' => []])),
        ]);
    }

    /**
     * Upload an HTML file, create a published page, and redirect to the live URL.
     */
    public function importStore(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'max:5120'],
            'locale' => ['required', 'string', 'max:8'],
            'url_style' => ['required', Rule::in(['flat', 'prefixed'])],
            'slug' => ['nullable', 'string', 'max:180', 'alpha_dash'],
            'show_in_header' => ['sometimes', 'boolean'],
            'show_in_footer' => ['sometimes', 'boolean'],
        ]);

        $uploaded = $request->file('file');
        $extension = strtolower($uploaded->getClientOriginalExtension() ?: '');
        if (! in_array($extension, ['html', 'htm', ''], true)) {
            throw ValidationException::withMessages([
                'file' => 'Please upload an .html or .htm file.',
            ]);
        }

        $html = (string) file_get_contents($uploaded->getRealPath());
        $html = preg_replace('/^\xEF\xBB\xBF/', '', $html) ?? $html;
        $imported = HtmlContent::importDocument($html, $uploaded->getClientOriginalName());

        if ($imported['content'] === '') {
            throw ValidationException::withMessages([
                'file' => 'No usable HTML content was found in that file.',
            ]);
        }

        $slug = $request->input('slug') ?: $imported['slug'];
        $slug = $this->uniqueSlug($slug, $request->input('locale'));

        if ($request->input('url_style') === 'flat' && in_array($slug, Page::reservedSlugs(), true)) {
            throw ValidationException::withMessages([
                'slug' => 'That slug is reserved. Choose another slug or use a /p/ URL.',
            ]);
        }

        $payload = $this->filterExistingColumns([
            'title' => $imported['title'],
            'slug' => $slug,
            'url_style' => $request->input('url_style'),
            'locale' => $request->input('locale'),
            'content' => $imported['content'],
            'custom_css' => $imported['custom_css'] !== '' ? $imported['custom_css'] : null,
            'body_class' => $imported['body_class'] !== '' ? $imported['body_class'] : null,
            'body_style' => $imported['body_style'] !== '' ? $imported['body_style'] : null,
            'meta_title' => $imported['meta_title'] !== '' ? $imported['meta_title'] : null,
            'meta_description' => $imported['meta_description'] !== '' ? $imported['meta_description'] : null,
            'is_published' => true,
            'show_in_header' => $request->boolean('show_in_header'),
            'show_in_footer' => $request->boolean('show_in_footer', true),
            'show_page_title' => $imported['custom_css'] === '' && ! preg_match('/<h1\b/i', $imported['content']),
            'sort_order' => 0,
        ]);

        $page = Page::query()->create($payload);

        $warning = $imported['warnings'] !== []
            ? ' Note: '.implode(' ', $imported['warnings'])
            : '';

        return redirect()
            ->to($page->publicPath())
            ->with('status', 'Page imported and published.'.$warning);
    }

    public function store(Request $request): RedirectResponse
    {
        $page = Page::query()->create($this->validated($request));

        if ($page->is_published) {
            return redirect()->to($page->publicPath())->with('status', 'Page created and live.');
        }

        return redirect()->route('admin.pages.index')->with('status', 'Page created.');
    }

    public function edit(Page $page): View
    {
        return view('admin.pages.form', compact('page'));
    }

    public function update(Request $request, Page $page): RedirectResponse
    {
        $page->update($this->validated($request, $page->id));

        if ($page->is_published) {
            return redirect()->to($page->publicPath())->with('status', 'Page updated and live.');
        }

        return redirect()->route('admin.pages.index')->with('status', 'Page updated.');
    }

    public function destroy(Page $page): RedirectResponse
    {
        $page->delete();

        return back()->with('status', 'Page deleted.');
    }

    /**
     * Parse HTML into fields without saving (used by the editor form helper).
     */
    public function importHtml(Request $request): JsonResponse
    {
        $data = $request->validate([
            'html' => ['nullable', 'string', 'max:5000000'],
            'file' => ['nullable', 'file', 'max:5120'],
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
            'custom_css' => $imported['custom_css'],
            'body_class' => $imported['body_class'],
            'body_style' => $imported['body_style'],
            'show_page_title' => $imported['custom_css'] === '' && ! preg_match('/<h1\b/i', $imported['content']),
            'warnings' => $imported['warnings'],
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
            'custom_css' => ['nullable', 'string', 'max:5000000'],
            'body_class' => ['nullable', 'string', 'max:255'],
            'body_style' => ['nullable', 'string', 'max:5000'],
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

        $rawCss = trim((string) ($data['custom_css'] ?? ''));
        if ($rawCss !== '') {
            $sanitized = HtmlContent::sanitizeCss($rawCss);
            $data['custom_css'] = str_contains($sanitized, '.cms-imported')
                ? $sanitized
                : HtmlContent::scopeCss($sanitized);
        } else {
            $data['custom_css'] = null;
        }

        $data['body_class'] = trim((string) ($data['body_class'] ?? '')) ?: null;
        $bodyStyle = HtmlContent::sanitizeInlineStyle((string) ($data['body_style'] ?? ''));
        $data['body_style'] = $bodyStyle !== '' ? $bodyStyle : null;

        if ($data['content'] === '') {
            throw ValidationException::withMessages([
                'content' => 'Page content is required.',
            ]);
        }

        if ($data['url_style'] === 'flat' && in_array($data['slug'], Page::reservedSlugs(), true)) {
            throw ValidationException::withMessages([
                'slug' => 'That slug is already used by the site. Pick another slug, or use a /p/ URL.',
            ]);
        }

        return $this->filterExistingColumns($data);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function filterExistingColumns(array $data): array
    {
        if (! Schema::hasTable('pages')) {
            return $data;
        }

        return collect($data)
            ->filter(fn ($value, $key) => Schema::hasColumn('pages', (string) $key))
            ->all();
    }

    protected function uniqueSlug(string $slug, string $locale): string
    {
        $slug = Str::slug($slug) ?: 'page';
        $base = $slug;
        $i = 2;

        while (
            Page::query()
                ->where('slug', $slug)
                ->where('locale', $locale)
                ->exists()
        ) {
            $slug = $base.'-'.$i;
            $i++;
        }

        return $slug;
    }
}
