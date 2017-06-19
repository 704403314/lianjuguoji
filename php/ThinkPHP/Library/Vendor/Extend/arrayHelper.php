<?php
    /**
     * 从数组中删除空白的元素（包括只有空白字符的元素）
     *
     * 用法：
     * @code php
     * $arr = array('', 'test', '   ');
     * Helper_Array::removeEmpty($arr);
     *
     * dump($arr);
     *   // 输出结果中将只有 'test'
     * @endcode
     *
     * @param array $arr 要处理的数组
     * @param boolean $trim 是否对数组元素调用 trim 函数
     */
    function removeEmpty(& $arr, $trim = true)
    {
        foreach ($arr as $key => $value) 
        {
            if (is_array($value)) 
            {
                removeEmpty($arr[$key]);
            } 
            else 
            {
                $value = trim($value);
                if ($value == '') 
                {
                    unset($arr[$key]);
                } 
                elseif ($trim) 
                {
                    $arr[$key] = $value;
                }
            }
        }
    }

    /**
     * 从一个二维数组中返回指定键的所有值
     *
     * 用法：
     * @code php
     * $rows = array(
     *     array('id' => 1, 'value' => '1-1'),
     *     array('id' => 2, 'value' => '2-1'),
     * );
     * $values = Helper_Array::cols($rows, 'value');
     *
     * dump($values);
     *   // 输出结果为
     *   // array(
     *   //   '1-1',
     *   //   '2-1',
     *   // )
     * @endcode
     *
     * @param array $arr 数据源
     * @param string $col 要查询的键
     *
     * @return array 包含指定键所有值的数组
     */
    function getCols($arr, $col)
    {
        $ret = array();
        foreach ($arr as $row) 
        {
            if (isset($row[$col])) { $ret[] = $row[$col]; }
        }
        return $ret;
    }

    /**
     * 将一个二维数组转换为 HashMap，并返回结果
     *
     * 用法1：
     * @code php
     * $rows = array(
     *     array('id' => 1, 'value' => '1-1'),
     *     array('id' => 2, 'value' => '2-1'),
     * );
     * $hashmap = Helper_Array::hashMap($rows, 'id', 'value');
     *
     * dump($hashmap);
     *   // 输出结果为
     *   // array(
     *   //   1 => '1-1',
     *   //   2 => '2-1',
     *   // )
     * @endcode
     *
     * 如果省略 $value_field 参数，则转换结果每一项为包含该项所有数据的数组。
     *
     * 用法2：
     * @code php
     * $rows = array(
     *     array('id' => 1, 'value' => '1-1'),
     *     array('id' => 2, 'value' => '2-1'),
     * );
     * $hashmap = Helper_Array::hashMap($rows, 'id');
     *
     * dump($hashmap);
     *   // 输出结果为
     *   // array(
     *   //   1 => array('id' => 1, 'value' => '1-1'),
     *   //   2 => array('id' => 2, 'value' => '2-1'),
     *   // )
     * @endcode
     *
     * @param array $arr 数据源
     * @param string $key_field 按照什么键的值进行转换
     * @param string $value_field 对应的键值
     *
     * @return array 转换后的 HashMap 样式数组
     */
    function toHashmap($arr, $key_field, $value_field = null)
    {
        $ret = array();
        if ($value_field) 
        {
            foreach ($arr as $row) 
            {
                $ret[$row[$key_field]] = $row[$value_field];
            }
        } 
        else 
        {
            foreach ($arr as $row) 
            {
                $ret[$row[$key_field]] = $row;
            }
        }
        return $ret;
    }

    /**
     * 将一个二维数组按照指定字段的值分组
     *
     * 用法：
     * @code php
     * $rows = array(
     *     array('id' => 1, 'value' => '1-1', 'parent' => 1),
     *     array('id' => 2, 'value' => '2-1', 'parent' => 1),
     *     array('id' => 3, 'value' => '3-1', 'parent' => 1),
     *     array('id' => 4, 'value' => '4-1', 'parent' => 2),
     *     array('id' => 5, 'value' => '5-1', 'parent' => 2),
     *     array('id' => 6, 'value' => '6-1', 'parent' => 3),
     * );
     * $values = Helper_Array::groupBy($rows, 'parent');
     *
     * dump($values);
     *   // 按照 parent 分组的输出结果为
     *   // array(
     *   //   1 => array(
     *   //        array('id' => 1, 'value' => '1-1', 'parent' => 1),
     *   //        array('id' => 2, 'value' => '2-1', 'parent' => 1),
     *   //        array('id' => 3, 'value' => '3-1', 'parent' => 1),
     *   //   ),
     *   //   2 => array(
     *   //        array('id' => 4, 'value' => '4-1', 'parent' => 2),
     *   //        array('id' => 5, 'value' => '5-1', 'parent' => 2),
     *   //   ),
     *   //   3 => array(
     *   //        array('id' => 6, 'value' => '6-1', 'parent' => 3),
     *   //   ),
     *   // )
     * @endcode
     *
     * @param array $arr 数据源
     * @param string $key_field 作为分组依据的键名
     *
     * @return array 分组后的结果
     */
    function groupBy($arr, $key_field)
    {
        $ret = array();
        foreach ($arr as $row) 
        {
            $key = $row[$key_field];
            $ret[$key][] = $row;
        }
        return $ret;
    }

    /**
     * 将一个平面的二维数组按照指定的字段转换为树状结构
     *
     * 用法：
     * @code php
     * $rows = array(
     *     array('id' => 1, 'value' => '1-1', 'parent' => 0),
     *     array('id' => 2, 'value' => '2-1', 'parent' => 0),
     *     array('id' => 3, 'value' => '3-1', 'parent' => 0),
     *
     *     array('id' => 7, 'value' => '2-1-1', 'parent' => 2),
     *     array('id' => 8, 'value' => '2-1-2', 'parent' => 2),
     *     array('id' => 9, 'value' => '3-1-1', 'parent' => 3),
     *     array('id' => 10, 'value' => '3-1-1-1', 'parent' => 9),
     * );
     *
     * $tree = Helper_Array::tree($rows, 'id', 'parent', 'nodes');
     *
     * dump($tree);
     *   // 输出结果为：
     *   // array(
     *   //   array('id' => 1, ..., 'nodes' => array()),
     *   //   array('id' => 2, ..., 'nodes' => array(
     *   //        array(..., 'parent' => 2, 'nodes' => array()),
     *   //        array(..., 'parent' => 2, 'nodes' => array()),
     *   //   ),
     *   //   array('id' => 3, ..., 'nodes' => array(
     *   //        array('id' => 9, ..., 'parent' => 3, 'nodes' => array(
     *   //             array(..., , 'parent' => 9, 'nodes' => array(),
     *   //        ),
     *   //   ),
     *   // )
     * @endcode
     *
     * 如果要获得任意节点为根的子树，可以使用 $refs 参数：
     * @code php
     * $refs = null;
     * $tree = Helper_Array::tree($rows, 'id', 'parent', 'nodes', $refs);
     * 
     * // 输出 id 为 3 的节点及其所有子节点
     * $id = 3;
     * dump($refs[$id]);
     * @endcode
     *
     * @param array $arr 数据源
     * @param string $key_node_id 节点ID字段名
     * @param string $key_parent_id 节点父ID字段名
     * @param string $key_childrens 保存子节点的字段名
     * @param boolean $refs 是否在返回结果中包含节点引用
     *
     * return array 树形结构的数组
     */
    function toTree($arr, $key_node_id, $key_parent_id = 'parent_id',
                           $key_childrens = 'childrens', & $refs = null)
    {
        $refs = array();
        foreach ($arr as $offset => $row) 
        {
            $arr[$offset][$key_childrens] = array();
            $refs[$row[$key_node_id]] =& $arr[$offset];
        }

        $tree = array();
        foreach ($arr as $offset => $row) 
        {
            $parent_id = $row[$key_parent_id];
            if ($parent_id)
            {
                if (!isset($refs[$parent_id]))
                {
                    $tree[] =& $arr[$offset];
                    continue;
                }
                $parent =& $refs[$parent_id];
                $parent[$key_childrens][] =& $arr[$offset];
            }
            else
            {
                $tree[] =& $arr[$offset];
            }
        }

        return $tree;
    }

    /**
     * 将树形数组展开为平面的数组
     *
     * 这个方法是 tree() 方法的逆向操作。
     *
     * @param array $tree 树形数组
     * @param string $key_childrens 包含子节点的键名
     *
     * @return array 展开后的数组
     */
    function treeToArray($tree, $key_childrens = 'childrens')
    {
        $ret = array();
        if (isset($tree[$key_childrens]) && is_array($tree[$key_childrens]))
        {
            $childrens = $tree[$key_childrens];
            unset($tree[$key_childrens]);
            $ret[] = $tree;
            foreach ($childrens as $node) 
            {
                $ret = array_merge($ret, treeToArray($node, $key_childrens));
            }
        }
        else
        {
            unset($tree[$key_childrens]);
            $ret[] = $tree;
        }
        return $ret;
    }

    /**
     * 根据指定的键对数组排序
     *
     * 用法：
     * @code php
     * $rows = array(
     *     array('id' => 1, 'value' => '1-1', 'parent' => 1),
     *     array('id' => 2, 'value' => '2-1', 'parent' => 1),
     *     array('id' => 3, 'value' => '3-1', 'parent' => 1),
     *     array('id' => 4, 'value' => '4-1', 'parent' => 2),
     *     array('id' => 5, 'value' => '5-1', 'parent' => 2),
     *     array('id' => 6, 'value' => '6-1', 'parent' => 3),
     * );
     *
     * $rows = Helper_Array::sortByCol($rows, 'id', SORT_DESC);
     * dump($rows);
     * // 输出结果为：
     * // array(
     * //   array('id' => 6, 'value' => '6-1', 'parent' => 3),
     * //   array('id' => 5, 'value' => '5-1', 'parent' => 2),
     * //   array('id' => 4, 'value' => '4-1', 'parent' => 2),
     * //   array('id' => 3, 'value' => '3-1', 'parent' => 1),
     * //   array('id' => 2, 'value' => '2-1', 'parent' => 1),
     * //   array('id' => 1, 'value' => '1-1', 'parent' => 1),
     * // )
     * @endcode
     *
     * @param array $array 要排序的数组
     * @param string $keyname 排序的键
     * @param int $dir 排序方向
     *
     * @return array 排序后的数组
     */
    function sortByCol($array, $keyname, $dir = SORT_ASC)
    {
        return sortByMultiCols($array, array($keyname => $dir));
    }

    /**
     * 将一个二维数组按照多个列进行排序，类似 SQL 语句中的 ORDER BY
     *
     * 用法：
     * @code php
     * $rows = Helper_Array::sortByMultiCols($rows, array(
     *     'parent' => SORT_ASC, 
     *     'name' => SORT_DESC,
     * ));
     * @endcode
     *
     * @param array $rowset 要排序的数组
     * @param array $args 排序的键
     *
     * @return array 排序后的数组
     */
    function sortByMultiCols($rowset, $args)
    {
        $sortArray = array();
        $sortRule = '';
        foreach ($args as $sortField => $sortDir) 
        {
            foreach ($rowset as $offset => $row) 
            {
                $sortArray[$sortField][$offset] = $row[$sortField];
            }
            $sortRule .= '$sortArray[\'' . $sortField . '\'], ' . $sortDir . ', ';
        }
        if (empty($sortArray) || empty($sortRule)) { return $rowset; }
        eval('array_multisort(' . $sortRule . '$rowset);');
        return $rowset;
    }
    
	/**
	* 将一个二维数组组成 下拉菜单的 下拉选项 
	*
	* 用法：
	* @code php
	* dafenglei_select($category,$m,$id,$index)
	* @endcode
	*
	* @param array $category 所有分类数组(含父子关系)
	* @param array $m 不详 0
	* @param int $id 默认选中项
	* @param int $index 父分类ID
	* @return string	 排序后的选项 字符串
	*/
	function dafenglei_select($category,$m,$id,$index)
	{   
			$str='';
		$n = str_pad('',$m,'-',STR_PAD_RIGHT);
		$n = str_replace("-","&nbsp;&nbsp;&nbsp;",$n);
		for($i=0;$i<count($category);$i++){
			if($category[$i]['parentId']==$id){
				if($category[$i]['classId']==$index){
					$str.= "<option value=\"".$category[$i]['classId']."\" selected=\"selected\">".$n."|--".$category[$i]['className']."</option>\n";
				}else{
				$str.=   "<option value=\"".$category[$i]['classId']."\">".$n."|--".$category[$i]['className']."</option>\n";
				}
				$str.=dafenglei_select($category,$m+1,$category[$i]['classId'],$index);
			}
		}
			return $str;
	}
	
	
	
	/**
	* 将一个二维数组组成 所有子分类 ,连接 字符串 
	*
	* 用法：
	* @code php
	* dafenglei_select($category,$m,$id,$index)
	* @endcode
	*
	* @param array $category 所有分类数组(含父子关系)
	* @param int $id 父ID
	* @return string	 字符串
	*/
