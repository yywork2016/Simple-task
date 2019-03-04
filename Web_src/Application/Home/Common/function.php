<?php
/**
 * 前台公共库文件
 * 主要定义前台公共函数库
 */

/**
 * 检测验证码
 * @param  integer $id 验证码ID
 * @return boolean     检测结果
 */
function check_verify($code, $id = 1){
	$verify = new \Think\Verify();
	return $verify->check($code, $id);
}

/**
 * 获取列表总行数
 * @param  string  $category 分类ID
 * @param  integer $status   数据状态
 */
function get_list_count($category, $status = 1){
    static $count;
    if(!isset($count[$category])){
        $count[$category] = D('Document')->listCount($category, $status);
    }
    return $count[$category];
}

/**
 * 获取段落总数
 * @param  string $id 文档ID
 * @return integer    段落总数
 */
function get_part_count($id){
    static $count;
    if(!isset($count[$id])){
        $count[$id] = D('Document')->partCount($id);
    }
    return $count[$id];
}

/**
 * 获取导航URL
 * @param  string $url 导航URL
 * @return string      解析或的url
 */
function get_nav_url($url){
    switch ($url) {
        case 'http://' === substr($url, 0, 7):
        case '#' === substr($url, 0, 1):
            break;        
        default:
            $url = U($url);
            break;
    }
    return $url;
}


//通用状态
function status_Meaning($statusid){
    switch ($statusid) {
        case -1 :
            $mean="<span class=\"label label-danger\">删除</span>";
            break;
        case 0  :
            $mean="<span class=\"label label-warning\">禁用</span>";
            break;
        case 1  :
            $mean="<span class=\"label label-success\">正常</span>";
            break;
        default:
            $mean="NULL";
            break;
    }
    return $mean;
}

// 任务执行状态
function task_Meaning($tid){  
    switch ($tid) {
        case 0  :
            $mean="<span class=\"label label-warning\">待审批</span>";
            break;
        case 1  :
            $mean="<span class=\"label label-danger\">待执行</span>";
            break;
        case 2  :
            $mean="<span class=\"label label-doing\">执行中</span>";
            break;
        case 3  :
            $mean="<span class=\"label label-success\">已反馈</span>";
            break;
        case 4  :
            $mean="<span class=\"label label-success\">已完成</span>";
            break;
        case 5  :
            $mean="<span class=\"label label-success\">待归档</span>";
            break;
        case 6  :
            $mean="<span class=\"label label-success\">已归档</span>";
            break;
        default:
            $mean="<span class=\"label label-danger\">未通过</span>";
            break;
    }
    return $mean;   
}

// 根据任务ID 判断获取哪个表的任务状态
function Get_TSTATUS($tid){
    $taskinfo=D('Task')->info($tid,'t_status');
    $resuinfo=D('TaskResult')->taskinfo($tid);
    if($resuinfo && $resuinfo['t_status']>=2 && $resuinfo['t_status']<=4){
        $backstatus=$resuinfo['t_status'];
    }else{
        $backstatus=$taskinfo['t_status'];
    }
    return $backstatus;
}

function schmeaing($tid){
    switch ($tid) {
        case 0  :
            $mean="待审批";
            break;
        case 1  :
            $mean="待执行";
            break;
        case 2  :
            $mean="执行中";
            break;
        case 3  :
            $mean="已反馈";
            break;
        case 4  :
            $mean="已完成";
            break;
        case 5  :
            $mean="待归档";
            break;
        case 6  :
            $mean="已归档";
            break;
        default:
            $mean="全部任务";
            break;
    }
    return $mean;   
}

// 等级状态
function level_Meaning($lid){
    switch ($lid) {
        case 1 :
            $mean="<span class=\"label label-danger\">紧急</span>";
            break;
        case 3 :
            $mean="<span class=\"label label-info\">中</span>";
            break;
        case 2 :
            $mean="<span class=\"label label-primary\">高</span>";
            break;
        default:
            $mean="<span class=\"label label-default\">低</span>";
            break;
    }
    return $mean;
}

