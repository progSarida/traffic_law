<?php
ini_set('soap.wsdl_cache_enabled',0);
ini_set('soap.wsdl_cache_ttl',0);

class cls_ws_mctc{

    public $xmlnsUrl = "http://www.w3.org/2000/xmlns/";
    public $envelopeUrl = "http://schemas.xmlsoap.org/soap/envelope/";
    public $infoUrl = "http://www.dtt.it/xsd/INFOWS";
    public $wsseUrl = 'http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd';
    public $wsuUrl = 'http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd';

    public $password_type = 'http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-username-token-profile-1.0#PasswordText';

    public $wsdl;
    public $location;

    public $client;
    public $service;
    public $serviceFolder;
    public $lastRequest;
    public $lastResponse;

    private $wsdlUrl = "https://www.ilportaledellautomobilista.it/Info-ws";

    private $ambient;



    private $wsdlOptions = array();
    private $parameters = array();

    private $attributeWss = array();
    private $attributeUserToken = array();
    private $attributePassword = array();

    private $wsuID = "XWSSGID-1253605895203984534550";

    private $username = 'PRFR000134';
    private $password = '8$1V6C4N';

    private $security;
    private $usernameToken;
    private $xmlUsername;
    private $xmlPassword;

    private $request;


    public function __construct(){

        $this->constructWsdlOptions();
        $this->constructAttributes();
        $this->constructServiceFolder();
        $this->constructParameters();
    }
    private function constructWsdlOptions(){
        $this->wsdlOptions = array(
            'trace' => 1,
            'exceptions'=>0,
            'connection_timeout' => 1,
            'soap_version' => SOAP_1_1,
            'stream_context' => stream_context_create(
                array(
                    'ssl' => array(
                        'verify_peer'       => false,
                        'verify_peer_name'  => false,
                    )
                )
            )
        );
    }
    private function constructAttributes(){
        $this->attributeWss[0] = array('url'=>$this->xmlnsUrl,'field'=>"xmlns:wsse",'value'=>$this->wsseUrl);
        $this->attributeWss[1] = array('field'=>"SOAP-ENV:mustUnderstand",'value'=>1);

        $this->attributeUserToken[0] = array('url'=>$this->xmlnsUrl,'field'=>"xmlns:wsu",'value'=>$this->wsuUrl);
        $this->attributeUserToken[1] = array('field'=>"wsu:id",'value'=>$this->wsuID);

        $this->attributePassword[0] = array('field'=>"Type",'value'=>$this->password_type);
    }

    private function constructServiceFolder(){
        $this->serviceFolder = array(
            'cambioPassword'=>'cambioPassword',
            'dettaglioPatente'=>'dettaglioPatenteBase',



            'WS_GET_CarTrespasser_With_Plate'=>'dettaglioAutoveicoloComproprietari',
            'WS_GET_MotorcycleTrespasser_With_Plate'=>'dettaglioMotoveicoloComproprietari',
            'WS_GET_MopedTrespasser_With_Plate'=>'dettaglioCiclomotoreComproprietari',
            'WS_GET_CarTrespasserOwner_With_Date'=>'estrattoCronologicoProprietariAutoveicolo',
            'WS_GET_CarTrespasserForeign_With_Plate'=>'dettaglioVeicoloEsteroInfrazioni',
            'WS_GET_TruckTrespasser_With_Plate'=>'dettaglioRimorchioComproprietari',


        );
    }

    private function constructParameters(){
        $this->parameters = array(
            'pdf'=>'false',
            'anteprimaPdf'=>'false'
        );
    }

    public function setAmbient($ambient){
        $this->ambient = $ambient;
    }

    public function setLogin($user, $password){
        $this->username = $user;
        $this->password = $password;
    }

    public function setParameters(array $a_parameters){
        foreach($a_parameters as $field=>$value){
            $this->parameters[$field] = $value;
        }
    }

    public function deleteParameters(){
        $this->constructParameters();
    }

    public function setWsuId($ID){
        $this->wsuID = $ID;
    }

    public function setService($service){
        $this->service = $service;

        $this->wsdl = $this->wsdlUrl."/services/".$this->serviceFolder[$this->service] ."/".$this->serviceFolder[$this->service].".wsdl";

    }
    /**
     * @return string
     */
    public function setHeaderXml(){

        $xml = $this->createXml("1.0", "UTF-8");
        $this->security = $this->createElement($xml,"wsse:Security",null);
        $this->setDomAttribute($this->security, $this->attributeWss);

        $this->usernameToken = $this->createElement($xml,"wsse:UsernameToken",$this->security);
        $this->setDomAttribute($this->usernameToken, $this->attributeUserToken);

        $this->xmlUsername = $this->createElement($xml,"wsse:Username",$this->usernameToken);
        $this->setValue($xml,$this->xmlUsername, $this->username);

        $this->xmlPassword = $this->createElement($xml,"wsse:Password",$this->usernameToken);
        $this->setDomAttribute($this->xmlPassword, $this->attributePassword);
        $this->setValue($xml,$this->xmlPassword, $this->password);

        return $xml->saveXML($this->security);
    }

