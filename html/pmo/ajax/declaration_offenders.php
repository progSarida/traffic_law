<?php
session_start();
include_once "../Librerie/XmlLanguageReader.php";
include_once "../Librerie/cls_DateTimeInLine.php";
include_once "../Librerie/cls_Utils.php";

$cls_date = new cls_DateTimeI("IT",false);
$xml = new xmlLanguageReader("../language.xml");

//$connTL = mysqli_connect("localhost","root","","traffic_law"); // da sostituire
//$connTL = mysqli_connect("62.94.231.188","root","GP~0o>hU@:/:q*","traffic_law");

//$connSR = mysqli_connect("localhost","root","","sarida"); // da sostituire
//$connSR = mysqli_connect("62.94.231.188","root","GP~0o>hU@:/:q*","sarida");

/*$query = "SELECT Id, Title FROM city ORDER BY Title";
$rs= mysqli_query($connSR, $query);

$optCom = "<option value=''></option>";
while($row = mysqli_fetch_array($rs)){
    $optCom .= "<option value=".$row['Id'].">".$row['Title']."</option>";
}*/
/*$dbConnect = array(
    "HOST" => "localhost",
    "USERNAME" => "root",
    "PASSWORD" => "",
    "DBNAME" => "polizia_municipale"
);*/

$dbConnect = array(
    "HOST" => "62.149.150.179",
    "USERNAME" => "Sql627048",
    "PASSWORD" => "41c608f5",
    "DBNAME" => "Sql627048_1"
);

$cls_Utils = new cls_Utils($dbConnect);

/*$allFile = $cls_Utils->getDirContents($_SERVER['DOCUMENT_ROOT']."inc/uploads/Temp");
for($i=0; $i<count($allFile); $i++){
    unlink($allFile[$i]);
}*/

$folder_name = "";
$count_folder = 0;
do{
    $folder_name = "Temp_".$count_folder;
    $count_folder++;
}while($cls_Utils->check_folder($_SERVER['DOCUMENT_ROOT']."inc/uploads/".$folder_name));

$cls_Utils->crea_dir($_SERVER['DOCUMENT_ROOT']."inc/uploads/".$folder_name);

$arrContextOptions=array(
    "ssl"=>array(
        "verify_peer"=>false,
        "verify_peer_name"=>false,
    ),
);

/*$rs = file_get_contents('http://formigine.ovunque-si.it/traffic_law/rest_richiestaDati.php?op=cityS',false, stream_context_create($arrContextOptions));
$rs = json_decode($rs);

var_dump($rs);*/

if($_POST['ComuneVerbale']=='D711')
{
    $URL_DATA = "http://formigine.ovunque-si.it/traffic_law/";
}
else {
    $URL_DATA = "https://gitco.ovunque-si.it/traffic_law/";
}

$rs = file_get_contents($URL_DATA.'rest_richiestaDati.php?op=cityS',false, stream_context_create($arrContextOptions));
$rs = json_decode($rs);
$rs = $rs->Dati;
//var_dump($rs->Dati[0]->Title);
//die;
$countRs = count($rs);
$optCom = "<option value=''></option>";
for($z=0; $z < $countRs; $z++){
    $optCom .= "<option value=".$rs[$z]->Id.">".$rs[$z]->Title."</option>";
}

/*$query = "SELECT Id,Title FROM country ORDER BY Title ASC";
$st = mysqli_query($connSR, $query);

$optSt = "<option value=''></option>";
while($row = mysqli_fetch_array($st)){
    $optSt .= "<option value=".$row['Id'].">".$row['Title']."</option>";
}*/

$st = file_get_contents($URL_DATA.'rest_richiestaDati.php?op=countryS',false, stream_context_create($arrContextOptions));
$st = json_decode($st);
$st = $st->Dati;
//var_dump($rs->Dati[0]->Title);
//die;
$countSt = count($st);
$optSt = "<option value=''></option>";
for($z=0; $z < $countSt; $z++){
    $optSt .= "<option value=".$st[$z]->Id.">".$st[$z]->Title."</option>";
}
/*$conn = mysqli_connect("localhost","root","","polizia_municipale");


$query = "SELECT * FROM stati ORDER BY Stato ASC";
$st = mysqli_query($conn, $query);

$query = "SELECT Id,Title FROM country ORDER BY Title ASC";
$st = mysqli_query($connTL, $query);

$optSt = "<option value=''></option>";
while($row = mysqli_fetch_array($st)){
    $optSt .= "<option value=".$row['Id'].">".$row['Title']."</option>";
}*/

/*$query = "SELECT Com_CC, Com_Nome_Comune FROM comune_gestito_dettagli ORDER BY Com_Nome_Comune";
$rs= mysqli_query($conn, $query);

$optCom = "<option value=''></option>";
while($row = mysqli_fetch_array($rs)){
    $optCom .= "<option value=".$row['Com_CC'].">".$row['Com_Nome_Comune']."</option>";
}*/

$aNotFound = array(
    'ita'=>'Verbale non trovato. Controllare i dati inseriti.',
    'eng'=>'Verbal not found. Check your details.',
    'ger'=>'Verbal nicht gefunden. Überprüfen Sie Ihre Angaben.',
    'fre'=>'Verbal not found. Vérifiez vos informations.',
    'spa'=>'Verbal no encontrado. Ver sus detalles.',
);

$cv = (isset($_POST['ComuneVerbale'])) ? $_POST['ComuneVerbale'] : null;
$tv = (isset($_POST['CronotVerbale'])) ? $_POST['CronotVerbale'] : null;
$nv = (isset($_POST['CronoNumeroVerbale'])) ? $_POST['CronoNumeroVerbale'] : null;
$yv = (isset($_POST['CronoAnnoVerbale'])) ? $_POST['CronoAnnoVerbale'] : null;
$dvd = (isset($_POST['GiornoVerbale'])) ? $_POST['GiornoVerbale'] : null;
$dvm = (isset($_POST['MeseVerbale'])) ? $_POST['MeseVerbale'] : null;
$dvy = (isset($_POST['AnnoVerbale'])) ? $_POST['AnnoVerbale'] : null;

$hv = (isset($_POST['OreVerbale'])) ? $_POST['OreVerbale'] : null;
$mv = (isset($_POST['MinutiVerbale'])) ? $_POST['MinutiVerbale'] : null;
$pv = (isset($_POST['TargaVeicolo'])) ? strtoupper($_POST['TargaVeicolo']) : null;

//$query = "SELECT B.ShortTitle FROM city AS A JOIN province AS B on B.Id = A.ProvinceId WHERE A.Id = '".$cv."'";
/*$query = "SELECT ManagerSector, ManagerCity, ManagerProvince FROM customer WHERE CityId = '".$cv."'";
$result = mysqli_query($connTL, $query);

while($row = mysqli_fetch_array($result)){
    //var_dump($row);
    $Polizia = $row["ManagerSector"];
    $Provincia = $row["ManagerProvince"];
    $cve = $row["ManagerCity"];
}*/

$responseCust = file_get_contents($URL_DATA.'rest_richiestaDati.php?op=customer&cv='.$cv,false, stream_context_create($arrContextOptions));
$responseCust = json_decode($responseCust);

if($responseCust->Esito == "OK"){
    $Polizia = $responseCust->Dati[0]->ManagerSector;
    $Provincia = $responseCust->Dati[0]->ManagerProvince;
    $cve = $responseCust->Dati[0]->ManagerCity;
}
else {
    $Polizia = "";
    $Provincia = "";
    $cve = "";
}

$dataAttuale = date("d/m/Y");

/*if(strlen($hv)==1) $hv = '0'.$hv;
if(strlen($mv)==1) $mv = '0'.$mv;

$v_t = $hv.":".$mv;
$v_c = $cv;
$v_n = $nv;
$v_y = $yv;
$v_d = $dvy."-".$dvm."-".$dvd;
$v_p = $pv;*/

$dC = $dvy."-".$dvm."-".$dvd;
$hC = $hv.":".$mv;
$cC = $nv."/".$yv;
if($tv!= "" && $tv != null)
    $cC .= "/".$tv;
//$query = "SELECT Id,Address FROM fine WHERE CityId = '".$cv."' AND ProtocolId = ".$nv." AND ProtocolYear = ".$yv." AND FineDate = '".$dC."' AND FIneTime = '".$hC."' AND VehiclePlate = '".$pv."' AND Code = '".$cC."'";
/*$query = "SELECT Id,Address FROM fine WHERE CityId = '".$cv."' AND FineDate = '".$dC."' AND FIneTime = '".$hC."' AND VehiclePlate = '".$pv."' AND Code = '".$cC."'";
$result = mysqli_query($connTL, $query);
$FineId = array();

while($row = mysqli_fetch_array($result)){
    //var_dump($row);
    $FineId[] = $row["Id"];
    $via = $row["Address"];
}*/

//echo "<br>".$cv."<br><br>";
//echo "CityId ".$cv." -- nv ".$nv." -- yv ".$yv." -- dC ".$dC." -- hc ".$hC. " -- targa ".$pv;

