<?php
namespace Admin\Controller;
use Think\Controller;

class ArtissueController extends BaseController{
    
    private $cManager = ""; //管理员信息
    //初始化
    public $weixinClass = "";
    public function _initialize() {
       parent::_initialize(); 
       $this->cManager = $this->baseLoginuser;
    }

    //微信公众号自媒体主删除的订单
    public function deletelist(){
	      $pageNum = 10;
	      $map = array();
	      $pagehtml = "";
	        
	      $tid = empty(I('tid'))?'':I('tid');

	      //检索3天前的时间
	      $thrtime = strtotime(date("Y-m-d 00:00:00",strtotime("-3days",time())));
	      $nowtime = time();
	      $map['grabtime'] = array("BETWEEN","$thrtime,$nowtime");

	      if($tid != ''){
	          $map['orderid'] = $tid; 
	      }
            $map['plattype'] = '0'; //平台类型：0=>'微信公众号',1=>'朋友圈', 2=>'微博'
	      $artissues = M('art_issue');
	      $artissuescount = $artissues -> where($map) -> count();
	      $count = empty($artissuescount) ? 0 : $artissuescount;
	      $Page = new \Think\Pagenew($count,$pageNum);
	      $data = $artissues -> field('orderid,grabtime') -> where($map)
	       				     -> order("orderid desc")
	        			     	     -> limit($Page->firstRow.",".$Page->listRows)
	        				     -> select();
	      $Page->parameter["tid"] = urlencode($tid);
	      $pagehtml = $Page->showtwo();
	         
	      $media_user = M('media_user',$this->tablePrefix);
	      $taskorders = M('taskorder');
	      $tasks = M('task');
	      foreach($data as &$d){
	      	$taskorders_info = $taskorders -> field('taskid,payeeid,wxtitle,publishtime,status') -> where("id='{$d['orderid']}'") -> find();
	            $info = $media_user -> field("name")-> where("id='{$taskorders_info['payeeid']}'") ->find();
	            $d['user_name'] = $info['name'];
	            $tname = $tasks -> field('taskname') -> where("id='{$taskorders_info['taskid']}'") -> find();
	            $d['taskname'] = $tname['taskname'];
	            $d['taskid'] = $taskorders_info['taskid'];
	            $d['payeeid'] = $taskorders_info['payeeid'];
	            $d['wxtitle'] = $taskorders_info['wxtitle'];
	            $d['publishtime'] = date("Y-m-d H:i:s",$taskorders_info['publishtime']);
	            $d['typestatus'] = $taskorders_info['status'];
	      }
      	$this -> assign('orders', $data);
        	$this -> assign('tid',$tid);
      	$this -> assign('pagehtml',$pagehtml);
    		$this -> assign("funcdesc","微信自媒体主删除的订单");
    		$this -> display();
      }

      //展示抓取的信息
      public function issueinfo(){
      	$id = I('orderid');
      	$artissues = M('art_issue');
      	$data = $artissues -> where("orderid='{$id}'") -> select();
      	foreach($data as &$v){
      		$v['grabtime'] = date("Y-m-d H:i:s",$v['grabtime']);
      	}
      	$this -> assign("orders",$data);
      	$this -> display();
      }

      //修改订单 =》 终止订单 status = 9
      public function updatestate(){
      	$id = I('id');
      	$taskorders = M('taskorder');
      	$data['status'] = 9;//修改订单的状态
      	$res = $taskorders -> where("id='{$id}'") -> save($data);
      	if($res){
      		    //若是事务成功提交，则记录日志
                      $operateUserId = $this->cManager['manager_user_id'];
                      $desc = "中止订单成功";
                      $sql = $taskorders->getLastSql();
                      funOperateLog($operateUserId, $sql, $desc);

      		exit(json_encode(array('err'=>0, 'msg'=>'中止订单成功')));
      	}else{
      		exit(json_encode(array('err'=>1, 'msg'=>'中止订单失败')));
      	}
      }

    //微博帐号自媒体主删除的订单
    public function blogdeletelist(){
        $pageNum = 10;
        $map = array();
        $pagehtml = "";
          
        $tid = empty(I('tid'))?'':I('tid');

        //检索3天前的时间
        $thrtime = strtotime(date("Y-m-d 00:00:00",strtotime("-3days",time())));
        $nowtime = time();
        $map['grabtime'] = array("BETWEEN","$thrtime,$nowtime");

        if($tid != ''){
            $map['orderid'] = $tid; 
        }
        $map['plattype'] = '2'; //平台类型：0=>'微信公众号',1=>'朋友圈', 2=>'微博'
            
        $artissues = M('art_issue');
        $artissuescount = $artissues -> where($map) -> count();
        $count = empty($artissuescount) ? 0 : $artissuescount;
        $Page = new \Think\Pagenew($count,$pageNum);
        $data = $artissues -> field('orderid,grabtime') -> where($map)
                     -> order("orderid desc")
                           -> limit($Page->firstRow.",".$Page->listRows)
                       -> select();
        $Page->parameter["tid"] = urlencode($tid);
        $pagehtml = $Page->showtwo();
           
        $media_user = M('media_user',$this->tablePrefix);
        $taskorders = M('taskorder');
        $tasks = M('task');
        foreach($data as &$d){
          $taskorders_info = $taskorders -> field('taskid,payeeid,publishtime,status') -> where("id='{$d['orderid']}'") -> find();
              $info = $media_user -> field("name")-> where("id='{$taskorders_info['payeeid']}'") ->find();
              $d['user_name'] = $info['name'];
              $tname = $tasks -> field('taskname') -> where("id='{$taskorders_info['taskid']}'") -> find();
              $d['taskname'] = $tname['taskname'];
              $d['taskid'] = $taskorders_info['taskid'];
              $d['payeeid'] = $taskorders_info['payeeid'];
              $d['publishtime'] = date("Y-m-d H:i:s",$taskorders_info['publishtime']);
              $d['typestatus'] = $taskorders_info['status'];
        }
        $this -> assign('orders', $data);
          $this -> assign('tid',$tid);
        $this -> assign('pagehtml',$pagehtml);
        $this -> assign("funcdesc","微博自媒体主删除的订单");
        $this -> display();
      }
}