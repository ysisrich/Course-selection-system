<?php
namespace app\index\controller;
use app\index\controller\Base;
use think\Cookie;
use think\Session;


class Index extends Base
{
	// 管理员
    public function index()
    {
		if(!empty(session('user')) && session('user')['type'] == 0){
			$this->assign('userInfo',session('user'));
		}
			
		if(!empty(session('user')) && session('user')['type'] == 1)
			$this -> redirect('index/student/index');
		if(!empty(session('user')) && session('user')['type'] == 2)
			$this -> redirect('index/teacher/index');
	    // 	展开侧边栏
        $this -> assign('selected_id',-1);
		return $this->fetch();
    }
    
    
    
    // 进行危险操作前 验证管理员密码
    public function checkManagerPW()
    {
		$user = session('user');
		$pw = input('pw');
		
		$res = db('user') -> where('id',$user['id']) -> find();
	
	
		if($res['password'] == md5($pw)){
		    return json([
					"status" => 1,
					"info" => '密码正确!'
				]);
		}else{
		    return json([
					"status" => 0,
					"info" => '密码错误!'
				]);
		}
    }
    
	
    // 渲染个人信息页面
    public function getPersonalInformation()
    {
		$user = session('user');

		$res = db('manager') ->where('account',$user['account'])->alias('a')->join('role w','a.role = w.rid')-> find();
		$this->assign('userMoreInfo',$res);
		
		
		// 	展开侧边栏
        $this -> assign('selected_id',-1);
		$this->assign('userInfo',$user);
		return $this->fetch();
	}
	
    // 	更新个人基本信息
	public function updateBaseInfo()
	{
	    $data = input('post.');
	    $data['id'] = input('id');
	    $user = session('user');
	   // dump($data);die;
	    $mod = db('manager') -> where('id',$data['id']) ->update($data);
	    if($mod){
	        recordlog($user['account'],'4',$user['account'].'个人基本信息修改成功！');
	        return json([
    		    "status" => 1,
    		    "info" => '个人基本信息修改成功'
		    ]);
	    }else{
	         return json([
    		    "status" => 0,
    		    "info" => '暂无改动'
		    ]);
	    }
	}
	
	
    // 渲染修改密码页面
    public function modifyPassword()
    {
		$user = session('user');
        $this -> assign('selected_id',-1);
		$this->assign('userInfo',$user);
		return $this->fetch();
	}
	
    // 	修改密码
	public function updatePassword()
	{
	    $old_password = input('oldpw');
	    $new_password = input('newpw');
	    $user = session('user');
	    $id = $user['id'];
	    
	    $db_password = db('user') -> where('id',$id) ->value('password');
	    if(md5($old_password) != $db_password){
	        recordlog($user['account'],'7',$user['account'].'修改失败，原密码输入错误！');
	        return json([
    		    "status" => 0,
    		    "info" => '原密码输入错误'
		    ]);
	    }else{
	        $mod = db('user') -> where('id',$id) ->setField('password',md5($new_password));
    	   // if($mod){
    	   recordlog($user['account'],'7',$user['account'].'密码修改成功！');
    	        return json([
        		    "status" => 1,
        		    "info" => '密码修改成功'
    		    ]);
    	   // }
    	   // else{
    	   //      return json([
        // 		    "status" => 0,
        // 		    "info" => '暂无改动'
    		  //  ]);
    	   // }
	    }
	}
	
	
	
	/**
	 * 
	 * 
	 * 权限管理
	 * 权限 0 查看 1 搜索  2 添加 3 修改 4 删除 5 清空
	 * 
	 * 
	 * 
	 * */
	
    // 	查看权限管理
	public function isShowPower()
	{
	    $user = session('user');
	    
        $role = db('manager') -> where('account',$user['account'])->value('role');
        if($role){
            $power = db('role') -> where('rid',$role)->value('power');
            if(strstr($power,'0')){
                $this -> assign('isPower',true);
            }else{
                $this -> assign('isPower',false);
            }
        } 
	}
	
	// 	搜索权限管理
	public function isSearchPower()
	{
	    $user = session('user');
	    
        $role = db('manager') -> where('account',$user['account'])->value('role');
        if($role){
            $power = db('role') -> where('rid',$role)->value('power');
             
            if(strstr($power,'1') == false){
                return false; 
            }else{
                return true;
            }
        } 
	}
	
	// 	添加权限管理
	public function isAddPower()
	{
	    $user = session('user');
	    
        $role = db('manager') -> where('account',$user['account'])->value('role');
        if($role){
            $power = db('role') -> where('rid',$role)->value('power');
             
            if(strstr($power,'2') == false){
                return false; 
            }else{
                return true;
            }
        } 
	}
	
	// 	编辑权限管理
	public function isEditPower()
	{
	    $user = session('user');
	    
        $role = db('manager') -> where('account',$user['account'])->value('role');
        if($role){
            $power = db('role') -> where('rid',$role)->value('power');
             
            if(strstr($power,'3') == false){
                return false; 
            }else{
                return true;
            }
        } 
	}
	
	// 	删除权限管理
	public function isDeletePower()
	{
	    $user = session('user');
	    
        $role = db('manager') -> where('account',$user['account'])->value('role');
        if($role){
            $power = db('role') -> where('rid',$role)->value('power');
             
            if(strstr($power,'4') == false){
                return false; 
            }else{
                return true;
            }
        } 
	}
	
	// 	清空权限管理
	public function isClearPower()
	{
	    $user = session('user');
	    
        $role = db('manager') -> where('account',$user['account'])->value('role');
        if($role){
            $power = db('role') -> where('rid',$role)->value('power');
             
            if(strstr($power,'5') == false){
                return false; 
            }else{
                return true;
            }
        } 
	}
	
	
	
   /**
	 * 
     * 用户管理>登录用户接口
     * 渲染页面
     *
     * 
     */
	
	 // 渲染登录用户管理界面
    public function accountManage()
    {
        $user = session('user');
		
		$mod = db('user') -> select();
		if($mod){
		    $this -> assign('users',$mod);
		}
		
		$this->isShowPower();
		
           
		
        // 	展开侧边栏
        $this -> assign('selected_id',0);
		$this->assign('userInfo',$user);
        return $this->fetch();
    }
    
    
    // 搜索功能
     public function getAccountData()
    {
        $user = session('user');
        $keyword = input('value','');
        
        if(!$this->isSearchPower()){
            $result = db('user') ->select();
            return json([
                "status" => 0,
                "info" => '您还没有搜索权限，请联系管理员添加！',
            ]);
        }
        
        $res = db('user')->where('account','like','%'.$keyword.'%')->select();
        
        // dump($res);die;
        if($res){
            recordlog($user['account'],'8','搜索用户成功，查询到'. count($res) . '条数据！');
            return json([
                "status" => 1,
                "info" => '已成功为你查询到'. count($res) . '条数据',
                "data" => $res
            ]);
        }else{
            return json([
                "status" => 0,
                "info" => '未查询到任何数据',
            ]);
        }
    }
    
    // 按身份筛选
     public function selectCondition()
    {
        $user = session('user');
        $keyword = input('value','');
        if($keyword == 3){
            $res = db('user')->select();
            recordlog($user['account'],'8','筛选用户成功，已成功为你查询到'. count($res) . '条数据！');
            return json([
                "status" => 1,
                "info" => '已成功为你查询到'. count($res) . '条数据',
                "data" => $res
            ]);
        }
        
        $res = db('user')->where('type',$keyword)->select();
        
        // dump($res);die;
        if($res){
            recordlog($user['account'],'8','筛选用户成功，已成功为你查询到'. count($res) . '条数据！');
            return json([
                "status" => 1,
                "info" => '已成功为你查询到'. count($res) . '条数据',
                "data" => $res
            ]);
        }else{
            return json([
                "status" => 0,
                "info" => '未查询到任何数据',
            ]);
        }
    }
    
    
	
	/**
	 * 
     * 用户管理>超级管理员接口
     * 渲染管理员页面
     * 对管理员信息的增删改查
     * 根据关键字搜索数据
     * 对管理员手机号进行验证
     * 重置管理员密码
     * 批量删除
     * 启用禁用管理员
     * 
     */
	
	
    // 	渲染超级用户管理界面
    public function superUserManage()
    {
        $user = session('user');
		
		$mod = db('manager')->alias('a')->join('role w','a.role = w.rid')->select();
// 		dump($mod);die;
		
		$this -> assign('managers',$mod);
        
        $this->isShowPower();
		
        // 	获取角色
        $role = db('role')->select();
        $this->assign('roles',$role);
        
        // 	展开侧边栏
        $this -> assign('selected_id',1);
		$this->assign('userInfo',$user);
        return $this->fetch();
    }
    
