<?php 
class Colissimo 
{
    /**
     * Soap client
     *
     * @access protected
     * @var SoapClient
     */
    protected $_client;

    protected $_contractNumber;
    protected $_password;

    public function __construct($contractNumber, $password) 
    {
        $this->_contractNumber = $contractNumber;
        $this->_password = $password;
    }

    public function setClient(SoapClient $client) 
    {
        $this->_client = $client;
    }

    public function generateLabel($params = array()) 
    {
        $request = array(
            'contractNumber' => $this->_contractNumber,
            'password' => $this->_password,
            'outputFormat' => array('outputPrintingType' => 'PDF_A4_300dpi'),
            'letter' => $params
        );

        try {
            $result = $this->_client->generateLabel(array('generateLabelRequest' => $request));
        } catch(SoapFault $e) {
            try {
                $result = array();
                $lastResponse = $this->_client->__getLastResponse();
                if(null !== $lastResponse) {
                    $domDocument = new DomDocument('1.0', 'UTF-8');
                    $domDocument->loadXml(str_replace(array('<![CDATA[', ']]>'), '', $lastResponse));
                    $return = $domDocument->getElementsByTagName('return')->item(0);
                    foreach($return->childNodes as $node) {
                        if($node->hasChildNodes()) {
                            foreach($node->childNodes as $subNode) {
                                $result[$subNode->nodeName] = $subNode->nodeValue;
                            }
                        } else {
                            $result[$node->nodeName] = $node->nodeValue;
                        }
                    }
                }
            } catch(Exception $ex) {
                $result = null;
            }
        }
        return $result;
    }
}
