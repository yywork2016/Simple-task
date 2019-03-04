<?php
namespace Home\Controller;
use Think\Controller;
use Admin\Model\AuthRuleModel;
use Admin\Model\AuthGroupModel;
/**
 * 前台公共控制器
 * 为防止多分组Controller名称冲突，公共Controller名称统一使用分组名称
 */
class HomeController extends Controller {

	/* 空操作，用于输出404页面 */
	public function _empty(){
		$this->redirect('Index/index');
	}


    protected function _initialize(){
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
        if(!IS_ROOT && C('ADMIN_ALLOW_IP')){
            // 检查IP地址访问
            if(!in_array(get_client_ip(),explode(',',C('ADMIN_ALLOW_IP')))){
                $this->error('403:禁止访问');
            }

            if(!C('WEB_SITE_CLOSE')){
            $this->error('系统已经关闭，请稍后访问~');
            }
        }

         // 检测系统权限
        if(!IS_ROOT){
            $access =   $this->accessControl();
            if ( false === $access ) {
                $this->error('403:禁止访问');
            }elseif(null === $access ){
                //检测访问权限
                $rule  = strtolower(MODULE_NAME.'/'.CONTROLLER_NAME.'/'.ACTION_NAME);

                if ( !$this->checkRule($rule,array('in','1,2')) ){
                    $this->error('未授权访问!');
                }else{
                    // 检测分类及内容有关的各项动态权限
                    $dynamic    =   $this->checkDynamic();
                    if( false === $dynamic ){
                        $this->error('未授权访问!');
                    }
                }
            }
        }

        //顶部菜单导航          
        //非超级管理员权限限制 YUN：2016-8-3       
        if(!IS_ROOT){
            // 获取当前用户节点的父类ID
            $Pid_groups=array_map('Get_auth_pid', $this->GetAll_AUTH());
            // 获取所有顶部菜单
            $gettopmenu=D('AuthRule')->lists(0,1);
            foreach ($gettopmenu as $kk => $val) {
                if(in_array($val['id'], $Pid_groups)){
                    $topmenulist[]=$val;
                }
            } 
        }else{
            $topmenulist=D('AuthRule')->lists(0,1);
        }
        $this->assign('topmenu',$topmenulist);       

    }

    // 获取当前用户授权的所有节点
    protected function GetAll_AUTH(){
            $modaccess=M('auth_group_access')->where('uid='.UID)->select();
            $group_ids='';
            foreach ($modaccess as $key => $value) {
                $group_ids .=D('AuthGroup')->where('id='.$value['group_id'].' and status=1')->getField('rules').',';                
            }
            $sub_group=substr($group_ids, 0,-1);
            $group_arr=explode(',', $sub_group);
            $new_groups=array_unique($group_arr);
            return $new_groups;
    }


    //检查权限
    public function checkUserInfo(){
        $auth=$_COOKIE["chs_auth_oa"];
        $this->keep_User_login($auth);
        $checkmeminfos=$this->memcache_checkInfo($auth);
        if( $checkmeminfos['status'] == 'no' ){
            D('Users')->logout();
            if(ACTION_NAME !='login'){
                // $this->redirect('Public/login');
                $this->error('会话已过期，请重新登陆！',U('Public/login'));
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
                $user=$moduser->where('id='.$user_id.' and password="'.$encode_pwd.'"')->find();
                if($user){
                    $moduser->Login_User_Auto($user);//自动登陆                    
                }
            }
        }
    }

    /**
     * 权限检测
     * @param string  $rule    检测的规则
     * @param string  $mode    check模式
     * @return boolean
     */
    final protected function checkRule($rule, $type=AuthRuleModel::RULE_URL, $mode='url'){
        static $Auth    =   null;
        if (!$Auth) {
            $Auth       =   new \Think\Auth();
        }
        if(!$Auth->check($rule,UID,$type,$mode)){
            return false;
        }
        return true;
    }

    /**
     * 检测是否是需要动态判断的权限
     * @return boolean|null
     *      返回true则表示当前访问有权限
     *      返回false则表示当前访问无权限
     *      返回null，则表示权限不明
     *
     */
    protected function checkDynamic(){}


    /**
     * action访问控制,在 **登陆成功** 后执行的第一项权限检测任务
     *
     * @return boolean|null  返回值必须使用 `===` 进行判断
     *
     *   返回 **false**, 不允许任何人访问(超管除外)
     *   返回 **true**, 允许任何管理员访问,无需执行节点权限检测
     *   返回 **null**, 需要继续执行节点权限检测决定是否允许访问
     */
    final protected function accessControl(){
        $allow = C('ALLOW_VISIT');
        $deny  = C('DENY_VISIT');
        $check = strtolower(CONTROLLER_NAME.'/'.ACTION_NAME);
        if ( !empty($deny)  && in_array_case($check,$deny) ) {
            return false;//非超管禁止访问deny中的方法
        }
        if ( !empty($allow) && in_array_case($check,$allow) ) {
            return true;
        }
        return null;//需要检测节点权限
    }

