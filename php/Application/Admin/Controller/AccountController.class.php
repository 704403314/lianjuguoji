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

class AccountController extends CommonController
{
    public function __construct()
    {
        //echo think_encrypt(123456); echo '<br/>';echo think_encrypt(123456);exit;
        parent::__construct();
        $this->meta_title = '用户管理';
    }



    /**
     * 删除
     */
    public function del($p=null,$number=null){
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
        $res = M('Account')->where(['id'=>['in',$id]])->save(['status'=>-1]);
        if ($res!==false) {

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
     * 资料详情
     * @param null $id  用户id
     */
    public function info($id=null){
        $res = M('Account')->find($id);
        $this->assign('res',$res);
        $this->display();
    }

    
}

/* End of file AccountController.class.php */
/* Location: #Application/Admin/Controller/AccountController.class.php */