    // 添加超级用户
	public function superUserManagerAdd()
    {
        $user = session('user');
        
        $data = input('post.');
        
        
        
        if($user['account'] != 'admin' && ($data['role'] == 1 || $data['role'] == 2 || $data['role'] == 3)){
            return json([
                "status" => 0,
                "info" => '普通管理员、超级管理员和开发者角色仅限admin超级管理员添加！'
            ]);
        }
      
        if(!$this->isAddPower()){
            return json([
                "status" => 0,
                "info" => '您还没有添加权限，请联系管理员添加！'
            ]);
        }
        
        $account = db('user')->where('account',$data['account'])->find();
        if(!empty($account)){
            recordlog($user['account'],'3','添加失败，'.$data['account'].'该管理员已存在！');
            return json([
                "status" => 0,
                "info" => '该管理员已存在，请重试'
            ]);
        }
        
        $data['status'] = input('status',0);
        $data['phone_check']=input('phone_check',0);
        
        $user1['account'] = $data['account'];
        $user1['password'] = md5($data['password']);
        $user1['type'] = 0;
        
        
        // 将账户密码先存入用户表中
        $mod1 = db('user') -> insert($user1);
        
        unset($data['password']);
        
        
        
        $mod2 = db('manager') -> insert($data);
        if($mod1 && $mod2){
            recordlog($user['account'],'3','添加成功，'.$user1['account'].'已添加到管理员列表中！');
            return json([
                "status" => 1,
                "info" => "添加成功"
            ]);
        }else{
            recordlog($user['account'],'3','添加失败，网络错误');
            return json([
                "status" => 0,
                "info" => "网络错误，添加失败"
            ]);
        }
    }
    
    // 删除用户
	public function superUserManagerDelete()
    {
        $user =session('user');
        $id = input('post.id');
        
        if(!$this->isDeletePower()){
            return json([
                "status" => 0,
                "info" => '您还没有删除权限，请联系管理员添加！'
            ]);
        }
        
        $res = db('manager') ->where('id',$id)->find();
        if($res['account'] == 'admin'){
            recordlog($user['account'],'5','删除失败，非法删除超级管理员！');
            return json([
                "status" => 0,
                "info" => '非法删除超级管理员'
            ]);
        }
        
        
        $account = $res['account'];
        $mod1 = db('user') -> where('account',$account) ->delete();
        $mod2 = db('manager') -> where('id',$id) ->delete();
        
        if($mod1 && $mod2){
            recordlog($user['account'],'5','删除成功，'.$account.'已从管理员列表中删除！');
            return json([
                "status" => 1,
                "info" => "删除成功"
            ]);
        }else{
            recordlog($user['account'],'5','删除失败，网络错误！');
            return json([
                "status" => 0,
                "info" => "网络错误，删除失败"
            ]);
        }
    }
	
    // 	根据id获取管理员用户信息
    public function getManagerOne()
    {
        $user = session('user');
        $id = input('post.id');
        
        $res = db('manager') -> where('id',$id) -> find();
        if($res){
            recordlog($user['account'],'9','查询成功，获取到一条关于'.$res['account'].'的信息!');
            return json([
                "status" => 1,
                "info" => '查询成功',
                "data" => $res
            ]);
        }else{
            recordlog($user['account'],'9','查询失败，网络错误!');
            return json([
                "status" => 0,
                "info" => '网络错误，查询失败',
            ]);
        }
    }
    
    // 	编辑管理员用户信息
    public function superUserManagerEdit()
    {
        $user = session('user');
        $id = input('id');
        $flag = input('flag');
        $data = input('post.');
        // dump($flag);die;
        
        if($user['account'] != 'admin' && ($data['role'] == 1 || $data['role'] == 2 || $data['role'] == 3)){
            return json([
                "status" => 0,
                "info" => '普通管理员、超级管理员和开发者角色仅限admin超级管理员添加！'
            ]);
        }
        
        if(!$this->isEditPower()){
            return json([
                "status" => 0,
                "info" => '您还没有编辑权限，请联系管理员添加！',
            ]);
        }
        
        // 账号已改动
        if(!$flag){
            $res = db('user')->where('account',$data['account'])->find();
            if(!empty($res)){
                recordlog($user['account'],'9','编辑失败，'.$res['account'].'该管理员已存在!');
                return json([
                    "status" => 0,
                    "info" => '该管理员已存在，请重试'
                ]);
            }
        }
        
        
        // 不能修改 超级管理员的角色信息
        // if(!empty($account) && $account=='admin' && $data['role']!=1){
        //     return json([
        //         "status" => 0,
        //         "info" => '非法修改超级管理员的角色信息'
        //     ]);
        // }
        
        $oldAccount =db('manager') -> where('id',$id) -> value('account');
        
        
        
        $res1 = db('user') -> where('account',$oldAccount) -> setField('account',$data['account']);
        $res2 = db('manager') -> where('id',$id) -> update($data);
        if($res1 || $res2){
            recordlog($user['account'],'4','编辑成功，'.$oldAccount.'被修改成'.$data['account'].'!');
            return json([
                "status" => 1,
                "info" => '数据更新成功',
            ]);
        }else{
            return json([
                "status" => 0,
                "info" => '数据暂无改动',
            ]);
        }
    }
    
    // 	根据id编辑管理员用户状态
    public function superUserManagerStatusEdit()
    {
        $user = session('user');
        $data = input('post.');
        
        if(!$this->isEditPower()){
            return json([
                "status" => 0,
                "info" => '您还没有编辑权限，请联系管理员添加！',
            ]);
        }
        
        $res = db('manager') -> where('id',$data['id']) -> value('account');
        if($res == 'admin'){
            recordlog($user['account'],'6','禁用失败,非法禁用超级管理员!');
            return json([
                "status" => 0,
                "info" => '非法禁用超级管理员'
            ]);
        }
        // dump($data);die;
        $account=db('manager') -> where('id',$data['id']) -> value('account');
        $mod =db('manager') -> where('id',$data['id']) -> setField('status',$data['status']);
        
        if($mod){
            recordlog($user['account'],'6','成功修改'.$account.'的状态!');
            return json([
                "status" => 1,
                "info" => '状态修改成功',
            ]);
        }else{
            return json([
                "status" => 0,
                "info" => '网络错误，状态修改失败',
            ]);
        }
    }
    
    // 	根据id重置管理员用户密码
    public function modifyManagerPw()
    {
        $user = session('user');
        $data = input('post.');
        
        $account = db('manager') -> where('id',$data['id']) -> value('account');
        if($account == 'admin'){
            recordlog($user['account'],'7','非法修改超级管理员的密码!');
            return json([
                "status" => 0,
                "info" => '非法修改超级管理员'
            ]);
        }
        
        $mod = db('user') ->where('account',$account) ->setField('password',md5($data['password']));
        
        if($mod){
            recordlog($user['account'],'7','成功修改账号'.$account.'的密码!');
            return json([
                "status" => 1,
                "info" => '密码修改成功',
            ]);
        }else{
            return json([
                "status" => 0,
                "info" => '暂无改动',
            ]);
        }
    }
    
    // 	根据关键字搜索内容
    public function getSearchData()
    {
        $user = session('user');
        $keyword = input('value','');
        
        if(!$this->isSearchPower()){
            $result = db('user') ->select();
            return json([
                "status" => 0,
                "info" => '您还没有搜索权限，请联系管理员添加！',
            ]);
        }
        
        $result = db('manager')->where('name','like','%'.$keyword.'%')->select();
    	$r1 = db('manager')->where('account','like','%'.$keyword.'%')->select();
    	$r2 = db('manager')->where('phone','like','%'.$keyword.'%')->select();
    	$r3 = db('manager')->where('email','like','%'.$keyword.'%')->select();
    	$res = array_merge((array)$result,(array)$r1,(array)$r2,(array)$r3);
        
        // 去除筛选重复的值
        $compare = array();
        foreach($res as $i=>$o) { if(in_array($o['id'], $compare )) unset($res[$i]); else $compare [] = $o['id'];}
        // dump($res);die;
        if($res){
            recordlog($user['account'],'9','查询管理员成功，已成功为你查询到'. count($res) . '条数据!');
            return json([
                "status" => 1,
                "info" => '已成功为你查询到'. count($res) . '条数据',
                "data" => $res
            ]);
        }else{
            return json([
                "status" => 0,
                "info" => '未查询到任何数据',
            ]);
        }
    }
    
