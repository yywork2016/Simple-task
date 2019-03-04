//dom加载完成后执行的js
;$(function(){

//主框架左右高度设置
var bodyheight=$(window).height();
var maincontent_height=bodyheight-122;
var left_height=bodyheight-50;
$("#content_allmainview").height(maincontent_height);
$("#indexmenu").height(left_height);
// 初始化任务列表
setTimeout(function(){
    $(".type_chuage_nav").find('[class=hover]').click();
},1000);

//刷新当前页面
var refresh_title=getCookie('refresh_title');
var refresh_tempid=getCookie('refresh_tempid');
var refresh_url=getCookie('refresh_url');
var menu_id=getCookie('refresh_menu_id');
if(menu_id==''){
    // 初始化  我的地盘菜单
    setTimeout(function(){
        $(".topmenu_list").find('[data-id=58]').click();
    },500);
}else{
    $('.topmenu_list').find('[data-id='+menu_id+']').click();
}
if(refresh_title!='' && refresh_tempid!='' && refresh_tempid!=0 && refresh_url!=''){    
    getMainContent(refresh_title,refresh_url,refresh_tempid);
}

//全选的实现
    $('body').on('click','.check-all',function(){
		$(".ids").prop("checked", this.checked);
	});
    $('body').on('click','.ids',function(){
		var option = $(".ids");
		option.each(function(i){
			if(!this.checked){
				$(".check-all").prop("checked", false);
				return false;
			}else{
				$(".check-all").prop("checked", true);
			}
		});
	});

    //ajax get请求
    $('body').on('click','.ajax-get',function(){
        var target;
        var that = this;
        var jump_url_type=$(that).attr('url-type');
        if ( $(this).hasClass('confirm') ) {
            if(!confirm('确认要执行该操作吗?')){
                return false;
            }
        }
        if ( (target = $(this).attr('href')) || (target = $(this).attr('url')) ) {
            $.get(target).success(function(data){
                if (data.status==1) {
                    if (data.url) {
                        updateAlert(data.info + ' 页面即将自动刷新~','alert-success');
                    }else{
                        updateAlert(data.info,'alert-success');
                    }
                    setTimeout(function(){
                        // 归档跳转
                        if(jump_url_type==2){
                            Refresh_indexTask();
                        }

                        if (data.url) {
                            if(jump_url_type==1){
                                location.href=data.url;
                            }else{
                                getNextMainContent(data.url);
                            }
                            // closeAlert();
                        }else if( $(that).hasClass('no-refresh')){
                            // $('#top-alert').find('button').click();
                            closeAlert();
                        }else{
                            AutoRefresh();
                        }
                        closeAlert();
                    },2000);
                }else{
                    updateAlert(data.info,'alert-error');
                    setTimeout(function(){
                        if (data.url) {
                            if(jump_url_type==1){
                                location.href=data.url;
                            }else{
                                getNextMainContent(data.url);
                            }
                        }else{
                            closeAlert();
                        }
                    },2000);
                }
            });

        }
        return false;
    });

    //ajax post submit请求
    $('body').on('click','.ajax-post',function(){
        var target,query,form;
        var target_form = $(this).attr('target-form');
        var that = this;
        var nead_confirm=false;
        var jump_url_type=$(that).attr('url-type');

        if( ($(this).attr('type')=='submit') || (target = $(this).attr('href')) || (target = $(this).attr('url')) ){
            form = $('.'+target_form);

            if ($(this).attr('hide-data') === 'true'){//无数据时也可以使用的功能
            	form = $('.hide-data');
            	query = form.serialize();
            }else if (form.get(0)==undefined){
            	return false;
            }else if ( form.get(0).nodeName=='FORM' ){
                if ( $(this).hasClass('confirm') ) {
                    if(!confirm('确认要执行该操作吗?')){
                        return false;
                    }
                }
                if($(this).attr('url') !== undefined){
                	target = $(this).attr('url');
                }else{
                	target = form.get(0).action;
                }
                query = form.serialize();
            }else if( form.get(0).nodeName=='INPUT' || form.get(0).nodeName=='SELECT' || form.get(0).nodeName=='TEXTAREA') {
                form.each(function(k,v){
                    if(v.type=='checkbox' && v.checked==true){
                        nead_confirm = true;
                    }
                })
                if ( nead_confirm && $(this).hasClass('confirm') ) {
                    if(!confirm('确认要执行该操作吗?')){
                        return false;
                    }
                }
                query = form.serialize();
            }else{
                if ( $(this).hasClass('confirm') ) {
                    if(!confirm('确认要执行该操作吗?')){
                        return false;
                    }
                }
                query = form.find('input,select,textarea').serialize();
            }
            $(that).addClass('disabled').attr('autocomplete','off').prop('disabled',true);
            $.post(target,query).success(function(data){
                if (data.status==1) {
                    if (data.url) {
                        updateAlert(data.info + ' 页面即将自动刷新~','alert-success');
                    }else{
                        updateAlert(data.info ,'alert-success');
                    }
                    setTimeout(function(){
                    	$(that).removeClass('disabled').prop('disabled',false);
                        if(jump_url_type==4){// 首页任务详情中调用审批使用
                            var dcc=$(".modal");
                            dcc.modal('hide');
                            setTimeout(function(){
                                dcc.remove();
                            },700);
                            Refresh_indexTask();
                        }else if(jump_url_type==2){//首页任务预览调用审批使用
                            CloseModal(that);
                            Refresh_indexTask();
                        }else if(jump_url_type==5){//修改头像使用
                            CloseModal(that);
                            location.reload();//头像更换后整个后台刷新
                        }else{
                                if (data.url) {
                                    if(jump_url_type==1){ //跳转URL使用
                                        location.href=data.url;
                                    }else{
                                        getNextMainContent(data.url);
                                    }             
                                    
                                    // closeAlert();
                                }else if( $(that).hasClass('no-refresh')){
                                    // closeAlert();
                                }else{
                                    AutoRefresh();
                                }
                        }
                        closeAlert();
                    },2000);
                }else{
                    updateAlert(data.info);
                    setTimeout(function(){
                    	$(that).removeClass('disabled').prop('disabled',false);
                        if (data.url) {
                            if(jump_url_type==1){
                                location.href=data.url;
                            }else{
                                getNextMainContent(data.url);
                            }
                        }
                        closeAlert();
                    },2000);
                }
            });
        }
        return false;
    });

	/**顶部警告栏*/
	var content = $('#main');
	var top_alert = $('#top-alert');
	top_alert.find('.close').on('click', function () {
		top_alert.removeClass('block').slideUp(200);
		// content.animate({paddingTop:'-=55'},200);
	});

    window.updateAlert = function (text,c) {
		text = text||'default';
		c = c||false;
		if ( text!='default' ) {
            top_alert.find('.alert-content').text(text);
			if (top_alert.hasClass('block')) {
			} else {
				top_alert.addClass('block').slideDown(200);
				// content.animate({paddingTop:'+=55'},200);
			}
		} else {
			if (top_alert.hasClass('block')) {
				top_alert.removeClass('block').slideUp(200);
				// content.animate({paddingTop:'-=55'},200);
			}
		}
		if ( c!=false ) {
            top_alert.removeClass('alert-error alert-warn alert-info alert-success').addClass(c);
		}
	};


    /**底部系统提醒*/
    var tishi=$("#tishidivshow");
    tishi.find('.closetishi').on('click', function () {
        tishi.removeClass('block').slideUp(300);
        // content.animate({paddingTop:'-=55'},200);
    });
    window.updateTishi=function(text){
        if(text!=''){
            var txts='<ol>';
            tishi.addClass('block').slideDown(300);
            for (var i =0;text.length>i;i++) {
                var tourl="<a href='javascript:Look_Notice("+text[i].id+",\"/index.php?s=/Task/task_detail/id/"+text[i].task_id+"\","+text[i].show_id+");'>[查看]</a>";
                if(text[i].show_id==2){
                    tourl +="<a href='javascript:Look_Notice("+text[i].id+",\"/index.php?s=/Task/examine/id/"+text[i].task_id+"/jur/2\","+text[i].show_id+");'>[审批]</a>";
                }else if(text[i].show_id==3){
                    tourl +="<a href='javascript:Look_Notice("+text[i].id+",\"/index.php?s=/Task/get_mytask/tid/"+text[i].task_id+"\","+text[i].show_id+");'>[领取任务]</a>";
                }
                txts +="<li>["+text[i].type+"]"+text[i].title+tourl+"</li>";
            }
            txts +="</ol>";
            tishi.find('.panel-body').html(txts);
        }
    };

    // ajax获取系统提醒数据
    window.ajaxgetnotice=function(){
        tishi.find('.closetishi').click();
        $.get('/index.php?s=/Notice/ajaxGetNotice.html',{},function(data){
            if(!isNaN(data.status)){
                updateAlert(data.info,'alert-error');                
            }else{
                updateTishi(data);
            }
        });
    }
    var get_nowurl = window.location.href;
    if(get_nowurl.substring(get_nowurl.length-18) != "/Public/login.html"){
        // 初始化获取系统提醒
        ajaxgetnotice();
        // 间隔100秒持续获取系统提醒
        setInterval(function(){
            ajaxgetnotice();
        },100000);
    }

    //按钮组
    // (function(){
        //按钮组(鼠标悬浮显示)
        // $(".btn-group").mouseenter(function(){
        //     var userMenu = $(this).children(".dropdown ");
        //     var icon = $(this).find(".btn i");
        //     icon.addClass("btn-arrowup").removeClass("btn-arrowdown");
        //     userMenu.show();
        //     clearTimeout(userMenu.data("timeout"));
        // }).mouseleave(function(){
        //     var userMenu = $(this).children(".dropdown");
        //     var icon = $(this).find(".btn i");
        //     icon.removeClass("btn-arrowup").addClass("btn-arrowdown");
        //     userMenu.data("timeout") && clearTimeout(userMenu.data("timeout"));
        //     userMenu.data("timeout", setTimeout(function(){userMenu.hide()}, 100));
        // });

        //按钮组(鼠标点击显示)
        // $(".btn-group-click .btn").click(function(){
        //     var userMenu = $(this).next(".dropdown ");
        //     var icon = $(this).find("i");
        //     icon.toggleClass("btn-arrowup");
        //     userMenu.toggleClass("block");
        // });
        // $(".btn-group-click .btn").click(function(e){
        //     if ($(this).next(".dropdown").is(":hidden")) {
        //         $(this).next(".dropdown").show();
        //         $(this).find("i").addClass("btn-arrowup");
        //         e.stopPropagation();
        //     }else{
        //         $(this).find("i").removeClass("btn-arrowup");
        //     }
        // })
        // $(".dropdown").click(function(e) {
        //     e.stopPropagation();
        // });
        // $(document).click(function() {
        //     $(".dropdown").hide();
        //     $(".btn-group-click .btn").find("i").removeClass("btn-arrowup");
        // });
    // })();

    // 独立域表单获取焦点样式
    $(".text").focus(function(){
        $(this).addClass("focus");
    }).blur(function(){
        $(this).removeClass('focus');
    });
    $("textarea").focus(function(){
        $(this).closest(".textarea").addClass("focus");
    }).blur(function(){
        $(this).closest(".textarea").removeClass("focus");
    });
});

