<?php
namespace Home\Controller;
use Think\Controller;
class NoticeController extends HomeController {

    // 列表
    public function index(){
        $map='Touid ='.UID;
        $_list=$this->lists('notice',$map,'status asc,id desc');
        $this->assign('_list',$_list);
        $this->display();
    }

	//ajax获取未读列表
    public function ajaxGetNotice(){
    	$ajaxback=array();
    	if(IS_GET){
    		if(UID<=0){
    			exit;
    		}
    		$map='Touid='.UID.' and status=0';
    		$_list=D('Notice')->lists($map);
    		foreach ($_list as $key => $value) {
    			$_list[$key]['ctime']=time_format($value['ctime']);
    			$_list[$key]['type']=notice_type($value['typeid']);
    		}
    		$this->ajaxReturn($_list);
    	}else{
    		$this->ajaxReturn($ajaxback);
    	}
    }


    /**
     * 设置一条或者多条数据的状态
     */
    public function setStatus($model='Notice'){
        $status=I('get.status','','htmlspecialchars');
        if($status==1){
            $msg="成功标为已读！";
        }
        return parent::setStatus('Notice',$msg);
    }

}