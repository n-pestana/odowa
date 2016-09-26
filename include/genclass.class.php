<?php
/*
 *    GenClass
 *    @author Nicolas PESTANA <nicolas.pestana@gaboweb.ent>
 *    @version 1.10 : juin 2006
 *    @ v1 : juillet 2002
 *       1.12    - refonte, psql plus supporté , retrocompat cassée
 *       1.11    - ajout champ auto update: updated_at et created_at comme dans propel 
 *       1.10    - ajout gestion des jointures uniquement pour les gets. 
 *       1.9     - ajout methode getCount pour compter uniquement
 *       1.8     - ajout gestion > et <
 *       1.7     - ajout gestion de not in et in
 *       1.6     - modif methode set pour insertion par default avec param1= array 
 *       1.5     - ajout methode usestrict 
 *       1.4     - modif Get, on peux avoir un critere array 
 *       1.3     - ajout de GetId, par defaut les clé primaires sont des char32
 *       1.2     - ajout de rollbacktrans()
 *       1.1     - ajout de SetUsedField() 
 */

Class GenClass{
  /*
   * nom de la table courante
   */
  var $table_name ;
  var $obj;
  var $versionning = true;
  var $versionning_table = 'gen_versions';
  var $id_locales ;
  var $id_locales_master ;
  var $origin_array_fields = '';
  /*
   * nom du champ primary key
   */
  var $primary_key;

  /*
   * tableau associatif comportant le nom des champs utilisé et eventuellement
   * leurs valeurs
   */
  var $array_fields ;

  /*
   * Object de connexion à la base de données
   */
 var $db ;
 
 var $collector;

  /*
   * Valeur de la clé primaire
   */
  var $primary_key_value;

  /*
   * si true alors on utilise = pour les criteres sinon like %
   */
  var $usetransaction = false; 
  var $simulation = false;
  var $cache_time = 36000;
  var $debug=false;
  var $log_events = true;
  /**
   * Champ utilisé dans la méthode getKV pour construire les valeurs des selects automatisées
   *
   * @access protected
   * @var string
   */
  protected $_libField = 'lib';
  /**
   * Champs définis pour cibler les champs utilisés pour la recherche
   * Nécessaire pour éviter les soucis de collation (recherche LIKE UTF-8 sur un champ entier par exemple)
   *
   * @access protected
   * @var array
   */
  protected $_search_fields = array();
  /**
   * Erreur du formulaire en cours de validation
   *
   * @access protected
   * @var array
   */
  protected $_formErrors = array();
  /**
   * Définit si on recherche automatiquement avec un % ou non
   *
   * @access protected
   * @var boolean
   */
  protected $_searchLike = false;

  function getDb(){
    return $this->db;
  }
  
  /*
   * @param table_name     : nom de la table
   * @param primary_key  : nom du champ primary key
   * @param array_fields   : tableau associatif comportant le nom des champs 
   */
  function add($class){
//print_r($this->collector);

	if(trim($class)=='') return false;
    if(defined('DEBUG') && DEBUG==true)error_log("Auto Loading class:".strtolower($class));
    $class_name=strtolower($class).'.class.php' ;
    if(file_exists(USR_DIR.'/classes/auto/Gen_'.$class_name))
    	require_once(USR_DIR.'/classes/auto/Gen_'.$class_name);
    if(file_exists(USR_DIR.'/classes/'.$class_name)){
        error_reporting(E_ALL);
       require_once(USR_DIR.'/classes/'.$class_name);	
    }
    else{
	$gen = 'gen_'.$class;
	eval("class ".$class." extends gen_".$class."{};");
    }

    if(defined('DEBUG') && DEBUG==true)error_log("Class:".strtolower($class)." loaded");
    $n = new $class();
    $n->table_name  = 'gen_'.strtolower($class);
    $n->primary_key = $n->array_fields[0];
    $n->db=&$this->db;
//    $n->obj=&$this->obj;
//    $n->collector=&$this->collector;
    if(!is_array($n->array_fields)) $this->SetError("<br>Error array_fields must be an array", 1 );
    foreach($n->array_fields as $key=>$val){
      unset($n->array_fields[$key]);
      $n->array_fields[$val] = '';
    }
    return $n;

    /*if(empty($primary_key) || empty($array_fields)){
      $out = '$primary_key or  $array_fields are empty.' .' Please pick some fields as :<br>';
      $out .='<pre>$array_fields = array (';
      $fields_list = $this->GetFieldsList($table_name);
      foreach( $fields_list as $k){
        $out .=  '\''.$k.'\','."\n";
      }
      $out = substr($out,0, -2);
      $out .= ');</pre>';
      $this->SetError($out,1);
    }*/
    #foreach($array_fields as $key=>$val){
    #  unset($array_fields[$key]);
    #  $this->array_fields[$val] = '';
    #}
  }
  
  function __construct($db=''){
	if(!empty($db)) $this->db=$db;
     if(defined("DEBUG")) $this->debug = DEBUG;
  
  }

  function getCached($primary_key_value = '', $order = '', $limit = '',$offset = '', $tables = '', $join = ''){
//    $this->OutputDebug('get cached',$primary_key_value);
    $temp = $this->simulation ;
    $this->simulation = true;
    $sql = $this->get($primary_key_value, $order, $limit,$offset, $tables, $join);
    $this->simulation = $temp;



    if(defined('DB_CACHE') && DB_CACHE==true) $rs = $this->db->CacheExecute($this->cache_time, $sql);
    else $rs = $this->db->Execute($sql);

    if(!$rs)return $this->SetError($this->db->errorMsg());
    # si pas de resultats
    $results_count = $this->RecordCount($rs);
  //  $this->OutputDebug('results',$results_count.' result(s) found');
    if($results_count == 0){
      return false;
    }
    # si resultat(s)
    elseif($results_count == 1){
      if(!empty($primary_key_value)){
        # selection d'un element en base
    //    $this->OutputDebug('return','1 result(s) found');
        $rs = $this->GetRows($rs);
        return $rs[0];
      }
    }
//    $this->OutputDebug('return',$results_count.' result(s) found');
    $this->simulation = $temp;
    return $this->GetRows($rs);
  }
  /*
   * Get()
   * @param $primary_key_value    : nom de la clé primaire
   * @param $limit
   * @param $order
   */
  function getOne($primary_key_value = '', $order = '', $limit = '',$offset = '', $tables = '', $join = ''){
	$rs = $this->get($primary_key_value,  $order, $limit, $offset, $tables, $join);
	if(isset($rs[0])) return $rs[0];
	return $rs;
  }

function getLib($primary_key_value){
	$rs = $this->get($primary_key_value);
	return isset($rs['lib']) ? $rs['lib'] : '';
  }

  function getId($primary_key_value = '', $order = '', $limit = '',$offset = '', $tables = '', $join = ''){
        $temp = $this->getOne($primary_key_value, $order ,$limit,$offset, $tables, $join);
        if(isset($temp[$this->primary_key])) return $temp[$this->primary_key];
        return false;
  }
  function get($primary_key_value = '', $order = '', $limit = '',$offset = '', $tables = '', $join = ''){

       if($offset < 0) $offset = 0;
    $criteres = '';
    if(is_array($primary_key_value)){
      $criteres = $primary_key_value;
      $primary_key_value = '';
    }
    $this->OutputDebug('get',$primary_key_value);
    if(!empty($primary_key_value))
      $this->primary_key_value = $primary_key_value;
    else
      $this->primary_key_value = '';

    # construction de la requete de séléctio
    $sql = 'SELECT ';
    if(!is_array($this->array_fields)) 
      return $this->SetError("array_fields is not an array,".
          " cannot build the request for ".$this->TableName,1);
    foreach($this->array_fields as $field_name=>$k){
      $sql .= $field_name.', ';
    }
    $sql = substr($sql,0, -2);
    $sql .= ' FROM ';
# si requete sur plusieurs tables
    if(is_array($tables)){
        foreach($tables as $v){
            $sql .= $v.', ';
        }
        $sql = substr($sql,0, -2);
    } else {
        $sql .= $this->table_name;
    }
# si clé primaire, on prend pas les autres criteres, si on a pas de jointure. 
    if(!empty($primary_key_value)){
      $sql .= ' WHERE ( ('.$this->primary_key.' = \''.$primary_key_value.'\'))';
    }
    elseif( ! empty($criteres)){
      $sql .= ' WHERE 1=1 ';
    }
    if(!empty($tables) && !empty($join) && ((empty($primary_key_value))&&(empty($criteres)) )){
      $sql .= ' WHERE '.$join;
    }
    if(!empty($tables) && !empty($join) && ((!empty($primary_key_value))||(! empty($criteres)) )){
      $sql .= ' AND '.$join;
    }
    if( ! empty($criteres)){
      $sql .= ' AND ' . $this->getSQLCriteres($criteres);
    }
    if(!empty($order) && is_string($order))
      $sql .= ' order by '.$order;
    if(!empty($limit) && is_int($limit))
      $sql .= ' limit '.$limit;
    if(!empty($offset) && is_int($offset))
      $sql .= ' offset '.$offset;

    $this->OutputDebug('query',$sql);
    if($this->simulation === true) return $sql;

    $rs = $this->Query($sql);
    if(!$rs){
       if($_SERVER['REMOTE_ADDR'] == IP_GABOWEB){
            echo $sql;
            die();            

        }

        return $this->SetError($this->db->errorMsg());
    }
    # si pas de resultats
    $results_count = $this->RecordCount($rs);
    $this->OutputDebug('results',$results_count.' result(s) found');
    if($results_count == 0){
      return false;
    }
   # si resultat(s)
    elseif($results_count == 1){
      if(!empty($primary_key_value)){
       # selection d'un element en base
        $this->OutputDebug('return','1 result(s) found');
        $rs = $this->GetRows($rs);
        return $rs[0];
        foreach($this->array_fields as $field_name => $field_value){
          $this->array_fields[$field_name]=$rs->fields[$field_name] ;
        }
        return $this->array_fields;
      }
    }
    $this->OutputDebug('return',$results_count.' result(s) found');
    return $this->GetRows($rs);
  }

  /*
   * Del
   * @param key_value : key_value de l'élément (ou tableau de key_value)
   * @return : false en cas de probleme, sinon true
   */
  function Del($primary_key_value = ''){

    $this->OutputDebug('delete',@count($primary_key_value).' element(s)');
    $sql = 'DELETE FROM '.$this->table_name.' WHERE ';
    if(is_array($primary_key_value)){
      foreach ($primary_key_value as $value){
        $sql .= $this->primary_key.' = \''.((int) $value) .'\' OR ' ;
      }
      $sql = substr($sql,0, -3);
    }else{
      if(empty($primary_key_value) && !empty($this->primary_key_value))
        $primary_key_value = $this->primary_key_value;
      $sql .= $this->primary_key.' = \''.((int) $primary_key_value) .'\' ;' ;
    }

    $this->SaveVersion($primary_key_value,array(),true);
    $rs = $this->Query($sql);
    if(!$rs)return $this->SetError($this->db->errorMsg());
    else return true;
  }

  /*
   * Set
   * Si key_value et libelle sont renseignés, on fait un update et on renvoi true si update = ok
   * Si un seul des deux est renseigné, on insert en autoincrement
   * @param key_value : key_value de l'élément
   * @param libelle : libelle de l'élement
   * @return :    
   */
  function set($primary_key_value = '' , $array_fields = '', $criteres = array()){
    if($this->usetransaction === true) $this->db->BeginTrans();

     # un seul parametre, et un tableau => insertion 
    if(empty($array_fields) && is_array($primary_key_value)){
      $array_fields       = $primary_key_value  ;
      $primary_key_value  = '';
    }
    if(empty($primary_key_value) && !empty($this->primary_key_value) && empty($array_fields)){
      $primary_key_value = $this->primary_key_value;
      $array_fields = $this->array_fields;
    }
    if(!empty($primary_key_value)){
      $sql = 'select count(*) as count from '.$this->table_name.'  WHERE '.$this->primary_key.' = \''.$primary_key_value.'\'';
      if( ! empty($criteres) ) {
        $sql .= ' AND ';
        $sql .= $this->getSQLCriteres($criteres);
      }
      $sql .= ';';
      $rs = $this->Query($sql);
    }
    if((isset($rs)) && (isset($rs->fields['count'])) && ($rs->fields['count'] > 0) && (!empty($primary_key_value))){
      $this->OutputDebug('update','return '.$primary_key_value);    
      $sql = 'UPDATE '.$this->table_name.' SET ';
       if(isset($array_fields['url']) && empty($array_fields['url']) && isset($array_fields['lib'])){
	  $array_fields['url'] = strtolower(urlize($array_fields['lib']));
        }

      foreach($array_fields as $field_name=>$field_value){
        if($field_name == $this->primary_key) continue;

//	if(isset($this->array_fields['updated_at'])){
        if($field_name == 'updated_at'){
          $sql .= 'updated_at = NOW(), ';
        }
	# add auto urlize 
	        elseif(is_null($field_value)){
          $sql .= $field_name.' = NULL, ';
        }
        else{
              $sql .=$field_name.' = '.$this->db->qstr($field_value).', ';
        }

       }
        $sql = substr($sql,0, -2);
        $sql .= ' WHERE '.$this->primary_key.' = \''.$primary_key_value.'\' ';
        if( ! empty($criteres) ) {
          $sql .= ' AND ';
          $sql .= $this->getSQLCriteres($criteres);
        }
        $sql .= ';';
	$this->SaveVersion($primary_key_value,$array_fields);
        $rs = $this->Query($sql);

     //   $this->log_events(array('lib'=>$this->table_name,'type'>='UPDATE'));
        if($this->usetransaction === true) $this->db->CommitTrans();
        if(!$rs)
          return $this->SetError($this->db->errorMsg());
        else{  
          return $primary_key_value;
	  // return true;
        }
      }
      else{
        $this->OutputDebug('insert','');
        if(empty($primary_key_value)){
          $primary_key_value = null; 
          $dont_insert_pk = false;
        }
        else{
          $dont_insert_pk = true;
        }

        $sql = 'INSERT INTO '.$this->table_name.' ('.$this->primary_key.' ,';
        $sql_fields = '';
        $sql_values = '';
        if(!is_array($array_fields)){
            $this->SetError("<br>Error you try to insert with no array datas", 1 );
        }
        if(isset($this->array_fields['created_at']) && !array_key_exists('created_at', $array_fields)) {
            $sql_fields .= 'created_at, ';
            $sql_values .= 'NOW(), ';
        }
        if(isset($array_fields['url']) && empty($array_fields['url']) && isset($array_fields['lib'])){
            $array_fields['url'] = strtolower(urlize($array_fields['lib']));
        }

        foreach($array_fields as $field_name=>$field_value) {
            if(($field_name == $this->primary_key)) continue;
            $sql_fields .= $field_name.', ';
            if(is_null($field_value)){
                $sql_values .= 'NULL, ';
            } else {
                $sql_values .= $this->db->qstr($field_value).', ';
            }
        }
        $sql .= $sql_fields ;
        $sql = substr($sql,0, -2);
        $sql .= ') VALUES ( \''.$primary_key_value.'\',';
          $sql .= $sql_values ;
          $sql = substr($sql,0, -2);
          $sql .= ');';

	$this->SaveVersion($primary_key_value,$array_fields);
        $rs = $this->Query($sql);
        //$this->log_events(array('lib'=>$this->table_name,'type'>='INSERT'));
        if($rs === false){
          if($this->usetransaction === true)
            $this->db->RollbackTrans();
          return $this->SetError($this->db->errorMsg());
        }
        else{
          if(!is_int($primary_key_value)) $primary_key_value = (int)$this->GetLastInsertId();
          if($this->usetransaction === true) $this->db->CommitTrans();
          return $primary_key_value;
        }
       }
      }

      /*
       * string GetField(string)
       * renvoi la valeur du champ passé en parametre.
       * Cette fonction doit etre appellée apres un Get
       * @param field_name : nom du champ
       * @return string : valeur contenu dans le champ
       */
      function GetField($field_name){
        if(!in_array($field_name, $this->array_fields)){
          $this->OutputDebug('get field',$field_name.' = '.$this->array_fields[$field_name]);
          return $this->array_fields[$field_name];
        }else{
          return false;
        }
      }

      /*
       * array GetFieldsList(string)
       * @param table_name : nom de la table
       * @return array : liste des champs de la table
       */
      function GetFieldsList($table_name){
        if(empty($table_name))
          $table_name = $this->table_name;
        if(empty($table_name))
          return false;
        return $this->db->MetaColumnNames($table_name);
      }

      /*
       * boolean SetField(string, string)
       * @param field_name : nom du champ
       * @param field_value : valeur du champ
       * @return boolean 
       */
      function SetField($field_name, $field_value){
        if(!in_array($field_name, $this->array_fields)){
          $this->array_fields[$field_name] = $field_value;
          return true;
        }else{
          return false;
        }
      }

      /*
       * GetLastInsertId
       * @return : l'id du dernier key_value en base (dans le cas de key_value numerique)
       */
      function GetLastInsertId($where=''){
        $sql = 'SELECT max('.$this->primary_key.') as max FROM '.$this->table_name.' ' .$where;
        $rs = $this->Query($sql);
        return (int)$rs->fields['max'];
      }

      /**
       * SetError
       * @param string error_txt
       * @param int error_level
       * @return  false 
       */
      function SetError($error_txt, $error_level = 1){
          //        mail('technique@gaboweb.net','ASB PROD MYSQL ERROR',$error_txt);
          $env = isset($_SERVER['prod']) && $_SERVER['prod'] == 0 ? 'DEV' : 'PROD';
          //mail('technique@gaboweb.net','CMS GENERIC [' . $_SERVER['usr'] . '] ' . $env . ' MYSQL ERROR sur :'.$_SERVER['PHP_SELF'],"\n".$_SERVER['PHP_SELF']."\n".$_SERVER['REQUEST_URI']."\n".$error_txt);

          echo '<b>[Error]</b><br>'.$error_txt;

          if(isset($_GET['debug'])) print_r($this);
          switch($error_level){
              case   1:    exit;
              case   2:    return false;
              default  :    exit;
          }
      }


      function CachedQuery($sql_query){
        if(isset($_SERVER['front']) && $_SERVER['front']==1){
            $rs = $this->db->CacheExecute($this->cache_time, $sql_query);
            if(!$rs)return $this->SetError($this->db->errorMsg());
            return $rs;
        }
        else{
            return $this->Query($sql_query);
        }
      }



      /*
       * Query()
       * Execute une requete
       * @param $sql_query : requete à executer
       * @return : le resultat de la requete
       */
      function Query($sql_query){
        if(false){
            $rs = $this->db->CacheExecute($this->cache_time, $sql_query);
            if(!$rs)return $this->SetError($this->db->errorMsg());
            return $rs;
        }

/*        global $time_start;
        $time_start = microtime(true);
*/
        $rs = $this->db->Execute($sql_query);

/*        $time_end= microtime(true);
        $time = $time_end-$time_start;
        if($time > 0.02) file_put_contents('/tmp/sqllog', ("\nTIME:".$time. "--->".str_replace(array('\r','\n'),'',$sql_query)),  FILE_APPEND | LOCK_EX);
*/

        if(!$rs)
          $this->SetError("<br>Wrong Query : ".$sql_query."<br />".$this->db->ErrorMsg(), 1);
        else{
          return $rs;
        }
      }

      /*
       * OutputDebug()
       * Affiche des messages debug
       */
      function OutputDebug($lib, $msg,$indent = false){
        if($this->debug == false) return false;
        echo '<div align="left"><b> ['.strtoupper(str_pad($lib.']',20)).'</b>';
        echo str_pad($msg,40)."\n<br></div>";
      }
      function SetTableName($TableName) {
        $this->table_name = $TableName;
      }


      /**
       * SetUsedFields()
       * Modifie la liste des champs à retourner par la methode get
       */
      function SetUsedFields($fields = array()){
        $this->origin_array_fields = $this->array_fields;
        $this->array_fields = array_flip($fields);
      }


	# fonction de compatiblité avec PEAR DB {{{
	  function GetRows($rs){
	    return $rs->GetRows();
	  }
	  function RecordCount($rs){
	    return $rs->RecordCount();
	  }
	# }}}

  function DebugOn(){
    $this->debug  = true;
  }

  function DebugOff(){
    $this->debug  = false;
  
  }

  function getSQLCriteres($criteres = array()) {
    $sql = '';
    foreach($criteres as $k => $v){
      if(is_array($v)){
        $temp = implode('\',\'',$v);
        if($k[0] == '!'){
          $k = substr($k,1);
          $sql .= $k.' not in (\''.$temp.'\') AND ';
        }
        elseif(substr($k, 0, 3) == 'ANY') {
          $sql .= "'".$temp."' = ".$k. " AND ";
        }
        else{
          $sql .= $k.' in (\''.$temp.'\') AND ';
        }
      }
      elseif($v == 'FALSE' || $v == 'TRUE') {
        $sql .= $k .' = '.$v.' AND ';
      }
      elseif(substr($k, 0, 2) == '>=' || substr($k, 0, 2) == '<='){
        $ope = substr($k, 0, 2);
        $k = substr($k,2, strlen($k));
        if($v == 'NOW()'){
          $sql .= $k.$ope.$v.' AND ';
        } else {
            $sql .= $k.$ope.'\''.$v.'\' AND ';
        }
      }
      elseif($k[0] == '>'){
        $k = substr($k,1);
        if($v == 'NOW()'){
          $sql .= $k.' > '.$v.' AND ';
        } else {
            $sql .= $k.' > \''.$v.'\' AND ';
        }
      }
      elseif($k[0] == '<'){
        $k = substr($k,1);
        if($v == 'NOW()'){
          $sql .= $k.' < '.$v.' AND ';
        } else {
            $sql .= $k.' < \''.$v.'\' AND ';
        }
      }
      elseif(substr($k, 0, 2) == '!=') {
        $k = substr($k, 2, strlen($k));
        $sql .= $k.' != \''.$v.'\' AND ';
      } elseif(is_null($v)) {
        $sql .= $k . ' IS NULL AND ';
      }
      elseif(substr($k, 0, 5) == 'LIKE>') {
        $k = substr($k, 5, strlen($k));
        $sql .= $k . ' LIKE ' . $this->db->qstr($v).' AND ';
      }
      else{
          //$sql .= $k.' = \''.$v.'\' AND ';
          $sql .= $k.' = '.$this->db->qstr($v).' AND ';
      }
    }
    return substr($sql,0, -4);
  }

  function getCount($conditions=array()){
    $old_status = $this->simulation ;
    $this->simulation = true;
    $sql = $this->get($conditions);
    $sql = ereg_replace('(SELECT).*(FROM)', 'SELECT COUNT(\'*\') as count FROM',$sql);
    $rs  = $this->Query($sql);
    $out = $rs->fields['count'];
    $this->simulation = $old_status;
    return $out;
  }
  function getDistinct($field,$conditions = array()){
    $old_status = $this->simulation ;
    $this->simulation = true;
    $sql = $this->get($conditions);
    $sql = ereg_replace('(SELECT).*(FROM)', 'SELECT distinct('.$field.') FROM',$sql);
    $rs  = $GLOBALS['obj']['db']->query($sql);
    $this->simulation = $old_status;
    return $this->GetRows($rs);
  }
  function getSearch($fields, $value, $order = '', $limit = '',$offset = '', $tables = '', $join = '',$cond=array()){
    $sim = $this->simulation;
    $this->simulation = true; 
    $this->usestrict = is_int($value);
    if(!$this->usestrict && $this->_searchLike === true) {
        $value = '%' . $value . '%';
    }
    foreach($fields as $field){
      if($value == '') continue;
      if(strstr($field,' as ')) continue;
      $array[$field] = $value;
    }
    if(empty($array)){
      $sql = $this->get($cond, $order, $limit,$offset, $tables,$join);
      return $sql;
    }
    $sql =  $this->get($array, $order, $limit,$offset, $tables);

    $sql = str_replace(' AND ',' OR ',$sql);
    $sql =str_replace('order by',') order by',$sql);
    $sql =str_replace('WHERE','WHERE(',$sql);
    //$sql = str_replace('order by', "and site_id='".SITE_ID."' order by",$sql);
    foreach($cond as $k=>$v){
      $sql = str_replace('WHERE', 'WHERE '.$k.'=\''.$v.'\' AND',$sql);
    }
    $this->simulation = $sim; 
    $sql = str_replace('=','  like ',$sql);
    $sql = str_replace('*', '%',$sql);
    #XXX wtf ? 
    $sql = str_replace('WHERE(  OR','WHERE(',$sql);
    if(!empty($join)){
       $join .= ' and ';
      $sql = str_replace('WHERE(','WHERE '.$join.' (',$sql); 
    }
    return $sql;
  }
  function buildClass(){
	$tables = $this->db->MetaTables();
	$include=array();
	foreach ($tables as $table){
		if(!strstr($table,'gen_')) continue;
		$file = Ucfirst($table).'.class.php'; 
		$fields = $this->db->MetaColumnNames($table);
		$fields_list = array();
		foreach($fields as $field){
			$fields_list[]=$field;
		}
		//$out = '<?php class '.ucfirst(str_replace('gen_','',$table))." extends GenClass {\n";
		$out = '<?php class '.$table." extends GenClass {\n";
		$out .= "\t".'var $array_fields = array ('."\n";
		$out .= "\t\t'".implode("'\n\t\t, '",$fields_list)."'\n";
		$out .= "\t);\n";
		$out .= "}\n";
		$include[]=str_replace('gen_','',$table);
		file_put_contents(USR_DIR.'/classes/auto/'.$file,$out);
	  }
	  file_put_contents(USR_DIR.'/classes/auto/include.php','<?php $objects=array(\''.implode("','",$include).'\');');
	}

   function loadClassFromDb(){
	  require(USR_DIR.'/classes/auto/include.php');
	  foreach(array_values($objects) as $obj_name){
	  	$obj = $this->add($obj_name);	
	  	$this->collector[$obj_name]=$obj;
	  }

	$this->obj=$this->collector;
	return $this->collector;
   }
  
  function getCollector($class=''){
    return $this->collector[$class];
  }

   function SaveVersion($primary_key_value=0,$array_fields=array(),$is_del=false){
        return true;
        if(isset($_GET['build'])) return false;
        if($this->versionning==false) return false;
        $sql = "SELECT max(version_number) as max FROM ".$this->versionning_table ." where module='".$this->table_name."'";
        $rs = $this->Query($sql);
        $version=(int)$rs->fields['max']+1;
        $id_admins = (isset($_SESSION['admins']) && isset($_SESSION['admins']['id_admins']) && !empty($_SESSION['admins']['id_admins'])) ? $_SESSION['admins']['id_admins'] : 0;
        $sql ="insert into ".$this->versionning_table." (remote_addr,id_element,module,version_number,id_admins,is_deleted) values('".$_SERVER['REMOTE_ADDR']."',".(int)$primary_key_value.",'".$this->table_name."',".$version.",".$id_admins.",".(($is_del==false) ? 0 : 1 ).")";
        $rs = $this->Query($sql);
        $sql = 'SELECT max(id_versions) as max FROM '.$this->versionning_table." where id_element =".(int)$primary_key_value." and module='".$this->table_name."'";
        $rs = $this->Query($sql);
        $id= (int)$rs->fields['max'];
        # if update
        if($primary_key_value > 0){
          $before_change = $this->getOne($primary_key_value);
          $has_change = false;
        }
        else{
          $has_change = true;
        }
        if($is_del==true){
           $has_change= true;
           $array_fields = $before_change;
            $before_change = array();
          
        }
        foreach($array_fields as $field_name=>$field_value){
                $old_value = (isset($before_change[$field_name])) ? $before_change[$field_name] : '';
                if((!$old_value) && !($field_value))continue;
                if($old_value ==$field_value) continue;
                if($field_name=='updated_at') continue;
                $has_change = true;
        	if($field_name == $this->primary_key) continue;
		$sql ="insert into  gen_versions_elements  (id_versions,fieldname,fieldvalue,field_old_value) values(".$id.",'".$field_name."',".$this->db->qstr($field_value).",".$this->db->qstr($old_value).")";
        	$rs = $this->Query($sql);
	}
        if($has_change == false){
          $sql = 'delete FROM '.$this->versionning_table." where id_versions=".$id;
          $rs = $this->Query($sql);
        }
   }


   
   function reverseBoolean($id=0, $field_name){
     $current = $this->get($id);
     $current_value = $current[$field_name];
     $new_value = (int)!$current_value; 
     $this->set($id,array($field_name=>$new_value));
     return $new_value;

    // $sql = "update ".$this->table_name." set ".$field_name." = !".$field_name." where ".$this->primary_key." =".$id  ;
   }

   function getKV($conditions = array(), $order = '', $limit = '', $noMultiple = null) {
       $rs = $this->get($conditions, $order, $limit);
       if(empty($rs)) return array();
       //$encodings = mb_detect_order();
       //$encodings = array_merge($encodings, array('ISO-8859-1'));
       foreach($rs as $k => $realArray) {
           $libField = $this->_libField;
           //$enc = mb_detect_encoding($realArray[$libField], $encodings);
           $val = $realArray[$libField];
           /*if($enc != 'UTF-8') {
               $val = iconv($enc, 'UTF-8', $val);
           }*/
           $retArr[$realArray[$this->primary_key]] = $val;
       }
       return $retArr;
   }
   function getKandMore($fields=array(),$conditions = array(), $order = '', $limit = '') {
       $rs = $this->get($conditions, $order, $limit);
       if(empty($rs)) return array();
       foreach($rs as $k => $realArray) {
           $temp = '';
           foreach($fields as $kv=>$vv){
               $temp .= $realArray[$vv].' ';
           } 
           $retArr[$realArray[$this->primary_key]] = $temp;
       }
       return $retArr;
   }
   function sqlGet($sql){
     $rs=$this->query($sql);
     return $rs->GetRows();
   }

   function is_enable_for_locale(){
     //$sql = 'select count(\'*from '.$this->table_name.' , gen_i8n  
   }

   function translate($array, $element,$id_locale=ID_LOC){
		if(!isset($array[$element])) return false;
		$sql = "select lib,is_published from gen_i18n where module='".str_replace('gen_','',$this->table_name)."' and field_name='".$element."' and id_locales=".$id_locale."  and id_element=".$array[$this->primary_key];
		//$sql = "select lib from gen_i18n where module='".str_replace('gen_','',$this->table_name)."' and field_name='".$element."' and id_locales=".$id_locale." and is_published=1 and id_element=".$array[$this->primary_key];
		$rs = $this->query($sql);
    	$temp = $rs->GetRows();
		if(empty($temp)) return $array[$element];
		$temp=$temp[0];
		if($temp['is_published'] != 1) return false;
		if(empty($temp['lib'])) return $array[$element];
		else return nl2br(($temp['lib']));
   }

   function getOneI18n($mixed='',$order='',$search='',$id_loc=ID_LOC,$id_loc_master=ID_LOC_MASTER){
      $rs = $this->getI18n($mixed, $order,$search, $id_loc,$id_loc_master);
      return (isset($rs[0])) ? $rs[0] : false;
   }
   function getOneI18nLib($mixed='',$order=''){
      $temp = $this->array_fields;       
      $rs = $this->getI18n($mixed, $order);
      $this->array_fields = array('lib');
      $return = (isset($rs[0]['lib'])) ? $rs[0]['lib'] : false;
      $this->array_fields=$temp;
      return $return;
   }

	
   function getBackI18n($mixed='',$order='',$search='',$id_loc=ID_LOC){
	$this->id_locales = $id_loc;
        $join_cnt = 0;
        if(!defined('ID_LOC_MASTER')) define('ID_LOC_MASTER',1);

	$this->id_locales_master = ID_LOC_MASTER;
        $search_sql = '';
	if(is_array($mixed) && !empty($mixed)){
		$critere = 'and '.$this->getSQLCriteres($mixed);
	}
	elseif(is_int($mixed)){
		$critere = ' and( '.$this->table_name.".".$this->primary_key.' = '.$mixed.')';
	}
        else{	
		$critere = '';
	}

	$join = '';
	$case = array();

	foreach($this->array_fields as $field_name=>$field_value){
              # XXX pas la peine de i18n les id ?.
      	      if(!strstr($field_name,'id_'))continue;
              $case[] = $this->table_name.".".$field_name;
        }
	foreach($this->array_fields as $field_name=>$field_value){
		if(empty($field_name))continue;
		if(strstr($field_name,'id_'))continue;
//              echo "<br />".$field_name;
		$case[] = "CASE WHEN i18n_".$field_name.".lib !='' THEN i18n_".$field_name.".lib ELSE CASE WHEN i18n_master_".$field_name.".lib !='' THEN i18n_master_".$field_name.".lib ELSE ".$this->table_name.".".$field_name." END END AS ".$field_name;
		//$case[] = "CASE WHEN i18n_".$field_name.".lib !=  '' THEN i18n_".$field_name.".lib ELSE ".$this->table_name.".".$field_name." END AS ".$field_name;
		$join.="LEFT JOIN gen_i18n i18n_".$field_name."  ON ( 
				    i18n_".$field_name.".id_element = ".$this->table_name.".".$this->primary_key."
				AND i18n_".$field_name.".id_locales =".$this->id_locales."
				AND i18n_".$field_name.".module = '".str_replace('gen_','',$this->table_name)."'
				AND i18n_".$field_name.".field_name =  '".$field_name."' ) ";
                $join.="LEFT JOIN gen_i18n i18n_master_".$field_name."  ON ( 
				    i18n_master_".$field_name.".id_element = ".$this->table_name.".".$this->primary_key."
				AND i18n_master_".$field_name.".id_locales =".$this->id_locales_master."
				AND i18n_master_".$field_name.".module = '".str_replace('gen_','',$this->table_name)."'
				AND i18n_master_".$field_name.".field_name =  '".$field_name."' ) ";
                $join_cnt=$join_cnt+1;

	}
