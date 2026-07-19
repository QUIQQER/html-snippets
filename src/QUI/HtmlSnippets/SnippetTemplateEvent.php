<?php

namespace QUI\HtmlSnippets;

use QUI;
use QUI\Smarty\Collector;

use function array_values;
use function base64_encode;
use function htmlspecialchars;

use const ENT_QUOTES;
use const ENT_SUBSTITUTE;

/**
 * Class SnippetEvent
 *
 * Represents an event that appends snippets to a collector.
 * This class is used to execute the snippets for a specific template event
 */
class SnippetTemplateEvent
{
    /**
     * @var list<array{snippet: string, active?: mixed, gdpr?: string}>
     */
    protected array $snippets;

    /**
     * @param list<array{snippet: string, active?: mixed, gdpr?: string}> $snippets
     */
    public function __construct(array $snippets)
    {
        $this->snippets = $snippets;
    }

    public function onFireEvent(mixed ...$args): void
    {
        $args = array_values($args);

        // only template events
        if (!isset($args[0]) || !isset($args[1])) {
            return;
        }

        $Collector = null;
        $Template = null;

        if ($args[0] instanceof Collector) {
            $Collector = $args[0];
        }

        if ($args[0] instanceof QUI\Smarty\Collector) {
            $Collector = $args[0];
        }

        if ($args[1] instanceof QUI\Template) {
            $Template = $args[1];
        }

        if (!$Collector) {
            return;
        }

        if (!$Template) {
            return;
        }

        foreach ($this->snippets as $snippet) {
            if (empty($snippet['active'])) {
                continue;
            }

            $gdprIsInstalled = $this->isGdprInstalled();

            if (empty($snippet['gdpr']) || !$gdprIsInstalled) {
                $Collector->append($snippet['snippet']);
                continue;
            }

            $snippetGdprCategory = htmlspecialchars(
                $snippet['gdpr'],
                ENT_QUOTES | ENT_SUBSTITUTE,
                'UTF-8'
            );
            $snippetContentEncoded = base64_encode($snippet['snippet']);

            $snippetHtml = <<<EOF
<template
    data-qui-html-snippet="gdpr"
    data-qui-html-snippet-gdpr-category="$snippetGdprCategory"
>
$snippetContentEncoded
</template>
EOF;

            $Collector->append($snippetHtml);
        }
    }

    protected function isGdprInstalled(): bool
    {
        return QUI::getPackageManager()->isInstalled('quiqqer/gdpr');
    }
}
