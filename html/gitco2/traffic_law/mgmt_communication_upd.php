<?php
include ("_path.php");
include (INC . "/parameter.php");
include (CLS . "/cls_db.php");
include (INC . "/function.php");
require (INC . "/initialization.php");
include (INC . "/header.php");
require (INC . '/menu_' . $_SESSION['UserMenuType'] . '.php');

$FineId = CheckValue('Id', 'n');

$NotificationType = array(
    1 => 'Su strada',
    2 => 'Messo',
    3 => 'Ufficio'
);

$a_Genre = array(
    'M' => array('Icon' => 'fa-user', 'Tooltip' => 'Persona fisica'),
    'F' => array('Icon' => 'fa-user', 'Tooltip' => 'Persona fisica'),
    'D' => array('Icon' => 'fa-building', 'Tooltip' => 'Ditta'),
    'DI' => array('Icon' => 'fa-user-tie', 'Tooltip' => 'Ditta individuale'),
);

$a_LegalFormIndividual = unserialize(LEGALFORM_INDIVIDUALCOMPANY);
$a_IncompleteCommunication = unserialize(INCOMPLETE_COMMUNICATION);
$a_AllowedExtensions = unserialize(GENERIC_DOCUMENT_EXT);
$n_MaxFileSize = ART126_DOCUMENT_MAX_FILE_SIZE;

$rs_Trespasser = $rs->Select('V_TrespasserCommunication', "FineId=" . $FineId);
$r_Trespasser = mysqli_fetch_array($rs_Trespasser);

$Genre = in_array($r_Trespasser['LegalFormId'], $a_LegalFormIndividual) ? 'DI' : $r_Trespasser['Genre'];
$GenreDriver = in_array($r_Trespasser['LegalFormIdDriver'], $a_LegalFormIndividual) ? 'DI' : $r_Trespasser['GenreDriver'];

$rs_FineNotification = $rs->SelectQuery("SELECT * FROM FineNotification FN JOIN Result R ON FN.ResultId = R.Id WHERE FineId=$FineId");
$r_FineNotification = mysqli_fetch_array($rs_FineNotification);

$LicenseDate = "";
if ($r_Trespasser['LicenseDate'] != "" or ! is_null($r_Trespasser['LicenseDate']))
  $LicenseDate = DateOutDB($r_Trespasser['LicenseDate']);

$LicenseDateDriver = "";
if ($r_Trespasser['LicenseDateDriver'] != "" or ! is_null($r_Trespasser['LicenseDateDriver']))
  $LicenseDateDriver = DateOutDB($r_Trespasser['LicenseDateDriver']);

$CommunicationDate = "";
if ($r_Trespasser['CommunicationDate'] != "" or ! is_null($r_Trespasser['CommunicationDate']))
  $CommunicationDate = DateOutDB($r_Trespasser['CommunicationDate']);

$documentType = "";
$rs_TrespasserDocType = $rs->Select('TrespasserDocumentType', 'Id='.$r_Trespasser['DocumentTypeId']);
if(mysqli_num_rows($rs_TrespasserDocType) > 0){
  $documentType = mysqli_fetch_assoc($rs_TrespasserDocType)['Title'];
}


$ownerType = '';
switch ($r_Trespasser['OwnerTypeId'])
  {
  case 1:
    $ownerType = "Proprietario/Trasgressore";
    $checkedDriver = "CHECKED";
    $checkedNoDriver = "";
    $Incomplete = $r_Trespasser['Incomplete'] ?? 0;
    break;
  case 2:
    $ownerType = "Obbligato";
    $checkedDriver = "";
    $checkedNoDriver = "CHECKED";
    $Incomplete = $r_Trespasser['DriverIncomplete'] ?? 0;
    break;
  }

$str_DocumentFolder = ($r_Trespasser['CountryId'] == 'Z000') ? NATIONAL_FINE_HTML . "/" . $_SESSION['cityid'] . "/" . $FineId . "/" : FOREIGN_FINE_HTML . "/" . $_SESSION['cityid'] . "/" . $FineId . "/";

$b_hasDoc = false;
$str_CommunicationFile = '';

$rs_Documentation = $rs->Select('FineDocumentation', "FineId=" . $FineId." AND DocumentationTypeId=20", "DocumentationTypeId");
if (mysqli_num_rows($rs_Documentation) > 0){
    $r_Documentation = mysqli_fetch_array($rs_Documentation);
    $b_hasDoc = true;
}

