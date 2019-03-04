//判断是否已经登陆,全局变量
var token=$api.getStorage('token');
var uid=$api.getStorage('uid');
var nickname=$api.getStorage('nickname');
//配置服务器获取数据地址
var mainurl="http://此处为IP或域名地址/index.php?s=";
var gettoken="/token/"+token;
var OpenAPI = {
"login":mainurl + "/Public/login", //登陆接口
"logout":mainurl + "/Public/mobile_logout"+gettoken, //退出接口
"upload_cover":mainurl + "/MobileMember/UploadCover"+gettoken, //上传头像接口
"memberinfo":mainurl + "/MobileMember/minfo"+gettoken, //获取用户信息接口
"edituser":mainurl + "/MobileMember/edituser"+gettoken, //更新用户信息接口
"getCover":mainurl + "/MobileMember/getcover"+gettoken, //获取用户头像
"getTaskList":mainurl + "/MobileTask/get_task"+gettoken, //获取任务列表
"taskdetail":mainurl + "/MobileTask/detail"+gettoken, //获取任务详情
"examine":mainurl + "/MobileTask/examine"+gettoken, //审批
"getMytask":mainurl + "/MobileTask/get_mytask"+gettoken, //领取任务
"taskResult":mainurl + "/MobileTask/taskResult"+gettoken, //任务反馈
"task_test":mainurl + "/MobileTask/task_test"+gettoken, //任务反馈结果测试接口
"fileman":mainurl + "/MobileTask/Todofile"+gettoken, //归档
"editpwd":mainurl + "/MobileMember/resetpassword"+gettoken, //重置密码
"getresu":mainurl + "/MobileTask/getResuTask"+gettoken, //获取已反馈结果的数据
"getusers":mainurl + "/MobileMember/getusers"+gettoken, //获取所有用户
"createtask":mainurl + "/MobileTask/addtask"+gettoken, //创建任务
"mytasklist":mainurl + "/MobileTask/buildtask"+gettoken, //我创建的任务列表
"taskinfo":mainurl + "/MobileTask/taskinfo"+gettoken, //获取需要编辑的任务数据
"deltaskapi":mainurl + "/MobileTask/deltask"+gettoken, //删除任务
"noticelist":mainurl + "/MobileNotice/index"+gettoken, //获取提醒信息列表
"setStatus":mainurl + "/MobileNotice/setStatus"+gettoken, //设置提醒信息已读
}


// apiready=function(){
//     if(token == null || token=='undefined' || token==''){
//        api.openWin({
//        name: "登陆",
//        url: 'html/login.html'
//        })
//     }
// }
// 退出登陆
function Logout(){
        api.ajax({
          url: OpenAPI.logout,
          method: 'post',
          timeout: 30,
          dataType: 'json',
          returnAll:false,
          data:{
               values: ""
             }
           },function(ret,err){
                var info=ret.info;
                if(ret.status==1){
                  // 解绑用户
                  var push = api.require('push');
                  push.unbind({
                      userName: nickname,
                      userId: uid
                  },function(ret,err){
                      // 啥都不做
                  });
                  // 退出群组
                  push.leaveGroup({
                      groupName:'chs-task'
                  },function(ret,err){
                       // 啥都不做
                  });
                	$api.rmStorage('token');
                    api.toast({msg:info});
                    setTimeout("api.openWin({name:\"login\",url:api.wgtRootDir+\"/html/login.html\"})",1000);                    
                }else if(err){
                    {api.toast({msg : ('错误码：' + err.code + '；错误信息：' + err.msg + '；网络状态码：' + err.statusCode)});}
                }else{
                	api.toast({msg : info});
                }
           });
}


