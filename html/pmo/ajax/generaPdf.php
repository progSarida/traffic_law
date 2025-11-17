<?php

if (!session_id()) session_start();


include_once "../Librerie/cls_DateTimeInLine.php";
include_once "../Librerie/cls_db.php";
include_once "../Librerie/cls_Utils.php";
require "../Librerie/tcpdf/tcpdf.php";
include_once "../Librerie/XmlLanguageReader.php";


function GetTCityTitle($CC,$URL){

    $arrContextOptions=array(
        "ssl"=>array(
            "verify_peer"=>false,
            "verify_peer_name"=>false,
        ),
    );

    $response = file_get_contents($URL.'rest_richiestaDati.php?op=cityS&cv='.$CC,false, stream_context_create($arrContextOptions));
    $response = json_decode($response);

    if($response->Esito == "OK")
    {
        if(isset($response->Dati[0])) return $response->Dati[0]->Title;
        else return null;
    }
    else return null;
}

class MYPDF extends TCPDF
{
    public $tipo;
    public $riga_1;
    public $riga_2;
    public $riga_3;
    public $riga_4;
    public $riga_5;

    public function AddMyPage($tip, $formato = null)
    {
        $this->tipo = $tip;
        $this->AddPage($formato);
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
        //$pdf->MultiCell(0, 0, $datiTrasgressore, false, "L", 0, 0, "", $pdf->GetY() + 2);

    }

    public function Footer()
    {

        $this->SetY(-10);
        $this->SetFont('helvetica', 'N', 7);
        $this->Cell(0, 5, "Pag. " . $this->getPage() . " - " . date("d/m/Y H\hi:s"), 0, false, 'C', 0, '', 0, false, 'T', 'M');

    }

}

$xml = new xmlLanguageReader("../language.xml");

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

$cls_date = new cls_DateTimeI("DB", false);
$cls_Utils = new cls_Utils($dbConnect);
//$cls_db_traffic_law = new cls_db("localhost", "root", "", "traffic_law"); // da sostituire
//$cls_db = new cls_db("localhost", "root", "", "polizia_municipale");
$cls_db = new cls_db("62.149.150.179","Sql627048","41c608f5","Sql627048_1");
//$cls_db_sarida = new cls_db("localhost", "root", "", "sarida"); //da sostituire
/*$cls_db = new cls_db("62.149.150.179","Sql627048","41c608f5","Sql627048_1");
$cls_db_sarida = new cls_db("62.94.231.188","root","GP~0o>hU@:/:q*","sarida");
$cls_db_traffic_law = new cls_db("62.94.231.188","root","GP~0o>hU@:/:q*", "traffic_law");*/


//echo "<br>".$ID_Utente_Trasgressore." --- ".mysqli_num_rows ( $result );
/*$dataViolazione = $_POST["AnnoVerbale"] . "-" . $_POST["MeseVerbale"] . "-" . $_POST["GiornoVerbale"] . " " . $_POST["OreVerbale"] . ":" . $_POST["MinutiVerbale"];

$query = "SELECT ID FROM `trasgressori` where Comune_Verbale = '" . $_POST['ComuneVerbale'] . "' AND Targa_Veicolo = '" . $_POST['TargaVeicolo'] . "' AND Crono_ID = " . $_POST['CronoNumeroVerbale'] . " AND Crono_Anno = " . $_POST['CronoAnnoVerbale'] . " AND Crono_T_Verbale = '" . $_POST['CronotVervbale'] . "' AND Data_Violazione = '" . $dataViolazione . "' ";
$result = $cls_db->getResults($cls_db->ExecuteQuery($query));

if (count($result) > 0) {
    $where = array("ID" => $result[0]["ID"]);
}*/

/*$query = "SELECT ManagerSector, ManagerCity, ManagerProvince, ManagerZIP, ManagerAddress, ManagerPEC FROM customer WHERE CityId = '".$_POST['ComuneVerbale']."'";
$resultManager = $cls_db_traffic_law->getArrayLine($cls_db_traffic_law->ExecuteQuery($query));*/

$resultManager = file_get_contents($URL_DATA.'rest_richiestaDati.php?op=customer&cv='.$_POST['ComuneVerbale'],false, stream_context_create($arrContextOptions));
$resultManager = json_decode($resultManager);

//var_dump($resultManager);

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

//echo $managerSector." -- ".$managerProvince." -- ".$managerCity." -- ".$managerZIP." -- ".$managerAddress." -- ".$managerPEC;

$CodiceAnticontraffazione = "";
do {
    for ($i = 0; $i < 2; $i++)
        $CodiceAnticontraffazione .= mt_rand(1000000000, 9999999999);
    $query = "SELECT Codice_Anticontraffazione from trasgressori WHERE Codice_Anticontraffazione = '" . $CodiceAnticontraffazione . "'";
    $result = $cls_db->getResults($cls_db->ExecuteQuery($query));
} while (count($result) != 0);


$fontPrimo = 8.5;
$fontSecondo = 6.5;

$pdf = new MYPDF("L", "mm", "A4", true, 'UTF-8', false);

$pdf->riga_1 = $xml->getWord("comuneDi",$_SESSION['lan'])." ".$managerCity;
$pdf->riga_2 = $xml->getWord("serviziDi",$_SESSION['lan'])." ".$managerSector;
$pdf->riga_3 = $managerAddress;
$pdf->riga_4 = $managerZIP. " " .$managerCity. " (" .$managerProvince . ")";
$pdf->riga_5 = "800.582.480";

$pdf->AddPage('P', 'A4');
$pdf->SetFont('Helvetica', 'B', $fontPrimo);

$oggettoPdf = $xml->getWord("oggetto_1",$_SESSION['lan']);
$oggettoPdf2 = $xml->getWord("oggetto_2",$_SESSION['lan']);

