<?php
/////////////////////////////////////////////////////////////////////////////
// Framework
//
// Copyright (c) 2009 Yan weidong
//
/////////////////////////////////////////////////////////////////////////////
/**
 * 定义 Action Abstract 类
 *
 * @package mvc
 * @version $Id: abstract.php 256 2008-03-16 19:20:53Z yan weidong $
 */
/**
 * Controller_Abstract 实现了一个其它控制器的超类
 * @package mvc
 */
abstract class actionAbstract 
{

	/**
     * Modules名称
     * @var string
     */
    public $appName;

	/**
     * Controller名称
     * @var string
     */
    public $actionName;

    /**
     * Action名称
     * @var string
     */
    public $functionName;

    public $seo = array();


    /**
     * Action对象
     * @var string
     */
    public $actionObject;

    protected $trace       = array();  // 页面trace变量
    
    public $menudata	=	null;//后台所有菜单
    public $appid	=	"wx0fc019ed4eaf6768";//微信
    public $appselect	=	"e4f398eae216bffc49002aa4d2559892";//微信
	var $openid	=	'0';

    public $kfdt = '';
    


    /**
     * 构造函数
     */
    function __construct()
    {
    	global $configs;
    	$this->configs = $configs;
		$this->_getUrls();
        //默认设置
        $this->loadModel('system','seo');
        $sql = "select * from system_seo where id=1 or posi = 'deft'";
        $this->seo = $this->system->seoModel->fetchRow($sql); 
        //客服
        $this->loadModel('system','setting');
        $sql  = "select * from system_setting where id >9";
        $this->kfdt = $this->system->settingModel->fetchAll($sql);
         
    }






	/**
     * 获取url的值.
     * @return array
     */
    function _getUrls()
    {
    	
    	$pathInfo = empty($_SERVER['PATH_INFO'])?'':$_SERVER['PATH_INFO'];
    	$pathInfo = str_replace(".html","",$pathInfo);
		$urls = explode('/',$pathInfo);
		
		// get app name.
		if(empty($urls[1])){
			$this->appName = NOT_MODULES;
		} else {
			$this->appName = $urls[1];
		}
		
		// get action name.
		if(empty($urls[2])){
			$this->actionName = DEFAULT_CONTROLLER;
		} else {
			$this->actionName = $urls[2];
		}
		
		// get action name.
		if(empty($urls[3])){
			$this->functionName = 'main';
		} else {
			$this->functionName = $urls[3];
		}
		
		// get params.
		$keyName = '';
		unset($urls[0],$urls[1],$urls[2],$urls[3]);
		$paramsAry = array_values($urls);
		foreach($paramsAry as $key=>$val){
			if($key %2 == 0){
				$keyName = $val;
				$this->params[$keyName] = '';
			} else {
				$this->params[$keyName] = $val;
			}
		}
    }
	

    /**
     * 执行指定的动作
     * @return mixed
     */
    function execute()
    {
    	
    	$appPath = $this->get_app_path();
    	$this->get_action();
    	
    	if(method_exists($this->actionObject,$this->functionName))
	    	$this->actionObject->{$this->functionName}();
	    elseif(method_exists($this->actionObject,'__go'))
	    	$this->actionObject->__Go();
	    else
	    	Framework::Error('function: <b>__go()</b> or <b>'.$this->functionName.'()</b> not defined on <b>'.$this->actionName .'.php</b>',__FILE__,__LINE__);
			
	    
    }
	
    /**
     * 根据app的参数来获取一个路径.
     *
     * @return string
     */
    function get_app_path()
    {
    	$appPath =  APPS_PATH.$this->appName;
    	if(!is_dir($appPath)) Framework::Error('app: <b>'.$this->appName.'</b> not found ['.$appPath.']',__FILE__,__LINE__);
    	return $appPath;
    }
    
