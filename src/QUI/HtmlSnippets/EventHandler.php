<?php

namespace QUI\HtmlSnippets;

use QUI;
use QUI\Smarty\Collector;

class EventHandler
{
    /**
     * @var array<string, list<array<string, mixed>>>|null
     */
    protected static array|null $events = null;

    public static function onQuiqqerInit(): void
    {
        if (self::$events !== null) {
            return;
        }

        $snippets = QUI::getQueryBuilder()
            ->select('*')
            ->from(QUI\Utils\Doctrine::quoteIdentifier(Snippets::table()))
            ->executeQuery()
            ->fetchAllAssociative();

        self::$events = [];

        // load event snippets
        foreach ($snippets as $snippet) {
            $event = (string)($snippet['event'] ?? '');

            if ($event === '') {
                continue;
            }

            self::$events[$event][] = [
                'snippet' => (string)($snippet['snippet'] ?? ''),
                'active' => $snippet['active'] ?? 0,
                'gdpr' => (string)($snippet['gdpr'] ?? '')
            ];
        }

        foreach (self::$events as $event => $snippet) {
            QUI::getEvents()->addEvent($event, [new SnippetTemplateEvent($snippet), 'onFireEvent']);
        }
    }

    public static function onTemplateEnd(
        Collector $Collection,
        QUI\Template $Template
    ): void {
        if (QUI::getPackageManager()->isInstalled('quiqqer/gdpr')) {
            $Collection->append(
                '<script src="' . URL_OPT_DIR . 'quiqqer/html-snippets/bin/frontend/gdprReader.js"></script>'
            );
        }
    }
}
