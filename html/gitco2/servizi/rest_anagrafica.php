<?php
define("EMPTYSESSION_NOREDIRECT", true);

ob_start();
require_once ('../_path.php');
require_once('../inc/parameter.php');
require_once ('funzioni.php');

//require_once("../".CONTESTO_URL."/cls"."/cls_db.php");
require_once("../cls/cls_db.php");
ob_clean();

header('Content-Type: application/json; charset=utf-8');

class RICHIESTA{
    const JSON_MALFORMATO  = 'JSON malformato';
    const ERRORE_INTERNO  = 'Errore interno';
    const NO_RISULTATI = 'Nessun risultato';
    const NO_PARAMETRI = 'Uno o più parametri sono richiesti';
    const NO_PARAMETRI_COUNTRY = 'Il parametro cityid manca';
    const NO_AUTENTICAZIONE = 'I parametri di autenticazione sono mancanti od errati';
    const OP_SCONOSCIUTA = 'Operazione sconosciuta';
        
    private $rs;
    public $esito = 'OK';
    public $dati = array();
    
    function __construct($rs){
        $this->rs = $rs;
    }
    
    //Verifica che la lista di parametri passata sia tutta valorizzata
    public function verificaParametri(...$parametro){
        foreach($parametro as $p){
            if ($p == '')
               return false;
        }
        return true;
    }
    
    //Effettua l'esecuzione della query
    private function query($sql, $tipi, ...$valori){
        try{
            $stmt = mysqli_prepare($this->rs->conn, $sql);
            $stmt->bind_param($tipi, ...$valori);
            $stmt->execute();
            return $stmt->get_result();
        } catch (mysqli_sql_exception $e){
            throw $e;
        }
    }

    //GET
    //**********/

    //ANAGRAFICA

    //Parametri necessari: $op = country
    public function prendiCountrySarida(){
        $rs_Country = $this->rs->SelectQuery("SELECT * FROM ".MAIN_DB.".Country ORDER BY Title ASC");
        if(mysqli_num_rows($rs_Country) > 0){
            while($r_Country = $this->rs->getArrayLine($rs_Country)){
                $o_Stato = new stdClass();
                foreach($r_Country as $indice => $valore){
                    $o_Stato->$indice = $valore;
                }
                array_push($this->dati, $o_Stato);
            }
        }
        else $this->esito = self::NO_RISULTATI;
    }
    
    //Parametri necessari: $op = province
    public function prendiProvinceSarida(){
        $rs_Province = $this->rs->SelectQuery("SELECT P.ShortTitle , C.Id , C.Title FROM ".MAIN_DB.".City AS C JOIN ".MAIN_DB.".Province AS P ON C.ProvinceId = P.Id ORDER BY C.Title ASC");
        if(mysqli_num_rows($rs_Province) > 0){
          while($r_Province = $this->rs->getArrayLine($rs_Province)){
                $o_Stato = new stdClass();
                foreach($r_Province as $indice => $valore){
                $o_Stato->$indice = $valore;
                }
                array_push($this->dati, $o_Stato);
            }
          }
        else $this->esito = self::NO_RISULTATI;
    }
    
    //Parametri necessari: $op = legal
    public function prendiLegalFormTrafficLaw(){
        $rs_LegalForm = $this->rs->SelectQuery("SELECT Type, Id, Description FROM ".DB_NAME.".LegalForm");
        if(mysqli_num_rows($rs_LegalForm) > 0){
          while($r_LegalForm = $this->rs->getArrayLine($rs_LegalForm)){
                $o_Stato = new stdClass();
                foreach($r_LegalForm as $indice => $valore){
                $o_Stato->$indice = $valore;
                }
                array_push($this->dati, $o_Stato);
            }
          }
        else $this->esito = self::NO_RISULTATI;
    }
    
    //Parametri necessari: $op = trespasserhistory, $trid = {$TrespasserId}
    public function prendiTrespasserHistoryTrafficLaw($trid){
        $rs_TrespasserHistory = $this->rs->SelectQuery("SELECT TH.VersionDate, TH.Surname, TH.City, TH.Province, TH.Address, C.Title, TH.UserId FROM ".DB_NAME.".TrespasserHistory AS TH LEFT JOIN ".DB_NAME.".Country AS C ON TH.CountryId = C.Id WHERE TH.TrespasserId = ".$trid);
        if(mysqli_num_rows($rs_TrespasserHistory) > 0){
          while($r_TrespasserHistory = $this->rs->getArrayLine($rs_TrespasserHistory)){
                $o_Stato = new stdClass();
                foreach($r_TrespasserHistory as $indice => $valore){
                $o_Stato->$indice = $valore;
                }
                array_push($this->dati, $o_Stato);
            }
          }
        else $this->esito = self::NO_RISULTATI;
    }

