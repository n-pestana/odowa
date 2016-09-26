<?php 
function partition( $list, $p ) {
    $listlen = count( $list );
    $partlen = floor( $listlen / $p );
    $partrem = $listlen % $p;
    $partition = array();
    $mark = 0;
    for ($px = 0; $px < $p; $px++) {
        $incr = ($px < $partrem) ? $partlen + 1 : $partlen;
        $partition[$px] = array_slice( $list, $mark, $incr );
        $mark += $incr;
    }
    return $partition;
}
function chrono($line){
$time_end = microtime(true);
$time = $time_end - TIME_START;
echo "\nTIME [".$line."]".date("d/m/Y").":".$time;

}
function getNoneAsciiTable() {
    return array(
        'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A',
         ' '=>'-','\''=>'','"'=>'',";"=>'',","=>"",":"=>"","\n"=>"-",
         "."=>"","\t"=>"-","\r"=>"-","™"=>"","€"=>"", 
        "&"=>"","»"=>"","«"=>"","’"=>"",
        "А"=>"a", "Б"=>"b", "В"=>"v", "Г"=>"g", "Д"=>"d",
        "Е"=>"e", "Ё"=>"yo", "Ж"=>"zh", "З"=>"z", "И"=>"i", 
        "Й"=>"j", "К"=>"k", "Л"=>"l", "М"=>"m", "Н"=>"n", 
        "О"=>"o", "П"=>"p", "Р"=>"r", "С"=>"s", "Т"=>"t", 
        "У"=>"u", "Ф"=>"f", "Х"=>"kh", "Ц"=>"ts", "Ч"=>"ch", 
        "Ш"=>"sh", "Щ"=>"sch", "Ъ"=>"", "Ы"=>"y", "Ь"=>"", 
        "Э"=>"e", "Ю"=>"yu", "Я"=>"ya", "а"=>"a", "б"=>"b", 
        "в"=>"v", "г"=>"g", "д"=>"d", "е"=>"e", "ё"=>"yo", 
        "ж"=>"zh", "з"=>"z", "и"=>"i", "й"=>"j", "к"=>"k", 
        "л"=>"l", "м"=>"m", "н"=>"n", "о"=>"o", "п"=>"p", 
        "р"=>"r", "с"=>"s", "т"=>"t", "у"=>"u", "ф"=>"f", 
        "х"=>"kh", "ц"=>"ts", "ч"=>"ch", "ш"=>"sh", "щ"=>"sch", 
        "ъ"=>"", "ы"=>"y", "ь"=>"", "э"=>"e", "ю"=>"yu", 
        "я"=>"ya", " "=>"-", "."=>"", ","=>"", "/"=>"-",  
        ":"=>"", ";"=>"","—"=>"", "–"=>"-",
        'Å'=>'A', 'Æ'=>'A', 'Ā'=>'A', 'Ą'=>'A', 'Ă'=>'A',
        'Ç'=>'C', 'Ć'=>'C', 'Č'=>'C', 'Ĉ'=>'C', 'Ċ'=>'C',
        'Ď'=>'D', 'Đ'=>'D', 'È'=>'E', 'É'=>'E', 'Ê'=>'E',
        'Ë'=>'E', 'Ē'=>'E', 'Ę'=>'E', 'Ě'=>'E', 'Ĕ'=>'E',
        'Ė'=>'E', 'Ĝ'=>'G', 'Ğ'=>'G', 'Ġ'=>'G', 'Ģ'=>'G',
        'Ĥ'=>'H', 'Ħ'=>'H', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I',
        'Ï'=>'I', 'Ī'=>'I', 'Ĩ'=>'I', 'Ĭ'=>'I', 'Į'=>'I',
        'İ'=>'I', 'Ĳ'=>'IJ', 'Ĵ'=>'J', 'Ķ'=>'K', 'Ľ'=>'K',
        'Ĺ'=>'K', 'Ļ'=>'K', 'Ŀ'=>'K', 'Ł'=>'L', 'Ñ'=>'N',
        'Ń'=>'N', 'Ň'=>'N', 'Ņ'=>'N', 'Ŋ'=>'N', 'Ò'=>'O',
        'Ó'=>'O', 'Ô'=>'O', 'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O',
        'Ō'=>'O', 'Ő'=>'O', 'Ŏ'=>'O', 'Œ'=>'OE', 'Ŕ'=>'R',
        'Ř'=>'R', 'Ŗ'=>'R', 'Ś'=>'S', 'Ş'=>'S', 'Ŝ'=>'S',
        'Š'=>'S', 'Ť'=>'T', 'Ţ'=>'T', 'Ŧ'=>'T', 'Ù'=>'U',
        'Ú'=>'U', 'Û'=>'U', 'Ü'=>'Ue', 'Ū'=>'U', 'Ů'=>'U',
        'Ű'=>'U', 'Ŭ'=>'U', 'Ũ'=>'U', 'Ų'=>'U', 'Ŵ'=>'W',
        'Ŷ'=>'Y', 'Ÿ'=>'Y', 'Ý'=>'Y', 'Ź'=>'Z', 'Ż'=>'Z',
        'Ž'=>'Z', 'à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a',
        'ä'=>'a', 'ā'=>'a', 'ą'=>'a', 'ă'=>'a', 'å'=>'a',
        'æ'=>'ae', 'ç'=>'c', 'ć'=>'c', 'č'=>'c', 'ĉ'=>'c',
        'ċ'=>'c', 'ď'=>'d', 'đ'=>'d', 'è'=>'e', 'é'=>'e',
        'ê'=>'e', 'ë'=>'e', 'ē'=>'e', 'ę'=>'e', 'ě'=>'e',
        'ĕ'=>'e', 'ė'=>'e', 'ƒ'=>'f', 'ĝ'=>'g', 'ğ'=>'g',
        'ġ'=>'g', 'ģ'=>'g', 'ĥ'=>'h', 'ħ'=>'h', 'ì'=>'i',
        'í'=>'i', 'î'=>'i', 'ï'=>'i', 'ī'=>'i', 'ĩ'=>'i',
        'ĭ'=>'i', 'į'=>'i', 'ı'=>'i', 'ĳ'=>'ij', 'ĵ'=>'j',
        'ķ'=>'k', 'ĸ'=>'k', 'ł'=>'l', 'ľ'=>'l', 'ĺ'=>'l',
        'ļ'=>'l', 'ŀ'=>'l', 'ñ'=>'n', 'ń'=>'n', 'ň'=>'n',
        'ņ'=>'n', 'ŉ'=>'n', 'ŋ'=>'n', 'ò'=>'o', 'ó'=>'o',
        'ô'=>'o', 'õ'=>'o', 'ö'=>'oe', 'ø'=>'o', 'ō'=>'o',
        'ő'=>'o', 'ŏ'=>'o', 'œ'=>'oe', 'ŕ'=>'r', 'ř'=>'r',
        'ŗ'=>'r', 'ś'=>'s', 'š'=>'s', 'ť'=>'t', 'ù'=>'u',
        'ú'=>'u', 'û'=>'u', 'ü'=>'u', 'ū'=>'u', 'ů'=>'u',
        'ű'=>'u', 'ŭ'=>'u', 'ũ'=>'u', 'ų'=>'', 'ŵ'=>'w',
        'ÿ'=>'y', 'ý'=>'y', 'ŷ'=>'y', 'ż'=>'z', 'ź'=>'z',
        'ž'=>'z', 'ß'=>'ss', 'ſ'=>'ss', 'Α'=>'A', 'Ά'=>'A',
        'Β'=>'B', 'Γ'=>'G', 'Δ'=>'D', 'Ε'=>'E', 'Έ'=>'E',
        'Ζ'=>'Z', 'Η'=>'I', 'Ή'=>'I', 'Κ'=>'K', 'Λ'=>'L',
        'Μ'=>'M', 'Ν'=>'N', 'Ξ'=>'KS', 'Ο'=>'O', 'Ό'=>'O',
        'Π'=>'P', 'Ρ'=>'R', 'Σ'=>'S', 'Ϋ'=>'Y', 'Φ'=>'F',
        'Ψ'=>'PS', 'Ω'=>'O', 'Ώ'=>'O', 'α'=>'a', 'ά'=>'a',
        'β'=>'b', 'γ'=>'g', 'δ'=>'d', 'ε'=>'e', 'έ'=>'e',
        'ζ'=>'z', 'η'=>'i', 'ή'=>'i', 'κ'=>'k', 'λ'=>'l',
        'μ'=>'m', 'ν'=>'n', 'ξ'=>'ks', 'ό'=>'o', 'π'=>'p',
        'ρ'=>'r', 'φ'=>'f', 'χ'=>'x', 'ψ'=>'ps', 'ω'=>'o',
        'ώ'=>'o', 'Б'=>'B', 'Г'=>'G', 'Д'=>'D', 'Ё'=>'E',
        'Ж'=>'ZH', 'З'=>'Z', 'И'=>'I', 'Й'=>'I', 'Л'=>'L',
        'П'=>'P', 'У'=>'U', 'Ф'=>'F', 'Ц'=>'TS', 'Ч'=>'CH',
        'Ш'=>'SH', 'Щ'=>'SHCH', 'Ы'=>'Y', 'Э'=>'E', 'Ю'=>'YU',
        'Я'=>'YA', 'б'=>'B', 'в'=>'V', 'г'=>'G', 'д'=>'D',
        'е'=>'E', 'ё'=>'E', 'ж'=>'ZH', 'з'=>'Z', 'и'=>'I',
        'й'=>'I', 'к'=>'K', 'л'=>'L', 'м'=>'M', 'н'=>'N', 'п'=>'P',
        'т'=>'T', 'у'=>'U', 'ф'=>'F', 'х'=>'KH', 'ц'=>'TS', 'ч'=>'CH',
        'ш'=>'SH', 'щ'=>'SHCH', 'ы'=>'Y', 'э'=>'E', 'ю'=>'YU',
        'я'=>'YA', 'Ъ'=>'', 'ъ'=>'', 'Ь'=>'', 'ь'=>'',
        'ð'=>'d', 'Ð'=>'D', 'þ'=>'th', 'Þ'=>'TH');
}
function asciify($value){
    $noneAsciiTable = getNoneAsciiTable();
    $value = strtr("$value", $noneAsciiTable);
    return $value;
}

