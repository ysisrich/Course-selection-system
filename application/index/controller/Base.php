<?php
namespace app\index\controller;
use think\Controller;


use PHPMailer\SMTP;
use PHPMailer\PHPMailer;
use PHPMailer\Exception;

class Base extends Controller
{
    public function _initialize()
    {
        // dump(session(''));die;
		// 是否有登录信息
       if(empty(session('user'))){
            // $this->error('登录失效,请重新登录','index/login/index');
       	    $this -> redirect('index/login/index');
       }
       
       
       // 颜色配置
    	if(empty(session('config_color'))){
    	    session('config_color','#0E4EA8');
    	    $this->assign('config_color','#0E4EA8');
    	}else{
    	    $this->assign('config_color',session('config_color'));
    	} 
    		
    }
    
    // 向手机发送验证码
	public function sendForgetCode()
	{
	    $user = session('user');
	    $type=input('type');
		$mobile = input('phone');
		$res=db('student')->where('phone',$mobile)->find();
		$res1=db('manager')->where('phone',$mobile)->find();
		$res2=db('teacher')->where('phone',$mobile)->find();
		
		if(empty($res)&&empty($res1)&&empty($res2)){
			cookie('forgetcode',null);
			recordlog($user['account'],'2',$mobile.'验证失败，未找到该手机号！');
			return json([
				"status" =>0,
				"info" => '未找到该手机号，请联系管理员修改'
			]);
		}
		
		if($type==0){
		    $phone_check=db('manager')->where('phone',$mobile)->value('phone_check');
		}elseif($type==1){
		    $phone_check=db('student')->where('phone',$mobile)->value('phone_check');
		}else{
		    $phone_check=db('teacher')->where('phone',$mobile)->value('phone_check');
		}
		
		if($phone_check){
		    recordlog($user['account'],'2',$mobile.'验证失败，该手机号已验证！');
		    return json([
				"status" =>0,
				"info" => '该手机号已验证，请勿重复操作'
			]);
		}
		

		$aliyun = new Aliyun();
        // $aliyun = new tencentcloud();
		$code = rand(1000, 9999);
		$res = $aliyun->sendCode($mobile, $code);
   
		if ($res['flag']) {
			cookie('forgetcode',$code);
			recordlog($user['account'],'2',$mobile.'验证中，短信发送成功！');
			return json([
				"status" =>1,
				"info" => '短信发送成功'
			]);
		} else {
		    recordlog($user['account'],'2',$mobile.'验证失败，'.$res['info'].'！');
			return json([
				"status" =>0,
				"info" => $res['info']
			]);
		}
	}
	
	 // 比对验证码
	public function checkCode(){
	    $user = session('user');
		$code = input('code');
		$id = input('id');
		$type = input('type');
		
		if($code!=cookie('forgetcode')){
		    recordlog($user['account'],'1',$user['account'].'验证码错误！');
			return json([
				"status" =>0,
				"info" => '验证码错误'
			]);
		}else {
		    if($type == 0)
		        db('manager') -> where('id',$id) ->setField('phone_check',1);
		    elseif($type == 1)
		        db('student') -> where('id',$id) ->setField('phone_check',1);
		    else
		        db('teacher') -> where('id',$id) ->setField('phone_check',1);
		        
		        recordlog($user['account'],'1','手机号验证成功！');
			return json([
				"status" =>1,
				"info" => '手机号验证成功'
			]);
		}

	}
	
	
    
    // 	换肤
	public function changeSkin()
	{
		$index = input('index');
		$colors = ['#009688','#001C26','#8552a1','#0E4EA8','green','#009ad6','red','#303643'];
		session('config_color',$colors[$index]);
		return json([
			"status" => 1,
			"info" => '换肤成功'
		]);
	}
        
    // 退出登录
	public function logout()
	{
	    $user = session('user');
		session('user',null);
		recordlog($user['account'],'1',$user['account'].'已安全退出本系统！');
		return json([
			"status" => 1,
			"info" => "退出成功"
		]);
	}
	
