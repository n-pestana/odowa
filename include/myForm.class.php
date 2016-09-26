<?php
# TODO : mettre en place la validation des checkbox

class myFormV1  extends genclass {

  var $id_locales_only=0;
  var $view_only = false;
  var $restrictions = array();
  var $id          	= '';
  var $etc_path     	= 'etc/';
  var $fields_ref   	= '';
  var $langue       	= 'fr';
  var $form_options 	= array(
      'name'     => ''
      ,'action'   => '#'
      ,'method'   => 'post'
      ,'enctype'  => 'multipart/form-data'
      ,'class'=>'block-content form'
  );
  var $default_values_locale = '';

  var $exclude    	= array();
  var $addAjaxSpan	= FALSE;
  var $from_groupe 	= FALSE;
  var $checkNoProfil	= FALSE;

  var $obj;
  var $collector;
  var $conf_file;
  var $self_callback = false;
  var $onlyFields = FALSE;
  var  $module_can_be_fully_edited_by_country_admin = array('pdv','press','news','pages','homepages','sites','backmsg','sites_options','admins','diag_r_incontournables','diag_r_soin','pages404','clients','orders','delivrymode','promocodes','advantages','adresses','pushs','comments','services','company','blogs','sportifs');


  function __construct(){
  }

    public function getConfig($formName) 
    {
        require($this->conf_file);
        $cmd = '$ref = $'.$formName.';';
        eval($cmd);
        if(!is_array($ref)) $this->error(_("no form reference found"));
        return $ref;
    }
         
