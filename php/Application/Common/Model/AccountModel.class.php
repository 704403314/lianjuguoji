<?php
/**
 * 用户账户表
 * @Author: hausir
 * @Date:   2016-09-01 19:01:59
 * @File:   AccountModel.class.php
 * @Last Modified by:   hausir
 * @Last Modified time: 2016-09-01 20:03:20
 */

namespace Common\Model;

class AccountModel extends CommonModel
{
    /*protected $_validate = [
        ['username', 'require', '用户名不能为空'],
        ['username', '', '用户名已存在', 0, 'unique', 3],
    ];*/

    public function getUserById($uid, $field = '*')
    {
        if (empty($uid)) {
            return false;
        }

        $data = $this->where(['uid' => $uid])->field($field)->find();

        return $data;
    }
}

/* End of file AccountModel.class.php */
/* Location: #Application/Common/Model/AccountModel.class.php */
