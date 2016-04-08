<?php
/*
*author: 
*date: 
*desc:自媒体主管理
*/
namespace Admin\Controller;
use Think\Controller;
class MediaController extends BaseController{
    function _empty() {}

    //管理员列表
    public function medialist(){
        $pagecount = 15;
        $status = empty(trim(I('request.status'))) ? '' : trim(I('request.status'));
        $username =  empty(trim(I('request.username'))) ? '' : I('request.username');
        $mobile =  empty(trim(I('request.mobile'))) ? '' : I('request.mobile');
        
        $ma=M('user',$this->tablePrefix);
        $map['agt_priv']  = 0;
        if(!empty($username)){
            $map['username']  = array('like',"%$username%");
        }
        if(!empty($mobile)){
            $map['mobile']  = $mobile;
        }
        if(is_numeric($status)){
            if($status==2){
                $map['status'] = 0;
            }else{
                $map['status'] = array('NEQ',0);
            }
        }
        //用户金额合计统计
        $retotal = $ma->field("sum(recharge) as recharge_total,sum(balance) as balance_total")->where($map)->select();
        $retotal = $retotal[0];
        if(!empty($retotal)){
            $available_total = $retotal['recharge_total']-$retotal['balance_total'];
            $total['available_total'] = priceConversion($available_total,'',true); //提现
            $total['recharge_total'] = priceConversion($retotal['recharge_total'],'',true);
            $total['balance_total'] = priceConversion($retotal['balance_total'],'',true);
        }

        $mediacount = $ma->where($map)->count();
        $count = empty($mediacount) ? 0 : $mediacount;
        $Page = new \Think\Pagenew($count,$pagecount);
        $mediaary = array();
        $returnary = $ma->where($map)->order('id desc')->limit($Page->firstRow,$Page->listRows)->select(); 
        foreach($returnary as $key=>$val){
            $id = $val['id'];
            $available = $val['recharge']-$val['balance'];
            $val['available'] = priceConversion($available,'',true);
            $val['recharge'] = priceConversion($val['recharge'],'',true);
            $val['balance'] = priceConversion($val['balance'],'',true);
        
             if($val['createtime']!=0 && !empty($val['createtime']))
                $val['createtime'] =   date('Y-m-d H:i:s',$val['createtime']);
             if($val['lastlogintime']!=0 && !empty($val['lastlogintime']))
                $val['lastlogintime'] =  date('Y-m-d H:i:s',$val['lastlogintime']);
             $mediaary[] = $val; 
        }

        $Page->parameter["username"] = urlencode($username);
        $Page->parameter["mobile"] = $mobile;
        $Page->parameter["status"] = $status;
        
        $pagehtml = $Page->showtwo();
        $this->assign('datalist', $mediaary);
        $this->assign('pagehtml',$pagehtml);
        $this->assign('username',$username);
        $this->assign('mobile',$mobile);
        $this->assign('status',$status);
        $this->assign('total',$total);
        $this->assign('funcdesc',"自媒体主列表");
        $this->display();
    }
    
    //自媒体主全部收入
    private function userSumMoney($payeeuid){
        //$sql = "SELECT SUM(price) AS moneySum from {$this->_table}";
       //$sql.=" WHERE payeeuid={$payeeuid} AND status=4 and paytype != 0";
       return 0;
    }
    
    //自媒体主未结算收入
    private function wjsMonsySum($payeeuid){
        //$sql = "SELECT SUM(price) AS wjsMoneySum from {$this->_table}";
        //$sql.=" WHERE payeeuid={$payeeuid} AND status=4 AND paystatus!=2";
       return 0;
    }
    
    //获取自媒体主详细信息（包含他的微信、微博账号数量）
    public function mediaInfo(){
      $payeeuid = I("post.userid");
      $ma=M('user',$this->tablePrefix);
      $mediainfo = $ma->where("id=$payeeuid")->find();
      if($mediainfo['agt_priv']==0){
        $media=M('media_user',$this->tablePrefix);
        $mcount = $media->where("uid=$payeeuid")->count();
        $mediainfo['media_count'] = intval($mcount);
        echo json_encode($mediainfo); 
      }else{
        echo json_encode('fail');
      }
    } 
    
