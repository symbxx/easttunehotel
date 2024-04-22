require.config({
	paths: {
		'addondev-highlight': '../addons/addondev/highlight/highlight.min'
	},
	shim: {
		'addondev-highlight': {
			deps: ['css!../addons/addondev/highlight/styles/vs.min.css']
		}
	}
});
require(['jquery', 'addondev-highlight'], function ($, undefined) {
	$('[data-toggle="addondev-select-file"]').on('click', "input[type=checkbox]", function (e) {
		var $container = $(e.delegateTarget);
		var that = $(this);
		var id = that.data("id");
		var checkboxList = $container.find('input[data-id="file"]');
		if (id == 'all') {
			var status = that.prop("checked");
			if (status) {
				checkboxList.prop("checked", true)
			} else {
				checkboxList.prop("checked", false)
			}
		} else if (id == 'file') {
			var checkedBoxList = $container.find('input[data-id="file"]:checked');
			var checkall = $container.find('input[data-id="all"]');
			if (checkboxList.length == checkedBoxList.length) {
				checkall.prop('checked', true)
			} else {
				checkall.prop('checked', false)
			}
		}

	});

	$(document).on('click', "[data-toggle='addondev-code-view']", function () {
		var that = this;
		var action = $(that).data("action") ? $(that).data("action") : "";
		var file_id = $(that).data("file-id") ? $(that).data("file-id") : "";
		var ids = $(that).data("ids") ? $(that).data("ids") : "";
		var url = "addondev/gen/" + action + "?ids=" + ids + "&file_id=" + file_id;
		parent.Fast.api.open(url, action == 'code' ? '代码预览' : '文件对比');
	});
	hljs.highlightAll();
});
