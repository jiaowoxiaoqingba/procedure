<?php
/*
*author:
*date: 
*desc:广告主管理
*/
namespace Admin\Controller;
use Think\Controller;

class OwnerController extends BaseController{
    private $ownerProcess = "";  //获取广告主信息
    private $userProcess = ""; //获取自媒体主信息
    private $feeflowProcess = ""; //获取广告主交易流水
    private $taskProcess = "";  //任务处理核心程序
    private $orderProcess = "";  //订单处理核心程序 
    
    private $cTasktype = "";  //任务类型
    private $cPlattype = "";  //发布任务的平台类型
    private $cTaskstatus = ""; //任务状态
        //初始化
    public function _initialize() {
       parent::_initialize();
       $this->ownerProcess = A('Publicpart/Ownerprocess');
       $this->userProcess = A('Publicpart/Userprocess');
       $this->feeflowProcess = A('Publicpart/Adfeeflowprocess');
       $this->taskProcess = A('Publicpart/Taskprocess');
       $this->orderProcess = A('Publicpart/Orderprocess'); 
       $this->mediaProcess = A('Publicpart/Mediaprocess');
       
       $this->cTasktype = C('TASKTYPE');
       $this->cWxPlattype = C('WXPLATTYPE');
       $this->cTaskstatus = C('TASKSTATUS');
    }
    
	//广告主列表
	public function index(){
        $pagecount = 10;
		$returnary = "";
       	$usertype = I('usertype');
       	$status = I('status');
       	$content = empty(trim(I('content'))) ? '' : I('content');
        $starttime = empty(I('starttime'))?'':strtotime(I('starttime'));
        $endtime = empty(I('endtime'))?'':strtotime(I('endtime'));
       	$users = M('user');
       	if($usertype !== '') {
       		$map['usertype'] = $usertype;
       	}
       	if($status !== '') {
       		$map['status'] = $status;
       	}
       	if($content) {
       		$map['username'] = array("LIKE","%{$content}%");
       	}
        if($starttime && $endtime){
           $map['createtime'] = array("BETWEEN","$starttime,$endtime");
        }
       	$userscount = $users -> where($map) -> count();
       	$count = empty($userscount) ? 0 : $userscount;
       	$Page = new \Think\Pagenew($count,$pagecount);
       	$data = $users -> where($map) -> limit($Page->firstRow.",".$Page->listRows) -> select();
        foreach($data as $key=>$val){
            $id = $val['id'];
            $val['balance'] = priceConversion($val['balance'],'',true);
        
             if($val['createtime']!=0 && !empty($val['createtime']))
                $val['createtime'] =   date('Y-m-d H:i:s',$val['createtime']);
             if($val['lastlogintime']!=0 && !empty($val['lastlogintime']))
                $val['lastlogintime'] =  date('Y-m-d H:i:s',$val['lastlogintime']);
             $returnary[] = $val; 
        }
        
        $Page->parameter["starttime"] = date('Y-m-dH:i:s',$starttime);
        $Page->parameter["endtime"] = date('Y-m-dH:i:s',$endtime);
       	$Page->parameter["content"] = urlencode($content);
        $Page->parameter["usertype"] = $usertype;
        $Page->parameter["status"] = $status;
	    $pagehtml = $Page->showtwo();
        
	    $typelist = array(0=>'独立广告主',1=>'代理商',2=>'代理商的广告主');
	    $statelist = C('OWNER_STATUS');
	    $this -> assign('typelist',$typelist);
	    $this -> assign('statelist',$statelist);
	    $this -> assign('users', $returnary);
	    $this -> assign('pagehtml',$pagehtml);
	    $this -> assign('usertype',$usertype);
	    $this -> assign('status',$status);
	    $this -> assign('content',$content);
        $this -> assign('starttime',date('Y-m-d H:i:s',$starttime));
        $this -> assign('endtime',date('Y-m-d H:i:s',$endtime));
	    $this -> assign('funcdesc',"广告主列表");
	    $this->display();
	}

