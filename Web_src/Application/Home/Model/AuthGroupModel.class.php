<?php
namespace Home\Model;
use Think\Model;

class AuthGroupModel extends Model{
    const TYPE_ADMIN                = 1;                   // 管理员用户组类型标识
    const USERS                     = 'users';
    const AUTH_GROUP_ACCESS         = 'auth_group_access'; // 关系表表名
    const AUTH_GROUP                = 'auth_group';        // 用户组表名
    const AUTH_EXTEND_CATEGORY_TYPE = 1;              // 分类权限标识
    const AUTH_EXTEND_MODEL_TYPE    = 2; //分类权限标识
    /* 模型自动验证 */
    protected $_validate = array(
        array('title','require','权限组名称必须！'),
    );

    /* 模型自动完成 */
    protected $_auto = array(
        array('module', 'home', self::MODEL_INSERT, 'string'),
        array('type', 1, self::MODEL_INSERT, 'string'),
    );
    
    //获取列表
    public function lists($where='',$field=true)
    {
    	return $this->where($where)->field($field)->select();
    }
    // 获取详情
    public function info($id){
    	return $this->where('id='.$id)->find();
    }

        /**
     * 获取某个用户组的用户列表
     *
     * @param int $group_id   用户组id
     *
     */
    static public function memberInGroup($group_id){
        $prefix   = C('DB_PREFIX');
        $l_table  = $prefix.self::USERS;
        $r_table  = $prefix.self::AUTH_GROUP_ACCESS;
        $list     = M() ->field('m.id,u.username,m.last_login_time,m.last_login_ip,m.status')
                       ->table($l_table.' m')
                       ->join($r_table.' a ON m.id=a.uid')
                       ->where(array('a.group_id'=>$group_id))
                       ->select();
        return $list;
    }

    /**
     * 检查id是否全部存在
     * @param array|string $gid  用户组id列表
     */
    public function checkId($modelname,$mid,$msg = '以下id不存在:'){
        if(is_array($mid)){
            $count = count($mid);
            $ids   = implode(',',$mid);
        }else{
            $mid   = explode(',',$mid);
            $count = count($mid);
            $ids   = $mid;
        }

        $s = M($modelname)->where(array('id'=>array('IN',$ids)))->getField('id',true);
        if(count($s)===$count){
            return true;
        }else{
            $diff = implode(',',array_diff($mid,$s));
            $this->error = $msg.$diff;
            return false;
        }
    }

    /**
     * 检查用户组是否全部存在
     * @param array|string $gid  用户组id列表
     */
    public function checkGroupId($gid){
        return $this->checkId('AuthGroup',$gid, '以下用户组id不存在:');
    }

    /**
     * 把用户添加到用户组,支持批量添加用户到用户组
     *
     * 示例: 把uid='1,2'的用户添加到group_id为1的组 `AuthGroupModel->addToGroup('1,2',1);`
     */
    public function addToGroup($uid,$gid){
        $uid = is_array($uid)?implode(',',$uid):trim($uid,',');
        $uid_arr = explode(',',$uid);
        $uid_arr = array_diff($uid_arr,array(C('USER_ADMINISTRATOR')));
        $Access = M(self::AUTH_GROUP_ACCESS);
        $add = array();
        // 检查是否重复
        if($uid_arr){
            foreach ($uid_arr as $u) {
                $iexit=$Access->where('uid='.$u.' and group_id='.$gid)->find();
                if($iexit){
                    return $u;
                }else{
                    if( is_numeric($u) && is_numeric($gid) ){
                        $add[] = array('group_id'=>$gid,'uid'=>$u);
                    }
                }
            }

            if(!empty($add)){
                $Access->addAll($add);        
                return true;
            }else{
                return false;
            }            
        }else{
            return false;
        }
    }

    /**
     * 将用户从用户组中移除
     * @param int|string|array $gid   用户组id
     * @param int|string|array $cid   分类id
     */
    public function removeFromGroup($uid,$gid){
        return M(self::AUTH_GROUP_ACCESS)->where( array( 'uid'=>$uid,'group_id'=>$gid) )->delete();
    }

}