if(!function_exists('getBackgroundColor')){
function getBackgroundColor($img){
	try {
        	$image = new Imagick($img);
	}
	catch(Exception $e){
		return false;
	}
        $x = 10;
        $y = 10;
        $p1= $image->getImagePixelColor(10, 10);
        $p2= $image->getImagePixelColor(10, 100);
        $controle=$p1->getColor();
        $colors=$p2->getColor();
        if(array_diff($colors,$controle)==array() && $colors['r']<120 && $colors['g']<120 && $colors['b']<120)
         return '#'.dechex($colors['r']).dechex($colors['g']).dechex($colors['b']);
        else
         return false;
}
}

if(!function_exists('DetectLocale')){
function DetectLocale(){
       if(!isset($_SERVER["HTTP_ACCEPT_LANGUAGE"]))return array();
        preg_match_all('|.*(..-..).*|isU',$_SERVER["HTTP_ACCEPT_LANGUAGE"],$out);
        if((empty($out) || empty($out[1])) && strlen($_SERVER["HTTP_ACCEPT_LANGUAGE"])==2){
          $out[1] = array($_SERVER["HTTP_ACCEPT_LANGUAGE"],$_SERVER["HTTP_ACCEPT_LANGUAGE"]);;
        }
        $formated_locales = array();
        foreach($out[1] as $langs){
                $formated_locales[] = strtolower(current(explode('-',$langs))).'_'.strtoupper(end(explode('-',$langs)));
        }
        return $formated_locales;

}
/*
        if(!isset($_SERVER["HTTP_ACCEPT_LANGUAGE"]))return array();
	preg_match_all('|.*(..-..).*|isU',$_SERVER["HTTP_ACCEPT_LANGUAGE"],$out);
	$formated_locales = array();
	foreach($out[1] as $langs){
		$formated_locales[] = strtolower(current(explode('-',$langs))).'_'.strtoupper(end(explode('-',$langs)));
		
	}
	return $formated_locales;
*/
}
if(!function_exists('SuppAccents')){
  function SuppAccents($chaine){
     return  SuppAccents2($string);
    $tofind = "ÀÁÂÃÄÅàáâãäåÒÓÔÕÖØòóôõöøÈÉÊËèéêëÇçÌÍÎÏìíîïÙÚÛÜùúûüÿÑñ";
    $replac = "AAAAAAaaaaaaOOOOOOooooooEEEEeeeeCcIIIIiiiiUUUUuuuuyNn";
    return(strtr($chaine,$tofind,$replac));
  }
}

