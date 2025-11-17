<?php
require("_path.php");
require(INC . "/parameter.php");
require(CLS . "/cls_db.php");
require(INC . "/function.php");
require(INC . "/header.php");

require(INC . '/menu_' . $_SESSION['UserMenuType'] . '.php');


require(CLS . "/cls_field_viw.php");


echo $str_out;
$rs_Province = $rs->Select(MAIN_DB . ".Province","1=1", "Title");


$str_Province = '<select class="form-control" name="ProvinceTitle" id="ProvinceTitle" style="width:15rem">';

$str_BornProvince = '<select class="form-control" name="BornProvinceTitle" id="BornProvinceTitle" style="width:15rem">';

$str_tmpProvince = '<option></option>';

while($r_Province = mysqli_fetch_array($rs_Province)) {
    $str_tmpProvince .= '<option value="'.$r_Province['Id'].'" short_title="'.$r_Province['ShortTitle'].'">'.$r_Province['Title'].'</option>';
}
$str_tmpProvince.='</select>';
$str_Province .= $str_tmpProvince;
$str_BornProvince .= $str_tmpProvince;



$Id = CheckValue('Id', 'n');
//mgmt_violation.php
$Search_ViolationArticle = CheckValue ('Search_ViolationArticle', 'n');

$str_GET_Parameter .= "&Search_ViolationArticle=$Search_ViolationArticle";


$rs_Fine = $rs->Select('V_Violation', "(TrespasserTypeId=1 OR TrespasserTypeId IS NULL) AND Id=$Id", "Id");


$n_TrespasserTypeId = 1;

$FindNumber = mysqli_num_rows($rs_Fine);
if ($FindNumber == 0) {
    $rs_Fine = $rs->Select('V_ViolationRent', "Id=$Id", "Id");


    $rs_TrespasserRent = $rs->Select('FineTrespasser', "TrespasserTypeId=10 AND FineId=" . $Id);
    $FindNumber = mysqli_num_rows($rs_TrespasserRent);
    if ($FindNumber == 0) {
        $TrespasserId = 0;
        $str_WhereTrespasser = "Id=10";
        $n_TrespasserTypeId = 10;

    } else {
        $str_WhereTrespasser = "Id=11";
        $n_TrespasserTypeId = 11;
        $r_TrespasserRent = mysqli_fetch_array($rs_TrespasserRent);
        $TrespasserId = $r_TrespasserRent['TrespasserId'];
    }
    $trespasser_rows = $rs->Select('V_Trespasser', "Id=" . $TrespasserId);
    $trespasser_row = mysqli_fetch_array($trespasser_rows);
    $strTrespasser = DivTrespasserView($trespasser_row, "Noleggio");


    $rs_TrespasserRent = $rs->Select('FineTrespasser', "TrespasserTypeId=11 AND FineId=" . $Id);
    $FindNumber = mysqli_num_rows($rs_TrespasserRent);
    if ($FindNumber == 0) {
        $TrespasserId = 0;

    } else {
        $r_TrespasserRent = mysqli_fetch_array($rs_TrespasserRent);
        $TrespasserId = $r_TrespasserRent['TrespasserId'];
    }
    $trespasser_rows = $rs->Select('V_Trespasser', "Id=" . $TrespasserId);
    $trespasser_row = mysqli_fetch_array($trespasser_rows);
    $strTrespasser .= DivTrespasserView($trespasser_row, "Locatario");
} else {
    $str_WhereTrespasser = "(Id=1 OR Id=10)";

}

$r_Fine = mysqli_fetch_array($rs_Fine);

if ($r_Fine['DetectorId'] == 0) $DetectorTitle = "";
else {
    $detectors = $rs->Select('Detector', "Id=" . $r_Fine['DetectorId']);
    $detector = mysqli_fetch_array($detectors);

    $DetectorTitle = $detector['Title' . LAN];
}


$str_ArticleDescription = $r_Fine['ArticleDescription' . LAN];

$str_ReasonDescription = $r_Fine['ReasonTitle' . LAN];

$article_rows = $rs->Select('V_Article', "Id=" . $r_Fine['ArticleId'] . " AND Year=" . $r_Fine['ProtocolYear']);
$article_row = mysqli_fetch_array($article_rows);

$rs_FineOwner = $rs->Select('FineOwner', "FineId=" . $Id);
$r_FineOwner = mysqli_fetch_array($rs_FineOwner);
if (strlen(trim($r_FineOwner['ArticleDescription' . LAN])) > 0) $str_ArticleDescription = $r_FineOwner['ArticleDescription' . LAN];
if (strlen(trim($r_FineOwner['ReasonDescription' . LAN])) > 0) $str_ReasonDescription = $r_FineOwner['ReasonDescription' . LAN];


$charge_rows = $rs->Select('CustomerCharge', "CreationType=1 AND CityId='" . $_SESSION['cityid'] . "' AND ToDate IS NULL", "Id");
$charge_row = mysqli_fetch_array($charge_rows);


$str_Charge = ($r_Fine['CountryId'] == 'Z000') ? "National" : "Foreign";

