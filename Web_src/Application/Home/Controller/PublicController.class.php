<?php
namespace Home\Controller;
use Think\Controller;
class PublicController extends \Think\Controller {
	//用户登陆   PC版及移动版共用此接口  $app 1为移动版
    public function login($username=null,$password=null,$remember=null,$app=''){
    	// echo chs_ucenter_md5(md5('123456'),C('DATA_AUTH_KEY'));
		$user_mod=D('Users');
		if (IS_POST) {
			if (!$username) {
				$this->blert(1,"用户名不能为空！",'',$app);
			}
			if(!$password){
				$this->blert(1,"密码不能为空！",'',$app);
			}
			$backStatus=$user_mod->login($username,$password,$remember,$app);
			if(strlen($backStatus)>10){
				$this->blert(2,'登陆成功！', U('Index/index') , $app,$backStatus);
			}else{//登录失败
				switch($backStatus) {
					case -1: $error = '用户不存在或被禁用！'; break; //系统级别禁用
					case -2: $error = '密码错误！'; break;
					default: $error = '未知错误！'; break; // 0-接口参数错误（调试阶段使用）
				}
				$this->blert(1,$error,'',$app);
			}
		}else{
			$this->display();
		}    	
    }

    // $type 1为错误，2为成功
    public function blert($type=1,$msg='',$url='',$app='',$token=''){
    	if(empty($app)){  	
	    	if($type==1){
	    		$this->error($msg,$url);
	    	}else{
	    		$this->success($msg,$url);
	    	}
    	}else{
    		if($type==1){
    			$data['status']=0;
    			$data['info']=$msg;    						
    		}else{
    			$data['status']=1;
    			$data['info']=$msg;
    			$data['token']=$token;
    			$data['uid']=is_login();
    			$data['nickname']=get_nickname(is_login());
    		}
    		$this->ajaxReturn($data);   		
    	}    	
    }

	/* 验证码，用于登录和注册 */
	public function verify(){
		$verify = new \Think\Verify();
		$verify->entry(1);
	}


	/* 退出登录 */
	public function logout(){
		if(is_login()){
			D('Users')->logout();
			$this->success('退出成功！', U('Public/login'));
		} else {
			$this->redirect('Public/login');
		}
	}

	// 移动端退出登陆接口
	public function mobile_logout($token=''){
		if(is_login()){
			D('Users')->logout();
			$data['status']=1;
			$data['info']="退出成功！";
		} elseif(empty($token)){
			$data['status']=2;
			$data['info']="非法操作";
		}else{
			$data['status']=0;
			$data['info']="出错";
		}
		$this->ajaxReturn($data);
	}

	// 删除缓存
	public function Clear_Runtime(){
		$dirname = './Runtime/';
		//清文件缓存
		$dirs	=	array($dirname);
		//清理缓存
		foreach($dirs as $value) {
			rmdirr($value);
		}
		$this->success('缓存清理成功！请重新登陆···');
	}




}