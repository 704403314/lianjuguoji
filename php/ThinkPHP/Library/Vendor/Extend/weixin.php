<?php
/*死鱼微信类 v1 QQ：84534856 欢迎加Q交流*/
//define("TOKEN", "xxxxxxxxxx"); //token设置
//define("APPID", "wx0fc019ed4eaf6768");
//define("SECRET", "e4f398eae216bffc49002aa4d2559892");
//define("APPID", "wx3a53b2e64c8df6b8");
//define("SECRET", "9b272f35961ff5129c3d6b8a924a0b70");
class weixin{
	//响应验证
	public function valid()
    {
        $echoStr = $_GET["echostr"];
        if($this->checkSignature()){
        	echo $echoStr;
        	exit;
        }
    }
	
	private function checkSignature()
	{
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];
        		
		$token = TOKEN;
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
	
	//添加菜单
	public function menuadd($tpl_str)
	{
		$access_token = $this->get_access_token(APPID, SECRET);
		$json=json_decode($this->curlPostGet($access_token,2));
//		var_dump($json->access_token);exit;
		$url='https://api.weixin.qq.com/cgi-bin/menu/create?access_token='.$json->access_token;
		return $this->curlPostGet($url, 1, $tpl_str);
	}

	//删除菜单
	public function menudel()
	{
		$access_token = $this->get_access_token(APPID, SECRET);
		$json=json_decode($this->curlPostGet($access_token,2));
		$url='https://api.weixin.qq.com/cgi-bin/menu/delete?access_token='.$json->access_token;
		return $this->curlPostGet($url, 2);
	}

	//获取用户发送消息
	public function GetMsg()
	{
		//echo 123;exit;
		$postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
		//$postStr = file_get_contents('php://input', 'r');
		if (!empty($postStr))
		{
			$postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
			$GMsg["fromUsername"] = $postObj->FromUserName;
			$GMsg["toUsername"] = $postObj->ToUserName;
			$GMsg["CreateTime"] = $postObj->CreateTime;
			$GMsg["MsgType"] = $postObj->MsgType;
			switch ($GMsg["MsgType"]) {
				case "text":
					$GMsg["Content"] = $postObj->Content;
					$GMsg["MsgId"] = $postObj->MsgId;
					break;
				case "image":
					$GMsg["PicUrl"] = $postObj->PicUrl;
					$GMsg["MediaId"] = $postObj->MediaId;
					$GMsg["MsgId"] = $postObj->MsgId;
					break;
				case "voice":
					$GMsg["MediaId"] = $postObj->MediaId;
					$GMsg["Format"] = $postObj->Format;
					$GMsg["MsgID"] = $postObj->MsgID;
					//开启语音识别后
					$GMsg["Recognition"] = $postObj->Recognition;
					break;
				case "video":
					$GMsg["MediaId"] = $postObj->MediaId;
					$GMsg["ThumbMediaId"] = $postObj->ThumbMediaId;
					$GMsg["MsgId"] = $postObj->MsgId;
					break;
				case "location":
					$GMsg["Location_X"] = $postObj->Location_X;
					$GMsg["Location_Y"] = $postObj->Location_Y;
					$GMsg["Scale"] = $postObj->Scale;
					$GMsg["Label"] = $postObj->Label;
					$GMsg["MsgId"] = $postObj->MsgId;
					break;
				case "link":
					$GMsg["Title"] = $postObj->Title;
					$GMsg["Description"] = $postObj->Description;
					$GMsg["Url"] = $postObj->Url;
					$GMsg["MsgId"] = $postObj->MsgId;
					break;
				case "event":
					$GMsg["Event"] = $postObj->Event;
					if ($GMsg["Event"] == "subscribe") //订阅
					{
						$GMsg["EventKey"] = $postObj->EventKey;
						$GMsg["Ticket"] = $postObj->Ticket;
					}
					if ($GMsg["Event"] == "unsubscribe")//取消订阅
					{}
					if ($GMsg["Event"] == "SCAN")//已关注的情况下扫描二维码
					{
						$GMsg["EventKey"] = $postObj->EventKey;
						$GMsg["Ticket"] = $postObj->Ticket;
					}
					if ($GMsg["Event"] == "LOCATION")//上报地理位置
					{
						$GMsg["Latitude"] = $postObj->Latitude;//维度
						$GMsg["Longitude"] = $postObj->Longitude;//经度
						$GMsg["Precision"] = $postObj->Precision;//地理位置精度
					}
					if ($GMsg["Event"] == "CLICK") //点击菜单
					{
						$GMsg["EventKey"] = $postObj->EventKey;//KEY值
					}
					if ($GMsg["Event"] == "VIEW")//点击菜单
					{
						$GMsg["EventKey"] = $postObj->EventKey;//跳转地址
					}
					break;
			}
			return $GMsg;
		}
		else
		{
			return "";
		}
	}

