

layui.use(['layer']);
/**
 * 错误提示框
 * 2018-5-11
 *
 */
function layer_error(text){
    layer.alert(text,{icon:2,title:'错误提示'});
}
/**
 * 注意提示框
 * 2018-5-11
 *
 */
function layer_danger(text){
    layer.alert(text,{icon:0,title:'温馨提示'});
}

/**
 * 注意暂无改动
 * 2018-5-11
 *
 */
function layer_noChange(text){
    layer.msg(text,{icon:0,time: 2000,offset:'t'});
}

/**
 * 抖动效果
 * 2018-5-11
 *
 */
function layer_shake(text){
    layer.msg(text,{icon:2,time: 3000,offset:'t',anim:6});
}

/**
 * 成功提示框
 * 2018-5-11
 *
 */
function layer_success(text){
    layer.msg(text,{icon:1,time: 2000,offset:'t'});
}