	/*
	*
	* 上传文件
	*
	*
	*/
	public function upload(){
        // 获取表单上传文件 例如上传了001.jpg
        $file = request()->file('uploadFile');
        // 移动到框架应用根目录/public/uploads/ 目录下
        if($file){
            $info = $file->move(ROOT_PATH . 'public' . DS . 'uploads');
            if($info){
                // 成功上传后 获取上传信息
                // 输出 jpg
                // echo $info->getExtension();
                // 输出 20160820/42a79759f284b767dfcb2a0197904287.jpg
                // echo $info->getSaveName();
                // 输出 42a79759f284b767dfcb2a0197904287.jpg
                // echo $info->getFilename(); 
                // echo $info->getSaveName();
                return json([
                    'status' => 1,
                    'info' => '附件上传成功！',
                    'url' => $info->getSaveName()
                ]);
                
            }else{
                // 上传失败获取错误信息
                // echo $file->getError();
                return json([
                    'status' => 0,
                    'info' => '附件上传失败！',
                    'url' => $file->getError()
                ]);
            }
        }
    }
	
	/*
	*
	* 发送邮箱
	*
	*
	*/
	public function sendMail()
	{
	    $data = input('');
	    $attachment = input('attachment',null);
	   // dump($data);die;
	   
	    $mail = new PHPMailer(true);
        try {
            //Server settings
            $mail->SMTPDebug = 0;                      //Enable verbose debug output
            $mail->isSMTP();                                            //Send using SMTP
            $mail->Host       = 'smtp.qq.com';                     //Set the SMTP server to send through
            $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
            $mail->Username   = '2177209690@qq.com';                     //SMTP username
            
            // 2048400850  tuaofuoxuztpejcj
            $mail->Password   = 'nxewihpleposdjcb';                               //SMTP password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;         //Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` encouraged
            $mail->Port       = 587;                                    //TCP port to connect to, use 465 for `PHPMailer::ENCRYPTION_SMTPS` above
        
            //Recipients
            $mail->setFrom('2177209690@qq.com', 'yangsong');
            $mail->addAddress($data['maile'], '...');     //Add a recipient
            // $mail->addAddress('ellen@example.com');               //Name is optional
            // $mail->addReplyTo('info@example.com', 'Information');
            // $mail->addCC('cc@example.com');
            // $mail->addBCC('bcc@example.com');
        
            //Attachments
            // $mail->addAttachment('/var/tmp/file.tar.gz');         //Add attachments
            // $mail->addAttachment('./1.mp4', 'new.mp4');    //Optional name
            // 添加附件
            if($attachment) { 
              if(is_string($attachment)){
                is_file($attachment) && $mail->AddAttachment($attachment);
              }
              else if(is_array($attachment)){
                foreach ($attachment as $file) {
                  is_file($file) && $mail->AddAttachment($file);
                }
              }  
            }
            
            //Content
            $mail->isHTML(true);                                  //Set email format to HTML
            $mail->Subject = $data['subject'];
            $mail->Body    = $data['content'];
            // $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';
        
            $mail->send();
            
            return json([
                'status' => 1,
                'info' => '邮件发送成功！'
            ]);
            
        } catch (Exception $e) {
            // echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
                return json([
                'status' => 0,
                'info' => '邮件发送失败！',
                'error' => $mail->ErrorInfo
            ]);
            
        } 
	}
	
	
	/*
	*
	* 以个人邮箱 发送邮件
	*
	*
	*/
	public function sendMail1()
	{
	    $data = input('');
	    $attachment = input('attachment1',null);
	   // dump($data);die;
	   
	    $mail = new PHPMailer(true);
        try {
            //Server settings
            $mail->SMTPDebug = 0;                      //Enable verbose debug output
            $mail->isSMTP();                                            //Send using SMTP
            $mail->Host       = 'smtp.qq.com';                     //Set the SMTP server to send through
            $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
            $mail->Username   = $data['sent'];                     //SMTP username
            
            // 2048400850  tuaofuoxuztpejcj
            $mail->Password   = $data['pw'];                               //SMTP password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;         //Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` encouraged
            $mail->Port       = 587;                                    //TCP port to connect to, use 465 for `PHPMailer::ENCRYPTION_SMTPS` above
        
            //Recipients
            $mail->setFrom($data['sent'], ' ');
            $mail->addAddress($data['maile1'], '...');     //Add a recipient
            // $mail->addAddress('ellen@example.com');               //Name is optional
            // $mail->addReplyTo('info@example.com', 'Information');
            // $mail->addCC('cc@example.com');
            // $mail->addBCC('bcc@example.com');
        
            //Attachments
            // $mail->addAttachment('/var/tmp/file.tar.gz');         //Add attachments
            // $mail->addAttachment('./1.mp4', 'new.mp4');    //Optional name
            // 添加附件
            if($attachment) { 
              if(is_string($attachment)){
                is_file($attachment) && $mail->AddAttachment($attachment);
              }
              else if(is_array($attachment)){
                foreach ($attachment as $file) {
                  is_file($file) && $mail->AddAttachment($file);
                }
              }  
            }
            
            //Content
            $mail->isHTML(true);                                  //Set email format to HTML
            $mail->Subject = $data['subject1'];
            $mail->Body    = $data['content1'];
            // $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';
        
            $mail->send();
            
            return json([
                'status' => 1,
                'info' => '邮件发送成功！'
            ]);
            
        } catch (Exception $e) {
            // echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
                return json([
                'status' => 0,
                'info' => '邮件发送失败！',
                'error' => $mail->ErrorInfo
            ]);
            
        } 
	}
	
	
	/*
    *
    *
    * 学生表格导出处理
    *
    *
    */
    public function exportStudent(){
        //1.从数据库中取出数据
        $student = db('student') -> select();
        
        // 性别
        foreach ($student as $k=>$v){
            $student[$k]['sex'] = $v['sex'] == 0 ? '女' : '男';
        }
        
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
		}
        
        
        
        
        //2.加载PHPExcle类库
        vendor('PHPExcel.PHPExcel');
        //3.实例化PHPExcel类
        $objPHPExcel = new \PHPExcel();
        //4.激活当前的sheet表
        $objPHPExcel->setActiveSheetIndex(0);
        //5.设置表格头（即excel表格的第一行）
        $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('A1', 'ID')                      
                ->setCellValue('B1', '姓名')
                ->setCellValue('C1', '学号')
                ->setCellValue('D1', '性别')
                ->setCellValue('E1', '邮箱')
                ->setCellValue('F1', '手机号')
                ->setCellValue('G1', '学院')
                ->setCellValue('H1', '年级')
                ->setCellValue('I1', '专业')
                ->setCellValue('J1', '班级')
                ->setCellValue('K1', '已选课程')
                ->setCellValue('L1', '课程分数');
                
