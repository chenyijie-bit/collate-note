<?php
/**
 * Created by PhpStorm.
 * User: zhenjiayv
 * Date: 2017/5/9
 * Time: 11:20
 *
 */
namespace Hospital\Model;
class MydoctorsViewModel extends \Think\Model\ViewModel{

    public $viewFields = array(
    	'collect'=>array('*','_type'=>'LEFT'),
    	'doctor'=>array('*','_on'=>'doctor.doctor_id=collect.doctor_id'),
    	//'user_opinion'=>array('avg(user_opinion.opinion_level)'=>'level','_on'=>'user_opinion.doctor_id=collect.doctor_id'),
    	'ranks'=>array('ranks_name','_on'=>'doctor.ranks_id=ranks.ranks_id'),
    	'department'=>array('department_name','_on'=>'doctor.department_id=department.department_id'),
    );


}