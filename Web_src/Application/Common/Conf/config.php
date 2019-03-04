<?php
define('UC_AUTH_KEY', 'EyYmC>|^2cq#!=ZS@Uvh:o~z+%Gfiswt)J,Ml/j['); //默认数据加密KEY
return array(
	//'配置项'=>'配置值'
	'DEFAULT_MODULE'     => 'Home',
    'MODULE_DENY_LIST'   => array('Common'),
    'MODULE_ALLOW_LIST'  => array('Home'),
    /* 系统数据加密设置 */
    'DATA_AUTH_KEY' => 'EyYmC>|^2cq#!=ZS@Uvh:o~z+%Gfiswt)J,Ml/j[', //默认数据加密KEY

    /* 用户相关设置 */
    'USER_MAX_CACHE'     => 1000, //最大缓存用户数
    'USER_ADMINISTRATOR' => 1, //管理员用户ID
	    /* URL配置 */
    'URL_CASE_INSENSITIVE' => true, //默认false 表示URL区分大小写 true则表示不区分大小写
    'URL_MODEL'            => 3, //URL模式
    'VAR_URL_PARAMS'       => '', // PATHINFO URL参数变量
    'URL_PATHINFO_DEPR'    => '/', //PATHINFO URL分割符

    /* 全局过滤配置 */
    'DEFAULT_FILTER' => '', //全局过滤函数
	
	/* 数据库设置 */
    'DB_TYPE'               =>  'mysql',     // 数据库类型
    'DB_HOST'               =>  'localhost', // 服务器地址
    'DB_NAME'               =>  'chs_oa',    // 数据库名
    'DB_USER'               =>  'root',      // 用户名
    'DB_PWD'                =>  'root',      // 密码
    'DB_PORT'               =>  '3306',      // 端口
    'DB_PREFIX'             =>  'chsoa_',    // 数据库表前缀


    // APICLOUD云推送
    'AppID'               =>  'A6934025903131',
    'AppKey'               =>  '6A3BCB29-43AB-31A0-481F-3DEF2A9B833D',
    'AppPath'               =>  'https://p.apicloud.com/api/push/message/',
    'timeOut'               =>  30,
);