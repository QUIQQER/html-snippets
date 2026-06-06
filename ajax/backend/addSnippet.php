<?php

use QUI\HtmlSnippets\Snippets;

QUI::getAjax()->registerFunction(
    'package_quiqqer_html-snippets_ajax_backend_addSnippet',
    function ($projectName, $snippetName, $eventName, $snippet = '', $gdpr = '') {
        $Project = QUI::getProject($projectName);
        $Snippet = Snippets::create($Project, $snippetName, $eventName, $snippet);

        $Snippet->setGDPRCategory($gdpr);
        $Snippet->save();

        return $Snippet->getName();
    },
    ['projectName', 'snippetName', 'eventName', 'snippet', 'gdpr'],
    'Permission::checkAdminUser'
);
