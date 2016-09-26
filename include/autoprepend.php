<?php 
error_reporting(0);
define('TIME_START', microtime(true));
define('IP_GABOWEB','80.119.202.145');

function translate($str){
	return '__'.$str.'__';
}

$conf = $obj = array();
foreach(array('lang','usr','env','country','front','prod') as $k=>$v):
 $conf[$v]=isset($_SERVER[$v]) ? $_SERVER[$v] : '';
endforeach;
$conf['cache']=false;

#{{{ debug option
if(isset($_GET['debug']) && $conf['env']!='prod'){
 $conf['cache']=false; 
  apc_clear_cache ();
}
else{
 error_reporting(0); 
}
#}}}
if(!defined('USR_DIR')) {
    if(isset($_SERVER['front']) && $_SERVER['front']==1){ 
        $conf['usrdir']=realpath($_SERVER['DOCUMENT_ROOT'].'../');
        $conf['root']=realpath($_SERVER['DOCUMENT_ROOT'].'../../../');
    }
    else{
        $conf['usrdir']=realpath($_SERVER['DOCUMENT_ROOT'].'../../../')."/usr/".$conf['usr'];
        $conf['root']=realpath($_SERVER['DOCUMENT_ROOT'].'../../../');
    }
    define('USR_DIR',$conf['usrdir']);
    define('ROOT',$conf['root']);
}
require(ROOT .'/include/PHPMailer_v5.1/class.phpmailer.php');
if(file_exists(USR_DIR.'/etc/config.php')) {
    include(USR_DIR.'/etc/config.php');
}
# cache 
if($conf['cache']===true):
	if(isset($_SERVER['front']) && $_SERVER['front']==1){
		include('Cache.class.php');
		if(!defined('DB_CACHE')) define('DB_CACHE',true);
		$cache = new Cache($conf['usrdir'].'/var/cache');
		$cache->timeToLive = '3600';
		if(isset($_GET['cache_purge'])):
		    $cache->del();
		endif; 
		if( (empty($_POST))):
		  if($cache->isCached()):
		      $rs = $cache->get();
		  endif;
		endif;
  	}
endif;

if(!isset($_SERVER['SERVER_NAME'])) $_SERVER['SERVER_NAME']='';
define('HASH_KEY',md5($_SERVER['SERVER_NAME'].$conf['usr']));
function  securitize($id,$action=''){
    return md5(AJAX_PASSPHRASE.$id);
}



# end cache
require(ROOT.'/include/db.php');
require(ROOT.'/include/functions.php');
require(ROOT.'/include/genclass.class.php');
if(is_file(USR_DIR . '/classes/myForm.class.php')) {
    require(USR_DIR . '/classes/myForm.class.php');
} else {
    require(ROOT.'/include/myForm.class.php');
}
define('LIB_PATH',ROOT.'/lib/');
define('VAR_PATH',ROOT.'/usr/'.$conf['usr'].'/var/');
//include(ROOT.'/include/Cookies.class.php');
$gen = new GenClass($obj['db']);


# autocreate class from database schema, write module in file and database
# dev only

if(isset($_GET['build'])){
	$gen->collector=$gen->buildClass();
}

$gen->loadClassFromDb();

if(isset($_GET['build']))
	foreach (array_keys($gen->collector) as $module_k=>$module_name){
		$gen->collector['modules']->set(crc32($module_name),array('lib'=>$module_name));
		$gen->collector[$module_name]->collector=$gen->collector;
	}

foreach (array_keys($gen->collector) as $module_k=>$module_name){
	$gen->collector[$module_name]->collector=&$gen->collector;
}

if(isset($_SERVER['front']) && $_SERVER['front']==1) include('front.php');
elseif($_SERVER['back'] == 1) include('back.php');

function convert($size)
{
    $unit=array('b','kb','mb','gb','tb','pb');
    return @round($size/pow(1024,($i=floor(log($size,1024)))),2).' '.$unit[$i];
}

function t($string,$autoTranslate=false){
 global $gen;
 return nl2br($gen->collector['translations']->translate($string,$autoTranslate));
}

