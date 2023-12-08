<?php

namespace QUI\HtmlSnippets;

use QUI;

class EventHandler
{
    protected static array|null $events = null;

    public static function onQuiqqerInit(): void
    {
        if (self::$events !== null) {
            return;
        }

        $snippets = QUI::getDatabase()->fetch([
            'from' => Snippets::table()
        ]);

        // load event snippets
        foreach ($snippets as $snippet) {
            if (empty($snippet['event'])) {
                continue;
            }

            self::$events[$snippet['event']][] = $snippet;
        }

        foreach (self::$events as $event => $snippet) {
            QUI::getEvents()->addEvent($event, [new SnippetTemplateEvent($snippet), 'onFireEvent']);
        }
    }
}
