<?php
/**
* @desc 代理商的添加邀请、代理商的
*/
namespace Admin\Controller;
use Think\Controller;
class AgentController extends BaseController{
	private $Assistpro = "";  //获取广告主用户信息
    private $Sendmail = ""; //发送邮件
    
	public function _initialize(){
		parent::_initialize();
		$this->Assistpro = A('Publicpart/Assistpro');
        $this->Sendmail = A('Publicpart/Mailsmtp');
	}

	//代理商的列表
	public function agentlist(){
		$page_size = 10;
        $where = "ui.agent=0";
        $username = I('username');
        $starttime = empty(I('starttime'))?'':strtotime(I('starttime'));
        $endtime = empty(I('endtime'))?'':strtotime(I('endtime'));
        if(isset($username) && !empty($username)){
            $where .= " AND u.username LIKE '".$username."%'";
        }
        if($starttime && $endtime){
           $where .= " AND ui.createtime>$starttime AND ui.createtime<=$endtime";
        }
        //获取代理商数量
		$nums = $this->Assistpro->countUserInvite($where);
		$nums = empty($nums) ? '0' : $nums;
        
		$Page = new \Think\Pagenew($nums,$page_size);
		$limit = $Page->firstRow.",".$Page->listRows;
		$list = $this->Assistpro->listUserInvite($where,'ui.id desc',$limit);
		if($list){
			foreach($list as &$val){
				$agentid = $val['userid'];
				$customercount = $this->Assistpro->countUser(array('usertype'=>2, 'agent'=>$agentid));
				$val['customers'] = $customercount;
                $usebalance = $val['balance']-$val['freeze'];
				$val['usebalance'] = empty($usebalance) ? '0' : priceConversion($usebalance);
				$val['createtime'] = date("Y-m-d H:i:s",$val['createtime']);
                if($val['regtime']!=0){
				    $val['regtime'] = date("Y-m-d H:i:s",$val['regtime']);
                }else{
                    $val['regtime'] = "";
                }
			}
		}
        $Page->parameter["starttime"] = date('Y-m-dH:i:s',$starttime);
        $Page->parameter["endtime"] = date('Y-m-dH:i:s',$endtime);
        $Page->parameter["username"] = urlencode($username);
		$pagehtml = $Page->showtwo();
        
        $this -> assign('data',$list);
		$this -> assign('pagehtml',$pagehtml);
		$this -> assign('username',$username);
        $this -> assign('starttime',date('Y-m-d H:i:s',$starttime));
        $this -> assign('endtime',date('Y-m-d H:i:s',$endtime));
		$this -> assign('funcdesc','邀请的代理商列表');
		$this -> display();
	}

