<?php 
//if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * @微信服务操作
 * @author zhoushuai
 * @license		正大图腾
 * @copyright(c) 2015-10-16
 * @version
 */
class WxService
{
    private $CI = NULL;
    private $appid = "wx10d9364013fad77c";
    private $appsecret = "ac8298b78ce5298c7def02345d75aa40";
    private $access_token = '';
    private $expires;
    private $lasttime;

//    public function __construct()
//    {
//        $this->CI =& get_instance();
//        $this->initWx();
//    }

    /**
     * @获取配置文件
     * @获取AccessToken
     */
//    private function initWx()
//    {
//        if (ENVIRONMENT!=='development'){
//            $this->_init();
//            $this->access_token=getMmemData('access_zdoa_token');
//            if (empty($this->access_token)) {
//                $this->initAccessToken();
//            }
//        }
//        return true;
//    }


    private function _init(){
        $this->CI->load->config('wxconfig');
        $wxconfig = $this->CI->config->item(ENVIRONMENT);
        $this->appid     = $wxconfig['appid'];
        $this->appsecret = $wxconfig['appsecret'];
    }

    public function AccessToken(){
        //if(empty(S('access_token'))){
            $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=" .$this->appid . "&secret=" . $this->appsecret;
            $res = $this->https_request($url);
            $result = json_decode($res, true);
            $this->access_token = $result["access_token"];
        //    S('access_token',$result["access_token"],$result["expires_in"]-10);
        //}else{
        //    $this->access_token = S('access_token');
        //}


//        $this->expires = $result['expires_in']-600;
//        setMmemData('access_zdoa_token',$this->access_token,false,$this->expires);
//        $this->lasttime = time();
        return $this->access_token;
    }

    /**
     * @第一步：用户同意授权，获取code
     * @param redirect_uri 回调地址
     */
    public function getcode($redirect_uri){
        $uri = urlencode(site_url($redirect_uri));//snsapi_base //snsapi_userinfo
        $url = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid=' . $this->appid . '&redirect_uri=' . $uri . '&response_type=code&scope=snsapi_base&state='.TOKEN.'#wechat_redirect';
        redirect($url);
    }


    /**
     * 第二步：通过code换取网页授权access_token 得到openid 并存入session
     * @param $code
     * @param redirect_uri 回调地址
     */
    public function getOpenid($code,$redirect_uri){
        $url = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid='.$this->appid.'&secret='.$this->appsecret.'&code='.$code.'&grant_type=authorization_code';
        $data = httpsRequest($url);
        $data = json_decode($data,true);
        if(isset($data['errcode']) && $data['errcode']=='40029'){
            $this->getcode($redirect_uri);
        }
        $openid = $data['openid'];
        $_SESSION['openid']= $openid;
    }







    //获取关注者列表
    public function get_wx_user_list($next_openid = null)
    {
        $url = "https://api.weixin.qq.com/cgi-bin/user/get?access_token=" . $this->access_token . "&next_openid=" . $next_openid;
        $res = $this->https_request($url);
        return json_decode($res, true);
    }

    //获取用户基本信息
    public function get_wx_user_info($openid)
    {
        $url = "https://api.weixin.qq.com/cgi-bin/user/info?access_token=" . $this->access_token . "&openid=" . $openid . "&lang=zh_CN";
        $res = $this->https_request($url);
        return json_decode($res, true);
    }

    /**
     * @创建自定义的菜单
     * @param data array;
     * @return <array>
     */
    public function create_wx_menu($data)
    {
        $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=" .$this->appid . "&secret=" . $this->appsecret;
        $res = $this->https_request($url);
        $result = json_decode($res, true);
        $this->access_token = $result["access_token"];
        //var_dump($this->access_token);exit;
        //$data = $this->json_unescaped($data);
        $url = "https://api.weixin.qq.com/cgi-bin/menu/create?access_token=" . $this->access_token;
        $res = $this->https_request($url, $data);
        return json_decode($res, true);
    }

    /**
     * @自定义菜单查询
     */

    public function get_wx_menu(){
        $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=" .$this->appid . "&secret=" . $this->appsecret;
        $res = $this->https_request($url);
        $result = json_decode($res, true);
        $this->access_token = $result["access_token"];
        $url = "https://api.weixin.qq.com/cgi-bin/menu/get?access_token=". $this->access_token;
        $res = $this->https_request($url);
        return json_decode($res, true);
    }


