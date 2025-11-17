<?php
include ("_path.php");
include (INC . "/parameter.php");
include (CLS . "/cls_db.php");
require(CLS."/cls_message.php");
include (INC . "/function.php");
include (INC . "/header.php");
require (INC . "/initialization.php");
require (INC . '/menu_' . $_SESSION['UserMenuType'] . '.php');
const CHECKED = ' CHECKED ';

$checkLicenseYoung=getRadioSelected('Search_LicenseYoung',3,0);
$checkFlag126bis=getRadioSelected('Flag126bis',3,1);
$checkFlagDispute=getRadioSelected('FlagDispute',3,0);
$checkSearchCom126bis=getRadioSelected('Search_Com126Bis',3,1);
$PageTitle = CheckValue('PageTitle', 's');
$Filter = CheckValue('Filter', 'n');

$str_Where = '1=1';
$str_Order = "ProtocolYear DESC, ProtocolId DESC";

if($Search_CommunicationStatus==null || $Search_CommunicationStatus==''){
    $Search_CommunicationStatus=1;
}

//tolgo la condizione sullo stato <=32 perché nella vista c'è già quella sullo stato <=30
//$str_WhereInit =$str_Where. " AND StatusTypeId>=10 AND StatusTypeId<=32 AND CityId='" . $_SESSION['cityid']."'";
$str_WhereInit =$str_Where. " AND StatusTypeId>=10 AND CityId='" . $_SESSION['cityid']."'";
$rs_parameter=$rs->Select("V_CustomerParameter","CityId='{$_SESSION['cityid']}'");
$customerParameter=mysqli_fetch_array($rs_parameter);

$str_Where=$str_WhereInit.createCommunicationFilter(
    $s_TypePlate,
    $Search_Country,
    $Search_FromProtocolId,
    $Search_ToProtocolId,
    $Search_Plate,
    $Search_Trespasser,
    $Search_Violation,
    $Search_LicenseYoung,
    $Search_FromFineDate,
    $Search_ToFineDate,
    $Search_Ref,
    //$Search_FromNotificationDate partirà sempre dal 30/03/2021 se i ricorsi sono esclusi
    $Search_FromNotificationDate,
    $Search_ToNotificationDate,
    $Search_PaymentDate,
    $Search_FromProtocolYear,
    $Search_ToProtocolYear,
    $Search_CommunicationStatus,
    $Search_Com126Bis,
    $customerParameter['Data126BisNationalWaitDay'],
    $Flag126bis,
    $FlagDispute,
    $Search_NotificationStatus);


if ($Filter == 1)
  {
  $rs_Fine = $rs->Select('V_FineCommunication', $str_Where, $str_Order);

  $RowNumber = mysqli_num_rows($rs_Fine);
  mysqli_data_seek($rs_Fine, $pagelimit);
  }
  
$strLabel = '
	<div style="position:absolute; top:5px;font-size:1.2rem;color:#fff;width:405px;text-align: left">
 		<div style="width:200px;float:left;">
 			<i class="fa fa-address-card-o" style="margin-top:0.2rem;margin-left:1rem;font-size:1.2rem;"></i> Comunicazione presentata
		</div>
		<div style="width:200px;float:left;">
			<i class="fa fa-sort-numeric-desc" style="margin-top:0.2rem;margin-left:1rem;font-size:1.2rem;"></i> Punti decurtati
		</div>
		<div style="width:200px;float:left;">
			<i class="fa fa-envelope-o" style="margin-top:0.2rem;margin-left:1rem;font-size:1.2rem;"></i> 126Bis spedito
		</div>
	</div>';

echo $str_out;
?>