    // 	typehead 根据关键字搜索内容
    public function getAccountList()
    {
        $user = session('user');
        $keyword = input('value','');
        
    	$res = db('manager')->where('account','like','%'.$keyword.'%')->select();
    	
        // dump($res);die;
        if($res){
            return json([
                "status" => 1,
                "info" => '已成功为你查询到'. count($res) . '条数据',
                "data" => $res
            ]);
        }else{
            return json([
                "status" => 0,
                "info" => '未查询到任何数据',
            ]);
        }
    }
    
    // 	批量删除管理员
    public function deleteMuch()
    {
        $user=session('user');
        $ids = input('id');
        
        if(!$this->isDeletePower()){
            return json([
                "status" => 0,
                "info" => '您还没有删除权限，请联系管理员添加！'
            ]);
        }
        
        // dump(str_split($ids));die;
        $ids = explode(',',$ids);
        foreach ($ids as $id){
            $account = db('manager')->where('id',$id) -> value('account'); 
            if($account == 'admin'){
                recordlog($user['account'],'5','删除失败，非法删除超级管理员!');
                 return json([
                    "status" => 0,
                    "info" => '非法删除超级管理员'
                ]);
            }else{
                $mod1 = db('user')->where('account',$account) -> delete();
                $mod2 = db('manager')->where('id',$id) -> delete();
            }
        }
        
        
        if($mod1 && $mod2){
            recordlog($user['account'],'5','成功批量删除管理员！');
            return json([
                "status" => 1,
                "info" => '删除成功',
            ]);
        }else{
            return json([
                "status" => 0,
                "info" => '网络错误，删除失败',
            ]);
        }
    }
    
    
    
    
    
    /**
     * 
     * 用户管理>学生管理接口
     * 渲染学生页面
     * 对学生信息的增删改查
     * 根据关键字搜索数据
     * 重置密码
     * 
     */
	
    // 	渲染学生页面
	public function studentManage()
	{
	    $user = session('user');
        $this->assign('userInfo',$user);
        
        // 学生信息
        $student = db('student') -> select();
        $this->isShowPower();
        // 课程
        
        foreach ($student as $k=>$v){
            if($student[$k]['selected_course']){
                $course_name = db('course')->where('cid',$v['selected_course']) -> value('cname');
                $student[$k]['selected_course'] = $course_name;
            }
        }
		// 学院		
		$college = db('college') -> select();
		if($college){
		   foreach ($student as $i=>$s){
    	       foreach($college as $k ) {
                    if ($k['id'] == $s['college']) {
                        $student[$i]['college']  =  $k['name'];
                    }
               }
    	    }
		    $this -> assign('college',$college);
		}
		// 专业	
		$major = db('major') -> select();
		if($major){
		      foreach ($student as $i=>$s){
    	       foreach($major as $k ) {
                    if ($k['id'] == $s['major']) {
                        $student[$i]['major']  =  $k['name'];
                    }
               }
    	    }
		    $this -> assign('major',$major);
		}
		// 年级	
		$year = db('year') -> select();
		if($year){
		      foreach ($student as $i=>$s){
    	       foreach($year as $k ) {
                    if ($k['id'] == $s['year']) {
                        $student[$i]['year']  =  $k['name'];
                    }
               }
    	    }
		    $this -> assign('year',$year);
		}
		// 班级	
		$grade = db('grade') -> select();
		if($grade){
		      foreach ($student as $i=>$s){
    	       foreach($grade as $k ) {
                    if ($k['id'] == $s['grade']) {
                        $student[$i]['grade']  =  $k['name'];
                    }
               }
    	    }
		    $this -> assign('grade',$grade);
		}
		
		$this -> assign('students',$student);
// 		dump($student);die;
		
		// 	展开侧边栏
        $this -> assign('selected_id',2);
	    return $this->fetch();
	}
	
    // 	添加学生
	public function UserStudentAdd()
	{
	    $user = session('user');
	    $data = input('post.');
        
        
        if(!$this->isAddPower()){
            return json([
                "status" => 0,
                "info" => '您还没有添加权限，请联系管理员添加！'
            ]);
        }
        
        // dump($data);die;
        $res = db('user')->where('account',$data['account'])->find();
        if(!empty($res)){
            recordlog($user['account'],'3','添加失败，'.$res['account'].'该学号已存在！');
            return json([
                "status" => 0,
                "info" => '该学号已存在，请重试'
            ]);
        }
        
        $user1['account'] = $data['account'];
        $user1['password'] = md5($data['password']);
        $user1['type'] = 1;
        $mod1 = db('user')->insert($user1);
        
        
        unset($data['password']);
        $data['phone_check']= input('phone_check',0);
        $data['status']= input('status',0);
        
        $mod = db('student')->insert($data);
        if($mod && $mod1){
            recordlog($user['account'],'3','添加成功，'.$user1['account'].'已添加到学生列表中！');
            return json([
                "status" => 1,
                "info" => '学生添加成功'
            ]);
        }else{
            return json([
               "status" => 0,
                "info" => '网络错误，学生添加失败' 
            ]);
        }
	}
	
    // 删除学生
    public function UserStudentDelete()
    {
        $user = session('user');
        $id = input('post.id');
        
        
        if(!$this->isDeletePower()){
            return json([
                "status" => 0,
                "info" => '您还没有删除权限，请联系管理员添加！'
            ]);
        }
        
        
        $res = db('student') ->where('id',$id)->find();
        
        $account = $res['account'];
        if($account){
            $mod1 = db('user') -> where('account',$account) ->delete();
            $mod2 = db('student') -> where('id',$id) ->delete();
        }
        
        
        if($mod1 && $mod2){
            recordlog($user['account'],'5','删除成功，'.$account.'已从学生列表中删除！');
            return json([
                "status" => 1,
                "info" => "删除成功"
            ]);
        }else{
            return json([
                "status" => 0,
                "info" => "网络错误，删除失败"
            ]);
        }
    }
	
    // 	重置学生密码
    public function modifyStudentPw()
    {
        $user = session('user');
        $data = input('post.');
        
        $account = db('student') -> where('id',$data['id']) -> value('account');
        $mod = db('user') ->where('account',$account) ->setField('password',md5($data['password']));
        
        if($mod){
            recordlog($user['account'],'7','密码修改成功，学生'.$account.'密码已被重置！');
            return json([
                "status" => 1,
                "info" => '密码修改成功',
            ]);
        }else{
            return json([
                "status" => 0,
                "info" => '暂无改动',
            ]);
        }
    }
	
	// 	根据id获取学生用户信息
    public function getStudentOne()
    {
        $user = session('user');
        $id = input('post.id');
        
        $res = db('student') -> where('id',$id) -> find();
        if($res){
            recordlog($user['account'],'9','查询成功，获取到一条关于'.$res['account'].'学生的信息！');
            return json([
                "status" => 1,
                "info" => '查询成功',
                "data" => $res
            ]);
        }else{
            return json([
                "status" => 0,
                "info" => '网络错误，查询失败',
            ]);
        }
    }
    
     // 根据id编辑学生用户状态
    public function UserStudentStatusEdit()
    {
        $user = session('user');
        $data = input('post.');
        
        if(!$this->isEditPower()){
            return json([
                "status" => 0,
                "info" => '您还没有编辑权限，请联系管理员添加！',
            ]);
        }
        
        $account =db('student') -> where('id',$data['id']) -> value('account');
        $mod =db('student') -> where('id',$data['id']) -> setField('status',$data['status']);
        
        if($mod){
            recordlog($user['account'],'6','状态修改成功，学生'.$account.'的状态已被修改！');
            return json([
                "status" => 1,
                "info" => '状态修改成功',
            ]);
        }else{
            return json([
                "status" => 0,
                "info" => '网络错误，状态修改失败',
            ]);
        }
    }
    
    // 获取学院、年级、专业、班级 信息
    public function getCYMG()
    {
        $year = db('year')->select();
        $college = db('college')->select();
        $major = db('major')->select();
        $grade = db('grade')->select();
        
        if(!empty($year) && !empty($college) && !empty($major) && !empty($grade)){
            return json([
                "status" => 1,
                "info" => '获取数据成功',
                "data" =>[
                    "year" => $year,
                    "major" => $major,
                    "college" => $college,
                    "grade" => $grade
                ]
            ]);
        }else{
             return json([
                "status" => 0,
                "info" => '获取数据失败'
            ]);
        }
    }
    
