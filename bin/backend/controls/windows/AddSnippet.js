define('package/quiqqer/html-snippets/bin/backend/controls/windows/AddSnippet', [

    'qui/QUI',
    'qui/controls/windows/Confirm',
    'Ajax',
    'Locale',
    'Mustache',

    'text!package/quiqqer/html-snippets/bin/backend/controls/windows/AddSnippet.html',
    'css!package/quiqqer/html-snippets/bin/backend/controls/windows/AddSnippet.css'

], function(QUI, QUIConfirm, QUIAjax, QUILocale, Mustache, template) {
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
                maxWidth: 640,
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
                textSnippet: QUILocale.get(lg, 'window.snippet')
            }));

            this.getContent().getElement('submit', (e) => {
                e.stop();
            });

            this.getContent().getElement('form').elements.name.focus();
        },

        submit: function() {
            this.Loader.show();

            const Project = this.getAttribute('Project');
            const Form = this.getContent().getElement('form');

            QUIAjax.post('package_quiqqer_html-snippets_ajax_backend_addSnippet', (snippetName) => {
                this.fireEvent('submit', [this, snippetName]);
                this.close();
            }, {
                'package': 'quiqqer/html-snippets',
                projectName: Project.getName(),
                snippetName: Form.elements.name.value,
                eventName: Form.elements.eventName.value,
                snippet: Form.elements.snippet.value,
                onError: () => {
                    this.Loader.hide();
                }
            });
        }
    });
});
