<?php 
class Cache{

    var $cache_path = '';
    var $id         = '';
    var $content    = '';
    var $timeToLive = 60;

    function __construct($path){
        $this->cache_path = $path;
        $this->id = md5($_SERVER['SERVER_NAME'].$_SERVER['PHP_SELF'].$_SERVER['QUERY_STRING'].(implode('', array_merge($_GET,$_POST))));
        $this->content = @file_get_contents($this->cache_path.$this->id);
    }
    function isCached(){
        if(!file_exists($this->cache_path.$this->id)) return false;
        $temp = time() - filemtime($this->cache_path.$this->id);
        if(($temp > $this->timeToLive) && ($this->timeToLive > 0)){
            return false;
        }
        return ($this->content !== false) ? true : false ;
    }
    function get(){
         return $this->content;
    }
    function del(){
        
    }
    function set(){
    $html = ob_get_contents();
    $this->content = $html;
    return file_put_contents($this->cache_path.$this->id, $this->content.'<!-- c: '.date("d/m/Y - H:i:s").'-->');
    }
}

?>
