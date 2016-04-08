<?php
  /**
 * desc : order process(订单的相关处理)
 * @author : 
 * @date 
 */
namespace Admin\Controller;
use Think\Controller;

class OrderController extends BaseController{
    
    private $mediaProcess = "";  //获取自媒体主信息
    private $userProcess = "";  //获取自媒体主用户信息
    private $ownerProcess = "";  //获取广告主用户信息
    private $taskProcess = "";  //任务处理类
    private $orderProcess = "";  //订单处理类
    
    private $cTasktype = "";  //任务类型
    private $cPlattype = "";  //发布任务的平台类型
    private $mPlattype = "";  //自媒体主的任务类型
    private $mOrdertime = ""; //自媒体主允许接单的时间
    
    private $cManager = ""; //管理员信息
    //初始化
    public function _initialize() {
       parent::_initialize(); 
       $this->mediaProcess = A('Publicpart/Mediaprocess');
       $this->userProcess = A('Publicpart/Userprocess');
       $this->ownerProcess = A('Publicpart/Ownerprocess');
       $this->taskProcess = A('Publicpart/Taskprocess');
       $this->orderProcess = A('Publicpart/Orderprocess');
       
       $this->cManager = $this->baseLoginuser;

       $this->mOrdertime = C('ORDER_ACCESSTIME');
       $this->cTasktype = C('TASKTYPE');
       $this->cWxPlattype = C('WXPLATTYPE');
       $this->mPlattype = C('PLATTYPE');       
    }
    
   //ajax请求接单人id的详细信息
   public function media_userinfo(){
        $id = I('id');
        
        $mediafield = "m.uid as muid,m.plattype as mplattype,m.platid as mplatid,m.name as mname,m.nick as mnick,";
        $mediafield .= "w.*";
        $media_userinfo = $this->mediaProcess->getMediainfo($id,$mediafield);
        if($media_userinfo){
           $uid = $media_userinfo["muid"];
           $userfield = "username,contact,mobile,email,address,company";
           $users_info = $this->userProcess->getUserinfo($uid,$userfield);
           $media_userinfo['username'] = $users_info['username'];
           $media_userinfo['contact'] = $users_info['contact'];
           $media_userinfo['mobile'] = $users_info['mobile'];
           $media_userinfo['email'] = $users_info['email'];
           $media_userinfo['address'] = $users_info['address'];
           $media_userinfo['company'] = $users_info['company'];
           $media_userinfo['adver_single_price'] = priceConversion($media_userinfo['adver_single_price']);
           $media_userinfo['adver_multi_first_price'] = priceConversion($media_userinfo['adver_multi_first_price']);
           $media_userinfo['adver_multi_two_price'] = priceConversion($media_userinfo['adver_multi_two_price']);
           $media_userinfo['adver_multi_notfirst_price'] = priceConversion($media_userinfo['adver_multi_notfirst_price']);
           
           $media_userinfo['plattype'] = $this->mPlattype[$media_userinfo['plattype']];
        }
        echo json_encode($media_userinfo);
        exit;
   }
   
