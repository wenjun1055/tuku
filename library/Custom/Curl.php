<?php
/*
*  @author  $花生
*  @vesion  $id:Curl.php  2012/6/2
*/
class Custom_Curl
{
	private $_sae = array();
	private $_contents;
	private $_reg_post_url = "http://vdisk.me/?a=save";//注册信息提交地址
	private $_reg_url = "http://vdisk.me/?a=login#register";	//注册页面地址
	private $_login_url = "http://vdisk.me/?a=login";	//登录显示页面
	private $_login_post_url = "http://vdisk.me/?a=login_check";  //登录信息提交页面
	private $_developer_post_url = "http://vdisk.me/api/updateinfo";		//填写开发者信息 
	private $_add_app_url = "http://vdisk.me/api/addappdo";		//创建开发者应用
	private $_applist_url = "http://vdisk.me/api/applist";	//应用列表页
	private $_active_mail_url = "http://vdisk.me/?a=send_active_mail";	//激活账户邮件
	
	public function __construct($type='')
	{
		if($type == 'reg')
		{
			$this->get_context($this->_reg_url);
		}else if ($type == 'login')
		{
			$this->get_context($this->_login_url);
		}
	}
	
	/*得到http协议的头文件*/
	private function header_function($ch, $header)
	{
		$this->_sae[] = $header;
		return strlen($header);
	}
	
