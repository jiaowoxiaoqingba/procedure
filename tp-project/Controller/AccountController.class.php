<?php
namespace Admin\Controller;
use Think\Controller;

class AccountController extends BaseController{
	
    //获取微信公众号
	public function wechatlist(){
        $classtypes = C('weixinClass');
        $tmp = empty(I('status')) ? 'waitaudit' : I('status');
        $audit = '1,3';$stats=0;
        switch ($tmp){
            case 'waitaudit':
              $audit='1,3';
              $stats=0;
              break;  
            case 'successaudit':
              $audit=2;
              $stats=1;
              break;
            case 'editaudit':
              $audit=2;
              $stats=0;
              break;
            case 'refuseaudit':
              $audit=3;
              $stats=2;
              break;
        }
        //exit;
        // 获取搜索参数
        $map = array(
            'plattype' => 2,
            'starttime' => empty(I('starttime')) ? '' : strtotime(I('starttime')),
            'endtime' => empty(I('endtime')) ? '' : strtotime(I('endtime')),
            'nick' => empty(I('nick')) ? '' : I('nick'),
            'username' => empty(I('username')) ? '' : I('username'),
            'status' => $stats, 
            'auditstatus' => $audit,
            'follower_min' => empty(I('follower_min')) ? '' : intval(I('follower_min')),
            'follower_max' => empty(I('follower_max')) ? '' : intval(I('follower_max')),
            'wbclass' => empty(I('mediaclass')) ? '' : I('mediaclass')
        );

        $wxcount = $this->getWechatCount($map);
       	$count = empty($wxcount) ? 0 : $wxcount;
       	$Page = new \Think\Pagenew($count,10);
       	
        $limit = $Page->firstRow.",".$Page->listRows;
        $wecatArr = $this->getWechatList($map,$limit);
        $media_wxgz = M('media_user_wxgz',$this->tablePrefix);
        if(!empty($wecatArr)){
            foreach($wecatArr as $key=>$val){
                if(!empty($val['mediaclass'])){
                    $class = implode(',',wbclassText($val['mediaclass']));
                }
                $wecatArr[$key]['mediaclass'] = empty($class) ? '/' : $class;
                $wecatArr[$key]['media_single_price'] = empty($val['media_single_price']) ? 0 : priceConversion($val['media_single_price']);
                $wecatArr[$key]['media_multi_first_price'] = empty($val['media_multi_first_price']) ? 0 : priceConversion($val['media_multi_first_price']);
                $wecatArr[$key]['media_multi_two_price'] = empty($val['media_multi_two_price']) ? 0 : priceConversion($val['media_multi_two_price']);
                $wecatArr[$key]['media_multi_notfirst_price'] = empty($val['media_multi_notfirst_price']) ? 0 : priceConversion($val['media_multi_notfirst_price']);
                $wecatArr[$key]['adver_single_price'] = empty($val['adver_single_price']) ? 0 : priceConversion($val['adver_single_price']);
                $wecatArr[$key]['adver_multi_first_price'] = empty($val['adver_multi_first_price']) ? 0 : priceConversion($val['adver_multi_first_price']);
                $wecatArr[$key]['adver_multi_two_price'] = empty($val['adver_multi_two_price']) ? 0 : priceConversion($val['adver_multi_two_price']);
                $wecatArr[$key]['adver_multi_notfirst_price'] = empty($val['adver_multi_notfirst_price']) ? 0 : priceConversion($val['adver_multi_notfirst_price']);
                if($tmp == 'editaudit'){
                    $wechatArr = $media_wxgz->where('mediaid='.$val['wmediaid'].' and status=1')->find();
                    $wecatArr[$key]['media_single_price2'] = empty($wechatArr['media_single_price']) ? 0 : priceConversion($wechatArr['media_single_price']);
                    $wecatArr[$key]['media_multi_notfirst_price2'] = empty($wechatArr['media_multi_notfirst_price']) ? 0 : priceConversion($wechatArr['media_multi_notfirst_price']);
                    $wecatArr[$key]['media_multi_first_price2'] = empty($wechatArr['media_multi_first_price']) ? 0 : priceConversion($wechatArr['media_multi_first_price']);
                    $wecatArr[$key]['media_multi_two_price2'] = empty($wechatArr['media_multi_two_price']) ? 0 : priceConversion($wechatArr['media_multi_two_price']);
                    $wecatArr[$key]['adver_single_price2'] = empty($wechatArr['adver_single_price']) ? 0 : priceConversion($wechatArr['adver_single_price']);
                    $wecatArr[$key]['adver_multi_first_price2'] = empty($wechatArr['adver_multi_first_price']) ? 0 : priceConversion($wechatArr['adver_multi_first_price']);
                    $wecatArr[$key]['adver_multi_two_price2'] = empty($wechatArr['adver_multi_two_price']) ? 0 : priceConversion($wechatArr['adver_multi_two_price']);
                    $wecatArr[$key]['adver_multi_notfirst_price2'] = empty($wechatArr['adver_multi_notfirst_price']) ? 0 : priceConversion($wechatArr['adver_multi_notfirst_price']);
                }
            }
        }
        
       	$Page->parameter["status"] = urlencode($tmp);
        $Page->parameter["starttime"] = date('Y-m-d H:i:s',$map['starttime']); 
        $Page->parameter["endtime"] = date('Y-m-d H:i:s',$map['endtime']); 
        $Page->parameter["nick"] = urlencode($map['nick']);     
       	$Page->parameter["username"] = urlencode($map['username']);
       	$Page->parameter["follower_min"] = urlencode($map['follower_min']);
        $Page->parameter["follower_max"] = urlencode($map['follower_max']);
       	$Page->parameter["mediaclass"] = urlencode($map['wbclass']);
	    $pagehtml = $Page->showtwo();
        
        $this -> assign('tmp',$tmp); 
	    $this -> assign('classtypes',$classtypes);
	    $this -> assign('mediaclass',$map['wbclass']);
        $this -> assign('username',$map['username']);
        $this -> assign('nick',$map['nick']);
        $this -> assign('starttime',date('Y-m-d H:i:s',$map['starttime']));
        $this -> assign('endtime',date('Y-m-d H:i:s',$map['endtime']));
        $this -> assign('follower_min',$map['follower_min']);
        $this -> assign('follower_max',$map['follower_max']);  
	    $this -> assign('wechats', $wecatArr);
        $this -> assign('pagehtml',$pagehtml);
		$this -> assign('funcdesc','微信公众号');
        if($tmp == 'editaudit'){
		    $this -> display('editaudit');
        }elseif($tmp=='refuseaudit'){
            $this -> display('refuseaudit');
        }elseif($tmp=='successaudit'){
            $this -> display('successaudit');
        }else{
            $this -> display();
        }
	}