    //全部订单的列表
	public function orderslist(){
	    $user_name = I('user_name');
	    $type = I('type');
	    $plattype = I('plattype');
       	$status = I('status');
       	$content = empty(trim(I('content'))) ? '' : I('content');
       	$orders = M("taskorder");
       	$users = M("user");
       	$tasks = M("task");
       	if($user_name){
       	    $uinfo['username'] = array("LIKE","%{$user_name}%");
       	    $usersinfo = $users -> field('id') -> where($uinfo) -> select();
       	    if($usersinfo){
	           $userin = array();
	           foreach($usersinfo as $user){
		          $userin[] = $user['id'];	
	           }
	            //var_dump($userin);exit;
	            $map['payerid'] = array("IN",$userin);
	        }else{
	            $map['payerid'] = 0;
	        }
       	}
       	if($type !== '') {
       		$map['type'] = $type;
       	}
       	if($plattype !== '') {
       		$map['plattype'] = $plattype;
       	}
       	if($status !== '') {
       		$map['status'] = $status;
       	}
       	if($content) {
       		$tinfo['taskname'] = array("LIKE","%{$content}%");
       		$tasksinfo = $tasks -> where($tinfo) -> select();
       		if($tasksinfo){
       			$taskin = array();
	       		foreach($tasksinfo as $task){
		       		$taskin[] = $task['id'];
		       	}
		       	$map['taskid'] = array("IN",$taskin);
       		}else{
       			$map['taskid'] = 0;
       		}
       	}
       	$orderscount = $orders -> where($map) -> count();
        $adprice_info = $orders -> field('adprice') -> where($map) -> select();
        $balance = '';
        foreach ($adprice_info as $value) {
            $balance += $value['adprice'];
        }
       	$count = empty($orderscount) ? 0 : $orderscount;
       	$Page = new \Think\Pagenew($count,2);
       	$data = $orders -> where($map) -> limit($Page->firstRow.",".$Page->listRows) -> select();

       	$media_user = M('media_user',$this->tablePrefix);

	     foreach($data as &$d){
       	    $info = $media_user -> field("name,platid")-> where("id='{$d['payeeid']}'") ->find();
             $d['user_name'] = $info['name'];
       	    $d['platid'] = $info['platid'];
             $uname = $users -> field('username') -> where("id='{$d['payerid']}'") -> find();
             $d['username'] = $uname['username'];
             $tname = $tasks -> field('taskname') -> where("id='{$d['taskid']}'") -> find();
             $d['taskname'] = $tname['taskname'];
             $d['starttime'] = date("Y-m-d H:i:s",$d['starttime']);
       	}

        $Page->parameter["content"] = urlencode($content);
        $Page->parameter["status"] = urlencode($status);
        $Page->parameter["plattype"] = urlencode($plattype);
       	$Page->parameter["type"] = urlencode($type);
       	$Page->parameter["user_name"] = urlencode($user_name);
	     $pagehtml = $Page->showtwo();
	     $typelist = array(1=>'直发',2=>'转发');
	     $plattypelist = array(0=>'微信公众号',1=>'朋友圈');
	     $statuslist = array(0=>'未派单',1=>'派单',2=>'接单',3=>'已发布',4=>'已完成',5=>'取消',6=>'失败',7=>'拒单',8=>'接单前流单',9=>'订单中途停止',10=>'接单后流单');
          $this -> assign('balance_total',$balance);
          $this -> assign('num_total',$count);
	     $this -> assign('typelist',$typelist);
	     $this -> assign('plattypelist',$plattypelist);
	     $this -> assign('statuslist',$statuslist);
	     $this -> assign('orders', $data);
	     $this -> assign('pagehtml',$pagehtml);
	     $this -> assign('type',$type);
	     $this -> assign('plattype',$plattype);
	     $this -> assign('status',$status);
	     $this -> assign('content',$content);
	     $this -> assign('user_name',$user_name);
	     $this -> assign('funcdesc',"全部订单");
	     $this -> display();
	}

    //ajax请求任务id的详细信息
	public function taskinfo(){
		$tid = I('id');
        $orderid = I('orderid');
		$infotasks = M('infotask');
		$datainfo = $infotasks -> field("content,url,descript,pic,picprove") -> where("taskid='{$tid}'") -> find();
		$tasks = M('task');
		$data = $tasks -> field("id,taskname,type,starttime,payerid") -> where("id='{$tid}'") -> find();
		$user = M('user');
		$userinfo = $user -> field("username,type") -> where("id='{$data['payerid']}'") -> find();
		$users = M('userinfo');
		$usersinfo = $users -> field("company") -> where("userid='{$data['payerid']}'") -> find();
        
        $order = M('taskorder');
        $orderinfo = $order->where("id=$orderid")->find();
        
		$data['username'] = $userinfo['username'];
		$data['usertype'] = $userinfo['type'];
		$data['name'] = $usersinfo['name'];
		$data['company'] = $usersinfo['company'];
		$data['typelist'] = $this->cTasktype[$data['type']];
        $data['starttime'] = date("Y-m-d H:i:s",$data['starttime']);
		$data['content'] = html_entity_decode($datainfo['content']);
		$data['pic'] = $datainfo['pic'];
        $data['url'] = $datainfo['url'];
		$data['picprove'] = $datainfo['picprove'];
		$data['descript'] = $datainfo['descript'];
        $data['confirmurl'] = $orderinfo['confirmurl'];
        $data['confirmpic'] = $orderinfo['confirmpic'];
		echo json_encode($data);
		exit;
	}

