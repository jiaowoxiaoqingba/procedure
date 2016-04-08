<?php
namespace Admin\Controller;
use Think\Controller;

class NoticeController extends BaseController{

	//公告列表
	public function noticelist(){

		$type = I('type');
		$acceptuser_one = I('acceptuser_one');
		$acceptuser_two = I('acceptuser_two');

		$user = M('user');  //实例化广告主表
		$mediauser = M('user',$this->tablePrefix);  //实例化自媒体主表
		$notice = M("notice");
		$map['status'] = 1;
		if($type) {
			$map['type'] = $type;

			if($type == 1 && $acceptuser) {
				unset($map['acceptuser']);
				$acceptuser = $acceptuser_one;
				$map['acceptuser'] = $acceptuser;
			}elseif($type == 2) {
				unset($map['acceptuser']);
				$acceptuser = $acceptuser_two;
				$username['username'] = array("LIKE","%{$acceptuser}%");
				$user_info = $user -> field("id") -> where($username) -> select();
				if($user_info) {
					$userinfo = array();
					foreach ($user_info as $val) {
						$userinfo[] = $val['id'];
					}
					$map['acceptuser'] = array("IN",$userinfo);
				}else{
					$map['acceptuser'] = 0;
				}
			}else{
				unset($map['acceptuser']);
				$acceptuser = $acceptuser_two;
				$names['username'] = array("LIKE","%{$acceptuser}%");
				$media_name = $mediauser -> field("id") -> where($names) -> select();
				if($media_name) {
					$name_info = array();
					foreach ($media_name as $v) {
						$name_info[] = $v['id'];
					}
					$map['acceptuser'] = array("IN",$name_info);
				}else{
					$map['acceptuser'] = 0;
				}
			}
		}else {
			unset($map['acceptuser']);
		}

       	$noticescount = $notice -> where($map) -> count();
       	$count = empty($noticescount) ? 0 : $noticescount;
       	$Page = new \Think\Pagenew($count,15);

       	$data = $notice -> field("id,type,acceptuser,noticename,noticenote,createtime,managerid,status") -> where($map) -> limit($Page->firstRow.",".$Page->listRows) -> select();

       	$manager=M('manager_user',$this->tablePrefix);
       	foreach($data as &$d){
       		$info = $manager -> field("username")-> where("id='{$d['managerid']}'") ->find();
       		$d['user_name'] = $info['username'];
       		$d['createtime'] = date("Y-m-d H:i:s",$d['createtime']);
       		if($d['type'] == '2'){
       			$users_info = $user -> field('username') -> where("id='{$d['acceptuser']}'") -> find();
       			$d['username'] = $users_info['username'];
       		}else{
       			$mediausers_info = $mediauser -> field('username') -> where("id='{$d['acceptuser']}'") -> find();
       			$d['username'] = $mediausers_info['name'];
       		}
       	}

       	$Page->parameter["type"] = urlencode($type);
       	$Page->parameter["acceptuser"] = urlencode($acceptuser);
       	$Page->parameter["acceptuser_one"] = urlencode($acceptuser_one);
       	$Page->parameter["acceptuser_two"] = urlencode($acceptuser_two);
	     $pagehtml = $Page->showtwo();
	     $typelist = array(1=>'系统消息', 2=>'广告主用户消息', 3=>'广告主审核消息', 4=>'自媒体主用户消息', 5=>'自媒体主接单消息');
	     $acceptuserlist = array(1=>'独立广告主', 2=>'代理商', 3=>'代理商的广告主', 4=>'自媒体主');
	     $this -> assign("typelist",$typelist);
	     $this -> assign("acceptuserlist",$acceptuserlist);
	     $this -> assign('notices', $data);
	     $this -> assign('type', $type);
	     $this -> assign('acceptuser_one', $acceptuser_one);
	     $this -> assign('acceptuser_two', $acceptuser_two);
	     $this -> assign('pagehtml',$pagehtml);
		$this->assign("funcdesc","公告列表");
		$this->display();
	}

