<?php
namespace Admin\Controller;
use Think\Controller;

class RefundController extends BaseController{

	//退款列表显示页面
	public function index(){
		$username = I('username');
       	$usertype = I('usertype');
       	$users = M('user');
       	if($username){
       		$map['username'] = array("LIKE","%{$username}%");
       	}
       	if($usertype !== '') {
       		$map['usertype'] = $usertype;
       	}

       	$userscount = $users -> where($map) -> count();
       	$count = empty($userscount) ? 0 : $userscount;
       	$Page = new \Think\Pagenew($count,10);
       	$data = $users -> where($map) -> limit($Page->firstRow.",".$Page->listRows)->order('id desc')-> select();
       	foreach ($data as &$v) {
       		$v['freefund'] = intval($v['balance']) - intval($v['freeze']);
       		$v['freefund'] = empty(conversion($v['freefund'],1)) ? '0' : conversion($v['freefund'],1);
       		$v['balance'] = empty(conversion($v['balance'],1)) ? '0' : conversion($v['balance'],1);
       		$v['freeze'] = empty(conversion($v['freeze'],1)) ? '0' : conversion($v['freeze'],1);
       	}
       	
        $Page->parameter["username"] = urlencode($username);
        $Page->parameter["usertype"] = urlencode($usertype);
	    $pagehtml = $Page->showtwo();
	    $usertypelist = array(0=>'独立广告主', 1=>'代理商', 2=>'代理商的广告主');

	    $this -> assign('usertypelist',$usertypelist);
	    $this -> assign('usersinfo', $data);
	    $this -> assign('pagehtml',$pagehtml);	    
	    $this -> assign('usertype',$usertype);
	    $this -> assign('username',$username);
		$this -> assign("funcdesc","退款");
		$this -> display();
	}

	//请求用户的详细信息
	public function userinfo(){
		$id = I('id');
		$users = M('user');
		$data = $users -> field('id,username,balance,freeze,usertype') -> where("id='{$id}'") -> find();
		$data['freefund'] = $data['balance'] - $data['freeze'];
		$data['freefund'] = empty(conversion($data['freefund'],1)) ? '0' : conversion($data['freefund'],1);
		$usertypelist = array(0=>'独立广告主', 1=>'代理商', 2=>'代理商的广告主');
		$data['type'] = $usertypelist[$data['usertype']];
		echo json_encode($data);
		exit;
	}