    /**
     * @return bool
     */
    public function soapConnect(){
        $request="";
        if($this->service==null){
            echo 'Impostare il servizio prima di effettuare la soapCall. [setService]';
            return false;
        }
        else{
            switch($this->service){
                case 'cambioPassword':
                    $request = $this->cambioPasswordXml();
                    break;
                case 'dettaglioPatente':
                    $request = $this->patenteRequestXml();
                    break;
                case 'dettaglioPersonaFisicaVeicoli':
                    $request = $this->personaFisicaVeicoliXml();
                    break;
                case 'WS_GET_CarTrespasserForeign_With_Plate':
                    $request = $this->WS_GET_CarTrespasserForeign_With_Plate_Xml();
                    break;
                case 'dettaglioVeicoloEsteroIntestatarioBase':
                    $request = $this->dettaglioVeicoloEsteroIntestatarioXml();

                    break;

                case 'WS_GET_CarTrespasser_With_Plate':
                    $request = $this->WS_GET_CarTrespasser_With_Plate_Xml();
                    break;

                case 'WS_GET_MotorcycleTrespasser_With_Plate':
                    $request = $this->WS_GET_MotorcycleTrespasser_With_Plate_Xml();
                    break;


                case 'WS_GET_MopedTrespasser_With_Plate':
                    $request = $this->WS_GET_MopedTrespasser_With_Plate_Xml();
                    break;

                case 'WS_GET_CarTrespasserOwner_With_Date':
                    $request = $this->WS_GET_CarTrespasserOwner_With_Date_Xml();
                    break;
                case 'WS_GET_TruckTrespasser_With_Plate':
                    $request = $this->WS_GET_TruckTrespasser_With_Plate_Xml();
                    break;




            }
        }
        //echo htmlspecialchars($request);
        $this->request = $request;
        if($this->request===false)
            return false;
        else
            $this->request = true;

        $this->client = new SoapClient($this->wsdl, $this->wsdlOptions);

        $this->client->__setLocation($this->location);



            $headerSoap = new SoapHeader($this->wsseUrl,'Security', new SoapVar($this->setHeaderXml(), XSD_ANYXML));
            $this->client->__setSoapHeaders($headerSoap);




        $this->soapCall($request);

        $a_Response = $this->getResponseArray();






        return $a_Response;
    }

    public function soapCallManuale($wsdl, $location, $service, $request){
        $client = new SoapClient($wsdl, $this->wsdlOptions);
        $client->__setLocation($location);

        $headerSoap = new SoapHeader($this->wsseUrl,'Security', new SoapVar($this->setHeaderXml(), XSD_ANYXML));
        $client->__setSoapHeaders($headerSoap);

        $client->{$service}(new SoapVar( $request, XSD_ANYXML));

        //echo "<br><br>REQUEST:<br>".htmlspecialchars($client->__getLastRequest())."<br><br>RESPONSE:<br>".htmlspecialchars($client->__getLastResponse());

    }

    /**
     * @param $request
     * @return bool
     */
    public function soapCall($request){

        $this->client->{$this->serviceFolder[$this->service]}(new SoapVar( $request, XSD_ANYXML));

        $this->lastRequest = $this->client->__getLastRequest();
        $this->lastResponse = $this->client->__getLastResponse();



        //echo "<br><br>REQUEST:<br>".htmlspecialchars($this->lastRequest)."<br><br>RESPONSE:<br>".htmlspecialchars($this->lastResponse);
        //return true;

        return $this->lastResponse;

    }

    /**
     * @return bool
     */
    public function getResponseArray(){
        if($this->request===false){
            echo 'soapCall fallita! Impossibile avere la risposta.';
            return false;
        }

        if($this->lastResponse==null){
            echo "Nessuna risposta dal web service! lastResponse null. Impossibile creare l'array";
            return false;
        }



        $root = new DOMDocument();
        $root->loadXML($this->lastResponse);
        $xmlArray = $this->xml_to_array($root,'inf');
        return $xmlArray['SOAP-ENV:Envelope']['SOAP-ENV:Body'];
    }

    /**
     * @param $version
     * @param $encoding
     * @param bool $format
     * @return DOMDocument
     */
    public function createXml($version, $encoding, $format=true){

        $xml = new DOMDocument($version, $encoding);
        $xml->formatOutput = $format;

        return $xml;

    }

