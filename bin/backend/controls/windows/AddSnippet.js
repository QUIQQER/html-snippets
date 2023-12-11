define('package/quiqqer/html-snippets/bin/backend/controls/windows/AddSnippet', [

    'qui/QUI',
    'qui/controls/windows/Confirm',
    'package/quiqqer/html-snippets/bin/backend/Utils',
    'Ajax',
    'Locale',
    'Mustache',

    'text!package/quiqqer/html-snippets/bin/backend/controls/windows/AddSnippet.html',
    'css!package/quiqqer/html-snippets/bin/backend/controls/windows/AddSnippet.css'

], function(QUI, QUIConfirm, SnippetUtils, QUIAjax, QUILocale, Mustache, template) {
    'use strict';

    const lg = 'quiqqer/html-snippets';

    return new Class({

        Extends: QUIConfirm,
        Type: 'package/quiqqer/html-snippets/bin/backend/controls/windows/AddSnippet',

        Binds: [
            '$onOpen',
            'submit'
        ],

        options: {
            Project: null
        },

        initialize: function(options) {
            this.parent(options);

            this.setAttributes({
                icon: 'fa fa-plus',
                title: QUILocale.get(lg, 'window.add.title'),
                maxHeight: 600,
                maxWidth: 800,
                autoclose: false
            });

            this.addEvents({
                onOpen: this.$onOpen
            });
        },

        $onOpen: function() {
            this.getContent().addClass('html-snippets-add');

            this.getContent().set('html', Mustache.render(template, {
                textName: QUILocale.get(lg, 'window.name'),
                textEvent: QUILocale.get(lg, 'window.event'),
                textSnippet: QUILocale.get(lg, 'window.snippet'),
                textGDPR: QUILocale.get(lg, 'window.gdpr'),
                textEssential: QUILocale.get('quiqqer/gdpr', 'cookie.category.essential'),
                textPreferences: QUILocale.get('quiqqer/gdpr', 'cookie.category.preferences'),
                textStatistics: QUILocale.get('quiqqer/gdpr', 'cookie.category.statistics'),
                textMarketing: QUILocale.get('quiqqer/gdpr', 'cookie.category.marketing')
            }));

            SnippetUtils.isGDPRInstalled().then((isInstalled) => {
                if (isInstalled) {
                    this.getContent().getElement('.gdpr-row').setStyle('display', null);
                }
            });

            this.getContent().getElement('submit', (e) => {
                e.stop();
            });

            this.getContent().getElement('form').elements.name.focus();
        },

        submit: function() {
            const Project = this.getAttribute('Project');
            const Form = this.getContent().getElement('form');

            const showError = function(Node) {
                Node.focus();

                if ('reportValidity' in Node) {
                    Node.reportValidity();
                }
            };

            if (Form.elements.name.value === '') {
                return showError(Form.elements.name);
            }

            if (Form.elements.eventName.value === '') {
                return showError(Form.elements.eventName);
            }

            if (Form.elements.snippet.value === '') {
                return showError(Form.elements.snippet);
            }

            this.Loader.show();

            QUIAjax.post('package_quiqqer_html-snippets_ajax_backend_addSnippet', (snippetName) => {
                this.fireEvent('submit', [this, snippetName]);
                this.close();
            }, {
                'package': 'quiqqer/html-snippets',
                projectName: Project.getName(),
                snippetName: Form.elements.name.value,
                eventName: Form.elements.eventName.value,
                snippet: Form.elements.snippet.value,
                gdpr: Form.elements.gdpr.value,
                onError: () => {
                    this.Loader.hide();
                }
            });
        }
    });
});