    //删除自媒体主（同时删除他的公众号、朋友圈等）
    public function delmedia(){
        $current_user = $this->baseLoginuser;
        $operateUserId = $current_user['manager_user_id'];
        
        $mediaid = empty(trim(I('request.mediaid'))) ? 0 : I('request.mediaid');
        $ma=M('user',$this->tablePrefix);
        $data['status'] = 1;
        $re = $ma->where("id=$mediaid")->save($data);
        $sql = $ma->getlastsql();
        if($re){
           $desc = "注销自媒体用户";
           funOperateLog($operateUserId, $sql, $desc); 
           $ma = M('media_user',$this->tablePrefix); 
           $reary = $ma->where("uid=$mediaid")->select();
           foreach($reary as $key=>$val){
                $id = $val['id'];  
                $map['platstatus']=99;
                $map['platid']=md5($val['id']);
                $map['accesstoken']=json_encode(array('uid' => $val['uid'],'platid' => $val['platid'],'manager_user_id' =>$operateUserId,'time' => time()));
                
                $ma->where("id=$id")->save($map);
                
                $sql = $ma->getlastsql();
                $desc = "注销公众号、朋友圈";
                funOperateLog($operateUserId, $sql, $desc); 
           }
           exit( json_encode(array('err'=>0,'msg'=>'删除成功！')));
        }else{
           exit( json_encode(array('err'=>1,'msg'=>'删除失败！')));
        }
    }
    
    //修改自媒体主信息
    public function updatemedia(){
        $iId = intval(I('post.id'));
        $sIdcard = htmlspecialchars(urldecode(trim(I('post.idcard'))));
        $sRealname = htmlspecialchars(urldecode(trim(I('post.realname'))));
        if (!$iId || !in_array(strlen($sIdcard), array(15, 18)))    exit();
        
        $ma=M('user',$this->tablePrefix);
        $data['idcard'] = $sIdcard;
        $data['realname'] = $sRealname;
        $re = $ma->where("id=$iId")->save($data);
        if($re){
          $current_user = $this->baseLoginuser;
          $operateUserId = $current_user['manager_user_id'];
          $sql = $ma->getlastsql();
          $desc = "用户信息修改成功！";
          funOperateLog($operateUserId, $sql, $desc);
           exit( json_encode(array('err'=>0,'msg'=>'用户信息修改成功！'))); 
        }else{
           exit( json_encode(array('err'=>1,'msg'=>'用户信息修改失败！'))); 
        }
    }
   
   //公众号列表
   public function mediaBindList(){
        $pagecount = 20;       
        $ma=M('media_user',$this->tablePrefix);
        $map['uid']  = intval($_GET['uid']);
        $uname =  trim(urldecode($_GET['uname']));
        $aplattype = C('PLATTYPE');
        $mediacount = $ma->where($map)->count();
        $count = empty($mediacount) ? 0 : $mediacount;
        $Page = new \Think\Pagenew($count,$pagecount);

        $returnary = $ma->where($map)->order('id desc')->limit($Page->firstRow,$Page->listRows)->select(); 
        $mediaary = array();
        foreach($returnary as $key=>$val){
            $val['plattypename'] = $aplattype[$val['plattype']];
            $nick = $val['nick'];
            $nick = $nick==''?$val['name']:$nick;
            $nick = $nick==''?'-----':$nick;
            $val['nick'] = $nick;
            $val['platstatus'] = $val['platstatus']==0?'正常':'异常';
            $mediaary[]=$val;
        }
        $Page->parameter["uname"] = urlencode($uname);
        $Page->parameter["uid"] = intval($_GET['uid']);
        
        $pagehtml = $Page->showtwo();
        $this->assign('datalist', $mediaary);
        $this->assign('pagehtml',$pagehtml);
        $this->assign('username',$uname);
        $this->assign('aPlatType',$aplattype); 
        $this->assign('funcdesc',"自媒体主账号列表");
        $this->display();
   }
   
