<?php
namespace Home\Model;
use Think\Model;

class TaskModel extends Model{
    /* 模型自动验证 */
    protected $_validate = array(
        array('title','require','任务名称必须！'),
        array('type','checktypevalue','请选择反馈类型！',self::EXISTS_VALIDATE,'callback'),        
        array('startdt','require','反馈时间必须！'),
        array('enddt','require','要求完成时间必须！'),
        array('enddt','checkenddt','要求完成时间不能小于反馈时间！',0,'callback'),
        array('applyid','require','申请人必须！'),
        array('leaderid','require','任务审核（领导）必须！'),        
        array('content','require','任务说明必须！'),
        array('dist','require','任务接收人必须！'),
        array('level','require','等级选择必须！'),
    );

    /* 模型自动完成 */
    protected $_auto = array(
        array('status', 1, self::MODEL_INSERT, 'string'),
        array('startdt', 'str_startdt', 3, 'callback'),
        array('enddt', 'str_enddt', 3, 'callback'),
    );
    //获取列表
    public function lists($where='',$field=true,$order='level asc,id desc',$limit='')
    {
    	return $this->where($where)->field($field)->order($order)->limit($limit)->select();
    }
    // 获取详情
    public function info($id,$field=true){
    	return $this->where('id='.$id)->field($field)->find();
    }
    // 开始时间
    protected function str_startdt(){
        $startdt    =   I('post.startdt','','htmlspecialchars');
        return $startdt?strtotime($startdt):NOW_TIME;
    }

    // 结束时间
    protected function str_enddt(){
        $enddt    =   I('post.enddt','','htmlspecialchars');
        return $enddt?strtotime($enddt):NOW_TIME;
    }

    protected function checkenddt(){
        $startdt    =   I('post.startdt','','htmlspecialchars');
        $enddt    =   I('post.enddt','','htmlspecialchars');

        if(empty($enddt)){
            return false;
        }

        if(strtotime($startdt)>strtotime($enddt)){
            return false;
        }

        return true;

    }

    protected function checktypevalue(){
        $type    =   I('post.type','','htmlspecialchars');
        if(empty($type)){
            return false;
        }
        return true;
    }


}