        //设置列水平居中
        $objPHPExcel->setActiveSheetIndex(0)->getStyle('A1')->getAlignment()
                    ->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
         $objPHPExcel->setActiveSheetIndex(0)->getStyle('B1')->getAlignment()
                    ->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
         $objPHPExcel->setActiveSheetIndex(0)->getStyle('C1')->getAlignment()
                    ->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
         $objPHPExcel->setActiveSheetIndex(0)->getStyle('D1')->getAlignment()
                    ->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
         $objPHPExcel->setActiveSheetIndex(0)->getStyle('E1')->getAlignment()
                    ->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
         $objPHPExcel->setActiveSheetIndex(0)->getStyle('F1')->getAlignment()
                    ->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
         $objPHPExcel->setActiveSheetIndex(0)->getStyle('G1')->getAlignment()
                    ->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
         $objPHPExcel->setActiveSheetIndex(0)->getStyle('H1')->getAlignment()
                    ->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
         $objPHPExcel->setActiveSheetIndex(0)->getStyle('I1')->getAlignment()
                    ->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
         $objPHPExcel->setActiveSheetIndex(0)->getStyle('J1')->getAlignment()
                    ->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
         $objPHPExcel->setActiveSheetIndex(0)->getStyle('K1')->getAlignment()
                    ->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
         $objPHPExcel->setActiveSheetIndex(0)->getStyle('L1')->getAlignment()
                    ->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        
        $objPHPExcel->setActiveSheetIndex(0)->getStyle('A')->getAlignment()
                    ->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
         $objPHPExcel->setActiveSheetIndex(0)->getStyle('B')->getAlignment()
                    ->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
         $objPHPExcel->setActiveSheetIndex(0)->getStyle('C')->getAlignment()
                    ->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
         $objPHPExcel->setActiveSheetIndex(0)->getStyle('D')->getAlignment()
                    ->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
         $objPHPExcel->setActiveSheetIndex(0)->getStyle('E')->getAlignment()
                    ->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
         $objPHPExcel->setActiveSheetIndex(0)->getStyle('F')->getAlignment()
                    ->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
         $objPHPExcel->setActiveSheetIndex(0)->getStyle('G')->getAlignment()
                    ->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
         $objPHPExcel->setActiveSheetIndex(0)->getStyle('H')->getAlignment()
                    ->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
         $objPHPExcel->setActiveSheetIndex(0)->getStyle('I')->getAlignment()
                    ->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
         $objPHPExcel->setActiveSheetIndex(0)->getStyle('J')->getAlignment()
                    ->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
         $objPHPExcel->setActiveSheetIndex(0)->getStyle('K')->getAlignment()
                    ->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
         $objPHPExcel->setActiveSheetIndex(0)->getStyle('L')->getAlignment()
                    ->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                    
        //设置单元格宽度
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('A')->setWidth(15);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('B')->setWidth(15);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('C')->setWidth(15);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('D')->setWidth(15);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('E')->setWidth(15);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('F')->setWidth(15);     
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('G')->setWidth(15);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('H')->setWidth(15);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('I')->setWidth(15);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('J')->setWidth(15);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('K')->setWidth(15);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('L')->setWidth(15);
        