// 设置任务等级数据
function Set_Level(){
    $arr=array(
        '1'=>"紧急",
        '2'=>"高",
        '3'=>"中",
        '4'=>"低"
        );
    return $arr;
}
    /**
     * 功能：生成二维码
     * @param string $qr_data   手机扫描后要跳转的网址
     * @param string $qr_level  默认纠错比例 分为L、M、Q、H四个等级，H代表最高纠错能力
     * @param string $qr_size   二维码图大小，1－10可选，数字越大图片尺寸越大
     * @param string $save_path 图片存储路径
     * @param string $save_prefix 图片名称前缀
     */
    function createQRcode($save_path,$qr_data='PHP QR Code :)',$qr_level='L',$qr_size=4,$save_prefix='qrcode'){
        if(!isset($save_path)) return '';

        //设置生成png图片的路径
        $PNG_TEMP_DIR = & $save_path;
        //导入二维码核心程序
        vendor('PHPQRcode.class#phpqrcode');  //注意这里的大小写哦，不然会出现找不到类，PHPQRcode是文件夹名字，class#phpqrcode就代表class.phpqrcode.php文件名
        //检测并创建生成文件夹
        if (!file_exists($PNG_TEMP_DIR)){
            mkdir($PNG_TEMP_DIR);
        }
        $filename = $PNG_TEMP_DIR.'test.png';
        $errorCorrectionLevel = 'L';
        if (isset($qr_level) && in_array($qr_level, array('L','M','Q','H'))){
            $errorCorrectionLevel = & $qr_level;
        }
        $matrixPointSize = 4;
        if (isset($qr_size)){
            $matrixPointSize = & min(max((int)$qr_size, 1), 10);
        }
        if (isset($qr_data)) {
            if (trim($qr_data) == ''){
                die('data cannot be empty!');
            }
            //生成文件名 文件路径+图片名字前缀+md5(名称)+.png
            $filename = $PNG_TEMP_DIR.$save_prefix.md5($qr_data.'|'.$errorCorrectionLevel.'|'.$matrixPointSize).'.png';
            //开始生成
            $fopen                     =   fopen($filename,   'wb ') or die("cane`t open file.txt:$php_errormsg");//新建文件命令

            fclose($fopen);
            QRcode::png($qr_data, $filename, $errorCorrectionLevel, $matrixPointSize,2);
        } else {
            //默认生成
            QRcode::png('PHP QR Code :)', $filename, $errorCorrectionLevel, $matrixPointSize, 2);
        }
        // p(basename($filename));p($PNG_TEMP_DIR);die;
        if(file_exists($PNG_TEMP_DIR.basename($filename)))
            return basename($filename);
        else
            return FALSE;
    }



    // 获取默认头像
    function User_Cover($uid){
            $userinfo=D('Users')->info($uid);
            if($userinfo['cover']=='0'){
                if($userinfo['sex']==1){
                    $coverimg='http://'.$_SERVER['HTTP_HOST'] . '/Public/' . MODULE_NAME . '/images/default_man.jpg';
                }else{
                    $coverimg='http://'.$_SERVER['HTTP_HOST'] . '/Public/' . MODULE_NAME . '/images/default_female.jpg';
                }
            }else{
                $coverimg='http://'.$_SERVER['HTTP_HOST'].$userinfo['cover'];
            }
            return $coverimg;
    }



    //通过上级主管ID  获取 上级主管 名称
    function Get_leader_names($lid){
        $leader_arr=explode(',', $lid);
        $lname='';
        foreach ($leader_arr as $value) {
            $lname .=get_nickname($value).',';
        }
        return substr($lname, 0,-1);
    }


// checkbox 数据勾选
function Get_checkbox($id,$lists){
    if(in_array($id, explode(',',$lists))){
        $backdata="checked='checked'";
    }else{
        $backdata='';
    }
    return $backdata;
}

// 任务结束时间处理
function enddt_time($time){
    $betime='';
    if($time){
        $betime=time_format($time,'Y-m-d');
        if($time<=time()){
            $betime .=" <span class=\"label label-danger\">已超期</span>";
        }
    }
    return $betime;
}

/**
 * select返回的数组进行整数映射转换
 *
 * @param array $map  映射关系二维数组  array(
 *                                          '字段名1'=>array(映射关系数组),
 *                                          '字段名2'=>array(映射关系数组),
 *                                           ......
 *                                       )
 * @return array
 *
 *  array(
 *      array('id'=>1,'title'=>'标题','status'=>'1','status_text'=>'正常')
 *      ....
 *  )
 *
 */
function int_to_string(&$data,$map=array('status'=>array(1=>'正常',-1=>'删除',0=>'禁用',2=>'未审核',3=>'草稿'))) {
    if($data === false || $data === null ){
        return $data;
    }
    $data = (array)$data;
    foreach ($data as $key => $row){
        foreach ($map as $col=>$pair){
            if(isset($row[$col]) && isset($pair[$row[$col]])){
                $data[$key][$col.'_text'] = $pair[$row[$col]];
            }
        }
    }
    return $data;
}


// 返回当前权限组名称
function Get_AuthGroupName($id){
    return M('auth_group')->where('id='.$id)->getField('title');
}

