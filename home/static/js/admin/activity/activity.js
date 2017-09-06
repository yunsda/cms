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
        {title: '活动编号', field: 'id', align: 'center', valign: 'middle'},
		{title: '活动名称', field: 'name', align: 'center', valign: 'middle', sortable: true},
		{title: '客户名称', field: 'custName', align: 'center', valign: 'middle', sortable: true},
		{title: '劵类型', field: 'coupon_type', align: 'center', valign: 'middle', sortable: true},
		{title: '开始时间', field: 'startTime', align: 'center', valign: 'middle', sortable: true},
		{title: '结束时间', field: 'endTime', align: 'center', valign: 'middle', sortable: true},
		{title: '活动状态', field: 'status', align: 'center', valign: 'middle', sortable: true, formatter: 'OptLog.statusFormatter'},
		{title: '创建时间', field: 'createTime', align: 'center', valign: 'middle', sortable: true},		
		];
};

//格式化状态
OptLog.statusFormatter = function(value, row, index) {
	switch (value) {
	case '待审核':
		return '<span class="label label-warning">' + value + '</span>';
		break;
	case '正常':
		return '<span class="label label-primary">' + value + '</span>';
		break;
	default:
		return '<span class="label label-success">' + value + '</span>';
		break;
	}
}

OptLog.openAddMgr = function () {
    var index = layer.open({
        type: 2,
        title: '创建活动',
        area: ['800px', '560px'], //瀹介珮
        fix: false, //涓嶅浐瀹�
        maxmin: true,
        content: Feng.ctxPath + '/admin/activity/add'
    });
    this.layerIndex = index;
};


/**
 * 创建商品
 */
OptLog.add = function () {
  if (this.check()) {
	var index = layer.open({
        type: 2,
        title: '添加商品',
        area: ['800px', '520px'], //宽高
        fix: false, //不固定
        maxmin: true,
        content: Feng.ctxPath + '/admin/goods/add/?activity_id=' + this.seItem.id
    });
    this.layerIndex = index;
  }
};

/**
 * 活动延期
 */
OptLog.laterActivity = function () {
  if (this.check()) {
	var index = layer.open({
        type: 2,
        title: '活动延期',
        area: ['800px', '350px'], //宽高
        fix: false, //不固定
        maxmin: true,
        content: Feng.ctxPath + '/admin/Activity/laterActivity/?activity_id=' + this.seItem.id
    });
    this.layerIndex = index;
  }
};

/**
 * 卡券延期
 */
OptLog.delay = function () {
  if (this.check()) {
	var index = layer.open({
        type: 2,
        title: '卡券延期',
        area: ['800px', '350px'], //宽高
        fix: false, //不固定
        maxmin: true,
        content: Feng.ctxPath + '/admin/Coupon/couponDelay/?activity_id=' + this.seItem.id
    });
    this.layerIndex = index;
  }
};

/**
 * 创建活动
 */
OptLog.addActivity = function () {

	var index = layer.open({
        type: 2,
        title: '创建活动',
        area: ['800px', '520px'], //宽高
        fix: false, //不固定
        maxmin: true,
        content: Feng.ctxPath + '/admin/Activity/add'
    });
    this.layerIndex = index;

};

/**
 * 创建商品
 */
OptLog.editActivity = function () {
  if (this.check()) {
	var index = layer.open({
        type: 2,
        title: '编辑活动',
        area: ['800px', '520px'], //宽高
        fix: false, //不固定
        maxmin: true,
        content: Feng.ctxPath + '/admin/Activity/edit/?activity_id=' + this.seItem.id
    });
    this.layerIndex = index;
  }
};

/**
	修改活动状态
	**/
	OptLog.setStatus = function(flag) {
		if (this.check()) {
		    var tip = flag ? '启用' : '停用';
		    var activityId = this.seItem.id;
		    var operation = function() {			 
				var ajax = new $ax(Feng.ctxPath + "/admin/Activity/setStatus", function(data){
					if (1 === parseInt(data.code)) {
						Feng.success("操作成功！" );
						OptLog.table.refresh();
					} else {
						Feng.error("操作失败！" + data.msg + "！");
					}
				},function(data){
					Feng.error("添加失败!" + data.responseJSON.message + "!");
				});
				ajax.set('activityId',activityId);
				ajax.set('status',flag);
				ajax.start();
			};
			Feng.confirm("是否"+tip+"该活动 ？", operation);
			
		}
	};


OptLog.openChangeUser = function () {
    if (this.check()) {
        var index = layer.open({
            type: 2,
            title: '修改活动',
            area: ['800px', '450px'], //瀹介珮
            fix: false, //涓嶅浐瀹�
            maxmin: true,
            content: Feng.ctxPath + '/admin/activity/update/idx/' + this.seItem.id
        });
        this.layerIndex = index;
    }
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
            title: '终端详情',
            area: ['800px', '520px'], //宽高
            fix: false, //不固定
            maxmin: true,
            content: Feng.ctxPath + '/admin/Activity/getLastSaleDetail/idx/' + this.seItem.id
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

    queryData['activityId'] = $("#activityId").val();
	queryData['activityName'] = $("#activityName").val();
    queryData['name'] = $("#name").val();
	queryData['couponType'] = $("#couponType").val();
    queryData['status'] = $("#status").val();
	queryData['organId'] = $("#organId").val();
	queryData['custName'] = $("#custName").val();
	queryData['custNote'] = $("#custNote").val();
	queryData['type'] = 'search';

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
    var table = new BSTable(OptLog.id, "/admin/Activity/listData", defaultColunms);
    table.setPaginationType("server");
    table.setQueryParams(OptLog.formParams());
    OptLog.table = table.init();
});
