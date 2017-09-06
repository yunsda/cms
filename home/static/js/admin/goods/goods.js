/**
 * 日志管理初始化
 */
var OptLog = {
    id: "OptLogTable",	//表格id
    seItem: null,		//选中的条目
    table: null,
    layerIndex: -1
};

/**
 * 初始化表格的列
 */
OptLog.initColumn = function () {
    return [
        {field: 'selectItem', radio: true},
        {title: 'id', field: 'id', visible: false, align: 'center', valign: 'middle'},
		{title: '操作者', field: 'username', align: 'center', valign: 'middle', sortable: true},
		{title: '节点', field: 'node', align: 'center', valign: 'middle', sortable: true},
		{title: '行为', field: 'action', align: 'center', valign: 'middle', sortable: true},
		{title: '操作内容', field: 'content', align: 'center', valign: 'middle', sortable: true},
		{title: '操作位置', field: 'isp', align: 'center', valign: 'middle', sortable: true},
		{title: '操作时间', field: 'create_at', align: 'center', valign: 'middle', sortable: true}
		];
};

/**
 * 检查是否选中
 */
OptLog.check = function () {
    var selected = $('#' + this.id).bootstrapTable('getSelections');
    if(selected.length == 0){
        Feng.info("请先选中表格中的某一记录！");
        return false;
    }else{
        OptLog.seItem = selected[0];
        return true;
    }
};

/**
 * 查看日志详情
 */
OptLog.detail = function () {
    if (this.check()) {
        var index = layer.open({
            type: 2,
            title: '部门详情',
            area: ['800px', '420px'], //宽高
            fix: false, //不固定
            maxmin: true,
            content: Feng.ctxPath + '/admin/index/main/?' + this.seItem.id
        });
        this.layerIndex = index;
    }
};


/**
 * 清空日志
 */
OptLog.delLog = function () {
    Feng.confirm("是否清空所有日志?",function(){
        var ajax = Feng.baseAjax("/log/delLog","清空日志");
        ajax.start();
        OptLog.table.refresh();
    });
}

/**
 * 查询表单提交参数对象
 * @returns {{}}
 */
OptLog.formParams = function() {
    var queryData = {};

    queryData['username'] = $("#username").val();
    queryData['beginTime'] = $("#beginTime").val();
    queryData['endTime'] = $("#endTime").val();

    return queryData;
}

/**
 * 查询日志列表
 */
OptLog.search = function () {

    OptLog.table.refresh({query: OptLog.formParams()});
};

$(function () {
    var defaultColunms = OptLog.initColumn();
    var table = new BSTable(OptLog.id, "/admin/log/listData", defaultColunms);
    table.setPaginationType("server");
    table.setQueryParams(OptLog.formParams());
    OptLog.table = table.init();
});
