<?php
namespace Admin\Controller;
use Think\Controller;

class TransferController extends BaseController{
      
      private $ownerProcess = "";  //获取广告主用户信息
      private $adfeeflowProcess = "";  //获取广告主用户信息
	
	public function _initialize(){
		parent::_initialize();
            $this->ownerProcess = A('Publicpart/Ownerprocess');
            $this->adfeeflowProcess = A('Publicpart/Adfeeflowprocess');
	}

	//转账记录
	public function transferlog(){
	      $pageNum = 10;
	      $map = array();
	      $pagehtml = "";

            $agent_name = empty(trim(I('agent_name'))) ? '' : I('agent_name');   //接收的代理商的agent_name
            $user_name = empty(trim(I('user_name'))) ? '' : I('user_name');   //接收的代理商下的广告主的user_name
            $starttime = empty(I('starttime'))?'':strtotime(I('starttime'));
  	      $endtime = empty(I('endtime'))?'':strtotime(I('endtime'));
  	      if($starttime && $endtime){
  		    $map['time'] = array("BETWEEN","$starttime,$endtime");
  	      }
  	
  	      //获得代理商的信息
  	      $agentfield = "id";
  	      $tousersInfo = $this->ownerProcess->getOwnerFusername($agent_name,$agentfield);
  	      if($tousersInfo){
      		$touserid = array();
      		foreach($tousersInfo as $val){
      			$touserid[] = $val['id'];
      		}
      		$map['touserid'] = array("IN",$touserid);
  	      }

            //获得代理商下面广告主的信息
            $userfield = "id";
            $fromusersInfo = $this->ownerProcess->getOwnerFusername($user_name,$userfield);
            if($fromusersInfo){
                  $fromuserid = array();
                  foreach($fromusersInfo as $val){
                      $fromuserid[] = $val['id'];
                  }
                  $map['fromuserid'] = array("IN",$fromuserid);
            }

            //类型为 type = 4 "代理商转账给广告主"；
            $map['type'] = 4;

            $ad_feeflowcounts = $this->adfeeflowProcess->getFeeflowNum($map);
            //echo $ad_feeflowcounts;die;
            $count = empty($ad_feeflowcounts) ? '0' : $ad_feeflowcounts;
            $Page = new \Think\Pagenew($count,$pageNum);
            $transferlimit = $Page->firstRow.",".$Page->listRows;
            $data = $this->adfeeflowProcess->getFeeflowList($map, $transferlimit);
            $Page->parameter["agent_name"] = urlencode($agent_name);
            $Page->parameter["user_name"] = urlencode($user_name);
            $Page->parameter["starttime"] = date('Y-m-dH:i:s',$starttime);
            $Page->parameter["endtime"] = date('Y-m-dH:i:s',$endtime);
            $pagehtml = $Page->showtwo();
            foreach($data as &$val){
                  $val['time'] = date("Y-m-d H:i:s",$val['time']);
                  $val['fee'] = priceConversion($val['fee']);
                  $userInfo1 = $this->ownerProcess->getOwnerAllinfo($val['touserid']);
                  $userInfo2 = $this->ownerProcess->getOwnerAllinfo($val['fromuserid']);
                  $val['agentid'] = $val['touserid'];
                  $val['agent_username'] = $userInfo1['username'];
                  $val['agent_contact'] = $userInfo1['contact'];
                  $val['userid'] = $val['fromuserid'];
                  $val['owner_username'] = $userInfo2['username'];
                  $val['owner_contact'] = $userInfo2['contact'];
            }
            //echo "<pre>";
            //var_dump($data);
            $this -> assign('starttime',date('Y-m-d H:i:s',$starttime));
            $this -> assign('endtime',date('Y-m-d H:i:s',$endtime));
            $this -> assign('agent_name',$agent_name);
            $this -> assign('user_name',$user_name);
            $this -> assign('data',$data);
            $this -> assign('pagehtml',$pagehtml);
            $this -> assign('funcdesc','转账记录');
            $this -> display();
	}

      //展示详细信息
      public function userdetail(){
            $id = I('uid');
            $data=$this->ownerProcess->getOwnerAllinfo($id);
            echo json_encode($data);
            exit;
      }
}