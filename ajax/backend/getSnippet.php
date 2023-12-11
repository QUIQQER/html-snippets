<?php

use QUI\HtmlSnippets\Snippets;

QUI::$Ajax->registerFunction(
    'package_quiqqer_html-snippets_ajax_backend_getSnippet',
    function ($projectName, $snippetName) {
        return Snippets::get(
            QUI::getProject($projectName),
            $snippetName
        )->toArray();
    },
    ['projectName', 'snippetName'],
    'Permission::checkAdminUser'
);
