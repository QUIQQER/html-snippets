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

        getGDPRList: function() {
            
        }

    };
});
