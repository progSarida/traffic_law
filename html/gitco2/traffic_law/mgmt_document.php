<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
require_once(CLS."/cls_view.php");
include(INC."/function.php");
include(INC."/header.php");
require(INC."/initialization.php");
require(INC."/menu_{$_SESSION['UserMenuType']}.php");

$PageTitle = CheckValue('PageTitle', 's');

$str_Where = '1=1';
$str_Where .= " AND CityId='".$_SESSION['cityid']."' AND ProtocolYear=".$_SESSION['year'];

//*******************Gestione Regolamento*******************
$RuleTypeId = $_SESSION['ruletypeid'];
$RuleTypeTitle = $_SESSION['ruletypetitle'];
$str_Where .= " AND RuleTypeId = $RuleTypeId";
//**********************************************************

if ($Search_FromProtocolId != '')
    $str_Where .= " AND ProtocolId  >= '" . $Search_FromProtocolId . "'";
if ($Search_ToProtocolId != '')
    $str_Where .= " AND ProtocolId  <= '" . $Search_ToProtocolId . "'";
if ($Search_Id > 0)
    $str_Where .= " AND FineId  = $Search_Id";
if ($s_TypePlate != ''){
    if ($s_TypePlate == "N") {
        $str_Where .= " AND VehicleCountryId='Z000'";
    } else {
        $str_Where .= " AND VehicleCountryId!='Z000'";
    }
}
if($Search_Plate != "")
    $str_Where .= " AND VehiclePlate LIKE '%".addslashes($Search_Plate)."%' ";
if($Search_Ref != "")
    $str_Where .= " AND Code LIKE '".addslashes($Search_Ref)."%'";
if($Search_Trespasser != '')
    $str_Where .= " AND CONCAT_WS(' ',CompanyName,Surname,Name) like '%{$Search_Trespasser}%'";
if($Search_Violation>0)
    $str_Where .= " AND ViolationTypeId=".$Search_Violation;

$viw_Mgmt_FineDocumentation = new CLS_VIEW(MGMT_FINEDOCUMENTATION);
$str_Query = $viw_Mgmt_FineDocumentation->generateSelect($str_Where, null, 'ProtocolId DESC');
$rs_Fine = $rs->SelectQuery($str_Query);
$RowNumber = mysqli_num_rows($rs_Fine);
mysqli_data_seek($rs_Fine, $pagelimit);

echo $str_out;
?>

