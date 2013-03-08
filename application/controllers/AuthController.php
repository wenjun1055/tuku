<?php
/*
*  @author  $花生
*  @vesion  $id:Auth.php 0010 2012/6/1
*/
require_once APPLICATION_PATH.'/models/User.php';
require_once APPLICATION_PATH.'/models/Disk.php';

class AuthController extends Zend_Controller_Action 
{
	public function init()
    {
    	$this->db = Zend_Registry::get('dbAdapter');
    	$this->authNamespace = new Zend_Session_Namespace('Zend_Auth');
    }
    
	public function loginAction()
    {
    	if($this->_request->isPost())
    	{
    		Zend_Loader::loadClass('Zend_Filter_StripTags');
    		$filter = new Zend_Filter_StripTags();
    		$email = $filter->filter($this->_request->getPost('email'));
    		$password = md5($filter->filter($this->_request->getPost('password')));
    		if($email!='' && $password!='')
    		{
    			$authAdapter = new Zend_Auth_Adapter_DbTable($this->db);
    			$authAdapter->setTableName('user');
    			$authAdapter->setIdentityColumn('email');
    			$authAdapter->setCredentialColumn('password');
    			$authAdapter->setIdentity($email);
    			$authAdapter->setCredential($password);
    			$auth=Zend_Auth::getInstance();
    			$result=$auth->authenticate($authAdapter);
    			if($result->isValid()){
    				$data=$authAdapter->getResultRowObject(null,'password');
    				$this->authNamespace->user_info = $data;
    				$disk_model = new Disk();
    				$disk = $disk_model->fetchRow('user_id='.$this->authNamespace->user_info->id)->toArray();
					$this->authNamespace->disk_info = $disk;
					$this->authNamespace->user_password = $password;
					$vdisk_library = new Custom_Vdisk($disk['appkey'], $disk['appsecret']);
					$vdisk_library->get_token($disk['account_name'], $disk['account_password']);
					$vdisk_library->keep_token();
					$this->authNamespace->token = $vdisk_library->token;	
					$user_model = new User();
					$user_login_info = array(
						'last_ip'		=>	$_SERVER["REMOTE_ADDR"],
						'last_login'	=>	time(),
						'visit_count'	=>	new Zend_Db_Expr('visit_count+1')
					);
					$user_model->update($user_login_info, 'id='.$this->authNamespace->user_info->id);
    				$auth->getStorage()->write($data);
    				$this->_redirect('/home/index');
    				$request=$this->getRequest();
    				exit();
    			}else{
    				echo mb_convert_encoding("<script>alert('登录失败，请重新登录！');</script>", 'gb2312', 'utf-8');
    				echo "<script>window.location.href='".BASE_URL."';</script>";
    				exit();
    			}
    			
    		}else{
    			$this->_forward('error','error');
    		}
    		$this->_forward('error','error');
    	}
    }
   
    public function logoutAction()
    {
    	Zend_Auth::getInstance()->clearIdentity();
    	$this->_redirect('/');	
    }
    
	public function showregAction()
    {
    	$vdisk = new Custom_Curl('reg');
    	$this->view->vcode = $vdisk->get_vcode();
    	$this->view->saeut = $vdisk->get_saeut();
    	$this->view->sessid = $vdisk->get_sessid();
    	$this->render('register');
    }
    
