<?php

namespace Tests\Unit;

use App\Support\HtmlContent;
use PHPUnit\Framework\TestCase;

class HtmlContentTest extends TestCase
{
    public function test_import_document_extracts_title_meta_and_body(): void
    {
        $html = <<<'HTML'
<!DOCTYPE html>
<html>
<head>
    <title>Qibla Tips</title>
    <meta name="description" content="Short guide for travelers.">
    <script>alert(1)</script>
    <style>.x{color:red}</style>
</head>
<body>
    <header class="hero"><h1>Qibla Tips</h1></header>
    <section>
        <p style="text-align: center; color: #0d3b2e">Hold the phone flat.</p>
        <a href="https://qiblafinders.io" target="_blank">Open site</a>
        <custom-wrapper><p>Nested safe text</p></custom-wrapper>
        <script>evil()</script>
    </section>
</body>
</html>
HTML;

        $imported = HtmlContent::importDocument($html);

        $this->assertSame('Qibla Tips', $imported['title']);
        $this->assertSame('Short guide for travelers.', $imported['meta_description']);
        $this->assertSame('qibla-tips', $imported['slug']);
        $this->assertStringContainsString('<h1>', $imported['content']);
        $this->assertStringContainsString('Hold the phone flat.', $imported['content']);
        $this->assertStringContainsString('Nested safe text', $imported['content']);
        $this->assertStringContainsString('text-align: center', $imported['content']);
        $this->assertStringNotContainsString('<script', $imported['content']);
        $this->assertStringNotContainsString('evil()', $imported['content']);
        $this->assertStringContainsString('rel="noopener noreferrer"', $imported['content']);
    }

    public function test_clean_removes_javascript_urls_but_keeps_images(): void
    {
        $clean = HtmlContent::clean(
            '<p><a href="javascript:alert(1)">bad</a><img src="/storage/photo.jpg" alt="Kaaba" class="content-img-md"></p>'
        );

        $this->assertStringNotContainsString('javascript:', $clean);
        $this->assertStringContainsString('src="/storage/photo.jpg"', $clean);
        $this->assertStringContainsString('alt="Kaaba"', $clean);
    }
}
