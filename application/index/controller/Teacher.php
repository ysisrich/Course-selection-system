<?php
namespace app\index\controller;
use app\index\controller\Base;
use think\Cookie;
use think\Session;
class Teacher extends Base
{
	// 教师
	public function index()
	{
		// 颜色配置
// 		if(empty(session('config_color')))
// 			$this->assign('config_color','#009688');
// 		else 
// 			$this->assign('config_color',session('config_color'));
			
		if(!empty(session('user')) && session('user')['type'] == 0)
			$this -> redirect('index/index');
		if(!empty(session('user')) && session('user')['type'] == 1)
			$this -> redirect('index/student/index');
		if(!empty(session('user')) && session('user')['type'] == 2)
			$this->assign('userInfo',session('user'));
			
		// 	展开侧边栏
        $this -> assign('selected_id',-1);
		return $this->fetch();
	}
	
	// 渲染个人信息页面
    public function getPersonalInformation()
    {
		$user = session('user');

		$teacher = db('teacher') ->where('account',$user['account'])->find();
		$course = db('course')->where('cid',$teacher['course'])->find();
		$teacher['course'] = $course['cname'];
		$this->assign('userMoreInfo',$teacher);
		
		$course = db('course')->select();
		$this->assign('courses',$course);
		
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
	    $mod = db('teacher') -> where('id',$data['id']) ->update($data);
	    if($mod){
	       // recordlog($user['account'],'4',$user['account'].'个人基本信息修改成功！');
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
	
	
	// 	学校公告
    public function schoolNotice()
    {
        $user = session('user');
        
        $res = db('notice')->find();
        if($res){
            $this->assign('content',$res['content']);
        }else{
            $this->assign('content','');
        }
        
        
        // 	展开侧边栏
        $this -> assign('selected_id',1);
        $this -> assign('userInfo',$user);
		return $this->fetch();
    }
	
	
	/**
	 * 
     * 学生浏览接口
     * 渲染学生浏览页面
     * 根据关键字搜索数据
     * 
     */
	
    // 	学生浏览
    public function myStudent()
    {
        $user = session('user');
        $course_id = db('teacher') -> where('account',$user['account']) -> value('course');
        $student = db('student') ->where('selected_course',$course_id) -> select();
        $course_name = db('course') -> where('cid',$course_id) -> value('cname');
        // dump($course_name);die;
        
        
        // 已有学生选择本门课程
        if($student){
            foreach ($student as $k=>$v){
                $major_name = db('major')->where('id',$student[$k]['major'])->value('name');
                $grade_name = db('grade')->where('id',$student[$k]['grade'])->value('name');
                $student[$k]['course_name'] = $course_name;
                $student[$k]['major_name'] = $major_name;
                $student[$k]['grade_name'] = $grade_name;
            }
            $this-> assign('students',$student);
        }else{
            $this-> assign('students',null);
        }
        
        
        // 	展开侧边栏
        $this -> assign('selected_id',2); 
        $this -> assign('userInfo',$user);
		return $this->fetch();
    }
    
    // 	获取学生信息
    public function getStudentInfo()
    {
        $user = session('user');
        $id = input('id');
        
        $student = db('student') ->where('id',$id) -> find();
        // dump($student);die;
        
        if($student['course_score']){
            return json([
               "status" => 0,
               "info" => "该学生已录入成绩，禁止重新录入成绩！"
            ]);
        }else{
            return json([
               "status" => 1,
               "info" => "获取成功！",
               "data" => $student
            ]);
        }
    }
    
    // 	设置学生成绩
    public function setGrade()
    {
        $user = session('user');
        $data = input('post.');
        
        
        $res = db('student') ->where('id',$data['id']) -> setField('course_score',$data['course_score']);
        
        if($res){
            return json([
               "status" => 1,
               "info" => "该学生成绩录入成功！"
            ]);
        }else{
            return json([
               "status" => 0,
               "info" => "网络错误，请重试！",
            ]);
        }
    }
    
    
    /**
	 * 
     * 我的课程接口
     * 渲染我的课程页面
     * 根据关键字搜索数据
     * 
     */
	
    // 	我的课程
    public function myCourse()
    {
        $user = session('user');
        
        $course_id = db('teacher') -> where('account',$user['account'])->value('course');
        $course = db('course') -> where('cid',$course_id)->find();
        $major_name = db('major')->where('id',$course['major'])->value('name');
        $course['major_name'] = $major_name;
        // dump($course);die;
        $this -> assign('course',$course);
        
        
        // 	展开侧边栏
        $this -> assign('selected_id',3);
        $this -> assign('userInfo',$user);
		return $this->fetch();
    }
    
    // 	编辑课程基本信息
    public function submitCourseInfoForm()
    {
        $user = session('user');
        $cid = input('cid');
        $data = input('post.');
        
        $res = db('course') -> where('cid',$cid)->update($data);
        
        if($res){
            return json([
               "status" => 1,
               "info" => "课程基本信息修改成功！"
            ]);
        }else{
            return json([
               "status" => 0,
               "info" => "网络错误，请重试！",
            ]);
        }
        
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
        
        
        
       // 	展开侧边栏
        $this -> assign('selected_id',4);
	    return $this->fetch();
    }
    
}
