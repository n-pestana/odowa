<?php 
class Displayer {

  private $call_func ;
  private $root;
  public $header;
  public $footer;
  public $collector;
  public $display ;
  public $vars; 
  public $cookies;
  public $id_visitors;
  public $id_members;
  public $conf;
  public $cache_file;
  public $write_cache_now;
  
  function __construct(){
	$this->display=$this;
        $path =sys_get_temp_dir().'/academie-cache/';
        if(!is_dir($path)) mkdir($path);

        $this->cache_file = $path.md5(serialize($_SESSION).serialize($_GET).serialize($_POST).$_SERVER['SERVER_NAME'].$_SERVER['PHP_SELF']);
        if(file_exists($this->cache_file)){
          die(file_get_contents($this->cache_file));
        }
        else{
#          $this->write_cache_now=true;
        }
        
  }
  
  function setCallBackFunction($func){
	$this->call_func=$func;
  }
  function Display(){
     
   $out = $this->header.$this->content.$this->footer;
    if(!empty($this->call_func)){
       if(!function_exists($this->call_func)) die("TEMPLATE call back function not found:".$this->call_func.'');        
       $out = call_user_func_array($this->call_func,array($out));
    }
    if($this->write_cache_now==true){
      file_put_contents($this->cache_file,$out.'<!--CACHED : '.date('d/m/Y H:i:s').'-->');
    }
    echo $out;
  }

  function fetch($filename){
        ob_start();
        include $filename;
        $contents = ob_get_contents();
        @ob_end_clean();
        return $contents;

  }
  function setVar($key,$value){
   $this->vars[$key] = $value;
  }
}
