<?php
namespace Home\Controller;
use Think\Controller;
class TaskController extends HomeController {

	//列表
    public function index(){


    }

        // 我的任务
    public function mytask($t_status=-1,$p=1){        
    	$map=' status=1 ';
    	$title=I('get.title','','strip_tags');
        if($title){
            $map .=' and title like "%'.$title.'%"';
        }

        if(!is_superman(UID)){
        // 任务指派人
        $zhipai='((dist like "'.UID.'" or dist like "%,'.UID.'" or dist like "%,'.UID.',%" or dist like "'.UID.',%") and (t_status=1 or t_status=5 or t_status=6))';
        // 任务审核人
        $shenhe='((leaderid like "'.UID.'" or leaderid like "%,'.UID.'" or leaderid like "%,'.UID.',%" or leaderid like "'.UID.',%") and t_status<1 and pass_leader_uid IS NULL)';
        // 测试人任务
        $testman='((testman like "'.UID.'" or testman like "%,'.UID.'" or testman like "%,'.UID.',%" or testman like "'.UID.',%") and t_status=1)';
        // 通知归档人归档，任务完成
        $fileman='((fileman like "'.UID.'" or fileman like "%,'.UID.'" or fileman like "%,'.UID.',%" or fileman like "'.UID.',%") and t_status>4)';            
        }else{
            $zhipai="(t_status=1 or t_status=5 or t_status=6)";
            $shenhe="t_status<1 and pass_leader_uid IS NULL";
            $testman="t_status=1";
            $fileman="t_status>4";
        }
        // 全部任务
        if($t_status==-1){
            $map .=' and '.$zhipai.' or '.$shenhe.' or '.$fileman;
        }
        // 待审批
        if($t_status==0){
            $map .=' and '.$shenhe;
        }
        // 待执行 待领取任务
        if($t_status==1){
            $map .=' and '.$zhipai;
        }
        // 任务已完成  对单任务接收人
        // if($t_status==4){
        //     $map .=' and b.t_status=4 and '.$zhipai;
        // }
        // 待归档
        if($t_status==5){
            $map .=' and t_status=5 and '.$fileman;
        }
        // 已归档
        if($t_status==6){
            $map .=' and t_status=6 and '.$fileman;
        }
        

        // t_status大于等于2时，进行联合查询
        if($t_status>=2 && $t_status<=4){
            $map .=' and b.t_status='.$t_status.' and uid='.UID;
            // 统计总数
            $total=M('task')->alias('a')
            ->join(C('DB_PREFIX').'task_result b on a.id=b.task_id')
            ->field('a.id')
            ->where($map)
            ->count();

            $listRows = C('LIST_ROWS') > 0 ? C('LIST_ROWS') : 10;
            $page = new \Think\Page($total, $listRows);            
            $_list=M('task')->alias('a')
            ->join(C('DB_PREFIX').'task_result b on a.id=b.task_id')
            ->field('a.id,title,type,level,startdt,enddt,dist,leaderid,applyid,b.t_status')
            ->where($map)
            ->order('level asc,id desc')
            ->limit($page->firstRow.','.$page->listRows)
            ->select();

            if($total>$listRows){
                $page->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%');
            }
            $p =$page->show();
            $this->assign('_page', $p? $p: '');
            $this->assign('_total',$total);
        }else{
            $_list=$this->lists('task',$map,'level asc,id desc');
        }
        // dump($map);
        // dump($map);
        // t_status为0时的处理
    	if($t_status==0 && is_numeric($t_status)){
    		$t_status=100;
    	}
    	$this->assign('_list',$_list);
    	$this->assign('t_status',(int)$t_status);
    	$this->display();
    }


    // 我新建任务
    public function buildtask($t_status=''){
    	$title=I('get.title','','htmlspecialchars');
    	$map=array('applyid'=>UID,'status'=>1);
		if(is_numeric($t_status)){
    		if($t_status!=-1){
    			$map['t_status']=$t_status;
    		}  		
    	}
    	if($title){
    		$map['title']=array('like',"%".$title."%");
    	}
    	$_list=$this->lists('task',$map,'id desc');
    	if($t_status==0 && is_numeric($t_status)){
    		$t_status=100;
    	}
    	$this->assign('t_status',(int)$t_status);
    	$this->assign('_list',$_list);
    	$this->display('mytask');
    }

    // 未完成任务
    public function bedone(){
    	$_list=D('Task')->lists();
    	$this->assign('_list',$_list);
    	$this->display();
    }

    // 创建任务
    public function addtask($type='',$file_id=''){
    	if(IS_POST){
    		$_POST['type']=implode(',', $type);

            // 相关文件处理
            if(!empty($file_id)){
                $_POST['file_id']=$this->_uploadFile($file_id);
            }else{
                unset($_POST['file_id']);
            }
            
    	}
        $this->assign('arr_level',Set_Level());
    	$this->assign('typeid_arr',array());
    	parent::add();
    }

