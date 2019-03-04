<?php
namespace Home\Controller;
use Think\Controller;
/**
 * 用户控制器
 * 包括用户中心，用户登录及注册
 */
class UsersController extends HomeController {

	/* 用户中心首页 */
	public function index($typeid=''){
        $map=array();  
        if(!empty($typeid) && $typeid==1){
            $map['id']=UID;
        }

        $list=D('Users')->lists($map);
        foreach ($list as $key => $value) {
            $list[$key]['groupname']=Get_depth_name($value['group_id']);
            $list[$key]['leadername']=Get_leader_names($value['leader_id']);
            $list[$key]['cover']=User_Cover($value['id']);
        }
        $this->assign('typeid',$typeid);
        $this->assign('list',$list);        
		$this->display('index');
	}

    //新增
    public function add(){
        if(IS_POST){
            $_POST['username']=$_POST['yonghuming_'];
            $_POST['password']=$_POST['mima_'];
            D('Users')->setSnull();
        }        
        parent::add();
    }

    // 个人用户资料
    public function uidinfo(){        
        $this->index(1);
    }

    //获取编辑数据
    public function user_edit($id=''){
        $mod = D('Users');
        if(IS_POST){            
            if(empty($id)){
                $this->error('ID值错误');
            }
            $pwd=$_POST['password'];

            //验证密码
            unset($_POST['password']);
            if($pwd){                
                $_POST['password']=chs_ucenter_md5($pwd, UC_AUTH_KEY);
            }

            $datas = $mod->create();
            if($datas){
                if($mod->save()){
                    D('Users')->setSnull();
                    $this->success('修改成功', Cookie('__forward__'));
                }else{
                    $this->error('数据未改变！');
                }
            }else{
                $this->error($mod->getError());
            }

        } else {
            $id=I('get.id','','htmlspecialchars');
            $typeid=I('get.typeid','','htmlspecialchars');
            if(empty($id)){
                $this->error('错误！');
            }
            $info=$mod->info($id);
            $info['leadername']=Get_leader_names($info['leader_id']);
            $info['groupname']=Get_depth_name($info['group_id']);
            $this->assign('info',$info);
            $this->assign('typeid',$typeid);
            $this->display('add');
        }   
    }


    public function _group(){
        return R('Group/_menu');
    }
    /**
     * 修改密码提交
     */
    public function resetpassword(){
		if ( !is_login() ) {
			$this->error( '您还没有登陆',U('Public/login') );
		}
        if ( IS_POST ) {
            //获取参数
            $data['id'] =   is_login();
            $password   =   I('post.old');
            $repassword = I('post.repassword');
            $newpassword = I('post.password');
            empty($password) && $this->error('请输入原密码');
            empty($newpassword) && $this->error('请输入新密码');
            empty($repassword) && $this->error('请输入确认密码');

            if($newpassword !== $repassword){
                $this->error('您输入的新密码与确认新密码不一致');
            }
            $oldtoken=chs_ucenter_md5($password, UC_AUTH_KEY);
            $oldinfo=D('Users')->where('id='.UID.' and password="'.$oldtoken.'"')->find();
            if(empty($oldinfo)){
                $this->error('原密码输入错误！');
            }

            $token=chs_ucenter_md5($newpassword, UC_AUTH_KEY);
            $data['password']=$token;
            $res = D('Users')->save($data);
            if($res){
                // 修改密码成功后注销当前登陆，然后跳转登陆页
                D('Users')->logout();
                $this->success('修改密码成功！',U('Public/login'));
            }else{
                $this->error('密码修改失败！');
            }
        }else{
            $this->display();
        }
    }

    // 设置头像
    public function setcover(){
        if(IS_POST){
            $imgpath=I('post.coverdata','','htmlspecialchars');
            $imgdata=base64_decode(str_replace('data:image/jpeg;base64,', '', $imgpath));
            $filename=time().rand(10000,99999).'.jpg';
            $filepath='./Uploads/cover/';
            if(!file_exists($filepath)){
                @mkdir($filepath, 0777);
            }
            if($imgdata){
                file_put_contents($filepath.$filename, $imgdata);
                $data['id']=UID;
                $data['cover']='/Uploads/cover/'.$filename;      
                if(M('users')->save($data)){
                    $this->success("修改头像成功！");
                }else{
                    $this->error('修改头像失败！');
                }
            }else{
                $this->error('修改头像失败！');
            }
        }else{
            $this->display();
        }        
    }



    /**
     * 设置一条或者多条数据的状态
     */
    public function setStatus($model='Users'){
        return parent::setStatus('Users');
    }


    /**
     * 获取用户注册错误信息
     * @param  integer $code 错误编码
     * @return string        错误信息
     */
    private function showRegError($code = 0){
        switch ($code) {
            case -1:  $error = '用户名长度必须在16个字符以内！'; break;
            case -2:  $error = '用户名被禁止注册！'; break;
            case -3:  $error = '用户名被占用！'; break;
            case -4:  $error = '密码长度必须在6-30个字符之间！'; break;
            case -5:  $error = '邮箱格式不正确！'; break;
            case -6:  $error = '邮箱长度必须在1-32个字符之间！'; break;
            case -7:  $error = '邮箱被禁止注册！'; break;
            case -8:  $error = '邮箱被占用！'; break;
            case -9:  $error = '手机格式不正确！'; break;
            case -10: $error = '手机被禁止注册！'; break;
            case -11: $error = '手机号被占用！'; break;
            default:  $error = '未知错误';
        }
        return $error;
    }


    /**
     * AJAX获取用户列表
     */
    public function getUser_dept(){
        $types=I('get.typeid','','htmlspecialchars');
        if(IS_POST){            
            $group_mod=D('Group')->lists();
            foreach ($group_mod as $key => $value) {
                if($types==''){
                $group_mod[$key]['userlist']=D('Users')->lists('group_id='.$value['id'].' and status=1','','id,nickname,jobs,cover,sex');
                        foreach ($group_mod[$key]['userlist'] as $k => $val) {
                            $group_mod[$key]['userlist'][$k]['cover']=User_Cover($val['id']);
                        }
                }else{
                    $group_mod[$key]['userlist']='';
                }
            }
            $res['glist']=$group_mod;
            $res['status']=1;            
        }else{
            $res['status']=0;
        }
        $this->ajaxReturn($res);
    }



}
