<?php
/**
 * Create by wujianke
 * Date: 2014-8-13
 * 功能： 和goods表相关的数据处理
 */

namespace Home\Model;
use Think\Model;

class GoodsModel extends Model{
	
	
	public function getInfoByTypeAndColor($type,$color){
		return $this->where("type='{$type}' and color='{$color}'")->find();
	}
	
	public function getInfoById($goods_id){
		return $this->where("goods_id='{$goods_id}'")->find();
	}
	
	/* 所有MODEL公共方法 */
	public function get_one($where,$field='')
	{
		return $this->field($field)->where($where)->find();
	}

	public function get_all($where,$field='')
	{
		return $this->field($field)->where($where)->select();
	}

	public function get_count($where,$field='')
	{
		return $this->field($field)->where($where)->count();
	}
	
	public function update_one($where,$data){
		return $this->where($where)->save($data);
 	}
}
