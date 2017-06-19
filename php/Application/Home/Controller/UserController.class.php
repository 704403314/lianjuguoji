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
    public function myPage(){
        $info = cookie('userinfo');
        $this->assign('userinfo',$info);
        //$info['id'] = 3;
        // 查看是否有未支付记录
        //$noPay = M('UserCharge')->where(['user_id'=>$info['id'],'status'=>1])->find();
        $noPay = M('UserCharge')->alias('a')->join('left join huzhu_insure as b on a.insure_id=b.id')
            ->where(['a.user_id'=>$info['id'],'a.status'=>1])->field('b.*,a.buyorder,a.buyid')->find();
        $product = M('HuzhuProduct')->getField('id,name');
        if(!empty($noPay)){
            $noPay['name'] = $product[$noPay['product_id']];
        }

        //var_dump($noPay);exit;
        if(!empty($noPay)){
            $this->assign('noPay',$noPay);
            $this->assign('label',1);
            $this->assign('url',MY_URL);
            
        }else{
            $this->assign('noPay','');
            $this->assign('label',2);
        }
        //var_dump($noPay);exit;
        $this->display();
    }

    /**
     * 查看我的保障
     */
    public function insure(){
        $info = cookie('userinfo');
        // 查看是否有保障
        $res = M('UserProduct')->alias('a')->join('left join huzhu_product as b on a.product_id=b.id')->where(['user_id'=>$info['id']])
                     ->field('distinct a.order,a.insurance_name,a.time_id,a.happentime,b.name,b.image,b.wait')->select();
        //var_dump($res);exit;

        if(empty($res)){
            header('Location:'.U('unsure'));
        }
       
        
        $this->assign('list',$res);
        $this->assign('times',M('HuzhuTime')->getField('id,time'));
        $this->display();
    }

    /**
     * 加入的保障详情
     * @param  $order  订单号
     */
    public function insureinfo($order=null){

        // 获取保障信息
        $list = M('UserProduct')->alias('a')->join('left join huzhu_product as b on a.product_id=b.id')->where(['a.order'=>$order])
                              ->field('a.*,b.name,b.image,b.wait,b.most')->select();
        $times = M('HuzhuTime')->getField('id,time');
        foreach($list as $k=>$v){
            $list[$k]['starttime'] = date("Y年m月d日",$v['happentime']);
            if($v['time_id']==1){
                $list[$k]['endtime'] = date("Y年m月d日",$v['happentime']+2592000);
            }elseif($v['time_id']==2){
                $list[$k]['endtime'] = date("Y年m月d日",$v['happentime']+15724800);
            }elseif($v['time_id']==3){
                $list[$k]['endtime'] = date("Y年m月d日",$v['happentime']+31536000);
            }else{
                $list[$k]['endtime'] = '长期';
            }

        }
        $this->assign('list',$list);
        $this->assign('count',count($list));
        // 判断保障状态
        $label = D('HuzhuUser')->getLabel($list,$times);

        // 等待期还剩多少天
        if($label==1){
            // 已经过了多少天
            $wait = floor((time()-$list[0]['createtime'])/86400);
            $middle = ceil(($list[0]['happentime']-$list[0]['createtime'])/86400) - $wait;
            $left = '等待期剩余';
            $right = '天';
        }elseif($label==2){
            $left = '已失效';
            $middle = '';
            $right = '';
        }elseif($label==3){
            $left = '已冻结';
            $middle = '';
            $right = '';
        }elseif($label==4){
            $left = '有效期剩余';
            $right = '天';
            // 过了多少天

            $happentime = floor((time()-$list[0]['happentime'])/86400);
            if($list[0]['time_id']==1){
                $middle = 30-$happentime;
            }elseif($list[0]['time_id']==2){
                $middle = 183-$happentime;
            }elseif($list[0]['time_id']==3){
                $middle = 365-$happentime;
            }elseif($list[0]['time_id']==4){
                $left = '长期有效';
                $middle = '';
                $right = '';
            }



        }
        $this->assign('left',$left);
        $this->assign('middle',$middle);
        $this->assign('right',$right);
        if(empty($list[0]['most'])){
            $most = '';
        }else{
            $most = $list[0]['most'];
        }
        $this->assign('times',$times);
        $this->assign('most',$most);
       
        $this->display();
    }

    /**
     * 未加入计划
     */
    public function  unsure(){
        $this->display();
    }

    ///**
    // * 申请互助
    // */
    //public function apply(){
    //    $res = M('HuzhuApply')->select();
    //    $this->assign('res',$res[0]);
    //    $this->display();
    //}

    /**
     * 展示反馈页面
     */
    public function suggestion(){
        $this->display();
    }

    /**
     * 用户反馈
     */
    public function addSuggestion(){
        $data = I('post.');
        $model = M('HuzhuSuggestion');
        //Vendor('Extend.extend');
        //var_dump($data);exit;
        if(empty($data['content'])){
            exit(json_encode(array('status' => 0, 'info' =>'请填写反馈内容')));
        }
        $userinfo = cookie('userinfo');
        $data['phone'] = $userinfo['phone'];
        $data['user_id'] = $userinfo['id'];
        $data['createtime']=time();
        $re=$model->add($data);
        if($re){
           $this->ajaxReturn(['status'=>1,'info'=>'反馈成功']);
        }else{
            $this->ajaxReturn(['status'=>0,'info'=>'添加反馈失败，请重新添加']);
        }
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
        $userinfo  = cookie('userinfo');
        $this->assign('userinfo',$userinfo);
        $this->assign('path',MY_URL);
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
