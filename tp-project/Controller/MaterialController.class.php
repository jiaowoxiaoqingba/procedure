<?php
/*
*author: 
*date: 
*desc:素材库管理
*/
namespace Admin\Controller;
use Think\Controller;

class MaterialController extends BaseController{
	//图片库列表展示
	public function photolist(){
		$u_name = empty(trim(I('u_name'))) ? '' : I('u_name');
		$isuse = I('isuse');
		$users = M('user');
		$adpic = M("adpiclibrary");
		$adpictype = M("adpictype");
		if($u_name){
			$names['username'] = array("LIKE","%{$u_name}%");
			$usersdata = $users -> field('id') -> where($names) -> select();
			$userinfo = array();
            if($usersdata){
			    foreach ($usersdata as $value) {
				    $userinfo[] = $value['id'];
			    }
			    $map['payerid'] = array("IN",$userinfo);
            }
		}                                         
		if($isuse !== '') {
			$map['isuse'] = $isuse;
		}
		$photocount = $adpic -> where($map) -> count();
		$count = empty($photocount) ? 0 : $photocount;
       	$Page = new \Think\Pagenew($count,10);
        if($map['payerid']){
       	    $picinfo = $adpic->where($map)->order("payerid,createtime desc")->limit($Page->firstRow.",".$Page->listRows)->select();
        }else{
            $picinfo = $adpic->where($map)->order("createtime desc")->limit($Page->firstRow.",".$Page->listRows)->select();
        }
       	foreach($picinfo as &$v) {
       		$typename = $adpictype->field("typename")->where("id = '{$v['type']}'")->find();
       		$typename['typename'] = empty(trim($typename['typename'])) ? '未分组' : $typename['typename'];
       		$v['type'] = $typename['typename'];
       		$username = $users -> field('username') -> where("id='{$v['payerid']}'") -> find();
       		$v['username'] = $username['username'];
       		$v['createtime'] = date("Y-m-d H:i:s",$v['createtime']);
       	}
       	$Page->parameter["u_name"] = urlencode($u_name);
       	$Page->parameter["isuse"] = urlencode($isuse);
       	$pagehtml = $Page->showtwo();
       	$isuselist = array(0=>'可用',1=>'不可用');
       	$this -> assign("isuselist",$isuselist);
       	$this -> assign('pics', $picinfo);
	    $this -> assign('pagehtml',$pagehtml);
	    $this -> assign('u_name',$u_name);
	    $this -> assign('isuse',$isuse);
	    $this -> assign('funcdesc',"图片库");
	    $this -> display();
	}

	//点击图片的详细信息，展示大图
	public function picinfo(){
		$id = is_numeric(I("id"))?I("id"):0;
		$adpic = M("adpiclibrary");
		$picinfos = $adpic -> field("id,isuse,pic") -> where("id='{$id}'") -> find();
        if($picinfos){
		    $this -> assign("picinfo",$picinfos);
            $this -> assign("sign",1);
        }else{
            $this -> assign("sign",0);
        }
		$this -> display();
	}

	//修改图片状态
	public function update(){
		$id = is_numeric(I('id'))?I('id'):0;
		$isuse = I('isuse');
		$adpic = M("adpiclibrary");
		$data['isuse'] = $isuse;
		$res = $adpic -> where("id='{$id}'") -> save($data);
		if($res){
			redirect(U('admin/material/photolist'));
		}else{
            redirect(U('admin/material/photolist'));
        }
	}

	//列表也ajax修改图片状态
	public function pic_update(){
		$id = is_numeric(I('id'))?I('id'):0;
        $isuse = I('isuse')==1?0:1;
		$adpic = M("adpiclibrary");
		$data['isuse'] = $isuse; 
		$res = $adpic -> where("id='{$id}'") -> save($data);
		if($res){
          		 exit( json_encode(array('err'=>0,'msg'=>'修改成功！')));
        	}else{
          		 exit( json_encode(array('err'=>1,'msg'=>'修改失败！')));
        	}
	}

