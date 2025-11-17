<?php
require_once("_path.php");
require_once(INC . "/parameter.php");
require_once(CLS . "/cls_db.php");
require_once(INC . "/function.php");
require_once(INC . "/header.php");
require_once(INC . '/menu_' . $_SESSION['UserMenuType'] . '.php');

global $rs;
$rs->SetCharset('utf8');

$PageTitle = CheckValue('PageTitle','s') ?: '/';
$Filter = CheckValue('Filter', 'n');

if($Filter > 0){
    $strOrder = "CompanyName, Surname, Name";
    $str_Where = "1=1 AND CityId='".$_SESSION['cityid']."' AND ProtocolYear=".$_SESSION['year'];
    
    if($Search_Status>0){
        if($Search_Status==15){
            $str_Where .= " AND StatusTypeId>=13 AND ProtocolId>0";
            $strOrder = "ProtocolId";
        }else{
            $str_Where .= " AND StatusTypeId<13";
        }
    }else $Search_Status=1;
    
    if ($Search_FromFineDate != "") {
        $str_Where .= " AND FineDate>='".DateInDB($Search_FromFineDate)."'";
    }
    if ($Search_ToFineDate != "") {
        $str_Where .= " AND FineDate<='".DateInDB($Search_ToFineDate)."'";
    }
    if ($Search_FromProtocolId != "") {
        $str_Where .= " AND ProtocolId>=$Search_FromProtocolId";
    }
    if ($Search_ToProtocolId != "") {
        $str_Where .= " AND ProtocolId<=$Search_ToProtocolId";
    }
    if ($Search_Locality != "") {
        $str_Where .= " AND Locality='$Search_Locality'";
    }
    switch($s_TypePlate){
        case 'F' : $str_Where .= " AND CountryId != 'Z000'"; break;
        case 'N' : $str_Where .= " AND CountryId = 'Z000'"; break;
    }
    if($Search_Trespasser != ""){
        $str_Where .= " AND CONCAT_WS(' ',CompanyName,Surname,Name) LIKE '%{$Search_Trespasser}%'";
    }
    if($Search_HasTaxCode > 0){
        $str_Where .= " AND COALESCE(TaxCode, '') = '' AND COALESCE(VatCode, '') = ''";
    }
    
    $a_Results = $rs->getResults($rs->Select('V_FineTrespasserList',$str_Where, $strOrder));
}