    /**
     * 根据action的参数来获取一个路径.
     *
     * @return string
     */
    function get_action()
    {
    	$actionPath =  APPS_PATH . $this->appName . "/action/".$this->actionName . ".php";
        $notFoundActionPath =  APPS_PATH . $this->appName . "/action/".NOT_CONTROLLER . ".php";
        
    	if(is_file($actionPath)){
    		include $actionPath;
    		$this->actionObject = new $this->actionName();
    	} elseif(is_file($notFoundActionPath)) {
            include $notFoundActionPath;
            $notFoundActionName = NOT_CONTROLLER;
            $this->actionObject = new $notFoundActionName();
        } else {
    		Framework::Error('action: <b>'.$this->actionName.'</b> not found, for: '.$actionPath,__FILE__,__LINE__);
    	}
    }
    
    
    /**
     * 调入model.
     *
     */
	function loadModel($appsName,$tableName,$primaryKey='id'){
		$modelName = $tableName.'Model';
    	$modelPath =  APPS_PATH . $appsName . "/model/".$modelName . ".php";
    	if(!isset($this->$appsName))
    		$this->$appsName = new stdClass();
    	if(is_file($modelPath)){
    		include_once($modelPath);
    			
    		$this->$appsName->$modelName = new $modelName();
    		
    	} else {
    		$modelPath =  LIBRARY_PATH . "db/model.php";
    		include_once($modelPath);
    		
    		$this->$appsName->$modelName = new model();
    		$this->$appsName->$modelName->_tableName  = $appsName.'_'.$tableName;
    		$this->$appsName->$modelName->_primaryKey = $primaryKey;
    		$this->$appsName->$modelName->_database   = 'portal';
    		
    	}
    	
    	// connection db server.
    	$this->$appsName->$modelName->initDb();
    }
    /**
     * 调入helper.
     *
     */
    function loadHelper($helperFileName){
    	$viewPath =  LIBRARY_PATH . "/helper/".$helperFileName . ".php";
    	 
    	if(!is_file($viewPath)){
    		Framework::Error("helper: <b>$viewPath</b> not found",__FILE__,__LINE__);
    	}
    	 
    	include_once $viewPath;
    }
    
    /**
     * 调入view.
     *
     */
    function loadView($viewFileName=''){
    	if(empty($viewFileName)){
    		$viewPath =  APPS_PATH . $this->appName . "/view/".$this->actionName .  "/".$this->functionName  .".php";
    	} else {
    		$viewPath =  APPS_PATH . $this->appName . "/view/".$viewFileName . ".php";
    	}
    	
    	if(!is_file($viewPath)){
    		Framework::Error("view: <b>$viewPath</b> not found",__FILE__,__LINE__);
    	}
    	
    	return $viewPath;
    }
    
    /*
     * ajax 调入
     */
    function loadAjaxView($viewFileName = '',$data='') {
    	if(empty($viewFileName)){
    		$viewPath =  APPS_PATH . $this->appName . "/view/".$this->actionName .  "/".$this->functionName  .".php";
    	} else {
    		$viewPath =  APPS_PATH . $this->appName . "/view/".$viewFileName . ".php";
    	}
    	
    	if(!is_file($viewPath)){
    		Framework::Error("view: <b>$viewPath</b> not found",__FILE__,__LINE__);
    	}
    	if(!empty($data)) {
    		extract($data);
    	}
    	ob_start();
    	include($viewPath);
    	$result = ob_get_contents();
    	ob_end_clean();
    	return $result;
    }
    
    /**
     * 调入widget.
     *
     */
    function loadWidget($widgetFileName=''){
    	$widgetPath =  ROOT_PATH . "/widget/".$widgetFileName . ".php";
    	 
    	if(!is_file($widgetPath)){
    		Framework::Error("widget: <b>$widgetPath</b> not found",__FILE__,__LINE__);
    	}
    	 
    	return $widgetPath;
    }
    /**
    *
    *菜单配置载入
    */
    protected function loadConfig($fileName=''){
        if(!$fileName){
            $files = CONFIG_PATH .$this->appName.'.php';
        }else{
            $files = CONFIG_PATH.$fileName.'.php';
        }
         
        if(file_exists($files)){
            return include_once $files;
        }
        return array();
    }


    
    
