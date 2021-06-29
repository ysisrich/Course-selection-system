<?php
namespace app\index\controller;
use app\index\controller\Base;
use think\Cookie;
use think\Session;
class Student extends Base
{
	// 学生
	public function index()
	{
		// 颜色配置
		if(empty(session('config_color')))
			$this->assign('config_color','#009688');
		else 
			$this->assign('config_color',session('config_color'));
			
		if(!empty(session('user')) && session('user')['type'] == 0)
			$this -> redirect('index/index');
		if(!empty(session('user')) && session('user')['type'] == 1)
			$this->assign('userInfo',session('user'));
		if(!empty(session('user')) && session('user')['type'] == 2)
			$this -> redirect('index/teacher/index');
				
		// 	展开侧边栏
        $this -> assign('selected_id',-1);
		return $this->fetch();
	}
	
	// 渲染个人信息页面
    public function getPersonalInformation()
    {
		$user = session('user');

		$student = db('student') ->where('account',$user['account'])-> find();
		// 与学院 学级 专业 联合查询
            
            $year=db('year')->where('id',$student['year'])->find();
            $student['year']=$year['name'];
            
            $major=db('major')->where('id',$student['major'])->find();
            $student['major']=$major['name'];
            
            $college=db('college')->where('id',$student['college'])->find();
            $student['college']=$college['name'];
            
            $grade=db('grade')->where('id',$student['grade'])->find();
            $student['grade']=$grade['name'];
            
		$this->assign('student',$student);
		
		$year=db('year')->select();
		$major=db('major')->select();
		$college=db('college')->select();
		$grade=db('grade')->select();
		
		$this->assign('year',$year);
		$this->assign('major',$major);
		$this->assign('college',$college);
		$this->assign('grade',$grade);
		
		
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
	    $mod = db('student') -> where('id',$data['id']) ->update($data);
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
	
	
	
    /**
	 * 
     * 课程浏览接口
     * 选择课程
     * 渲染课程浏览页面
     * 根据关键字搜索数据
     * 
     */
	
	
    // 渲染课程浏览页面
    public function selectCourse()
    {
        $user = session('user');
        
        
        $select_course_time = db('time')->find();
        // dump($select_course_time);die;
        $date = json_decode($select_course_time['start_time_data'],true);
        $date2 = json_decode($select_course_time['end_time_data'],true);
        $startTime = $date['year'].'-'.$date['month'].'-'.$date['date'].' '.$date['hours'].':'.$date['minutes'].':'.$date['seconds'];
        $endTime = $date2['year'].'-'.$date2['month'].'-'.$date2['date'].' '.$date2['hours'].':'.$date2['minutes'].':'.$date2['seconds'];
        $startTime = strtotime($startTime);
        $endTime = strtotime($endTime);
        
        $nowTime = time();
        
        // 当前时间是否在选课时间段
        if($nowTime>$startTime && $nowTime<$endTime){
            $this->assign('fitTime',1);
        }else{
            $this->assign('fitTime',0);
        }
        
        $student_year = db('student')->where('account',$user['account'])->value('year');
        $student_college = db('student')->where('account',$user['account'])->value('college');
        
        // 查询可选课程
        $course = db('course')->where('status=1')->where('cyear',$student_year)->where('college',$student_college)->select();
        // dump($course);die;
        foreach ($course as $k=>$v){
            $teacher = db('teacher')->where('course',$v['cid'])->value('name');
            $course[$k]['teacher'] = $teacher;
        }
        
        // 查询学生的课程
        $this->assign('isCourse',0);
        $student_course = db('student')->where('account',$user['account'])->value('selected_course');
        if($student_course){
            $this->assign('isCourse',$student_course);
            // foreach ($course as $k=>$v){
            //     if($student_course == $v['cid']){
            //         $this->assign('isCourse',$student_course);
            //     }
            // }
        }
        $this->assign('courses',$course);
        
        $this->assign('time',$select_course_time);
        
        // 	展开侧边栏
        $this -> assign('selected_id',1);
		$this->assign('userInfo',$user);
        return $this->fetch();   
    }
    
    
    // 查询课程剩余量
    public function selectCourseCount()
    {
        $user = session('user');
        $cid = input('cid');
        
        // 判断当前学生是否已选课
        $student_course = db('student')->where('account',$user['account'])->value('selected_course');
        if($student_course){
            return json([
    		    "status" => 0,
    		    "info" => '你已经成功选课，不能再选择其他课程！'
		    ]);
        }   
        
        // 查询可选课程的余量
        $course_count = db('course')->where('cid',$cid)->value('notselected');
        $course_selected_count = db('course')->where('cid',$cid)->value('selected');
        // dump($course_count);die;
        if($course_count == 0){
            return json([
    		    "status" => 0,
    		    "info" => '课程可选余量为0，请选择其他课程！'
		    ]);
        }else{
            $res = db('course')->where('cid',$cid)->setField('notselected',$course_count-1);
            $res = db('course')->where('cid',$cid)->setField('selected',$course_selected_count+1);
            $mod = db('student')->where('account',$user['account'])->setField('selected_course',$cid);
            
              if($res){
                    return json([
            		    "status" => 1,
            		    "info" => '选课成功',
        		    ]);
                }else{
                    return json([
            		    "status" => 0,
            		    "info" => '网路错误，请刷新重试'
        		    ]);
                }
        }
    }
    
    
    
    
     /**
	 * 
     * 我的课程接口
     * 
     */
	
	
    // 渲染我的课程页面
    public function myCourse()
    {
        $user = session('user');
        
        $student_course = db('student')->where('account',$user['account'])->value('selected_course');
        if($student_course){
            $teacher = db('teacher')->where('course',$student_course)->find();
            $course = db('course')->where('cid',$student_course)->find();
            $course['teacher'] = $teacher;
            $this->assign('course',$course);
            $this->assign('teacher',$teacher);
        }else{
            $this->assign('course',null);
            $this->assign('teacher',null);
        }
        
        // 	展开侧边栏
        $this -> assign('selected_id',2);
		$this->assign('userInfo',$user);
        return $this->fetch();   
    }
    
    
    
    
    /**
	 * 
     * 成绩查阅接口
     * 
     */
	
	
    // 渲染成绩查阅页面
    public function gradeCourse()
    {
        $user = session('user');
        
        $student = db('student')->where('account',$user['account'])->find();
        
        $course_name = db('course')->where('cid',$student['selected_course'])->value('cname');
        $course_score = db('course')->where('cid',$student['selected_course'])->value('score');
        $student['course_name'] = $course_name;
        $student['score'] = $course_score;
        // $student['course_score']=59;
        // dump($student);die;
        
        $this -> assign('student',$student);
        
        // 	展开侧边栏
        $this -> assign('selected_id',3);
		$this->assign('userInfo',$user);
        return $this->fetch();   
    }
    
    
    
    
    /**
	 * 
     * 课程评价接口
     * 
     */
	
	
    // 渲染课程评价页面
    public function evaluateCourse()
    {
        $user = session('user');
        
        
        $student_course = db('student')->where('account',$user['account'])->value('selected_course');
        if($student_course){
            $course = db('course')->where('cid',$student_course)->find();
            $coursepj = db('coursepj')->where('course',$student_course)->select();
            if($coursepj){
                foreach ($coursepj as $k=>$v){
                    $student_info = db('student')->where('account',$v['student'])->find();
                    $coursepj[$k]['student_info'] = $student_info;
                }
                // 课程评价平均分
                $course_score = db('coursepj')->where('course',$student_course)->column('score');
                $course_sum = 0;
                foreach ($course_score as $k=>$v){
                    $course_sum+=$v;
                }
                $course_avg = number_format($course_sum/count($course_score),2);
                
                $this->assign('courseAvg',$course_avg);
                $this->assign('coursepjs',$coursepj);
            }else{
                $this->assign('coursepjs',null);
            }
            
            $this->assign('course',$course);
            
        }else{
            $this->assign('course',null);
            $this->assign('coursepjs',null);
        }
        // dump($coursepj);die;
        
        // 	展开侧边栏
        $this -> assign('selected_id',4);
		$this->assign('userInfo',$user);
        return $this->fetch();   
    }
    
    // 课程评价
    public function submitCourseEvaluate()
    {
        $user = session('user');
        $data = input('post.');
        $data['student'] = $user['account'];
        $data['create_time'] = date('Y-m-d H:i:s',time());
        
        // dump($data);die;
        
        $student = db('coursepj')->where('student',$user['account'])->find();
        if($student){
            $res = db('coursepj')->where('student',$user['account'])->update($data);
        }else{
            $res = db('coursepj')->insert($data);
        }
        
        if($res){
            return json([
    		    "status" => 1,
    		    "info" => '评价成功!',
		    ]);
        }else{
            return json([
    		    "status" => 0,
    		    "info" => '网路错误，请刷新重试'
		    ]);
        }
    }
    
    
    
    
    
    
    
    /**
	 * 
     * 教师评价接口
     * 
     */
	
	
    // 渲染教师评价页面
    public function evaluateTeacher()
    {
        $user = session('user');
        
        $student_course = db('student')->where('account',$user['account'])->value('selected_course');
        if($student_course){
            $teacher = db('teacher')->where('course',$student_course)->find();
            $course = db('course')->where('cid',$student_course)->find();
            $teacherpj = db('teacherpj')->where('teacher',$teacher['id'])->select();
            if($teacherpj){
                foreach ($teacherpj as $k=>$v){
                    $student_info = db('student')->where('account',$v['student'])->find();
                    $teacherpj[$k]['student_info'] = $student_info;
                }
                 // 教师评价平均分
                $teacher_score = db('teacherpj')->where('teacher',$teacher['id'])->column('score');
                $teacher_sum = 0;
                foreach ($teacher_score as $k=>$v){
                    $teacher_sum+=$v;
                }
                $teacher_avg = number_format($teacher_sum/count($teacher_score),2);
                
                $this->assign('teacherAvg',$teacher_avg);
                $this->assign('teacherpjs',$teacherpj);
                
            }else{
                $this->assign('teacherpjs',null);
            }
            
            $this->assign('teacher',$teacher);
            
        }else{
            $this->assign('teacher',null);
            $this->assign('teacherpjs',null);
        }
        
        // 	展开侧边栏
        $this -> assign('selected_id',5);
		$this->assign('userInfo',$user);
        return $this->fetch();   
    }
    
     // 教师评价
    public function submitTeacherEvaluate()
    {
        $user = session('user');
        $data = input('post.');
        $data['student'] = $user['account'];
        $data['create_time'] = date('Y-m-d H:i:s',time());
        // dump($data);die;
        
        $student = db('teacherpj')->where('student',$user['account'])->find();
        if($student){
            $res = db('teacherpj')->where('student',$user['account'])->update($data);
        }else{
            $res = db('teacherpj')->insert($data);
        }
        
        if($res){
            return json([
    		    "status" => 1,
    		    "info" => '评价成功!',
		    ]);
        }else{
            return json([
    		    "status" => 0,
    		    "info" => '网路错误，请刷新重试'
		    ]);
        }
    }
    
    
	
}
