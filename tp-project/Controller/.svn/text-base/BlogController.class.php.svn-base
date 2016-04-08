<?php
namespace Admin\Controller;
use Think\Controller;

class BlogController extends BaseController{
    
    //获取微博帐号
    public function waitaudit(){
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
            'plattype' => 0,
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

        $wbcount = $this->getBlogCount($map);
        $count = empty($wbcount) ? 0 : $wbcount;
        $Page = new \Think\Pagenew($count,10);
        
        $limit = $Page->firstRow.",".$Page->listRows;
        $wecatArr = $this->getBlogList($map,$limit);
        $class_price_apply = M('class_price_apply',$this->tablePrefix);
        if(!empty($wecatArr)){
            foreach($wecatArr as $key=>$val){
                if(!empty($val['classids'])){
                    $class = implode(',',wbclassText($val['classids']));
                }
                $wecatArr[$key]['classids'] = empty($class) ? '/' : $class;
                $wecatArr[$key]['postprice'] = empty($val['postprice']) ? 0 : priceConversion($val['postprice']);
                $wecatArr[$key]['forwardprice'] = empty($val['forwardprice']) ? 0 : priceConversion($val['forwardprice']);
                $wecatArr[$key]['postprice_adver'] = empty($val['postprice_adver']) ? 0 : priceConversion($val['postprice_adver']);
                $wecatArr[$key]['forwardprice_adver'] = empty($val['forwardprice_adver']) ? 0 : priceConversion($val['forwardprice_adver']);
                
                if($tmp == 'editaudit'){
                    $wechatArr = $class_price_apply->where('mediaid='.$val['cmediaid'].' and status=1')->find();
                    $wecatArr[$key]['postprice2'] = empty($wechatArr['postprice']) ? 0 : priceConversion($wechatArr['postprice']);
                    $wecatArr[$key]['forwardprice2'] = empty($wechatArr['forwardprice']) ? 0 : priceConversion($wechatArr['forwardprice']);
                    $wecatArr[$key]['postprice_adver2'] = empty($wechatArr['postprice_adver']) ? 0 : priceConversion($wechatArr['postprice_adver']);
                    $wecatArr[$key]['forwardprice_adver2'] = empty($wechatArr['forwardprice_adver']) ? 0 : priceConversion($wechatArr['forwardprice_adver']);
                    
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
        $this -> assign('data', $wecatArr);
        $this -> assign('pagehtml',$pagehtml);
        $this -> assign('funcdesc','微博帐号');
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
     * 微博审核列表总数
     * @param array $param
     * @return array
    */
    private function getBlogCount($param=array()){
        $result = 0;
        if(!empty($param)){
            $modle = new \Think\Model();
            $sql = '';
            $sql .= 'select ';
            $sql .= 'count(*) as num ';
            $sql .= 'from '.$this->tablePrefix.'media_user as m ';
            $sql .= 'inner join '.$this->tablePrefix.'user as u on u.id = m.uid ';
            $sql .= 'left join '.$this->tablePrefix.'class_price_apply as c1 on c1.mediaid = m.id ';
            $sql .= 'where '.$this->getWhere($param).' ';

            $r = $modle->query($sql);
            $result = $r[0]['num'];
        }
        return $result;
    }
    
    /**
     * 微博审核列表
     * @param array $param
     * @param string $limit
     * @return array
    */
    private function getBlogList($param=array(), $limit=''){
        $result = array();
        if(!empty($param)){
            $modle = new \Think\Model();
            
            $sql = '';
            $sql .= 'select ';
            $sql .= 'm.nick,m.pricestatus,from_unixtime(m.createtime) as register_date,m.verified as verified,';
            $sql .= 'c1.id as cid,c1.mediaid as cmediaid,c1.followers_count as cfollower,c1.followers_count,c1.classids,c1.status,c1.postprice,c1.forwardprice,c1.postprice_adver,c1.forwardprice_adver,from_unixtime(c1.applytime) as date,';
            $sql .= 'c1.status as status_history,c1.postprice as postprice2,c1.forwardprice as forwardprice2,c1.postprice_adver as postprice_adver2,c1.forwardprice_adver as forwardprice_adver2 ';
            $sql .= 'from '.$this->tablePrefix.'media_user as m ';
            $sql .= 'inner join '.$this->tablePrefix.'user as u on u.id = m.uid ';
            $sql .= 'left join '.$this->tablePrefix.'class_price_apply as c1 on c1.mediaid = m.id ';
            $sql .= 'where '.$this->getWhere($param).' ';
            $sql .= 'order by c1.applytime desc ';
            $sql .= 'limit '.$limit;
            
            $result = $modle->query($sql);
        }
        return $result;
    }
    
    //微博帐号的详细信息
    public function bloginfo(){
        $id = I('cid');
        $class_price_apply = M('class_price_apply',$this->tablePrefix);
        $user_wbinfo = $class_price_apply -> field("mediaid,followers_count,avatar,qrcode,fanspic,postprice,forwardprice,postprice_adver,forwardprice_adver") -> where("id='{$id}'") -> find();
        if($user_wbinfo){
            $mediauser = M("media_user",$this->tablePrefix);
            $user_info = $mediauser -> field("uid,plattype,name,nick,weibo_url") -> where("id='{$user_wbinfo['mediaid']}'") -> find();
            $user = M("user",$this->tablePrefix);
            $info = $user -> field("contact,mobile,qq,email,address,company") -> where("id='{$user_info['uid']}'") -> find();
            $user_wbinfo['uid'] = $user_info['uid'];
            $user_wbinfo['plattype'] = $user_info['plattype'];
            $user_wbinfo['name'] = $user_info['name'];
            $user_wbinfo['nick'] = $user_info['nick'];
            $user_wbinfo['weibo_url'] = $user_info['weibo_url'];
            $user_wbinfo['contact'] = $info['contact'];
            $user_wbinfo['mobile'] = $info['mobile'];
            $user_wbinfo['qq'] = $info['qq'];
            $user_wbinfo['email'] = $info['email'];
            $user_wbinfo['address'] = $info['address'];
            $user_wbinfo['company'] = $info['company'];
            $user_wbinfo['postprice'] =  priceConversion($user_wbinfo['postprice']);
            $user_wbinfo['forwardprice'] =  priceConversion($user_wbinfo['forwardprice']);
            $user_wbinfo['postprice_adver'] =  priceConversion($user_wbinfo['postprice_adver']);
            $user_wbinfo['forwardprice_adver'] =  priceConversion($user_wbinfo['forwardprice_adver']);
            echo json_encode($user_wbinfo);
            exit;
        }else{
            echo json_encode('fail');
            exit;
        }
    }

    /**
    * 审核微博帐号
    * @param array $param
    */
    public function verifyBlog(){
        $result = array('err'=>false,'msg'=>'数据不完整');
        $param = I('POST.');
        if(is_numeric($param['status']) && is_numeric($param['blog']) && is_numeric($param['postprice']) && is_numeric($param['forwardprice'])){
            $result = $this->setUserStatus($param);
        }
        echo json_encode($result);
    }
    
    /**
    * 更新微博帐号状态
    * @param array $param
    */
    private function setUserStatus($param=array()){
        $sql = "";
        $result = array('err'=>true,'msg'=>'');
        $managerary = $this->baseLoginuser;
        if(!empty($param)){
                $m = M();
                $m->startTrans();
                $cid = $param['blog'];
                $class_price_apply = M('class_price_apply',$this->tablePrefix);
                $media = M('media_user',$this->tablePrefix);
                $userInfo = $class_price_apply->where("id=$cid")->find();
                if(empty($userInfo)){
                    $result = array('err'=>false,'msg'=>"微博帐号不存在！");
                    return $result; 
                }
                $mediaInfo = $media->where("id=".$userInfo['mediaid'])->find();
                if(empty($mediaInfo)){
                    $result = array('err'=>false,'msg'=>"微博帐号不存在！");
                    return $result; 
                }
                if(empty($param['status'])){
                    $set = array(
                        'reason' => "{$param['reason']}",
                        'status' => 2
                    );
                }else{
                    $rowWB = $class_price_apply->where('mediaid='.$userInfo['mediaid'].' and id<>'.$cid)->delete();
                    $sql = $class_price_apply->getLastsql();
                    $set = array(
                        'status' => 1,
                        'postprice_adver' => priceConversion($param['postprice'],'',false),
                        'forwardprice_adver' => priceConversion($param['forwardprice'],'',false),
                        'managerid' => empty($managerary['manager_user_id']) ? 0 : intval($managerary['manager_user_id'])
                    );
                }
                $rowWB = $class_price_apply->where('id='.$cid.' and status=0')->save($set);
                $sql = $sql.$class_price_apply->getLastsql(); 
                if(empty($rowWB)){
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
                $desc = "更新微博帐号状态";
                funOperateLog($managerary['manager_user_id'],$sql,$desc);
                //$m->rollBack ();
        }
        return $result;
    }
    
    /**
    * 修改微博价格
    * @param array $param
    */
    public function updatePrice(){
        $result = array('err'=>false,'msg'=>'数据不完整');
        $param = I('POST.');
        if(is_numeric($param['blog']) && is_numeric($param['postprice']) && is_numeric($param['forwardprice'])){
            $result = $this->setUpdatePrice($param);
        }
        echo json_encode($result); 
    }
    
    /**
    * 修改微博帐号用户审请价格
    * @param array $param
    */
    private function setUpdatePrice($param=array()){
        $result = array('err'=>true,'msg'=>'');
        $cid = intval($param['blog']);
        $managerary = $this->baseLoginuser;
        $set = array(
            'status' => 1,
            'postprice_adver' => priceConversion($param['postprice'],'',false),
            'forwardprice_adver' => priceConversion($param['forwardprice'],'',false),          
            'managerid' => empty($managerary['manager_user_id']) ? 0 : intval($managerary['manager_user_id'])
        );
        $class_price_apply = M('class_price_apply',$this->tablePrefix);
        $rowWB = $class_price_apply->where('id='.$cid.' and status=1')->save($set); 
        if(empty($rowWB)){
            $operateUserId = $managerary['manager_user_id'];
            $sql = $class_price_apply->getlastsql();
            $desc = "微博价格状态修改失败";
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
        if(is_numeric($param['cmediaid']) && is_numeric($param['verified'])){
            $result = array('err'=>true,'msg'=>'');
            $mediaid = intval($param['cmediaid']);
            $verified =  intval($param['verified']);
            $set['verified'] = $verified;
            $media = M('media_user',$this->tablePrefix);
            $rowWB = $media->where('id='.$mediaid.' and auditstatus=2')->save($set);
            if(empty($rowWB)){
                $result = array('err'=>true,'msg'=>'粉丝认证成功！');
            }
        }
        echo json_encode($result);
    }
    
    //微博自媒体主帐号惩罚统计
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
        //平台类型的条件  微博帐号平台 plattype = 2
        $map['plattype'] = 2;
        
        $mediacreditcount = $mediacredit -> where($map) -> count();
        $count = empty($mediacreditcount) ? 0 : $mediacreditcount;
        $Page = new \Think\Pagenew($count,10);
        $data = $mediacredit -> where($map)->order('id DESC') -> limit($Page->firstRow.",".$Page->listRows) -> select();
        foreach($data as &$v){
            $mediauser_info = $mediauser -> field('platstatus,name') -> where("id='{$v['mediaid']}'") -> find();
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
            $this -> assign('funcdesc',"微博惩罚统计");    
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
            if(is_numeric($param['cid'])){
                $result .= ' and c1.id = '.$param['cid'].' ';
            }
            if(is_numeric($param['status'])){
                $result .= ' and c1.status = '.$param['status'].' ';
            }
            if(!empty($param['auditstatus'])){
                $result .= ' and m.auditstatus in('.$param['auditstatus'].') ';
            }
            if(is_numeric($param['plattype'])){
                $result .= ' and m.plattype in ('.$param['plattype'].')';
            }
            if(is_numeric($param['follower_min']) && is_numeric($param['follower_max'])){
                $result .= ' and c1.followers_count between '.$param['follower_min'].' and '.$param['follower_max'];
            }elseif(is_numeric($param['follower_min']) && !is_numeric($param['follower_max'])){
                $result .= ' and c1.followers_count >= '.$param['follower_min'];
            }elseif(!is_numeric($param['follower_min']) && is_numeric($param['follower_max'])){
                $result .= ' and c1.followers_count <= '.$param['follower_max'];
            }
            if(is_numeric($param['active_follower_min']) && is_numeric($param['active_follower_max'])){
                $result .= ' and m.active_follower between '.$param['active_follower_min'].' and '.$param['active_follower_max'];
            }elseif(is_numeric($param['active_follower_min']) && !is_numeric($param['active_follower_max'])){
                $result .= ' and m.active_follower >= '.$param['active_follower_min'];
            }elseif(!is_numeric($param['active_follower_min']) && is_numeric($param['active_follower_max'])){
                $result .= ' and m.active_follower <= '.$param['active_follower_max'];
            }
            if(!empty($param['wbclass']) && isset(C('weixinClass')[$param['wbclass']])){
                $result .= ' and (c1.classids&'.$param['wbclass'].'>0)';
            }
        }
        return $result;
    }
}