$str_CommunicationFile .= '
    <div class="col-sm-4 BoxRowLabel">
        Comunicazione
    </div>
    <div class="col-sm-7 table_caption_H">
        <form name="f_Upl20" id="f_Upl20" enctype="multipart/form-data" action="#" method="post">
            <input type="hidden" name="Id" value="'. $FineId .'">
            <input type="hidden" name="CountryId" value="'. $r_Trespasser['VehicleCountryId'] . '">
            <input type="hidden" name="DocumentationTypeId" value="20">
            <input type="hidden" name="DocumentationId" value="'.($b_hasDoc ? $r_Documentation['Id'] : '').'" id="DocumentationId">
            <input class="'.($b_hasDoc ? 'hidden' : '').'" type="file" id="upl_file" name="upl_file">
        </form>
        <a id="DocName" href="javascript:void(0)" file="'.($b_hasDoc ?  $r_Documentation['Documentation'] : '').'">'.($b_hasDoc ? $r_Documentation['Documentation'] : '').'</a>
    </div>
    <div class="col-sm-1 BoxRowCaption">
        <button id="upload_button" data-toggle="tooltip" data-container="body" data-placement="left" title="Carica" data-btnaction="upl" class="tooltip-r btn btn-success'.($b_hasDoc ? ' hidden' : '').'" style="width: 100%;height: 100%;padding: 0;"><i class="fa fa-plus"></i></button>
        <button id="delete_button" data-toggle="tooltip" data-container="body" data-placement="left" title="Elimina" data-btnaction="del" class="tooltip-r btn btn-danger'.(!$b_hasDoc ? ' hidden' : '').'" style="width: 100%;height: 100%;padding: 0;"><i class="fa fa-times"></i></button>
    </div>
';

$str_out .= '
    	<div class="col-sm-6">
        	<div class="col-sm-12">
        	<form name="f_comm_upd" id="f_comm_upd" action="mgmt_communication_upd_exe.php" method="post">
                <input type="hidden" id="TrespasserId" name="TrespasserId" value="' . $r_Trespasser['TrespasserIdDriver'] . '">
                <input type="hidden" name="MainTrespasserId" value="' . $r_Trespasser['TrespasserId'] . '">
                <input type="hidden" name="FineId" value="' . $FineId . '">
                <input type="hidden" Id="FineDate" value="' . DateOutDB($r_Trespasser['FineDate']) . '">
                <input type="hidden" name="FineCommunicationId" value="' . $FineId . '">
                <input type="hidden" name="Filters" value="' . $str_GET_Parameter . '">
        	    <div class="col-sm-12">
                    <div class="col-sm-12 BoxRow text-center" style="margin:0;">
                        CRON: '.$r_Trespasser['ProtocolId'].'/'.$r_Trespasser['ProtocolYear'].'
                    </div>

                    <div class="clean_row HSpace4"></div>

                    <div class="col-sm-3 BoxRowLabel">
                        Data comunicazione
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input class="form-control frm_field_date frm_field_required" type="text" name="CommunicationDate"  style="width:12rem" value="' . $CommunicationDate . '" />
                    </div>
                    <div class="col-sm-2 BoxRowLabel">
                        Protocollo n°
                    </div>
                    <div class="col-sm-4 BoxRowCaption">
                        <input class="form-control frm_field_string" type="text" name="CommunicationProtocol" style="width:12rem" value="' . $r_Trespasser['CommunicationProtocol'] . '" />
                    </div>

                    <div class="clean_row HSpace4"></div>

                    <div class="col-sm-3 BoxRowLabel">
                        Dati incompleti
                    </div>
                    <div class="col-sm-1 BoxRowCaption">
                        <input type="checkbox" id="CheckIncomplete" name="CheckIncomplete" value="1"'.($Incomplete > 0 ? ' checked' : '').'/>
                    </div>
                    <div class="col-sm-8 BoxRowCaption">
                        '.CreateArraySelect($a_IncompleteCommunication, true, 'Incomplete', 'Incomplete', $Incomplete, true, null, $Incomplete > 0 ? null : 'hidden').'
                    </div>

                    <div class="clean_row HSpace16"></div>

                    <div class="col-sm-12 BoxRowLabel BoxRowTitle">

                        <input type="radio" name="TrespasserTypeId" value="1" ' . $checkedDriver . '> Proprietario/Trasgressore &nbsp;
                        <input'.($Incomplete == 1 || $Incomplete ==2 ? ' disabled' : '').' type="radio" name="TrespasserTypeId" value="2" ' . $checkedNoDriver . '> Proprietario/Obbligato in solido
                    </div>

                    <div class="clean_row HSpace16"></div>

                    <div class="alert alert-info col-sm-12">
                        <div class="col-sm-6 BoxRowLabel" style="background-color:#294A9C;">
                            PROPRIETARIO:
                        </div>
                        <div class="col-sm-6 table_caption_H text-center">
                            <div class="col-sm-11">
                            '.$r_Trespasser['Trespasser1Code'].' - ' . (!empty($r_Trespasser['CompanyName']) ? $r_Trespasser['CompanyName'].' ' : '') . $r_Trespasser['Surname'] . ' '. $r_Trespasser['Name'] . '
                            </div>
                            <div class="col-sm-1">
                                <i data-toggle="tooltip" data-container="body" data-placement="top" title="'.$a_Genre[$Genre]['Tooltip'].'"  class="tooltip-r fas '.$a_Genre[$Genre]['Icon'].'" style="font-size:1.8rem;margin-top:0.2rem"></i>
                            </div>
                        </div>
    
                        <div class="clean_row HSpace4"></div>

                        <div id="DIV_Message1" class="col-sm-12 alert alert-warning" style="display:none;margin:0;align-items:center;padding:0;">
                            <i class="fa fa-warning col-sm-1" style="text-align:center;line-height:inherit;"></i>
                            <ul class="col-sm-11" style="list-style-position:inside;"></ul>
    
                            <div class="clean_row HSpace4"></div>
                        </div>';

