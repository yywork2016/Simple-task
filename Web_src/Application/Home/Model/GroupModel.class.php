<?php
namespace Home\Model;
use Think\Model;

class GroupModel extends Model{
    /* 模型自动验证 */
    protected $_validate = array(
        array('title','require','部门名称必须！'),
    );

	public function lists(){
		$list=$this->where('status=1')->order('sort')->select();
		return $list;
	}

	    /**
     * 获取分类树，指定分类则返回指定分类极其子分类，不指定则返回所有分类树
     * @param  integer $id    分类ID
     * @param  boolean $field 查询字段
     * @return array          分类树
     */
    public function getTree($id = 0, $field = true){
        /* 获取当前分类信息 */
        if($id){
            $info = $this->info($id);
            $id   = $info['id'];
        }

        /* 获取所有分类 */
        $list = $this->field($field)->order('sort')->select();
        foreach ($list as $key => $value) {
        	if($value['pid']==0){
        		$list[$key]['pidname']='-';
        	}else{
        		$list[$key]['pidname']=get_table_field($value['pid'],'id','title','group');
        	}
        }
        $list = list_to_tree($list, $pk = 'id', $pid = 'pid', $child = '_', $root = $id);

        /* 获取返回数据 */
        if(isset($info)){ //指定分类则返回当前分类极其子分类
            $info['_'] = $list;
        } else { //否则返回所有分类
            $info = $list;
        }
        return $info;
    }


	public function info($id){
		return $this->where('id='.$id)->find();
	}

}