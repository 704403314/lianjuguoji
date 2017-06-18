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

class DistributionController extends CommonController
{
    public function __construct()
    {
        parent::__construct();
        $this->meta_title = '分销管理';
    }

    /**
     * 列表
     */
    public function index()
    {
        $map = [];
       /* $phone       =   I('phone');
        $type       =   I('type');

        if(!empty($phone)){
            $map['status']  =   array('egt',0);
            $map['phone']=   array('like','%'.$phone.'%');

        }
        if(!empty($type)){
            $map['type'] = ['eq',$type];
        }*/

        $list = $this->commonlists('Distribution', $map);
        //$this->assign('users',M('Account')->getField('id,nickname'));
        //$this->assign('goods',M('Goods')->getField('id,name'));
        
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
        //$this->assign('goods',M('Goods')->getField('id,name'));
        $this->assign('data', $data);
        $this->display();
    }

    
}

/* End of file AccountController.class.php */
/* Location: #Application/Admin/Controller/AccountController.class.php */
