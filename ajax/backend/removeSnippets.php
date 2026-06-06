<?php

use QUI\HtmlSnippets\Snippets;

QUI::getAjax()->registerFunction(
    'package_quiqqer_html-snippets_ajax_backend_removeSnippets',
    function ($projectName, $snippetNames) {
        $Project = QUI::getProject($projectName);
        $snippetNames = json_decode($snippetNames, true);

        foreach ($snippetNames as $snippetName) {
            Snippets::delete($Project, $snippetName);
        }
    },
    ['projectName', 'snippetNames'],
    'Permission::checkAdminUser'
);
