<?php
/*
*  @author  $花生
*  @vesion  $id:Acl.php 0010 2012/6/1
*/

class IndexController extends Zend_Controller_Action
{

	
    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction()
    {
        // action body
    }

public function imgurlAction()
    {
    	$db = Zend_Registry::get('dbAdapter');
   		$id = $this->_getParam('id');
   		$select = $db->select();
		$select->where('picture.id = ?', $id)
    			->from('picture', '*')
    			->join('disk', 'picture.disk_id = disk.id', '*');
		$sql = $select->__toString();
		$res = $db->fetchAll($sql);
		$result = $res[0];
		$vdisk_library = new Custom_Vdisk($result['appkey'], $result['appsecret']);
		$vdisk_library->get_token($result['account_name'], $result['account_password']);
		$picture = $vdisk_library->get_file_info($result['fid']);
		$this->_redirect($picture['data']['s3_url']);
    	exit();
    }
}