if ($charge_row[$str_Charge . 'TotalFee'] > 0) {
    $TotalCharge = $charge_row[$str_Charge . 'TotalFee'];
} else {

    $TotalCharge = $charge_row[$str_Charge . 'NotificationFee'] + $charge_row[$str_Charge . 'ResearchFee'];
}


$TotalFee = $TotalCharge + $r_Fine['Fee'];


$str_Locality = '    <div class="col-sm-1 BoxRowLabel">
                        Comune
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                    ' . $r_Fine['CityTitle'] . '
                    </div>

';

$str_Department = ($r_Fine['CountryId'] == "Z110") ? $r_Fine['DepartmentId'] : '';




$str_SpanTime = '
    <span class="tooltip-r" data-toggle="tooltip" data-placement="left" title="L\'ora solare vige dalla fine di Ottobre alla fine di Marzo.
        L\'ora legale vige dalla fine di Marzo alla fine di Ottobre ed è uguale all\'ora solare +1 ora. "><i class="glyphicon glyphicon-info-sign" style="margin-top:0.2rem;margin-left:1rem;font-size:1.8rem;"></i>
';





$obj_Field = new CLS_FIELD_VIW();

$obj_Field->str_TypeCol = 'col-sm-';
$obj_Field->str_TopMargin = 0.2;
$obj_Field->str_LabelCss = 'BoxRowLabel';
$obj_Field->str_CaptionCss = 'BoxRowCaption';



$obj_Field->OpenRow('row-fluid');

$obj_Field->OpenCol(6);

$obj_Field->OpenCol(12);
$obj_Field->WriteCol(12, 'BoxRowHTitle', 'DATI VIOLAZIONE');
$obj_Field->CloseCol();

$obj_Field->OpenCol(12);
$obj_Field->WriteColLbl(2, 'Riferimento');
$obj_Field->WriteColCtn(2, $r_Fine['Code']);
$obj_Field->WriteColLbl(2,  'Data');
$obj_Field->WriteColCtn(3,  DateOutDB($r_Fine['FineDate']));
$obj_Field->WriteColLbl(1,  'Ora');
$obj_Field->WriteColCtn(2,  TimeOutDB($r_Fine['FineTime']));
$obj_Field->CloseCol();

$obj_Field->OpenCol(12);
echo $str_Locality;
$obj_Field->WriteColLbl(1,  'Strada');
$obj_Field->WriteColCtn(2,  $r_Fine['StreetTypeTitle']);
$obj_Field->WriteColLbl(1,  'Località');
$obj_Field->WriteColCtn(4,  $r_Fine['Address']);
$obj_Field->CloseCol();

$obj_Field->OpenCol(12);
$obj_Field->WriteColLbl(2,  'Tipo veicolo');
$obj_Field->WriteColCtn(3,  $r_Fine['VehicleTitleIta']);
$obj_Field->WriteColLbl(2,  'Nazione');
$obj_Field->WriteColCtn(3,  $r_Fine['VehicleCountry']);
$obj_Field->WriteColLbl(1,  'Dip.');
$obj_Field->WriteColCtn(1,  $str_Department);
$obj_Field->CloseCol();


$obj_Field->OpenCol(12);
$obj_Field->WriteColLbl(3,  'Targa');
$obj_Field->WriteColCtn(3,  $r_Fine['VehiclePlate']);
$obj_Field->WriteColLbl(3,  'Massa');
$obj_Field->WriteColCtn(3,  $r_Fine['VehicleMass']);
$obj_Field->CloseCol();

$obj_Field->OpenCol(12);
$obj_Field->WriteColLbl(2,  'Colore');
$obj_Field->WriteColCtn(2,  $r_Fine['VehicleColor']);
$obj_Field->WriteColLbl(2,  'Marca');
$obj_Field->WriteColCtn(2,  $r_Fine['VehicleBrand']);
$obj_Field->WriteColLbl(2,  'Modello');
$obj_Field->WriteColCtn(2,  $r_Fine['VehicleModel']);
$obj_Field->CloseCol();


$obj_Field->OpenCol(12);
$obj_Field->WriteColLbl(2,  'Rilevatore');
$obj_Field->WriteColCtn(5,  $DetectorTitle);
$obj_Field->WriteColLbl(2,  'Ora'.$str_SpanTime);
$obj_Field->WriteColCtn(3,  $a_TimeTypeId[$r_Fine['TimeTypeId']]);
$obj_Field->CloseCol();


if ($r_Fine['Speed'] > 0) {
    $obj_Field->OpenCol(12);
    $obj_Field->WriteCol(12, 'BoxRowHTitle', 'VELOCITA');
    $obj_Field->CloseCol();

    $obj_Field->OpenCol(12);
    $obj_Field->WriteColLbl(2,  'Limite');
    $obj_Field->WriteColCtn(2,  round($r_Fine['SpeedLimit']));
    $obj_Field->WriteColLbl(2,  'Rilevata');
    $obj_Field->WriteColCtn(2,  round($r_Fine['SpeedControl']));
    $obj_Field->WriteColLbl(2,  'Effettiva');
    $obj_Field->WriteColCtn(2,  round($r_Fine['Speed']));
    $obj_Field->CloseCol();
} else if ($r_Fine['TimeTLightFirst'] > 0) {
    $obj_Field->OpenCol(12);
    $obj_Field->WriteCol(12, 'BoxRowHTitle', 'SEMAFORO');
    $obj_Field->CloseCol();

    $obj_Field->OpenCol(12);
    $obj_Field->WriteColLbl(4,  'Primo fotogramma');
    $obj_Field->WriteColCtn(2,  $r_Fine['TimeTLightFirst']);
    $obj_Field->WriteColLbl(4,  'Secondo fotogramma');
    $obj_Field->WriteColCtn(2,  $r_Fine['TimeTLightSecond']);
    $obj_Field->CloseCol();

}