    /**
     * @param DOMDocument $xml
     * @param $tag
     * @param $parentElement
     * @param null $url
     * @return DOMElement|DOMNode
     */
    public function createElement(DOMDocument $xml, $tag, $parentElement, $url=null){
        if($url==null)
            $element = $xml->createElement($tag);
        else
            $element = $xml->createElementNS( $url , $tag );

        if($parentElement==null)
            $element = $xml->appendChild($element);
        else
            $element = $parentElement->appendChild($element);

        return $element;
    }

    /**
     * @param DOMDocument $xml
     * @param DOMElement $element
     * @param $text
     */
    public function setValue(DOMDocument $xml, DOMElement $element, $text){

        $value = $xml->createTextNode($text);
        $element->appendChild($value);
    }

    /**
     * @param DOMElement $element
     * @param array $a_attribute
     */
    public function setDomAttribute(DOMElement $element, Array $a_attribute){

        foreach ($a_attribute as $array){

            if(isset($array['url']))
                $element->setAttributeNS($array['url'], $array['field'], $array['value']);
            else
                $element->setAttribute($array['field'], $array['value']);
        }

    }

    /**
     * @param $a_options
     * @return bool
     */
    private function checkOptions($a_options){
        $message = "";
        foreach ($a_options as $field=>$value){
            if(!isset($this->parameters[$value]))
                $message.= "Non e' stato impostato l'attributo ".$value." per questo servizio!<br>";
        }
        if($message!=""){
            echo "<br>".$message;
            alert('Impostare le opzioni per il servizio desiderato prima di effettuare la soapCall.');
            return false;
        }
        else
            return true;
    }

    private function headerEsteroNode(DOMDocument $xml, DOMElement $element){

        $inf = "ns1";

        $root = $this->createElement($xml,$inf.":Header", $element);
        $messageId = $this->createElement($xml,$inf.":MessageID",$root);
        $this->setValue($xml, $messageId, 'f9598e2f-f50f-4981-a3c7-b9c6195f8a1b');
        $messageVersion = $this->createElement($xml,$inf.":MessageVersion",$root);
        $this->setValue($xml, $messageVersion, '1.0');
        $ServiceExecutionReason = $this->createElement($xml,$inf.":ServiceExecutionReason",$root);
        $ServiceExecutionReasonCode = $this->createElement($xml,$inf.":ServiceExecutionReasonCode",$ServiceExecutionReason);
        $this->setValue($xml, $ServiceExecutionReasonCode, '0');
        $ServiceExecutionReasonDesc = $this->createElement($xml,$inf.":ServiceExecutionReasonDesc",$ServiceExecutionReason);
        $this->setValue($xml, $ServiceExecutionReasonDesc, 'NOT SPECIFIED');
        $RecipientCountry = $this->createElement($xml,$inf.":RecipientCountry",$root);
        $this->setValue($xml, $RecipientCountry, 'I');
        $SenderCountry = $this->createElement($xml,$inf.":SenderCountry",$root);
        $this->setValue($xml, $SenderCountry, 'I');
        $SenderOrganisation = $this->createElement($xml,$inf.":SenderOrganisation",$root);
        $SenderOrganisationCode = $this->createElement($xml,$inf.":SenderOrganisationCode",$SenderOrganisation);
        $this->setValue($xml, $SenderOrganisationCode, '1');
        $SenderOrganisationDesc = $this->createElement($xml,$inf.":SenderOrganisationDesc",$SenderOrganisation);
        $this->setValue($xml, $SenderOrganisationDesc, 'Registration Office');
        $SenderName = $this->createElement($xml,$inf.":SenderName",$root);
        $this->setValue($xml, $SenderName, 'EUCADG01');
        $TimeStamp = $this->createElement($xml,$inf.":TimeStamp",$root);
        $this->setValue($xml, $TimeStamp, $this->parameters['timeStamp']);
        $TimeOut = $this->createElement($xml,$inf.":TimeOut",$root);
        $this->setValue($xml, $TimeOut, '0');

        return $xml;
    }



    /**
     * @return bool|string
     */
    private function cambioPasswordXml(){
        $a_options = array('newPassword');
        $check = $this->checkOptions($a_options);
        if(!$check)
            return false;

        $sic = "ns1";

        $xml = $this->createXml("1.0", "UTF-8");

        $root = $this->createElement($xml,$sic.":richiestaCambioPassword", null);
        $utente = $this->createElement($xml,$sic.":utente",$root);
        $this->setValue($xml, $utente, $this->username);
        $oldPass = $this->createElement($xml,$sic.":vecchiaPassword",$root);
        $this->setValue($xml,$oldPass, $this->password);
        $newPass = $this->createElement($xml,$sic.":nuovaPassword",$root);
        $this->setValue($xml,$newPass, $this->parameters['newPassword']);
        $confirmNewPass = $this->createElement($xml,$sic.":confermaNuovaPassword",$root);
        $this->setValue($xml,$confirmNewPass, $this->parameters['newPassword']);

        return $xml->saveXML($root);
    }

