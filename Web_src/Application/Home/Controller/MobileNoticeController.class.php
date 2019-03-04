<?php
namespace Home\Controller;
use Think\Controller;
class MobileNoticeController extends MobileController {

    // 列表
    public function index(){
        $map='Touid ='.UID;
        $_list=D('Notice')->lists($map);
        if($_list){
            foreach ($_list as $key => $value) {
                $_list[$key]['ctime']=time_format($value['ctime']);
            }
            $back['status']=1;
            $back['info']=$_list;
        }else{
            $back['status']=0;
            $back['info']="暂无数据";
        }
        $this->ajaxReturn($back);
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
    public function setStatus($id=''){
        if(!is_numeric($id) || empty($id)){
            $data['status']=0;
            $data['info']='非法操作';
            $this->ajaxReturn($data);
        }

        $map['id']=$id;
        $map['status']=1;
        
        if(D('Notice')->save($map)){
            $data['status']=1;
        }else{
            $data['status']=0;
        }
        $this->ajaxReturn($data);
    }

}