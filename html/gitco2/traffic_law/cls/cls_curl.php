<?php
include_once CLS."/cls_xml.php";
class cls_curl extends cls_xml{

    public $soapEnv;
    public $soapHeader;
    public $soapBody;

    public $soapAttributes;
    public $soapFile;

    public $curlHeaders;
    public $username;
    public $password;
    public $wsdl;

    public $boundary;
    public $soapType;

    public function __construct(){

        $this->soapAttributes["envelope"] = array(array('field'=>"xmlns:soapenv",'value'=>"http://schemas.xmlsoap.org/soap/envelope/"));
        $this->soapAttributes["header"] = array();
        $this->soapAttributes["body"] = array();
        $this->curlHeaders = array();
    }


    public function setAttributes($type, array $a_attributes = array()){
        for($i=0;$i<count($a_attributes);$i++){
            $this->soapAttributes[$type][] = $a_attributes[$i];
        }
    }

    public function setWsdl($wsdl){
        $this->wsdl = $wsdl;
    }

    public function setCredentials($username, $password){
        $this->username = $username;
        $this->password = $password;
    }

    public function uploadFile(array $a_file){
        $this->soapFile = $a_file;
    }

    public function setSoapEnvelopeXml(){

        $this->createXml(1.0, "UTF-8");

        $this->soapEnv = $this->createElement("soapenv:Envelope",null,$this->soapAttributes['envelope']);
        $this->soapHeader = $this->createElement("soapenv:Header",$this->soapEnv, $this->soapAttributes['header']);
        $this->soapBody = $this->createElement("soapenv:Body",$this->soapEnv,$this->soapAttributes['body']);

    }

    public function startCurl(){
        $this->Curl = curl_init();
    }

    public function setLoginHeader($type){
        if($type=="Basic"){
            $this->curlHeaders[] = "Authorization: Basic ".base64_encode($this->username.":".$this->password);
        }
    }

    public function setPostHeader($type, $location){
        $this->curlHeaders[] = $type.' '.$location.' HTTP/1.1';
    }

    public function setHostHeader($host){
        $this->curlHeaders[] = 'Host: '.$host;
    }

    public function setActionHeader($action){
        $this->curlHeaders[] = 'SOAPAction: '.$action;
    }

    public function setSoapType($type){
        $this->soapType = $type;
        $this->setContentTypeHeader();
        if($type=="SWA"){
            $this->curlHeaders[] = 'Accept-Encoding: gzip,deflate';
            $this->curlHeaders[] = 'MIME-Version: 1.0';
            $this->curlHeaders[] = 'Connection: Keep-Alive';
            $this->curlHeaders[] = 'Expect:';
        }
    }

    public function setBoundary(){
        $this->boundary = "------------".rand(100000000000000000000000, 999999999999999999999999);
    }

    public function setContentTypeHeader(){
        $header = null;
        switch($this->soapType){
            case "xml":
                $header = 'Content-Type: text/xml; charset="UTF-8" ';

                break;

            case "SWA":
                $this->setBoundary();

                $header = 'Content-Type: multipart/related; type="application/xop+xml"; ';
                $header.= 'start-info="text/xml"; start="<gitco@sarida.it>; ';
                $header.= 'boundary="'.$this->boundary.'"';

                break;
        }

        $this->curlHeaders[] = $header;
    }

    public function setCurlOptions(array $a_opt){
        curl_setopt_array($this->Curl, $a_opt);
    }

    public function setRequest($xml){
        $body = '';
        $n = "\r\n";
        if($this->soapType=="SWA"){

            $body .= "--".$this->boundary . $n;
            $body .= 'Content-Type: application/xop+xml; charset=UTF-8; type="text/xml"'. $n;
            $body .= 'Content-Transfer-Encoding: 8bit'. $n;
            $body .= 'Content-ID: <gitco@sarida.it>'. $n;
            $body .= $n.$xml.$n;

            if(is_array($this->soapFile)){
                $body .= "--".$this->boundary . $n;

                $body .= 'Content-Disposition: attachment; filename=' . $this->soapFile['name'] . $n;
                $body .= "Content-Type: " .  $this->soapFile['content_type'] . $n;
                $body .= "Content-Transfer-Encoding: binary". $n;
                $body .= "Content-ID: <".$this->soapFile['name'].">". $n;
                $body .= $n . file_get_contents (  $this->soapFile['path'] ) . $n;
            }

            $body .= "--".$this->boundary."--";
        }
        else if($this->soapType=="xml"){
            $body .= $n.$xml.$n;
        }

        $this->soapRequestField = $body;
    }

