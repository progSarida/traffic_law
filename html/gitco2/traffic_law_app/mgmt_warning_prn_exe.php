<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");


require_once(TCPDF."/tcpdf.php");

$CreateType = 1;
$FineId          = CheckValue('FineId','n');
$Year = Date("Y");
$DocumentationTypeId=1;
$rs= new CLS_DB();



$rs_FormWarning = $rs->Select('FormWarning', "CityId='". $_SESSION['cityid'] ."'");
$str_Warning_App_Content = mysqli_fetch_array($rs_FormWarning)['Content'];

$a_Page = explode("<page>", $str_Warning_App_Content);

$rs_Fine = $rs->Select('V_Fine', "Id=".$FineId);
$r_Fine = mysqli_fetch_array($rs_Fine);
$ViolationTypeId = $r_Fine['ViolationTypeId'];
$rs_RuleType = $rs->Select('V_RuleType', "ViolationTypeId=" . $ViolationTypeId . " AND CityId='" . $_SESSION['cityid'] . "'");
$r_RuleType = mysqli_fetch_array($rs_RuleType);
$RuleTypeId = $r_RuleType['Id'];
$str_WhereRule = ($RuleTypeId == 1) ? " AND RuleTypeId=1" : " AND RuleTypeId!=1";
$rs_Protocol = $rs->SelectQuery("SELECT IFNULL(MAX(ProtocolId)+1,1) ProtocolId, IFNULL(MAX(ProtocolIdAssigned)+1,0) ProtocolIdAssigned FROM V_FineAll WHERE CityId='" . $_SESSION['cityid'] . "' AND ProtocolYear=" . $Year . $str_WhereRule);
$r_Protocol = mysqli_fetch_array($rs_Protocol);
$ProtocolNumber = ($r_Protocol['ProtocolId']>$r_Protocol['ProtocolIdAssigned']) ? $r_Protocol['ProtocolId'] : $r_Protocol['ProtocolIdAssigned'];



$rs_FineOwner = $rs->Select('FineOwner', "FineId=".$FineId);
$r_FineOwner = mysqli_fetch_array($rs_FineOwner);

$ControllerCode         = $r_Fine['ControllerCode'];
$FineDate               = DateOutDB($r_Fine['FineDate']);
$FineTime               = TimeOutDB($r_Fine['FineTime']);
$Address                = $r_Fine['Address'];
$VehicleType            = $r_Fine['VehicleTitleIta'];
$VehicleCountry         = $r_Fine['VehicleCountry'];
$VehiclePlate           = $r_Fine['VehiclePlate'];
$VehicleColor           = $r_Fine['VehicleColor'];
$VehicleBrand           = $r_Fine['VehicleBrand'];
$VehicleModel           = $r_Fine['VehicleModel'];
$CityTitle              = $r_Fine['CityTitle'];

$Article                = $r_Fine['Article'];
$Paragraph              = $r_Fine['Paragraph'];
$Letter                 = $r_Fine['Letter'];
$ArticleDescription     = $r_FineOwner['ArticleDescriptionIta'];
$Fee                    = $r_Fine['Fee'];
$PartialFee             = number_format($Fee*FINE_PARTIAL,2);
$MaxFee                 = $r_Fine['MaxFee'];
$ReasonTitle            = $r_Fine['ReasonTitleIta'];


$str_DocumentationPath =  ($VehicleCountry=='Italia') ? NATIONAL_VIOLATION : FOREIGN_VIOLATION;

if (!is_dir($str_DocumentationPath ."/". $_SESSION['cityid'] ."/". $FineId)) {
    mkdir($str_DocumentationPath ."/". $_SESSION['cityid'] ."/". $FineId, 0777);
}