// 任务审批和反馈结果使用函数  ---弹出层
// type为1时是审批，为2时是反馈结果
// func_name 保存时要执行的函数名称
function pop_common(type,taskid,func_name,getdata_func){
	var htmls='';
	var that_body=$api.dom('body');
	var popname="备 注";
	var xuanze="审 批";
	if(type==2){
		popname="结 果";
	}
	if(type==3){
		xuanze="选 择";
	}
	htmls +='<div id="pop-common">';
	htmls +='<div class="pop-all-bg"></div>';
	htmls +='<div class="pop-content">';
	if(type==1 || type==3){
	        htmls +='<div class="aui-list-item-inner task-radio-nav">';
	            htmls +='<div class="aui-list-item-label">'+xuanze+'</div>';
	            htmls +='<div class="aui-list-item-input">';
	                htmls +='<label style="width: 100px;"><input class="aui-radio" type="radio" name="t_status" value="1" checked> 通过</label>';
	                htmls +='<label><input class="aui-radio" type="radio" name="t_status" value="-2"> 不通过</label>';
	            htmls +='</div>';
	        htmls +='</div>';
	}
	    htmls +='<div class="task-radio-nav">';
	    htmls +='<div class="aui-list-item-label">'+popname+'</div>';
	    htmls +='<div class="task-textarea"><input type="hidden" name="taskid" value="'+taskid+'" />';
	    htmls +='<textarea placeholder="请输入···" name="content"></textarea>';
	    htmls +='</div>';
	    htmls +='</div>';

	if(type==2){
		    htmls +='<div class="task-radio-nav">';
		    htmls +='<div class="aui-list-item-label">其 他</div>';
		    htmls +='<div class="task-textarea" style="height:33px;">';
		    htmls +='<textarea placeholder="请输入···" name="other_content"></textarea>';
		    htmls +='</div>';
		    htmls +='</div>';

			htmls +='<div class="aui-list-item-inner task-radio-nav">';
	            htmls +='<div class="aui-list-item-label">选 择</div>';
	            htmls +='<div class="aui-list-item-input">';
	                htmls +='<label style="width: 145px;"><input class="aui-radio" type="radio" name="pcb_update" value="有更新" checked> PCB有更新</label>';
	                htmls +='<label><input class="aui-radio" type="radio" name="pcb_update" value="无更新"> PCB无更新</label>';
	            htmls +='</div>';
	        htmls +='</div>';

	        htmls +='<div class="aui-list-item-inner task-radio-nav">';
	            htmls +='<div class="aui-list-item-label">选 择</div>';
	            htmls +='<div class="aui-list-item-input">';
	                htmls +='<label style="width: 145px;"><input class="aui-radio" type="radio" name="software_update" value="有更新" checked> 程序有更新</label>';
	                htmls +='<label><input class="aui-radio" type="radio" name="software_update" value="无更新"> 程序无更新</label>';
	            htmls +='</div>';
	        htmls +='</div>';

	        htmls +='<div class="aui-list-item-inner task-radio-nav">';
	            htmls +='<div class="aui-list-item-label">选 择</div>';
	            htmls +='<div class="aui-list-item-input">';
	                htmls +='<label style="width: 145px;"><input class="aui-radio" type="radio" name="product_update" value="有更新" checked> 生产有更新</label>';
	                htmls +='<label><input class="aui-radio" type="radio" name="product_update" value="无更新"> 生产无更新</label>';
	            htmls +='</div>';
	        htmls +='</div>';
	}

	    htmls +='<p style="display:flex;flex-flow: row;"><div class="aui-btn task-button-submit" onclick="close_pop()">默默关闭</div><div class="aui-btn aui-btn-info task-button-submit" onclick="'+func_name+'">保 存</div></p>';
	htmls +='</div>';
	htmls +='</div>';

	$api.append(that_body, htmls);

	if(getdata_func != ''){
		getresudata(getdata_func);
	}

}
// 关闭弹出层
function close_pop(){
	var pop=$api.byId('pop-common');
	$api.remove(pop);
}

// 获取已反馈的数据
function getresudata(id){
  if(id==''){
    return false;
  }
	api.ajax({
      url: OpenAPI.getresu,
      method: 'post',
      timeout: 30,
      dataType: 'json',
      data:{
           values: {
            	id: id
        	}
         }
       },function(ret,err){
       		CheckLogin(ret.loginout);
            var info=ret.info;
            if(ret.status==1){
				$api.val($api.dom("textarea[name=content]"),info.resu_content);
				$api.val($api.dom("textarea[name=other_content]"),info.other_content);
				$api.attr($api.dom("input[name=pcb_update][value="+info.pcb_update+"]"),"checked","checked");
				$api.attr($api.dom("input[name=software_update][value="+info.software_update+"]"),"checked","checked");
				$api.attr($api.dom("input[name=product_update][value="+info.product_update+"]"),"checked","checked");       
            }else if(err){
                api.toast({msg : ('错误码：' + err.code + '；错误信息：' + err.msg + '；网络状态码：' + err.statusCode)});
            }else{
            	api.toast({msg : info});
            }
       });
}

