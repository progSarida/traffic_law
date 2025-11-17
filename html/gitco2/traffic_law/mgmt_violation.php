<?php
require_once ("mgmt_violation_cmn.php");
require(INC . '/menu_'.$_SESSION['UserMenuType'].'.php');

$a_StatusTypeId = array();
$a_StatusTypeId[35] = "#A94442";
$a_StatusTypeId[36] = "#23448E";
$a_StatusTypeId[37] = "#A94442";
$a_StatusTypeId[8] = "#928703";
$a_StatusTypeId[9] = "#3C763D";

$a_Euro = array();
$a_Euro[27] = "A94442";//ROSSO
$a_Euro[28] = "c9c427";//GIALLO
$a_Euro[30] = "3C763D";//VERDE

$FineIcon = '';

$str_Union = CreateSelectCustomerUnion($Search_Locality);
readparametersAndBuildWhere();

//*******************Gestione Regolamento*******************
$RuleTypeId = $_SESSION['ruletypeid'];
$RuleTypeTitle = $_SESSION['ruletypetitle'];
$str_Where .= " AND RuleTypeId = $RuleTypeId";
//**********************************************************

$str_Search_ViolationDisabled = "";
if($s_TypePlate!="F")
    $str_Search_ViolationDisabled = "$('#Search_Country').prop('disabled', true);";

echo $str_out;
?>

