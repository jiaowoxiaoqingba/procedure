<?php
/*
* desc: 财务管理：后台用户充值、充值列表查询、自媒体主提现
* auth:
* createtime:
*/
namespace Admin\Controller;
use Think\Controller;
class FinanceController extends BaseController{
    private $ownerProcess = "";  //获取广告主信息
    private $rechargeProcess = ""; //获取充值信息
    private $userProcess = ""; //获取自媒体主信息
    private $cManager = ""; //管理员的信息
    private $cFinance = ""; //自媒体财务结算
    
    private $table_prefix = "";
        //初始化
    public function _initialize() {
       parent::_initialize();
       $this->ownerProcess = A('Publicpart/Ownerprocess');
       $this->rechargeProcess = A('Publicpart/Rechargeprocess');
       $this->userProcess = A('Publicpart/Userprocess');
       $this->cFinance = A('Publicpart/Financeprocess');
       $this->cManager = $this->baseLoginuser;
       
       $this->table_prefix =  C("DB_OTHERPREFIX");
    }
    
    //充值列表
	public function index(){}
    //管理员给广告主充值
    public function managerRecharge(){
        $username = I('POST.user_name');
        $identity_ary = array(0=>'个人',1=>'企业');
        $type_ary = array(0=>'独立广告主',1=>'代理商',2=>'代理商的广告主');
        $status_ary = array(0=>'正常',1=>'异常',2=>'只能登录查询');
        
        $platform_user_list = array();
        if(!empty($username)){
            $platform_user_list = $this->ownerProcess->getOwnerFusername($username);
            if(!empty($platform_user_list)){
                foreach($platform_user_list as &$val){
                    $val['typename'] = $identity_ary[$val['type']];
                    $val['usertypename'] = $type_ary[$val['usertype']];
                    $val['statusname'] = $status_ary[$val['status']];
                }
            }
        }
        
        $this->assign("funcdesc","账号充值");
        $this->assign("platform_user_list",$platform_user_list);
        $this->display(); 
    }

    //录入充值金额
    public function rechargeAmount(){
        $platform_user_id = I('id');
        $platform_user_id = is_numeric($platform_user_id)?$platform_user_id:0;
        $platform_user_list = $this->ownerProcess->getOneOwnerinfo($platform_user_id);
        $nick="";
        if(count($platform_user_list)>0){
            $platform_user = $platform_user_list;
        }

        $this->assign("funcdesc","填写充值金额");
        $this->assign("platform_user",$platform_user);
        $this->display(); 
    }

    //正式提交充值
    public function recharge(){
        $manager_user_id = $this->cManager['manager_user_id'];
        $id = I('platform_user_id');
        $num = is_numeric(I('reset_recharge'))?I('reset_recharge'):0;
        $num = $num*100;
        if($num<=0){
             exit( json_encode(array('err'=>1,'msg'=>'充值失败！')));  
        }
        $res = $this->rechargeProcess->updatePlatformUserRecharge($manager_user_id,$id,$num);
        if($res){
            $this->rechargeProcess->getRechargeSum($manager_user_id, $id);
            exit( json_encode(array('err'=>0,'msg'=>'充值成功！')));
         }else{
            exit( json_encode(array('err'=>1,'msg'=>'充值失败！')));
        }
    }

