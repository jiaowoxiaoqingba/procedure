<?php
  /**
 * desc : task process(任务处理) audit task(任务审核)
 * @author : 
 * @date 
 */
namespace Admin\Controller;
use Think\Controller;
class BlogtaskController extends BaseController{
    private $taskProcess = "";  //任务处理核心程序
    private $orderProcess = "";  //订单处理核心程序
    private $ownerProcess = "";  //获取广告主信息
    private $userProcess = "";  //获取自媒体主信息
    
    private $cTasktype = "";  //任务类型
    private $cPlattype = "";  //发布任务的平台类型
    private $cTaskstatus = ""; //任务状态
    
    private $cManager = ""; //管理员信息
    
    private $owninterfaceCla = ""; //Website interface 
    //初始化
    public function _initialize() {
       parent::_initialize();
       $this->ownerProcess = A('Publicpart/Ownerprocess');
       $this->taskProcess = A('Publicpart/Taskprocess');
       $this->orderProcess = A('Publicpart/Orderprocess'); 
       $this->mediaProcess = A('Publicpart/Mediaprocess');
       
       $this->cTasktype = C('TASKTYPE');
       $this->cWxPlattype = C('WXPLATTYPE');
       $this->cTaskstatus = C('TASKSTATUS'); 
       
       $this->cManager = $this->baseLoginuser;
       $this->owninterfaceCla = A('Publicpart/Owninterface');   
    }
	//待审核的任务列表
	public function waitcheck(){
        $param = I('param.');
        $param['status'] = '1';//状态为  “待审核”
        $param['plattype'] = '2'; //  平台类型为 “微博帐号”

        $pageNum = 10;
        $taskAry = array();
        $pagehtml = "";
        //获取未审核订单数量
        $taskNum = $this->taskProcess->getTaskCount($param);
        $taskList = "";
        if($taskNum > 0){
           $taskNum = empty($taskNum) ? 0 : $taskNum;
           $Page = new \Think\Pagenew($taskNum,$pageNum);
           
           $ownerfield = "id,username,contact,realname,mobile,email";
           $taskfield = "id,taskname,wxtitle,type,plattype,createtime,starttime,payerid,managerid";
           $tasklimit = $Page->firstRow.','.$Page->listRows;
           $taskList = $this->taskProcess->getTaskAll($param,$tasklimit,$order='starttime',$taskfield);
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
                    $pageData = $taskArr;
                }
           }
            $pagehtml = $Page->showtwo();
        }
	     $this -> assign('tasks', $pageData);
	     $this -> assign('pagehtml',$pagehtml);
	     $this -> assign('funcdesc',"待审核任务列表");
	     $this -> display();
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
        $pageNum = 20; 
        $pagehtml = "";
        $param['taskid'] = $id;
        $taskorderscount = $this->orderProcess->getOrderNum($param);
        $count = empty($taskorderscount) ? 0 : $taskorderscount;
        $Page = new \Think\Pagenew($count,$pageNum);
        
        $orderfield = "id,payeeid,mediaprice,realadprice,status,starttime";
        $orderlimit =  $Page->firstRow.",".$Page->listRows;
        $statuslist = array(0=>'未派单',1=>'派单',2=>'接单',3=>'已发布',4=>'已完成',5=>'取消',6=>'失败',7=>'拒单',8=>'接单前流单',9=>'订单中途停止',10=>'接单后流单',11=>'未上传完成图片');
        
        $orderList = $this->orderProcess->getOrderList($param,$orderlimit,$orderfield);
        $pagehtml = $Page->showtwo();
        
        $mediafield = "m.nick as mnick,w.followers_count as mfollower";
        $fansnum = 0;
        $moneytotal = 0;
        foreach($orderList as &$v){
           $mediaid = $v['payeeid'];
           $media_info = $this->mediaProcess->getMediaBloginfo($mediaid,$mediafield);
           $v['nick'] = $media_info['mnick'];
           $fansnum += $media_info['mfollower'];
           $moneytotal += $v['realadprice'];
           $v['realadprice'] = priceConversion($v['realadprice']);
           $v['statusname'] = $statuslist[$v['status']];
           $v['starttime'] = date("Y-m-d H:i:s",$v['starttime']);
        }
        
        $moneytotal = priceConversion($moneytotal);
        $this -> assign('moneytotal',$moneytotal);
        $this -> assign('count',$count);
        $this -> assign('fansnum',$fansnum);
        $this -> assign('pagehtml',$pagehtml);
        $this -> assign('taskorders',$orderList);
        $this -> assign('funcdesc','派单自媒体主账号列表');
        $this -> display();
    }
       
    //ajax请求任务id的详细信息
	public function taskinfo(){
		$tid = I('id');
        
        $taskfield = "id,taskname,type,starttime,payerid";
        $tasks = $this->taskProcess->getTaskcontent($tid,$taskfield);
        if($tasks){
            $uid = $tasks["payerid"];
            
            $taskinfofield = "content,url,descript,pic,picprove";
            $taskinfo = $this->taskProcess->getTaskinfo($tid,$taskinfofield);
            $ownerfield = "m.*,w.company as wcompany,w.website as website";
            $users = $this->ownerProcess->getOwnerAllinfo($uid,$ownerfield);
        }
		$tasks['username'] = $users['username'];
		$tasks['usertype'] = $users['type'];
		$tasks['company'] = $users['wcompany'];
		$tasks['typelist'] = $this->cTasktype[$tasks['type']];
        $tasks['starttime'] = date("Y-m-d H:i:s",$tasks['starttime']);
		$tasks['content'] = html_entity_decode($taskinfo['content']);
		$tasks['pic'] = $taskinfo['pic'];
		$tasks['url'] = $taskinfo['url'];
		$tasks['picprove'] = $taskinfo['picprove'];
		$tasks['descript'] = $taskinfo['descript'];
		echo json_encode($tasks);
		exit;
	}
    
    //任务审核
    public function taskpass(){
        $taskId = I("POST.taskId");
        $act    = I("POST.act");
        $operateUserId = $this->cManager['manager_user_id'];
        //任务 通过 拒绝 叫停
        if($act == 'updateTaskStatus'){
            $updateStatus = I("POST.updateStatus");//2:审核通过，3：拒绝, 5:叫停
            $rejectDesc = I("POST.rejectDesc");
            if($taskId<=0 || $updateStatus<=0){
                echo "参数错误";exit;
            }
            //获取任务信息
            $task = $this->taskProcess->getTaskcontent($taskId);
            if(empty($task))
                exit("任务不存在");
            //更新任务状态   paytype:0微信公众号 1：朋友圈 2: 微博
            if($task['paytype'] == 0){
                $mdb = M();
                $mdb->startTrans();
                $re_res = $this->taskProcess->updateTask($operateUserId,$taskId, $updateStatus, $rejectDesc);
                if($re_res['err']==1){
                    $mdb->commit();
                    echo "success";
                }elseif($re_res['err']==2){
                    $mdb->rollback();
                    echo $re_res['msg']; 
                    exit;
                }elseif($re_res['err']==3){
                   echo "Handle"; 
                   exit;
                }
                //调用通知接口通知任务状态变化
                if($updateStatus == 2 && $task['paytype'] == 0){
                    $pInfoList = $this->taskProcess->getTaskOrderPayeeidInfo($taskId);
                    foreach($pInfoList as $k=>$v){
                        if($v['username']){
                            $mobileList[] = $v['username'];
                        }
                    }
                    //短信
                    $content = "【订单提醒】您有一条WEIQ的新订单，请尽快登录处理！http://www.weiq.com 【WEIQ】";
                    $mobileList = array_chunk($mobileList, 50);
                    foreach($mobileList as $k=>$v){
                        $phones = implode(',',$v);
                        $this->owninterfaceCla->sendMessage($phones,$content);
                    }
                }
            }
        }
    } 

    //已审核任务列表
	public function checkover(){ 
        $pageNum = 10;
        $taskAry = array();
        $pagehtml = "";
        
        $starttime = empty(I('starttime'))?'':strtotime(I('starttime'));
        $endtime = empty(I('endtime'))?'':strtotime(I('endtime'));
		$tid = empty(I('tid'))?'':I('tid');
		$type = I('type');
       	$status = empty(I('status'))?'':I('status');
       	$content = empty(trim(I('content'))) ? '' : I('content');
        
       	$map['status'] = array("in","2,3,4,5");
            $map['plattype'] = '2';   //  平台类型为 “微博帐号”
       	if($tid !== ''){
       		$map['id'] = $tid;
       	}
       	if($type !== '') {
       		$map['type'] = $type;
       	}
       	if($status !== '') {
       		$map['status'] = $status;
       	}
       	if($content) {
       		$map['taskname'] = array("LIKE","%{$content}%");
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
                    $pageData = $taskArr;
                }
           }
           
           $Page->parameter["content"] = urlencode($content);
           $Page->parameter["tid"] = urlencode($tid);
           $Page->parameter["type"] = urlencode($type);
           $Page->parameter["plattype"] = urlencode($plattype);
           $Page->parameter["status"] = urlencode($status);
           $Page->parameter["starttime"] = date('Y-m-dH:i:s',$starttime);
           $Page->parameter["endtime"] = date('Y-m-dH:i:s',$endtime);
           $pagehtml = $Page->showtwo();
        }
         $this->assign('tasks', $pageData);
         $this->assign('pagehtml',$pagehtml);
	     $this->assign('type',$type);
	     $this->assign('plattype',$plattype);
	     $this->assign('status',$status);
	     $this->assign('content',$content);
	     $this->assign('tid',$tid);
         $this->assign('starttime',date('Y-m-d H:i:s',$starttime));
         $this->assign('endtime',date('Y-m-d H:i:s',$endtime));
	     $this->assign('funcdesc',"已审核任务");
	 
	     $this -> display();
	}

    //未支付的任务列表
    public function unpayment(){
            $pageNum = 10;
            $taskAry = array();
            $pagehtml = "";
                    
            $starttime = empty(I('starttime'))?'':strtotime(I('starttime'));
            $endtime = empty(I('endtime'))?'':strtotime(I('endtime'));
            $tid = empty(I('tid'))?'':I('tid');
            $type = I('type');
            $content = empty(trim(I('content'))) ? '' : I('content');
                    
            $map['status'] = 9;
            $map['plattype'] = '2';   //  平台类型为 “微博帐号”
            if($tid !== ''){
              $map['id'] = $tid;
            }
            if($type !== '') {
              $map['type'] = $type;
            }
            if($content) {
              $map['taskname'] = array("LIKE","%{$content}%");
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
                        $pageData = $taskArr;
                    }
               }
               
               $Page->parameter["content"] = urlencode($content);
               $Page->parameter["tid"] = urlencode($tid);
               $Page->parameter["type"] = urlencode($type);
               $Page->parameter["plattype"] = urlencode($plattype);
               $Page->parameter["status"] = urlencode($status);
               $Page->parameter["starttime"] = date('Y-m-dH:i:s',$starttime);
               $Page->parameter["endtime"] = date('Y-m-dH:i:s',$endtime);
               $pagehtml = $Page->showtwo();
            }
           $this->assign('tasks', $pageData);
           $this->assign('pagehtml',$pagehtml);
           $this->assign('type',$type);
           $this->assign('plattype',$plattype);
           $this->assign('status',$status);
           $this->assign('content',$content);
           $this->assign('tid',$tid);
           $this->assign('starttime',date('Y-m-d H:i:s',$starttime));
           $this->assign('endtime',date('Y-m-d H:i:s',$endtime));
           $this->assign('funcdesc',"未支付任务");
           $this -> display();
      }

    //查看派单数的内容==显示订单id的信息 以及可以修改id
    public function task_unpayment(){
        $id = I('id');
        $pageNum = 20; 
        $pagehtml = "";
        $param['taskid'] = $id;
        $taskorderscount = $this->orderProcess->getOrderNum($param);
        $count = empty($taskorderscount) ? 0 : $taskorderscount;
        $Page = new \Think\Pagenew($count,$pageNum);
        
        $orderfield = "id,payeeid,mediaprice,realadprice";
        $orderlimit =  $Page->firstRow.",".$Page->listRows;
        
        $orderList = $this->orderProcess->getOrderList($param,$orderlimit,$orderfield);
        $pagehtml = $Page->showtwo();
        
        $mediafield = "m.nick as mnick,w.followers_count as mfollower";
        $fansnum = 0;
        $moneytotal = 0;
        foreach($orderList as &$v){
           $mediaid = $v['payeeid'];
           $media_info = $this->mediaProcess->getMediaBloginfo($mediaid,$mediafield);
           $v['nick'] = $media_info['mnick'];
           $fansnum += $media_info['mfollower'];
           $moneytotal += $v['realadprice'];
           $v['realadprice'] = priceConversion($v['realadprice']);
        }
        
        $moneytotal = priceConversion($moneytotal);
        $this -> assign('moneytotal',$moneytotal);
        $this -> assign('count',$count);
        $this -> assign('fansnum',$fansnum);
        $this -> assign('pagehtml',$pagehtml);
        $this -> assign('taskorders',$orderList);
        $this -> assign('funcdesc','未支付任务订单列表');
        $this -> display();
    }

    //未支付任务中订单详情的修改价格
    public function update_adprice(){
        $id = I('id');
        $taskorders = M('taskorder');
        $data = $taskorders -> where("id='{$id}'") -> find();
        $uid = $data['payeeid'];
        $data['adprice'] = empty(conversion($data['adprice'],1)) ? '0' : conversion($data['adprice'],1);
        //公众微信号的价格附表
        //$mediauser_wxgz = M('media_user_wxgz',$this->tablePrefix);
        //$mediauser_data = $mediauser_wxgz -> field('adver_single_price,adver_multi_first_price')-> where("mediaid='{$uid}'") -> find();
        //$data['adver_single_price'] = empty(conversion($mediauser_data['adver_single_price'],1)) ? '0' : conversion($mediauser_data['adver_single_price'],1);
        //$data['adver_multi_first_price'] = empty(conversion($mediauser_data['adver_multi_first_price'],1)) ? '0' : conversion($mediauser_data['adver_multi_first_price'],1);
        $mediauser = M('media_user',$this->tablePrefix);
        $mediauser_info = $mediauser -> field("nick") -> where("id='{$uid}'") -> find();
        $data['nick'] = $mediauser_info['nick'];
        echo json_encode($data);
        exit;
    }

    //执行修改未支付的价格
    public function task_updateadprice(){
        $id = I('id');
        
        $realadprice = I('realadprice');
        $realtime = I('realusetime');
        $m = M();                                     //定义一个来启动事务
        $taskorders = M('taskorder');            //任务订单表
        $adjustorders = M('adjustorder');   //管理员订单价格调整表
        $tasks = M('task');  //任务
        //后台操作的id
        $manager_user_id = $this->cManager['manager_user_id'];

        if(!isset($id)) {
          exit(json_encode(array('err'=>1,'msg'=>'非法参数')));
        }
        //修改价格的值存在
        if($realadprice){
            if(!is_numeric($realadprice) && 100*$realadprice < 1){
                exit(json_encode(array('err'=>1,'msg'=>'修改金额错误')));
            }
            //开启事务处理
            $m -> startTrans();
            //修改价格 以及 修改的时间
            $realusetime = "+{$realtime} minutes";
            $realusetime = strtotime($realusetime);

            $realadprice = 100*$realadprice;    //价格以分的形式写进数据库
            $data1 = array('realadprice'=>$realadprice, 'realusetime'=>$realusetime);
            if(!$data_taskorder = $taskorders->where("id='{$id}'")->save($data1)){
                  exit(json_encode(array('err'=>1,'msg'=>'修改价格操作失败')));
            }
            
            //查询订单的一些基本信息
            $taskorders_info = $taskorders -> field('id,taskid,adprice') -> where("id='{$id}'") -> find();
            
            $task_data['realusetime'] = $realusetime;
            $task_result = $tasks->where("id={$taskorders_info['taskid']}")->save($task_data);
            if(!$task_result){
              $m -> rollback();  //不成功，则回滚
              exit(json_encode(array('err'=>1,'msg'=>'修改价格操作失败')));
            }
            
            $adjustorder_data['taskid'] = $taskorders_info['taskid'];
            $adjustorder_data['orderid'] = $taskorders_info['id'];
            $adjustorder_data['manageid'] = $manager_user_id;
            $adjustorder_data['oldrealprice'] = $taskorders_info['adprice'];//调整前的价格
            $adjustorder_data['realprice'] = $realadprice;//调整后的价格
            $adjustorder_data['createtime'] = $realusetime;//调整产生的时间
            //写进管理员价格修改日志里面
            $data2 = $adjustorders -> add($adjustorder_data);

            if($data_taskorder && $data2){
              $m -> commit();  //成功则提交
              exit(json_encode(array('err'=>0,'msg'=>'修改价格操作成功')));
            }else{
              $m -> rollback();  //不成功，则回滚
              exit(json_encode(array('err'=>1,'msg'=>'修改价格操作失败')));
            }
        }else{
              exit(json_encode(array('err'=>1,'msg'=>'修改价格的值不能为空')));
        }
    }
}