    /**
     * @自定义菜单删除
     */

    public function del_wx_menu(){
        $url = "https://api.weixin.qq.com/cgi-bin/menu/delete?access_token=". $this->access_token;
        $res = $this->https_request($url);
        return json_decode($res, true);
    }

    /**
     * @获取自动回复规则
     */
    public function get_current_autoreply_info(){
        $url = "https://api.weixin.qq.com/cgi-bin/get_current_autoreply_info?access_token=". $this->access_token;
        $res = $this->https_request($url);
        return json_decode($res, true);
    }


    //发送客服消息，已实现发送文本，其他类型可扩展
    public function send_custom_message($touser, $type, $data)
    {
        $msg = array('touser' => $touser);
        switch ($type) {
            case 'text':
                $msg['msgtype'] = 'text';
                $msg['text'] = array('content' => urlencode($data));
                break;
        }
        $url = "https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=" . $this->access_token;
        return $this->https_request($url, urldecode(json_encode($msg)));
    }

    //生成参数二维码
    public function create_wx_qrcode($scene_type, $scene_id)
    {
        switch ($scene_type) {
            case 'QR_LIMIT_SCENE': //永久
                $data = '{"action_name": "QR_LIMIT_SCENE", "action_info": {"scene": {"scene_id": ' .
                    $scene_id . '}}}';
                break;
            case 'QR_SCENE': //临时
                $data = '{"expire_seconds": 1800, "action_name": "QR_SCENE", "action_info": {"scene": {"scene_id": ' .
                    $scene_id . '}}}';
                break;
        }
        $url = "https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token=" . $this->access_token;
        $res = $this->https_request($url, $data);
        $result = json_decode($res, true);
        return "https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=" . urlencode($result["ticket"]);
    }

    /**
     * @长链接转短链接接口
     */
    public function getWxShortUrl($longUrl){
        $data = '{"action":"long2short","long_url":"'.$longUrl.'"}';
        $url = "https://api.weixin.qq.com/cgi-bin/shorturl?access_token=".$this->access_token;
        $res = $this->https_request($url, $data);
        return json_decode($res, true);
    }


    /**
     * @创建分组
     */
    public function create_group($name)
    {
        $data = '{"group": {"name": "' . $name . '"}}';
        $url = "https://api.weixin.qq.com/cgi-bin/groups/create?access_token=" . $this->access_token;
        $res = $this->https_request($url, $data);
        return json_decode($res, true);
    }

    /**
     * @移动用户分组
     */
    public function update_group($openid, $to_groupid)
    {
        $data = '{"openid":"' . $openid . '","to_groupid":' . $to_groupid . '}';
        $url = "https://api.weixin.qq.com/cgi-bin/groups/members/update?access_token=" . $this->access_token;
        $res = $this->https_request($url, $data);
        return json_decode($res, true);
    }

    /**
     * @上传多媒体文件
     */
    public function upload_media($type, $file)
    {
        $data = array("media" => "@" . dirname(__file__) . '\\' . $file);
        $url = "http://file.api.weixin.qq.com/cgi-bin/media/upload?access_token=" . $this->access_token . "&type=" . $type;
        $res = $this->https_request($url, $data);
        return json_decode($res, true);
    }


    /**
     * @获取素材总数
     */
    public function  get_materialcount(){
        $url = "https://api.weixin.qq.com/cgi-bin/material/get_materialcount?access_token=". $this->access_token;
        $res = $this->https_request($url);
        return json_decode($res, true);
    }

    /**
     * @获取素材列表
     * @type	 是	 素材的类型，图片（image）、视频（video）、语音 （voice）、图文（news）
     * @offset	 是	 从全部素材的该偏移位置开始返回，0表示从第一个素材 返回
     * @count	 是	 返回素材的数量，取值在1到20之间
     */
    public function get_batchget_material($type,$offset,$count){
        $data = array(
            'type'=>$type,
            'offset'=>$offset,
            'count'=>$count,
        );
        $data = json_unescaped($data);
        $url = "https://api.weixin.qq.com/cgi-bin/material/batchget_material?access_token=". $this->access_token;
        $res = $this->https_request($url,$data);
        return json_decode($res, true);
    }