    /**
     * 微信审核列表总数
     * @param array $param
     * @return array
    */
    private function getWechatCount($param=array()){
        $result = 0;
        if(!empty($param)){
            $modle = new \Think\Model();
            $sql = '';
            $sql .= 'select ';
            $sql .= 'count(*) as num ';
            $sql .= 'from '.$this->tablePrefix.'media_user as m ';
            $sql .= 'inner join '.$this->tablePrefix.'user as u on u.id = m.uid ';
            $sql .= 'left join '.$this->tablePrefix.'media_user_wxgz as w1 on w1.mediaid = m.id ';
            $sql .= 'where '.$this->getWhere($param).' ';

            $r = $modle->query($sql);
            $result = $r[0]['num'];
        }
        return $result;
    }
    
    /**
     * 微信审核列表
     * @param array $param
     * @param string $limit
     * @return array
    */
    private function getWechatList($param=array(), $limit=''){
        $result = array();
        if(!empty($param)){
            $modle = new \Think\Model();
            
            $sql = '';
            $sql .= 'select ';
            $sql .= 'm.platid,m.nick,m.pricestatus,from_unixtime(m.createtime) as register_date,m.verified as verified,';
            $sql .= 'w1.id as wid,w1.mediaid as wmediaid,w1.follower as wfollower,w1.gender_dis as wgender_dis,w1.follower_img as wfollower_img,w1.reason as wreason,w1.follower,w1.mediaclass,w1.status,w1.media_single_price,w1.media_multi_first_price,w1.media_multi_two_price,w1.media_multi_notfirst_price,w1.adver_single_price,w1.adver_multi_first_price,w1.adver_multi_two_price,w1.adver_multi_notfirst_price,from_unixtime(w1.createtime) as date,';
            $sql .= 'w1.status as status_history,w1.media_single_price as media_single_price2,w1.media_multi_first_price as media_multi_first_price2,w1.media_multi_two_price as media_multi_two_price2,w1.media_multi_notfirst_price as media_multi_notfirst_price2,w1.adver_single_price as adver_single_price2,w1.adver_multi_first_price as adver_multi_first_price2,w1.adver_multi_two_price as adver_multi_two_price2,w1.adver_multi_notfirst_price as adver_multi_notfirst_price2 ';
            $sql .= 'from '.$this->tablePrefix.'media_user as m ';
            $sql .= 'inner join '.$this->tablePrefix.'user as u on u.id = m.uid ';
            $sql .= 'left join '.$this->tablePrefix.'media_user_wxgz as w1 on w1.mediaid = m.id ';
            $sql .= 'where '.$this->getWhere($param).' ';
            $sql .= 'order by w1.createtime desc ';
            $sql .= 'limit '.$limit;
            
            $result = $modle->query($sql);
        }
        return $result;
    }
    