    // 	编辑学生信息
     public function UserStudentEdit()
    {
        $user = session('user');
        $id = input('id');
        $flag = input('flag');
        $data = input('post.');
        
        if(!$this->isEditPower()){
            return json([
                "status" => 0,
                "info" => '您还没有编辑权限，请联系管理员添加！',
            ]);
        }
        // dump($flag);die;
        
        // 账号已改动
        if(!$flag){
            $res = db('user')->where('account',$data['account'])->find();
            if(!empty($res)){
                recordlog($user['account'],'4','编辑失败，'.$res['account'].'该学生学号已存在！');
                return json([
                    "status" => 0,
                    "info" => '该学生学号已存在，请重试'
                ]);
            }
        }
        
        $oldAccount =db('student') -> where('id',$id) -> value('account');
        
        
        $res1 = db('user') -> where('account',$oldAccount) -> setField('account',$data['account']);
        $res2 = db('student') -> where('id',$id) -> update($data);
        if($res1 || $res2){
            recordlog($user['account'],'4','编辑成功，'.$oldAccount.'该学生信息已被修改！');
            return json([
                "status" => 1,
                "info" => '数据更新成功',
            ]);
        }else{
            return json([
                "status" => 0,
                "info" => '数据暂无改动',
            ]);
        }
    }
    
    // 	根据关键字搜索内容
    public function getSearchStudentData()
    {
        $user = session('user');
        $keyword = input('value','');
        
        if(!$this->isSearchPower()){
            $result = db('user') ->select();
            return json([
                "status" => 0,
                "info" => '您还没有搜索权限，请联系管理员添加！',
            ]);
        }
        
        $result = db('student')->where('name','like','%'.$keyword.'%')->select();
    	$r1 = db('student')->where('account','like','%'.$keyword.'%')->select();
    	$r2 = db('student')->where('phone','like','%'.$keyword.'%')->select();
    	$r3 = db('student')->where('email','like','%'.$keyword.'%')->select();
    	$res = array_merge((array)$result,(array)$r1,(array)$r2,(array)$r3);
        
        // 去除筛选重复的值
        $compare = array();
        foreach($res as $i=>$o) { if(in_array($o['id'], $compare )) unset($res[$i]); else $compare [] = $o['id'];}
        // dump($res);die;
        if($res){
            recordlog($user['account'],'9','查询学生成功，已成功为你查询到'. count($res) . '条数据');
            return json([
                "status" => 1,
                "info" => '已成功为你查询到'. count($res) . '条数据',
                "data" => $res
            ]);
        }else{
            return json([
                "status" => 0,
                "info" => '未查询到任何数据',
            ]);
        }
    }
    
    // 	typehead 根据关键字搜索内容
    public function getStudentList()
    {
        $user = session('user');
        $keyword = input('value','');
        
        $res = db('student')->where('account','like','%'.$keyword.'%')->select();
        
        if($res){
            return json([
                "status" => 1,
                "info" => '已成功为你查询到'. count($res) . '条数据',
                "data" => $res
            ]);
        }else{
            return json([
                "status" => 0,
                "info" => '未查询到任何数据',
            ]);
        }
    }
    
    // 	批量删除学生
    public function deleteMuchStudent()
    {
        $user = session('user');
        $ids = input('id');
        
        if(!$this->isDeletePower()){
            return json([
                "status" => 0,
                "info" => '您还没有删除权限，请联系管理员添加！'
            ]);
        }
        
        
        // dump(str_split($ids));die;
        $ids = explode(',',$ids);
        foreach ($ids as $id){
            $account = db('student')->where('id',$id) -> value('account'); 
            if($account){
                $mod1 = db('user')->where('account',$account) -> delete();
                $mod2 = db('student')->where('id',$id) -> delete();
            }
        }
        
        if($mod1 && $mod2){
            recordlog($user['account'],'5','成功批量删除学生数据');
            return json([
                "status" => 1,
                "info" => '删除成功',
            ]);
        }else{
            return json([
                "status" => 0,
                "info" => '网络错误，删除失败',
            ]);
        }
    }
    
    
    
    
    
	
	 /**
     * 
     * 用户管理>教师管理接口
     * 渲染教师页面
     * 对教师信息的增删改查
     * 根据关键字搜索数据
     * 重置密码
     * 
     */
	
	
	public function teacherManage()
	{
	    $user = session('user');
        $this->assign('userInfo',$user);
        
        $this->isShowPower();
        // 教师信息
        $teacher = db('teacher') -> select();
        foreach ($teacher as $k=>$v){
            $course=db('course')->where('cid',$v['course'])->find();
            $teacher[$k]['course']=$course['cname'];
        }  
        
        $course = db('course')->select();
        $this->assign('courses',$course);
        
        $this->assign('teachers',$teacher);
        // 	展开侧边栏
        $this -> assign('selected_id',3);
	    return $this->fetch();
	}
	
	// 	添加教师
	public function UserTeacherAdd()
	{
	    $user = session('user');
	    $data = input('post.');
	    
	    if(!$this->isAddPower()){
            return json([
                "status" => 0,
                "info" => '您还没有添加权限，请联系管理员添加！'
            ]);
        }
        
        
        // dump($data);die;
        $res = db('user')->where('account',$data['account'])->find();
        if(!empty($res)){
            recordlog($user['account'],'3','添加失败，'.$res['account'].'该教师工号已存在！');
            return json([
                "status" => 0,
                "info" => '该教师工号已存在，请重试'
            ]);
        }
        
        $data['status'] = input('status',0);
        $data['phone_check']=input('phone_check',0);
        
        $user1['account'] = $data['account'];
        $user1['password'] = md5($data['password']);
        $user1['type'] = 2;
        $mod1 = db('user')->insert($user1);
        
        
        unset($data['password']);
        $data['phone_check']= input('phone_check',0);
        
        $mod = db('teacher')->insert($data);
        if($mod && $mod1){
            recordlog($user['account'],'3','添加成功，'.$user1['account'].'已添加到教师列表中！');
            return json([
                "status" => 1,
                "info" => '教师添加成功'
            ]);
        }else{
            return json([
               "status" => 0,
                "info" => '网络错误，教师添加失败' 
            ]);
        }
	}
	
    // 删除教师
    public function UserTeacherDelete()
    {
        $user = session('user');
        $id = input('post.id');
        
        if(!$this->isDeletePower()){
            return json([
                "status" => 0,
                "info" => '您还没有删除权限，请联系管理员添加！'
            ]);
        }
        
        $res = db('teacher') ->where('id',$id)->find();
        
        $account = $res['account'];
        $mod1 = db('user') -> where('account',$account) ->delete();
        $mod2 = db('teacher') -> where('id',$id) ->delete();
        
        if($mod1 && $mod2){
            recordlog($user['account'],'5','删除成功，'.$account.'已从教师列表中删除！');
            return json([
                "status" => 1,
                "info" => "删除成功"
            ]);
        }else{
            return json([
                "status" => 0,
                "info" => "网络错误，删除失败"
            ]);
        }
    }
	
    // 	重置教师密码
    public function modifyTeacherPw()
    {
        $user = session('user');
        $data = input('post.');
        
        $account = db('teacher') -> where('id',$data['id']) -> value('account');
        $mod = db('user') ->where('account',$account) ->setField('password',md5($data['password']));
        
        if($mod){
            recordlog($user['account'],'7','密码修改成功，教师'.$account.'密码已被重置！');
            return json([
                "status" => 1,
                "info" => '密码修改成功',
            ]);
        }else{
            return json([
                "status" => 0,
                "info" => '暂无改动',
            ]);
        }
    }
	
	// 	根据id获取教师用户信息
    public function getTeacherOne()
    {
        $user = session('user');
        $id = input('post.id');
        
        $res = db('teacher') -> where('id',$id) -> find();
        if($res){
            recordlog($user['account'],'9','查询成功，获取到一条关于'.$res['account'].'教师的信息！');
            return json([
                "status" => 1,
                "info" => '查询成功',
                "data" => $res
            ]);
        }else{
            return json([
                "status" => 0,
                "info" => '网络错误，查询失败',
            ]);
        }
    }
    