    //获取广告主的详细信息
	public function userinfo(){
        $returnary = "";
        
		$id = I('post.id');
		$users = M("user");
		$person = $users-> where("id='{$id}'") -> find();
		$info = M("userinfo");
		$data = $info->where("userid='{$id}'") -> find();
        if(empty($data)){
          $data['userid'] = '';
          $data['name'] = '';
          $data['company'] = '';
          $data['website'] = '';
          $data['advertype'] = '';
          $data['goodstyle'] = '';
          $data['area'] = '';
          $data['address'] = '';
          $data['remark'] = '';
          $data['createtime'] = '';
        }
        $returnary = array_merge($person,$data);
        if(!empty($returnary))
        {
            //$saler = $userManage->getSalerById($adverInfo['salerid']);
            $returnary['saler'] = "";
            //$saler['name'];
        }
        
        $advertype = C('ADVERTYPE');
        $goodstyle = C('GOODSTYPE');
        $returnary['advertype'] = $returnary['advertype']?$advertype[$returnary['advertype']]:'';
        $returnary['goodstype'] = $returnary['goodstype']?$goodstyle[$returnary['goodstype']]:'';
        echo json_encode(array('err'=>0,'data'=>$returnary));
		exit;
	}

	//修改广告主的状态
    public function updateStatus(){
		$id = I("id");
		$status = I("status");
		$data['status'] = $status;
		$users = M("user");
		$res = $users -> where("id='{$id}'") -> save($data);
		if($res){
           $current_user = $this->baseLoginuser;
           $operateUserId = $current_user['manager_user_id'];
           $sql = $users->getlastsql();
           $desc = "修改广告主状态成功！";
           funOperateLog($operateUserId, $sql, $desc);
          
           $statelist = C('OWNER_STATUS'); 
           exit( json_encode(array('err'=>0,'msg'=>"$statelist[$status]")));
        }else{
            exit( json_encode(array('err'=>1,'msg'=>'修改失败！')));
        }
	}

    //广告主任务列表 
	public function tasklist(){
        $pageNum = 10;
        $taskAry = array();
        $pagehtml = "";
        
        $starttime = empty(I('starttime'))?'':strtotime(I('starttime'));
        $endtime = empty(I('endtime'))?'':strtotime(I('endtime'));
        $type = I('type');
        $plattype = I('plattype');
        $status = I('status');
        $username = empty(trim(I('username'))) ? '' : I('username');
        $wid = is_numeric(trim(I('wid'))) ? trim(I('wid')) : 0;
        
        //获取广告主
        if($wid){
            $map['payerid'] = $wid;
            $tempowner = $this->ownerProcess->getOneOwnerinfo($wid,'username');
            $tempowner_name = $tempowner['username']; 
        }else{
            $ownerfield = "id,username,contact,mobile";
            $ownerlist = $this->ownerProcess->getOwnerFusername($uname,$ownerfield);
            if($ownerlist){
                $userin = array();
                foreach($ownerlist as $user){
                 $userin[] = $user['id'];
                }
                $map['payerid'] = array("IN",$userin);
            }
        }
        
       	if($type != '') {
       		$map['type'] = $type;
       	}
       	if($plattype != '') {
       		$map['plattype'] = $plattype;
       	}
       	if($status != '') {
       		$map['status'] = $status;
       	}
        if($starttime && $endtime){
           $map['createtime'] = array("BETWEEN","$starttime,$endtime");
        }

        $taskNum = $this->taskProcess->getTaskCount($map);
        if($taskNum > 0){
           $taskNum = empty($taskNum) ? 0 : $taskNum;
           $Page = new \Think\Pagenew($taskNum,$pageNum);
           
           $ownerfield = "id,username,contact,realname,mobile,email";
           $taskfield = "id,taskname,wxtitle,type,plattype,createtime,starttime,payerid,managerid,status";
           $tasklimit = $Page->firstRow.','.$Page->listRows;
           $taskList = $this->taskProcess->getTaskAll($map,$tasklimit,$order='starttime',$taskfield);
           foreach($taskList as &$taskinfo){
              $taskAry[] = $taskinfo['id'];
              $ownerId = $taskinfo['payerid'];
              $ownerInfo = $this->ownerProcess->getOwnerinfo($ownerId,$ownerfield);
              
              $taskinfo['username'] = $ownerInfo['username'];
              $taskinfo['contact'] = $ownerInfo['contact'];
              $taskinfo['realname'] = $ownerInfo['realname'];
              $taskinfo['mobile'] = $ownerInfo['mobile'];
              $taskinfo['email'] = $ownerInfo['email'];
              
              $taskinfo['type'] = $this->cTasktype[$taskinfo['type']];
              $taskinfo['plattype'] = $this->cWxPlattype[$taskinfo['plattype']];
              
              $taskinfo['createtime'] = date('Y-m-d H:i:s',$taskinfo['createtime']);
              $taskinfo['starttime'] = date('Y-m-d H:i:s',$taskinfo['starttime']);
              $taskinfo['statusname'] = $this->cTaskstatus[$taskinfo['status']];
              
              $managerid = $taskinfo['managerid'];
              $managerinfo = M("manager_user",$this->tablePrefix)->field("username")->where("id=$managerid")->find();
              $taskinfo['managername'] = $managerinfo['username'];
           }
           if($taskAry){
                $orderfield = 'taskid,count(id) as orderrows,SUM(realadprice) as total_price';
                $ordergroup = 'taskid';
                $orderlimit = "0,$pageNum";
                $orderwhere['taskid']  = array('in',$taskAry);
                $orderArr = $this->orderProcess->getOrderAll($orderwhere,$orderlimit,$orderfield,$ordergroup);
                if(!empty($orderArr)){
                    $pageData = $this->mergeOrderPrice($taskList,$orderArr);
                }else{
                    $pageData = $taskList;
                }
           }
           $Page->parameter["username"] = urlencode($username);
           $Page->parameter["type"] = urlencode($type);
           $Page->parameter["plattype"] = urlencode($plattype);
           $Page->parameter["status"] = urlencode($status);
           $Page->parameter["starttime"] = date('Y-m-dH:i:s',$starttime);
           $Page->parameter["endtime"] = date('Y-m-dH:i:s',$endtime);
           $Page->parameter["wid"] = $wid;
           $pagehtml = $Page->showtwo();
        }
         $this->assign('tasks', $pageData);
         $this->assign('pagehtml',$pagehtml);
         $this->assign('type',$type);
         $this->assign('plattype',$plattype);
         $this->assign('status',$status);
         $this->assign('username',$username);
         $this->assign('starttime',date('Y-m-d H:i:s',$starttime));
         $this->assign('endtime',date('Y-m-d H:i:s',$endtime));
         $this->assign('wid',$wid);
         $this->assign('tempowner_name',$tempowner_name);
	     $this -> assign('funcdesc',"广告主任务列表");
	 
	     $this->display();
	}