    //获取微信公众号的订单
	public function wechatorder(){
        $pageNum = 10;
        $taskorderAry = array();
        $map = array();
        $pagehtml = "";
        $statuslist = array(0=>'未派单',1=>'派单',2=>'接单',3=>'已发布',4=>'已完成',5=>'取消',6=>'失败',7=>'拒单',8=>'接单前流单',9=>'订单中途停止',10=>'接单后流单',11=>'未上传完成图片');
        
        $starttime = empty(I('starttime'))?'':strtotime(I('starttime'));
        $endtime = empty(I('endtime'))?'':strtotime(I('endtime'));
        $tid = empty(I('tid'))?'':I('tid');
        $status = I('status');
        $content = empty(trim(I('content'))) ? '' : I('content');  
	    $ower_name = empty(trim(I('user_name'))) ? '' : I('user_name');
        //获取广告主
        $ownerfield = "id,username";
        $ownerlist = $this->ownerProcess->getOwnerFusername($ower_name,$ownerfield);
        if($ownerlist){
            $userin = array();
            foreach($ownerlist as $user){
             $userin[] = $user['id'];
            }
            $map['payerid'] = array("IN",$userin);
        }
        if($status != '') {
            $map['status'] = $status;
        }else{
            $map['status'] = array('neq',13);
        }
        //获取任务名称
        $taskfield = "id,taskname";
        $tasklist = $this->taskProcess->getTaskFtaskname($content,$taskfield);
        if($tasklist){
            $taskin = array();
            foreach($tasklist as $task){
                $taskin[] = $task['id'];
            }
            $map['taskid'] = array("IN",$taskin);
        }
        if($tid != ''){
            $map['id'] = $tid; 
        }
        if($starttime && $endtime){
           $map['createtime'] = array("BETWEEN","$starttime,$endtime");
        }
        $map['plattype'] = '0';   //  平台类型为 “微信公众号”

        $orderscount = $this->orderProcess->getOrderNum($map);
        $count = empty($orderscount) ? 0 : $orderscount;
        
        $sumprice = $this->orderProcess->sumOrderPrice($map);
        $sumprice = priceConversion($sumprice);   
        $Page = new \Think\Pagenew($count,$pageNum);
        $orderlimit = $Page->firstRow.",".$Page->listRows;
        $data = $this->orderProcess->getOrderList($map,$orderlimit);
        $Page->parameter["content"] = urlencode($content);
        $Page->parameter["status"] = urlencode($status);
        $Page->parameter["user_name"] = urlencode($ower_name);
        $Page->parameter["tid"] = urlencode($tid);
        $Page->parameter["starttime"] = date('Y-m-dH:i:s',$starttime);
        $Page->parameter["endtime"] = date('Y-m-dH:i:s',$endtime);
        $pagehtml = $Page->showtwo();
              
        //申诉的时间条件限制(任务完成后的一天)
        $shensu_time = C('APPEAL_TIME'); //24*60*60; //一天的时间
        //可申诉的订单状态
        $statusDeal = array(4,11);
        //可修改时间的订单状态
        $statusType = array(1);
        $nowtime = time();
        $media_user = M('media_user',$this->tablePrefix);
        $owner = M('user');
        $tasks = M('task');
	    foreach($data as &$d){
       	      $info = $media_user -> field("name,platid")-> where("id='{$d['payeeid']}'") ->find();
       	      $d['user_name'] = $info['name'];
              $d['platid'] = $info['platid'];
              if($ownerlist){
                 $d['username'] = $ownerlist[$d['payerid']]['username'];
              }else{
                 $uname = $owner -> field('username') -> where("id='{$d['payerid']}'") -> find();
                 $d['username'] = $uname['username'];
              }
              if($tasklist){
                 $d['taskname'] = $tasklist[$d['taskid']]['taskname'];
              }else{
                 $tname = $tasks -> field('taskname') -> where("id='{$d['taskid']}'") -> find();
                 $d['taskname'] = $tname['taskname'];
              }
              $d['createtime'] = date("Y-m-d H:i:s",$d['createtime']);
              //判断订单是否流单（或具备延长接单时间的功能）
              $d['updatetime']= 0;
              $configOrdertime = $this->mOrdertime;
              $updateTime = $d['starttime'] + $configOrdertime;
              if(in_array($d['status'], $statusType) && $d['starttime'] && $nowtime<$updateTime && $d['paystatus']==0){
                $d['updatetime'] = 1;
              }

              $d['starttime'] = date("Y-m-d H:i:s",$d['starttime']);
              $d['realadprice'] = priceConversion($d['realadprice']);
              
              //判断是否在申诉条件内
              $d['appealsign'] = 0;
              $tempFinshtimetime = $d['finshtime']?$d['finshtime']:0;
              $appealtime = strtotime(date("Y-m-d 00:00:00",$tempFinshtimetime))+$shensu_time;
              if(in_array($d['status'],$statusDeal) && $tempFinshtimetime && $tempFinshtimetime<$nowtime && $nowtime<$appealtime && $d['paystatus']==0){
                 $d['appealsign'] = 1;
              }
       	}
        $this -> assign('statuslist',$statuslist);
        $this -> assign("num_total",$count);
        $this -> assign("balance_total",$sumprice);
	    $this -> assign('orders', $data);
        $this -> assign('tid',$tid);
        $this -> assign('starttime',date('Y-m-d H:i:s',$starttime));
        $this -> assign('endtime',date('Y-m-d H:i:s',$endtime));
	    $this -> assign('pagehtml',$pagehtml);
	    $this -> assign('status',$status);
	    $this -> assign('content',$content);
	    $this -> assign('user_name',$ower_name);
	    $this -> assign('funcdesc',"微信公众号订单");
	    $this -> display();
	}
    //在订单流单前，修改订单的投放时间
    public function UpdateTime(){
        $oid = I('oid');
        $tmpstarttime = I('tmpstarttime');
        $taskorders = M('taskorder');
        $data = $taskorders -> field('starttime') -> where("id=".$oid) -> find();
        $afterUpdate = $data['starttime'] + $tmpstarttime*60;
        $param['starttime'] = $afterUpdate;
        $res = $taskorders -> where("id=".$oid) -> save($param);
        if($res){
            //记录管理员修改日志
            $operateUserId = $this->cManager['manager_user_id'];
            $desc = "修改订单时间，增加".$tmpstarttime."分钟";
            $sql = $taskorders->getLastSql();
            funOperateLog($operateUserId, $sql, $desc);
            
            exit(json_encode(array('err'=>0, 'msg'=>'修改成功')));
        }else{
            exit(json_encode(array('err'=>1, 'msg'=>'修改失败')));
        }
    }

