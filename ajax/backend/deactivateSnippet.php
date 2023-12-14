<?php

use QUI\HtmlSnippets\Snippets;

QUI::$Ajax->registerFunction(
    'package_quiqqer_html-snippets_ajax_backend_deactivateSnippet',
    function ($projectName, $snippetName) {
        $Project = QUI::getProject($projectName);
        $Snippet = Snippets::get($Project, $snippetName);
        $Snippet->deactivate();
    },
    ['projectName', 'snippetName'],
    'Permission::checkAdminUser'
);