    function mergeOrderPrice($taskArr=array(),$orderArr=array()){
        $result = $taskArr;
        if(!empty($orderArr)){
            foreach ($taskArr as $key => $val){
                $result[$key] = $val;
                $result[$key]['orderrows'] = 0;
                $result[$key]['total_price'] = 0;
                $orderinfo = $orderArr[$val['id']]; 
                if($orderinfo){
                    $result[$key]['orderrows'] = $orderinfo['orderrows'];
                    $result[$key]['total_price'] = priceConversion($orderinfo['total_price']);
                }
            }
        }
        return $result;
    }
    
	//查看派单数的内容==显示订单id的信息
	public function taskorders(){
		$id = I('id');
		$taskorder = M("taskorder");
		$mediauser = M("media_user",$this->tablePrefix);
		$taskorderscount = $taskorder -> where("taskid='{$id}'") -> count();
       	$count = empty($taskorderscount) ? 0 : $taskorderscount;
       	$Page = new \Think\Pagenew($count,10);
       	$data = $taskorder -> field("id,payeeid,realadprice") -> where("taskid='{$id}'") -> limit($Page->firstRow.",".$Page->listRows) -> select();
       	$pagehtml = $Page->showtwo();
       	$fansnum = '';
       	$moneytotal = '';
       	foreach($data as &$v){
       		$media_info = $mediauser -> field("nick,followers_count") -> where("id='{$v['payeeid']}'") -> find();
       		$v['nick'] = $media_info['nick'];
       		$fansnum += $media_info['followers_count'];
       		$moneytotal += $v['realadprice'];
            $v['realadprice'] = priceConversion($v['realadprice']); 
       	}
        $moneytotal = priceConversion($moneytotal);
       	$this -> assign('moneytotal',$moneytotal);
       	$this -> assign('count',$count);
       	$this -> assign('fansnum',$fansnum);
		$this -> assign('pagehtml',$pagehtml);
		$this -> assign('taskorders',$data);
		$this -> assign('funcdesc','派单微博主列表');
		$this -> display();
	}

