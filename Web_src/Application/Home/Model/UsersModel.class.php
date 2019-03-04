<?php
namespace Home\Model;
use Think\Model;

class UsersModel extends Model{

    /* 用户模型自动验证 */
    protected $_validate = array(
        array('nickname', '1,30', '姓名不能为空！', self::EXISTS_VALIDATE, 'length'), //姓名必填
        /* 验证用户名 */
        array('username', '1,30', '用户名长度必须在16个字符以内！', self::EXISTS_VALIDATE, 'length'), //用户名长度不合法
        array('username', 'checkDenyMember', '用户名被禁止注册！', self::EXISTS_VALIDATE, 'callback'), //用户名禁止注册
        array('username', 'checkuser', '用户名被占用！', self::EXISTS_VALIDATE, 'unique'), //用户名被占用

        array('group_id', 'require', '请选择部门！'), //选择部门
        /* 验证密码 */
        array('username', '6,100', '密码长度必须在6-30个字符之间！', self::EXISTS_VALIDATE, 'length'), //密码长度不合法

        array('jobs', 'require', '职位不能为空！'), //职位必填
        // array('leader_id', 'require', '请选择上级主管！'), //上级主管必填
        array('reg_time', 'require', '请选择入职时间！'), //入职时间必填      

        /* 验证手机号码 */
        array('phone', 'require', '手机不能为空！'), //手机必填
        array('phone','/^1[3|4|5|8][0-9]\d{4,8}$/','手机号码错误！','0','regex',1),
        array('phone', 'checkphone', '手机号被占用', self::EXISTS_VALIDATE, 'unique'), //手机号被占用
    );

    /* 用户模型自动完成 */
    protected $_auto = array(
        array('status', 1, self::MODEL_INSERT, 'string'),
        // array('password', 'chs_ucenter_md5', self::MODEL_BOTH, 'function', UC_AUTH_KEY),
        array('reg_time', 'jobs_regtime', self::MODEL_BOTH, 'callback'),
        array('reg_ip', 'get_client_ip', self::MODEL_BOTH, 'function', 1),
    );

    /**
     * 检测用户名是不是被禁止注册
     * @param  string $username 用户名
     * @return boolean          ture - 未禁用，false - 禁止注册
     */
    protected function checkDenyMember($username){
        return true; //TODO: 暂不限制，下一个版本完善
    }

    // 检查用户名是否重复
    protected function checkuser($username){
        $user_name=$this->where('username='.$username)->find();
        if($user_name){
            return false;
        }else{
            return true;
        }
    }
    // 检查手机是否重复
    protected function checkphone($phone){
        $user_phone=$this->where('phone='.$phone)->find();
        if($user_phone){
            return false;
        }else{
            return true;
        }
    }
    //时间截转换
    protected function jobs_regtime($reg_time){
        return strtotime($reg_time);
    }

    /**
     * 检测邮箱是不是被禁止注册
     * @param  string $email 邮箱
     * @return boolean       ture - 未禁用，false - 禁止注册
     */
    protected function checkDenyEmail($email){
        return true; //TODO: 暂不限制，下一个版本完善
    }

    /**
     * 检测手机是不是被禁止注册
     * @param  string $mobile 手机
     * @return boolean        ture - 未禁用，false - 禁止注册
     */
    protected function checkDenyMobile($mobile){
        return true; //TODO: 暂不限制，下一个版本完善
    }


    /**
     * 用户登录
     * @param  string $username 用户名
     * @param  string $password 密码
     * @param  string $remember 是否长期登陆   remember-me
     * @return boolean      ture-登录成功，false-登录失败
     */
    public function login($username,$password,$remember,$app){
        //生成认证条件
        $map  = array();
        // 支持使用绑定帐号登录
        if(strripos($username, '@') === false){
            if(is_numeric($username)){
                $map['phone'] = $username;
            }else{
                $map['username'] = $username;
            }               
        }else{
            $map['email'] = $username;
        }
        $map['status'] = 1;
        $user = $this->where($map)->find();

        //使用用户名、密码和状态的方式进行认证
        if(empty($user)) {
            return -1;
        }else {
            $token=chs_ucenter_md5($password, UC_AUTH_KEY);
            if( $token!== $user['password']){
                return -2;
            }
            
            // 开始cookie保存
            if($remember=='remember-me'){
                $save_time=time()+3600*24*365;
            }else{
                $save_time=time()+3600;
            }                               
            $cookiecontent=$token.':'.$user['id'];
            $encrypwd=think_encrypt($cookiecontent,'chs_oa');
            setcookie('chs_auth_oa',$encrypwd,$save_time);

        // 缓存
        S('chs_auth_'.$user['id'],$encrypwd);

        /* 登录用户 */
        $this->autoLogin($user,$app);

        //记录行为
        // action_log('user_login', 'users', $uid, $uid);

        return $encrypwd;
    }
}

    //获取用户列表
    public function lists($where='',$order='id asc',$field=true){
        return $this->where($where)->order($order)->field($field)->select();
    }

	
    /**
     * 获取用户信息
     * @param  string  $uid         用户ID或用户名
     * @param  boolean $is_username 是否使用用户名查询
     * @return array                用户信息
     */
    public function info($uid, $is_username = false){
        $map = array();
        if($is_username){ //通过用户名获取
            $map['username'] = $uid;
        } else {
            $map['id'] = $uid;
        }

        $user = $this->where($map)->find();
        if(is_array($user) && $user['status'] == 1){
            return $user;
        } else {
            return -1; //用户不存在或被禁用
        }
    }

    /**
     * 关闭浏览器，再此打开浏览器自动登录
     * @param  integer $user 用户信息数组
     */
    public function Login_User_Auto($user){
        /* 登录用户 */
        $this->autoLogin($user);
    }



    /**
     * 自动登录用户
     * @param  integer $user 用户信息数组
     */
    protected function autoLogin($user,$app){
        /* 更新登录信息 */
        $data = array(
            'id'             => $user['id'],
            'login'           => array('exp', '`login`+1'),
            'last_login_time' => NOW_TIME,
            'last_login_ip'   => get_client_ip(1)
        );
        if($app==1){
            $data['login_type']=2;
        }        
        $this->save($data);

        /* 记录登录SESSION和COOKIES */        
        $auth = array(
            'uid'             => $user['id'],
            'username'        => $user['username'],
            'last_login_time' => $user['last_login_time']
        );

        session('user_auth', $auth);
        session('user_auth_sign', data_auth_sign($auth));

    }

    /**
     * 更新用户登录信息
     * @param  integer $uid 用户ID
     */
    protected function updateLogin($uid){
        $data = array(
            'id'              => $uid,
            'last_login_time' => NOW_TIME,
            'last_login_ip'   => get_client_ip(1)
        );
        $this->save($data);
    }

    /**
     * 注销当前用户所有SESSION和COOKIE
     * @return void
     */
    public function logout(){
        session('user_auth', null);
        session('user_auth_sign', null);
        Cookie(null,'refresh_'); //  清空指定前缀的所有cookie值
        Cookie(null,'chs_'); //  清空指定前缀的所有cookie值
    }

    public function setSnull(){
        S('sys_active_user_list', null);
        S('sys_user_nickname_list', null);
    }



}
