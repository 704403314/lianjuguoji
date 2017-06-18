<?php
/**
 * 活动分类表
 * @Author: hausir
 * @Date:   2016-09-02 10:44:01
 * @File:   ActivityCategoryModel.class.php
 * @Last Modified by:   hausir
 * @Last Modified time: 2016-09-02 14:25:06
 */

namespace Common\Model;

class DistributionModel extends CommonModel
{
    protected $_validate = [
        ['return', 'require', '请填写商品返现周期'],
        ['time', 'require', '请填写商品提现周期'],
        ['percent_m', 'currency', '请填写正确格式的一级返现比例'],
        ['percent_n', 'currency', '请填写正确格式的二级返现比例'],
        ['return', 'currency', '请填写正确格式的返现周期'],
        ['time', 'currency', '请填写正确格式的提现周期'],

    ];
}

/* End of file ActivityCategoryModel.class.php */
/* Location: #Application/Common/Model/ActivityCategoryModel.class.php */
