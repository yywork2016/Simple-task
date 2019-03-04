<?php
namespace Home\Controller;
use Think\Controller;

class MobileTaskController extends MobileController {

	// 获取任务列表
	public function get_task($typeid=1){
		if(!is_numeric($typeid)){
			$data['status']=0;
			$data['info']='非法操作';
			$this->ajaxReturn($data);
		}

		$data['status']=1;
		$data['info']=$this->_getTasks($typeid);
		
		$this->ajaxReturn($data);
	}


    // 创建任务\
    public function addtask($id='')
    {
        if(IS_POST){       
            $_POST['applyid']=UID;
            $_POST['leaderid']=C('TASK_EXAMINE');
            $_POST['fileman']=C('FILE_MANS');
            $mod=D('Task');
            $data = $mod->create();
            if($data){
                if($id){
                    if($mod->filter('strip_tags')->save()){
                        // 修改新任务提醒领导审批
                        $arr[]=array(
                            'title'=>get_nickname(UID).'修改了任务【'.I('post.title','','htmlspecialchars').'】等待审批',
                            'Touid'=>C('TASK_EXAMINE'),
                            'task_id'=>$id,
                            'show_id'=>2
                            );
                        $this->notice($arr); 
                        $back['status']=1;
                        $back['info']='任务编辑成功！';              
                    } else {
                        $back['status']=0;
                        $back['info']='任务编辑失败！';
                    } 
                }else{
                    $backid=$mod->filter('strip_tags')->add();
                    if($backid){  
                        $back['status']=1;
                        $back['info']='任务发布成功！';
                        // 新任务提醒领导审批
                        $arr[]=array(
                            'title'=>get_nickname(UID).'创建了任务【'.I('post.title','','htmlspecialchars').'】等待审批',
                            'Touid'=>C('TASK_EXAMINE'),
                            'task_id'=>$backid,
                            'show_id'=>2
                            );
                        $this->notice($arr);         
                    } else {
                        $back['status']=0;
                        $back['info']='任务发布失败！';
                    }
                }         
            } else {
                $back['status']=0;
                $back['info']=$mod->getError();
            }
            $this->ajaxReturn($back);
        }
    }

    // 我创建的任务
    public function buildtask(){
        $map=array('applyid'=>UID,'status'=>1);
        $_list=D('Task')->lists($map,'id,title,level,applyid,enddt,t_status');
        if($_list){
            foreach ($_list as $key => $value) {
                $_list[$key]['cover']=User_Cover($value['applyid']);
                $_list[$key]['enddt']=time_format($value['enddt'],'Y-m-d');
                $_list[$key]['levelname']=level_Meaning($value['level']);
                if($value['t_status']<1){
                    $_list[$key]['showquick']=1;
                }
            }
            $back['status']=1;
            $back['info']=$_list;
        }else{
            $back['status']=0;
            $back['info']="暂无数据";
        }
        $this->ajaxReturn($back);
    }

    // 获取编辑的信息
    public function taskinfo($id=''){
        if(!is_numeric($id) || empty($id)){
            $data['status']=0;
            $data['info']='非法操作';
            $this->ajaxReturn($data);
        }
        $info=D('Task')->info($id);
        if($info){
            $info['typelist']=explode(',', $info['type']);
            $info['startdt']=time_format($info['startdt'],'Y-m-d');
            $info['enddt']=time_format($info['enddt'],'Y-m-d');
            $info['testmanname']=Get_leader_names($info['testman']);
            $info['distname']=Get_leader_names($info['dist']);
            $back['status']=1;
            $back['info']=$info;
        }else{
            $back['status']=0;
            $back['info']="数据为空";
        }
        $this->ajaxReturn($back);
    }


    // 删除任务
    public function deltask($taskid=''){
        if(!is_numeric($taskid) || empty($taskid)){
            $data['status']=0;
            $data['info']='非法操作';
            $this->ajaxReturn($data);
        }

        $map['id']=$taskid;
        $map['status']=-1;
        if(D('Task')->save($map)){
            $data['status']=1;
            $data['info']='成功删除任务';
        }else{
            $data['status']=0;
            $data['info']='删除任务失败';
        }
        $this->ajaxReturn($data);
    }