	//微信公众号的详细信息
	public function wechatinfo(){
		$id = I('wid');
		$mediauser_wxgz = M('media_user_wxgz',$this->tablePrefix);
		$user_wxgzinfo = $mediauser_wxgz -> field("mediaid,follower,gender_dis,avatar,qrcode,follower_img,wechatbiz,media_single_price,media_multi_first_price,media_multi_two_price,media_multi_notfirst_price,adver_single_price,adver_multi_first_price,adver_multi_two_price,adver_multi_notfirst_price") -> where("id='{$id}'") -> find();
		if($user_wxgzinfo){
			$mediauser = M("media_user",$this->tablePrefix);
			$user_info = $mediauser -> field("uid,plattype,platid,name,nick") -> where("id='{$user_wxgzinfo['mediaid']}'") -> find();
			$user = M("user",$this->tablePrefix);
			$info = $user -> field("contact,mobile,qq,wechat,email,address,company") -> where("id='{$user_info['uid']}'") -> find();
			$user_wxgzinfo['uid'] = $user_info['uid'];
			$user_wxgzinfo['plattype'] = $user_info['plattype'];
			$user_wxgzinfo['platid'] = $user_info['platid'];
			$user_wxgzinfo['name'] = $user_info['name'];
			$user_wxgzinfo['nick'] = $user_info['nick'];
			$user_wxgzinfo['contact'] = $info['contact'];
			$user_wxgzinfo['mobile'] = $info['mobile'];
			$user_wxgzinfo['qq'] = $info['qq'];
			$user_wxgzinfo['wechat'] = $info['wechat'];
			$user_wxgzinfo['email'] = $info['email'];
			$user_wxgzinfo['address'] = $info['address'];
			$user_wxgzinfo['company'] = $info['company'];
            $user_wxgzinfo['media_single_price'] =  priceConversion($user_wxgzinfo['media_single_price']);
            $user_wxgzinfo['media_multi_first_price'] =  priceConversion($user_wxgzinfo['media_multi_first_price']);
            $user_wxgzinfo['media_multi_two_price'] =  priceConversion($user_wxgzinfo['media_multi_two_price']);
            $user_wxgzinfo['media_multi_notfirst_prcie'] =  priceConversion($user_wxgzinfo['media_multi_notfirst_prcie']);
            $user_wxgzinfo['adver_single_price'] =  priceConversion($user_wxgzinfo['adver_single_price']);
            $user_wxgzinfo['adver_multi_first_price'] =  priceConversion($user_wxgzinfo['adver_multi_first_price']);
            $user_wxgzinfo['adver_multi_two_price'] =  priceConversion($user_wxgzinfo['adver_multi_two_price']);
            $user_wxgzinfo['adver_multi_notfirst_price'] =  priceConversion($user_wxgzinfo['adver_multi_notfirst_price']);
			echo json_encode($user_wxgzinfo);
			exit;
		}else{
			echo json_encode('fail');
			exit;
		}
	}

    /**
    * 审核微信帐号
    * @param array $param
    */
    public function verifyWechat(){
        $result = array('err'=>false,'msg'=>'数据不完整');
        $param = I('POST.');
        if(is_numeric($param['status']) && is_numeric($param['wechat']) && is_numeric($param['single']) && is_numeric($param['first']) && is_numeric($param['second']) && is_numeric($param['third'])){
            $result = $this->setUserStatus($param);
        }
        echo json_encode($result);
    }
    