	//充值记录
	public function record(){
        $pageNum = 10;
        $map = array();
        $pagehtml = "";
        $user_typelist = array(0=>'独立广告主',1=>'代理商',2=>'代理商的广告主');
        $operate_typelist = array(0=>'平台用户自己充值',1=>'后台给用户充值');
        $statuslist = array(0=>'开始充值',1=>'开始调用接口',2=>'充值成功',3=>'调用接口充值失败',4=>'账号加钱失败',5=>'账号加钱成功');

        $starttime = empty(I('starttime'))?'':strtotime(I('starttime'));
        $endtime = empty(I('endtime'))?'':strtotime(I('endtime'));
        $agt_priv = I('agt_priv');
        $operate_type = I('operate_type');
        $status = I('status');  
        $uname = empty(trim(I('uname'))) ? '' : I('uname');
        
        //获取广告主
        $ownerfield = "id,username,contact,realname,mobile";
        $ownerlist = $this->ownerProcess->getOwnerFusername($uname,$ownerfield);
        if($ownerlist){
            $userin = array();
            foreach($ownerlist as $user){
             $userin[] = $user['id'];
            }
            $map['userid'] = array("IN",$userin);
        }
        if($agt_priv != ''){
            $map['usertype'] = $agt_priv; 
        }
        if($operate_type != ''){
            $map['operate_type'] = $operate_type; 
        }
        if($status != ''){
            $map['status'] = $status; 
        }
        if($starttime && $endtime){
           $map['createtime'] = array("BETWEEN","$starttime,$endtime");
        }
        
        $nototal = $this->rechargeProcess->sumRecharge($map);
        $nototal = priceConversion($nototal);
        
        $rechargecount = $this->rechargeProcess->getRechargeNum($map);
        $count = empty($rechargecount) ? 0 : $rechargecount;
        $Page = new \Think\Pagenew($count,$pageNum);
        
        $rechargelimit = $Page->firstRow.",".$Page->listRows;
        $data = $this->rechargeProcess->getRechargeList($map,$rechargelimit);
        $owner = M('user');
       	foreach ($data as &$v) {
            if($ownerlist){
                 $v['username'] = $ownerlist[$v['userid']]['username'];
                 $v['contact'] = $ownerlist[$v['userid']]['realname'];
                 $v['mobile'] = $ownerlist[$v['userid']]['mobile']; 
            }else{
                 $ownerinfo = $owner -> field('username,contact,mobile,realname') -> where("id='{$v['userid']}'") -> find();
                 $v['username'] = $ownerinfo['username'];
                 $v['contact'] = $ownerinfo['realname'];
                 $v['mobile'] = $ownerinfo['mobile'];
            }
            $manager_user_id = $v['manager_user_id'];
            if($manager_user_id){
       		    $info = $this->userProcess->getUserinfo($manager_user_id,"username");
       		    $v['user_name'] = $info['username'];
            }else{
                $v['user_name'] = "---";
            }
            $v['usertypename'] = $user_typelist[$v['usertype']];
            $v['statusname'] = $statuslist[$v['status']]; 
            $v['operate_typename'] = $operate_typelist[$v['operate_type']]; 
       		$v['createtime'] = date("Y-m-d H:i:s",$v['createtime']);
            $v['fee'] = priceConversion($v['fee']);
       	}

         $Page->parameter["starttime"] = date('Y-m-dH:i:s',$starttime);
         $Page->parameter["endtime"] = date('Y-m-dH:i:s',$endtime);
         $Page->parameter["agt_priv"] = urlencode($agt_priv);
         $Page->parameter["operate_type"] = urlencode($operate_type);
         $Page->parameter["status"] = urlencode($status);
       	 $Page->parameter["uname"] = urlencode($uname);
	     $pagehtml = $Page->showtwo();
         
	     $this -> assign('records', $data);
	     $this -> assign('pagehtml',$pagehtml);
         $this -> assign('starttime',date('Y-m-d H:i:s',$starttime));
         $this -> assign('endtime',date('Y-m-d H:i:s',$endtime));	    
	     $this -> assign('status',$status);
	     $this -> assign('uname',$uname);
         $this -> assign('agt_priv',$agt_priv);
         $this -> assign('operate_type',$operate_type);
	     $this -> assign('recharge_total',$nototal);
	     $this -> assign('funcdesc',"充值记录");	 
	     $this -> display();
	}

	public function recordsinfo(){
		$id = I('id');
		$users = M("user");
		$usersinfo = M("userinfo");
		$data = $users -> field("username,contact,mobile,email,type,status,createtime,lastlogintime,ip,realname,businessid") -> where("id='{$id}'") -> find();
		$datainfo = $usersinfo -> field("address,company") -> where("id='{$id}'") -> find();
		$data['address'] = $datainfo['address'];
		$data['company'] = $datainfo['company'];
		$typelist = array(0=>'个人',1=>'企业');
		$statuslist = array(0=>'正常',1=>'异常');
		$data['createtime'] = date("Y-m-d H:i:s",$data['createtime']);
		$data['lastlogintime'] = date("Y-m-d H:i:s",$data['lastlogintime']);
		$data['status'] = $statuslist[$data['status']];
		$data['typename'] = $typelist[$data['type']];
		echo json_encode($data);
		exit;
	}

    //用户的基本信息
    public function userinfo(){
        $id = I("id");
        $users = M('user');
        $data = $users -> field("username,contact,mobile,email,recharge,freeze,balance") -> where("id='{$id}'") -> find();
        echo json_encode($data);
        exit;
    }
    
