<?php
/*
*author: 
*date: 
*desc:管理员信息管理
*/
namespace Admin\Controller;
use Think\Controller;
class ManageoperController extends BaseController{
    function _empty() {}

    //管理员列表
    public function managelist(){
        $wxary = array();
        $pagecount = 10;
        $roles = $this->baseAllRole();
        $content = empty(trim(I('request.content'))) ? '' : I('request.content');
        $roleid =  empty(trim(I('request.search_roleid'))) ? '' : I('request.search_roleid');
        $rolehtml = $this->getSelectOption_role($roles,$roleid);
        
        $ma=M('manager_user',$this->tablePrefix);
        $map['status']  = 0;
        if($roleid){
            $map['roleid']  = $roleid;
        }
        if($content){
            $map['username']  = $content;
        }
        $managercount = $ma->where($map)->count();
    
        $count = empty($managercount) ? 0 : $managercount;
        $Page = new \Think\Pagenew($count,$pagecount);
        $managerary = array();
        $returnary = $ma->where($map)->order('id desc')->limit($Page->firstRow,$Page->listRows)->select(); 
        foreach($returnary as $key=>$val){
         $temproleid = $val['roleid'];
         if($val['createtime']!=0 && !empty($val['createtime']))
            $val['createtime'] =   date('Y-m-d H:i:s',$val['createtime']);
         if($val['lastlogintime']!=0 && !empty($val['lastlogintime']))
            $val['lastlogintime'] =  date('Y-m-d H:i:s',$val['lastlogintime']);
         $val['rolename'] = $roles[$temproleid]['role'];
         $managerary[] = $val; 
        }

        $Page->parameter["content"] = urlencode($content);
        $Page->parameter["search_roleid"] = $roleid;

        $pagehtml = $Page->showtwo();
        $this->assign('datalist', $managerary);
        $this->assign('pagehtml',$pagehtml);
        $this->assign('content',$content);
        $this->assign('rolehtml',$rolehtml);
        $this->assign('funcdesc',"管理员列表");
 
        $this->display();
    }
    
    private function getSelectOption_role($roles,$search_roleid=0){
        $option = "<option value=\"0\">"."选择用户类型 "."</option>";
        foreach ($roles as $key=>$value){
            if($search_roleid==$value['id']){
                $option = $option."<option selected=\"selected\" value=\"".$value['id']."\">".$value['role']."</option>";
            }else{
                $option = $option."<option value=\"".$value['id']."\">".$value['role']."</option>";
            }
        }
        return $option;
    }
    
    //删除用户
    public function deletemanage(){
        $manageid = empty(trim(I('request.managerid'))) ? 0 : I('request.managerid');
        $ma=M('manager_user',$this->tablePrefix);
        $data['status'] = 1;
        $re = $ma->where("id=$manageid")->save($data);
        if($re){
            //记录日志
            $current_user = $this->baseLoginuser;
            $operateUserId = $current_user['manager_user_id'];
            $sql = $ma->getLastsql();
            $desc='删除管理员成功！';
            funOperateLog($operateUserId, $sql, $desc); 
           exit( json_encode(array('err'=>0,'msg'=>'删除成功！')));
        }else{
           exit( json_encode(array('err'=>1,'msg'=>'删除失败！')));
        }
    }
    
    //添加用户
    public function addmanage(){
        $sysUserId=$_GET['id'];
        $this->assign('funcdesc',"添加管理员"); 
        if($sysUserId){
            //修改用户
            $ma=M('manager_user',$this->tablePrefix);
            $map['id'] = "$sysUserId";
            $sysUser = $ma->where($map)->find();
            if(count($sysUser)>0){
                $search_roleid=$sysUser['roleid'];
                $this->assign('username',$sysUser['username']);
                $this->assign('truename',$sysUser['truename']);
                $this->assign('sysUserId',$sysUserId);
                $this->assign('funcdesc',"修改管理员信息"); 
            }
        }
       $roles = $this->baseAllRole();
       $rolehtml = $this->getSelectOption_role($roles,$search_roleid);
       $this->assign('option_role',$rolehtml); 
       $this->display();
    }
    
