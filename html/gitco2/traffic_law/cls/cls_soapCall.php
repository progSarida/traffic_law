<?php
include_once (CLS . "/cls_xml.php");
class cls_soapCall extends cls_xml {

    public $Username;
    public $Password;

    public $soapUrl;
    public $a_soapUrl;

    public $soapActionRoot;
    public $RequestName;
    public $soapAction;

    public $a_reqObject;
    public $xmlResponse;
    public $a_responseKeys;
    public $a_response;

    public $printResponseArray = false;

    public function setDefaultAttributes(){
        $this->xmlAttributes['xmlns:soap']='http://www.w3.org/2003/05/soap-envelope';
    }

    public function setSoapUrlArray($url){
        $mainParts = explode('://', $url);
        $this->a_soapUrl['protocol'] = $mainParts[0];
        $domainPort=explode(':',$mainParts[1]);
        $this->a_soapUrl['port'] = null;
        if(isset($domainPort[1]))
            $this->a_soapUrl['port'] = $domainPort[1];
        $parts = explode('/', $domainPort[0]);
        $this->a_soapUrl['domain'] = $parts[0];
        unset($parts[0]);
        $this->a_soapUrl['path'] = implode('/', $parts);
    }

    public function showRequest(){
        echo "<br><br>".$this->RequestName." REQUEST:<br><br>";
        $this->showXml();
    }

    public function showResponse(){
        echo "<br><br>".$this->RequestName." RESPONSE:<br><br>". htmlentities($this->xmlResponse->getXml());
    }

    public function callRequest($method = "POST", $auth = "Basic"){
        $this->unlockChilkat();
        $http = new CkHttp();
        if (!is_dir(LOG)){
            mkdir(LOG, 0770, true);
            chmod(LOG, 0770);
        }
        $http->put_SessionLogFilename(LOG."/servizi.log");
        chmod(LOG."/servizi.log", 0770);
        $http->put_FollowRedirects(true);

        $req = new CkHttpRequest();
        $req->put_HttpVerb($method);
        $req->put_SendCharset(false);
        $this->setReqHeader($req, $auth);
        $req->put_Path($this->a_soapUrl['path']);
        $req->LoadBodyFromString($this->getXml(),'utf-8');

        $resp = $http->SynchronousRequest($this->a_soapUrl['domain'], $this->setReqPort(), $this->a_soapUrl["protocol"], $req);
        if (!$http->get_LastMethodSuccess()) {
            trigger_error("Errore in chiamata SOAP", E_USER_WARNING);
            trigger_error("Header: ". $http->lastHeader(),E_USER_WARNING);
            trigger_error("XML: ". $this->getXml(),E_USER_WARNING);
            trigger_error("Errore XML: ". $http->lastErrorXml(),E_USER_WARNING);
            return null;
        }
        else {
            $this->xmlResponse = $xmlResponse = new CkXml();
            $this->xmlResponse->LoadXml($resp->bodyStr());
            $this->setResponseArray();
        }
    }

    private function setReqHeader(CkHttpRequest &$req, $auth){
        if($auth!=null)
            $req->AddHeader('Authorization',$this->setReqAuthentication($auth));

        $req->AddHeader('Content-Type','text/xml; charset=utf-8');
        $req->AddHeader('Accept-Encoding', 'gzip, deflate');
        $req->AddHeader('SOAPAction', $this->soapAction);
        $req->AddHeader('Host', $this->a_soapUrl['host']);
        $req->AddHeader('User-Agent', 'PHPSoap');
        $req->AddHeader('Connection', 'Keep-Alive');
    }

    private function setReqPort(){
        if(!is_null($this->a_soapUrl['port']))
            return $this->a_soapUrl['port'];
        else if($this->a_soapUrl["protocol"])
            return 443;
        else
            return 80;
    }

    private function setReqAuthentication($auth){
        switch ($auth){
            case "Basic":
                $strAuth = "Basic ".base64_encode($this->Username.":".$this->Password);
                break;
            default:
                $strAuth = null;
        }
        return $strAuth;
    }

    public function setResponseArray(){
        $this->a_response = $this->getElementsFromArray($this->xmlResponse, $this->a_responseKeys);
    }

    public function setPrintResponseArray($isPrinted){
        $this->printResponseArray = $isPrinted;
    }

    public function getElementsFromArray(CkXml $xml, $a_childs){
        $a_return = array();
        if($this->printResponseArray)
            echo "<br><br>";
        foreach ($a_childs as $key=>$value){
            $a_return[$key] = $xml->getChildContent($value);
            if($this->printResponseArray)
                echo $key.": ".$a_return[$key]."<br>";
        }
        return $a_return;
    }

    private function unlockChilkat(){
        $glob = new CkGlobal();
        return $glob->UnlockBundle(CHILKAT_CODICE_LICENZA);
    }
}


?>