     // 根据id编辑教师用户状态
    public function UserTeacherStatusEdit()
    {
        $user = session('user');
        $data = input('post.');
        
        if(!$this->isEditPower()){
            return json([
                "status" => 0,
                "info" => '您还没有编辑权限，请联系管理员添加！',
            ]);
        }
        
        $account=db('teacher') -> where('id',$data['id']) -> value('account');
        $mod =db('teacher') -> where('id',$data['id']) -> setField('status',$data['status']);
        
        if($mod){
            recordlog($user['account'],'6','状态修改成功，教师'.$account.'的状态已被修改！');
            return json([
                "status" => 1,
                "info" => '状态修改成功',
            ]);
        }else{
            return json([
                "status" => 0,
                "info" => '网络错误，状态修改失败',
            ]);
        }
    }
    
    // 	编辑教师信息
     public function UserTeacherEdit()
    {
        $user = session('user');
        $id = input('id');
        $flag = input('flag');
        $data = input('post.');
        // dump($flag);die;
        
        if(!$this->isEditPower()){
            return json([
                "status" => 0,
                "info" => '您还没有编辑权限，请联系管理员添加！',
            ]);
        }
        
        
        // 账号已改动
        if(!$flag){
            $res = db('user')->where('account',$data['account'])->find();
            if(!empty($res)){
                recordlog($user['account'],'4','编辑失败，'.$res['account'].'该教师工号已存在！');
                return json([
                    "status" => 0,
                    "info" => '该教师工号已存在，请重试'
                ]);
            }
        }
        
        $oldAccount =db('teacher') -> where('id',$id) -> value('account');
        
        
        $res1 = db('user') -> where('account',$oldAccount) -> setField('account',$data['account']);
        $res2 = db('teacher') -> where('id',$id) -> update($data);
        if($res1 || $res2){
            recordlog($user['account'],'4','编辑成功，'.$oldAccount.'该教师信息已被修改！');
            return json([
                "status" => 1,
                "info" => '数据更新成功',
            ]);
        }else{
            return json([
                "status" => 0,
                "info" => '数据暂无改动',
            ]);
        }
    }
    
    // 	根据关键字搜索内容
    public function getSearchTeacherData()
    {
        $user = session('user');
        $keyword = input('value','');
        
        if(!$this->isSearchPower()){
            $result = db('user') ->select();
            return json([
                "status" => 0,
                "info" => '您还没有搜索权限，请联系管理员添加！',
            ]);
        }
        
        $result = db('teacher')->where('name','like','%'.$keyword.'%')->select();
    	$r1 = db('teacher')->where('account','like','%'.$keyword.'%')->select();
    	$r2 = db('teacher')->where('phone','like','%'.$keyword.'%')->select();
    	$r3 = db('teacher')->where('email','like','%'.$keyword.'%')->select();
    	$res = array_merge((array)$result,(array)$r1,(array)$r2,(array)$r3);
        
        // 去除筛选重复的值
        $compare = array();
        foreach($res as $i=>$o) { if(in_array($o['id'], $compare )) unset($res[$i]); else $compare [] = $o['id'];}
        // dump($res);die;
        if($res){
            recordlog($user['account'],'9','查询教师成功，已成功为你查询到'. count($res) . '条数据');
            return json([
                "status" => 1,
                "info" => '已成功为你查询到'. count($res) . '条数据',
                "data" => $res
            ]);
        }else{
            return json([
                "status" => 0,
                "info" => '未查询到任何数据',
            ]);
        }
    }
    
    
    // 	typehead 根据关键字搜索内容
    public function getTeacherList()
    {
        $user = session('user');
        $keyword = input('value','');
        
    	$res = db('teacher')->where('account','like','%'.$keyword.'%')->select();
    	
        if($res){
            return json([
                "status" => 1,
                "info" => '已成功为你查询到'. count($res) . '条数据',
                "data" => $res
            ]);
        }else{
            return json([
                "status" => 0,
                "info" => '未查询到任何数据',
            ]);
        }
    }
    
    // 	批量删除教师
    public function deleteMuchTeacher()
    {
        $user = session('user');
        $ids = input('id');
        
        if(!$this->isDeletePower()){
            return json([
                "status" => 0,
                "info" => '您还没有删除权限，请联系管理员添加！'
            ]);
        }
        
        // dump(str_split($ids));die;
        $ids = explode(',',$ids);
        foreach ($ids as $id){
            $account = db('teacher')->where('id',$id) -> value('account'); 
            if($account){
                $mod1 = db('user')->where('account',$account) -> delete();
                $mod2 = db('teacher')->where('id',$id) -> delete();
            }
        }
        
        if($mod1 && $mod2){
            recordlog($user['account'],'5','成功批量删除教师数据');
            return json([
                "status" => 1,
                "info" => '删除成功',
            ]);
        }else{
            return json([
                "status" => 0,
                "info" => '网络错误，删除失败',
            ]);
        }
    }
	
	
	
	/**
     * 
     * 课程管理>课程管理接口
     * 渲染课程页面
     * 对课程信息的增删改查
     * 根据关键字搜索数据
     * 
     */
    
    // 课程管理
    public function  courseManage()
    {
        $user = session('user');
        $this->assign('userInfo',$user);
        
        $this->isShowPower();
        
        // 与学院 学级 专业 联合查询
        $course = db('course')->select();
        foreach ($course as $k=>$v){
            
            $cyear=db('year')->where('id',$v['cyear'])->find();
            $course[$k]['cyear']=$cyear['name'];
            
            $major=db('major')->where('id',$v['major'])->find();
            $course[$k]['major']=$major['name'];
            
            $college=db('college')->where('id',$v['college'])->find();
            $course[$k]['college']=$college['name'];
        }    
        // dump($course);die;
        
        $year = db('year')->select();
        $college = db('college')->select();
        $major = db('major')->select();
        
        $this->assign('years',$year);
        $this->assign('colleges',$college);
        $this->assign('majors',$major);
        
        $this->assign('courses',$course);
        
       // 	展开侧边栏
        $this -> assign('selected_id',4);
	    return $this->fetch();
    }
    
    // 添加课程
    public function  addCourse()
    {
        $user = session('user');
        $data = input('post.');
        
        if(!$this->isAddPower()){
            return json([
                "status" => 0,
                "info" => '您还没有添加权限，请联系管理员添加！'
            ]);
        }
        
        
        $notselected = input('number');
        $cname = input('post.cname');
        $mod = db('course')->where('cname',$cname)->find();
        
        if($mod){
            recordlog($user['account'],'3','添加失败，'.$cname.'该课程已存在课程列表中!');
            return json([
                "status" => 0,
                "info" => '添加失败，该课程已存在课程列表中',
            ]);
        }
        $data['status'] = input('status',0);
        $data['selected'] = input('selected',0);
        $data['notselected'] = input('notselected',$notselected);
        // dump($data);die;
        
        $res = db('course')->insert($data);
        if($res){
            recordlog($user['account'],'3','添加成功，'.$cname.'该课程成功添加到课程列表中!');
            return json([
                "status" => 1,
                "info" => '添加课程成功',
            ]);
        }else{
            return json([
                "status" => 0,
                "info" => '添加失败，请刷新重试',
            ]);
        }
        
        
       // 	展开侧边栏
        $this -> assign('selected_id',4);
	    return $this->fetch();
    }
    
    // 编辑课程状态
    public function  editCourseStatus()
    {
        $user = session('user');
        $data = input('post.');
        
        if(!$this->isEditPower()){
            return json([
                "status" => 0,
                "info" => '您还没有编辑权限，请联系管理员添加！',
            ]);
        }
        
        // dump($data);die;
        $course = db('course')->where('cid',$data['cid'])->find();
        $res = db('course')->where('cid',$data['cid'])->setField('status',$data['status']);
        if($res){
            recordlog($user['account'],'6','课程状态修改成功，'.$course['cname'].'该课程状态已被修改!');
            return json([
                "status" => 1,
                "info" => '课程状态修改成功',
            ]);
        }else{
            return json([
                "status" => 0,
                "info" => '课程状态修改失败，请刷新重试',
            ]);
        }
        
        
       // 	展开侧边栏
        $this -> assign('selected_id',4);
	    return $this->fetch();
    }
    
