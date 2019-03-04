<?php
namespace Home\Controller;
use Think\Controller;
class AuthRuleController extends HomeController {
    public function index(){
            $pid=I('get.pid','0','htmlspecialchars');
            $list=D('AuthRule')->lists($pid);
            foreach ($list as $key => $value) {
                if($value['pid']==0){
                    $list[$key]['pid']="无";
                }else{
                    $list[$key]['pid']=get_table_field($value['pid'],'id', 'title', 'AuthRule');
                }   
                $list[$key]['status']=status_Meaning($value['status']);    
            }
           
            // 记录当前列表页的cookie
            // Cookie('__forward__',$_SERVER['REQUEST_URI']);
            $ppid=D('AuthRule')->where('id='.$pid)->getField('pid');
            // dump($ppid);
            $this->assign('ppid',$ppid);
            $this->assign('list',$list);            
            $this->display();
    }

    //新增
    public function add(){
        $this->assign('Menus', $this->_menus());
        parent::add();
    }

    //获取编辑数据
    public function edit(){
        $this->assign('Menus', $this->_menus());
        parent::edit('AuthRule','add');     
    }

    //public
    public function _menus(){
        $menu=D('AuthRule')->where('status=1')->select();
        $menus = D('Common/Tree')->toFormatTree($menu);
        $menus = array_merge(array(0=>array('id'=>0,'title_show'=>'顶级菜单')), $menus);
        return $menus;
    }


    /**
     * 设置一条或者多条数据的状态
     */
    public function setStatus($model='AuthRule'){
        return parent::setStatus('AuthRule');
    }

    /**
     * 删除后台菜单
    */
    public function del(){
        $id = array_unique((array)I('id',0));

        if ( empty($id) ) {
            $this->error('请选择要操作的数据!');
        }

        $map = array('id' => array('in', $id) );
        if(M('AuthRule')->where($map)->delete()){
            session('ADMIN_MENU_LIST',null);
            $this->success('删除成功', Cookie('__forward__'));
        } else {
            $this->error('删除失败！');
        }
    }


    // 排序
    public function sort($id='',$sort=''){
        if(empty($id) || !is_numeric($id) || empty($id) || !is_numeric($id)){
            $this->error('非法操作！');
        }

        $map['id']=$id;
        $map['sort']=$sort;
        if(M('auth_rule')->save($map)){
            $this->success('排序成功！');
        }else{
            $this->error('排序失败！');
        }
    }


    /**
     * 获取左侧菜单
     */
    public function getMenu($pid=''){
        if(IS_POST){
            if($pid==0) $this->ajaxReturn(true);
            $menulist=D('AuthRule')->lists($pid,1);
            // 获取当前用户所有授权节点
            $get_user_auth=$this->GetAll_AUTH();
            foreach ($menulist as $key => $value) {
                $menulist[$key]['url']=get_nav_url($value['url']);
            }
            if(!IS_ROOT){
                foreach ($menulist as $k => $val) {
                    if(in_array($val['id'], $get_user_auth)){
                        $newmenu[]=$val;
                    }
                }
                $back['_']=$newmenu;
            }else{
                $back['_']=$menulist;
            }
            $back['categoryTitle']=get_table_field($pid,'id', 'title', 'AuthRule');
            $this->ajaxReturn($back);
        }
    }


   
}