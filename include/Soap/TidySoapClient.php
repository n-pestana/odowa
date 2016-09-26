<?php
class TidySoapClient extends SoapClient
{
    public function __doRequest($request, $location, $action, $version, $one_way = 0)
    {
        $response = parent::__doRequest($request, $location, $action, $version, $one_way);
        $start=strpos($response,'<soap:Envelope');
        $end=strrpos($response,'</soap:Envelope>');   
        $responseString = substr($response,$start,$end-$start+16);
        $responseString = '<![CDATA[' . $responseString . ']]>';
        return $responseString;
    }
}