    public function richiestaFornituraPec(){
        $this->setSoapType("SWA");
        $this->setWsdl("https://fpecws.infocamere.it/fpec/ServizioFornituraPec?wsdl");
        $this->setAttributes("envelope", array(array('field'=>'xmlns:ws','value'=>"http://ws.fpec.gemo.infocamere.it")));

        $this->setPostHeader("POST","/fpec/ServizioFornituraPec");
        $this->setLoginHeader("Basic");
        $this->setHostHeader("fpecws.infocamere.it");
        $this->setActionHeader("richiestaFornituraPec");

        $xml = $this->richiestaFornituraPec_xml();

        $this->setRequest($xml);
//        $completeRequest = str_replace("<","&lt;",$this->soapRequestField);
//        $completeRequest = str_replace(">","&gt;",$completeRequest);
//        $exp_completeRequest = explode("\r\n",$completeRequest);
//        print_r($exp_completeRequest);

        $a_opt = array(
            CURLOPT_HTTPHEADER => $this->curlHeaders,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_POST => true,
            CURLOPT_URL => $this->wsdl,
            CURLOPT_POSTFIELDS => $this->soapRequestField
        );

        $this->startCurl();
        $this->setCurlOptions($a_opt);

        $response = curl_exec ( $this->Curl );

        $err = curl_error($this->Curl);

        curl_close($this->Curl);

        $responseEcho = str_replace("<","&lt;",$response);
        $responseEcho = str_replace(">","&gt;",$responseEcho);

        $exp_response = explode("\r\n",$responseEcho);
        print_r($exp_response);

    }

    public function richiestaFornituraPec_xml(){

        $n = "\n";
        $xml = '<?xml version="1.0" encoding="UTF-8"?>'. $n;
        $xml.= '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ws="http://ws.fpec.gemo.infocamere.it">'. $n;
        $xml.= '<soapenv:Header/>'. $n;
        $xml.= '<soapenv:Body>'. $n;
        $xml.= '<ws:richiestaRichiestaFornituraPec>'. $n;
        $xml.= '<elencoCf>'. $n;
        $xml.= '<nomeDocumento>'.$this->soapFile['nameNoExt'].'</nomeDocumento>'. $n;
        $xml.= '<tipoDocumento>'.$this->soapFile['extension'].'</tipoDocumento>'. $n;
        $xml.= '<documento><inc:Include href="cid:'.$this->soapFile['name'].'" xmlns:inc="http://www.w3.org/2004/08/xop/include"/></documento>'. $n;
        $xml.= '</elencoCf>'. $n;
        $xml.= '</ws:richiestaRichiestaFornituraPec>'. $n;
        $xml.= '</soapenv:Body>'. $n;
        $xml.= '</soapenv:Envelope>';

        return $xml;
    }

    public function scaricoFornituraPec($numeroFornitura){
        $this->setSoapType("SWA");
        $this->setWsdl("https://fpecws.infocamere.it/fpec/ServizioFornituraPec?wsdl");
        $this->setAttributes("envelope", array(array('field'=>'xmlns:ws','value'=>"http://ws.fpec.gemo.infocamere.it")));

        $this->setPostHeader("POST","/fpec/ServizioFornituraPec");
        $this->setLoginHeader("Basic");
        $this->setHostHeader("fpecws.infocamere.it");
        $this->setActionHeader("scaricoFornituraPec");

        $xml = $this->scaricoFornituraPec_xml($numeroFornitura);

        $this->setRequest($xml);
//        $completeRequest = str_replace("<","&lt;",$this->soapRequestField);
//        $completeRequest = str_replace(">","&gt;",$completeRequest);
//        $exp_completeRequest = explode("\r\n",$completeRequest);
//        print_r($exp_completeRequest);

        $a_opt = array(
            CURLOPT_HTTPHEADER => $this->curlHeaders,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_POST => true,
            CURLOPT_URL => $this->wsdl,
            CURLOPT_POSTFIELDS => $this->soapRequestField
        );

//        print_r($a_opt);
        $this->startCurl();
        $this->setCurlOptions($a_opt);

        $response = curl_exec ( $this->Curl );

        $err = curl_error($this->Curl);

        curl_close($this->Curl);

        $responseEcho = str_replace("<","&lt;",$response);
        $responseEcho = str_replace(">","&gt;",$responseEcho);

        $exp_response = explode("\r\n",$responseEcho);
        print_r($exp_response);

        $exp_response = explode("\r\n",$response);

        $contenteDisposition = explode('"',$exp_response[11]);
        file_put_contents($contenteDisposition[1], $exp_response[13]);
    }

    public function scaricoFornituraPec_xml($numeroFornitura){

        $n = "\n";
        $xml = '<?xml version="1.0" encoding="UTF-8"?>'. $n;
        $xml.= '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ws="http://ws.fpec.gemo.infocamere.it">'. $n;
        $xml.= '<soapenv:Header/>'. $n;
        $xml.= '<soapenv:Body>'. $n;
        $xml.= '<ws:richiestaScaricoFornituraPec>'. $n;
        $xml.= '<tokenRichiestaInfocamere>'. $n;
        $xml.= '<tipoRichiesta>FORNITURA_FPEC</tipoRichiesta>'. $n;
        $xml.= '<idRichiesta>'.$numeroFornitura.'</idRichiesta>'. $n;
        $xml.= '</tokenRichiestaInfocamere>'. $n;
        $xml.= '</ws:richiestaScaricoFornituraPec>'. $n;
        $xml.= '</soapenv:Body>'. $n;
        $xml.= '</soapenv:Envelope>';

        return $xml;
    }


}


?>