	//添加公告页面
	public function noticeadd(){
		$this -> assign("funcdesc","添加公告");
		$this -> display();
	}

	//执行添加公告
	public function noticeinsert(){
		//var_dump($_POST);exit;
        $current_user = $this -> baseLoginuser;
		$type = I('type');
		$data['noticename'] = I('noticename');
		$data['noticenote'] = I('noticenote');
		$data['type'] = $type;

    	//实例化公告表
    	$notices = M('notice');
		$user = M('user');  //实例化广告主表
		$mediauser = M('user',$this->tablePrefix);  //实例化自媒体主表
		if($type == '1') {
			$data['acceptuser'] = I('acceptuser_one');
			
			$data['createtime'] = time();
	    	//记录日志
	    	$operateUserId = $current_user['manager_user_id'];
	    	$data['managerid'] = $operateUserId;
	    	$re = $notices -> add($data);
	    	if($re){
	    		$desc = "添加公告";
	    		$sql = $notices -> getLastSql();
			    $this->baselog($operateUserId, $sql, $desc);
	    		redirect(U('admin/notice/noticelist'));
	    	}else{
	    		redirect(U('admin/notice/noticeadd'));
	    	}
		}else if($type == '2') {
			$data['acceptuser'] = trim(I('acceptuser_two'),",");
			$dat = explode(",", $data['acceptuser']);
			foreach ($dat as $k=>$vo) {
				$id_user = $user -> field("id") -> where("username='{$vo}'") -> find();
				$data['acceptuser'] = $id_user['id'];
                if($data['acceptuser']){
				    $data['createtime'] = time();
		    	    //记录日志
		    	    $current_user = $this -> baseLoginuser;
		    	    $operateUserId = $current_user['manager_user_id'];
		    	    
		    	    $data['managerid'] = $operateUserId;

		    	    $re = $notices -> add($data);
		    	    if($re){
		    		    $desc = "添加公告";
		    		    $sql = $notices -> getLastSql();
				    $this -> baselog($operateUserId, $sql, $desc);
		    	    }else{
		    		    redirect(U('admin/notice/noticeadd'));
		    	    }
                }
			}
		    redirect(U('admin/notice/noticelist'));
		}else {
			$data['acceptuser'] = trim(I('acceptuser_two'),",");

			$dat = explode(",", $data['acceptuser']);
			foreach ($dat as $vo) {
				$id_mediauser = $mediauser -> field("id") -> where("name='{$vo}'") -> find();
				$data['acceptuser'] = $id_mediauser['id'];

				$data['createtime'] = time();
		    		//记录日志
		    		$current_user = $this -> baseLoginuser;
		    		$operateUserId = $current_user['manager_user_id'];
		    		
		    		$data['managerid'] = $operateUserId;

		    		$re = $notices -> add($data);
		    		if($re){
		    			$desc = "添加公告";
		    			$sql = $notices -> getLastSql();
					$this -> baselog($operateUserId, $sql, $desc);
		    		}else{
		    			redirect(U('admin/notice/noticeadd'));
		    		}
			}
		    	redirect(U('admin/notice/noticelist'));
		}		
	}

	//执行删除公告   以及全部删除
	public function noticedel(){
		$nid = trim(I('nid'));
		$nid = explode(",",$nid);
		$map['id'] = array("IN",$nid);
		$data['status'] = 2;
		$notices = M("notice");
           $re = $notices->where($map)->save($data);
           if($re){
              exit( json_encode(array('err'=>0,'msg'=>'删除成功！')));
           }else{
              exit( json_encode(array('err'=>1,'msg'=>'删除失败！')));
           }
	}

	//展示公告的详细信息
	public function noticeinfo(){
		$id = I('id');
		$notices = M("notice");
		$data = $notices -> field("noticename,noticenote") -> where("id='{$id}'") -> find();
		echo json_encode($data);
		exit;
	}