    // 	根据id获取课程信息
    public function getCourseOne()
    {
        $user = session('user');
        $cid = input('post.cid');
        
        
        // dump($cid);die;
        $res = db('course') -> where('cid',$cid) -> find();
        if($res){
            recordlog($user['account'],'9','查询成功，获取到一条关于'.$res['cname'].'课程的信息！');
            return json([
                "status" => 1,
                "info" => '查询成功',
                "data" => $res
            ]);
        }else{
            return json([
                "status" => 0,
                "info" => '网络错误，查询失败',
            ]);
        }
    }
    
    // 	id编辑课程信息
    public function editCourse()
    {
        $user = session('user');
        $cid = input('cid');
        $flag = input('flag');
        $data = input('post.');
        // dump($flag);die;
        
         if(!$this->isEditPower()){
            return json([
                "status" => 0,
                "info" => '您还没有编辑权限，请联系管理员添加！',
            ]);
        }
        
        // 课程已改动
        if(!$flag){
            $res = db('course')->where('cname',$data['cname'])->find();
            if(!empty($res)){
                recordlog($user['account'],'4','编辑失败，'.$res['cname'].'该课程名称已存在！');
                return json([
                    "status" => 0,
                    "info" => '该课程名称已存在，请重试'
                ]);
            }
        }
        
        $oldCname =db('course') -> where('cid',$cid) -> value('cname');
        $selected = db('course') ->where('cid',$cid) -> value('selected');
        $data['notselected'] = input('number') - $selected;
        $res = db('course') -> where('cid',$cid) -> update($data);
        if($res){
            recordlog($user['account'],'4','编辑成功，'.$oldCname.'该课程信息已被修改！');
            return json([
                "status" => 1,
                "info" => '数据更新成功',
            ]);
        }else{
            return json([
                "status" => 0,
                "info" => '数据暂无改动',
            ]);
        }
    }
    
    // 根据课程名称搜课程
    public function  searchCourseName()
    {
        $user = session('user');
        $keyword = input('value','');
        
        if(!$this->isSearchPower()){
            $result = db('user') ->select();
            return json([
                "status" => 0,
                "info" => '您还没有搜索权限，请联系管理员添加！',
            ]);
        }
        
        $res = db('course')->where('cname','like','%'.$keyword.'%')->select();
        
        // dump($res);die;
        if($res){
            recordlog($user['account'],'9','查询课程成功，已成功为你查询到'. count($res) . '条数据');
            return json([
                "status" => 1,
                "info" => '已成功为你查询到'. count($res) . '条数据',
                "data" => $res
            ]);
        }else{
            return json([
                "status" => 0,
                "info" => '未查询到任何数据',
            ]);
        }
    }
    
    // typeahead根据课程名称搜课程
    public function  getCourseList()
    {
        $user = session('user');
        $keyword = input('value','');
        
        $res = db('course')->where('cname','like','%'.$keyword.'%')->select();
        
        // dump($res);die;
        if($res){
            return json([
                "status" => 1,
                "info" => '已成功为你查询到'. count($res) . '条数据',
                "data" => $res
            ]);
        }else{
            return json([
                "status" => 0,
                "info" => '未查询到任何数据',
            ]);
        }
    }
    
    // 根据id删除课程
    public function  deleteCourse()
    {
        $user = session('user');
        $data = input('post.');
        
        
        if(!$this->isDeletePower()){
            return json([
                "status" => 0,
                "info" => '您还没有删除权限，请联系管理员添加！'
            ]);
        }
        
        // dump($data);die;
        $course = db('course')->where('cid',$data['cid'])->find();
        $res = db('course')->where('cid',$data['cid'])->delete();
        if($res){
            recordlog($user['account'],'6','课程删除成功，'.$course['cname'].'该课程已被删除!');
            return json([
                "status" => 1,
                "info" => '课程删除成功',
            ]);
        }else{
            return json([
                "status" => 0,
                "info" => '课程删除失败，请刷新重试',
            ]);
        }
        
        
       // 	展开侧边栏
        $this -> assign('selected_id',4);
	    return $this->fetch();
    }
    
     // 批量删除课程
    public function  deleteCourseMuch()
    {
       $user = session('user');
        $ids = input('cid');
        
        if(!$this->isDeletePower()){
            return json([
                "status" => 0,
                "info" => '您还没有删除权限，请联系管理员添加！'
            ]);
        }
        
        // dump(explode(',',$ids));die;
        $ids = explode(',',$ids);
        foreach ($ids as $id){
                $mod = db('course')->where('cid',$id) -> delete();
        }
        
        if($mod){
            recordlog($user['account'],'5','成功批量删除课程数据');
            return json([
                "status" => 1,
                "info" => '批量删除成功',
            ]);
        }else{
            return json([
                "status" => 0,
                "info" => '网络错误，批量删除失败',
            ]);
        }
    }
    
    
    // 渲染发布管理
    public function  issueManage()
    {
        $user = session('user');
        $this->assign('userInfo',$user);
        
        $this->isShowPower();
        
        $course = db('course')->where('status=1')->select();
        // dump($course);die;
        foreach ($course as $k=>$v){
            $teacher = db('teacher')->where('course',$v['cid'])->value('name');
            $course[$k]['teacher'] = $teacher;
        }
        // dump($course);die;
        
        $year = db('year')->select();
        $college = db('college')->select();
        $major = db('major')->select();
        
        $this->assign('years',$year);
        $this->assign('colleges',$college);
        $this->assign('majors',$major);
        
        $this->assign('courses',$course);
        
        // 选课时间
        $time = db('time')->find();
        // dump($time);die;
        $this -> assign('start_time',$time['start_time']);
        $this -> assign('end_time',$time['end_time']);
        
       // 	展开侧边栏
        $this -> assign('selected_id',5);
	    return $this->fetch();
    }
    
    // 筛选课程
    public function  accordConditionSearchCourse()
    {
        $user = session('user');
        
        $data = input('post.');
        
        // 讨论筛选情况
        //  0 0 0 
        if($data['cyear']==0 && $data['college']==0 && $data['major']==0){
            $course = db('course')->where('status',1)->select();
        }
        
        // 1 0 0
        if($data['cyear']!=0 && $data['college']==0 && $data['major']==0){
            $course = db('course')->where('status',1)->where('cyear',$data['cyear'])->select();
        }
        
        // 0 1 0
        if($data['cyear']==0 && $data['college']!=0 && $data['major']==0){
            $course = db('course')->where('status',1)->where('college',$data['college'])->select();
        }
        
        // 0 0 1
        if($data['cyear']==0 && $data['college']==0 && $data['major']!=0){
            $course = db('course')->where('status',1)->where('major',$data['major'])->select();
        }
        
        // 1 1 0
        if($data['cyear']!=0 && $data['college']!=0 && $data['major']==0){
            $course = db('course')->where('status',1)->where('cyear',$data['cyear'])->where('college',$data['college'])->select();
        }
        
        // 0 1 1
        if($data['cyear']==0 && $data['college']!=0 && $data['major']!=0){
            $course = db('course')->where('status',1)->where('college',$data['college'])->where('major',$data['major'])->select();
        }
        
        // 1 0 1
        if($data['cyear']!=0 && $data['college']==0 && $data['major']!=0){
            $course = db('course')->where('status',1)->where('cyear',$data['cyear'])->where('major',$data['major'])->select();
        }
        
        // 1 1 1
        if($data['cyear']!=0 && $data['college']==0 && $data['major']!=0){
            $course = db('course')->where('status',1)->where('cyear',$data['cyear'])->where('college',$data['college'])->where('major',$data['major'])->select();
        }
        
        
        //   dump($course);die;
        
        if($course){
            foreach ($course as $k=>$v){
                $teacher = db('teacher')->where('course',$v['cid'])->value('name');
                $course[$k]['teacher'] = $teacher;
            }
            recordlog($user['account'],'8','筛选课程成功，筛选到'. count($course) . '条数据！');
            return json([
                "status" => 1,
                "info" => '已成功为你查询到'. count($course) . '条数据',
                "data" => $course
            ]);
        }else{
            return json([
                "status" => 0,
                "info" => '暂无数据',
            ]);
        }
      
        
       
    }
    