	public function taskinfo(){
		$tid = I('id');
		$infotasks = M('infotask');
		$datainfo = $infotasks -> field("content,url,descript,wxtype") -> where("taskid='{$tid}'") -> find();
		$tasks = M('task');
		$data = $tasks -> field("id,taskname,wx_author,type,starttime,payerid") -> where("id='{$tid}'") -> find();
		$data['content'] = $datainfo['content'];
		$data['url'] = $datainfo['url'];
		$data['descript'] = $datainfo['descript'];
		$data['wxtype'] = $datainfo['wxtype'];
		echo json_encode($data);
		exit;
	}

	//广告主财务列表
	public function chargelist(){
        $pageNum = 10;
        $map = array();
        $pagehtml = "";
        $typelist = array(0=>'充值', 1=>'冻结', 2=>'扣费', 3=>'解冻', 4=>'代理商转出给广告主', 5=>'广告主转入', 6=>'广告主退款给代理商', 7=>'实际退款', 9=>'创建任务', 10=>'任务续费');
        
        $starttime = empty(I('starttime'))?'':strtotime(I('starttime'));
        $endtime = empty(I('endtime'))?'':strtotime(I('endtime'));
        $type = I('type'); 
        $u_name = empty(trim(I('u_name'))) ? '' : I('u_name');
        $wid = is_numeric(trim(I('wid'))) ? trim(I('wid')) : 0;
        
        //获取广告主
        if($wid){
            $map['userid'] = $wid;
        }else{
            $ownerfield = "id,username,contact,mobile";
            $ownerlist = $this->ownerProcess->getOwnerFusername($uname,$ownerfield);
            if($ownerlist){
                $userin = array();
                foreach($ownerlist as $user){
                 $userin[] = $user['id'];
                }
                $map['userid'] = array("IN",$userin);
            }
        }
        if($type != '') {
               $map['type'] = $type;
        }
        if($starttime && $endtime){
           $map['time'] = array("BETWEEN","$starttime,$endtime");
        }
        
        $sumMoney = $this->feeflowProcess->sumFeeflow($map);
        $sumMoney = priceConversion($sumMoney);
       
        $ad_feeflowscount = $this->feeflowProcess->getFeeflowNum($map);
        $count = empty($ad_feeflowscount) ? 0 : $ad_feeflowscount;
        $Page = new \Think\Pagenew($count,$pageNum);

        $feeflowlimit = $Page->firstRow.",".$Page->listRows;
        $data = $this->feeflowProcess->getFeeflowList($map,$feeflowlimit);
        $owner = M('user');
        foreach($data as &$v){
             $v['time'] = date("Y-m-d H:i:s",$v['time']);
             $v['recharge'] = priceConversion($v['recharge']);
             $v['balance'] = priceConversion($v['balance']);
             $v['freeze'] = priceConversion($v['freeze']);
             $v['fee'] = priceConversion($v['fee']);
             $v['typename'] = $typelist[$v['type']];
             if($ownerlist){
                 $v['username'] = $ownerlist[$v['userid']]['username'];
                 $v['contact'] = $ownerlist[$v['userid']]['contact'];
                 $v['mobile'] = $ownerlist[$v['userid']]['mobile']; 
            }else{
                 $ownerinfo = $owner -> field('username,contact,mobile') -> where("id='{$v['userid']}'") -> find();
                 $v['username'] = $ownerinfo['username'];
                 $v['contact'] = $ownerinfo['contact'];
                 $v['mobile'] = $ownerinfo['mobile'];
            }
            $manager_user_id = $v['operator'];
            if($manager_user_id){
                   $info = $this->userProcess->getUserinfo($manager_user_id,"username");
                   $v['user_name'] = $info['username'];
            }else{
                $v['user_name'] = "---";
            } 
         }
        
        $Page->parameter["starttime"] = date('Y-m-dH:i:s',$starttime);
        $Page->parameter["endtime"] = date('Y-m-dH:i:s',$endtime);
        $Page->parameter["u_name"] = urlencode($u_name);
        $Page->parameter["type"] = urlencode($type);
        $Page->parameter["wid"] = $wid;
        $pagehtml = $Page->showtwo();
	     
	    $this -> assign('feeflows', $data);
	    $this -> assign('pagehtml',$pagehtml);
        $this -> assign('sumMoney',$sumMoney);
	    $this -> assign('type',$type);
	    $this -> assign('u_name',$u_name);
        $this -> assign('starttime',date('Y-m-d H:i:s',$starttime));
        $this -> assign('endtime',date('Y-m-d H:i:s',$endtime));
        $this -> assign('funcdesc',"广告主交易流水");
        $this->display();
	}
}