    //添加用户
    public function addsavemanage(){
        $current_user = $this->baseLoginuser;
        $operateUserId = $current_user['manager_user_id'];

        $username = I('post.username');
        $truename = I('post.truename');
        $pass = I('post.pass');
        $roleid = I('post.sys_user_roleid');
        $sysUserId = I('post.sysUserId');

        $sysUser = array();
        $sysUser['username']=$username;
        $sysUser['truename']=$truename;
        $sysUser['password']=$pass;
        $sysUser['roleid']=$roleid;
        $sysUser['id']=$sysUserId;
        if(empty($username) || empty($truename) || empty($pass) || empty($roleid)){
            exit( json_encode(array('err'=>1,'msg'=>'请把信息填写完整！')));
        }
        
        $isExistSysUser = $this->isExistSysUser($sysUserId,$username);
        if($isExistSysUser){
            exit( json_encode(array('err'=>1,'msg'=>'此用户名已存在！')));
        }else{
            if($sysUserId){
                //更新用户
                $return = $this->updateSystemUser($operateUserId,$sysUser);
                if($return){
                    exit( json_encode(array('err'=>0,'msg'=>'修改用户信息成功！')));
                }else{
                    exit( json_encode(array('err'=>1,'msg'=>'修改用户信息失败！')));
                }
            }else{
                //创建用户
                $return = $this->createSystemUser($operateUserId,$sysUser);
                if($return){
                    exit( json_encode(array('err'=>0,'msg'=>'用户创建成功！')));
                }else{
                    exit( json_encode(array('err'=>1,'msg'=>'用户创建失败！')));
                }
            }}
        }
    
    //检查用户是否存在
    private function isExistSysUser($sysUserId,$username){
        $ma=M('manager_user',$this->tablePrefix);
        $map['username'] = "$username";
        $user = $ma->where($map)->find();
        if(count($user)>0){
            if($user['id']==$sysUserId){
                return false;
            }else{
                return true;
            }
        }else{
            return false;
        }
    }
    
    //创建系统用户
    private function createSystemUser($operateUserId,$systemUser){
        $salt = rand(1000, 9999);
        $md5_pass = md5($systemUser['password'].$salt);

        $ma=M('manager_user',$this->tablePrefix);
        $data['username'] = $systemUser['username'];
        $data['truename'] = $systemUser['truename'];
        $data['password'] = $md5_pass;
        $data['salt'] = $salt;
        $data['roleid'] = $systemUser['roleid'];  
        $data['createtime'] = time();  
        $re = $ma->add($data);
        if($re){
            $desc = "创建管理员用户";
            $sql = $ma->getLastSql();
            funOperateLog($operateUserId, $sql, $desc);
            return true;
        }else{
            return false;
        }
    }

    //修改系统用户
    private function updateSystemUser($operateUserId,$systemUser){
        $salt = rand(1000, 9999);
        $md5_pass = md5($systemUser['password'].$salt);

        $ma=M('manager_user',$this->tablePrefix);
        $data['username'] = $systemUser['username'];
        $data['truename'] = $systemUser['truename'];
        $data['password'] = $md5_pass;
        $data['salt'] = $salt;
        $data['roleid'] = $systemUser['roleid'];
        $id = $systemUser['id']; 
        $re = $ma->where("id=$id")->save($data);
        if($re){
            $desc = "修改管理员用户";
            $sql = $ma->getLastSql();
            funOperateLog($operateUserId, $sql, $desc);
            return true;
        }else{
            return false;
        }
    } 
    
    //修改密码
    public function updatePassword(){
       $this->assign('funcdesc',"修改用户密码"); 
       $this->display(); 
    }
    
    //修改密码
    public function updatesavePass(){
        $current_user = $this->baseLoginuser;
        $operateUserId = $current_user['manager_user_id'];

        $oldpassword = I('post.oldpassword');
        $newpassword = I('post.newpassword');
        $conform_pass = I('post.conform_pass');

        $salt = rand(1000, 9999);
        $md5_pass = md5($newpassword.$salt);
        $sysUser = array();
        $sysUser['password']=$md5_pass;
        $sysUser['salt']=$salt;
        
        if(empty($oldpassword) || empty($newpassword) || empty($conform_pass)){
            exit( json_encode(array('err'=>1,'msg'=>'请把信息填写完整！')));
        }
        
        if($newpassword!=$conform_pass){
            exit( json_encode(array('err'=>1,'msg'=>'新密码和验证密码不一致！')));
        }
        
        $ma=M('manager_user',$this->tablePrefix);
        $map['id'] = "$operateUserId";
        $user = $ma->where($map)->find();
        if($user){
           $tempsalt = $user['salt'];
           $temppassword = $user['password'];
           $tempoldpassword  = md5($oldpassword.$tempsalt);
           if($tempoldpassword == $temppassword){
                $re = $ma->where($map)->save($sysUser);
                if($re){
                    exit( json_encode(array('err'=>0,'msg'=>'修改密码成功！')));
                }else{
                    exit( json_encode(array('err'=>1,'msg'=>'修改密码失败！')));
                }
           }else{
              exit( json_encode(array('err'=>1,'msg'=>'原始密码不正确！')));
           }
        }else{
           exit( json_encode(array('err'=>1,'msg'=>'用户不存在！')));
        }
    }   
}