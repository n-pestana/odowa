<?php
$configfile = USR_DIR.'/etc/db.xml';
if(!file_exists($configfile)){ 
    die('<!DOCTYPE HTML PUBLIC "-//IETF//DTD HTML 2.0//EN"> <html><head> <title>Configuration Error</title></head><body> <h1>Database Error</h1> <p>No database defined, please check the db config file</p> <p>For more informations contact <a href="mailto:contact@gaboweb.com">contact@gaboweb.com</a></p> </body></html>');
}

if ( ( $obj['Config'] = simplexml_load_file( $configfile ) ) === FALSE ) {
    throw new Exception( sprintf(_("Unable to open config file.")));
}
# }}} 
# chargement du fichier de conf dsn {{{
require_once(ROOT.'/lib/adodb5/adodb.inc.php');
require_once(ROOT.'/lib/adodb5/adodb-pager.inc.php');
$obj['db'] = &ADONewConnection($obj['Config']->db->phptype);
if(isset($obj['Config']->db->charset)) {
    $obj['db']->charSet = (string) $obj['Config']->db->charset;
}
$obj['db']->PConnect(
	      $obj['Config']->db->hostspec
	    , $obj['Config']->db->username
	    , $obj['Config']->db->password
	    , $obj['Config']->db->database
);
if(isset($obj['Config']->db->charset)) {
    $obj['db']->Query("SET character_set_results = '" . $obj['db']->charSet . "', character_set_client = '" . $obj['db']->charSet . "', character_set_connection = '" . $obj['db']->charSet . "', character_set_database = '" . $obj['db']->charSet . "', character_set_server = '" . $obj['db']->charSet . "'");
}

$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;
$ADODB_CACHE_DIR  = USR_DIR.'/var/cache/db/';
# }}}
?>