if(!function_exists('urlize')){
  function urlize($string){
    $find   = array(
     '/[^A-Za-z0-9ÀÁÂÃÄÅàáâãäåÒÓÔÕÖØòóôõöøÈÉÊËèéêëÇçÌÍÎÏìíîïÙÚÛÜùúûüÿÑñ]/'  #alphanum + accents
     ,'/(^http)/'                # - as end
     ,'/[-]+/'              # multi -
     ,'/(^-)/'              # - as begin
     ,'/(-$)/'                # - as end
    );
    $repl   = array('-','','-','','');
    return  preg_replace($find, $repl, $string);
  }
}
if(!function_exists('getDefaultLanguage')){
function getDefaultLanguage() {

/*
   if (isset($_SERVER["HTTP_ACCEPT_LANGUAGE"]))
      return parseDefaultLanguage($_SERVER["HTTP_ACCEPT_LANGUAGE"]);
   else
      return parseDefaultLanguage(NULL);*/
   }
}

if(!function_exists('parseDefaultLanguage')){
function parseDefaultLanguage($http_accept, $deflang = "en") {


   if(isset($http_accept) && strlen($http_accept) > 1)  {
      # Split possible languages into array
      $x = explode(",",$http_accept);
      foreach ($x as $val) {
         #check for q-value and create associative array. No q-value means 1 by rule
         if(preg_match("/(.*);q=([0-1]{0,1}\.\d{0,4})/i",$val,$matches))
            $lang[$matches[1]] = (float)$matches[2];
         else
            $lang[$val] = 1.0;
      }

      #return default language (highest q-value)
      $qval = 0.0;
      foreach ($lang as $key => $value) {
         if ($value > $qval) {
            $qval = (float)$value;
            $deflang = $key;
         }
      }
   }
   return strtolower($deflang);
}
}