	//图文列表展示
	public function pictextlist(){
		$u_name = empty(trim(I('u_name'))) ? '' : I('u_name');
		$isuse = I('isuse');
		$title = empty(trim(I('title'))) ? '' : I('title');
		$users = M('user');
		$adnote = M("adnotelibrary");

		if($u_name){
			$names['username'] = array("LIKE","%{$u_name}%");
			$usersdata = $users -> field('id') -> where($names) -> select();
			$userinfo = array();
            if($usersdata){
			    foreach ($usersdata as $value) {
				    $userinfo[] = $value['id'];
			    }
			    $map['payerid'] = array("IN",$userinfo);
            }
		}
		if($isuse !== '') {
			$map['isuse'] = $isuse;
		}
		if($title) {
			$map['title'] = array("LIKE","%{$title}%");
		}

		$pictextcount = $adnote -> where($map) -> count();
		$count = empty($pictextcount) ? 0 : $pictextcount;

       	$Page = new \Think\Pagenew($count,10);
        if($map['payerid']){
            $pictextinfo = $adnote->where($map)->order("payerid,createtime desc")->limit($Page->firstRow.",".$Page->listRows) -> select();
        }else{
            $pictextinfo = $adnote->where($map)->order("createtime desc")->limit($Page->firstRow.",".$Page->listRows) -> select();
        }
       	$Page->parameter["u_name"] = urlencode($u_name);
       	$Page->parameter["isuse"] = urlencode($isuse);
       	$Page->parameter["title"] = urlencode($title);
       	$pagehtml = $Page->showtwo();

       	foreach($pictextinfo as &$v) {
       		$username = $users -> field('username') -> where("id='{$v['payerid']}'") -> find();
       		$v['username'] = $username['username'];
       		$v['createtime'] = date("Y-m-d H:i:s",$v['createtime']);
       	}

       	$isuselist = array(0=>'可用',1=>'不可用');
       	$this -> assign("isuselist",$isuselist);
       	$this -> assign('pics', $pictextinfo);
	     $this -> assign('pagehtml',$pagehtml);
	     $this -> assign('u_name',$u_name);
	     $this -> assign('isuse',$isuse);
	     $this -> assign('title',$title);
	     $this -> assign('funcdesc',"图文库");
	     $this -> display();
	}

	//修改图文展示的状态
	public function pictext_update(){
		$id = I('id');
		$adnote = M("adnotelibrary");
		$isuse = $adnote -> field("isuse") -> where("id='{$id}'") -> find();
		$data['isuse'] = empty(trim($isuse['isuse'])) ? 1 : 0; 

		$res = $adnote -> where("id='{$id}'") -> save($data);
		if($res){
          		 exit( json_encode(array('err'=>0,'msg'=>'修改成功！')));
        	}else{
          		 exit( json_encode(array('err'=>1,'msg'=>'修改失败！')));
        	}
	}

	//查看素材库中图文的详细信息
	public function pictext_info(){
		$id = I('pid');
		$adnote = M("adnotelibrary");
		$adnotedata = $adnote -> where("id='{$id}'") -> find();
		$isshowpic = array(0=>'不显示' , 1=>'显示');
		$adnotedata['content'] = html_entity_decode($adnotedata['content']);
		$adnotedata['isshowpic'] = $isshowpic[$adnotedata['isshowpic']];
		echo json_encode($adnotedata);
		exit;
	}

	//点击图片的详细信息，展示大图
	public function bigpictext(){
		$id = I("id");
		$adpic = M("adnotelibrary");
		$picinfos = $adpic -> field("id,isuse,pic") -> where("id='{$id}'") -> find();
		$this -> assign("picinfo",$picinfos);
		$this -> display();
	}

	//修改图片状态
	public function update_pictext(){
		$id = I('id');
		$isuse = I('isuse');
		$adpic = M("adnotelibrary");
		$data['isuse'] = $isuse;
		$res = $adpic -> where("id='{$id}'") -> save($data);
		if($res){
			redirect(U('admin/material/pictextlist'));
		}
	}
}