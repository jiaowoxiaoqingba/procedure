<?php
/*
*author:zxw 
*date: 2015-8-28
*desc:用户登录
*/
namespace Admin\Controller;
use Think\Controller;
class ManageController extends BaseController{
    function _empty() {}

    public function login() {
        $this->display();
    }

    public function doLogin() {
        $authcode = trim(I('post.verify'));
        $password = trim(I('post.password'));
        $uname = trim(I('post.uname'));
        
        $code=$_COOKIE['wadmincode'];
        $authcode=strtolower($authcode); $code=strtolower($code);
        $ifyanmatrue= $this->yanzhengma_verification($code,$authcode);
        if(empty($authcode)){
           $this->error("验证码不正确！");
        }
        if(!$ifyanmatrue){
            $this->error("验证码不正确！");
        }
        // 数据检查
        if (empty($password)) {
            $this->error('密码不能为空');
        }

        if (empty($uname)) {
            $this->error('账号错误');
        }else{
            $uname = strip_tags($uname);
            $uname = addslashes($uname);
        }

        $user = array();
        $condition = "status=0 and username='".$uname."'";
        $ma=M('manager_user',$this->tablePrefix);
        $user = $ma->where($condition)->find();
        $upwd = md5($password.$user['salt']);
        if ((!empty($user)) && ($user['password'] == $upwd)) {
                $ip = get_client_ip();
                //修改 最后登录时间
                $this->updateManagerUserLogin($user['id']);
                $user['logintime'] = time();

                $mecAry['manager_user_id']=$user['id'];
                $mecAry['manager_user']=$user;
                //echo $_SESSION['manager_user_id'];
                //用户登录后,应取得该用户对应的所能访问url,放入session
                $allUrls = $this->getAllVisitUrls($user['id'],$user['roleid']);
                $mecAry['wrw_all_can_visit_urls']=$allUrls;
                
                $keyary = $this->baseMemcName('login',$this->adminPhpSessid);
                $key = $keyary[0];
                $keytime = $keyary[1];
                S($key,$mecAry,$keytime);
                redirect(U('admin/index/index'));
        } else {
            $this->error("登录失败，用户名或密码错误！");
        }
    }
    
    //修改最后登录时间
    private function updateManagerUserLogin($loginUserId){
        $data['lastlogintime'] = time();
        $ma=M('manager_user',$this->tablePrefix);
        $reval = $ma->where("id=$loginUserId")->save($data);
        //$reval==1 成功
    }
    
    //获取权限列表
    private function getAllVisitUrls($loginUserId,$roleid){
        $ma=M('manager_role',$this->tablePrefix);
        $rolelist = $ma->field('funcids')->where("id=$roleid")->find();
        
        if(empty($rolelist))
            return null;
        
        $return = array();
        if($funcids = $rolelist['funcids'])
        {
            $ma=M('manager_sysfunc',$this->tablePrefix);
            $map['id']  = array('in',"$funcids");
            $list = $ma->where($map)->select();
            foreach ($list as $key=>$value){
                $return[$key] = $value['func'];
            }                                                          
        }
        
        return $return;
    }
    
    //判断验证码
    private function yanzhengma_verification($codeyama,$loginyama){
        $keyyama = md5("wadmincode".$codeyama);
         if(empty($loginyama) || empty($codeyama)){
            return false;
         }
         if(md5(strtolower(S($keyyama))) == md5(strtolower($loginyama))){
             return true;
         }else{
            return false;
         }
         S($keyyama,null);
   }

    public function loginout() {
        $keyary = $this->baseMemcName('login',$this->adminPhpSessid);
        $key = $keyary[0];
        S($key,null);
        redirect(U('admin/manage/login'));
    }

    public function get_authcode() {
        //import('vendor.Authcode.lcode.wadmincode');
        $img=new \Vendor\Authcode\lcode\Wadmincode(4,80,35,true,'code',0);
        $img->show();
    }

}
