<?php
class Cookies{

    public $site;
    protected $_siteProperties = array();


    function __construct($site, $properties = array()){
        $this->site = str_replace('-','',urlize($site));
        $this->_siteProperties = $properties;
    }
    # LoadConfiguration {{{
    function LoadConfiguration(){
       return $this->getCookie($this->site);
    }
    # }}}

    # setLangue {{{
    function setLocale($lang){
       $temp = $this->LoadConfiguration($this->site);
       $temp['current_lang'] = $lang;
       $this->setCookie($temp);
    }
    #  }}}


    # getCookie{{{
    function getCookie(){
        return isset($_COOKIE[$this->getCookieName()]) ? unserialize($_COOKIE[$this->getCookieName()]) : '';
    }
    # }}}

    # setCookie{{{
    function setCookie($userProperties){
        //$ck = session_get_cookie_params();
//        return setcookie($this->getCookieName(), serialize($userProperties),time()+$ck['lifetime'] , $ck['path'], $ck['domain'], $ck['secure']);
//        return setcookie($this->getCookieName(), serialize($userProperties));
        return setcookie($this->getCookieName(), serialize($userProperties), strtotime("+7 days"), '/');
        //return setcookie($this->getCookieName(), serialize($userProperties), strtotime("+365 days"), '/');

    }
    # }}}

    # delCookie{{{
    function delCookie(){
        //return setcookie($this->getCookieName(), '', time()-1 , $ck['path'], $ck['domain'], $ck['secure']);
        return setcookie($this->getCookieName(), '');
    }
    # }}}

    # getCookieName{{{
    function getCookieName(){
        return $this->site;
    }
    # }}}
}
