define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        _queryString: '',
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'addondev/gen/index' + Controller._queryString,
                    add_url: 'addondev/gen/add' + Controller._queryString,
                    edit_url: 'addondev/gen/edit',
                    del_url: 'addondev/gen/del',
                    multi_url: 'addondev/gen/multi',
                    import_url: 'addondev/gen/import',
                    table: 'addondev_gen',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                fixedColumns: true,
                fixedRightNumber: 1,
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id')},
                        {field: 'name', title: __('Name'), operate: 'LIKE'},
                        {field: 'addon', title: __('Addon'), operate: 'LIKE'},
                        {field: 'mtable', title: __('Mtable'), operate: 'LIKE'},
                        {field: 'controller', title: __('Controller'), operate: 'LIKE'},
                        {field: 'relation', title: __('Relation')},
                        {field: 'relationmodel', title: __('Relationmodel')},
                        {field: 'selectpagefield', title: __('Selectpagefield')},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, 
                        buttons: [
                            {
                                name: 'history',
                                text: __('日志'),
                                title: __('代码被覆盖的日志'),
                                extend: 'data-toggle="tooltip"',
                                classname: 'btn btn-xs btn-success btn-dialog',
                                icon: 'fa fa-history',
                                url: function(table,row,j){
                                    var url = 'addondev/log/index?gen_id=' + table.id;
                                    return Fast.api.fixurl(url);
                                }
                            },
                            {
                                name: 'copy',
                                text: __('复制'),
                                title: __('复制当前的模板准备新建'),
                                extend: 'data-toggle="tooltip"',
                                classname: 'btn btn-xs btn-info btn-dialog',
                                icon: 'fa fa-copy',
                                url: function(table,row,j){
                                    var url = 'addondev/gen/add?id=' + table.id;
                                    return Fast.api.fixurl(url);
                                }
                            }

                        ],
                        formatter: Table.api.formatter.operate}
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        add: function () {
            Controller.api.bindevent();
        },
        edit: function () {
            Controller.api.bindevent();
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            },
            queryString: function () {
                return location.search.replace("dialog=1", "").split('&').filter(function (item) {
                    return !!item;
                }).join("&");
            }
        }
    };
    Controller._queryString = Controller.api.queryString();
    return Controller;
});