        //6.循环刚取出来的数组，将数据逐一添加到excel表格。
        for($i=0;$i<count($student);$i++){
            $objPHPExcel->getActiveSheet()->setCellValue('A'.($i+2),$student[$i]['id']);//添加ID
            $objPHPExcel->getActiveSheet()->setCellValue('B'.($i+2),$student[$i]['name']);//添加姓名
            $objPHPExcel->getActiveSheet()->setCellValue('C'.($i+2),$student[$i]['account']);//添加年龄
            $objPHPExcel->getActiveSheet()->setCellValue('D'.($i+2),$student[$i]['sex']);//添加班级
            $objPHPExcel->getActiveSheet()->setCellValue('E'.($i+2),$student[$i]['email']);//添加电话
            $objPHPExcel->getActiveSheet()->setCellValue('F'.($i+2),$student[$i]['phone']);//添加邮箱
            $objPHPExcel->getActiveSheet()->setCellValue('G'.($i+2),$student[$i]['college']);//添加姓名
            $objPHPExcel->getActiveSheet()->setCellValue('H'.($i+2),$student[$i]['year']);//添加年龄
            $objPHPExcel->getActiveSheet()->setCellValue('I'.($i+2),$student[$i]['major']);//添加班级
            $objPHPExcel->getActiveSheet()->setCellValue('J'.($i+2),$student[$i]['grade']);//添加电话
            $objPHPExcel->getActiveSheet()->setCellValue('K'.($i+2),$student[$i]['selected_course']);//添加邮箱
            $objPHPExcel->getActiveSheet()->setCellValue('L'.($i+2),$student[$i]['course_score']);//添加邮箱
        }
        //7.设置保存的Excel表格名称
        $filename = '学生信息'.date('Ymd',time()).'.xls';
        //8.设置当前激活的sheet表格名称；
        $objPHPExcel->getActiveSheet()->setTitle('学生信息');
        //9.设置浏览器窗口下载表格
        header("Content-Type: application/force-download");  
        header("Content-Type: application/octet-stream");  
        header("Content-Type: application/download");  
        header('Content-Disposition:inline;filename="'.$filename.'"');  
        //生成excel文件
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        //下载文件在浏览器窗口
        $objWriter->save('php://output');
        exit;
    }
    
    
    /*
    *
    *
    * 教师表格导出处理
    *
    *
    */
    
    public function exportTeacher(){
        //1.从数据库中取出数据
        $teacher = db('teacher') -> select();
        
        // 性别
        foreach ($teacher as $k=>$v){
            $teacher[$k]['sex'] = $v['sex'] == 0 ? '女' : '男';
        }
        
        // 课程
        foreach ($teacher as $k=>$v){
            if($teacher[$k]['course']){
                $course_name = db('course')->where('cid',$v['course']) -> value('cname');
                $teacher[$k]['course'] = $course_name;
            }
        }
        
        
        //2.加载PHPExcle类库
        vendor('PHPExcel.PHPExcel');
        //3.实例化PHPExcel类
        $objPHPExcel = new \PHPExcel();
        //4.激活当前的sheet表
        $objPHPExcel->setActiveSheetIndex(0);
        //5.设置表格头（即excel表格的第一行）
        $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('A1', 'ID')                      
                ->setCellValue('B1', '姓名')
                ->setCellValue('C1', '工号')
                ->setCellValue('D1', '性别')
                ->setCellValue('E1', '邮箱')
                ->setCellValue('F1', '手机号')
                ->setCellValue('G1', '职称')
                ->setCellValue('H1', '所授课程');
                
        //设置列水平居中
        $objPHPExcel->setActiveSheetIndex(0)->getStyle('A1')->getAlignment()
                    ->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
         $objPHPExcel->setActiveSheetIndex(0)->getStyle('B1')->getAlignment()
                    ->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
         $objPHPExcel->setActiveSheetIndex(0)->getStyle('C1')->getAlignment()
                    ->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
         $objPHPExcel->setActiveSheetIndex(0)->getStyle('D1')->getAlignment()
                    ->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
         $objPHPExcel->setActiveSheetIndex(0)->getStyle('E1')->getAlignment()
                    ->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
         $objPHPExcel->setActiveSheetIndex(0)->getStyle('F1')->getAlignment()
                    ->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
         $objPHPExcel->setActiveSheetIndex(0)->getStyle('G1')->getAlignment()
                    ->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
         $objPHPExcel->setActiveSheetIndex(0)->getStyle('H1')->getAlignment()
                    ->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        
        $objPHPExcel->setActiveSheetIndex(0)->getStyle('A')->getAlignment()
                    ->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
         $objPHPExcel->setActiveSheetIndex(0)->getStyle('B')->getAlignment()
                    ->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
         $objPHPExcel->setActiveSheetIndex(0)->getStyle('C')->getAlignment()
                    ->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
         $objPHPExcel->setActiveSheetIndex(0)->getStyle('D')->getAlignment()
                    ->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
         $objPHPExcel->setActiveSheetIndex(0)->getStyle('E')->getAlignment()
                    ->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
         $objPHPExcel->setActiveSheetIndex(0)->getStyle('F')->getAlignment()
                    ->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
         $objPHPExcel->setActiveSheetIndex(0)->getStyle('G')->getAlignment()
                    ->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
         $objPHPExcel->setActiveSheetIndex(0)->getStyle('H')->getAlignment()
                    ->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                    
        //设置单元格宽度
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('A')->setWidth(15);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('B')->setWidth(15);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('C')->setWidth(15);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('D')->setWidth(15);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('E')->setWidth(15);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('F')->setWidth(15);     
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('G')->setWidth(15);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('H')->setWidth(35);
        
        //6.循环刚取出来的数组，将数据逐一添加到excel表格。
        for($i=0;$i<count($teacher);$i++){
            $objPHPExcel->getActiveSheet()->setCellValue('A'.($i+2),$teacher[$i]['id']);
            $objPHPExcel->getActiveSheet()->setCellValue('B'.($i+2),$teacher[$i]['name']);
            $objPHPExcel->getActiveSheet()->setCellValue('C'.($i+2),$teacher[$i]['account']);
            $objPHPExcel->getActiveSheet()->setCellValue('D'.($i+2),$teacher[$i]['sex']);
            $objPHPExcel->getActiveSheet()->setCellValue('E'.($i+2),$teacher[$i]['email']);
            $objPHPExcel->getActiveSheet()->setCellValue('F'.($i+2),$teacher[$i]['phone']);
            $objPHPExcel->getActiveSheet()->setCellValue('G'.($i+2),$teacher[$i]['position']);
            $objPHPExcel->getActiveSheet()->setCellValue('H'.($i+2),$teacher[$i]['course']);
        }
        //7.设置保存的Excel表格名称
        $filename = '教师信息'.date('Ymd',time()).'.xls';
        //8.设置当前激活的sheet表格名称；
        $objPHPExcel->getActiveSheet()->setTitle('教师信息');
        //9.设置浏览器窗口下载表格
        header("Content-Type: application/force-download");  
        header("Content-Type: application/octet-stream");  
        header("Content-Type: application/download");  
        header('Content-Disposition:inline;filename="'.$filename.'"');  
        //生成excel文件
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        //下载文件在浏览器窗口
        $objWriter->save('php://output');
        exit;
    }
    
    
    /*
    *
    *
    * 课程表格导出处理
    *
    *
    */
    public function exportCourse(){
        //1.从数据库中取出数据
        $course = db('course') -> select();
        
        
        // 课程
        foreach ($course as $k=>$v){
            if($v['type']==0 || $v['type']==1){
                $course[$k]['type'] = $v['type'] == 0 ? '线下' : '线上';
            }
        }
        
		// 学院		
		$college = db('college') -> select();
		if($college){
		   foreach ($course as $i=>$s){
    	       foreach($college as $k ) {
                    if ($k['id'] == $s['college']) {
                        $course[$i]['college']  =  $k['name'];
                    }
               }
    	    }
		}
		// 专业	
		$major = db('major') -> select();
		if($major){
		      foreach ($course as $i=>$s){
    	       foreach($major as $k ) {
                    if ($k['id'] == $s['major']) {
                        $course[$i]['major']  =  $k['name'];
                    }
               }
    	    }
		}
		// 年级	
		$year = db('year') -> select();
		if($year){
		      foreach ($course as $i=>$s){
    	       foreach($year as $k ) {
                    if ($k['id'] == $s['cyear']) {
                        $course[$i]['cyear']  =  $k['name'];
                    }
               }
    	    }
		}
		
        //2.加载PHPExcle类库
        vendor('PHPExcel.PHPExcel');
        //3.实例化PHPExcel类
        $objPHPExcel = new \PHPExcel();
        //4.激活当前的sheet表
        $objPHPExcel->setActiveSheetIndex(0);
        //5.设置表格头（即excel表格的第一行）
        $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('A1', 'ID')                      
                ->setCellValue('B1', '课程名称')
                ->setCellValue('C1', '学分')
                ->setCellValue('D1', '年级')
                ->setCellValue('E1', '学院')
                ->setCellValue('F1', '专业')
                ->setCellValue('G1', '类型')
                ->setCellValue('H1', '上课地点')
                ->setCellValue('I1', '已选')
                ->setCellValue('J1', '未选')
                ->setCellValue('K1', '总数');
                
        //设置列水平居中
        $objPHPExcel->setActiveSheetIndex(0)->getStyle('A1')->getAlignment()
                    ->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
         $objPHPExcel->setActiveSheetIndex(0)->getStyle('B1')->getAlignment()
                    ->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
         $objPHPExcel->setActiveSheetIndex(0)->getStyle('C1')->getAlignment()
                    ->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
         $objPHPExcel->setActiveSheetIndex(0)->getStyle('D1')->getAlignment()
                    ->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
         $objPHPExcel->setActiveSheetIndex(0)->getStyle('E1')->getAlignment()
                    ->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
         $objPHPExcel->setActiveSheetIndex(0)->getStyle('F1')->getAlignment()
                    ->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
         $objPHPExcel->setActiveSheetIndex(0)->getStyle('G1')->getAlignment()
                    ->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
         $objPHPExcel->setActiveSheetIndex(0)->getStyle('H1')->getAlignment()
                    ->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
         $objPHPExcel->setActiveSheetIndex(0)->getStyle('I1')->getAlignment()
                    ->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
         $objPHPExcel->setActiveSheetIndex(0)->getStyle('J1')->getAlignment()
                    ->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
         $objPHPExcel->setActiveSheetIndex(0)->getStyle('K1')->getAlignment()
                    ->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        
        $objPHPExcel->setActiveSheetIndex(0)->getStyle('A')->getAlignment()
                    ->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
         $objPHPExcel->setActiveSheetIndex(0)->getStyle('B')->getAlignment()
                    ->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
         $objPHPExcel->setActiveSheetIndex(0)->getStyle('C')->getAlignment()
                    ->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
         $objPHPExcel->setActiveSheetIndex(0)->getStyle('D')->getAlignment()
                    ->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
         $objPHPExcel->setActiveSheetIndex(0)->getStyle('E')->getAlignment()
                    ->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
         $objPHPExcel->setActiveSheetIndex(0)->getStyle('F')->getAlignment()
                    ->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
         $objPHPExcel->setActiveSheetIndex(0)->getStyle('G')->getAlignment()
                    ->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
         $objPHPExcel->setActiveSheetIndex(0)->getStyle('H')->getAlignment()
                    ->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
         $objPHPExcel->setActiveSheetIndex(0)->getStyle('I')->getAlignment()
                    ->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
         $objPHPExcel->setActiveSheetIndex(0)->getStyle('J')->getAlignment()
                    ->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
         $objPHPExcel->setActiveSheetIndex(0)->getStyle('K')->getAlignment()
                    ->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        //设置单元格宽度
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('A')->setWidth(15);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('B')->setWidth(25);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('C')->setWidth(15);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('D')->setWidth(15);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('E')->setWidth(25);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('F')->setWidth(25);     
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('G')->setWidth(15);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('H')->setWidth(15);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('I')->setWidth(15);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('J')->setWidth(15);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('K')->setWidth(15);
        
        //6.循环刚取出来的数组，将数据逐一添加到excel表格。
        for($i=0;$i<count($course);$i++){
            $objPHPExcel->getActiveSheet()->setCellValue('A'.($i+2),$course[$i]['cid']);
            $objPHPExcel->getActiveSheet()->setCellValue('B'.($i+2),$course[$i]['cname']);
            $objPHPExcel->getActiveSheet()->setCellValue('C'.($i+2),$course[$i]['score']);
            $objPHPExcel->getActiveSheet()->setCellValue('D'.($i+2),$course[$i]['cyear']);
            $objPHPExcel->getActiveSheet()->setCellValue('E'.($i+2),$course[$i]['college']);
            $objPHPExcel->getActiveSheet()->setCellValue('F'.($i+2),$course[$i]['major']);
            $objPHPExcel->getActiveSheet()->setCellValue('G'.($i+2),$course[$i]['type']);
            $objPHPExcel->getActiveSheet()->setCellValue('H'.($i+2),$course[$i]['place']);
            $objPHPExcel->getActiveSheet()->setCellValue('I'.($i+2),$course[$i]['selected']);
            $objPHPExcel->getActiveSheet()->setCellValue('J'.($i+2),$course[$i]['notselected']);
            $objPHPExcel->getActiveSheet()->setCellValue('K'.($i+2),$course[$i]['number']);
        }
        //7.设置保存的Excel表格名称
        $filename = '课程信息'.date('Ymd',time()).'.xls';
        //8.设置当前激活的sheet表格名称；
        $objPHPExcel->getActiveSheet()->setTitle('课程信息');
        //9.设置浏览器窗口下载表格
        header("Content-Type: application/force-download");  
        header("Content-Type: application/octet-stream");  
        header("Content-Type: application/download");  
        header('Content-Disposition:inline;filename="'.$filename.'"');  
        //生成excel文件
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        //下载文件在浏览器窗口
        $objWriter->save('php://output');
        exit;
    }
    
    
    
    /*
    *
    *
    * 日志导出处理
    *1 登录 2 手机验证 3 添加 4 编辑 5 删除 6 启用禁用 7 密码重置 8 搜索 9 查询
    *
    */
    
    public function exportLog(){
        //1.从数据库中取出数据
        $log = db('log') -> select();
        
        $type =['','登录','手机验证','添加','编辑','删除','启用禁用','密码重置','搜索','查询'];
        
        // 课程
        foreach ($log as $k=>$v){
            $log[$k]['type'] = $type[$log[$k]['type']];
        }
        
        //2.加载PHPExcle类库
        vendor('PHPExcel.PHPExcel');
        //3.实例化PHPExcel类
        $objPHPExcel = new \PHPExcel();
        //4.激活当前的sheet表
        $objPHPExcel->setActiveSheetIndex(0);
        //5.设置表格头（即excel表格的第一行）
        $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('A1', 'ID')                      
                ->setCellValue('B1', '管理员账号')
                ->setCellValue('C1', '操作类型')
                ->setCellValue('D1', '操作事件')
                ->setCellValue('E1', '操作时间');
                
        //设置列水平居中
        $objPHPExcel->setActiveSheetIndex(0)->getStyle('A1')->getAlignment()
                    ->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
         $objPHPExcel->setActiveSheetIndex(0)->getStyle('B1')->getAlignment()
                    ->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
         $objPHPExcel->setActiveSheetIndex(0)->getStyle('C1')->getAlignment()
                    ->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
         $objPHPExcel->setActiveSheetIndex(0)->getStyle('D1')->getAlignment()
                    ->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
         $objPHPExcel->setActiveSheetIndex(0)->getStyle('E1')->getAlignment()
                    ->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        
        $objPHPExcel->setActiveSheetIndex(0)->getStyle('A')->getAlignment()
                    ->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
         $objPHPExcel->setActiveSheetIndex(0)->getStyle('B')->getAlignment()
                    ->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
         $objPHPExcel->setActiveSheetIndex(0)->getStyle('C')->getAlignment()
                    ->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
         $objPHPExcel->setActiveSheetIndex(0)->getStyle('D')->getAlignment()
                    ->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
         $objPHPExcel->setActiveSheetIndex(0)->getStyle('E')->getAlignment()
                     ->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                    
        //设置单元格宽度
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('A')->setWidth(15);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('B')->setWidth(15);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('C')->setWidth(15);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('D')->setWidth(60);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('E')->setWidth(30);
        
        //6.循环刚取出来的数组，将数据逐一添加到excel表格。
        for($i=0;$i<count($log);$i++){
            $objPHPExcel->getActiveSheet()->setCellValue('A'.($i+2),$log[$i]['id']);
            $objPHPExcel->getActiveSheet()->setCellValue('B'.($i+2),$log[$i]['account']);
            $objPHPExcel->getActiveSheet()->setCellValue('C'.($i+2),$log[$i]['type']);
            $objPHPExcel->getActiveSheet()->setCellValue('D'.($i+2),$log[$i]['detail_thing']);
            $objPHPExcel->getActiveSheet()->setCellValue('E'.($i+2),$log[$i]['create_time']);
        }
        //7.设置保存的Excel表格名称
        $filename = '系统日志信息'.date('Ymd',time()).'.xls';
        //8.设置当前激活的sheet表格名称；
        $objPHPExcel->getActiveSheet()->setTitle('系统日志信息');
        //9.设置浏览器窗口下载表格
        header("Content-Type: application/force-download");  
        header("Content-Type: application/octet-stream");  
        header("Content-Type: application/download");  
        header('Content-Disposition:inline;filename="'.$filename.'"');  
        //生成excel文件
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        //下载文件在浏览器窗口
        $objWriter->save('php://output');
        exit;
    }
}