$pageMargins  = $pdf->getMargins();     // Get all margins as array
$headerMargin = $pageMargins['top'];

$pdf->MultiCell(0, 0, $xml->getWord("raccomandata",$_SESSION['lan'])." ".$managerPEC, false, "C", 0, 0, "", $pdf->GetY() + $headerMargin + 25);
$pdf->Ln();
//$pdf->MultiCell( 0 , 2, "" , false , "L" ,0 , 0 );
//$pdf->Ln();

$pdf->SetFont('Helvetica', 'I', $fontSecondo);
$pdf->MultiCell(40, 0, $xml->getWord("oggetto",$_SESSION['lan']).":", false, "L", 0, 0, "", $pdf->GetY() + 8);
$pdf->MultiCell(120, 0, $oggettoPdf, false, "L", 0, 0);
$pdf->Ln();
$pdf->SetFont('Helvetica', 'B', $fontSecondo);
$pdf->MultiCell(40, 0, "", false, "L", 0, 0);
$pdf->MultiCell(120, 0, $oggettoPdf2, false, "L", 0, 0);
$pdf->Ln();
$pdf->SetFont('Helvetica', '', $fontSecondo);

switch ($_POST["TipoDichiarazione"]) {
    case 1:
    {
        if ($_POST['ComuneNascita1']!="") $comNascita["Title"] = $_POST['ComuneNascita1'];
        else {
            /*$query = "SELECT Title FROM city WHERE Id = '" . $_POST['ComuneNascita'] . "'";
            $comNascita = $cls_db_sarida->getArrayLine($cls_db_sarida->ExecuteQuery($query));*/
            $comNascita["Title"] = GetTCityTitle($_POST['ComuneNascita'],$URL_DATA);
        }

        if ($_POST['ComuneResidenza1']!="") $comResidenza["Title"] = $_POST['ComuneResidenza1'];
        else {
            /*$query = "SELECT Title FROM city WHERE Id = '" . $_POST['ComuneResidenza'] . "'";
            $comResidenza = $cls_db_sarida->getArrayLine($cls_db_sarida->ExecuteQuery($query));*/

            $comResidenza["Title"] = GetTCityTitle($_POST['ComuneResidenza'],$URL_DATA);
        }

        $datiTrasgressore = $xml->getWord("pdf_utente_1",$_SESSION['lan']) . $_POST['CronoNumeroVerbale'] . "/" . $_POST['CronoAnnoVerbale'] . "/" . $_POST['CronotVervbale'];
        $datiTrasgressore .= " ".$xml->getWord("pdf_utente_2",$_SESSION['lan'])." " . $_POST["GiornoVerbale"] . "/" . $_POST["MeseVerbale"] . "/" . $_POST["AnnoVerbale"] . " ".$xml->getWord("pdf_utente_3",$_SESSION['lan'])." " . $_POST['TargaVeicolo']." ";
        $datiTrasgressore .= $xml->getWord("pdf_utente_4",$_SESSION['lan'])." ". strtoupper($_POST['Nome']) . " " . strtoupper($_POST['Cognome']) ." ". $xml->getWord("pdf_utente_5",$_SESSION['lan'])." " . strtoupper($_POST['StatoNascitaCompleto']) ." ". $xml->getWord("pdf_utente_23",$_SESSION['lan']). " " . strtoupper($comNascita["Title"]) . " " . $xml->getWord("pdf_utente_6",$_SESSION['lan'])." " . $_POST['DataNascita'];
        $datiTrasgressore .= " ".$xml->getWord("pdf_utente_7",$_SESSION['lan'])." " . strtoupper($_POST['StatoResidenzaCompleto']) ." ". $xml->getWord("pdf_utente_23",$_SESSION['lan']). " " . strtoupper($comResidenza["Title"]) . " (" . strtoupper($_POST['ComuneProvincia']) . ") " . strtoupper($_POST['ViaResidenza']) . " " . $_POST['NumeroResidenza'] . "/" . $_POST['InternoResidenza'];
        $datiTrasgressore .= $xml->getWord("pdf_utente_8",$_SESSION['lan']). " " . $_POST['Categoria'] . " " .$xml->getWord("pdf_utente_9",$_SESSION['lan']). " " . $_POST['NumeroPatente'] . " " .$xml->getWord("pdf_utente_10",$_SESSION['lan']). " " . $_POST['DataRilascio'] . " " . $xml->getWord("pdf_utente_22",$_SESSION['lan']) . " " . $_POST["DataPrimoRilascio"] . " " .$xml->getWord("pdf_utente_11",$_SESSION['lan'])." ";
        $datiTrasgressore .= $_POST['AutoritaRilascio'] . " ".$xml->getWord("pdf_utente_12",$_SESSION['lan']). " " . $_POST['DataValidita'] . " ".$xml->getWord("pdf_utente_13",$_SESSION['lan']). " " . $_POST['TelefonoUtente'] . $xml->getWord("pdf_utente_14",$_SESSION['lan']). " ";

        if ($_POST["TipoTrasgressore"] == "PV") {
            $datiTrasgressore .= $xml->getWord("pdf_utente_15",$_SESSION['lan']);
        } else {
            if ($_POST['ComuneDitta1']!="") $comSedeDitta["Title"] = $_POST['ComuneDitta1'];
            else {
                /*$query = "SELECT Title FROM city WHERE Id = '" . $_POST['ComuneDitta'] . "'";
                $comSedeDitta = $cls_db_sarida->getArrayLine($cls_db_sarida->ExecuteQuery($query));*/

                $comSedeDitta["Title"] = GetTCityTitle($_POST['ComuneDitta'],$URL_DATA);
            }
            /*$query = "SELECT Title FROM country WHERE Id = '" . $_POST['SedeLR'] . "'";
            $statoSedeDitta = $cls_db_sarida->getArrayLine($cls_db_sarida->ExecuteQuery($query));*/

            $statoSedeDitta["Title"] = GetTCityTitle($_POST['SedeLR'],$URL_DATA);

            $datiTrasgressore .= $xml->getWord("pdf_utente_16",$_SESSION['lan']). " " . $_POST['NomeDitta'] . " " . $xml->getWord("pdf_utente_17",$_SESSION['lan']). " " . strtoupper($statoSedeDitta["Title"]) . " " . $xml->getWord("pdf_utente_18",$_SESSION['lan']). " " . strtoupper($comSedeDitta["Title"]);
            $datiTrasgressore .= " (" . strtoupper($_POST['ProvinciaDitta']) . ") " . $_POST['ViaDitta'] . " " . $_POST['NumeroDitta'] . "/" . $_POST['InternoDitta'] . " " . $xml->getWord("pdf_utente_19",$_SESSION['lan']). " " . $_POST['TelefonoDitta'] . ".";
        }

        $pdf->MultiCell(0, 0, $datiTrasgressore, false, "L", 0, 0, "", $pdf->GetY() + 2);
        $pdf->Ln();
        $pdf->SetFont('Helvetica', 'B', $fontSecondo);

        $datiTrasgressore_1 = $xml->getWord("pdf_utente_20",$_SESSION['lan']);
        $pdf->MultiCell(0, 0, $datiTrasgressore_1, false, "L", 0, 0, "", $pdf->GetY() + 2);
        $pdf->Ln();

        $pdf->SetFont('Helvetica', '', $fontSecondo);
        $pdf->MultiCell(0, 0, $xml->getWord("pdf_utente_21",$_SESSION['lan']), false, "L", 0, 0, "", $pdf->GetY() + 2);
        $pdf->Ln();

        $pdf->MultiCell(0, 0, $xml->getWord("data",$_SESSION['lan'])." ____/____/______", false, "L", 0, 0, "", $pdf->GetY() + 2);
        $pdf->Ln();

        /*$pageWidth    = $pdf->getPageWidth();   // Get total page width, without margins
        $pageMargins  = $pdf->getMargins();     // Get all margins as array
        $headerMargin = $pageMargins['header']; // Get the header margin
        $px2          = $pageWidth - $headerMargin; // Compute x value for second point of line

        $p1x   = $px2 - 20;//$this->getX();
        $p1y   = $pdf->getY();
        $p2x   = $px2;
        $p2y   = $p1y;  // Use same y for a straight line*/
        $style = array();

        $pdf->MultiCell(50, 0, $xml->getWord("firma_trasgressore",$_SESSION['lan']), false, "C", 0, 0, "", $pdf->GetY() + 11);
        $pdf->Ln();
        $pdf->Line($pdf->getX()+3, $pdf->getY()+10, $pdf->getX()+50, $pdf->getY()+10, $style);
        $pdf->Ln();


        break;
    }
    case 2:
    {

        if ($_POST['ComuneNascita1']!="") $comNascita["Title"] = $_POST['ComuneNascita1'];
        else {
            /*$query = "SELECT Title FROM city WHERE Id = '" . $_POST['ComuneNascita'] . "'";
            $comNascita = $cls_db_sarida->getArrayLine($cls_db_sarida->ExecuteQuery($query));*/

            $comNascita["Title"] = GetTCityTitle($_POST['ComuneNascita'],$URL_DATA);
        }

        if ($_POST['ComuneResidenza1']!="") $comResidenza["Title"] = $_POST['ComuneResidenza1'];
        else {
            /*$query = "SELECT Title FROM city WHERE Id = '" . $_POST['ComuneResidenza'] . "'";
            $comResidenza = $cls_db_sarida->getArrayLine($cls_db_sarida->ExecuteQuery($query));*/

            $comResidenza["Title"] = GetTCityTitle($_POST['ComuneResidenza'],$URL_DATA);
        }

        if ($_POST['ComuneNascitaTrasgressore1']!="") $comNascitaTrasgressore["Title"] = $_POST['ComuneNascitaTrasgressore1'];
        else {
            /*$query = "SELECT Title FROM city WHERE Id = '" . $_POST['ComuneNascitaTrasgressore'] . "'";
            $comNascitaTrasgressore = $cls_db_sarida->getArrayLine($cls_db_sarida->ExecuteQuery($query));*/

            $comNascitaTrasgressore["Title"] = GetTCityTitle($_POST['ComuneNascitaTrasgressore'],$URL_DATA);
        }

        if ($_POST['ComuneResidenzaTrasgressore1']!="") $comResidenzaTrasgressore["Title"] = $_POST['ComuneResidenzaTrasgressore1'];
        else {
            /*$query = "SELECT Title FROM city WHERE Id = '" . $_POST['ComuneResidenzaTrasgressore'] . "'";
            $comResidenzaTrasgressore = $cls_db_sarida->getArrayLine($cls_db_sarida->ExecuteQuery($query));*/

            $comResidenzaTrasgressore["Title"] = GetTCityTitle($_POST['ComuneResidenzaTrasgressore'],$URL_DATA);
        }


        $datiUtente = $xml->getWord("pdf_utente_1",$_SESSION['lan']) . $_POST['CronoNumeroVerbale'] . "/" . $_POST['CronoAnnoVerbale'] . "/" . $_POST['CronotVervbale'];
        $datiUtente .= " ".$xml->getWord("pdf_utente_2",$_SESSION['lan'])." " . $_POST["GiornoVerbale"] . "/" . $_POST["MeseVerbale"] . "/" . $_POST["AnnoVerbale"] . " ".$xml->getWord("pdf_utente_3",$_SESSION['lan'])." " . $_POST['TargaVeicolo']." ";
        $datiUtente .= $xml->getWord("pdf_utente_4",$_SESSION['lan'])." ". strtoupper($_POST['Nome']) . " " . strtoupper($_POST['Cognome']) . $xml->getWord("pdf_utente_5",$_SESSION['lan'])." " . strtoupper($_POST['StatoNascitaCompleto']) ." ". $xml->getWord("pdf_utente_23",$_SESSION['lan'])." " . strtoupper($comNascita["Title"]) . " " . $xml->getWord("pdf_utente_6",$_SESSION['lan'])." " . $_POST['DataNascita'];
        $datiUtente .= " ".$xml->getWord("pdf_utente_7",$_SESSION['lan'])." " . strtoupper($_POST['StatoResidenzaCompleto']) ." ". $xml->getWord("pdf_utente_23",$_SESSION['lan']). " " . strtoupper($comResidenza["Title"]) . " (" . strtoupper($_POST['ComuneProvincia']) . ") " . strtoupper($_POST['ViaResidenza']) . " " . $_POST['NumeroResidenza'] . "/" . $_POST['InternoResidenza'];
        $datiUtente .= $xml->getWord("pdf_utente_8",$_SESSION['lan']). " " . $_POST['Categoria'] . " " .$xml->getWord("pdf_utente_9",$_SESSION['lan']). " " . $_POST['NumeroPatente'] . " " .$xml->getWord("pdf_utente_10",$_SESSION['lan']). " " . $_POST['DataRilascio'] . " " . $xml->getWord("pdf_utente_22",$_SESSION['lan']) . " " . $_POST["DataPrimoRilascio"] . " " .$xml->getWord("pdf_utente_11",$_SESSION['lan'])." ";
        $datiUtente .= $_POST['AutoritaRilascio'] . " ".$xml->getWord("pdf_utente_12",$_SESSION['lan']). " " . $_POST['DataValidita'] . " ".$xml->getWord("pdf_utente_13",$_SESSION['lan']). " " . $_POST['TelefonoUtente'] . $xml->getWord("pdf_utente_14",$_SESSION['lan']). " ";

        if ($_POST["TipoTrasgressore"] == "PV") {
            $datiUtente .= $xml->getWord("pdf_utente_15",$_SESSION['lan']);
        } else {
            if ($_POST['ComuneDitta1']!="") $comSedeDitta["Title"] = $_POST['ComuneDitta1'];
            else {
                /*$query = "SELECT Title FROM city WHERE Id = '" . $_POST['ComuneDitta'] . "'";
                $comSedeDitta = $cls_db_sarida->getArrayLine($cls_db_sarida->ExecuteQuery($query));*/

                $comSedeDitta["Title"] = GetTCityTitle($_POST['ComuneDitta'],$URL_DATA);
            }
            /*$query = "SELECT Title FROM country WHERE Id = '" . $_POST['SedeLR'] . "'";
            $statoSedeDitta = $cls_db_sarida->getArrayLine($cls_db_sarida->ExecuteQuery($query));*/

            $statoSedeDitta["Title"] = GetTCityTitle($_POST['SedeLR'],$URL_DATA);

            $datiUtente .= $xml->getWord("pdf_utente_16",$_SESSION['lan']). " " . $_POST['NomeDitta'] . " " . $xml->getWord("pdf_utente_17",$_SESSION['lan']). " " . strtoupper($statoSedeDitta["Title"]) . " " . $xml->getWord("pdf_utente_18",$_SESSION['lan']). " " . strtoupper($comSedeDitta["Title"]);
            $datiUtente .= " (" . strtoupper($_POST['ProvinciaDitta']) . ") " . $_POST['ViaDitta'] . " " . $_POST['NumeroDitta'] . "/" . $_POST['InternoDitta'] . " " . $xml->getWord("pdf_utente_19",$_SESSION['lan']). " " . $_POST['TelefonoDitta'] . ".";
        }

        $pdf->MultiCell(0, 0, $datiUtente, false, "L", 0, 0, "", $pdf->GetY() + 2);
        $pdf->Ln();
        $pdf->SetFont('Helvetica', 'B', $fontSecondo);

        $datiUtente_1 = $xml->getWord("pdf_utente_20",$_SESSION['lan']);
        $pdf->MultiCell(0, 0, $datiUtente_1, false, "L", 0, 0, "", $pdf->GetY() + 2);
        $pdf->Ln();

        $datiTrasgressore = $xml->getWord("pdf_trasgressore_1",$_SESSION['lan'])." ".strtoupper($_POST['NomeTrasgressore'])." ".strtoupper($_POST['CognomeTrasgressore'])." ".$xml->getWord("pdf_utente_5",$_SESSION['lan'])." " . strtoupper($_POST['StatoNascitaTrasgressoreCompleto']) ." ". $xml->getWord("pdf_utente_23",$_SESSION['lan'])." ".strtoupper($comNascitaTrasgressore["Title"])." ".$xml->getWord("pdf_trasgressore_3",$_SESSION['lan'])." ".$_POST['DataNascitaTrasgressore'];
        $datiTrasgressore .= " ".$xml->getWord("ResidenteIn",$_SESSION['lan'])." " . strtoupper($_POST['StatoResidenzaTrasgressoreCompleto']) ." ". $xml->getWord("pdf_utente_23",$_SESSION['lan'])." ".strtoupper($comResidenzaTrasgressore["Title"]). " (" . strtoupper($_POST['ProvinciaTrasgressore']) . ") " . $xml->getWord("Via",$_SESSION['lan'])." ".strtoupper($_POST['ViaResidenzaTrasgressore'])." ".$xml->getWord("pdf_utente_9",$_SESSION['lan'])." ".$_POST['NumeroResidenzaTrasgressore'].". ".$xml->getWord("pdf_utente_19",$_SESSION['lan']);
        $datiTrasgressore .= " ".$_POST['TelefonoUtenteTrasgressore']." ".$xml->getWord("pdf_utente_8",$_SESSION['lan'])." ".$_POST['CategoriaTrasgressore']." ".$xml->getWord("pdf_utente_9",$_SESSION['lan'])." ".$_POST['NumeroPatenteTrasgressore']." ".$xml->getWord("pdf_utente_10",$_SESSION['lan'])." ".$_POST['DataRilascioTrasgressore']. " " . $xml->getWord("pdf_utente_22",$_SESSION['lan']) . " " . $_POST["DataPrimoRilascioTrasgressore"];
        $datiTrasgressore .= " ".$xml->getWord("pdf_trasgressore_4",$_SESSION['lan'])." ".$_POST['AutoritaRilascioTrasgressore']." ".$xml->getWord("pdf_utente_12",$_SESSION['lan'])." ".$_POST['DataValiditaTrasgressore'];


        $pdf->SetFont('Helvetica', '', $fontSecondo);
        $pdf->MultiCell(0, 0, $datiTrasgressore, false, "L", 0, 0, "", $pdf->GetY() + 2);
        $pdf->Ln();

        $pdf->MultiCell(0, 0, $xml->getWord("data",$_SESSION['lan'])." ____/____/______", false, "L", 0, 0, "", $pdf->GetY() + 2);
        $pdf->Ln();

        /*$pageWidth    = $pdf->getPageWidth();   // Get total page width, without margins
        $pageMargins  = $pdf->getMargins();     // Get all margins as array
        $headerMargin = $pageMargins['header']; // Get the header margin
        $px2          = $pageWidth - $headerMargin; // Compute x value for second point of line

        $p1x   = $px2 - 20;//$this->getX();
        $p1y   = $pdf->getY();
        $p2x   = $px2;
        $p2y   = $p1y;  // Use same y for a straight line*/

        $pageMargins  = $pdf->getMargins();     // Get all margins as array
        $headerMargin = $pageMargins['header'];

        $style = array();

        $pdf->MultiCell(50, 0, $xml->getWord("firma_obbligato",$_SESSION['lan']), false, "C", 0, 0, "", $pdf->GetY() + 11);
        $pdf->MultiCell(50, 0, $xml->getWord("firma_trasgressore",$_SESSION['lan']), false, "C", 0, 0, $pdf->getPageWidth()-$pageMargins["right"]-50, $pdf->GetY());
        $pdf->Ln();
        $pdf->Line($pdf->getX()+3, $pdf->getY()+10, $pdf->getX()+50, $pdf->getY()+10, $style);
        $pdf->Line($pdf->getPageWidth()-$pageMargins["right"]-50, $pdf->getY()+10, $pdf->getPageWidth()-$pageMargins["right"], $pdf->getY()+10, $style);
        $pdf->Ln();



        break;
    }
    case 3:
    {
        if ($_POST['ComuneNascita1']!="") $comNascita["Title"] = $_POST['ComuneNascita1'];
        else {
            /*$query = "SELECT Title FROM city WHERE Id = '" . $_POST['ComuneNascita'] . "'";
            $comNascita = $cls_db_sarida->getArrayLine($cls_db_sarida->ExecuteQuery($query));*/

            $comNascita["Title"] = GetTCityTitle($_POST['ComuneNascita'],$URL_DATA);
        }



        if ($_POST['ComuneResidenza1']!="") $comResidenza["Title"] = $_POST['ComuneResidenza1'];
        else {
            /*$query = "SELECT Title FROM city WHERE Id = '" . $_POST['ComuneResidenza'] . "'";
            $comResidenza = $cls_db_sarida->getArrayLine($cls_db_sarida->ExecuteQuery($query));*/

            $comResidenza["Title"] = GetTCityTitle($_POST['ComuneResidenza'],$URL_DATA);
            //echo $comResidenza["Title"]; die;

        }

        if ($_POST['ComuneNascitaTrasgressore1']!="") $comNascitaTrasgressore["Title"] = $_POST['ComuneNascitaTrasgressore1'];
        else {
            /*$query = "SELECT Title FROM city WHERE Id = '" . $_POST['ComuneNascitaTrasgressore'] . "'";
            $comNascitaTrasgressore = $cls_db_sarida->getArrayLine($cls_db_sarida->ExecuteQuery($query));*/

            $comNascitaTrasgressore["Title"] = GetTCityTitle($_POST['ComuneNascitaTrasgressore'],$URL_DATA);
        }

        if ($_POST['ComuneResidenzaTrasgressore1']!="") $comResidenzaTrasgressore["Title"] = $_POST['ComuneResidenzaTrasgressore1'];
        else {
            /*$query = "SELECT Title FROM city WHERE Id = '" . $_POST['ComuneResidenzaTrasgressore'] . "'";
            $comResidenzaTrasgressore = $cls_db_sarida->getArrayLine($cls_db_sarida->ExecuteQuery($query));*/

            $comResidenzaTrasgressore["Title"] = GetTCityTitle($_POST['ComuneResidenzaTrasgressore'],$URL_DATA);
        }


        $datiUtente = $xml->getWord("pdf_utente_1",$_SESSION['lan']) . $_POST['CronoNumeroVerbale'] . "/" . $_POST['CronoAnnoVerbale'] . "/" . $_POST['CronotVervbale'];
        $datiUtente .= " ".$xml->getWord("pdf_utente_2",$_SESSION['lan'])." " . $_POST["GiornoVerbale"] . "/" . $_POST["MeseVerbale"] . "/" . $_POST["AnnoVerbale"] . " ".$xml->getWord("pdf_utente_3",$_SESSION['lan'])." " . $_POST['TargaVeicolo']." ";
        $datiUtente .= $xml->getWord("pdf_utente_4",$_SESSION['lan'])." ". strtoupper($_POST['Nome']) . " " . strtoupper($_POST['Cognome']) . $xml->getWord("pdf_utente_5",$_SESSION['lan'])." " . strtoupper($_POST['StatoNascitaCompleto']) ." ". $xml->getWord("pdf_utente_23",$_SESSION['lan'])." " . strtoupper($comNascita["Title"]) . " " . $xml->getWord("pdf_utente_6",$_SESSION['lan'])." " . $_POST['DataNascita'];
        $datiUtente .= " ".$xml->getWord("pdf_utente_7",$_SESSION['lan'])." " . strtoupper($_POST['StatoResidenzaCompleto']) ." ". $xml->getWord("pdf_utente_23",$_SESSION['lan']). " " . strtoupper($comResidenza["Title"]) . " (" . strtoupper($_POST['ComuneProvincia']) . ") " . strtoupper($_POST['ViaResidenza']) . " " . $_POST['NumeroResidenza'] . "/" . $_POST['InternoResidenza'];
        $datiUtente .= $xml->getWord("pdf_utente_8",$_SESSION['lan']). " " . $_POST['Categoria'] . " " .$xml->getWord("pdf_utente_9",$_SESSION['lan']). " " . $_POST['NumeroPatente'] . " " .$xml->getWord("pdf_utente_10",$_SESSION['lan']). " " . $_POST['DataRilascio'] . " " . $xml->getWord("pdf_utente_22",$_SESSION['lan']) . " " . $_POST["DataPrimoRilascio"] . " " .$xml->getWord("pdf_utente_11",$_SESSION['lan'])." ";
        $datiUtente .= $_POST['AutoritaRilascio'] . " ".$xml->getWord("pdf_utente_12",$_SESSION['lan']). " " . $_POST['DataValidita'] . " ".$xml->getWord("pdf_utente_13",$_SESSION['lan']). " " . $_POST['TelefonoUtente'] . $xml->getWord("pdf_utente_14",$_SESSION['lan']). " ";

        if ($_POST["TipoTrasgressore"] == "PV") {
            $datiUtente .= $xml->getWord("pdf_utente_15",$_SESSION['lan']);
        } else {
            if ($_POST['ComuneDitta1']!="") $comSedeDitta["Title"] = $_POST['ComuneDitta1'];
            else {
                /*$query = "SELECT Title FROM city WHERE Id = '" . $_POST['ComuneDitta'] . "'";
                $comSedeDitta = $cls_db_sarida->getArrayLine($cls_db_sarida->ExecuteQuery($query));*/

                $comSedeDitta["Title"] = GetTCityTitle($_POST['ComuneDitta'],$URL_DATA);
            }
            /*$query = "SELECT Title FROM country WHERE Id = '" . $_POST['SedeLR'] . "'";
            $statoSedeDitta = $cls_db_sarida->getArrayLine($cls_db_sarida->ExecuteQuery($query));*/

            $statoSedeDitta["Title"] = GetTCityTitle($_POST['SedeLR'],$URL_DATA);

            $datiUtente .= $xml->getWord("pdf_utente_16",$_SESSION['lan']). " " . $_POST['NomeDitta'] . " " . $xml->getWord("pdf_utente_17",$_SESSION['lan']). " " . strtoupper($statoSedeDitta["Title"]) . " " . $xml->getWord("pdf_utente_18",$_SESSION['lan']). " " . strtoupper($comSedeDitta["Title"]);
            $datiUtente .= " (" . strtoupper($_POST['ProvinciaDitta']) . ") " . $_POST['ViaDitta'] . " " . $_POST['NumeroDitta'] . "/" . $_POST['InternoDitta'] . " " . $xml->getWord("pdf_utente_19",$_SESSION['lan']). " " . $_POST['TelefonoDitta'] . ".";
        }

        $pdf->MultiCell(0, 0, $datiUtente, false, "L", 0, 0, "", $pdf->GetY() + 2);
        $pdf->Ln();

        $pdf->SetFont('Helvetica', 'B', $fontSecondo);
        $datiUtente_1 = $xml->getWord("pdf_utente_20",$_SESSION['lan']);
        $pdf->MultiCell(0, 0, $datiUtente_1, false, "L", 0, 0, "", $pdf->GetY() + 2);
        $pdf->Ln();

        $datiTrasgressore = $xml->getWord("pdf_trasgressore_1",$_SESSION['lan'])." ".strtoupper($_POST['NomeTrasgressore'])." ".strtoupper($_POST['CognomeTrasgressore'])." ".$xml->getWord("pdf_utente_5",$_SESSION['lan'])." " . strtoupper($_POST['StatoNascitaTrasgressoreCompleto']) ." ". $xml->getWord("pdf_utente_23",$_SESSION['lan'])." ".strtoupper($comNascitaTrasgressore["Title"])." ".$xml->getWord("pdf_trasgressore_3",$_SESSION['lan'])." ".$_POST['DataNascitaTrasgressore'];
        $datiTrasgressore .= " ".$xml->getWord("ResidenteIn",$_SESSION['lan'])." " . strtoupper($_POST['StatoResidenzaTrasgressoreCompleto']) ." ". $xml->getWord("pdf_utente_23",$_SESSION['lan'])." ".strtoupper($comResidenzaTrasgressore["Title"]). " (" . strtoupper($_POST['ProvinciaTrasgressore']) . ") " .$xml->getWord("Via",$_SESSION['lan'])." ".strtoupper($_POST['ViaResidenzaTrasgressore'])." ".$xml->getWord("pdf_utente_9",$_SESSION['lan'])." ".$_POST['NumeroResidenzaTrasgressore'].". ".$xml->getWord("pdf_utente_19",$_SESSION['lan']);
        $datiTrasgressore .= " ".$_POST['TelefonoUtenteTrasgressore']." ".$xml->getWord("pdf_utente_8",$_SESSION['lan'])." ".$_POST['CategoriaTrasgressore']." ".$xml->getWord("pdf_utente_9",$_SESSION['lan'])." ".$_POST['NumeroPatenteTrasgressore']." ".$xml->getWord("pdf_utente_10",$_SESSION['lan'])." ".$_POST['DataRilascioTrasgressore']. " " . $xml->getWord("pdf_utente_22",$_SESSION['lan']) . " " . $_POST["DataPrimoRilascioTrasgressore"];
        $datiTrasgressore .= " ".$xml->getWord("pdf_trasgressore_4",$_SESSION['lan'])." ".$_POST['AutoritaRilascioTrasgressore']." ".$xml->getWord("pdf_utente_12",$_SESSION['lan'])." ".$_POST['DataValiditaTrasgressore'];


        $pdf->SetFont('Helvetica', '', $fontSecondo);
        $pdf->MultiCell(0, 0, $datiTrasgressore, false, "L", 0, 0, "", $pdf->GetY() + 2);
        $pdf->Ln();

        $pdf->MultiCell(0, 0, $xml->getWord("data",$_SESSION['lan'])." ____/____/______", false, "L", 0, 0, "", $pdf->GetY() + 2);
        $pdf->Ln();

        /*$pageWidth    = $pdf->getPageWidth();   // Get total page width, without margins
        $pageMargins  = $pdf->getMargins();     // Get all margins as array
        $headerMargin = $pageMargins['header']; // Get the header margin
        $px2          = $pageWidth - $headerMargin; // Compute x value for second point of line

        $p1x   = $px2 - 20;//$this->getX();
        $p1y   = $pdf->getY();
        $p2x   = $px2;
        $p2y   = $p1y;  // Use same y for a straight line*/

        $pageMargins  = $pdf->getMargins();     // Get all margins as array
        $headerMargin = $pageMargins['header'];

        $style = array();

        $pdf->MultiCell(50, 0, $xml->getWord("firma_obbligato",$_SESSION['lan']), false, "C", 0, 0, "", $pdf->GetY() + 11);
        //$pdf->MultiCell(50, 0, $xml->getWord("firma_trasgressore",$_SESSION['lan']), false, "C", 0, 0, $pdf->getPageWidth()-$pageMargins["right"]-50, $pdf->GetY());
        $pdf->Ln();
        $pdf->Line($pdf->getX()+3, $pdf->getY()+10, $pdf->getX()+50, $pdf->getY()+10, $style);
        //$pdf->Line($pdf->getPageWidth()-$pageMargins["right"]-50, $pdf->getY()+10, $pdf->getPageWidth()-$pageMargins["right"], $pdf->getY()+10, $style);
        $pdf->Ln();


        break;
    }
    case 4:
    {

        if ($_POST['ComuneNascitaTrasgressore1']!="") $comNascitaTrasgressore["Title"] = $_POST['ComuneNascitaTrasgressore1'];
        else {
            /*$query = "SELECT Title FROM city WHERE Id = '" . $_POST['ComuneNascitaTrasgressore'] . "'";
            $comNascitaTrasgressore = $cls_db_sarida->getArrayLine($cls_db_sarida->ExecuteQuery($query));*/

            $comNascitaTrasgressore["Title"] = GetTCityTitle($_POST['ComuneNascitaTrasgressore'],$URL_DATA);
        }

        if ($_POST['ComuneResidenzaTrasgressore1']!="") $comResidenzaTrasgressore["Title"] = $_POST['ComuneResidenzaTrasgressore1'];
        else {
            /*$query = "SELECT Title FROM city WHERE Id = '" . $_POST['ComuneResidenzaTrasgressore'] . "'";
            $comResidenzaTrasgressore = $cls_db_sarida->getArrayLine($cls_db_sarida->ExecuteQuery($query));*/

            $comResidenzaTrasgressore["Title"] = GetTCityTitle($_POST['ComuneResidenzaTrasgressore'],$URL_DATA);
        }

        $datiTrasgressore = $xml->getWord("pdf_utente_1",$_SESSION['lan']) . $_POST['CronoNumeroVerbale'] . "/" . $_POST['CronoAnnoVerbale'] . "/" . $_POST['CronotVervbale'];
        $datiTrasgressore .= " ".$xml->getWord("pdf_utente_2",$_SESSION['lan'])." " . $_POST["GiornoVerbale"] . "/" . $_POST["MeseVerbale"] . "/" . $_POST["AnnoVerbale"] . " ".$xml->getWord("pdf_utente_3",$_SESSION['lan'])." " . $_POST['TargaVeicolo']." ";
        $datiTrasgressore .= $xml->getWord("pdf_trasgressore_1",$_SESSION['lan'])." ".strtoupper($_POST['NomeTrasgressore'])." ".strtoupper($_POST['CognomeTrasgressore'])." ".$xml->getWord("pdf_utente_5",$_SESSION['lan'])." " . strtoupper($_POST['StatoNascitaTrasgressoreCompleto']) ." ". $xml->getWord("pdf_utente_23",$_SESSION['lan'])." ".strtoupper($comNascitaTrasgressore["Title"])." ".$xml->getWord("pdf_trasgressore_3",$_SESSION['lan'])." ".$_POST['DataNascitaTrasgressore'];
        $datiTrasgressore .= " ".$xml->getWord("ResidenteIn",$_SESSION['lan'])." " . strtoupper($_POST['StatoResidenzaTrasgressoreCompleto']) ." ". $xml->getWord("pdf_utente_23",$_SESSION['lan'])." ".strtoupper($comResidenzaTrasgressore["Title"]). " (" . strtoupper($_POST['ProvinciaTrasgressore']) . ") " .$xml->getWord("Via",$_SESSION['lan'])." ".strtoupper($_POST['ViaResidenzaTrasgressore'])." ".$xml->getWord("pdf_utente_9",$_SESSION['lan'])." ".$_POST['NumeroResidenzaTrasgressore'].". ".$xml->getWord("pdf_utente_19",$_SESSION['lan']);
        $datiTrasgressore .= " ".$_POST['TelefonoUtenteTrasgressore']." ".$xml->getWord("pdf_utente_8",$_SESSION['lan'])." ".$_POST['CategoriaTrasgressore']." ".$xml->getWord("pdf_utente_9",$_SESSION['lan'])." ".$_POST['NumeroPatenteTrasgressore']." ".$xml->getWord("pdf_utente_10",$_SESSION['lan'])." ".$_POST['DataRilascioTrasgressore']. " " . $xml->getWord("pdf_utente_22",$_SESSION['lan']) . " " . $_POST["DataPrimoRilascioTrasgressore"];
        $datiTrasgressore .= " ".$xml->getWord("pdf_trasgressore_4",$_SESSION['lan'])." ".$_POST['AutoritaRilascioTrasgressore']." ".$xml->getWord("pdf_utente_12",$_SESSION['lan'])." ".$_POST['DataValiditaTrasgressore'];

        $pdf->SetFont('Helvetica', '', $fontSecondo);
        $pdf->MultiCell(0, 0, $datiTrasgressore, false, "L", 0, 0, "", $pdf->GetY() + 2);
        $pdf->Ln();

        $pdf->SetFont('Helvetica', 'B', $fontSecondo);
        $datiTrasgressore_1 = $xml->getWord("pdf_utente_20",$_SESSION['lan']);
        $pdf->MultiCell(0, 0, $datiTrasgressore_1, false, "L", 0, 0, "", $pdf->GetY() + 2);
        $pdf->Ln();

        $pdf->SetFont('Helvetica', '', $fontSecondo);
        $pdf->MultiCell(0, 0, $xml->getWord("pdf_utente_21",$_SESSION['lan']), false, "L", 0, 0, "", $pdf->GetY() + 2);
        $pdf->Ln();

        $pdf->MultiCell(0, 0, $xml->getWord("data",$_SESSION['lan'])." ____/____/______", false, "L", 0, 0, "", $pdf->GetY() + 2);
        $pdf->Ln();

        $style = array();
        $pdf->MultiCell(50, 0, $xml->getWord("firma_trasgressore",$_SESSION['lan']), false, "C", 0, 0, "", $pdf->GetY() + 11);
        $pdf->Ln();

        $pdf->Line($pdf->getX()+3, $pdf->getY()+10, $pdf->getX()+50, $pdf->getY()+10, $style);
        $pdf->Ln();


        break;
    }
}

