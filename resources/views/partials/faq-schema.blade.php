@php
    $schemaFaqs = $schemaFaqs ?? collect();
@endphp
@if ($schemaFaqs->isNotEmpty())
<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'FAQPage',
    'mainEntity' => $schemaFaqs->map(fn ($faq) => [
        '@type' => 'Question',
        'name' => is_array($faq) ? $faq['question'] : $faq->question,
        'acceptedAnswer' => [
            '@type' => 'Answer',
            'text' => trim(strip_tags(is_array($faq) ? $faq['answer'] : $faq->answer)),
        ],
    ])->values()->all(),
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
</script>
@endif
