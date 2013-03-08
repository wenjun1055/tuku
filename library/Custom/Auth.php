<?php
/*
*  @author  $花生
*  @vesion  $id:Auth.php 2012/6/1
*/
require_once	'Zend/Controller/Plugin/Abstract.php';

class	Custom_Auth	extends	Zend_Controller_Plugin_Abstract 
{
	/**
	 * An instance of Zend_Auth
	 * @var Zend_Auth
	 */
	private $_auth;
	
	/**
	 * An instance of Custom_Acl
	 * @var Custom_Acl
	 */
	private $_acl;
	
	/**
	 * Redirect to a new controller when the user has a invalid indentity.
	 * @var array
	 */
	private $_noauth=array(	'controller'=>'error',
							'action'=>'error');
	/**
	 * Redirect to 'error' controller when the user has a vailid identity 
	 * but no privileges
	 * @var array
	 */
	private $_nopriv=array(	'controller'=>'error',
							'action'=>'error');
	
	/**
	 * Constructor.
	 * @return void
	 */
	
	public function	__construct($auth,$acl)
	{
		$this->_auth=$auth;
		$this->_acl=$acl;
	}
	
	/**
	 * Track user privileges.
	 * @param Zend_Controller_Request_Abstract $request
	 * @return void
	 */
	public function	preDispatch(Zend_Controller_Request_Abstract $request)
	{
		if($this->_auth->hasIdentity())
		{
			switch ($this->_auth->getIdentity()->type)
			{
				case 10 :
					$role = 'admin';
					break;
				case 0 :
					$role = 'member';
					break;
			}
		}else{
			$role='guest';
		}
		$controller=$request->controller;
		$action=$request->action;
		$resource=$controller;
		if(!$this->_acl->has($resource)){
			$resource=null;
		}
		if(!$this->_acl->isAllowed($role,$resource,$action)){
			if(!$this->_auth->hasIdentity()){
				$controller=$this->_noauth['controller'];
				$action=$this->_noauth['action'];
			}else{
				$controller=$this->_nopriv['controller'];
				$action=$this->_nopriv['action'];
			}
		}
		
		$request->setControllerName($controller);
		$request->setActionName($action);
	}
} 