if($r_Trespasser['Genre'] != 'D' || ($r_Trespasser['Genre'] == 'D' && in_array($r_Trespasser['LegalFormId'], $a_LegalFormIndividual))){
                    $str_out.= '
    
                        <div class="col-sm-2 BoxRowLabel">
                            Documento
                        </div>
                        <div class="col-sm-4 BoxRowCaption">
                            ' . CreateSelect("TrespasserDocumentType", null, "Title", "DocumentTypeId", "Id", "Title", $r_Trespasser['DocumentTypeId'] ?? 1, true, null, "") . '
                        </div>
                        <div class="col-sm-2 BoxRowLabel">
                            Stato rilascio
                        </div>
                        <div class="col-sm-4 BoxRowCaption">
                            ' . CreateSelect("Country", null, "Title", "DocumentCountryId", "Id", "Title", $r_Trespasser['DocumentCountryId'], true, null, "") . '
                        </div>

                        <div class="clean_row HSpace4"></div>';
}
                    $str_out.= '
                        <div class="col-sm-2 BoxRowLabel">
                            Tipo veicolo
                        </div>
                        <div class="col-sm-4 BoxRowCaption">
                            '.$rs->getArrayLine($rs->Select('VehicleType', "Id=".$r_Trespasser['VehicleTypeId']))['TitleIta'].'
                        </div>
                        <div class="col-sm-6 BoxRowLabel">
                        </div>

                        <div class="clean_row HSpace4"></div>

                        <div class="col-sm-12 BoxRow">
                            DATI NOTIFICA:
                        </div>
    
                        <div class="clean_row HSpace4"></div>
    
                        <div class="col-sm-3 BoxRowLabel">
                            Modalità
                        </div>
                        <div class="col-sm-3 BoxRowCaption">
                            '.($NotificationType[$r_Trespasser['FineNotificationType']] ?? '').'
                        </div>
                        <div class="col-sm-3 BoxRowLabel">
                            Data notifica
                        </div>
                        <div class="col-sm-3 BoxRowCaption">
                            '.DateOutDB($r_FineNotification['NotificationDate']).'
                        </div>
    
                        <div class="clean_row HSpace4"></div>
    
                        <div class="col-sm-3 BoxRowLabel">
                            Raccomandata
                        </div>
                        <div class="col-sm-3 BoxRowCaption">
                            '.$r_FineNotification['LetterNumber'].'
                        </div>
                        <div class="col-sm-3 BoxRowLabel">
                            Ricevuta
                        </div>
                        <div class="col-sm-3 BoxRowCaption">
                            '.$r_FineNotification['ReceiptNumber'].'
                        </div>
    
                        <div class="clean_row HSpace4"></div>
    
                        <div class="col-sm-3 BoxRowLabel">
                            Data spedizione
                        </div>
                        <div class="col-sm-3 BoxRowCaption">
                            '.DateOutDB($r_FineNotification['SendDate']).'
                        </div>
                        <div class="col-sm-3 BoxRowLabel">
                            Data log
                        </div>
                        <div class="col-sm-3 BoxRowCaption">
                            '.DateOutDB($r_FineNotification['LogDate']).'
                        </div>
    
                        <div class="clean_row HSpace4"></div>
    
                        <div class="col-sm-3 BoxRowLabel">
                            Esito
                        </div>
                        <div class="col-sm-3 BoxRowCaption">
                            '.$r_FineNotification['Title'].'
                        </div>
                        <div class="col-sm-6 BoxRowLabel">
                        </div>';

