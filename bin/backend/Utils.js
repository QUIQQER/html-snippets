/**
 * @module package/quiqqer/html-snippets/bin/backend/Utils
 * @author www.pcsg.de (Henning Leutz)
 */
define('package/quiqqer/html-snippets/bin/backend/Utils', function() {
    'use strict';

    return {

        isGDPRInstalled: function() {
            return new Promise(function(resolve) {
                require(['Packages'], function(Packages) {
                    Packages.isInstalled('quiqqer/gdpr').then(resolve);
                });
            });
        },

        activateSnippet: function(snippetName, project) {
            return new Promise(function(resolve, reject) {
                require(['Ajax'], function(QUIAjax) {
                    QUIAjax.post('package_quiqqer_html-snippets_ajax_backend_activateSnippet', resolve, {
                        'package': 'quiqqer/html-snippets',
                        projectName: project,
                        snippetName: snippetName,
                        onError: reject
                    });
                });
            });
        },

        deactivateSnippet: function(snippetName, project) {
            return new Promise(function(resolve, reject) {
                require(['Ajax'], function(QUIAjax) {
                    QUIAjax.post('package_quiqqer_html-snippets_ajax_backend_deactivateSnippet', resolve, {
                        'package': 'quiqqer/html-snippets',
                        projectName: project,
                        snippetName: snippetName,
                        onError: reject
                    });
                });
            });
        }
    };
});