    /**
     * @return bool|string
     */
    private function patenteRequestXml()
    {
        $a_options = array('numeroPatente');
        $check = $this->checkOptions($a_options);
        if(!$check)
            return false;

        $inf = "ns1";

        $xml = $this->createXml("1.0", "UTF-8");
        $root = $this->createElement($xml, $inf . ":dettaglioPatenteRequest", null);
        $loginRoot = $this->createElement($xml, $inf . ":login", $root);

        if(isset($this->parameters['pin'])){
            $codicePinRoot = $this->createElement($xml, $inf . ":codicePin", $loginRoot);
            $this->setValue($xml, $codicePinRoot, $this->parameters['pin']);
        }

        $ambitoRoot = $this->createElement($xml, $inf . ":ambitoPatenteBase", $root);
        $patente = $this->createElement($xml, $inf . ":patente", $ambitoRoot);
        $numeroPatente = $this->createElement($xml, $inf . ":numeroPatente", $patente);
        $this->setValue($xml, $numeroPatente, $this->parameters['numeroPatente']);

        $pdfRoot = $this->createElement($xml, $inf . ":pdf", $root);
        $this->setValue($xml, $pdfRoot, $this->parameters['pdf']);
        $anteprimaPdfRoot = $this->createElement($xml, $inf . ":pdfAnteprimaPatente", $root);
        $this->setValue($xml, $anteprimaPdfRoot, $this->parameters['anteprimaPdf']);

        return $xml->saveXML($root);
    }




    /**
     * @return bool|string
     */
    private function WS_GET_TruckTrespasser_With_Plate_Xml()
    {
        $a_options = array('numeroTarga');
        $check = $this->checkOptions($a_options);
        if(!$check)
            return false;

        $inf = "ns1";

        $xml = $this->createXml("1.0", "UTF-8");
        $root = $this->createElement($xml, $inf . ":dettaglioRimorchioComproprietariRequest", null);
        $loginRoot = $this->createElement($xml, $inf . ":login", $root);

        if(isset($this->parameters['pin'])){
            $codicePinRoot = $this->createElement($xml, $inf . ":codicePin", $loginRoot);
            $this->setValue($xml, $codicePinRoot, $this->parameters['pin']);
        }

        $ambitoRoot = $this->createElement($xml, $inf . ":dettaglioRimorchioBaseInput", $root);
        $targa = $this->createElement($xml, $inf . ":targa", $ambitoRoot);
        $numeroTarga = $this->createElement($xml, $inf . ":numeroTarga", $targa);
        $this->setValue($xml, $numeroTarga, $this->parameters['numeroTarga']);



        $pdfRoot = $this->createElement($xml, $inf . ":pdf", $root);
        $this->setValue($xml, $pdfRoot, $this->parameters['pdf']);

        return $xml->saveXML($root);


    }



