/**
 * @module package/quiqqer/html-snippets/bin/backend/controls/SnippetInput
 * @author www.pcsg.de (Henning Leutz)
 */
define('package/quiqqer/html-snippets/bin/backend/controls/SnippetInput', [

    'qui/QUI',
    'qui/controls/Control',
    'qui/controls/loader/Loader',
    'qui/controls/buttons/Switch',
    'package/quiqqer/html-snippets/bin/backend/Utils',
    'Ajax',
    'Locale',

    'css!package/quiqqer/html-snippets/bin/backend/controls/SnippetInput.css'

], function(QUI, QUIControl, QUILoader, QUISwitch, SnippetUtils, QUIAjax, QUILocale) {
    'use strict';

    return new Class({

        Extends: QUIControl,
        Type: 'package/quiqqer/html-snippets/bin/backend/controls/SnippetInput',

        Binds: [
            '$onImport',
            '$onChange',
            '$onStatusChange',
            '$load'
        ],

        initialize: function(options) {
            this.parent(options);

            this.Loader = new QUILoader();

            this.$loaded = false;
            this.$data = null;

            this.$Status = null;
            this.$Project = null;
            this.$GDPR = null;

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
            this.$Textarea.placeholder = '<html-snippet-code>code</html-snippet-code>';

            this.setAttribute('snippetName', this.$Textarea.get('data-qui-options-snippet'));
            this.setAttribute('snippetEvent', this.$Textarea.get('data-qui-options-event'));

            const TextAreaLabel = new Element('label').wraps(this.$Textarea);

            new Element('span', {
                html: 'Code'
            }).inject(TextAreaLabel, 'top');

            this.$Elm = new Element('div', {
                'class': 'field-container-field quiqqer-html-snippet-input'
            }).wraps(TextAreaLabel);

            this.Loader.inject(this.$Elm);
            this.Loader.show();

            SnippetUtils.isGDPRInstalled().then((isInstalled) => {
                if (isInstalled) {
                    const GDPRLabel = new Element('label', {
                        html: '<span>GDPR Kategorie</span>'
                    }).inject(this.$Elm, 'top');


                    this.$GDPR = new Element('select', {
                        name: 'gdpr',
                        html: '' +
                            '<option value="">---</option>' +
                            '<option value="essential">' +
                            QUILocale.get('quiqqer/gdpr', 'cookie.category.essential') +
                            '</option>' +
                            '<option value="preferences">' +
                            QUILocale.get('quiqqer/gdpr', 'cookie.category.preferences') +
                            '</option>' +
                            '<option value="statistics">' +
                            QUILocale.get('quiqqer/gdpr', 'cookie.category.statistics') +
                            '</option>' +
                            '<option value="marketing">' +
                            QUILocale.get('quiqqer/gdpr', 'cookie.category.marketing') +
                            '</option>',
                        events: {
                            change: this.$onChange
                        }
                    }).inject(GDPRLabel);

                    if (this.$data) {
                        this.$GDPR.value = this.$data.gdpr;
                    }
                }


                const Title = new Element('div', {
                    'class': 'quiqqer-html-snippet-input-title',
                    html: '<span>HTML-Snippet</span>'
                }).inject(this.$Elm, 'top');


                this.$StatusContainer = new Element('div', {
                    'class': 'quiqqer-html-snippet-input-status'
                }).inject(Title);

                new Element('span', {
                    html: 'HTML-Snippet ist aktiviert',
                    styles: {
                        'float': 'left'
                    }
                }).inject(this.$StatusContainer);

                this.$Status = new QUISwitch({
                    events: {
                        onChange: this.$onStatusChange
                    }
                }).inject(this.$StatusContainer);

                return this.$load();
            }).then(() => {
                this.$Textarea.addEvents({
                    change: this.$onChange,
                    blur: this.$onChange
                });

                if (this.$data && this.$data.active) {
                    this.$Status.setSilentOn();
                }

                if (this.$data && !this.$data.active) {
                    this.$Status.setSilentOff();
                }
            });
        },

        $onChange: function() {
            this.Loader.show();

            QUIAjax.post('package_quiqqer_html-snippets_ajax_backend_saveSnippet', () => {
                this.Loader.hide();
            }, {
                'package': 'quiqqer/html-snippets',
                projectName: this.$Project.getName(),
                snippetName: this.getAttribute('snippetName'),
                eventName: this.getAttribute('snippetEvent'),
                snippet: this.$Textarea.value,
                gdpr: this.$GDPR ? this.$GDPR.value : ''
            });
        },

        $load: function() {
            if (this.$loaded) {
                return Promise.resolve();
            }

            if (this.$Textarea.value !== '') {
                this.$loaded = true;
                this.Loader.hide();

                return Promise.resolve();
            }

            if (!this.$Project) {
                return Promise.resolve();
            }

            this.$loaded = true;

            return new Promise((resolve) => {
                QUIAjax.get('package_quiqqer_html-snippets_ajax_backend_getSnippet', (snippetData) => {
                    this.$Textarea.value = snippetData.snippet;
                    this.$data = snippetData;

                    if (this.$GDPR) {
                        this.$GDPR.value = snippetData.gdpr;
                    }

                    if (this.$Status) {
                        if (this.$data.active) {
                            this.$Status.setSilentOn();
                        } else {
                            this.$Status.setSilentOff();
                        }
                    }

                    this.Loader.hide();
                    resolve();
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
                                this.$load().then(resolve);
                            }, {
                                'package': 'quiqqer/html-snippets',
                                projectName: this.$Project.getName(),
                                snippetName: this.getAttribute('snippetName'),
                                eventName: this.getAttribute('snippetEvent')
                            });

                            return;
                        }

                        resolve();
                    }
                });
            });
        },

        $onStatusChange: function() {
            const status = this.$Status.getStatus();
            const project = this.$Project.getName();
            const snippetName = this.getAttribute('snippetName');

            this.Loader.show();

            if (status) {
                SnippetUtils.activateSnippet(snippetName, project).then(() => {
                    this.Loader.hide();
                }).catch((err) => {
                    QUI.getMessageHandler().then((MH) => {
                        MH.addError(err.getMessage());
                    });
                });
            } else {
                SnippetUtils.deactivateSnippet(snippetName, project).then(() => {
                    this.Loader.hide();
                }).catch((err) => {
                    QUI.getMessageHandler().then((MH) => {
                        MH.addError(err.getMessage());
                    });
                });
            }
        }
    });
});
