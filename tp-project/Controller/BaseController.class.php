<?php
/*
*author:zxw 
*date: 2015-8-11
*desc:从数据库中获取基础信息
*/
namespace Admin\Controller;
use Think\Controller;
class BaseController extends Controller{
    public $tablePrefix = "";
    public $adminPhpSessid = "";
    public $baseLoginuser = "";
    
    public function _initialize() {
        //设置cookie中存储sessid，多服务器时不用session共享
        if(empty($_COOKIE['ADMINPHPSESSID'])){
           cookie("ADMINPHPSESSID",session_id());
           $this->adminPhpSessid = session_id();
        }else{
           $this->adminPhpSessid = $_COOKIE['ADMINPHPSESSID'];
        }
        //表前缀
        $this->tablePrefix = C('DB_OTHERPREFIX'); 
        //判断用户是否登录以及用户权限
        $current_operUrl=$_SERVER['PHP_SELF'];
        $this->baseLoginuser = $this->baseManagerInfo();
        if($current_operUrl!='/index.php/admin/manage/login.html' && $current_operUrl!='/index.php/admin/manage/loginout.html' && $current_operUrl!='/index.php/admin/manage/get_authcode.html' && $current_operUrl!='/index.php/admin/manage/dologin.html'){
            if(!$this->baseLoginuser){
               redirect(U('admin/manage/login'));
            }else{
                $auth = $this->baseLoginuser['wrw_all_can_visit_urls'];
                if(!empty($auth)){
                    if($current_operUrl!='/index.php/admin/index/index.html'){
                        if(!$this->baseCanVisit($current_operUrl,$auth)){
                            echo "对不起你没有权限";
                            exit;
                        }
                    }
                }
            }
        }
    }
    
    //获取后台的memcache的名称
    public function baseMemcName($param=0,$paramone=0){
        $reAry = array();
        $ownPrefix =  C('ADMIN_MEMC_PREFIX');
        $ip = get_client_ip();
        $memcName = $ownPrefix;
        $memcName = md5($memcName); 
        $memcTime = 10800;
        
        switch ($param){
            case 'login':  //用户登陆次数
              $memcName = $ownPrefix.$ip.$paramone.'_login';
              $memcName = md5($memcName);
              $reAry = array($memcName,$memcTime);
              break;
            default:
              $reAry = array($memcName,$memcTime);  
        }
        return $reAry;
    }
    
    private function baseManagerInfo() {
        $adminuser = "";
        $keyary = $this->baseMemcName('login',$this->adminPhpSessid);
        $key = $keyary[0];
        $adminuser = S($key);
        return $adminuser;
    }
    
    //判断用户权限
    private function baseCanVisit($url,$urls){
        $url = str_replace('/index.php',"",$url);
        return in_array($url, $urls);
    }
    
    //取得所有角色
    public function baseAllRole(){
        $rolelist = array();
        $ma=M('manager_role',$this->tablePrefix);
        $list = $ma->where('1=1')->select();
        foreach($list as $key=>$val){
          $id = $val['id'];
          $rolelist[$id] = $val; 
        }
        return $rolelist;        
    }
    
    //记录管理员的操作日志
    public function baselog($userId,$sql,$desc){
        if($userId || $sql || $desc){
            $ma=M('manager_operate_log',$this->tablePrefix);
            $data['userid'] = $userId;
            $data['sqlstr'] = "$sql";
            $data['description'] = "$desc";
            $data['operate_time'] = time();
            $ma->add($data);
        }
    }
    
    public function _empty(){
        redirect(U('admin/index/index'));
    }

}
?>