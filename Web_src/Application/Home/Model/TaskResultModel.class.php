<?php
namespace Home\Model;
use Think\Model;

class TaskResultModel extends Model{
    /* 模型自动验证 */
    protected $_validate = array(
        // array('resu_content','1,10000','改进结果必须！'),     
    );

    /* 模型自动完成 */
    protected $_auto = array(
        array('uid', UID, self::MODEL_INSERT),
        array('gettask_time', NOW_TIME, self::MODEL_INSERT),
    );

    //获取列表
    public function lists($where='',$field=true)
    {
        return $this->where($where)->field($field)->select();
    }
    // 根据ID获取详情
    public function info($id){
        return $this->where('id='.$id)->find();
    }

    // 根据TASKID获取详情
    public function taskinfo($tid){
        return $this->where('task_id='.$tid.' and uid='.UID)->find();
    }

}