echo $str_out;
?>
<div class="row-fluid">
	<form id="f_prn_trespasser" action="prn_trespasser.php" method="post" autocomplete="off">
		<input type="hidden" name="PageTitle" value="<?= $PageTitle; ?>">
		<input type="hidden" name="Filter" value="1">
		
		<div class="col-sm-11">
            <div class="col-sm-1 BoxRowLabel">
                Nazionalità
            </div>
            <div class="col-sm-1 BoxRowCaption">
				<?= CreateArraySelect(array('N' => 'Nazionali', 'F' => 'Esteri'), true, 'TypePlate', 'TypePlate', $s_TypePlate, false, null, null, ''); ?>
            </div> 
            <div class="col-sm-1 BoxRowLabel">
                Da data
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <input class="form-control frm_field_date" type="text" value="<?= $Search_FromFineDate; ?>" name="Search_FromFineDate">
            </div>
            <div class="col-sm-1 BoxRowLabel">
                A data
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <input class="form-control frm_field_date" type="text" value="<?= $Search_ToFineDate; ?>" name="Search_ToFineDate">
            </div>
            <div class="col-sm-1 BoxRowLabel">
                Nominativo
            </div>
            <div class="col-sm-2 BoxRowCaption">
                <input class="form-control frm_field_string" name="Search_Trespasser" type="text" value="<?= $Search_Trespasser; ?>">
            </div>   
            <div class="col-sm-2 BoxRowLabel">
                Solo nominativi senza C.F./P.IVA
            </div>   
            <div class="col-sm-1 BoxRowCaption">
                 <input name="Search_HasTaxCode" id="Search_HasTaxCode" type="checkbox" value="1" <?= ChkCheckButton($Search_HasTaxCode); ?>/>
            </div>        
                          
            <div class="clean_row HSpace4"></div>                 
                   
            <div class="col-sm-1 BoxRowLabel">
                Pratica
            </div>
            <div class="col-sm-3 BoxRowCaption">
                <?= CreateSelect("StatusType","Id=1 OR Id=15","Id","Search_Status","Id","Title",$Search_Status,true); ?>
            </div>
            <div class="col-sm-1 BoxRowLabel">
			    Da cron 
            </div>
            <div class="col-sm-1 BoxRowCaption">			
			    <input class="form-control frm_field_numeric" type="text" value="<?=  $Search_FromProtocolId; ?>" id="Search_FromProtocolId" name="Search_FromProtocolId" disabled>
			</div>
            <div class="col-sm-1 BoxRowLabel">		
			    A cron 
            </div>
            <div class="col-sm-1 BoxRowCaption">			
			    <input class="form-control frm_field_numeric" type="text" value="<?=  $Search_ToProtocolId; ?>" id="Search_ToProtocolId" name="Search_ToProtocolId" disabled>
		    </div>            
            <div class="col-sm-1 BoxRowLabel">
                Località
            </div>
            <div class="col-sm-3 BoxRowCaption">
            	<?= CreateSelect(MAIN_DB.'.City', "UnionId='".$_SESSION['cityid']."'", "Title", "Search_Locality", "Id", "Title", $Search_Locality, false); ?>
        	</div>
        </div>
        <div class="col-sm-1 BoxRowFilterButton" style="height:4.5rem;">
        	<button type="submit" data-toggle="tooltip" data-container="body" data-placement="top" title="Cerca" class="tooltip-r btn btn-primary" id="search" name="search" style="margin-top:0;width:100%;height:100%;padding:0;"><i class="glyphicon glyphicon-search" style="font-size:2rem;"></i></button>
        </div>

    	<div class="clean_row HSpace4"></div>
    	
		<div class="table_label_H col-sm-3">Nominativo</div>
		<div class="table_label_H col-sm-1">Data</div>
		<div class="table_label_H col-sm-1">Ora</div>
		<div class="table_label_H col-sm-2">Targa</div>
		<div class="table_label_H col-sm-4">Localita</div>
		<div class="table_label_H col-sm-1">Articolo</div>
        
        <div class="clean_row HSpace4"></div>

		<?php if($Filter <= 0): ?>
        	<div class="table_caption_H col-sm-12 text-center">
				Inserire i criteri di ricerca.
			</div>
        <?php else: ?>
        	<?php if(!empty($a_Results)): ?>
        		<?php foreach($a_Results as $result): ?>
        			<div class="tableRow">
            			<div class="table_caption_H col-sm-3"><?= $result['CompanyName'] .' '.$result['Surname'].' '.$result['Name']; ?></div>
            			<div class="table_caption_H col-sm-1"><?= DateOutDB($result['FineDate']); ?></div>
            			<div class="table_caption_H col-sm-1"><?= TimeOutDB($result['FineTime']); ?></div>
            			<div class="table_caption_H col-sm-2"><?= $result['VehiclePlate']; ?></div>
            			<div class="table_caption_H col-sm-4"><?= $result['Address']; ?></div>
                        <div class="table_caption_H col-sm-1"><?= $result['Article'] .' ' . $result['Paragraph'] .' ' . $result['Letter']; ?></div>
        			</div>
        			
        			<div class="clean_row HSpace4"></div>
        		<?php endforeach; ?>
        		
				<div class="col-sm-12 table_caption_H" style="height:6rem;text-align:center;line-height:6rem;">
        			<?= ChkButton($aUserButton, 'prn', '<button data-toggle="tooltip" data-container="body" data-placement="top" title="Stampa pdf prospetto" type="submit" class="tooltip-r btn btn-warning" id="prn_button" style="margin-top:1rem;">Stampa <i class="fa fa-file-pdf"></i></button>'); ?>   		 		
        		</div>
        	<?php else: ?>
    	        <div class="table_caption_H col-sm-12 text-center">
                	Nessun record presente.
                </div>
        	<?php endif; ?>
        <?php endif; ?>

	</form>
</div>

<script type="text/javascript">
	$(document).ready(function () {
        $('#prn_button').on("click", function () {
    		$('#f_prn_trespasser').attr('action', 'prn_trespasser_exe.php');
        	$(this).html(`
        		<i class="fas fa-circle-notch fa-spin" style="font-size:2rem"></i>
    		`);
        });
        
        $('#f_prn_trespasser').on('submit', function () {
        	$('#search, #prn_button').prop('disabled', true);
        });

        $('#f_prn_trespasser').on('keyup keypress', function(e) {
            var keyCode = e.keyCode || e.which;
            if (keyCode === 13) {
                $("#f_prn_trespasser").submit();
            }
        });

        $("#Search_Status").change(function(){
            if ($("#Search_Status").val()==15){
                $('#Search_FromProtocolId').prop("disabled", false);
                $('#Search_ToProtocolId').prop("disabled", false);

            }else{
                $('#Search_FromProtocolId').val('');
                $('#Search_ToProtocolId').val('');

                $('#Search_FromProtocolId').prop("disabled", true);
                $('#Search_ToProtocolId').prop("disabled", true);
            }
        });

		$("#Search_Status").change();
	});
</script>
<?php
require_once(INC."/footer.php");
