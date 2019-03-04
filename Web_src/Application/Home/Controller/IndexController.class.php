<?php
namespace Home\Controller;
use Think\Controller;

class IndexController extends HomeController {

    public function index(){

        $this->display();
    }

    public function main($type=''){
    	if(empty($type) || !is_numeric($type)){
    		$this->error('非法操作！');
    	}
    	$newtask=array();
    	$ongoingtask=array();
    	$donetask=array();
    	
    	$where='status=1';
        if(!is_superman(UID)){
        // 任务指派人  任务接收人
        $zhipai='((dist like "'.UID.'" or dist like "%,'.UID.'" or dist like "%,'.UID.',%" or dist like "'.UID.',%") and t_status>=1)';
        // 任务审核人
        $shenhe='((leaderid like "'.UID.'" or leaderid like "%,'.UID.'" or leaderid like "%,'.UID.',%" or leaderid like "'.UID.',%") and t_status<1 and pass_leader_uid IS NULL)';
        // 通知归档人归档，任务完成
        $fileman='((fileman like "'.UID.'" or fileman like "%,'.UID.'" or fileman like "%,'.UID.',%" or fileman like "'.UID.',%") and t_status>=5)';
        }else{
            $zhipai="t_status>=1";
            $shenhe="t_status<1 and pass_leader_uid IS NULL";
            $fileman="t_status>=5";
        }

        // 读取属于我的任务 和 属于我审批的未审批任务  
        $where .=' and ('.$zhipai.' or '.$shenhe.' or '.$fileman.')';

    	// 本月
    	if($type==2){
	    	// 本月第一天
	    	$month_first=mktime(0,0,0,date('n'),1,date('Y'));
	    	// 本月最后一天
	    	$month_last=mktime(23,59,59,date('n'),date('t'),date('Y'));
	    	$where .=' and startdt>='.$month_first.' and startdt<='.$month_last;
    	}

    	// 本周
    	if($type==3){
	    	// 本周第一天（星期一为一周开始）
	    	$week_first=strtotime(date('Y-m-d 00:00:00', time()-86400*date('w')+(date('w')>0?86400:-6*86400)));
	    	// 本周最后一天
	    	$week_last=strtotime(date("Y-m-d 23:59:59",strtotime("+0 week Sunday")));
	    	$where .=' and startdt>='.$week_first.' and startdt<='.$week_last;
    	}
        // dump($where);
    	// 全部
    	$_list=D('Task')->lists($where,'id,title,enddt,dist,leaderid,applyid,level,t_status');
    	foreach ($_list as $key => $value) {
            // 任务结果表过滤
            $resu_info=D('TaskResult')->taskinfo($value['id']);

            // 我的新任务/等待我审核、领取 
    		if($value['t_status']<=1 && empty($resu_info)){
    			$newtask[]=$value;
    		}
            // 待归档
            if($value['t_status']==5){
                $newtask[]=$value;
            }
            // 执行中的任务
    		if($resu_info['t_status']==2 || $resu_info['t_status']==3){
    			$ongoingtask[]=$value;
    		}
            // 已完成的任务  完成归档
    		if($resu_info['t_status']==4 || $value['t_status']==6){
    			$donetask[]=$value;
    		}
    	}

    	$this->assign('newtask',$newtask);
    	$this->assign('ongoingtask',$ongoingtask);
    	$this->assign('donetask',$donetask);

    	$this->display();
    }

    
}