	//发送响应消息
	//发送文本消息时$msg_arr["msg"]为文本内容，发送多媒体消息时$msg_arr["mediaid"]为MediaId
	public function SendMsg($msgtype, $msg_arr = NULL)
	{
		$postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
		if (!empty($postStr))
		{
			$postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
			$fromUsername = $postObj->FromUserName;
			$toUsername = $postObj->ToUserName;
			$time = time();

			if ($msgtype == "text")
			{
				$textTpl = "<xml>
							<ToUserName><![CDATA[%s]]></ToUserName>
							<FromUserName><![CDATA[%s]]></FromUserName>
							<CreateTime>%s</CreateTime>
							<MsgType><![CDATA[%s]]></MsgType>
							<Content><![CDATA[%s]]></Content>
							</xml>";
				$resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgtype, $msg_arr["msg"]);
			}
			if ($msgtype == "image")//图片消息
			{
				$textTpl = "<xml>
							<ToUserName><![CDATA[%s]]></ToUserName>
							<FromUserName><![CDATA[%s]]></FromUserName>
							<CreateTime>%s</CreateTime>
							<MsgType><![CDATA[%s]]></MsgType>
							<Image>
							<MediaId><![CDATA[%s]]></MediaId>
							</Image>
							</xml>";
				$resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgtype, $msg_arr["mediaid"]);
			}
			if ($msgtype == "voice")//语音消息
			{
				$textTpl = "<xml>
							<ToUserName><![CDATA[%s]]></ToUserName>
							<FromUserName><![CDATA[%s]]></FromUserName>
							<CreateTime>%s</CreateTime>
							<MsgType><![CDATA[%s]]></MsgType>
							<Voice>
							<MediaId><![CDATA[%s]]></MediaId>
							</Voice>
							</xml>";
				$resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgtype, $msg_arr["mediaid"]);
			}
			if ($msgtype == "video")//视频消息
			{
				$textTpl = "<xml>
							<ToUserName><![CDATA[%s]]></ToUserName>
							<FromUserName><![CDATA[%s]]></FromUserName>
							<CreateTime>%s</CreateTime>
							<MsgType><![CDATA[%s]]></MsgType>
							<Video>
							<MediaId><![CDATA[%s]]></MediaId>
							<Title><![CDATA[%s]]></Title>
							<Description><![CDATA[%s]]></Description>
							</Video> 
							</xml>";
				$resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgtype, $msg_arr["mediaid"], $msg_arr['title'], $msg_arr['description']);
			}
			if ($msgtype == "music")//音乐消息
			{
				$textTpl = "<xml>
							<ToUserName><![CDATA[%s]]></ToUserName>
							<FromUserName><![CDATA[%s]]></FromUserName>
							<CreateTime>%s</CreateTime>
							<MsgType><![CDATA[%s]]></MsgType>
							<Music>
							<Title><![CDATA[%s]]></Title>
							<Description><![CDATA[%s]]></Description>
							<MusicUrl><![CDATA[%s]]></MusicUrl>
							<HQMusicUrl><![CDATA[%s]]></HQMusicUrl>
							<ThumbMediaId><![CDATA[%s]]></ThumbMediaId>
							</Music>
							</xml>";
				$resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgtype, $msg_arr["title"], $msg_arr['description'], $msg_arr['musicurl'], $msg_arr['hqmusicurl'], $msg_arr['mediaid']);
			}
			if ($msgtype == "news")//图文消息
			{
				$str_newslist = "";
				$arcc = count($msg_arr['news']);
				if ($arcc && $arcc < 11)//最大支持发送10条图文信息，否则返回错误
				{
					$textTpl = "<xml>
								<ToUserName><![CDATA[%s]]></ToUserName>
								<FromUserName><![CDATA[%s]]></FromUserName>
								<CreateTime>%s</CreateTime>
								<MsgType><![CDATA[%s]]></MsgType>
								<ArticleCount>%d</ArticleCount>
								<Articles>%s</Articles>
								</xml>";
					$newlistTpl = "<item>
								<Title><![CDATA[%s]]></Title> 
								<Description><![CDATA[%s]]></Description>
								<PicUrl><![CDATA[%s]]></PicUrl>
								<Url><![CDATA[%s]]></Url>
								</item>";
					foreach ($msg_arr['news'] as $newslist)
					{
						$str_newslist .= sprintf($newlistTpl, $newslist['title'], $newslist['description'], $newslist['picurl'], $newslist['url']);
					}
					$resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgtype, $arcc, $str_newslist);
				}
				else
				{
					$resultStr = "error";
				}
			}
			echo $resultStr;
		}
	}

	//发送客服消息
	public function SendCustomMsg($openid, $msgtype, $msg_arr = NULL)
	{
		$access_token = $this->get_access_token(APPID, SECRET);
		$json=json_decode($this->curlPostGet($access_token,2));
		$url='https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token='.$json->access_token;
		if($msgtype == "text")//文本消息
		{
			$textTpl = '{
							"touser":"%s",
							"msgtype":"%s",
							"%s":
							{
								 "content":"%s"
							}
						}';
			$data = sprintf($textTpl, $openid, $msgtype, $msgtype, $msg_arr["msg"]);
		}
		if($msgtype == "image")//图片消息
		{
			$textTpl = '{
							"touser":"%s",
							"msgtype":"%s",
							"%s":
							{
							  "media_id":"%s"
							}
						}';
			$data = sprintf($textTpl, $openid, $msgtype, $msgtype, $msg_arr["mediaid"]);
		}
		if($msgtype == "voice")//语音消息
		{
			$textTpl = '{
							"touser":"%s",
							"msgtype":"%s",
							"%s":
							{
							  "media_id":"%s"
							}
						}';
			$data = sprintf($textTpl, $openid, $msgtype, $msgtype, $msg_arr["mediaid"]);
		}
		if($msgtype == "video")//视频消息
		{
			$textTpl = '{
							"touser":"%s",
							"msgtype":"%s",
							"%s":
							{
							  "media_id":"%s",
							  "title":"%s",
							  "description":"%s"
							}
						}';
			$data = sprintf($textTpl, $openid, $msgtype, $msgtype, $msg_arr["mediaid"], $msg_arr["title"], $msg_arr["description"]);
		}
		if($msgtype == "music")//音乐消息
		{
			$textTpl = '{
							"touser":"%s",
							"msgtype":"%s",
							"%s":
							{
							  "title":"%s",
							  "description":"%s",
							  "musicurl":"%s",
							  "hqmusicurl":"%s",
							  "thumb_media_id":"%s" 
							}
						}';
			$data = sprintf($textTpl, $openid, $msgtype, $msgtype, $msg_arr["title"], $msg_arr["description"], $msg_arr["musicurl"], $msg_arr["hqmusicurl"], $msg_arr["mediaid"]);
		}
		if($msgtype == "news")//图文消息
		{
			$str_newslist = "";
			$arcc = count($msg_arr['news']);
			if ($arcc && $arcc < 11)//最大支持发送10条图文信息，否则返回错误
			{
				$textTpl = '{
								"touser":"%s",
								"msgtype":"%s",
								"%s":{
									"articles": [%s]
								}
							}';
				$newlistTpl = '{
									"title":"%s",
									"description":"%s",
									"url":"%s",
									"picurl":"%s"
								}';
				foreach ($msg_arr['news'] as $newslist)
				{
					$str_newslist_arr[] = sprintf($newlistTpl, $newslist['title'], $newslist['description'], $newslist['url'], $newslist['picurl']);
				}
				$str_newslist = implode(",", $str_newslist_arr);

				$data = sprintf($textTpl, $openid, $msgtype, $msgtype, $str_newslist);
			}
			else
			{
				return "error";
			}
		}
		return $this->curlPostGet($url, 1, $data);

	}
	
	//获取用户基本信息
	public function GetUserInfo($openid)
	{
		$access_token = $this->get_access_token(APPID, SECRET);
		$json=json_decode($this->curlPostGet($access_token,2));
		$url='https://api.weixin.qq.com/cgi-bin/user/info?access_token='.$json->access_token.'&openid='.$openid.'&lang=zh_CN';
		return json_decode($this->curlPostGet($url, 2));
	}

	//创建分组
	public function CreateGroup($groupname)
	{
		$access_token = $this->get_access_token(APPID, SECRET);
		$json=json_decode($this->curlPostGet($access_token,2));
		$url='https://api.weixin.qq.com/cgi-bin/groups/create?access_token='.$json->access_token;
		$textTpl = '{"group":{"name":"%s"}}';
		$data = sprintf($textTpl, $groupname);
		return $this->curlPostGet($url, 1, $data);
	}

	//查询所有分组
	public function SearchGroup()
	{
		$access_token = $this->get_access_token(APPID, SECRET);
		$json=json_decode($this->curlPostGet($access_token,2));
		$url='https://api.weixin.qq.com/cgi-bin/groups/get?access_token='.$json->access_token;
		return json_decode($this->curlPostGet($url, 2));
	}
	
	//查询用户所在分组
	public function SearchUserGroup($openid)
	{
		$access_token = $this->get_access_token(APPID, SECRET);
		$json=json_decode($this->curlPostGet($access_token,2));
		$url='https://api.weixin.qq.com/cgi-bin/groups/getid?access_token='.$json->access_token;
		$textTpl = '{"openid":"%s"}';
		$data = sprintf($textTpl, $openid);
		return $this->curlPostGet($url, 1, $data);
	}

	//修改分组名
	public function UpdateGroupName($groupid, $groupname)
	{
		$groupid = intval($groupid);
		$access_token = $this->get_access_token(APPID, SECRET);
		$json=json_decode($this->curlPostGet($access_token,2));
		$url='https://api.weixin.qq.com/cgi-bin/groups/update?access_token='.$json->access_token;
		$textTpl = '{"group":{"id":%d,"name":"%s"}}';
		$data = sprintf($textTpl, $groupid, $groupname);
		return $this->curlPostGet($url, 1, $data);
	}

	//移动用户分组
	public function MoveToGroup($openid, $groupid)
	{
		$groupid = intval($groupid);
		$access_token = $this->get_access_token(APPID, SECRET);
		$json=json_decode($this->curlPostGet($access_token,2));
		$url='https://api.weixin.qq.com/cgi-bin/groups/members/update?access_token='.$json->access_token;
		$textTpl = '{"openid":"%s","to_groupid":%d}';
		$data = sprintf($textTpl, $openid, $groupid);
		return $this->curlPostGet($url, 1, $data);
	}

	//获取关注者列表
	public function GetFollowList($nextopenid = NUll)
	{
		//关注者大于10000时递归取列表
		//global $followlist_arr;

		$access_token = $this->get_access_token(APPID, SECRET);
		$json=json_decode($this->curlPostGet($access_token,2));
		$url='https://api.weixin.qq.com/cgi-bin/user/get?access_token='.$json->access_token."&next_openid=".$nextopenid;
		
		//关注者大于10000时递归取列表
		/*
		$followlist = json_decode($this->curlPostGet($url, 2),TRUE);
		if($followlist["next_openid"])
		{
			$followlist_arr[] = $followlist;
			$this->GetFollowList($followlist["next_openid"]);
		}
		return $followlist_arr;
		*/

		return json_decode($this->curlPostGet($url, 2));
	}

	//网页授权获取用户基本信息
	//$redirect_url:用户指定跳转网址
	//$scope:0:snsapi_base （不弹出授权页面，直接跳转，只能获取用户openid），1:snsapi_userinfo （弹出授权页面，可通过openid拿到昵称、性别、所在地
	//$state:重定向后会带上state参数，开发者可以填写a-zA-Z0-9的参数值
	public function GetCode($appid,$redirect_url,$scope,$state = NULL)
	{
		if (intval($scope) == 0)
		{
			$scope = 'snsapi_base';
		}
		else
		{
			$scope = 'snsapi_userinfo';
		}
		$url = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid='.$appid.'&redirect_uri='.$redirect_url.'&response_type=code&scope='.$scope.'&state='.$state.'#wechat_redirect';
		return $url;
	}

	//拉取用户信息(需scope为 snsapi_userinfo)
	public function	WebGetUserInfo($access_token,$openid,$lang = "zh_CN") //zh_CN 简体，zh_TW 繁体，en 英语
	{
		$url = "https://api.weixin.qq.com/sns/userinfo?access_token=".$access_token."&openid=".$openid."&lang=".$lang;
		return json_decode($this->curlPostGet($url, 2));
	}

