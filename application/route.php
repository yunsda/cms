<?php

// +----------------------------------------------------------------------
// | Think.Admin
// +----------------------------------------------------------------------
// | 版权所有 2014~2017 武汉市金源信企业服务信息系统有限公司 [ http://www.whjyx.com ]
// +----------------------------------------------------------------------
// | 官方网站: http://www.whjyx.com
// +----------------------------------------------------------------------
// | 开源协议 ( https://mit-license.org )
// +----------------------------------------------------------------------
// | github开源项目：
// +----------------------------------------------------------------------

/*  测试环境禁止操作路由绑定 */
think\Route::post([
    // 禁止修改用户资料
//     'admin/index/info'    => function () {
//         return json(['code' => 0, 'msg' => '测试环境禁修改用户资料<br>请修改路由配置文件!']);
//     },
]);

return [];