$obj_Field->OpenCol(12);
$obj_Field->WriteCol(12, 'BoxRowHTitle', 'DATI ARTICOLO');
$obj_Field->CloseCol();

$obj_Field->OpenCol(12);
$obj_Field->WriteCol(1, 'BoxRowCaption', 'Articolo', 'style="height:6rem;"');
$obj_Field->WriteCol(2, 'BoxRowLabel', $r_Fine['Article'] .' '. $r_Fine['Paragraph'] .' '.$r_Fine['Letter'], 'style="height:6rem;"');
$obj_Field->WriteCol(9, 'BoxRowLabel', StringOutDB($str_ArticleDescription),'style="height:6rem;"');
$obj_Field->CloseCol();

$obj_Field->OpenCol(12);
$obj_Field->WriteColLbl(3,'Mancata contestazione');
$obj_Field->WriteColCtn(9, StringOutDB($str_ReasonDescription));
$obj_Field->CloseCol();

$obj_Field->OpenCol(12);
$obj_Field->WriteColLbl(3,  'Tipo infrazione');
$obj_Field->WriteColCtn(9,  StringOutDB($r_Fine['ViolationTitle']));
$obj_Field->CloseCol();



$obj_Field->OpenCol(12);
$obj_Field->WriteColLbl(2,  'Min/Max edittale');
$obj_Field->WriteColCtn(2,  NumberDisplay($article_row['Fee']) . ' / ' . NumberDisplay($article_row['MaxFee']));
$obj_Field->WriteColLbl(2,  'Spese notifica');
$obj_Field->WriteColCtn(2,  NumberDisplay($TotalCharge));
$obj_Field->WriteColLbl(2,  'Importo totale');
$obj_Field->WriteColCtn(2,  NumberDisplay($TotalFee));
$obj_Field->CloseCol();

$obj_Field->OpenCol(12);
$obj_Field->WriteColLbl(2,  'Accertatore');
$obj_Field->WriteColCtn(4,  substr($r_Fine['ControllerCode'] . ' - ' . StringOutDB($r_Fine['ControllerName']),0,35));
if ($r_Fine['ControllerDate'] != "") {
    $obj_Field->WriteColLbl(2,  'Data/ora accertamento');
    $obj_Field->WriteColCtn(4,  DateOutDB($r_Fine['ControllerDate']) . ' ' . $r_Fine['ControllerTime']);
} else {
    $obj_Field->WriteColCtn(6,  '');
}
$obj_Field->CloseCol();


$obj_Field->OpenCol(12);
$obj_Field->WriteCol(12, 'BoxRowHTitle', 'LAVORAZIONE');
$obj_Field->CloseCol();

$obj_Field->OpenCol(12);
$obj_Field->WriteColLbl(3,'Stato pratica');
$obj_Field->WriteColCtn(9, $r_Fine['StatusTitle']);
$obj_Field->CloseCol();

$obj_Field->OpenCol(12);
$obj_Field->WriteCol(3, 'BoxRowLabel', 'Note operatore', 'style="height:4rem;"');
$obj_Field->WriteCol(9, 'BoxRowCaption', $r_Fine['Note'],'style="height:4rem;"');
$obj_Field->CloseCol();





echo '
    <form name="f_violation" id="f_violation" method="post" action="mgmt_violation_trespasser_exe.php' . $str_GET_Parameter . '">
    <input type="hidden" id="FineId" name="FineId" value="' . $Id . '">
    <input type="hidden" id="TrespasserId" name="TrespasserId" value="">
    <input type="hidden" name="P" value="' . $str_BackPage . '">
    <input type="hidden" name="Genre" id="Genre" value="M">
    <input type="hidden" id="VehiclePlate" name="VehiclePlate" value="' . $r_Fine['VehiclePlate'] . '">
    <input type="hidden" id="CountryId_S" name="CountryId_S" value="' . $r_Fine['CountryId'] . '">
';


$obj_Field->OpenCol(12);
$obj_Field->WriteCol(12, 'BoxRowHTitle', 'ASSEGNAZIONE TRASGRESSORE: <span id="span_name"></span>');
$obj_Field->CloseCol();

$obj_Field->OpenCol(12);
$obj_Field->WriteColLbl(3,'Tipologia');
$obj_Field->WriteColCtn(9, CreateSelect("TrespasserType", $str_WhereTrespasser, "Id", "TrespasserTypeId", "Id", "Title", $n_TrespasserTypeId, true));
$obj_Field->CloseCol();

