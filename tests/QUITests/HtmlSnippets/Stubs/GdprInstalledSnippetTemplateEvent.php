<?php

namespace QUITests\HtmlSnippets\Stubs;

use QUI\HtmlSnippets\SnippetTemplateEvent;

class GdprInstalledSnippetTemplateEvent extends SnippetTemplateEvent
{
    protected function isGdprInstalled(): bool
    {
        return true;
    }
}