function get_all_child($category,$id,$classId='id')
	{   
			$str='';
		for($i=0;$i<count($category);$i++){
			if($category[$i]['parentid']==$id){
				$str.=$category[$i][$classId].',';
				$str.=get_all_child($category,$category[$i][$classId]);
			}
		}
			return $str;
	}
	/**
	*去除一个数组中所有为空的值
	*
	*/
	function del_null_array($arr){
	   $array=array();
	   foreach($arr as $val){
	      if(!empty($val)){
		    $array[]=$val;
		  }
	   }
	   return $array;
	
	
	}

    
    //多级分类
   function _getOrderList(&$list){
    $order_list = array();
 
    _recursion_list($list, $order_list);               
     
    return $order_list;
  }
  
   function _recursion_list(&$list, &$order_list, $result_list = false, $parent_id = 0, $level = 0, $view_name = ''){
    if (!$result_list)
      $result_list = array();
    foreach($list as $item){
      if ($item['parentid']== $parent_id){
        if (!isset( $result_list[$parent_id] ) )
        $result_list[$parent_id] = array();
        $item['name'] = $view_name . $item['name'];
        $result_list[$parent_id][] = $item;
        $order_list[] = $item;                                                                         //|- ┝
        _recursion_list($list, $order_list, $result_list, $item['id'], $level + 1, $view_name . "&nbsp;|-&nbsp;");
      }
    }
  } 
  
  /**
 * 查询子类
 *
 * @param array $arr        	
 * @param number $id        	
 * @return multitype:array
 */
function find_child($arr, $id) {
	$childs = array ();
	if (! empty ( $arr )) {
		foreach ( $arr as $k => $v ) {
			if ($v ['parentid'] == $id) {
				$childs [] = $v;
			}
		}
	}
	return $childs;
}

/**
 * 递归
 *
 * @param number $root_id
 *        	开始ID
 * @param array $lists
 *        	需要递归的数据
 * @param string $loop_id
 *        	ID
 * @return NULL Ambigous multitype:array, multitype:array >
 */
function menu_tree($root_id, $lists = array(), $field = 'menuid') {
	$childs = find_child ( $lists, $root_id );
	
	if (empty ( $childs )) {
		return null;
	}
	foreach ( $childs as $k => $v ) {
		$rescurTree = menu_tree ( $v [$field], $lists, $field );
		if (null != $rescurTree) {
			$childs [$k] ['childs'] = $rescurTree;
		}
	}
	return $childs;
}
	
