<?php
if (!session_id()) session_start();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

include_once "../Librerie/cls_DateTimeInLine.php";
include_once "../Librerie/cls_db.php";
include_once "../Librerie/cls_Utils.php";

require '../Librerie/PHPMailer/src/Exception.php';
require '../Librerie/PHPMailer/src/PHPMailer.php';
require '../Librerie/PHPMailer/src/SMTP.php';
require "../Librerie/phpqrcode/qrlib.php";
require "../Librerie/tcpdf/tcpdf.php";
include_once "../Librerie/XmlLanguageReader.php";


class MYPDF extends TCPDF
{
    public $tipo;
    public $riga_1;
    public $riga_2;
    public $riga_3;
    public $riga_4;
    public $riga_5;

    public function AddMyPage ($tip, $formato = null)
    {
        $this->tipo = $tip;
        $this->AddPage ($formato);
    }

    public function Header()
    {

        $this->SetFont('helvetica', 'B', 11);
        $this->ln(5);

        $this->Cell(0, 5, $this->riga_1, 0, false, 'L', 0, '', 0, false, 'T', 'M');
        $this->ln();

        $this->SetFont('helvetica', '', 8);
        $this->Cell(0, 0, $this->riga_2, 0, false, 'L', 0, '', 0, false, 'T', 'M');
        $this->ln();
        $this->Cell(0, 0, $this->riga_3, 0, false, 'L', 0, '', 0, false, 'T', 'M');
        $this->ln();
        $this->Cell(0, 0, $this->riga_4, 0, false, 'L', 0, '', 0, false, 'T', 'M');
        $this->ln();
        $this->Cell(0, 0, $this->riga_5, 0, false, 'L', 0, '', 0, false, 'T', 'M');
        $this->ln();
    }

    public function Footer()
    {

        $this->SetY(-10);
        $this->SetFont('helvetica', 'N', 7);
        $this->Cell(0, 5, "Pag. ". $this->getPage() ." - ".date("d/m/Y H\hi:s"), 0, false, 'C', 0, '', 0, false, 'T', 'M');

    }

}

$arrContextOptions=array(
    "ssl"=>array(
        "verify_peer"=>false,
        "verify_peer_name"=>false,
    ),
);

if($_POST['ComuneVerbale']=='D711')
{
    $URL_DATA = "http://formigine.ovunque-si.it/traffic_law/";
}
else {
    $URL_DATA = "https://gitco.ovunque-si.it/traffic_law/";
}

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

$cls_date = new cls_DateTimeI("DB",false);
$cls_Utils = new cls_Utils($dbConnect);
$cls_db = new cls_db("62.149.150.179","Sql627048","41c608f5","Sql627048_1");
//$cls_db_sarida = new cls_db("62.94.231.188","root","GP~0o>hU@:/:q*","sarida");
/** $cls_db_traffic_law = new cls_db("62.94.231.188","root","GP~0o>hU@:/:q*", "traffic_law"); // questo è da sostituire con curl o altro metodo **/
//$cls_db = new cls_db("localhost","root", "", "polizia_municipale");
/*$cls_db_sarida = new cls_db("localhost","root","","sarida");
$cls_db_traffic_law = new cls_db("localhost", "root", "", "traffic_law");*/

$xml = new xmlLanguageReader("../language.xml");

//echo "<br>".$ID_Utente_Trasgressore." --- ".mysqli_num_rows ( $result );
$dataViolazione = $_POST["AnnoVerbale"]."-".$_POST["MeseVerbale"]."-".$_POST["GiornoVerbale"]." ".$_POST["OreVerbale"].":".$_POST["MinutiVerbale"];
$dataViolazioneResoconto = $_POST["GiornoVerbale"]."-".$_POST["MeseVerbale"]."-".$_POST["AnnoVerbale"]." ".$_POST["OreVerbale"].":".$_POST["MinutiVerbale"];

$query = "SELECT ID FROM `trasgressori` where Comune_Verbale = '".$_POST['ComuneVerbale']."' AND Targa_Veicolo = '".$_POST['TargaVeicolo']."' AND Crono_ID = ".$_POST['CronoNumeroVerbale']." AND Crono_Anno = ".$_POST['CronoAnnoVerbale']." AND Crono_T_Verbale = '".$_POST['CronotVervbale']."' AND Data_Violazione = '".$dataViolazione."' ";
$result = $cls_db->getResults($cls_db->ExecuteQuery($query));

if(count($result) > 0){
    $where = array("ID" => $result[0]["ID"]);
}

/*$query = "SELECT ManagerSector, ManagerCity, ManagerProvince, ManagerZIP, ManagerAddress, ManagerPEC FROM customer WHERE CityId = '".$_POST['ComuneVerbale']."'";
$resultManager = $cls_db_traffic_law->getArrayLine($cls_db_traffic_law->ExecuteQuery($query));*/

/*if($_POST['ComuneVerbale']=='D711')
{
    $URL_DATA = "http://formigine.ovunque-si.it/traffic_law/";
}
else {
    $URL_DATA = "https://gitcocoll.ovunque-si.it/gitco2/traffic_law/";
}*/
$CC = $_POST['ComuneVerbale'];

$resultManager = file_get_contents($URL_DATA.'rest_richiestaDati.php?op=customer&cv='.$CC,false, stream_context_create($arrContextOptions));
$resultManager = json_decode($resultManager);

if($resultManager->Esito == "OK"){
    $managerSector = $resultManager->Dati[0]->ManagerSector;
    $managerProvince = $resultManager->Dati[0]->ManagerProvince;
    $managerCity = $resultManager->Dati[0]->ManagerCity;
    $managerZIP = $resultManager->Dati[0]->ManagerZIP;
    $managerAddress = $resultManager->Dati[0]->ManagerAddress;
    $managerPEC = $resultManager->Dati[0]->ManagerPEC;
}
else {
    $managerSector = "";
    $managerProvince = "";
    $managerCity = "";
    $managerZIP = "";
    $managerAddress = "";
    $managerPEC = "";
}

$CodiceAnticontraffazione = $_POST["codiceAnticontraffazione"];




