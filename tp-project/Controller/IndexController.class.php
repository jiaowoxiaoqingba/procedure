<?php
namespace Admin\Controller;
use Think\Controller;
class IndexController extends BaseController {
    //后台框架页
    public function index() {
        $current_user = $this->baseLoginuser;
        $this->assign('current_user',$current_user['manager_user']);
        $this->assign('channel', $this->_getChannel());
        $this->assign('menu', $this->_getMenu());
        $this->display();
    }

    //后台首页
    public function main() {
        echo '<h2>这里是后台首页</h2>';
        $this->display();
    }

    protected function _getChannel() {
        //获取用户权限标识
        $arrMenu['manage'] = '管理员';
        $arrMenu['media'] = '自媒体主';
        $arrMenu['owner'] = '广告主管理';
        $arrMenu['agent'] = '代理商';
        $arrMenu['material'] = '素材管理';
        $arrMenu['task'] = '任务审核';
        $arrMenu['order'] = '订单管理'; 
        $arrMenu['finance'] = '财务管理';
        $arrMenu['systemset'] = '系统设置';
        
        return $arrMenu;
    }

    protected function _getMenu() {
        $menu = array();   //注意顺序！！        
        $apps_menu = array();
        // 后台管理首页
        $menu['manage'] = array(
                '管理员管理' => array(
                '管理员列表' => U('admin/manageoper/managelist'),
                '添加管理员' => U('admin/manageoper/addmanage'),
                '修改密码' => U('admin/manageoper/updatepassword'),
                '角色设置' => U('admin/role/rolelist'),
                '功能管理' => U('admin/role/funclist'),
            ),
        );
        
        $menu['media'] = array(
             '自媒主管理' => array(
                '自媒体主列表' => U('admin/media/medialist'),
                '自媒体(微信公众号)注销' => U('admin/media/mediauser'),
             ),
             '微信自媒主账号管理' => array(
                '微信公众号' => U('admin/account/wechatlist'),
                '公众号惩罚情况' => U('admin/account/punishlist'), 
             ),
             '新浪微博账号管理' => array(
                '新浪微博帐号审核' => U('admin/blog/waitaudit'),
                '微博惩罚情况' => U('admin/blog/punishlist'), 
             ),
        );
        $menu['agent'] = array(
                '代理商管理' => array(
                '代理商列表' => U('admin/agent/agentlist'),
                '代理商广告主审核' => U('admin/agent/agentaudit'),
                '代理商广告主列表' => U('admin/agent/adverlist')
            ),
        );
        $menu['owner'] = array(
                '广告主管理' => array(
                '广告主列表' => U('admin/owner/index'),
                '广告主任务列表' => U('admin/owner/tasklist'),
                '广告主流水' => U('admin/owner/chargelist'), 
            ),
        );

        $menu['material'] = array(
                '素材管理' => array(
                '图片库' => U('admin/material/photolist'),
                '图文库' => U('admin/material/pictextlist'),
            ),
        );
        
      $menu['task'] = array(
                '微信任务管理' => array(
                '待审核任务' => U('admin/task/unchecklist'),
                '已审核任务' => U('admin/task/checkedlist'),
                '未支付任务' => U('admin/task/unpayment'),
            ),
                '微博任务管理' => array(
                '待审核任务' => U('admin/blogtask/waitcheck'),
                '已审核任务' => U('admin/blogtask/checkover'),
                '未支付任务' => U('admin/blogtask/unpayment'),
            ),
        );

        $menu['order'] = array(
                '微信订单管理' => array(
                //'全部订单' => U('admin/order/orderslist'),
                '微信公众号订单'            => U('admin/order/wechatorder'),
                '可申诉和申诉中的订单'  => U('admin/order/appeallist'),
                '已出账的订单'               => U('admin/order/payorderlist'),
                '微信自媒体主删除的订单'     => U('admin/artissue/deletelist'),
            ),
            '微博订单管理' => array(
                //'全部订单' => U('admin/order/orderslist'),
                '微博帐号订单'               => U('admin/blogorder/blogorder'),
                '可申诉和申诉中的订单'  => U('admin/blogorder/appeallist'),
                '已出账的订单'               => U('admin/blogorder/payorderlist'),
                '微博自媒体主删除的订单'     => U('admin/artissue/blogdeletelist'),
            ),
        );

        $menu['finance'] = array(
            '财务管理' => array(
                '账号充值' => U('admin/finance/managerrecharge'),
                '充值记录' => U('admin/finance/record'),
                '提现申请' => U('admin/finance/withdrawals'),
                '提现方式管理' => U('admin/draw/draw'),  
            ),
            '退款管理' => array(
                '退款' => U('admin/refund/index'),
                '退款记录' => U('admin/refund/recordlist'),
            ),
            '转账管理' => array(
                '转账记录' => U('admin/transfer/transferlog'),
            ),
        );
        
        $menu['systemset'] = array(
            '公告管理' => array(
                '公告列表' => U('admin/notice/noticelist'),
            ),
            '计划任务' => array(
                '计划任务' => U('admin/cron/cronlist'),
            ),
        );
        return $menu;
    }
    //end class 
}