    //添加修改代理商
    public function operateInvite(){
        $current_user = $this->baseLoginuser;
        $managerid = $current_user['manager_user_id'];
        if(IS_POST){
            if(I('id')){
                //获取代理商
                if(!($userInfo = $this->Assistpro->getUserInviteById($_POST['id'])) || $userInfo['agent']>0)
                    exit(json_encode(array('err'=>1,'msg'=>'代理商不存在')));
                if(isset($_POST['company']))
                {
                    foreach($_POST as $key=>$val)
                        $data[$key] = trim($val);
                    if(!$this->Assistpro->updateUserInvite($data, "id={$userInfo['id']}"))
                        exit(json_encode(array('err'=>1,'msg'=>'代理商信息修改失败')));
                    echo json_encode(array('err'=>0,'msg'=>'代理商信息修改成功'));
                }
                else
                    echo json_encode(array('err'=>0,'data'=>$userInfo));
            }else{
                if(!isset($_POST['company']) || !trim($_POST['company']))
                    exit(json_encode(array('err'=>1,'msg'=>'公司名称不能为空')));
                if(!isset($_POST['email']) || !trim($_POST['email']))
                    exit(json_encode(array('err'=>1,'msg'=>'邮箱不能为空')));
                elseif(!preg_match("~^.+@[\w-]+\.[a-z]+$~",trim($_POST['email'])))
                    exit(json_encode(array('err'=>1,'msg'=>'邮箱格式错误')));
                
                foreach($_POST as $key=>$val){
                    $data[$key] = htmlspecialchars(trim($val));
                }
                    
                list($usec, $sec) = explode(" ", microtime());
                $data['code'] = md5($data['company'] .'|'. ($usec+$sec));  //唯一码
                $data['expire'] = time() + 24*3600;  //24小时过期
                $data['status'] = 1;
                if(!$this->Assistpro->addUserInvite($data)){
                   exit(json_encode(array('err'=>1,'msg'=>'代理商添加失败')));
                }
                
                //发送邮件
                $smtp = $this->Sendmail->MailSmtp(C('MAIL_HOST'),C('MAIL_PORT'),true,C('MAIL_NAME'),C('MAIL_PASS'));    
                $title = iconv('UTF-8', 'GBK', "WeiQ注册邀请");
                $url = "http://{$_SERVER['HTTP_HOST']}/assist/ownerlogin/register.html?code={$data['code']}";
                $mailHtml = "
                {$data['company']} {$data['contact']}，您好！<br />
                恭喜您获得成为WeiQ自媒体平台代理商的机会！请访问 <a href=\"{$url}\">{$url}</a> 完成帐号注册。相关事宜请咨询您的销售联系人。<br /><br />
                Weiq.com<br />
                注：本邮件由系统自动发送，请勿直接回复。";
                $mailHtml = iconv('UTF-8', 'GBK', $mailHtml);
                $email = trim($_POST['email']);
                $ret = $this->Sendmail->send_mail($email,C('MAIL_NAME'),$title,$mailHtml,"HTML");    
                echo json_encode(array('err'=>0,'msg'=>'代理商添加成功'));
            }
        }else{
            echo "参数错误";
        }
    }
    
	//代理商下的广告主待审核列表
	public function agentaudit(){
        //每页显示的条数
        $page_size=10;
        
        $where = "ui.status IN(0,2) AND ui.agent>0";
        $advername = $_REQUEST['advername'];
        $agentname = $_REQUEST['agentname'];
        if(isset($advername) && !empty($advername)){
            $where .= " AND ui.advername LIKE '%".$advername."%'";
        }
        if(isset($agentname) && !empty($agentname)){
            $where .= " AND u.username LIKE '%".$agentname."%'";
        }
        //获取代理商的广告主（未审核和审核未通过）
        $nums = $this->Assistpro->countInviteAdver($where);
        $nums = empty($nums) ? '0' : $nums;
        $Page = new \Think\Pagenew($nums,$page_size);
        $limit = $Page->firstRow.",".$Page->listRows;
        $list = $this->Assistpro->listInviteAdver($where,'ui.id desc',$limit);
        if($list){
            foreach($list as &$val){
                $val['createtime'] = date("Y-m-d H:i:s",$val['createtime']);
            }
        }
        
        $Page->parameter["advername"] = urlencode($advername);
        $Page->parameter["agentname"] = urlencode($agentname);
        $pagehtml = $Page->showtwo();
        
        $this -> assign('data',$list);
        $this -> assign('pagehtml',$pagehtml);
        $this -> assign('advername',$advername);
        $this -> assign('agentname',$agentname);
		$this -> assign('funcdesc','广告主审核');
		$this -> display();
	}

    //查看邀请广告主的详情 
    public function adverInfoview(){
        $invite = I('invite');
        $id = I('id');
        $id = is_numeric($id)?$id:1;
        if(!empty($invite)){
            $adverInfo = $this->Assistpro->getUserInviteById($id);
            if(!empty($adverInfo)){
                $saler = $this->Assistpro->getSalerById($adverInfo['salerid']);
                $adverInfo['saler'] = $saler['name'];
            }
        }else{
            $adverInfo = $this->Assistpro->getUserAdverById($id);
            if(!empty($adverInfo)){
                $saler = $this->Assistpro->getSalerById($adverInfo['salesid']);
                $adverInfo['saler'] = $saler['name'];
            }
        }
        

        $advertype = C('ADVERTYPE');
        $goodstype = C('GOODSTYPE');
        $adverInfo['advertype'] = $advertype[$adverInfo['advertype']];
        $adverInfo['goodstype'] = $goodstype[$adverInfo['goodstype']];
        echo json_encode(array('err'=>0,'data'=>$adverInfo));
    }
    
