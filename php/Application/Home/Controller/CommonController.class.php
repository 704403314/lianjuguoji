<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2016/8/16
 * Time: 11:21
 */

namespace Home\Controller;
use Think\Controller;
define("APPID", "wx2ac95455d468b963");
define("SECRET", "cd4e9adf126f49c299d4b711d95ac482");
class CommonController extends Controller
{


    /**
     * 登陆获取用户信息
     */
    public function login(){
        if(!empty(cookie('userinfo'))){
            header("Location:".U('User/myPage'));exit;
        }
        //echo  232;exit;
        Vendor('Extend.weixin');
        $weixin = new \weixin();
        $host = 'http://'.$_SERVER['HTTP_HOST'];
            $url=$weixin->GetCode(APPID,$host.'/Common/recode',1);
        //var_dump($url);exit;
            header("Location:".$url);

    }


    /**
     * 接受code
     */
    public function recode(){
//echo 11;exit;
//        $this->display('login');exit;
        $appid = APPID;
        $secret = SECRET;
        @$code = $_GET["code"];
        $get_token_url = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid='.$appid.'&secret='.$secret.'&code='.$code.'&grant_type=authorization_code';
        $ch = curl_init();
        curl_setopt($ch,CURLOPT_URL,$get_token_url);
        curl_setopt($ch,CURLOPT_HEADER,0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        $res = curl_exec($ch);
        curl_close($ch);
        $json_obj = json_decode($res,true);
//根据openid和access_token查询用户信息
        @$access_token = $json_obj['access_token'];
        @$openid = $json_obj['openid'];

        //var_dump($secret);exit;
        Vendor('Extend.weixin');
        $weixin = new \weixin();
        $userinfo = $weixin->GetUserInfo($openid,$access_token);
        $image = $userinfo->headimgurl;
        $nickname = $userinfo->nickname;
        @session('openid',$openid);
        @session('image',$image);
        @session('nickname',$nickname);
        //var_dump($userinfo);exit;
        //header("Location:".U());
        $this->display('login');
    }

    /**
     * 注册
     */
    public function register(){
        if(IS_POST){
            $phone = I('post.phone');
            $code = I('post.code');
            $password = I('post.password');
            //var_dump(strlen($password));exit;
            if(empty($phone) || empty($code)){
                $this->ajaxReturn(['status'=>0,'msg'=>'手机号或验证码不能为空']);
            }
            if(!preg_match('/^1[345678]\d{9}$/', $phone)){
                $this->ajaxReturn(['status'=>0,'msg'=>'请输入正确的手机号']);
            }
            if(empty($password)){
                $this->ajaxReturn(['status'=>0,'msg'=>'请输入密码']);
            }
            if(strlen($password<6)){
                $this->ajaxReturn(['status'=>0,'msg'=>'密码长度至少为6位']);
            }

            // 验证验证码
            $codeData = M('Code')->where(['phone'=>$phone])->order('createtime desc')->getField('code');
            //var_dump($codeData,$code);exit;
            if($codeData==$code){
                M('Code')->where(['phone'=>$phone])->delete();

                $info = D('Account')->getByphone($phone);

                $data = [];
                // 判断是否已注册
                if(empty($info)){
                    if(empty(I('get.parentid'))){
                        $data = [
                            'phone'=>$phone,
                            'password'=>md5($password),
                            'createtime'=>time()
                        ];

                    }
                    $res = D('Account')->add($data);
                    if($res===false){
                        $this->ajaxReturn(['status'=>0,'msg'=>'注册失败']);
                    }else{
                        $this->ajaxReturn(['status'=>1,'msg'=>'注册成功']);
                    }

                }else{
                    $this->ajaxReturn(['status'=>0,'msg'=>'该手机号已存在']);
                }
            }else{
                $this->ajaxReturn(['status'=>0,'msg'=>'请输入正确的验证码']);
            }
        }else{
            $this->display();
        }
    }
    /**
     * @param null $phone  手机号
     * @param null $code   验证码
     */
    public function checkLogin($phone=null,$code=null){
        $phone = I('post.phone');
        $password = I('post.password');
        if(empty($phone) || empty($password)){
            $this->ajaxReturn(['status'=>0,'msg'=>'用户名或密码不能为空']);
        }
        if(!preg_match('/^1[345678]\d{9}$/', $phone)){
            $this->ajaxReturn(['status'=>0,'msg'=>'请输入正确的手机号']);
        }
        $info = D('Account')->getByphone($phone);
        $data = [];
        // 判断是否已注册
        if(empty($info)){
            $this->ajaxReturn(['status'=>0,'msg'=>'该手机号还未注册']);
        }else{
            if(md5($password)!=$info['password']){
                $this->ajaxReturn(['status'=>0,'msg'=>'请输入正确的密码']);
            }
            $data = [
                'id'=>$info['id'],
                'image'=>session('image'),
                'openid'=>session('openid'),
                'nickname'=>session('nickname'),
                'last_ip'=>get_client_ip(),
                'last_time'=>time()
            ];
            if(empty($data['openid'])){
                unset($data['openid']);
            }
            if(empty($data['image'])){
                unset($data['image']);
            }
            if(empty($data['nickname'])){
                unset($data['nickname']);
            }
            $res = D('Account')->save($data);
            if($res===false){
                $this->ajaxReturn(['status'=>0,'msg'=>'登陆异常，请重新登录']);
            }else{
                $info['image'] = $data['image'];
                $info['last_ip'] = $data['last_ip'];
                $info['last_time'] = $data['last_time'];
                $info['openid'] = $data['openid'];
                $info['nickname'] = $data['nickname'];
                cookie('userinfo',$info,time()+31536000,'/');
                $this->ajaxReturn(['status'=>1,'msg'=>'登录成功']);
            }
        }

    }

    /**
     * 获取手机验证码
     * @param null $phone  手机号
     */
    public function getCode($phone=null){
        $phone = I('post.phone');
        $type = I('post.type');
        //var_dump($phone);exit;
        $info = D('Account')->getByphone($phone);
        if(!empty($info) &&$type==1){
            $this->ajaxReturn(['status'=>0,'msg'=>'该手机号已注册']);
        }
        if(empty($info) &&$type==2){
            $this->ajaxReturn(['status'=>0,'msg'=>'该手机号还未注册']);
        }
        $code = createRandomNum(4);
        $content = '【联聚国际】 您的验证码为'.$code;
        $res = sendSms($content,$phone);
        //var_dump($res);exit;
        if($res=='短信发送成功'){
            M('Code')->add(['code'=>$code,'phone'=>$phone,'createtime'=>time()]);
            $this->ajaxReturn(['status'=>1,'msg'=>'验证码发送成功']);
        }else{
            $this->ajaxReturn(['status'=>0,'msg'=>$res]);
        }
    }

    /**
     * 退出
     */
    public function logout(){
        cookie('userinfo',null);
        $this->ajaxReturn(['state'=>1,'msg'=>'退出成功']);
    }


    
   

    /**
     * 分享后的页面
     * @param null $nickname  昵称
     * @param null $image     头像
     */
    public function share($nickname=null,$image=null){
        $this->assign('nickname',$nickname);
        $image = strstr($_SERVER['PHP_SELF'],'/image');
        $image = strstr($image,'http:/');
        $image = str_replace('http:/','http://' , $image);
        //var_dump($image);exit;
        $this->assign('image',$image);
        
        // 总加入人数
        $this->assign('total',M('UserProduct')->count());

        // 产品结果集
        $re = D('HuzhuProduct')->where(['status'=>1])->field('id,image,property,name,insure,range,age')->select();
        foreach($re as $k=>$v){
            $count = M('UserProduct')->where(['product_id'=>$v['id']])->count();
            $re[$k]['count'] = $count;
        }
        $this->assign('re',$re);
        $images = M('UserProduct')->alias('a')->join('left join huzhu_user as b on a.user_id=b.id')
                                 ->order('a.createtime desc')->field('b.image')->limit(5)->select();
        $this->assign('images',$images);
        //var_dump($images);exit;
       /* header("Content-type:text/html;charset=utf-8");
        var_dump($image,M('UserProduct')->count(),$re);exit;*/
        //var_dump($images[0]['image'],$images[1]['image']);exit;
        $this->display();
    }




}