    /**
     * 对数据表中的单行或多行记录执行修改 GET参数id为数字或逗号分隔的数字
     *
     * @param string $model 模型名称,供M函数使用的参数
     * @param array  $data  修改的数据
     * @param array  $where 查询时的where()方法的参数
     * @param array  $msg   执行正确和错误的消息 array('success'=>'','error'=>'', 'url'=>'','ajax'=>false)
     *                     url为跳转页面,ajax是否ajax方式(数字则为倒数计时秒数)
     */
    final protected function editRow ( $model ,$data, $where , $msg ){
        $id    = array_unique((array)I('id',0));
        $id    = is_array($id) ? implode(',',$id) : $id;
        //如存在id字段，则加入该条件
        $fields = M($model)->getDbFields();
        if(in_array('id',$fields) && !empty($id)){
            $where = array_merge( array('id' => array('in', $id )) ,(array)$where );
        }

        $msg   = array_merge( array( 'success'=>'操作成功！', 'error'=>'操作失败！', 'url'=>'' ,'ajax'=>IS_AJAX) , (array)$msg );
        if( M($model)->where($where)->save($data)!==false ) {
            $this->success($msg['success'],$msg['url'],$msg['ajax']);
        }else{
            $this->error($msg['error'],$msg['url'],$msg['ajax']);
        }
    }

    /**
     * 条目假删除
     * @param string $model 模型名称,供D函数使用的参数
     * @param array  $where 查询时的where()方法的参数
     * @param array  $msg   执行正确和错误的消息 array('success'=>'','error'=>'', 'url'=>'','ajax'=>false)
     *                     url为跳转页面,ajax是否ajax方式(数字则为倒数计时秒数)
     *
     */
    protected function delete ( $model , $where = array() , $msg = array( 'success'=>'删除成功！', 'error'=>'删除失败！')) {
        $data['status']         =   -1;
        $this->editRow(   $model , $data, $where, $msg);
    }
    /**
     * 禁用条目
     * @param string $model 模型名称,供D函数使用的参数
     * @param array  $where 查询时的 where()方法的参数
     * @param array  $msg   执行正确和错误的消息,可以设置四个元素 array('success'=>'','error'=>'', 'url'=>'','ajax'=>false)
     *                     url为跳转页面,ajax是否ajax方式(数字则为倒数计时秒数)
     *
     */
    protected function forbid ( $model , $where = array() , $msg = array( 'success'=>'状态禁用成功！', 'error'=>'状态禁用失败！')){
        $data    =  array('status' => 0);
        $this->editRow( $model , $data, $where, $msg);
    }
    /**
     * 恢复条目
     * @param string $model 模型名称,供D函数使用的参数
     * @param array  $where 查询时的where()方法的参数
     * @param array  $msg   执行正确和错误的消息 array('success'=>'','error'=>'', 'url'=>'','ajax'=>false)
     *                     url为跳转页面,ajax是否ajax方式(数字则为倒数计时秒数)
     *
     */
    protected function resume (  $model , $where = array() , $msg = array( 'success'=>'状态恢复成功！', 'error'=>'状态恢复失败！')){
        $data    =  array('status' => 1);
        $this->editRow(   $model , $data, $where, $msg);
    }

    /**
     * 设置一条或者多条数据的状态
     */
    public function setStatus($Model=CONTROLLER_NAME,$one_msg_success='启用成功'){

        $ids    =   I('request.ids');
        $status =   I('request.status');
        if(empty($ids)){
            $this->error('请选择要操作的数据');
        }

        $map['id'] = array('in',$ids);
        switch ($status){
            case -1 :
                $this->delete($Model, $map, array('success'=>'删除成功','error'=>'删除失败'));
                break;
            case 0  :
                $this->forbid($Model, $map, array('success'=>'禁用成功','error'=>'禁用失败'));
                break;
            case 1  :
                $this->resume($Model, $map, array('success'=>$one_msg_success,'error'=>'启用失败'));
                break;
            default :
                $this->error('参数错误');
                break;
        }
    }