$obj_Field->OpenCol(12);
$obj_Field->WriteCol(3, 'BoxRowLabel', 'Note', 'style="height:4rem;"');
$obj_Field->WriteCol(9, 'BoxRowCaption', '<textarea class="form-control frm_field_string" name="Note" style="width:45rem;height:3.5rem"></textarea>','style="height:4rem;"');
$obj_Field->CloseCol();

$obj_Field->OpenCol(12);
$obj_Field->WriteColLbl(3,  'Costi aggiuntivi');
$obj_Field->WriteColCtn(3,  '<input type="text" class="form-control frm_field_numeric" name="OwnerAdditionalFee" value="0" style="width:10rem;">');
$obj_Field->WriteColLbl(3,  'Data comunicazione');
$obj_Field->WriteColCtn(3,  '<input class="form-control frm_field_date" type="text" name="ReceiveDate" style="width:15rem;">');
$obj_Field->CloseCol();


$str_Tab ='

    <ul class="nav nav-tabs" id="mioTab">
        <li class="active" id="tab_Trespasser_src"><a href="#Trespasser_src" data-toggle="tab">Persona fisica</a></li>
        <li id="tab_Company_src"><a href="#Company_src" data-toggle="tab">Ditta</a></li>
    </ul>
        
    <span class="glyphicon glyphicon-plus-sign add_button" style="color:#294A9C;position:absolute; right:10px;top:10px;font-size:25px; "></span>        
               
';


$obj_Field->OpenCol(12);
$obj_Field->WriteCol(12, 'BoxRowHTitle', $str_Tab, 'style="height:4rem;"');
$obj_Field->CloseCol();


$obj_Field->OpenCol(12);
echo '
<div class="tab-content">
    <div class="tab-pane" id="Company_src">
        <div class="col-sm-12">
            <div class="col-sm-3 BoxRowLabel">
                Ragione sociale
            </div>
            <div class="col-sm-9 BoxRowCaption">
                <input name="CompanyName_S" id="CompanyName_S" type="text" style="width:20rem">
            </div>
        </div>
    </div>
    <div class="tab-pane active" id="Trespasser_src">
        <div class="col-sm-12">
            <div class="col-sm-2 BoxRowLabel">
                Cognome
            </div>
            <div class="col-sm-4 BoxRowCaption">
                <input type="text" name="Surname_S" id="Surname_S" style="width:12rem">
            </div>
        
            <div class="col-sm-2 BoxRowLabel">
                Nome
            </div>
            <div class="col-sm-4 BoxRowCaption">
                <input type="text" name="Name_S" id="Name_S" style="width:12rem">
            </div>
            
        </div>
    </div> 
</div>

';
$obj_Field->CloseCol();

$obj_Field->OpenCol(12);
$obj_Field->WriteCol(12, 'BoxRowLabel', '', 'style="height:150px; overflow:auto; display:none;"', 'id="trespasser_content"');
$obj_Field->CloseCol();

$obj_Field->OpenCol(12);
$obj_Field->WriteCol(12, 'BoxRowLabel', '', 'style="margin-top:2rem; height:100px; overflow:auto; display:none;"', 'id="plate_content"');
$obj_Field->CloseCol();



$obj_Field->CloseCol();

$obj_Field->OpenCol(6);

$obj_Field->OpenCol(12);
$obj_Field->WriteCol(12, 'BoxRowHTitle', 'DOCUMENTAZIONE');
$obj_Field->CloseCol();

$obj_Field->OpenCol(12);
$obj_Field->WriteCol(12, 'BoxRow', '', 'style="width:100%;height:10rem;"', 'id="fileTreeDemo_1"');
$obj_Field->CloseCol();


$str_ImgWrapper ='
    <div class="imgWrapper" id="preview_img" style="display: none; height:60rem;overflow:auto; display: none;">
        <img id="preview" class="iZoom"  />
    </div>
    <div id="preview_doc" style="height:60rem;overflow:auto; display: none;"></div>      

';



$obj_Field->OpenCol(12);
$obj_Field->WriteCol(12, 'BoxRow', $str_ImgWrapper, 'style="width:100%;height:60.2rem;"');
$obj_Field->CloseCol();

$obj_Field->CloseCol();

$obj_Field->CloseCol();

$str_Button ='
    <input type="submit" value="Salva" class="btn btn-primary" />
    <input type="reset" value="Indietro" id="back" class="btn btn-primary" />
';

$obj_Field->OpenCol(12);
$obj_Field->WriteCol(12, 'BoxRowButton', $str_Button, 'style="text-align:center; height:6rem;"');
$obj_Field->CloseCol();

echo '</form>';


$obj_Field->CloseRow();


$str_out = '

<div class="overlay" id="overlay" style="display:none;"></div>

