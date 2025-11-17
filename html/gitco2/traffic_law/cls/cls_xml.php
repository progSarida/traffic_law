<?php
include_once (INC . "/chilkat_9_5_0.php");
class cls_xml {

    public $db;

    public $xml;
    public $xmlAttributes;
    public $xmlHeader;
    public $xmlNS = null;


    public function createXml($xmlHeader = true){
        $this->xml = new CkXml();
        $this->xmlHeader = $xmlHeader;
    }

    public function setDbClass(CLS_DB $db){
        $this->db = $db;
    }

    public function addAttributes($a_attributes = array()){
        $this->xmlAttributes = array_merge($this->xmlAttributes, $a_attributes);
        foreach($this->xmlAttributes as $key=>$value)
            $this->xml->AddAttribute($key, $value);
    }

    public function showXml(){
        echo htmlentities($this->xml->getXml());
    }

    public function getXml(){
        return $this->xml->getXml();
    }

    public function setXmlHeader(){
        $this->xml->put_EmitXmlDecl($this->xmlHeader);
    }

    public function createSoapXml( $a_attributes = array(), $a_header = array(), $a_body = array()){
        $this->setXmlHeader();
        $this->addTag("soap:Envelope");
        $this->addAttributes($a_attributes);
        $this->addNodes("soap:Header", $a_header);
        $this->addNodes("soap:Body", $a_body);
    }

    public function addTag($tag){
        $this->xml->put_Tag($tag);
    }

    public function addNodes($tag, $a_data){
        $this->addNodesFromArray(array($tag=>$a_data));
    }

    public function addNodesFromArray($a_data, $a_tags = array(), $level = 0){
        foreach ($a_data as $key=>$val){
            if($this->xmlNS!=null && strpos($key,"soap:")===false)
                $key = $this->xmlNS.":".$key;
            if(is_array($val)){
                $a_tags[$level] = $key;
                $this->addNodesFromArray($val, $a_tags, $level+1);
            }
            else{
                $tags = "";
                for ($i=0; $i<$level; $i++){
                    if($tags!="")
                        $tags.="|";
                    $tags.= $a_tags[$i];
                }
                $this->xml->NewChild($tags."|".$key, $val);
            }
        }
    }
}


?>