	//提现申请
	public function withdrawals(){
        $pageNum = 20;
		$mobile = I('mobile');
        $tmp = empty($_GET['tmp']) ? 'wait' : $_GET['tmp'];
        $starttime = empty(I('starttime'))?'': I('starttime');
        $endtime = empty(I('endtime'))?'': I('endtime');

        $manager=M('manager_user',$this->tablePrefix);
        $user = M("user",$this->tablePrefix);
		$wallet = M("user_wallet",$this->tablePrefix);
        switch($tmp){
            case 'wait':
                $auditstatus = 1;
                break;
            case 'pass':
                $auditstatus = 2;
                break;
            case 'refuse':
                $auditstatus = 3;
                break;
            case 'finish':
                $paystatus = 'S';
                break;
            case 'fail':
                $paystatus = 'F';
                break;
            case 'pay' :
                $paystatus = 'O';
                break;
        }
		if($mobile){
			$mobileinfo['mobile'] = $mobile;
			$phone = $user->field('id')->where($mobileinfo)->select();
			if($phone){
				$map['payeeuid'] = $phone['id'];
			}else{
				$map['payeeuid'] = 0;
			}
		}
        if($starttime && $endtime){
              $map['paydate'] = array("BETWEEN","$starttime,$endtime");
        }
        if(!empty($paystatus)){
              $map['paystatus'] = array("eq",$paystatus);
        }else{
              $map['auditstatus'] = array("eq",$auditstatus);
              $map['paystatus'] = '';
        }

       	//条件下的金额总数
        $totail = $this->cFinance->getPayeeAndUserCount($map);
        $recharge_total = $totail['fee'];
        $recharge_total = priceConversion($recharge_total,'',true);
        $count = $totail['num'];
       	$count = empty($count) ? 0 : $count;
       	$Page = new \Think\Pagenew($count,$pageNum);
        
        $orderlimit = $Page->firstRow.",".$Page->listRows;
       	$list = $this->cFinance->getPayeeAndUserList($map,$orderlimit);
       	foreach($list as &$v) {
       		$contacts = $user->field("username,alipay_name,alipay")->where("id='{$v['payeeuid']}'")->find();
       		$v['mobile'] = $contacts['username'];
            $info = $manager->field("username")-> where("id='{$v['managerid']}'") ->find();
            $v['user_name'] = $info['username'];
            $v['account'] = $contacts['alipay'];
            $v['account_name'] = $contacts['alipay_name'];
            $v['operate_time'] = date("Y-m-d H:i:s",$v['operate_time']);
            $v['fee'] = priceConversion($v['fee'],'',true);
       	}

        $Page->parameter["tmp"] = urlencode($tmp);
        $Page->parameter["paystatus"] = urlencode($paystatus);
       	$Page->parameter["auditstatus"] = urlencode($auditstatus);
       	$Page->parameter["mobile"] = urlencode($mobile);
        $Page->parameter["starttime"] = $starttime;
        $Page->parameter["endtime"] = $endtime;
        $pagehtml = $Page->showtwo();
        
	    $this -> assign('records', $list);;
        $this -> assign('tmp', $tmp);
	    $this -> assign('pagehtml',$pagehtml);
        $this -> assign('starttime',$starttime);
        $this -> assign('endtime',$endtime);
	    $this -> assign('mobile',$mobile);
	    $this -> assign('recharge_total',$recharge_total);
        $this -> assign('count',$count);
		$this -> assign('funcdesc','待审核的提现申请');
		$this -> display($tmp);
	}

	//ajax请求的结果,通过的请求    以及   全部通过
	public function pass(){
		$rid = trim(I('rid'));
		$rid = explode(",",$rid);
		$map['id'] = array("IN",$rid);
		$data['auditstatus'] = 2;
        //通过的操作时间
        $data['operate_time'] = time();
        $data['managerid'] = $this->cManager['manager_user_id'];
		$payee = M("pay_payee",$this->tablePrefix);
        $re = $payee->where($map)->save($data);
        if($re){
            exit( json_encode(array('err'=>0,'msg'=>'审核通过！')));
        }else{
          exit( json_encode(array('err'=>1,'msg'=>'申请结算更新失败!')));
        }
	}

