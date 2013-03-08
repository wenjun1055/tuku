<?php
/*
*  @author  $花生
*  @vesion  $id:Acl.php 2012/6/1
*/
require_once	'Zend/Acl.php';

/**
 * Zend_Acl_Role
 */
require_once	'Zend/Acl/Role.php';

/**
 *Zend_Acl_Resource
 */
require_once	'Zend/Acl/Resource.php';


class	Custom_Acl	extends	Zend_Acl
{
	/**
	 * Constructor.
	 * @return void
	 */
	public function	__construct()
	{
		//Add resource
		$this->add(new Zend_Acl_Resource('index'));
		$this->add(new Zend_Acl_Resource('error'));
		$this->add(new Zend_Acl_Resource('home'));
		$this->add(new Zend_Acl_Resource('auth'));
		
		//Add role
		$this->addRole(new Zend_Acl_Role('guest'));
		$this->addRole(new Zend_Acl_Role('member'));
		$this->addRole(new Zend_Acl_Role('admin'));
		 
    	
		//Assign ruleadmin:imgcode
		$this->allow('guest','index','index');
		$this->allow('guest','index','imgurl');
		$this->allow('guest','auth','login');
		$this->allow('guest','auth','showreg');
		$this->allow('guest','auth','reg');
		$this->allow('guest','error','');
		
		
		$this->allow('member','home',null);
		$this->allow('member','auth','activemail');
		$this->allow('member','auth','checkvolume');
		$this->allow('member','auth','editpassword');
		$this->allow('member','auth','logout');
		$this->allow('member','error','');
		$this->allow('member','index','imgurl');
		
		$this->allow('admin');
	//	$this->deny(null,null,null); 
	}
}