    //Parametri necessari: $op = trespassercontacthistory, $trid = {$TrespasserId}, $contactid = {$ContactTypeId}
    //Parametri necessari: $op = trespasserid, $trid = {$TrespasserId}, $contactid = {$ContactTypeId}
    public function prendiTrespasserContactTrafficLaw($op, $trid, $contactid){
        $query = '';
        switch($op)
            {
                case "trespassercontacthistory": {$query = "SELECT TC.*, 
                    TH.Id AS HistoryId,
                    TH.TrespasserContactId AS HistoryTrespasserContactId,
                    TH.TrespasserId AS HistoryTrespasserId,
                    TH.ContactTypeId AS HistoryContactTypeId,
                    TH.Nominative AS HistoryNominative,
                    TH.Address AS HistoryAddress,
                    TH.StreetNumber AS HistoryStreetNumber,
                    TH.Ladder AS HistoryLadder,
                    TH.Indoor AS HistoryIndoor,
                    TH.Plan AS HistoryPlan,
                    TH.ZIP AS HistoryZIP,
                    TH.City AS HistoryCity,
                    TH.CityId AS HistoryCityId,
                    TH.Province AS HistoryProvince,
                    TH.CountryId AS HistoryCountryId,
                    TH.Mail AS HistoryMail,
                    TH.Phone AS HistoryPhone,
                    TH.Phone2 AS HistoryPhone2,
                    TH.Fax AS HistoryFax,
                    TH.PEC AS HistoryPEC,
                    TH.Notes AS HistoryNotes,
                    TH.ValidUntil AS HistoryValidUntil,
                    TH.UserId AS HistoryUserId,
                    TH.VersionDate AS HistoryVersionDate,
                    TH.LandId AS HistoryLandId FROM ".DB_NAME.".TrespasserContact AS TC LEFT JOIN ".DB_NAME.".TrespasserContactHistory AS TH ON TH.TrespasserContactId = TC.Id AND TC.ContactTypeId = ".$contactid." WHERE TC.TrespasserId = ".$trid." AND TC.ContactTypeId = ".$contactid; break;}
                case "trespasserid": {$query = "SELECT TC.Id FROM ".DB_NAME.".TrespasserContact AS TC WHERE TC.TrespasserId = ".$trid." AND TC.ContactTypeId = ".$contactid; break;}
            }

        $rs_TrespasserContact = $this->rs->SelectQuery($query);
        if(mysqli_num_rows($rs_TrespasserContact) > 0){
          while($r_TrespasserContact = $this->rs->getArrayLine($rs_TrespasserContact)){
                $o_Stato = new stdClass();
                foreach($r_TrespasserContact as $indice => $valore){
                $o_Stato->$indice = $valore;
                }
                array_push($this->dati, $o_Stato);
            }
          }
        else $this->esito = self::NO_RISULTATI;
    }

    //Parametri necessari: $op = trespassercountry, $cityid = {$CityId}
    public function prendiTrespasserCountryTrafficLaw($cityid){
        $rs_TrespasserCountry = $this->rs->SelectQuery("SELECT T.Id, T.Code, T.Genre, T.Surname, T.Name, T.CompanyName, T.City, C.Title FROM ".DB_NAME.".Trespasser AS T LEFT JOIN ".DB_NAME.".Country AS C ON T.CountryId = C.Id WHERE T.CustomerId ='".$cityid."'");
        if(mysqli_num_rows($rs_TrespasserCountry) > 0){
            while($r_TrespasserCountry = $this->rs->getArrayLine($rs_TrespasserCountry)){
                $o_Stato = new stdClass();
                foreach($r_TrespasserCountry as $indice => $valore){
                    $o_Stato->$indice = $valore;
                }
                array_push($this->dati, $o_Stato);
            }
        }
        else $this->esito = self::NO_RISULTATI;
    }

    //STRADARIO

    //Parametri necessari: $op = alltoponyms
    public function prendiToponymsSarida(){
        $rs_Toponyms = $this->rs->SelectQuery("SELECT Id, Title FROM ".MAIN_DB.".Toponym");
        if(mysqli_num_rows($rs_Toponyms) > 0){
            while($r_Toponyms = $this->rs->getArrayLine($rs_Toponyms)){
                $o_Stato = new stdClass();
                foreach($r_Toponyms as $indice => $valore){
                    $o_Stato->$indice = $valore;
                }
                array_push($this->dati, $o_Stato);
            }
        }
        else $this->esito = self::NO_RISULTATI;
    }

    //Parametri necessari: $op = toponymid, $toponymid = {$ToponymId}
    public function prendiToponymIdSarida($toponymid){
        $rs_ToponymId = $this->rs->SelectQuery("SELECT Title FROM ".MAIN_DB.".Toponym WHERE Id = ".$toponymid);
        if(mysqli_num_rows($rs_ToponymId) > 0){
            while($r_Toponym = $this->rs->getArrayLine($rs_ToponymId)){
                $o_Stato = new stdClass();
                foreach($r_Toponym as $indice => $valore){
                    $o_Stato->$indice = $valore;
                }
                array_push($this->dati, $o_Stato);
            }
        }
        else $this->esito = self::NO_RISULTATI;
    }

    //Parametri necessari: $op = zipcityid, $zipid = {$ZipCityId}
    public function prendiZipIdSarida($zipid){
        $rs_ZipId = $this->rs->SelectQuery("SELECT * FROM ".MAIN_DB.".ZIPCity WHERE ID = ".$zipid);
        if(mysqli_num_rows($rs_ZipId) > 0){
            while($r_ZipId = $this->rs->getArrayLine($rs_ZipId)){
                $o_Stato = new stdClass();
                foreach($r_ZipId as $indice => $valore){
                    $o_Stato->$indice = $valore;
                }
                array_push($this->dati, $o_Stato);
            }
        }
        else $this->esito = self::NO_RISULTATI;
    }

    //Parametri necessari: $op = zipcitystreet, $cityid = {$CityId}, $streetname = {$StreetName}
    public function prendiZipCityStreetSarida($cityid, $streetname){
        $rs_ZipCityStreet = $this->rs->SelectQuery("SELECT ID, StreetName AS Odonimo, ZIP AS Cap, Title AS Comune, CityId AS CC, ToponymId, DUG_Odonimo FROM ".MAIN_DB.".ZIPCity WHERE CityId = '".$cityid."' AND StreetName LIKE '%".$streetname."%'");
        if(mysqli_num_rows($rs_ZipCityStreet) > 0){
            while($r_ZipCityStreet = $this->rs->getArrayLine($rs_ZipCityStreet)){
                $o_Stato = new stdClass();
                foreach($r_ZipCityStreet as $indice => $valore){
                    $o_Stato->$indice = $valore;
                }
                array_push($this->dati, $o_Stato);
            }
        }
        else $this->esito = self::NO_RISULTATI;
    }

    //Parametri necessari: $op = cityprovince, $cityid = {$CityId}
    public function prendiCityProvinceSarida($cityid){
        $rs_CityProvince = $this->rs->SelectQuery("SELECT C.Id AS Com_Codice_Catastale, C.Title AS Com_Nome, P.ShortTitle AS Pro_Sigla, C.ZIP AS Com_Cap, IF(C.Com_Capoluogo = 1, 'cappato', 'normale') AS Tipo FROM ".MAIN_DB.".City AS C LEFT JOIN ".MAIN_DB.".Province AS P ON P.Id = C.ProvinceId WHERE C.Id = '".$cityid."' ORDER BY C.Title ASC");
        if(mysqli_num_rows($rs_CityProvince) > 0){
            while($r_CityProvince = $this->rs->getArrayLine($rs_CityProvince)){
                $o_Stato = new stdClass();
                foreach($r_CityProvince as $indice => $valore){
                    $o_Stato->$indice = $valore;
                }
                array_push($this->dati, $o_Stato);
            }
        }
        else $this->esito = self::NO_RISULTATI;
    }

    //*********/

         
    
    public function verificaJSON($output){
        if(!json_decode($output)){
            http_response_code(500);
            echo json_encode(
                array(
                    "Esito" => RICHIESTA::JSON_MALFORMATO,
                    "Dati" => $output,
                )
            );
        } else echo $output;
    }

    //Verifica che:
    //- In caso abbia ApplicationId = 3 che l'utente sia registrato nella tabella user
    //- In caso non abbia ApplicationId controlla che venga passato lo user name
    public function verificaUtente($un, $aid)
        {
            switch($aid){
                case 3: {
                    $rs_User = $this->rs->SelectQuery("SELECT * FROM ".MAIN_DB.".User WHERE UserName = '".trim($un)."'");
                    $check = mysqli_num_rows($rs_User)>0 ? true : false;
                    return $check;
                    break;
                }
                case '':{
                    $check = $un!='' ? true : false;
                    return $check;
                    break;
                }
            }
        }
}

$op = CheckValue('op', 's');   //Operazione
$un = CheckValue('un','s');    //User name
$aid = CheckValue('aid','n');  //ApplicationID
$trid = CheckValue('trid','n'); //TrespasserID
$contactid = CheckValue('contactid','n'); //ContactTypeID
$cityid = CheckValue('cityid','s'); //CustomerID
$toponymid = CheckValue('toponymid','n'); //ToponymID
$zipid = CheckValue('zipid','n'); //ZipCityID
$streetname = CheckValue('streetname','s'); //StreetName

$rs = new CLS_DB(new cls_db_gestoreErroriJSON(false));
$rs->SetCharset("utf8");
$richiesta = new RICHIESTA($rs);

if($richiesta->verificaUtente($un,$aid)){
switch($op){
    case 'country':         $richiesta->prendiCountrySarida(); break;       //1 anagrafica
    case 'province':        $richiesta->prendiProvinceSarida(); break;      //2 anagrafica
    case 'legal':           $richiesta->prendiLegalFormTrafficLaw(); break; //3 anagrafica
    case 'trespassercontacthistory':  //4-5-6 anagrafica
    case 'trespasserid':            { //8-9-10 anagrafica
                                    if($richiesta->verificaParametri($trid,$contactid)){
                                        $richiesta->prendiTrespasserContactTrafficLaw($op,$trid,$contactid);
                                        break;
                                    }
                                    $richiesta->esito = RICHIESTA::NO_PARAMETRI;
                                    break;
                                    }
    case 'trespasserhistory':       { //7 anagrafica
                                    if($richiesta->verificaParametri($trid)){
                                        $richiesta->prendiTrespasserHistoryTrafficLaw($trid);
                                        break;
                                    }
                                    $richiesta->esito = RICHIESTA::NO_PARAMETRI;
                                    break; 
                                    }
    case 'trespassercountry':       { //11 anagrafica
                                    if($richiesta->verificaParametri($cityid)){
                                        $richiesta->prendiTrespasserCountryTrafficLaw($cityid);
                                        break;
                                    }
                                    $richiesta->esito = RICHIESTA::NO_PARAMETRI_COUNTRY;
                                    break; 
                                    }
    case 'alltoponyms':             { //1 stradario
                                    $richiesta->prendiToponymsSarida();
                                    }
    case 'cityprovince':            {  //2 stradario
                                    if($richiesta->verificaParametri($cityid)){
                                        $richiesta->prendiCityProvinceSarida($cityid);
                                        break;
                                        }
                                    $richiesta->esito = RICHIESTA::NO_PARAMETRI;
                                    break;
                                    }
    case 'toponymid':               { //3 stradario
                                    if($richiesta->verificaParametri($toponymid)){
                                        $richiesta->prendiToponymIdSarida($toponymid);
                                        break;
                                        }
                                    $richiesta->esito = RICHIESTA::NO_PARAMETRI;
                                    break;
                                    }
    case 'zipcityid':               { //4 stradario
                                    if($richiesta->verificaParametri($zipid)){
                                        $richiesta->prendiZipIdSarida($zipid);
                                        break;
                                        }
                                    $richiesta->esito = RICHIESTA::NO_PARAMETRI;
                                    break;
                                    }
    case 'zipcitystreet':           { //5 stradario
                                    if($richiesta->verificaParametri($cityid, $streetname)){
                                        $richiesta->prendiZipCityStreetSarida($cityid, $streetname);
                                        break;
                                        }
                                    $richiesta->esito = RICHIESTA::NO_PARAMETRI;
                                    break;
                                    }
    default:                $richiesta->esito = RICHIESTA::OP_SCONOSCIUTA;
}
}
else{
    $richiesta->esito = RICHIESTA::NO_AUTENTICAZIONE;
}

echo json_encode(
    array(
        "Esito" => $richiesta->esito,
        "Dati" => $richiesta->dati
    )
);

$output = ob_get_contents();

ob_end_clean();

$richiesta->verificaJSON($output);