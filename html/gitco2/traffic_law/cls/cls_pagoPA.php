<?php
include_once (CLS . "/cls_soapCall.php");
class cls_pagoPA extends cls_soapCall {

    public $ServiceId;
    public $CityId;
    public $Production;

    public $Username;
    public $Password;

    public $soapMethod;
    public $soapAuth;

    public $soapUrl;
    public $soapUrlAdd;
    public $soapUrlUpdate;
    public $soapUrlStatus;

    public $a_soapUrl;

    public $soapActionRoot;
    public $soapAction;
    public $a_soapAction;
    public $RequestName;

    public $a_reqObject;
    public $a_responseKeys;

    public function __construct($cityId, $serviceId, $db = null, $production){
        $this->CityId = $cityId;
        $this->ServiceId = $serviceId;
        if($production===true)
            $this->Production = 1;
        else
            $this->Production = 0;
        if($db!=null)
            $this->setDbClass($db);

        $this->setServiceDataFromDB();
    }

    public function getIUVs($params){
        foreach ($params['Payment'] as $key=>$value){
            $a_iuv[] = $this->newIUV($key, $params);
        }
        return $a_iuv;
    }



    public function callWS($key, $params, $actionType){
        $this->setRequest($actionType, $this->setServiceParams($key, $params, $actionType));
        $this->callRequest($this->soapMethod,$this->soapAuth);
        $this->showRequest();
        $this->showResponse();
    }

    public function newIUV($key, $params){
        $this->callWS($key,$params,"create");
        return $this->a_response['IUV'];
    }

    public function updateIUV($key, $params){
        $this->callWS($key,$params,"update");
        return $this->a_response['IUV'];
    }

    public function readIUV($key, $params){
        $this->callWS($key,$params,"read");
        return $this->a_response;
    }

    public function deleteIUV($key, $params){
        $this->callWS($key,$params,"delete");
        return $this->a_response;
    }

    public function setServiceParams($key, $params, $actionType){
        switch($this->ServiceId){
            case 11:    return $this->setSiscomParams($key, $params, $actionType);
        }
    }

    private function setSpecificSoapUrl($actionType){
        if($this->a_soapAction[$actionType]['SoapUrl']!=null){
            $this->soapUrl = $this->a_soapAction[$actionType]['SoapUrl'];
            $this->setSoapUrlArray($this->soapUrl);
        }
        if($this->a_soapAction[$actionType]['SoapHost']!=null){
            $this->a_soapUrl['host'] = $this->a_soapAction[$actionType]['SoapHost'];
        }
    }

    public function setRequest($actionType, $params = array(), $a_attributes = array()){
        $this->RequestName = $this->a_soapAction[$actionType]['Name'];
        $this->soapAction = $this->a_soapAction[$actionType]['Url'];
        $this->setSpecificSoapUrl($actionType);

        $this->createXml();
        $this->setDefaultAttributes();
        $this->setResponseKeys();

        $request = $this->RequestName;
        $this->$request($params);

        $this->createSoapXml($a_attributes, null, $this->a_reqObject);
    }

    public function setParams($params, $a_reqObject = null){
        if($a_reqObject==null)
            $a_reqObject = $this->a_reqObject;
        foreach ($a_reqObject as $key=>$val){
            if(is_array($val)){
                $a_reqObject[$key] = $this->setParams($params, $val);
            }
            else if(isset($params[$key])){
                $a_reqObject[$key] = $params[$key];
            }
        }

        return $a_reqObject;
    }

    public function setResponseKeys(){
        $root = "soap:Body|".$this->RequestName."Response|".$this->RequestName."Result|";

        $this->a_responseKeys['IUV'] = $root."IUV";
        $this->a_responseKeys['FineId'] = $root."ProceduraChiave";
        $this->a_responseKeys['StatoIuv'] = $root."SiscStato";
        $this->a_responseKeys['Importo'] = $root."PagamentoImporto";
        $this->a_responseKeys['Causale'] = $root."PagamentoCausale";

        $this->a_responseKeys['RagSocCognome'] = $root."DebitRagSocCognome";
        $this->a_responseKeys['Nome'] = $root."DebitNome";
        $this->a_responseKeys['CodFiscale'] = $root."DebitCodFiscale";
        $this->a_responseKeys['PartitaIva'] = $root."DebitPartitaIva";

        $this->a_responseKeys['DataEmissione'] = $root."DataEmissione";
        $this->a_responseKeys['DataScadenza'] = $root."DataScadenza";
        $this->a_responseKeys['DataScadStampa'] = $root."DataScadStampa";
    }


    /**
     * GET DATA FROM DB
     */
    private function setServiceDataFromDB()
    {
        $this->setCredentials();
        $this->setSoapUrl();
        $this->setSoapActions();
        $this->setSoapNS();
    }

