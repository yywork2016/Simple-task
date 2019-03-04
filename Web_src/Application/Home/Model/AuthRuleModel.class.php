<?php
namespace Home\Model;
use Think\Model;

class AuthRuleModel extends Model{
    
    const RULE_URL = 1;
    const RULE_MAIN = 2;

    /* 模型自动验证 */
    protected $_validate = array(
        array('title','require','标题必须！'),
        array('url','require','链接必须！'),
    );

    /* 模型自动完成 */
    protected $_auto = array(
        array('module', 'home', self::MODEL_INSERT, 'string'),
    );

    // 按父类ID获取列表
	public function lists($pid=0,$status=-1,$order='sort asc,id asc',$field=true)
	{
        if($status==-1){
            $where='pid='.$pid.' and status>-1';
        }else{
            $where='pid='.$pid.' and status='.$status;
        }
		return $this->where($where)->order($order)->field($field)->select();
	}

    // 按ID获取详情
    public function info($id){
        return $this->where('id='.$id)->find();
    }

}