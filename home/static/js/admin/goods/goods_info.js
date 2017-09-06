/**
 * 角色详情对话框（可用于添加和修改对话框）
 */
var GoodsInfoDlg = {
    goodsInfoData: {},
    deptZtree: null,
    pNameZtree: null,
    validateFields: {
    	activity_id: {
            validators: {
                notEmpty: {
                    message: '活动编号不能为空'
                }
            }
        },
        name: {
            validators: {
                notEmpty: {
                    message: '商品名称不能为空'
                }
            }
        },
        money: {
            validators: {
                notEmpty: {
                    message: '面额不能为空'
                }
            }
        }
    }
};

/**
 * 清除数据
 */
GoodsInfoDlg.clearData = function () {
    this.goodsInfoData = {};
};

/**
 * 设置对话框中的数据
 *
 * @param key 数据的名称
 * @param val 数据的具体值
 */
GoodsInfoDlg.set = function (key, val) {
    this.goodsInfoData[key] = (typeof value == "undefined") ? $("#" + key).val() : value;
    return this;
};

/**
 * 设置对话框中的数据
 *
 * @param key 数据的名称
 * @param val 数据的具体值
 */
GoodsInfoDlg.get = function (key) {
    return $("#" + key).val();
};

/**
 * 关闭此对话框
 */
GoodsInfoDlg.close = function () {
	var index = parent.layer.getFrameIndex(window.name);
//	var index = window.parent.CodeGoods.layerIndex;
    parent.layer.close(index);
};


/**
 * 收集数据
 */
GoodsInfoDlg.collectData = function () {
    this.set('id').set('activity_id').set('name').set('money').set('negPrice').set('note').set('startTime').set('endTime').set("old_status").set("laterTime").set("datetime").set("status", $("input[name='status']:checked").val());
};

/**
 * 验证数据是否为空
 */
GoodsInfoDlg.validate = function () {
    $('#goodsInfoForm').data("bootstrapValidator").resetForm();
    $('#goodsInfoForm').bootstrapValidator('validate');
    return $("#goodsInfoForm").data('bootstrapValidator').isValid();
};

/**
 * 提交添加
 */
GoodsInfoDlg.addSubmit = function () {

    this.clearData();
    this.collectData();
    if (!this.validate()) {
        return;
    }
    var tip = '添加';
    //提交信息
    var ajax = new $ax(Feng.ctxPath + "/admin/goods/save", function (data) {
    	if (1 === parseInt(data.code)) {
			Feng.success(tip + "成功！" );
			window.parent.OptLog.table.refresh();
	        GoodsInfoDlg.close();
		} else {
			Feng.error(tip + "失败！" + data.msg + "！");
		}
        
    }, function (data) {
        Feng.error("添加失败!" + data.responseJSON.message + "!");
    });
    ajax.set(this.goodsInfoData);
    ajax.start();
};

/**
 * 提交修改
 */
GoodsInfoDlg.editSubmit = function () {

    this.clearData();
    this.collectData();

    if (!this.validate()) {
        return;
    }
    var tip = '修改';
    //提交信息
    var ajax = new $ax(Feng.ctxPath + "/admin/goods/save", function (data) {
    	if (1 === parseInt(data.code)) {
			Feng.success(tip + "成功！" );
			window.parent.CodeGoods.table.refresh();
	        GoodsInfoDlg.close();
		} else {
			Feng.error(tip + "失败！" + data.msg + "！");
		}
    }, function (data) {
        Feng.error("修改失败!" + data.responseJSON.message + "!");
    });
    ajax.set(this.goodsInfoData);
    ajax.start();
};

/**
 * 提交添加
 */
GoodsInfoDlg.laterSubmit = function () {

    this.clearData();
    this.collectData();
    if (!this.validate()) {
        return;
    }
    var tip = '延期';
    //提交信息
    var ajax = new $ax(Feng.ctxPath + "/admin/goods/laterGoods", function (data) {
    	if (1 === parseInt(data.code)) {
			Feng.success(tip + "成功！" );
			window.parent.CodeGoods.table.refresh();
	        GoodsInfoDlg.close();
		} else {
			Feng.error(tip + "失败！" + data.msg + "！");
		}
        
    }, function (data) {
        Feng.error("添加失败!" + data.responseJSON.message + "!");
    });
    ajax.set(this.goodsInfoData);
    ajax.start();
};

$(function () {
    Feng.initValidator("goodsInfoForm", GoodsInfoDlg.validateFields);
});
