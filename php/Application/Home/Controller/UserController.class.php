<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2016/8/17
 * Time: 9:46
 */

namespace Home\Controller;


use Think\Controller;

class UserController extends Controller
{

    public function _initialize(){
      if(empty(cookie('userinfo'))){
            header('Location:'.U('Common/login'));
        }
    }
    /**
     * 我的账户页面
     */
    public function mypage(){
        $info = cookie('userinfo');
        $this->assign('userinfo',$info);
        //var_dump($info);exit;
        $this->display();
    }

    /**
     * 自定义分享
     */
    public function share(){
        Vendor('Extend.jssdk');
        $jssdk = new \JSSDK("wx10d9364013fad77c", "ac8298b78ce5298c7def02345d75aa40");
        $signPackage = $jssdk->GetSignPackage();
        $this->assign('signPackage',$signPackage);
        $userinfo = cookie('userinfo');
        $news = array("Title" =>$userinfo['nickname']."邀请您加入帮帮益善", "Description"=>"帮帮益善是一个真实可靠的互助社群。在这里，一人患病，众人均摊，大家互帮互助。", "PicUrl" =>MY_URL.'/Public/img/QQ20160801102712.png', "Url" =>MY_URL.'/Home/Common/share/nickname/'.$userinfo['nickname'].'/image/'.$userinfo['image']);
        $this->assign('news',$news);
        $this->display();
    }

    /**
     * 展示二维码
     */
    public function code(){
        Vendor('Extend.jssdk');
        $jssdk = new \JSSDK("wx2ac95455d468b963", "cd4e9adf126f49c299d4b711d95ac482");
        $signPackage = $jssdk->GetSignPackage();
        $this->assign('signPackage',$signPackage);
        $userinfo = cookie('userinfo');
        $url = 'http://'.$_SERVER['HTTP_HOST'];
        $news = array("Title" =>$userinfo['nickname']."邀请您注册联聚国际", "Description"=>"", "PicUrl" =>$url.'/Public/img/20170621112909.png', "Url" =>$url.'/Common/register/nickname/'.$userinfo['nickname'].'/phone/'.$userinfo['phone']);
        $this->assign('news',$news);
        $this->assign('phone',$userinfo['phone']);

        $this->assign('path',$url);
        $this->display();
    }

    /**
     * 支付
     */
    public function pay(){
        $id = I('get.id');
        //$product_id = I('get.product_id');
        $res = M('HuzhuInsure')->alias('a')->join('left join huzhu_product as b on a.product_id=b.id')
            ->where(['a.id'=>$id])->field('a.content,a.id,a.payment,a.price,a.content,a.label,b.name')->find();
        //var_dump($data);exit;
        $this->assign('res',$res);
        $this->display();
    }
    
    /**
     * 查询支付记录
     */
    public function record(){
        $data = I('get.');
        $res = M('UserCharge')->alias('a')->join('left join huzhu_product as b on a.product_id=b.id')
                        ->where(['buyorder'=>$data['buyorder'],'a.status'=>2])
                        ->field('a.price,a.insure_id,a.pay_time,a.order_no,b.name')->select();
        $this->assign('res',$res);
        $this->display();
    }

}
