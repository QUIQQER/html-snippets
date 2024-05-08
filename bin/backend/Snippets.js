/**
 * Snippet Verwaltungs Control
 * - Wird in einem Projekt angezeigt
 *
 * @module package/quiqqer/html-snippets/bin/backend/Snippets
 * @author www.pcsg.de (Henning Leutz)
 */
define('package/quiqqer/html-snippets/bin/backend/Snippets', [

    'qui/QUI',
    'qui/controls/Control',
    'qui/controls/loader/Loader',
    'package/quiqqer/html-snippets/bin/backend/Utils',
    'qui/controls/buttons/Switch',
    'controls/grid/Grid',
    'Ajax',
    'Locale'

], function(QUI, QUIControl, QUILoader, SnippetUtils, QUISwitch, Grid, QUIAjax, QUILocale) {
    'use strict';

    const lg = 'quiqqer/html-snippets';

    return new Class({

        Extends: QUIControl,
        Type: 'package/quiqqer/html-snippets/bin/backend/Snippets',

        Binds: [
            'add',
            'edit',
            'remove',
            'refresh',
            '$onImport',
            '$onStatusChange'
        ],

        initialize: function(options) {
            this.parent(options);

            this.Loader = new QUILoader();
            this.$loaded = false;

            this.$Project = null;
            this.$Grid = null;

            this.addEvents({
                onImport: this.$onImport
            });
        },

        setProject: function(Project) {
            this.$Project = Project;

            if (this.$Grid) {
                this.$Grid.enable();
                this.$Grid.refresh();
            }
        },

        $onImport: function() {
            const Label = this.getElm().getParent('label');
            Label.getElements('.field-container-item').destroy();

            const Container = new Element('div', {
                styles: {
                    width: '100%'
                }
            }).inject(Label);

            SnippetUtils.isGDPRInstalled().then((isInstalled) => {

                const columnModel = [
                    {
                        header: QUILocale.get(lg, 'grid.status'),
                        dataIndex: 'status',
                        dataType: 'QUI',
                        width: 80
                    }, {
                        header: QUILocale.get(lg, 'grid.name'),
                        dataIndex: 'name',
                        dataType: 'string',
                        width: 200
                    }, {
                        header: QUILocale.get(lg, 'grid.event'),
                        dataIndex: 'event',
                        dataType: 'string',
                        width: 400
                    }
                ];

                if (isInstalled) {
                    columnModel.push({
                        header: QUILocale.get(lg, 'grid.gdpr'),
                        dataIndex: 'gdpr',
                        dataType: 'string',
                        width: 200
                    });
                }

                this.$Grid = new Grid(Container, {
                    height: Label.getParent('.qui-panel-content').getSize().y - 100,
                    multipleSelection: true,
                    columnModel: columnModel,
                    buttons: [
                        {
                            name: 'add',
                            text: QUILocale.get('quiqqer/core', 'add'),
                            events: {
                                click: this.add
                            }
                        }, {
                            name: 'remove',
                            icon: 'fa fa-trash',
                            title: QUILocale.get('quiqqer/core', 'remove'),
                            disabled: true,
                            styles: {
                                'float': 'right'
                            },
                            events: {
                                click: this.remove
                            }
                        }
                    ]
                });

                this.$Grid.disable();
                this.$Grid.addEvents({
                    refresh: this.refresh,
                    onDblClick: this.edit,
                    click: () => {
                        const selected = this.$Grid.getSelectedData();

                        if (selected.length) {
                            this.$Grid.getButton('remove').enable();
                        }
                    }

                });

                if (this.$Project) {
                    this.$Grid.enable();
                    this.$Grid.refresh();
                }
            });
        },

        refresh: function() {
            if (!this.$Project) {
                return;
            }

            this.Loader.show();

            QUIAjax.get('package_quiqqer_html-snippets_ajax_backend_getSnippets', (result) => {
                for (let i = 0, len = result.length; i < len; i++) {
                    result[i].status = new QUISwitch({
                        status: parseInt(result[i].active),
                        events: {
                            change: this.$onStatusChange
                        }
                    });
                }

                this.$Grid.setData({
                    data: result
                });

                this.$Grid.getButton('remove').disable();
                this.Loader.hide();
            }, {
                'package': 'quiqqer/html-snippets',
                projectName: this.$Project.getName()
            });
        },

        add: function() {
            if (!this.$Project) {
                return;
            }

            require([
                'package/quiqqer/html-snippets/bin/backend/controls/windows/AddSnippet'
            ], (AddSnippet) => {
                new AddSnippet({
                    Project: this.$Project,
                    events: {
                        onClose: () => {
                            this.refresh();
                        }
                    }
                }).open();
            });
        },

        edit: function() {
            if (!this.$Project) {
                return;
            }

            require([
                'package/quiqqer/html-snippets/bin/backend/controls/windows/EditSnippet'
            ], (EditSnippet) => {
                new EditSnippet({
                    Project: this.$Project,
                    name: this.$Grid.getSelectedData()[0].name,
                    events: {
                        onClose: () => {
                            this.refresh();
                        }
                    }
                }).open();
            });
        },

        remove: function() {
            const selected = this.$Grid.getSelectedData();

            if (!selected.length) {
                return;
            }

            const snippetNames = selected.map((entry) => {
                return entry.name;
            });

            require(['qui/controls/windows/Confirm'], (QUIConfirm) => {
                const listElements = (n) => {
                    return '<li>' + n + '</li>';
                };

                new QUIConfirm({
                    icon: 'fa fa-trash',
                    texticon: 'fa fa-trash',
                    title: QUILocale.get(lg, 'window.remove.title'),
                    information: QUILocale.get(lg, 'window.remove.information', {
                        snippetList: '<ul>' + snippetNames.map(listElements).join('') + '</ul>'
                    }),
                    text: QUILocale.get(lg, 'window.remove.text', {
                        snippetList: snippetNames.join(', ')
                    }),
                    autoclose: false,
                    maxWidth: 600,
                    maxHeight: 400,
                    events: {
                        onSubmit: (Win) => {
                            Win.Loader.show();

                            QUIAjax.post('package_quiqqer_html-snippets_ajax_backend_removeSnippets', () => {
                                Win.close();
                                this.refresh();
                            }, {
                                'package': 'quiqqer/html-snippets',
                                projectName: this.$Project.getName(),
                                snippetNames: JSON.encode(snippetNames),
                                onError: () => {
                                    Win.Loader.hide();
                                    this.refresh();
                                }
                            });
                        }
                    }
                }).open();
            });
        },

        $onStatusChange: function(Switch) {
            const status = Switch.getStatus();
            const Row = Switch.getElm().getParent('.tr');
            const rowData = this.$Grid.getDataByRow(Row.get('data-row'));
            const snippetName = rowData.name;
            const project = rowData.project;

            this.Loader.show();

            if (status) {
                SnippetUtils.activateSnippet(snippetName, project).then(() => {
                    this.refresh();
                }).catch(() => {
                    this.refresh();
                });
            } else {
                SnippetUtils.deactivateSnippet(snippetName, project).then(() => {
                    this.refresh();
                }).catch(() => {
                    this.refresh();
                });
            }
        }
    });
});
