<?php
/**
 * 用户账户管理
 * @Author: hausir
 * @Date:   2016-09-01 18:58:05
 * @File:   AccountController.class.php
 * @Last Modified by:   hausir
 * @Last Modified time: 2016-09-01 18:59:01
 */

namespace Admin\Controller;

class GoodsController extends CommonController
{
    public function __construct()
    {
        parent::__construct();
        $this->meta_title = '商品管理';
    }

    public function index()
    {
       
        //$categorys = M('GoodsCategory')->where(['status'=>1])->field('id,name')->select();
        //$this->assign('categorys',$categorys);
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

        $list = $this->commonlists($this->modelName, $map,'sort asc');

        $this->assign('_list', $list);
        // 用于删除开始
        $this->assign('number', count($list));
        $pp = I('get.p');
        if(empty($pp)){
            $p = 1;
            $this->assign('p',$p);
        }else{
            $p = I('get.p');
            $this->assign('p',$p);
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

        //var_dump($designs);exit;
        $this->assign('data', $data);
        $this->display();
    }

    /**
     * 资料详情
     * @param null $id  商品id
     */
    public function info($id=null){
        $res = M('Goods')->find($id);
        $res['gallery'] = explode(',',$res['gallery']);
        $res['photo'] = explode(',',$res['photo']);
        $res['content'] = html_entity_decode($res['content']);
        $res['category_id'] = D('GoodsCategory')->getTitleById($res['category_id']);
        if(!empty($res['design_id'])){
            $res['design_id'] = M('Design')->where(['id'=>['in',$res['design_id']]])->getField('name',true);
            $res['design_id'] = implode(',',$res['design_id']);
        }else{
            $res['design_id'] = '无';
        }
        if(!empty($res['color_id'])){
            $res['color_id'] = M('GoodsColor')->where(['id'=>['in',$res['color_id']]])->getField('name',true);
            $res['color_id'] = implode(',',$res['color_id']);
        }else{
            $res['color_id'] = '无';
        }
        if(!empty($res['button_id'])){
            $res['button_id'] = M('GoodsButton')->where(['id'=>['in',$res['button_id']]])->getField('name',true);
            $res['button_id'] = implode(',',$res['button_id']);
        }else{
            $res['button_id'] = '无';
        }
        if(!empty($res['sleeve_id'])){
            $res['sleeve_id'] = M('GoodsSleeve')->where(['id'=>['in',$res['sleeve_id']]])->getField('name',true);
            $res['sleeve_id'] = implode(',',$res['sleeve_id']);
        }else{
            $res['sleeve_id'] = '无';
        }
        if(!empty($res['theme_id'])){
            $res['theme_id'] = M('GoodsTheme')->where(['id'=>['in',$res['theme_id']]])->getField('name',true);
            $res['theme_id'] = implode(',',$res['theme_id']);
        }else{
            $res['theme_id'] = '无';
        }
        $this->assign('res',$res);
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

        $result = M('Goods')->save($data);
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
     * 评论
     * @param null $id  商品id
     */
    public function comment($id=null){

        $this->assign('id',$id);
        $this->assign('users',M('Account')->getField('id,nickname,image'));
        //var_dump(M('Account')->getField('id,nickname,image'));exit;
        $list = $this->commonlists('GoodsComment',['goods_id'=>$id],'createtime desc');
        //$list = $this->commonlists('GoodsComment',['a.goods_id'=>$id],'a.createtime desc',[],'a.*,b.nickname,b.image as userimage','hanshi_account','a.uid=b.id');
        //var_dump(M('Comment')->getLastSql());exit;
        // 用于删除开始
        $this->assign('number', count($list));
        $pp = I('get.p');
        if(empty($pp)){
            $p = 1;
            $this->assign('p',$p);
        }else{
            $p = I('get.p');
            $this->assign('p',$p);
        }
        // 用于删除结束
        $this->assign('_list',$list);
        $this->display();
    }

    /**
     * 删除评论
     */
    public function delComment($p=null,$number=null,$cid=null){
        $id = I('request.id');

        if (empty($id)) {
            $this->error('请至少选择一条数据');
        }

        if (is_array($id)) {
            $delnumber = count($id);
            $id = implode(',', $id);

        }else{
            $delnumber = 1;
        }
        //var_dump($id);exit;
        $res = M('GoodsComment')->delete($id);
        if ($res!==false) {

            if($p>1&&$number==$delnumber){
                $this->success('删除成功',U('Goods/comment',['p'=>$p-1,'id'=>$cid]));
            }else{
                $this->success('删除成功');
            }
        } else {
            $this->error('删除失败');
        }
    }

    /**
     * 评论详情
     * @param null $id  评论id
     */
    public function commentinfo($id=null){
        $res = M('GoodsComment')->alias('a')->join('left join hanshi_goods as b on a.goods_id=b.id')
                 ->where(['a.id'=>$id])->field('a.*,b.name')->find();
       if(!empty($res['image'])){
           $res['image'] = explode(',',$res['image']);
       }
        //var_dump($res['image']);
        $res['content'] = html_entity_decode($res['content']);
        $this->assign('res',$res);
        $this->display();
    }
}

/* End of file AccountController.class.php */
/* Location: #Application/Admin/Controller/AccountController.class.php */