$errorConnectionLevel = "L";
$matrixPointSize = 4;
$data = $CodiceAnticontraffazione;
$path = $cls_Utils->crea_dir($_SERVER['DOCUMENT_ROOT']."inc/uploads/qrcode");
$qrcodeName = $path."/qrcodeCodiceAnticontraffazione.png";//vedere se funzione il percorso
QRcode::png($data,$qrcodeName,$errorConnectionLevel,$matrixPointSize,2);

$fontPrimo = 8.5;
$fontSecondo = 6.5;

$pdf = new MYPDF("L", "mm", "A4", true, 'UTF-8', false);

$pdf->riga_1 = $xml->getWord("comuneDi",$_SESSION['lan'])." ".$managerCity;
$pdf->riga_2 = $xml->getWord("serviziDi",$_SESSION['lan'])." ".$managerSector;
$pdf->riga_3 = $managerAddress;
$pdf->riga_4 = $managerZIP. " " .$managerCity. " (" . $managerProvince . ")";
$pdf->riga_5 = "800.582.480";

$pdf->AddPage('P', 'A4');
$pdf->SetFont('Helvetica', 'B', $fontPrimo);

$titoloPdf = $xml->getWord("resocontoDic",$_SESSION['lan']);

$pageMargins  = $pdf->getMargins();     // Get all margins as array
$headerMargin = $pageMargins['top'];

$pdf->MultiCell( 0 , 0, $titoloPdf , false , "C" ,0 , 0 , "", $pdf->GetY() + $headerMargin + 25);
$pdf->Ln();
//$pdf->MultiCell( 0 , 2, "" , false , "L" ,0 , 0 );
//$pdf->Ln();

$pdf->SetFont('Helvetica', '', $fontSecondo);
$pdf->MultiCell( 40 , 0, strtoupper($xml->getWord("Comune",$_SESSION['lan'])).":" , false , "L" ,0 , 0,"",$pdf->GetY()+2 );
$pdf->MultiCell( 120 , 0, $managerCity , false , "L" ,0 , 0 );
$pdf->Ln();
$pdf->MultiCell( 40 , 0, strtoupper($xml->getWord("GiornoOra",$_SESSION['lan'])).":" , false , "L" ,0 , 0,"",$pdf->GetY()+2 );
$pdf->MultiCell( 120 , 0, $dataViolazioneResoconto , false , "L" ,0 , 0 );
$pdf->Ln();
$pdf->MultiCell( 40 , 0, strtoupper($xml->getWord("cronologico",$_SESSION['lan'])).":" , false , "L" ,0 , 0,"",$pdf->GetY()+2 );
$pdf->MultiCell( 120 , 0, $_POST['CronoNumeroVerbale']."/".$_POST['CronoAnnoVerbale']."/".$_POST['CronotVervbale'] , false , "L" ,0 , 0 );
$pdf->Ln();
$pdf->MultiCell( 40 , 0, strtoupper($xml->getWord("Frase_5_Offenders",$_SESSION['lan'])).":" , false , "L" ,0 , 0,"",$pdf->GetY()+2 );
$pdf->MultiCell( 120 , 0, $_POST['TargaVeicolo'] , false , "L" ,0 , 0 );
$pdf->Ln();


