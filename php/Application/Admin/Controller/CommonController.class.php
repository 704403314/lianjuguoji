<?php
/**
 * 控制器基类
 * @Author: hausir
 * @Date:   2016-09-01 16:10:04
 * @File:   CommonController.class.php
 * @Last Modified by:   hausir
 * @Last Modified time: 2016-09-01 19:32:12
 */

namespace Admin\Controller;

class CommonController extends AdminController
{
    protected $modelName;
    protected static $model;

    public function __construct()
    {
        parent::__construct();

        $this->modelName = $this->modelName ? $this->modelName : CONTROLLER_NAME;
        //var_dump($this->modelName);exit;
        if (!empty($this->modelName) && empty(static::$model)) {
            static::$model = D($this->modelName);
        }
    }

    /**
     * 列表
     */
    public function index()
    {
        $map = [];
        $nickname       =   I('nickname');
        $category  = I('category_id');
        
        if(!empty($nickname)){
            $map['status']  =   array('egt',0);
            if(is_numeric($nickname)){
                $map['id|nickname']=   array(intval($nickname),array('like','%'.$nickname.'%'),'_multi'=>true);
            }else{
                $map['nickname']    =   array('like', '%'.(string)$nickname.'%');
            }
        }
        if(!empty($category)){
           $map['category_id'] = ['eq',$category];
        }

        $list = $this->commonlists($this->modelName, $map);

        $this->assign('_list', $list);
        // 用于删除开始
        $this->assign('number', count($list));
        $pp = I('get.p');
        if(empty($pp)){
            $p = 1;
            $this->assign('p',$p);
        }else{
            //$p = I('get.p');
            $this->assign('p',$pp);
        }
        // 用于删除结束
        //var_dump(I('get.p'));exit;
        $this->display();
    }

    /**
     * 编辑页
     */
    public function edit()
    {
        //var_dump(static::$model->saveData());exit;
        if (IS_POST) {
            $result = static::$model->saveData();

            if ($result !== false) {
                $this->success('保存成功', U('index'));
            }

            $error = static::$model->getError();
            $this->error($error);
        }

        $id = I('get.id', 0, 'intval');

        $data = static::$model->find($id);

        $this->assign('data', $data);
        $this->display();
    }

    /**
     * 修改状态
     */
    public function changeStatus()
    {

        $pk = static::$model->getPk();

        $id     = I('request.' . $pk);
        $status = I('request.status');

        if (empty($id)) {
            $this->error('请至少选择一条数据');
        }

        $data = [
            'id'     => $id,
            'status' => $status,
        ];

        $result = static::$model->saveData($data);
        //var_dump(static::$model->getError());exit;
        if ($result !== false) {
            $this->success('修改成功');
        } else {
            $error = static::$model->getError();
            $this->error($error);
        }
    }

    /**
     * 删除数据
     */
    protected function deleteData($modelName)
    {
        $model = D($modelName);

        $paramName = $model->getPk();

        $id = I('request.' . $paramName);

        if (empty($id)) {
            $this->error('参数错误');
        }

        if (is_array($id)) {
            $id = implode(',', $id);
        }

        if ($model->delete($id)) {
            $this->success('删除成功');
        } else {
            $this->error('删除失败');
        }
    }

    /**
     * 删除数据
     */
    public function del_tmp()
    {
        $paramName = static::$model->getPk();

        $id = I('request.' . $paramName);

        if (empty($id)) {
            $this->error('参数错误');
        }

        if (is_array($id)) {
            $id = implode(',', $id);
        }

        if (static::$model->delete($id)) {
            $this->success('删除成功');
        } else {
            $this->error('删除失败');
        }
    }

