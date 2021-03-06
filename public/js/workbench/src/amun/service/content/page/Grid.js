
Ext.define('Amun.service.content.page.Grid', {
    extend: 'Ext.panel.Panel',

    tree: null,
    grid: null,

    initComponent: function(){
        var me = this;

        var config = {
            layout: 'border',
            border: false,
            items: [this.buildTree(), this.buildGrid()]
        };
        Ext.apply(me, config);

        me.callParent();
    },

    reload: function(){
        // grid
        this.grid.reload();
    },

    buildTree: function(){

        Ext.Ajax.request({
            url: url + 'api/page/tree?format=json',
            scope: this,
            success: function(response, opts){
                var tree = this.buildRecTree(JSON.parse(response.responseText));

                this.tree.setRootNode(tree);
                this.tree.getRootNode().expand();
            }
        });

        this.tree = Ext.create('Ext.tree.Panel', {
            region: 'west',
            margins: '0 5 0 0',
            header: false,
            border: false,
            width: 200,
            viewConfig: {
                plugins: {
                    ptype: 'treeviewdragdrop',
                    containerScroll: true
                },
                listeners: {
                    beforedrop: function(node, data, overModel, dropPosition) {
                        return data.records.length == 1 && dropPosition != 'append';
                    }
                }
            },
            hideHeaders: true,
            useArrows: true,
            rootVisible: true
        });

        this.tree.on('itemmove', function(el, oldParent, newParent, index, eOpts){
            var n = this.getStore().getNodeById(newParent.get('id'));
            if (n) {
                var data = [];
                var i = 0;
                n.eachChild(function(el){
                    data.push({
                        id: el.get('id'),
                        sort: i
                    });
                    i++;
                });
                if (data.length > 0) {
                    var params = {
                        entry: data
                    };
                    // save sort
                    var uri = Amun.xrds.Manager.findServiceUri('http://ns.amun-project.org/2011/amun/service/content/page');
                    Ext.Ajax.request({
                        url: uri + '/tree?format=json',
                        method: 'POST',
                        headers: {
                            'X-HTTP-Method-Override': 'PUT',
                            'Accept': 'application/json'
                        },
                        jsonData: params,
                        scope: this,
                        success: function(response, opts) {
                            try {
                                var result = Ext.JSON.decode(response.responseText);
                                if (result.success == true) {
                                    // successful
                                    return;
                                }
                            } catch(e) {
                            }
                            this.getStore().load();
                        },
                        failure: function(response, opts) {
                            this.getStore().load();
                        }
                    });
                }
            }
        });

        /*
        this.tree.on('celldblclick', function(el, td, index, rec){
            var serviceType = rec.raw.type;
            var service = Amun.xrds.Manager.findService(serviceType);
            if (service) {
                var uri = service.getUri() + '/form?method=update&id=' + rec.get('id');
                this.grid.loadForm(uri, serviceType);
            }
        }, this);
        */

        this.tree.on('select', function(el, rec){
            var rec = this.grid.getStore().getById(rec.get('id'));
            this.grid.getSelectionModel().select([rec]);
        }, this);

        return this.tree;
    },

    buildRecTree: function(result){
        var children = [];
        if (result.children && result.children.length > 0) {
            for (var i = 0; i < result.children.length; i++) {
                children.push(this.buildRecTree(result.children[i]));
            }
        }

        var node = {
            text: result.title,
            id: result.id,
            cls: result.status == 0 ? 'wb-tree-page-normal' : 'wb-tree-page-hidden',
            leaf: true,
            children: null
        };

        if (children.length > 0) {
            node.leaf = false;
            node.children = children;
        }

        return node;
    },

    buildGrid: function(){
        this.grid = Ext.create('Amun.Grid', {
            border: false,
            region: 'center',
            service: this.service,
            result: this.result,
            columnConfig: {
                id: 80,
                path: 200,
                title: 100,
                template: 100,
                serviceName: 100,
                date: 120,
                serviceType: 100
            }
        });

        this.grid.on('reload', function(){
            this.tree.getStore().load();
        }, this);

        this.grid.on('celldblclick', function(el){
            var rec = this.grid.getSelectionModel().getSelection()[0];
            var service = Amun.xrds.Manager.findService(rec.raw.serviceType);
            if (service) {
                var editor = null;
                var editorName = 'Amun.' + service.getNamespace() + '.Editor';
                if (Ext.ClassManager.get(editorName) != null) {
                    editor = Ext.create(editorName, {
                        service: service,
                        page: rec.raw
                    });
                }

                // as fallback use the default form
                if (editor == null) {
                    editor = Ext.create('Amun.Editor', {
                        service: service,
                        page: rec.raw
                    });
                }
                editor.show();
            }
        }, this);

        return this.grid;
    }

});