/* 上传图片预览弹出层 */

//标签页切换(无下一步)
function showTab() {
    $(".tab-nav li").click(function(){
        var self = $(this), target = self.data("tab");
        self.addClass("current").siblings(".current").removeClass("current");
        window.location.hash = "#" + target.substr(3);
        $(".tab-pane.in").removeClass("in");
        $("." + target).addClass("in");
    }).filter("[data-tab=tab" + window.location.hash.substr(1) + "]").click();
}

//标签页切换(有下一步)
function nextTab() {
     $(".tab-nav li").click(function(){
        var self = $(this), target = self.data("tab");
        self.addClass("current").siblings(".current").removeClass("current");
        window.location.hash = "#" + target.substr(3);
        $(".tab-pane.in").removeClass("in");
        $("." + target).addClass("in");
        showBtn();
    }).filter("[data-tab=tab" + window.location.hash.substr(1) + "]").click();

    $("#submit-next").click(function(){
        $(".tab-nav li.current").next().click();
        showBtn();
    });
}

// 下一步按钮切换
function showBtn() {
    var lastTabItem = $(".tab-nav li:last");
    if( lastTabItem.hasClass("current") ) {
        $("#submit").removeClass("hidden");
        $("#submit-next").addClass("hidden");
    } else {
        $("#submit").addClass("hidden");
        $("#submit-next").removeClass("hidden");
    }
}