	//执行退款添加
	public function refundinsert(){
		$id = I('id');
		$fee = I('fee');
		$tradeno = I('tradeno');
		$usertype = I('usertype');
		$users = M('user');
        $m = M();
		$refundlog = M('refundlog');	    //退款表
		$ad_feeflow = M('ad_feeflow');	    //广告主流水表
		//后台操作的id
		$manager = $this -> baseLoginuser;
		$manager_user_id = $manager['manager_user_id'];

		if(!isset($id)) {
			exit(json_encode(array('err'=>1,'msg'=>'非法参数')));
		}
		if(!$userinfo = $users -> where("id='{$id}'") -> find()){
			exit(json_encode(array('err'=>1,'msg'=>'用户信息不存在')));
		}
		if(!$tradeno){
			exit(json_encode(array('err'=>1,'msg'=>'订单号不能为空')));
		}
		$freefund = intval($userinfo['balance']) - intval($userinfo['freeze']);
        $fee = empty($fee)?0:$fee;
        $fee = $fee*100;
        
		if($fee){
			if(!is_numeric($fee) && $fee < 1){
				exit(json_encode(array('err'=>1,'msg'=>'退款金额错误')));
			} else if ($fee > $freefund){
				exit(json_encode(array('err'=>1,'msg'=>'退款金额不能超过可用余额')));
			}

			$param = array('userid'=>$userinfo['id'],
				'role'=>$userinfo['usertype'],
				'plattype'=>1,					//支付平台的填写，还没有确定
				'tradeno'=>trim($tradeno),
				'balance'=>$userinfo['balance'],     //操作前的余额
				'fee'=>intval($fee),
				'operator'=>$manager_user_id,
				'createtime'=>time());

			//若是存在usertype  以及 usertype=2为代理商的广告主，则广告主退款给代理商
			if(isset($usertype) && $usertype == 2){
				//获取代理商信息
				if(!$agentinfo = $users -> where("id='{$userinfo['agentid']}'") ->find()){
					exit(json_encode(array('err'=>1,'msg'=>'代理商信息不存在')));
				}
				$param['touserid'] = $userinfo['agentid'];
				$param['type'] = 3;
			}else{	//广告主或者代理商只能自己退款给自己
				$param['touserid'] = $userinfo['id'];
				$param['type'] = $userinfo['usertype'] == 1 ? 2 : 1;
				
				//调用退款接口
				if(false) { //调用接口退款失败
					$param['status'] = 2;
				}
			}
			if(!isset($param['status'])){
				//开启事务处理
				$m -> startTrans();
				//修改余额
				$data1 = array('balance'=>$userinfo['balance'] - intval($fee));
				if(!$data1_Message = $users -> where("id='{$userinfo['id']}'") -> save($data1)){
					$param['status'] = 3;  //帐户减钱失败
				}else{
					$param['status'] = 1;  //退款成功
			
					if($param['type'] == 3) {  //广告主退款给代理商
						$data2 = array('balance'=>$userinfo['balance'] + intval($fee));
						$users -> where("id='{$agentinfo['id']}'") ->save($data2);
					}
					//记录广告主/代理商金额流水
					$flowData = array(
						'userid'=>$userinfo['id'],
						'agt_priv'=>$userinfo['usertype'],
						'type'=>$param['type']==3?6:8,
						'fee'=>intval($fee),
						'time'=>$param['createtime'],
						'touserid'=>$param['touserid'],   // 代理商
		                'fromuserid' => $userinfo['id'],   // 广告主
						'opplat'=>1,
						'operator'=>$manager_user_id,
		                'ordercode' => $param['tradeno'], // 退款编号
		                'recharge'=>$userinfo['recharge'], // 当前累计充值总额
		                'balance'=>$userinfo['balance'], // 当前余额
		                'freeze'=>$userinfo['freeze'], // 当前冻结额
		                'descript'=> $param['type']==3 ? $userinfo['username'].'(广告主)退款给'.$agentinfo['username'].'(代理商)' : '用户自己退款'
		            );
					$ret1 = $ad_feeflow->add($flowData);
					if($param['type'] == 3 && $ret1)   //广告主退款给代理商
					{
						$flowData['userid'] = $param['touserid'];
						$flowData['agt_priv'] = 2;  //代理商
						$flowData['type'] = 7;      //代理商转入 即广告主退款给代理商
                        $flowData['fee'] = intval($fee);
                        $flowData['time'] = $param['createtime'];
						$flowData['touserid'] = $agentinfo['id'];   // 代理商
						$flowData['fromuserid'] = $userinfo['id'];   // 广告主
                        $flowData['opplat'] = 1;
                        $flowData['operator'] = $manager_user_id;
                        $flowData['ordercode'] = $param['tradeno']; //退款编号
		                $flowData['recharge'] = $agentinfo['recharge']; // 当前累计充值总额
		                $flowData['balance'] = $agentinfo['balance'];   // 当前余额
		               	$flowData['freeze'] = $agentinfo['freeze'];     // 当前冻结额
		                $flowData['descript'] = $userinfo['username'].'(广告主)转入'.$agentinfo['username'].'(代理商)';
						$ret2 = $ad_feeflow->add($flowData);
					}
				}
				if($ret1){
					$m -> commit();	//成功则提交
				}else{
					$m -> rollback();	//不成功，则回滚
					exit(json_encode(array('err'=>1,'msg'=>'退款操作失败')));
				}
			}
			//记录退款日志
			$res = $refundlog->add($param);
			if($param['status'] == 2){
				exit(json_encode(array('err'=>1,'msg'=>'调用接口退款失败')));
			}else{
				exit(json_encode(array('err'=>0,'msg'=>'退款记录成功')));
			}
		}else{
			exit(json_encode(array('err'=>1,'msg'=>'退款金额不能为空')));
		}
	}

