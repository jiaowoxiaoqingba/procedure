<?php
namespace Admin\Controller;
use Think\Controller;
class RoleController extends BaseController{
    function _empty() {}
    
    //角色管理
    public function rolelist(){
        //echo "角色管理：角色功能修改、添加新角色";
    	 $role = I('role');
         $map['state'] = 0;
    	 if($role){
    		$map['role'] = array("LIKE","%{$role}%");
    	 }
    	 $roles = M("manager_role",$this->tablePrefix);
    	 $rolescount = $roles -> where($map) -> count();
    	 $count = empty($rolescount) ? 0 : $rolescount;
    	 $Page = new \Think\Pagenew($count,10);
    	 $data = $roles -> where($map) -> limit($Page->firstRow.",".$Page->listRows) -> select();
    	 $Page->parameter["role"] = urlencode($role);
	     $pagehtml = $Page->showtwo();
	     $this -> assign('roles',$data);
	     $this -> assign('role',$role);
	     $this -> assign('pagehtml',$pagehtml);
	     $this -> assign('funcdesc',"角色管理列表");
	     $this -> display();
    }

    //添加新角色
    public function roleadd(){
    		$sysfunc = M("manager_sysfunc",$this->tablePrefix);
    		$sysdata = $sysfunc -> select();
    		$this -> assign('sysfuncs',$sysdata);
    		$this -> assign('funcdesc','添加新角色');
    		$this -> display();
    }

    //执行新角色的添加
    public function roleinsert(){
    		$role = I('role');
    		$funcids = I('funcids');
    		$ids = '';
    		foreach($funcids as $vo){
    			$ids .= $vo.",";
    		}
    		$ids = trim($ids,",");

    		$data['funcids'] = $ids;
    		$data['role'] = $role;
    		$roles = M('manager_role',$this->tablePrefix);
    		//记录日志
    		$current_user = $this -> baseLoginuser;
    		$operateUserId = $current_user['manager_user_id'];

    		$re = $roles -> add($data);
    		if($re){
    			$desc = "添加新角色";
    			$sql = $roles -> getLastSql();
			    funOperateLog($operateUserId, $sql, $desc);
    			redirect(U('admin/role/rolelist'));
    		}else{
    			redirect(U('admin/role/roleadd'));
    		}
    }

     //修改角色的页面
    public function rolemod(){
    		$id = I('id');
    		$roles = M("manager_role",$this->tablePrefix);
    		$sysfunc = M("manager_sysfunc",$this->tablePrefix);
    		$rolesdata = $roles -> where("id='{$id}'") -> find();
            $rolesdata['funcids'] = explode(",",$rolesdata['funcids']);
    		$sysdata = $sysfunc -> select();
    		$this -> assign('sysfuncs',$sysdata);
    		$this -> assign("roles",$rolesdata);
    		$this -> assign("funcdesc","修改角色");
    		$this -> display();
    }

    //执行修改角色
    public function roleupdate(){
    		$id = I('id');
    		$role = I('role');
    		$funcids = I('funcids');
    		$ids = '';
    		foreach($funcids as $vo){
    			$ids .= $vo.",";
    		}
    		$ids = trim($ids,",");

    		$data['funcids'] = $ids;
    		$data['role'] = $role;
    		$roles = M('manager_role',$this->tablePrefix);
    		//记录日志
    		$current_user = $this -> baseLoginuser;
    		$operateUserId = $current_user['manager_user_id'];

    		$res = $roles -> where("id='{$id}'") -> save($data);
    		if($res){
    			$desc = "修改用户角色";
    			$sql = $roles -> getLastSql();
			    funOperateLog($operateUserId, $sql, $desc);
    			redirect(U('admin/role/rolelist'));
    		}else{
    			redirect(U('admin/role/rolemod'));
    		}
    }

    //点击删除，将该id的字段改变，隐藏
    public function roledel(){
        $id = I('id');
        $data['state'] = 1;
        $roles = M("manager_role",$this->tablePrefix);
        $res = $roles -> where("id='{$id}'") -> save($data);
        //记录日志
        $current_user = $this -> baseLoginuser;
        $operateUserId = $current_user['manager_user_id'];

        if($res){
            $desc = "删除用户角色";
            $sql = $roles->getLastSql();
            funOperateLog($operateUserId, $sql, $desc);
            exit( json_encode(array('err'=>0,'msg'=>'删除成功！')));
        }else{
            exit( json_encode(array('err'=>1,'msg'=>'删除失败！')));
        }
    }
    