//加载左侧菜单  ---MAINCOENTENT
$(".topmenu_list").on('click','li',function(){
    var url=$(this).attr('data-url');
    var pid=$(this).attr('data-id');
    var menu=$("#menulist");
    setCookie('refresh_menu_id',pid,1);
    $(this).addClass('hovermenu').siblings('li').removeClass('hovermenu');

    loading(menu);    
    if(pid==''){
        alert("错误！");
        return false;
    }
    $.post(url,{pid:pid},function(data){
        // console.log(data);
        var menulist='';
        if(data){
            var menus=data._;
            for (var i = 0; menus.length>i; i++) {
                menulist += '<div onclick="getMainContent(\''+menus[i].title+'\',\''+menus[i].url+'\','+menus[i].id+');" class="menuone"><i class="icon-bookmark-empty"></i> '+menus[i].title+'</div>';
            }
            $("#menulisttop").html(data.categoryTitle);
           menu.html(menulist); 
        }
        
    });  
});

//加载右边主要内容
function getMainContent(title,url,tempid){
    var maincontent=$("#content_allmainview"); 
    loading(maincontent); 

    var juid=judgeEXIT(tempid);
    if(juid==0){
    //如右边主要内容不存在则添加
    addtabsIndex(tempid,title);  
    $.get(url,{},function(data){
        var sinfo='';
        loadingOut();
        if(!isNaN(data.status)){
            sinfo="<div style='text-align: center;padding-top:20px;font-size:20px;'><span style='font-size:80px;'>:)</span> <br/>"+data.info+"</div>";
        }else{
            sinfo=data;
        }
        maincontent.append("<div class='main_content' id='tabs_main_"+tempid+"' temp='"+tempid+"'>"+sinfo+"</div>");
        maincontent.find('div.main_content:last').show().siblings().hide();
    });
    }else{
        //如果存在则进行切换
        changetabs(maincontent,tempid);
    }
    //当前页存入COOKIE
    Set_refresh_cookie_url(title,url,tempid);
}

