/**
 * 角色详情对话框（可用于添加和修改对话框）
 */
var UserInfoDlg = {
    userInfoData: {},
    deptZtree: null,
    pNameZtree: null,
    validateFields: {
    	username: {
            validators: {
                notEmpty: {
                    message: '用户名不能为空'
                }
            }
        },
        password: {
            validators: {
                notEmpty: {
                    message: '登录密码不能为空'
                }
            }
        },
        repassword: {
            validators: {
                notEmpty: {
                    message: '重复密码不能为空'
                }
            }
        }
    }
};

/**
 * 清除数据
 */
UserInfoDlg.clearData = function () {
    this.userInfoData = {};
};

/**
 * 设置对话框中的数据
 *
 * @param key 数据的名称
 * @param val 数据的具体值
 */
UserInfoDlg.set = function (key, val) {
    this.userInfoData[key] = (typeof value == "undefined") ? $("#" + key).val() : value;
    return this;
};

/**
 * 设置对话框中的数据
 *
 * @param key 数据的名称
 * @param val 数据的具体值
 */
UserInfoDlg.get = function (key) {
    return $("#" + key).val();
};

/**
 * 关闭此对话框
 */
UserInfoDlg.close = function () {
	var index = parent.layer.getFrameIndex(window.name);
//	var index = window.parent.CodeUser.layerIndex;
    parent.layer.close(index);
};


/**
 * 收集数据
 */
UserInfoDlg.collectData = function () {
    this.set('id').set('username').set('password').set('note').set('name').set('repassword');
};

/**
 * 验证数据是否为空
 */
UserInfoDlg.validate = function () {
    $('#userInfoForm').data("bootstrapValidator").resetForm();
    $('#userInfoForm').bootstrapValidator('validate');
    return $("#userInfoForm").data('bootstrapValidator').isValid();
};

/**
 * 提交添加
 */
UserInfoDlg.addSubmit = function () {

    this.clearData();
    this.collectData();
    if (!this.validate()) {
        return;
    }
    var tip = '添加';
    //提交信息
    var ajax = new $ax(Feng.ctxPath + "/admin/user/add", function (data) {
    	if (1 === parseInt(data.code)) {
			Feng.success(tip + "成功！" );
			window.parent.User.table.refresh();
	        UserInfoDlg.close();
		} else {
			Feng.error(tip + "失败！" + data.msg + "！");
		}
        
    }, function (data) {
        Feng.error("添加失败!" + data.responseJSON.message + "!");
    });
    ajax.set(this.userInfoData);
    ajax.start();
};

/**
 * 提交修改
 */
UserInfoDlg.editSubmit = function () {

    this.clearData();
    this.collectData();

    if (!this.validate()) {
        return;
    }
    var tip = '修改';
    //提交信息
    var ajax = new $ax(Feng.ctxPath + "/admin/user/edit", function (data) {
    	if (1 === parseInt(data.code)) {
			Feng.success(tip + "成功！" );
			window.parent.User.table.refresh();
	        UserInfoDlg.close();
		} else {
			Feng.error(tip + "失败！" + data.msg + "！");
		}
    }, function (data) {
        Feng.error("修改失败!" + data.responseJSON.message + "!");
    });
    ajax.set(this.userInfoData);
    ajax.start();
};

/**
 * 重置密码
 */
UserInfoDlg.setPassword = function () {

    this.clearData();
    this.collectData();

    if (!this.validate()) {
        return;
    }
    var tip = '修改';
    //提交信息
    var ajax = new $ax(Feng.ctxPath + "/admin/user/setPassword", function (data) {
    	if (1 === parseInt(data.code)) {
			Feng.success(tip + "成功！" );
	        UserInfoDlg.close();
		} else {
			Feng.error(tip + "失败！" + data.msg + "！");
		}
    }, function (data) {
        Feng.error("修改失败!" + data.responseJSON.message + "!");
    });
    ajax.set(this.userInfoData);
    ajax.start();
};


/**
 * 提交添加
 */
UserInfoDlg.addAuth = function () {
    
    var tip = '设置';
	var str = '';
	$("input[name='authorize']").each(function(){  
		if($(this).is(":checked"))  
		{  
			str += "," + $(this).val();  
		}  
	});  
	str = str.substr(1);
    var ajax = new $ax(Feng.ctxPath + "/admin/user/auth", function (data) {
    	if (1 === parseInt(data.code)) {
			Feng.success(tip + "成功！" );
	        UserInfoDlg.close();
		} else {
			Feng.error(tip + "失败！" + data.msg + "！");
		}
    }, function (data) {
        Feng.error("修改失败!" + data.responseJSON.message + "!");
    });
    ajax.set('purviewval',str);
	ajax.set('id',$("#id").val());
    ajax.start();
    
};


$(function () {
    Feng.initValidator("userInfoForm", UserInfoDlg.validateFields);
	
});