  /*  get {{{ */
  function get($id, $fields, $values = array(), $form_name, $selectFields = null, $idToEdit = array(),$legend = ''){
    require($this->conf_file);
    $cmd = '$ref = $'.$form_name.($selectFields != '' ? '["child"]' : '').';';
    eval($cmd);
    if(!is_array($ref)) $this->error(_("no form reference found"));
    $excludeTypesCheck = array ('button' , 'submit');
    $head = $foot = $out = '';
    $forced_id_site = '';
    $field_struc    = array(
        'type'
        ,'id'
        ,'label'
        ,'pattern'
        ,'errorMsg'
        ,'maxlength'
        ,'size'
        ,'value'
        ,'arrayValues'
        ,'cols'
        ,'rows'
        ,'onclick'
        ,'onchange'
        ,'onsubmit'
        ,'class'
        ,'style'
        ,'optionStyle'
        ,'placeholder'
        );

    if( ! $this->onlyFields ) {
      $head.='<form 
        action   ="'.$this->form_options['action'].'"
        method   ="'.$this->form_options['method'].'"
        id       ="'.$this->form_options['id'].'"
        enctype  ="'.$this->form_options['enctype'].'"
        class    ="'.$this->form_options['class'].'"
        >';

    }
    if(!empty($legend)){
      $head.='<h1>'.$legend.'</h1>';
    }
    foreach($fields as $f_id){
      $f_id = trim($f_id);
      if(!isset($ref[$f_id])) continue;
      if( isset($ref[$f_id]['disabled']) && ($ref[$f_id]['disabled'] === true)) continue;
      # initialisation pour eviter de faire des isset
      foreach($field_struc as $k_struc){
        if(!isset($ref[$f_id][$k_struc])) $ref[$f_id][$k_struc] = '';
      }
      if(!isset($values[$f_id])) $values[$f_id] = '';
      # on test si multilangue pour le libele du champ:
      $ref[$f_id]['label']    = (is_array($ref[$f_id]['label'])) ? $ref[$f_id]['label'][$this->langue] : $ref[$f_id]['label'];
      $ref[$f_id]['value']    = (is_array($ref[$f_id]['value'])) ? $ref[$f_id]['value'][$this->langue] : $ref[$f_id]['value'];
      $ref[$f_id]['errorMsg'] = (is_array($ref[$f_id]['errorMsg'])) ? $ref[$f_id]['errorMsg'][$this->langue] : $ref[$f_id]['errorMsg'];
      $value = '';

      //if(!empty($this->restrictions) && isset($this->restrictions['locales']) && (!strstr($ref[$f_id]['type'],'i18n')) && (!strstr($ref[$f_id]['type'],'submit'))) continue;
      if(defined('ID_SITE') && !in_array($form_name,$this->module_can_be_fully_edited_by_country_admin)  && (!strstr($ref[$f_id]['type'],'i18n')) && (!strstr($ref[$f_id]['type'],'submit')) && (!strstr($ref[$f_id]['type'],'hidden'))){
        continue;
      } 
      elseif(defined('ID_SITE') && empty($forced_id_site)){
          $forced_id_site .= ID_SITE;
      }
      $out .= '<div class="block-line" id="block-'.$f_id.'" ><a name="link-'.$f_id.'"></a>';

      $hasPattern = isset($ref[$f_id]['pattern']) && $ref[$f_id]['pattern'] != '';
      $class = $hasPattern ? 'class="required formlabel"' : 'class="formlabel"';

      $label = (isset($ref[$f_id]['label']) && $ref[$f_id]['label'] != ''   && $ref[$f_id]['type'] != 'hidden')
        ? "<label ".$class." '>".$ref[$f_id]['label']."</label>" 
        : "";
      $out .= $label;
      switch($ref[$f_id]['type']){
        case 'info':
          if(isset($ref[$f_id]['callBack']) && !empty($ref[$f_id]['callBack'])){
            if(isset($ref[$f_id]['args']) && !empty($ref[$f_id]['args']) && !empty($values[$ref[$f_id]['args']])) {
              eval('$value = '.$ref[$f_id]["callBack"].'(\''.$values[$ref[$f_id]['args']].'\');');
            } else {
              if($values[$f_id] != '') eval('$value = '.$ref[$f_id]["callBack"].'(\''.$values[$f_id].'\');');
              else $value = $ref[$f_id]['value'];
            }
          } else {
            $value = (($values[$f_id] == '') ? $ref[$f_id]['value']  : $values[$f_id] );
          }
          if($this->view_only === true){
            $out .= '<p class="read-only">'.$value.'</p>';
            continue;
          }

          $value = ($value); 
          $out .= '<div class="info">'.$value.'</div>';
          break;
        case 'text':
         
          if(isset($ref[$f_id]['callBack']) && !empty($ref[$f_id]['callBack'])){
            if(isset($ref[$f_id]['args']) && !empty($ref[$f_id]['args']) && !empty($values[$ref[$f_id]['args']])) {
              eval('$value = '.$ref[$f_id]["callBack"].'(\''.$values[$ref[$f_id]['args']].'\');');
            } else {
              if($values[$f_id] != '') eval('$value = '.$ref[$f_id]["callBack"].'(\''.$values[$f_id].'\');');
              else $value = $ref[$f_id]['value'];
            }
          } else {
            $value = (($values[$f_id] == '') ? $ref[$f_id]['value']  : $values[$f_id] );
          }
          if($this->view_only === true || isset($ref[$f_id]['view_only'])){
            $out .= '<p class="read-only">'.$value.'</p>';
            continue;
          }
          $patternAttr = $ref[$f_id]['pattern'] != '' ? ' pattern="' . $ref[$f_id]['pattern'] . '" ' : '';

          $value = ($value); 
          $out .= '<input 
            name        ="'.$id.'['.$f_id.']"
            id          ="'.$f_id.'"
            type        ="'.$ref[$f_id]['type'].'" 
            value       ="'.$value.'" 
            size        ="'.$ref[$f_id]['size'].'" 
            maxlength   ="'.$ref[$f_id]['maxlength'].'" 
            onclick     ="'.$ref[$f_id]['onclick'].'"
            ' . $patternAttr . '
            errorMsg    ="'.$ref[$f_id]['errorMsg'].'"
            class       ="'.$ref[$f_id]['class'].' full-width"
            style       ="'.$ref[$f_id]['style'].'"
            ' . (isset($ref[$f_id]['readonly']) ? 'readonly="readonly"' : '') . '
            />';
          break;
        case 'submit':
          if($this->view_only === true) continue;
        case 'button':
          if($this->view_only === true) continue;
          $out .='<button type="'.$ref[$f_id]['type'].'" onclick     ="'.$ref[$f_id]['onclick'].'" >'.$ref[$f_id]['value'].'</button>';  
          break;

        case 'textarea':
          if($this->view_only === true){
            $out .=nl2br((($values[$f_id] == '') ? $ref[$f_id]['value']  : $values[$f_id] ));
            continue;
          } 
          $out .='<textarea 
            name        ="'.$id.'['.$f_id.']"
            id          ="'.$f_id.'"
            onclick     ="'.$ref[$f_id]['onclick'].'"
            class       ="'.$ref[$f_id]['class'].'"
            cols        ="'.$ref[$f_id]['cols'].'"
            rows        ="'.$ref[$f_id]['rows'].'"
            maxlength   ="'.$ref[$f_id]['maxlength'].'" 
            onclick     ="'.$ref[$f_id]['onclick'].'"
            pattern     ="'.$ref[$f_id]['pattern'].'"
            errorMsg    ="'.$ref[$f_id]['errorMsg'].'"
            style       ="'.$ref[$f_id]['style'].'"
            >'.(($values[$f_id] == '') ? $ref[$f_id]['value']  : $values[$f_id] ).'</textarea>'; 
          break;
        case 'html_i18n':
        case 'file_i18n':
        case 'i18n':
        case 'simple_html_i18n':
        case 'select_i18n':
# object gammes
          if(!empty($this->restrictions) && isset($this->restrictions['locales'])){
            $available_locales  = $this->collector['locales']->get(array('id_locales'=>$this->restrictions['locales']));
            $info ='';
          }
          else{
            $available_locales  = ($this->collector['locales']->get(array('is_master'=>1),'is_master desc ,lib asc')); 
            $info ='Only master locales are displayed, thanks to use the language selector to modify non master language';
          }

           $out.=' <div id="oldtab-locales'.$f_id.'" style="">';

         $out .= ' <fieldset>';
       /*    if($ref[$f_id]['label'] != '') { 
              $out .='<legend>'.$ref[$f_id]['label'] .': <span>[-]</span></legend>';
              $labels[] = $ref[$f_id]['label'];
            }*/


          switch($ref[$f_id]['type']):
        case 'i18n':

	      $readonly = (!empty($this->restrictions) && isset($this->restrictions['locales']) && empty($forced_id_site)) ? 'readonly' : '';
	      $disabled="";

	      if(defined('ID_SITE') && !in_array($form_name,$this->module_can_be_fully_edited_by_country_admin) ){
		      $disabled="disabled";
	      } 

	      //$disabled="disabled";
		if(!isset($ref[$f_id]['hidemain']) || ($ref[$f_id]['hidemain']==false)){
			    $out .= '<textarea '.$disabled.' 
			name        ="'.$id.'['.$f_id.'][i18n][0][value]"
			onclick     ="'.$ref[$f_id]['onclick'].'"
			class       ="full-width'.$ref[$f_id]['class'].'"
			cols        ="'.$ref[$f_id]['cols'].'"
			rows        ="'.$ref[$f_id]['rows'].'"
			maxlength   ="'.$ref[$f_id]['maxlength'].'" 
			onclick     ="'.$ref[$f_id]['onclick'].'"
			pattern     ="'.$ref[$f_id]['pattern'].'"
			errorMsg    ="'.$ref[$f_id]['errorMsg'].'"
			style       ="'.$ref[$f_id]['style'].'"
			>'.($values[$f_id]).'</textarea>'; 
		  }
            break;
      case 'select_i18n':
            $count = 1;
            $multiple = isset($ref[$f_id]['multiple']) ? 'multiple = "'.$ref[$f_id]['multiple'].'"' : '';
            $size     = isset($ref[$f_id]['size']) ? 'size = "'.$ref[$f_id]['size'].'"' : '';

      $readonly = (!empty($this->restrictions) && isset($this->restrictions['locales'])  && empty($forced_id_site)) ? 'disabled' : '';
      if(defined('ID_SITE') && !in_array($form_name,$this->module_can_be_fully_edited_by_country_admin) ){
          $readonly = "disabled";
      }
            $out .= '<select '.$readonly.'
              data-placeholder="'.$ref[$f_id]['placeholder'].'"
              name        ="'.$id.'['.$f_id.'][i18n][0][value]"
              onclick     ="'.$ref[$f_id]['onclick'].'"
              onchange    ="'.$ref[$f_id]['onchange'].'"
              pattern     ="'.$ref[$f_id]['pattern'].'"
              errorMsg    ="'.$ref[$f_id]['errorMsg'].'"
              style       ="'.$ref[$f_id]['style'].'"
              class="'.$ref[$f_id]['class'].'"
              '.$multiple.'
              '.$size.'
              >';
            if(isset($ref[$f_id]['placeholder']) && !empty($ref[$f_id]['placeholder'])){
              $out .= '<option value=""></option>';
            }
            foreach ( $ref[$f_id]['arrayValues'] as $opt_val => $opt_lib ) {
              $opt_val = trim($opt_val);
              $sel = ( isset( $values[$f_id]) && ( strtolower($opt_val) ==  strtolower($values[$f_id])) ) ? ' selected' : '';
              $sel = ( ( (empty($sel)) && (empty($values[$f_id])) && (!$opt_val) ) || ! empty($sel)) ?  'selected' : '';
              $out .= ' <option value = "'.$opt_val .'" ';
              $out .= ' style="'.(str_replace('$'.$f_id, $opt_val,$ref[$f_id]['optionStyle'])).  '" ';
              $out .= $sel.'>'.$opt_lib.'</option>';
            }
            $out .= '</select>';
            unset($count,$opt_val,$opt_lib);
            break;


        case 'file_i18n':

            $readonly = (!empty($this->restrictions) && isset($this->restrictions['locales']) && empty($forced_id_site)) ? 'readonly' : '';
            $out .='<input '.$readonly.' value="'.$values[$f_id].'" type="text" id="'.$id.$f_id.'i18n0" name="'.$id.'['.$f_id.'][i18n][0][value]" onchange    ="'.$ref[$f_id]['onchange'].'" class="input-xlarge" style="width:300px"/> <button  onclick="moxman.browse({fields: \''.$id.$f_id.'i18n0\', no_host: true});return false;" class="btn" onclick="">Pick file</button>&nbsp;
            <button onclick="moxman.edit({ path: \''.$values[$f_id].'\'});return false;" class="btn">Edit</button>&nbsp;<a href="'.$values[$f_id].'" class="big-button  fancybox">View</a>
             ';
            break;
            case 'html_i18n':
              $out .= '<textarea 
                name        ="'.$id.'['.$f_id.'][i18n][0][value]"
                style       ="display:none"
                class  ="html"

                >'.($values[$f_id]).'</textarea>'; 
            break;
            case 'simple_html_i18n':
		if(!isset($values['media'])) $values['media'] = '';
                $out .= "<input type='hidden' id='bg_".$f_id."' value='".$values['media']."' />";
                $out .= '<textarea id="'.$f_id.'"    name="'.$id.'['.$f_id.'][i18n][0][value]" class="html" rel="'.$values['media'].'">'.($values[$f_id]).'</textarea>'; 
            break;
           endswitch;

            $out .='<div class="i18nchild" id="tab-locale'.$f_id.'"> <ul class="mini-tabs  js-tabs same-height tab'.$f_id.'"> <span style="font-size:8px; color: #666">'.$info.'</span>';
            $j=0; 
            foreach($available_locales as $locale){
              $lib_lang= strtolower($locale['country']);
              $out .= '		<li class="'.(($j==0) ? 'current' : '' ).'"> <a href="#tab-'.$f_id.$locale['id_locales'].'" '.(($locale['is_master']==1) ? 'class="highlighted"'  : '').'>
                                <img width="16" height="11" title="'.$locale['lib'].'" alt="'.$locale['lib'].'" src="/images/icons/flags/'.$lib_lang.'.png" >&nbsp;'.strtoupper($locale['language']).'
                              </a></li>';
              $j++;
            }
            $out .='		</ul>';


            $j=0;
            foreach($available_locales as $locale){

              $value= $this->collector['i18n']->getOne(array('module'=>$form_name,'field_name'=>$f_id,'id_locales'=>$locale['id_locales'],'id_element'=>array_values($idToEdit))); 
              $lib_lang= strtolower($locale['country']);
              $out .= ' <div class="" id="tab-'.$f_id.$locale['id_locales'].'" style="'.(($j==0) ? 'current' : 'display: none;' ).'"> ';
              $j++;

              switch($ref[$f_id]['type']):
              case 'i18n':

                $out .= '<textarea 
                  name        ="'.$id.'['.$f_id.'][i18n]['.$locale['id_locales'].'][value]"
                  id          ="'.$f_id.'['.$lib_lang.']"
                  onclick     ="'.$ref[$f_id]['onclick'].'"
                  class       ="full-width'.$ref[$f_id]['class'].' i18n-textarea-child"
                  cols        ="'.$ref[$f_id]['cols'].'"
                  rows        ="'.$ref[$f_id]['rows'].'"
                  maxlength   ="'.$ref[$f_id]['maxlength'].'" 
                  onclick     ="'.$ref[$f_id]['onclick'].'"
                  pattern     ="'.$ref[$f_id]['pattern'].'"
                  errorMsg    ="'.$ref[$f_id]['errorMsg'].'"
                  style       ="'.$ref[$f_id]['style'].'"
                  >'.($value['lib']).'</textarea>'; 

                break;
                 case 'simple_html_i18n':


                $out .= '<textarea 
                  name        ="'.$id.'['.$f_id.'][i18n]['.$locale['id_locales'].'][value]"
                  id          ="'.$f_id.'['.$lib_lang.$locale['id_locales'].']"
                  onclick     ="'.$ref[$f_id]['onclick'].'"
                  class       = "full-width html '.$ref[$f_id]['class'].' i18n-textarea-child-html"
                  cols        ="'.$ref[$f_id]['cols'].'"
                  rows        ="'.$ref[$f_id]['rows'].'"
                  maxlength   ="'.$ref[$f_id]['maxlength'].'" 
                  onclick     ="'.$ref[$f_id]['onclick'].'"
                  pattern     ="'.$ref[$f_id]['pattern'].'"
                  errorMsg    ="'.$ref[$f_id]['errorMsg'].'"
                  style       ="'.$ref[$f_id]['style'].'"
                  >'.($value['lib']).'</textarea>'; 

                break;

          case 'select_i18n': 
             $count = 1;
            $multiple = isset($ref[$f_id]['multiple']) ? 'multiple = "'.$ref[$f_id]['multiple'].'"' : '';
            $size     = isset($ref[$f_id]['size']) ? 'size = "'.$ref[$f_id]['size'].'"' : '';
            $out .= '<select
              data-placeholder="'.$ref[$f_id]['placeholder'].'"
              name        ="'.$id.'['.$f_id.'][i18n]['.$locale['id_locales'].'][value]"
              id          ="'.$form_name . '_' . $f_id.'['.$lib_lang.']"
              onclick     ="'.$ref[$f_id]['onclick'].'"
              onchange    ="'.$ref[$f_id]['onchange'].'"
              pattern     ="'.$ref[$f_id]['pattern'].'"
              errorMsg    ="'.$ref[$f_id]['errorMsg'].'"
              style       ="'.$ref[$f_id]['style'].'"
              class="'.$ref[$f_id]['class'].'"
              '.$multiple.'
              '.$size.'
              >';
            if(isset($ref[$f_id]['placeholder']) && !empty($ref[$f_id]['placeholder'])){
          //    $out .= '<option value="">Not set</option>';
            }

            $out .= '<option value="">Not set</option>';
            foreach ( $ref[$f_id]['arrayValues'] as $opt_val => $opt_lib ) {
              $opt_val = trim($opt_val);
              $sel = ($opt_val ==  $value['lib'] ) ? ' selected' : '';
              //$sel = ( ( (empty($sel))  && (!$opt_val) ) || ! empty($sel)) ?  'selected' : '';
              //$sel = ( ( (empty($sel)) && (empty($value['lib'])) && (!$opt_val) ) || ! empty($sel)) ?  'selected' : '';
              $out .= ' <option value = "'.$opt_val .'" ';
              $out .= ' style="'.(str_replace('$'.$f_id, $opt_val,$ref[$f_id]['optionStyle'])).  '" ';
              $out .= $sel.'>'.$opt_lib.'</option>';
            }
            $out .= '</select>';
            unset($count,$opt_val,$opt_lib);
 
          break;
              case 'file_i18n':
                $out .='<input  value="'.$value['lib'].'" type="text" id="'.$id.$f_id.$locale['id_locales'].'" name="'.$id.'['.$f_id.'][i18n]['.$locale['id_locales'].'][value]" class="input-xlarge media-child" /> <button  onclick="moxman.browse({fields: \''.$id.$f_id.$locale['id_locales'].'\', no_host: true});return false;" class="btn">Pick file</button>';
                break;
              case 'html_i18n':
                $out .= '<textarea 
                  name        ="'.$id.'['.$f_id.'][i18n]['.$locale['id_locales'].'][value]"
                  id          ="'.$f_id.'['.$lib_lang.']"
                  style       ="display:none"
                  >'.($value['lib']).'</textarea>'; 

                //$out .='<button id="">Edit html content</button><br /><br />';
                //$out .="<button id=\"\" onclick=\"EditInLine('".$locale['language']."','".$locale['id_locales']."');return false\">Edit html content</button><br />";
                
                $furl = substr(FRONT_URL, 0, 1) == '.' ? ID_SITE . FRONT_URL : FRONT_URL;
                $out .='<a class="big-button ifancybox2" href="http://'.$furl.'tiny.php?editable=true&id_pages='.((isset($_REQUEST['id_pages'])) ? $_REQUEST['id_pages'] : '').'&id_locales='.$locale['id_locales'].'">Edit html content ['.$locale['language'].']</a><br />';

                break;

                endswitch;


               $out .= '<div class="colx2-left green-bg"><span>Enable for this language ? </span><select
                  name        ="'.$id.'['.$f_id.'][i18n]['.$locale['id_locales'].'][is_published]"
                  id          ="'.$f_id.'" 
                  onclick     ="'.$ref[$f_id]['onclick'].'"
                  onchange    ="'.$ref[$f_id]['onchange'].'"
                  pattern     ="'.$ref[$f_id]['pattern'].'"
                  errorMsg    ="'.$ref[$f_id]['errorMsg'].'"
                  style       ="'.$ref[$f_id]['style'].'"
                  class="'.$ref[$f_id]['class'].'"
                  data-placeholder="" >';
                if(isset($ref[$f_id]['placeholder']) && !empty($ref[$f_id]['placeholder'])){
                  $out .= '<option value=""></option>';
                }
                foreach ( array('0'=>("No"),'1'=>("Yes")) as $opt_val => $opt_lib ) {
                  if(!isset($value['is_published']) && $opt_val=='1') {
                    $out .= ' <option value = "'.$opt_val .'" selected="selected">'.$opt_lib.'</option>';
                  } 
                  else{
                    $out .= ' <option value = "'.$opt_val .'" '.(($value['is_published'] == $opt_val)  ? 'selected="selected"' : '' ).'>'.$opt_lib.'</option>';
                  }
                }
                unset($opt_val, $opt_lib);
                $out .= '</select></div>';
                $out . '</div>';

                if(!$locale['is_master']): #add userright
                                            endif; #is_master
                                             // if($this->id_locales_only==0):
                                               /* $out .= '<div class="colx2-right grey-bg"><span class="label">Can be overwrited by noadmin ? <select
                                                  name        ="'.$id.'['.$f_id.'][i18n]['.$locale['id_locales'].'][editable]"
                                                  id          ="'.$f_id.'" 
                                                  onclick     ="'.$ref[$f_id]['onclick'].'"
                                                  onchange    ="'.$ref[$f_id]['onchange'].'"
                                                  pattern     ="'.$ref[$f_id]['pattern'].'"
                                                  errorMsg    ="'.$ref[$f_id]['errorMsg'].'"
                                                  style       ="'.$ref[$f_id]['style'].'"
                                                  class="'.$ref[$f_id]['class'].'"
                                                  data-placeholder="" >';
                if(isset($ref[$f_id]['placeholder']) && !empty($ref[$f_id]['placeholder'])){
                  $out .= '<option value=""></option>';
                }
                foreach ( array('0'=>("No"),'1'=>("Yes")) as $opt_val => $opt_lib ) {
                  $out .= ' <option value = "'.$opt_val .'" '.(($value['is_published'] == $opt_val)  ? 'selected="selected"' : '' ).'>'.$opt_lib.'</option>';
                }
                unset($opt_val, $opt_lib);
                $out .= '</select></span></div>';

  */
                //endif;


                $out .='</div>';
            }
            $out .='</div></fieldset></div>';

# on va chercher les locales qui sont existe et sont actives ou non.
# pour chaque locale, afficher un champ avec le libelle de la locale dans un section avec tab pour chaque locale.
            break;
        case 'relationel_list':


            $count = 1;
            $multiple = (isset($ref[$f_id]['multiple'])) ? ' multiple="multiple"' : '';
            //$multiple = isset($ref[$f_id]['multiple']) ? 'multiple = "'.$ref[$f_id]['multiple'].'"' : '';
            $size     = isset($ref[$f_id]['size']) ? 'size = "'.$ref[$f_id]['size'].'"' : '';

            $linked_object= $ref[$f_id]['linked_object']; 

            if(!isset( $this->collector[$form_name.'_'.$linked_object])){
              die('you are trying to call an non existing object:'.$form_name.'_'.$linked_object);
            }
            $this->collector[$form_name.'_'.$linked_object]->SetUsedFields(array('id_'.$linked_object));

            $current = @array_values($this->collector[$form_name.'_'.$linked_object]->get($idToEdit));
            if(!is_array($current)) $current = array();
            $linked_values = array();
            foreach($current as $k=>$v){
              $linked_values[]=$v['id_'.$linked_object]; 
            }



            // $id_value = current($values);	
            $out .='<select
              name        ="'.$id.'['.$f_id.'][]"
              id          ="'.$f_id.'" 
              onclick     ="'.$ref[$f_id]['onclick'].'"
              onchange    ="'.$ref[$f_id]['onchange'].'"
              pattern     ="'.$ref[$f_id]['pattern'].'"
              errorMsg    ="'.$ref[$f_id]['errorMsg'].'"
              style       ="'.$ref[$f_id]['style'].'"
              class="'.$ref[$f_id]['class'].'"
              data-placeholder="'.$ref[$f_id]['placeholder'].'"
              '.$multiple.'
              '.$size.'
              >';
            if(isset($ref[$f_id]['placeholder']) && !empty($ref[$f_id]['placeholder'])){
              $out .= '<option value=""></option>';
            }


            foreach ( $ref[$f_id]['arrayValues'] as $opt_val => $opt_lib ) {
              $opt_val = trim($opt_val);
              $sel = in_array($opt_val, $linked_values) ? 'selected' : '' ;
              $out .= ' <option value = "'.$opt_val .'" ';
              $out .= ' style="'.(str_replace('$'.$f_id, $opt_val,$ref[$f_id]['optionStyle'])).  '" ';
              $out .= $sel.'>'.$opt_lib.'</option>';
            }
            $out .= '</select>';
            unset($count,$opt_val,$opt_lib);
            break;
        case 'select':
          if($this->view_only === true){
            $out .= '<p class="read-only">'.$ref[$f_id]['arrayValues'][$values[$f_id]].'</p>';

            continue;
          }
            if(!is_array($ref[$f_id]['arrayValues'])){
              $this->warning(_("select list  without arrayValues: ").$f_id);
              continue;
            }
            $count = 1;
            $multiple = isset($ref[$f_id]['multiple']) ? 'multiple = "'.$ref[$f_id]['multiple'].'"' : '';
            $size     = isset($ref[$f_id]['size']) ? 'size = "'.$ref[$f_id]['size'].'"' : '';
            $out .='<select
              data-placeholder="'.$ref[$f_id]['placeholder'].'"
              name        ="'.$id.'['.$f_id.']"
              id          ="'.$form_name . '_' . $f_id.'" 
              onclick     ="'.$ref[$f_id]['onclick'].'"
              onchange    ="'.$ref[$f_id]['onchange'].'"
              pattern     ="'.$ref[$f_id]['pattern'].'"
              errorMsg    ="'.$ref[$f_id]['errorMsg'].'"
              style       ="'.$ref[$f_id]['style'].'"
              class="'.$ref[$f_id]['class'].'"
              '.$multiple.'
              '.$size.'
              >';
            if(isset($ref[$f_id]['placeholder']) && !empty($ref[$f_id]['placeholder'])){
              $out .= '<option value=""></option>';
            }
            foreach ( $ref[$f_id]['arrayValues'] as $opt_val => $opt_lib ) {
              $opt_val = trim($opt_val);
              $sel = ( isset( $values[$f_id]) && ( strtolower($opt_val) ==  strtolower($values[$f_id])) ) ? ' selected' : '';
              $sel = ( ( (empty($sel)) && (empty($values[$f_id])) && (!$opt_val) ) || ! empty($sel)) ?  'selected' : '';
              $out .= ' <option value = "'.$opt_val .'" ';
              $out .= ' style="'.(str_replace('$'.$f_id, $opt_val,$ref[$f_id]['optionStyle'])).  '" ';
              $out .= $sel.'>'.$opt_lib.'</option>';
            }
            $out .= '</select>';
            unset($count,$opt_val,$opt_lib);
            break;
        case 'selectMultiple':
            break;
        case 'radio':
            if(!is_array($ref[$f_id]['arrayValues'])){
              $this->warning(_("radio without arrayValues:").$f_id);
              continue;
            }
            $count      = 1;
            $out  = '';
            foreach ( $ref[$f_id]['arrayValues'] as $opt_val => $opt_lib ) {
              $sel = (isset( $values[$f_id]) && ( $opt_val ==  $values[$f_id]) ) ? ' checked' : '';
              $out .= '<input 
                type        ="'.$ref[$f_id]['type'].'" 
                name        ="'.$id.'['.$f_id.']"
                id          ="'.$f_id.'" 
                value       ="'.$opt_val.'" '.$sel.'  
                onclick     ="document.getElementById(\''.$id.'['.$f_id.']'.'_validator\').value=this.value;'.$ref[$f_id]['onclick'].'"

                />'.$opt_lib;
            }
            $out .= '<input type="hidden" value="'.$values[$f_id].'" id="'.$id.'['.$f_id.']'.'_validator" pattern     = "'.$ref[$f_id]['pattern'].'" errorMsg    = "'.$ref[$f_id]['errorMsg'].'"/>';
            unset($count,$opt_val,$opt_lib);
            break;

        case 'switch':
            $count      = 1;
            $out  = '
              <div class="columns">
              <div class="colx2-left">
              <legend for="simple-switch-off">Switch off<span>[-]</span></legend>
              <input type="checkbox" name="simple-switch-off" id="simple-switch-off" value="1" class="switch">
              <legend for="simple-switch-off">Switch off<span>[-]</span></legend> <input type="checkbox" name="'.$f_id.'" id="'.$f_id.'" value="1" class="switch"></div>';
            break;

            /*	checkbox {{{ */
        case 'checkbox':
            if(!is_array($ref[$f_id]['arrayValues'])){
              $this->warning(_("checkbox  without arrayValues: ").$f_id);
              continue;
            }
            $count      = 1;
            $out  = '';
            if(!empty($ref[$f_id]['pattern'])) $this->warning(_("pattern on checkbox is not supported yet").$f_id);
            if( ! is_array($values[$f_id])  ) $values[$f_id] = array($values[$f_id]);
            foreach ( $ref[$f_id]['arrayValues'] as $opt_val => $opt_lib ) {
              $sel = (is_array( $values[$f_id]) && in_array( $opt_val, $values[$f_id] )) ? ' checked' : '';
              $out .= '<input 
                type        ="'.$ref[$f_id]['type'].'" 
                name        ="'.$id.'['.$f_id.'][]"
                id          ="'.$f_id.'_'.$count++.'" 
                value       ="'.$opt_val.'" '.$sel.'  
                onclick     ="'.$ref[$f_id]['onclick'].'"
                onchange    ="'.$ref[$f_id]['onchange'].'"
                pattern     ="'.$ref[$f_id]['pattern'].'"
                errorMsg    ="'.$ref[$f_id]['errorMsg'].'"
                class		="checkbox"
                style     	="'.$ref[$f_id]['style'].'"
                />'.( (isset($ref[$f_id]['make_lib_float']) && $ref[$f_id]['make_lib_float'] == true) ? '<div class="float-left-block">'.$opt_lib.'</div>' : $opt_lib);
            }
            unset($count,$opt_val,$opt_lib);
            break;
            /*	}}}	*/
            /*	password {{{ */
        case 'password':
              //value       ="'.(($values[$f_id] == '') ? $ref[$f_id]['value']  : $values[$f_id] ).'" 
            $out .='<input 
              name        ="'.$id.'['.$f_id.']"
              id          ="'.$f_id.'"
              type        ="'.$ref[$f_id]['type'].'" 
              value       ="" 
              size        ="'.$ref[$f_id]['size'].'" 
              maxlength   ="'.$ref[$f_id]['maxlength'].'" 
              onclick     ="'.$ref[$f_id]['onclick'].'"
              pattern     ="'.$ref[$f_id]['pattern'].'"
              errorMsg    ="'.$ref[$f_id]['errorMsg'].'"
              class       ="'.$ref[$f_id]['class'].'"
              style       ="'.$ref[$f_id]['style'].'"
              />';  
            break;
            /*	}}}	*/
            /* hidden {{{ */
        case 'hidden':
            $out .= '<input
              type   = "hidden" 
              name   = "'.$id.'['.$f_id.']"
              id     = "'.$f_id.'" 
              value  = "'.(($values[$f_id] == '') ? $ref[$f_id]['value']  : $values[$f_id] ).'" 
              />';
            break;
            /*	}}}	*/
            /*	html {{{ */
        case 'html':
              $out .= '<textarea 
              name        ="'.$id.'['.$f_id.'][i18n][0][value]" class="html_editable" style       ="'.$ref[$f_id]['style'].'" >'.($values[$f_id]).'</textarea>'; 

            break;
            /*	}}}	*/
            /*	file {{{ */
        case 'file':
            $out .='<input type="text" id="absurl" class="input-xlarge" style="width:300px"/> <button  onclick="moxman.browse({fields: \'absurl\', no_host: true});return false;" class="btn">Pick file</button>';
            /*
               $out .='<span id="input_'.$f_id.'"></span><input
               name        ="'.$id.'['.$f_id.']"
               id          ="'.$f_id.'"
               type        ="'.$ref[$f_id]['type'].'"
               value       ="'.(($values[$f_id] == '') ? $ref[$f_id]['value']  : $values[$f_id] ).'"
               size        ="'.$ref[$f_id]['size'].'"
               maxlength   ="'.$ref[$f_id]['maxlength'].'"
               pattern     ="'.$ref[$f_id]['pattern'].'"
               errorMsg    ="'.$ref[$f_id]['errorMsg'].'"
               class       ="'.$ref[$f_id]['class'].'"
               /><div id="fichiers'.$f_id.'"> </div> ';
               $src = '';
               if( ! empty ($values[$f_id]) ){
               $src = $values[$f_id];
               } else {
            // Si on n'a pas la valeur en base, on regarde si toutefois on ne peut pas retrouver le fichier si les parametres sont definis
            if(isset($GLOBALS['obj'][$id]->_upload_params[$f_id]['dir']) && isset($GLOBALS['obj'][$id]->_upload_params[$f_id]['field_used']) && !empty($values[$GLOBALS['obj'][$id]->_upload_params[$f_id]['field_used']])) {
            $file_name = $GLOBALS['obj'][$id]->_upload_params[$f_id]['dir'].'/'.$values[$GLOBALS['obj'][$id]->_upload_params[$f_id]['field_used']];
            $exts = isset($GLOBALS['obj'][$id]->_allowed_ext) ? $GLOBALS['obj'][$id]->_allowed_ext : array('.jpg');
            foreach($exts as $ext) { if(file_exists($GLOBALS['conf']['dir']['usr'].'/'.$file_name.$ext)) $src = $file_name.$ext; }
            }
            }
            if(! empty($src))
            $out .= '&nbsp;<a href="/usr/'.$src.'" target="fileview"><img src="templates/default/images/kview.png" style="border:0; text-decoration:none;"/></a>';
             */
            break;


            /*	}}}	*/
            /*	calendar {{{ */
        case 'calendar':
            $format_date = isset($ref[$f_id]['format']) ? $ref[$f_id]['format'] : 'format-d-m-y';
            if(isset($ref[$f_id]['callBack']) && !empty($ref[$f_id]['callBack'])){
              if(isset($ref[$f_id]['args']) && !empty($ref[$f_id]['args']) && !empty($values[$ref[$f_id]['args']])) {
                eval('$value = '.$ref[$f_id]["callBack"].'(\''.$values[$ref[$f_id]['args']].'\');');
              } else {
                if($values[$f_id] != '') eval('$value = '.$ref[$f_id]["callBack"].'(\''.$values[$f_id].'\');');
                else $value = $ref[$f_id]['value'];
              }
            } else {
              $value = (($values[$f_id] == '') ? $ref[$f_id]['value']  : $values[$f_id] );
            }
            $out .='<input 
              name        ="'.$id.'['.$f_id.']"
              id          ="'.$f_id.'"
              type        ="text" 
              value       ="'.$value.'" 
              size        ="'.$ref[$f_id]['size'].'" 
              maxlength   ="'.$ref[$f_id]['maxlength'].'" 
              onclick     ="'.$ref[$f_id]['onclick'].'"
              ' . (isset($ref[$f_id]['pattern']) && $ref[$f_id]['pattern'] != '' ? 'pattern     ="'.$ref[$f_id]['pattern'].'"' : '') . '
              errorMsg    ="'.$ref[$f_id]['errorMsg'].'"
              class       ="'.$format_date.' '.$ref[$f_id]['class'].' datepicker"

              style       ="'.$ref[$f_id]['style'].'"
              />';
            break;
            /*	}}}	*/
            /*	autocompletion {{{ */
/*        case 'autocompletion':
            if(isset($ref[$f_id]['callBack']) && !empty($ref[$f_id]['callBack'])){
              if(isset($ref[$f_id]['args']) && !empty($ref[$f_id]['args']) && !empty($values[$ref[$f_id]['args']])) {
                eval('$value = '.$ref[$f_id]["callBack"].'(\''.$values[$ref[$f_id]['args']].'\');');
              } else {
                if($values[$f_id] != '') eval('$value = '.$ref[$f_id]["callBack"].'(\''.$values[$f_id].'\');');
                else $value = $ref[$f_id]['value'];
              }
            } else
              $value = (($values[$f_id] == '') ? $ref[$f_id]['value']  : $values[$f_id] );

            $out .='<input
              id          ="'.$f_id.'"
              type        ="text"
              value       ="'.$value.'"
              size        ="'.$ref[$f_id]['size'].'"
              maxlength   ="'.$ref[$f_id]['maxlength'].'"
              onclick     ="'.$ref[$f_id]['onclick'].'"
              pattern     ="'.$ref[$f_id]['pattern'].'"
              errorMsg    ="'.$ref[$f_id]['errorMsg'].'"
              class       ="'.$ref[$f_id]['class'].'"
              style       ="'.$ref[$f_id]['style'].'"
              />';

            #	resultFilledId : S'il y a ce parametre, on stocke dans un champ hidden l'id  du champs a mettre a jour
#                , j'ai pas trouvé mieux vu que l'Autocompleter ne permet pas de passer de params ... 
            if(isset($ref[$f_id]['resultFilledId']) && !empty($ref[$f_id]['resultFilledId']))
              $out .= '<input type="hidden" id="resultFilled_'.$f_id.'" value="'.$ref[$f_id]['resultFilledId'].'"/>';
            $out .= "
              <div id='update_".$f_id."'></div>
              <script type='text/javascript'>
              new Ajax.Autocompleter('".$f_id."', 'update_".$f_id."', '/auto_complete.php', {method: 'post', paramName: '".$f_id."'});
            </script>";
            break;*/
            /*	}}}	*/
        case 'generated':
            $out .= $ref[$f_id]['value'];
            break;
      }
      $out .= '</div>';
      //   if( ! isset($ref[$f_id]['noBR'])) { $out .= "<br class='clear'/>"; }
      //     if($this->addAjaxSpan) $out .= '<span id="ajax_update"></span>';
    }

    $key = array_keys($idToEdit);
    if(! empty($idToEdit))  $foot.= '<input type="hidden" name="'.$key[0].'" value="'.$idToEdit[$key[0]].'"/>';
    if(!empty($legend)){
//      $out.='</fieldset>';
    }
    //$out .= $out_id_site;
    $foot.= '</form>';
    return $head.$out.$foot;
  }