	//添加的时候检索用户名
	public function user_view(){
		$type = I('id');
		$u_name = I('u_name');
		$user = M('user');  //实例化广告主表
		$mediauser = M('media_user',$this->tablePrefix);  //实例化自媒体主表
		$notice = M("notice");
		
		if($type == '2') {
			unset($map);
			$map['username'] = array("LIKE","%{$u_name}%");
			$data_user = $user -> field('id,username') -> where($map) -> select();
			$html = "";
			if($data_user) {
				$html
				.= "<div class=\"win_title\">
				<a href=\"javascript:void(0)\" rel=\"777\" class=\"close_btn\"></a>
				</div>
				<div class=\"win_con\">
				<div class=\"view_user\">
				<div>
				<table cellspacing=\"0\" class=\"view_task\" style='margin-bottom:10px;'>
				<tbody>
				<tr>
				<th width=\"100\"><a href='javascript:;' onclick='checkall()'>全选</a>/<a href='javascript:;' onclick='checkno()'>全不选</a></th>
				<th width=\"100\">接收用户名：</th>
				</tr>";
				foreach ($data_user as $vo) {
					$html .= "<tr>";
					$html .= "<td><input type='checkbox' name='checked' value='{$vo['id']}' class='checkedbox' /></td>";
					$html .= "<td>{$vo['username']}</td>";
					$html .= "</tr>";
				}
				$html 
				.= "<tr>
				<td><a href='javascript:;' onclick='allpass(2)'>选中</a></td>
				<td><button class=\"close_btn\">取消</button></td>
				</tr>";
				$html 
				.= "</tbody>
				</table>";
				$html 
				.= "</div>
				</div>
				</div>
				</div>";
			}
			echo json_encode($html);
			exit;
		}else {
			unset($map);
			$map['name'] = array("LIKE","%{$u_name}%");
			$data_mediauser = $mediauser -> field('id,name') -> where($map) -> select();
			$html = "";
			if($data_mediauser) {
				$html
				.= "<div class=\"win_title\">
				<a href=\"javascript:void(0)\" rel=\"777\" class=\"close_btn\"></a>
				</div>
				<div class=\"win_con\">
				<div class=\"view_user\">
				<div>
				<table cellspacing=\"0\" class=\"view_task\" style='margin-bottom:10px;'>
				<tbody>
				<tr>
				<th width=\"100\"><a href='javascript:;' onclick='checkall()'>全选</a>/<a href='javascript:;' onclick='checkno()'>全不选</a></th>
				<th width=\"100\">接收用户名：</th>
				</tr>";
				foreach ($data_mediauser as $vo) {
					$html .= "<tr>";
					$html .= "<td><input type='checkbox' name='checked' value='{$vo['id']}' class='checkedbox' /></td>";
					$html .= "<td>{$vo['name']}</td>";
					$html .= "</tr>";
				}
				$html 
				.= "<tr>
				<td><a href='javascript:;' onclick='allpass(4)'>选中</a></td>
				<td><button class=\"close_btn\">取消</button></td>
				</tr>";
				$html 
				.= "</tbody>
				</table>";
				$html 
				.= "</div>
				</div>
				</div>
				</div>";
			}
			echo json_encode($html);
			exit;		
		}
	}

	//
	public function checked(){
		$nid = trim(I('id'),",");
		$nid = explode(",",$nid);
		$type = I('type');
		$user = M('user');  //实例化广告主表
		$mediauser = M('media_user',$this->tablePrefix);  //实例化自媒体主表
		$info = "";
		$ids  = "";
		if($type == '2') {
			foreach ($nid as $v) {
				$info_user = $user -> field("id,username") -> where("id='{$v}'") -> find();
				$info .= $info_user['username'].",";
				$ids .= $info_user['id'].",";
			}
			$info = trim($info,",");
			echo json_encode($info,$ids);
			exit;
		}else {
			foreach ($nid as $v) {
				$info_mediauser = $mediauser -> field("id,name") -> where("id='{$v}'") -> find();
				$info .= $info_mediauser['name'].",";
				$ids .= $info_mediauser['id'].",";
			}
			$info = trim($info,",");
			echo json_encode($info);
			exit;
		}
	}
}