<div id="Div_Windows_Insert_Trespasser" style="height:60rem">
    <form name="f_ins_trespasser" id="f_ins_trespasser" class="form-horizontal" action="mgmt_trespasser_add_exe.php" method="post">
        <input type="hidden" name="Genre" id="Genre" value="M">
        <input type="hidden" name="Province" id="Province">
        <input type="hidden" name="City" id="City">
        <input type="hidden" name="BornProvince" id="BornProvince">
        <input type="hidden" name="BornCity" id="BornCity">

        <input type="hidden" name="VehiclePlate" value="' . $r_Fine['VehiclePlate'] . '">            
        <input type="hidden" id="FineId" name="FineId" value="' . $Id . '">
        <input type="hidden" name="P" value="' . $str_BackPage . '">


        <div class="col-sm-12 BoxRowTitle" style="text-align:center">
            Inserimento anagrafica
            <span class="fa fa-times-circle close_window_trespasser" style="color:#fff;position:absolute; right:2px;top:2px;font-size:20px; "></span>
        </div>

        <div class="clean_row HSpace4"></div>

        <div class="col-sm-12">
            <div class="col-sm-3 BoxRowLabel">
                Tipologia
            </div>
            <div class="col-sm-9 BoxRowCaption">
                <select class="form-control" name="TrespasserTypeId" id="TrespasserTypeId" ><option value="1" SELECTED >Proprietario</option><option value="10">Noleggio/Leasing</option></select>		
            </div>
        </div>
        <div class="clean_row HSpace4"></div>      
        <div class="col-sm-12">
            <div class="col-sm-3 BoxRowLabel">
                Note
            </div>
            <div class="col-sm-9 BoxRowCaption">
                <input type="text" class="form-control frm_field_string" name="Note" />
            </div>
        </div>   
        <div class="clean_row HSpace4"></div>    
        <div class="col-sm-12"">
            <div class="col-sm-3 BoxRowLabel">
                Costi aggiuntivi
            </div>
            <div class="col-sm-3 BoxRowCaption">
                <input type="text" class="form-control frm_field_numeric" name="AdditionalFee" value="0" style="width:10rem;">	
            </div>
            <div class="col-sm-3 BoxRowLabel">
                Data comunicazione
            </div>
            <div class="col-sm-3 BoxRowCaption">
                <input type="text" class="form-control frm_field_date" name="ReceiveDate" style="width:12rem;">	
            </div>
        </div>                      
        <div class="clean_row HSpace4"></div>   


        <div class="col-sm-12">
            <ul class="nav nav-tabs" id="mioTab">
                <li class="active" id="tab_Trespasser"><a href="#Trespasser" data-toggle="tab">Persona fisica</a></li>
                <li id="tab_Company"><a href="#Company" data-toggle="tab">Ditta</a></li>
            </ul>
        </div>

        <div class="clean_row HSpace4"></div>

        <div class="tab-content"><!-- open div tab-content -->
            <div class="tab-pane" id="Company"><!-- open div tab-pane Company -->
                <div class="col-sm-12">
                    <div class="col-sm-3 BoxRowLabel">
                        Ragione sociale
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input class="form-control frm_field_string frm_field_required" name="CompanyName" id="CompanyName" type="text">
                    </div>
                    <div class="col-sm-3 BoxRowLabel">
                        Forma Giuridica
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <select class="form-control" name="LegalFormId">
                                <option></option>';
                                $getLegalform = $rs->SelectQuery("SELECT * FROM LegalForm");
                                $type = null;

                                while($row = mysqli_fetch_array($getLegalform)){
                                    if ($row['Type'] != $type) {
                                        if ($type !== null) {
                                            echo '</optgroup>';
                                        }
                                        $type = $row['Type'];
                                        $str_out.='<optgroup label="' . htmlspecialchars($type) . '">';
                                    }

                                    $str_out.='<option value="'.$row['Id'].'">'.$row['Description'].'</option>';
                                }

    $str_out.='</select>
                    </div>
                </div>
            </div> <!-- open div tab-pane Company -->

            <div class="tab-pane active" id="Trespasser"><!-- open div tab-pane Trespasser -->
                <div class="col-sm-12">
                    <div class="col-sm-1 BoxRowLabel">
                        Sesso
                    </div>
                    <div class="col-sm-1 BoxRowCaption">
                        <input type="radio" value="M" name="Sex" id="sexM" CHECKED>M &nbsp;
                        <input type="radio" value="F" name="Sex" id="sexF">F
                    </div>
                    <div class="col-sm-2 BoxRowLabel">
                        Cognome
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input class="form-control frm_field_string frm_field_required" type="text" id="Surname" name="Surname">
                    </div>
                    <div class="col-sm-2 BoxRowLabel">
                        Nome
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input class="form-control frm_field_string frm_field_required" type="text" id="Name" name="Name">
                    </div>
                </div>
                
                <div class="clean_row HSpace4"></div>

                <div class="col-sm-12">
                    <div class="col-sm-2 BoxRowHTitle">
                        DATI NASCITA
                    </div>
                    <div class="col-sm-1 BoxRowLabel">
                        Nazione
                    </div>
                    <div class="col-sm-4 BoxRowCaption">
                        ' . CreateSelect(MAIN_DB . ".Country", "1=1", "Title", "BornCountry", "Id", "Title", "Z000", false, 15) . '
                    </div>
                    <div class="col-sm-1 BoxRowLabel">
                        Provincia
                    </div>
                    <div class="col-sm-4 BoxRowCaption">
                        ' . $str_BornProvince . '                    
                    </div>      
                </div>   

                <div class="clean_row HSpace4"></div>

                <div class="col-sm-12">
                    <div class="col-sm-2 BoxRowHTitle"></div>
                    <div class="col-sm-1 BoxRowLabel">
                        Città
                    </div>
                    <div class="col-sm-5 BoxRowCaption">
                        <input id="BornCityInput" class="form-control frm_field_string" type="text" name="BornCityInput" style="display:none; width:12rem">
                        <select id="BornCitySelect" class="form-control" name="BornCitySelect">
                             <option></option>
                        </select>
                    </div> 
                    <div class="col-sm-2 BoxRowLabel">
                        Data Nascita
                    </div>
                    <div class="col-sm-2 BoxRowCaption">
                        <input class="form-control frm_field_date" type="text" name="BornDate" id="BornDate" style="width:12rem">
                    </div>     
                </div>   

                <div class="clean_row HSpace4"></div>

                <div class="col-sm-12">
                    <div class="col-sm-2 BoxRowHTitle">
                        DATI PATENTE
                    </div>
                    <div class="col-sm-2 BoxRowLabel">
                        Categoria
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input class="form-control frm_field_string" type="text" name="LicenseCategory"  style="width:4rem">
                    </div>
                    <div class="col-sm-2 BoxRowLabel">
                        Numero
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input class="form-control frm_field_string" type="text" name="LicenseNumber" style="width:12rem">
                    </div>                   
                </div>
                
                <div class="clean_row HSpace4"></div>
                
                <div class="col-sm-12">
                    <div class="col-sm-2 BoxRowHTitle"></div>                   
                    <div class="col-sm-2 BoxRowLabel">
                        Data rilascio
                    </div>
                    <div class="col-sm-2 BoxRowCaption">
                        <input class="form-control frm_field_date" type="text" name="LicenseDate"  style="width:12rem">
                    </div>
                    <div class="col-sm-2 BoxRowLabel">
                        Ente rilascio
                    </div>
                    <div class="col-sm-4 BoxRowCaption">
                        <input class="form-control frm_field_string" type="text" name="LicenseOffice" style="width:12rem">
                    </div>
                </div>
            </div><!-- close div tab-pane Trespasser -->
        </div><!-- close div tab-content -->
        
        <div class="clean_row HSpace4"></div>
        
        <div class="col-sm-12">
            <div class="col-sm-2 BoxRowHTitle">
                DATI RESIDENZA
            </div>
            <div class="col-sm-1 BoxRowLabel">
                Nazione
            </div>
            <div class="col-sm-3 BoxRowCaption">
                ' . CreateSelect(MAIN_DB . ".Country", "1=1", "Title", "CountryId", "Id", "Title", "Z000", true, 15) . '
            </div>
            <div class="col-sm-1 BoxRowLabel">
                Indirizzo
            </div>
            <div class="col-sm-5 BoxRowCaption">
                <input class="form-control frm_field_string" type="text" id="Address" name="Address" style="width:30rem">
            </div>            
        </div>  
        <div class="clean_row HSpace4"></div>
                   
        <div class="col-sm-12">
            <div class="col-sm-2 BoxRowHTitle"></div>      
            <div class="col-sm-1 BoxRowLabel">
                Provincia
            </div>
            <div class="col-sm-2 BoxRowCaption">
                ' . $str_Province . '
            </div>                 
            <div class="col-sm-1 BoxRowLabel">
                Città
            </div>
            <div class="col-sm-3 BoxRowCaption">
                <input id="CityInput" class="form-control frm_field_string" type="text" name="CityInput" style="display:none; width:20rem">
                <select id="CitySelect" class="form-control" name="CitySelect">
                     <option></option>
                </select>
            </div>            
            <div class="col-sm-1 BoxRowLabel">
                Cap
            </div>
            <div class="col-sm-2 BoxRowCaption">
                <input class="form-control frm_field_string" type="text" name="ZIP" id="ZIP" style="width:8rem">
            </div>           
        </div>
            
        <div class="clean_row HSpace4"></div>
                   
        <div class="col-sm-12">
            <div class="col-sm-1 BoxRowLabel">
                C.F./P.IVA
            </div>
            <div class="col-sm-5 BoxRowCaption">
                <input class="form-control frm_field_string" type="text" name="TaxCode" id="TaxCode" style="width:18rem">
            </div>
            <div class="col-sm-1 BoxRowLabel">
                PEC
            </div>
            <div class="col-sm-5 BoxRowCaption">
                <input class="form-control frm_field_string" type="text" name="PEC" id="PEC" style="width:18rem">
            </div>
        </div>
        
        <div class="clean_row HSpace4"></div>
        
        <div class="col-sm-12">
            <div class="col-sm-2 BoxRowLabel">
                Mail
            </div>
            <div class="col-sm-4 BoxRowCaption">
                <input class="form-control frm_field_string" type="text" name="Mail">
            </div>
            <div class="col-sm-2 BoxRowLabel">
                Telefono
            </div>
            <div class="col-sm-4 BoxRowCaption">
                <input class="form-control frm_field_string" type="text" name="Phone">
            </div>
        </div>
      
        <div class="clean_row HSpace4"></div>      
        <div id="plate_content_add" class="col-sm-12" style="margin-top:2rem;height:100px;overflow:auto"></div>
      	
        <div class="col-sm-12">
            <div class="col-sm-12 BoxRowButton">
                <input type="submit" id="btn_Save" value="Salva" class="btn btn-primary" />
            </div>
        </div>  
    </form>