<div class="row-fluid">
    <form id="f_Search" action="mgmt_violation.php" method="post">
    <div class="col-sm-12" >
        <div class="col-sm-11">
            <div class="col-sm-1 BoxRowLabel">
                Genere
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <?= $RuleTypeTitle ?>
            </div>
            <div class="col-sm-1 BoxRowLabel">
                Targa
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <input class="form-control frm_field_string" name="Search_Plate" type="text" value="<?= $Search_Plate ?>">
            </div>
            <div class="col-sm-1 BoxRowLabel">
                Rilevatore
            </div>
            <div class="col-sm-2 BoxRowCaption">
                <?= CreateSelectQuery("SELECT Id,CONCAT(progressive, ' - ', COALESCE(NULLIF(Ratification, ''), TitleIta)) AS TitleIta FROM Detector WHERE CityId='{$_SESSION['cityid']}' ORDER BY progressive", 'Search_Detector', 'Id', 'TitleIta', $Search_Detector, false) ?>
            </div>
            <div class="col-sm-1 BoxRowLabel">
                Prot/Ref
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <input class="form-control frm_field_string" name="Search_Ref" type="text" value="<?= $Search_Ref ?>">
            </div>
            <div class="col-sm-1 BoxRowLabel">
                Località
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <?= $str_Union ?>
            </div>
            <div class="col-sm-1 BoxRowLabel">
            </div>
                    
            <div class="clean_row HSpace4"></div>
                    
            <div class="col-sm-1 BoxRowLabel">
                Nazionalità
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <select class="form-control" name="TypePlate" id="TypePlate">
                    <option></option>
                    <option value="N"<?= $s_SelPlateN ?>>Nazionali</option>
                    <option value="F"<?= $s_SelPlateF ?>>Estere</option>
                </select>
            </div>
            <div class="col-sm-1 BoxRowLabel">
                Nazione
            </div>
            <div class="col-sm-2 BoxRowCaption">
                <?= CreateSelectConcat("SELECT DISTINCT F.CountryId, C.Title FROM Fine F JOIN Country C ON F.CountryId=C.Id WHERE CountryId!='Z000' ORDER BY C.Title","Search_Country","CountryId","Title",$Search_Country,false) ?>
            </div>
            <div class="col-sm-1 BoxRowLabel">
                Pratica
            </div>
            <div class="col-sm-2 BoxRowCaption">
                <?= CreateSelect("StatusType","Id<=10","Id","Search_Status","Id","Title",$Search_Status,false) ?>
            </div>
            <div class="col-sm-1 BoxRowLabel">
                Da data
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <input class="form-control frm_field_date" type="text" value="<?= $Search_FromFineDate ?>" name="Search_FromFineDate">
            </div>
            <div class="col-sm-1 BoxRowLabel">
                A data
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <input class="form-control frm_field_date" type="text" value="<?= $Search_ToFineDate ?>" name="Search_ToFineDate">
            </div>
                    
            <div class="clean_row HSpace4"></div>
                    
            <div class="col-sm-1 BoxRowLabel">
                Violazione
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <?= CreateSelect("ViolationType","1=1 AND RuleTypeId = $RuleTypeId","Id","Search_Violation","Id","Title",$Search_Violation,false) ?>
            </div>
            <div class="col-sm-1 BoxRowLabel">
                Tipo di veicolo
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <?= CreateSelect("VehicleType", "1=1", "Id", "Search_VehicleType", "Id", "TitleIta", $Search_VehicleType, false) ?>
            </div>
            <div class="col-sm-1 BoxRowLabel">
                Validazione
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <?= CreateArraySelect(array('Da validare','Validato'), false, 'Search_ValidationType', 'Search_ValidationType', $Search_ValidationType, false) ?>
            </div>
            
            <div id="Articles"<?= ($Search_Violation != 5 ? ' style="display:none"' : '') ?>>
            <div class="col-sm-2 BoxRowLabel">
                Articolo
            </div>
            <div class="col-sm-2 BoxRowCaption">
                <input type="radio" name="Search_ViolationArticle" value="1" style="position: initial;vertical-align: top;"<?= ($Search_ViolationArticle == 1 ? ' checked=""' : '') ?>>
                <label style="line-height:2;vertical-align: top;"> Entrambi</label>
            </div>
            <div class="col-sm-2 BoxRowCaption">
                <input type="radio" name="Search_ViolationArticle" value="2" style="position: initial;vertical-align: top;"<?= ($Search_ViolationArticle == 2 ? ' checked=""' : '') ?>>
                <label style="line-height:2;vertical-align: top;"> 126 bis</label>
            </div>
            <div class="col-sm-2 BoxRowCaption">
                <input type="radio" name="Search_ViolationArticle" value="3" style="position: initial;vertical-align: top;"<?= ($Search_ViolationArticle == 3 ? ' checked=""' : '') ?>>
                <label style="line-height:2;vertical-align: top;"> 180</label>
            </div>
            </div>
            <div class="col-sm-1 BoxRowLabel font_small">
                Solo lettere avviso bonario
            </div>
            <div class="col-sm-1 BoxRowCaption">
            	<!-- Input per checkbox vuota -->
    			<input value="0" type="hidden" name="Search_HasKindSendDate"> 
                <input type="checkbox" value="1" name="Search_HasKindSendDate" <?= ChkCheckButton($Search_HasKindSendDate > 0 ? 1 : 0) ?>>
            </div>
            <div class="col-sm-1 BoxRowLabel font_small">
                Solo anomalie velocità
            </div>
            <div class="col-sm-1 BoxRowCaption">
            	<!-- Input per checkbox vuota -->
    			<input value="0" type="hidden" name="Search_HasSpeedAnomaly"> 
                <input type="checkbox" value="1" name="Search_HasSpeedAnomaly" <?= ChkCheckButton($Search_HasSpeedAnomaly > 0 ? 1 : 0) ?>>
            </div>
            <div class="col-sm-1 BoxRowLabel font_small">
                Tipo invio
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <?= CreateArraySelect(array("Stampatore","PEC"), false, "Search_SendType", "Search_SendType", $Search_SendType, false) ?>
            </div>
        </div>
        <div class="col-sm-1">
            <div class="col-sm-12 BoxRowFilterButton" style="height:6.8rem">
            	<button type="submit" data-toggle="tooltip" data-placement="top" title="Cerca" class="tooltip-r btn btn-primary" id="search" name="search" style="margin-top:0;width:100%;height:100%"><i class="glyphicon glyphicon-search" style="font-size:3rem;"></i></button>
            </div>
        </div>
    </div>
    </form>
</div>

