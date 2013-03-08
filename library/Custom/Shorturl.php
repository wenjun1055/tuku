<?php
/*
*  @author  $花生
*  @vesion  $id:Auth.php 0010 2012/6/7
*/

class Custom_Shorturl
{
	
	private $_long_2_short = "http://126.am/api!shorten.action";		//长转短
	private $_short_2_long = "http://126.am/api!expand.action";			//短转长
	private $_custom_long_2_short = "http://126.am/api!alias.action";	//自定义长转短
	private $_ApiKey = 'fe0adf3078524302899ce71392151181';				//126.am获取到的apikey
	
	public function transform($type = '', $param = '')
	{
		if(!empty($type) && !empty($param))
		{
			if($type = 'long_2_short')
			{
				$post_data="key=".$this->_ApiKey."&longUrl=".$param;
				$post_url = $this->_long_2_short;
			}else if($type = 'short_2_long'){
				$post_data="key=".$this->_ApiKey."&shortUrl=".$param;
				$post_url = $this->_short_2_long;
			}else if($type = 'custom'){
				$post_data="key=".$this->_ApiKey."&longUrl=".$param['long_url']."&userShort=".$param['user_short'];
				$$post_url = $this->_short_2_long;
			}
			$transform_url=curl_init($post_url);//创建CURL对象
			curl_setopt($transform_url,CURLOPT_HEADER,0);//返回头部
			curl_setopt($transform_url,CURLOPT_RETURNTRANSFER,1);//返回信息
			curl_setopt($transform_url,CURLOPT_POST,1);//设置POST提交
			curl_setopt($transform_url,CURLOPT_POSTFIELDS,$post_data);//提交POST数据
			$data=curl_exec($transform_url);//执行已经定义的设置
			curl_close($transform_url);//关闭
			return json_decode($data);
		}else {
			return array();
		}
	}
}