//判断右边主要内容是否已加载
function judgeEXIT(id){
    var stopaddtab=0;
    $("#tabs_title>td").each(function(){
        var tempid=$(this).attr('temp');
        if(tempid==id){
            stopaddtab=1;
            changetabs(this,id);
            return false;
        }
    });
    return stopaddtab;
}


//通用加载动画
function loading(strid){
    loadingOut();
    strid.append("<div id='load_content'><div class='loading_bg'></div><div class='spinner'><div class='rect1'></div><div class='rect2'></div><div class='rect3'></div><div class='rect4'></div><div class='rect5'></div></div></div>");
}
//关闭加载动画
function loadingOut(){
    $("#load_content").remove();
}


//添加入TabIndex快捷导航
function addtabsIndex(id,title){
    var tabindex=$("#tabs_title");
    var tabhtml='<td temp="'+id+'" onclick="changetabs(this,'+id+')" id="tabs_'+id+'"><div>'+title+'</div><span onclick="closetabs('+id+')"><span class="glyphicon glyphicon-remove"></span></span></td>';
    tabindex.append(tabhtml).parent().find('td:last').addClass('accive').siblings().removeClass('accive');
}

//tabIndex快捷导航切换
function changetabs(me,id){
    loadingOut();
    $(me).addClass("accive").siblings().removeClass('accive');
    $("#tabs_main_"+id).show().siblings().hide();
    //更新刷新页面
    var title=Get_accive_TITLE();    
    var url=Get_accive_URL();
    Set_refresh_cookie_url(title,url,id);
}
//关闭快捷导航
function closetabs(str){
    var has_act=$("#tabs_"+str).hasClass('accive');
    $("#tabs_"+str+",#tabs_main_"+str).remove();
    if(has_act==true){
        //关闭当前主内容后显示上一级主内容；
        $("#content_allmainview").find('div.main_content:last').show();
        $("#tabs_title").find('td:last').addClass('accive');
    }
    // 更新刷新页面    
    var id=Get_accive_TEMPID();
    var title=Get_accive_TITLE();
    var url=Get_accive_URL();
    Set_refresh_cookie_url(title,url,id);
}