$pdf->SetFont('Helvetica', 'B', $fontPrimo);
$pdf->MultiCell(0, 0, $xml->getWord("avvertenze",$_SESSION['lan']), false, "C", 0, 0, "", $pdf->GetY() + 25);
$pdf->Ln();

$pdf->SetFont('Helvetica', 'B', $fontSecondo);
$pdf->MultiCell(0, 0, $xml->getWord("avvertenze_testo",$_SESSION['lan']), false, "L", 0, 0, "", $pdf->GetY() + 2);
$pdf->Ln();

$pdf->SetFont('Helvetica', 'B', $fontPrimo);
$pdf->MultiCell(0, 0, $xml->getWord("istruzioni",$_SESSION['lan']), false, "C", 0, 0, "", $pdf->GetY() + 12);
$pdf->Ln();

$pdf->SetFont('Helvetica', '', $fontSecondo);
$pdf->MultiCell(0, 0, "1) ".$xml->getWord("istruzioni_testo_1",$_SESSION['lan']), false, "L", 0, 0, "", $pdf->GetY() + 2);
$pdf->Ln();

$pdf->MultiCell(0, 0, "2) ".$xml->getWord("istruzioni_testo_2",$_SESSION['lan']), false, "L", 0, 0, "", $pdf->GetY() + 6);
$pdf->Ln();

