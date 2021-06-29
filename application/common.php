<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------


// 判断设备
function isMobile(){
    if (\think\Request::instance()->isMobile()) {
       define('VIEW_PATH', __DIR__ . '/index/view/mobile/');
       return 1;
    } else {
       define('VIEW_PATH', __DIR__ . '/index/view/index/');
       return 2;
    } 
}

// 	记录日志信息

/*
 * 1 登录 2 手机验证 3 添加 4 编辑 5 删除 6 启用禁用 7 密码重置 8 搜索 9 查询
 *
 */
function recordlog($account,$type,$detail_thing)
{
    
    $data['account'] = $account;
    $data['type'] = $type;
    $data['detail_thing'] = $detail_thing;
    $data['ip'] = request()->ip();
    db('log')->insert($data);
}


//返回ajax
function ajaxReturn($status,$arr){
    // $aj['status']=$status;
    // $aj['info']=$arr;
	return json([
		'status' => $status,
		'info' => $arr
	]);
}
//根据数据库的操作返回 
function returnInfo($mod){
    if(is_numeric($mod)){
        echo ajaxReturn(1,'操作成功');
    }else{
        echo ajaxReturn(0,'请刷新后重试');
    }
}
//时间方法简化
function diyDate($time){
    return date("Y-m-d H:i:s",$time);
}