if($r_Trespasser['Genre'] != 'D' || ($r_Trespasser['Genre'] == 'D' && in_array($r_Trespasser['LegalFormId'], $a_LegalFormIndividual))){
                    $str_out.= '
    
                        <div class="clean_row HSpace4"></div>
    
                        <div class="col-sm-12 BoxRow">
                            DATI PATENTE:
                        </div>
    
                        <div class="clean_row HSpace4"></div>
    
                        <div class="col-sm-2 BoxRowLabel">
                            Data rilascio
                        </div>
                        <div class="col-sm-4 BoxRowCaption">
                            <input class="form-control frm_field_date frm_field_required" type="text" id="LicenseDateMain" name="LicenseDate"  style="width:12rem" value="' . $LicenseDate . '" />
                        </div>
                        <div class="col-sm-2 BoxRowLabel">
                            Ente rilascio
                        </div>
                        <div class="col-sm-4 BoxRowCaption">
                            <input class="form-control frm_field_string frm_field_required" type="text" id="LicenseOfficeMain" name="LicenseOffice" style="width:18rem" value="' . $r_Trespasser['LicenseOffice'] . '" />
                        </div>
    
                        <div class="clean_row HSpace4"></div>
    
                        <div class="col-sm-2 BoxRowLabel">
                            Categoria
                        </div>
                        <div class="col-sm-4 BoxRowCaption">
                            <input class="form-control frm_field_string frm_field_required" type="text" id="LicenseCategoryMain" name="LicenseCategory"  style="width:10rem" value="' . $r_Trespasser['LicenseCategory'] . '" />
                        </div>
                        <div class="col-sm-2 BoxRowLabel">
                            Numero
                        </div>
                        <div class="col-sm-4 BoxRowCaption">
                            <input class="form-control frm_field_string frm_field_required" type="text" id="LicenseNumberMain" name="LicenseNumber"  style="width:10rem" value="' . $r_Trespasser['LicenseNumber'] . '" />
                        </div>';
}
                    $str_out.= '
                    </div>
                </div>


                <div id="othertrespasser" class="alert alert-info col-sm-12"'.($checkedDriver != '' ? ' style="display: none;"' : '').'>
                    <div class="col-sm-12">
                        <div class="col-sm-6 BoxRowLabel" style="background-color:#294A9C;">
                            TRASGRESSORE:
                        </div>
                        <div class="col-sm-6 table_caption_H text-center">
                            <div class="col-sm-11">
                                <span id="span_name">'.$r_Trespasser['Trespasser2Code'].' - ' . (! empty($r_Trespasser['CompanyNameDriver']) ? $r_Trespasser['CompanyNameDriver'] . ' ' : '') . $r_Trespasser['SurnameDriver'] . ' ' . $r_Trespasser['NameDriver'] . '</span>
                            </div>
                            <div class="col-sm-1">
                                '.(!empty($GenreDriver)
                                    ? '<span id="span_genre"><i data-toggle="tooltip" data-container="body" data-placement="top" title="'.$a_Genre[$GenreDriver]['Tooltip'].'"  class="tooltip-r fas '.$a_Genre[$GenreDriver]['Icon'].'" style="font-size:1.8rem;margin-top:0.2rem"></i></span>' 
                                    : '<span id="span_genre"></span>').'
                            </div>
                        </div>
                    </div>

                    <div id="DIV_Message2" class="col-sm-12 alert alert-warning" style="display:none;margin:0;align-items:center;padding:0;">
                        <i class="fa fa-warning col-sm-1" style="text-align:center;line-height:inherit;"></i>
                        <ul class="col-sm-11" style="list-style-position:inside;"></ul>

                        <div class="clean_row HSpace4"></div>
                    </div>

                    <div class="clean_row HSpace4"></div>

                    <ul class="nav nav-tabs" id="mioTab">
                        <li class="active" id="tab_Trespasser"><a href="#Trespasser" data-toggle="tab">PERSONA FISICA</a></li>
                        <span class="glyphicon glyphicon-plus-sign add_button tooltip-r" data-placement="top" title="Inserisci trasgressore" style="color:#294A9C;top:10px;font-size:25px;float: right;cursor:pointer;"></span>
                    </ul>

                    <div class="tab-content">
                        <div class="tab-pane active" id="Trespasser">
                            <div class="row-fluid">
                                <div class="col-sm-12">
                                    <div class="col-sm-3 BoxRowLabel">
                                        Cognome
                                    </div>
                                    <div class="col-sm-3 BoxRowCaption">
                                        <input class="form-control frm_field_string" type="text" name="Surname_S" id="Surname_S" value="' . $r_Trespasser['SurnameDriver'] . '">
                                    </div>
                                    <div class="col-sm-3 BoxRowLabel">
                                        Nome
                                    </div>
                                    <div class="col-sm-3 BoxRowCaption">
                                        <input class="form-control frm_field_string" type="text" name="Name_S" id="Name_S" value="' . $r_Trespasser['NameDriver'] . '">
                                    </div>
                                    <div class="clean_row HSpace4"></div>
                                </div>
                            </div>
                        </div>
                        <div id="trespasser_content" class="col-sm-12" style="max-height:20rem;overflow:auto"></div>
                    </div>

                    <div class="col-sm-12">
                        <div class="col-sm-2 BoxRowLabel">
                            Documento
                        </div>
                        <div class="col-sm-4 BoxRowCaption">
                            ' . CreateSelect("TrespasserDocumentType", null, "Title", "DocumentTypeIdDriver", "Id", "Title", $r_Trespasser['DocumentTypeIdDriver'] ?? 1, true, null, "") . '
                        </div>
                        <div class="col-sm-2 BoxRowLabel">
                            Stato rilascio
                        </div>
                        <div class="col-sm-4 BoxRowCaption">
                        ' . CreateSelect("Country", null, "Title", "DocumentCountryIdDriver", "Id", "Title", $r_Trespasser['DocumentCountryIdDriver'] ?? 'Z000', true, 20, "") . '
                        </div>
                    </div>

                    <div class="clean_row HSpace4"></div>

                    <div class="col-sm-12">
                        <div class="col-sm-12 BoxRow">
                            DATI PATENTE:
                        </div>
                    </div>

                    <div class="clean_row HSpace4"></div>

                    <div class="col-sm-12">
                        <div class="col-sm-2 BoxRowLabel">
                            Data rilascio
                        </div>
                        <div class="col-sm-4 BoxRowCaption">
                            <input class="form-control frm_field_date frm_field_required" type="text" id="LicenseDateDriver" name="LicenseDateDriver"  style="width:12rem" value="' . $LicenseDateDriver . '" />
                        </div>
                        <div class="col-sm-2 BoxRowLabel">
                            Ente rilascio
                        </div>
                        <div class="col-sm-4 BoxRowCaption">
                            <input class="form-control frm_field_string frm_field_required" type="text" id="LicenseOfficeDriver" name="LicenseOfficeDriver" style="width:18rem" value="' . $r_Trespasser['LicenseOfficeDriver'] . '" />
                        </div>
                    </div>

                    <div class="clean_row HSpace4"></div>

                    <div class="col-sm-12">
                        <div class="col-sm-2 BoxRowLabel">
                            Categoria
                        </div>
                        <div class="col-sm-4 BoxRowCaption">
                            <input class="form-control frm_field_string frm_field_required" type="text" id="LicenseCategoryDriver" name="LicenseCategoryDriver"  style="width:10rem" value="' . $r_Trespasser['LicenseCategoryDriver'] . '" />
                        </div>
                        <div class="col-sm-2 BoxRowLabel">
                            Numero
                        </div>
                        <div class="col-sm-4 BoxRowCaption">
                            <input class="form-control frm_field_string frm_field_required" type="text" id="LicenseNumberDriver" name="LicenseNumberDriver"  style="width:10rem" value="' . $r_Trespasser['LicenseNumberDriver'] . '" />
                        </div>
                    </div>
                </div>

                <div class="clean_row HSpace4"></div>

                <div class="col-sm-12" style="text-align:center;line-height:6rem;background-color: rgb(40, 114, 150);">
                    <button class="btn btn-success" id="save" type="submit">Salva</button>
                    <button class="btn btn-default" id="back">Indietro</button>
                </div>
            </form>';