<div class="row-fluid">
    <form id="f_Search" action="mgmt_document.php" method="post">
    	<input type="hidden" name="PageTitle" value="<?=$PageTitle;?>">
    
        <div class="col-sm-11" style="height:4.5rem;">
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
                Nazionalità
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <select class="form-control" name="TypePlate" id="TypePlate">
                    <option></option>
                    <option value="N"<?= $s_SelPlateN; ?>>Nazionali</option>
                    <option value="F"<?= $s_SelPlateF; ?>>Estere</option>								
                </select>
            </div>
            <div class="col-sm-1 BoxRowLabel">
                Targa
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <input class="form-control frm_field_string" name="Search_Plate" type="text" value="<?= $Search_Plate; ?>">
            </div>          
            <div class="col-sm-1 BoxRowLabel">
                Riferimento
            </div>
            <div class="col-sm-2 BoxRowCaption">
                <input class="form-control frm_field_string" name="Search_Ref" type="text" value="<?= $Search_Ref; ?>">
            </div>  
            <div class="col-sm-1 BoxRowLabel"></div>                                                       

            <div class="clean_row HSpace4"></div>

            <div class="col-sm-1 BoxRowLabel">
                ID
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <input name="Search_Id" type="text" class="form-control frm_field_string" value="<?= $Search_Id; ?>">
            </div>
            <div class="col-sm-2 BoxRowLabel">
                Tragressore
            </div>
            <div class="col-sm-4 BoxRowCaption">
                <input name="Search_Trespasser" type="text" class="form-control frm_field_string" value="<?= $Search_Trespasser; ?>">
            </div>
            <div class="col-sm-1 BoxRowLabel">
                Violazione
            </div>
            <div class="col-sm-2 BoxRowCaption">
                <?= CreateSelect("ViolationType","1=1","Id","Search_Violation","Id","Title",$Search_Violation,false); ?>
            </div> 
            <div class="col-sm-1 BoxRowLabel"></div>
        </div>
        <div class="col-sm-1 BoxRowFilterButton" style="height:4.5rem">
            <button type="submit" data-toggle="tooltip" data-container="body" data-placement="top" title="Cerca" class="tooltip-r col-sm-4 btn btn-primary" id="search" name="search" style="font-size:3rem;padding:0;margin:0;width:100%;height:100%">
                <i class="glyphicon glyphicon-search"></i>
            </button>
        </div>
    </form>
    
    <div class="clean_row HSpace4"></div>
    
    <div class="col-sm-12">
    	<div class="table_label_H col-sm-1">ID</div>
		<div class="table_label_H col-sm-1">Cron</div>
		<div class="table_label_H col-sm-2">Ref</div>
		<div class="table_label_H col-sm-1">Data</div>
		<div class="table_label_H col-sm-1">Ora</div>
		<div class="table_label_H col-sm-1">Targa</div>
		<div class="table_label_H col-sm-3">Trasgressore</div>
		<div class="table_label_H col-sm-1">Documenti</div>
        <div class="table_label_H col-sm-1">Azioni</div>
        <div class="clean_row HSpace4"></div>

        <?php if ($RowNumber > 0):?>
        	<?php $pos = ($page-1) * PAGE_NUMBER; ?>
			<?php for ($i = 0; $i < PAGE_NUMBER; $i ++):?>
				<?php $r_Fine = mysqli_fetch_assoc($rs_Fine); ?>
				<?php if (!empty($r_Fine)): ?>
					<?php
                    if($r_Fine['KindSendDate'] != ''){
                       $FineIcon = '<i style="font-size:1.3rem; margin-right:0.5rem;" class="fa fa-files-o tooltip-r" data-container="body" data-toggle="tooltip" data-placement="top" title="Invito in AG"></i>';
                    } else $FineIcon = $a_FineTypeId[$r_Fine['FineTypeId']];
    					
                    $s_FineDocumentation = 'SELECT Id FROM FineDocumentation WHERE FineId = ' . $r_Fine['FineId'];
                    $s_FinePresentation = 'SELECT Id FROM FinePresentation WHERE FineId = ' . $r_Fine['FineId'];
                    $s_FineDispute = 'SELECT Id FROM DisputeDocumentation WHERE FineId = ' . $r_Fine['FineId'];
                    $rs_Documents = $rs->SelectQuery("$s_FineDocumentation UNION $s_FinePresentation UNION $s_FineDispute");
                    $n_DocumentsNumber = mysqli_num_rows($rs_Documents);
					?>
    	            <div class="tableRow">
                        <div class="table_caption_H col-sm-1"><?= $FineIcon.$r_Fine['FineId']; ?></div>
            			<div class="table_caption_H col-sm-1"><?= $r_Fine['ProtocolId'].' / '.$r_Fine['ProtocolYear']; ?></div>
            			<div class="table_caption_H col-sm-2"><?= $r_Fine['Code']; ?></div>
                    	<div class="table_caption_H col-sm-1"><?= DateOutDB($r_Fine['FineDate']); ?></div>
                    	<div class="table_caption_H col-sm-1"><?= $r_Fine['FineTime']; ?></div>
                    	<div class="table_caption_H col-sm-1">
                    		<?= StringOutDB($r_Fine['VehiclePlate']); ?>
                    		<i class="<?= $aVehicleTypeId[$r_Fine['VehicleTypeId']]; ?>" style="color:#337AB7;position:absolute;right:2px;top:2px;"></i>
                		</div>
                		<div class="table_caption_H col-sm-3"><?= StringOutDB(isset($r_Fine['CompanyName']) ? $r_Fine['CompanyName'] : $r_Fine['Surname'].' '.$r_Fine['Name']); ?></div>
                        <div class="table_caption_H col-sm-1 text-center"><?= $n_DocumentsNumber; ?></div>
                        <div class="table_caption_button col-sm-1">
                        	<?= ChkButton($aUserButton, 'viw','<a href="mgmt_document_viw.php'.$str_GET_Parameter.'&Id='.$r_Fine['FineId'].'&pos='.$pos.'"><span data-toggle="tooltip" data-placement="top" title="Visualizza" class="tooltip-r glyphicon glyphicon-eye-open" style="position:absolute;left:5px;top:5px;"></span></a>'); ?>
                        	<?= ChkButton($aUserButton, 'upd','<a href="mgmt_document_upd.php'.$str_GET_Parameter.'&Id='.$r_Fine['FineId'].'&pos='.$pos.'"><span data-toggle="tooltip" data-placement="top" title="Modifica" class="tooltip-r glyphicon glyphicon-pencil" style="position:absolute;left:25px;top:5px;"></span></a>'); ?>
                        	<?= ChkButton($aUserButton, 'add','<a href="mgmt_document_add.php'.$str_GET_Parameter.'&Id='.$r_Fine['FineId'].'"><span data-toggle="tooltip" data-placement="top" title="Carica" class="tooltip-r fas fa-file-upload" style="font-size:1.6rem;position:absolute;left:45px;top:2px;"></span></a>'); ?>
                        </div>
                    </div>
                    <div class="clean_row HSpace4"></div>
                    <?php $pos++; ?>
				<?php endif; ?>
			<?php endfor; ?>
			<?= CreatePagination(PAGE_NUMBER, $RowNumber, $page, $str_CurrentPage.$str_GET_Parameter, ''); ?>
		<?php else: ?>
            <div class="table_caption_H col-sm-12 text-center">
            	Nessun record presente
            </div>
		<?php endif; ?>
    </div>
</div>

<script type="text/javascript">

    $(document).ready(function () {
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
