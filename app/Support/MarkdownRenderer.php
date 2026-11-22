<?php

namespace App\Support;

use Illuminate\Support\Str;

class MarkdownRenderer
{
    /**
     * Calcula el prefijo de ruta web hacia /storage respetando instalaciones en subdirectorios.
     */
    public static function storageUrlPath(string $relativePath = ''): string
    {
        $clean = ltrim($relativePath, '/');
        $assetUrl = asset($clean !== '' ? 'storage/' . $clean : 'storage');
        $path = parse_url($assetUrl, PHP_URL_PATH);

        if (is_string($path) && $path !== '') {
            return $clean === '' ? rtrim($path, '/') : $path;
        }

        return $clean === '' ? '/storage' : '/storage/' . $clean;
    }

    /**
     * Normaliza rutas de imágenes Markdown (/storage/...) al prefijo actual de la aplicación.
     */
    public static function normalizeStorageUrls(?string $markdown): string
    {
        if ($markdown === null || $markdown === '') {
            return '';
        }

        $targetBase = self::storageUrlPath();
        if ($targetBase === '/storage') {
            return $markdown;
        }

        return preg_replace_callback(
            '/```[\s\S]*?```|`[^`\r\n]*`|(!\[[^\]]*\]\()\/storage\/([^)\s]+(?:\s+"[^"]*")?\))/',
            function ($match) use ($targetBase) {
                if (!isset($match[1])) {
                    return $match[0];
                }
                return $match[1] . $targetBase . '/' . $match[2];
            },
            $markdown
        );
    }

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

        $targetBase = self::storageUrlPath();
        if ($pdfLocalImages) {
            $localPrefix = rtrim(public_path('storage'), '/\\') . '/';
            $storagePattern = preg_quote($targetBase, '/');
            $html = preg_replace('/(<img\b[^>]*\bsrc=["\'])(?:' . $storagePattern . '|\/storage)\//i', '$1' . $localPrefix, $html);
        } elseif ($targetBase !== '/storage') {
            $html = preg_replace('/(<img\b[^>]*\bsrc=["\'])\/storage\//i', '$1' . $targetBase . '/', $html);
        }

        return $html;
    }
}
