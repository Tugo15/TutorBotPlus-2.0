<?php

namespace App\Support;

use Illuminate\Support\Str;

class MarkdownRenderer
{
    /**
     * Renderiza Markdown de manera segura preservando bloques matemáticos ($...$ / $$...$$)
     * y bloques de código sin alterarlos durante la conversión.
     */
    public static function render(?string $content, bool $pdfLocalImages = false): string
    {
        $sanitized = preg_replace('/!\[(.*?)\]\s*\r?\n\s*\(([^)]+)\)/', '![$1]($2)', $content ?? '');
        $mathBlocks = [];

        $sanitized = preg_replace_callback(
            '/```[\s\S]*?```|`[^`\r\n]*`|\$\$[\s\S]+?\$\$|(?<!\$)\$(?!\$)([^\$\r\n]+?)\$(?!\$)|(?<=[\p{L}\p{N}])\*(?=[\p{L}\p{N}])/u',
            function ($match) use (&$mathBlocks, $pdfLocalImages) {
                if ($match[0] === '*') {
                    return '\\*';
                }
                if (!$pdfLocalImages && str_starts_with($match[0], '$')) {
                    $key = 'TBMATHPLACEHOLDER' . count($mathBlocks) . 'END';
                    $mathBlocks[$key] = e($match[0]);
                    return $key;
                }
                return $match[0];
            },
            $sanitized
        );

        $html = Str::markdown($sanitized, [
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
        ]);

        if (!empty($mathBlocks)) {
            $html = strtr($html, $mathBlocks);
        }

        if ($pdfLocalImages) {
            $html = str_replace('src="/storage/', 'src="' . public_path('storage/') . '/', $html);
        }

        return $html;
    }
}
