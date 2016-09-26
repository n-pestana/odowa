<?php 
class SoapClass 
{
    public function __construct($params = array()) 
    {
        foreach($params as $param => $val) {
            if(is_array($val) && array_key_exists('soapInst', $val)) {
                $vals = $val;
                $inst = $val['soapInst'];
                unset($vals['soapInst']);
                $this->{$param} = new $inst($vals);
            } else {
                $this->{$param} = $val;
            }
        }
    }
}
