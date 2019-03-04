<?php
namespace Home\Controller;
use Think\Controller;
class GroupController extends HomeController {
	//组织架构列表
	public function index(){
		$modGroup=D('Group');
		$tree=$modGroup->getTree(0,'id,title,sort,pid,status');;
		C('_SYS_GET_CATEGORY_TREE_', true); //标记系统获取分类树模板
        // 记录当前列表页的cookie
        // Cookie('__forward__',$_SERVER['REQUEST_URI']);
		$this->assign('tree',$tree);
		$this->display();
	}

    public function add(){
        $this->assign('Menus', $this->_menu());
        parent::add();
    }


    public function edit(){
        $this->assign('Menus', $this->_menu());
        parent::edit('Group','add');
    }


    public function _menu(){
        $group=D('Group')->order('sort asc')->select();
        $menus = D('Common/Tree')->toFormatTree($group);
        $menus = array_merge(array(0=>array('id'=>0,'title_show'=>'顶级部门')), $menus);
        return $menus;
    }

    /**
     * 设置一条或者多条数据的状态
     */
    public function setStatus($model='Group'){
        return parent::setStatus('Group');
    }


    /**
     * 显示分类树，仅支持内部调
     * @param  array $tree 分类树
     */
    public function tree($tree = null){
        C('_SYS_GET_CATEGORY_TREE_') || $this->_empty();
        $this->assign('tree', $tree);
        $this->display('tree');
    }




}