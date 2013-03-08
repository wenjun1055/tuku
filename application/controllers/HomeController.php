<?php
/*
*  @author  $花生
*  @vesion  $id:Auth.php 0010 2012/6/1
*/
require_once APPLICATION_PATH.'/models/Disk.php';
require_once APPLICATION_PATH.'/models/Picture.php';
require_once APPLICATION_PATH.'/models/Album.php';

class HomeController extends Zend_Controller_Action 
{
	private $_numPerPage = 12;
	private $_pageRange = 5;
	
	public function init()
	{
		$this->db = Zend_Registry::get('dbAdapter');
		Zend_Loader::loadClass('Zend_Filter_StripTags');
		$this->authNamespace = new Zend_Session_Namespace('Zend_Auth');
	}
	
	public function indexAction()
	{
		$image_library = new Custom_Image();
		$album_id = $this->_getParam('id',0);
		$page = $this->_getParam('page', 1);
		$picture_model = new Picture();
		$this->view->current = $album_id;
		$offset = $page * $this->_numPerPage;
		$picture = $picture_model->fetchAll('disk_id='.$this->authNamespace->disk_info['id'].' and album_id='.$album_id)->toArray();
		if(count($picture) > 0)
		{
			$picture  = $image_library->disk_2_url($picture);
		}
		$paginator = Zend_Paginator::factory($picture);
		$paginator->setCurrentPageNumber($page)
					->setItemCountPerPage($this->_numPerPage)
					->setPageRange($this->_pageRange);
		$this->view->picture = $paginator;
		$album_model = new Album();
		$album = $album_model->fetchAll('user_id='.$this->authNamespace->user_info->id)->toArray();
		if(count($album) > 0)
		{
			$this->view->album = $image_library->img_of_gallery($album);
		}else {
			$this->view->album = ''; 
		}
		$this->view->user_volume = $this->authNamespace->disk_info['user_volume']/(1024*1024);
		$this->view->used_volume = $this->authNamespace->disk_info['used_volume']/(1024*1024);
		$this->view->progress = ($this->view->used_volume/$this->view->user_volume)*100;
		$this->view->email = $this->authNamespace->user_info->email;
	}
	
	public function uploadAction()
	{
		$gallery = $this->_request->getPost('gallery');
		$picture_model = new Picture();
		$image_library = new Custom_Image();
		$count = count($_FILES['files']['name']);
		$disk_model = new Disk();
		$vdisk_library = new Custom_Vdisk();
		$vdisk_library->token = $this->authNamespace->token;
		$data = array();
		$j = 0;
		for($i = 0; $i < $count; $i++)
		{
			if($_FILES['files']['size'][$i] < $this->authNamespace->disk_info['user_volume'] - $this->authNamespace->disk_info['used_volume'])
			{
				if($image_library->check_type($_FILES['files']['name'][$i]) && $image_library->check_size($_FILES['files']['size'][$i],$_FILES['files']['name'][$i]))
				{
					$file = $vdisk_library->upload_file($_FILES['files']['tmp_name'][$i],0);
					if($file['err_code'] == '0')
					{
						preg_match('|\.(\w+)$|', $_FILES['files']['name'][$i], $ext);
						$ext = strtolower($ext[1]);
						$file_name = time().rand(0,100000).'.'.$ext;
						$result = $vdisk_library->rename_file($file['data']['fid'], $file_name);
						if($result['err_code'] == '0')
						{
    						$data[$j] = array(
    							'disk_id'		=>	$this->authNamespace->disk_info['id'],
    							'fid'			=>	$file['data']['fid'],
    							'upload_time'	=>	time(),
    							'desc_name'		=>	$file_name,
    							'album_id'		=>	$gallery,
    						);
    						$j++;
    						echo "<center><strong>".$_FILES['files']['name'][$i]."&nbsp;&nbsp;上传成功</strong></center>";
						}else{
							$vdisk_library->delete_file($file['data']['fid']);
							echo "<center><strong>".$_FILES['files']['name'][$i]."$nbsp;$nbsp;重命名失败，请重新上传或联系管理员！</strong></center>";
						}
					}else {
						echo "<center><strong>".$_FILES['files']['name'][$i]."$nbsp;$nbsp;上传失败，请重新上传或联系管理员！</strong></center>";
					}
				}else {
					echo "<center><strong>".$image_library->message."！</strong></center>";
				}	
			}else {
				echo "<center><strong>".$_FILES['files']['name'][$i]."&nbsp;&nbsp;上传失败，您空间容量不足！</strong></center>";
				return FALSE;
			}
		}
		foreach ($data as $row)
		{
			$picture_model->insert($row);
		}
    	$vdisk_library->truncate_recycle();
    	$volume = $vdisk_library->get_quota();
    	if($volume['err_code'] == '0')
		{
			$data = array(
				'used_volume'		=>	$volume['data']['used']
			);
			$this->authNamespace->disk_info['used_volume'] = $volume['data']['used'];
			$disk_model->update($data,'id='.$this->authNamespace->disk_info['id']);
		}
		exit();
	}
	
	

	
	
	
	public function addgalleryAction()
	{
		$filter = new Zend_Filter_StripTags();
		$album_model = new Album();
		$name = $filter->filter($this->_request->getPost('name'));
		if(!empty($name))
		{
			$data = array(
				'user_id'		=>	$this->authNamespace->user_info->id,
				'album_name'	=>	$name,
				'album_desc'	=>	$name,
				'parent_id'		=>	'0',
				'create_time'	=>	time()
			);
			$where = $this->db->quoteInto('user_id = ?', $this->authNamespace->user_info->id).$this->db->quoteInto('and album_name = ?', $name);
			$row = $album_model->fetchRow($where);
			if(!count($row))
			{
				if($album_model->insert($data))
				{
					echo '<div class="alert alert-block alert-success"><center><strong>保存成功！</strong></center></div>';
				}
			}else {
				echo '<div class="alert alert-block alert-error"><center><strong>已有同名相册，请重新输入！</strong></center></div>';
			}
		}else {
			echo '<div class="alert alert-block alert-error"><center><strong>请输入相册名称！</strong></center></div>';
		}
		
		exit();
	}
	
