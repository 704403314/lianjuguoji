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

class BalanceController extends CommonController
{
    protected $modelName = 'Cash';
    public function __construct()
    {
        
        parent::__construct();
        $this->meta_title = '用户返现记录';
    }

    /**
     * 列表
     */
    public function index()
    {

        $map = [];

        //$rs = M('Balance')->alias('a')->join('left join hanshi_distribution as b on a.goods_id=b.goods_id')
        //      ->where(['a.type'=>4])->field('a.*,b.return')->select();
        ////var_dump($rs);exit;
        //foreach ($rs as $k=>$v){
        //    if(time()>=86400*$v['return']+$v['createtime']){
        //        M('Balance')->where(['id'=>$v['id']])->save(['type'=>5,'createtime'=>86400*$v['return']+$v['createtime']]);
        //    }
        //}
        $list = $this->commonlists('Cash',$map,'status asc,createtime desc');
        $this->assign('users',M('Account')->getField('id,nickname,phone'));

        $this->assign('_list', $list);
        // 用于删除开始
        $pp = I('get.p');
        $this->assign('number', count($list));
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

        $data = static::$model->alias('ba')->join('left join  onethink_account as ac on ac.id=ba.uid')

            ->where(['ba.id'=>$id])->field('ba.*,ac.nickname,ac.phone')
            ->find();
        //var_dump($data);exit;
        $data['createtime'] = date('Y-m-d H:i:s',$data['createtime']);
        $this->assign('User',M('Account')->getField('id,nickname,phone'));
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
        if($status==1){
            $data = [
                'id'     => $id,
                'status' => $status,
                'dealtime' => time(),
            ];
        }else{
            $data = [
                'id'     => $id,
                'status' => $status,
                'dealtime' => '',
            ];
        }


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
     * 资料详情
     * @param null $id  商品id
     */
    public function info($id=null){
        $res = M('Cash')->find($id);
        //var_dump($res);exit;
        $this->assign('res',$res);
        $this->display();
    }

}

/* End of file AccountController.class.php */
/* Location: #Application/Admin/Controller/AccountController.class.php */
