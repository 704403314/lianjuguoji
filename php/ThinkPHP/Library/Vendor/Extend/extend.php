<?php

    /**
     * 页面跳转
     * @param string $url
     */
    //function redirect($url) {
    //   header('Location: ' . $url);
    //}

    /**
     * 切割字符串
     */
    function stringMin($string, $size, $isadd='…') {
        $string = strip_tags($string);
        if (mb_strlen($string,'utf-8') > $size) {
            $string = mb_substr($string, 0, $size, 'utf-8');
            $string = $string . $isadd;
        }
        return $string;
    }

    /**
	*得到字符串的长度
	*
	*/
	function strlength($string){
	$string = strip_tags($string);
	return mb_strlen($string,'utf-8');
	
	}
    /**
     * 安全检查
     * 过滤html标签
     * @param string $str
     * @return string
     */
    function filterHtml($str) {
        $farr = array(
            "/\s+/", //过滤多余的空白
            "/<(\/?)(script|i?frame|html|body|title|meta|\?|\%)([^>]*?)>/isU", //过滤 <script 等可能引入恶意内容或恶意改变显示布局的代码,如果不需要插入flash等,还可以加入<object的过滤
            "/(<[^>]*)on[a-zA-Z]+\s*=([^>]*>)/isU", //过滤javascript的on事件
        );
        $tarr = array(
            " ",
            " ", //如果要直接清除不安全的标签，这里可以留空
            " ",
        );
        $str = preg_replace($farr, $tarr, $str);
        return $str;
    }

    /**
    * 基于ID分析出目录名称
    * @return ID所对应的目录
    */
    function splitDir($_Id,$Number=-3)
    {
        $Dir = $_Id;
        if($_Id > 999)
        {
            $Dir = substr($_Id,$Number);
        }
        return $Dir;
    }
    
    /**
    * @desc 用户头像
    */
    function userPhoto($userid)
    {
         $photourl = '/data/userphoto/'.splitDir($userid).'/'.$userid.'.jpg';
         $path = ROOT_PATH.$photourl;
         if(!file_exists($path)){
           $photourl = "/resource/assets/images/nophoto.jpg";
         }  
         return $photourl.'?'.time();
    }
    

    /**
    * @desc 缩略图读取
    */
    function getThumbPath($filePath,$ext='_thumb'){
        $pathArray = explode(".",$filePath);
        $pathArray[count($pathArray)-2] = $pathArray[count($pathArray)-2].$ext;
        $thumbFilePath = implode(".", $pathArray);
        return $thumbFilePath;
    }

    
    /**
     * 计算时间
     * 规则如下，如果一小时内，显示分钟，如果大于1小时小于1天显示小时，如果大于天且小于3天，显示天数，否则显示日期时间
     */
    function time_tran($the_time){
       //$now_time = date("Y-m-d H:i:s",time()+8*60*60);
       //$now_time = strtotime($now_time);
       //$show_time = strtotime($the_time);
       $now_time  = time();
       $show_time = $the_time;
       $dur = $now_time - $show_time;
       if($dur < 0){
        return date("Y-m-d H:i:s",$the_time);
       }else{
        if($dur < 60){
         return '刚刚更新';
        }else{
         if($dur < 3600){
          return floor($dur/60).'分钟前';
         }else{
          if($dur < 86400){
           return floor($dur/3600).'小时前';
          }else
           if($dur < 86400*3){
            return floor($dur/86400).'天前'; 
           }else{
            return date("Y-m-d H:i:s",$the_time);
          }
         }
        }
       }
    }
    
    
    function is_num($prama){
       return (isset($_GET[$prama])&& is_numeric($_GET[$prama]))?$_GET[$prama]:0;
    }



  /*
  输入生日 返回据今年月天数
  此方法未对参数有效性进行判断
  调用方法 getAGE(yyyy,mm,dd); 返回字符串
  可用 echo getAGE(1984,12,18);打印
 */

