define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        _queryString: '',
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'addondev/addon/index' + Controller._queryString,
                    add_url: 'addondev/addon/add' + Controller._queryString,
                    edit_url: 'addondev/addon/edit',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                pagination: false,
                fixedColumns: true,
                fixedRightNumber: 1,
                columns: [
                    [
                        { field: 'name', title: __('Name'), operate: 'LIKE' },
                        { field: 'title', title: __('Title'), operate: 'LIKE' },
                        { field: 'version', title: __('version') },
                        {
                            field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate,
                            buttons: [
                                {
                                    name: 'exportmenu',
                                    text: __('菜单'),
                                    title: __('导出插件菜单代码'),
                                    extend: 'data-toggle="tooltip"',
                                    classname: 'btn btn-xs btn-primary btn-dialog',
                                    icon: 'fa fa-external-link',
                                    url: function (table, row, j) {
                                        var url = 'addondev/addon/exportmenu?addon=' + table.name;
                                        return Fast.api.fixurl(url);
                                    }
                                },
                                {
                                    name: 'backend',
                                    text: __('生成后端'),
                                    title: function (table, row) {
                                        return table.title + "代码管理";
                                    },
                                    extend: 'data-toggle="tooltip"',
                                    classname: 'btn btn-xs btn-info btn-addtabs',
                                    icon: 'fa fa-code',
                                    url: function (table, row, j) {
                                        var url = 'addondev/gen/index?addon=' + table.id;
                                        return Fast.api.fixurl(url);
                                    }
                                },
                                {
                                    name: 'backup',
                                    text: __('备份'),
                                    title: __('备份插件代码模板'),
                                    extend: 'data-toggle="tooltip"',
                                    classname: 'btn btn-xs btn-warning btn-ajax',
                                    icon: 'fa fa-save',
                                    confirm: '确认备份?',
                                    url: function (table, row, j) {
                                        var url = 'addondev/addon/backup?addon=' + table.id;
                                        return Fast.api.fixurl(url);
                                    }
                                },
                                {
                                    name: 'recover',
                                    text: __('恢复'),
                                    title: __('恢复插件代码模板'),
                                    extend: 'data-toggle="tooltip"',
                                    classname: 'btn btn-xs btn-danger btn-ajax',
                                    icon: 'fa fa-database',
                                    confirm: '确认恢复?',
                                    url: function (table, row, j) {
                                        var url = 'addondev/addon/recover?addon=' + table.id;
                                        return Fast.api.fixurl(url);
                                    }
                                }

                            ],
                            formatter: Table.api.formatter.operate
                        }
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
        backup: function () {
            Controller.api.bindevent();
        },
        recover: function () {
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
