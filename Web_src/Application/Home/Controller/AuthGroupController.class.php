<?php
namespace Home\Controller;
use Home\Model\AuthRuleModel;
use Home\Model\AuthGroupModel;
class AuthGroupController extends HomeController {

	//列表
    public function index(){
    	$lists=D('AuthGroup')->lists();
    	$this->assign('list',$lists);
    	$this->display();
    }

    // 新增
    public function add(){
		parent::add();
    }

    // 编辑
    public function edit($id=0){
    	$this->assign('id',$id);
        parent::edit('AuthGroup','add');
    }


	/**
     * 设置一条或者多条数据的状态
     */
    public function setStatus($model='AuthGroup'){
        return parent::setStatus('AuthGroup');
    }

    /**
     * 组权限设置
     */
    public function access($id=''){
        if(empty($id) || !is_numeric($id)){
            $this->error('错误!');
        }
		$AuthRule=D('AuthRule');
		$AuthGroup=D('AuthGroup');

		$auths=$AuthGroup->where('id='.$id)->getField('rules');
		$auths_arr=explode(',', $auths);
		// dump($auths_arr);
        $map         = array('status'=>1,'pid'=>0);
        $main_rules  = $AuthRule->lists(0,1,'','title,id');
        foreach ($main_rules as $key => $value) {
        	$main_rules[$key]['child_one']=$AuthRule->lists($value['id'],1,'','title,id');
        	if($main_rules[$key]['child_one']){        		
        		foreach ($main_rules[$key]['child_one'] as $k => $val) {
        			if(in_array($val['id'], $auths_arr)) $main_rules[$key]['child_one'][$k]['istrue']=1;
        			$main_rules[$key]['child_one'][$k]['child_two']=$AuthRule->lists($val['id'],1,'','title,id');
        			if($main_rules[$key]['child_one'][$k]['child_two']){
        				foreach ($main_rules[$key]['child_one'][$k]['child_two'] as $kk => $vals) {
        					if(in_array($vals['id'], $auths_arr)) $main_rules[$key]['child_one'][$k]['child_two'][$kk]['istrue']=1;
        				}
        			}
        		}
        	}
        }
        // dump($main_rules);
        $this->assign('main_rules', $main_rules);
        $this->assign('this_group', $id);
    	$this->display('managergroup');

    }

    /**
     * 管理员用户组数据写入/更新
     */
    public function writeGroup($id='',$rules=''){
    	if(empty($id) || !is_numeric($id)){
    		 $this->error('操作失败');
    	}
        if(isset($_POST['rules'])){
            sort($rules);
            $rules_im  = implode( ',' , array_unique($rules));
        }
        $data['id']=$id;
        $data['rules']=$rules_im;
        $AuthGroup       =  D('AuthGroup');
        $r = $AuthGroup->save($data);
        if($r===false){
            $this->error('操作失败'.$AuthGroup->getError());
        } else{
            $this->success('操作成功!',U('index'));
        }

    }

// 成员授权列表
public function user($id=''){
    if(empty($id) || !is_numeric($id)){
        $this->error('错误！');
    }  

    $auth_group = M('AuthGroup')->where( array('status'=>array('egt','0')) )->select();
    $prefix   = C('DB_PREFIX');
    $l_table  = $prefix.(AuthGroupModel::USERS);
    $r_table  = $prefix.(AuthGroupModel::AUTH_GROUP_ACCESS);
    $model    = M()->table( $l_table.' m' )->join ( $r_table.' a ON m.id=a.uid' );
    $_REQUEST = array();
    $list = $this->lists($model,array('a.group_id'=>$id,'m.status'=>array('egt',0)),'m.id asc','m.id,m.nickname,m.last_login_time,m.last_login_ip,m.status');
    int_to_string($list);
    $this->assign( '_list',     $list );
    $this->assign('auth_group', $auth_group);
    $this->assign('groupid',$id);
    $this->display();
}


    /**
     * 将用户添加到用户组的编辑页面
     */
    public function group(){
        $uid            =   I('uid');
        $auth_groups    =   D('AuthGroup')->getGroups();
        $user_groups    =   AuthGroupModel::getUserGroup($uid);
        $ids = array();
        foreach ($user_groups as $value){
            $ids[]      =   $value['group_id'];
        }
        $nickname       =   D('Member')->getNickName($uid);
        $this->assign('nickname',   $nickname);
        $this->assign('auth_groups',$auth_groups);
        $this->assign('user_groups',implode(',',$ids));
        $this->meta_title = '用户组授权';
        $this->display();
    }

    /**
     * 将用户添加到用户组,入参uid,group_id
     */
    public function addToGroup($dist=''){
        if(empty($dist)){
            $data['status']=0;
            $this->ajaxReturn($data);
        }
        $gid = I('get.id','','htmlspecialchars');        
        $AuthGroup = D('AuthGroup');
        if(is_numeric($dist)){
            if ( is_administrator($dist) ) {
                $data['status']=0;
                $data['info']="该用户为超级管理员";
                $this->ajaxReturn($data);
            }
            if( !M('Users')->where(array('id'=>$dist))->find() ){
                $data['status']=0;
                $data['info']="用户不存在";
                $this->ajaxReturn($data);
            }
        }

        if( $gid && !$AuthGroup->checkGroupId($gid)){
            $data['status']=0;
            $data['info']=$AuthGroup->error;
            $this->ajaxReturn($data);
        }
        $UTG=$AuthGroup->addToGroup($dist,$gid);
        if ( $UTG ){
            if(is_numeric($UTG)){
                $data['status']=0;
                $data['info']='编号为'.$UTG.'的用户已存在！';            
            }else{
                $data['status']=1;
                $data['info']='操作成功';
            }
            $this->ajaxReturn($data);
        }else{
            $data['status']=0;
            $data['info']=$AuthGroup->getError();
            $this->ajaxReturn($data);
        }        
    }

    /**
     * 将用户从用户组中移除  入参:uid,group_id
     */
    public function removeFromGroup(){
        $uid = I('uid');
        $gid = I('group_id');
        if( $uid==UID ){
            $this->error('不允许解除自身授权');
        }
        if( empty($uid) || empty($gid) ){
            $this->error('参数有误');
        }
        $AuthGroup = D('AuthGroup');
        if( !$AuthGroup->find($gid)){
            $this->error('用户组不存在');
        }
        if ( $AuthGroup->removeFromGroup($uid,$gid) ){
            $this->success('操作成功');
        }else{
            $this->error('操作失败');
        }
    }


}