    /**
     * @return bool|string
     */
    private function WS_GET_CarTrespasserOwner_With_Date_Xml()
    {
        $a_options = array('numeroTarga','situazioneAl');
        $check = $this->checkOptions($a_options);
        if(!$check)
            return false;

        $inf = "ns1";

        $xml = $this->createXml("1.0", "UTF-8");
        $root = $this->createElement($xml, $inf . ":estrattoCronologicoProprietariAutoveicoloRequest", null);
        $loginRoot = $this->createElement($xml, $inf . ":login", $root);

        if(isset($this->parameters['pin'])){
            $codicePinRoot = $this->createElement($xml, $inf . ":codicePin", $loginRoot);
            $this->setValue($xml, $codicePinRoot, $this->parameters['pin']);
        }

        $ambitoRoot = $this->createElement($xml, $inf . ":estrattoCronologicoProprietariAutoveicoloBaseInput", $root);
        $targa = $this->createElement($xml, $inf . ":targa", $ambitoRoot);
        $numeroTarga = $this->createElement($xml, $inf . ":numeroTarga", $targa);
        $this->setValue($xml, $numeroTarga, $this->parameters['numeroTarga']);

        $situazioneAl = $this->createElement($xml, $inf . ":situazioneAl", $ambitoRoot);
        $this->setValue($xml, $situazioneAl, $this->parameters['situazioneAl']);




        $pdfRoot = $this->createElement($xml, $inf . ":pdf", $root);
        $this->setValue($xml, $pdfRoot, $this->parameters['pdf']);

        return $xml->saveXML($root);


    }
    /**
     * @return bool|string
     */
    private function WS_GET_CarTrespasser_With_Plate_Xml()
    {
        $a_options = array('numeroTarga');
        $check = $this->checkOptions($a_options);
        if(!$check)
            return false;

        $inf = "ns1";

        $xml = $this->createXml("1.0", "UTF-8");
        $root = $this->createElement($xml, $inf . ":dettaglioAutoveicoloComproprietariRequest", null);
        $loginRoot = $this->createElement($xml, $inf . ":login", $root);

        if(isset($this->parameters['pin'])){
            $codicePinRoot = $this->createElement($xml, $inf . ":codicePin", $loginRoot);
            $this->setValue($xml, $codicePinRoot, $this->parameters['pin']);
        }

        $ambitoRoot = $this->createElement($xml, $inf . ":dettaglioAutoveicoloBaseInput", $root);
        $targa = $this->createElement($xml, $inf . ":targa", $ambitoRoot);
        $numeroTarga = $this->createElement($xml, $inf . ":numeroTarga", $targa);
        $this->setValue($xml, $numeroTarga, $this->parameters['numeroTarga']);



        $pdfRoot = $this->createElement($xml, $inf . ":pdf", $root);
        $this->setValue($xml, $pdfRoot, $this->parameters['pdf']);

        return $xml->saveXML($root);


    }
    /**
     * @return bool|string
     */
    private function WS_GET_MotorcycleTrespasser_With_Plate_Xml()
    {
        $a_options = array('numeroTarga');
        $check = $this->checkOptions($a_options);
        if(!$check)
            return false;

        $inf = "ns1";

        $xml = $this->createXml("1.0", "UTF-8");
        $root = $this->createElement($xml, $inf . ":dettaglioMotoveicoloComproprietariRequest", null);
        $loginRoot = $this->createElement($xml, $inf . ":login", $root);

        if(isset($this->parameters['pin'])){
            $codicePinRoot = $this->createElement($xml, $inf . ":codicePin", $loginRoot);
            $this->setValue($xml, $codicePinRoot, $this->parameters['pin']);
        }

        $ambitoRoot = $this->createElement($xml, $inf . ":dettaglioMotoveicoloBaseInput", $root);
        $targa = $this->createElement($xml, $inf . ":targa", $ambitoRoot);
        $numeroTarga = $this->createElement($xml, $inf . ":numeroTarga", $targa);
        $this->setValue($xml, $numeroTarga, $this->parameters['numeroTarga']);

        $pdfRoot = $this->createElement($xml, $inf . ":pdf", $root);
        $this->setValue($xml, $pdfRoot, $this->parameters['pdf']);

        return $xml->saveXML($root);


    }


    /**
     * @return bool|string
     */
    private function WS_GET_MopedTrespasser_With_Plate_Xml()
    {
        $a_options = array('numeroTarga');
        $check = $this->checkOptions($a_options);
        if(!$check)
            return false;

        $inf = "ns1";

        $xml = $this->createXml("1.0", "UTF-8");
        $root = $this->createElement($xml, $inf . ":dettaglioCiclomotoreComproprietariRequest", null);
        $loginRoot = $this->createElement($xml, $inf . ":login", $root);

        if(isset($this->parameters['pin'])){
            $codicePinRoot = $this->createElement($xml, $inf . ":codicePin", $loginRoot);
            $this->setValue($xml, $codicePinRoot, $this->parameters['pin']);
        }

        $ambitoRoot = $this->createElement($xml, $inf . ":dettaglioCiclomotoreBaseInput", $root);
        $targa = $this->createElement($xml, $inf . ":targa", $ambitoRoot);
        $numeroTarga = $this->createElement($xml, $inf . ":numeroTarga", $targa);
        $this->setValue($xml, $numeroTarga, $this->parameters['numeroTarga']);




               $pdfRoot = $this->createElement($xml, $inf . ":pdf", $root);
               $this->setValue($xml, $pdfRoot, $this->parameters['pdf']);

               return $xml->saveXML($root);


           }



           /**
            * @return bool|string
            */
    private function personaFisicaVeicoliXml()
    {
        $a_options = array('codiceFiscale');
        $check = $this->checkOptions($a_options);
        if(!$check)
            return false;

        $inf = "ns1";

        $xml = $this->createXml("1.0", "UTF-8");
        $root = $this->createElement($xml, $inf . ":dettaglioPersonaFisicaVeicoliRequest", null);
        $loginRoot = $this->createElement($xml, $inf . ":login", $root);

        if(isset($this->parameters['pin'])){
            $codicePinLogin = $this->createElement($xml, $inf . ":codicePin", $loginRoot);
            $this->setValue($xml, $codicePinLogin, $this->parameters['pin']);
        }

        $ambitoRoot = $this->createElement($xml, $inf . ":ambitoPersonaFisicaVeicoli", $root);
        $codFiscale = $this->createElement($xml, $inf . ":codiceFiscale", $ambitoRoot);
        $this->setValue($xml, $codFiscale, $this->parameters['codiceFiscale']);

        $pdfRoot = $this->createElement($xml, $inf . ":pdf", $root);
        $this->setValue($xml, $pdfRoot, $this->parameters['pdf']);

        return $xml->saveXML($root);
    }