    /**
    * 更新微信帐号状态
    * @param array $param
    */
    private function setUserStatus($param=array()){
        $sql = "";
        $result = array('err'=>true,'msg'=>'');
        $managerary = $this->baseLoginuser;
        if(!empty($param)){
                $m = M();
                $m->startTrans();
                $wid = $param['wechat'];
                $media_wxgz = M('media_user_wxgz',$this->tablePrefix);
                $media = M('media_user',$this->tablePrefix);
                $userInfo = $media_wxgz->where("id=$wid")->find();
                if(empty($userInfo)){
                    $result = array('err'=>false,'msg'=>"微信账号不存在！");
                    return $result; 
                }
                $mediaInfo = $media->where("id=".$userInfo['mediaid'])->find();
                if(empty($mediaInfo)){
                    $result = array('err'=>false,'msg'=>"微信账号不存在！");
                    return $result; 
                }
                if(empty($param['status'])){
                    $set = array(
                        'reason' => "{$param['reason']}",
                        'status' => 2
                    );
                }else{
                    $rowWX = $media_wxgz->where('mediaid='.$userInfo['mediaid'].' and id<>'.$wid)->delete();
                    $sql = $media_wxgz->getLastsql();
                    $set = array(
                        'status' => 1,
                        'adver_single_price' => priceConversion($param['single'],'',false),
                        'adver_multi_first_price' => priceConversion($param['first'],'',false),
                        'adver_multi_two_price' => priceConversion($param['second'],'',false),
                        'adver_multi_notfirst_price' => priceConversion($param['third'],'',false),
                        'managerid' => empty($managerary['manager_user_id']) ? 0 : intval($managerary['manager_user_id'])
                    );
                }
                $rowWX = $media_wxgz->where('id='.$wid.' and status=0')->save($set);
                $sql = $sql.$media_wxgz->getLastsql(); 
                if(empty($rowWX)){
                    $result = array('err'=>false,'msg'=>"自媒体价格修改失败1");
                    $m->rollBack ();
                    return $result;
                }
                if($mediaInfo['auditstatus'] != 2){       // 首次提交价格
                    if($param['status']){
                        $setUser = array(
                            'auditstatus' => 2 ,
                            'classstatus' => 2 ,
                            'pricestatus' => 2 
                        );
                        $rowMedia = $media->where("id=".$mediaInfo['id'])->save($setUser);
                        $sql = $sql.$media->getLastsql(); 
                        if(empty($rowMedia)){
                            $result = array('err'=>false,'msg'=>"自媒体价格修改失败2");
                            $m->rollBack ();
                            return $result;
                        }
                    }else{
                       $setUser = array(
                            'auditstatus' => 3 ,
                            'classstatus' => 3 ,
                            'pricestatus' => 3 
                        );
                        $rowMedia = $media->where("id=".$mediaInfo['id'])->save($setUser);
                        $sql = $sql.$media->getLastsql();
                        if(empty($rowMedia)){
                            $result = array('err'=>false,'msg'=>"自媒体价格修改失败3");
                            $m->rollBack ();
                            return $result;
                        }
                    }
                } 
                $m->commit();
                $desc = "更新微信帐号状态";
                funOperateLog($managerary['manager_user_id'],$sql,$desc);
                //$m->rollBack ();
        }
        return $result;
    }
    
    /**
    * 修改微信价格
    * @param array $param
    */
    public function updatePrice(){
        $result = array('err'=>false,'msg'=>'数据不完整');
        $param = I('POST.');
        if(is_numeric($param['wechat']) && is_numeric($param['single']) && is_numeric($param['first']) && is_numeric($param['second']) && is_numeric($param['third'])){
            $result = $this->setUpdatePrice($param);
        }
        echo json_encode($result); 
    }
    