    //微信号后台提交申诉理由   或者 修改广告主和自媒体主的真实结算价
    public function appealed_reason(){
          $id = I('id');  //订单的id
          $rejectDesc = I('rejectDesc');//申诉的理由
          $appeal = I('appeal');  //申诉的状态
          $adappealprice = I('adappealprice');  //广告主的真实结算价
          $mappealprice = I('mappealprice');    //自媒体主真实结算价
          $appealsign = intval(I('appealsign'));    //用来区分申诉=0；和申诉中=1
          $appeals = M('appeal');
          $taskorders = M('taskorder');
          if($appeal == '1'){
                if($appealsign==0){
                      $taskorder_data = $taskorders -> field('id,taskid') -> where("id='{$id}'") -> find();
                      $data['taskid'] = $taskorder_data['taskid'];
                      $data['orderid'] = $taskorder_data['id'];
                      $data['reason'] = $rejectDesc;
                      $data['createtime'] = time();
                      $data['appealtype'] = 3;
                      $data['managerid'] = $this->cManager['manager_user_id'];
                      $res1 = $appeals -> add($data);
                      $res2 = $taskorders -> where("id='{$id}'") -> setField('appeal',1);
                      if($res1 && $res2){
                          //申诉成功记录日志
                          $operateUserId = $this->cManager['manager_user_id'];
                          $desc = "申诉提交成功";
                          $sql = $appeals->getLastSql();
                          funOperateLog($operateUserId, $sql, $desc);
                          exit(json_encode(array('err'=>0, 'msg'=>'申诉提交成功')));
                      }else{
                          exit(json_encode(array('err'=>1, 'msg'=>'申诉提交失败')));
                      }
                }
          }else{
                $m = M();//实例化一个空，用来处理事务
                $m -> startTrans();//开启事务
                $adappealprice = empty(conversion($adappealprice)) ? '0' : conversion($adappealprice);
                $mappealprice = empty(conversion($mappealprice)) ? '0' : conversion($mappealprice);
                $data = array('appeal'=>2, 'adappealprice'=> $adappealprice, 'mappealprice'=>$mappealprice);
                $res3 = $taskorders -> where("id='{$id}'") -> setField($data);
                if($res3){
                      $m -> commit(); //成功则提交
                      
                      //若是事务成功提交，则记录日志
                      $operateUserId = $this->cManager['manager_user_id'];
                      $desc = "申诉结算完成";
                      $sql = $taskorders->getLastSql();
                      funOperateLog($operateUserId, $sql, $desc);
                      exit(json_encode(array('err'=>0, 'msg'=>'申诉结算完成')));
                }else{
                      $m -> rollback();//失败，则回滚
                      exit(json_encode(array('err'=>1, 'msg'=>'申诉结算失败')));
                }
          }
      }

