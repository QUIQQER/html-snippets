<?php

use QUI\HtmlSnippets\Snippets;

QUI::getAjax()->registerFunction(
    'package_quiqqer_html-snippets_ajax_backend_saveSnippet',
    function ($projectName, $snippetName, $eventName, $snippet, $gdpr) {
        $Project = QUI::getProject($projectName);
        $Snippet = Snippets::get($Project, $snippetName);

        $Snippet->setEvent($eventName);
        $Snippet->setSnippet($snippet);

        if (!empty($gdpr)) {
            $Snippet->setGDPRCategory($gdpr);
        }

        $Snippet->save();

        return $Snippet->getName();
    },
    ['projectName', 'snippetName', 'eventName', 'snippet', 'gdpr'],
    'Permission::checkAdminUser'
);