$response = file_get_contents($URL_DATA.'rest_richiestaDati.php?op=finetrespasser&cv='.$cv.'&nv='.$nv.'&yv='.$yv.'&dC='.$dC.'&hC='.$hC.'&pv='.$pv,false, stream_context_create($arrContextOptions));
//var_dump($response);
//die;
$response = json_decode($response);
//var_dump($response);
//die;

if(isset($response->Dati[0]->Address)) $via = $response->Dati[0]->Address;
else $via = "";

//echo "<br>".count($response->Dati[0]->Trespasser)."<br><br>";
//var_dump($response->Dati[0]->Trespasser[0]->Address);
//die;

/*if(mysqli_num_rows($result) == 0)
{
    echo json_encode(
        array(
            "id_div" => "message_offenders",
            "content" => '<div class="alert alert-danger">'.$aNotFound[$_SESSION['lan']].'</div>'
        )
    );
    die;
}*/

//var_dump($response->Esito);
//die;
if($response->Esito != "OK")
{
    echo json_encode(
        array(
            "id_div" => "message_offenders",
            "content" => '<div class="alert alert-danger">'.$aNotFound[$_SESSION['lan']].'</div>'
        )
    );
    die;
}
else
{

    /*$obj = "Test object";
    $to = "gianluca.virdis8901@gmail.com";
    $from = "informazioni@poliziamunicipale-online.it";
    $mex = "(questo è il mio messaggio)";


    $res = mail($to, $obj, $mex, "From: ".$from."\r\nReply-To: ".$from."\r\nX-Mailer: DT_formmail");*/

    //var_dump($res);
    /*$query = "SELECT TrespasserId,FineId,TrespasserTypeId FROM finetrespasser WHERE FineId = ".$FineId[0];
    $result = mysqli_query($connTL, $query);

    $TrespasserId = array();
    while($row = mysqli_fetch_array($result)){
        $TrespasserId[] = $row["TrespasserId"];
    }

    $query = "SELECT * FROM trespasser WHERE  1 = 2 ";
    for($i=0; $i<count($TrespasserId); $i++)
    {
        $query .= " OR Id = ".$TrespasserId[$i];
    }
    $result = mysqli_query($connTL, $query);
    $count = 0;
    $data = array();*/

   /* $query = "SELECT A.TrespasserId,A.FineId,A.TrespasserTypeId, B.* FROM finetrespasser as A join trespasser as B on A.TrespasserId = B.Id WHERE FineId = ".$FineId[0];
    $result = mysqli_query($connTL, $query);
    $count = 0;
    $data = array();

    //echo $query;

    while($row = mysqli_fetch_array($result)){
        //var_dump($row);
        foreach( $row as $key => $value)
        {
            if(gettype($key)=="string")
                $data[$count][$key] = $value;
        }
        $count++;
    }*/

    $trasgressore = array();
    $utente = array();
    $data = array();
    $radioChoice_1 = "checked";
    $radioChoice_2 = "";
    $radioChoice_3 = "";

    //var_dump($data);

    if(count($response->Dati[0]->Trespasser)>0)
    {
        $data = $response->Dati[0]->Trespasser;

        //$response->Dati[0]->Trespasser[0]->Address
        for($i=0; $i < count($data); $i++)
        {
            switch($data[$i]->TrespasserTypeId)
            {
                case 1:
                case 2:
                case 10:
                    $utente["NomeDitta"] = $data[$i]->CompanyName;
                    $utente["PI"] = $data[$i]->VatCode;

                    $utente["Cognome"] = $data[$i]->Surname;
                    $utente["Nome"] = $data[$i]->Name;
                    if($data[$i]->Genre=="D") $utente["Genere"] = null;
                    else $utente["Genere"] = $data[$i]->Genre;//drop
                    $utente["CF"] = $data[$i]->TaxCode;
                    $utente["StatoNascita"] = $data[$i]->BornCountryId;//drop
                    $utente["ComuneNascita"] = $data[$i]->BornPlace;//drop

                    $utente["NumeroPatente"] = $data[$i]->LicenseNumber;
                    $utente["CategoriaPatente"] = $data[$i]->LicenseCategory;
                    $utente["DataValiditaPatente"] = $data[$i]->LicenseDate;
                    $utente["AutoritaRilascio"] = $data[$i]->LicenseOffice;
                    $utente["StatoRilascioPatente"] = $data[$i]->DocumentCountryId;

                    $utente["TrespasserTypeId"] = $data[$i]->TrespasserTypeId;//
                    $utente["FineId"] = $response->Dati[0]->FineId;//
                    $utente["TrespasserId"] = $data[$i]->Id;//questo è Id dal nulvo oggetto su Trespasser
                    $utente["DataNascita"] = $data[$i]->BornDate;//


                    switch($data[$i]->Genre)
                    {
                        case "M":
                        case "F":
                            $utente["IndirizzoUtente"] = $data[$i]->Address;
                            $utente["CivicoUtente"] = $data[$i]->StreetNumber;
                            $utente["InternoUtente"] = $data[$i]->Indoor;
                            $utente["EsponenteUtente"] = $data[$i]->Ladder;//forse sbagliato chiedere
                            $utente["ComuneUtente"] = $data[$i]->CityId;//drop
                            $utente["ComuneUtente1"] = $data[$i]->City;
                            $utente["StatoUtente"] = $data[$i]->CountryId;
                            $utente["TelefonoUtente"] = $data[$i]->Phone;
                            $utente["MailUtente"] = $data[$i]->Mail;
                            $utente["ProvinciaUtente"] = $data[$i]->Province;

                            $utente["IndirizzoDitta"] = null;
                            $utente["CivicoDitta"] = null;
                            $utente["InternoDitta"] = null;
                            $utente["EsponenteDitta"] = null;//forse sbagliato chiedere
                            $utente["ComuneDitta"] = null;//drop
                            $utente["ComuneDitta1"] = null;
                            $utente["StatoDitta"] = null;
                            $utente["TelefonoDitta"] = null;
                            $utente["MailDitta"] = null;
                            $utente["ProvinciaDitta"] = null;

                            break;
                        case "D":
                            $utente["IndirizzoUtente"] = null;
                            $utente["CivicoUtente"] = null;
                            $utente["InternoUtente"] = null;
                            $utente["EsponenteUtente"] = null;//forse sbagliato chiedere
                            $utente["ComuneUtente"] = null;//drop
                            $utente["ComuneUtente1"] = null;
                            $utente["StatoUtente"] = null;
                            $utente["TelefonoUtente"] = null;
                            $utente["MailUtente"] = null;
                            $utente["ProvinciaUtente"] = null;

                            $utente["IndirizzoDitta"] = $data[$i]->Address;
                            $utente["CivicoDitta"] = $data[$i]->StreetNumber;
                            $utente["InternoDitta"] = $data[$i]->Indoor;
                            $utente["EsponenteDitta"] = $data[$i]->Ladder;//forse sbagliato chiedere
                            $utente["ComuneDitta"] = $data[$i]->CityId;//drop
                            $utente["ComuneDitta1"] = $data[$i]->City;//drop
                            $utente["StatoDitta"] = $data[$i]->CountryId;
                            $utente["TelefonoDitta"] = $data[$i]->Phone;
                            $utente["MailDitta"] = $data[$i]->Mail;
                            $utente["ProvinciaDitta"] = $data[$i]->Province;

                            if($data[$i]->TrespasserTypeId == 10) {
                                $radioChoice_1 = "";
                                $radioChoice_3 = "checked";
                            }
                            else {
                                $radioChoice_1 = "";
                                $radioChoice_2 = "checked";
                            }//drop
                            break;
                        default:
                            break;
                    }
                    break;
                case 3:
                case 11:
                    $trasgressore["Cognome"] = $data[$i]->Surname;
                    $trasgressore["Nome"] = $data[$i]->Name;
                    if($data[$i]->Genre=="D") $trasgressore["Genere"] = null;
                    else $trasgressore["Genere"] = $data[$i]->Genre;//drop
                    $trasgressore["CF"] = $data[$i]->TaxCode;
                    $trasgressore["StatoNascita"] = $data[$i]->BornCountryId;//drop
                    $trasgressore["ComuneNascita"] = $data[$i]->BornPlace;
                    $trasgressore["DataNascita"] = $data[$i]->BornDate;

                    $trasgressore["NumeroPatente"] = $data[$i]->LicenseNumber;
                    $trasgressore["CategoriaPatente"] = $data[$i]->LicenseCategory;
                    $trasgressore["DataValiditaPatente"] = $data[$i]->LicenseDate;
                    $trasgressore["AutoritaRilascio"] = $data[$i]->LicenseOffice;
                    $trasgressore["StatoRilascioPatenteTrasgressore"] = $data[$i]->DocumentCountryId;

                    $trasgressore["IndirizzoTrasgressore"] = $data[$i]->Address;
                    $trasgressore["CivicoTrasgressore"] = $data[$i]->StreetNumber;
                    $trasgressore["InternoTrasgressore"] = $data[$i]->Indoor;
                    $trasgressore["EsponenteTrasgressore"] = $data[$i]->Ladder;//forse sbagliato chiedere
                    $trasgressore["ComuneTrasgressore"] = $data[$i]->CityId;//drop
                    $trasgressore["ComuneTrasgressore1"] = $data[$i]->City;
                    $trasgressore["StatoTrasgressore"] = $data[$i]->CountryId;
                    $trasgressore["TelefonoTrasgressore"] = $data[$i]->Phone;
                    $trasgressore["MailTrasgressore"] = $data[$i]->Mail;

                    $trasgressore["TrespasserTypeId"] = $data[$i]->TrespasserTypeId;
                    $trasgressore["FineId"] = $response->Dati[0]->FineId;
                    $trasgressore["TrespasserId"] = $data[$i]->Id;//Id
                    $trasgressore["ProvinciaTrasgressore"] = $data[$i]->Province;

                    break;
                default:
                    break;
            }
        }

    }

    if(count($data)==0)
    {
        $utente["NomeDitta"] = null;
        $utente["PI"] = null;

        $utente["Cognome"] = null;
        $utente["Nome"] = null;
        $utente["Genere"] = null;//drop
        $utente["CF"] = null;
        $utente["StatoNascita"] = null;//drop
        $utente["ComuneNascita"] = null;
        $utente["DataNascita"] = null;

        $utente["NumeroPatente"] = null;
        $utente["CategoriaPatente"] = null;
        $utente["DataValiditaPatente"] = null;
        $utente["AutoritaRilascio"] = null;

        $utente["IndirizzoDitta"] = null;
        $utente["CivicoDitta"] = null;
        $utente["InternoDitta"] = null;
        $utente["EsponenteDitta"] = null;//forse sbagliato chiedere
        $utente["ComuneDitta"] = null;//drop
        $utente["ComuneDitta1"] = null;
        $utente["StatoDitta"] = null;
        $utente["TelefonoDitta"] = null;
        $utente["MailDitta"] = null;
        $utente["ProvinciaDitta"] = null;

        $utente["IndirizzoUtente"] = null;
        $utente["CivicoUtente"] = null;
        $utente["InternoUtente"] = null;
        $utente["EsponenteUtente"] = null;//forse sbagliato chiedere
        $utente["ComuneUtente"] = null;//drop
        $utente["ComuneUtente1"] = null;
        $utente["StatoUtente"] = null;
        $utente["TelefonoUtente"] = null;
        $utente["MailUtente"] = null;
        $utente["ProvinciaUtente"] = null;
        $utente["StatoRilascioPatente"] = null;

        $utente["TrespasserTypeId"] = null;
        $utente["FineId"] = null;
        $utente["TrespasserId"] = null;

        $trasgressore["Cognome"] = null;
        $trasgressore["Nome"] = null;
        $trasgressore["Genere"] = null;//drop
        $trasgressore["CF"] = null;
        $trasgressore["StatoNascita"] = null;//drop
        $trasgressore["ComuneNascita"] = null;
        $trasgressore["DataNascita"] = null;

        $trasgressore["NumeroPatente"] = null;
        $trasgressore["CategoriaPatente"] = null;
        $trasgressore["DataValiditaPatente"] = null;
        $trasgressore["AutoritaRilascio"] = null;

        $trasgressore["IndirizzoTrasgressore"] = null;
        $trasgressore["CivicoTrasgressore"] = null;
        $trasgressore["InternoTrasgressore"] = null;
        $trasgressore["EsponenteTrasgressore"] = null;//forse sbagliato chiedere
        $trasgressore["ComuneTrasgressore"] = null;//drop
        $trasgressore["ComuneTrasgressore1"] = null;
        $trasgressore["StatoTrasgressore"] = null;
        $trasgressore["TelefonoTrasgressore"] = null;
        $trasgressore["MailTrasgressore"] = null;
        $trasgressore["ProvinciaTrasgressore"] = null;
        $trasgressore["StatoRilascioPatenteTrasgressore"] = null;

        $trasgressore["TrespasserTypeId"] = null;
        $trasgressore["FineId"] = null;
        $trasgressore["TrespasserId"] = null;
    }
    else{

        if(count($utente) == 0){
            $utente["NomeDitta"] = null;
            $utente["PI"] = null;

            $utente["Cognome"] = null;
            $utente["Nome"] = null;
            $utente["Genere"] = null;//drop
            $utente["CF"] = null;
            $utente["StatoNascita"] = null;//drop
            $utente["ComuneNascita"] = null;
            $utente["DataNascita"] = null;

            $utente["NumeroPatente"] = null;
            $utente["CategoriaPatente"] = null;
            $utente["DataValiditaPatente"] = null;
            $utente["AutoritaRilascio"] = null;

            $utente["IndirizzoDitta"] = null;
            $utente["CivicoDitta"] = null;
            $utente["InternoDitta"] = null;
            $utente["EsponenteDitta"] = null;//forse sbagliato chiedere
            $utente["ComuneDitta"] = null;//drop
            $utente["ComuneDitta1"] = null;
            $utente["StatoDitta"] = null;
            $utente["TelefonoDitta"] = null;
            $utente["MailDitta"] = null;
            $utente["ProvinciaDitta"] = null;

            $utente["IndirizzoUtente"] = null;
            $utente["CivicoUtente"] = null;
            $utente["InternoUtente"] = null;
            $utente["EsponenteUtente"] = null;//forse sbagliato chiedere
            $utente["ComuneUtente"] = null;//drop
            $utente["ComuneUtente1"] = null;
            $utente["StatoUtente"] = null;
            $utente["TelefonoUtente"] = null;
            $utente["MailUtente"] = null;
            $utente["ProvinciaUtente"] = null;
            $utente["StatoRilascioPatente"] = null;

            $utente["TrespasserTypeId"] = null;
            $utente["FineId"] = $trasgressore["FineId"];
            $utente["TrespasserId"] = null;
        }
        if(count($trasgressore) == 0){
            $trasgressore["Cognome"] = null;
            $trasgressore["Nome"] = null;
            $trasgressore["Genere"] = null;//drop
            $trasgressore["CF"] = null;
            $trasgressore["StatoNascita"] = null;//drop
            $trasgressore["ComuneNascita"] = null;
            $trasgressore["DataNascita"] = null;

            $trasgressore["NumeroPatente"] = null;
            $trasgressore["CategoriaPatente"] = null;
            $trasgressore["DataValiditaPatente"] = null;
            $trasgressore["AutoritaRilascio"] = null;

            $trasgressore["IndirizzoTrasgressore"] = null;
            $trasgressore["CivicoTrasgressore"] = null;
            $trasgressore["InternoTrasgressore"] = null;
            $trasgressore["EsponenteTrasgressore"] = null;//forse sbagliato chiedere
            $trasgressore["ComuneTrasgressore"] = null;//drop
            $trasgressore["ComuneTrasgressore1"] = null;
            $trasgressore["StatoTrasgressore"] = null;
            $trasgressore["TelefonoTrasgressore"] = null;
            $trasgressore["MailTrasgressore"] = null;
            $trasgressore["ProvinciaTrasgressore"] = null;
            $trasgressore["StatoRilascioPatenteTrasgressore"] = null;

            $trasgressore["TrespasserTypeId"] = null;
            $trasgressore["FineId"] = $utente["FineId"];
            $trasgressore["TrespasserId"] = null;
        }
    }

    $sessoUtF = "";
    $sessoUtM = "";
    if($utente["Genere"]== "F") $sessoUtF = "selected";
    if($utente["Genere"]== "M") $sessoUtM = "selected";

    $sessoTrF = "";
    $sessoTrM = "";
    if($trasgressore["Genere"]== "F") $sessoTrF = "selected";
    if($trasgressore["Genere"]== "M") $sessoTrM = "selected";

   //var_dump($trasgressore);
//die;
}
//onchange="ControllaComune(this,1,\"ComuneNascita1\");"