/*
图片（image）: 128K，支持JPG格式
语音（voice）：256K，播放长度不超过60s，支持AMR\MP3格式
视频（video）：1MB，支持MP4格式
缩略图（thumb）：64KB，支持JPG格式
注意，上传缩略图（thumb）返回的media_id名称为thumb_media_id，其他3中均为media_id
*/
	//上传多媒体文件
	public function UploadFile($fileurl, $type)
	{
		$access_token = $this->get_access_token(APPID, SECRET);
		$json=json_decode($this->curlPostGet($access_token,2));
		//$realfile = dirname(__FILE__).$fileurl;
		$realfile = ROOT_PATH .$fileurl;
		$data = array("media"  => "@".$realfile);
		$url='http://file.api.weixin.qq.com/cgi-bin/media/upload?access_token='.$json->access_token.'&type='.$type;
		return $this->curlPostGet($url, 1, $data);
	}
	
	//下载多媒体文件
	public function DownloadFile($media_id)
	{
		$access_token = $this->get_access_token(APPID, SECRET);
		$json=json_decode($this->curlPostGet($access_token,2));
		$url='http://file.api.weixin.qq.com/cgi-bin/media/get?access_token='.$json->access_token.'&media_id='.$media_id;
		//return $this->curlPostGet($url, 2);
		$ch = curl_init();
		$header = "Accept-Charset: utf-8";
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)');
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$temp = curl_exec($ch);
		$http = curl_getinfo($ch);
		$http['media'] = $temp;
		return $http;
	}

	//生成带参数的二维码
	public function MakeQRcode($scene, $expire = 0)
	{
		$access_token = $this->get_access_token(APPID, SECRET);
		$json=json_decode($this->curlPostGet($access_token,2));
		$url = 'https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token='.$json->access_token;
		if($expire > 0)
		{
			$arr['expire_seconds'] = $expire;
			$arr['action_name'] = "QR_SCENE";
			$arr['action_info']["scene"]["scene_id"] = $scene;
			//$QRTpl = '{"expire_seconds": '.$expire.', "action_name": "QR_SCENE", "action_info": {"scene": {"scene_id": '.$scene.'}}}';
		}
		else
		{
			$arr['action_name'] = "QR_LIMIT_SCENE";
			$arr['action_info']["scene"]["scene_id"] = $scene;
			//$QRTpl = '{"action_name": "QR_LIMIT_SCENE", "action_info": {"scene": {"scene_id": '.$scene.'}}}';
		}
		$QRTpl = json_encode($arr);
		return $this->curlPostGet($url, 1, $QRTpl);
	}

	//通过ticket换取二维码
	public function GetQRcode($ticket)
	{
		$url = 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket='.urlencode($ticket);
		return $url;
	}
	
	//保存多媒体文件
	public function SaveMedia($media, $filename = NULL)
	{
		//视频文件不支持下载
		switch ($media["content_type"]) {
			case "image/jpeg":
				$suffix = ".jpg";
				break;
			case "audio/amr":
				$suffix = ".mp3";
				break;
		}

		if ($filename == NULL)
		{
			$filename = "sf_".time().$suffix;
		}

		$file = fopen($filename, 'w');
		if ($file !== false){
			$result = fwrite($file, $media["media"]);
			if($result)
			{
				fclose($file);
			}
			return $filename;
		}
		else
		{
			return false;
		}
	}
	
	//curl get post
	function curlPostGet($url, $type = 1, $data = NULL){
		if($type == 1)
		{
			$method = "POST";
		}
		else
		{
			$method = "GET";
		}
		$ch = curl_init();
		$header = "Accept-Charset: utf-8";
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)');
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$temp = curl_exec($ch);
		/*
		if($type == 1)
		{
			if (curl_errno($ch)) {
				return false;
			}else{
				return true;
			}
		}
		*/
		return $temp;
	}

	/*高级群发接口 注意：需要认证服务号才能使用该接口，否则群发返回 48001 api功能未授权*/
	//上传图文消息素材
	public function UploadImgTEXT($msg_arr)
	{
		$str_newslist = "";
		$access_token = $this->get_access_token(APPID, SECRET);
		$json=json_decode($this->curlPostGet($access_token,2));
		$url = 'https://api.weixin.qq.com/cgi-bin/media/uploadnews?access_token='.$json->access_token;
		$textTpl = '{
					   "articles": [%s]
					}';
		$newlistTpl = '{
							"thumb_media_id":"%s",
							"author":"%s",
							"title":"%s",
							"content_source_url":"%s",
							"content":"%s",
							"digest":"%s"
						}';
		foreach ($msg_arr as $newslist)
		{
			$str_newslist_arr[] = sprintf($newlistTpl, $newslist['thumb_media_id'], $newslist['author'], $newslist['title'], $newslist['content_source_url'], $newslist['content'], $newslist['digest']);
		}
		$str_newslist = implode(",", $str_newslist_arr);
		$data = sprintf($textTpl, $str_newslist);

		return $this->curlPostGet($url, 1, $data);
	}

	//根据分组进行群发
	public function GroupSend($group_id, $media_id)
	{
		$access_token = $this->get_access_token(APPID, SECRET);
		$json=json_decode($this->curlPostGet($access_token,2));
		$url = 'https://api.weixin.qq.com/cgi-bin/message/mass/sendall?access_token='.$json->access_token;
		$textTpl = '{
					   "filter":{
						  "group_id":"%s"
					   },
					   "mpnews":{
						  "media_id":"%s"
					   },
						"msgtype":"mpnews"
					}';
		$data = sprintf($textTpl, $group_id, $media_id);
		return $this->curlPostGet($url, 1, $data);
	}

	/*end 高级群发接口*/

	//获取全局唯一票据
	public function get_access_token($appid, $secret, $grant_type = "client_credential")
	{
		
		$access_token = "https://api.weixin.qq.com/cgi-bin/token?grant_type=".$grant_type."&appid=".$appid."&secret=".$secret;
		return $access_token;
	}

	//通过code换取网页授权access_token
	public function get_web_access_token($appid, $secret, $code, $grant_type = "authorization_code")
	{
		$access_token = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=".$appid."&secret=".$secret."&code=".$code."&grant_type=".$grant_type;
		$json=json_decode($this->curlPostGet($access_token,2),TRUE);
		return $json;
	}

	//刷新access_token
	public function refresh_access_token($appid, $refresh_token, $grant_type = "refresh_token")
	{
		$url = "https://api.weixin.qq.com/sns/oauth2/refresh_token?appid=".$appid."&grant_type=".$grant_type."&refresh_token=".$refresh_token;
		$json=json_decode($this->curlPostGet($access_token,2),TRUE);
		return $json;
	}
}