echo $str_out;
require_once INC . '/module/mod_fineCommunicationHistory.php';

$str_out = '
             </div>
            </div>
            <div class="col-sm-6">
                <div class="col-sm-12 BoxRowLabel">Estensioni consentite: '.implode(' ', array_keys($a_AllowedExtensions)).'</div>
                <div class="col-sm-12 BoxRowLabel">Dimensione massima: '.$n_MaxFileSize.' MB</div>
                <div class="clean_row HSpace4"></div>
                 ' . $str_CommunicationFile . '
                <div class="col-sm-12 BoxRow" style="width:100%;height:60.2rem; position:relative;">
                    <div class="imgWrapper" id="preview_img" style="display: none; height:60rem;overflow:auto; display: none;">
                        <img id="preview" class="iZoom"  />
                    </div>
                    <div id="preview_doc" style="height:60rem;overflow:auto; display: none;"></div>
                </div>
            </div>
        </div>';

require (INC . "/module/mod_trespasser.php");

echo $str_out;

?>

<script src="<?=LIB?>/upload/js/jquery.ui.widget.js"></script>
<script src="<?=LIB?>/upload/js/jquery.iframe-transport.js"></script>
<script src="<?=LIB?>/upload/js/jquery.fileupload.js"></script>

<!-- Our main JS file -->
<script src="<?=LIB?>/upload/js/script.js"></script>

