<?php
if($_SERVER['SCRIPT_URL']=='/google-site-verification.php'){
    echo(file_get_contents(GOOGLE_VERIFICATION));
    die();
}
mb_internal_encoding("UTF-8");
session_start();
require('Displayer.class.php');
require('Cookies.class.php');
$gen->versionning=false;
if($gen->collector['ban']->IsUserBan($_SERVER['REMOTE_ADDR'])) {
    error_log('BLOCK:'.$_SERVER['REMOTE_ADDR']);
	sleep(30);
	die('you\'ve been blocked due to abnormal use');
}
else{
	if( $gen->collector['ban']->is_hacking() ) {
        if($_SERVER['REMOTE_ADDR'] != IP_GABOWEB){
    		$gen->collector['ban']->banIP($_SERVER['REMOTE_ADDR']);
            error_log('BAN :'.$_SERVER['REMOTE_ADDR']);
    		sleep(30);
            die('you\'ve been blocked due to abnormal use');
        }
        else{
            echo "Hack attempt but Gaboweb detected"; 
        }
	}
}

//test
# cookie conf
$display = new Displayer();
if(isset($_SERVER['id_site'])){
	$site =$gen->collector['sites']->get($_SERVER['id_site']);
}

if(($site['is_published'] == 0) && ($_SERVER['prod'] == 1) && !isset($_SERVER['private'])){
 echo $display->fetch($conf['usrdir'].'/www/soon.php');
 die();
}

$display->vars['cookies']=new Cookies(COOKIE_NAME);
$display->cookies=$display->vars['cookies'];
$cookie =  $display->vars['cookies']->LoadConfiguration();

if(isset($_SERVER['private']) &&  $_SERVER['private']==1){
    $_SERVER['prod']=0;
}

$languages  = $gen->collector['sites']->getAvailabledLanguageForCountry($_SERVER['id_site'],$_SERVER['prod']);

if(empty($languages) && empty($_SERVER['lang'])){
  echo $display->fetch($conf['usrdir'].'/www/soon.php');
  die();
}
elseif(empty($languages)){
 $cookie['current_lang_'.$_SERVER['id_site']] = $_SERVER['lang'];
}
else{
  $available_language= array();
  $preg_language = array();
  foreach($languages as $k=>$v){
    $available_language[] = current(explode('_',$v['locale'])); 
    $preg_language[] = '('.strtolower(current(explode('_',$v['locale']))).')';
  }
}


if(isset($_GET['logout']) && $gen->collector['clients']->hasIdentity()) {
       # droping previous basket non-client
       if(isset($cookie['id_visitors']) && isset($cookie['id_sites'])){
        

       }
       $gen->collector['clients']->clearSession();
}

#################### GESTION DES LOCALES LANG_COUNTRY #########################
if(isset($_GET['lang'])){
 if(in_array($_GET['lang'], $available_language)){
   $cookie['current_lang_'.$_SERVER['id_site']] = $_GET['lang'];
  }else{
    $cookie['current_lang_'.$_SERVER['id_site']] = $available_language[0];
  }
  header('Location: /'.$cookie['current_lang_'.$_SERVER['id_site']]).'/';
  die();
}
elseif(isset($preg_language) && preg_match('@'.implode('|',$preg_language).'@isu',$_SERVER['PHP_SELF'],$preg)){
  #error_log( 'preg'.implode('|',$preg_language));
  $_SERVER['PHP_SELF'] = str_replace('//','/',str_replace('/'.$preg[0].'/','/',$_SERVER['PHP_SELF']));
  $_SERVER['SCRIPT_NAME'] = str_replace('//','/',str_replace('/'.$preg[0].'/','/',$_SERVER['SCRIPT_NAME']));
  $cookie['current_lang_'.$_SERVER['id_site']] = strtolower(current(explode('/',$preg[0])));

}
# detection par geoloc
/*elseif(isset($_SERVER['lang']) && isset($_SERVER['country'])){
   $cookie['current_lang'] = strtolower($_SERVER['lang']);
}*/