if(!function_exists('get_include_contents')){
function get_include_contents($filename) {
    if (is_file($filename)) {
        ob_start();
        include $filename;
        $contents = ob_get_contents();
        ob_end_clean();
        return $contents;
    }
    return false;
}
}

if(!function_exists('largeStockByID')){
function largeStockByID($root, $id){
 $path = $root;
 $md = $id;
 $r1 = substr($md,0,1).'/';
 $r2 = substr($md,1,1).'/';
 $r3 = substr($md,2,1).'/';
 $r4 = substr($md,3,1).'/';
 if(!is_dir($path.$r1)) mkdir ($path.$r1);
 if(!is_dir($path.$r1.$r2)) mkdir ($path.$r1.$r2);
 if(!is_dir($path.$r1.$r2.$r3)) mkdir ($path.$r1.$r2.$r3);
 if(!is_dir($path.$r1.$r2.$r3.$r4)) mkdir ($path.$r1.$r2.$r3.$r4);
 return $path.$r1.$r2.$r3.$r4;
}
}

if(!function_exists('SuppAccents3')){
function SuppAccents3($string){
  //$string = html_entity_decode(valid_utf8($string),null,'UTF-8');
  $search = array( 'ç','ñ','Ä','Â','ä','â','à','Ë','Ê','é','è','ë','ê','Ï','Î','ï','î','Ö','Ô','ö','ô','Ü','Û','ü','û');
  $replace = array('c','n','A','A','a','a','a','E','E','e','e','e','e','I','I','i','i','O','O','o','o','U','U','u','u');
  $string = str_replace($search, $replace, $string);
  return $string;
}
}

