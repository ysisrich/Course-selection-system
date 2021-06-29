<?php
namespace app\index\controller;
use think\Controller;
use think\Cookie;
use think\Session;
class Login extends controller{
    
    public function index(){
        
		// 颜色配置
		if(empty(session('config_color')))
			$this->assign('config_color','#0E4EA8');
		else 
			$this->assign('config_color',session('config_color'));
		
		// dump(session('config_lizi'));die;
		// 粒子特效配置
		if(empty(session('config_lizi')))
			$this->assign('config_lizi',0);
		else 
			$this->assign('config_lizi',session('config_lizi'));
			
			if(isMobile()==1){
			    return $this->fetch('/mobile/index');
			}else{
			    return $this->fetch('/login/index');
			}
			
        
    }
	// 检测登录信息
	public function checkLogin(){
	    $account=input('account');
	    $type=input('type');
	    $password=input('password');
	    $code=input('code');
		
		if(!captcha_check($code)){
		    recordlog($account,'1',$account.'登录失败，验证码错误！');
			return json([
				"status" => 0,
				"info" => '验证码搞错了，仔细再看一下哇!'
			]);
		};
	    
		$res=db('user')->where('account',$account)->find();
		if(empty($res)){
			return json([
				"status" => 0,
				"info" => '没得这个人呐!'
			]);
		}else{
			if (md5($password)!=$res['password']){
			    recordlog($account,'1',$account.'登录失败，密码错误！');
				return json([
					"status" => 0,
					"info" => '密码搞错哒!'
				]);
			}else{
				if($res['type'] == $type){
				    
				    if($res['type']==0){
				        $status = db('manager')->where('account',$account) ->value('status');
				        switch ($status) {
				            case '0':
				                recordlog($account,'1',$account.'登录失败，当前账号为禁用状态！');
				                return json(["status"=>0,"info"=>'当前账号为禁用状态，请联系管理员']);
				                break;
				            default:
				                session('user',$res);
				                recordlog($res['account'],'1',$res['account'].'成功登入了校园选课系统！');
					            return json(["status"=>1,"info"=>'欢迎进入选课系统']);
				                break;
				        }
				    }
				    
				    if($res['type']==1){
				        $status = db('student')->where('account',$account) ->value('status');
				        switch ($status) {
				            case '0':
				                recordlog($account,'1',$account.'登录失败，当前账号为禁用状态！');
				                return json(["status"=>0,"info"=>'当前账号为禁用状态，请联系管理员']);
				                break;
				            default:
				                session('user',$res);
				                recordlog($res['account'],'1',$res['account'].'成功登入了校园选课系统！');
					            return json(["status"=>1,"info"=>'欢迎进入选课系统']);
				                break;
				        }
				    }
				    
				    if($res['type']==2){
				        $status = db('teacher')->where('account',$account) ->value('status');
				        switch ($status) {
				            case '0':
				                recordlog($account,'1',$account.'登录失败，当前账号为禁用状态！');
				                return json(["status"=>0,"info"=>'当前账号为禁用状态，请联系管理员']);
				                break;
				            default:
				                session('user',$res);
				                recordlog($res['account'],'1',$res['account'].'成功登入了校园选课系统！');
					            return json(["status"=>1,"info"=>'欢迎进入选课系统']);
				                break;
				        }
				    }
				    
				}
				else{
				    recordlog($res['account'],'1',$res['account'].'登录失败，身份选择错误！');
				    return json(["status"=>0,"info"=>'身份选择错误，再检查一下哇!']);
				}
				  
			}
		}
	}
	
	public function changeSkin(){
		$index = input('index');
		$colors = ['#009688','#001C26','#8552a1','#0E4EA8','green','#009ad6','red','#303643'];
		session('config_color',$colors[$index]);
		return json([
			"status" => 1,
			"info" => '换肤成功'
		]);
	}
	
	public function setLizi(){
		$type = input('type');
		session('config_lizi',$type);
		return json([
			"status" => 1,
			"info" => '设置粒子特效成功'
		]);
	}
	
	// 忘记密码
	public function forget(){
		// 颜色配置
		if(empty(session('config_color')))
			$this->assign('config_color','#0E4EA8');
		else 
			$this->assign('config_color',session('config_color'));
			
		// 粒子特效配置
		if(empty(session('config_lizi')))
			$this->assign('config_lizi',0);
		else 
			$this->assign('config_lizi',session('config_lizi'));
		
		if(isMobile()==1){
		    return $this->fetch('/mobile/forget');
		}else{
		    return $this->fetch('/login/forget');
		}
	}
	
