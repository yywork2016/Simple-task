<?php
namespace Home\Controller;
use Think\Controller;

class MobileMemberController extends MobileController {


    // 获取用户个人信息
    public function minfo(){
        $info=D('Users')->info(UID);
        $info['depet']=Get_depth_name($info['group_id']);
        $info['cover']=User_Cover(UID);
        $info['newtask']=$this->getTotal(1);
        $info['doingtask']=$this->getTotal(2);
        $info['donetask']=$this->getTotal(3);
        $this->ajaxReturn($info);
    }

    // 获取最新头像
    public function getcover(){
        $data['cover']=User_Cover(UID);
        $data['username']=get_nickname(UID);
        $this->ajaxReturn($data);
    }

    // 获取所有用户
    public function getusers(){
        if(IS_POST){
            $lists=D('Users')->lists('','','id,nickname,jobs');
            if($lists){
                foreach ($lists as $key => $value) {
                    $lists[$key]['usercover']=User_Cover($value['id']);
                }
                $back['status']=1;
                $back['info']=$lists;
            }else{
                $back['status']=0;
                $back['info']='暂无数据！';
            }

            $this->ajaxReturn($back);
        }
    }


    public function getTotal($typeid){
        $where='status=1';
        if(!is_superman(UID)){
        // 任务指派人  任务接收人
        $zhipai='((dist like "'.UID.'" or dist like "%,'.UID.'" or dist like "%,'.UID.',%" or dist like "'.UID.',%") and t_status>=1)';
        // 任务审核人
        $shenhe='((leaderid like "'.UID.'" or leaderid like "%,'.UID.'" or leaderid like "%,'.UID.',%" or leaderid like "'.UID.',%") and t_status<1 and pass_leader_uid IS NULL)';
        // 通知归档人归档，任务完成
        $fileman='((fileman like "'.UID.'" or fileman like "%,'.UID.'" or fileman like "%,'.UID.',%" or fileman like "'.UID.',%") and t_status>=5)';
        }else{
            $zhipai="t_status>=1";
            $shenhe="t_status<1 and pass_leader_uid IS NULL";
            $fileman="t_status>=5";
        }

        // 读取属于我的任务 和 属于我审批的未审批任务  
        $where .=' and ('.$zhipai.' or '.$shenhe.' or '.$fileman.')';

        // dump($where);
        // 全部
        $_list=D('Task')->lists($where,'id,title,enddt,dist,leaderid,applyid,level,t_status');
        foreach ($_list as $key => $value) {
            // 任务结果表过滤
            $resu_info=D('TaskResult')->taskinfo($value['id']);

            // 我的新任务/等待我审核、领取 
            if($value['t_status']<=1 && empty($resu_info)){
                $newtask[]=$value;
            }
            // 待归档
            if($value['t_status']==5){
                $newtask[]=$value;
            }
            // 执行中的任务
            if($resu_info['t_status']==2 || $resu_info['t_status']==3){
                $ongoingtask[]=$value;
            }
            // 已完成的任务  完成归档
            if($resu_info['t_status']==4 || $value['t_status']==6){
                $donetask[]=$value;
            }
        }
        if($typeid==1){
            return count($newtask);
        }elseif ($typeid==2) {
            return count($ongoingtask);
        }else{
            return count($donetask);
        }
        
    }


    // 编辑用户信息
    public function edituser(){
        $mod = D("Users");
        if(IS_POST){
            $id=I('post.id','','htmlspecialchars');
            if(empty($id)){
                $bd['status']=0;
                $bd['info']="非法操作";
                $this->ajaxReturn($bd);
            }

            unset($_POST['password']);
            $data = $mod->create();
            if($data){
                if($mod->filter('strip_tags')->save()){
                    $bd['status']=1;
                    $bd['info']='修改成功';
                }else{
                    $bd['status']=0;
                    $bd['info']="数据未改变！";
                }
            } else {
                $bd['status']=0;
                $bd['info']=$mod->getError();
            }
            $this->ajaxReturn($bd);
        }
    }

    /**
     * 修改密码提交
    */
    public function resetpassword(){

        if ( IS_POST ) {
            //获取参数
            $data['id'] =   is_login();
            $password   =   I('post.old');
            $repassword = I('post.repassword');
            $newpassword = I('post.password');
            if(empty($password)){
                $beback['status']=0;
                $beback['info']="请输入原密码";
                $this->ajaxReturn($beback);
            }

            if(empty($newpassword)){
                $beback['status']=0;
                $beback['info']="请输入新密码";
                $this->ajaxReturn($beback);
            }
            
            if(empty($repassword)){
                $beback['status']=0;
                $beback['info']="请输入确认密码";
                $this->ajaxReturn($beback);
            }

            if($newpassword !== $repassword){
                $beback['status']=0;
                $beback['info']="您输入的新密码与确认新密码不一致";
                $this->ajaxReturn($beback);
            }

            $oldtoken=chs_ucenter_md5($password, UC_AUTH_KEY);
            $oldinfo=D('Users')->where('id='.UID.' and password="'.$oldtoken.'"')->find();
            if(empty($oldinfo)){
                $beback['status']=0;
                $beback['info']="原密码输入错误！";
                $this->ajaxReturn($beback);
            }

            $token=chs_ucenter_md5($newpassword, UC_AUTH_KEY);
            $data['password']=$token;
            $res = D('Users')->save($data);
            if($res){
                // 修改密码成功后注销当前登陆，然后跳转登陆页
                // D('Users')->logout();
                $beback['status']=1;
                $beback['info']="修改密码成功！";
            }else{
                $beback['status']=0;
                $beback['info']="数据未改变！";
            }
            $this->ajaxReturn($beback);
        }
    }

	// 更新头像
	public function UploadCover(){
        /* 调用文件上传组件上传文件 */
        $Picture = D('Picture');
        $pic_driver = C('PICTURE_UPLOAD_DRIVER');
        $info = $Picture->upload(
            $_FILES,
            C('PICTURE_UPLOAD'),
            C('PICTURE_UPLOAD_DRIVER'),
            C("UPLOAD_{$pic_driver}_CONFIG")
        ); //TODO:上传到远程服务器

        /* 记录图片信息 */
        if($info){
        	$besave['id']=UID;
        	$besave['cover']=$info['file']['path'];
        	if(D('Users')->save($besave)){
	            $return['progress'] = 100;
	            $return['status'] = 1;
	            $return['body'] = '';
            }
        } else {
            $return['statusCode'] = 400;
            $return['code'] = 0;
            $return['msg'] = "更新失败";
            $return['body'] = '';
        }

        /* 返回JSON数据 */
        $this->ajaxReturn($return);
	}

}