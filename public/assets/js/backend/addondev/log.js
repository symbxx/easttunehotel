define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        _queryString: '',
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'addondev/log/index' + Controller._queryString,
                    del_url: 'addondev/log/del',
                    multi_url: 'addondev/log/multi',
                    import_url: 'addondev/log/import',
                    table: 'addondev_log',
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
                        { checkbox: true },
                        { field: 'id', title: __('Id') },
                        { field: 'createtime', title: __('Createtime'), operate: 'RANGE', addclass: 'datetimerange', autocomplete: false, formatter: Table.api.formatter.datetime },
                        { field: 'gen.name', title: __('Gen.name'), operate: 'LIKE' },
                        { field: 'gen.mtable', title: __('Gen.mtable'), operate: 'LIKE' },
                        { field: 'filename', title: __('Filename'), operate: 'LIKE' },
                        { field: 'filetype', title: __('Filetype'), searchList: { "php": __('Filetype php'), "js": __('Filetype js'), "html": __('Filetype html') , "other": __('Filetype other') }, formatter: Table.api.formatter.normal },
                        {
                            field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate,
                            buttons: [
                                {
                                    name: 'code',
                                    text: __('预览'),
                                    title: __('预览'),
                                    classname: 'btn btn-xs btn-primary btn-dialog',
                                    icon: 'fa fa-code',
                                    url: function (table, row, j) {
                                        var url = 'addondev/log/code?ids=' + table.id;
                                        return Fast.api.fixurl(url);
                                    }
                                },
                                {
                                    name: 'diff',
                                    text: __('对比本地'),
                                    title: __('对比本地'),
                                    classname: 'btn btn-xs btn-success btn-dialog',
                                    icon: 'fa fa-code-fork',
                                    url: function (table, row, j) {
                                        var url = 'addondev/log/diff?ids=' + table.id;
                                        return Fast.api.fixurl(url);
                                    }
                                },
                                {
                                    name: 'recover',
                                    text: __('恢复'),
                                    title: __('恢复'),
                                    classname: 'btn btn-xs btn-danger btn-ajax',
                                    icon: 'fa fa-reply',
                                    url: function (table, row, j) {
                                        var url = 'addondev/log/recover?ids=' + table.id;
                                        return Fast.api.fixurl(url);
                                    },
                                    confirm: "确定覆盖当前文件?",
                                    success: function (data, ret) {
                                        table.bootstrapTable("refresh");
                                    },
                                    error: function (data, ret) {
                                        Layer.alert(ret.msg);
                                    }
                                },

                            ],
                            formatter: Table.api.formatter.operate
                        }
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
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