function getAGE($bedate,$fmdate = '') {
    $fdate = $fmdate ? explode('-', $fmdate):array();
    $today['y'] = $fmdate ? $fdate['0']:date('Y');
    $today['m'] = $fmdate ? $fdate['1']:date('m');
    $today['d'] = $fmdate ? $fdate['2']:date('d');
    
    $date = explode('-', $bedate);
    $d = $date['2'];$m = $date['1'];$y = $date['0'];
    if ($today['d'] < $d) {
        if ($today['m'] == 1) {
            $today['m'] = 13;
            $today['y']--;
        }
        $today['m']--;
        $today['d']+=getDAYS($today['y'], $today['m']);
    }
    if ($today['m'] < $m) {
        $today['m']+=12;
        $today['y']--;
    }
    if ($today['y'] < $y) {
        //return "还未出生";
        return 0;
    }
    return ($today['y'] - $y) * 365 + ($today['m'] - $m) * 30 + ceil(($today['d'] - $d)+1);
    //return ($today['y'] - $y) . "-" . ($today['m'] - $m) . "-" . ceil(($today['d'] - $d) / 7);
    //return ($today['y'] - $y);
}

//根据年月 返回该月有多少天
function getDAYS($y, $m) {
    if (($m == 4) || ($m == 6) || ($m == 9) || ($m == 11)) {
        return 30;
    } elseif ($m == 2) {
        if ((($y % 4 == 0) && ($y % 100 != 0)) || ($y % 400 == 0)) {
            return 29;
        } else {
            return 28;
        }
    } else {
        return 31;
    }
}

 /**
    * 字符串截取
    *
    * @author gesion<gesion@163.com>
    * @param string $str 原始字符串
    * @param int    $len 截取长度（中文/全角符号默认为 2 个单位，英文/数字为 1。
    *                    例如：长度 12 表示 6 个中文或全角字符或 12 个英文或数字）
    * @param bool   $dot 是否加点（若字符串超过 $len 长度，则后面加 "..."）
    * @return string
    */
    function string_substr($str, $len = 12, $dot = true) {
        $i = 0;
        $l = 0;
        $c = 0;
        $a = array();
        while ($l < $len) {
            $t = substr($str, $i, 1);
            if (ord($t) >= 224) {
                $c = 3;
                $t = substr($str, $i, $c);
                $l += 2;
            } elseif (ord($t) >= 192) {
                $c = 2;
                $t = substr($str, $i, $c);
                $l += 2;
            } else {
                $c = 1;
                $l++;
            }
            // $t = substr($str, $i, $c);
            $i += $c;
            if ($l > $len) break;
            $a[] = $t;
        }
        $re = implode('', $a);
        if (substr($str, $i, 1) !== false) {
            array_pop($a);
            ($c == 1) and array_pop($a);
            $re = implode('', $a);
            $dot and $re .= '...';
        }
        return $re;
    }
    /**
 * ajax返回
 */
function ajaxReturn($data, $info = '', $status = 1, $type = 'JSON') {
  $result = array ();
  $result ['state'] = $status;
  $result ['info'] = $info;
  $result ['data'] = $data;
  
  if (empty ( $type )) {
    $type = 'JSON';
  }
  if (strtoupper ( $type ) == 'JSON') {
    // 返回JSON数据格式到客户端 包含状态信息
    //header ( 'Content-Type:text/html; charset=utf-8' );
    exit ( json_encode ( $result ) );
  } else {
    // TODO 增加其它格式
  }
}
/**
 * 获取菜单树 dynatree-checkbox
 *
 * @param unknown $menu        	
 */
function get_menu_tree($menu = array(), $selected = array(), $field = 'menuid') {
	foreach ( $menu as $key => $value ) {
		echo '<li id="' . $value [$field] . '" ' . (! empty ( $selected ) && in_array ( $value ['pin'], $selected ) ? 'class="selected"' : "") . '>' . $value ['title'];
		if (isset ( $value ['childs'] ) && is_array ( $value ['childs'] )) {
			echo '<ul>';
			get_menu_tree ( $value ['childs'], $selected, $field );
			echo '</ul></li>';
		} else {
			echo '</li>';
		}
	}
}

/**
 * 获取菜单树--地区
 *
 * @param unknown $menu        	
 */
function get_menu_tree_area($menu = array(), $selected = array(), $field = 'id',$get_menu_tree=array()) {
	foreach ( $menu as $key => $value ) {
		echo '<li id="' . $value [$field] . '" ' . (! empty ( $selected ) && in_array ( $value ['id'], $selected ) ? 'class="selected"' : "") . '>' . $value ['name'];
		if (isset ( $value ['childs'] ) && is_array ( $value ['childs'] )) {
			echo '<ul>';
			get_menu_tree_area ( $value ['childs'], $selected, $field );
			echo '</ul></li>';
		} else {
			echo '</li>';
		}
	}
}

 
