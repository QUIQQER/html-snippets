<?php

use QUI\HtmlSnippets\Snippets;

QUI::$Ajax->registerFunction(
    'package_quiqqer_html-snippets_ajax_backend_addSnippet',
    function ($projectName, $snippetName, $eventName, $snippet = '') {
        $Project = QUI::getProject($projectName);
        $Snippet = Snippets::create($Project, $snippetName, $eventName, $snippet);

        return $Snippet->getName();
    },
    ['projectName', 'snippetName', 'eventName', 'snippet'],
    'Permission::checkAdminUser'
);