    // 搜索课程
    public function  searchCourse()
    {
        
         $user = session('user');
        $keyword = input('value','');
        
        if(!$this->isSearchPower()){
            $result = db('user') ->select();
            return json([
                "status" => 0,
                "info" => '您还没有搜索权限，请联系管理员添加！',
            ]);
        }
        
        $course = db('course')->where('cname','like','%'.$keyword.'%')->select();
        
        // dump($course);die;
        if($course){
            foreach ($course as $k=>$v){
                $teacher = db('teacher')->where('course',$v['cid'])->value('name');
                $course[$k]['teacher'] = $teacher;
            }
            recordlog($user['account'],'9','查询课程成功，已成功为你查询到'. count($course) . '条数据');
            return json([
                "status" => 1,
                "info" => '已成功为你查询到'. count($course) . '条数据',
                "data" => $course
            ]);
        }else{
            return json([
                "status" => 0,
                "info" => '未查询到任何数据',
            ]);
        }
    }
    
    // 设置课程时间
    public function  setClassTime()
    {
        
        $user = session('user');
        $data = input('post.');
        // dump($data);die;
        
         if(!$this->isEditPower()){
            return json([
                "status" => 0,
                "info" => '您还没有编辑权限，请联系管理员添加！',
            ]);
        }
        
        $time = db('time')->select();
        if($time){
            $time = db('time')->where('id',1)->update($data);
            
            if($time){
                recordlog($user['account'],'4','成功修改开放选课时间段！');
                return json([
                    "status" => 1,
                    "info" => '成功修改开放选课时间段',
                ]);
            }else{
                return json([
                    "status" => 0,
                    "info" => '设置开放选课时间段失败',
                ]);
            }
        }else{
            $time = db('time')->insert($data);
            
            if($time){
                recordlog($user['account'],'3','成功设置开放选课时间段！');
                return json([
                    "status" => 1,
                    "info" => '成功设置开放选课时间段',
                ]);
            }else{
                return json([
                    "status" => 0,
                    "info" => '设置开放选课时间段失败',
                ]);
            }
        }
        
        
    }
    
    
    
    
    // 渲染专业页面
    public function  majorManage()
    {
        
        $user = session('user');
        $this->assign('userInfo',$user);
        $this->isShowPower();
        
        $majors = db('major')->select();
        $this->assign('majors',$majors);
        
        
       // 	展开侧边栏
        $this -> assign('selected_id',6);
	    return $this->fetch();
    }
    
    // 搜专业
    public function searchMajor()
    {
        $user = session('user');
        $keyword = input('value','');
        
        if(!$this->isSearchPower()){
            $result = db('user') ->select();
            return json([
                "status" => 0,
                "info" => '您还没有搜索权限，请联系管理员添加！',
            ]);
        }
        
        $res = db('major')->where('name','like','%'.$keyword.'%')->select();
        
        // dump($res);die;
        if($res){
            recordlog($user['account'],'9','查询专业成功，已成功为你查询到'. count($res) . '条数据!');
            return json([
                "status" => 1,
                "info" => '已成功为你查询到'. count($res) . '条数据',
                "data" => $res
            ]);
        }else{
            return json([
                "status" => 0,
                "info" => '未查询到任何数据',
            ]);
        }
    }
    
    // 渲染院系
    public function  collegeManage()
    {
        $user = session('user');
        $this->assign('userInfo',$user);
        
        $this->isShowPower();
        
        $colleges = db('college')->select();
        $this->assign('colleges',$colleges);
       // 	展开侧边栏
        $this -> assign('selected_id',7);
	    return $this->fetch();
    }
    
    // 搜院系
    public function searchCollege()
    {
        $user = session('user');
        $keyword = input('value','');
        
        if(!$this->isSearchPower()){
            $result = db('user') ->select();
            return json([
                "status" => 0,
                "info" => '您还没有搜索权限，请联系管理员添加！',
            ]);
        }
        
        $res = db('college')->where('name','like','%'.$keyword.'%')->select();
        
        // dump($res);die;
        if($res){
            recordlog($user['account'],'9','查询院系成功，已成功为你查询到'. count($res) . '条数据!');
            return json([
                "status" => 1,
                "info" => '已成功为你查询到'. count($res) . '条数据',
                "data" => $res
            ]);
        }else{
            return json([
                "status" => 0,
                "info" => '未查询到任何数据',
            ]);
        }
    }
     
     
     /**
     * 
     * 权限管理>权限管理接口
     * 渲染权限页面
     * 对角色信息的增删改查
     * 根据关键字搜索数据
     * 
     */
     /*日志操作 事件
      * 1 登录 2 手机验证 3 添加 4 编辑 5 删除 6 启用禁用 7 密码重置 8 搜索 9 查询
      *
      */
    // 权限
    public function  powerManage()
    {
        $user = session('user');
        $this->assign('userInfo',$user);
        
        $this->isShowPower();
        
        $power = db('power') -> select();
        $this -> assign('powers',$power);
       // 	展开侧边栏
        $this -> assign('selected_id',8);
	    return $this->fetch();
    }
    
    // 角色
    public function  roleManage()
    {
        $user = session('user');
        $this->assign('userInfo',$user);
        
        $this->isShowPower();
        
        $role = db('role') -> select();
        // dump($role);die;
        $this -> assign('roles',$role);
        
       // 	展开侧边栏
        $this -> assign('selected_id',9);
	    return $this->fetch();
    }
    
    // 添加角色
    public function  addRole()
    {
        $user = session('user');
        
        $data['role_name'] = input('role_name');
        $data['power'] = input('power');
        
        if($user['account'] != 'admin'){
            return json([
                "status" => 0,
                "info" => '仅限admin超级管理员添加角色！'
            ]);
        }
        
        // dump($data);die;
        
        $res = db('role')->insert($data);
        if($res){
            recordlog($user['account'],'3','添加成功，'.$data['role_name'].'已添加到角色列表中！');
            return json([
               "status" => 1 ,
               "info" => '添加角色成功'
            ]);
        }else{
            return json([
               "status" => 1 ,
               "info" => '网络错误，添加角色失败'
            ]);
        }
        
    }
    
    // 删除角色
    public function  deleteRole()
    {
        $user = session('user');
        
        $rid = input('rid');
        
        
        if($user['account'] != 'admin'){
            return json([
                "status" => 0,
                "info" => '仅限admin超级管理员删除角色！'
            ]);
        }
        
        if($rid == 1 || $rid == 2 || $rid == 3){
             recordlog($user['account'],'3','删除失败，该角色规定不能被删除！');
            return json([
               "status" => 0,
               "info" => '删除角色失败，该角色规定不能被删除'
            ]);
        }
        // dump($rid);die;
        $role_name = db('role')->where('rid',$rid)->value('role_name');
        $res = db('role')->where('rid',$rid)->delete();
        if($res){
            recordlog($user['account'],'3','删除成功，'.$role_name.'已从角色列表中删除！');
            return json([
               "status" => 1,
               "info" => '删除角色成功'
            ]);
        }else{
            return json([
               "status" => 0,
               "info" => '网络错误，删除角色失败'
            ]);
        }
        
    }
    
    // 根据id获取角色信息
    public function getRoleOne()
    {
        $user = session('user');
        $rid = input('rid');
        // dump($id);die;
         if($rid == 1 || $rid == 2 || $rid == 3){
             recordlog($user['account'],'3','编辑失败，该角色规定不能被编辑！');
            return json([
               "status" => 0,
               "info" => '编辑失败，该角色规定不能被编辑'
            ]);
        }
        $res = db('role')->where('rid',$rid)->find();
        if($res){
            recordlog($user['account'],'9','查询成功，已查询到一条关于'.$res['role_name'].'角色信息！');
            return json([
                "status" => 1,
                "info" => '查询成功',
                "data" => $res
            ]);
        }else{
            return json([
                "status" => 0,
                "info" => '查询失败'
            ]);
        }
    }
    
    // 根据rid编辑角色信息
    public function editRole()
    {
        $user = session('user');
        $rid = input('rid');
        
         if($user['account'] != 'admin'){
            return json([
                "status" => 0,
                "info" => '仅限admin超级管理员编辑角色！'
            ]);
        }
        
        $data['role_name'] = input('role_name');
        $data['power'] = input('power');
        // dump($data);die;
        $res = db('role')->where('rid',$rid)->update($data);
        if($res){
            recordlog($user['account'],'4','编辑成功，'.$data['role_name'].'的角色信息已被修改！');
            return json([
                "status" => 1,
                "info" => '编辑成功',
                "data" => $res
            ]);
        }else{
            return json([
                "status" => 0,
                "info" => '数据暂无改动'
            ]);
        }
    }
    
    /**
     * 
     * 评价管理>评价管理接口
     * 渲染数据课程、教师评价页面
     * 
     * 
     */
     