if($CreateType){
    $FileNamePDF = date("Y-m-d_H-i-s") . "_". $FineId. ".pdf";


    $format=array(60,110);
    $pdf=new TCPDF('P','mm',$format);
    $pdf->AddPage();
    $pdf->SetFont('arial', '', 7);
    $pdf->SetTextColor(0,0,0);
    $pdf->SetLeftMargin('1');
    $pdf->SetRightMargin('1');
    $pdf->SetTopMargin('0');

    $pdf->Image(IMG.'/blazon/'.$_SESSION['cityid'].'/blazon.png',2, 12, 7, 11 );

    $str_Warning_App_Content = str_replace("<page>",'<div style="page-break-after:always"><span style="display:none">&nbsp;</span></div>',$str_Warning_App_Content);

    $str_Warning_App_Content = str_replace("{ProtocolId}",$ProtocolNumber,$str_Warning_App_Content);
    $str_Warning_App_Content = str_replace("{ProtocolYear}",$Year,$str_Warning_App_Content);

    $str_Warning_App_Content = str_replace("{FineDate}",$FineDate,$str_Warning_App_Content);
    $str_Warning_App_Content = str_replace("{FineTime}",$FineTime,$str_Warning_App_Content);
    $str_Warning_App_Content = str_replace("{Address}",$Address,$str_Warning_App_Content);
    $str_Warning_App_Content = str_replace("{VehicleType}",$VehicleType,$str_Warning_App_Content);
    $str_Warning_App_Content = str_replace("{VehicleCountry}",$VehicleCountry,$str_Warning_App_Content);
    $str_Warning_App_Content = str_replace("{VehiclePlate}",$VehiclePlate,$str_Warning_App_Content);
    $str_Warning_App_Content = str_replace("{VehicleColor}",$VehicleColor,$str_Warning_App_Content);
    $str_Warning_App_Content = str_replace("{VehicleBrand}",$VehicleBrand,$str_Warning_App_Content);
    $str_Warning_App_Content = str_replace("{VehicleModel}",$VehicleModel,$str_Warning_App_Content);
    $str_Warning_App_Content = str_replace("{CityTitle}",$CityTitle,$str_Warning_App_Content);

    $str_Warning_App_Content = str_replace("{Article}",$Article,$str_Warning_App_Content);
    $str_Warning_App_Content = str_replace("{Paragraph}",$Paragraph,$str_Warning_App_Content);
    $str_Warning_App_Content = str_replace("{Letter}",$Letter,$str_Warning_App_Content);
    $str_Warning_App_Content = str_replace("{ArticleDescription}",$ArticleDescription,$str_Warning_App_Content);
    $str_Warning_App_Content = str_replace("{Fee}",$Fee,$str_Warning_App_Content);
    $str_Warning_App_Content = str_replace("{ReasonTitle}",$ReasonTitle,$str_Warning_App_Content);
    $str_Warning_App_Content = str_replace("{ControllerCode}",$ControllerCode,$str_Warning_App_Content);





    $str_Warning_App_Content = str_replace("{Fee}",$Fee,$str_Warning_App_Content);
    $str_Warning_App_Content = str_replace("{PartialFee}",$PartialFee,$str_Warning_App_Content);







    $style = array(
        'border' => true,
        'vpadding' => 'auto',
        'hpadding' => 'auto',
        'fgcolor' => array(0,0,0),
        'bgcolor' => false, //array(255,255,255)
        'module_width' => 1, // width of a single module in points
        'module_height' => 1 // height of a single module in points
    );

    $PagoPAParameter = $pdf->serializeTCPDFtagParameters(array("www.tcpdf.org", 'QRCODE,H', 7, 15, 20, 20, $style, 'N'));



    $str_Warning_App_Content = str_replace("{PagoPAParameter}",$PagoPAParameter,$str_Warning_App_Content);
    $str_Warning_App_Content = str_replace("{ReasonTitle}",$ReasonTitle,$str_Warning_App_Content);
    $str_Warning_App_Content = str_replace("{ControllerCode}",$ControllerCode,$str_Warning_App_Content);




    $pdf->WriteHTML($str_Warning_App_Content,true);

    $pdf->Output($str_DocumentationPath . "/" . $_SESSION['cityid'] ."/". $FineId . '/' . $FileNamePDF, "F");
    sleep(1);

    $a_FineDocumentation = array(
        array('field' => 'FineId', 'selector' => 'value', 'type' => 'int', 'value' => $FineId, 'settype' => 'int'),
        array('field' => 'Documentation', 'selector' => 'value', 'type' => 'str', 'value' => $FileNamePDF),
        array('field' => 'DocumentationTypeId', 'selector' => 'value', 'type' => 'int', 'value' => $DocumentationTypeId, 'settype' => 'int'),
    );
    $rs->Insert('FineDocumentation', $a_FineDocumentation);

    $a_Fine = array(
        array('field'=>'ProtocolId','selector'=>'value','type'=>'int','value'=>$ProtocolNumber, 'settype'=>'int'),
    );

    $rs->Update('Fine', $a_Fine, "Id=".$FineId);



    $_SESSION['Documentation'] = $MainPathDoc . '/doc/national/violation/'. $_SESSION['cityid'] ."/". $FineId. '/' . $FileNamePDF;
    header("Location:panel.php");



} else {






    $FileNamePDF_1 = date("Y-m-d_H-i-s") . "_". $FineId. "_1.pdf";
    $FileNamePDF_2 = date("Y-m-d_H-i-s") . "_". $FineId. "_2.pdf";

    $FileNameJPG = date("Y-m-d_H-i-s") . "_". $FineId. ".jpeg";








    $format=array(60,160);
    $pdf=new TCPDF('P','mm',$format);
    $pdf->AddPage();
    $pdf->SetFont('arial', '', 7);
    $pdf->SetTextColor(0,0,0);
    $pdf->SetLeftMargin('1');
    $pdf->SetRightMargin('1');
    $pdf->SetTopMargin('0');



    $pdf->Image(IMG.'/blazon/'.$_SESSION['cityid'].'/blazon.png',2, 12, 7, 11 );

    $str_Warning_App_Content = $a_Page[0];

    $str_Warning_App_Content = str_replace("{ProtocolId}",$ProtocolNumber,$str_Warning_App_Content);
    $str_Warning_App_Content = str_replace("{ProtocolYear}",$Year,$str_Warning_App_Content);

    $str_Warning_App_Content = str_replace("{FineDate}",$FineDate,$str_Warning_App_Content);
    $str_Warning_App_Content = str_replace("{FineTime}",$FineTime,$str_Warning_App_Content);
    $str_Warning_App_Content = str_replace("{Address}",$Address,$str_Warning_App_Content);
    $str_Warning_App_Content = str_replace("{VehicleType}",$VehicleType,$str_Warning_App_Content);
    $str_Warning_App_Content = str_replace("{VehicleCountry}",$VehicleCountry,$str_Warning_App_Content);
    $str_Warning_App_Content = str_replace("{VehiclePlate}",$VehiclePlate,$str_Warning_App_Content);
    $str_Warning_App_Content = str_replace("{VehicleColor}",$VehicleColor,$str_Warning_App_Content);
    $str_Warning_App_Content = str_replace("{VehicleBrand}",$VehicleBrand,$str_Warning_App_Content);
    $str_Warning_App_Content = str_replace("{VehicleModel}",$VehicleModel,$str_Warning_App_Content);
    $str_Warning_App_Content = str_replace("{CityTitle}",$CityTitle,$str_Warning_App_Content);

    $str_Warning_App_Content = str_replace("{Article}",$Article,$str_Warning_App_Content);
    $str_Warning_App_Content = str_replace("{Paragraph}",$Paragraph,$str_Warning_App_Content);
    $str_Warning_App_Content = str_replace("{Letter}",$Letter,$str_Warning_App_Content);
    $str_Warning_App_Content = str_replace("{ArticleDescription}",$ArticleDescription,$str_Warning_App_Content);
    $str_Warning_App_Content = str_replace("{Fee}",$Fee,$str_Warning_App_Content);
    $str_Warning_App_Content = str_replace("{ReasonTitle}",$ReasonTitle,$str_Warning_App_Content);
    $str_Warning_App_Content = str_replace("{ControllerCode}",$ControllerCode,$str_Warning_App_Content);





    $str_Warning_App_Content = str_replace("{Fee}",$Fee,$str_Warning_App_Content);
    $str_Warning_App_Content = str_replace("{PartialFee}",$PartialFee,$str_Warning_App_Content);



    $pdf->WriteHTML($str_Warning_App_Content,true);
    $pdf->Output($str_DocumentationPath . "/" . $_SESSION['cityid'] ."/". $FineId . '/' . $FileNamePDF_1, "F");
    sleep(1);

    $a_FineDocumentation = array(
        array('field' => 'FineId', 'selector' => 'value', 'type' => 'int', 'value' => $FineId, 'settype' => 'int'),
        array('field' => 'Documentation', 'selector' => 'value', 'type' => 'str', 'value' => $FileNamePDF_1),
        array('field' => 'DocumentationTypeId', 'selector' => 'value', 'type' => 'int', 'value' => $DocumentationTypeId, 'settype' => 'int'),
    );
    $rs->Insert('FineDocumentation', $a_FineDocumentation);

    $format=array(60,140);
    $pdf=new TCPDF('P','mm',$format);
    $pdf->AddPage();
    $pdf->SetFont('arial', '', 7);
    $pdf->SetTextColor(0,0,0);
    $pdf->SetLeftMargin('2');
    $pdf->SetRightMargin('2');






    $style = array(
        'border' => true,
        'vpadding' => 'auto',
        'hpadding' => 'auto',
        'fgcolor' => array(0,0,0),
        'bgcolor' => false, //array(255,255,255)
        'module_width' => 1, // width of a single module in points
        'module_height' => 1 // height of a single module in points
    );

    $PagoPAParameter = $pdf->serializeTCPDFtagParameters(array("www.tcpdf.org", 'QRCODE,H', 7, 40, 20, 20, $style, 'N'));

    $str_Warning_App_Content = $a_Page[1];

    $str_Warning_App_Content = str_replace("{PagoPAParameter}",$PagoPAParameter,$str_Warning_App_Content);
    $str_Warning_App_Content = str_replace("{Fee}",$Fee,$str_Warning_App_Content);
    $str_Warning_App_Content = str_replace("{ReasonTitle}",$ReasonTitle,$str_Warning_App_Content);
    $str_Warning_App_Content = str_replace("{ControllerCode}",$ControllerCode,$str_Warning_App_Content);





    $str_Warning_App_Content = str_replace("{Fee}",$Fee,$str_Warning_App_Content);
    $str_Warning_App_Content = str_replace("{PartialFee}",$PartialFee,$str_Warning_App_Content);



    $pdf->WriteHTML($str_Warning_App_Content,true);



    $pdf->Output($str_DocumentationPath . "/" . $_SESSION['cityid'] ."/". $FineId . '/' . $FileNamePDF_2, "F");
    sleep(1);

    $a_FineDocumentation = array(
        array('field' => 'FineId', 'selector' => 'value', 'type' => 'int', 'value' => $FineId, 'settype' => 'int'),
        array('field' => 'Documentation', 'selector' => 'value', 'type' => 'str', 'value' => $FileNamePDF_2),
        array('field' => 'DocumentationTypeId', 'selector' => 'value', 'type' => 'int', 'value' => $DocumentationTypeId, 'settype' => 'int'),
    );
    $rs->Insert('FineDocumentation', $a_FineDocumentation);
    /*


    $im = new imagick();
    $im->setResolution(300, 300);
    $im->readImage($str_DocumentationPath . "/" . $_SESSION['cityid'] ."/". $FineId . '/' . $FileNamePDF);
    $im->setImageResolution(300, 300);
    $im->setImageFormat('jpeg');
    $im->setImageCompression(imagick::COMPRESSION_JPEG);
    $im->setImageCompressionQuality(100);
    $im->writeImage($str_DocumentationPath . "/" . $_SESSION['cityid'] ."/". $FineId . '/' . $FileNameJPG);
    $im->clear();
    $im->destroy();

    sleep(2);

    $a_FineDocumentation = array(
        array('field' => 'FineId', 'selector' => 'value', 'type' => 'int', 'value' => $FineId, 'settype' => 'int'),
        array('field' => 'Documentation', 'selector' => 'value', 'type' => 'str', 'value' => $FileNameJPG),
        array('field' => 'DocumentationTypeId', 'selector' => 'value', 'type' => 'int', 'value' => $DocumentationTypeId, 'settype' => 'int'),
    );
    $rs->Insert('FineDocumentation', $a_FineDocumentation);

    */













    $a_Fine = array(
        array('field'=>'ProtocolId','selector'=>'value','type'=>'int','value'=>$ProtocolNumber, 'settype'=>'int'),
    );



    $rs->Update('Fine', $a_Fine, "Id=".$FineId);



//$_SESSION['Documentation'] = $MainPathDoc . '/doc/national/violation/'. $_SESSION['cityid'] ."/". $FineId. '/' . $FileNameJPG;
    header("Location:panel.php");



}





