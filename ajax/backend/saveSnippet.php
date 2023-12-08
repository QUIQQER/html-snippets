<?php

use QUI\HtmlSnippets\Snippets;

QUI::$Ajax->registerFunction(
    'package_quiqqer_html-snippets_ajax_backend_saveSnippet',
    function ($projectName, $snippetName, $eventName, $snippet) {
        $Project = QUI::getProject($projectName);
        $Snippet = Snippets::get($Project, $snippetName);

        $Snippet->setEvent($eventName);
        $Snippet->setSnippet($snippet);
        $Snippet->save();

        return $Snippet->getName();
    },
    ['projectName', 'snippetName', 'eventName', 'snippet'],
    'Permission::checkAdminUser'
);
