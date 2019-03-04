<?php
namespace Home\Model;
use Think\Model;

class NoticeModel extends Model{
    /* 模型自动验证 */
    protected $_validate = array(

    );

    /* 模型自动完成 */
    protected $_auto = array(
        array('uid', UID, self::MODEL_INSERT),
        array('ctime', NOW_TIME, self::MODEL_INSERT),
    );
    //获取列表
    public function lists($where='',$field=true,$order='status asc,id desc',$limit='')
    {
    	return $this->where($where)->field($field)->order($order)->limit($limit)->select();
    }
    // 获取详情
    public function info($id,$field=true){
    	return $this->where('id='.$id)->field($field)->find();
    }
}