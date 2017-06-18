<?php
/**
 * 数据库模型基类
 * @Author: hausir
 * @Date:   2016-09-01 16:21:40
 * @File:   CommonModel.class.php
 * @Last Modified by:   hausir
 * @Last Modified time: 2016-09-02 14:55:14
 */

namespace Common\Model;

use Think\Model;

class CommonModel extends Model
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
                return $this->save($data);
            }
        }

        return false;
    }

    /**
     * 获取数据列表
     */
    public function getList($field = '*', $where = [], $order = 'id desc', $needPage = false, $page = 1, $pageSize = 10)
    {
        if ($needPage) {
            $page = I('get.page', $page, 'intval');
            return $this->where($where)->order($order)->limit($pageSize)->page($page)->field($field)->select();
        } else {
            return $this->where($where)->order($order)->field($field)->select();
        }
    }

    /**
     * 根据id获取某一字段的值
     */
    public function getFieldById($id, $field)
    {
        return $this->where(['id' => $id])->getField($field);
    }
}

/* End of file CommonModel.class.php */
/* Location: #Application/Common/Model/CommonModel.class.php */