<div class="clean_row HSpace4"></div>

    	<div class="row-fluid">
        	<div class="col-sm-12">
				<div class="table_label_H col-sm-1">Rif.to</div>
				<div class="table_label_H col-sm-1">Stato</div>
				<div class="table_label_H col-sm-2">Dati atto</div>
				<div class="table_label_H col-sm-3">Proprietario / Obbligato / Noleggio</div>
				<div class="table_label_H col-sm-2">Trasgressore / Noleggiante</div>
                <div class="table_label_H col-sm-1">Giorni utili</div>
				<div class="table_label_H col-sm-1">Articoli</div>
        		<div class="table_add_button col-sm-1 right">
				<?= ChkButton($aUserButton, 'add','
                            <a href="mgmt_report_add.php'.$str_GET_Parameter.'&insertionType=3">
                            <span class="glyphicon glyphicon-plus-sign add_button" style="margin-right:0.3rem; "></span>
                            </a>
                    ') ?>
				</div>
		<div class="clean_row HSpace4"></div>

<?php 

$rs_Violation = $rs->SelectQuery("
SELECT vmv.*,126bis,PresentationDocument
FROM ". LIST_TABLE." vmv
LEFT JOIN ArticleTariff ata ON vmv.ArticleId = ata.ArticleId AND ata.Year = ProtocolYear
WHERE $str_Where ORDER BY ".LIST_ORDER." LIMIT $pagelimit,".PAGE_NUMBER);

/*
echo "
SELECT vmv.*,126bis,PresentationDocument
FROM ". LIST_TABLE." vmv
LEFT JOIN ArticleTariff ata ON vmv.ArticleId = ata.ArticleId AND ata.Year = ProtocolYear
WHERE $str_Where ORDER BY ".LIST_ORDER." LIMIT $pagelimit,".PAGE_NUMBER;
*/

$RowNumber = mysqli_num_rows($rs_Violation);

if ($RowNumber == 0) {
?>
    <div class="table_caption_H col-sm-14">
			Nessun record presente
		</div>
<?php
} else {
    while ($r_Violation = mysqli_fetch_array($rs_Violation)) {
        $a_TrespasserTypeId     = array();
        $a_TrespasserId         = array();
        $a_PEC                  = array();
        $a_TrespasserFullName   = array();
        $a_FineNotificationDate = array();
        
        
        $rs_FineHistoryTrespasser = $rs->Select('mgmt_FineHistory_Trespasser',"FineId=".$r_Violation['FineId']);
        $r_FineHistoryTrespasser = mysqli_fetch_array($rs_FineHistoryTrespasser);
        
        $str_Style      = (isset($a_StatusTypeId[$r_Violation['StatusTypeId']])) ? ' style="color:'.$a_StatusTypeId[$r_Violation['StatusTypeId']].';"' : '';
        $str_CssEuro    = (isset($a_Euro[$r_Violation['StatusTypeId']])) ? '#' . $a_Euro[$r_Violation['StatusTypeId']] : '#000';
        $str_Euro       = (isset($a_Euro[$r_Violation['StatusTypeId']])) ? $a_Euro[$r_Violation['StatusTypeId']] : '000';
        
        if (strpos($r_Violation['TrespasserId'], "|") === false) {
            
            $a_TrespasserId[$r_Violation['TrespasserTypeId']]          = $r_Violation['TrespasserId'];
            $a_PEC[$r_Violation['TrespasserTypeId']]                   = $r_Violation['PEC'];
            $a_TrespasserFullName[$r_Violation['TrespasserTypeId']]    = $r_Violation['TrespasserFullName'];
            $a_FineNotificationDate[$r_Violation['TrespasserTypeId']]  = $r_Violation['FineNotificationDate'];
            
        } else {
            
            $a_Tmp_TrespasserTypeId     = explode("|", $r_Violation['TrespasserTypeId']);
            $a_Tmp_TrespasserId         = explode("|", $r_Violation['TrespasserId']);
            $a_Tmp_PEC                  = explode("|", $r_Violation['PEC']);
            $a_Tmp_TrespasserFullName   = explode("|", $r_Violation['TrespasserFullName']);
            $a_Tmp_FineNotificationDate = explode("|", $r_Violation['FineNotificationDate']);;
            
            for($i=0; $i<count($a_Tmp_TrespasserId); $i++){
                $a_TrespasserId[$a_Tmp_TrespasserTypeId[$i]]          = $a_Tmp_TrespasserId[$i];
                $a_PEC[$a_Tmp_TrespasserTypeId[$i]]                                = $a_Tmp_PEC[$i];
                $a_TrespasserFullName[$a_Tmp_TrespasserTypeId[$i]]    = $a_Tmp_TrespasserFullName[$i];
                $a_FineNotificationDate[$a_Tmp_TrespasserTypeId[$i]]  = $a_Tmp_FineNotificationDate[$i];
            }
            
        }
        
        $str_Trespasser1 = $str_Trespasser2 = '';
        $str_NotificationDate = '<span class="tooltip-r" data-toggle="tooltip" data-placement="left" title="Non notificato"><i class="fa fa-calendar opaque" style="margin-top:0.2rem;font-size:1.8rem"></i></span>';
        $str_PEC = '<i class="fas fa-at tooltip-r opaque" data-container="body" data-toggle="tooltip" data-placement="top" title="PEC assente" style="margin-top:0.2rem;font-size:1.8rem;"></i>';
        if(isset($a_TrespasserId[1]) || isset($a_TrespasserId[2]) || isset($a_TrespasserId[10])){
            if(isset($a_TrespasserId[1])){
                $str_Trespasser1 = $a_TrespasserFullName[1];
                $n_AssingedIndex = 1;
            } else if(isset($a_TrespasserId[2])){
                $str_Trespasser1 = $a_TrespasserFullName[2];
                $n_AssingedIndex = 2;
            } else{
                $str_Trespasser1 = $a_TrespasserFullName[10];
                $n_AssingedIndex = 10;
            }
            
            if($a_FineNotificationDate[$n_AssingedIndex]!="") $str_NotificationDate = '<span class="tooltip-r" data-toggle="tooltip" data-placement="left" title="Notificato in data '. DateOutDB($a_FineNotificationDate[$n_AssingedIndex]).'"><i class="fa fa-calendar-alt" style="margin-top:0.2rem;font-size:1.8rem;"></i></span>';
            if($a_PEC[$n_AssingedIndex]!="") $str_PEC = '<i class="fas fa-at tooltip-r" data-container="body" data-toggle="tooltip" data-placement="top" title="'.$a_PEC[$n_AssingedIndex].'" style="margin-top:0.2rem;font-size:1.8rem;"></i>';
            
            $str_Trespasser1 = (strlen($str_Trespasser1)>33) ? substr($str_Trespasser1,0,30).'...' : $str_Trespasser1;
            $str_Trespasser1 = $str_NotificationDate . ' ' . $str_PEC . $str_Trespasser1;
            
        }
        $str_NotificationDate = '<span class="tooltip-r" data-toggle="tooltip" data-placement="left" title="Non notificato"><i class="fa fa-calendar opaque" style="margin-top:0.2rem;font-size:1.8rem"></i></span>';
        $str_PEC = '<i class="fas fa-at tooltip-r opaque" data-container="body" data-toggle="tooltip" data-placement="top" title="PEC assente" style="margin-top:0.2rem;font-size:1.8rem;"></i>';     
        if(isset($a_TrespasserId[3]) || isset($a_TrespasserId[11])){
            if(isset($a_TrespasserId[3])){
                $str_Trespasser2 = $a_TrespasserFullName[3];
                $n_AssingedIndex = 3;
            } else {
                $str_Trespasser2 = $a_TrespasserFullName[11];
                $n_AssingedIndex = 11;
            }
            
            if($a_FineNotificationDate[$n_AssingedIndex]!="") $str_NotificationDate = '<span class="tooltip-r" data-toggle="tooltip" data-placement="left" title="Notificato in data '. DateOutDB($a_FineNotificationDate[$n_AssingedIndex]).'"><i class="fa fa-calendar-alt" style="margin-top:0.2rem;font-size:1.8rem;"></i></span>';
            if($a_PEC[$n_AssingedIndex]!="") $str_PEC = '<i class="fas fa-at tooltip-r" data-container="body" data-toggle="tooltip" data-placement="top" title="'.$a_PEC[$n_AssingedIndex].'" style="margin-top:0.2rem;font-size:1.8rem;"></i>';
            
            $str_Trespasser2 = (strlen($str_Trespasser2)>33) ? substr($str_Trespasser2,0,30).'...' : $str_Trespasser2;
            $str_Trespasser2 = $str_NotificationDate .' '. $str_PEC . $str_Trespasser2;
            
        }
        
        $str_VehicleType = '<i class="'.$aVehicleTypeId[$r_Violation['VehicleTypeId']].'" style="color:#337AB7;"></i>';
        
        if($r_Violation['KindCreateDate'] != '' && $r_Violation['StatusTypeId'] != 8 && $r_Violation['StatusTypeId'] != 9){
            $FineIcon = '<i style="font-size:1.3rem; margin-right:0.5rem;" class="fa fa-files-o tooltip-r" data-container="body" data-toggle="tooltip" data-placement="top" title="Lettera avviso bonario"></i>';
        } else $FineIcon = $a_FineTypeId[$r_Violation['FineTypeId']];
        
		$str_FineData = $FineIcon . ' ' . DateOutDB($r_Violation['FineDate']) . ' - ' . TimeOutDB($r_Violation['FineTime']) . ' <span style="position:absolute; right:0.5rem;">' . StringOutDB($r_Violation['VehiclePlate']) . ' ' . $str_VehicleType . '</span>';
               
        $str_ArticleNumber = ($r_Violation['ArticleNumber']>1) ? '<i class="fa fa-list-ol" style="position:absolute;right:2rem;top:0.3rem; color:#337AB7; font-size:1.6rem;"></i>' : '';
        
        $Status = '';
        
        $str_ButtonController = "";
        $str_ButtonController = ($_SESSION['usertype']==2 && $r_Violation['ControllerId'] == "") ? '<i id="'.$r_Violation['FineId'].'" class="fa fa-cogs" style="position:absolute;color:#fff; left:0.1rem;font-size:1.7rem;top:0.2rem;"></i>' : '';
        
        $str_ChkController = ($_SESSION['usertype']==2 && $r_Violation['ControllerId'] == "") ? ' style="background-color: rgba(107,155,29,0.76);"' : "";
        
        
        if($r_Violation['TrespasserId'] != ""){
            $Status = ($r_Violation['ControllerId'] == "") ? $str_ButtonController.'<i id="icon_'.$r_Violation['FineId'].'" class="fa fa-battery-three-quarters" style="color:rgba(107,155,29,0.76); margin-top:0.2rem;"></i>' : '<i class="fa fa-battery-full" title="Trasgressore associato" style="color:#3C763D; margin-top:0.2rem;"></i>';
        }
        elseif($r_Violation['StatusTypeId']==5) $Status = $str_ButtonController.'<i class="fa fa-battery-half" style="color:#E0DB75; margin-top:0.2rem;"></i>';
        else $Status = $str_ButtonController.'<i class="fa fa-battery-quarter" title="Non e associato un trasgressore" style="color:#C43A3A; margin-top:0.2rem;"></i>';
        
        //stato pagamento
        if($r_FineHistoryTrespasser['PaymentDate']!=""){
            $Status .= '<span class="tooltip-r" data-toggle="tooltip" data-placement="left" title="Verbale pagato in data '. DateOutDB($r_FineHistoryTrespasser['PaymentDate']).'"><i id="'.$r_FineHistoryTrespasser['PaymentId'].'" class="fa fa-eur" style="margin-top:0.2rem;margin-left:1rem;font-size:1.8rem;color:'.$str_CssEuro.'" name="'.$str_Euro.'"></i></span>';
        }else if($r_Violation['StatusTypeId']==27 && $_SESSION['userlevel']>=7) $Status .= '<i class="fa fa-eur" style="margin-top:0.2rem;margin-left:1rem;font-size:1.8rem;color:#A94442" name="A94442"></i>';
        else{
            
            if($_SESSION['usertype']>50) {
                $Status .= '
                    <span class="tooltip-r" data-toggle="tooltip" data-placement="left" title="Cerca pagamento">
                        <i class="fa fa-eur src_payment" fineid="'.$r_Violation['FineId'].'" style="margin-top:0.2rem;margin-left:0.7rem;font-size:1.8rem;opacity:.2"></i>
                    </span>
                ';
            }else{
                $Status .= '<i class="fa fa-eur" style="margin-top:0.2rem;margin-left:1rem;font-size:1.8rem;opacity:.1;color:'.$str_CssEuro.'" name="'.$str_Euro.'"></i>';
            }
        }
        
        //bonario
        if($r_Violation['StatusTypeId']==8){
            $Status .= '<span class="tooltip-r" data-container="body" data-toggle="tooltip" data-placement="right" title="Avviso bonario'.(!is_null($r_FineHistoryTrespasser['FlowDate']) ? ' - creato in data '.DateOutDB($r_FineHistoryTrespasser['FlowDate']) : '').'"><i class="fas fa-wallet" style="margin-top:0.2rem;margin-left:1rem;font-size:1.8rem;color:#928703;"></i></span>';
            $KindFine = true;
        }else if($r_Violation['StatusTypeId']==9){
            $Status .= '<span class="tooltip-r" data-container="body" data-toggle="tooltip" data-placement="right" title="Avviso bonario'.(!is_null($r_FineHistoryTrespasser['SendDate']) ? ' - inviato in data '.DateOutDB($r_FineHistoryTrespasser['SendDate']) : '').'"><i class="fas fa-wallet" style="margin-top:0.2rem;margin-left:1rem;font-size:1.8rem;color:#3C763D"></i></span>';
            $KindFine = true;
        } else $KindFine = false;
        
        $ResidualDays = residualDays($rs, $r_Violation);
        
?>
        	<div class="table_caption_H col-sm-1" <?= $str_Style ?>><?= $r_Violation['Code'] ?></div>
			<div class="table_caption_H col-sm-1" style="text-align:center;font-size:2rem;"><?= $Status ?></div>
			<div class="table_caption_H col-sm-2" <?= $str_Style ?>><?= $str_FineData ?></div>
			<div class="table_caption_H col-sm-3"><?= StringOutDB($str_Trespasser1) ?></div>
			<div class="table_caption_H col-sm-2"><?= StringOutDB($str_Trespasser2) ?></div>
            <div class="table_caption_H col-sm-1 text-center">
            	<?php if($ResidualDays === 'dispute'){ ?>
                    <i class="fa fa-gavel tooltip-r" data-container="body" data-toggle="tooltip" data-placement="right" title="Verificare l\'esito del ricorso" style="font-size: 1.3rem;margin-top: 0.3rem;"></i>
            	<?php } else { ?>
                    <span <?= ($ResidualDays <= 0 ? ' class="text-danger"' : '') ?>><?= $ResidualDays ?></span>
            	<?php } ?>
            </div>
			<div class="table_caption_H col-sm-1"<?= $str_ChkController ?>><?= $r_Violation['Article'] .'/'.$r_Violation['Paragraph'].' '. $r_Violation['Letter'] . ' '. $str_ArticleNumber ?></div>
        	<div class="table_caption_button col-sm-1">
        	<?= ChkButtonLink($aUserButton, 'view',"mgmt_violation_viw.php$str_GET_Parameter&Id={$r_Violation['FineId']}"); ?>
<?php 
            if(!$KindFine){
                echo ChkButtonLink($aUserButton, 'update',"mgmt_report_upd.php$str_GET_Parameter&Id={$r_Violation['FineId']}",25);
            if(($r_Violation['Article']==80 AND $r_Violation['Paragraph']=='14') || ($r_Violation['Article']==193 AND $r_Violation['Paragraph']=='2'))
                echo ChkButtonLink($aUserButton, 'violationAct',"mgmt_violation_act.php$str_GET_Parameter&Id={$r_Violation['FineId']}",45);
            elseif($r_Violation['ControllerId']==0 && $_SESSION['usertype']==2)
                echo ChkButtonLink($aUserButton, 'control',"mgmt_violation_viw.php$str_GET_Parameter&Validation=1&Id={$r_Violation['FineId']}",65);
            echo ChkButtonLink($aUserButton, 'delete',"mgmt_violation_del.php$str_GET_Parameter&Id={$r_Violation['FineId']}",85);
        }
        echo ChkButtonLink($aUserButton, 'archive',"mgmt_violation_exp.php$str_GET_Parameter&Id={$r_Violation['FineId']}",105);
?>
        </div>
			<div class="clean_row HSpace4"></div>
<?php
        }
    }
    
//TODO Viene fatta una nuova query per intero solo per sapere il numero di record totali. Valutare se e come eliminare
$table_users_number = $rs->SelectQuery("
SELECT vmv.*,126bis,PresentationDocument
FROM V_mgmt_ViolationTrespasser vmv
LEFT JOIN ArticleTariff ata ON vmv.ArticleId = ata.ArticleId AND ata.Year = ProtocolYear WHERE $str_Where");
$UserNumberTotal = mysqli_num_rows($table_users_number);

echo CreatePagination(PAGE_NUMBER, $UserNumberTotal, $page, $str_CurrentPage, $str_FineTypeLabel);
?>
	<div>
</div>

<script type="text/javascript">

	$(document).ready(function () {

        $(".fa-cogs").hover(function(){
            $(this).css("color","#2684b1");
            $(this).css("cursor","pointer");
        },function(){
            $(this).css("color","#fff");
            $(this).css("cursor","");
        });

        $(".glyphicon").click(function(){
            $(this).css("color","#E7E7E7");
            $(this).css("cursor","pointer");
        },function(){
            $(this).css("color","#fff");
            $(this).css("cursor","");
        });

        $("#Search_Violation").change(function(){
            if ($(this).val() == 5)
            	$("#Articles").show();
            else
            	$("#Articles").hide();
        });

        /*
        $(".fa-cogs").click(function () {

            var id=$(this).attr('id');

            $(this).hide();

            $.ajax({
                url: 'ajax/ajx_upd_controller_exe.php',
                type: 'POST',
                dataType: 'json',
                cache: false,
                data: {Id: id},
                success: function () {

                    $('#icon_'+id).removeClass('fa-battery-three-quarters').addClass('fa-battery-full').css('color','#3C763D');

                }
            });
        });
        */


	});
</script>
<?php
include(INC."/footer.php");