      //申诉中与可申诉的订单列表
    public function appeallist(){
        $pageNum = 10;
        $taskorderAry = array();
        $map = array();
        $pagehtml = "";
        $statuslist = array(0=>'未派单',1=>'派单',2=>'接单',3=>'已发布',4=>'已完成',5=>'取消',6=>'失败',7=>'拒单',8=>'接单前流单',9=>'订单中途停止',10=>'接单后流单',11=>'未上传完成图片');
        
        $starttime = empty(I('starttime'))?'':strtotime(I('starttime'));
        $endtime = empty(I('endtime'))?'':strtotime(I('endtime'));
        $tid = empty(I('tid'))?'':I('tid');
        $content = empty(trim(I('content'))) ? '' : I('content');  
      $ower_name = empty(trim(I('user_name'))) ? '' : I('user_name');
        //获取广告主
        $ownerfield = "id,username";
        $ownerlist = $this->ownerProcess->getOwnerFusername($ower_name,$ownerfield);
        if($ownerlist){
            $userin = array();
            foreach($ownerlist as $user){
             $userin[] = $user['id'];
            }
            $map['payerid'] = array("IN",$userin);
        }

        //申诉的状态情况
        $status = 4;
        $map['status'] = $status;
        $appeal = array('0','1');
        $map['appeal'] = array("IN",$appeal);

        //获取任务名称
        $taskfield = "id,taskname";
        $tasklist = $this->taskProcess->getTaskFtaskname($content,$taskfield);
        if($tasklist){
            $taskin = array();
            foreach($tasklist as $task){
                $taskin[] = $task['id'];
            }
            $map['taskid'] = array("IN",$taskin);
        }
        if($tid != ''){
            $map['id'] = $tid; 
        }
        if($starttime && $endtime){
           $map['createtime'] = array("BETWEEN","$starttime,$endtime");
        }
        $map['plattype'] = '0';   //  平台类型为 “微信公众号”

        $orderscount = $this->orderProcess->getOrderNum($map);
        $count = empty($orderscount) ? 0 : $orderscount;
        
        $sumprice = $this->orderProcess->sumOrderPrice($map);
        $sumprice = priceConversion($sumprice);   
        $Page = new \Think\Pagenew($count,$pageNum);
        $orderlimit = $Page->firstRow.",".$Page->listRows;
        $data = $this->orderProcess->getOrderList($map,$orderlimit);
        $Page->parameter["content"] = urlencode($content);
        $Page->parameter["status"] = urlencode($status);
        $Page->parameter["user_name"] = urlencode($ower_name);
        $Page->parameter["tid"] = urlencode($tid);
        $Page->parameter["starttime"] = date('Y-m-dH:i:s',$starttime);
        $Page->parameter["endtime"] = date('Y-m-dH:i:s',$endtime);
        $pagehtml = $Page->showtwo();
              
        //申诉的时间条件限制(任务完成后的一天)
        $shensu_time = C('APPEAL_TIME'); //24*60*60; //一天的时间
        //可申诉的订单状态
        $statusDeal = array(4,11);
        $nowtime = time();
        $media_user = M('media_user',$this->tablePrefix);
        $appeals = M('appeal');
        $owner = M('user');
        $tasks = M('task');
      foreach($data as &$d){
              $info = $media_user -> field("name,platid")-> where("id='{$d['payeeid']}'") ->find();
              $d['user_name'] = $info['name'];
              $d['platid'] = $info['platid'];
              if($ownerlist){
                 $d['username'] = $ownerlist[$d['payerid']]['username'];
              }else{
                 $uname = $owner -> field('username') -> where("id='{$d['payerid']}'") -> find();
                 $d['username'] = $uname['username'];
              }
              if($tasklist){
                 $d['taskname'] = $tasklist[$d['taskid']]['taskname'];
              }else{
                 $tname = $tasks -> field('taskname') -> where("id='{$d['taskid']}'") -> find();
                 $d['taskname'] = $tname['taskname'];
              }
              $d['createtime'] = date("Y-m-d H:i:s",$d['createtime']);
              $d['starttime'] = date("Y-m-d H:i:s",$d['starttime']);
              $d['realadprice'] = priceConversion($d['realadprice']);

              //判断是否已经存在申诉的信息
              $d['appealInfoSign'] = 0;
              $appealInfo = $appeals -> where("orderid='{$d['id']}'") -> select();
              if($appealInfo){
                  $d['appealInfoSign'] = 1;
              }

        }
        $this -> assign('statuslist',$statuslist);
        $this -> assign("num_total",$count);
        $this -> assign("balance_total",$sumprice);
      $this -> assign('orders', $data);
        $this -> assign('tid',$tid);
        $this -> assign('starttime',date('Y-m-d H:i:s',$starttime));
        $this -> assign('endtime',date('Y-m-d H:i:s',$endtime));
      $this -> assign('pagehtml',$pagehtml);
      $this -> assign('content',$content);
      $this -> assign('user_name',$ower_name);
      $this -> assign('funcdesc',"申诉中与可申诉的订单");
      $this -> display();
    }