if(!function_exists('urlize2')){
function urlize2($string){
  $string = SuppAccents3($string);

  $find   = array(
   '/[^A-Za-z0-9]/'  #alphanum
   ,'/(^http)/'                # - as end
   ,'/[-]+/'              # multi -
   ,'/(^-)/'              # - as begin
   ,'/(-$)/'                # - as end
  );

  $repl   = array('-','','-','','');
  return  preg_replace($find, $repl, $string);
}
}

if(!function_exists('myasort')){
# because asort cant return value, and i need it into form_fields
function myasort($array = array()){
  asort($array);
  return $array;  

}
}

/*  au cas ou gettext n'est pas installe   {{{ */
if(!function_exists("_")) { function _($str){ return $str ; } };
/*  }}} */

if(!function_exists('kz_sort_by_key')){
# permet de trier un tableau sur un clé niveau2 {{{
function kz_sort_by_key( &$array, $key, $type = 'string'){
     if( empty( $array ) || empty( $key ) ){ return false ;}
     switch($type){
         case 'string':
             return uasort( $array, create_function(
                 '$k1, $k2', 'return strcmp( $k1[\''.$key.'\'], $k2[\''.$key.'\'] );')
             );
             break;
         case 'int':
             return uasort( $array, create_function(
                 '$k1, $k2', 'return ( $k1[\''.$key.'\'] > $k2[\''.$key.'\'] );')
             );
             break;
     }
}
}
# }}}

# memory_get_usage {{{
if (!function_exists('memory_get_usage')) {
    function memory_get_usage(){
        return 'unable to get memory usage under windows';
    }
}
# }}}

if(!function_exists('array_merge_keys')){
# array_merge_keys {{{
function array_merge_keys(){
	$args = func_get_args();
	$result = array();
	foreach($args as &$array){
		foreach($array as $key=>&$value){
			$result[$key] = $value;
		}
	}
	return $result;
}
# }}}
}

if(!function_exists('mkpath')){
function mkpath($path){
    $temp     = explode('/', $path);
    $fullpath = '';
    foreach($temp as $k=>$v){
        $fullpath .= $v.'/';
        if(!is_dir($fullpath)){
            if(!mkdir($fullpath)) return false;
        }
    }
    return true;
}
}

if(!function_exists('readdirr')){
function readdirr($dir){
    $handle = opendir($dir);
    for(;(false !== ($readdir = readdir($handle)));){
        if($readdir != '.' && $readdir != '..'){
            $path = $dir.'/'.$readdir;
            if(is_dir($path))    $output[$readdir] = readdirr($path);
            if(is_file($path))   $output[] = $readdir;
        }
    }
    return isset($output)?$output:false;
    closedir($handle);
}
}


if(!function_exists('path_to_url')){
# path_to_url {{{
function path_to_url($path,$absolute = false){
    # XXX ajouter le protocol
    return (($absolute === true) ? 'http://'.$_SERVER['HTTP_HOST'] : '' ).'/'.str_replace($GLOBALS['conf']['dir']['root'],'',realpath($path));
}
#}}}
}

if(!function_exists('SuppAccents2')){
function SuppAccents2($string){
  //$string = html_entity_decode(valid_utf8($string),null,'UTF-8');
  $search = array( 'ç','ñ','Ä','Â','ä','â','à','Ë','Ê','é','è','ë','ê','Ï','Î','ï','î','Ö','Ô','ö','ô','Ü','Û','ü','û');
  $replace = array('c','n','A','A','a','a','a','E','E','e','e','e','e','I','I','i','i','O','O','o','o','U','U','u','u');
  $string = str_replace($search, $replace, $string);
  return $string;
}
}