//echo "<br ><b>".($join_cnt*2+1)." Joins</b>";
        if(!empty($search)){
          $search_sql =  'and ( false ';
          foreach($this->array_fields as $field_name=>$field_value){
                  if(empty($field_name))continue;
                  if(count($this->_search_fields) > 0 && in_array($field_name, $this->_search_fields)) {
                      $search_sql .= ' OR '. 'i18n_'.$field_name.".lib" .' like \''.$search.'\''; 
                      $search_sql .= ' OR '. 'i18n_'.$field_name.".lib" .' like \'%'.$search.'\''; 
                      $search_sql .= ' OR '. 'i18n_'.$field_name.".lib" .' like \'%'.$search.'%\''; 
                      // Utile si %valeur% ?  Pour perf ?
                      $search_sql .= ' OR '. $this->table_name.".".$field_name .' like \''.$search.'\''; 
                      // Utile si %valeur% ?  Pour perf ?
                      $search_sql .= ' OR '. $this->table_name.".".$field_name .' like \'%'.$search.'\''; 
                      $search_sql .= ' OR '. $this->table_name.".".$field_name .' like \'%'.$search.'%\''; 
                  }
          } 
          $search_sql .= ' OR false )';
        }
	$sql = "SELECT  ".implode(',',$case). " FROM ".$this->table_name." ".$join." where 1  ".$critere. " ".$search_sql." group by ".$this->primary_key." ".$order; 
	//$sql = "SELECT  i18n_lib.is_published as is_country_published,".implode(',',$case). " FROM ".$this->table_name." ".$join." where 1  ".$critere. " ".$search_sql." ".$order; 
        if($this->simulation===true) return $sql;
        $rs=$this->query($sql);
        return $rs->GetRows();
   }
	
  function log_events ($array){
    if($array['lib']=='gen_backevents') return false;
    if(!$this->log_events) return false;
    global $gen;
    if(!isset($array['lib'])) $array['lib'] = 'no information';
    if(!isset($array['type'])) $array['type'] = 'MSG';
    //$this->collector[$obj_name]    
    if(isset($_SESSION['admins']) && isset($_SESSION['admins']['id_admins']) && !empty($_SESSION['admins']['id_admins'])){
      $gen->collector['backevents']->set('',array('type'=>'MSG','lib'=>$array['lib'],'id_admins'=>$_SESSION['admins']['id_admins']));
    }

  }
  function downloadCSVBySQL($sql){
	ob_get_clean();
	$filename = date('d-m-Y-H-i-S').'.csv';
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename='.$filename);
        $output = fopen('php://output', 'w');
        $rows = $this->GetRows($this->Query($sql));
	foreach($rows as $row){
		fputcsv($output, $row,";");
	}
        die();
	

  }

  function downloadCSV($curr_obj='',$fields='',$conditions='',$filename=''){
	ob_get_clean();
        if(empty($curr_obj)) $curr_obj = str_replace('gen_','',$this->table_name);
        if(empty($fields )) $fields = $this->array_fields;
	if(empty($filename)) $filename = $curr_obj.'.csv';
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename='.$filename);
        $output = fopen('php://output', 'w');
        fputcsv($output, array_values($fields),";");
        $sql = 'select '.implode(',',array_keys($fields)).' from gen_'.$curr_obj .' '.$conditions;
        $rows = $this->GetRows($this->Query($sql));
	foreach($rows as $row){
		fputcsv($output, $row,";");
	}
        die();
	

  }
  function loadCSV($curr_obj,$fields,$file){
	ob_get_clean();
	$csv = file_get_contents($file);
	$nbr_fields_await=count($fields);
   	$lines =explode("\n",$csv);	
	$sql = '';
	$out = array();
	$out[]= count($lines)." lines detected";
	$i=$j=0;
	$inserted=0;
	$error=0;
# oui je sais c'est assez etrange comme principe de sauvegarde
	$this->collector[$curr_obj]->db->Query('update gen_'.$curr_obj.' set id_site = -'.ID_SITE.' where id_site='.ID_SITE);
	
	foreach($lines as $line){
		if(strstr($line,'PROD')){
			 $out[]='Header detected:'. $line;
			continue;
		}
		$i++;
		$user_fields = explode(";",$line);
		$nbr_field = count($user_fields);
		if($nbr_field !=  $nbr_fields_await){
		 	$out[]='Warning line : '.$i.': bad field number ('.$nbr_field.' found, '.$nbr_fields_await.' awaited)'; 
			continue;
		} 
		$j++;
		$array=array('id_site'=>ID_SITE);
		$i2=0;
		foreach($fields as $a_field=>$lib){
			$array[$a_field]=$user_fields[$i2];
			$i2++;
		}
		
		if($this->collector[$curr_obj]->set('',$array)) {
			$inserted++;
		}else{ 	
			$error++;
		}
		
		
	}
	$out[]= $inserted.' inserted values';
	$out[]= $error.' error';
	foreach($out as $log){
		echo '<li>'.$log.'</li>';
	}
	die();

  }

   function RollBackUsedFields(){
        if(is_array($this->origin_array_fields)) {
            $this->array_fields=$this->origin_array_fields;
        }
   }


   public function getI18n($mixed='',$order='',$search='',$id_loc=ID_LOC, $id_loc_master=ID_LOC_MASTER)
   {
        $this->id_locales = $id_loc;
//        if(!defined('ID_LOC_MASTER')) define('ID_LOC_MASTER',0);

        $this->id_locales_master = $id_loc_master;

        $search_sql = '';
        //if(is_array($mixed) && !empty($mixed)) {
        if(is_array($mixed)) {
            if(!empty($mixed)) $critere = 'and '.$this->getSQLCriteres($mixed);
            else $critere='';
        }
        elseif(!empty($mixed)){
            $mixed = (int)$mixed;
            $critere = ' and( '.$this->table_name.".".$this->primary_key.' = '.$mixed.')';
        }
        else{
            $critere='';
        }

        $fields = $this->array_fields;
        if(count($fields) >= 30) {
            $fields = partition($this->array_fields, ceil(count($fields) / 30));
        } else {
            $fields = array($fields);
        }
        foreach($fields as $fieldset) {
            $join_cnt = 0;
            $case = array();
            $join = '';
            $results = array();
            foreach($fieldset as $field_name=>$field_value) {
                if(substr($field_name, 0, 3) != 'id_' && !strstr($field_name,'id_')) continue;
                $case[] = $this->table_name.".".$field_name;
            }
            /*if(!empty($search)) {
                $search_sql =  'and ( false ';
            }*/

            $has_lib=false;
            foreach($fieldset as $field_name=>$field_value) {
                if(empty($field_name))continue;
                if(substr($field_name, 0, 3) == 'id_' && strstr($field_name,'id_')) continue;
                if($field_name=='lib') $has_lib=true;
                if($field_name=='is_published') $has_lib=false;
                $case[] = "CASE WHEN i18n_".$field_name.".lib !='' THEN i18n_".$field_name.".lib ELSE CASE WHEN i18n_master_".$field_name.".lib !='' THEN i18n_master_".$field_name.".lib ELSE ".$this->table_name.".".$field_name." END END AS ".$field_name;
                $join.="LEFT JOIN gen_i18n i18n_".$field_name."  ON ( 
                        i18n_".$field_name.".id_element = ".$this->table_name.".".$this->primary_key."
                    AND i18n_".$field_name.".id_locales =".$this->id_locales."
                    AND i18n_".$field_name.".module = '".str_replace('gen_','',$this->table_name)."'
                    AND i18n_".$field_name.".field_name =  '".$field_name."' ) ";
                $join.="LEFT JOIN gen_i18n i18n_master_".$field_name."  ON ( 
                        i18n_master_".$field_name.".id_element = ".$this->table_name.".".$this->primary_key."
                    AND i18n_master_".$field_name.".id_locales =".$this->id_locales_master."
                    AND i18n_master_".$field_name.".module = '".str_replace('gen_','',$this->table_name)."'
                    AND i18n_master_".$field_name.".field_name =  '".$field_name."' ) ";
                $join_cnt=$join_cnt+1;
                /*if(!empty($search)) {
                    //foreach($fieldset as $field_name=>$field_value) {
                        //if(empty($field_name)) continue;
                        if(count($this->_search_fields) > 0 && in_array($field_name, $this->_search_fields)) {
                            $search_sql .= ' OR '. 'i18n_'.$field_name.".lib" .' like \''.$search.'\''; 
                            $search_sql .= ' OR '. 'i18n_'.$field_name.".lib" .' like \'%'.$search.'\''; 
                            $search_sql .= ' OR '. 'i18n_'.$field_name.".lib" .' like \'%'.$search.'%\''; 
                            // Utile si %valeur% ?  Pour perf ?
                            $search_sql .= ' OR '. $this->table_name.".".$field_name .' like \''.$search.'\''; 
                            // Utile si %valeur% ?  Pour perf ?
                            $search_sql .= ' OR '. $this->table_name.".".$field_name .' like \'%'.$search.'\''; 
                            $search_sql .= ' OR '. $this->table_name.".".$field_name .' like \'%'.$search.'%\''; 
                        }
                  //}
                }*/
            }
            if($has_lib){
                $sql = "SELECT  ".implode(',',$case).","." i18n_lib.is_published  FROM ".$this->table_name." ".$join." where 1 ".$critere. " ".$search_sql." ".$order; 
            }
            else{
               $sql = "SELECT  ".implode(',',$case). " FROM ".$this->table_name." ".$join." where 1  ".$critere. " ".$search_sql." ".$order; 
            }

            $this->OutputDebug('query',$sql);
            if($this->simulation===true) return $sql;
    
            $rs=$this->CachedQuery($sql);
            //$rs=$this->Query($sql);
            $results = array_merge($results, $rs->GetRows());
            $allResults[] = $results;
        }
        $merged = array();
        foreach($allResults as $key => $rows) {
            foreach($rows as $rowKey => $vals) {
                foreach($vals as $field => $val) {
                    $merged[$rowKey][$field] = $val;
                }
            }
        }
        return $merged;
   }

   function getSearchI18n($mixed='',$order='',$search='',$id_loc=ID_LOC)
   {
       $this->id_locales = $id_loc;
       $join_cnt = 0;
       if(!defined('ID_LOC_MASTER')) define('ID_LOC_MASTER',0);

       $this->id_locales_master = ID_LOC_MASTER;
       $search_sql = '';
       if(is_array($mixed) && !empty($mixed)){
           $critere = 'and '.$this->getSQLCriteres($mixed);
       } elseif(is_int($mixed)) {
           $critere = ' and( '.$this->table_name.".".$this->primary_key.' = '.$mixed.')';
       } else {	
           $critere = '';
       }

       $join = '';
       $case = array();
       $fields = $this->_search_fields;
       array_unshift($fields, $this->primary_key);
       foreach($fields as $field_name) {
           // XXX pas la peine de i18n les id ?.
           if(substr($field_name, 0, 3) != 'id_' && !strstr($field_name,'id_')) continue;
           $case[] = $this->table_name.".".$field_name;
       }
       foreach($fields as $field_name) {
           if(empty($field_name)) continue;
           if(substr($field_name, 0, 3) != 'id_' && strstr($field_name,'id_')) continue;
           $case[] = "CASE WHEN i18n_".$field_name.".lib !='' THEN i18n_".$field_name.".lib ELSE CASE WHEN i18n_master_".$field_name.".lib !='' THEN i18n_master_".$field_name.".lib ELSE ".$this->table_name.".".$field_name." END END AS ".$field_name;
            $join.="LEFT JOIN gen_i18n i18n_".$field_name."  ON (
                        i18n_".$field_name.".id_element = ".$this->table_name.".".$this->primary_key."
                    AND i18n_".$field_name.".id_locales =".$this->id_locales."
                    AND i18n_".$field_name.".module = '".str_replace('gen_','',$this->table_name)."'
                    AND i18n_".$field_name.".field_name =  '".$field_name."' ) ";
                    $join.="LEFT JOIN gen_i18n i18n_master_".$field_name."  ON ( 
                        i18n_master_".$field_name.".id_element = ".$this->table_name.".".$this->primary_key."
                    AND i18n_master_".$field_name.".id_locales =".$this->id_locales_master."
                    AND i18n_master_".$field_name.".module = '".str_replace('gen_','',$this->table_name)."'
                    AND i18n_master_".$field_name.".field_name =  '".$field_name."' ) ";

                    $join_cnt=$join_cnt+1;
       }
       if(!empty($search)) {
          $search_sql =  'and ( false ';
          foreach($fields as $field_name) {
                  if(empty($field_name)) continue;
                  $search_sql .= ' OR '. 'i18n_'.$field_name.".lib" .' like \''.$search.'\''; 
                  $search_sql .= ' OR '. 'i18n_'.$field_name.".lib" .' like \'%'.$search.'\''; 
                  $search_sql .= ' OR '. 'i18n_'.$field_name.".lib" .' like \'%'.$search.'%\''; 
                  // Utile si %valeur% ?  Pour perf ?
                  $search_sql .= ' OR '. $this->table_name.".".$field_name .' like \''.$search.'\''; 
                  // Utile si %valeur% ?  Pour perf ?
                  $search_sql .= ' OR '. $this->table_name.".".$field_name .' like \'%'.$search.'\''; 
                  $search_sql .= ' OR '. $this->table_name.".".$field_name .' like \'%'.$search.'%\''; 
          }
          $search_sql .= ' OR false )';
       }
       $sql = "SELECT  ".implode(',',$case). " FROM ".$this->table_name." ".$join." where 1  ".$critere. " ".$search_sql." group by ".$this->primary_key." ".$order; 
       if($this->simulation===true) return $sql;
       $rs=$this->query($sql);
       return $rs->GetRows();
   }

   public function isFormValid($data = array(), $pkVal = null)
   {
       $errors = array();
       try {
           $className = strtolower(get_class($this));
           //$file = implode(DIRECTORY_SEPARATOR, array(dirname(__FILE__), '..', 'admin', 'gen_admin', 'form_fields.php'));
           //$file =USR_DIR.'/etc/form_fields.php';
           $file = defined('CODE_SITE') && is_file(USR_DIR.'/etc/' . CODE_SITE . '.form_fields.php') ? USR_DIR.'/etc/' . CODE_SITE . '.form_fields.php' : USR_DIR.'/etc/form_fields.php';
           include_once($file);
           /*include_once(implode(DIRECTORY_SEPARATOR, array(USR_DIR, 'etc', 'form_fields.php')));*/
           if(isset($$className)) {
               $ref = $$className;
           } else {
               $baseName = str_replace('_' . CODE_SITE, '', $className);
               $ref = $$baseName;
           }
           $isValid = true;
           foreach($ref as $key => $conf) {
               if(array_key_exists('type', $conf) && in_array($conf['type'], array('generated', 'ouinon')) && !isset($data[$key])) {
                   $data[$key] = '';
               }
           }
           foreach($data as $key => $value) {
                if(isset($ref[$key]['pattern']) && $ref[$key]['pattern'] != '') {
                    $checkField = true;
                    if($key == 'password' && '' != $pkVal) {
                        $checkField = false;
                    }
                    if($checkField) {
                        if(!is_string($value)) {
                            if(isset($value['i18n'][0]['value'])) {
                                $value = $value['i18n'][0]['value'];
                            }
                        }
                        if(is_string($value)) { 
                            preg_match('/' . $ref[$key]['pattern'] . '/', $value, $res);
                            if(!is_array($res) || is_array($res) && count($res) == 0) {
                                $isValid = false;
                                $errors[$key] = "This field is not valid : " . $ref[$key]['label'];
                            }
                        }
                    }
                }
           }
           $this->_formErrors = $errors;
       } catch(Exception $e) {
           $errors['general'] = $e->getMessage();
           $isValid = false;
       }
       return $isValid;
   }

   public function getFormErrors() 
   {
       return $this->_formErrors;
   }

   public function setSearchLike($bool = true) 
   {
       $this->_searchLike = $bool;
   }

    public function __call($name, $args) 
    {
        if(strstr($name, 'getBy')) {
            $field = substr($name, 5, strlen($name));
            return $this->get(array($field => $args[0]));
        } elseif(strstr($name, 'getOneBy')) {
            $field = substr($name, 8, strlen($name));
            return $this->getOne(array($field => $args[0]));
        }
    }

    public function getLibField() 
    {
        return $this->_libField;
    }
    
    public function getSiteCollector($name)
    {
        if(array_key_exists($name . '_' . CODE_SITE, $this->collector)) {
            return $this->collector[$name . '_' . CODE_SITE];
        }
        return $this->collector[$name];
    }

    public function getObjectName($name) 
    {
        return strtolower(get_class($this->getSiteCollector($name)));
    }

}