<div class="row-fluid">
    <!-- <div class="col-sm-12">
        <div class="col-sm-12 alert alert-danger" style="display: flex;margin: 0px;align-items: center;">
            <i class="fas fa-2x fa-info-circle col-sm-1" style="text-align:center;"></i>
            <div class="col-sm-11" style="font-size: 1.2rem;">
                A fine elaborazione è opportuno confrontare il risultato con quello prodotto dalla pagina Verbali>Comunicazioni art. 126 in modo da poter sopperire ad eventuali mancate elaborazioni di verbali per notifiche non ancora restituite o non ancora inserite a programma.<br>
                Eseguire questa operazione inserendo le stesse date nei campi "Da data notifica" - "A data notifica" e spostando il radio button associato alla selezione: Verbale 126 bis creato su (0) Escludi
            </div>
        </div>
    </div>

    <div class="clean_row HSpace4"></div> -->

	<form id="f_search" action="mgmt_communication.php" method="post" autocomplete="off">
		<input type="hidden" name="PageTitle" value="<?=$PageTitle;?>">
		<input type="hidden" name="Filter" value="1">
		<input type="hidden" id="PrintType" name="PrintType" value="Pdf">

        <div class="col-sm-11" style="height:11.4rem; border-right:1px solid #E7E7E7;">
            <div class="col-sm-1 BoxRowLabel">
                Da cron
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <input class="form-control frm_field_numeric" type="text" value="<?= $Search_FromProtocolId ?>" id="Search_FromProtocolId" name="Search_FromProtocolId">
            </div>
            <div class="col-sm-1 BoxRowLabel">
                A cron
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <input class="form-control frm_field_numeric" type="text" value="<?=$Search_ToProtocolId?>" id="Search_ToProtocolId" name="Search_ToProtocolId">
            </div>
            <div class="col-sm-1 BoxRowLabel">
                Da anno
            </div>
            <div class="col-sm-1 BoxRowCaption">
            	<?= CreateArraySelect($_SESSION['YearArray'][MENU_ID][$_SESSION['cityid']], false, 'Search_FromProtocolYear', 'Search_FromProtocolYear', $Search_FromProtocolYear, false); ?>
            </div>
            <div class="col-sm-1 BoxRowLabel">
                Ad anno
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <?= CreateArraySelect($_SESSION['YearArray'][MENU_ID][$_SESSION['cityid']], false, 'Search_ToProtocolYear', 'Search_ToProtocolYear', $Search_ToProtocolYear, false); ?>
            </div>
            <div class="col-sm-1 BoxRowLabel" style="font-size:0.9rem">
                Da data accertamento
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <input class="form-control frm_field_date" type="text" value="<?=$Search_FromFineDate ?>" name="Search_FromFineDate">
            </div>
            <div class="col-sm-1 BoxRowLabel" style="font-size:0.9rem">
                A data accertamento
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <input class="form-control frm_field_date" type="text" value="<?= $Search_ToFineDate ?>" name="Search_ToFineDate">
            </div>
            
            <div class="clean_row HSpace4"></div>
            
            <div class="col-sm-1 BoxRowLabel">
                Riferimento
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <input class="form-control frm_field_string" name="Search_Ref" type="text" value="<?=$Search_Ref;?>">
            </div>
            <div class="col-sm-1 BoxRowLabel">
                Nazionalità
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <select class="form-control" name="TypePlate" id="TypePlate">
                    <option value="">Tutte</option>
                    <option value="N"<?=$s_SelPlateN;?>>Nazionali</option>
                    <option value="F"<?=$s_SelPlateF;?>>Estere</option>
                </select>
            </div>
            <div class="col-sm-1 BoxRowLabel">
                Nazione
            </div>
            <div class="col-sm-3 BoxRowCaption">
            	<?=CreateSelectConcat("SELECT DISTINCT F.CountryId, C.Title FROM Fine F JOIN Country C ON F.CountryId=C.Id WHERE CountryId!='Z000' ORDER BY C.Title", "Search_Country", "CountryId", "Title", $Search_Country, false, null, null, $s_TypePlate == 'F' ? false : true);?>
            </div>
            <div class="col-sm-1 BoxRowLabel">
                Da data notifica
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <input class="form-control frm_field_date" type="text" value="<?= $Search_FromNotificationDate ?>" name="Search_FromNotificationDate">
            </div>
            <div class="col-sm-1 BoxRowLabel">
                A data notifica
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <input class="form-control frm_field_date" type="text" value="<?= $Search_ToNotificationDate ?>" name="Search_ToNotificationDate">
            </div>

            <div class="clean_row HSpace4"></div>
            
            <div class="col-sm-1 BoxRowLabel">
                Neopatentati:
            </div>
            <div class="col-sm-2 BoxRowCaption">
                <input type="radio" name="Search_LicenseYoung" value="1" style="top:0;" <?= $checkLicenseYoung[1] ?>><span  style="position:relative;top:-0.3rem"> Escludi</span>
                <input type="radio" name="Search_LicenseYoung" value="0" style="top:0;" <?= $checkLicenseYoung[0] ?>><span  style="position:relative;top:-0.3rem"> Includi</span>
                <input type="radio" name="Search_LicenseYoung" value="2" style="top:0;" <?= $checkLicenseYoung[2] ?>><span  style="position:relative;top:-0.3rem"> Solo loro</span>
            </div>
            <div class="col-sm-2 BoxRowLabel">
                Verbale art. 126 bis creato
            </div>
            <div class="col-sm-2 BoxRowCaption">
                <input type="radio" name="Search_Com126Bis" value="1" style="top:0;" <?= $checkSearchCom126bis[1] ?>><span  style="position:relative;top:-0.3rem"> Escludi</span>
                <input type="radio" name="Search_Com126Bis" value="0" style="top:0;" <?= $checkSearchCom126bis[0] ?>><span  style="position:relative;top:-0.3rem"> Includi</span>
                <input type="radio" name="Search_Com126Bis" value="2" style="top:0;" <?= $checkSearchCom126bis[2] ?>><span  style="position:relative;top:-0.3rem"> Solo loro</span>
            </div>
            <div class="col-sm-1 BoxRowLabel">
                Stato comunicazione:
            </div>
            <div class="col-sm-2 BoxRowCaption">
            	<?= CreateArraySelect(COMMUNICATIONSTATUSOPTIONS, true, 'Search_CommunicationStatus', 'Search_CommunicationStatus', $Search_CommunicationStatus, true) ?>
            </div>
            <div class="col-sm-1 BoxRowLabel" style="font-size:0.9rem">
                Stato notifica verbale originario:
            </div>
            <div class="col-sm-1 BoxRowCaption">
            	<?= CreateArraySelect(ORIGINALFINENOTIFICATIONSTATUS, true, 'Search_NotificationStatus', 'Search_NotificationStatus', $Search_NotificationStatus, true) ?>
            </div>

            <div class="clean_row HSpace4"></div>
            <div class="col-sm-1 BoxRowLabel">
                Trasgressore
            </div>
            <div class="col-sm-3 BoxRowCaption">
                <input class="form-control frm_field_string" name="Search_Trespasser" type="text" value="<?=$Search_Trespasser ?>">
            </div>
            <div class="col-sm-1 BoxRowLabel">
                Violazione
            </div>
            <div class="col-sm-3 BoxRowCaption">
                <?=CreateSelect("ViolationType", "1=1", "Id", "Search_Violation", "Id", "Title", $Search_Violation, false);?>
            </div>
 			<div class="col-sm-2 BoxRowLabel">
                Viol. art. 126 bis in "Ulteriori dati" a NO:
            </div>
            <div class="col-sm-2 BoxRowCaption">
                <input type="radio" name="Flag126bis" value="1" style="top:0;" <?= $checkFlag126bis[1] ?>><span  style="position:relative;top:-0.3rem"> Escludi</span>
                <input type="radio" name="Flag126bis" value="0" style="top:0;" <?= $checkFlag126bis[0] ?>><span  style="position:relative;top:-0.3rem"> Includi</span>
                <input type="radio" name="Flag126bis" value="2" style="top:0;" <?= $checkFlag126bis[2] ?>><span  style="position:relative;top:-0.3rem"> Solo loro</span>
            </div>
        	 <div class="clean_row HSpace4"></div>
  			<div class="col-sm-1 BoxRowLabel">
                Ricorsi:
            </div>
            <div class="col-sm-2 BoxRowCaption">
                <input type="radio" name="FlagDispute" value="1" style="top:0;" <?= $checkFlagDispute[1] ?>><span  style="position:relative;top:-0.3rem"> Escludi</span>
                <input type="radio" name="FlagDispute" value="0" style="top:0;" <?= $checkFlagDispute[0] ?>><span  style="position:relative;top:-0.3rem"> Includi</span>
                <input type="radio" name="FlagDispute" value="2" style="top:0;" <?= $checkFlagDispute[2] ?>><span  style="position:relative;top:-0.3rem"> Solo loro</span>
            </div>
            <div class="col-sm-4 BoxRowLabel font_small">
                In assenza di notifica considera il verbale notificato dalla data di pagamento
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <input disabled type="checkbox" value="1" name="Search_PaymentDate" <?= ChkCheckButton($Search_PaymentDate); ?>>
            </div>
  			<div class="col-sm-4 BoxRowLabel">
            </div>
        	 <div class="clean_row HSpace4"></div>
        </div>
        <div class="col-sm-1 BoxRowFilterButton" style="height:11.4rem">
            <button type="submit" data-toggle="tooltip" data-container="body" data-placement="top" title="Cerca" class="tooltip-r col-sm-4 btn btn-primary" id="search" name="search" style="height:33%;font-size:2.2rem;padding:0;margin:0;width:100%">
                <i class="glyphicon glyphicon-search"></i>
            </button>
            <button type="submit" data-toggle="tooltip" data-container="body" data-placement="top" title="Stampa pdf prospetto" class="tooltip-r btn btn-warning col-sm-4" id="printPdf" name="printPdf" style="height:33%;font-size:2.2rem;padding:0;;width:100%">
            	<i class="fa fa-file-pdf-o"></i>
            </button>
            <button type="submit" data-toggle="tooltip" data-container="body" data-placement="top" title="Stampa excel prospetto" class="tooltip-r btn btn-success col-sm-4" id="printExcel" name="printExcel" style="height:33%;font-size:2.2rem;padding:0;;width:100%">
            	<i class="fa fa-file-excel-o"></i>
            </button>
        </div>
    </form>

    <div class="clean_row HSpace4"></div>

	<div class="table_label_H col-sm-1">Riga | Cron</div>
	<div class="table_label_H col-sm-1">Riferimento</div>
	<div class="table_label_H col-sm-2">Dati atto</div>
	<div class="table_label_H col-sm-4">Trasgressore</div>
	<div class="table_label_H col-sm-1">Data not.</div>
	<div class="table_label_H col-sm-1">Articolo</div>
	<div class="table_label_H col-sm-2">Stato pratica</div>
    <?php if ($Filter != 1):?>
        <div class="table_caption_H col-sm-12 text-center">
        	Inserire criteri di ricerca
        </div>
    <?php else: ?>
		<?php if ($RowNumber > 0):?>
			<?php for ($i = 0; $i < PAGE_NUMBER; $i ++):?>
				<?php $r_Fine = mysqli_fetch_assoc($rs_Fine);?>
				<?php if (! empty($r_Fine)): ?>
					<?php
                    $rs_126Bis = $rs->SelectQuery("SELECT Id,FineDate FROM Fine WHERE PreviousId=" . $r_Fine['Id'] . " AND StatusTypeId<35 and Note = 'Creazione automatica 126 BIS' ");
                    $RowNumber126Bis = mysqli_num_rows($rs_126Bis);
                    $r_126Bis = $RowNumber126Bis > 0 ? mysqli_fetch_assoc($rs_126Bis) : null;
                    ?>
					<div class="tableRow">
            			<div class="table_caption_H col-sm-1">
        	            	<?php if ($r_Fine['CommunicationStatus'] == 5): ?>
        	            		<i class="fa fa-reply tooltip-r" data-container="body" data-toggle="tooltip" data-placement="right" title="Ripristina punti" data-id="<?=$r_Fine['Id'];?>" style="cursor:pointer;"></i>
        	            	<?php endif; ?>
            				<?=(($i+1)+$pagelimit).' | '.$r_Fine['ProtocolId'] . ' / ' . $r_Fine['ProtocolYear'];?>
        				</div>
            			<div class="table_caption_H col-sm-1">
            				<?=$r_Fine['Code'];?>
        				</div>
                    	<div class="table_caption_H col-sm-2" style="padding-right:0.5rem">
	                    	<div class="col-sm-8">
                        		<?= $a_FineTypeId[$r_Fine['FineTypeId']] . ' ' . DateOutDB($r_Fine['FineDate']) . ' - ' . TimeOutDB($r_Fine['FineTime']); ?>
                    		</div>
	                    	<div class="col-sm-4 text-right">
                        		<?=StringOutDB($r_Fine['VehiclePlate']);?>
                        		<i class="<?=$aVehicleTypeId[$r_Fine['VehicleTypeId']];?>" style="color:#337AB7;"></i>
                    		</div>
                		</div>
                    	<div class="table_caption_H col-sm-4" style="white-space: nowrap;overflow: hidden;text-overflow: ellipsis;">
    						<?=StringOutDB((! empty($r_Fine['CompanyName']) ? $r_Fine['CompanyName'] . ' ' : '') . $r_Fine['Surname'] . ' ' . $r_Fine['Name']);?>
                		</div>
                    	<div class="table_caption_H col-sm-1">
                    		<?=! empty($r_Fine['NotificationDate']) ? DateOutDB($r_Fine['NotificationDate']) : '-';?>
                		</div>
                    	<div class="table_caption_H col-sm-1">
                    		<?=$r_Fine['Article'] . '/' . $r_Fine['Paragraph'] . '/' . $r_Fine['Letter'];?>
                		</div>
                    	<div class="table_caption_H col-sm-1">
                    		<?php if (! empty($r_Fine['CommunicationDate'])): ?>
                    			<i class="fa fa-address-card-o fa-fw tooltip-r" data-container="body" data-toggle="tooltip" data-placement="left" title="Comunicazione presentata in data <?=DateOutDB($r_Fine['CommunicationDate']);?>" style="margin-top: 0.2rem;font-size: 1.8rem;"></i>
                    		<?php else: ?>
                    			<i class="fa fa-address-card-o fa-fw tooltip-r opaque" data-container="body" data-toggle="tooltip" data-placement="left" title="Comunicazione non presentata" style="margin-top: 0.2rem;font-size: 1.8rem;"></i>
                    		<?php endif; ?>

                    		<?php if ($r_Fine['ReducedPoint'] > 0): ?>
                    			<i class="fa fa-sort-numeric-desc fa-fw tooltip-r" data-container="body" data-toggle="tooltip" data-placement="left" title="Punti decurtati in data <?=DateOutDB($r_Fine['ReducedDate']) . ' (' . $r_Fine['ReducedPoint'] . ')';?>" style="margin-top:0.2rem;font-size:1.8rem;"></i>
                    		<?php else:?>
                    			<i class="fa fa-sort-numeric-desc fa-fw tooltip-r opaque" data-container="body" data-toggle="tooltip" data-placement="left" title="Decurtazione punti assente" style="margin-top: 0.2rem;font-size: 1.8rem;"></i>
                    		<?php endif;?>
                    		<?php if ($RowNumber126Bis > 0): ?>
                    			<i class="fa fa-envelope-o fa-fw tooltip-r" data-container="body" data-toggle="tooltip" data-placement="left" title="126 BIS creato in data <?=DateOutDB($r_126Bis['FineDate']);?>" style="margin-top:0.2rem;font-size:1.8rem;"></i>
                    		<?php else:?>
                    			<i class="fa fa-envelope-o fa-fw tooltip-r opaque" data-container="body" data-toggle="tooltip" data-placement="left" title="126 BIS assente" style="margin-top: 0.2rem;font-size: 1.8rem;"></i>
                    		<?php endif; ?>
                		</div>
                        <div class="table_caption_button  col-sm-1">
                        	<?php if (empty($r_Fine['CommunicationDate'])): ?> 
                        	    <!-- Il controllo su 126BisProcedure per attivar il bottone add era stato richiesto da Emanuela 
                        	    ma la comunicazione deve poter esser inserita anche se il 126 bis è stato elaborato o se il flag è stato portato a NO -->
                        		<?php //if ($r_Fine['126BisProcedure']!=0): ?>
                        			<?=ChkButton($aUserButton, 'add', '<a href="mgmt_communication_add.php' . $str_GET_Parameter . '&Id=' . $r_Fine['Id'] . '"><span data-container="body" data-toggle="tooltip" data-placement="top" title="Crea comunicazione" class="fa fa-user fa-fw tooltip-r" style="margin-top: 0.4rem;"></span></a>');?>
                        		<?php //endif; ?>
                        	<?php else : ?>
                        		<?=ChkButton($aUserButton, 'viw', '<a href="mgmt_communication_viw.php' . $str_GET_Parameter . '&Id=' . $r_Fine['Id'] . '"><span data-container="body" data-toggle="tooltip" data-placement="top" title="Visualizza" class="glyphicon glyphicon-eye-open fa-fw tooltip-r" style="margin-top: 0.4rem;"></span></a>');?>
                            	<?=ChkButton($aUserButton, 'upd', '<a href="mgmt_communication_upd.php' . $str_GET_Parameter . '&Id=' . $r_Fine['Id'] . '"><span data-container="body" data-toggle="tooltip" data-placement="top" title="Modifica" class="glyphicon glyphicon-pencil fa-fw tooltip-r" style="margin-top: 0.4rem;"></span></a>');?>
                        	<?php endif; ?>
                        </div>
            		</div>
            		<div class="clean_row HSpace4"></div>
        		<?php endif; ?>
			<?php endfor; ?>
			<?=CreatePagination(PAGE_NUMBER, $RowNumber, $page, impostaParametriUrl(array('Filter' => 1), $str_CurrentPage.$str_GET_Parameter), $strLabel);?>
    	<?php else: ?>
            <div class="table_caption_H col-sm-12 text-center">
            	Nessun record presente
            </div>
    	<?php endif; ?>
    <?php endif; ?>