//链接进入下一级
function getNextMainContent(url){
    var newID=tabs_main_id();
    GetHtmlData(url,newID);
}

// 返回上一页   一级返回
function Histroy_Back(){
    var url=getCookie('chs_home___forward__');
    getNextMainContent(url);
}

//获取当前显示页的 temp 值
function Get_accive_TEMPID(){
    return $("#tabs_title").find('.accive').attr('temp');
}
//获取当前显示页的 URL 值
function Get_accive_URL(){
    var id=Get_accive_TEMPID();
    return $("#tabs_main_"+id).find('.Refresh').attr('url');
}
//获取当前显示页的 TITLE 值
function Get_accive_TITLE(){
    var title=$("#tabs_title>.accive").find('div').text();
    return title;
}

//刷新函数
function RefreshHtml(me){
    var thisID=tabs_main_id();
    var reURL=$(me).attr('url');
    GetHtmlData(reURL,thisID);
}

//刷新当前页面  自动刷新调用
function AutoRefresh(){
    var tempid=Get_accive_TEMPID();
    $("#tabs_main_"+tempid).find('.Refresh').click();
}

//通用GET网页数据函数   替换
/*
* url     GET数据网址
* shtml   数据附加位置
*/
function GetHtmlData(url,shtml){
    loading(shtml);
    $.get(url,{},function(data){
        loadingOut();
        var sinfo='';
        if(!isNaN(data.status)){
            sinfo="<div style='text-align: center;padding-top:20px;font-size:20px;'><span style='font-size:80px;'>:)</span> <br/>"+data.info+"</div>";
        }else{
            sinfo=data;
        }
        shtml.html(sinfo);
     });
}

// 首页任务列表预览
function Get_main_task(me,url){    
    $(me).addClass('hover').siblings().removeClass('hover');
    GetTask_Page(url);
}

function GetTask_Page(url){
    var shtml=$("#main-list-task");
    GetHtmlData(url,shtml);
}

// 领取任务 type:1为内页，2为首页任务预览
function Get_mytask(type,url){
  $.get(url,{},function(data){
      if(data.status==1){
        updateAlert("领取任务成功！" ,'alert-success');
      }else{
        updateAlert(data.info , 'alert-error');
      }
      setTimeout(function(){
        if(type==1){
            AutoRefresh();
        }else{
            Refresh_indexTask();
        }
        closeAlert();
      },2000);
  });
}
// 首页任务预览专属刷新接口
function Refresh_indexTask(){
    var refresh=$("#tabs_main_0").find('.Refresh').attr('url');
    GetTask_Page(refresh);
}

//添加编辑接口  ······弹出层
function addModal(url){    
    $.get(url,{},function(data){
        if(!isNaN(data.status)){ //返回ajax数据时调用
            updateAlert(data.info ,'alert-error');
            setTimeout(function(){
                closeAlert();
            },2000);
        }else{
            var thisid=tabs_main_id();
            thisid.append(data);
        }
     });
}

// 关闭弹出层
function CloseModal(me){
    var myModal=$(me).parent().parent().parent().parent();
    // alert(myModal.attr('class'));
    myModal.modal('hide');
    setTimeout(function(){
        // 如果弹出层加载了日期控件，则同时关闭日期控件
        if($('.datetimepicker').hasClass('datetimepicker-dropdown-bottom-right')){
            $(".datetimepicker-dropdown-bottom-right").remove();
        }
        myModal.remove();
    },700);
}


// 当前页面 $("#tabs_main_"+tempid);
function tabs_main_id(){
    var tempid=Get_accive_TEMPID();
    return $("#tabs_main_"+tempid);
}