    /**
    * 修改微信用户审请价格
    * @param array $param
    */
    private function setUpdatePrice($param=array()){
        $result = array('err'=>true,'msg'=>'');
        $wid = intval($param['wechat']);
        $managerary = $this->baseLoginuser;
        $set = array(
            'status' => 1,
            'adver_single_price' => priceConversion($param['single'],'',false),
            'adver_multi_first_price' => priceConversion($param['first'],'',false),
            'adver_multi_two_price' => priceConversion($param['second'],'',false),
            'adver_multi_notfirst_price' => priceConversion($param['third'],'',false),           
            'managerid' => empty($managerary['manager_user_id']) ? 0 : intval($managerary['manager_user_id'])
        );
        $media_wxgz = M('media_user_wxgz',$this->tablePrefix);
        $rowWX = $media_wxgz->where('id='.$wid.' and status=1')->save($set); 
        if(empty($rowWX)){
            $operateUserId = $managerary['manager_user_id'];
            $sql = $media_wxgz->getlastsql();
            $desc = "微信价格状态修改失败";
            funOperateLog($operateUserId, $sql, $desc);
            $result = array('err'=>true,'msg'=>'微信价格状态修改失败');
        }
        return $result;
    }
    
    /*
    * 粉丝认证
    */
    public function authFollower(){
        $result = array('err'=>false,'msg'=>'数据不完整');
        $param = I('POST.');
        if(is_numeric($param['wmediaid']) && is_numeric($param['verified'])){
            $result = array('err'=>true,'msg'=>'');
            $mediaid = intval($param['wmediaid']);
            $verified =  intval($param['verified']);
            $set['verified'] = $verified;
            $media = M('media_user',$this->tablePrefix);
            $rowWX = $media->where('id='.$mediaid.' and auditstatus=2')->save($set);
            if(empty($rowWX)){
                $result = array('err'=>true,'msg'=>'粉丝认证成功！');
            }
        }
        echo json_encode($result);
    }
    
	//自媒体主帐号惩罚统计
	public function punishlist(){
        $plattypelist = C('WXPLATTYPE');
		$username = empty(trim(I('username'))) ? '' : I('username');
		$name = empty(trim(I('name'))) ? '' : I('name');
		$mediacredit = M("mediacredit");
		$mediauser = M("media_user",$this->tablePrefix);
		$user = M("user",$this->tablePrefix);
		$starttime = empty(I('starttime'))?'':strtotime(I('starttime'));
        	$endtime = empty(I('endtime'))?'':strtotime(I('endtime'));
		if($username){
			$umap['username'] = array("LIKE","%{$username}%");
			$UsersInfo = $user -> field('id') -> where($umap) -> select();
			if($UsersInfo){
				$userin = array();
				foreach($UsersInfo as $val){
					$userin[] = $val['id'];
				}
				$map['userid'] = array("IN",$userin);
			}else{
				$map['userid'] = 0;
			}
		}
		if($name){
			$mmap['name'] = array("LIKE","%{$name}%");
			$MediaUsersInfo = $mediauser -> field('id') -> where($mmap) -> select();
			if($MediaUsersInfo){
				$mediauserin = array();
				foreach($MediaUsersInfo as $v){
					$mediauserin[] = $v['id'];
				}
				$map['mediaid'] = array("IN",$mediauserin);
			}else{
				$map['mediaid'] = 0;
			}
		}
		if($starttime && $endtime){
           		$map['updatetime'] = array("BETWEEN","$starttime,$endtime");
        	}
        //平台类型的条件  微信公众号平台 plattype = 0
        $map['plattype'] = 0;
       	$mediacreditcount = $mediacredit -> where($map) -> count();
       	$count = empty($mediacreditcount) ? 0 : $mediacreditcount;
       	$Page = new \Think\Pagenew($count,10);
       	$data = $mediacredit -> where($map)->order('cliquenumber desc') -> limit($Page->firstRow.",".$Page->listRows) -> select();
       	foreach($data as &$v){
       		$mediauser_info = $mediauser -> field('platstatus,name,plattype') -> where("id='{$v['mediaid']}'") -> find();
       		$v['platstatus'] = $mediauser_info['platstatus'];
       		$v['name'] = $mediauser_info['name'];
       		$user_info = $user -> field('username') -> where("id='{$v['userid']}'") -> find();
       		$v['username'] = $user_info['username'];
       		$v['updatetime'] = date("Y-m-d H:i:s",$v['updatetime']);
       	}
       	$Page->parameter["username"] = urlencode($username);
       	$Page->parameter["name"] = urlencode($name);
           	$Page->parameter["starttime"] = date('Y-m-dH:i:s',$starttime);
          	$Page->parameter["endtime"] = date('Y-m-dH:i:s',$endtime);
	    	$pagehtml = $Page->showtwo();
            $this -> assign('plattypelist',$plattypelist);
	    	$this -> assign('punishs', $data);
	    	$this -> assign('pagehtml',$pagehtml);	    
	    	$this -> assign('username',$username);
	    	$this -> assign('name',$name);
		    $this->assign('starttime',date('Y-m-d H:i:s',$starttime));
         	$this->assign('endtime',date('Y-m-d H:i:s',$endtime));
	    	$this -> assign('funcdesc',"公众号惩罚统计");	 
	    	$this -> display();
	}

