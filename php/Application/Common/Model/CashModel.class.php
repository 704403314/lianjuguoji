<?php
/**
 * 广告表
 * @Author: hausir
 * @Date:   2016-08-23 10:59:51
 * @File:   AdModel.class.php
 * @Last Modified by:   hausir
 * @Last Modified time: 2016-09-01 17:20:02
 */

namespace Common\Model;

class CashModel extends CommonModel
{
    /**
     * 保存数据
     */
    public function saveData($data)
    {
        $pk = $this->getPk();
        $fields = $this->getDbFields();
        $data = $this->create($data);

        if ($data) {

            if (isset($data['start_time'])) {
                $data['start_time'] = strtotime($data['start_time']);
            }
            if (isset($data['end_time'])) {
                $data['end_time'] = strtotime($data['end_time']);
            }

            if (in_array('update_time', $fields)) {
                if (isset($data['update_time'])) {
                    $data['update_time'] = strtotime($data['update_time']);
                } else {
                    $data['update_time'] = time();
                }
            }

            if (in_array('public_time', $fields)) {
                if (isset($data['status']) && !empty($data['status'])) {
                    $data['public_time'] = time();
                }
            }

            if (empty($data[$pk])) {
                if (in_array('createtime', $fields)) {
                    if (isset($data['createtime'])) {
                        $data['createtime'] = strtotime($data['createtime']);
                    } else {
                        $data['createtime'] = time();
                    }
                }

                return $this->add($data);
            } else {
                $data['dealtime'] = time();
                return $this->save($data);
            }
        }

        return false;
    }
}

/* End of file AdModel.class.php */
/* Location: #Application/Common/Model/AdModel.class.php */