	// 任务详情
	public function detail($id=''){
		if(!is_numeric($id) || empty($id)){
			$data['status']=0;
			$data['info']='非法操作';
			$this->ajaxReturn($data);
		}

		$alldata=D('Task')->info($id);
		$resu=D('TaskResult')->taskinfo($id);

		$alldata['nickname']=get_nickname($alldata['applyid']);
		$alldata['level']=level_Meaning($alldata['level']);
		$alldata['addtime']=time_format($alldata['startdt'],'Y-m-d');
		$alldata['endtime']=time_format($alldata['enddt'],'Y-m-d');
		$alldata['cover']=User_Cover($alldata['applyid']);
		$dists=explode(',', $alldata['dist']);

        $button='';
		if(TF_explode_value(UID,$alldata['leaderid']) == true && $alldata['t_status'] < 1 && $alldata['pass_leader_uid'] == NULL){
			$button .='<div class="aui-btn aui-btn-danger task-button" onclick="pop_common(1,'.$id.',\'examine(2)\',\'\')">审批</div>';
		}
		if(in_array(UID, $dists) && $alldata['t_status']==1 && empty($resu)){
    		$button .='<div class="aui-btn aui-btn-info task-button" onclick="get_task('.$id.',2)">领取</div>';
    	}
    	if($resu['t_status']==2){
    		$button .='<div class="aui-btn aui-btn-success task-button" onclick="pop_common(2,'.$id.',\'resultTask('.$resu['id'].',2)\',\'\')">反馈结果</div>';
    	}
        if($resu['t_status']==3){
            $button .='<div class="aui-btn aui-btn-success task-button" onclick="pop_common(2,'.$id.',\'resultTask('.$resu['id'].',2)\','.$resu['id'].')">修改反馈</div>';
        }
    	if(TF_explode_value(UID,C('FILE_MANS')) == true && $alldata['t_status'] == 5){
    		$button .='<div class="aui-btn aui-btn-danger task-button" onclick="fileman('.$id.',2)">归档</div>';
    	}
		$alldata['sbutton']=$button;
		
		// if($resu){
		// 	$alldata=array_merge($alldata,$resu);
		// }

		// 对话式数据流
		// 1、审批
		if($alldata['t_status']<1){
			$szt="未通过";
		}elseif ($alldata['t_status']>=1) {
			$szt="通过";
		}

		$shenpi['cover']=User_Cover($alldata['pass_leader_uid']);
		$shenpi['nickname']=get_nickname($alldata['pass_leader_uid']);
		$shenpi['content']="<span style='color:red'>审批状态<<".$szt.">></span><br/>".$alldata['reason'];
		$shenpi['ctime']=$alldata['pass_time'];
		$shenpi['chatname']="审批";
		if($alldata['t_status'] != 0){
			$chatlist[]=$shenpi;
		}
		// 2、领取任务
		if(!is_superman(UID)){
			if($resu){
				$lingqu['cover']=User_Cover($resu['uid']);
				$lingqu['nickname']=get_nickname($resu['uid']);
				$lingqu['content']="成功领取任务！";
				$lingqu['ctime']=$resu['gettask_time'];
				$lingqu['chatname']="领任务";
				$chatlist[]=$lingqu;
			}
		}else{
			$gettaskslist=D('TaskResult')->where('task_id='.$id)->select();
			if($gettaskslist){
				foreach ($gettaskslist as $key => $value) {
					$lqs['cover']=User_Cover($value['uid']);
					$lqs['nickname']=get_nickname($value['uid']);
					$lqs['content']="成功领取任务！";
					$lqs['ctime']=$value['gettask_time'];
					$lqs['chatname']="领任务";
					$chatlist[]=$lqs;
				}
			}
		}

		// 任务反馈
		if(!is_superman(UID)){
			if($resu['t_status']>=3){
				$contentvalue="<table><tr><td style='color:red;vertical-align: top;width:60px;'>改进结果：</td><td>".nl2br($resu['resu_content'])."</td></tr>";
				$contentvalue .="<tr><td style='color:red;vertical-align: top'>其他说明：</td><td>".nl2br($resu['other_content'])."</td></tr></table>";
				$contentvalue .="<table><tr><td style='color:red'>PCB文件是否有更新：</td><td>".$resu['pcb_update']."</td></tr>";
				$contentvalue .="<tr><td style='color:red'>程序文件是否有更新：</td><td>".$resu['software_update']."</td></tr>";
				$contentvalue .="<tr><td style='color:red'>生产文件是否有更新：</td><td>".$resu['product_update']."</td></tr>";
                if((TF_explode_value(UID,$alldata['testman']) || $alldata['applyid'] == UID || $resu['uid'] == UID) && $resu['t_status'] != 4){
                        $contentvalue .="<tr><td colspan='2' style='padding-top:9px;text-align: center;'><div class=\"aui-btn aui-btn-danger\" onclick='pop_common(3,".$resu['id'].",\"resuTest(".$id.",".$resu['uid'].")\",\"\")'>确认（测试）结果</div></td></tr>";
                    }
                $contentvalue .="</table>";
				$reback['cover']=User_Cover($resu['uid']);
				$reback['nickname']=get_nickname($resu['uid']);
				$reback['content']=$contentvalue;
				$reback['ctime']=$resu['resu_time'];
				$reback['chatname']="反馈结果";
				$chatlist[]=$reback;
			}
		}else{
			if($gettaskslist){
				foreach ($gettaskslist as $kk => $val) {
					if($val['t_status']>=3){
						$contentvalue="<table><tr><td style='color:red;vertical-align: top;width:60px;'>改进结果：</td><td>".nl2br($val['resu_content'])."</td></tr>";
						$contentvalue .="<tr><td style='color:red;vertical-align: top'>其他说明：</td><td>".nl2br($val['other_content'])."</td></tr></table>";
						$contentvalue .="<table><tr><td style='color:red'>PCB文件是否有更新：</td><td>".$val['pcb_update']."</td></tr>";
						$contentvalue .="<tr><td style='color:red'>程序文件是否有更新：</td><td>".$val['software_update']."</td></tr>";
						$contentvalue .="<tr><td style='color:red'>生产文件是否有更新：</td><td>".$val['product_update']."</td></tr>";
                    if((TF_explode_value(UID,$alldata['testman']) || $alldata['applyid'] == UID || $val['uid'] == UID) && $val['t_status'] != 4){
                        $contentvalue .="<tr><td colspan='2' style='padding-top:9px;text-align: center;'><div class=\"aui-btn aui-btn-danger\" onclick='pop_common(3,".$val['id'].",\"resuTest(".$id.",".$val['uid'].")\",\"\")'>确认（测试）结果</div></td></tr>";
                    }
                        $contentvalue .="</table>";
						$reback['cover']=User_Cover($val['uid']);
						$reback['nickname']=get_nickname($val['uid']);
						$reback['content']=$contentvalue;
						$reback['ctime']=$val['resu_time'];
						$reback['chatname']="反馈结果";
						$chatlist[]=$reback;
					}
				}
			}
		}

        // 任务测试反馈
        if(!is_superman(UID)){
            if($resu){
            $task_testlist=D('TaskTest')->where('resu_id='.$resu['id'])->select();
            if($task_testlist){
                foreach ($task_testlist as $kt => $vals) {
                    if($vals['test_status']==0){
                        $test_status="未通过";
                    }else{
                        $test_status="通过";
                    }
                    $ttb['cover']=User_Cover($vals['testman_uid']);
                    $ttb['nickname']=get_nickname($vals['testman_uid']);
                    $ttb['content']="<span style='color:red'>".get_nickname($resu['uid'])."的反馈经检测验证<<".$test_status.">></span><br/>".$vals['test_content'];
                    $ttb['ctime']=$vals['test_time'];
                    $ttb['chatname']="验证结果";
                    $chatlist[]=$ttb;
                }
            }
            }
        }else{
            if($gettaskslist){
                foreach ($gettaskslist as $kal => $valts) {
                    $task_testlist=D('TaskTest')->where('resu_id='.$valts['id'])->select();
                    if($task_testlist){
                        foreach ($task_testlist as $kt => $vals) {
                            if($vals['test_status']==0){
                                $test_status="未通过";
                            }else{
                                $test_status="通过";
                            }
                            $ttb['cover']=User_Cover($vals['testman_uid']);
                            $ttb['nickname']=get_nickname($vals['testman_uid']);
                            $ttb['content']="<span style='color:red'>".get_nickname($valts['uid'])."的反馈经检测验证<<".$test_status.">></span><br/>".$vals['test_content'];
                            $ttb['ctime']=$vals['test_time'];
                            $ttb['chatname']="验证结果";
                            $chatlist[]=$ttb;
                        }
                    }
                }
            }
        }

        // 任务归档
        if($alldata['t_status']==6){
            $files['cover']=User_Cover($alldata['pass_fileman_uid']);
            $files['nickname']=get_nickname($alldata['pass_fileman_uid']);
            $files['content']=get_nickname($alldata['pass_fileman_uid']).'已将此任务归档。';
            $files['ctime']=$alldata['pass_file_time'];
            $files['chatname']="归档";
            $chatlist[]=$files;
        }

		$data['status']=1;
		$data['info']=$alldata;

        // 对chatlist数组进行按时间排序
        if(isset($chatlist)){
            $sort_time=array_column($chatlist, 'ctime');
            array_multisort($sort_time,SORT_ASC,$chatlist);
            foreach ($chatlist as $kkk => $valtask) {
                $chatlist[$kkk]['ctime']=time_format($valtask['ctime'],'Y-m-d');
            }
        }else{
            $chatlist=null;
        }
		$data['chatlist']=$chatlist;

		$this->ajaxReturn($data);

	}