  function warning($lib){
    $this->error($lib);
  }

  function error($lib){
    die($lib); 
  }

  /*  createForm  {{{ */
  function createForm($form_name, $class, $form_fields = array(), $title, $legend = null, $confirmMsg = null, $addAjaxSpan = false) {
    global $obj, $conf;
    if( ! isset($obj[$class]) ) {
      $this->error();
    }

    $this->form_options = array(
        'id'     => $obj[$class]->table_name
        ,'action'   => $_SERVER['PHP_SELF']
        ,'method'   => 'post'
        ,'enctype'  => 'multipart/form-data'
        ,'class'    => 'block-content form myform' 
    );
    $this->addAjaxSpan = $addAjaxSpan;
    // current_id est soit : le $_GET['sites_id'] ou le $_POST['sites['site_id']']
    $current_id = $i18n_filters = array() ;

    $current_id[$obj[$class]->primary_key] = (isset($_REQUEST[$obj[$class]->primary_key])) ? $_REQUEST[$obj[$class]->primary_key] : '';
    if(isset($_REQUEST[$obj[$class]->table_name])) {

      $tmp = $current_id[$obj[$class]->primary_key];

      if(empty($current_id[$obj[$class]->primary_key]) && isset($_REQUEST[$obj[$class]->table_name][$obj[$class]->primary_key]))
        $current_id[$obj[$class]->primary_key] = $_REQUEST[$obj[$class]->table_name][$obj[$class]->primary_key];

      # type list
      $list_fields = array();


      foreach($_REQUEST[$obj[$class]->table_name] as $request_key=>$request_value){
        if(!strstr($request_key,'_list'))continue;
        $list_fields[$request_key]=$request_value;
        unset($_REQUEST[$obj[$class]->table_name][$request_key]);
      }

     $default_i18n_values=array();	

      if(defined('ID_SITE') && in_array($form_name,$this->module_can_be_fully_edited_by_country_admin) && $form_name != 'sites'){
        $_REQUEST[$obj[$class]->table_name]['id_site']=ID_SITE;
      }
      foreach($_REQUEST[$obj[$class]->table_name] as $request_key=>$request_value){
        if(is_array($request_value) && isset($request_value['i18n']) && !empty($request_value['i18n'])) {
          if(isset($request_value['i18n'][0])){
            $default_i18n_values[$request_key] = $request_value['i18n'][0]['value'];
            unset($request_value['i18n'][0]);
          }
          $i18n_filters[$request_key] = $request_value['i18n'];
          unset($_REQUEST[$obj[$class]->table_name][$request_key]);
        }
      }

      # setting	       	
      if(defined('ID_SITE') && !in_array($form_name,$this->module_can_be_fully_edited_by_country_admin)){

      }
      else{
        // Modif Ken pour poster des NULL au lieu de '' (pour les clés étrangères notamment)
        $saveValues = array_merge($_REQUEST[$obj[$class]->table_name],$default_i18n_values);
        foreach($saveValues as &$saveVal) {
            $saveVal = ('' === $saveVal) ? NULL : $saveVal;
        }
        $current_id[$obj[$class]->primary_key] = $obj[$class]->set($current_id[$obj[$class]->primary_key], $saveValues);
      }

      # after save callback
      if($this->self_callback != false){
        call_user_func( array( $obj[$class],$this->self_callback),$current_id[$obj[$class]->primary_key]) ;
      }

      foreach($i18n_filters as $i18n_field=>$i18n_filter):
        foreach($i18n_filter as $id_locale=>$i18n_values){
         // if($i18n_values['value']=='' && $i18n_values['is_published']!=0) continue;
        # on set : module,  field_name, id_locales, lib, is_published ,level 		
          $array = array(
              'module'	=> $form_name
              ,'field_name'	=> $i18n_field
              ,'id_locales'	=> $id_locale 
              ,'lib'		=> $i18n_values['value']
              ,'is_published' => $i18n_values['is_published']
              //,'level'	=> $i18n_values['editable']
              ,'id_element'   => $current_id[$obj[$class]->primary_key]
          );
          $existing_i18n = $this->collector['i18n']->getOne(array('module'=> $form_name,'field_name'=> $i18n_field,'id_locales'=> $id_locale,'id_element'=>$current_id[$obj[$class]->primary_key]));
          $this->collector['i18n']->set((isset($existing_i18n['id_i18n'])) ? $existing_i18n['id_i18n'] : '' ,$array);

        }
      endforeach;


      # is list detected
      foreach($list_fields as $request_key=>$request_value){
      # mai il est dans le conf ?
        require($this->conf_file);
        $linked_object = $$form_name;
        $linked_object= $linked_object[$request_key]['linked_object'];

        # get current value for merging
        $linked_current = @array_values($this->collector[$class.'_'.$linked_object]->get(array($obj[$class]->primary_key=>$current_id[$obj[$class]->primary_key])));
        if(!is_array($linked_current)) $linked_current=array();
        foreach($linked_current as $k=>$v){
        # we don't need the primary key value
          unset($v['id_'.$class.'_'.$linked_object]);
          $linked_values[$v['id_'.$linked_object]]=$v; 
        }

        # if editing/existing
        if($current_id[$obj[$class]->primary_key] != '' && !empty($linked_values))	{

          $to_del=$this->collector[$class.'_'.$linked_object]->get(array($obj[$class]->primary_key=>$current_id[$obj[$class]->primary_key]));
          $id_to_del = array();
          foreach($to_del as $k=>$v){
            $id_to_del[]=$v['id_'.$class.'_'.$linked_object];
          }
          $this->collector[$class.'_'.$linked_object]->del($id_to_del);


        }

        foreach($request_value as $rel_value){
          if(isset($linked_values[$rel_value])){
            $this->collector[$class.'_'.$linked_object]->set('',$linked_values[$rel_value]);
          } 
          else{
//            if($rel_value == 'NULL')continue;

        #on set si rel_value danss linked_values;	
        # sinon on fait ce dessous;

            $array = array(
                $obj[$class]->primary_key=>$current_id[$obj[$class]->primary_key]
                ,'id_'.$linked_object=>$rel_value
                );
             $this->collector[$form_name.'_'.$linked_object]->set('',$array);
            
          }
        }		

      }
      // REQUEST est utilisé pour la fonction makeLists et current_id pour createForm
      $_REQUEST[$obj[$class]->primary_key] = ($current_id[$obj[$class]->primary_key] == '' || $current_id[$obj[$class]->primary_key] == 1) ? $tmp : $current_id[$obj[$class]->primary_key];
      $current_id[$obj[$class]->primary_key] = $_REQUEST[$obj[$class]->primary_key];
      $confirm = 1;
    }
    #$fields = array('button');
    $fields = array();
    $temp = ( ! empty($form_fields) ? $form_fields : array_keys($obj[$class]->array_fields) );
    $fields = array_merge($fields, $temp);
    $fields[] = 'submit';
    $fields[] = 'delete';
    $display='';
    if(isset($confirm) && $confirm == 1) {
      $display .= $this->getConfirm();
    }
    else{
      if(!empty($this->restrictions) && isset($this->restrictions['locales']) && isset($this->restrictions['pref_loc'])){
        if(!defined('ID_LOC')) define('ID_LOC',$this->restrictions['pref_loc']);
        $values = ((isset($_REQUEST) && $current_id[$obj[$class]->primary_key]) != '') ? $obj[$class]->getOneI18n((int)$current_id[$obj[$class]->primary_key]) : array();
      }
      else{
        $values = ((isset($_REQUEST) && $current_id[$obj[$class]->primary_key]) != '') ? $obj[$class]->get((int)$current_id[$obj[$class]->primary_key]) : array();
      }

      $formRequestVals = method_exists($obj[$class], 'getFormRequestKeys') ? $obj[$class]->getFormRequestKeys() : array('lang', 'groupe_id', 'sites_id_projet', 'id_clients');
      foreach($formRequestVals as $val) {
        if(isset($_REQUEST[$val]) && trim($_REQUEST[$val]) != '') $values[$val] = $_REQUEST[$val];
      }

      $display .= $this->get(   $this->form_options['id']
          , $fields 
          , $values
          , $form_name
          , ''
          , $current_id,$title);
      $display .= $this->getFooter();
    }
    return $display;
  }
  /*  }}}    */

  function getFooter() {
    //$display = '</fieldset>';
    //return $display;
  }

  function getConfirm($NoSecDiv = null) {
    $display = '<script>notify("Saved !");</script>';
    $display .= '<script>location.reload();</script>';
    return $display;
  }

# XXX des tradu dans une class ??
  function getConfirmFront($type = '') {
    $msg = $type == 'users' 
      ? ('Merci de vous être enregistré, vous aurez bientôt accès à plus de fonctionnalités.')
      : ('Votre demande a bien été enregistrée, nous vous contacterons prochainement.');
    $display = '<div id="confirmFront">'.$msg.'</div>';
    return $display;
  }

}
function array_values_recursive($arr){
  $arr = array_values($arr);
  foreach($arr as $key => $val)
    if(array_values($val) === $val)
      $arr[$key] = array_values_recursive($val);

  return $arr;
}
