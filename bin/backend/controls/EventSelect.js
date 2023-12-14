define('package/quiqqer/html-snippets/bin/backend/controls/EventSelect', [

    'qui/QUI',
    'qui/controls/Control',
    'qui/controls/contextmenu/Menu',
    'qui/controls/contextmenu/Item',
    'Locale',

    'css!package/quiqqer/html-snippets/bin/backend/controls/EventSelect.css'

], function(QUI, QUIControl, QUIMenu, QUIMenuItem, QUILocale) {
    'use strict';

    const lg = 'quiqqer/html-snippets';

    return new Class({

        Extends: QUIControl,
        Type: 'package/quiqqer/html-snippets/bin/backend/controls/EventSelect',

        Binds: [
            '$openMenu',
            '$menuItemMouseDown',
            '$keyup'
        ],

        initialize: function(options) {
            this.parent(options);

            this.$Menu = null;

            this.addEvents({
                onImport: this.$onImport
            });
        },

        $onImport: function() {
            this.$Input = this.$Elm;
            this.$Input.autocomplete = 'off';
            this.$Input.addEvents({
                keyup: this.$keyup,
                blur: () => {
                    if (this.$Menu) {
                        this.$Menu.hide();
                    }
                }
            });

            this.$Elm = new Element('div', {
                'class': 'field-container-field html-snippet-eventSelect'
            }).wraps(this.$Input);

            new Element('span', {
                'class': 'html-snippet-eventSelect-drop fa fa-chevron-down',
                events: {
                    click: this.$openMenu
                }
            }).inject(this.getElm());


            this.$Menu = new QUIMenu({
                showIcons: false,
                styles: {
                    width: this.$Elm.getSize().x
                },
                events: {
                    onMouseDown: function() {

                    }
                }
            }).inject(this.$Elm);

            this.$Menu.appendChild(
                new QUIMenuItem({
                    text: QUILocale.get(lg, 'html.snippet.select.template.event.headerBegin'),
                    value: 'onQuiqqer::template::header::begin',
                    events: {
                        onMouseDown: this.$menuItemMouseDown
                    }
                })
            );

            this.$Menu.appendChild(
                new QUIMenuItem({
                    text: QUILocale.get(lg, 'html.snippet.select.template.event.headerEnd'),
                    value: 'onQuiqqer::template::header::end',
                    events: {
                        onMouseDown: this.$menuItemMouseDown
                    }
                })
            );

            this.$Menu.appendChild(
                new QUIMenuItem({
                    text: QUILocale.get(lg, 'html.snippet.select.template.event.bodyBegin'),
                    value: 'onQuiqqer::template::body::begin',
                    events: {
                        onMouseDown: this.$menuItemMouseDown
                    }
                })
            );

            this.$Menu.appendChild(
                new QUIMenuItem({
                    text: QUILocale.get(lg, 'html.snippet.select.template.event.bodyEnd'),
                    value: 'onQuiqqer::template::body::end',
                    events: {
                        onMouseDown: this.$menuItemMouseDown
                    }
                })
            );
        },

        $openMenu: function() {
            this.$Menu.setPosition(-10, 50);
            this.$Menu.show();
        },

        $keyup: function(e) {
            if (e.key === 'down' && this.$Menu.isHidden()) {
                this.$openMenu();
                return;
            }

            if (e.key === 'down') {
                this.$Menu.down();
                return;
            }

            if (e.key === 'up' && !this.$Menu.isHidden()) {
                this.$Menu.up();
                return;
            }

            if (e.key === 'enter' && !this.$Menu.isHidden()) {
                this.$Input.value = this.$Menu.getActive().getAttribute('value');
                this.$Menu.hide();
            }
        },

        $menuItemMouseDown: function(Instance) {
            this.$Input.value = Instance.getAttribute('value');
        }
    });
});