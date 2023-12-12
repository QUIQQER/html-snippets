<?php

namespace QUI\HtmlSnippets;

use QUI;
use Quiqqer\Engine\Collector;

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

        if (is_iterable(self::$events)) {
            foreach (self::$events as $event => $snippet) {
                QUI::getEvents()->addEvent($event, [new SnippetTemplateEvent($snippet), 'onFireEvent']);
            }
        }
    }

    public static function onTemplateEnd(
        Collector $Collection,
        QUI\Template $Template
    ) {
        if (QUI::getPackageManager()->isInstalled('quiqqer/gdpr')) {
            $Collection->append(
                '<script src="' . URL_OPT_DIR . 'quiqqer/html-snippets/bin/frontend/gdprReader.js"></script>'
            );
        }
    }
}