	//ajax请求的结果,拒绝的请求    以及  全部拒绝
	public function unpass(){
		$rid = trim(I('rid'));
		$rid = explode(",",$rid);
		$map['id'] = array("IN",$rid);
		$comment = empty(I('reason')) ? '拒绝原因为空' : I('reason');
		$data['comment'] = $comment;
		$data['auditstatus'] = 3;
        $data['paystatus'] = '';
        $data['managerid'] = $this->cManager['manager_user_id'];
        //拒绝的话，操作时间直接未最后操作时间
        $data['operate_time'] = time();
		$payee = M("pay_payee",$this->tablePrefix);
        $re = $payee->where($map)->save($data);
        if($re){
            exit( json_encode(array('err'=>0,'msg'=>'拒绝成功')));
        }else{
            exit( json_encode(array('err'=>1,'msg'=>'拒绝失败')));
        }
	}

	//ajax请求的结果,待支付的请求
	public function waitpay(){
		$rid = trim(I('rid'));
		$rid = explode(",",$rid);
		$map['id'] = array("IN",$rid);
		$data['paystatus'] = 'O';
		$data['auditstatus'] = 2;
            $data['managerid'] = $this->cManager['manager_user_id'];
		$payee = M("pay_payee",$this->tablePrefix);
          $re = $payee->where($map)->save($data);
          if($re){
             exit( json_encode(array('err'=>0,'msg'=>'转变成功')));
          }else{
             exit( json_encode(array('err'=>1,'msg'=>'转变失败')));
          }
	}
    
    //结款给自媒体
    public function alipayPayee(){
        $manager_user_id = $this->cManager['manager_user_id']; 
        $mobile = empty(I('mobile'))?'':I('mobile');
        $starttime = empty(I('starttime'))?'': I('starttime');
        $endtime = empty(I('endtime'))?'': I('endtime');

        $user = M("user",$this->tablePrefix);
        $wallet = M("user_wallet",$this->tablePrefix);
        if($mobile){
            $mobileinfo['mobile'] = $mobile;
            $phone = $user->field('id')->where($mobileinfo)->select();
            if($phone){
                $map['payeeuid'] = $phone['id'];
            }else{
                $map['payeeuid'] = 0;
            }
        }
        if($starttime && $endtime){
              $map['paydate'] = array("BETWEEN","$starttime,$endtime");
        }
        $map['auditstatus'] = array("eq",2);
        $map['paystatus'] = 'O';
        $list = $this->cFinance->getPayeeList($map,'0,1000');
        
        /*************自媒体结款****支付宝**start********/
        require_once("/application/publicpart/controller/lib/alipay.config.php");
        $notify_url = C("WEB_URL")."/admin/notifypayee/index.php";
        $email = '13121922763@sina.cn';
        $account_name = '北京天下秀科技有限公司';
        $pay_date = date('Ymd');
        $batch_no = $pay_date.'090'.rand(1000,9000);
        $batch_fee = 0;
        $batch_num = 0;
        foreach($list as $row ){ // 批付
            if($row['paystatus'] == 'O'){
                $payee_fee = sprintf('%.2f', priceConversion($row['fee'],'',true));
                $batch_fee += $payee_fee;
                $batch_num += 1;
                $trade_no = $batch_no.rand(1000000,9000000);
                $this->cFinance->setPayeeTradeno($row['id'],$trade_no,$manager_user_id);
                $payee_account = $row['alipay'];
                $payee_realname = $row['alipay_name'];
                $detail_data .= $trade_no.'^'.$payee_account.'^'.$payee_realname.'^'.$payee_fee.'^';
                $detail_data .= 'WEIQ任务广场';
                $detail_data .= '|';
            }
        }
        $detail_data = substr($detail_data,0,-1);
        $parameter = array(
                "service" => "batch_trans_notify",
                "partner" => trim($alipay_config['partner']),
                "notify_url"    => $notify_url,
                "email"    => $email,
                "account_name"    => $account_name,
                "pay_date"    => $pay_date,
                "batch_no"    => $batch_no,
                "batch_fee"    => $batch_fee,
                "batch_num"    => $batch_num,
                "detail_data"    => $detail_data,
                "_input_charset"    => trim(strtolower($alipay_config['input_charset']))
        );
        $alipaySubmit = new AlipaysubmitController($alipay_config);
        $html_text = $alipaySubmit->buildRequestForm($parameter,"post", "确认");
        $this->assign("html_text",$html_text);
        $this->display(); 
        //echo $html_text;
        exit;
    }
}