    // 通用添加数据函数
    public function add($Model=CONTROLLER_NAME,$tpl='add'){
        $mod=D($Model);
        if(IS_POST){
            $data = $mod->create();
            if($data){
                $id=$mod->filter('strip_tags')->add();
                if($id){       
                    if($Model=='Task'){
                        // 新任务提醒领导审批
                        $arr[]=array(
                            'title'=>get_nickname(UID).'创建了任务【'.I('post.title','','htmlspecialchars').'】等待审批',
                            'Touid'=>I('post.leaderid','','htmlspecialchars'),
                            'task_id'=>$id,
                            'show_id'=>2
                            );
                        $this->notice($arr);
                    }             
                    $this->success('新增成功', Cookie('__forward__'));
                } else {
                    $this->error('新增失败');
                }              
            } else {
                $this->error($mod->getError());
            }
        }else{
            $this->display($tpl);
        }
    }


    // 通用编辑数据函数
    public function edit($Model=CONTROLLER_NAME,$tpl='edit'){
        $mod = D($Model);
        if(IS_POST){
            $id=I('post.id','','htmlspecialchars');
            if(empty($id)){
                $this->error('ID值错误');
            }
            $data = $mod->create();
            if($data){
                if($mod->filter('strip_tags')->save()){
                    if($Model=='Task'){
                        // 新任务提醒领导审批
                        $arr[]=array(
                            'title'=>get_nickname(UID).'修改了任务【'.I('post.title','','htmlspecialchars').'】等待审批',
                            'Touid'=>I('post.leaderid','','htmlspecialchars'),
                            'task_id'=>$id,
                            'show_id'=>2
                            );
                        $this->notice($arr);
                    }
                    $this->success('修改成功', Cookie('__forward__'));
                }else{
                    $this->error('修改失败');
                }
            } else {
                $this->error($mod->getError());
            }
        } else {
            $id=I('get.id','','htmlspecialchars');
            if(empty($id)){
                $this->error('错误！');
            }
            $info=$mod->info($id);
            $this->assign('info',$info);
            $this->display($tpl);
        }
       
    }

    /**
     * 通用分页列表数据集获取方法
     *
     *  可以通过url参数传递where条件,例如:  index.html?name=asdfasdfasdfddds
     *  可以通过url空值排序字段和方式,例如: index.html?_field=id&_order=asc
     *  可以通过url参数r指定每页数据条数,例如: index.html?r=5
     *
     * @param sting|Model  $model   模型名或模型实例
     * @param array        $where   where查询条件(优先级: $where>$_REQUEST>模型设定)
     * @param array|string $order   排序条件,传入null时使用sql默认排序或模型属性(优先级最高);
     *                              请求参数中如果指定了_order和_field则据此排序(优先级第二);
     *                              否则使用$order参数(如果$order参数,且模型也没有设定过order,则取主键降序);
     *
     * @param boolean      $field   单表模型用不到该参数,要用在多表join时为field()方法指定参数
     *
     * @return array|false
     * 返回数据集
     */
    protected function lists ($model,$where=array(),$order='',$field=true){
        $options    =   array();
        // $REQUEST    =   (array)I('request.');  // 旧方法
        $REQUEST    =   (array)I('get.');

        if(is_string($model)){
            $model  =   M($model);
        }

        $OPT        =   new \ReflectionProperty($model,'options');
        $OPT->setAccessible(true);

        $pk         =   $model->getPk();
        if($order===null){
            //order置空
        }else if ( isset($REQUEST['_order']) && isset($REQUEST['_field']) && in_array(strtolower($REQUEST['_order']),array('desc','asc')) ) {
            $options['order'] = '`'.$REQUEST['_field'].'` '.$REQUEST['_order'];
        }elseif( $order==='' && empty($options['order']) && !empty($pk) ){
            $options['order'] = $pk.' desc';
        }elseif($order){
            $options['order'] = $order;
        }
        unset($REQUEST['_order'],$REQUEST['_field']);

        if(empty($where)){
            $where  =   array('status'=>array('egt',0));
        }
        if( !empty($where)){
            $options['where']   =   $where;
        }
        $options      =   array_merge( (array)$OPT->getValue($model), $options );
        $total        =   $model->where($options['where'])->count();

        if( isset($REQUEST['r']) ){
            $listRows = (int)$REQUEST['r'];
        }else{
            $listRows = C('LIST_ROWS') > 0 ? C('LIST_ROWS') : 10;
        }
        $page = new \Think\Page($total, $listRows, $REQUEST);
        if($total>$listRows){
            $page->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%');
        }
        $p =$page->show();
        $this->assign('_page', $p? $p: '');
        $this->assign('_total',$total);
        $options['limit'] = $page->firstRow.','.$page->listRows;

        $model->setProperty('options',$options);

        return $model->field($field)->select();
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