/*----------------------------------------------死鱼微信类 使用说明-----------------------------------------------------------------------------*/
//$sifish = new sifish_wechat();
//高级群发接口
//$json=json_decode($sifish->UploadFile("/demo1.jpg","thumb"));//缩略图
/*
$msg_arr["thumb_media_id"] = $json->thumb_media_id;
$msg_arr["author"] = "author";
$msg_arr["title"] = "title";
$msg_arr["content_source_url"] = "http://www.qq.com";
$msg_arr["content"] = "content";
$msg_arr["digest"] = "digest";

$msg_arr = array (
    array ("thumb_media_id"=>$json->thumb_media_id, "author"=>"author", "title"=>"title", "content_source_url"=>"http://www.qq.com", "content"=>"content", "digest"=>"digest"),
	array ("thumb_media_id"=>$json->thumb_media_id, "author"=>"author", "title"=>"title", "content_source_url"=>"http://www.qq.com", "content"=>"content", "digest"=>"digest")
);
$obj = json_decode($sifish->UploadImgTEXT($msg_arr));
var_dump($sifish->GroupSend(0, $obj->media_id));
*/
//生成带参数的二维码
//$json=json_decode($sifish->MakeQRcode(10, 1800));
//header("Location: ".$sifish->GetQRcode($json->ticket));