    //功能管理
    public function funclist(){
        //echo "功能管理：功能信息修改、添加新功能";
        $sign = I('sign');
		$description = I('description');
        $map['state'] = 0;
		if($sign != ''){
			$map['sign'] = $sign;
		}
		if($description){
			$map['descript'] = $description;
		}
		$sysfunc = M("manager_sysfunc",$this->tablePrefix);
		$sysfunccount = $sysfunc -> where($map) -> count();
		$count = empty($sysfunccount) ? 0 : $sysfunccount;
       	$Page = new \Think\Pagenew($count,10);
       	$data = $sysfunc -> where($map) -> limit($Page->firstRow.",".$Page->listRows) -> select();
       	$Page->parameter["sign"] = urlencode($sign);
       	$Page->parameter["description"] = urlencode($description);
	     $pagehtml = $Page->showtwo();
	     $this -> assign('sysfuncs',$data);
	     $this -> assign('sign',$sign);
	     $this -> assign('description',$description);
	     $this -> assign('pagehtml',$pagehtml);
	     $this -> assign('funcdesc',"功能信息列表");
	     $this -> display();
    }

    //功能添加
    public function funcadd(){
    		$id = empty(I('id')) ? 0 : I('id');
    		$this -> assign('sign',1);
    		$this -> assign('pid',$id);
    		$this -> assign('funcdesc','添加功能');
    		$this -> display();
    }

    //执行功能添加
    public function funcinsert(){
    	$pid = I('pid');
    	$sign = I('sign');
    	$func = I('func');
    	$descript = I('descript');
    	$data['pid'] = $pid;
    	$data['sign'] = $sign;
    	$data['func'] = $func;
    	$data['descript'] = $descript;
    	$sysfunc = M('manager_sysfunc',$this->tablePrefix);
    	//记录日志
    	$current_user = $this -> baseLoginuser;
    	$operateUserId = $current_user['manager_user_id'];

    	$re = $sysfunc -> add($data);
    	if($re){
    		$desc = "添加后台功能模块";
    		$sql = $sysfunc -> getLastSql();
			funOperateLog($operateUserId, $sql, $desc);
    		redirect(U('admin/role/funclist'));
    	}else{
    		redirect(U('admin/role/funcadd'));
    	}
    }

    //修改功能的页面
    public function funcmod(){
    		$id = I('id');
    		$sysfunc = M("manager_sysfunc",$this->tablePrefix);
    		$sysdata = $sysfunc -> where("id='{$id}'") -> find();
    		$this -> assign("sysdata",$sysdata);
    		$this -> assign("funcdesc","修改功能");
    		$this -> display();
    }

    //执行修改功能
    public function funcupdate(){

    		$id = I('id');
    		$func = I('func');
    		$descript = I('descript');
    		$data['func'] = $func;
    		$data['descript'] = $descript;
    		$sysfunc = M('manager_sysfunc',$this->tablePrefix);
    		//记录日志
    		$current_user = $this -> baseLoginuser;
    		$operateUserId = $current_user['manager_user_id'];

    		$res = $sysfunc -> where("id='{$id}'") -> save($data);
    		if($res){
    			$desc = "修改后台功能模块";
    			$sql = $sysfunc -> getLastSql();
			    funOperateLog($operateUserId, $sql, $desc);
    			redirect(U('admin/role/funclist'));
    		}else{
    			redirect(U('admin/role/funcmod'));
    		}
    }

    //点击删除，将该id的字段改变，隐藏
    public function funcdel(){
        $id = I('id');
        $data['state'] = 1;
        $sysfunc = M("manager_sysfunc",$this->tablePrefix);
        $res = $sysfunc -> where("id='{$id}'") -> save($data);
        //记录日志
        $current_user = $this -> baseLoginuser;
        $operateUserId = $current_user['manager_user_id'];

        if($res){
                $desc = "删除后台功能模块";
                $sql = $sysfunc -> getLastSql();
                funOperateLog($operateUserId, $sql, $desc);
                exit( json_encode(array('err'=>0,'msg'=>'删除成功！')));
        }else{
                exit( json_encode(array('err'=>1,'msg'=>'删除失败！')));
        }
    }

}