// 任务反馈结果测试验证
function resuTest(taskid,touid){
	var resuid=$api.dom("input[name=taskid]");
	var tstatus=$api.val($api.dom("input[name=t_status]:checked"));
	var content=$api.dom("textarea[name=content]");
	if(tstatus==-2){
		tstatus=0;
	}

      api.ajax({
      url: OpenAPI.task_test,
      method: 'post',
      timeout: 30,
      dataType: 'json',
      data:{
           values: {
            	resu_id: $api.val(resuid),
            	task_id: taskid,
            	touid: touid,
            	test_status: tstatus,
            	test_content: $api.val(content)
        	}
         }
       },function(ret,err){
       		CheckLogin(ret.loginout);
            var info=ret.info;
            if(ret.status==1){
                api.toast({msg:info});
            	var jsfun = 'showtasklist();';
				api.execScript({
				    name: 'homeSlide',
				    frameName: 'newtask',
				    script: jsfun
				});
                setTimeout(function(){
                	window.location.reload();
                },1000);                                                 
            }else if(err){
                api.toast({msg : ('错误码：' + err.code + '；错误信息：' + err.msg + '；网络状态码：' + err.statusCode)});
            }else{
            	api.alert({msg : info});
            }
            close_pop();
       });
}


// 审批函数
// showtype 1为列表调用，2为详细页调用
function examine(showtype){
	var taskid=$api.dom("input[name=taskid]");
	var tstatus=$api.dom("input[name=t_status]:checked");
	var content=$api.dom("textarea[name=content]");

      api.ajax({
      url: OpenAPI.examine,
      method: 'post',
      timeout: 30,
      dataType: 'json',
      data:{
           values: {
            	id: $api.val(taskid),
            	t_status: $api.val(tstatus),
            	reason: $api.val(content)
        	}
         }
       },function(ret,err){
       		CheckLogin(ret.loginout);
            var info=ret.info;
            if(ret.status==1){
                api.toast({msg:info});
                if(showtype==2){
                	var jsfun = 'showtasklist();';
					api.execScript({
					    name: 'homeSlide',
					    frameName: 'newtask',
					    script: jsfun
					});
                }
                setTimeout(function(){
                	window.location.reload();
                },1000);                                                 
            }else if(err){
                api.toast({msg : ('错误码：' + err.code + '；错误信息：' + err.msg + '；网络状态码：' + err.statusCode)});
            }else{
            	api.alert({msg : info});
            }
            close_pop();
       });
}


// 领取任务  taskid 任务ID，showtype操作页：1为列表，2为详细页
function get_task(taskid,showtype){
	var dialogBox = api.require('dialogBox');
	     dialogBox.alert({
        texts: {
            content: '你确定要领取此任务吗？',
            leftBtnTitle: '默默拒绝',
            rightBtnTitle: '立即领取'
        },
        styles: {
            bg: '#fff',
            corner: 6,
            w: 280,
            content: {
                color: '#000',
                size: 18
            },
            left: {
                marginB: 15,
                marginL: 30,
                w: 100,
                h: 38,
                corner: 20,
                color: "#fff",
                bg: '#CDCDCD',
                size: 15
            },
            right: {
                marginB: 15,
                marginL: 20,
                w: 100,
                h: 38,
                corner: 20,
                color: "#fff",
                bg: '#263065',
                size: 15
            }
        },
        tapClose: true
    }, function(ret) {
        if (ret.eventType == 'left') {
            dialogBox.close({
                dialogName: 'alert'
            });
        }else{
        	  api.ajax({
		      url: OpenAPI.getMytask,
		      method: 'post',
		      timeout: 30,
		      dataType: 'json',
		      data:{
		           values: {
		            	tid: taskid
		        	}
		         }
		       },function(ret,err){
		       		CheckLogin(ret.loginout);
		            var info=ret.info;
		            if(ret.status==1){		                
			            api.toast({
			                msg: info,
			                duration: 2000,
			                location: 'middle'
			            });
		                if(showtype==2){
		                	var jsfun = 'showtasklist();';
							api.execScript({
							    name: 'homeSlide',
							    frameName: 'newtask',
							    script: jsfun
							});
		                }
		                setTimeout(function(){
		                	window.location.reload();
		                },2000);                                                 
		            }else if(err){
		                api.toast({msg : ('错误码：' + err.code + '；错误信息：' + err.msg + '；网络状态码：' + err.statusCode)});
		            }else{
		            	api.alert({msg : info});
		            }
		            dialogBox.close({
		                dialogName: 'alert'
		            });
		       });
        }
    });
}


