/**
 * 角色详情对话框（可用于添加和修改对话框）
 */
var CouponInfoDlg = {
    CouponInfoData: {},
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
CouponInfoDlg.clearData = function () {
    this.CouponInfoData = {};
};

/**
 * 设置对话框中的数据
 *
 * @param key 数据的名称
 * @param val 数据的具体值
 */
CouponInfoDlg.set = function (key, val) {
    this.CouponInfoData[key] = (typeof value == "undefined") ? $("#" + key).val() : value;
    return this;
};

/**
 * 设置对话框中的数据
 *
 * @param key 数据的名称
 * @param val 数据的具体值
 */
CouponInfoDlg.get = function (key) {
    return $("#" + key).val();
};

/**
 * 关闭此对话框
 */
CouponInfoDlg.close = function () {
	var index = parent.layer.getFrameIndex(window.name);
//	var index = window.parent.CodeCoupon.layerIndex;
    parent.layer.close(index);
};


/**
 * 收集数据
 */
CouponInfoDlg.collectData = function () {
	
    this.set('goods_id').set('id').set('activityId').set('goodsId').set('billId').set('coupons').set('datetime').set('number').set('note').set('startTime').set('endTime');
};

/**
 * 验证数据是否为空
 */
CouponInfoDlg.validate = function () {
    $('#CouponInfoForm').data("bootstrapValidator").resetForm();
    $('#CouponInfoForm').bootstrapValidator('validate');
    return $("#CouponInfoForm").data('bootstrapValidator').isValid();
};

/**
 * 提交添加
 */
CouponInfoDlg.addSubmit = function () {
    
    this.clearData();
    this.collectData();
    if (!this.validate()) {
        return;
    }
    var tip = '添加';
	var mode = $("input[name='mode']:checked").val();
    //提交信息
    var ajax = new $ax(Feng.ctxPath + "/admin/coupon/add", function (data) {
    	if (1 === parseInt(data.code)) {
			Feng.success("制卡成功！本次制卡编号为：" + data.id);
			window.parent.CodeGoods.table.refresh();
	        CouponInfoDlg.close();
		} else {
			Feng.error("制卡失败！" + data.msg + "！");
		}
        
    }, function (data) {
        Feng.error("添加失败!" + data.responseJSON.message + "!");
    });
	ajax.set('mode',mode);
    ajax.set(this.CouponInfoData);
    ajax.start();
	
};

/**
 * 提交修改
 */
CouponInfoDlg.editSubmit = function () {

    this.clearData();
    this.collectData();

    if (!this.validate()) {
        return;
    }

    //提交信息
    var ajax = new $ax(Feng.ctxPath + "/admin/Coupon/save", function (data) {
        Feng.success("修改成功!");
        window.parent.Role.table.refresh();
        CouponInfoDlg.close();
    }, function (data) {
        Feng.error("修改失败!" + data.responseJSON.message + "!");
    });
    ajax.set(this.CouponInfoData);
    ajax.start();
};

/**
 * 卡券延期
 */
CouponInfoDlg.delaySubmit = function () {

    this.clearData();
    this.collectData();

    if (!this.validate()) {
        return;
    }

    //提交信息
    var ajax = new $ax(Feng.ctxPath + "/admin/Coupon/couponDelay", function (data) {
        if (1 === parseInt(data.code)) {
			Feng.success("延期成功");
			window.parent.Coupon.table.refresh();
	        CouponInfoDlg.close();
		} else {
			Feng.error("操作失败" + data.msg + "！");
		}
    }, function (data) {
        Feng.error("操作失败!" + data.responseJSON.message + "!");
    });
    ajax.set(this.CouponInfoData);
    ajax.start();
};

$(function () {
    Feng.initValidator("CouponInfoForm", CouponInfoDlg.validateFields);
});
