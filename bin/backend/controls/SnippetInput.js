/**
 * @module package/quiqqer/html-snippets/bin/backend/controls/SnippetInput
 * @author www.pcsg.de (Henning Leutz)
 */
define('package/quiqqer/html-snippets/bin/backend/controls/SnippetInput', [

    'qui/QUI',
    'qui/controls/Control',
    'qui/controls/loader/Loader',
    'Ajax',

    'css!package/quiqqer/html-snippets/bin/backend/controls/SnippetInput.css'

], function(QUI, QUIControl, QUILoader, QUIAjax) {
    'use strict';

    return new Class({

        Extends: QUIControl,
        Type: 'package/quiqqer/html-snippets/bin/backend/controls/SnippetInput',

        Binds: [
            '$onImport',
            '$load'
        ],

        initialize: function(options) {
            this.parent(options);

            this.Loader = new QUILoader();
            this.$loaded = false;
            this.$Project = null;

            this.addEvents({
                onImport: this.$onImport
            });
        },

        setProject: function(Project) {
            this.$Project = Project;
            this.$load();
        },

        $onImport: function() {
            this.$Textarea = this.getElm();

            this.setAttribute('snippetName', this.$Textarea.get('data-qui-options-snippet'));
            this.setAttribute('snippetEvent', this.$Textarea.get('data-qui-options-event'));

            this.$Elm = new Element('div', {
                'class': 'field-container-field quiqqer-html-snippet-input'
            }).wraps(this.$Textarea);

            this.Loader.inject(this.$Elm);
            this.Loader.show();
            this.$load();
        },


        save: function() {
            console.log(111);
        },

        $load: function() {
            if (this.$loaded) {
                return;
            }

            if (!this.$Project) {
                return;
            }

            this.$loaded = true;

            QUIAjax.get('package_quiqqer_html-snippets_ajax_backend_getSnippet', (snippetData) => {
                this.$Textarea.value = snippetData.snippet;
                this.Loader.hide();
            }, {
                'package': 'quiqqer/html-snippets',
                projectName: this.$Project.getName(),
                snippetName: this.getAttribute('snippetName'),
                showError: false,
                onError: (err) => {
                    // create snippet if snippet not exists
                    console.error(err);

                    if (err.getCode() === 404) {
                        this.$loaded = false;

                        QUIAjax.post('package_quiqqer_html-snippets_ajax_backend_addSnippet', () => {
                            this.$load();
                        }, {
                            'package': 'quiqqer/html-snippets',
                            projectName: this.$Project.getName(),
                            snippetName: this.getAttribute('snippetName'),
                            eventName: this.getAttribute('snippetEvent')
                        });
                    }
                }
            });
        }
    });
});