if(!function_exists('urlizator')){
function urlizator($string){
  $string = SuppAccents2($string);

  $find   = array(
   '/[^A-Za-z0-9]/'  #alphanum
   ,'/(^http)/'                # - as end
   ,'/[-]+/'              # multi -
   ,'/(^-)/'              # - as begin
   ,'/(-$)/'                # - as end
  );

  $repl   = array('-','','-','','');
  return  preg_replace($find, $repl, $string);
}
}

if(!function_exists('get_distance_m')){
function get_distance_m($lat1, $lng1, $lat2, $lng2) {

  $earth_radius = 6378137;   // Terre = sphère de 6378km de rayon
  $rlo1 = deg2rad($lng1);
  $rla1 = deg2rad($lat1);
  $rlo2 = deg2rad($lng2);
  $rla2 = deg2rad($lat2);
  $dlo = ($rlo2 - $rlo1) / 2;
  $dla = ($rla2 - $rla1) / 2;
  $a = (sin($dla) * sin($dla)) + cos($rla1) * cos($rla2) * (sin($dlo) * sin($dlo));
  $d = 2 * atan2(sqrt($a), sqrt(1 - $a));
  return ($earth_radius * $d);

}
}


if(!function_exists('dateFrtoDbDate')){
function dateFrtoDbDate($dateFr){
	if(empty($dateFr)) return false;
	$reg = '/^(0[1-9]|[12][0-9]|3[01])([- \/.])(0[1-9]|1[012])([- \/.])(\d\d?\d\d)$/';
	preg_match($reg, $dateFr, $matches);
	if( empty($matches) ) return;
	$d = $matches[1];
	$m = $matches[3];
	$y = $matches[5];
	return $y.'-'.$m.'-'.$d;
}
}

if(!function_exists('DbDatetoDateFr')){
function DbDatetoDateFr($dbdate){

	if(empty($dateFr)) return false;
	 $reg = '/^(0[1-9]|1[012])([- \/.])(0[1-9]|[12][0-9]|3[01])([- \/.])(\d\d?\d\d)$/';
                        preg_match($reg, $date, $matches);
                        if( empty($matches) ) return;
                        $d = $matches[3];
                        $m = $matches[1];
                        $y = $matches[5];

	return $d.'/'.$m.'/'.$y;

}
}
if(!function_exists('checkPassword')){
function checkPassword($pwd) {
    $errors=array();

    if (strlen($pwd) < 8) {
        $errors[] = "Password too short!";
    }

    if (!preg_match("#[0-9]+#", $pwd)) {
        $errors[] = "Password must include at least one number!";
    }

    if (!preg_match("#[a-zA-Z]+#", $pwd)) {
        $errors[] = "Password must include at least one letter!";
    }     
    return (empty($errors)) ? true : $errors;
}
}

function xml_to_array($root) {
    $result = array();

    if ($root->hasAttributes()) {
        $attrs = $root->attributes;
        foreach ($attrs as $attr) {
            $result['@attributes'][$attr->name] = $attr->value;
        }
    }

    if ($root->hasChildNodes()) {
        $children = $root->childNodes;
        if ($children->length == 1) {
            $child = $children->item(0);
            if ($child->nodeType == XML_TEXT_NODE) {
                $result['_value'] = $child->nodeValue;
                return count($result) == 1
                    ? $result['_value']
                    : $result;
            }
        }
        $groups = array();
        foreach ($children as $child) {
            if (!isset($result[$child->nodeName])) {
                $result[$child->nodeName] = xml_to_array($child);
            } else {
                if (!isset($groups[$child->nodeName])) {
                    $result[$child->nodeName] = array($result[$child->nodeName]);
                    $groups[$child->nodeName] = 1;
                }
                $result[$child->nodeName][] = xml_to_array($child);
            }
        }
    }

    return $result;
}

function Truncate($input, $numWords)
{
  if(str_word_count($input,0)>$numWords)
  {
    $WordKey = str_word_count($input,1);
    $PosKey = str_word_count($input,2);
    reset($PosKey);
    foreach($WordKey as $key => &$value)
    {
        $value=key($PosKey);
        next($PosKey);
    }
    return substr($input,0,$WordKey[$numWords]);
  }
  else {return $input;}
} 
?>
