<?php
namespace Home\Controller;
use Think\Controller;
class SystemController extends HomeController {
    public function index(){
        if(IS_POST){
            $Menu = D('AuthRule');
            $id=I('post.id','','htmlspecialchars');
            $data = $Menu->create();
            if($data){
                if($id){
                    if($Menu->save()){
                        $this->success('修改成功', Cookie('__forward__'));
                    }else{
                        $this->error('修改失败');
                    }
                }else{
                    $backid = $Menu->add();
                    if($backid){
                        $this->success('新增成功', Cookie('__forward__'));
                    } else {
                        $this->error('新增失败');
                    }
                }                
            } else {
                $this->error($Menu->getError());
            }
        } else {
            $pid=I('get.pid','0','htmlspecialchars');
            $list=D('AuthRule')->lists($pid);
            foreach ($list as $key => $value) {
                if($value['pid']==0){
                    $list[$key]['pid']="无";
                }else{
                    $list[$key]['pid']=get_table_field($value['pid'],'id', 'title', 'AuthRule');
                }   
                if($value['status']==0){
                    $list[$key]['status']="是";
                }else{
                    $list[$key]['status']="否";
                }     
            }
            $menu=D('AuthRule')->where('status=1')->select();
            $menus = D('Common/Tree')->toFormatTree($menu);
            $menus = array_merge(array(0=>array('id'=>0,'title_show'=>'顶级菜单')), $menus);
           
    		$this->assign('list',$list);
            $this->assign('Menus', $menus);
            $this->meta_title = '新增菜单';
            $this->display();
        }
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
            $this->success('删除成功');
        } else {
            $this->error('删除失败！');
        }
    }




    
}