    // 获取已反馈的数据
    public function getResuTask($id=''){
        if(empty($id) || !is_numeric($id)){
                $back['status']=0;
                $back['info']="非法操作！";
                $this->ajaxReturn($back);
        }

        $info=D("TaskResult")->info($id);
        if($info){
            $back['status']=1;
            $back['info']=$info;
        }else{
            $back['status']=0;
            $back['info']="暂无数据";
        }
        $this->ajaxReturn($back);
    }



	 // 领导审批   
    public function examine($id=''){
        if(empty($id) || !is_numeric($id)){
                $back['status']=0;
                $back['info']="非法操作！";
                $this->ajaxReturn($back);
        }
        if(IS_POST){
        	// file_put_contents('./pp.txt', print_r($_POST,true));
            $taskinfo=D('Task')->info($id,'title,dist,applyid,pass_leader_uid');
            // 先判断是否已经有其他领导审批过，已经审批了提示哪个领导已经做了审批
            if($taskinfo['pass_leader_uid']){
                    $back['status']=0;
                    $back['info']=get_nickname($taskinfo['pass_leader_uid']).'已经做了此任务的审批！';
                    $this->ajaxReturn($back);
            }
            // 加入系统提醒
            if($_POST['t_status']<1){
                $tstatus="审批不通过";
            }else{
                $tstatus="审批通过";
                // 审批通过 提醒任务接收者有新任务
                $arr[]=array(
                    'title'=>"你有新的任务【".$taskinfo['title']."】",
                    'Touid'=>$taskinfo['dist'],
                    'task_id'=>$id,
                    'show_id'=>3
                    );
            }
            // 提醒任务发布者任务是否通过审批
            $arr[]=array(
                'title'=>"【".$taskinfo['title']."】".get_nickname(UID).$tstatus,
                'Touid'=>$taskinfo['applyid'],
                'task_id'=>$id,
                'show_id'=>1
                );
            $this->notice($arr);
            // 保存审批
            $_POST['pass_time']=NOW_TIME;
            $_POST['pass_leader_uid']=UID;
            unset($_POST['type']);
            $mod=D('Task');
            $data = $mod->create();
            if($data){
                if($mod->filter('strip_tags')->save()){
                    $back['status']=1;
                    $back['info']="审批成功";
                }else{
                    $back['status']=0;
                    $back['info']="审批失败";
                }
            } else {
                $back['status']=0;
                $back['info']=$mod->getError();
            }
            $this->ajaxReturn($back);
        }
    }


