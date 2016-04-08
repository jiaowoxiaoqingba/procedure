<?php
/*
*@desc 所有计划任务列表
*@time 2015/09/13
*@author zxw
*/
namespace Admin\Controller;
use Think\Controller;
class CronController extends Controller {
    public function index(){
    }
    
    //检测任务是否超过审核时间
    public function cronList(){
        $this -> assign('funcdesc','计划任务列表');
        $this -> assign('onecron_fortask',"广告任务时间检测，5分钟执行一次");
        $this -> assign('onecron_url','/cron/taskcron/checktasktime.html');
        $this -> assign('twocron_fororder',"订单接单前流单、接单后流单、是否按要求完成");
        $this -> assign('twocron_url','/cron/ordercron/index.html');
        $this -> assign('thrcron_fororder',"订单上传完成截图短信提示，一天执行一次：9---16点都可以");
        $this -> assign('thrcron_url','/cron/ordercron/completesmstips.html');
        $this -> assign('fourcron_fororder',"统计cpt任务的日应结帐单（计算订单真实价格，并更改支出）");
        $this -> assign('fourcron_url','/cron/financecron/index.html');
        $this -> assign('fivecron_fororder',"自媒体主收入初始化（只执行一次）");
        $this -> assign('fivecron_url','/cron/financecron/financialinit.html');
        $this -> assign('sixcron_fororder',"自媒体主每天收入（每天执行一次，尽可能在--广场任务和统计cpt任务的日应结帐单--执行完成后执行）");
        $this -> assign('sixcron_url','/cron/financecron/financialcheange.html');
        $this -> assign('sevencron_fororder',"任务完成广告主扣款");
        $this -> assign('sevencron_url','/cron/taskcron/taskfinish.html');
        $this -> assign('eightcron_fororder',"更新记录自媒体主的订单数目变化");
        $this -> assign('eightcron_url','/cron/accountcron/publishrecord.html');
        $this -> assign('ninecron_fororder',"检测文章是否被删除");
        $this -> assign('ninecron_url','/cron/artissue/artrelease.html');
        $this -> display();
    }
}