	public function delgalleryAction()
	{
		$image_library = new Custom_Image();
		$disk_model = new Disk();
		$album_model = new Album();
		$picture_model = new Picture();
		$vdisk_library = new Custom_Vdisk();
		$vdisk_library->token = $this->authNamespace->token;
		$id = $this->_request->getPost('delete_id');
		if(count($id) > 0)
		{
			try {
				$this->db->beginTransaction();
				foreach ($id as $row)
				{
					$where = 'album_id='.$row;
					$picture = $picture_model->fetchAll('album_id='.$row)->toArray();
					if(count($picture) > 0)
					{
						foreach ($picture as $row_1)
						{
							$vdisk_library->delete_file($row_1['fid']);
							$picture_model->delete('id='.$row_1['id']);
						}
					}
					$album_model->delete('id='.$row);
				}
				$this->db->commit();
    		} catch (Exception $e) {
    			$this->db->rollBack();
    			echo "<script>alert('".$e->getMessage()."');</script>";	
			}
			$vdisk_library->truncate_recycle();
			$volume = $vdisk_library->get_quota();
			if($volume['err_code'] == '0')
			{
				$data = array(
					'used_volume'		=>	$volume['data']['used'],
					'account_volume'	=>	$volume['data']['total']
				);
			$disk_model->update($data,'id='.$this->authNamespace->disk_info['id']);
			echo '<div class="alert alert-block alert-success"><center><strong>删除成功！</strong></center></div>';	
		}
	}else{
			echo '<div class="alert alert-block alert-error"><center><strong>请选择要删除的相册！</strong></center></div>';
		}
		exit();
	}
	
	public function getpictureinfoAction()
	{
		$id = $this->_getParam('picture_id');
	//	$id = 1;
		$url_library = new Custom_Shorturl();
		$get_url = $url_library->transform('long_2_short', BASE_URL.'/index/imgurl/id/'.$id);
		if($get_url->status_code == '200')
		{
                  //	$short_url = BASE_URL.'/index/imgurl/id/'.$id;
                  	$short_url = 'http://'.$get_url->url;
		}else {
			$short_url = '短地址获取失败！';
		}
		echo  Zend_Json::encode($short_url);
		exit();
	}
	
	public function deletepictureAction()
	{
		$image_library = new Custom_Image();
		$fid = $this->_getParam('fid');
		if($image_library->delete_picture($fid))
		{
			echo '1';
		}else {
			echo '';
		}
		exit();
	}
public function pagelistAction ()
{
	$this->render('pagelist');
}
	
}