<script type="text/javascript">
	function convertDate(date){
		var arr = date.split('/');
		return arr[2]+"-"+arr[1]+"-"+arr[0];
	}

	function checkWarningMex(id){
		if($('#'+id+' ul li').length > 0)
			$('#'+id).show();
		else
			$('#'+id).hide();
	}

	function incompleteChangeRadio(value){
		if(value == 1 || value == 2){
			$('input:radio[name=TrespasserTypeId]').val([1]);
			$('input:radio[name=TrespasserTypeId][value=2]').prop('disabled', true);
			$('input:radio[name=TrespasserTypeId][value=1]').change();
		} else {
			$('input:radio[name=TrespasserTypeId][value=2]').prop('disabled', false);
		}
	}

	function toggleRequiredFields(radioVal){
		$('#f_comm_upd').data('bootstrapValidator').resetForm();
		if(radioVal == 1){
			if(checkDocumentType($('#DocumentTypeId'))){
				$("#f_comm_upd").bootstrapValidator('addField', $('#LicenseDateMain, #LicenseCategoryMain, #LicenseNumberMain, #LicenseOfficeMain'));
				$("#f_comm_upd").bootstrapValidator('removeField', $('#LicenseDateDriver, #LicenseCategoryDriver, #LicenseNumberDriver, #LicenseOfficeDriver'));
				$('#LicenseDateMain, #LicenseCategoryMain, #LicenseNumberMain, #LicenseOfficeMain').addClass("frm_field_required");
				$('#LicenseDateDriver, #LicenseCategoryDriver, #LicenseNumberDriver, #LicenseOfficeDriver').removeClass("frm_field_required");
			} else toggleRequiredFields(0);
		} else if (radioVal == 2) {
			if(checkDocumentType($('#DocumentTypeIdDriver'))){
				$("#f_comm_upd").bootstrapValidator('removeField', $('#LicenseDateMain, #LicenseCategoryMain, #LicenseNumberMain, #LicenseOfficeMain'));
				$("#f_comm_upd").bootstrapValidator('addField', $('#LicenseDateDriver, #LicenseCategoryDriver, #LicenseNumberDriver, #LicenseOfficeDriver'));
				$('#LicenseDateMain, #LicenseCategoryMain, #LicenseNumberMain, #LicenseOfficeMain').removeClass("frm_field_required");
				$('#LicenseDateDriver, #LicenseCategoryDriver, #LicenseNumberDriver, #LicenseOfficeDriver').addClass("frm_field_required");
			} else toggleRequiredFields(0);
		} else {
			$("#f_comm_upd").bootstrapValidator('removeField', $('#LicenseDateMain, #LicenseCategoryMain, #LicenseNumberMain, #LicenseOfficeMain, #LicenseDateDriver, #LicenseCategoryDriver, #LicenseNumberDriver, #LicenseOfficeDriver'));
			$('#LicenseDateMain, #LicenseCategoryMain, #LicenseNumberMain, #LicenseOfficeMain, #LicenseDateDriver, #LicenseCategoryDriver, #LicenseNumberDriver, #LicenseOfficeDriver').removeClass("frm_field_required");
		}
		$('#f_comm_upd').data('bootstrapValidator').resetForm();
	}

	function checkIncompleteRequired(){
		if($('#CheckIncomplete').prop("checked") && $('#Incomplete').val() == 3)
			return false;
		else return true;
	}

	function checkDocumentType(element){
		if(element.val() == 1 || element.val() == 6)
			return true;
		else return false;
	}

    $('document').ready(function(){

        var min_length = 2;
        var VehiclePlate = '';
        var Id = 0;
        var Genre = "M";

        var InputField = ["Surname","Name","LicenseNumber","LicenseCategory","LicenseOffice","LicenseDate","DocumentCountryId"];
        var InputName = ["Surname_S","Name_S","LicenseNumberDriver","LicenseCategoryDriver","LicenseOfficeDriver","LicenseDateDriver","DocumentCountryIdDriver"];

        var InputName = JSON.stringify(InputName);
        var InputField = JSON.stringify(InputField);


        $('#f_comm_upd').bootstrapValidator({
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
            }
        });

        $('#preview').iZoom({borderColor:'#294A9C', borderStyle:'double', borderWidth: '3px'});

        $('#DocName').click(function () {
            var path = '<?= $str_DocumentFolder; ?>';
            var file = path+$(this).attr('file');

            var FileType = file.substr(file.length - 3);

            if(FileType.toLowerCase()=='pdf' || FileType.toLowerCase()=='doc'){
                $("#preview_img").hide();
                $("#preview_doc").html("<iframe style=\"width:100%; height:100%\" src='"+file+"'></iframe>");
                $("#preview_doc").show();
            }else{
                $("#preview_doc").hide();
                $("#preview").attr("src",file);
                $("#preview_img").show();
            }

        });

        $('#upload_button, #delete_button').click(function () {
            var button = $(this);
            var operation = button.data('btnaction');
            var exec = true
            
        	if (operation == 'upl'){
        		if ($('#upl_file').get(0).files.length == 0){
        			alert ('È necessario selezionare un file');
        			return false;
        		}
        	} else if (operation == 'del'){
                if(!confirm("Si sta per cancellare l'immagine. Continuare?")){
                    return false;
                } else if (!confirm('Sei proprio sicuro di voler procedere?'))
                    return false;
        	}
        	
            var formdata = new FormData($('#f_Upl20')[0]);
            formdata.append('Operation', operation);
            if(operation == 'del'){
            	formdata.append('DocumentationId', $('#DocumentationId').val());
            }

            button.prop('disabled', true);
            button.find('i').removeClass('fa-plus fa-times');
            button.find('i').addClass('fa-circle-notch fa-spin');
                
            $.ajax({
                url: 'ajax/ajx_upl_communication_exe.php',
                dataType: 'JSON',
                cache: false,
                contentType: false,
                processData: false,
                data: formdata,                         
                type: 'POST',
                success: function(data){
                    if(data.Status){
                        if(data.Operation == 'upl'){
                            alert('Documento caricato con successo.');
                            $('#DocumentationId').val(data.DocumentationId);
                            $('#upl_file, #upload_button, #delete_button').toggleClass('hidden');
                            $('#DocName').html(data.Documentation).attr('file', data.Documentation).click();
                            $('#upl_file').val('');
                        }
                        if(data.Operation == 'del'){
                        	alert('Documento eliminto con successo.');
                            $('#DocumentationId').val('');
                            $('#upl_file, #upload_button, #delete_button').toggleClass('hidden');
                            $('#DocName').html('').attr('file', '');
                            $("#preview_doc").html('');
                            $("#preview").attr("src",'');
                        }
                    } else {
                    	if(data.Message) alert(data.Message);
                    }
                    button.prop('disabled', false);
                    button.find('i').removeClass('fa-circle-notch fa-spin');
                    button.find('i').addClass(operation == 'del' ? 'fa-times' : 'fa-plus');
                },
	            error: function (data) {
	                console.log(data);
	                alert("error: " + data.responseText);
	                button.prop('disabled', false);
	            }
             });
        	
        });

        $('#Surname_S, #Name_S').keyup(function(){
            var Name = $('#Name_S').val();
            var Surname = $('#Surname_S').val();
            var FineDate = $('#FineDate').val();

            if (Surname.length >= min_length || Name.length >= min_length) {
                $.ajax({
                    url: 'ajax/ajx_search_trespasser.php',
                    type: 'POST',
                    dataType: 'json',
                    cache: false,
                    data: {Surname:Surname, Name:Name, Genre:Genre, VehiclePlate:VehiclePlate, Id:Id, InputField:InputField, InputName:InputName, FineDate:FineDate},
                    success:function(data){
                        $('#trespasser_content').html(data.Trespasser).show();
                    },
    	            error: function (result) {
    	                console.log(result);
    	                alert("error: " + result.responseText);
    	            }
                });
            } else {
                $('#trespasser_content').hide();
            }
        });

        $('#back').click(function () {
            window.location = "<?=impostaParametriUrl(array('Filter' => 1), $str_BackPage)?>";
            return false;
        });

        $("input:radio[name='TrespasserTypeId']").change(function() {
            var radioVal = $("input:radio[name='TrespasserTypeId']:checked").val();
            if(radioVal == 1){
                $('#othertrespasser').hide();

                if(!checkIncompleteRequired())
                	toggleRequiredFields(0);
                else toggleRequiredFields(radioVal);
            }else{
                $('#othertrespasser').show();

                if(!checkIncompleteRequired())
                	toggleRequiredFields(0);
                else toggleRequiredFields(radioVal);
            }
        });

        $(".add_button").click(function () {
            $('#TitleTrespasser').html('Inserimento nuovo conducente');
            $('#overlay').fadeIn('fast');
            $('#Div_Windows_Insert_Trespasser').fadeIn('slow');
        });

        $( "#f_ins_trespasser" ).on( "submitted", function( event, TrespasserId, TrespasserName, Genre, BornDate, LicenseCategory, LicenseDate, LicenseOffice, LicenseNumber) {
            var genreIcon = 'fa-user';
            var genreTooltip = 'Persona fisica';
            var age = new Date(convertDate($('#FineDate').val())).getFullYear() - new Date(convertDate(BornDate)).getFullYear();
            
            $("#span_name").html(TrespasserName);
            $("#span_name").parent().parent().css( "background-color", "#06ff7c" );
            $("#TrespasserId").val(TrespasserId);
            $("#LicenseCategoryDriver").val(LicenseCategory);
            $("#LicenseDateDriver").val(LicenseDate);
            $("#LicenseOfficeDriver").val(LicenseOffice);
            $("#LicenseNumberDriver").val(LicenseNumber);

            if(Genre == 'D'){
                genreIcon = 'fa-building';
                genreTooltip = 'Ditta';
            } else if (Genre == 'DI'){
                genreIcon = 'fa-user-tie';
                genreTooltip = 'Ditta individuale';
            }

            $("#span_genre").html('<i data-toggle="tooltip" data-container="body" data-placement="top" title="'+genreTooltip+'" class="tooltip-r fas '+genreIcon+'" style="font-size:1.8rem;margin-top:0.2rem"></i>');

            if(age >= 18){
                $("#DIV_Message2 .minor").remove();
            } else {
            	if($('#DIV_Message2 ul .minor').length <= 0)
                	$("#DIV_Message2 ul").append('<li class="minor"> Trattasi di trasgressore minorenne</li>');
            }
            
            $('#LicenseDateDriver').change();

            checkWarningMex('DIV_Message2');
    	});

        $(document).on('TrespasserAdd', function(e, data){
            var genreIcon = 'fa-user';
            var genreTooltip = 'Persona fisica';
            
            $("#span_name").html(data.Name);
            $("#span_name").parent().parent().css( "background-color", "#06ff7c" );
            $("#TrespasserId").val(data.Id);

            if(data.Genre == 'D'){
                genreIcon = 'fa-building';
                genreTooltip = 'Ditta';
            } else if (data.Genre == 'DI'){
                genreIcon = 'fa-user-tie';
                genreTooltip = 'Ditta individuale';
            }

            $("#span_genre").html('<i data-toggle="tooltip" data-container="body" data-placement="top" title="'+genreTooltip+'" class="tooltip-r fas '+genreIcon+'" style="font-size:1.8rem;margin-top:0.2rem"></i>');

            if(data.Age==0 || data.Age >= 18){
                $("#DIV_Message2 .minor").remove();
            } else {
            	if($('#DIV_Message2 ul .minor').length <= 0)
                	$("#DIV_Message2 ul").append('<li class="minor"> Trattasi di trasgressore minorenne</li>');
            }
            $('#LicenseDateDriver').change();

            checkWarningMex('DIV_Message2');

            $("#trespasser_content").hide();
        });

        $('#LicenseDateDriver').on('change', function(){
            if($('#TrespasserId').val() != '' && $(this).val() != ''){
            	var years = new Date(convertDate($('#FineDate').val())).getFullYear() - new Date(convertDate($(this).val())).getFullYear();
            	if(years < 3){
            		if($('#DIV_Message2 ul .neo').length <= 0)
                    	$("#DIV_Message2 ul").append('<li class="neo"> Trattasi di neopatentato</li>');
            	} else {
                    $("#DIV_Message2 .neo").remove();
            	}
            	checkWarningMex('DIV_Message2');
            }
        });

        $('#LicenseDateMain').on('change', function(){
            if($(this).val() != ''){
            	var years = new Date(convertDate($('#FineDate').val())).getFullYear() - new Date(convertDate($(this).val())).getFullYear();
            	if(parseInt(years) < 3){
            		if($('#DIV_Message1 ul .neo').length <= 0)
                    	$("#DIV_Message1 ul").append('<li class="neo"> Trattasi di neopatentato</li>');
            	} else {
                    $("#DIV_Message1 .neo").remove();
            	}
            	checkWarningMex('DIV_Message1');
            }
        });

		$("#CheckIncomplete").change(function(){
			if($(this).prop("checked")){
				$('#Incomplete').removeClass('hidden');
				incompleteChangeRadio($('#Incomplete').val());
			} else {
				$('#Incomplete').addClass('hidden');
				incompleteChangeRadio(0);
			}
			var radioVal = $("input:radio[name='TrespasserTypeId']:checked").val();
			if(!checkIncompleteRequired())
				toggleRequiredFields(0)
			else toggleRequiredFields(radioVal);
		});

		$("#Incomplete").change(function(){
			incompleteChangeRadio($(this).val());

			var radioVal = $("input:radio[name='TrespasserTypeId']:checked").val();
			if(!checkIncompleteRequired())
				toggleRequiredFields(0)
			else toggleRequiredFields(radioVal);
		});

		$("#DocumentTypeId, #DocumentTypeIdDriver").change(function(){
			var radioVal = $("input:radio[name='TrespasserTypeId']:checked").val();
			toggleRequiredFields(radioVal);
		});

		//Attiva/disattiva i campi obbligatori al caricamento della pagina (tutti attivati di default) per ovviare a un problema con bootstrapvalidator
		var radioVal = $("input:radio[name='TrespasserTypeId']:checked").val();
		if(!checkIncompleteRequired())
			toggleRequiredFields(0)
		else toggleRequiredFields(radioVal);

        //Controlla i neopatentati al caricamento della pagina
        $('#LicenseDateDriver, #LicenseDateMain').change();

    });

</script>
<?php
include (INC . "/footer.php");