//$result = $sifish->SendCustomMsg("openid","text","测试消息123".$i);
//var_dump($result);

//上传多媒体文件
//var_dump($sifish->UploadFile("/demo1.jpg","thumb"));

//下载多媒体文件
//$media = $sifish->DownloadFile("xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx");
//$return = $sifish->SaveMedia($media,"dream.jpg");
//print_r($return);


//获取用户信息
//$return = $sifish->GetUserInfo("openid");
//print_r($return);

//创建分组
//print_r(json_decode($sifish->CreateGroup("分组")));

//查询所有分组
//print_r($sifish->SearchGroup());

//查询用户所在分组
//echo $sifish->SearchUserGroup("openid");

//修改分组名
//print_r(json_decode($sifish->UpdateGroupName(100, "分组")));

//移动用户分组
//print_r(json_decode($sifish->MoveToGroup("openid", 0)));

//获取关注者列表
//print_r($sifish->GetFollowList("openid"));

//网页授权获取用户基本信息
/*
$redirect_url = "http://www.youwebsite.com/youpage.php";
$scope = 1;
$state = 0;
$url = $sifish->GetCode(APPID,$redirect_url,$scope,$state);
$msg_arr["news"] = array (
    array ("title"=>"title", "description"=>"description", "picurl"=>"http://www.youwebsite.com/jpg.jpg", "url"=>$url),
);
$sifish->SendCustomMsg("openid", "news", $msg_arr);
*/