// 获取任务审核后的领导名字
function T_statusDone_LeadingName($tid){
    $leadname='';
    $task=D('Task')->info($tid);
    if($task['t_status']>0){
        $leadname=Get_leader_names($task['leaderid']);
    }
    return $leadname;
}

// 中文转码，应用于word导出
function StrToIconv($str){
    return iconv('utf-8', 'GB2312//IGNORE', $str);
}

// 输出到WORD文档  换行符号
function nl2Word($str){
    // $str=str_replace("\r", "<w:br />", $str);
    return str_replace("\n", "<w:br />", $str);
}

// 分解校验，存在为true  不存在为false,$str为检验字符串，$arr为需校验的字符串
function TF_explode_value($str,$arr){
    $back=false;
    $exp=explode(',', $arr);
    if(in_array($str, $exp)) $back=true;
    return $back;
}

// 提醒模块类型
function notice_type($typeid){
    if($typeid==1){
        $typename="任务";
    }
    return $typename;
}

// 通过任务ID获取任务标题
function Get_Task_Title($tid){
    return M('task')->where('id='.$tid)->getField('title');
}
// 通过任务ID 获取任务接收者
function Get_Task_Dist($tid){
    return M('task')->where('id='.$tid)->getField('dist');
}
// 通过任务ID 获取任务发布者ID
function Get_Task_Applyid($tid){
    return M('task')->where('id='.$tid)->getField('applyid');
}
// 通过任务ID 获取任务反馈结果确定人ID
function Get_Task_Testmanid($tid){
    return M('task')->where('id='.$tid)->getField('testman');
}
// 根据任务ID 获取测试者和发布者ID
function Get_TaskTest_mans($tid){
    $ts=M('task')->where('id='.$tid)->getField('testman,applyid');
    if($ts['testman']){
        $backdata=$ts['testman'].','.$ts['applyid'];
    }else{
        $backdata=$ts['applyid'];
    }
    return $backdata;
}
// 根据任务ID 获取归档人
function Get_task_Fileman($tid){
    $fileman=M('task')->where('id='.$tid)->getField('fileman');
    $backman=$fileman?$fileman:C('FILE_MANS');
    return $backman;
}

//通过任务ID  判断每个任务接收人的任务是否完成状态，有一个为完成都返回false    ???
function Check_Task_isover($tid){
    $all_dist=Get_Task_Dist($tid);//获取此任务的所有任务者
    $exp_dist=explode(',', $all_dist);
    foreach ($exp_dist as $value) {
        // 开始
        $resu_status=D('TaskResult')->where('uid='.$value.' and task_id='.$tid)->getField('t_status');
        if($resu_status != 4){
            return false;
        }
    }
    // 任务全部完成，写入状态  待归档
    $map['id']=$tid;
    $map['t_status']=5;
    if(D('Task')->save($map)){
        return true;
    }else{
        return false;
    }
}


 // 分析枚举类型配置值 格式 a:名称1,b:名称2
function parse_config_attr($string) {
    $array = preg_split('/[,;\r\n]+/', trim($string, ",;\r\n"));
    if(strpos($string,':')){
        $value  =   array();
        foreach ($array as $val) {
            list($k, $v) = explode(':', $val);
            $value[$k]   = $v;
        }
    }else{
        $value  =   $array;
    }
    return $value;
}

// 获取父类PID  权限使用
function Get_auth_pid($id){
    return M('auth_rule')->where('id='.$id)->getField('pid');
}


// APICloud云推送  函数
function APICloud() 
 { 
    return array( 
     'X-APICloud-AppId:'.C('AppID'), 
     'X-APICloud-AppKey:'.getSHAKey() 
     ); 
 } 
 
 //毫秒 
 function getMilliSecond() 
 { 
 list($s1, $s2) = explode(' ', microtime()); 
 return (float)sprintf('%.0f', (floatval($s1) + floatval($s2)) * 1000); 
 } 
 
 function getSHAKey() 
 { 
 $time = getMilliSecond();
 return sha1(C('AppID').'UZ'.C('AppKey').'UZ'.$time).'.'.$time; 
 } 
 
 function push($data) 
 { 
 $ch = curl_init(); 
 curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); 
 curl_setopt ($ch, CURLOPT_URL, C('AppPath')); 
 curl_setopt ($ch, CURLOPT_HTTPHEADER, APICloud()); 
 curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1); 
 curl_setopt ( $ch, CURLOPT_POSTFIELDS, $data ); 
 $result = curl_exec($ch); 
 curl_close($ch); 
 return $result; 
 } 
 // APICloud云推送结束
 