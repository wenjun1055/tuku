<?php
/*
*  @author  $花生
*  @vesion  $id:Auth.php 0010 2012/6/6
*/

require_once APPLICATION_PATH.'/models/Picture.php';
class Custom_Image
{
	private $_allow_type = array('jpg','jpeg','gif','png');
	private $_allow_size = 307200;    //大小限制为300K
	public $message;
	
	public function __construct() 
	{
		$this->authNamespace = new Zend_Session_Namespace('Zend_Auth');
		$this->vdisk_library = new Custom_Vdisk();
		$this->vdisk_library->token = $this->authNamespace->token;
	}
	
	
	public function check_type($file_name)
	{
		preg_match('|\.(\w+)$|', $file_name, $ext);
		#转化成小写
		$ext = strtolower($ext[1]);
		#判断是否在被允许的扩展名里
		if(!in_array($ext, $this->_allow_type)){
 			echo $this->message = $file_name.'&nbsp;&nbsp;上传失败！文件类型错误！<br>';
 			return FALSE;
		}
		return TRUE;
	}
	
	public function check_size($file_size,$file_name)
	{
		if($file_size > $this->_allow_size)
		{
			echo $this->message =  $file_name.'&nbsp;&nbsp;上传失败！文件超过300K<br>';
			return FALSE;
		}
		return TRUE;
	}
	
	public function disk_2_url($picture = '')
	{
		$picture_url = array();
		if(!empty($picture))
		{
			
			foreach ($picture as $row)
			{
				$result = $this->vdisk_library->get_file_info($row['fid']);
				$picture_url[] = array(
					'id'		=>	$row['id'],
					'time'		=>	date('Y-m-d', $row['upload_time']),
					'name'		=>	$row['desc_name'],
					'url'		=>	$result['data']['s3_url'],
					'fid'		=>	$row['fid'],
				); 
			}
			return $picture_url;
		}else {
			return FALSE;
		}
	}
	
	public function img_of_gallery($gallery = '')
	{
		if(!empty($gallery))
		{
			$picture_model = new Picture();
			$new_gallery = array();
			foreach ($gallery as $row)
			{
				$picture = $picture_model->fetchAll('album_id='.$row['id'])->toArray();
				$count = count($picture);
				$new_gallery[] = array(
					'id'			=>	$row['id'],
					'user_id'		=>	$row['user_id'],
					'album_name'	=>	$row['album_name'],
					'create_time'	=>	date('Y-m-d', $row['create_time']),
					'picture_count'	=>	$count
				);
			}
			return $new_gallery;
		}else {
			
		}
	}
	
	public function delete_picture($fid = '') 
	{
		if(!empty($fid))
		{
			$picture_model = new Picture();
			$this->vdisk_library->delete_file($fid);
			if($picture_model->delete('fid='.$fid))
			{
				return TRUE;
			}else {
				return FALSE;
			}
		}else {
			return FALSE;
		}
	}
	
}