//通过code换取网页授权access_token
/*
$code = $_GET["code"];
header("Content-type: text/html; charset=utf-8");
if ($code)
{
	$arr = $sifish->get_web_access_token(APPID, SECRET, $code);
	print_r($arr);
}
*/


//刷新access_token
/*
$refresh_token = "xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx";
$arr = $sifish->refresh_access_token(APPID, $refresh_token);
print_r($arr);
*/

//拉取用户信息(需scope为 snsapi_userinfo)
/*
$userarr = $sifish->WebGetUserInfo($arr["access_token"],$arr["openid"]);
echo "<br><br><br><br>";
print_r($userarr);
*/

//删除菜单
//$return = $sifish->menudel();

//添加菜单
//$tpl_str = '{"button":[{"type":"click","name":"今日歌曲","key":"V1001_TODAY_MUSIC"},{"type":"click","name":"歌手简介","key":"V1001_TODAY_SINGER"},{"name":"菜单","sub_button":[{	"type":"view","name":"搜索","url":"http://www.soso.com/"},{"type":"view","name":"视频","url":"http://v.qq.com/"},{"type":"click","name":"赞一下我们","key":"V1001_GOOD"}]}]}';
//$return = $sifish->menuadd($tpl_str);
//print_r($return);

//获取用户发送消息
//$arr = $sifish->GetMsg();
//发送响应消息
/*
if ($arr['Content'] == "sfwechat")
{
	$msg_arr["msg"] = "谢谢使用";
	$sifish->SendMsg("text", $msg_arr);
}
*/
//发送图片消息
/*
$json=json_decode($sifish->UploadFile("/demo.jpg","image"));
$msg_arr["mediaid"] = $json->media_id;
$sifish->SendMsg("image", $msg_arr);
*/
//发送语音消息
/*
$json=json_decode($sifish->UploadFile("/voice.amr","voice"));
$msg_arr["mediaid"] = $json->media_id;
$sifish->SendMsg("voice", $msg_arr);
*/

