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

class GoodsModel extends CommonModel
{
    protected $_validate = [
        ['name','','名称已存在',self::MUST_VALIDATE,'unique'],
        ['card','','充值卡卡号已存在',self::MUST_VALIDATE,'unique'],
        //['category_id','require','请选择商品分类',self::MUST_VALIDATE],
        ['name','require','请填写名称'],
        ['card','require','请填写充值卡卡号'],
        ['price','require','请填写价格'],
        ['price','currency','请填写正确格式的价格'],
        ['image','require','请上传图片'],
        //['content','require','请填写介绍'],
        ['sort','require','请填写排序'],
        ['sort','number','排序请填写数字'],


    ];
    /**
     * 保存数据
     */
    public function saveData($data)
    {
        //var_dump(11);exit;
        $pk = $this->getPk();
        $fields = $this->getDbFields();
        $data = $this->create();
        //var_dump($this->getError());exit;
        if ($data) {

            //if(empty($data['gallery'])){
            //    $this->error = '请上传商品相册';
            //    return false;
            //}


            $dataa = I('post.');
          /*  if(empty($dataa['img_url'][0])){
                //var_dump($dataa['img_url']);exit;
               $this->error = '请上传商品页面中间展示的图片';
                return false;
            }*/
            if (empty($data[$pk])) {
                if (in_array('createtime', $fields)) {
                    if (isset($data['createtime'])) {
                        $data['createtime'] = strtotime($data['createtime']);
                    } else {
                        $data['createtime'] = time();
                    }
                }
                //var_dump($data);exit;
                $res = $this->add($data);
                if ($res !== false) {


                }
                return $res;
            } else {
                $res = $this->save($data);
                if($res===false){
                    return false;
                }else{

                    return $res;
                }
            }
        }else{
            return false;
        }


    }
  
}

/* End of file ActivityCategoryModel.class.php */
/* Location: #Application/Common/Model/ActivityCategoryModel.class.php */