    public function regAction()
    {
    	if($this->_request->isPost())
    	{
    		$vdisk = new Custom_Curl('login');
    		Zend_Loader::loadClass('Zend_Filter_StripTags');
    		$filter = new Zend_Filter_StripTags();
    		$email = $filter->filter($this->_request->getPost('email'));
    		$password = md5($filter->filter($this->_request->getPost('password')));
    		$vcode = $filter->filter($this->_request->getPost('vcode'));
    		$saeut = $filter->filter($this->_request->getPost('saeut'));
    		$sessid = $filter->filter($this->_request->getPost('sessid'));
    		$vdisk_password = time();
    		$user_vdisk = array(
    			'email'		=>	$email,
    			'password'	=>	$vdisk_password,
    			'vcode'		=>	$vcode,
    			'sessid'	=>	$sessid,
    			'saeut'		=>	$saeut
    		);
    	 	$result = $vdisk->vdisk_reg($user_vdisk);
          	if($result == 'OK')
    		{
    			$user_data = array(
    				'email'			=>	$email,
    				'password'		=>	$password,
    				'reg_time'		=>	$vdisk_password,
    				'visit_count'	=>	'0',
    				'type'			=>	'0'
    			);
    			$data_login = array(
    				'email'		=> 	$email,
    				'password'	=>	$vdisk_password
    			);
    			$vdisk->vdisk_login($data_login);
    			$vdisk->developer_info($data_login);
    			$vdisk->add_app();
   				$app = $vdisk->get_appkey();
    			try {
    				$this->db->beginTransaction();
    				$user_model = new User();
    				$id = $user_model->insert($user_data);
    				$disk_sql = "insert into `disk` (user_id,appkey,appsecret,account_name,account_password,account_volume,user_volume) values ('".$id."','".$app['key']."','".$app['secret']."','".$email."','".$vdisk_password."','52428800','52428800')";
    				$this->db->query($disk_sql);
    				$this->db->commit();
    			} catch (Exception $e) {
    				$this->db->rollBack();
    				echo mb_convert_encoding("<script>alert('".$e->getMessage()."');</script>",'gb2312', 'utf-8');
    				echo "<script>window.location.href='".BASE_URL."/auth/showreg';</script>";
    			}
    			echo mb_convert_encoding("<script>alert('注册成功！验证信息已发送到邮箱！');</script>" , 'gb2312', 'utf-8');
    			echo "<script>window.location.href='".BASE_URL."/';</script>";
    		}else {
    			echo mb_convert_encoding("<script>alert('".$result."');</script>" , 'gb2312', 'utf-8');
    			echo "<script>window.location.href='".BASE_URL."/auth/showreg';</script>";
    		}
    	}else {
    		$this->_forward('error','error');
    	}
    	exit();
    }
    
	public function activemailAction()
    {
    	$vdisk = new Custom_Curl('login');
    	$data_login = array(
    				'email'		=> 	$this->authNamespace->disk_info['account_name'],
    				'password'	=>	$this->authNamespace->disk_info['account_password'],
    	);
    	$vdisk->vdisk_login($data_login);
    	echo $vdisk->send_active_mail();
   		exit();
    }
    
    public function checkvolumeAction()
    {
    	$vdisk_library = new Custom_Vdisk();
		$vdisk_library->token = $this->authNamespace->token;
		$disk_model = new Disk();
		$volume = $vdisk_library->get_quota();
		if($volume['err_code'] == '0')
		{
			if ($volume['data']['total'] > 52428800) 
			{
				$data = array(
					'used_volume'		=>	$volume['data']['used'],
					'account_volume'	=>	$volume['data']['total'],
					'user_volume'		=>	'2147483648'
				);
				$this->authNamespace->disk_info['user_volume'] = '2147483648';
				$disk_model->update($data,'id='.$this->authNamespace->disk_info['id']);
				echo '1';
			}else {
				echo mb_convert_encoding('<strong>账户已经激活！</strong>' , 'gb2312', 'utf-8');
			}
		}else {
				echo mb_convert_encoding('<strong>账户激活失败，请重新激活！</strong>' , 'gb2312', 'utf-8');
			}
		exit();
    }
    
    public function editpasswordAction()
    {
    	$user_model = new User();
    	$filter = new Zend_Filter_StripTags();
    	$old_password = $filter->filter($this->_request->getPost('old_password'));
    	$new_password = $filter->filter($this->_request->getPost('new_password'));
    	$confirm_password = $filter->filter($this->_request->getPost('confirm_password'));
    	if(!empty($old_password) && !empty($new_password) && !empty($confirm_password))
    	{
    		if($new_password == $confirm_password)
    		{
    			if($this->authNamespace->user_password == md5($old_password))
    			{
    				
    				if($user_model->update(array('password'=>md5($new_password)), 'id='.$this->authNamespace->user_info->id))
    				{
    					$this->authNamespace->user_password = md5($new_password);
    					echo '<div class="alert alert-block alert-success"><center><strong>密码修改成功，新密码为：'.$new_password.'下次登录请使用新密码！</strong></center></div>';
    				}else {
    					echo '<div class="alert alert-block alert-error"><center><strong>密码修改失败，请重试！</strong></center></div>';
    				}
    			}else {
    				echo '<div class="alert alert-block alert-error"><center><strong>旧密码输入错误，请重新输入！</strong></center></div>';
    			}
    		}else {
    			echo '<div class="alert alert-block alert-error"><center><strong>两次输入密码不一样，请重新输入！</strong></center></div>';
    		}
    	}else {
    		echo '<div class="alert alert-block alert-error"><center><strong>输入密码不能为空！</strong></center></div>';
    	}
    	exit();
    }
}