    /**
     * 删除数据
     */
    public function del($p=null,$number=null)
    {
        //var_dump($p,$number);exit;
        $paramName = static::$model->getPk();

        $id = I('request.' . $paramName);

        if (empty($id)) {
            $this->error('参数错误');
        }

        if (is_array($id)) {
            $delnumber = count($id);
            $id = implode(',', $id);

        }else{
            $delnumber = 1;
        }
        //var_dump($delnumber);exit;
        if (static::$model->delete($id)) {
            if($p>1&&$number==$delnumber){
                $this->success('删除成功',U('index',['p'=>$p-1]));
            }else{
                $this->success('删除成功');
            }

        } else {
            $this->error('删除失败');
        }
    }

    /**
     * 通用分页列表数据集获取方法
     *
     *  可以通过url参数传递where条件,例如:  index.html?name=asdfasdfasdfddds
     *  可以通过url空值排序字段和方式,例如: index.html?_field=id&_order=asc
     *  可以通过url参数r指定每页数据条数,例如: index.html?r=5
     *
     * @param sting|Model  $model   模型名或模型实例
     * @param array        $where   where查询条件(优先级: $where>$_REQUEST>模型设定)
     * @param array|string $order   排序条件,传入null时使用sql默认排序或模型属性(优先级最高);
     *                              请求参数中如果指定了_order和_field则据此排序(优先级第二);
     *                              否则使用$order参数(如果$order参数,且模型也没有设定过order,则取主键降序);
     *
     * @param array        $base    基本的查询条件
     * @param boolean      $field   单表模型用不到该参数,要用在多表join时为field()方法指定参数
     * @author 朱亚杰 <xcoolcc@gmail.com>
     *
     * @return array|false
     * 返回数据集
     */
    protected function commonlists ($model,$where=array(),$order='',$base = array('status'=>array('egt',0)),$field=true,$secondTable=null,$on=null){
        //var_dump(111);exit;
        $options    =   array();
        $REQUEST    =   (array)I('request.');
        //var_dump($REQUEST);exit;
        if(is_string($model)){
            $model  =   M($model);
        }

        $OPT        =   new \ReflectionProperty($model,'options');

        $r=$OPT->setAccessible(true);

        $pk         =   $model->getPk();
        if($order===null){
            //order置空
        }else if ( isset($REQUEST['_order']) && isset($REQUEST['_field']) && in_array(strtolower($REQUEST['_order']),array('desc','asc')) ) {
            $options['order'] = '`'.$REQUEST['_field'].'` '.$REQUEST['_order'];
        }elseif( $order==='' && empty($options['order']) && !empty($pk) ){
            $options['order'] = $pk.' desc';
        }elseif($order){
            $options['order'] = $order;
        }
        unset($REQUEST['_order'],$REQUEST['_field']);

        $options['where'] = array_filter(array_merge( (array)$base, /*$REQUEST,*/ (array)$where ),function($val){
            if($val===''||$val===null){
                return false;
            }else{
                return true;
            }
        });
        if( empty($options['where'])){
            unset($options['where']);
        }
        $options      =   array_merge( (array)$OPT->getValue($model), $options );
        //var_dump($OPT->getValue($model));exit;
        $total        =   $model->where($options['where'])->count();

        if( isset($REQUEST['r']) ){
            $listRows = (int)$REQUEST['r'];
        }else{
            $listRows = C('LIST_ROWS') > 0 ? C('LIST_ROWS') : 10;
        }

        $page = new \Think\Page($total, $listRows, $REQUEST);

        if($total>$listRows){
            $page->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%');
        }
        $p =$page->show();
        //var_dump($total,$listRows,$REQUEST);exit;
        $this->assign('_page', $p? $p: '');
        $this->assign('_total',$total);
        $options['limit'] = $page->firstRow.','.$page->listRows;

        $model->setProperty('options',$options);
        if(empty($secondTable)){
            $res = $model->field($field)->select();
        }else{
            $res = $model->alias('a')->join('left join  '.$secondTable.' as b on '.$on)
                ->where($where)->field($field)->select();
            //var_dump($res);exit;
        }

        return $res;
    }
}

/* End of file CommonController.class.php */
/* Location: #Application/Admin/Controller/CommonController.class.php */