    // 编辑任务
    public function edit($id='', $type='', $file_id=''){
    	if(empty($id) && !is_numeric($id)){
    		$this->error('错误！');
    	}
    	if(IS_POST){
            $_POST['startdt']=strtotime($_POST['startdt']);
            $_POST['enddt']=strtotime($_POST['enddt']);
    		$_POST['type']=implode(',', $type);
            // 相关文件处理
            if(!empty($file_id)){
                $_POST['file_id']=$this->_uploadFile($file_id);
            }else{
                unset($_POST['file_id']);
            }
    	}else{
            // 相关文件处理
            $file_id=get_table_field($id, 'id', 'file_id', 'task');
            if($file_id){
                $file_ids=explode(',', $file_id);
                foreach ($file_ids as $value) {
                    $data=D('File')->info($value);
                    $filear['shuju'] = think_encrypt(json_encode($data));
                    $filear['name'] =$data['name'];
                    $file_arr[]=$filear;
                }
                $this->assign('file_arr',$file_arr);
            }
            $this->assign('arr_level',Set_Level());
        }
    	parent::edit('Task','add');
    }

    // 查看任务详情  $type 2为反馈结果调用
    public function task_detail($id='',$type=1,$tpl=''){
    	if(empty($id) || !is_numeric($id)){
    		$this->error('错误！');
    	}
        if(isset($_GET['type'])){
            $this->assign('jur',$_GET['type']);
        }
    	$info=D('Task')->info($id);
        if($info['file_id']){
            $files=explode(',', $info['file_id']);
            $this->assign('files',$files);
        }
        if($type==2){
            // 任务反馈者页面调用
        	$resu=D('TaskResult')->taskinfo($id);
            $this->assign('resu',$resu);            
        }else{
            // 任务详情调用
            $map['task_id']=$id;
            // 超级权限操作
            if(!is_superman(UID)){
                $map['uid']=UID;
            }
            $resu_list=D('TaskResult')->lists($map);
            foreach ($resu_list as $key => $value) {
                $resu_list[$key]['_']=D('TaskTest')->lists('resu_id='.$value['id']);
            }            
            $this->assign('resu_list',$resu_list);
        }
    	$this->assign('info',$info);
    	$this->display($tpl);
    }

    // 我的任务  下拉操作列表   $tid为表task中的id值；
    public function dropdown_taskmenu($tid=''){
    	if(IS_GET){
    		if(empty($tid) || !is_numeric($tid)){
    			$data['status']=0;
    			$this->ajaxReturn($data);
    		}
    		$modtask=D('Task');
    		$taskinfo=$modtask->info($tid);
            $resuinfo=D('TaskResult')->taskinfo($tid);
    		$dists=explode(',', $taskinfo['dist']);
            $leaders=explode(',', $taskinfo['leaderid']);
    		$backhtml='<li><a href="javascript:addModal(\''.U("task_detail?id=".$tid).'\');">详情</a></li>';
            // 领导审核任务
            if(in_array(UID, $leaders) && $taskinfo['t_status']<=0){
                $backhtml .='<li><a href="javascript:addModal(\''.U("examine",array('id'=>$tid,'jur'=>3)).'\');" style="color:#F00">审批</a></li>';
            }

    		// 如果任务接收人为UID 并且 任务状态为待执行  则显示  领取任务链接
    		if(in_array(UID, $dists) && $taskinfo['t_status']==1 && empty($resuinfo)){
    			$backhtml .='<li><a href="javascript:Get_mytask(1,\''.U("Task/get_mytask?tid=".$tid).'\');" style="color:#F00">领取任务</a></li>';
    		}
    		// 领取任务后 显示可操作反馈结果
    		if($resuinfo['t_status']==2){
    			$backhtml .='<li><a href="javascript:addModal(\''.U("taskResult?id=".$tid).'\');" style="color:#337AB7">反馈结果</a></li>';
    		}    

            // 归档操作
            if(TF_explode_value(UID,C('FILE_MANS')) == true && $taskinfo['t_status'] == 5){
                $backhtml .='<li><a class="ajax-get confirm" href="javascript:;" url="'.U('Task/Todofile?id='.$info['id']).'" style="color:#F00">归档</a></li>';
            } 

    		// 当发布者为当前用户
    		$mtask=$modtask->where('id='.$tid)->getField('applyid');
    		if($mtask==UID){    			
    			if(!in_array($resuinfo['uid'], $dists)){
                $backhtml .='<li role="separator" class="divider"></li>';
                $backhtml .='<li><a href="javascript:;" class="getuser" type-id="checkbox" url="'.U('Users/getUser_dept').'" data-name="任务接收人" data-tid="2" data-toggle="modal" data-target=".bs-example-modal-sm" data-jumpurl="'.U('sendDists',array('id'=>$tid)).'">指派给···</a></li>';
                }    			
                if($taskinfo['t_status']<1){
                    $backhtml .='<li role="separator" class="divider"></li>';
                    $backhtml .='<li><a href="javascript:addModal(\''.U("edit?id=".$tid).'\');" style="color:#337AB7">编辑</a></li>';
    			     $backhtml .='<li><a class="ajax-get" href="'.U("setStatus",array('ids'=>$tid,'status'=>-1)).');">删除</a></li>';
                }
    		}

    		$data['status']=1;
    		$data['info']=$backhtml;
    	}else{
    		$data['status']=0;
    	}

    	$this->ajaxReturn($data);
    }

