<?php

use QUI\HtmlSnippets\Snippets;

QUI::getAjax()->registerFunction(
    'package_quiqqer_html-snippets_ajax_backend_getSnippets',
    function ($projectName) {
        return Snippets::getList(QUI::getProject($projectName));
    },
    ['projectName'],
    'Permission::checkAdminUser'
);