// 选择用户弹出层函数
// typeid=1 显示选择部门  typeid为其他都显示部门及部门下的用户
function Get_changeuser(url,dataname,backvalue,backname,typename,typeid,jump_url){
    var userhtml='';
    var that=tabs_main_id();
    userhtml +='<div class="modal fade bs-example-modal-sm" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel">';
    userhtml +='<div class="modal-dialog modal-sm" role="document">';
    userhtml +='<div class="modal-content">';
    userhtml +='<div class="modal-header">';
    userhtml +='<button type="button" class="close" onclick="close_changeuser();"><span aria-hidden="true">&times;</span></button>';
    userhtml +='<h4 class="modal-title" id="gridSystemModalLabel">选择'+dataname+'</h4>';
    userhtml +='</div>';
    userhtml +='<div id="user_body" class="modal-body" style="max-height:300px;overflow:auto;">';
    userhtml +='  ...';
    userhtml +='</div>';
    userhtml +='<div class="modal-footer">';
    userhtml +='<button type="button" class="btn btn-default" onclick="close_changeuser();">取消</button>';
    userhtml +='<button type="button" class="btn btn-primary" onclick="sure_user(\''+backvalue+'\',\''+backname+'\',\''+jump_url+'\',\''+dataname+'\');">确定</button></div>';
    userhtml +='</div></div>';
    userhtml +='</div>';
    that.append(userhtml);

    var modebody=$('#user_body');
    modebody.html('<div align="center" style="padding:30px"><img src="/Public/Home/images/loading.gif"></div>');
    $.post(url,{},function(data){        
        if(data.status==1){
            var ulist='';
            var groupdata=data.glist;
            for (var j = 0; groupdata.length > j; j++) {
                ulist +='<div class="depth" style="line-height:36px;border-bottom:1px solid #e2e2e2;"><div class="button_depth" style="cursor: pointer;"><i class="iconfont" style="margin-right:10px;">&#xe634;</i>'+groupdata[j].title+'';
                if(typeid==1){
                    ulist +='<input name="changeuserinput" xname="'+groupdata[j].title+'" value="'+groupdata[j].id+'" style="width:18px;height:18px;float:right;margin-top:10px;margin-right:10px;" type="'+typename+'">';
                }
                ulist +='</div>';
                if(groupdata[j]['userlist']!=''){
                    ulist +='<div class="user_warper display_none">';
                    var userlist=groupdata[j]['userlist'];
                    for (var k = 0; userlist.length > k; k++) {
                    ulist +='<div class="userlists">';
                    ulist +='<i class="iconfont"><img src="'+userlist[k].cover+'" height="27"></i>'+userlist[k].nickname+'<span style="font-size:12px;color:#999;">（'+userlist[k].jobs+'）</span>';
                    ulist +='<input name="changeuserinput" xname="'+userlist[k].nickname+'" value="'+userlist[k].id+'" style="width:18px;height:18px;float:right;margin-top:10px;margin-right:10px;" type="'+typename+'">';
                    ulist +='</div>';
                    }
                    ulist +='</div>';
                }
                ulist +='</div>';
            }

            modebody.html(ulist);       
        }else{
            modebody.html('Error!');    
        }
    });
}
$('body').on('click','.button_depth',function(){
    var uw=$(this).parent().find('.user_warper');
    if(uw.hasClass('display_none')){
        uw.removeClass('display_none');
        uw.addClass('display_show');
    }else{
        uw.removeClass('display_show');
        uw.addClass('display_none');
    }
});
function close_changeuser(){
    var myModal_user=$('.bs-example-modal-sm');
    myModal_user.modal('hide');
    setTimeout(function(){
        myModal_user.remove();
    },700);
}
// 保存提交
function sure_user(backvalue,backname,url,dataname){  
    var s=''; 
    var h='';
    var xname,o1;
    var obj=document.getElementsByName('changeuserinput');
    for(var i=0; i<obj.length; i++){ 
        o1 = $(obj[i]);
        if(obj[i].checked){
            xname= o1.attr('xname');
            s+=','+obj[i].value; //如果选中，将value添加到变量s中 
            h+=','+xname;
        }
    }
    s=s.substr(1);
    h=h.substr(1);
    if(s=='' || h==''){
        updateAlert('请选择'+dataname+'！' ,'alert-error');
        setTimeout(function(){
            closeAlert();
        },2000);
        return false;
    }    
    if(backvalue=='undefined' && backname=='undefined'){
        changeTaskUser(url,s);
    }else{
        $("#"+backname).val(h);
        $("#"+backvalue).val(s);
    }
    close_changeuser();
}