    /**
     * @删除永久素材
     * @media_id	 是	 要获取的素材的media_id
     */
    public function del_material($media_id){
        $data = array("media_id"=>$media_id);
        $data = json_unescaped($data);
        $url = "https://api.weixin.qq.com/cgi-bin/material/del_material?access_token=". $this->access_token;
        $res = $this->https_request($url,$data);
        return json_decode($res, true);
    }

    /**
     * @获取永久素材
     * @media_id	 是	 要获取的素材的media_id
     */
    public function get_material($media_id){
        $data = array("media_id"=>trim($media_id));
        $data = json_unescaped($data);
        $url = "https://api.weixin.qq.com/cgi-bin/material/get_material?access_token=". $this->access_token;
        $res = $this->https_request($url,$data);
        return json_decode($res, true);
    }

    /**
     * @获取图片素材
     */
    public function get_image_material($media_id){
        $data = array("media_id"=>$media_id);
        $data = json_unescaped($data);
        $url = "https://api.weixin.qq.com/cgi-bin/material/get_material?access_token=". $this->access_token;
        $res = $this->https_request($url,$data);
        return $res;
    }


    /**
     * 获取临时素材
     */
    public function get_thumb_material($media_id){
        $url = "https://api.weixin.qq.com/cgi-bin/media/get?access_token=". $this->access_token."&media_id=".$media_id;
        $res = $this->https_request($url);
        return json_decode($res, true);
    }
    /**
     * @模板消息接口:设置所属行业
     * @param industry_id1 int 主营行业代码
     * @param industry_id2 int 副营行业代码
     * http://mp.weixin.qq.com/wiki/17/304c1885ea66dbedf7dc170d84999a9d.html
     * 查询行业代码
     */
    public function set_industry($industry_id1,$industry_id2){
        $data = array("industry_id1"=>$industry_id1,'industry_id2'=>$industry_id2);
        $data = json_unescaped($data);
        $url = "https://api.weixin.qq.com/cgi-bin/template/api_set_industry?access_token=". $this->access_token;
        $res = $this->https_request($url,$data);
        return $res;
    }


    /**
     * @模板消息接口:获得模板ID
     * @@param  template_id_short	 是	 模板库中模板的编号，有“TM**”和“OPENTMTM**”等形式
     */
    public function getTemplateID($template_id_short){
        $data = array("template_id_short"=>$template_id_short);
        $data = json_unescaped($data);
        $url = "https://api.weixin.qq.com/cgi-bin/template/api_add_template?access_token=". $this->access_token;
        $res = $this->https_request($url,$data);
        $res = json_decode($res, true);
        if($res['errcode']==0&&$res['errmsg']=='ok'){
            return $res['template_id'];
        }else{
            return false;
        }
    }


    function json_unescaped($arr)
    {
        array_walk_recursive($arr, function (&$item, $key)
        {
            if (is_string($item))$item = mb_encode_numericentity($item, array(
                0x80,
                0xffff,
                0,
                0xffff), 'UTF-8'); }
        );
        return str_replace("\\/", "/", mb_decode_numericentity(json_encode($arr), array
        (
            0x80,
            0xffff,
            0,
            0xffff), 'UTF-8'));
    }

    /**
     * @模板消息接口:发送模板消息
     * @param touser 接收模板消息的用户的openid
     * @param template_id  模板ID
     * @param url 详情地址
     */
    public function sendTemplateMsg($openid,$template_id,$url,$data,$access_token){
       $sendata = array(
            'touser'=>$openid,
            'template_id'=>$template_id,
            'url'=>$url,
            'data'=>$data
        );
//        $this->initAccessToken();
        $data = $this->json_unescaped($sendata);
        //var_dump($access_token);exit;
//        $data = json_encode($sendata);
//        echo $data;exit;
        $url = "https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=". $access_token;
        $res = $this->https_request($url,$data);
        $res = json_decode($res, true);
        //var_dump($res);exit;
        if($res['errcode']==0 && $res['errmsg']=='ok'){
            return array('status'=>1,'msg'=>$res['errmsg'],'data'=>$res['msgid']);
        }else{
            return array('status'=>0,'msg'=>$res['errmsg'],'data'=>$res['errcode']);
        }
    }


