<?php
namespace Admin\Controller;
use Think\Controller;

class DrawController extends BaseController{
	/**
	*	提现方式管理
	*/
	public function draw(){
		$tell = I('tell');
       	$mediausers = M('user',$this->tablePrefix);
       	$userwallets = M('user_wallet',$this->tablePrefix);
       	if($tell){
       		$mtell['mobile'] = array("LIKE","%{$tell}%");
       		$tellinfo = $mediausers -> field("id") -> where($mtell) -> select();
       		if($tellinfo){
       			$mobilein = array();
       			foreach ($tellinfo as $val) {
       				$mobilein[] = $val['id'];
       			}
       			$map['id'] = array("IN",$mobilein);
       		}else{
       			$map['id'] = 0;
       		}
       	}

       	$mediauserscount = $mediausers -> where($map) -> count();
       	$count = empty($mediauserscount) ? 0 : $mediauserscount;
       	$Page = new \Think\Pagenew($count,10);

       	$data = $mediausers -> where($map) -> order("id DESC") -> limit($Page->firstRow.",".$Page->listRows) -> select();
       	foreach ($data as &$v) {
       		$walletinfo = $userwallets -> where("userid='{$v['id']}'") -> find();
       		$v['sina_pay'] = '';
       		$v['taobao_pay'] = '';
       		$v['default'] = '';
       		$platType = empty($walletinfo['plattype']) ? 'sina' : 'taobao';
       		$font = empty($walletinfo['plattype']) ? '微钱包' : '支付宝';
       		$v[$platType.'_pay'] = $walletinfo['account'];
       		if(!empty($walletinfo['isdefault'])){
       			$v['default'] = $font;
       		}
       	}
/*       	//plattype 0,",",w.account 1,",",w.isdefault 2)
if(!empty($drawArr)){
    foreach($drawArr as $key=>$val){
        $drawArr[$key]['sina_pay'] = '';
        $drawArr[$key]['taobao_pay'] = '';
        $drawArr[$key]['default'] = '';
        $account = explode(',',$val['account']);
        for($i=0;$i<count($account);$i+=3){
            $pay = $i+1;
            $default = $i+2;
            $platType = empty($account[$i]) ? 'sina' : 'taobao';
            $font = empty($account[$i]) ? '微钱包' : '支付宝';
            $drawArr[$key][$platType.'_pay'] = $account[$pay];
            if(!empty($account[$default])){
                $drawArr[$key]['default'] =  $font;
            }
            unset($pay,$default,$platType,$font);
        }
    }
}
   */    	
           $Page->parameter["tell"] = urlencode($tell);
	     $pagehtml = $Page->showtwo();
	     $this -> assign('draws', $data);
	     $this -> assign('pagehtml',$pagehtml);
	     $this -> assign('tell',$tell);
		$this -> assign("funcdesc","提现方式管理");
		$this -> display();
	}

	/**
	 * 更新提现
	 */
	 public function setDrawAction(){
	 	$mediausers = M('user',$this->tablePrefix);
	 	$userwallets = M('user_wallet',$this->tablePrefix);
	 	$result = array('err'=>0,'msg'=>'');
	 	
	 	$userId = I('userid');
	 	$alipay_name = I('alipay_name');
	 	$taobao_pay = I('taobao_pay');
	 	$plattype = I('plattype');
	 	$user_data['alipay_name'] = $alipay_name;
	 	$user_data['alipay'] = $taobao_pay;
	 	//$user_data['plattype'] = $plattype;
	 	$res = $mediausers -> where("id='{$userId}'") -> save($user_data);
	 	if(!$res){
	 		//$result = array('err'=>1001, 'msg'=>'写入支付宝实名失败');
	 		exit(json_encode(array('err'=>1,'msg'=>'写入支付宝实名失败')));
	 	}

            $userWallet = $this->getUserWallet($userId,1);
            // 新建提现
            if(empty($userWallet)){
                $set = array(
                    'userid' => $userId,
                    'plattype' => 1,
                    'account' => $taobao_pay,
                    'account_name' => $alipay_name,
                    'isdefault' =>1,
                    'status' => 1,
                    'createtime' =>time()
                );
                $setResult = $userwallets -> add($set);
                if(empty($setResult)){
                    //$result = array('err'=>1002, 'msg'=>'写入提现帐号失败');
	 		  exit(json_encode(array('err'=>1,'msg'=>'写入提现帐号失败')));
                }
            }else{  
            	// 修改提现
                $set = array(
                    'account' => $taobao_pay,
                    'account_name' => $alipay_name,
                    'plattype' => 1,
                    'isdefault' => 1,
                );
                $where = 'id='.$userWallet['id'];
                $setResult = $userwallets -> where("id='{$userWallet['id']}'") -> save($set);
                if(empty($setResult)){
                    //$result = array('err'=>1003, 'msg'=>'更新提现帐号失败');
	 		  exit(json_encode(array('err'=>1,'msg'=>'更新提现帐号失败')));
                }
            }
            exit(json_encode(array('err'=>0,'msg'=>'成功')));
		return $result;
	 }

	/**
     * 取现帐号详细
     * @param int $userId
     * @param int $plattype
     */
    public function getUserWallet($userId=0, $plattype=0, $status=''){
        $result = array();
        $userwallets = M('user_wallet',$this->tablePrefix);
        if (!empty($mediaId) || !empty($userId)){
            $map['userid'] = $userId;
            $map['plattype'] = $plattype;
            if($status){
            	$map['status'] = $status;
            }
            $result = $userwallets -> field("plattype,id,userid,account,isdefault") 
            					    -> where($map) -> find();
        }
        return $result;
    }
}