    private function setCredentials(){
        $query = "SELECT * FROM CustomerService WHERE CityId='".$this->CityId."' AND ServiceId=".$this->ServiceId;
        $a_credentials = $this->db->getArrayLine($this->db->ExecuteQuery($query));
        if($a_credentials!=null){
            $this->Username = $a_credentials['UserName'];
            $this->Password = $a_credentials['Password'];
        }
    }

    private function setSoapUrl(){
        $query = "SELECT * FROM SoapService WHERE ServiceId=".$this->ServiceId." AND EnvType=".$this->Production;
        $a_soapService = $this->db->getArrayLine($this->db->ExecuteQuery($query));
        if($a_soapService!=null){
            $this->soapUrl = $a_soapService['SoapUrl'];
            $this->setSoapUrlArray($this->soapUrl);
            $this->a_soapUrl['host'] = $a_soapService['SoapHost'];
            $this->soapMethod = $a_soapService['Method'];
            $this->soapAuth = $a_soapService['AuthenticationType'];
        }
    }

    private function setSoapActions(){
        $query = "SELECT * FROM SoapAction WHERE ServiceId=".$this->ServiceId." AND EnvType=".$this->Production;
        $a_soapActions = $this->db->getResults($this->db->ExecuteQuery($query));
        if($a_soapActions!=null){
            foreach ($a_soapActions as $key=>$a_soapAction){
                $this->a_soapAction[$a_soapAction['Type']] = $a_soapAction;
            }
        }
    }

    private function setSoapNS(){
        $query = "SELECT * FROM SoapNameSpace WHERE ServiceId=".$this->ServiceId;
        $a_soapNS = $this->db->getArrayLine($this->db->ExecuteQuery($query));
        if($a_soapNS!=null){
            $this->xmlNS = $a_soapNS['Name'];
            $this->xmlAttributes['xmlns:'.$this->xmlNS] = $a_soapNS['Url'];
        }
    }


    /**
     *  SISCOM
     */
    public function GeneraNuovoMovimentoIUV($params){
        $this->setSiscomDefaultObject($this->RequestName);
        if(count($params)>0)
            $this->a_reqObject = $this->setParams($params);
    }

    public function GeneraNuovoMovimentoIUVconTipologie($params){

        $this->setSiscomDefaultObject($this->RequestName);
        $this->a_reqObject[$this->RequestName]["MovimentoRemotoDett"] = array();

        for($cont=0;$cont<count($extraParams);$cont++){
            $key = "MovimentoRemotoDett[".$cont."]";
            $this->a_reqObject[$this->RequestName]["MovimentoRemotoDett"][$key] = array(
                "IDMovimentoRemotoDett" => 0,
                "IDMovimentoRemoto" => 0,
                "RifEnte" => "12345678901",
                "IUV" => "",
                "ProceduraCodTipo" => "Tax",
                "PagamentoCausale" => "Dettaglio Tassazione",
                "PagamentoImporto" => null,//OBBLIGATORIO

                //DATI CONTABILI (Si possono settare a "O")
                "ContVoce" => "0",
                "ContCapitolo" => "0",
                "ContArticolo" => "0",
                "ContAnnoAccert" => "0",
                "ContNumAccert" => "0",
                "ContNumSubAccert" => "0",

                "ContTipoContabilita" => "9",//se ne occupa il WS
                "ContCodiceContabilita" => null,//se ne occupa il WS
            );

            $this->a_reqObject[$this->RequestName]["MovimentoRemotoDett"][$key] = $this->setParams($extraParams, $this->a_reqObject[$this->RequestName]["MovimentoRemotoDett"][$key]);
        }
    }

    public function ModificaMovimentoIUV($params){
        $this->setSiscomDefaultObject($this->RequestName);
        if(count($params)>0)
            $this->a_reqObject = $this->setParams($params);
    }

    public function LeggiStatoMovimentoIUV($params){

        $this->a_reqObject[$this->RequestName] = array(
            "pCodFiscaleEnte" => null,//Codice fiscale ente OBBLIGATORIO
            "IDMovimentoRemoto" => 0,
            "pIUV" => null,//IUV da leggere
            "pProceduraNome" => "PagoPASiscom",
            "pProceduraServizio" => "traffic_law",
            "pProceduraChiave" => null//Chiave da leggere (FineID)
        );

        if(count($params)>0)
            $this->a_reqObject = $this->setParams($params);
    }

    public function AnnullaMovimentoIUV($params){

        $this->a_reqObject[$this->RequestName] = array(
            "pCodFiscaleEnte" => null,//Codice fiscale ente OBBLIGATORIO
            "pIUVMovimento" => null,//IUV da leggere
            "pCodiceServizio" => null,
            "pOpeAnnulla" => null,
        );

        if(count($params)>0)
            $this->a_reqObject = $this->setParams($params);
    }