    // 领取任务
    public function get_mytask($tid=''){
    	if(IS_POST){
    		if(empty($tid) || !is_numeric($tid)){
    			$data['status']=0;
                $data['info']="非法操作！";
    			$this->ajaxReturn($data);
    		}
            // 检查是否非法领取任务
            // 任务指派人
        $zhipai='((dist like "'.UID.'" or dist like "%,'.UID.'" or dist like "%,'.UID.',%" or dist like "'.UID.',%") and t_status=1)';
            $where='id='.$tid.' and '.$zhipai;
            $istrue=M('task')->where($where)->find();
            if(empty($istrue)){
                $data['status']=0;
                $data['info']="此任务无需你的参与！";
                $this->ajaxReturn($data);
            }
            // 检查是否已经领取过任务
            $checktask=D('TaskResult')->taskinfo($tid);
            if($checktask){
                $data['status']=0;
                $data['info']="你已领取过此任务！";
                $this->ajaxReturn($data);
            }

            // 事务开始
    		// $modtask=D();
    		$saves['task_id']=$tid;
    		$saves['uid']=UID;
    		$saves['gettask_time']=time();
    		// $modtask->startTrans();

    		$resu_one=D('TaskResult')->add($saves);
    		if($resu_one){
    			$data['status']=1;
    			$data['info']="任务领取成功！";
                // 提醒任务发布者任务已被领取
                $arr[]=array(
                    'title'=>get_nickname(UID)."已领取你的任务【".Get_Task_Title($tid)."】",
                    'Touid'=>Get_Task_Applyid($tid),
                    'task_id'=>$tid,
                    'show_id'=>1
                    );
                $this->notice($arr);
    		}else{
    			$data['status']=0;
                $data['info']="发生错误！";
    		} 		
    	}else{
    		$data['status']=0;
            $data['info']="发生错误！";
    	}
    	$this->ajaxReturn($data);
    }