    /**
     * @return bool|string
     */
    private function WS_GET_CarTrespasserForeign_With_Plate_Xml()
    {
        $a_options = array('statoEstero','primaParteTarga','dataInfrazione','tipoInfrazione');
        $check = $this->checkOptions($a_options);
        if(!$check)
            return false;

        $inf = "ns1";

        $xml = $this->createXml("1.0", "UTF-8");




        $root = $this->createElement($xml, $inf . ":dettaglioVeicoloEsteroInfrazioneRequest", null);
        $loginRoot = $this->createElement($xml, $inf . ":login", $root);

        if(isset($this->parameters['pin'])){
            $codicePinRoot = $this->createElement($xml, $inf . ":codicePin", $loginRoot);
            $this->setValue($xml, $codicePinRoot, $this->parameters['pin']);
        }

        $statoEstero = $this->createElement($xml, $inf . ":statoEstero", $root);
        $this->setValue($xml, $statoEstero, $this->parameters['statoEstero']);
        $primaParteTarga = $this->createElement($xml, $inf . ":primaParteTarga", $root);
        $this->setValue($xml, $primaParteTarga, $this->parameters['primaParteTarga']);
        $dataInfrazione = $this->createElement($xml, $inf . ":dataInfrazione", $root);
        $this->setValue($xml, $dataInfrazione, $this->parameters['dataInfrazione']);
        $tipoInfrazione = $this->createElement($xml, $inf . ":tipoInfrazione", $root);
        $this->setValue($xml, $tipoInfrazione, $this->parameters['tipoInfrazione']);



        $pdfRoot = $this->createElement($xml, $inf . ":pdf", $root);
        $this->setValue($xml, $pdfRoot, $this->parameters['pdf']);

        return $xml->saveXML($root);

    }

    /**
     * @return bool|string
     */
    private function dettaglioVeicoloEsteroIntestatarioXml()
    {
        $a_options = array('siglaStato','numeroTarga','timeStamp');
        $check = $this->checkOptions($a_options);
        if(!$check)
            return false;

        $inf = "ns1";

        $xml = $this->createXml("1.0", "UTF-8");
        $root = $this->createElement($xml, $inf . ":dettaglioVeicoloEsteroIntestatarioRequest", null);
        $loginRoot = $this->createElement($xml, $inf . ":login", $root);

        if(isset($this->parameters['pin'])){
            $codicePinLogin = $this->createElement($xml, $inf . ":codicePin", $loginRoot);
            $this->setValue($xml, $codicePinLogin, $this->parameters['pin']);
        }

        $ambitoRoot = $this->createElement($xml, $inf . ":ambitoVeicoloEsteroIntestatarioBase", $root);
        $telaio = $this->createElement($xml, $inf . ":telaio", $ambitoRoot);
        $VehOwnerHolderByChassisAndDate = $this->createElement($xml, $inf . ":VehOwnerHolderByChassisAndDate", $telaio);

        $xml = $this->headerEsteroNode($xml, $VehOwnerHolderByChassisAndDate);
        $body = $this->createElement($xml, $inf . ":Body", $VehOwnerHolderByChassisAndDate);
        $request = $this->createElement($xml, $inf . ":Request", $body);
        $VehCountryReq = $this->createElement($xml, $inf . ":VehCountryReq", $request);
        $this->setValue($xml, $VehCountryReq,  $this->parameters['siglaStato']);
        $VehIdentificationNumberReq = $this->createElement($xml, $inf . ":VehIdentificationNumberReq", $request);
        $this->setValue($xml, $VehIdentificationNumberReq, $this->parameters['numeroTelaio']);
        $ReferenceDateTimeReq = $this->createElement($xml, $inf . ":ReferenceDateTimeReq", $request);
        $this->setValue($xml, $ReferenceDateTimeReq, $this->parameters['timeStamp']);

        $pdfRoot = $this->createElement($xml, $inf . ":pdf", $root);
        $this->setValue($xml, $pdfRoot, $this->parameters['pdf']);
        $indiceListaSinonimia = $this->createElement($xml, $inf . ":indiceListaSinonimia", $root);
        $this->setValue($xml, $indiceListaSinonimia, '0');
        return $xml->saveXML($root);
    }