$pdf->MultiCell(0, 0, "3) ".$xml->getWord("istruzioni_testo_3",$_SESSION['lan']), false, "L", 0, 0, "", $pdf->GetY() + 6);
$pdf->Ln();

$pdf->MultiCell(0, 0, "4) ".$xml->getWord("istruzioni_testo_4",$_SESSION['lan']), false, "L", 0, 0, "", $pdf->GetY() + 6);
$pdf->Ln();

$pdf->MultiCell(0, 0, "5) ".$xml->getWord("istruzioni_testo_5",$_SESSION['lan']), false, "L", 0, 0, "", $pdf->GetY() + 6);
$pdf->Ln();

//var_dump($_SERVER['DOCUMENT_ROOT']);
//$path = $cls_Utils->crea_dir($_SERVER['DOCUMENT_ROOT'] . "/PoliziaMunicipale/inc/uploads/documenti/pdfTemp");
/*if(is_dir(str_replace("/home/","",$_SERVER['DOCUMENT_ROOT']) . "/inc/uploads/documenti/pdfTemp"))
    echo "1) ".str_replace("/home/","",$_SERVER['DOCUMENT_ROOT']) . "/inc/uploads/documenti/pdfTemp"."<br><br><br>";
if(is_dir($_SERVER['DOCUMENT_ROOT']. "/inc/uploads/documenti/pdfTemp"))
    echo "2) ".$_SERVER['DOCUMENT_ROOT']. "/inc/uploads/documenti/pdfTemp";*/
//die;
$path = $cls_Utils->crea_dir($_SERVER['DOCUMENT_ROOT']. "inc/uploads/documenti/pdfTemp");
$pdf->Output($path . "/dichiarazione_trasgressore.pdf", "F");


//$conn->close();

//echo "<script>history.go(-1);</script>";


echo json_encode(
    array(
        "page" => "pdf",
        "path" => "/inc/uploads/documenti/pdfTemp/dichiarazione_trasgressore.pdf",
        "codiceAnticontraffazione" => $CodiceAnticontraffazione,
        "flagPortale" => "si"//poi sarà in base alla scelta della drop del portale ,raccomandata A.R. etc
    )
);
die;