    // 反馈任务结果
    public function taskResult(){
    	if(IS_POST){
            $_POST['resu_time']=NOW_TIME;
            $_POST['t_status']=3;
            // 提醒任务发布者任务结果已经反馈
            $tid=I('post.task_id','','htmlspecialchars');
            $testman_arr=explode(',', Get_Task_Testmanid($tid));
            if(in_array(Get_Task_Applyid($tid), $testman_arr) ){
                $TOTEST=Get_Task_Testmanid($tid);
            }else{
                $TOTEST=Get_Task_Testmanid($tid).','.Get_Task_Applyid($tid);
            }
            // 提醒任务结果确定人检测任务反馈结果
            $arr[]=array(
                'title'=>get_nickname(UID)."已提交【".Get_Task_Title($tid)."】反馈结果，等待你的测试验证确定",
                'Touid'=>$TOTEST,
                'task_id'=>$tid,
                'show_id'=>1
                );
            $this->notice($arr);
    		// parent::edit('TaskResult','replytask');
    		$mod=D('TaskResult');
            $data = $mod->create();
            if($data){
                if($mod->filter('strip_tags')->save()){
                    $back['status']=1;
                    $back['info']="反馈成功";
                }else{
                    $back['status']=0;
                    $back['info']="反馈失败";
                }
            } else {
                $back['status']=0;
                $back['info']=$mod->getError();
            }
            $this->ajaxReturn($back);
    	}
    }


// 测试者测试任务  并反馈 任务人
    public function task_test($id=''){
        if(IS_POST){
            // 系统系统测试是否通过或不通过
            $test_status=I('post.test_status',0,'htmlspecialchars');
            $tid=I('post.task_id','','htmlspecialchars');
            $touid=I('post.touid','','htmlspecialchars');
            $task_title=Get_Task_Title($tid);
            if($test_status==1){
                $TaskResult=D('TaskResult');
                $be_title="通过";
                // 测试通过则更新任务反馈表状态
                $map['t_status']=4;
                $map['id']=I('post.resu_id','','htmlspecialchars');
                $TaskResult->save($map);
                // 通过状态即完成，需要这个任务的所有任务人都完成了才通知归档人             
                if(Check_Task_isover($tid)){
                    $arr[]=array(
                    'title'=>"【".$task_title."】已完成，等待归档",
                    'Touid'=>Get_task_Fileman($tid),
                    'task_id'=>$tid,
                    'show_id'=>1
                    );
                }
                // 通知发布任务者，谁的任务已完成
                $arr[]=array(
                'title'=>"你发布的任务【".$task_title."】".get_nickname($touid)."已完成",
                'Touid'=>Get_Task_Applyid($tid),
                'task_id'=>$tid,
                'show_id'=>1
                );
            }else{
                $be_title="不通过";
            }
            $arr[]=array(
                'title'=>get_nickname(UID)."对你的任务【".$task_title."】反馈结果确认（测试）为".$be_title,
                'Touid'=>$touid,
                'task_id'=>$tid,
                'show_id'=>1
                );
            $this->notice($arr);         

            // 保存测试反馈结果
            $mod=D('TaskTest');
            $data = $mod->create();
            if($data){
                $id=$mod->filter('strip_tags')->add();
                if($id){           
                    $beback['status']=1;
                    $beback['info']="测试反馈成功！";
                } else {
                    $beback['status']=0;
                    $beback['info']="测试反馈失败！";
                }              
            } else {
                $beback['status']=0;
                $beback['info']=$mod->getError();
            }
            $this->ajaxReturn($beback);
        }
    }