    //订单号的申诉情况详细
  public function infoorder(){
    $appealtypelist = array(1=>'广告主申诉', 2=>'自媒体主申诉', 3=>'后台处理');
    $id = I('orderid');
    $appeals = M('appeal');
    $tasks = M('task');
    $data = $appeals->where("orderid='{$id}'")->order('createtime desc')->select();
    foreach($data as &$d){
          $taskdata = $tasks -> field("taskname") -> where("id='{$d['taskid']}'") -> find();
          $d['taskname'] = $taskdata['taskname'];
          $d['createtime'] = date("Y-m-d H:i:s",$d['createtime']);
          if($d['replytime']){
            $d['replytime'] = date("Y-m-d H:i:s",$d['replytime']);
          }else{
            $d['replytime'] = "----";
          }
    }
    $this -> assign('orders', $data);
    $this -> assign('appealtypelist',$appealtypelist);
    $this->display();
  }

    //回馈内容的请求发送
    public function appeal_reply(){
          $id = I('id');
          $rejectDesc = I('rejectDesc');//回馈的内容
          $appeals = M('appeal');
          $param['reply'] = $rejectDesc;
          $param['replytime'] = time();
          //修改回馈日志表里的内容
          $data1 = $appeals -> where("id='{$id}'") -> save($param);
          $data2 = $appeals -> where("id='{$id}'") -> setField('status',2);
          $appeal_data = $appeals -> where("id='{$id}'") -> find();
          $taskorders = M('taskorder');
          if($data1 && $data2){
              exit(json_encode(array('err'=>0, 'msg'=>'回馈成功')));
          }else{
              exit(json_encode(array('err'=>1, 'msg'=>'回馈失败')));
          }
      }

