/*
Navicat MySQL Data Transfer

Source Server         : 阿里云
Source Server Version : 50629
Source Host           : 121.41.9.33:3306
Source Database       : chsoa

Target Server Type    : MYSQL
Target Server Version : 50629
File Encoding         : 65001

Date: 2017-04-10 13:54:28
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for `chsoa_action`
-- ----------------------------
DROP TABLE IF EXISTS `chsoa_action`;
CREATE TABLE `chsoa_action` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `name` char(30) NOT NULL DEFAULT '' COMMENT '行为唯一标识',
  `title` char(80) NOT NULL DEFAULT '' COMMENT '行为说明',
  `remark` char(140) NOT NULL DEFAULT '' COMMENT '行为描述',
  `rule` text COMMENT '行为规则',
  `log` text COMMENT '日志规则',
  `type` tinyint(2) unsigned NOT NULL DEFAULT '1' COMMENT '类型',
  `status` tinyint(2) NOT NULL DEFAULT '0' COMMENT '状态',
  `update_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '修改时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=12 DEFAULT CHARSET=utf8 COMMENT='系统行为表';

-- ----------------------------
-- Records of chsoa_action
-- ----------------------------

-- ----------------------------
-- Table structure for `chsoa_action_log`
-- ----------------------------
DROP TABLE IF EXISTS `chsoa_action_log`;
CREATE TABLE `chsoa_action_log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `action_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '行为id',
  `user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '执行用户id',
  `action_ip` bigint(20) NOT NULL COMMENT '执行行为者ip',
  `model` varchar(50) NOT NULL DEFAULT '' COMMENT '触发行为的表',
  `record_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '触发行为的数据id',
  `remark` varchar(255) NOT NULL DEFAULT '' COMMENT '日志备注',
  `status` tinyint(2) NOT NULL DEFAULT '1' COMMENT '状态',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '执行行为的时间',
  PRIMARY KEY (`id`),
  KEY `action_ip_ix` (`action_ip`) USING BTREE,
  KEY `action_id_ix` (`action_id`) USING BTREE,
  KEY `user_id_ix` (`user_id`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=342 DEFAULT CHARSET=utf8 COMMENT='行为日志表';

-- ----------------------------
-- Records of chsoa_action_log
-- ----------------------------

-- ----------------------------
-- Table structure for `chsoa_auth_group`
-- ----------------------------
DROP TABLE IF EXISTS `chsoa_auth_group`;
CREATE TABLE `chsoa_auth_group` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '用户组id,自增主键',
  `title` char(20) NOT NULL DEFAULT '' COMMENT '用户组中文名称',
  `description` varchar(255) NOT NULL DEFAULT '' COMMENT '描述信息',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '用户组状态：为1正常，为0禁用,-1为删除',
  `rules` varchar(500) NOT NULL DEFAULT '' COMMENT '用户组拥有的规则id，多个规则 , 隔开',
  `module` varchar(20) NOT NULL DEFAULT '' COMMENT '用户组所属模块',
  `type` tinyint(4) NOT NULL DEFAULT '0' COMMENT '组类型',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of chsoa_auth_group
-- ----------------------------
INSERT INTO `chsoa_auth_group` VALUES ('3', '系统管理员', '此组为系统管理员权限组', '1', '3,10,11,12,24,25,27,28,29,32,33,34,35,36,37,38,39,40,42,43,44,45,46,47,48,49,50,51,54,55,56,57,59,60,61,62,63,64,65,66,67,68', 'home', '1');
INSERT INTO `chsoa_auth_group` VALUES ('4', '软件组', '研发部门的软件组', '1', '24,25,26,37,44,45,46,47,48,49,50,56', 'home', '1');
INSERT INTO `chsoa_auth_group` VALUES ('5', '硬件组', '研发部门的硬件组', '1', '', 'home', '1');
INSERT INTO `chsoa_auth_group` VALUES ('6', '管理层用户组', '此用户组为管理层用户组', '1', '', 'home', '1');

-- ----------------------------
-- Table structure for `chsoa_auth_group_access`
-- ----------------------------
DROP TABLE IF EXISTS `chsoa_auth_group_access`;
CREATE TABLE `chsoa_auth_group_access` (
  `uid` int(10) unsigned NOT NULL COMMENT '用户id',
  `group_id` mediumint(8) unsigned NOT NULL COMMENT '用户组id',
  UNIQUE KEY `uid_group_id` (`uid`,`group_id`) USING BTREE,
  KEY `uid` (`uid`) USING BTREE,
  KEY `group_id` (`group_id`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of chsoa_auth_group_access
-- ----------------------------
INSERT INTO `chsoa_auth_group_access` VALUES ('2', '3');
INSERT INTO `chsoa_auth_group_access` VALUES ('2', '4');
INSERT INTO `chsoa_auth_group_access` VALUES ('4', '3');
INSERT INTO `chsoa_auth_group_access` VALUES ('5', '3');

-- ----------------------------
-- Table structure for `chsoa_auth_rule`
-- ----------------------------
DROP TABLE IF EXISTS `chsoa_auth_rule`;
CREATE TABLE `chsoa_auth_rule` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `pid` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '父类',
  `module` varchar(20) NOT NULL DEFAULT '' COMMENT '规则所属module',
  `type` tinyint(2) NOT NULL DEFAULT '1' COMMENT '1-模块;2-主菜单;3-操作',
  `icon` char(50) DEFAULT NULL COMMENT '图标',
  `url` varchar(255) NOT NULL DEFAULT '' COMMENT 'URL',
  `title` char(80) NOT NULL DEFAULT '' COMMENT '规则中文描述',
  `group` varchar(50) DEFAULT NULL COMMENT '分组',
  `sort` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '排序（同级有效）',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否有效(0:无效,1:有效)',
  `content` varchar(255) DEFAULT NULL COMMENT '备注',
  `condition` varchar(30) NOT NULL DEFAULT '' COMMENT '规则附加条件',
  PRIMARY KEY (`id`),
  KEY `module` (`sort`,`status`,`type`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=69 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of chsoa_auth_rule
-- ----------------------------
INSERT INTO `chsoa_auth_rule` VALUES ('2', '0', 'home', '1', '&#xe607;', 'AuthGroup/index', '系统', '', '3', '1', '系统模块', '');
INSERT INTO `chsoa_auth_rule` VALUES ('10', '2', 'home', '1', '', 'Group/index', '组织架构', '', '0', '1', '公司组织架构', '');
INSERT INTO `chsoa_auth_rule` VALUES ('14', '2', 'home', '1', null, 'AuthRule/index', '节点管理', null, '0', '1', '系统各个菜单节点管理', '');
INSERT INTO `chsoa_auth_rule` VALUES ('11', '2', 'home', '1', '', 'Users/index', '用户管理', '', '0', '1', '用户管理', '');
INSERT INTO `chsoa_auth_rule` VALUES ('12', '2', 'home', '1', '', 'AuthGroup/index', '权限组管理', '', '0', '1', '权限组管理', '');
INSERT INTO `chsoa_auth_rule` VALUES ('13', '0', 'home', '1', '', 'Knowledge/index', '知识库', '', '2', '0', '公司知识库', '');
INSERT INTO `chsoa_auth_rule` VALUES ('20', '0', 'home', '1', '&#xe628;', 'Task/index', '任务', '001', '1', '1', '', '');
INSERT INTO `chsoa_auth_rule` VALUES ('23', '14', 'home', '1', '', 'AuthRule/add', '添加', '', '0', '1', '', '');
INSERT INTO `chsoa_auth_rule` VALUES ('24', '20', 'home', '1', '', 'Task/mytask', '我的任务', '', '0', '1', '', '');
INSERT INTO `chsoa_auth_rule` VALUES ('25', '20', 'home', '1', '', 'Task/buildtask', '我创建任务', '', '0', '1', '', '');
INSERT INTO `chsoa_auth_rule` VALUES ('26', '20', 'home', '1', '', 'Task/bedone', '未完成任务', '', '0', '0', '', '');
INSERT INTO `chsoa_auth_rule` VALUES ('27', '10', 'home', '1', '', 'Group/add', '添加', '', '0', '1', '', '');
INSERT INTO `chsoa_auth_rule` VALUES ('28', '10', 'home', '1', '', 'Group/edit', '编辑', '', '0', '1', '', '');
INSERT INTO `chsoa_auth_rule` VALUES ('29', '10', 'home', '1', '', 'Group/setStatus', '改变状态', '', '0', '1', '', '');
INSERT INTO `chsoa_auth_rule` VALUES ('30', '14', 'home', '1', '', 'AuthRule/edit', '编辑', '', '0', '1', '', '');
INSERT INTO `chsoa_auth_rule` VALUES ('31', '14', 'home', '1', '', 'AuthRule/setStatus', '改变状态', '', '0', '1', '', '');
INSERT INTO `chsoa_auth_rule` VALUES ('3', '11', 'home', '1', '', 'Users/add', '添加', '', '0', '1', '', '');
INSERT INTO `chsoa_auth_rule` VALUES ('32', '11', 'home', '1', '', 'Users/user_edit', '编辑', '', '0', '1', '', '');
INSERT INTO `chsoa_auth_rule` VALUES ('33', '11', 'home', '1', '', 'Users/setStatus', '改变状态', '', '0', '1', '', '');
INSERT INTO `chsoa_auth_rule` VALUES ('34', '12', 'home', '1', '', 'AuthGroup/add', '添加', '', '0', '1', '', '');
INSERT INTO `chsoa_auth_rule` VALUES ('35', '12', 'home', '1', '', 'AuthGroup/edit', '编辑', '', '0', '1', '', '');
INSERT INTO `chsoa_auth_rule` VALUES ('36', '12', 'home', '1', '', 'AuthGroup/setStatus', '改变状态', '', '0', '1', '', '');
INSERT INTO `chsoa_auth_rule` VALUES ('37', '24', 'home', '1', '', 'Task/mytask', '任务列表', '', '0', '1', '', '');
INSERT INTO `chsoa_auth_rule` VALUES ('38', '10', 'home', '1', '', 'Group/index', '列表', '', '0', '1', '', '');
INSERT INTO `chsoa_auth_rule` VALUES ('39', '11', 'home', '1', '', 'Users/index', '列表', '', '0', '1', '', '');
INSERT INTO `chsoa_auth_rule` VALUES ('40', '12', 'home', '1', '', 'AuthGroup/index', '列表', '', '0', '1', '', '');
INSERT INTO `chsoa_auth_rule` VALUES ('41', '14', 'home', '1', '', 'AuthRule/index', '列表', '', '0', '1', '', '');
INSERT INTO `chsoa_auth_rule` VALUES ('42', '12', 'home', '1', '', 'AuthGroup/access', '节点授权', '', '0', '1', '', '');
INSERT INTO `chsoa_auth_rule` VALUES ('43', '12', 'home', '1', '', 'AuthGroup/writeGroup', '保存授权', '', '0', '1', '', '');
INSERT INTO `chsoa_auth_rule` VALUES ('44', '24', 'home', '1', '', 'Task/task_detail', '查看任务详情', '', '0', '1', '', '');
INSERT INTO `chsoa_auth_rule` VALUES ('45', '24', 'home', '1', '', 'Task/taskResult', '反馈任务结果', '', '0', '1', '', '');
INSERT INTO `chsoa_auth_rule` VALUES ('46', '24', 'home', '1', '', 'Task/submit_task', '提交主管审核', '', '0', '1', '', '');
INSERT INTO `chsoa_auth_rule` VALUES ('47', '25', 'home', '1', '', 'Task/addtask', '创建任务', '', '0', '1', '', '');
INSERT INTO `chsoa_auth_rule` VALUES ('48', '25', 'home', '1', '', 'Task/edit', '编辑任务', '', '0', '1', '', '');
INSERT INTO `chsoa_auth_rule` VALUES ('49', '25', 'home', '1', '', 'Task/setStatus', '删除任务', '', '0', '1', '', '');
INSERT INTO `chsoa_auth_rule` VALUES ('50', '25', 'home', '1', '', 'Task/sendDists', '指派任务人', '', '0', '1', '', '');
INSERT INTO `chsoa_auth_rule` VALUES ('62', '59', 'home', '1', '', 'Notice/index', '提醒列表', '', '0', '1', '', '');
INSERT INTO `chsoa_auth_rule` VALUES ('51', '12', 'home', '1', '', 'AuthGroup/user', '成员授权', '', '0', '1', '', '');
INSERT INTO `chsoa_auth_rule` VALUES ('52', '13', 'home', '1', '', 'Knowledge/software', '软件组', '', '0', '1', '', '');
INSERT INTO `chsoa_auth_rule` VALUES ('53', '13', 'home', '1', '', 'Knowledge/hardware', '硬件组', '', '0', '1', '', '');
INSERT INTO `chsoa_auth_rule` VALUES ('54', '12', 'home', '1', '', 'AuthGroup/addToGroup', '添加成员（授权）', '', '0', '1', '', '');
INSERT INTO `chsoa_auth_rule` VALUES ('55', '12', 'home', '1', '', 'AuthGroup/removeFromGroup', '解除授权', '', '0', '1', '', '');
INSERT INTO `chsoa_auth_rule` VALUES ('56', '24', 'home', '1', '', 'Task/get_mytask', '领取任务', '', '0', '1', '', '');
INSERT INTO `chsoa_auth_rule` VALUES ('57', '24', 'home', '1', '', 'Task/downloadWord', '导出Word', '', '0', '1', '', '');
INSERT INTO `chsoa_auth_rule` VALUES ('58', '0', 'home', '1', '&#xe697;', 'Myarea', '我的地盘', '', '0', '1', '', '');
INSERT INTO `chsoa_auth_rule` VALUES ('59', '58', 'home', '1', '', 'Notice/index', '提醒信息', '个人中心', '0', '1', '', '');
INSERT INTO `chsoa_auth_rule` VALUES ('60', '58', 'home', '1', '', 'Users/uidinfo', '个人资料', '个人中心', '1', '1', '', '');
INSERT INTO `chsoa_auth_rule` VALUES ('61', '24', 'home', '1', '', 'Task/examine', '审批任务', '', '0', '1', '', '');
INSERT INTO `chsoa_auth_rule` VALUES ('63', '59', 'home', '1', '', 'Notice/ajaxGetNotice', '弹出提醒', '', '0', '1', '', '');
INSERT INTO `chsoa_auth_rule` VALUES ('64', '59', 'home', '1', '', 'Notice/setStatus', '标为已读', '', '0', '1', '', '');
INSERT INTO `chsoa_auth_rule` VALUES ('65', '2', 'home', '1', '', 'Config/group', '系统设置', '', '0', '1', '', '');
INSERT INTO `chsoa_auth_rule` VALUES ('66', '24', 'home', '1', '', 'Task/task_test', '确认（测试）反馈结果', '', '0', '1', '', '');
INSERT INTO `chsoa_auth_rule` VALUES ('67', '24', 'home', '1', '', 'Task/Todofile', '归档操作', '', '0', '1', '', '');
INSERT INTO `chsoa_auth_rule` VALUES ('68', '65', 'home', '1', '', 'Config/save', '保存设置', '', '0', '1', '', '');

-- ----------------------------
-- Table structure for `chsoa_config`
-- ----------------------------
DROP TABLE IF EXISTS `chsoa_config`;
CREATE TABLE `chsoa_config` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '配置ID',
  `name` varchar(30) NOT NULL DEFAULT '' COMMENT '配置名称',
  `type` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '配置类型',
  `title` varchar(50) NOT NULL DEFAULT '' COMMENT '配置说明',
  `group` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '配置分组',
  `extra` varchar(255) NOT NULL DEFAULT '' COMMENT '配置值',
  `remark` varchar(100) NOT NULL DEFAULT '' COMMENT '配置说明',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '状态',
  `value` text COMMENT '配置值',
  `sort` smallint(3) unsigned NOT NULL DEFAULT '0' COMMENT '排序',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_name` (`name`) USING BTREE,
  KEY `type` (`type`) USING BTREE,
  KEY `group` (`group`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=48 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of chsoa_config
-- ----------------------------
INSERT INTO `chsoa_config` VALUES ('1', 'WEB_SITE_TITLE', '1', '系统标题', '1', '', '系统标题前台显示标题', '1378898976', '1379235274', '1', 'CHS任务系统', '1');
INSERT INTO `chsoa_config` VALUES ('4', 'WEB_SITE_CLOSE', '4', '关闭站点', '1', '0:关闭,1:开启', '站点关闭后其他用户不能访问，管理员可以正常访问', '1378898976', '1379235296', '1', '1', '20');
INSERT INTO `chsoa_config` VALUES ('34', 'DENY_VISIT', '3', '超管专限控制器方法', '4', '', '仅超级管理员可访问的控制器方法', '1386644141', '1386644659', '1', '', '0');
INSERT INTO `chsoa_config` VALUES ('25', 'LIST_ROWS', '0', '每页记录数', '1', '', '数据列表每页显示记录数', '1379503896', '1380427745', '1', '10', '10');
INSERT INTO `chsoa_config` VALUES ('26', 'USER_ALLOW_REGISTER', '4', '是否允许用户注册', '4', '0:关闭注册\r\n1:允许注册', '是否开放用户注册', '1379504487', '1379504580', '1', '1', '3');
INSERT INTO `chsoa_config` VALUES ('28', 'DATA_BACKUP_PATH', '1', '数据库备份根路径', '0', '', '路径必须以 / 结尾', '1381482411', '1381482411', '1', './Data/', '5');
INSERT INTO `chsoa_config` VALUES ('29', 'DATA_BACKUP_PART_SIZE', '0', '数据库备份卷大小', '0', '', '该值用于限制压缩后的分卷最大长度。单位：B；建议设置20M', '1381482488', '1381729564', '1', '20971520', '7');
INSERT INTO `chsoa_config` VALUES ('30', 'DATA_BACKUP_COMPRESS', '4', '数据库备份文件是否启用压缩', '0', '0:不压缩\r\n1:启用压缩', '压缩备份文件需要PHP环境支持gzopen,gzwrite函数', '1381713345', '1381729544', '1', '1', '9');
INSERT INTO `chsoa_config` VALUES ('31', 'DATA_BACKUP_COMPRESS_LEVEL', '4', '数据库备份文件压缩级别', '0', '1:普通\r\n4:一般\r\n9:最高', '数据库备份文件的压缩级别，该配置在开启压缩时生效', '1381713408', '1381713408', '1', '9', '10');
INSERT INTO `chsoa_config` VALUES ('33', 'ALLOW_VISIT', '3', '不受限控制器方法', '0', '', '', '1386644047', '1386644741', '1', '0:index/index\r\n1:Users/getUser_dept\r\n2:Task/dropdown_taskmenu\r\n3:Index/main\r\n4:Notice/ajaxGetNotice\r\n5:AuthRule/sort\r\n6:AuthRule/getMenu', '0');
INSERT INTO `chsoa_config` VALUES ('45', 'ADMIN_SUPERMAN', '1', '超级管理员', '2', '', '用于任务处是否可以查看全部任务', '1387165685', '1387165685', '1', '1,4,2', '0');
INSERT INTO `chsoa_config` VALUES ('46', 'FILE_MANS', '1', '任务归档人', '2', '', '用于任务', '1386644047', '1386644047', '1', '4,2', '0');
INSERT INTO `chsoa_config` VALUES ('47', 'TASK_EXAMINE', '1', '默认任务审核（领导）', '2', '', '用于任务', '1387165685', '1387165685', '1', '4,2', '0');
INSERT INTO `chsoa_config` VALUES ('20', 'CONFIG_GROUP_LIST', '3', '配置分组', '4', '', '配置分组', '1379228036', '1384418383', '1', '1:基本\r\n2:任务\r\n4:系统', '0');
INSERT INTO `chsoa_config` VALUES ('35', 'REPLY_LIST_ROWS', '0', '回复列表每页条数', '0', '', '', '1386645376', '1387178083', '1', '10', '0');
INSERT INTO `chsoa_config` VALUES ('36', 'ADMIN_ALLOW_IP', '2', '后台允许访问IP', '4', '', '多个用逗号分隔，如果不配置表示不限制IP访问', '1387165454', '1387165553', '1', '', '12');
INSERT INTO `chsoa_config` VALUES ('37', 'SHOW_PAGE_TRACE', '4', '是否显示页面Trace', '4', '0:关闭\r\n1:开启', '是否显示页面Trace信息', '1387165685', '1387165685', '1', '0', '1');

-- ----------------------------
-- Table structure for `chsoa_file`
-- ----------------------------
DROP TABLE IF EXISTS `chsoa_file`;
CREATE TABLE `chsoa_file` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '文件ID',
  `name` char(30) NOT NULL DEFAULT '' COMMENT '原始文件名',
  `savename` char(20) NOT NULL DEFAULT '' COMMENT '保存名称',
  `savepath` char(30) NOT NULL DEFAULT '' COMMENT '文件保存路径',
  `ext` char(5) NOT NULL DEFAULT '' COMMENT '文件后缀',
  `mime` char(40) NOT NULL DEFAULT '' COMMENT '文件mime类型',
  `size` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '文件大小',
  `md5` char(32) NOT NULL DEFAULT '' COMMENT '文件md5',
  `sha1` char(40) NOT NULL DEFAULT '' COMMENT '文件 sha1编码',
  `location` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '文件保存位置',
  `url` varchar(255) NOT NULL DEFAULT '' COMMENT '远程地址',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '上传时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_md5` (`md5`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=utf8 COMMENT='文件表';

-- ----------------------------
-- Records of chsoa_file
-- ----------------------------
INSERT INTO `chsoa_file` VALUES ('6', '201603300919201920.jpg', '58c7c043bc398.jpg', '2017-03-14/', 'jpg', 'image/jpeg', '658414', '66dfffdf6d0780d94c398557302d4e08', 'a7a95361991bd83e26fa95ed4fae0d02303666b4', '0', '', '1489485891');
INSERT INTO `chsoa_file` VALUES ('7', '20140810135923982398.jpg', '58c7c0bbe5296.jpg', '2017-03-14/', 'jpg', 'image/jpeg', '27053', '8b7524a65cdd02252411166851b118bb', '26966058f4ff98c7a95039ab5ef091bdaab8a898', '0', '', '1489486011');
INSERT INTO `chsoa_file` VALUES ('8', '网络版app原型稿.pdf', '58c8a468a5990.pdf', '2017-03-15/', 'pdf', 'application/pdf', '148631', '7874d032e49484f1d97ef66a8c90a9dc', '826d1c9fe3223ff9c410e546fa2f2eb116a59da2', '0', '', '1489544296');

-- ----------------------------
-- Table structure for `chsoa_group`
-- ----------------------------
DROP TABLE IF EXISTS `chsoa_group`;
CREATE TABLE `chsoa_group` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL COMMENT '组名',
  `pid` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '上级ID',
  `sort` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '排序',
  `status` tinyint(3) DEFAULT '1' COMMENT '状态：-1删除，1为正常',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of chsoa_group
-- ----------------------------
INSERT INTO `chsoa_group` VALUES ('1', '开发部', '0', '0', '1');
INSERT INTO `chsoa_group` VALUES ('2', '硬件组', '0', '0', '1');
INSERT INTO `chsoa_group` VALUES ('3', '生产部', '0', '2', '1');
INSERT INTO `chsoa_group` VALUES ('4', '总经办', '0', '0', '1');
INSERT INTO `chsoa_group` VALUES ('5', '财务部', '0', '0', '1');
INSERT INTO `chsoa_group` VALUES ('6', '软件组', '0', '0', '1');
INSERT INTO `chsoa_group` VALUES ('7', '售后维修部', '0', '0', '1');

-- ----------------------------
-- Table structure for `chsoa_notice`
-- ----------------------------
DROP TABLE IF EXISTS `chsoa_notice`;
CREATE TABLE `chsoa_notice` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `Touid` int(5) unsigned NOT NULL COMMENT '接收提醒信息的用户ID',
  `typeid` int(11) unsigned NOT NULL DEFAULT '1' COMMENT '提醒类型：1为任务',
  `title` text NOT NULL COMMENT '标题',
  `task_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '任务ID',
  `show_id` int(2) unsigned NOT NULL DEFAULT '1' COMMENT '显示超链接类型，1为查看任务，2为审核，3为领取任务',
  `status` int(2) unsigned NOT NULL DEFAULT '0' COMMENT '是否已经查看，0未看，1已看',
  `ctime` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '通知提醒时间',
  PRIMARY KEY (`id`),
  KEY `index_touid` (`Touid`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=149 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of chsoa_notice
-- ----------------------------
INSERT INTO `chsoa_notice` VALUES ('62', '2', '1', '费用创建任务【测试任务1111111】等待审批', '19', '2', '1', '1488880284');
INSERT INTO `chsoa_notice` VALUES ('63', '4', '1', '费用创建任务【测试任务1111111】等待审批', '19', '2', '1', '1488880284');
INSERT INTO `chsoa_notice` VALUES ('64', '2', '1', '你有新的任务【测试任务1111111】', '19', '3', '1', '1488880362');
INSERT INTO `chsoa_notice` VALUES ('65', '5', '1', '你有新的任务【测试任务1111111】', '19', '3', '1', '1488880362');
INSERT INTO `chsoa_notice` VALUES ('66', '5', '1', '【测试任务1111111】黄工审批通过', '19', '1', '1', '1488880362');
INSERT INTO `chsoa_notice` VALUES ('67', '5', '1', '黄工已领取你的任务【测试任务1111111】', '19', '1', '1', '1488881121');
INSERT INTO `chsoa_notice` VALUES ('68', '4', '1', '黄工已提交【测试任务1111111】反馈结果，等待你的测试验证确定', '19', '1', '1', '1488881201');
INSERT INTO `chsoa_notice` VALUES ('69', '5', '1', '黄工已提交【测试任务1111111】反馈结果，等待你的测试验证确定', '19', '1', '1', '1488881201');
INSERT INTO `chsoa_notice` VALUES ('70', '2', '1', '黄工创建任务【测试测试用户4人任务】等待审批', '20', '2', '1', '1488881285');
INSERT INTO `chsoa_notice` VALUES ('71', '4', '1', '黄工创建任务【测试测试用户4人任务】等待审批', '20', '2', '1', '1488881285');
INSERT INTO `chsoa_notice` VALUES ('72', '1', '1', '你有新的任务【测试测试用户4人任务】', '20', '3', '1', '1488881601');
INSERT INTO `chsoa_notice` VALUES ('73', '2', '1', '你有新的任务【测试测试用户4人任务】', '20', '3', '1', '1488881601');
INSERT INTO `chsoa_notice` VALUES ('74', '5', '1', '你有新的任务【测试测试用户4人任务】', '20', '3', '1', '1488881601');
INSERT INTO `chsoa_notice` VALUES ('75', '4', '1', '你有新的任务【测试测试用户4人任务】', '20', '3', '1', '1488881601');
INSERT INTO `chsoa_notice` VALUES ('76', '2', '1', '【测试测试用户4人任务】秦总审批通过', '20', '1', '1', '1488881601');
INSERT INTO `chsoa_notice` VALUES ('77', '2', '1', '秦总已领取你的任务【测试测试用户4人任务】', '20', '1', '1', '1488881811');
INSERT INTO `chsoa_notice` VALUES ('78', '2', '1', '费用已领取你的任务【测试测试用户4人任务】', '20', '1', '1', '1488882164');
INSERT INTO `chsoa_notice` VALUES ('79', '4', '1', '黄工已提交【测试任务1111111】反馈结果，等待你的测试验证确定', '19', '1', '1', '1488937518');
INSERT INTO `chsoa_notice` VALUES ('80', '5', '1', '黄工已提交【测试任务1111111】反馈结果，等待你的测试验证确定', '19', '1', '1', '1488937518');
INSERT INTO `chsoa_notice` VALUES ('81', '2', '1', '黄工对你的任务【测试任务1111111】反馈结果确认（测试）为通过', '19', '1', '1', '1488944971');
INSERT INTO `chsoa_notice` VALUES ('82', '2', '1', '黄工对你的任务【测试任务1111111】反馈结果确认（测试）为通过', '19', '1', '1', '1488945056');
INSERT INTO `chsoa_notice` VALUES ('83', '4', '1', '秦总已提交【测试测试用户4人任务】反馈结果，等待你的测试验证确定', '20', '1', '1', '1488959420');
INSERT INTO `chsoa_notice` VALUES ('84', '2', '1', '秦总已提交【测试测试用户4人任务】反馈结果，等待你的测试验证确定', '20', '1', '1', '1488959420');
INSERT INTO `chsoa_notice` VALUES ('85', '2', '1', '你发布的任务【测试测试用户4人任务】秦总已完成', '20', '1', '1', '1488960677');
INSERT INTO `chsoa_notice` VALUES ('86', '4', '1', '秦总对你的任务【测试测试用户4人任务】反馈结果确认（测试）为通过', '20', '1', '1', '1488960677');
INSERT INTO `chsoa_notice` VALUES ('87', '4', '1', '费用已提交【测试测试用户4人任务】反馈结果，等待你的测试验证确定', '20', '1', '1', '1488961619');
INSERT INTO `chsoa_notice` VALUES ('88', '2', '1', '费用已提交【测试测试用户4人任务】反馈结果，等待你的测试验证确定', '20', '1', '1', '1488961619');
INSERT INTO `chsoa_notice` VALUES ('89', '2', '1', '你发布的任务【测试测试用户4人任务】费用已完成', '20', '1', '1', '1488961680');
INSERT INTO `chsoa_notice` VALUES ('90', '5', '1', '费用对你的任务【测试测试用户4人任务】反馈结果确认（测试）为通过', '20', '1', '1', '1488961680');
INSERT INTO `chsoa_notice` VALUES ('91', '5', '1', '费用已领取你的任务【测试任务1111111】', '19', '1', '1', '1488962012');
INSERT INTO `chsoa_notice` VALUES ('92', '4', '1', '费用已提交【测试任务1111111】反馈结果，等待你的测试验证确定', '19', '1', '1', '1488962037');
INSERT INTO `chsoa_notice` VALUES ('93', '5', '1', '费用已提交【测试任务1111111】反馈结果，等待你的测试验证确定', '19', '1', '1', '1488962037');
INSERT INTO `chsoa_notice` VALUES ('94', '4', '1', '【测试任务1111111】已完成，等待归档', '19', '1', '1', '1488962124');
INSERT INTO `chsoa_notice` VALUES ('95', '5', '1', '你发布的任务【测试任务1111111】费用已完成', '19', '1', '1', '1488962124');
INSERT INTO `chsoa_notice` VALUES ('96', '5', '1', '费用对你的任务【测试任务1111111】反馈结果确认（测试）为通过', '19', '1', '1', '1488962124');
INSERT INTO `chsoa_notice` VALUES ('97', '4', '1', '费用创建任务【费用创建的任务】等待审批', '21', '2', '1', '1489032214');
INSERT INTO `chsoa_notice` VALUES ('98', '4', '1', '管理员创建任务【测试上传附件】等待审批', '22', '2', '1', '1489485253');
INSERT INTO `chsoa_notice` VALUES ('99', '4', '1', '管理员创建任务【再次测试附件上传】等待审批', '23', '2', '1', '1489544333');
INSERT INTO `chsoa_notice` VALUES ('100', '2', '1', '管理员创建任务【再次测试附件上传】等待审批', '23', '2', '0', '1489544333');
INSERT INTO `chsoa_notice` VALUES ('101', '4', '1', '管理员创建任务【测试移动端推送】等待审批', '24', '2', '1', '1491039691');
INSERT INTO `chsoa_notice` VALUES ('102', '2', '1', '管理员创建任务【测试移动端推送】等待审批', '24', '2', '0', '1491039692');
INSERT INTO `chsoa_notice` VALUES ('103', '1', '1', '你有新的任务【测试移动端推送】', '24', '3', '0', '1491039970');
INSERT INTO `chsoa_notice` VALUES ('104', '1', '1', '【测试移动端推送】何总审批通过', '24', '1', '0', '1491039971');
INSERT INTO `chsoa_notice` VALUES ('105', '1', '1', '你有新的任务【测试移动端推送】', '24', '3', '0', '1491039976');
INSERT INTO `chsoa_notice` VALUES ('106', '1', '1', '【测试移动端推送】何总审批通过', '24', '1', '0', '1491039977');
INSERT INTO `chsoa_notice` VALUES ('107', '1', '1', '你有新的任务【测试移动端推送】', '24', '3', '0', '1491040077');
INSERT INTO `chsoa_notice` VALUES ('108', '1', '1', '【测试移动端推送】何总审批通过', '24', '1', '0', '1491040077');
INSERT INTO `chsoa_notice` VALUES ('109', '1', '1', '【再次测试附件上传】何总审批不通过', '23', '1', '1', '1491040219');
INSERT INTO `chsoa_notice` VALUES ('110', '1', '1', '你有新的任务【费用创建的任务】', '21', '3', '1', '1491040926');
INSERT INTO `chsoa_notice` VALUES ('111', '2', '1', '你有新的任务【费用创建的任务】', '21', '3', '0', '1491040926');
INSERT INTO `chsoa_notice` VALUES ('112', '5', '1', '【费用创建的任务】何总审批通过', '21', '1', '0', '1491040926');
INSERT INTO `chsoa_notice` VALUES ('113', '4', '1', '何总创建任务【测试何总推送】等待审批', '25', '2', '1', '1491040982');
INSERT INTO `chsoa_notice` VALUES ('114', '2', '1', '何总创建任务【测试何总推送】等待审批', '25', '2', '0', '1491040983');
INSERT INTO `chsoa_notice` VALUES ('115', '1', '1', '管理员已领取你的任务【测试移动端推送】', '24', '1', '1', '1491377017');
INSERT INTO `chsoa_notice` VALUES ('116', '5', '1', '管理员已领取你的任务【费用创建的任务】', '21', '1', '0', '1491379643');
INSERT INTO `chsoa_notice` VALUES ('117', '4', '1', '你有新的任务【DSP460修改频谱】', '25', '3', '1', '1491545818');
INSERT INTO `chsoa_notice` VALUES ('118', '4', '1', '【DSP460修改频谱】何总审批通过', '25', '1', '1', '1491545819');
INSERT INTO `chsoa_notice` VALUES ('119', '4', '1', '你有新的任务【DSP460修改频谱】', '25', '3', '1', '1491545954');
INSERT INTO `chsoa_notice` VALUES ('120', '4', '1', '【DSP460修改频谱】何总审批通过', '25', '1', '1', '1491545954');
INSERT INTO `chsoa_notice` VALUES ('121', '4', '1', '你有新的任务【DSP460修改频谱】', '25', '3', '1', '1491546083');
INSERT INTO `chsoa_notice` VALUES ('122', '4', '1', '【DSP460修改频谱】何总审批通过', '25', '1', '1', '1491546083');
INSERT INTO `chsoa_notice` VALUES ('123', '4', '1', '你有新的任务【DSP460修改频谱】', '25', '3', '0', '1491546321');
INSERT INTO `chsoa_notice` VALUES ('124', '4', '1', '【DSP460修改频谱】何总审批通过', '25', '1', '1', '1491546321');
INSERT INTO `chsoa_notice` VALUES ('125', '4', '1', '何总创建任务【测试审批啦】等待审批', '27', '2', '0', '1491546720');
INSERT INTO `chsoa_notice` VALUES ('126', '2', '1', '何总创建任务【测试审批啦】等待审批', '27', '2', '0', '1491546720');
INSERT INTO `chsoa_notice` VALUES ('127', '4', '1', '何总修改了任务【测试审批啦DSP】等待审批', '27', '2', '1', '1491547201');
INSERT INTO `chsoa_notice` VALUES ('128', '2', '1', '何总修改了任务【测试审批啦DSP】等待审批', '27', '2', '0', '1491547201');
INSERT INTO `chsoa_notice` VALUES ('129', '4', '1', '何总修改了任务【红与黑】等待审批', '26', '2', '0', '1491548519');
INSERT INTO `chsoa_notice` VALUES ('130', '2', '1', '何总修改了任务【红与黑】等待审批', '26', '2', '0', '1491548520');
INSERT INTO `chsoa_notice` VALUES ('131', '4', '1', '何总修改了任务【红与黑BDPS】等待审批', '26', '2', '0', '1491548547');
INSERT INTO `chsoa_notice` VALUES ('132', '2', '1', '何总修改了任务【红与黑BDPS】等待审批', '26', '2', '0', '1491548548');
INSERT INTO `chsoa_notice` VALUES ('133', '4', '1', '何总修改了任务【什么东西！！！！】等待审批', '26', '2', '0', '1491548654');
INSERT INTO `chsoa_notice` VALUES ('134', '2', '1', '何总修改了任务【什么东西！！！！】等待审批', '26', '2', '0', '1491548654');
INSERT INTO `chsoa_notice` VALUES ('135', '4', '1', '你有新的任务【测试审批啦DSP】', '27', '3', '0', '1491548793');
INSERT INTO `chsoa_notice` VALUES ('136', '9', '1', '你有新的任务【测试审批啦DSP】', '27', '3', '0', '1491548794');
INSERT INTO `chsoa_notice` VALUES ('137', '4', '1', '【测试审批啦DSP】何总审批通过', '27', '1', '0', '1491548794');
INSERT INTO `chsoa_notice` VALUES ('138', '0', '1', '何总修改了任务【】等待审批', '27', '2', '0', '1491548794');
INSERT INTO `chsoa_notice` VALUES ('139', '4', '1', '你有新的任务【什么东西！！！！】', '26', '3', '0', '1491548892');
INSERT INTO `chsoa_notice` VALUES ('140', '7', '1', '你有新的任务【什么东西！！！！】', '26', '3', '0', '1491548893');
INSERT INTO `chsoa_notice` VALUES ('141', '4', '1', '【什么东西！！！！】何总审批通过', '26', '1', '0', '1491548894');
INSERT INTO `chsoa_notice` VALUES ('142', '0', '1', '何总修改了任务【】等待审批', '26', '2', '0', '1491548894');
INSERT INTO `chsoa_notice` VALUES ('143', '4', '1', '何总已领取你的任务【测试审批啦DSP】', '27', '1', '0', '1491554830');
INSERT INTO `chsoa_notice` VALUES ('144', '4', '1', '何总已领取你的任务【什么东西！！！！】', '26', '1', '0', '1491554843');
INSERT INTO `chsoa_notice` VALUES ('145', '0', '1', '何总已提交【测试审批啦DSP】反馈结果，等待你的测试验证确定', '27', '1', '0', '1491554873');
INSERT INTO `chsoa_notice` VALUES ('146', '4', '1', '何总已提交【测试审批啦DSP】反馈结果，等待你的测试验证确定', '27', '1', '1', '1491554874');
INSERT INTO `chsoa_notice` VALUES ('147', '0', '1', '何总已提交【测试审批啦DSP】反馈结果，等待你的测试验证确定', '27', '1', '0', '1491555393');
INSERT INTO `chsoa_notice` VALUES ('148', '4', '1', '何总已提交【测试审批啦DSP】反馈结果，等待你的测试验证确定', '27', '1', '1', '1491555393');

-- ----------------------------
-- Table structure for `chsoa_picture`
-- ----------------------------
DROP TABLE IF EXISTS `chsoa_picture`;
CREATE TABLE `chsoa_picture` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键id自增',
  `path` varchar(255) NOT NULL DEFAULT '' COMMENT '路径',
  `url` varchar(255) NOT NULL DEFAULT '' COMMENT '图片链接',
  `md5` char(32) NOT NULL DEFAULT '' COMMENT '文件md5',
  `sha1` char(40) NOT NULL DEFAULT '' COMMENT '文件 sha1编码',
  `status` tinyint(2) NOT NULL DEFAULT '0' COMMENT '状态',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of chsoa_picture
-- ----------------------------

-- ----------------------------
-- Table structure for `chsoa_task`
-- ----------------------------
DROP TABLE IF EXISTS `chsoa_task`;
CREATE TABLE `chsoa_task` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL COMMENT '任务名称',
  `type` char(50) NOT NULL DEFAULT '1' COMMENT '类型',
  `level` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '等级',
  `content` text COMMENT '说明',
  `startdt` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '开始时间',
  `enddt` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '截止时间',
  `dist` char(50) NOT NULL COMMENT '任务分配者ID',
  `testman` char(50) DEFAULT NULL COMMENT '任务结果确定人',
  `leaderid` char(50) NOT NULL COMMENT '领导ID，审批者ID',
  `fileman` char(50) DEFAULT NULL COMMENT '归档人',
  `pass_fileman_uid` tinyint(2) unsigned DEFAULT NULL COMMENT '确认归档的人UID',
  `pass_file_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '归档日期',
  `pass_leader_uid` tinyint(2) unsigned DEFAULT NULL COMMENT '审核的领导UID,通过与否都需要记录',
  `reason` text COMMENT '领导审批通过与否原因',
  `pass_time` int(11) unsigned DEFAULT NULL COMMENT '审批时间',
  `file_id` char(50) DEFAULT NULL COMMENT '文件ID',
  `applyid` char(10) NOT NULL DEFAULT '0' COMMENT '申请者ID',
  `status` tinyint(3) NOT NULL DEFAULT '0' COMMENT '状态',
  `t_status` tinyint(3) NOT NULL DEFAULT '0' COMMENT '任务执行状态，总控制字段.1为已审核，-2为审核不通过，5为待归档，6为已归档',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=28 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of chsoa_task
-- ----------------------------
INSERT INTO `chsoa_task` VALUES ('19', '测试任务1111111', '生产,客户', '1', '1111111111111111111', '1488816000', '1490803200', '2,5', '4', '2,4', '4', '4', '1489028914', '4', '正式可以', '1488880360', null, '5', '1', '6');
INSERT INTO `chsoa_task` VALUES ('20', '测试测试用户4人任务', '客户', '3', '测试测试用户4人任务测试测试用户4人任务\r\n测试测试用户4人任务\r\n测试测试用户4人任务', '1488816000', '1490889600', '5,4', '4', '2,4', '4', '4', '1488967849', '4', '不错不错，可以继续', '1488881600', null, '2', '1', '6');
INSERT INTO `chsoa_task` VALUES ('21', '秦总创建的任务DSP680', '自主', '2', '嘿嘿hi额hi恶化\r\n这个是不一般的\r\n赶紧处理', '1491040926', '1491040926', '1,2', '', '4', '4', null, '0', '4', 'bbbbbb', '1491040926', null, '5', '1', '1');
INSERT INTO `chsoa_task` VALUES ('22', 'DSP460-8客户要求增加功能', '客户,开发', '1', 'TESTfffffffff', '1489420800', '1490803200', '4', '', '4,2', '4,2', null, '0', null, null, null, '7', '1', '1', '0');
INSERT INTO `chsoa_task` VALUES ('23', '再次测试附件上传', '开发', '2', '再次测试附件上传\r\n再次测试附件上传\r\n再次测试附件上传', '1491040219', '1491040219', '1', '', '4,2', '4,2', null, '0', '4', '123456', '1491040219', '8', '1', '1', '-2');
INSERT INTO `chsoa_task` VALUES ('24', '测试移动端推送', '生产,客户', '2', '通知通过', '1491040077', '1491040077', '1', '', '4,2', '4,2', null, '0', '4', '123456', '1491040077', null, '1', '1', '1');
INSERT INTO `chsoa_task` VALUES ('25', 'DSP460修改频谱', '生产', '2', '地对地导弹', '1491546321', '1491546321', '4', '5,7', '4,2', '4,2', null, '0', '4', '不会哈哈哈', '1491546321', null, '4', '1', '1');
INSERT INTO `chsoa_task` VALUES ('26', '什么东西！！！！', '客户', '1', '步步紧逼几年级\r\n\r\n22121212121\r\nZNN', '1491548892', '1491548892', '4,7', '6', '4,2', '4,2', null, '0', '4', '132456789', '1491548892', null, '4', '1', '1');
INSERT INTO `chsoa_task` VALUES ('27', '测试审批啦DSP', '生产,开发', '2', '哈哈哈宝贝姐姐好', '1491548793', '1491548793', '4,9', '', '4,2', '4,2', null, '0', '4', '这个就叼爆了！@！', '1491548793', null, '4', '1', '1');

-- ----------------------------
-- Table structure for `chsoa_task_result`
-- ----------------------------
DROP TABLE IF EXISTS `chsoa_task_result`;
CREATE TABLE `chsoa_task_result` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `task_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '任务ID',
  `gettask_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '领取任务日期',
  `uid` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '领取人ID',
  `resu_content` text COMMENT '改进结果',
  `other_content` text COMMENT '其他说明',
  `pcb_update` char(10) DEFAULT NULL COMMENT 'PCB文件是否有更新：',
  `software_update` char(10) DEFAULT NULL COMMENT '程序文件是否有更新：',
  `product_update` char(10) DEFAULT NULL COMMENT '生产文件是否有更新：',
  `resu_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '反馈时间',
  `t_status` tinyint(2) unsigned NOT NULL DEFAULT '2' COMMENT '任务状态,领取任务后就为2,任务反馈为3，完成状态为4',
  PRIMARY KEY (`id`),
  KEY `index_id` (`id`) USING BTREE,
  KEY `index_taskid` (`task_id`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=42 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of chsoa_task_result
-- ----------------------------
INSERT INTO `chsoa_task_result` VALUES ('34', '19', '1488881121', '2', '反馈解决了\r\n已经好了', '圣达菲', '有更新', '有更新', '无更新', '1488937517', '4');
INSERT INTO `chsoa_task_result` VALUES ('35', '20', '1488881811', '4', '测试测试', '1111', '有更新', '有更新', '无更新', '1488959419', '4');
INSERT INTO `chsoa_task_result` VALUES ('36', '20', '1488882164', '5', '做好了，谢谢', '·1111', '有更新', '无更新', '无更新', '1488961618', '4');
INSERT INTO `chsoa_task_result` VALUES ('37', '19', '1488962012', '5', '11111111', '11111111', '无更新', '无更新', '无更新', '1488962036', '4');
INSERT INTO `chsoa_task_result` VALUES ('38', '24', '1491377017', '1', null, null, null, null, null, '0', '2');
INSERT INTO `chsoa_task_result` VALUES ('39', '21', '1491379643', '1', null, null, null, null, null, '0', '2');
INSERT INTO `chsoa_task_result` VALUES ('40', '27', '1491554830', '4', '反馈测试bi\nbihh', '日日日', '有更新', '无更新', '有更新', '1491555393', '3');
INSERT INTO `chsoa_task_result` VALUES ('41', '26', '1491554843', '4', null, null, null, null, null, '0', '2');

-- ----------------------------
-- Table structure for `chsoa_task_test`
-- ----------------------------
DROP TABLE IF EXISTS `chsoa_task_test`;
CREATE TABLE `chsoa_task_test` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `resu_id` int(11) unsigned NOT NULL COMMENT '任务反馈结果ID',
  `testman_uid` int(5) unsigned NOT NULL COMMENT '测试者ID',
  `test_content` text,
  `test_status` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '测试是否通过,0为未通过，1为通过',
  `test_time` int(11) unsigned DEFAULT '0' COMMENT '测试时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of chsoa_task_test
-- ----------------------------
INSERT INTO `chsoa_task_test` VALUES ('2', '34', '2', '好好', '1', '1488945055');
INSERT INTO `chsoa_task_test` VALUES ('3', '35', '4', '非常好，继续加油', '1', '1488960676');
INSERT INTO `chsoa_task_test` VALUES ('4', '36', '5', '好好好好好', '1', '1488961679');
INSERT INTO `chsoa_task_test` VALUES ('5', '37', '5', '匪巢', '1', '1488962122');

-- ----------------------------
-- Table structure for `chsoa_users`
-- ----------------------------
DROP TABLE IF EXISTS `chsoa_users`;
CREATE TABLE `chsoa_users` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(255) NOT NULL COMMENT '用户名',
  `nickname` varchar(255) DEFAULT NULL COMMENT '名字',
  `password` varchar(100) NOT NULL COMMENT '密码',
  `phone` varchar(100) DEFAULT NULL COMMENT '手机',
  `email` varchar(100) DEFAULT NULL COMMENT '邮箱',
  `qq` char(20) NOT NULL COMMENT 'qq',
  `cover` varchar(255) NOT NULL DEFAULT '0' COMMENT '头像',
  `group_id` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '归属部门ID',
  `auth_group_id` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '用户权限组',
  `reg_ip` bigint(20) NOT NULL DEFAULT '0' COMMENT '注册IP',
  `reg_time` int(11) unsigned NOT NULL COMMENT '添加时间',
  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '状态',
  `last_login_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '最后登陆的时间',
  `last_login_ip` bigint(20) NOT NULL DEFAULT '0' COMMENT '最后登陆IP',
  `login` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '登陆次数',
  `leader_id` char(20) NOT NULL DEFAULT '0' COMMENT '领导ID',
  `jobs` char(20) NOT NULL COMMENT '职位',
  `sex` tinyint(2) unsigned NOT NULL DEFAULT '1' COMMENT '性别：1为男，2为女',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=15 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of chsoa_users
-- ----------------------------
INSERT INTO `chsoa_users` VALUES ('1', 'admin', '管理员', '96f7f42423319c2bdc954976e7eb6936', '13726296000', '', '', '/Uploads/cover/148947160050708.jpg', '1', '1', '0', '1467820800', '1', '1491536442', '1032619335', '148', '1', '管理员', '1');
INSERT INTO `chsoa_users` VALUES ('2', 'chs001', '秦总', 'e3ca4a64bed374733f831b154ca7ae2e', '13829708000', '', '', '0', '4', '1', '0', '1970', '1', '1489459551', '1032636088', '66', '', '总经理', '1');
INSERT INTO `chsoa_users` VALUES ('4', 'chs002', '何总', '96f7f42423319c2bdc954976e7eb6936', '13726296000', '', '', '0', '1', '0', '1032619677', '1492704000', '1', '1491555367', '1032619335', '28', '2', '技术总监', '1');
INSERT INTO `chsoa_users` VALUES ('5', 'chs003', '周总', '92c54f96178715f19bd9e21a5613d632', '1372628000', '', '', '0', '5', '0', '3232283848', '2017', '1', '1489031784', '3232283848', '9', '2', '财务主管', '2');
INSERT INTO `chsoa_users` VALUES ('6', 'chs004', '唐工', '92c54f96178715f19bd9e21a5613d632', '13570379000', '', '', '0', '1', '0', '1032636088', '1970', '1', '0', '0', '0', '4', '开发部组长', '1');
INSERT INTO `chsoa_users` VALUES ('7', 'chs005', '王工', '82b998b7fdf04e7122ed9a3a7e0d9e49', '13570379000', '', '', '0', '1', '0', '1032636088', '1489461627', '1', '0', '0', '0', '4', '硬件工程师', '1');
INSERT INTO `chsoa_users` VALUES ('8', 'chs006', '刘工', 'e3ca4a64bed374733f831b154ca7ae2e', '13570379000', '', '', '0', '1', '0', '1032636088', '2017', '1', '0', '0', '0', '4', '硬件工程师', '1');
INSERT INTO `chsoa_users` VALUES ('9', 'chs007', '伍工', 'f9756531791cac6699708d86358d8779', '13545381000', '', '', '0', '1', '0', '1032636088', '1489462887', '1', '0', '0', '0', '4', '工程师', '1');
INSERT INTO `chsoa_users` VALUES ('10', 'chs008', '林工', '7c8eca2022dc00427c03a1204cf2a36e', '13542597000', '', '', '0', '1', '0', '1032636088', '1489463037', '1', '0', '0', '0', '4', '工程师', '1');
INSERT INTO `chsoa_users` VALUES ('11', 'chs009', '黄工', '63b6b61e9f737aeac01492fa412e8fe7', '13548723000', '', '', '0', '1', '0', '1032636088', '1489463699', '1', '0', '0', '0', '4', '软件工程师', '1');
INSERT INTO `chsoa_users` VALUES ('12', 'chs010', '蒙工', '7b66601dfcebc51eaa02fb02f0e4c29c', '13756479000', '', '', '0', '1', '0', '1032636088', '1489463809', '1', '0', '0', '0', '4', '软件工程师', '1');
INSERT INTO `chsoa_users` VALUES ('13', 'chs011', '李工', '60a160f7852345c44583f7a3727ae1bb', '13745789000', '', '', '0', '1', '0', '1032636088', '1489463861', '1', '0', '0', '0', '4', 'UI工程师', '1');
INSERT INTO `chsoa_users` VALUES ('14', 'chs012', '丁工', 'e3ca4a64bed374733f831b154ca7ae2e', '13726268000', '', '', '0', '1', '0', '1032636088', '1970', '1', '0', '0', '0', '4', '软件工程师', '1');
