<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Home\Controller;
use OT\DataDictionary;

/**
 * 前台首页控制器
 * 主要获取首页聚合数据
 */
class IndexController extends HomeController {

    private $appId='wx2ac95455d468b963';
    private $secret='cd4e9adf126f49c299d4b711d95ac482';

    public function _initialize()
    {
        Vendor('Extend.weixin');
        $this->wechat = new \weixin();
        //var_dump($this->wechat);exit;
        Vendor('wechat.autoload');

    }
	//系统首页
    public function index(){
        //echo 1;exit;
        $this->display();
    }

    /**
     * 接受微信消息  地址
     */
    public function valid()
    {
        /*$echoStr = $_GET["echostr"];
        if($this->checkSignature()){
            echo $echoStr;
            exit;
        }else{
            echo '验证失败';
        }*/
    }

    private function checkSignature()
    {
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];

        $token = 'weixin';
        $tmpArr = array($token, $timestamp, $nonce);
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode( $tmpArr );
        $tmpStr = sha1( $tmpStr );

        if( $tmpStr == $signature ){
            return true;
        }else{
            return false;
        }
    }

    /**
     * 设置菜单
     */
    public function setMenu(){
        //echo MY_URL."/Home/Product/show";exit;
        $url = $_SERVER['HTTP_HOST'];
        //echo $url;exit;
        $menus = [
            'button'=>[
                ['type'=>'view','name'=>'商城','url'=>'http://'.$url.'/Home/Index/index'],

            ]

        ];
//        var_dump($menus);exit;
        Vendor('Extend.WxService');
        $weixin = new \WxService();
        //$menus = $this->json_unescaped($menus);
        //Header("Content-Type:text/json;charset=utf-8");
        $caidan = $weixin->get_wx_menu();
        header('content-Type:text/html;charset=utf-8');
        //print_r($caidan);exit;
        $res = $weixin->create_wx_menu(json_encode($menus,JSON_UNESCAPED_UNICODE));
        var_dump($res);exit;
    }
}