	//退款记录页面
	public function recordlist(){
		$plattype = I('plattype');
		$status = I('status');
		$username = I('username');
       	$role = I('role');
        $starttime = empty(I('starttime'))?'':strtotime(I('starttime'));
      	$endtime = empty(I('endtime'))?'':strtotime(I('endtime'));
       	$users = M('user');
       	$refunds = M('refundlog');
       	$manager=M('manager_user',$this->tablePrefix);
       	if($username){
       		$uinfo['username'] = array("LIKE","%{$username}%");
                  $usersinfo = $users -> field('id') -> where($uinfo) -> select();
       	      if($usersinfo){
	                  $userin = array();
	                  foreach($usersinfo as $user){
	       	      	$userin[] = $user['id'];	       
	                   }
		     		$map['userid'] = array("IN",$userin);
	           	}else{
		    		$map['userid'] = 0;
	             }
       	}
       	if($role !== '') {
       		$map['role'] = $role;
       	}
       	if($plattype){
       		$map['plattype'] = $plattype;
       	}
       	if($status){
       		$map['status'] = $status;
       	}
       	if($starttime && $endtime){
           		$map['createtime'] = array("BETWEEN","$starttime,$endtime");
        	}

       	$refundscount = $refunds -> where($map) -> count();
       	$count = empty($refundscount) ? 0 : $refundscount;
       	$Page = new \Think\Pagenew($count,10);

       	$data = $refunds -> where($map) -> limit($Page->firstRow.",".$Page->listRows) -> select();
       	foreach ($data as &$v) {
       		$data_user = $users -> field('username,contact') -> where("id='{$v['userid']}'") -> find();
       		$v['username'] = $data_user['username'];
       		$v['contact'] = $data_user['contact'];
       		$data_manager = $manager -> field('username') -> where("id='{$v['operator']}'") -> find();
       		$v['manager_user'] = $data_manager['username'];
       		$v['createtime'] = date("Y-m-d H:i:s",$v['createtime']);
       		$v['fee'] = conversion($v['fee'],1);
       	}
        	$Page->parameter["starttime"] = date('Y-m-dH:i:s',$starttime);
        	$Page->parameter["endtime"] = date('Y-m-dH:i:s',$endtime);       	
           $Page->parameter["username"] = urlencode($username);
           $Page->parameter["role"] = urlencode($role);
           $Page->parameter["status"] = urlencode($status);
           $Page->parameter["plattype"] = urlencode($plattype);
	     $pagehtml = $Page->showtwo();
	     $rolelist = array(0=>'独立广告主', 1=>'代理商', 2=>'代理商的广告主');
	     $plattypelist = array(1=>'支付宝', 2=>'微信钱包');
	     $statuslist = array(1=>'退款成功', 2=>'调用退款接口失败', 3=>'帐户减钱失败');
	     $this -> assign('rolelist',$rolelist);
	     $this -> assign('plattypelist',$plattypelist);
	     $this -> assign('statuslist',$statuslist);
	     $this -> assign('refunds', $data);
	     $this -> assign('pagehtml',$pagehtml);	    
	     $this -> assign('role',$role);
	     $this -> assign('username',$username);
	     $this -> assign('plattype',$plattype);
	     $this -> assign('status',$status);
        	$this -> assign('starttime',date('Y-m-d H:i:s',$starttime));
       	$this -> assign('endtime',date('Y-m-d H:i:s',$endtime));
		$this -> assign("funcdesc","退款记录");
		$this -> display();
	}
}