switch($_POST["TipoDichiarazione"]){
    case 1:
    {

        $pdf->MultiCell( 40 , 0, strtoupper($xml->getWord("obbligato",$_SESSION['lan'])).":" , false , "L" ,0 , 0,"",$pdf->GetY()+2 );
        $pdf->MultiCell( 120 , 0, $_POST['Nome']." ".$_POST['Cognome'] , false , "L" ,0 , 0 );
        $pdf->Ln();
        $pdf->MultiCell( 40 , 0, strtoupper($xml->getWord("trasgressore_pdf",$_SESSION['lan'])).":" , false , "L" ,0 , 0,"",$pdf->GetY()+2 );
        $pdf->MultiCell( 120 , 0, $_POST['Nome']." ".$_POST['Cognome'] , false , "L" ,0 , 0 );
        $pdf->Ln();

        /*$query = "SELECT MAX(Unione_ID) as Unione_ID FROM trasgressori";
        $result_id = $cls_db->getArrayLine($cls_db->ExecuteQuery($query));
        $UnionId = $result_id["Unione_ID"];

        if($UnionId == null) $UnionId = 1;
        else $UnionId++;*/

        $save = new stdClass();

        $save->Tipo_Polizia = $_POST['Polizia'];
        $save->Via_Verbale = $_POST['ViaVerbale'];
        $save->Comune_Verbale = $_POST['ComuneVerbale'];
        $save->Provincia_Verbale = $_POST['Provincia'];
        $save->Crono_Anno = $_POST['CronoAnnoVerbale'];
        $save->Crono_ID = $_POST['CronoNumeroVerbale'];
        $save->Targa_Veicolo = $_POST['TargaVeicolo'];
        $save->Modalita_Trasmissione = $_POST['ModTrasmissione'];
        $save->Tipo_Dichiarazione = $_POST['TipoDichiarazione'];
        $save->Data_Dichiarazione = $cls_date->GetDateDB($_POST['DataDichiarazione'],"IT");
        $save->Codice_Anticontraffazione = $CodiceAnticontraffazione;
        $save->Crono_T_Verbale = $_POST['CronotVervbale'];
        $save->Data_Violazione = $dataViolazione;

        $id_trasgressori = $cls_db->DbSave($cls_Utils->GetObjectQuery( $save,"trasgressori"));


        $save = new stdClass();

        $save->Trasgressori_ID = $id_trasgressori;
        $save->Autorita_Rilascio_Patente = $_POST['AutoritaRilascio'];
        $save->Categoria_Patente = $_POST['Categoria'];
        $save->CF = $_POST['CF'];
        $save->Cognome = $_POST['Cognome'];
        $save->Comune_Ditta_Id = $_POST['ComuneDitta'];
        $save->Comune_Nascita_Id = $_POST['ComuneNascita'];
        $save->Comune_Residenza_Id = $_POST['ComuneResidenza'];
        $save->Data_Rilascio_Patente = $cls_date->GetDateDB($_POST['DataRilascio'],"IT");
        $save->Data_Validita_Patente = $cls_date->GetDateDB($_POST['DataValidita'],"IT");
        $save->Data_Nascita = $cls_date->GetDateDB($_POST['DataNascita'],"IT");
        $save->EMail = $_POST['EMailUtente'];
        $save->EMail_Ditta = $_POST['EMailDitta'];
        $save->Esponente_Ditta = $_POST['EsponenteDitta'];
        $save->Esponente_Residenza = $_POST['EsponenteResidenza'];
        $save->Interno_Ditta = $_POST['InternoDitta'];
        $save->Interno_Residenza = $_POST['InternoResidenza'];
        $save->Nome = $_POST['Nome'];
        $save->Numero_Ditta = $_POST['NumeroDitta'];
        $save->Numero_Patente = $_POST['NumeroPatente'];
        $save->Numero_Residenza = $_POST['NumeroResidenza'];
        $save->Sede_Ditta = $_POST['SedeLR'];
        $save->Sesso = $_POST['Sesso'];
        $save->Stato_Nascita = $_POST['StatoNascita'];
        $save->Stato_Residenza = $_POST['StatoResidenza'];
        $save->Stato_Validazione_Dati = 'Attesa_Conferma';
        $save->Telefono = $_POST['TelefonoUtente'];
        $save->Telefono_Ditta = $_POST['TelefonoDitta'];
        $save->Tipo_Patente = $_POST['TipoPatente'];
        $save->Via_Ditta = $_POST['ViaDitta'];
        $save->Via_Residenza = $_POST['ViaResidenza'];
        $save->TrespasserTypeId = '1';
        $save->FineId = $_POST['FineIdUtente'];
        $save->TrespasserId = $_POST['TrespasserIdUtente'];
        $save->NomeDitta = $_POST['NomeDitta'];
        $save->PIDitta = $_POST['PIDitta'];
        $save->Comune_Nascita_Completo = $_POST['ComuneNascita1'];
        $save->Comune_Residenza_Completo = $_POST['ComuneResidenza1'];
        $save->Comune_Ditta_Completo = $_POST['ComuneDitta1'];
        $save->Provincia_Ditta = $_POST['ProvinciaDitta'];
        $save->Provincia_Residenza = $_POST['ComuneProvincia'];
        $save->Data_Primo_Rilascio_Patente = $cls_date->GetDateDB($_POST["DataPrimoRilascio"],"IT");
        $save->Stato_Rilascio_Patente = $_POST["StatoRilascio"];

        $cls_db->DbSave($cls_Utils->GetObjectQuery( $save,"trasgressori_dettaglio"));


        $allFile = $cls_Utils->getDirContents($_SERVER['DOCUMENT_ROOT']."inc/uploads/".$_POST["folder_name"]);

        $pathDocumentUpload = $cls_Utils->crea_dir($_SERVER['DOCUMENT_ROOT']."inc/uploads/documenti/".$CodiceAnticontraffazione);
        for($i=0; $i<count($allFile); $i++){
            $path_parts = pathinfo($allFile[$i]);
            copy($allFile[$i], $pathDocumentUpload."/".$path_parts['filename'].".".$path_parts['extension']);

            $saveDoc = new stdClass();
            $saveDoc->id_trasgressore = $id_trasgressori;
            $saveDoc->Nome_Documento = $path_parts['filename'].".".$path_parts['extension'];
            $saveDoc->Path = $pathDocumentUpload."/".$path_parts['filename'].".".$path_parts['extension'];

            $cls_db->DbSave($cls_Utils->GetObjectQuery( $saveDoc,"documenti_dichiarazione"));
        }
        break;
    }
    case 2:
    {
        $pdf->MultiCell( 40 , 0, strtoupper($xml->getWord("obbligato",$_SESSION['lan'])).":" , false , "L" ,0 , 0,"",$pdf->GetY()+2 );
        $pdf->MultiCell( 120 , 0, $_POST['Nome']." ".$_POST['Cognome'] , false , "L" ,0 , 0 );
        $pdf->Ln();
        $pdf->MultiCell( 40 , 0, strtoupper($xml->getWord("trasgressore_pdf",$_SESSION['lan'])).":" , false , "L" ,0 , 0,"",$pdf->GetY()+2 );
        $pdf->MultiCell( 120 , 0, $_POST['NomeTrasgressore']." ".$_POST['CognomeTrasgressore'] , false , "L" ,0 , 0 );
        $pdf->Ln();

        $TrespasserTypeIdUtente = $_POST["TrespasserTypeIdUtente"] != ""?$_POST["TrespasserTypeIdUtente"]:2;
        $TrespasserTypeIdTrasgressore = $_POST["TrespasserTypeIdTrasgressore"] != ""?$_POST["TrespasserTypeIdTrasgressore"]:3;

        if($TrespasserTypeIdUtente!=2 && $TrespasserTypeIdUtente!=10) $TrespasserTypeIdUtente = 2;
        if($TrespasserTypeIdTrasgressore!=3 && $TrespasserTypeIdTrasgressore!=11) $TrespasserTypeIdTrasgressore = 3;

        if($_POST["TipoTrasgressore"] == "NL"){
            $TrespasserTypeIdUtente = 10;
            $TrespasserTypeIdTrasgressore = 11;
        }

        /*$query = "SELECT MAX(Unione_ID) as Unione_ID FROM trasgressori";
        $result_id = $cls_db->getArrayLine($cls_db->ExecuteQuery($query));
        $UnionId = $result_id["Unione_ID"];

        if($UnionId == null) $UnionId = 1;
        else $UnionId++;*/


        $save = new stdClass();

        $save->Tipo_Polizia = $_POST['Polizia'];
        $save->Via_Verbale = $_POST['ViaVerbale'];
        $save->Comune_Verbale = $_POST['ComuneVerbale'];
        $save->Provincia_Verbale = $_POST['Provincia'];
        $save->Crono_Anno = $_POST['CronoAnnoVerbale'];
        $save->Crono_ID = $_POST['CronoNumeroVerbale'];
        $save->Targa_Veicolo = $_POST['TargaVeicolo'];
        $save->Modalita_Trasmissione = $_POST['ModTrasmissione'];
        $save->Tipo_Dichiarazione = $_POST['TipoDichiarazione'];
        $save->Data_Dichiarazione = $cls_date->GetDateDB($_POST['DataDichiarazione'],"IT");
        $save->Codice_Anticontraffazione = $CodiceAnticontraffazione;
        $save->Crono_T_Verbale = $_POST['CronotVervbale'];
        $save->Data_Violazione = $dataViolazione;

        $id_trasgressori = $cls_db->DbSave($cls_Utils->GetObjectQuery( $save,"trasgressori"));


        $save = new stdClass();

        $save->Trasgressori_ID = $id_trasgressori;
        $save->Autorita_Rilascio_Patente = $_POST['AutoritaRilascio'];
        $save->Categoria_Patente = $_POST['Categoria'];
        $save->CF = $_POST['CF'];
        $save->Cognome = $_POST['Cognome'];
        $save->Comune_Ditta_Id = $_POST['ComuneDitta'];
        $save->Comune_Nascita_Id = $_POST['ComuneNascita'];
        $save->Comune_Residenza_Id = $_POST['ComuneResidenza'];
        $save->Data_Rilascio_Patente = $cls_date->GetDateDB($_POST['DataRilascio'],"IT");
        $save->Data_Validita_Patente = $cls_date->GetDateDB($_POST['DataValidita'],"IT");
        $save->Data_Nascita = $cls_date->GetDateDB($_POST['DataNascita'],"IT");
        $save->EMail = $_POST['EMailUtente'];
        $save->EMail_Ditta = $_POST['EMailDitta'];
        $save->Esponente_Ditta = $_POST['EsponenteDitta'];
        $save->Esponente_Residenza = $_POST['EsponenteResidenza'];
        $save->Interno_Ditta = $_POST['InternoDitta'];
        $save->Interno_Residenza = $_POST['InternoResidenza'];
        $save->Nome = $_POST['Nome'];
        $save->Numero_Ditta = $_POST['NumeroDitta'];
        $save->Numero_Patente = $_POST['NumeroPatente'];
        $save->Numero_Residenza = $_POST['NumeroResidenza'];
        $save->Sede_Ditta = $_POST['SedeLR'];
        $save->Sesso = $_POST['Sesso'];
        $save->Stato_Nascita = $_POST['StatoNascita'];
        $save->Stato_Residenza = $_POST['StatoResidenza'];
        $save->Stato_Validazione_Dati = 'Attesa_Conferma';
        $save->Telefono = $_POST['TelefonoUtente'];
        $save->Telefono_Ditta = $_POST['TelefonoDitta'];
        $save->Tipo_Patente = $_POST['TipoPatente'];
        $save->Via_Ditta = $_POST['ViaDitta'];
        $save->Via_Residenza = $_POST['ViaResidenza'];
        $save->TrespasserTypeId = $TrespasserTypeIdUtente;
        $save->FineId = $_POST['FineIdUtente'];
        $save->TrespasserId = $_POST['TrespasserIdUtente'];
        $save->NomeDitta = $_POST['NomeDitta'];
        $save->PIDitta = $_POST['PIDitta'];
        $save->Comune_Nascita_Completo = $_POST['ComuneNascita1'];
        $save->Comune_Residenza_Completo = $_POST['ComuneResidenza1'];
        $save->Comune_Ditta_Completo = $_POST['ComuneDitta1'];
        $save->Provincia_Ditta = $_POST['ProvinciaDitta'];
        $save->Provincia_Residenza = $_POST['ComuneProvincia'];
        $save->Data_Primo_Rilascio_Patente = $cls_date->GetDateDB($_POST["DataPrimoRilascio"],"IT");
        $save->Stato_Rilascio_Patente = $_POST["StatoRilascio"];

        $cls_db->DbSave($cls_Utils->GetObjectQuery( $save,"trasgressori_dettaglio"));


        $save = new stdClass();

        $save->Trasgressori_ID = $id_trasgressori;
        $save->Autorita_Rilascio_Patente = $_POST['AutoritaRilascioTrasgressore'];
        $save->Categoria_Patente = $_POST['CategoriaTrasgressore'];
        $save->CF = $_POST['CFTrasgressore'];
        $save->Cognome = $_POST['CognomeTrasgressore'];
        $save->Comune_Ditta_Id = NULL;
        $save->Comune_Nascita_Id = $_POST['ComuneNascitaTrasgressore'];
        $save->Comune_Residenza_Id = $_POST['ComuneResidenzaTrasgressore'];
        $save->Data_Rilascio_Patente = $cls_date->GetDateDB($_POST['DataRilascioTrasgressore'],"IT");
        $save->Data_Validita_Patente = $cls_date->GetDateDB($_POST['DataValiditaTrasgressore'],"IT");
        $save->Data_Nascita = $cls_date->GetDateDB($_POST['DataNascitaTrasgressore'],"IT");
        $save->EMail = $_POST['EMailUtenteTrasgressore'];
        $save->EMail_Ditta = NULL;
        $save->Esponente_Ditta = NULL;
        $save->Esponente_Residenza = $_POST['EsponenteResidenzaTrasgressore'];
        $save->Interno_Ditta = NULL;
        $save->Interno_Residenza = $_POST['InternoResidenzaTrasgressore'];
        $save->Nome = $_POST['NomeTrasgressore'];
        $save->Numero_Ditta = NULL;
        $save->Numero_Patente = $_POST['NumeroPatenteTrasgressore'];
        $save->Numero_Residenza = $_POST['NumeroResidenzaTrasgressore'];
        $save->Sede_Ditta = NULL;
        $save->Sesso = $_POST['SessoTrasgressore'];
        $save->Stato_Nascita = $_POST['StatoNascitaTrasgressore'];
        $save->Stato_Residenza = $_POST['StatoResidenzaTrasgressore'];
        $save->Stato_Validazione_Dati = 'Attesa_Conferma';
        $save->Telefono = $_POST['TelefonoUtenteTrasgressore'];
        $save->Telefono_Ditta = NULL;
        $save->Tipo_Patente = $_POST['TipoPatenteTrasgressore'];
        $save->Via_Ditta = NULL;
        $save->Via_Residenza = $_POST['ViaResidenzaTrasgressore'];
        $save->TrespasserTypeId = $TrespasserTypeIdTrasgressore;
        $save->FineId = $_POST['FineIdTrasgressore'];
        $save->TrespasserId = $_POST['TrespasserIdTrasgressore'];
        $save->NomeDitta = NULL;
        $save->PIDitta = NULL;
        $save->Comune_Nascita_Completo = $_POST['ComuneNascitaTrasgressore1'];
        $save->Comune_Residenza_Completo = $_POST['ComuneResidenzaTrasgressore1'];
        $save->Comune_Ditta_Completo = NULL;
        $save->Provincia_Ditta = NULL;
        $save->Provincia_Residenza = $_POST['ProvinciaTrasgressore'];
        $save->Data_Primo_Rilascio_Patente = $cls_date->GetDateDB($_POST["DataPrimoRilascioTrasgressore"],"IT");
        $save->Stato_Rilascio_Patente = $_POST["StatoRilascioTrasgressore"];

        $cls_db->DbSave($cls_Utils->GetObjectQuery( $save,"trasgressori_dettaglio"));



        $allFile = $cls_Utils->getDirContents($_SERVER['DOCUMENT_ROOT']."inc/uploads/".$_POST["folder_name"]);

        $pathDocumentUpload = $cls_Utils->crea_dir($_SERVER['DOCUMENT_ROOT']."inc/uploads/documenti/".$CodiceAnticontraffazione);
        for($i=0; $i<count($allFile); $i++){
            $path_parts = pathinfo($allFile[$i]);
            copy($allFile[$i], $pathDocumentUpload."/".$path_parts['filename'].".".$path_parts['extension']);

            $saveDoc = new stdClass();
            $saveDoc->id_trasgressore = $id_trasgressori;
            $saveDoc->Nome_Documento = $path_parts['filename'].".".$path_parts['extension'];
            $saveDoc->Path = $pathDocumentUpload."/".$path_parts['filename'].".".$path_parts['extension'];

            $cls_db->DbSave($cls_Utils->GetObjectQuery( $saveDoc,"documenti_dichiarazione"));
        }

        break;
    }
    case 3:
    {
        //$dataViolazione = $_POST["AnnoVerbale"]."-".$_POST["MeseVerbale"]."-".$_POST["GiornoVerbale"]." ".$_POST["OreVerbale"].":".$_POST["MinutiVerbale"];
        $pdf->MultiCell( 40 , 0, strtoupper($xml->getWord("obbligato",$_SESSION['lan'])).":" , false , "L" ,0 , 0,"",$pdf->GetY()+2 );
        $pdf->MultiCell( 120 , 0, $_POST['Nome']." ".$_POST['Cognome'] , false , "L" ,0 , 0 );
        $pdf->Ln();
        $pdf->MultiCell( 40 , 0, strtoupper($xml->getWord("trasgressore_pdf",$_SESSION['lan'])).":" , false , "L" ,0 , 0,"",$pdf->GetY()+2 );
        $pdf->MultiCell( 120 , 0, $_POST['NomeTrasgressore']." ".$_POST['CognomeTrasgressore'] , false , "L" ,0 , 0 );
        $pdf->Ln();



        $TrespasserTypeIdUtente = $_POST["TrespasserTypeIdUtente"] != ""?$_POST["TrespasserTypeIdUtente"]:2;
        $TrespasserTypeIdTrasgressore = $_POST["TrespasserTypeIdTrasgressore"] != ""?$_POST["TrespasserTypeIdTrasgressore"]:3;

        if($TrespasserTypeIdUtente!=2 && $TrespasserTypeIdUtente!=10) $TrespasserTypeIdUtente = 2;
        if($TrespasserTypeIdTrasgressore!=3 && $TrespasserTypeIdTrasgressore!=11) $TrespasserTypeIdTrasgressore = 3;

        if($_POST["TipoTrasgressore"] == "NL"){
            $TrespasserTypeIdUtente = 10;
            $TrespasserTypeIdTrasgressore = 11;
        }

        /*$query = "SELECT MAX(Unione_ID) as Unione_ID FROM trasgressori";
        $result_id = $cls_db->getArrayLine($cls_db->ExecuteQuery($query));
        $UnionId = $result_id["Unione_ID"];

        if($UnionId == null) $UnionId = 1;
        else $UnionId++;*/

        $save = new stdClass();

        $save->Tipo_Polizia = $_POST['Polizia'];
        $save->Via_Verbale = $_POST['ViaVerbale'];
        $save->Comune_Verbale = $_POST['ComuneVerbale'];
        $save->Provincia_Verbale = $_POST['Provincia'];
        $save->Crono_Anno = $_POST['CronoAnnoVerbale'];
        $save->Crono_ID = $_POST['CronoNumeroVerbale'];
        $save->Targa_Veicolo = $_POST['TargaVeicolo'];
        $save->Modalita_Trasmissione = $_POST['ModTrasmissione'];
        $save->Tipo_Dichiarazione = $_POST['TipoDichiarazione'];
        $save->Data_Dichiarazione = $cls_date->GetDateDB($_POST['DataDichiarazione'],"IT");
        $save->Codice_Anticontraffazione = $CodiceAnticontraffazione;
        $save->Crono_T_Verbale = $_POST['CronotVervbale'];
        $save->Data_Violazione = $dataViolazione;

        $id_trasgressori = $cls_db->DbSave($cls_Utils->GetObjectQuery( $save,"trasgressori"));


        $save = new stdClass();

        $save->Trasgressori_ID = $id_trasgressori;
        $save->Autorita_Rilascio_Patente = $_POST['AutoritaRilascio'];
        $save->Categoria_Patente = $_POST['Categoria'];
        $save->CF = $_POST['CF'];
        $save->Cognome = $_POST['Cognome'];
        $save->Comune_Ditta_Id = $_POST['ComuneDitta'];
        $save->Comune_Nascita_Id = $_POST['ComuneNascita'];
        $save->Comune_Residenza_Id = $_POST['ComuneResidenza'];
        $save->Data_Rilascio_Patente = $cls_date->GetDateDB($_POST['DataRilascio'],"IT");
        $save->Data_Validita_Patente = $cls_date->GetDateDB($_POST['DataValidita'],"IT");
        $save->Data_Nascita = $cls_date->GetDateDB($_POST['DataNascita'],"IT");
        $save->EMail = $_POST['EMailUtente'];
        $save->EMail_Ditta = $_POST['EMailDitta'];
        $save->Esponente_Ditta = $_POST['EsponenteDitta'];
        $save->Esponente_Residenza = $_POST['EsponenteResidenza'];
        $save->Interno_Ditta = $_POST['InternoDitta'];
        $save->Interno_Residenza = $_POST['InternoResidenza'];
        $save->Nome = $_POST['Nome'];
        $save->Numero_Ditta = $_POST['NumeroDitta'];
        $save->Numero_Patente = $_POST['NumeroPatente'];
        $save->Numero_Residenza = $_POST['NumeroResidenza'];
        $save->Sede_Ditta = $_POST['SedeLR'];
        $save->Sesso = $_POST['Sesso'];
        $save->Stato_Nascita = $_POST['StatoNascita'];
        $save->Stato_Residenza = $_POST['StatoResidenza'];
        $save->Stato_Validazione_Dati = 'Attesa_Conferma';
        $save->Telefono = $_POST['TelefonoUtente'];
        $save->Telefono_Ditta = $_POST['TelefonoDitta'];
        $save->Tipo_Patente = $_POST['TipoPatente'];
        $save->Via_Ditta = $_POST['ViaDitta'];
        $save->Via_Residenza = $_POST['ViaResidenza'];
        $save->TrespasserTypeId = $TrespasserTypeIdUtente;
        $save->FineId = $_POST['FineIdUtente'];
        $save->TrespasserId = $_POST['TrespasserIdUtente'];
        $save->NomeDitta = $_POST['NomeDitta'];
        $save->PIDitta = $_POST['PIDitta'];
        $save->Comune_Nascita_Completo = $_POST['ComuneNascita1'];
        $save->Comune_Residenza_Completo = $_POST['ComuneResidenza1'];
        $save->Comune_Ditta_Completo = $_POST['ComuneDitta1'];
        $save->Provincia_Ditta = $_POST['ProvinciaDitta'];
        $save->Provincia_Residenza = $_POST['ComuneProvincia'];
        $save->Data_Primo_Rilascio_Patente = $cls_date->GetDateDB($_POST["DataPrimoRilascio"],"IT");
        $save->Stato_Rilascio_Patente = $_POST["StatoRilascio"];

        $cls_db->DbSave($cls_Utils->GetObjectQuery((array) $save,"trasgressori_dettaglio"));


        $save = new stdClass();

        $save->Trasgressori_ID = $id_trasgressori;
        $save->Autorita_Rilascio_Patente = $_POST['AutoritaRilascioTrasgressore'];
        $save->Categoria_Patente = $_POST['CategoriaTrasgressore'];
        $save->CF = $_POST['CFTrasgressore'];
        $save->Cognome = $_POST['CognomeTrasgressore'];
        $save->Comune_Ditta_Id = NULL;
        $save->Comune_Nascita_Id = $_POST['ComuneNascitaTrasgressore'];
        $save->Comune_Residenza_Id = $_POST['ComuneResidenzaTrasgressore'];
        $save->Data_Rilascio_Patente = $cls_date->GetDateDB($_POST['DataRilascioTrasgressore'],"IT");
        $save->Data_Validita_Patente = $cls_date->GetDateDB($_POST['DataValiditaTrasgressore'],"IT");
        $save->Data_Nascita = $cls_date->GetDateDB($_POST['DataNascitaTrasgressore'],"IT");
        $save->EMail = $_POST['EMailUtenteTrasgressore'];
        $save->EMail_Ditta = NULL;
        $save->Esponente_Ditta = NULL;
        $save->Esponente_Residenza = $_POST['EsponenteResidenzaTrasgressore'];
        $save->Interno_Ditta = NULL;
        $save->Interno_Residenza = $_POST['InternoResidenzaTrasgressore'];
        $save->Nome = $_POST['NomeTrasgressore'];
        $save->Numero_Ditta = NULL;
        $save->Numero_Patente = $_POST['NumeroPatenteTrasgressore'];
        $save->Numero_Residenza = $_POST['NumeroResidenzaTrasgressore'];
        $save->Sede_Ditta = NULL;
        $save->Sesso = $_POST['SessoTrasgressore'];
        $save->Stato_Nascita = $_POST['StatoNascitaTrasgressore'];
        $save->Stato_Residenza = $_POST['StatoResidenzaTrasgressore'];
        $save->Stato_Validazione_Dati = 'Attesa_Conferma';
        $save->Telefono = $_POST['TelefonoUtenteTrasgressore'];
        $save->Telefono_Ditta = NULL;
        $save->Tipo_Patente = $_POST['TipoPatenteTrasgressore'];
        $save->Via_Ditta = NULL;
        $save->Via_Residenza = $_POST['ViaResidenzaTrasgressore'];
        $save->TrespasserTypeId = $TrespasserTypeIdTrasgressore;
        $save->FineId = $_POST['FineIdTrasgressore'];
        $save->TrespasserId = $_POST['TrespasserIdTrasgressore'];
        $save->NomeDitta = NULL;
        $save->PIDitta = NULL;
        $save->Comune_Nascita_Completo = $_POST['ComuneNascitaTrasgressore1'];
        $save->Comune_Residenza_Completo = $_POST['ComuneResidenzaTrasgressore1'];
        $save->Comune_Ditta_Completo = NULL;
        $save->Provincia_Ditta = NULL;
        $save->Provincia_Residenza = $_POST['ProvinciaTrasgressore'];
        $save->Data_Primo_Rilascio_Patente = $cls_date->GetDateDB($_POST["DataPrimoRilascioTrasgressore"],"IT");
        $save->Stato_Rilascio_Patente = $_POST["StatoRilascioTrasgressore"];

        $cls_db->DbSave($cls_Utils->GetObjectQuery((array) $save,"trasgressori_dettaglio"));


        $allFile = $cls_Utils->getDirContents($_SERVER['DOCUMENT_ROOT']."inc/uploads/".$_POST["folder_name"]);

        $pathDocumentUpload = $cls_Utils->crea_dir($_SERVER['DOCUMENT_ROOT']."inc/uploads/documenti/".$CodiceAnticontraffazione);
        for($i=0; $i<count($allFile); $i++){
            $path_parts = pathinfo($allFile[$i]);
            copy($allFile[$i], $pathDocumentUpload."/".$path_parts['filename'].".".$path_parts['extension']);

            $saveDoc = new stdClass();
            $saveDoc->id_trasgressore = $id_trasgressori;
            $saveDoc->Nome_Documento = $path_parts['filename'].".".$path_parts['extension'];
            $saveDoc->Path = $pathDocumentUpload."/".$path_parts['filename'].".".$path_parts['extension'];

            $cls_db->DbSave($cls_Utils->GetObjectQuery( $saveDoc,"documenti_dichiarazione"));
            //unlink($allFile[$i]);
        }

        break;
    }
    case 4:
    {
        //$dataViolazione = $_POST["AnnoVerbale"]."-".$_POST["MeseVerbale"]."-".$_POST["GiornoVerbale"]." ".$_POST["OreVerbale"].":".$_POST["MinutiVerbale"];

        $pdf->MultiCell( 40 , 0, strtoupper($xml->getWord("obbligato",$_SESSION['lan'])).":" , false , "L" ,0 , 0,"",$pdf->GetY()+2 );
        $pdf->MultiCell( 120 , 0, "" , false , "L" ,0 , 0 );
        $pdf->Ln();
        $pdf->MultiCell( 40 , 0, strtoupper($xml->getWord("trasgressore_pdf",$_SESSION['lan'])).":" , false , "L" ,0 , 0,"",$pdf->GetY()+2 );
        $pdf->MultiCell( 120 , 0, $_POST['NomeTrasgressore']." ".$_POST['CognomeTrasgressore'] , false , "L" ,0 , 0 );
        $pdf->Ln();

        /*$query = "SELECT MAX(Unione_ID) as Unione_ID FROM trasgressori";
        $result_id = $cls_db->getArrayLine($cls_db->ExecuteQuery($query));
        $UnionId = $result_id["Unione_ID"];

        if($UnionId == null) $UnionId = 1;
        else $UnionId++;*/

        $save = new stdClass();

        $save->Tipo_Polizia = $_POST['Polizia'];
        $save->Via_Verbale = $_POST['ViaVerbale'];
        $save->Comune_Verbale = $_POST['ComuneVerbale'];
        $save->Provincia_Verbale = $_POST['Provincia'];
        $save->Crono_Anno = $_POST['CronoAnnoVerbale'];
        $save->Crono_ID = $_POST['CronoNumeroVerbale'];
        $save->Targa_Veicolo = $_POST['TargaVeicolo'];
        $save->Modalita_Trasmissione = $_POST['ModTrasmissione'];
        $save->Tipo_Dichiarazione = $_POST['TipoDichiarazione'];
        $save->Data_Dichiarazione = $cls_date->GetDateDB($_POST['DataDichiarazione'],"IT");
        $save->Codice_Anticontraffazione = $CodiceAnticontraffazione;
        $save->Crono_T_Verbale = $_POST['CronotVervbale'];
        $save->Data_Violazione = $dataViolazione;

        $id_trasgressori = $cls_db->DbSave($cls_Utils->GetObjectQuery( $save,"trasgressori"));

        $save = new stdClass();

        $save->Trasgressori_ID = $id_trasgressori;
        $save->Autorita_Rilascio_Patente = $_POST['AutoritaRilascioTrasgressore'];
        $save->Categoria_Patente = $_POST['CategoriaTrasgressore'];
        $save->CF = $_POST['CFTrasgressore'];
        $save->Cognome = $_POST['CognomeTrasgressore'];
        $save->Comune_Ditta_Id = NULL;
        $save->Comune_Nascita_Id = $_POST['ComuneNascitaTrasgressore'];
        $save->Comune_Residenza_Id = $_POST['ComuneResidenzaTrasgressore'];
        $save->Data_Rilascio_Patente = $cls_date->GetDateDB($_POST['DataRilascioTrasgressore'],"IT");
        $save->Data_Validita_Patente = $cls_date->GetDateDB($_POST['DataValiditaTrasgressore'],"IT");
        $save->Data_Nascita = $cls_date->GetDateDB($_POST['DataNascitaTrasgressore'],"IT");
        $save->EMail = $_POST['EMailUtenteTrasgressore'];
        $save->EMail_Ditta = NULL;
        $save->Esponente_Ditta = NULL;
        $save->Esponente_Residenza = $_POST['EsponenteResidenzaTrasgressore'];
        $save->Interno_Ditta = NULL;
        $save->Interno_Residenza = $_POST['InternoResidenzaTrasgressore'];
        $save->Nome = $_POST['NomeTrasgressore'];
        $save->Numero_Ditta = NULL;
        $save->Numero_Patente = $_POST['NumeroPatenteTrasgressore'];
        $save->Numero_Residenza = $_POST['NumeroResidenzaTrasgressore'];
        $save->Sede_Ditta = NULL;
        $save->Sesso = $_POST['SessoTrasgressore'];
        $save->Stato_Nascita = $_POST['StatoNascitaTrasgressore'];
        $save->Stato_Residenza = $_POST['StatoResidenzaTrasgressore'];
        $save->Stato_Validazione_Dati = 'Attesa_Conferma';
        $save->Telefono = $_POST['TelefonoUtenteTrasgressore'];
        $save->Telefono_Ditta = NULL;
        $save->Tipo_Patente = $_POST['TipoPatenteTrasgressore'];
        $save->Via_Ditta = NULL;
        $save->Via_Residenza = $_POST['ViaResidenzaTrasgressore'];
        $save->TrespasserTypeId = '3';
        $save->FineId = $_POST['FineIdTrasgressore'];
        $save->TrespasserId = $_POST['TrespasserIdTrasgressore'];
        $save->NomeDitta = NULL;
        $save->PIDitta = NULL;
        $save->Comune_Nascita_Completo = $_POST['ComuneNascitaTrasgressore1'];
        $save->Comune_Residenza_Completo = $_POST['ComuneResidenzaTrasgressore1'];
        $save->Comune_Ditta_Completo = NULL;
        $save->Provincia_Ditta = NULL;
        $save->Provincia_Residenza = $_POST['ProvinciaTrasgressore'];
        $save->Data_Primo_Rilascio_Patente = $cls_date->GetDateDB($_POST["DataPrimoRilascioTrasgressore"],"IT");
        $save->Stato_Rilascio_Patente = $_POST["StatoRilascioTrasgressore"];

        $cls_db->DbSave($cls_Utils->GetObjectQuery((array) $save,"trasgressori_dettaglio"));


        $allFile = $cls_Utils->getDirContents($_SERVER['DOCUMENT_ROOT']."inc/uploads/".$_POST["folder_name"]);

        $pathDocumentUpload = $cls_Utils->crea_dir($_SERVER['DOCUMENT_ROOT']."inc/uploads/documenti/".$CodiceAnticontraffazione);
        for($i=0; $i<count($allFile); $i++){
            $path_parts = pathinfo($allFile[$i]);
            copy($allFile[$i], $pathDocumentUpload."/".$path_parts['filename'].".".$path_parts['extension']);

            $saveDoc = new stdClass();
            $saveDoc->id_trasgressore = $id_trasgressori;
            $saveDoc->Nome_Documento = $path_parts['filename'].".".$path_parts['extension'];
            $saveDoc->Path = $pathDocumentUpload."/".$path_parts['filename'].".".$path_parts['extension'];

            $cls_db->DbSave($cls_Utils->GetObjectQuery( $saveDoc,"documenti_dichiarazione"));
        }

        break;
    }
}

