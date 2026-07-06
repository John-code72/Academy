<?php

namespace App\Services\KnowledgeIngestion;

class KnowledgeDocumentChunker
{
    public function sanitize(string $text): string
    {
        $text = $this->normalize($text);

        if ($text === '') {
            return '';
        }

        if (! mb_check_encoding($text, 'UTF-8')) {
            $converted = @iconv('UTF-8', 'UTF-8//IGNORE', $text);
            if (is_string($converted) && $converted !== '') {
                $text = $converted;
            } else {
                $text = mb_convert_encoding($text, 'UTF-8', 'ISO-8859-1, Windows-1252, UTF-8');
            }
        }

        $text = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $text) ?? $text;
        $text = @iconv('UTF-8', 'UTF-8//IGNORE', $text) ?: $text;

        // Strip remaining invalid UTF-8 byte sequences (common in government PDFs).
        $text = preg_replace('/(?:[\xC0-\xDF](?![\x80-\xBF])|[\xE0-\xEF](?:[^\x80-\xBF]|(?:[\x80-\xBF](?![\x80-\xBF]))|[\x80-\xBF]{2}(?![\x80-\xBF]))|[\xF0-\xF7](?:[^\x80-\xBF]|(?:[\x80-\xBF](?![\x80-\xBF])){1,2}|[\x80-\xBF]{3}(?![\x80-\xBF]))|[\xF8-\xFF].)/', '', $text) ?? $text;

        if (! mb_check_encoding($text, 'UTF-8')) {
            $text = mb_convert_encoding($text, 'UTF-8', 'ISO-8859-1');
            $text = @iconv('UTF-8', 'UTF-8//IGNORE', $text) ?: $text;
        }

        return trim($text);
    }

    public function chunk(string $text): array
    {
        $text = $this->sanitize($text);

        if ($text === '') {
            return [];
        }

        $size = max(400, (int) config('knowledge_sources.chunk_size', 1200));
        $overlap = max(0, min($size - 100, (int) config('knowledge_sources.chunk_overlap', 200)));

        $paragraphs = preg_split("/\n{2,}/", $text) ?: [$text];
        $chunks = [];
        $buffer = '';

        foreach ($paragraphs as $paragraph) {
            $paragraph = trim($paragraph);
            if ($paragraph === '') {
                continue;
            }

            if (strlen($buffer) + strlen($paragraph) + 2 <= $size) {
                $buffer = $buffer === '' ? $paragraph : $buffer . "\n\n" . $paragraph;
                continue;
            }

            if ($buffer !== '') {
                $chunks[] = $buffer;
                $buffer = $this->tailOverlap($buffer, $overlap);
            }

            if (strlen($paragraph) <= $size) {
                $buffer = $buffer === '' ? $paragraph : trim($buffer . "\n\n" . $paragraph);
                continue;
            }

            foreach ($this->splitLongParagraph($paragraph, $size) as $part) {
                if (strlen($buffer) + strlen($part) + 2 > $size && $buffer !== '') {
                    $chunks[] = $buffer;
                    $buffer = $this->tailOverlap($buffer, $overlap);
                }

                $buffer = $buffer === '' ? $part : trim($buffer . "\n\n" . $part);
            }
        }

        if ($buffer !== '') {
            $chunks[] = $buffer;
        }

        return array_values(array_filter($chunks, fn ($chunk) => trim($chunk) !== ''));
    }

    private function normalize(string $text): string
    {
        $text = html_entity_decode(strip_tags($text), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace("/[ \t]+/", ' ', $text) ?? $text;
        $text = preg_replace("/\n{3,}/", "\n\n", $text) ?? $text;

        return trim($text);
    }

    private function tailOverlap(string $text, int $overlap): string
    {
        if ($overlap <= 0 || strlen($text) <= $overlap) {
            return '';
        }

        return trim(substr($text, -$overlap));
    }

    /**
     * @return array<int, string>
     */
    private function splitLongParagraph(string $paragraph, int $size): array
    {
        $parts = [];
        $offset = 0;
        $length = strlen($paragraph);

        while ($offset < $length) {
            $parts[] = substr($paragraph, $offset, $size);
            $offset += $size;
        }

        return $parts;
    }
}