</div>

<script type="text/javascript">
    $(document).ready(function () {
        $('.fa-reply').click(function() {
            var Id = $(this).data('id');
            if(confirm("Vuoi ripristinare i punti per questo verbale?")){
                $(this).html('<i class="fas fa-circle-notch fa-spin"></i>');
                window.location="mgmt_communication_act_exe.php<?=$str_GET_Parameter?>&Id="+Id;
            }

        });
        $('#search').click(function () {
            $("#printExcel, #printPdf, #search").prop('disabled', true);
            $('#search i').toggleClass('glyphicon glyphicon-search fa fa-circle-notch fa-spin');
            $('#f_search').submit();
        });
        $("#TypePlate").change(function(){
            if ($("#TypePlate").val()=='F'){
                $('#Search_Country').prop("disabled", false);
            }else{
                $('#Search_Country').prop("disabled", true);
                $('#Search_Country').val("");

            }
        });
        $("#Search_FromProtocolYear, #Search_FromProtocolId").change(function() {
            if($("#Search_FromProtocolId").val()!='' || $("#Search_ToProtocolId").val()!='')
                $("#Search_ToProtocolYear").val($("#Search_FromProtocolYear").val());
        });
        $("#Search_FromProtocolId").change(function(){
        	if($.trim($("#Search_ToProtocolId").val()) == '')
        		$("#Search_ToProtocolId").val($("#Search_FromProtocolId").val());
        });

        $("#Search_FromProtocolId").change(function(){
        	if($(this).val() != '' && $(this).val() > $("#Search_ToProtocolId").val())
        		$("#Search_ToProtocolId").val($(this).val());
        });

        $("#Search_ToProtocolId").change(function(){
        	if($(this).val() != '' && $(this).val() < $("#Search_FromProtocolId").val())
        		$("#Search_FromProtocolId").val($(this).val());
        });

        $("#Search_FromProtocolYear").change(function(){
        	if($(this).val() != '' && $(this).val() > $("#Search_ToProtocolYear").val())
        		$("#Search_ToProtocolYear").val($(this).val());
        });

        $("#Search_ToProtocolYear").change(function(){
        	if($(this).val() != '' && $(this).val() < $("#Search_FromProtocolYear").val())
        		$("#Search_FromProtocolYear").val($(this).val());
        });

        $("#printExcel, #printPdf").on('click',function(e){
            if ($(this).attr("id") == 'printExcel') $('#PrintType').val("Excel");
            else $('#PrintType').val("Pdf");
            
            $('#f_search').attr('action', 'prn_communication_exe.php');
            $('#search, #printPdf, #printExcel').prop('disabled', true);
            $(this).html('<i class="fas fa-circle-notch fa-spin"></i>');
            $('#f_search').submit();
        });

      	$(".tableRow").mouseover(function(){
      		$( this ).find( '.table_caption_H, .table_caption_button' ).css("background-color", "#cfeaf7c7");
      	});
      	$(".tableRow").mouseout(function(){
      		$( this ).find( '.table_caption_H, .table_caption_button' ).css("background-color", "");
      	});
    });
</script>

<?php
include (INC . "/footer.php");