    //申诉订单的回馈详情
    public function appeal_info(){
          $id = I('id');
          $appeals = M('appeal');   //申诉表
          $tasks = M('task');         //任务表
          $taskorders = M('taskorder');          //订单表
          $users = M('user');         //广告主表
          $mediauser = M('media_user',$this->tablePrefix);  //自媒体主表
          $managers = M('manager_user',$this->tablePrefix); //管理员表
          $appeals_info = $appeals -> where("id='{$id}'") -> find();
          $tasks_info = $tasks -> field("taskname") -> where("id='{$appeals_info['taskid']}'") -> find();
          $taskorders_info = $taskorders -> field("payerid,payeeid") -> where("id='{$appeals_info['orderid']}'") -> find();
          $users_info = $users -> field("username") -> where("id='{$taskorders_info['payerid']}'") -> find();
          $mediauser_info = $mediauser -> field("name") -> where("id='{$taskorders_info['payeeid']}'") -> find();
          $managers_info = $managers -> field("username") -> where("id='{$appeals_info['managerid']}'") -> find();
          $data['username'] = $users_info['username'];  //下单人的昵称
          $data['taskname'] = $tasks_info['taskname'];  //任务名称
          $data['name'] = $mediauser_info['name'];        //接单人的用户名
          $data['reason'] = $appeals_info['reason'];        //申诉的理由
          $data['user_name'] = $managers_info['username'];  //后台操作员
          echo json_encode($data);
          exit;
      }

