<?php
require_once ('cost-sarida-gitco.php');
session_start();

define("LOCK_SITE",false);

const PDFA="pdf";
ini_set('display_errors',DEBUG);
ini_set('memory_limit','512M');
date_default_timezone_set("Europe/Rome");


define("ROOT", __DIR__);

$MainPath = CONTESTO_URL.'/traffic_law';
$MainRoot = str_replace('traffic_law','',ROOT);
$GitcoRoot = str_replace('traffic_law','',ROOT);
$empySession = CONTESTO_URL ?: '/';

if(!isset($_SESSION['username']) && (!defined('EMPTYSESSION_NOREDIRECT') || EMPTYSESSION_NOREDIRECT === false)){
    header("location: ".$empySession);
}


$WebRoot= $empySession.'/traffic_law';

define("IMG", $MainPath."/img");
define("FONT", $MainPath."/fonts");+
define("INC", ROOT."/inc");
define("PGFN", INC."/page_functions");
define("LOG", ARCHIVIO."/traccia");

define("JS", $MainPath."/js");
define("LIB", $MainPath."/lib");
define("CSS", $MainPath."/css");
define("CLS", ROOT."/cls");
define("TCPDF", $GitcoRoot. "tcpdf");
define("COD", ROOT."/cod");

define("SIGN", $MainPath."/img/sign");
define("BLAZON", $MainPath."/img/blazon");

define("SIGNATURES", ARCHIVIO."/doc/signatures");

define("FOREIGN_VIOLATION", ARCHIVIO."/doc/foreign/violation");
define("FOREIGN_FINE", ARCHIVIO."/doc/foreign/fine");
define("FOREIGN_REQUEST", ARCHIVIO."/doc/foreign/request");
define("FOREIGN_FLOW", ARCHIVIO."/doc/foreign/flow");
define("FOREIGN_DISPUTE", ARCHIVIO."/doc/foreign/dispute");
define("FOREIGN_RATE", ARCHIVIO."/doc/foreign/rate");
define("WEB_FOREIGN_RATE", $WebRoot."/doc/foreign/rate");
define("FOREIGN_PEC", ARCHIVIO."/doc/foreign/pec");

define("INIPEC", ARCHIVIO."/doc/inipec");
define("INIPEC_REQUEST", INIPEC."/richieste");
define("INIPEC_RESPONSE", INIPEC); //Le risposte vengono lette dalla cartella inipec stessa senza sottocartelle

define("NATIONAL_VIOLATION", ARCHIVIO."/doc/national/violation");
define("NATIONAL_FINE", ARCHIVIO."/doc/national/fine");
define("NATIONAL_REQUEST", ARCHIVIO."/doc/national/request");
define("NATIONAL_FLOW", ARCHIVIO."/doc/national/flow");
define("NATIONAL_DISPUTE", ARCHIVIO."/doc/national/dispute");
define("NATIONAL_PEC", ARCHIVIO."/doc/national/pec");
define("NATIONAL_RATE", ARCHIVIO."/doc/national/rate");
define("WEB_NATIONAL_RATE", $WebRoot."/doc/national/rate");
define("NATIONAL_INJUNCTION", ARCHIVIO."/doc/national/injunction");
define("FOREIGN_INJUNCTION", ARCHIVIO."/doc/foreign/injunction");
define("WEB_NATIONAL_INJUNCTION", $MainPath."/doc/national/injunction");
define("WEB_FOREIGN_INJUNCTION", $MainPath."/doc/foreign/injunction");

define("NATIONAL_REQUEST_MCTC_WS", NATIONAL_REQUEST."/mctc_ws");

define("FOREIGN_FINE_HTML", $MainPath."/doc/foreign/fine");
define("FOREIGN_VIOLATION_HTML", $MainPath."/doc/foreign/violation");
define("FOREIGN_FLOW_HTML", $MainPath."/doc/foreign/flow");
define("FOREIGN_RATE_HTML", $MainPath."/doc/foreign/rate");

define("NATIONAL_FINE_HTML", $MainPath."/doc/national/fine");
define("NATIONAL_VIOLATION_HTML", $MainPath."/doc/national/violation");
define("NATIONAL_FLOW_HTML", $MainPath."/doc/national/flow");
define("NATIONAL_RATE_HTML", $MainPath."/doc/national/rate");

define("PAYMENT_RECLAIM_HTML", $MainPath."/doc/reclaim/payment");
define("MAILDOWNLOAD_FOLDER_HTML", $MainPath."/doc/mail_download");
define("IMPORT_FOLDER_MAGGIOLI_HTML", $MainPath."/public/DA_IMPORTARE_MAGGIOLI");
define("VIOLATION_FOLDER_HTML", $MainPath."/public/_VIOLATION_");
define("PRINT_FOLDER_HTML", $MainPath."/doc/print");


define("PUBLIC_FOLDER", ARCHIVIO."/public");
define("VALIDATE_FOLDER", ARCHIVIO."/public/_VALIDATION_");
define("VIOLATION_FOLDER", ARCHIVIO."/public/_VIOLATION_");
define("REPORT_FOLDER", ARCHIVIO."/public/_REPORT_");
define("WARNING_FOLDER", ARCHIVIO."/public/_WARNING_");
define("PAYMENT_FOLDER", ARCHIVIO."/public/_PAYMENT_");
define("TESTVISURE_FOLDER", ARCHIVIO."/public/_TESTVISURE_");
define("IMPORT_FOLDER", ARCHIVIO."/public/DA_IMPORTARE"); 
define("IMPORT_FOLDER_MAGGIOLI", ARCHIVIO."/public/DA_IMPORTARE_MAGGIOLI");
define('IMPORT_FOLDER_PUBLIMAIL' , ARCHIVIO.'/public/DA_IMPORTARE/PUBLIMAIL'); //Costante Publimail utilizzata nella relativa importazione
define("PECRECEIPT_FOLDER", ARCHIVIO."/public/RICEVUTE_PEC");
define("TMP", ARCHIVIO."/public/TMP");
define("HELP",$MainPath."/doc/help");
define("PRINT_FOLDER", ARCHIVIO."/doc/print");
define("SIGN_FOLDER", ARCHIVIO."/img/sign");
define("PAYMENT_RECLAIM", ARCHIVIO."/doc/reclaim/payment");
define("MAILDOWNLOAD_FOLDER", ARCHIVIO."/doc/mail_download");
define("EXPORT_FOLDER", ARCHIVIO."/doc/export");

define("LICENSE_POINT", ARCHIVIO."/doc/license_point");

define("SIGNED_FOLDER",PUBLIC_FOLDER.'/FIRMATI');
define("TOSIGN_FOLDER",PUBLIC_FOLDER.'/DA_FIRMARE');