$content = '<form name="offendersForm" id="offendersForm" method="post" enctype="multipart/form-data">
    <input type="hidden" name="ComuneVerbale" value="'.$cv.'" />
	<input type="hidden" name="CronotVervbale" value="'.$tv.'" />
	<input type="hidden" name="CronoNumeroVerbale" value="'.$nv.'" />
	<input type="hidden" name="CronoAnnoVerbale" value="'.$yv.'" />
	<input type="hidden" name="GiornoVerbale" value="'.$dvd.'" />
	<input type="hidden" name="MeseVerbale" value="'.$dvm.'" />
	<input type="hidden" name="AnnoVerbale" value="'.$dvy.'" />
	
	<input type="hidden" name="folder_name" id="folder_name" value="'.$folder_name.'" />
	
	<input type="hidden" name="OreVerbale" value="'.$hv.'" />
	<input type="hidden" name="MinutiVerbale" value="'.$mv.'" />
	<input type="hidden" name="TargaVeicolo" value="'.$pv.'" />
    <input type="hidden" name="DataDichiarazione" value="'.$dataAttuale.'" />
    
    <input type="hidden" name="TrespasserTypeIdUtente" value="'.$utente["TrespasserTypeId"].'" />
    <input type="hidden" name="TrespasserTypeIdTrasgressore" value="'.$trasgressore["TrespasserTypeId"].'" />
    <input type="hidden" name="FineIdUtente" value="'.$utente["FineId"].'" />
    <input type="hidden" name="FineIdTrasgressore" value="'.$trasgressore["FineId"].'" />
    <input type="hidden" name="TrespasserIdUtente" value="'.$utente["TrespasserId"].'" />
    <input type="hidden" name="TrespasserIdTrasgressore" value="'.$trasgressore["TrespasserId"].'" />
    <input type="hidden" name="codiceAnticontraffazione" id="codiceAnticontraffazione" value="" />
    
    <input type="hidden" name="StatoNascitaCompleto" id="StatoNascitaCompleto" value="" />
    <input type="hidden" name="StatoResidenzaCompleto" id="StatoResidenzaCompleto" value="" />
    <input type="hidden" name="StatoDittaCompleto" id="StatoDittaCompleto" value="" />
    <input type="hidden" name="StatoNascitaTrasgressoreCompleto" id="StatoNascitaTrasgressoreCompleto" value="" />
    <input type="hidden" name="StatoResidenzaTrasgressoreCompleto" id="StatoResidenzaTrasgressoreCompleto" value="" />
    
    <div class="row-fluid">
        <div class="span8"></div>
        <div class="span2">'.$xml->getWord("DataDichiarazione",$_SESSION['lan']).': </div>
        <div class="span2">'.$dataAttuale.'</div>
    </div>
    <div class="row-fluid">
        <div class="span4">'.$xml->getWord("SelectIntestazione",$_SESSION['lan']).': </div>
        <div class="span7">
            <select multiple class="multiSelect" data-validation="required" style="width: 100%;height: 110px;" data-validation-error-msg="Campo obbligatorio" id="TipoDichiarazione" name="TipoDichiarazione" onchange="VisualizzaNascondi(this);">
                <option value="1" selected><div>'.$xml->getWord("SelectTras_1",$_SESSION['lan']).'</div></option>
                <option value="2"><div>'.$xml->getWord("SelectTras_2",$_SESSION['lan']).'</div></option>
                <option value="3"><div>'.$xml->getWord("SelectTras_3",$_SESSION['lan']).'</div></option>
                <option value="4"><div>'.$xml->getWord("SelectTras_4",$_SESSION['lan']).'</div></option>
            </select>
        </div>
        <div class="span1"><img src="img/freccia_trasp.png" style="margin-top: 60%; width: 60%;"></div>
    </div>
    <div class="row-fluid">
        <label_ModTrasmissione class="span4">'.$xml->getWord("ModTr",$_SESSION['lan']).'</label_ModTrasmissione>
        <div class="span8">
            <select class="validateCustom vld_Custom_r" style="width: 100%;" onchange="visualizzaNascondiUploadFile(this);" id="ModTrasmissione" name="ModTrasmissione">
                <option></option>
                <option value="2">'.$xml->getWord("SelectModTr_2",$_SESSION['lan']).'</option>'.
                //<option value="3">'.$xml->getWord("SelectModTr_3",$_SESSION['lan']).'</option>
                //<option value="4">'.$xml->getWord("SelectModTr_4",$_SESSION['lan']).'</option>
                '<option value="5">'.$xml->getWord("SelectModTr_5",$_SESSION['lan']).'</option>
            </select>
        </div>
    </div>
    <div class="row-fluid" style="margin-top: 2%;">
        <div class="span1">'.$xml->getWord("Polizia",$_SESSION['lan']).'</div>
        <div class="span2"><input style="width: 98%" type="text" id="Polizia" name="Polizia" value="'.$Polizia.'" readonly placeholder="'.$xml->getWord("Polizia",$_SESSION['lan']).' ..."></div>
        <div class="span1">'.$xml->getWord("Indirizzo",$_SESSION['lan']).'</div>
        <div class="span3"><input style="width: 98%" type="text" id="Comune" name="Comune" value="'.$cve.'" readonly placeholder="'.$xml->getWord("Comune",$_SESSION['lan']).' ..."></div>
        <div class="span1"><input style="width: 98%" type="text" id="Provincia" name="Provincia" value="'.$Provincia.'" readonly placeholder="'.$xml->getWord("Provincia",$_SESSION['lan']).' ..."></div>
        <div class="span4"><input style="width: 98%" type="text" id="ViaVerbale" name="ViaVerbale" value="'.$via.'" readonly placeholder="'.$xml->getWord("Via",$_SESSION['lan']).' ..."></div>
    </div>
    <div class="row-fluid" style="margin-top: 2%">
        <div class="span1">'.$xml->getWord("CrNumberAbbrev",$_SESSION['lan']).' </div>
        <div class="span2"><input style="width: 98%" type="text" id="CronoID" name="CronoID" value="'.$nv.'" disabled placeholder="'.$xml->getWord("CrNumberPlace",$_SESSION['lan']).' ..."></div>
        <div class="span1">'.$xml->getWord("CrAnnoAbbrev",$_SESSION['lan']).' </div>
        <div class="span2"><input style="width: 98%" type="text" id="CronoYear" name="CronoYear" value="'.$yv.'" disabled placeholder="'.$xml->getWord("CrAnnoPlace",$_SESSION['lan']).' ..."></div>
        <div class="span1">'.$xml->getWord("Targa",$_SESSION['lan']).' </div>
        <div class="span2"><input style="width: 98%" type="text" id="Targa" name="Targa" placeholder="'.$xml->getWord("TargaPlace",$_SESSION['lan']).' ..." value="'.$pv.'" disabled></div>
    </div>
    
    <div class="row-fluid Utente" style="display: none;">
        <div class="row-fluid" style="margin-top: 2%">
            <div class="span12" style="text-align: center;"><h4>'.$xml->getWord("DtUtente",$_SESSION['lan']).'</h4></div>
        </div>
        <div class="row-fluid">
            <label_Nome class="span1">'.$xml->getWord("Nome",$_SESSION['lan']).': </label_Nome>
            <div class="span2"><input class="CLutente validateCustom vld_Custom_r" style="width: 98%" value="'.$utente["Nome"].'" type="text" id="Nome" name="Nome" placeholder="'.$xml->getWord("NomePlace",$_SESSION['lan']).' ..."></div>
            <label_Cognome class="span1">'.$xml->getWord("Cognome",$_SESSION['lan']).': </label_Cognome>
            <div class="span2"><input class="CLutente validateCustom vld_Custom_r" style="width: 98%" type="text" value="'.$utente["Cognome"].'" id="Cognome" name="Cognome" placeholder="'.$xml->getWord("CognomePlace",$_SESSION['lan']).' ..."></div>
            <label_Sesso class="span1">'.$xml->getWord("Sesso",$_SESSION['lan']).': </label_Sesso>
            <div class="span1">
                <select class="CLutente validateCustom vld_Custom_r" style="width: 100%;" id="Sesso" name="Sesso">
                    <option></option>
                    <option value="M" '.$sessoUtM.'>'.$xml->getWord("Maschio",$_SESSION['lan']).'</option>
                    <option value="F" '.$sessoUtF.'>'.$xml->getWord("Femmina",$_SESSION['lan']).'</option>
                </select>
            </div>
            <label_CF class="span1">'.$xml->getWord("CodFiscaleAbbr",$_SESSION['lan']).': </label_CF>
            <div class="span3"><input style="width: 98%" value="'.$utente["CF"].'" class="CLutente validateCustom vld_Custom_r vld_Custom_CF" type="text" id="CF" name="CF" placeholder="'.$xml->getWord("CodFiscaleUtente",$_SESSION['lan']).' ..."></div>
        </div>
        <div class="row-fluid">
            <label_StatoNascita class="span1">'.$xml->getWord("Nato",$_SESSION['lan']).': </label_StatoNascita>
            <div class="span2">
                <select class="CLutente validateCustom vld_Custom_r" style="width: 100%;" id="StatoNascita" name="StatoNascita" onchange="ScegliInputComune(\"ComuneEsistente\",\"ComuneNonTrovato\",\"ComuneNascita1\",\"ComuneNascita\",this);SetStatoCompleto(this,\"StatoNascitaCompleto\");">
                    '.$optSt.'
                </select>
            </div>
            <label_ComuneNascita class="span1 ComuneEsistente">'.$xml->getWord("Comune",$_SESSION['lan']).': </label_ComuneNascita>
            <div class="span2 ComuneEsistente">
                <select class="CLutente" style="width: 100%;" id="ComuneNascita" name="ComuneNascita" >
                    '.$optCom.'
                </select>
            </div>
            <label_ComuneNascita1 class="span1 ComuneNonTrovato" style="display: none;">'.$xml->getWord("Comune",$_SESSION['lan']).': </label_ComuneNascita1>
            <div class="span3 ComuneNonTrovato" style="display: none;"><input class="CLutente" onblur="checkComune(this,\"ComuneNascita\");" style="width: 98%" type="text" id="ComuneNascita1" name="ComuneNascita1" placeholder="'.$xml->getWord("Comune",$_SESSION['lan']).' ..."></div>
            <label_DataNascita class="span2">'.$xml->getWord("DataNascita",$_SESSION['lan']).': </label_DataNascita>
            <div class="span3"><input style="width: 98%" value="'.$cls_date->Get_DateNewFormat($utente["DataNascita"],"DB").'" class="CLutente date validateCustom vld_Custom_r vld_Custom_date" type="text" id="DataNascita" name="DataNascita" placeholder="'.$xml->getWord("DataNascita",$_SESSION['lan']).' ..."></div>
        </div>
        <div class="row-fluid" style="margin-top: 2%">
            <div class="span12" style="text-align: center;"><h4>'.$xml->getWord("TitoloResUtente",$_SESSION['lan']).'</h4></div>
        </div>
        <div class="row-fluid">
            <label_StatoResidenza class="span1">'.$xml->getWord("ResidenteIn",$_SESSION['lan']).': </label_StatoResidenza>
            <div class="span2">
                <select class="CLutente validateCustom vld_Custom_r" onchange="ScegliInputComune(\"ComuneEsistente_1\",\"ComuneNonTrovato_1\",\"ComuneResidenza1\",\"ComuneResidenza\",this);SetStatoCompleto(this,\"StatoResidenzaCompleto\");" style="width: 100%;" id="StatoResidenza" name="StatoResidenza" >
                    '.$optSt.'
                </select>
            </div>
            <label_ComuneResidenza class="span1 ComuneEsistente_1">'.$xml->getWord("Comune",$_SESSION['lan']).': </label_ComuneResidenza>
            <div class="span2 ComuneEsistente_1">
                <select class="CLutente" style="width: 100%;" id="ComuneResidenza" name="ComuneResidenza" >
                    '.$optCom.'
                </select>
            </div>
            <label_ComuneResidenza1 class="span1 ComuneNonTrovato_1" style="display: none;">'.$xml->getWord("Comune",$_SESSION['lan']).': </label_ComuneResidenza1>
            <div class="span3 ComuneNonTrovato_1" style="display: none;"><input class="CLutente" onblur="checkComune(this,\"ComuneResidenza\");" style="width: 98%" type="text" id="ComuneResidenza1" name="ComuneResidenza1" placeholder="'.$xml->getWord("ComuneUtente",$_SESSION['lan']).' ..."></div>
            <label_ComuneProvincia class="span1">'.$xml->getWord("Provincia",$_SESSION['lan']).': </label_ComuneProvincia>
            <div class="span3"><input class="CLutente validateCustom vld_Custom_r" style="width: 98%" type="text" id="ComuneProvincia" maxlength="3" name="ComuneProvincia" value="'.$utente["ProvinciaUtente"].'" placeholder="'.$xml->getWord("Provincia",$_SESSION['lan']).' ..."></div>
        </div>
        <div class="row-fluid">
            <label_ViaResidenza class="span1">'.$xml->getWord("VSP",$_SESSION['lan']).': </label_ViaResidenza>
            <div class="span2"><input class="CLutente validateCustom vld_Custom_r" style="width: 98%" value="'.$utente["IndirizzoUtente"].'" type="text" id="ViaResidenza" name="ViaResidenza" placeholder="'.$xml->getWord("Via",$_SESSION['lan']).' ..."></div>
            <label_NumeroResidenza class="span1">'.$xml->getWord("Numero",$_SESSION['lan']).': </label_NumeroResidenza>
            <div class="span1"><input class="CLutente validateCustom vld_Custom_r vld_Custom_n" style="width: 98%" value="'.$utente["CivicoUtente"].'" type="text" id="NumeroResidenza" name="NumeroResidenza" placeholder="'.$xml->getWord("NumeroPlace",$_SESSION['lan']).' ..."></div>
            <label_InternoResidenza class="span1">'.$xml->getWord("Interno",$_SESSION['lan']).': </label_InternoResidenza>
            <div class="span1"><input class="CLutente validateCustom vld_Custom_r vld_Custom_n" style="width: 98%" value="'.$utente["InternoUtente"].'" type="text" id="InternoResidenza" name="InternoResidenza" placeholder="'.$xml->getWord("InternoPlace",$_SESSION['lan']).' ..."></div>
            <label_EsponenteResidenza class="span1">'.$xml->getWord("Esponente",$_SESSION['lan']).': </label_EsponenteResidenza>
            <div class="span1"><input class="CLutente validateCustom vld_Custom_r vld_Custom_a" style="width: 98%" value="'.$utente["EsponenteUtente"].'" type="text" id="EsponenteResidenza" name="EsponenteResidenza" placeholder="'.$xml->getWord("EsponentePlace",$_SESSION['lan']).' ..."></div>
        </div>
        <div class="row-fluid">
            <label_TelefonoUtente class="span1">'.$xml->getWord("AbbrTelefono",$_SESSION['lan']).'.: </label_TelefonoUtente>
            <div class="span2"><input class="CLutente validateCustom vld_Custom_r vld_Custom_tel" style="width: 98%" value="'.$utente["TelefonoUtente"].'" type="text" id="TelefonoUtente" name="TelefonoUtente" placeholder="'.$xml->getWord("TelPlace",$_SESSION['lan']).' ..."></div>
            <label_EMailUtente class="span1">'.$xml->getWord("EMail",$_SESSION['lan']).': </label_EMailUtente>
            <div class="span2"><input class="CLutente validateCustom vld_Custom_r vld_Custom_mail" style="width: 98%" value="'.$utente["MailUtente"].'" type="text" id="EMailUtente" name="EMailUtente" placeholder="'.$xml->getWord("EMailPlace",$_SESSION['lan']).' ..."></div>
        </div>
        <div class="row-fluid" style="margin-top: 2%">
            <div class="span12" style="text-align: center;"><h4>'.$xml->getWord("TitoloDatiP",$_SESSION['lan']).'</h4></div>
        </div>
        <div class="row-fluid">
            <label_TipoPatente class="span1">'.$xml->getWord("TipoPat",$_SESSION['lan']).': </label_TipoPatente>
            <div class="span2">
                <select class="CLutente validateCustom vld_Custom_r" style="width: 100%;" id="TipoPatente" name="TipoPatente">
                    <option value="0">'.$xml->getWord("SelectTipoPat_1",$_SESSION['lan']).'</option>
                    <option value="1">'.$xml->getWord("SelectTipoPat_2",$_SESSION['lan']).'</option>
                    <option value="2">'.$xml->getWord("SelectTipoPat_3",$_SESSION['lan']).'</option>
                    <option value="3">'.$xml->getWord("SelectTipoPat_4",$_SESSION['lan']).'</option>
                </select>
            </div>
            <label_Categoria class="span1">'.$xml->getWord("CatAbr",$_SESSION['lan']).'. </label_Categoria>
            <div class="span2"><input class="CLutente validateCustom vld_Custom_r" style="width: 98%" value="'.$utente["CategoriaPatente"].'" type="text" id="Categoria" name="Categoria" placeholder="'.$xml->getWord("Categoria",$_SESSION['lan']).' ..."></div>
            <label_NumeroPatente class="span1">'.$xml->getWord("Numero",$_SESSION['lan']).' </label_NumeroPatente>
            <div class="span2"><input class="CLutente validateCustom vld_Custom_r" style="width: 98%" value="'.$utente["NumeroPatente"].'" type="text" id="NumeroPatente" name="NumeroPatente" placeholder="'.$xml->getWord("NumPat",$_SESSION['lan']).' ..."></div>
            <label_DataRilascio class="span1">'.$xml->getWord("DataRilascio",$_SESSION['lan']).' </label_DataRilascio>
            <div class="span2"><input class="CLutente date validateCustom vld_Custom_r vld_Custom_date" style="width: 98%" type="text" id="DataRilascio" name="DataRilascio" placeholder="'.$xml->getWord("DatePlace",$_SESSION['lan']).'"></div>
        </div>
        <div class="row-fluid">
            <label_StatoRilascio class="span1">'.$xml->getWord("StatoR",$_SESSION['lan']).' </label_StatoRilascio>
            <div class="span2">
                <select class="CLutente validateCustom vld_Custom_r" style="width: 100%;" id="StatoRilascio" name="StatoRilascio">
                    '.$optSt.' 
                </select>
            </div>
            <label_AutoritaRilascio class="span1">'.$xml->getWord("AutoritaR",$_SESSION['lan']).' </label_AutoritaRilascio>
            <div class="span2"><input class="CLutente validateCustom vld_Custom_r" style="width: 98%" value="'.$utente["AutoritaRilascio"].'" type="text" id="AutoritaRilascio" name="AutoritaRilascio" placeholder="'.$xml->getWord("AutoritaRPlace",$_SESSION['lan']).' ..."></div>
            <label_DataValidita class="span1">'.$xml->getWord("DataVal",$_SESSION['lan']).' </label_DataValidita>
            <div class="span2"><input class="CLutente date validateCustom vld_Custom_r vld_Custom_date" style="width: 98%" value="'.$cls_date->Get_DateNewFormat($utente["DataValiditaPatente"],"DB").'" type="text" id="DataValidita" name="DataValidita" placeholder="'.$xml->getWord("DataValPlace",$_SESSION['lan']).'"></div> 
        </div>
        <div  class="row-fluid">
            <label_DataPrimoRilascio class="span2">'.$xml->getWord("DataPrimoRilascioUt",$_SESSION['lan']).' </label_DataPrimoRilascio>
            <div class="span2"><input class="CLutente date validateCustom vld_Custom_r vld_Custom_date" style="width: 98%" value="" type="text" id="DataPrimoRilascio" name="DataPrimoRilascio" placeholder="'.$xml->getWord("DataPrimoRilascioUt",$_SESSION['lan']).'"></div>
        </div>
        <div class="row-fluid" style="margin-top: 2%;">
            <div class="span4">'.$xml->getWord("CheckDittaProp",$_SESSION['lan']).': </div>
            <div class="span8">
                <label class="radio">
                    <input type="radio" onchange="AbilitaDisabilitaTr(\"DatiLR\",\"TipoTrasgressore\");" name="TipoTrasgressore" id="optionsRadios3" value="PV" '.$radioChoice_1.'>
                    '.$xml->getWord("CheckDittaProp_1",$_SESSION['lan']).'
                </label>
                <br>
                <label class="radio">
                    <input type="radio" onchange="AbilitaDisabilitaTr(\"DatiLR\",\"TipoTrasgressore\");" name="TipoTrasgressore" id="optionsRadios4" value="LR" '.$radioChoice_2.'>
                    '.$xml->getWord("CheckDittaProp_2",$_SESSION['lan']).'
                </label>
                <br>
                <label class="radio">
                    <input type="radio" onchange="AbilitaDisabilitaTr(\"DatiLR\",\"TipoTrasgressore\");" name="TipoTrasgressore" id="optionsRadios5" value="NL" '.$radioChoice_3.'>
                    '.$xml->getWord("CheckDittaProp_3",$_SESSION['lan']).'
                </label>
            </div>
        </div>
        <div class="row-fluid DatiLR" style="margin-top: 2%">
            <div class="span12" style="text-align: center;"><h4>'.$xml->getWord("TitoloDatiDitta",$_SESSION['lan']).'</h4></div>
        </div>
        <div class="row-fluid DatiLR">
            <label_NomeDitta class="span1">'.$xml->getWord("NomeDitta",$_SESSION['lan']).': </label_NomeDitta>
            <div class="span3"><input class="CLditta validateCustom vld_Custom_r" style="width: 98%" value="'.$utente["NomeDitta"].'" type="text" id="NomeDitta" name="NomeDitta" placeholder="'.$xml->getWord("NomeDittaPlace",$_SESSION['lan']).'..."></div>
            <label_PIDitta class="span1">'.$xml->getWord("PIDitta",$_SESSION['lan']).': </label_PIDitta>
            <div class="span3"><input class="CLditta validateCustom vld_Custom_r vld_Custom_PI" style="width: 98%" value="'.$utente["PI"].'" type="text" id="PIDitta" name="PIDitta" placeholder="'.$xml->getWord("PIDittaPlace",$_SESSION['lan']).' ..."></div>
        </div>
        <div class="row-fluid DatiLR" style="margin-top: 2%;">
            <label_SedeLR class="span1">'.$xml->getWord("Sede",$_SESSION['lan']).' </label_SedeLR>
            <div class="span2">
                <select class="CLditta validateCustom vld_Custom_r" onchange="ScegliInputComune(\"ComuneEsistente_2\",\"ComuneNonTrovato_2\",\"ComuneDitta1\",\"ComuneDitta\",this);SetStatoCompleto(this,\"StatoDittaCompleto\");" style="width: 100%;" id="SedeLR" name="SedeLR">
                    '.$optSt.' 
                </select>
            </div>
            <label_ComuneDitta class="span1 ComuneEsistente_2">'.$xml->getWord("Comune",$_SESSION['lan']).': </label_ComuneDitta>
            <div class="span2 ComuneEsistente_2">
                <select class="CLditta" style="width: 100%;" id="ComuneDitta" name="ComuneDitta" >
                    '.$optCom.'
                </select>
            </div>
            <label_ComuneDitta1 class="span1 ComuneNonTrovato_2" style="display: none;">'.$xml->getWord("Comune",$_SESSION['lan']).': </label_ComuneDitta1>
            <div class="span3 ComuneNonTrovato_2" style="display: none;"><input class="CLditta" onblur="checkComune(this,\"ComuneDitta\");" style="width: 98%" type="text" id="ComuneDitta1" name="ComuneDitta1" placeholder="'.$xml->getWord("ComuneDitta",$_SESSION['lan']).' ..."></div>
            <label_ProvinciaDitta class="span1">'.$xml->getWord("Provincia",$_SESSION['lan']).': </label_ProvinciaDitta>
            <div class="span3"><input class="CLditta validateCustom vld_Custom_r" style="width: 98%" maxlength="3" type="text" id="ProvinciaDitta" name="ProvinciaDitta" value="'.$utente["ProvinciaDitta"].'" placeholder="'.$xml->getWord("Provincia",$_SESSION['lan']).' ..."></div>
        </div>
        <div class="row-fluid DatiLR">
            <label_ViaDitta class="span1">'.$xml->getWord("VSP",$_SESSION['lan']).': </label_ViaDitta>
            <div class="span2"><input class="CLditta validateCustom vld_Custom_r" style="width: 98%" value="'.$utente["IndirizzoDitta"].'" type="text" id="ViaDitta" name="ViaDitta" placeholder="'.$xml->getWord("Via",$_SESSION['lan']).'..."></div>
            <label_NumeroDitta class="span1">'.$xml->getWord("Numero",$_SESSION['lan']).': </label_NumeroDitta>
            <div class="span1"><input class="CLditta validateCustom vld_Custom_r vld_Custom_n" style="width: 98%" value="'.$utente["CivicoDitta"].'" type="text" id="NumeroDitta" name="NumeroDitta" placeholder="'.$xml->getWord("NumeroPlace",$_SESSION['lan']).' ..."></div>
            <label_InternoDitta class="span1">'.$xml->getWord("Interno",$_SESSION['lan']).'.: </label_InternoDitta>
            <div class="span1"><input class="CLditta validateCustom vld_Custom_r vld_Custom_n" style="width: 98%" value="'.$utente["InternoDitta"].'" type="text" id="InternoDitta" name="InternoDitta" placeholder="'.$xml->getWord("InternoPlace",$_SESSION['lan']).' ..."></div>
            <label_EsponenteDitta class="span1">'.$xml->getWord("Esponente",$_SESSION['lan']).': </label_EsponenteDitta>
            <div class="span1"><input class="CLditta validateCustom vld_Custom_r vld_Custom_a" style="width: 98%" value="'.$utente["EsponenteDitta"].'" type="text" id="EsponenteDitta" name="EsponenteDitta" placeholder="'.$xml->getWord("EsponentePlace",$_SESSION['lan']).' ..."></div>
        </div>
        <div class="row-fluid DatiLR">
            <label_TelefonoDitta class="span1">'.$xml->getWord("AbbrTelefono",$_SESSION['lan']).'.: </label_TelefonoDitta>
            <div class="span2"><input class="CLditta validateCustom vld_Custom_r vld_Custom_tel" style="width: 98%" value="'.$utente["TelefonoDitta"].'" type="text" id="TelefonoDitta" name="TelefonoDitta" placeholder="'.$xml->getWord("TelPlace",$_SESSION['lan']).' ..."></div>
            <label_EMailDitta class="span1">'.$xml->getWord("EMail",$_SESSION['lan']).': </label_EMailDitta>
            <div class="span2"><input class="CLditta validateCustom vld_Custom_r vld_Custom_mail" style="width: 98%" value="'.$utente["MailDitta"].'" type="text" id="EMailDitta" name="EMailDitta" placeholder="'.$xml->getWord("EMailPlace",$_SESSION['lan']).' ..."></div>
        </div>
    </div>
    <div class="row-fluid Trasgressore" style="display: none;">
        <div class="row-fluid" style="margin-top: 2%">
            <div class="span12" style="text-align: center;"><h4>'.$xml->getWord("TitoloTrasgressore",$_SESSION['lan']).'</h4></div>
        </div>
        <div class="row-fluid">
            <label_NomeTrasgressore class="span1">'.$xml->getWord("Nome",$_SESSION['lan']).': </label_NomeTrasgressore>
            <div class="span2"><input class="CLtrasgressore validateCustom vld_Custom_r" style="width: 98%" value="'.$trasgressore["Nome"].'" type="text" id="NomeTrasgressore" name="NomeTrasgressore" placeholder="'.$xml->getWord("NomePlaceTr",$_SESSION['lan']).' ..."></div>
            <label_CognomeTrasgressore class="span1">'.$xml->getWord("Cognome",$_SESSION['lan']).': </label_CognomeTrasgressore>
            <div class="span2"><input class="CLtrasgressore validateCustom vld_Custom_r" style="width: 98%" value="'.$trasgressore["Cognome"].'" type="text" id="CognomeTrasgressore" name="CognomeTrasgressore" placeholder="'.$xml->getWord("CognomePlaceTr",$_SESSION['lan']).' ..."></div>
            <label_SessoTrasgressore class="span1">'.$xml->getWord("Sesso",$_SESSION['lan']).': </label_SessoTrasgressore>
            <div class="span1">
                <select class="CLtrasgressore validateCustom vld_Custom_r" style="width: 100%;" id="SessoTrasgressore" name="SessoTrasgressore">
                    <option></option>
                    <option value="M" '.$sessoTrM.'>'.$xml->getWord("Maschio",$_SESSION['lan']).'</option>
                    <option value="F" '.$sessoTrF.'>'.$xml->getWord("Femmina",$_SESSION['lan']).'</option>
                </select>
            </div>
            <label_CFTrasgressore class="span1">'.$xml->getWord("CodFiscaleAbbr",$_SESSION['lan']).': </label_CFTrasgressore>
            <div class="span3"><input class="CLtrasgressore validateCustom vld_Custom_r vld_Custom_CF" style="width: 98%" value="'.$trasgressore["CF"].'" type="text" id="CFTrasgressore" name="CFTrasgressore" placeholder="'.$xml->getWord("CodFiscaleTrasgressore",$_SESSION['lan']).' ..."></div>
        </div>
        <div class="row-fluid">
            <label_StatoNascitaTrasgressore class="span1">'.$xml->getWord("Nato",$_SESSION['lan']).': </label_StatoNascitaTrasgressore>
            <div class="span2">
                <select class="CLtrasgressore validateCustom vld_Custom_r" onchange="ScegliInputComune(\"ComuneEsistente_3\",\"ComuneNonTrovato_3\",\"ComuneNascitaTrasgressore1\",\"ComuneNascitaTrasgressore\",this);SetStatoCompleto(this,\"StatoNascitaTrasgressoreCompleto\");" style="width: 100%;" id="StatoNascitaTrasgressore" name="StatoNascitaTrasgressore">
                    '.$optSt.'
                </select>
            </div>
            <label_ComuneNascitaTrasgressore class="span1 ComuneEsistente_3">'.$xml->getWord("Comune",$_SESSION['lan']).': </label_ComuneNascitaTrasgressore>
            <div class="span2 ComuneEsistente_3">
                <select class="CLtrasgressore" style="width: 100%;" id="ComuneNascitaTrasgressore" name="ComuneNascitaTrasgressore">
                    '.$optCom.'
                </select>
            </div>
            <label_ComuneNascitaTrasgressore1 class="span1 ComuneNonTrovato_3" style="display: none;">'.$xml->getWord("Comune",$_SESSION['lan']).': </label_ComuneNascitaTrasgressore1>
            <div class="span3 ComuneNonTrovato_3" style="display: none;"><input class="CLtrasgressore" onblur="checkComune(this,\"ComuneNascitaTrasgressore\");" style="width: 98%" type="text" id="ComuneNascitaTrasgressore1" name="ComuneNascitaTrasgressore1" placeholder="'.$xml->getWord("ComuneTrasgressore",$_SESSION['lan']).' ..."></div>
            <label_DataNascitaTrasgressore class="span2">'.$xml->getWord("DataNascita",$_SESSION['lan']).': </label_DataNascitaTrasgressore>
            <div class="span3"><input style="width: 98%" value="'.$cls_date->Get_DateNewFormat($trasgressore["DataNascita"],"DB").'" class="CLtrasgressore date validateCustom vld_Custom_r vld_Custom_date" type="text" id="DataNascitaTrasgressore" name="DataNascitaTrasgressore" placeholder="'.$xml->getWord("DataNascitaTrasgressore",$_SESSION['lan']).' ..."></div>
        </div>
        <div class="row-fluid" style="margin-top: 2%">
            <div class="span12" style="text-align: center;"><h4>'.$xml->getWord("TitoloResidenzaTrasgressore",$_SESSION['lan']).'</h4></div>
        </div>
        <div class="row-fluid">
            <label_StatoResidenzaTrasgressore class="span1">'.$xml->getWord("ResidenteIn",$_SESSION['lan']).': </label_StatoResidenzaTrasgressore>
            <div class="span2">
                <select class="CLtrasgressore validateCustom vld_Custom_r" onchange="ScegliInputComune(\"ComuneEsistente_4\",\"ComuneNonTrovato_4\",\"ComuneResidenzaTrasgressore1\",\"ComuneResidenzaTrasgressore\",this);SetStatoCompleto(this,\"StatoResidenzaTrasgressoreCompleto\");" style="width: 100%;" id="StatoResidenzaTrasgressore" name="StatoResidenzaTrasgressore">
                    '.$optSt.'
                </select>
            </div>
            <label_ComuneResidenzaTrasgressore class="span1 ComuneEsistente_4">'.$xml->getWord("Comune",$_SESSION['lan']).': </label_ComuneResidenzaTrasgressore>
            <div class="span2 ComuneEsistente_4">
                <select class="CLtrasgressore" style="width: 100%;" id="ComuneResidenzaTrasgressore" name="ComuneResidenzaTrasgressore" >
                    '.$optCom.'
                </select>
            </div>
            <label_ComuneResidenzaTrasgressore1 class="span1 ComuneNonTrovato_4" style="display: none;">'.$xml->getWord("Comune",$_SESSION['lan']).': </label_ComuneResidenzaTrasgressore1>
            <div class="span3 ComuneNonTrovato_4" style="display: none;"><input class="CLtrasgressore" onblur="checkComune(this,\"ComuneResidenzaTrasgressore\");" style="width: 98%" type="text" id="ComuneResidenzaTrasgressore1" name="ComuneResidenzaTrasgressore1" placeholder="'.$xml->getWord("ComuneTrasgressore",$_SESSION['lan']).' ..."></div>
            <label_ProvinciaTrasgressore class="span1">'.$xml->getWord("Provincia",$_SESSION['lan']).': </label_ProvinciaTrasgressore>
            <div class="span3"><input class="CLtrasgressore validateCustom vld_Custom_r" maxlength="3" style="width: 98%" type="text" id="ProvinciaTrasgressore" name="ProvinciaTrasgressore" value="'.$trasgressore["ProvinciaTrasgressore"].'" placeholder="'.$xml->getWord("Provincia",$_SESSION['lan']).' ..."></div>
        </div>
        <div class="row-fluid">
            <label_ViaResidenzaTrasgressore class="span1">'.$xml->getWord("VSP",$_SESSION['lan']).': </label_ViaResidenzaTrasgressore>
            <div class="span2"><input class="CLtrasgressore validateCustom vld_Custom_r" style="width: 98%" value="'.$trasgressore["IndirizzoTrasgressore"].'" type="text" id="ViaResidenzaTrasgressore" name="ViaResidenzaTrasgressore" placeholder="'.$xml->getWord("Via",$_SESSION['lan']).' ..."></div>
            <label_NumeroResidenzaTrasgressore class="span1">'.$xml->getWord("Numero",$_SESSION['lan']).': </label_NumeroResidenzaTrasgressore>
            <div class="span1"><input class="CLtrasgressore validateCustom vld_Custom_r vld_Custom_n" style="width: 98%" value="'.$trasgressore["CivicoTrasgressore"].'" type="text" id="NumeroResidenzaTrasgressore" name="NumeroResidenzaTrasgressore" placeholder="'.$xml->getWord("NumeroPlace",$_SESSION['lan']).' ..."></div>
            <label_InternoResidenzaTrasgressore class="span1">'.$xml->getWord("Interno",$_SESSION['lan']).'.: </label_InternoResidenzaTrasgressore>
            <div class="span1"><input class="CLtrasgressore validateCustom vld_Custom_r vld_Custom_n" style="width: 98%" value="'.$trasgressore["InternoTrasgressore"].'" type="text" id="InternoResidenzaTrasgressore" name="InternoResidenzaTrasgressore" placeholder="'.$xml->getWord("InternoPlace",$_SESSION['lan']).' ..."></div>
            <label_EsponenteResidenzaTrasgressore class="span1">'.$xml->getWord("Esponente",$_SESSION['lan']).': </label_EsponenteResidenzaTrasgressore>
            <div class="span1"><input class="CLtrasgressore validateCustom vld_Custom_r vld_Custom_a" style="width: 98%" value="'.$trasgressore["EsponenteTrasgressore"].'" type="text" id="EsponenteResidenzaTrasgressore" name="EsponenteResidenzaTrasgressore" placeholder="'.$xml->getWord("EsponentePlace",$_SESSION['lan']).' ..."></div>
        </div>
        <div class="row-fluid">
            <label_TelefonoUtenteTrasgressore class="span1">'.$xml->getWord("AbbrTelefono",$_SESSION['lan']).'.: </label_TelefonoUtenteTrasgressore>
            <div class="span2"><input class="CLtrasgressore validateCustom vld_Custom_r vld_Custom_tel" style="width: 98%" value="'.$trasgressore["TelefonoTrasgressore"].'" type="text" id="TelefonoUtenteTrasgressore" name="TelefonoUtenteTrasgressore" placeholder="'.$xml->getWord("TelPlace",$_SESSION['lan']).'..."></div>
            <label_EMailUtenteTrasgressore class="span1">'.$xml->getWord("EMail",$_SESSION['lan']).': </label_EMailUtenteTrasgressore>
            <div class="span2"><input class="CLtrasgressore validateCustom vld_Custom_r vld_Custom_mail" style="width: 98%" value="'.$trasgressore["MailTrasgressore"].'" type="text" id="EMailUtenteTrasgressore" name="EMailUtenteTrasgressore" placeholder="'.$xml->getWord("EMailPlace",$_SESSION['lan']).' ..."></div>
        </div>
        <div class="row-fluid" style="margin-top: 2%">
            <div class="span12" style="text-align: center;"><h4>'.$xml->getWord("TitoloDatiPatenteTrasgressore",$_SESSION['lan']).'</h4></div>
        </div>
        <div class="row-fluid">
            <label_TipoPatenteTrasgressore class="span1">'.$xml->getWord("TipoPat",$_SESSION['lan']).': </label_TipoPatenteTrasgressore>
            <div class="span2">
                <select class="CLtrasgressore validateCustom vld_Custom_r" style="width: 100%;" id="TipoPatenteTrasgressore" name="TipoPatenteTrasgressore">
                    <option value="0">'.$xml->getWord("SelectTipoPat_1",$_SESSION['lan']).'</option>
                    <option value="1">'.$xml->getWord("SelectTipoPat_2",$_SESSION['lan']).'</option>
                    <option value="2">'.$xml->getWord("SelectTipoPat_3",$_SESSION['lan']).'</option>
                    <option value="3">'.$xml->getWord("SelectTipoPat_4",$_SESSION['lan']).'</option>
                </select>
            </div>
            <label_CategoriaTrasgressore class="span1">'.$xml->getWord("CatAbr",$_SESSION['lan']).'. </label_CategoriaTrasgressore>
            <div class="span2"><input class="CLtrasgressore validateCustom vld_Custom_r" style="width: 98%" value="'.$trasgressore["CategoriaPatente"].'" type="text" id="CategoriaTrasgressore" name="CategoriaTrasgressore" placeholder="'.$xml->getWord("Categoria",$_SESSION['lan']).' ..."></div>
            <label_NumeroPatenteTrasgressore class="span1">'.$xml->getWord("Numero",$_SESSION['lan']).' </label_NumeroPatenteTrasgressore>
            <div class="span2"><input class="CLtrasgressore validateCustom vld_Custom_r" style="width: 98%" value="'.$trasgressore["NumeroPatente"].'" type="text" id="NumeroPatenteTrasgressore" name="NumeroPatenteTrasgressore" placeholder="'.$xml->getWord("NumPat",$_SESSION['lan']).' ..."></div>
            <label_DataRilascioTrasgressore class="span1">'.$xml->getWord("DataRilascio",$_SESSION['lan']).' </label_DataRilascioTrasgressore>
            <div class="span2"><input class="CLtrasgressore date validateCustom vld_Custom_r vld_Custom_date" style="width: 98%" type="text"  id="DataRilascioTrasgressore" name="DataRilascioTrasgressore" placeholder="'.$xml->getWord("DatePlace",$_SESSION['lan']).'"></div>
        </div>
        <div class="row-fluid">
            <label_StatoRilascioTrasgressore class="span1">'.$xml->getWord("StatoR",$_SESSION['lan']).' </label_StatoRilascioTrasgressore>
            <div class="span2">
                <select class="CLtrasgressore validateCustom vld_Custom_r" style="width: 100%;" id="StatoRilascioTrasgressore" name="StatoRilascioTrasgressore">
                    '.$optSt.' 
                </select>
            </div>
            <label_AutoritaRilascioTrasgressore class="span1">'.$xml->getWord("AutoritaR",$_SESSION['lan']).' </label_AutoritaRilascioTrasgressore>
            <div class="span2"><input class="CLtrasgressore validateCustom vld_Custom_r" style="width: 98%" value="'.$trasgressore["AutoritaRilascio"].'" type="text" id="AutoritaRilascioTrasgressore" name="AutoritaRilascioTrasgressore" placeholder="'.$xml->getWord("AutoritaRPlace",$_SESSION['lan']).' ..."></div>
            <label_DataValiditaTrasgressore class="span1">'.$xml->getWord("DataVal",$_SESSION['lan']).' </label_DataValiditaTrasgressore>
            <div class="span2"><input class="CLtrasgressore date validateCustom vld_Custom_r vld_Custom_date" style="width: 98%" value="'.$trasgressore["DataValiditaPatente"].'" type="text" id="DataValiditaTrasgressore" name="DataValiditaTrasgressore" placeholder="'.$xml->getWord("DataValPlace",$_SESSION['lan']).'"></div>  
        </div>
        <div class="row-fluid">
            <label_DataPrimoRilascioTrasgressore class="span2">'.$xml->getWord("DataPrimoRilascioUt",$_SESSION['lan']).' </label_DataPrimoRilascioTrasgressore>
            <div class="span2"><input class="CLtrasgressore date validateCustom vld_Custom_r vld_Custom_date" style="width: 98%" value="" type="text" id="DataPrimoRilascioTrasgressore" name="DataPrimoRilascioTrasgressore" placeholder="'.$xml->getWord("DataPrimoRilascioUt",$_SESSION['lan']).'"></div>
        </div>
    </div>
    <button class="btn btn-primary" type="button" onclick="salvaTrasgressore(\"generaPdf.php\");">'.$xml->getWord("BtnGeneratePdf",$_SESSION['lan']).'</button>
    <div class="row-fluid uploadFile" style="margin-top: 2%;display:none;">
        <div class="span12" style="text-align: center;">
            <p><b>'.$xml->getWord("AllegaFile",$_SESSION['lan']).'</b></p>
            <p style="color: red;"><b>'.$xml->getWord("AllegaFile_1",$_SESSION['lan']).'</b></p>
            <p><b>'.$xml->getWord("caratteristicheFile",$_SESSION['lan']).'</b></p>
        </div>
    </div>
    <div class="row-fluid uploadFile" style="display:none;">
        <div class="span1"></div>
        <div class="span2">
            <div class="file_drag_area">
                '.$xml->getWord("informazioniDrag",$_SESSION['lan']).'
            </div>
        </div>
        <div class="span8" id="container_uploaded_file">
            <div class="row-fluid" style="min-height: 130px;display: flex;"><div class="span12" id="uploaded_file"></div></div>
        </div>
    </div>
    <div class="row-fluid" id="errorUpload_0" style="display:none;">
        <div class="span1"></div>
        <div class="span10"  style="color: #B34F4F; text-align: center;">'.$xml->getWord("errore_0",$_SESSION['lan']).'</div>
    </div>
    <div class="row-fluid" id="errorUpload_1" style="display:none;">
        <div class="span1"></div>
        <div class="span10"  style="color: #B34F4F; text-align: center;">'.$xml->getWord("errore_1",$_SESSION['lan']).'</div>
    </div>
    <div class="row-fluid" id="errorUpload_2" style="display:none;">
        <div class="span1"></div>
        <div class="span10"  style="color: #B34F4F; text-align: center;">'.$xml->getWord("errore_2",$_SESSION['lan']).'</div>
    </div>
    <button class="btn btn-primary uploadFile" style="display:none;margin-top: 3%;" type="button" onclick="salvaTrasgressore(\"saveOffenders.php\");">'.$xml->getWord("BtnSave",$_SESSION['lan']).'</button>
