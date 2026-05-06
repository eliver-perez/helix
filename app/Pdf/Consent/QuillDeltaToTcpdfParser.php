<?php

declare(strict_types=1);

namespace App\Pdf\Consent;

class QuillDeltaToTcpdfParser
{
    public function parse(string $deltaJson): array
    {
        $data = json_decode($deltaJson, true);

        if (!isset($data['ops']) || !is_array($data['ops'])) {
            return [];
        }

        $blocks = [];
        $lineBuffer = '';
        $listTypeOpen = null;

        foreach ($data['ops'] as $op) {
            $insert = $op['insert'] ?? '';
            $attrs = $op['attributes'] ?? [];

            if (is_array($insert)) {
                $embedText = $this->parseQuillEmbed($insert);

                if ($embedText !== '') {
                    $lineBuffer .= $this->applyInlineFormats($embedText, $attrs);
                }

                continue;
            }

            $parts = explode("\n", (string)$insert);
            $lastIndex = count($parts) - 1;

            foreach ($parts as $i => $part) {
                if ($part !== '') {
                    $lineBuffer .= $this->applyInlineFormats($part, $attrs);
                }

                if ($i < $lastIndex) {
                    $block = $this->renderBlockAsArray($lineBuffer, $attrs, $listTypeOpen);

                    if ($block !== null) {
                        $blocks[] = $block;
                    }

                    $lineBuffer = '';
                }
            }
        }

        if (trim(strip_tags($lineBuffer)) !== '') {
            if ($listTypeOpen !== null) {
                $blocks[] = [
                    'type' => 'list_close',
                    'html' => $this->closeListTag($listTypeOpen),
                ];

                $listTypeOpen = null;
            }

            $blocks[] = [
                'type' => 'paragraph',
                'html' => $lineBuffer,
            ];
        } elseif ($listTypeOpen !== null) {
            $blocks[] = [
                'type' => 'list_close',
                'html' => $this->closeListTag($listTypeOpen),
            ];
        }

        return $blocks;
    }

    private function renderBlockAsArray(string $content, array $attrs, ?string &$listTypeOpen): ?array
    {
        $content = trim($content) === '' ? '&nbsp;' : $content;

        $align = '';

        if (!empty($attrs['align'])) {
            $allowed = ['left', 'center', 'right', 'justify'];

            if (in_array($attrs['align'], $allowed, true)) {
                $align = ' style="text-align:' . $attrs['align'] . ';"';
            }
        }

        if (!empty($attrs['header'])) {
            $html = '';

            if ($listTypeOpen !== null) {
                $html .= $this->closeListTag($listTypeOpen);
                $listTypeOpen = null;
            }

            $level = (int)$attrs['header'];
            $level = max(1, min(6, $level));

            return [
                'type' => 'header',
                'html' => $html . "<h{$level}{$align}>{$content}</h{$level}>",
            ];
        }

        if (!empty($attrs['list'])) {
            $type = $attrs['list'] === 'ordered' ? 'ol' : 'ul';
            $html = '';

            if ($listTypeOpen !== $type) {
                if ($listTypeOpen !== null) {
                    $html .= $this->closeListTag($listTypeOpen);
                }

                $html .= "<{$type}>";
                $listTypeOpen = $type;
            }

            return [
                'type' => 'list_item',
                'html' => $html . "<li>{$content}</li>",
            ];
        }

        $html = '';

        if ($listTypeOpen !== null) {
            $html .= $this->closeListTag($listTypeOpen);
            $listTypeOpen = null;
        }

        return [
            'type' => 'paragraph',
            'html' => $html . "<span{$align}>{$content}</span>",
        ];
    }

    private function parseQuillEmbed(array $insert): string
    {
        if (isset($insert['variable'])) {
            $var = $insert['variable'];

            if (is_array($var)) {
                if (!empty($var['key'])) {
                    return '{{' . $var['key'] . '}}';
                }

                if (!empty($var['value'])) {
                    return '{{' . $var['value'] . '}}';
                }

                if (!empty($var['nombre'])) {
                    return '{{' . $var['nombre'] . '}}';
                }
            }

            if (is_string($var) && $var !== '') {
                return '{{' . $var . '}}';
            }
        }

        return '';
    }

    private function applyInlineFormats(string $text, array $attrs = []): string
    {
        if (str_starts_with($text, '{{') && str_ends_with($text, '}}')) {
            $escaped = $text;
        } else {
            $escaped = htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        }

        if (!empty($attrs['bold'])) {
            $escaped = '<b>' . $escaped . '</b>';
        }

        if (!empty($attrs['italic'])) {
            $escaped = '<i>' . $escaped . '</i>';
        }

        if (!empty($attrs['underline'])) {
            $escaped = '<u>' . $escaped . '</u>';
        }

        if (!empty($attrs['strike'])) {
            $escaped = '<del>' . $escaped . '</del>';
        }

        return $escaped;
    }

    private function closeListTag(string $type): string
    {
        return "</{$type}>";
    }
}