//发送视频消息
/*
$msg_arr["title"] = 'title';
$msg_arr["description"] = 'description';
$json=json_decode($sifish->UploadFile("/video.mp4","video"));
$msg_arr["mediaid"] = $json->media_id;
$sifish->SendMsg("video", $msg_arr);
*/

//发送音乐消息
/*
$msg_arr["title"] = 'title';
$msg_arr["description"] = 'description';
$json=json_decode($sifish->UploadFile("/demo1.jpg","thumb"));//缩略图
$msg_arr["mediaid"] = $json->thumb_media_id;
$msg_arr["musicurl"] = 'http://www.youwebsite.com/mp3.mp3';
$msg_arr["hqmusicurl"] = 'http://www.youwebsite.com/mp3_1.mp3';
$sifish->SendMsg("music", $msg_arr);
*/

//发送图文消息(一条新闻可以显示description，大于一条不显示，微信的问题)
/*
$msg_arr["news"] = array (
    array ("title"=>"新闻1", "description"=>"新闻1新闻1", "picurl"=>"http://img0.bdstatic.com/img/image/shouye/lvyouwuyuan.jpg", "url"=>"http://www.baidu.com"),
    array ("title"=>"新闻2", "description"=>"新闻2新闻2", "picurl"=>"http://img0.bdstatic.com/img/image/shouye/lvyoujiuzhaigou.jpg", "url"=>"http://www.sina.com.cn"),
    array ("title"=>"新闻3", "description"=>"新闻3新闻3", "picurl"=>"http://img0.bdstatic.com/img/image/shouye/lvyouqinghaihu.jpg", "url"=>"http://www.qq.com"),
	array ("title"=>"新闻4", "description"=>"新闻4新闻4", "picurl"=>"http://img0.bdstatic.com/img/image/shouye/lvyouwuhan.jpg", "url"=>"http://www.qq.com"),
	array ("title"=>"新闻5", "description"=>"新闻5新闻5", "picurl"=>"http://img0.bdstatic.com/img/image/shouye/lvyouyunnan.jpg", "url"=>"http://www.qq.com"),
	array ("title"=>"新闻6", "description"=>"新闻6新闻6", "picurl"=>"http://img0.bdstatic.com/img/image/shouye/lvyouhangzhou.jpg", "url"=>"http://www.qq.com"),
	array ("title"=>"新闻7", "description"=>"新闻7新闻7", "picurl"=>"http://img0.bdstatic.com/img/image/shouye/lvyouhulunbeier.jpg", "url"=>"http://www.qq.com"),
	array ("title"=>"新闻8", "description"=>"新闻8新闻8", "picurl"=>"http://img0.bdstatic.com/img/image/shouye/lvyoujizhoudao.jpg", "url"=>"http://www.qq.com"),
	array ("title"=>"新闻9", "description"=>"新闻9新闻9", "picurl"=>"http://img0.bdstatic.com/img/image/shouye/lvyoubeihaidao.jpg", "url"=>"http://www.qq.com"),
	array ("title"=>"新闻10", "description"=>"新闻10新闻10", "picurl"=>"http://img0.bdstatic.com/img/image/bali0324.jpg", "url"=>"http://www.qq.com")
);
$sifish->SendMsg("news", $msg_arr);
*/


