@php
    $editorId = $editorId ?? $name;
@endphp
<label class="block text-sm" for="{{ $editorId }}">{{ $label }}
    <span class="block mt-1 text-forest/50 font-normal">Headings help search engines. Add images from a URL or upload a file, then resize with the handles or Small / Medium / Large / Full width.</span>
    <textarea
        id="{{ $editorId }}"
        name="{{ $name }}"
        rows="{{ $rows ?? 14 }}"
        data-editor
        class="mt-1 w-full rounded-2xl border border-forest/15 px-4 py-3"
    >{{ old($name, $value) }}</textarea>
</label>
