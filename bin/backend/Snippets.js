/**
 * @module package/quiqqer/html-snippets/bin/backend/Snippets
 * @author www.pcsg.de (Henning Leutz)
 */
define('package/quiqqer/html-snippets/bin/backend/Snippets', [

    'qui/QUI',
    'qui/controls/Control',
    'qui/controls/loader/Loader',
    'controls/grid/Grid',
    'Ajax',
    'Locale'

], function(QUI, QUIControl, QUILoader, Grid, QUIAjax, QUILocale) {
    'use strict';

    const lg = 'quiqqer/html-snippets';

    return new Class({

        Extends: QUIControl,
        Type: 'package/quiqqer/html-snippets/bin/backend/Snippets',

        Binds: [
            '$onImport'
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
        },

        $onImport: function() {
            const Label = this.getElm().getParent('label');
            Label.getElements('.field-container-item').destroy();

            const Container = new Element('div', {
                styles: {
                    width: '100%'
                }
            }).inject(Label);

            this.$Grid = new Grid(Container, {
                height: Label.getParent('.qui-panel-content').getSize().y - 100,
                columnModel: [
                    {
                        header: QUILocale.get(lg, 'grid.name'),
                        dataIndex: 'name',
                        dataType: 'string',
                        width: 200
                    }, {
                        header: QUILocale.get(lg, 'grid.event'),
                        dataIndex: 'range',
                        dataType: 'string',
                        width: 200
                    }
                ]
            });

        }
    });
});