// 任务反馈结果
function resultTask(resuid,showtype){

	var tid=$api.val($api.dom("input[name=taskid]"));
	var resucontent=$api.val($api.dom("textarea[name=content]"));
	var other_content=$api.val($api.dom("textarea[name=other_content]"));
	var pcb_update=$api.val($api.dom("input[name=pcb_update]:checked"));
	var software_update=$api.val($api.dom("input[name=software_update]:checked"));
	var product_update=$api.val($api.dom("input[name=product_update]:checked"));
	if(tid==''){
		api.toast({msg:"id出错！"});
		return false;
	}
	if(resucontent==''){
		api.toast({msg:"反馈结果不能为空！"});
		return false;
	}

	  api.ajax({
      url: OpenAPI.taskResult,
      method: 'post',
      timeout: 30,
      dataType: 'json',
      data:{
           values: {
           		id: resuid,
            	task_id: tid,
            	resu_content: resucontent,
            	other_content: other_content,
            	pcb_update: pcb_update,
            	software_update: software_update,
            	product_update: product_update
        	}
         }
       },function(ret,err){
       		CheckLogin(ret.loginout);
            var info=ret.info;
            if(ret.status==1){
	            close_pop();              
	            api.toast({
	                msg: info,
	                duration: 2000,
	                location: 'middle'
	            });
                if(showtype==2){
                	var jsfun = 'showtasklist();';
					api.execScript({
					    name: 'homeSlide',
					    frameName: 'todo',
					    script: jsfun
					});
                }
                setTimeout(function(){
                	window.location.reload();
                },2000); 
                                             
            }else if(err){
                api.toast({msg : ('错误码：' + err.code + '；错误信息：' + err.msg + '；网络状态码：' + err.statusCode)});
            }else{
            	api.alert({msg : info});
            }
       });
}


// 归档操作
function fileman(taskid,showtype){
	var dialogBox = api.require('dialogBox');
	     dialogBox.alert({
        texts: {
            content: '你确定要归档此任务吗？',
            leftBtnTitle: '默默拒绝',
            rightBtnTitle: '立即归档'
        },
        styles: {
            bg: '#fff',
            corner: 6,
            w: 280,
            content: {
                color: '#000',
                size: 18
            },
            left: {
                marginB: 15,
                marginL: 30,
                w: 100,
                h: 38,
                corner: 20,
                color: "#fff",
                bg: '#CDCDCD',
                size: 15
            },
            right: {
                marginB: 15,
                marginL: 20,
                w: 100,
                h: 38,
                corner: 20,
                color: "#fff",
                bg: '#263065',
                size: 15
            }
        },
        tapClose: true
    }, function(ret) {
        if (ret.eventType == 'left') {
            dialogBox.close({
                dialogName: 'alert'
            });
        }else{
        	  api.ajax({
		      url: OpenAPI.fileman,
		      method: 'post',
		      timeout: 30,
		      dataType: 'json',
		      data:{
		           values: {
		            	id: taskid
		        	}
		         }
		       },function(ret,err){
		       		CheckLogin(ret.loginout);
		            var info=ret.info;
		            if(ret.status==1){		                
			            api.toast({
			                msg: info,
			                duration: 2000,
			                location: 'middle'
			            });
		                if(showtype==2){
		                	var jsfun = 'showtasklist();';
							api.execScript({
							    name: 'homeSlide',
							    frameName: 'newtask',
							    script: jsfun
							});
		                }
		                setTimeout(function(){
		                	window.location.reload();
		                },2000);                                                 
		            }else if(err){
		                api.toast({msg : ('错误码：' + err.code + '；错误信息：' + err.msg + '；网络状态码：' + err.statusCode)});
		            }else{
		            	api.alert({msg : info});
		            }
		            dialogBox.close({
		                dialogName: 'alert'
		            });
		       });
        }
    });
}


// 判断返回值 
function CheckLogin(loginout){
	if(loginout==1){
        $api.rmStorage('token');
        setTimeout("api.openWin({name:\"login\",url:api.wgtRootDir+\"/html/login.html\"})",1000); 
	}
}

