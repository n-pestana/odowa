<?php session_start() ?>
<?php $restrictions = array();?>
<?php if($_SERVER['PHP_SELF'] != '/login.php'): 
if(isset($_SESSION['admins']) && isset($_SESSION['admins']['id_admins'])){
  $_SESSION['admins']=$gen->collector['admins']->get($_SESSION['admins']['id_admins']);
  if(isset($_SESSION['admins']['id_locales']) && $_SESSION['admins']['id_locales']!=0 && $_SESSION['admins']['id_sites'] != 0){
    define('ID_LOC',$_SESSION['admins']['id_locales']);
    define('ID_SITE',$_SESSION['admins']['id_sites']);
    $restrictions= array(
      'locales' => $gen->collector['sites']->getAvailabledIdLocalesForSite(ID_SITE, $only_enabled = false)
      ,'pref_loc' => $_SESSION['admins']['id_locales']
      ,'id_sites' => $_SESSION['admins']['id_sites']
    );
  } 
}
if(isset($_SESSION['current_site'])) {
    define('CURRENT_SITE', $_SESSION['current_site']);
    $site = $gen->collector['sites']->getOne(CURRENT_SITE);
    define('CODE_SITE', $site['internal_code']);
}
 if(isset($_POST['change_id_site']) && $_POST['change_id_site'] == ''){
    unset($_SESSION['change_id_site']);
    unset($_POST['change_id_site']);
  }
if(isset($_POST['change_id_site']) ||( isset($_SESSION['change_id_site']) && $_SESSION['change_id_site']!='')){
 
  if(isset($_POST['change_id_site'])) {
   $_SESSION['change_id_site']=$_POST['change_id_site'];
  }
 define('ID_SITE', $_SESSION['change_id_site']);
   $restrictions= array(
      'locales' => $gen->collector['sites']->getAvailabledIdLocalesForSite(ID_SITE, $only_enabled = false)
      ,'pref_loc' => $_SESSION['admins']['id_locales']
      ,'id_sites' => $_SESSION['admins']['id_sites']
    );

    define('ID_LOC',$_SESSION['admins']['id_locales']);
}

?>
<?php $_SESSION['is_logged']=true?>
<?php 
endif ;
if(is_file(USR_DIR . '/classes/myForm.class.php')) {
    include_once(USR_DIR . '/classes/myForm.class.php');
} else {
    include_once($conf['root'].'/include/myForm.class.php');
}
include_once(ROOT.'/lib/securimage/securimage.php');
$form = new myFormV1();
$form->id_locales_only=(isset($_SESSION['admins']['id_locales'])) ? $_SESSION['admins']['id_locales'] : false;
$form->conf_file = defined('CODE_SITE') && is_file(USR_DIR.'/etc/' . CODE_SITE . '.form_fields.php') ? USR_DIR.'/etc/' . CODE_SITE . '.form_fields.php' : USR_DIR.'/etc/form_fields.php';
$form->form_options['class']='block-content form';
$form->restrictions = $restrictions; 
$form->collector= $gen->collector;

$gen->versionning=true;
if(isset($self_callback)) $form->self_callback=$self_callback;
if(isset($_SESSION['admins']) && isset($_SESSION['admins']['id_admins'])) $gen->collector['admins']->id=$_SESSION['admins']['id_admins'];

define('MUST_CHOOSE_SITE','<h2>You MUST choose a website for editing free content by selecting the drop down menu on the top right corner</h2><br /><br /><img src="/images/B4xxrW4.png" />');
define('ID_LOC_MASTER',1);
