/**
 * 初始化部门详情对话框
 */
var activityInfoDlg = {
    activityInfoData : {},
    zTreeInstance : null,
    validateFields: {
        name: {
            validators: {
                notEmpty: {
                    message: '活动名称名称不能为空'
                }
            }
        }
        
    }
};

/**
 * 清除数据
 */
activityInfoDlg.clearData = function() {
    this.activityInfoData = {};
}

/**
 * 设置对话框中的数据
 *
 * @param key 数据的名称
 * @param val 数据的具体值
 */
activityInfoDlg.set = function(key, val) {
    this.activityInfoData[key] = (typeof value == "undefined") ? $("#" + key).val() : value;
    return this;
}

/**
 * 设置对话框中的数据
 *
 * @param key 数据的名称
 * @param val 数据的具体值
 */
activityInfoDlg.get = function(key) {
    return $("#" + key).val();
}

/**
 * 关闭此对话框
 */
activityInfoDlg.close = function() {
    var index = parent.layer.getFrameIndex(window.name);
//	var index = window.parent.CodeGoods.layerIndex;
    parent.layer.close(index);
}

/**
 * 点击部门ztree列表的选项时
 *
 * @param e
 * @param treeId
 * @param treeNode
 * @returns
 */
activityInfoDlg.onClickactivity = function(e, treeId, treeNode) {
    $("#pName").attr("value", activityInfoDlg.zTreeInstance.getSelectedVal());
    $("#pid").attr("value", treeNode.id);
}

/**
 * 显示部门选择的树
 *
 * @returns
 */
activityInfoDlg.showactivitySelectTree = function() {
    var pName = $("#pName");
    var pNameOffset = $("#pName").offset();
    $("#parentactivityMenu").css({
        left : pNameOffset.left + "px",
        top : pNameOffset.top + pName.outerHeight() + "px"
    }).slideDown("fast");

    $("body").bind("mousedown", onBodyDown);
}

/**
 * 隐藏部门选择的树
 */
activityInfoDlg.hideactivitySelectTree = function() {
    $("#parentactivityMenu").fadeOut("fast");
    $("body").unbind("mousedown", onBodyDown);// mousedown当鼠标按下就可以触发，不用弹起
}

/**
 * 收集数据
 */
activityInfoDlg.collectData = function() {
    this.set('id').set('name').set('note').set('startTime').set('endTime').set('coupon_type_id').set('custName').set('custNote').set('laterTime');
}

/**
 * 验证数据是否为空
 */
activityInfoDlg.validate = function () {
    $('#activityInfoForm').data("bootstrapValidator").resetForm();
    $('#activityInfoForm').bootstrapValidator('validate');
    return $("#activityInfoForm").data('bootstrapValidator').isValid();
}

/**
 * 提交添加部门
 */
activityInfoDlg.addSubmit = function() {

    this.clearData();
    this.collectData();

    if (!this.validate()) {
        return;
    }

    //提交信息
    var ajax = new $ax(Feng.ctxPath + "/admin/activity/add/type/2", function(data){
        if (1 === parseInt(data.code)) {
			Feng.success("添加成功！" );
			window.parent.OptLog.table.refresh();
	        activityInfoDlg.close();
		} else {
			Feng.error("添加失败！" + data.msg + "！");
		}
    },function(data){
        Feng.error("添加失败!" + data.responseJSON.message + "!");
    });
    ajax.set(this.activityInfoData);
    ajax.start();
}

activityInfoDlg.addDraft = function() {

    this.clearData();
    this.collectData();

    if (!this.validate()) {
        return;
    }

    //提交信息
    var ajax = new $ax(Feng.ctxPath + "/admin/activity/add/type/1", function(data){
        if (1 === parseInt(data.code)) {
			Feng.success("添加成功！" );
			window.parent.OptLog.table.refresh();
	        activityInfoDlg.close();
		} else {
			Feng.error("添加失败！" + data.msg + "！");
		}
    },function(data){
        Feng.error("添加失败!" + data.responseJSON.message + "!");
    });
    ajax.set(this.activityInfoData);
    ajax.start();
}

/**
 * 提交修改
 */
activityInfoDlg.editSubmit = function() {

    this.clearData();
    this.collectData();

    if (!this.validate()) {
        return;
    }

    //提交信息
     var ajax = new $ax(Feng.ctxPath + "/admin/activity/edit/type/2", function (data) {
    	if (1 === parseInt(data.code)) {
			Feng.success("修改成功！" );
			window.parent.OptLog.table.refresh();
	        activityInfoDlg.close();
		} else {
			Feng.error("修改失败！" + data.msg + "！");
		}
        
    }, function (data) {
        Feng.error("添加失败!" + data.responseJSON.message + "!");
    });

    ajax.set(this.activityInfoData);
    ajax.start();
}

/**
 * 提交修改
 */
activityInfoDlg.editDraft = function() {

    this.clearData();
    this.collectData();

    if (!this.validate()) {
        return;
    }

    //提交信息
     var ajax = new $ax(Feng.ctxPath + "/admin/activity/edit/type/1", function (data) {
    	if (1 === parseInt(data.code)) {
			Feng.success("修改成功！" );
			window.parent.OptLog.table.refresh();
	        activityInfoDlg.close();
		} else {
			Feng.error("修改失败！" + data.msg + "！");
		}
        
    }, function (data) {
        Feng.error("添加失败!" + data.responseJSON.message + "!");
    });

    ajax.set(this.activityInfoData);
    ajax.start();
}


activityInfoDlg.laterSubmit = function() {
    this.clearData();
    this.collectData();

    if (!this.validate()) {
        return;
    }

    //提交信息
    var ajax = new $ax(Feng.ctxPath + "/admin/activity/laterActivity", function(data){
        if (1 === parseInt(data.code)) {
			Feng.success("设置成功！" );
			window.parent.OptLog.table.refresh();
	        activityInfoDlg.close();
		} else {
			Feng.error("设置失败！" + data.msg + "！");
		}
    },function(data){
        Feng.error("添加失败!" + data.responseJSON.message + "!");
    });
    ajax.set(this.activityInfoData);
    ajax.start();
}

function onBodyDown(event) {
    if (!(event.target.id == "menuBtn" || event.target.id == "parentactivityMenu" || $(
            event.target).parents("#parentactivityMenu").length > 0)) {
        activityInfoDlg.hideactivitySelectTree();
    }
}

$(function() {
    Feng.initValidator("activityInfoForm", activityInfoDlg.validateFields);
    
});