</div>
';
echo $str_out;

?>
    <script type="text/javascript">

        $('document').ready(function () {
            $('#tab_Company_src').click(function () {
                $('#f_violation').find('#Genre').val('D');
                $('#f_violation').find('#Surname').val('');
                $('#f_violation').find('#Name').val('');

            });

            $('#tab_Trespasser_src').click(function () {
                $('#f_violation').find('#Genre').val('M');
                $('#f_violation').find('#CompanyName').val('');
            });


            var min_length = 3;
            $('#CompanyName_S, #Name_S, #Surname_S' ).keyup(function () {

                var CompanyName = $('#CompanyName_S').val();
                var Name = $('#Name_S').val()
                var Surname = $('#Surname_S').val()
                var CountryId = $('#CountryId_S').val()

                var Genre = $('#f_violation').find('#Genre').val();
                var VehiclePlate = $('#VehiclePlate').val();
                var Id = $('#f_violation').find('#FineId').val();


                if (CompanyName.length >= min_length || Surname.length >= min_length) {
                    $.ajax({
                        url: 'ajax/search_trespasser.php',
                        type: 'POST',
                        dataType: 'json',
                        cache: false,
                        data: {Name:Name, Surname:Surname, CompanyName: CompanyName, Genre: Genre, VehiclePlate: VehiclePlate, Id: Id, CountryId:CountryId},
                        success: function (data) {

                            $('#trespasser_content').show();
                            $('#trespasser_content').html(data.Trespasser);

                            $('#plate_content').show();
                            $('#plate_content').html(data.Plate);
                        }
                    });
                } else {
                    $('#trespasser_content').hide();
                    $('#plate_content').hide();
                }
            });


            $(".add_button").click(function () {
                var VehiclePlate = $('#VehiclePlate').val();
                var Id = $('#f_violation').find('#FineId').val();
                var AddTrespasser = 1;

                $.ajax({
                    url: 'ajax/search_trespasser.php',
                    type: 'POST',
                    dataType: 'json',
                    cache: false,
                    data: {VehiclePlate: VehiclePlate, Id: Id, AddTrespasser: AddTrespasser},
                    success: function (data) {

                        $('#plate_content_add').html(data.Plate);
                    }
                });

                $('#overlay').fadeIn('fast');
                $('#Div_Windows_Insert_Trespasser').fadeIn('slow');


            });



            $('#back').click(function () {
                window.location = "<?= $str_BackPage . $str_GET_Parameter ?>"
                return false;
            });


            $(function () {
                $("#Div_Windows_Insert_Trespasser").draggable();
            });

            $(".close_window_trespasser").click(function () {
                $('#overlay').fadeOut('fast');
                $('#Div_Windows_Insert_Trespasser').hide();
            });

            $(".add_button").click(function () {
                $('#overlay').fadeIn('fast');
                $('#Div_Windows_Insert_Trespasser').fadeIn('slow');
            });

            $("#overlay").click(function () {
                $(this).fadeOut('fast');
                $('#Div_Windows_Insert_Trespasser').hide();

            });


            $('#tab_Company').click(function () {
                $('#f_ins_trespasser').find('#Genre').val('D');
                $('#f_ins_trespasser').find("#sexM").prop("checked", true);
                $('#f_ins_trespasser').find("#sexF").prop("checked", false);

                $('#f_ins_trespasser').find('#TaxCode').val('');



            });

            $('#tab_Trespasser').click(function () {
                $('#f_ins_trespasser').find('#Genre').val('M');

                $('#f_ins_trespasser').find('#TaxCode').val('');
                $('#f_ins_trespasser').find('#CompanyName').val('');


            });

            $('#sexM').click(function () {
                $('#f_ins_trespasser').find('#Genre').val('M');
                $('#f_ins_trespasser').find("#sexF").prop("checked", false);

            });
            $('#sexF').click(function () {
                $('#f_ins_trespasser').find('#Genre').val('F');
                $('#f_ins_trespasser').find("#sexM").prop("checked", false);

            });


            $("#BornCountry, #CountryId").change(function () {

                var str_FieldProvince = ($(this).attr("id")=="BornCountry") ? "BornProvinceTitle" : "ProvinceTitle";
                var str_FieldCityS = ($(this).attr("id")=="BornCountry") ? "BornCitySelect" : "CitySelect";
                var str_FieldCityI = ($(this).attr("id")=="BornCountry") ? "BornCityInput" : "CityInput";

                var Country = $(this).val();


                $("#ZIP").val('');

                if (Country == "Z000") {
                    $("#"+str_FieldProvince).show().children('option:not(:first)').remove();
                    $("#"+str_FieldCityS).show().children('option:not(:first)').remove();
                    $("#"+str_FieldCityI).hide();


                    $.ajax({
                        url: 'ajax/ajx_src_prov_city.php',
                        type: 'POST',
                        dataType: 'json',
                        cache: false,
                        data: {Country: Country},
                        success: function (data) {

                            $("#"+str_FieldProvince).children('option:not(:first)').remove();


                            $.each(data.selectValues, function (key, value) {
                                $("#"+str_FieldProvince)
                                    .append($("<option></option>")
                                        .attr({
                                            'value': key,
                                        })
                                        .text(value['Title']));
                            });

                        }
                    });
                } else {
                    $("#"+str_FieldProvince).hide().children('option:not(:first)').remove();
                    $("#"+str_FieldCityS).hide().children('option:not(:first)').remove();
                    $("#"+str_FieldCityI).show();
                }


            });


            $("#BornProvinceTitle, #ProvinceTitle").change(function () {
                var str_FieldCityS = ($(this).attr("id")=="BornProvinceTitle") ? "BornCitySelect" : "CitySelect";



                if(str_FieldCityS=="CitySelect"){
                    $('#Province').val($('option:selected', this).attr('short_title'));
                    $("#ZIP").val('');
                }else{
                    $('#BornProvince').val($('option:selected', this).attr('short_title'));
                }
                var Province = $(this).val();


                $.ajax({
                    url: 'ajax/ajx_src_prov_city.php',
                    type: 'POST',
                    dataType: 'json',
                    cache: false,
                    data: {Province: Province},
                    success: function (data) {

                        $("#"+str_FieldCityS).children('option:not(:first)').remove();


                        $.each(data.selectValues, function (key, value) {
                            $('#'+str_FieldCityS)
                                .append($("<option></option>")
                                    .attr({
                                        'value': key,
                                    })
                                    .text(value['Title']));
                        });

                    }
                });

            });






            $("#CityInput, #BornCitySelect, #CitySelect, #Address").change(function () {

                var str_FieldNaneId = $(this).attr("id");

                if(str_FieldNaneId=="BornCitySelect") $('#BornCity').val($("#"+str_FieldNaneId+" option:selected" ).text());
                else{
                    var CityId="";
                    if(str_FieldNaneId=="CitySelect" || str_FieldNaneId=="Address"){
                        $('#City').val($("#"+str_FieldNaneId+" option:selected" ).text());
                        CityId =  $('#CitySelect').val();
                    } else if(str_FieldNaneId=="CityInput" || str_FieldNaneId=="Address") {
                        CityId =  $('#CityInput').val();
                    }

                    var Address = $('#Address').val();
                    var CountryId = $('#CountryId').val();

                    if(CityId!=""){

                        $.ajax({
                            url: 'ajax/ajx_src_zip.php',
                            type: 'POST',
                            dataType: 'json',
                            cache: false,
                            data: {CountryId:CountryId, CityId: CityId, Address:Address},
                            success: function (data) {

                                $("#ZIP").removeClass('txt-success txt-warning txt-danger').addClass(data.CSS);
                                $("#ZIP").val(data.ZIP);
                            }
                        });

                    }else $("#ZIP").val('');
                }




            });


            $("#BornCountry, #BornProvinceTitle, #Surname, #Name, #sexM, #sexF, #BornDate, #BornCitySelect, #CountryId").change(function(){

                if ($('#CountryId').val() == 'Z000') {
                    var Surname = $('#Surname').val();
                    var Name = $('#Name').val();
                    var Sex = $('#sexM').prop('checked') ? 'M' : 'F';

                    var BornDate = $('#BornDate').val();
                    var BornCitySelect = ($('#BornCountry').val() == 'Z000') ? $('#BornCitySelect').val() : $('#BornCountry').val();


                    if (Surname && Name && Sex && BornDate && BornCitySelect) {
                        var TaxCode = compute_CF(Surname, Name, Sex, BornDate, BornCitySelect);





                        if (TaxCode.length == 16){
                            $('#TaxCode').removeClass('txt-success txt-danger').addClass('txt-success');
                            $('#TaxCode').val(TaxCode);

                        }else{
                            $('#TaxCode').removeClass('txt-success txt-danger').addClass('txt-danger');
                        }

                    } else {
                        $('#TaxCode').val("")
                    }

                } else {
                    $('#TaxCode').val("")
                }
            });


            $('#f_ins_trespasser').on('keyup keypress', function(e) {
                var keyCode = e.keyCode || e.which;
                if (keyCode === 13) {
                    e.preventDefault();
                    return false;
                }
            });

            $('#btn_Save').click(function () {
                $(this).hide();
            });


            $('#f_ins_trespasser').bootstrapValidator({
                live: 'disabled',
                fields: {
                    frm_field_required: {
                        selector: '.frm_field_required',
                        validators: {
                            notEmpty: {
                                message: 'Richiesto'
                            }
                        }
                    },

                    frm_field_numeric: {
                        selector: '.frm_field_numeric',
                        validators: {
                            numeric: {
                                message: 'Numero'
                            }
                        }
                    },

                    frm_field_date:{
                        selector: '.frm_field_date',
                        validators: {
                            date: {
                                format: 'DD/MM/YYYY',
                                message: 'Data non valida'
                            }

                        }

                    },

                }
            });


        });
    </script>
<?php
include(INC . "/footer.php");