     // 课程评价
	public function  coursePj()
    {
        $user = session('user');
        $this->assign('userInfo',$user);
        
        $this->isShowPower();
        
        $coursepj = db('coursepj')->select();
        if($coursepj){
            foreach ($coursepj as $k=>$v){
                $course_name = db('course')->where('cid',$coursepj[$k]['course'])->value('cname');
                $student_name = db('student') ->where('account',$coursepj[$k]['student'])->value('name');
                $coursepj[$k]['course_name'] =$course_name;
                $coursepj[$k]['student_name'] =$student_name;
            }
             $this -> assign('coursepj',$coursepj);
            //  dump($coursepj);die;
        }else{
            $this -> assign('coursepj',$coursepj);
        }
        
        
       // 	展开侧边栏
        $this -> assign('selected_id',15);
	    return $this->fetch();
    }
    
    // 教师评价
	public function  teacherPj()
    {
        $user = session('user');
        $this->assign('userInfo',$user);
        
        $this->isShowPower();
        
        $teacherpj = db('teacherpj')->select();
        if($teacherpj){
            foreach ($teacherpj as $k=>$v){
                $teacher_name = db('teacher')->where('id',$teacherpj[$k]['teacher'])->value('name');
                $student_name = db('student') ->where('account',$teacherpj[$k]['student'])->value('name');
                $teacherpj[$k]['teacher_name'] =$teacher_name;
                $teacherpj[$k]['student_name'] =$student_name;
            }
             $this -> assign('teacherpj',$teacherpj);
            //  dump($teacherpj);die;
        }else{
            $this -> assign('teacherpj',$teacherpj);
        }
        
       // 	展开侧边栏
        $this -> assign('selected_id',16);
	    return $this->fetch();
    }
    
    
    /**
     * 
     * 邮件管理>发送邮件
     * 邮件管理页面
     * 
     * 
     */
	public function  mailManage()
    {
        $user = session('user');
        $this->assign('userInfo',$user);
        
        $this->isShowPower();
        
       // 	展开侧边栏
        $this -> assign('selected_id',18);
	    return $this->fetch();
    }
     
     /**
     * 
     * 数据统计>数据统计管理接口
     * 渲染数据echars页面
     * 
     * 
     */
     
	public function  dataManage()
    {
        $url = request()->domain();
        $user = session('user');
        $this->assign('userInfo',$user);
        
        $this->isShowPower();
        
        $managerCount = db('manager')->select();
        $studentCount = db('student')->select();
        $teacherCount = db('teacher')->select();
        $userCount = db('user')->select();
        $collegeCount = db('college')->select();
        $majorCount = db('major')->select();
        $courseCount = db('course')->select();
        $logCount = db('log')->select();
        $roleCount = db('role')->select();
        $powerCount = db('power')->select();
        
        $this -> assign('managerCount',count($managerCount));
        $this -> assign('studentCount',count($studentCount));
        $this -> assign('teacherCount',count($teacherCount));
        $this -> assign('userCount',count($userCount));
        $this -> assign('collegeCount',count($collegeCount));
        $this -> assign('majorCount',count($majorCount));
        $this -> assign('courseCount',count($courseCount));
        $this -> assign('logCount',count($logCount));
        $this -> assign('roleCount',count($roleCount));
        $this -> assign('powerCount',count($powerCount));
        
       // 	展开侧边栏
        $this -> assign('selected_id',10);
	    return $this->fetch();
    }
	
	
	/**
     * 
     * 系统管理>系统设置、系统概述、系统日志接口
     * 系统日志查看 批量删除 清空
     *  
     * 
     */
      
    public function  systemConfig()
    {
        $user = session('user');
        $this->assign('userInfo',$user);
        
        $this->isShowPower();
        
        $ip = request()->ip();
        $this->assign('ip',$ip);
        
       // 	展开侧边栏
        $this -> assign('selected_id',11);
	    return $this->fetch();
    }
    
    // 渲染系统公告
    public function  systemNotice()
    {
        $user = session('user');
        $this->assign('userInfo',$user);
        
        $this->isShowPower();
        
        $res = db('notice')->find();
        if($res){
            $this->assign('content',$res['content']);
        }else{
            $this->assign('content','');
        }
        
       // 	展开侧边栏
        $this -> assign('selected_id',12);
	    return $this->fetch();
    }
    
    // 获取公告
    public function  getNotice()
    {
        $user = session('user');
        
	    $res = db('notice')->find();
        if($res){
            return json([
                "status" => 1,
                "info" => '获取成功',
                'data' => $res
            ]);
        }else{
            return json([
                "status" => 0,
                "info" => '暂无数据'
            ]);
        }
    }
    
    // 清空公告
    public function  clearNotice()
    {
        $user = session('user');
        
        if(!$this->isClearPower()){
            return json([
                "status" => 0,
                "info" => '您还没有清空权限，请联系管理员添加！'
            ]);
        }
        
	    $res = db('notice')->find();
        if($res){
            $res = db('notice') -> where('id',$res['id'])->setField('content','');
            return json([
                "status" => 1,
                "info" => '清空成功',
                'data' => $res
            ]);
        }else{
            return json([
                "status" => 0,
                "info" => '暂无数据'
            ]);
        }
    }
    
    
    // 保存公告
    public function  saveNotice()
    {
        $user = session('user');
        $data = input();
        
        if(!$this->isAddPower()){
            return json([
                "status" => 0,
                "info" => '您还没有添加权限，请联系管理员添加！'
            ]);
        }
        
	    $res = db('notice')->find();
	    if($res){
	        $mod  = db('notice') -> where('id',$res['id']) -> update($data);
	    }else{
	        $mod  = db('notice') -> insert($data);
	    }
        if($mod){
            recordlog($user['account'],'4','编辑成功，公告已被修改！');
            return json([
                "status" => 1,
                "info" => '编辑成功',
                "data" => $res
            ]);
        }else{
            return json([
                "status" => 0,
                "info" => '数据暂无改动'
            ]);
        }
    }
    
    
    // 系统概述
    public function  systemIntroduction()
    {
        $user = session('user');
        $this->assign('userInfo',$user);
        
        $this->isShowPower();
        
       // 	展开侧边栏
        $this -> assign('selected_id',13);
	    return $this->fetch();
    }
    
    
    // 渲染日志页面
    public function  systemLog()
    {
        
        $user = session('user');
        $this->assign('userInfo',$user);
        
        $mod=db('log')->select();
        
        $this->isShowPower();
        
        $this->assign('count',count($mod));
        $this->assign('logs',$mod);
        
       // 	展开侧边栏
        $this -> assign('selected_id',14);
	    return $this->fetch();
    }
    
    // 批量删除日志
    public function deleteMuchLog()
    {
        $user = session('user');
        $ids = input('id');
        
        if(!$this->isDeletePower()){
            return json([
                "status" => 0,
                "info" => '您还没有删除权限，请联系管理员添加！'
            ]);
        }
        
        // dump(str_split($ids));die;
        $ids = explode(',',$ids);
        foreach ($ids as $id){
            $mod = db('log')->where('id',$id) -> delete(); 
        }
        
        if($mod){
            recordlog($user['account'],'5','成功批量删除日志数据');
            return json([
                "status" => 1,
                "info" => '删除成功',
            ]);
        }else{
            return json([
                "status" => 0,
                "info" => '网络错误，删除失败',
            ]);
        }
    }
    
    // 清空日志
    public function  clearLog()
    {
        
        $user = session('user');
        
        if(!$this->isClearPower()){
            return json([
                "status" => 0,
                "info" => '您还没有清空权限，请联系管理员添加！'
            ]);
        }
        
        // if(!($user['account'] == 'admin' && $user['account']==md5(8834760))){
        //     return json([
        //         "status"=>0,
        //         "info"=>'您没有权限清空系统日志'
        //     ]);
        // }
       
        $mod = db('log')->delete(true);
        if($mod){
            return json([
                "status"=>1,
                "info"=>'成功清空系统日志'
            ]);
        }else{
            return json([
                "status"=>0,
                "info"=>'网络错误，请重试'
            ]);
        }
    }
	
	
	
		/**
     * 
     * 回收站>回收站接口
     * 将删除的数据恢复
     *  
     * 
     */
     public function recovery()
     {
         $user = session('user');
         
         $this->assign('userInfo',$user);
         
         // 	展开侧边栏
        $this -> assign('selected_id',17);
	    return $this->fetch();
     }
     
     
     
}