   //注销自媒体主号
   public function mediauser(){
        $pagecount = 20;
        $aplattype = C('PLATTYPE');
        $verified = C('VERIFIED');
        $qqverified = C('QQVERIFIED'); 
        $wxverified = C('WEIXINVERIFIED'); 
        $iPlatType = '';
        $ma=M('media_user',$this->tablePrefix);
        $sKeyword = I('post.kwd')?htmlspecialchars(trim(I('post.kwd')), ENT_QUOTES):'';
        $aList = array();
        if ($sKeyword) {
            $iPlatType = (I('post.plattype') !== '')?intval(I('post.plattype')):'';
            $sWhere = "platstatus != 99 ";
            $sWhere .= " and (platid like '%".$sKeyword."%' or nick like '%".$sKeyword."%' or name like '%".$sKeyword."%')";
            if ($iPlatType !== '') {
                $sWhere .= " and plattype=".$iPlatType;
            };
            
            $mediacount = $ma->where($sWhere)->count();
            $count = empty($mediacount) ? 0 : $mediacount;
            $Page = new \Think\Pagenew($count,$pagecount);
            $aList = $ma->where($sWhere)->order('id desc')->limit($Page->firstRow,$Page->listRows)->select();
            
            $Page->parameter["plattype"] = $iPlatType;
            $Page->parameter["kwd"] = urlencode($sKeyword);
            
            $mediaary = array();
            foreach($aList as $key=>$val){
                $val['plattypename'] = $aplattype[$val['plattype']];
                $nick = $val['nick'];
                $nick = $nick==''?$val['name']:$nick;
                $nick = $nick==''?'-----':$nick;
                $val['nick'] = $nick;
                $val['platstatus'] = $val['platstatus']==0?'正常':'异常';
            
                if($val['verified']){
                    $verified = $val['verified']; 
                    if($val['plattype']==0){
                        $val['leveltype'] = $verified['$verified'];
                    }
                    if($val['plattype']==1){
                        $val['leveltype'] = $qqverified['$verified']; 
                    }
                    if($val['plattype']==2){
                        $val['leveltype'] = $wxverified['$verified']; 
                    }
                }else{
                  $val['leveltype'] = "---";
                }
                if($val['createtime']>0){
                    $val['createtime'] =  date('Y-m-d H:i:s',$val['createtime']);
                }else{
                    $val['createtime'] = "----";
                }
                $mediaary[]=$val;
            }
            $pagehtml = $Page->showtwo();
        }
        $this->assign('datalist', $mediaary);
        $this->assign('pagehtml',$pagehtml);
        $this->assign('sKeyword',$sKeyword);
        $this->assign('plattype',$iPlatType);
        $this->assign('aplattype',$aplattype);
        $this->assign('funcdesc',"自媒体账号注销(微信公众号、朋友圈、微博账号等)");
        $this->display();
   }
   
   //注销自媒主账号
   public function destroy_mediauser(){
        $current_user = $this->baseLoginuser;
        $operateUserId = $current_user['manager_user_id'];
        
        $ma=M('media_user',$this->tablePrefix);
        $iMediaId = intval(I('get.id'));
        $aRow = $ma->where("id=$iMediaId")->find();
        if(empty($aRow))exit();
        $map['id'] = $iMediaId;
        $map['uid'] = '';
        $map['platstatus'] = 99;
        $map['platid'] = md5($iMediaId); 
        $map['accesstoken'] = json_encode(array('uid' => $aRow['uid'],'platid' => $aRow['platid'],'manager_user_id' => $operateUserId,'time' => time()));
        $ma->where("id=$iMediaId")->save($map);
        $sql = $ma->getlastsql();
        $desc = "注销自媒主账号";
        funOperateLog($operateUserId, $sql, $desc);
        exit;
   } 
}