// 上级主管 弹出层
$('body').on('click','.getuser',function(){
    var url=$(this).attr('url');//获取数据的地址
    var dataname=$(this).attr('data-name');//获取抬头标题
    var backvalue=$(this).attr('data-value');//获取需要返回的真实input  ID值
    var backname=$(this).attr('data-backname');//获取需要返回显示名称的input ID值
    var typename=$(this).attr('type-id'); //获取 input 类型，checkbox 或者 radio  (单选或多选)
    var typeid=$(this).attr('data-tid');  //获取 显示 部门或者部门及用户   1为部门，其他都为部门及用户 
    var jump_url=$(this).attr('data-jumpurl');
    Get_changeuser(url,dataname,backvalue,backname,typename,typeid,jump_url);
});

// 指派用户post操作
function changeTaskUser(url,s){
    $.post(url,{dist:s},function(data){
        if(data.status==1){
            updateAlert(data.info ,'alert-success');
        }else{
            if(data.info==''){
                updateAlert("操作失败！" ,'alert-error');
            }else{
                updateAlert(data.info ,'alert-error');
            }
        }
        setTimeout(function(){
            closeAlert();
            AutoRefresh();
        },2000);
    });
}


//点击刷新后记录当前显示TEMP ID 和URL   
// 用于刷新当前页面
function Set_refresh_cookie_url(title,url,tempid){
    setCookie('refresh_title',title,1);
    setCookie('refresh_url',url,1);
    setCookie('chs_home___forward__',url);    
    setCookie('refresh_tempid',tempid,1);
}

function getCookie(c_name)
{
if (document.cookie.length>0)
  {
  c_start=document.cookie.indexOf(c_name + "=")
  if (c_start!=-1)
    { 
    c_start=c_start + c_name.length+1 
    c_end=document.cookie.indexOf(";",c_start)
    if (c_end==-1) c_end=document.cookie.length
    return unescape(document.cookie.substring(c_start,c_end))
    } 
  }
return ""
}

function setCookie(c_name,value,expiredays)
{
var exdate=new Date()
exdate.setDate(exdate.getDate()+expiredays)
document.cookie=c_name+ "=" +escape(value)+
((expiredays==null) ? "" : ";expires="+exdate.toGMTString())
}

//清除COOKIE
function clear_Cookie(){
    setCookie('refresh_title','');
    setCookie('refresh_url','');
    setCookie('chs_home___forward__','');    
    setCookie('refresh_tempid','');
    setCookie('refresh_menu_id','');
}

//关闭提示框函数
function closeAlert(){
    $('#top-alert').find('button').click();
}

// 查看提醒信息调用已读接口
function Look_Notice(nid,url,showid){
    // 对url进行处理，如果是首页点击打开的增加参数/type/4
    var tempid=Get_accive_TEMPID();
    var jumpurl=url;
    if(url.substring(0,30)=="/index.php?s=/Task/task_detail" && tempid==0){
        jumpurl=jumpurl+"/type/4";
    }
    // alert(jumpurl);
    //标记已读
    $.get('/index.php?s=/Notice/setStatus',{status:1,ids:nid},function(data){
        if (data.status==1) {            
            if(showid==1 || showid==2){
                //打开查看的信息框
                addModal(jumpurl);
            }else if(showid==3){
                // 领取任务
                Get_mytask(2,jumpurl);
            }
        }else{
            updateAlert(data.info,'alert-error');
            setTimeout(function(){
                closeAlert();
            },2000);
        }
        // 初始化获取系统提醒
        ajaxgetnotice();
        // 如果是提醒页点击查看的同时标注已读颜色
        var maincontent_notice=tabs_main_id();
        maincontent_notice.find('[data-id='+nid+']').addClass('notice_isread');

    });
}