	//广告主列表
	public function adverlist(){
        //每页显示的条数
        $page_size=10;
        $where = "ui.agent<>0 AND ui.status=1";
        $sign = I('status');
        $agentname = I('agentname');
        if(isset($sign) && !empty($sign)){
            if($sign == 1){
                $where .= " AND ui.userid>0";
            }elseif($sign == 2){
                $where .= " AND ui.userid=0";
            }
        }
        if(isset($agentname) && !empty($agentname)){
            $where .= " AND u.username LIKE '%".$agentname."%'";
        }
        $nums = $this->Assistpro->countInviteAdver($where);
        $nums = empty($nums) ? '0' : $nums;
        $Page = new \Think\Pagenew($nums,$page_size);
        $limit = $Page->firstRow.",".$Page->listRows;
        $list = $this->Assistpro->listInviteAdver($where,'ui.id desc',$limit);
        if($list){
            foreach($list as &$val){
                $userid = $val['userid'];
                if($userid){
                    $userinfo = $this->Assistpro->getUserAdverById($userid);
                    $val['r_username'] = $userinfo['username'];
                    $usebalance = $val['balance']-$val['freeze'];
                    $val['r_usebalance'] = empty($usebalance) ? '0' : priceConversion($usebalance);
                }else{
                    $val['r_username'] = '';
                    $val['r_usebalance'] = 0;
                }
                $saler = $this->Assistpro->getSalerById($val['salerid']);
                $adverInfo['saler'] = $saler['name'];
                $val['createtime'] = date("Y-m-d H:i:s",$val['createtime']);
            }
        }
        $Page->parameter["sign"] = urlencode($sign);
        $Page->parameter["agentname"] = urlencode($agentname);
        $pagehtml = $Page->showtwo();
        
        $this -> assign('data',$list);
        $this -> assign('pagehtml',$pagehtml);
        $this -> assign('agentname',$agentname);
        $this -> assign('sign',$sign);
        $this -> assign('funcdesc','代理商广告主列表');
        $this -> display();
	}
    
    //代理商广告主审核
    public function checkAdver(){
        $status = I('status');
        $id = I('id');
        $id = is_numeric($id)?$id:1;
        
        if(!in_array($status,array(1,2)) || !isset($id))
            exit(json_encode(array('err'=>1,'msg'=>'非法参数')));

        if(!$inviteInfo = $this->Assistpro->getUserInviteById($id))
            exit(json_encode(array('err'=>1,'msg'=>'广告主申请信息不存在')));

        list($usec, $sec) = explode(" ", microtime());
        $data = array('status'=>$status,
                      'code'=>md5($inviteInfo['advername'] .'|'. ($usec+$sec)),
                      'expire'=>time() + 24*3600);
        if($status == 2)
            $data['reason'] = htmlspecialchars(trim(I('reason')));
        if(!$this->Assistpro->updateUserInvite($data,"id=".intval($id)))
            exit(json_encode(array('err'=>1,'msg'=>'广告主审核失败')));

        if($data['status'] == 1){  //审核通过发邮件
            //发送邮件
            $smtp = $this->Sendmail->MailSmtp(C('MAIL_HOST'),C('MAIL_PORT'),true,C('MAIL_NAME'),C('MAIL_PASS'));    
            $title = iconv('UTF-8', 'GBK', "WeiQ注册邀请");
            $url = "http://{$_SERVER['HTTP_HOST']}/assist/ownerlogin/register.html?={$data['code']}";
            $mailHtml = "
            {$inviteInfo['advername']} {$inviteInfo['contact']}，您好！<br />
            感谢选择WeiQ自媒体平台推广您的产品与品牌！请访问 <a href=\"{$url}\">{$url}</a> 完成帐号注册，并保管好您的帐号密码。
            下一步在WeiQ平台的广告投放将由#代理商公司名称#的客户经理#销售名称#为您服务。<br /><br />
            Weiq.com<br />
            注：本邮件由系统自动发送，请勿直接回复。";
            $mailHtml = iconv('UTF-8', 'GBK', $mailHtml);
            $email = $inviteInfo['email'];
            $ret = $this->Sendmail->send_mail($email,C('MAIL_NAME'),$title,$mailHtml,"HTML");    
        }

        echo json_encode(array('err'=>0,'msg'=>'广告主审核成功'));
    }
}