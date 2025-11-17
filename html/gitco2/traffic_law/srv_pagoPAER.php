<?php
define("EMPTYSESSION_NOREDIRECT", true);
define("ENTRANEXT_WSDL", "EntraNext_-_Connettore LinkNext_Proxy CDS_v1.6.6.wsdl");

require_once ('_path.php');
require_once(INC."/parameter.php");
require_once(CLS."/cls_db.php");
require_once(INC."/function_postalCharge.php");
require_once(INC."/function.php");
require_once(CLS."/pagopaER/PagoPAERService.php");

define("ENTRANEXT_TMP_FOLDER", TMP."/pagoPAER");
define("ENTRANEXT_REQ_FOLDER", ENTRANEXT_TMP_FOLDER."/richieste");
define("ENTRANEXT_REQ_INVALID_FOLDER", ENTRANEXT_TMP_FOLDER."/richieste_non_valide");
define("ENTRANEXT_RESP_FOLDER", ENTRANEXT_TMP_FOLDER."/risposte");

ini_set("error_log", ENTRANEXT_TMP_FOLDER."/PagoPAERService_".date("Y-m-d").".log");

function leggiXml(&$invalid, &$errorXmlText){
    $xml = implode(" ", file("php://input"));
    $parser = xml_parser_create("UTF-8");
    if (!xml_parse($parser, $xml, true)){
        http_response_code(500);
        $invalid = true;
        $errorXmlText = "XML non valido: ".
            xml_error_string(xml_get_error_code($parser)).
            " alla linea: ".xml_get_current_line_number($parser).
            ", colonna: ".xml_get_current_column_number($parser);
        echo $errorXmlText.PHP_EOL.$xml;
    }
    return $xml;
}

function salvaDatiLetti($file, $dati){
    $xmlFile = @fopen($file, 'w');
    @fwrite($xmlFile, $dati);
    @fclose($xmlFile);
}

if (!is_dir(ENTRANEXT_REQ_FOLDER)){
    mkdir(ENTRANEXT_REQ_FOLDER, 0770, true);
    chmod(ENTRANEXT_REQ_FOLDER, 0770);
}
if (!is_dir(ENTRANEXT_RESP_FOLDER)){
    mkdir(ENTRANEXT_RESP_FOLDER, 0770, true);
    chmod(ENTRANEXT_RESP_FOLDER, 0770);
}
if (!is_dir(ENTRANEXT_REQ_INVALID_FOLDER)){
    mkdir(ENTRANEXT_REQ_INVALID_FOLDER, 0770, true);
    chmod(ENTRANEXT_REQ_INVALID_FOLDER, 0770);
}

$cls_db = new CLS_DB(new cls_db_gestoreErroriXML());
$invalid = false;
$errorXmlText = '';
$rand = rand();
$data = date('Y-m-d_H-i-s');
$richiesta = leggiXml($invalid, $errorXmlText);

$PagoPAERService=NEW PagoPAERService(ROOT."/external/".ENTRANEXT_WSDL,"",$cls_db);

if($invalid){
    $PagoPAERService->Log('D', 'leggiXml', $errorXmlText);
    salvaDatiLetti(ENTRANEXT_REQ_INVALID_FOLDER.'/'.$rand.'_'.$data.'_invalid.txt',$errorXmlText.PHP_EOL.$richiesta);
} else {
    salvaDatiLetti(ENTRANEXT_REQ_FOLDER.'/'.$rand.'_'.$data.'_request.xml',$richiesta);
    
    $server=$PagoPAERService->AvviaServer();
    ob_start();
    $server->handle($richiesta);
    $risposta = ob_get_contents();
    
    salvaDatiLetti(ENTRANEXT_RESP_FOLDER.'/'.$rand.'_'.$data.'_response_'.$PagoPAERService->metodoChiamato.'.xml', $risposta);
}