$pageMargins  = $pdf->getMargins();     // Get all margins as array
$headerMargin = $pageMargins['left'];

$pdf->Image($qrcodeName,$headerMargin + 10,$pdf->GetY() + 20,25);

$path = $cls_Utils->crea_dir($_SERVER['DOCUMENT_ROOT']."inc/uploads/documenti/".$_POST["folder_name"]);

$pdf->Output($path."/resoconto_dichiarazione.pdf","F");

try {
    $mail = new PHPMailer(true);
    //Server settings
    $mail->SMTPDebug = SMTP::DEBUG_OFF;                      //Enable verbose debug output
    $mail->isSMTP();                                            //Send using SMTP
    $mail->Host       = 'smtps.aruba.it';                     //Set the SMTP server to send through
    $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
    $mail->Username   = 'protocollo@poliziamunicipale-online.it';                     //SMTP username
    $mail->Password   = 'S@rid@consulting2021';                               //SMTP password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            //Enable implicit TLS encryption   ENCRYPTION_SMTPS - ENCRYPTION_STARTTLS
    $mail->Port       = 465;

    $mail->SMTPOptions = array(
        'ssl' => array(
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
        )
    );

    $returnMessage_1 = array(
        'ita'=>'Mail di conferma inviata a',
        'eng'=>'Confirmation mail sent to',
        'ger'=>'Bestätigungsmail gesendet an',
        'fre'=>'Courrier de confirmation envoyé à',
        'spa'=>'Correo de confirmación enviado a',
    );

    $returnForm = "<p>".$returnMessage_1[$_SESSION['lan']].":</br>";
    //Recipients
    $mail->setFrom('protocollo@poliziamunicipale-online.it', 'Polizia municipale');
    if(isset($_POST['EMailUtente'])) {
        if($_POST['EMailUtente']!="") {
            $mail->addAddress($_POST['EMailUtente']);//Add a recipient
            $returnForm .= "- " . $_POST['EMailUtente'] . "</br>";
        }
    }
    if(isset($_POST['EMailUtenteTrasgressore'])) {
        if($_POST['EMailUtenteTrasgressore']!="") {
            $mail->addAddress($_POST['EMailUtenteTrasgressore']);
            $returnForm .= "- " . $_POST['EMailUtenteTrasgressore'] . "</br>";
        }
    }

    $returnMessage_2 = array(
        'ita'=>'Controllare la casella di posta elettronica',
        'eng'=>'Check your inbox',
        'ger'=>'Überprüfe deinen Posteingang',
        'fre'=>'Vérifiez votre boîte de réception',
        'spa'=>'Revisa tu correo',
    );

    $returnForm .= "<b>".$returnMessage_2[$_SESSION['lan']]."</b></p></br>";
    //$mail->addReplyTo('info@example.com', 'Information');
    //$mail->addCC('cc@example.com');
    //$mail->addBCC('bcc@example.com');

    //Attachments
    $mail->addAttachment($path."/resoconto_dichiarazione.pdf");         //Add attachments
    //$mail->addAttachment('/tmp/image.jpg', 'new.jpg');    //Optional name

    //Content
    $mail->isHTML(true);                                  //Set email format to HTML
    $mail->Subject = 'Dichiarazione trasgressore';
    $mail->Body    = 'In allegato troverete il pdf con i dettagli della dichiarazione e codice anticontraffazione.<br>Distinti saluti';
    //$mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

    $result = $mail->send();
    //echo 'Message has been sent '.$result;
} catch (Exception $e) {
    echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
}

$cls_Utils->deleteDir($_SERVER['DOCUMENT_ROOT']."inc/uploads/documenti/".$_POST["folder_name"]);
$cls_Utils->deleteDir($_SERVER['DOCUMENT_ROOT']."inc/uploads/".$_POST["folder_name"]);

$aSuccess = array(
    'ita'=>'Dati inseriti correttamente.',
    'eng'=>'Data entered correctly.',
    'ger'=>'Daten richtig eingegeben.',
    'fre'=>'Données saisies correctement.',
    'spa'=>'Datos ingresados correctamente.',
);

$button = array(
    'ita'=>'Nuova operazione',
    'eng'=>'New operation',
    'ger'=>'Neuer Betrieb',
    'fre'=>'Nouvelle opération',
    'spa'=>'Nueva operación',
);

echo json_encode(
    array(
        "page" => "save",
        "id_div" => "content_offenders",
        "content" => '<div class="alert alert-success">'.$returnForm.$aSuccess[$_SESSION['lan']].'</br></br><button class="btn btn-primary" type="button" onclick="refresch();">'.$button[$_SESSION['lan']].'</button></div>'
    )
);
die;
//$conn->close();

//echo "<script>history.go(-1);</script>";
