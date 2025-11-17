<?php
$str_Notification_Fees='';

$charge_rows = $rs->Select('CustomerCharge',"CreationType=1 AND CityId='".$_SESSION['cityid']."' AND ToDate IS NULL", "Id");
$charge_row = mysqli_fetch_array($charge_rows);


$TotalChargeForeign = ($charge_row['ForeignTotalFee']>0) ? $charge_row['ForeignTotalFee'] : $charge_row['ForeignNotificationFee'] + $charge_row['ForeignResearchFee'];


$TotalChargeNational = ($charge_row['NationalTotalFee']>0) ? $charge_row['NationalTotalFee'] : $charge_row['NationalNotificationFee'] + $charge_row['NationalResearchFee'];
        	$str_Notification_Fees .= '


	        	<div class="col-sm-12">
	        	    <div class="col-sm-6 BoxRowLabel">
        				Spese notifica
					</div>
					<div class="col-sm-6 BoxRowCaption">
				    	<input type="hidden" name="TotalCharge" id="TotalCharge" value="'.$TotalChargeNational.'">
        				<span id="span_TotalCharge">'.$TotalChargeNational.'</span>
					</div>
  				</div>';
        	
?>
        	
<script type="text/javascript">

var totCharge = <?= $TotalChargeNational ?>;

$('document').ready(function(){
    $('#CountryId').on('change', function(){
    	var id = $("#CountryId").val();
    	$('#VehicleCountry').val($( "#CountryId option:selected" ).text());

    	if(id=='Z000'){
         $('#TotalCharge').val('<?= $TotalChargeNational ?>');
         $('#span_TotalCharge').html('<?= $TotalChargeNational ?>');
         totCharge = <?= $TotalChargeNational ?>;
        
        }else{
         $('#TotalCharge').val('<?= $TotalChargeForeign ?>');
         $('#span_TotalCharge').html('<?= $TotalChargeForeign ?>');
         totCharge = <?= $TotalChargeForeign ?>;
        }
    });
});
    
</script>