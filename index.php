<?php
// Define BaseUrl
define('BASE_URL', 'http://'.$_SERVER['HTTP_HOST']);

// Define path to application directory
defined('APPLICATION_PATH')
    || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/./application'));

// Define application environment
defined('APPLICATION_ENV')
    || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production'));

// Ensure library/ is on include_path
set_include_path(implode(PATH_SEPARATOR, array(
    realpath(APPLICATION_PATH . '/../library'),
    get_include_path(),
)));

require_once 'Zend/Loader/Autoloader.php';
Zend_Loader_Autoloader::getInstance()->setFallbackAutoloader(true);
Zend_Session::start();
$view = new Zend_View();
Zend_Registry::set('view',$view);
//config  database  connect
$config=new Zend_Config_Ini('./application/configs/application.ini',null, true);
Zend_Registry::set('config',$config);
$dbAdapter=Zend_Db::factory($config->general->db->adapter,$config->general->db->config->toArray());
$dbAdapter->query('SET NAMES UTF8');
Zend_Db_Table::setDefaultAdapter($dbAdapter);
Zend_Registry::set('dbAdapter',$dbAdapter);

$acl = new Custom_Acl();
$auth=Zend_Auth::getInstance();
//assuming $fc is the front controller
$frontController = Zend_Controller_Front::getInstance();
$frontController->setControllerDirectory('./application/controllers')
				->registerPlugin(new Custom_Auth($auth,$acl));
$frontController->throwExceptions(true); 
$frontController->dispatch();
