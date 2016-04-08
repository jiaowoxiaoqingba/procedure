<?php
/* *
 * 功能：支付宝服务器异步通知页面
 * 如果没有收到该页面返回的 success 信息，支付宝会在24小时内按一定的时间策略重发通知
 */
namespace Admin\Controller;
use Think\Controller; 
class NotifypayeeController extends Controller{
//批量支付返回
public function index(){
   $this->batchPayment();
}

private function batchPayment(){
    $mPayee = M('weiq_pay_payee');
    require_once("/application/publicpart/controller/lib/alipay.config.php");
    write_log(json_encode($_POST),'batchpayment');
    //计算得出通知验证结果
    $alipayNotify = new AlipaynotifyController($alipay_config);
    $verify_result = $alipayNotify->verifyNotify();
    $success_trade_no = $fail_trade_no = array();
    if($verify_result) {//验证成功   
        //批量付款数据中转账成功的详细信息
        $success_details = $_POST['success_details'];
        while(strpos($success_details,'|')){
            $success_detail = explode('^',substr($success_details, 0, strpos($success_details,'|')));
            $data = array();
            $data['paystatus']='S';
            $data['comment']='';
            $data['updatime']=time();
            $mPayee->where('trade_no="'.$success_detail[0].'"')->save($data);
            $success_details = substr($success_details, strpos($success_details,'|') + 1);
            $success_trade_no[] = $success_detail[0];
        }
        
        //批量付款数据中转账失败的详细信息
        $fail_details = $_POST['fail_details'];
        while(strpos($fail_details,'|')){
            $fail_detail = explode('^',substr($fail_details, 0, strpos($fail_details,'|')));
            $data = array();
            $data['paystatus']='F';
            $data['comment']="'".$fail_detail[5]."'";
            $data['updatime']=time();
            $mPayee->where('trade_no="'.$success_detail[0].'"')->save($data);
            $fail_details = substr($fail_details, strpos($fail_details,'|') + 1);
            $fail_trade_no[] = $fail_detail[0];
        }
        
        echo "success";        //请不要修改或删除

        //调试用，写文本函数记录程序运行情况是否正常
        write_log("success");
    }else {
        //验证失败
        echo "fail";
        //调试用，写文本函数记录程序运行情况是否正常
        logResult("fail");
    }
    if(!empty($success_trade_no) || $fail_trade_no){
        function payPush($successTradeNo,$failTradeNo){
               $sendMessage = function($tradeNo=array(), $textKey=1, $pageSize=1000){ // 获取设备号
                global $db;
                $len = ceil(count($tradeNo)/$pageSize);
                for($i=1; $i<=$len; $i++){
                    // 获取设备号
                    $offset = ($i - 1) * $pageSize;
                    $sql = 'select p.pushid from weiq_pay_payee e inner join weiq_push_user p on p.userid=e.payeeuid where e.trade_no in("'.implode('","',$tradeNo).'") limit '.$offset.','.$pageSize;
                    $res = mysql_query($sql);
                    while ($row = mysql_fetch_assoc($res)) {
                        $ids[] = $row['pushid'];
                    }
                    // 推送
                    $helperObj = new Helper_Push();
                    $text =  array(
                        0 => '【订单提醒】您有一条WEIQ的新订单，请尽快登录处理！',
                        1 => '【支付提醒】您在WEIQ的提现申请已通过审核并已付款，请关注支付宝到账信息！',
                        2 => '【支付提醒】您在WEIQ的提现申请付款失败，请访问WEIQ了解详情！',
                        3 => '【账号提醒】您的微博账号授权即将到期，请登录WEIQ授权！'
                    );
                    $param = array(
                        'linkpage' => $textKey == 1 ? 10 : 12   // 推送跳转页
                    );
                    $helperObj->send($ids,$text[$textKey],$param);
                    // 写入日志
                    logResult("{ids:".implode(',',$ids).",text:".$text[$textKey].",param:".$param['linkpage']."}",'push_pay.log');
                }
            };
            if(!empty($successTradeNo)){
                $sendMessage($successTradeNo,1); // 支付成功
            }
            if(!empty($failTradeNo)){
                $sendMessage($failTradeNo,2);   // 支付失败
            }
        }
        payPush($success_trade_no,$fail_trade_no);
    }
}
}
?>