	//ajax请求修改自媒体账号的状态   可用或者不可用
    public function punishinfo(){
        $id = I('pid');
        $mediaid = I('mediaid');
        $status = I('status');
        $user = M('media_user',$this->tablePrefix);
        $data['platstatus'] = $status;
        $re = $user -> where("id='{$mediaid}'") -> setField($data);
        if($re){
            $managerary = $this->baseLoginuser;
            $operateUserId = $managerary['manager_user_id']; 
            $sql = $user->getlastsql();
            $desc = "自媒体主状态修改成功！";
            funOperateLog($operateUserId, $sql, $desc);
            exit( json_encode(array('err'=>0,'msg'=>'自媒体主状态修改成功！')));
        }else{
            exit( json_encode(array('err'=>1,'msg'=>'自媒体主状态修改失败！')));
        }
    }
    
    /**
    * 组合查询条件
    * @param array $param
    */
    private function getWhere($param=array()){
        $result = ' 1 ';
        if(!empty($param)){
            if(!empty($param['starttime']) && !empty($param['endtime'])){
                $result .= ' and m.createtime between '.$param['starttime'].' and '.$param['endtime'];
            }elseif(!empty($param['starttime']) && empty($param['endtime'])){
                $result .= ' and m.createtime >= '.$param['starttime'];
            }elseif(empty($param['starttime']) && !empty($param['endtime'])){
                $result .= ' and m.createtime <= '.$param['endtime'];
            }
            if(!empty($param['nick']) || is_numeric($param['nick'])){
                $result .= ' and m.nick like "%'.$param['nick'].'%"';
            }
            if(!empty($param['username']) || is_numeric($param['username'])){
                $result .= ' and u.username like "%'.$param['username'].'%"';
            }
            if(is_numeric($param['wid'])){
                $result .= ' and w1.id = '.$param['wid'].' ';
            }
            if(is_numeric($param['status'])){
                $result .= ' and w1.status = '.$param['status'].' ';
            }
            if(!empty($param['auditstatus'])){
                $result .= ' and m.auditstatus in('.$param['auditstatus'].') ';
            }
            if(!empty($param['plattype'])){
                $result .= ' and m.plattype in ('.$param['plattype'].')';
            }
            if(is_numeric($param['follower_min']) && is_numeric($param['follower_max'])){
                $result .= ' and w1.follower between '.$param['follower_min'].' and '.$param['follower_max'];
            }elseif(is_numeric($param['follower_min']) && !is_numeric($param['follower_max'])){
                $result .= ' and w1.follower >= '.$param['follower_min'];
            }elseif(!is_numeric($param['follower_min']) && is_numeric($param['follower_max'])){
                $result .= ' and w1.follower <= '.$param['follower_max'];
            }
            if(is_numeric($param['active_follower_min']) && is_numeric($param['active_follower_max'])){
                $result .= ' and m.active_follower between '.$param['active_follower_min'].' and '.$param['active_follower_max'];
            }elseif(is_numeric($param['active_follower_min']) && !is_numeric($param['active_follower_max'])){
                $result .= ' and m.active_follower >= '.$param['active_follower_min'];
            }elseif(!is_numeric($param['active_follower_min']) && is_numeric($param['active_follower_max'])){
                $result .= ' and m.active_follower <= '.$param['active_follower_max'];
            }
            if(!empty($param['wbclass']) && isset(C('weixinClass')[$param['wbclass']])){
                $result .= ' and (w1.mediaclass&'.$param['wbclass'].'>0)';
            }
        }
        return $result;
    }
}