    // 归档操作
    public function Todofile($id=''){
        if(empty($id) || !is_numeric($id)){
            $back['status']=0;
            $back['info']="非法操作！";
            $this->ajaxReturn($back);
        }

        if(TF_explode_value(UID,C('FILE_MANS'))==false){
            $back['status']=0;
            $back['info']="非法操作！";
            $this->ajaxReturn($back);
        }
        $map['id']=$id;
        $map['pass_fileman_uid']=UID;
        $map['pass_file_time']=time();
        $map['t_status']=6;
        if(D('Task')->save($map)){
            $back['status']=1;
            $back['info']="归档成功！";            
        }else{
            $back['status']=0;
            $back['info']="归档失败！";
        }
        $this->ajaxReturn($back);
    }





	// 封装函数
	public function _getTasks($typeid){
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

        // dump($where);
        // 全部
        $_list=D('Task')->lists($where,'id,title,enddt,dist,leaderid,applyid,level,t_status');
        foreach ($_list as $key => $value) {
            // 任务结果表过滤
            $resu_info=D('TaskResult')->taskinfo($value['id']);
            $value['cover']=User_Cover($value['applyid']);
            $value['levelname']=level_Meaning($value['level']);
            $value['enddt']=time_format($value['enddt'],'Y-m-d');
            $dists=explode(',', $value['dist']);
            // 我的新任务
            // 等待我审批
            if($value['t_status']==0){
                if(TF_explode_value(UID,$value['leaderid']) == true && $value['t_status'] < 1 && $value['pass_leader_uid'] == NULL){
					$value['showquick']=1;	
				}
            	$backtask[]=$value;            	
            }
            // 等待我领取
            if($value['t_status']==1 && empty($resu_info)){   
            	if(in_array(UID, $dists) && $value['t_status']==1 && empty($resu_info)){
    				$value['showquick']=1;
    			}         	
            	$lingqu[]=$value;            	
            }

            // 待归档
            if(TF_explode_value(UID,C('FILE_MANS')) == true && $value['t_status'] == 5){
		    	if($value['t_status']==5 && Get_task_Fileman($value['id'])==true){
		    		$value['showquick']=1;
		    	}
            	$guidang[]=$value;             
            }

            // 执行中的任务
            if($resu_info['t_status']==2 || $resu_info['t_status']==3){
            	if($resu_info['t_status']==2){
            		$value['showquick']=1;
            		$value['resuid']=$resu_info['id'];
            	}
                if($resu_info['t_status']==3){
                    $value['showquick']=2;
                    $value['resuid']=$resu_info['id'];
                }
                $ongoingtask[]=$value;
            }

            // 任务结果待验证
            if($resu_info){
                $testinfo=D('TaskTest')->where('resu_id='.$resu_info['id'])->find();
                if((empty($testinfo) || $testinfo['test_status']==0) && $resu_info['t_status']==3){
                    $betest[]=$value;
                }
            }
            

            // 已完成的任务  完成归档
            if($resu_info['t_status']==4 || $value['t_status']==6){
                $donetask[]=$value;
            }
        }
        if($backtask){
			$exam['task_category']="待审批";
			$exam['tasktypeid']=1;
			$exam['_']=$backtask;
	        $newtask[]=$exam;
        }
        if($lingqu){
        	$lq['task_category']="待领取";
        	$lq['tasktypeid']=2;
        	$lq['_']=$lingqu;
        	$newtask[]=$lq;
        }
        if($betest){
            $yz['task_category']="待验证";
            $yz['tasktypeid']=3;
            $yz['_']=$betest;
            $newtask[]=$yz;
        }
        if($guidang){
        	$gd['task_category']="待归档";
        	$gd['tasktypeid']=4;
        	$gd['_']=$guidang;
        	$newtask[]=$gd;
        }    
        

        if($typeid==1){
            return $newtask;
        }elseif ($typeid==2) {
            return $ongoingtask;
        }else{
            return $donetask;
        }
        
    }




}