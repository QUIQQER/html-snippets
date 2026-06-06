<?php

use QUI\HtmlSnippets\Snippets;

QUI::getAjax()->registerFunction(
    'package_quiqqer_html-snippets_ajax_backend_activateSnippet',
    function ($projectName, $snippetName) {
        $Project = QUI::getProject($projectName);
        $Snippet = Snippets::get($Project, $snippetName);
        $Snippet->activate();
    },
    ['projectName', 'snippetName'],
    'Permission::checkAdminUser'
);