    /**
     * @param DOMNode $root
     * @param null $tag
     * @return array|string
     */
    public function xml_to_array( DOMNode $root, $tag = null) {
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
                if($tag!=null){
                    $explode = explode($tag.':',$child->nodeName);
                    $nodeName = $explode[count($explode)-1];
                }
                else
                    $nodeName = $child->nodeName;

                if (!isset($result[$nodeName])) {
                    $result[$nodeName] = $this->xml_to_array($child,$tag);
                } else {
                    if (!isset($groups[$child->nodeName])) {
                        $result[$nodeName] = array($result[$nodeName]);
                        $groups[$nodeName] = 1;
                    }
                    $result[$nodeName][] = $this->xml_to_array($child,$tag);
                }
            }
        }

        return $result;
    }

}

/**
 *

utenza CMGE002101​ di esercizio


/Info-ws/services/datiAntiInquinamentoAutoveicolo
/Info-ws/services/datiAntiInquinamentoCiclomotore
/Info-ws/services/datiAntiInquinamentoMotoveicolo
/Info-ws/services/datiCartaCircolazioneAutoveicoloProprietario
/Info-ws/services/datiCartaCircolazioneMotoveicoloProprietario
/Info-ws/services/datiCartaCircolazioneRimorchioProprietario
/Info-ws/services/datiRevisioneCiclomotore
 *
/Info-ws/services/dettaglioAutoveicoloComproprietari
 *
/Info-ws/services/dettaglioCartaCircolazioneAutoveicolo
/Info-ws/services/dettaglioCartaCircolazioneCiclomotoreProprietario
/Info-ws/services/dettaglioCartaCircolazioneMacchinaAgricola
/Info-ws/services/dettaglioCartaCircolazioneMotoveicolo
/Info-ws/services/dettaglioCartaCircolazioneRimorchio
/Info-ws/services/dettaglioCartaDiCircolazioneCiclomotore
/Info-ws/services/dettaglioCiclomotoreComproprietari
/Info-ws/services/dettaglioCiclomotoreContrassegnato
/Info-ws/services/dettaglioMacchinaAgricolaComproprietari
 *
/Info-ws/services/dettaglioMotoveicoloComproprietari
 *
/Info-ws/services/dettaglioOmologazione
 *
/Info-ws/services/dettaglioPersonaFisicaVeicoli
 *
/Info-ws/services/dettaglioPersonaGiuridicaListaVeicoliCompleta
 *
/Info-ws/services/dettaglioPersonaGiuridicaListaVeicoliRidotta
 *
/Info-ws/services/dettaglioRimorchioComproprietari
/Info-ws/services/dettaglioTrasportoMerciContoTerziSintetico
 *
/Info-ws/services/dettaglioVeicoloEsteroBase
 *
/Info-ws/services/dettaglioVeicoloEsteroInfrazioni
 *


utenza PRFR000134 di collaudo può eseguire i seguenti:

/Info-ws/services/cronologiaEventiMacchinaOperatrice
/Info-ws/services/datiAntiInquinamentoAutoveicolo
/Info-ws/services/datiAntiInquinamentoCiclomotore
/Info-ws/services/datiAntiInquinamentoMotoveicolo
/Info-ws/services/datiCartaCircolazioneAutoveicoloProprietario
/Info-ws/services/datiCartaCircolazioneMotoveicoloProprietario
/Info-ws/services/datiCartaCircolazioneRimorchioProprietario
/Info-ws/services/datiRevisioneCiclomotore
/Info-ws/services/denominazioneCommercialeVeicoloBase
/Info-ws/services/dettaglioAutoveicoloComproprietari
/Info-ws/services/dettaglioAutoveicoloComproprietariTrasferimentiRes
/Info-ws/services/dettaglioCAP
/Info-ws/services/dettaglioCartaCircolazioneAutoveicolo
/Info-ws/services/dettaglioCartaCircolazioneCiclomotoreProprietario
/Info-ws/services/dettaglioCartaCircolazioneMacchinaAgricola
/Info-ws/services/dettaglioCartaCircolazioneMotoveicolo
/Info-ws/services/dettaglioCartaCircolazioneRimorchio
/Info-ws/services/dettaglioCartaDiCircolazioneCiclomotore
/Info-ws/services/dettaglioCertificatoADRAutoveicolo
/Info-ws/services/dettaglioCertificatoADRMotoveicolo
/Info-ws/services/dettaglioCertificatoADRRimorchio
/Info-ws/services/dettaglioCertificatoATP
/Info-ws/services/dettaglioCFP
/Info-ws/services/dettaglioCiclomotoreComproprietari
/Info-ws/services/dettaglioCiclomotoreConComproprietariTrasferimentiRes
/Info-ws/services/dettaglioCiclomotoreContrassegnato
/Info-ws/services/dettaglioCIGAdattamentiEOstativi
/Info-ws/services/dettaglioCIGBase
/Info-ws/services/dettaglioCQCBase
/Info-ws/services/dettaglioCQCOstativi
/Info-ws/services/dettaglioMacchinaAgricolaBase
/Info-ws/services/dettaglioMacchinaAgricolaComproprietari
/Info-ws/services/dettaglioMacchinaAgricolaConComproprietariETrasferimentiRes
/Info-ws/services/dettaglioMacchinaOperatrice
/Info-ws/services/dettaglioMotoveicoloComproprietari
/Info-ws/services/dettaglioMotoveicoloComproprietariTrasferimentiRes
/Info-ws/services/dettaglioOmologazione
/Info-ws/services/dettaglioPatenteAdattamentiEOstativi
/Info-ws/services/dettaglioPatenteBase
/Info-ws/services/dettaglioPatenteDatiDuplicabilita
/Info-ws/services/dettaglioPatenteEsteraBase
/Info-ws/services/dettaglioPDS
/Info-ws/services/dettaglioPersonaFisicaAbilitazioni
 *
/Info-ws/services/dettaglioPersonaFisicaVeicoli
 *
/Info-ws/services/dettaglioPersonaFisicaVeicoliAbilitazioniDuplicabilitaApprofondita
/Info-ws/services/dettaglioPersonaFisicaVeicoliAbilitazioniDuplicabilitaEssenziale
 *
/Info-ws/services/dettaglioPersonaGiuridicaListaVeicoliCompleta
 *
/Info-ws/services/dettaglioPersonaGiuridicaListaVeicoliRidotta
 *
/Info-ws/services/dettaglioRimorchioBase
/Info-ws/services/dettaglioRimorchioComproprietari
/Info-ws/services/dettaglioRimorchioEsteso
/Info-ws/services/dettaglioTargaProva
/Info-ws/services/dettaglioTrasportoMerciContoProprio
/Info-ws/services/dettaglioTrasportoMerciContoTerzi
/Info-ws/services/dettaglioTrasportoMerciContoTerziEsteso
/Info-ws/services/dettaglioTrasportoMerciContoTerziSintetico
/Info-ws/services/dettaglioTrasportoPersone
 *
/Info-ws/services/dettaglioVeicoloEsteroBase
 *
/Info-ws/services/dettaglioVeicoloEsteroInfrazioni
 *
/Info-ws/services/dettaglioVeicoloEsteroIntestatarioBase
/Info-ws/services/dettaglioVisualizzazioneCoc
/Info-ws/services/elencoTargaProvaImpresa
/Info-ws/services/esameCIG
/Info-ws/services/esameCQC
/Info-ws/services/esamePatente
/Info-ws/services/estrattoCronologicoProprietariAutoveicolo
/Info-ws/services/estrattoCronologicoProprietariCiclomotore
/Info-ws/services/estrattoCronologicoProprietariMacchinaAgricola
/Info-ws/services/estrattoCronologicoProprietariMacchinaOperatrice
/Info-ws/services/estrattoCronologicoProprietariMotoveicolo
/Info-ws/services/estrattoCronologicoProprietariRimorchio
/Info-ws/services/listaInfrazioniCAP
/Info-ws/services/listaInfrazioniCIG
/Info-ws/services/listaInfrazioniCQC
/Info-ws/services/listaInfrazioniPatenteItaliana
/Info-ws/services/storicoEventiAutoveicolo
/Info-ws/services/storicoEventiCiclomotore
/Info-ws/services/storicoEventiMacchinaAgricola
/Info-ws/services/storicoEventiMotoveicolo
/Info-ws/services/storicoEventiRimorchio
/Info-ws/services/storicoEventiTargaCiclomotore
/Info-ws/services/storicoEventiTargaProva
/Info-ws/services/verificaCoperturaAssicurativaDatiAnagrafici
/Info-ws/services/verificaCoperturaAssicurativaScadenzaRevisione
/Info-ws/services/verificaDuplicabilitaCartaCircolazioneAutoveicolo
/Info-ws/services/verificaDuplicabilitaCartaCircolazioneMotoveicolo
/Info-ws/services/verificaDuplicabilitaPatenteItaliana
/Info-ws/services/verificaValiditaCAP/
/Info-ws/services/verificaValiditaCFP
/Info-ws/services/verificaValiditaCIG
/Info-ws/services/verificaValiditaCQC
/Info-ws/services/verificaValiditaPatente
/Info-ws/services/verificaValiditaPatenteCartaQualificazione
/Info-ws/services/verificaValiditaPatenteCategoria
/Info-ws/services/verificaValiditaPatenteEni
/Info-ws/services/verificaValiditaPDS
/Info-ws-sh/services/informazioniVeicoloECall


 */