elseif(isset($cookie['current_lang_'.$_SERVER['id_site']]) && !empty($cookie['current_lang_'.$_SERVER['id_site']])){
  #error_log('Already set and not changed');
  //if($_SERVER['PHP_SELF'] == '/index.php') header('Location: /'.$cookie['current_lang_'.$_SERVER['id_site']].'/');
}
else{
  # si pas cookie ni session, on detect la langue
  //if(!isset($cookie['current_lang']) || empty($cookie['current_lang'])) {
    #error_log('DETECTING');
    # XXX add ajax detection to not dot that
    $detected = DetectLocale();
    foreach( $detected as $locale){
      $lang=current(explode('_',$locale));
      if(in_array($lang, $available_language)){
        $cookie['current_lang_'.$_SERVER['id_site']] = $lang;
        if($_SERVER['PHP_SELF'] == '/index.php') {
            header('Location: /'.$cookie['current_lang_'.$_SERVER['id_site']].'/');
            die();
        }
        break;
      }
    }
  } 

if(empty($cookie['current_lang_'.$_SERVER['id_site']])){
  $cookie['current_lang_'.$_SERVER['id_site']]= $available_language[0]; //en
}


###les locales sont maintenant definis
$display->vars['cookies']->setLocale($cookie['current_lang_'.$_SERVER['id_site']]);
# par default : la premiere langue 
$master_locale=$gen->collector['locales']->getOne(array('is_master'=>1,'language'=>$cookie['current_lang_'.$_SERVER['id_site']]));
$locale=$gen->collector['sites']->getAvailabledLocalesBySiteAndLang($_SERVER['id_site'],$cookie['current_lang_'.$_SERVER['id_site']],$_SERVER['prod']);
if(!isset($locale['id_locales']) ||empty($locale['id_locales'])){
  echo $display->fetch($conf['usrdir'].'/www/soon.php');
  die();
}

define('ID_COUNTRY',$site['id_country']);
define('ID_SITE',$_SERVER['id_site']);
define('ID_LOC',$locale['id_locales']);
if(empty($master_locale['id_locales'])) $master_locale['id_locales']='0';
define('ID_LOC_MASTER',$master_locale['id_locales']);

$gen->collector['i18n']->id_locales_master =  ID_LOC_MASTER;
$gen->collector['i18n']->id_locales = ID_LOC;
$loc = $gen->collector['locales']->get(ID_LOC);
define('LOC',$loc['locale']);
//setlocale(LC_MONETARY, LOC.'.UTF-8');


$display->conf['sites_options']=$gen->collector['sites_options']->getOneI18n(array('id_site'=>ID_SITE));
# XXX passer par les sites options
define('DATE_FORMAT','jj/mm/aaaa');

/*if( ($display->conf['sites_options']['shop_mod']==0)  &&  ( stristr($_SERVER['PHP_SELF'],'/shop/') || stristr($_SERVER['PHP_SELF'],'/clients/') )) {
//    header("Location: /");
}*/


//if(!defined('TVA')) define('TVA', $display->conf['sites_options']['tva']);
# setting default 
//if(empty($display->conf['sites_options'])){
  

//}
if(
  isset($_SERVER['HTTP_USER_AGENT']) && 
  !stristr($_SERVER['HTTP_USER_AGENT'],'bot') && 
  !stristr($_SERVER['HTTP_USER_AGENT'],'crawl') && 
  !stristr($_SERVER['HTTP_USER_AGENT'],'spider') && 
  !stristr($_SERVER['HTTP_USER_AGENT'],'Slurp') && 
  !stristr($_SERVER['HTTP_USER_AGENT'],'Java/1.7.0_67')
){
        $visitors=array(
            'remote_addr' => $_SERVER['REMOTE_ADDR']
            ,'user_agent' => $_SERVER['HTTP_USER_AGENT']
            ,'continent_code' => (isset($_SERVER['GEOIP_CONTINENT_CODE'])) ? $_SERVER['GEOIP_CONTINENT_CODE'] : ''
            ,'continent_code' => (isset($_SERVER['GEOIP_CONTINENT_CODE'])) ? $_SERVER['GEOIP_CONTINENT_CODE'] :''
            ,'country_code' => (isset($_SERVER['GEOIP_COUNTRY_CODE'])) ? $_SERVER['GEOIP_COUNTRY_CODE'] :''
            ,'country_name' => (isset($_SERVER['GEOIP_COUNTRY_NAME'])) ? $_SERVER['GEOIP_COUNTRY_NAME'] : ''
            ,'latitude' => (isset($_SERVER['GEOIP_LATITUDE'])) ? $_SERVER['GEOIP_LATITUDE'] :''
            ,'longitude' => (isset($_SERVER['GEOIP_LONGITUDE'])) ? $_SERVER['GEOIP_LONGITUDE'] : ''
            ,'id_sites' => $_SERVER['id_site']
        );


        $temp=$display->vars['cookies']->getCookie();
        
        if(isset($temp) && isset($temp['id_visitors']) && !empty($temp['id_visitors'])){ 
            $visitors['id_visitors']=$temp['id_visitors'];
            $visitors['updated_at']='';
            $gen->collector['visitors']->set($visitors['id_visitors'],$visitors);

            if(isset($temp['filters'])) {
                $visitors['filters'] = $temp['filters'];
            }
            //error_log('RECOGNIZED VISITOR:'); 
        } else {
            $visitors['id_visitors']=$gen->collector['visitors']->set('',$visitors);
            //error_log('NEW VISITOR:'); 
        }

        define('ID_VISITOR',$visitors['id_visitors']);
        $cookie_set =$display->vars['cookies']->setCookie($visitors);
         #error_log('COOKIE SET:'.(int)$cookie_set);
}
else{
    define('ID_VISITOR',0);
    define('IS_BOT',true);

}
if(defined('IS_BOT') && IS_BOT===true  && $_SERVER['PHP_SELF']=='/index.php'){
    header("HTTP/1.1 301 Moved Permanently"); 
   header("Location: /".$available_language[0]."/"); 
   die();
}
if(!defined('IS_BOT')){
    define('IS_BOT', false);
}

 #error_log('VISITOR:'.$visitors['id_visitors']);