	// 向手机发送验证码
	public function sendForgetCode(){
		$mobile = input('phone');
		$res=db('student')->where('phone',$mobile)->find();
		$res1=db('manager')->where('phone',$mobile)->find();
		$res2=db('teacher')->where('phone',$mobile)->find();
		
		
		if(empty($res)&&empty($res1)&&empty($res2)){
			cookie('forgetcode',null);
// 			recordlog($mobile,'2',$mobile.'验证失败，未找到该手机号！');
			return json([
				"status" =>0,
				"info" => '未找到该手机号，请联系管理员修改'
			]);
		}

		$aliyun = new Aliyun();
		$code = rand(1000, 9999);
		$res = $aliyun->sendCode($mobile, $code);
   
		if ($res['flag']) {
			cookie('forgetcode',$code);
			return json([
				"status" =>1,
				"info" => '短信发送成功'
			]);
		} else {
			return json([
				"status" =>0,
				"info" => $res['info']
			]);
		}
	}
	
	//  确定修改密码
	public function modifyPassworrd(){
		$account=input('account');
		$phone=input('phone');
		$code=input('code');
		$password=input('password');
		
		if($code!=cookie('forgetcode')){
		    recordlog($account,'7',$account.'密码重置失败，验证码错误！');
			return json([
				"status" =>0,
				"info" => '验证码错误'
			]);
		}
		
		$mod = db('user')->where('account',$account)->find();
		if($mod){
			if($mod['type']==0){
				$mod = db('manager')->where('phone',$phone)->find();
				if(!$mod){
				    recordlog($account,'7',$account.'密码重置失败，账号绑定的手机号不一致！');
					return json([
						"status" =>0,
						"info" => '账号绑定的手机号不一致'
					]);
				}else{
					$data['password']=md5($password);
					$mod = db('user')->where('account',$account)->setField($data);
					cookie('forgetcode',null);
					recordlog($account,'7',$account.'密码修改成功！');
					return json([
						"status" =>1,
						"info" => '修改成功'
					]);
				}
			}else if($mod['type']==1){
				$mod = db('student')->where('phone',$phone)->find();
				if(!$mod){
				    recordlog($account,'7',$account.'密码重置失败，账号绑定的手机号不一致！');
					return json([
						"status" =>0,
						"info" => '账号绑定的手机号不一致'
					]);
				}else{
					$data['password']=md5($password);
					$mod = db('user')->where('account',$account)->setField($data);
					cookie('forgetcode',null);
					recordlog($account,'7',$account.'密码修改成功！');
					return json([
						"status" =>1,
						"info" => '修改成功'
					]);
				}
			}else{
				$mod = db('teacher')->where('phone',$phone)->find();
				if(!$mod){
				    recordlog($account,'7',$account.'密码重置失败，账号绑定的手机号不一致！');
					return json([
						"status" =>0,
						"info" => '账号绑定的手机号不一致'
					]);
				}else{
					$data['password']=md5($password);
					$mod = db('user')->where('account',$account)->setField($data);
					cookie('forgetcode',null);
					recordlog($account,'7',$account.'密码修改成功！');
					return json([
						"status" =>1,
						"info" => '修改成功'
					]);
				}
			}
			
			
		}else{
		    recordlog($account,'7',$account.'密码重置失败，不存在该账号！');
			return json([
				"status" =>0,
				"info" => '不存在该账号，请联系管理员添加'
			]);
		}
			
	}


    protected function doCurlPost($url,$data,$time=5){
        if($url==''||$data==''||$time<=0){
            return false;
        }

        //初始化curl
        $curl=curl_init($url);
        //设置为POST方式
        curl_setopt($curl, CURLOPT_POST, 1);
        //POST数据
		// curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        //如果成功只将结果返回，不自动输出任何内容。
        curl_setopt($curl, CURLOPT_RETURNTRANSFER,1);
        //如果想把一个头包含在输出中，设置这个选项为一个非零值。
        curl_setopt($curl, CURLOPT_HEADER,0);
        //启用时追踪句柄的请求字符串。
        curl_setopt($curl, CURLINFO_HEADER_OUT,1);

        //返回信息
        return curl_exec($curl);
        @curl_close($ch);

    }


}