    public function setSiscomDefaultObject(){
        $this->a_reqObject = array();
        $this->a_reqObject[$this->RequestName] = array(
            "pCodFiscaleEnte" => null//Codice fiscale ente OBBLIGATORIO
        );

        $this->a_reqObject[$this->RequestName]["pMovimentoRemoto"] = array(
            "IDMovimentoRemoto" => 0,//OBBLIGATORIO
            "RifEnte" => null,//Codice fiscale ente OBBLIGATORIO

            //DATI INTERNI PER USO SARIDA
            "ProceduraNome" => "PagoPASiscom",
            "ProceduraServizio" => "traffic_law",
            "ProceduraCodTipo" => null,
            "ProceduraChiave" => null,//Id Fine OBBLIGATORIO

            "PagamentoCausale" => "Pagamento esempio",//Causale Pagamento
            "PagamentoNote" => null,//Causale Pagamento
            "PagamentoImporto" => null,//Importo Pagamento OBBLIGATORIO

            "TipoPagoPa" => 0,//se ne occupa il WS
            "CodiceServizio" => 1,//Siscom comunica il codice corretto da parametrizzare
            "CodiceEntrata" => null,//se ne occupa il WS
            "RifNumero" => null,

            "RifAnno" => date('Y'),//Anno in corso
            "RifRata" => 1,//Rif rata

            //DATI SOGGETTO
            "DebitNatura" => null,//F (fisica) o G (giuridica) OBBLIGATORIO
            "DebitRagSocCognome" => null,//Ditta o Cognome OBBLIGATORIO
            "DebitNome" => null,//Nome
            "DebitCodFiscale" => null,//CF
            "DebitPartitaIVA" => null,//PI
            "DebitCellulare" => null,//Cell
            "DebitEMail" => null,//Email
            "DebitIndirizzo" => null,//Indirizzo
            "DebitCivico" => null,//Civico
            "DebitCap" => null,//CAP
            "DebitCitta" => null,//Città
            "DebitProvincia" => null,//Prov
            "DebitNazione" => null,//Stato

            //DATI CONTABILI (Si possono settare a "O")
            "ContVoce" => "0",
            "ContCapitolo" => "0",
            "ContArticolo" => "0",
            "ContAnnoAccert" => "0",
            "ContNumAccert" => "0",
            "ContNumSubAccert" => "0",

            "ContTipoContabilita" => "9",//se ne occupa il WS
            "ContCodiceContabilita" => null,//se ne occupa il WS

            "DataEmissione" => date('Y-m-d'),//Data Odierna di Emissione
            "DataScadenza" => null,//Data in cui lo IUV si elimina
            "DataScadStampa" => null,//Data di scadenza che viene mostrata sul pdf

            "IUV" => null,//se ne occupa il WS
            "NumAvviso" => null,//se ne occupa il WS
            "SiscStato" => "0",//se ne occupa il WS
            "LogOperatore" => $_SESSION['username']
        );
    }

    public function setSiscomParams($key, $params, $actionType){
        switch ($actionType){
            case "create":
            case "update":
                $a_return = array(
                    "pCodFiscaleEnte" => $params['Customer']['Code'],
                    "RifEnte" => $params['Customer']['Code'],
                    "ProceduraChiave" => $params['Payment'][$key]['ProcedureKey'],
                    "PagamentoCausale" => $params['Payment'][$key]['Causale'],
                    "PagamentoImporto" => $params['Payment'][$key]['Amount'],
                    "IUV" => $params['Payment'][$key]['IUV'],

                    "DebitNatura" => $params['Trespasser']['Type'],//F (fisica) o G (giuridica) OBBLIGATORIO
                    "DebitRagSocCognome" => $params['Trespasser']['CompanyName'].$params['Trespasser']['Surname'],
                    "DebitNome" => $params['Trespasser']['Name'],//Nome
                    "DebitCodFiscale" => $params['Trespasser']['TaxCode'],//CF
                    "DebitPartitaIva" => $params['Trespasser']['VatCode'],//CF

                    "DataEmissione" => date('Y-m-d'),//Data Odierna di Emissione
                    "DataScadenza" => date('Y-m-d', strtotime($params['Fine']['Date']. ' + '. EXPIRATION_PERIOD)),//Data in cui lo IUV si elimina
                    "DataScadStampa" => date('Y-m-d', strtotime($params['Fine']['Date']. ' + '. EXPIRATION_PERIOD)),//Data di scadenza che viene mostrata sul pdf
                );
                break;
            case "read":
            case "delete":
                $a_return = array(
                    "pCodFiscaleEnte" => $params['Customer']['Code'],
                    "pIUV" => $params['IUV']['Code']
                );
                break;
        }
        return $a_return;
    }
    /**
     *
     */
}


?>