# si un visiteur alors on logue les requests
if(!empty($visitors['id_visitors'])){
  $display->vars['id_visitors'] = $visitors['id_visitors'];
  if(!strstr($_SERVER['REQUEST_URI'],'img.php'))
  	$gen->collector['visited_pages']->set('',array('id_visitors'=>$visitors['id_visitors'],'url'=>$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI']));
}

//$display->vars['cookies']

# ip2long ($_SERVER['REMOTE_ADDR']);
# ajouter info cookie geoip : https://github.com/fiorix/freegeoip
$lang = current(explode('_',$cookie['current_lang_'.$_SERVER['id_site']]));
$pays= end(explode('_',$cookie['current_lang_'.$_SERVER['id_site']]));
define('COUNTRY',$pays);
define('URL_LOC','/'.strtolower(str_replace('_','/',$cookie['current_lang_'.$_SERVER['id_site']])));

$currencyId = $display->conf['sites_options']['id_currencies'];
if(!empty($currencyId)) {
    $currency = $gen->collector['currencies']->getOne((int) $currencyId);
}
define('CURRENCY', isset($currency['lib']) ? $currency['lib'] : '€');
define('CURRENCY_CODE', isset($currency['payment_code']) ? $currency['payment_code'] : '978');
define('CURRENCY_POSITION', isset($currency['position']) ? $currency['position'] : 'BEFORE');
define('CURRENCY_DECIMALS_NB', isset($currency['decimals_nb']) ? $currency['decimals_nb'] : '2');
define('CURRENCY_DEC_SEP', isset($currency['currency_dec_sep']) ? $currency['currency_dec_sep'] : ',');
define('CURRENCY_THOUSANDS_SEP', isset($currency['currency_thousands_sep']) ? $currency['currency_thousands_sep'] : ',');

###############################################################################

# user
# if not logged but old session in cookie
/*if((!isset($_SESSION['user']) || empty($_SESSION['user'])) && isset($cookie['user'] )){
 $display->vars['user'] = $_SESSION['user'] = $cookie['user'];
}*/


#GESTION DES POST {{{
if(isset($_POST) && !empty($_POST) && isset($_POST['formname']) && !empty($_POST['formname'])){
   if(isset($_POST['first_name']) && !empty($_POST['first_name'])) die ('are you a bot ?');

  $post = $fields = array();
  $post['name']= filter_var($_POST['formname'], FILTER_SANITIZE_STRING);
  unset($_POST['formname']);
  foreach($_POST as $k=>$v){
    $fields[$k] = $v;//filter_var($v, FILTER_SANITIZE_STRING);
  }
  $post['ip'] = $_SERVER['REMOTE_ADDR'];
  $post['agent'] = $_SERVER['HTTP_USER_AGENT'];
  $post['localisation'] = $_SERVER['GEOIP_COUNTRY_NAME'];
  if(isset($_SERVER['GEOIP_CITY'])) $post['localisation'].=', '.$_SERVER['GEOIP_CITY'];

  $inserted_id = $gen->collector['posted_data']->setPost($post,$fields,ID_SITE);

  if($inserted_id>0 && $inserted_id!=false) define('FORM_RESULT_'.strtoupper($post['name']),true);
  else $this->error='error saving form';

  if(isset($_FILES) && !empty($_FILES)) {
    @mkdir($path.'/'.$post['name'].'/');
    $ext_accept = array('pdf','PDF','JPG','JPEG','jpeg','jpg','doc','DOC','DOCX','docx','od','odd','xls','xlsx','bmp','gif','GIF','png','PNG');
    foreach($_FILES as $field=>$file){
      if(empty($file['name']))continue;
      $path = realpath($_SERVER['DOCUMENT_ROOT'].'../').'/upload/';
      @mkdir($path.'/'.$post['name'].'/'.$field);
      $path = $path.'/'.$post['name'].'/'.$field.'/';
      if ( !in_array(end(explode('.',$file['name'])), $ext_accept) ) {
        define('HAS_ERROR_'.strtoupper($field), $field.': Upload format file not allowed');
      }
      else{
        if ( ! move_uploaded_file($file['tmp_name'], $path.$inserted_id.'.'.end(explode('.',$file['name'])))) {
          define('HAS_ERROR_'.strtoupper($field), $field.': Unable to upload');
        }
      }
    }
  }
}
# }}}fin gestion des posts



# get page 
$display->collector = $gen->collector;

define('HIDE_DISABLED_SITE',true);


$temp = preg_replace(array('|^/|','|/$|','|//|'),'',$_SERVER['SCRIPT_URL']);
$request_token= (!empty($temp)) ? explode('/',$temp) : '';

if($_SERVER['SCRIPT_NAME']=='/' || $_SERVER['SCRIPT_NAME'] == '') $_SERVER['SCRIPT_NAME']='/index.php';

if(!empty($request_token)){
  if(strlen($request_token[0])==2) {
    //print_r($gen->collector['locales']->get(array('','country'=>strtoupper((string)$request_token[0]))));
  }
 $country = $request_token[0];

 if(isset($request_token[1])) $lang = $request_token[1];
}

# 
//if(isset($gen->collector['pages'])){
  //}
//}
$page = '';
$pagei18= $gen->collector['i18n']->getOne(array('id_locales'=>ID_LOC,'is_published'=>1,'module'=>'pages','field_name'=>'url','lib'=>strtolower($_SERVER['REQUEST_URI'])));

if(!empty($pagei18)){
  $page = $gen->collector['pages']->getOneI18n(array('id_pages'=>$pagei18['id_element'],'id_site'=>ID_SITE));
  if($page)
      $elements = $gen->collector['pages']->getPagesElements($page['id_pages'],ID_LOC);
}
if(!empty($page) && $page['is_published']==1){
  $tpl = $gen->collector['templates']->getOne($page['id_templates']);
  $contents = $gen->collector['pages']->getPagesElements($page['id_pages'],ID_LOC);
  $display->content = $gen->collector['pages']->Compil($contents,$tpl,true);
  $display->content = $gen->collector['pages']->CallBackModule($display->content);

  define('IS_CMS',true);
  # nettoyer en base !
  $display->content = str_replace('<center><img src="http://19.preprod.front.asb.gaboweb.net/images/aem-menu.jpg"></center>', $display->fetch($conf['usrdir'].'/www/clients/partials/client-navbar.php'), $display->content);
  $display->content = str_replace('<center><img src="/images/aem-menu.jpg"></center>', $display->fetch($conf['usrdir'].'/www/clients/partials/client-navbar.php'), $display->content);

  $display->vars['meta']['title']=$page['meta_title'];
  $display->vars['meta']['desc']=$page['meta_desc'];
}
# si page existante
elseif(
	(file_exists($conf['usrdir'].'/www'.$_SERVER['SCRIPT_NAME']) &&  is_file($conf['usrdir'].'/www'.$_SERVER['SCRIPT_NAME']))
	||(is_link($conf['usrdir'].'/www'.$_SERVER['SCRIPT_NAME']) )
	){
  $display->content = $display->fetch($conf['usrdir'].'/www'.$_SERVER['SCRIPT_NAME']);
}
elseif(
	(file_exists($conf['usrdir'].'/www'.$_SERVER['SCRIPT_NAME'].'/index.php') &&  is_file($conf['usrdir'].'/www'.$_SERVER['SCRIPT_NAME'].'/index.php'))
	||(is_link($conf['usrdir'].'/www'.$_SERVER['SCRIPT_NAME'].'/index.php') )
	){
        $display->content = $display->fetch($conf['usrdir'].'/www'.$_SERVER['SCRIPT_NAME'].'/index.php');
}
else{
  # gestion des erreur 404, les admins peuvent créer des 301 
  $el = $gen->collector['produits']->getElementByUrl($_SERVER['PHP_SELF']);

//	error_log('CANON get : '.$el[0]['id']);
  if(!empty($el) && isset($el[0]['idp'])) {
      $elIsValid = $gen->collector['i18n']->getStatusByID_SITE(ID_SITE, $el[0]['idp']);
      if(current($elIsValid) == 0) {
        $el = null;
      }
  }
  if(!empty($el)){

      $cur_gamme=$el[0];
      $id_menus = (empty($cur_gamme['ml4'])) ?
               (empty($cur_gamme['ml3'])) ?
                (empty($cur_gamme['ml2'])) ?
                      (empty($cur_gamme['ml1'])) ? ''

                      : $cur_gamme['ml1']
                    : $cur_gamme['ml2']
                : $cur_gamme['ml3']
            : '';
      $menu = $gen->collector['menus_published']->get(array('id_menus_published'=>$id_menus));
      $menu = $menu[0];
      $display->setVar('menu',$menu);
      $display->setVar('gamme',$el);

      if(!empty($menu['controller']) && (!isset($el[0]['idp']) || (isset($el[0]['idp']) && empty($el[0]['idp'])))) {
          $display->setVar('controller', $menu['controller']);
          $display->content = $display->fetch($conf['usrdir'].'/www/'.$menu['controller']);
      }
      elseif(isset($el[0]['idp']) && !empty($el[0]['idp'])){
           $display->setVar('id_produits',$el[0]['idp']);
           $display->content = $display->fetch($conf['usrdir'].'/www/produitv2.php');
      }
      else{
           # suite mise en prod, la partie diff soins/produit n'a pas été faite, donc magic number a l'arrage
           /*if($cur_gamme['ml1']==199){
               $display->content = $display->fetch($conf['usrdir'].'/www/soins.php');
           }
           else*/
          $display->content = $display->fetch($conf['usrdir'].'/www/gammev2.php');
      }

  }
  else{

    $p404 = $gen->collector['pages404']->getOne(array('lib'=>$_SERVER['REQUEST_URI'],'id_site'=>ID_SITE));
    if(!empty($p404)){
          switch($p404['http_code']):

                  case '301':
                          header("HTTP/1.1 301 Moved Permanently");	
                          header("Location: ".$p404['destination']);
                  break;

                  case '302':
                          header("HTTP/1.1 302 Found"); 
                          header("Location: ".$p404['destination']);
                          die();
                  break;


                  case '404':
                          header("HTTP/1.0 404 Not Found"); 
                  break;

          endswitch;
    }
    else{
            echo '<!--'.$conf['usrdir'].'/www'.$_SERVER['SCRIPT_NAME'].' not found -->';
            header("HTTP/1.0 404 Not Found"); 
          # if is not bot
                  if( isset($_SERVER['HTTP_USER_AGENT']) && 
                    !stristr($_SERVER['HTTP_USER_AGENT'],'bot') && 
                    !stristr($_SERVER['HTTP_USER_AGENT'],'crawl') && 
                    !stristr($_SERVER['HTTP_USER_AGENT'],'spider') && 
                    !stristr($_SERVER['HTTP_USER_AGENT'],'Java/1.7.0_67')&&
                    !stristr($_SERVER['REQUEST_URI'],'/js/')
                  ){
    
                    $gen->collector['pages404']->set('',array('lib'=>$_SERVER['REQUEST_URI'],'id_site'=>ID_SITE));
          }
    }

  $display->content ='<div id="main"><h1><div class="container"><div class="boxes info-boxes"><div class="info-row"><h2>'.("Page not found !")."</h2></div></div></div></h1></div>";
  }
	
}


if($display->header!==false && empty($display->header)) $display->header = $display->fetch($conf['usrdir'].'/www/header.php');
if($display->footer!==false && empty($display->footer)) $display->footer = $display->fetch($conf['usrdir'].'/www/footer.php');


# callback function
if(file_exists($conf['usrdir'].'/etc/content_callback.php') &&  is_file($conf['usrdir'].'/etc/content_callback.php')){
	include($conf['usrdir'].'/etc/content_callback.php');
	$display->setCallBackFunction('content_callback');
}

$display->display();

# voir pour le cache

exit(1);