//发送客服消息
//for($i = 0; $i < 10; $i++)
//{
//$msg_arr["msg"] = "测试消息";
//$sifish->SendCustomMsg("openid","text",$msg_arr);
//sleep(1);
//}
//发送客服消息 - 图片消息
/*
$json=json_decode($sifish->UploadFile("/demo1.jpg","image"));
$msg_arr["mediaid"] = $json->media_id;
$sifish->SendCustomMsg("openid","image",$msg_arr);
*/
//发送客服消息 - 语音消息
/*
$json=json_decode($sifish->UploadFile("/voice.amr","voice"));
$msg_arr["mediaid"] = $json->media_id;
$sifish->SendCustomMsg("openid","voice",$msg_arr);
*/
//发送客服消息 - 视频消息
/*
$msg_arr["title"] = '测试视频';
$msg_arr["description"] = '你看这是谁？';
$json=json_decode($sifish->UploadFile("/video.mp4","video"));
$msg_arr["mediaid"] = $json->media_id;
$sifish->SendCustomMsg("openid","video",$msg_arr);
*/
//发送客服消息 - 音乐消息
/*
$msg_arr["title"] = 'title';
$msg_arr["description"] = 'description';
$json=json_decode($sifish->UploadFile("/demo1.jpg","thumb"));//缩略图
$msg_arr["mediaid"] = $json->thumb_media_id;
$msg_arr["musicurl"] = 'http://www.youwebsite.com/mp3.mp3';
$msg_arr["hqmusicurl"] = 'http://www.youwebsite.com/mp3_1.mp3';
$sifish->SendCustomMsg("openid", "music", $msg_arr);
*/
//发送客服消息 - 图文消息(一条新闻可以显示description，大于一条不显示，微信的问题)
/*
$msg_arr["news"] = array (
    array ("title"=>"新闻1", "description"=>"新闻1新闻1", "picurl"=>"http://img0.bdstatic.com/img/image/shouye/lvyouwuyuan.jpg", "url"=>"http://www.baidu.com"),
    array ("title"=>"新闻2", "description"=>"新闻2新闻2", "picurl"=>"http://img0.bdstatic.com/img/image/shouye/lvyoujiuzhaigou.jpg", "url"=>"http://www.sina.com.cn"),
    array ("title"=>"新闻3", "description"=>"新闻3新闻3", "picurl"=>"http://img0.bdstatic.com/img/image/shouye/lvyouqinghaihu.jpg", "url"=>"http://www.qq.com"),
	array ("title"=>"新闻4", "description"=>"新闻4新闻4", "picurl"=>"http://img0.bdstatic.com/img/image/shouye/lvyouwuhan.jpg", "url"=>"http://www.qq.com"),
	array ("title"=>"新闻5", "description"=>"新闻5新闻5", "picurl"=>"http://img0.bdstatic.com/img/image/shouye/lvyouyunnan.jpg", "url"=>"http://www.qq.com"),
	array ("title"=>"新闻6", "description"=>"新闻6新闻6", "picurl"=>"http://img0.bdstatic.com/img/image/shouye/lvyouhangzhou.jpg", "url"=>"http://www.qq.com"),
	array ("title"=>"新闻7", "description"=>"新闻7新闻7", "picurl"=>"http://img0.bdstatic.com/img/image/shouye/lvyouhulunbeier.jpg", "url"=>"http://www.qq.com"),
	array ("title"=>"新闻8", "description"=>"新闻8新闻8", "picurl"=>"http://img0.bdstatic.com/img/image/shouye/lvyoujizhoudao.jpg", "url"=>"http://www.qq.com"),
	array ("title"=>"新闻9", "description"=>"新闻9新闻9", "picurl"=>"http://img0.bdstatic.com/img/image/shouye/lvyoubeihaidao.jpg", "url"=>"http://www.qq.com"),
	array ("title"=>"新闻10", "description"=>"新闻10新闻10", "picurl"=>"http://img0.bdstatic.com/img/image/bali0324.jpg", "url"=>"http://www.qq.com")
);
$sifish->SendCustomMsg("openid", "news", $msg_arr);
*/

//需要将数据记录到数据库，请自行按需求建表
/*替换为你自己的数据库名*/
//$dbname = 'dbname';
/*填入数据库连接信息*/
//$host = '192.168.0.1';
//$port = 4050;
//$user = 'username';//用户名(api key)
//$pwd = 'password';//密码(secret key)

/*接着调用mysql_connect()连接服务器*/
//$link = @mysql_connect("{$host}:{$port}",$user,$pwd,true);

//if(!$link) {
//die("Connect Server Failed: " . mysql_error());
//}
/*

if(!mysql_select_db($dbname,$link)) {
die("Select Database Failed: " . mysql_error($link));
}

$sql = "insert into wxtest (fromUsername, toUsername, CreateTime, MsgType, Content, MsgId, PicUrl, MediaId, Format, ThumbMediaId, Location_X, Location_Y, Scale, Label, Title, Description, Url, Event, EventKey, Ticket, Latitude, Longitude, `Precision`, `Recognition`) values('".$arr["fromUsername"]."', '".$arr["toUsername"]."', '".$arr["CreateTime"]."', '".$arr["MsgType"]."', '".$arr["Content"]."', '".$arr["MsgId"]."', '".$arr["PicUrl"]."', '".$arr["MediaId"]."', '".$arr["Format"]."', '".$arr["ThumbMediaId"]."', '".$arr["Location_X"]."', '".$arr["Location_Y"]."', '".$arr["Scale"]."', '".$arr["Label"]."', '".$arr["Title"]."', '".$arr["Description"]."', '".$arr["Url"]."', '".$arr["Event"]."', '".$arr["EventKey"]."', '".$arr["Ticket"]."', '".$arr["Latitude"]."', '".$arr["Longitude"]."', '".$arr["Precision"]."', '".$arr["Recognition"]."')";
$ret = mysql_query($sql, $link);
if ($ret === false) {
	die("Insert Failed: " . mysql_error($link));
}
*/
?>