      //已出账的订单列表
    public function payorderlist(){
        $pageNum = 10;
        $taskorderAry = array();
        $map = array();
        $pagehtml = "";
        $statuslist = array(0=>'未派单',1=>'派单',2=>'接单',3=>'已发布',4=>'已完成',5=>'取消',6=>'失败',7=>'拒单',8=>'接单前流单',9=>'订单中途停止',10=>'接单后流单',11=>'未上传完成图片');
        
        $starttime = empty(I('starttime'))?'':strtotime(I('starttime'));
        $endtime = empty(I('endtime'))?'':strtotime(I('endtime'));
        $tid = empty(I('tid'))?'':I('tid');
        $content = empty(trim(I('content'))) ? '' : I('content');  
      $ower_name = empty(trim(I('user_name'))) ? '' : I('user_name');
        //获取广告主
        $ownerfield = "id,username";
        $ownerlist = $this->ownerProcess->getOwnerFusername($ower_name,$ownerfield);
        if($ownerlist){
            $userin = array();
            foreach($ownerlist as $user){
             $userin[] = $user['id'];
            }
            $map['payerid'] = array("IN",$userin);
        }

        //出账的状态情况
        $status = array('4','11');
        $map['status'] = array("IN",$status);
        $paystatus = 1;
        $map['paystatus'] = $paystatus;
        $map['plattype'] = '0';   //  平台类型为 “微信公众号”

        //获取任务名称
        $taskfield = "id,taskname";
        $tasklist = $this->taskProcess->getTaskFtaskname($content,$taskfield);
        if($tasklist){
            $taskin = array();
            foreach($tasklist as $task){
                $taskin[] = $task['id'];
            }
            $map['taskid'] = array("IN",$taskin);
        }
        if($tid != ''){
            $map['id'] = $tid; 
        }
        if($starttime && $endtime){
           $map['starttime'] = array("BETWEEN","$starttime,$endtime");
        }

        $orderscount = $this->orderProcess->getOrderNum($map);
        $count = empty($orderscount) ? 0 : $orderscount;
        
        $realadprices = $this->orderProcess->sumPrice($map,'realadprice');
        $realadprices = priceConversion($realadprices);   
        $realmediaprices = $this->orderProcess->sumPrice($map,'realmediaprice');
        $realmediaprices = priceConversion($realmediaprices);   
        $adappealprices = $this->orderProcess->sumAppealPrice($map,'adappealprice');
        $adappealprices = priceConversion($adappealprices);   
        $mappealprices = $this->orderProcess->sumAppealPrice($map,'mappealprice');
        $mappealprices = priceConversion($mappealprices);   

        $Page = new \Think\Pagenew($count,$pageNum);
        $orderlimit = $Page->firstRow.",".$Page->listRows;
        $data = $this->orderProcess->getOrderList($map,$orderlimit);
        $Page->parameter["content"] = urlencode($content);
        $Page->parameter["status"] = urlencode($status);
        $Page->parameter["user_name"] = urlencode($ower_name);
        $Page->parameter["tid"] = urlencode($tid);
        $Page->parameter["starttime"] = date('Y-m-dH:i:s',$starttime);
        $Page->parameter["endtime"] = date('Y-m-dH:i:s',$endtime);
        $pagehtml = $Page->showtwo();
              
        //申诉的时间条件限制(任务完成后的一天)
        $shensu_time = C('APPEAL_TIME'); //24*60*60; //一天的时间
        //可申诉的订单状态
        $statusDeal = array(4,11);
        $nowtime = time();
        $media_user = M('media_user',$this->tablePrefix);
        $appeals = M('appeal');
        $owner = M('user');
        $tasks = M('task');
        $orderaccount = M('orderaccountlog');   //实例化订单结算的真实价格日志表
      foreach($data as &$d){
              $info = $media_user -> field("name,platid")-> where("id='{$d['payeeid']}'") ->find();
              $d['user_name'] = $info['name'];
              $d['platid'] = $info['platid'];
              if($ownerlist){
                 $d['username'] = $ownerlist[$d['payerid']]['username'];
              }else{
                 $uname = $owner -> field('username') -> where("id='{$d['payerid']}'") -> find();
                 $d['username'] = $uname['username'];
              }
              if($tasklist){
                 $d['taskname'] = $tasklist[$d['taskid']]['taskname'];
              }else{
                 $tname = $tasks -> field('taskname') -> where("id='{$d['taskid']}'") -> find();
                 $d['taskname'] = $tname['taskname'];
              }              
              $d['starttime'] = date("Y-m-d H:i:s",$d['starttime']);
              $d['publishtime'] = date("Y-m-d H:i:s",$d['publishtime']);
              if($d['finshtime']){
                  $d['finshtime'] = date("Y-m-d H:i:s",$d['finshtime']);                  
              }else{
                  $d['finshtime'] = "----";
              }
              if($d['paytime']){
                    $d['paytime'] = date("Y-m-d H:i:s",$d['paytime']);
              }else{
                    $d['paytime'] = "----";
              }
              
              $d['realadprice'] = priceConversion($d['realadprice']);
              $d['realmediaprice'] = priceConversion($d['realmediaprice']);
              //订单的真实结算价格
              $orderaccountInfo = $orderaccount -> where("orderid='{$d['id']}'") -> find();
              $d['adappealprices'] = priceConversion($orderaccountInfo['adappealprice']);
              $d['mappealprices'] = priceConversion($orderaccountInfo['mappealprice']);

              //判断是否已经存在申诉的信息
              $d['appealInfoSign'] = 0;
              $appealInfo = $appeals -> where("orderid='{$d['id']}'") -> select();
              if($appealInfo){
                  $d['appealInfoSign'] = 1;
              }

        }
        $this -> assign('statuslist',$statuslist);
        $this -> assign("num_total",$count);
        $this -> assign("realadprices",$realadprices);
        $this -> assign("realmediaprices",$realmediaprices);
        $this -> assign("adappealprices",$adappealprices);
        $this -> assign("mappealprices",$mappealprices);
        $this -> assign('orders', $data);
        $this -> assign('tid',$tid);
        $this -> assign('starttime',date('Y-m-d H:i:s',$starttime));
        $this -> assign('endtime',date('Y-m-d H:i:s',$endtime));
        $this -> assign('pagehtml',$pagehtml);
        $this -> assign('content',$content);
        $this -> assign('user_name',$ower_name);
        $this -> assign('funcdesc',"已出账的订单");
        $this -> display();
    }
    
    //出账订单的明细展示
    public function orderpay_info(){
        $id = I('id');
        $taskorders = M('taskorder');
        $fields = "wxtitle,confirmpic,success,readnum,praisenum";
        $data = $taskorders -> field($fields) -> where("id='{$id}'") -> find();
        echo json_encode($data);
        exit;
    }
}