    // URL重定向
    protected function redirect($url,  $msg='',$time=0) {
        //多行URL地址支持
        $url = str_replace(array("\n", "\r"), '', $url);
        if (empty($msg))
            $msg = "系统将在{$time}秒之后自动跳转到{$url}！";
        if (!headers_sent()) {
            // redirect
            if (0 === $time) {
                header('Location: ' . $url);
            } else {
                header("refresh:{$time};url={$url}");
                echo($msg);
            }
            exit();
        } else {
            $str = "<meta http-equiv='Refresh' content='{$time};URL={$url}'>";
            if ($time != 0)
                $str .= $msg;
            exit($str);
        }
    }
    //url生成
    // $this->url('controllers/action');
    // $this->url('controllers/action',array('id'=>1));
    // $this->url('controllers/action@appname',array('id'=>1));
    public function url($urls,$arg=array()){
        
        if(is_array($urls)){
            $arg = $urls[1];
            (string)$urls = $urls[0];
        }
        if(@$urls[0] == '/') return $urls;

        $url = '/index.php/';
        //处理index.php
        if(strpos($_SERVER["REQUEST_URI"],'index.php')){
            $url = '/index.php/';
        }
        if (!strpos($urls, '/')) {
            $urls =$this->actionName.'/'.$urls;
        }

        if (strpos($urls,'@')) {
            $arr=explode('@', $urls);
            $url.=$arr[1].'/'.$arr[0];
        }else{
            $url .= $this->appName.'/'. $urls;
        }
        if(!empty($arg)){
            $str = '';
            foreach ($arg as $k => $v) {
                $str .='&'.$k.'='.$v; 
            }

            if (strpos($urls,'?')) {
                $url .='&'.ltrim($str,'&');
            } else {
                $url .='?'.ltrim($str,'&');
            }

        }
        return $url;
    }

    public function trace($title,$value='') {
        if(is_array($title))
            $this->trace   =  array_merge($this->trace,$title);
        else
            $this->trace[$title] = $value;
    }
    	//得到用户的openid
	public function getuseropenid(){
	//得到openid
		if(isset($_SESSION['weixinuser']['openid']) && !empty($_SESSION['weixinuser']['openid'])){$this->openid	=	$_SESSION['weixinuser']['openid'];return true;}
		if(isset($_GET['code'])){
			$code = $_GET['code'];
			$url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=".$this->appid."&secret=".$this->appselect."&code=".$code."&grant_type=authorization_code";
			$str = file_get_contents($url);
			$arr = (array)json_decode($str);
			$_SESSION['weixinuser'] = isset($arr['openid'])?$arr:'';
			//acl::setCookie("openid",$arr);
			
			$this->openid	=	$_SESSION['weixinuser']['openid'];
		}else{
			if(empty($_SESSION['weixinuser'])){
				$redirect_uri= urlencode("http://".ROOT_URL.$_SERVER['REQUEST_URI']);
				$url	=	"https://open.weixin.qq.com/connect/oauth2/authorize?appid=".$this->appid."&redirect_uri=$redirect_uri&response_type=code&scope=snsapi_userinfo&state=1#wechat_redirect";
				header('location:'.$url);
			}else{
				$this->openid	=	$_SESSION['weixinuser']['openid'];
			}
			
		}
	}
    //输出调试信息
    function debug(){
         // 系统默认显示信息
        $this->trace('请求方法',    $_SERVER['REQUEST_METHOD']);
        $this->trace('通信协议',    $_SERVER['SERVER_PROTOCOL']);
        $this->trace('请求时间',    date('Y-m-d H:i:s',$_SERVER['REQUEST_TIME']));
        $this->trace('用户代理',    $_SERVER['HTTP_USER_AGENT']);
        $this->trace('会话ID'   ,   session_id());
        $Sql    =  isset($_ENV['Sql'])?$_ENV['Sql']:array();
        $this->trace('SQL记录',count($Sql)?count($Sql).'条<br/>'.implode('<br/>',$Sql):'无SQL记录');
        $files =  get_included_files();
        $this->trace('加载文件',    count($files).str_replace("\n",'<br/>',substr(substr(print_r($files,true),7),0,-2)));
        // 调用Trace页面模板
        include LIBRARY_PATH.'/helper/trace.tpl';
    }
    //魔术方法 有不存在的操作的时候执行
    public function __call($method,$args) {
        switch(strtolower($method)) {
                // 判断提交方式
                case 'ispost':
                case 'isget':
                case 'ishead':
                case 'isdelete':
                case 'isput':
                    return strtolower($_SERVER['REQUEST_METHOD']) == strtolower(substr($method,2));
                default:
                    return  false;
            }
    }
    
}
?>