<?php
namespace Home\Controller;
use Think\Controller;
class MobileController extends Controller {

	/* 空操作，用于输出404页面 */
	public function _empty(){
		
	}


    protected function _initialize(){

        // 获取TOKEN 用于检测
        $token=I('get.token','','htmlspecialchars');
        if(defined('token')) return ;
        define('TOKEN',$token);

        // 用户权限检查
        $this->checkUserInfo();

        // 获取当前用户ID
        if(defined('UID')) return ;
        define('UID',is_login());

        /* 读取数据库中的配置 */
        $config =   S('DB_CONFIG_DATA');
        if(!$config){
            $config =   api('Config/lists');
            S('DB_CONFIG_DATA',$config);
        }
        C($config); //添加配置
        // 是否是超级管理员
        define('IS_ROOT',   is_administrator());

    }

       //检查权限
    public function checkUserInfo(){
        $auth=TOKEN;
        $this->keep_User_login($auth);
        $checkmeminfos=$this->memcache_checkInfo($auth);
        if( $checkmeminfos['status'] == 'no' ){
            D('Users')->logout();
            if(ACTION_NAME !='login'){
                $data['status']=0;
                $data['info']="会话已过期，请重新登陆！";
                $data['loginout']=1;
                $this->ajaxReturn($data);
            }
        }

    }

    //单点用户登陆方法、一个用户只能在一个客户端登陆使用
    public function memcache_checkInfo($auth){
        // 读取Memcached进行验证,单点登陆        
        if($auth){
            $decrypwd=think_decrypt($auth,'chs_oa');
            $exp_auth=explode(':', $decrypwd);
            $user_id=$exp_auth[1];
            $backinfo['userid']=$user_id;
            $memcache_user_data=S('chs_auth_'.$user_id);
            if($memcache_user_data!=$auth){
                $backinfo['status']='no';
            }else{
                $backinfo['status']='yes';
            }
        }else{
            $backinfo['status']='no';
        }
        return $backinfo;
    }

    //持续登陆
    public function keep_User_login($auth){
        $uid=is_login();        
        if($uid<=0){
            if($auth){
                $moduser=D('Users');
                $decrypwd=think_decrypt($auth,'chs_oa');
                $exp_auth=explode(':', $decrypwd);
                $user_id=$exp_auth[1];
                $encode_pwd=$exp_auth[0];
                if(is_numeric($user_id)){
                    $user=$moduser->where('id='.$user_id.' and password="'.$encode_pwd.'"')->find();
                    if($user){
                        $moduser->Login_User_Auto($user);//自动登陆                    
                    }
                }
            }
        }
    }


    /**
     * 通用任务操作通知     $arr为数组,可以多维数组，必须以0为第一个键名
     * @param int           $arr['typeid']    通知的归属模块类型  如：任务模块、知识库模块.为空默认为1
     * @param string        $arr['title']   提醒内容
     * @param int           $arr['task_id']  任务模块的任务ID
     * @param int           $arr['Touid']   接收提醒人ID
     */
    protected function notice($arr){
        if(is_array($arr)){   
            // 用户群拆分        
            foreach ($arr as $key => $val) {
                $Touid_arr=explode(',', $val['Touid']);
                foreach ($Touid_arr as $vv) {    
                    $least['Touid']=$vv;
                    $least['title']=$val['title'];
                    $least['task_id']=$val['task_id'];
                    $least['show_id']=$val['show_id'];
                    $least['ctime']=time();
                    $news_arr[]=$least;
                    // 移动端推送
                    $data = array ( 
                     'title' => '提醒信息', 
                     'content' => $val['title'], 
                     'type' => 2, 
                     'timer' => '', 
                     'platform' => 0, 
                     'groupName' => '', 
                     'userIds' => $vv
                    );
                    push($data);
                }
            }
            M('notice')->addAll($news_arr);
        }
    }

}