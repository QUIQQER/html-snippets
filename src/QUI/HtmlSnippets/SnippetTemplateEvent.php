<?php

namespace QUI\HtmlSnippets;

use QUI;
use QUI\Smarty\Collector;

use function array_values;
use function base64_encode;

/**
 * Class SnippetEvent
 *
 * Represents an event that appends snippets to a collector.
 * This class is used to execute the snippets for a specific template event
 */
class SnippetTemplateEvent
{
    protected array $snippets;

    public function __construct(array $snippets)
    {
        $this->snippets = $snippets;
    }

    public function onFireEvent(...$args): void
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

            $gdprIsInstalled = QUI::getPackageManager()->isInstalled('quiqqer/gdpr');

            if (empty($snippet['gdpr']) || !$gdprIsInstalled) {
                $Collector->append($snippet['snippet']);
                continue;
            }

            // consider gdpr status
            $div = '<div ';
            $div .= ' data-qui-html-snippet="gdpr"';
            $div .= ' data-qui-html-snippet-gdpr-category="' . $snippet['gdpr'] . '"';
            $div .= ' style="display: none"';
            $div .= '>';
            $div .= base64_encode($snippet['snippet']);
            $div .= '</div>';

            $Collector->append($div);
        }
    }
}