	/*打开网页并返回响应的内容*/
	private function get_context($url)
	{
		$ch = curl_init(); 
		$timeout = 5;
		curl_setopt($ch, CURLOPT_URL, $url); 
		curl_setopt($ch, CURLOPT_HEADERFUNCTION,array('Custom_Curl', 'header_function'));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout); 
		$this->_contents = curl_exec($ch); 
		curl_close($ch);
	}
	
	//获取验证码的地址
	public function get_vcode()
	{
		$pattern = "/<img src='http:\/\/(.+?)'/is"; 
		preg_match_all($pattern, $this->_contents, $reg);
		$vcode = 'http://'.$reg[1][0];
		return $vcode;
	}
	
	//获取saeut的值
	public function get_saeut()
	{
		$pattern_saeut="/saeut=(.+?);/is"; 
		preg_match_all($pattern_saeut, $this->_sae[6], $saeut);
		return $saeut[1][0];
	}
	
	//获取PHPSESSID
	public function get_sessid()
	{
		$pattern_sessid="/PHPSESSID=(.+?);/is"; 
		preg_match_all($pattern_sessid, $this->_sae[8], $sessid);
		return $sessid[1][0];
	}
	
	//注册帐号
	public function vdisk_reg($user = '')
	{
		$reg_header = array(
	//		'0'	=>	'Host: vdisk.me',
	//		'1'	=>	'Connection: keep-alive',
	//		'2'	=>	'Accept-Language: zh-cn,zh;q=0.8,en-us;q=0.5,en;q=0.3',
	//		'3'	=>	'Content-Type: application/x-www-form-urlencoded; charset=UTF-8',
	//		'4'	=>	'X-Requested-With	XMLHttpRequest',
			'0'	=>	'Cookie: saeut='.$user['saeut'].'; PHPSESSID='.$user['sessid']
		);
		$post_data = "password=".$user['password']."&confirm=".$user['password']."&account=".$user['email']."&code=000qqq&uid=''&vcode=".$user['vcode'];
        $reg=curl_init($this->_reg_post_url);//创建CURL对象
		curl_setopt($reg,CURLOPT_HEADER,0);//返回头部
		curl_setopt($reg,CURLOPT_RETURNTRANSFER,1);//返回信息
		curl_setopt($reg, CURLOPT_HTTPHEADER, $reg_header); 
		curl_setopt($reg,CURLOPT_POST,1);//设置POST提交
		curl_setopt($reg,CURLOPT_POSTFIELDS,$post_data);//提交POST数据
		$result = curl_exec($reg);//执行已经定义的设置
		curl_close($reg);   
        return $result;
	}
	
	//模拟登录vdisk
	public function vdisk_login($data)
	{
		$this->login_header = array(
	//		'0'	=>	'Host: vdisk.me',
	//		'1'	=>	'Connection: keep-alive',
	//		'2'	=>	'Accept-Language: zh-cn,zh;q=0.8,en-us;q=0.5,en;q=0.3',
	//		'3'	=>	'Content-Type: application/x-www-form-urlencoded; charset=UTF-8',
	//		'4'	=>	'X-Requested-With	XMLHttpRequest',
			'0'	=>	'Cookie: saeut='.$this->get_saeut().'; PHPSESSID='.$this->get_sessid()
		);
		$post_data="email=".$data['email']."&password=".$data['password'];
		$login=curl_init($this->_login_post_url);//创建CURL对象
		curl_setopt($login,CURLOPT_HEADER,0);//返回头部
		curl_setopt($login,CURLOPT_RETURNTRANSFER,1);//返回信息
		curl_setopt($login, CURLOPT_HTTPHEADER, $this->login_header); 
		curl_setopt($login,CURLOPT_POST,1);//设置POST提交
		curl_setopt($login,CURLOPT_POSTFIELDS,$post_data);//提交POST数据
		$result = curl_exec($login);//执行已经定义的设置
		curl_close($login);
		return $result;
	}
	
	//填写开发者资料
	public function developer_info($data)
	{
		$post_data="type=personal&name=".time()."&mail=".$data['email']."&phone=123456789&site=http://wenjun.in";
		$login=curl_init($this->_developer_post_url);//创建CURL对象
		curl_setopt($login,CURLOPT_HEADER,0);//返回头部
		curl_setopt($login,CURLOPT_RETURNTRANSFER,1);//返回信息
		curl_setopt($login, CURLOPT_HTTPHEADER, $this->login_header); 
		curl_setopt($login,CURLOPT_POST,1);//设置POST提交
		curl_setopt($login,CURLOPT_POSTFIELDS,$post_data);//提交POST数据
		$result = curl_exec($login);//执行已经定义的设置
		curl_close($login);
		return $result;
	}
	
	//添加应用
	public function add_app()
	{
		$post_data="app_name=".time()."&source_url=http://wenjun.in&app_desc=".time()."&app_allow=1";
		$add_app=curl_init($this->_add_app_url);//创建CURL对象
		curl_setopt($add_app,CURLOPT_HEADER,0);//返回头部
		curl_setopt($add_app,CURLOPT_RETURNTRANSFER,1);//返回信息
		curl_setopt($add_app, CURLOPT_HTTPHEADER, $this->login_header); 
		curl_setopt($add_app,CURLOPT_POST,1);//设置POST提交
		curl_setopt($add_app,CURLOPT_POSTFIELDS,$post_data);//提交POST数据
		$result = curl_exec($add_app);//执行已经定义的设置
		curl_close($add_app);
		return $result;
	}
	
	//获得App Key和App Secret
	public function get_appkey()
	{
		$app = curl_init($this->_applist_url); 
		$timeout = 5;
		curl_setopt($app,CURLOPT_HEADER,0);//返回头部
		curl_setopt($app, CURLOPT_RETURNTRANSFER, 1); 
		curl_setopt($app, CURLOPT_HTTPHEADER, $this->login_header); 
		curl_setopt($app, CURLOPT_CONNECTTIMEOUT, $timeout); 
		$content = curl_exec($app);
		curl_close($app);
		$pattern_key="/App\sKey:\s(.+?)\<em/is"; 
		preg_match_all($pattern_key, $content, $appkey);
		$app_value['key'] = trim($appkey[1][0]);
		$pattern_secret="/App\sSecret:\s(.+?)\</is"; 
		preg_match_all($pattern_secret, $content, $appsecret);
		$app_value['secret'] = trim($appsecret[1][0]);
		return $app_value;
	}
	
	//发送重新激活邮件
	public function send_active_mail()
	{
		$send=curl_init($this->_active_mail_url);//创建CURL对象
		curl_setopt($send,CURLOPT_HEADER,0);//返回头部
		curl_setopt($send,CURLOPT_RETURNTRANSFER,1);//返回信息
		curl_setopt($send, CURLOPT_HTTPHEADER, $this->login_header); 
		$result = curl_exec($send);//执行已经定义的设置
		curl_close($send);
		return $result;
	}

}