    /**
     * @多客服
     * @获取在线客服接待信息
     * @return kf_account	客服账号@微信别名
     * @return status	客服在线状态 1：pc在线，2：手机在线 若pc和手机同时在线则为 1+2=3
     * @return kf_id	客服工号
     * @return auto_accept	客服设置的最大自动接入数
     * @return accepted_case	客服当前正在接待的会话数
     */
    public function get_online_kf_list(){
        $url = "https://api.weixin.qq.com/cgi-bin/customservice/getonlinekflist?access_token=". $this->access_token;
        $res = $this->https_request($url);
        return json_decode($res, true);
    }


    /**
     * @多客服
     * @获取客服基本信息开发者通过本接口
     * @return kf_account	客服账号@微信别名
     * @return kf_nick	客服昵称
     * @return kf_id	客服工号
     */
    public function get_kf_list(){
        $url = "https://api.weixin.qq.com/cgi-bin/customservice/getkflist?access_token=". $this->access_token;
        $res = $this->https_request($url);
        return json_decode($res, true);
    }


    /**
     * @多客服
     * @获取会话记录
     * @return starttime 查询开始时间，UNIX时间戳(秒级别)
     * @return endtime 查询结束时间，UNIX时间戳(秒级别)，每次查询不能跨日查询
     * @return openid 普通用户openid，若不填则查询该appid下所有用户
     * @return pagesize	是	每页大小，每页最多拉取1000条
     * @return pageindex	是	查询第几页，从1开始
     */
    public function getrecord($data){
        $data = array(
            "starttime"=>$data['starttime'],
            "endtime"=>$data['endtime'],
            "openid"=>$data['openid'],
            "pagesize"=>$data['pagesize'],
            "pageindex"=>$data['pageindex']
        );
        $data = array_filter($data);
        $data = json_unescaped($data);
        $url = "https://api.weixin.qq.com/cgi-bin/customservice/getrecord?access_token=". $this->access_token;
        $res = $this->https_request($url,$data);
        return $res;
    }



    /**
     *GPS,谷歌坐标转换成百度坐标
     *@param lnt
     *@param lat
     *@return array
     */
    public function mapApi($lng,$lat,$type){
        $map=array();
        if($type=='gps'){
            $url="http://map.yanue.net/gpsApi.php?lat=".$lat."&lng=".$lng;
            $res=json_decode(file_get_contents($url));
            $map['lng']=$res->baidu->lng;
            $map['lat']=$res->baidu->lat;
        }
        if($type=='google'){
            $url="http://api.map.baidu.com/ag/coord/convert?from=2&to=4&mode=1&x=".$lng."&y=".$lat;
            $res=json_decode(file_get_contents($url));
            $map['lng']=base64_decode($res[0]->x);
            $map['lat']=base64_decode($res[0]->y);
        }
        return $map;
    }

    /**
     * @根据经纬度计算距离和方向
     */
    public function getRadian($d){
        return $d * M_PI / 180;
    }

    /**
     * @根据经纬度计算距离和方向
     */
    public function getDistance ($lat1, $lng1, $lat2, $lng2){
        $EARTH_RADIUS=6378.137;//地球半径
        $lat1 = $this->getRadian($lat1);
        $lat2 = $this->getRadian($lat2);
        $a = $lat1 - $lat2;
        $b = $this->getRadian($lng1) - $this->getRadian($lng2);
        $v = 2 * asin(sqrt(pow(sin($a/2),2) + cos($lat1) * cos($lat2) * pow(sin($b/2),2)));
        $v = round($EARTH_RADIUS * $v * 10000) / 10000;
        return $v;
    }






    /**
     * @https请求（支持GET和POST）
     * @param url string 请求的地址
     * @param data  array 发送的数据
     */
    public function https_request($url, $data = null)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        if (!empty($data)) {
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($curl);
        curl_setopt($curl, CURLOPT_VERBOSE, true);
        curl_close($curl);
        return $output;
    }

    /**
     * 打印日志
     * @param log 日志内容
     */
    public function debuglog($data)
    {
        $log = '
#===============================================================================================================
#DEBUG-WxService start |执行时间：%s (ms)
#===============================================================================================================
#请求接口:%s
#User-Agent:%s
#返回数据(Array):
$data=%s;
#==================================================debug end====================================================' .
            "\r\n\r\n";
        debug_log($log, $data);
    }




}