</form>
<div id="message_offenders"></div>';


echo json_encode(
    array(
        "id_div" => "content_offenders",
        "content" => str_replace('\"',"'",$content),
        "StatoRilascioPatente" => array("key" => "StatoRilascio", "value" => $utente["StatoRilascioPatente"]),
        "StatoRilascioPatenteTrasgressore" => array("key" => "StatoRilascioTrasgressore", "value" => $trasgressore["StatoRilascioPatenteTrasgressore"]),
        "statoNascitaUtente" => array("key" => "StatoNascita", "value" => $utente["StatoNascita"]),
        "comuneNascitaUtente" => array("key" => "ComuneNascita", "key_1" => "ComuneNascita1", "value" => $utente["ComuneNascita"]),
        "statoResidenzaUtente" => array("key" => "StatoResidenza", "value" => $utente["StatoUtente"]),
        "comuneResidenzaUtente" => array("key" => "ComuneResidenza", "key_1" => "ComuneResidenza1", "value" => $utente["ComuneUtente"], "value_1" => $utente["ComuneUtente1"]),
        "StatoDitta" => array("key" => "SedeLR", "value" => $utente["StatoDitta"]),
        "ComuneDitta" => array("key" => "ComuneDitta", "key_1" => "ComuneDitta1", "value" => $utente["ComuneDitta"], "value_1" => $utente["ComuneDitta1"]),
        "StatoNascitaTrasgressore" => array("key" => "StatoNascitaTrasgressore", "value" => $trasgressore["StatoNascita"]),
        "ComuneNascitaTrasgressore" => array("key" => "ComuneNascitaTrasgressore", "key_1" => "ComuneNascitaTrasgressore1", "value" => $trasgressore["ComuneNascita"]),
        "StatoResidenzaTrasgressore" => array("key" => "StatoResidenzaTrasgressore", "value" => $trasgressore["StatoTrasgressore"]),
        "ComuneResidenzaTrasgressore" => array("key" => "ComuneResidenzaTrasgressore", "key_1" => "ComuneResidenzaTrasgressore1", "value" => $trasgressore["ComuneTrasgressore"], "value_1" => $trasgressore["ComuneTrasgressore1"]),
    )
);
?>