    // 领导审批   
    public function examine($id=''){
        if(empty($id) || !is_numeric($id)){
            $this->error('非法操作！');
        }
        if(IS_POST){
            $taskinfo=D('Task')->info($id,'title,dist,applyid,pass_leader_uid');
            // 先判断是否已经有其他领导审批过，已经审批了提示哪个领导已经做了审批
            if($taskinfo['pass_leader_uid']){
                $this->error(get_nickname($taskinfo['pass_leader_uid']).'已经做了此任务的审批！');
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
            parent::edit('Task','examine');
        }else{
            $info=D('Task')->info($id,'id,title,t_status,reason');
            $this->assign('info',$info);
            $this->display();
        }
    }


    // 领取任务
    public function get_mytask($tid=''){
    	if(IS_GET){
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
                // 提醒任务发布者任务已被领取
                $arr[]=array(
                    'title'=>get_nickname(UID)."已领取你的任务【".Get_Task_Title($tid)."】",
                    'Touid'=>Get_Task_Applyid($tid),
                    'task_id'=>$tid,
                    'show_id'=>1
                    );
                $this->notice($arr);
    			// $modtask->commit();
    		}else{
    			$data['status']=0;
                $data['info']="发生错误！";
    			// $modtask->rollback();
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
            unset($_POST['type']);
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
    		parent::edit('TaskResult','replytask');
    	}else{
    		$id=I('get.id','','htmlspecialchars');
    		$this->task_detail($id,2,'replytask');
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
            parent::add('TaskTest','task_test');
        }else{
            if(empty($id) || !is_numeric($id)){
                $this->error('非法操作！');
            }        
            $resuinfo=D('TaskResult')->info($id);
            $this->assign('info',$resuinfo);
            $this->display();
        }
    }


    // 归档操作
    public function Todofile($id=''){
        if(empty($id) || !is_numeric($id)){
            $this->error('非法操作！');
        }

        if(TF_explode_value(UID,C('FILE_MANS'))==false){
            $this->error('非法操作！');
        }
        $map['id']=$id;
        $map['pass_fileman_uid']=UID;
        $map['pass_file_time']=time();
        $map['t_status']=6;
        if(D('Task')->save($map)){
            $this->success("归档成功！");
        }else{
            $this->error("归档失败！");
        }
    }


    // 任务指派给···   待做指派后，原来的失效功能，以及提醒功能
    public function sendDists($dist=''){
    	$id=I('get.id','','htmlspecialchars');
    	if(IS_POST){
    		if(empty($id) || empty($dist)){
    			$back['status']=0;
    			$this->ajaxReturn($back);
    		}
    		$save['dist']=$dist;
    		$save['id']=$id;
    		if(M('task')->save($save)){
    			$back['status']=1;
    			$back['info']="任务指派成功！";
    		}else{
    			$back['status']=0;
    		}
    	}else{
    		$back['status']=0;
    	}
    	$this->ajaxReturn($back);
    }


    /**
     * 设置一条或者多条数据的状态
     */
    public function setStatus($model='Task'){
        return parent::setStatus('Task');
    }


    // 导出任务书word文档
    public function downloadWord($id=''){

        if(empty($id) || !is_numeric($id)){
            $this->error('error!');
        }
        vendor('PHPWord.PHPWord');
        $PHPWord = new \PHPWord();
        // 读取任务数据
        $info=D('Task')->info($id);
        // 权限操作
        if(is_superman(UID)){
            $map['task_id']=$id;
            $_list= D('TaskResult')->lists($map);
            $resu_content='';
            $other_content='';
            $comfirm_uid='';
            $gettask_time='';
            $confirm_time='';
            foreach ($_list as $key => $value) {
                $confirm_times=$this->Get_Testman($value['id'],'test_time');
                $resu_content.=$value['resu_content'].'<w:br />';
                $other_content.=$value['other_content'].'<w:br />';
                $comfirm_uid.=','.$this->Get_Testman($value['id'],'testman_uid');
                $gettask_time.=' , '.time_format($value['gettask_time'],'Y-m-d');
                $confirm_time.=' , '.time_format($confirm_times,'Y-m-d');
                $get_taskuid.=','.$value['uid'];
            }
            $resu['resu_content']=substr($resu_content, 0,-8);
            $resu['comfirm_uid']=substr($comfirm_uid, 1);
            $resu['other_content']=substr($other_content, 0,-8);
            $resu['pcb_update']=$this->CheckUpdate($id,'pcb_update');
            $resu['software_update']=$this->CheckUpdate($id,'software_update');
            $resu['product_update']=$this->CheckUpdate($id,'product_update');
            $gettask_time=substr($gettask_time, 2);
            $confirm_time=substr($confirm_time, 2);
            $get_taskuids=substr($get_taskuid, 1);
        }else{
            $resu=D('TaskResult')->taskinfo($id);
            $gettask_time=time_format($resu['gettask_time'],'Y-m-d');
            $confirm_time=time_format($resu['confirm_time'],'Y-m-d');
            $get_taskuids=$resu['uid'];
        }

        
        // file_put_contents('./resu.txt', print_r($resu,true));
        // 任务书模板赋值
        $document = $PHPWord->loadTemplate('./Doc/Template_Task.docx');// 读取任务书模板
        $document->setValue('title', StrToIconv($info['title']));
        $document->setValue('bt', StrToIconv($info['type']));
        $document->setValue('startdt', time_format($info['startdt'],'Y-m-d'));
        $document->setValue('sqz', StrToIconv(get_nickname($info['applyid'])));
        $document->setValue('enddt', time_format($info['enddt'],'Y-m-d'));
        $document->setValue('lingdao', StrToIconv($this->_dataUserTran(get_nickname($info['pass_leader_uid']))));
        $document->setValue('content', StrToIconv(nl2Word($info['content'])));
        $document->setValue('dist', StrToIconv($this->_dataUserTran(Get_leader_names($get_taskuids))));

        $document->setValue('distdt', $gettask_time);
        $document->setValue('gaijin', StrToIconv($resu['resu_content']));
        $document->setValue('qdrmz', StrToIconv($this->_dataUserTran(Get_leader_names($resu['comfirm_uid']))));
        $document->setValue('qdrq', $confirm_time);
        $document->setValue('qtsm', StrToIconv(nl2Word($resu['other_content'])));
        $document->setValue('pcbgx', StrToIconv($resu['pcb_update']));
        $document->setValue('cxwjgx', StrToIconv($resu['software_update']));
        $document->setValue('scwjgx', StrToIconv($resu['product_update']));

        $document->setValue('boss', StrToIconv($this->_dataUserTran(get_nickname($info['pass_fileman_uid']))));
        $document->setValue('bosstime', time_format($info['pass_file_time'],'Y-m-d'));

        $rands='chs'.time();
        $rootpath='./Doc/';
        $savepath='download/';
        $savename=$rands.'.docx';
        $all_path=$rootpath.$savepath.$savename;
        $document->save($all_path);

        sleep(1);//停顿1秒，让DOC文件保存好

        //打开文件，r表示以只读方式打开
        $handle = fopen($all_path,"r");
        //获取文件的统计信息
        $fstat = fstat($handle);

        $file['rootpath']=$rootpath;
        $file['savepath']=$savepath;
        $file['savename']=$savename;
        $file['name']=basename($all_path);
        $file['size']=round($fstat["size"]);
        $file['type']='application/vnd.openxmlformats';
        $resu_down=D('File')->downLocalFile($file); 
        // file_put_contents('./fff.txt', $resu_down);
        if($resu_down==true){
            unlink($all_path);
        }
    }

    // 检查有无更新
    protected function CheckUpdate($tid,$field){
        $_list= D('TaskResult')->lists('task_id='.$tid);
        if($_list){
            foreach ($_list as $key => $value) {
                if($value[$field]=="有更新"){
                        return "有更新";
                }
            }
            return "无更新";
        }else{
            return '';
        }        
    }

    // 任务审核人
    protected function Get_Testman($resuid,$field){
        return M('task_test')->where('resu_id='.$resuid.' and test_status=1')->getField($field);
    }

    // 上传附件业务逻辑处理
    protected function _uploadFile($file_id){
        foreach ($file_id as $value) {
            $file = json_decode(think_decrypt($value), true);
            if(!empty($file)){
                $files[]=$file['id'];
            }
        }
        return implode(',', $files);
    }
    // 人员为空时处理
    protected function _dataUserTran($str){
        if($str=='admin'){
            $backstr='';
        }else{
            $backstr=$str;
        }
        return $backstr;
    }     

}