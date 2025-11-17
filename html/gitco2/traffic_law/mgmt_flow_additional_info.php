<?php
require_once("_path.php");
require_once(INC."/parameter.php");
require_once(CLS."/cls_db.php");
require_once(CLS."/cls_view.php");
require_once(INC."/function.php");
require_once(INC."/header.php");
require_once(INC."/menu_{$_SESSION['UserMenuType']}.php");

$FlowId = CheckValue('FlowId','n');

$rs_Flow = $rs->Select('Flow', "Id=$FlowId");
$r_Flow = mysqli_fetch_assoc($rs_Flow);

$cls_view = new CLS_VIEW(MGMT_FLOW_ADDITIONAL_INFO);
$rs_Fine = $rs->SelectQuery($cls_view->generateSelect("FH.FlowId=$FlowId", null, 'F.ProtocolId ASC'));
$RowNumber = mysqli_num_rows($rs_Fine);

echo $str_out;
?>

<div class="row-fluid">
	<form id="f_flow_additional_info" action="mgmt_flow_additional_info_exe.php" method="get" autocomplete="off">
		<input type="hidden" name="FlowId" value="<?= $FlowId; ?>">
		<input type="hidden" name="Filters" value="<?= $str_GET_Parameter; ?>">
		
        <div class="col-sm-2 BoxRowLabel">
            Flusso N.
        </div>
        <div class="col-sm-2 BoxRowCaption">      
        	<?= '('.$r_Flow['CityId'].') '.$r_Flow['Number'].'/'.$r_Flow['Year']; ?>
        </div>   
        <div class="col-sm-2 BoxRowLabel">
            Del
        </div>
        <div class="col-sm-2 BoxRowCaption">      
            <?= DateOutDB($r_Flow['CreationDate']); ?>
        </div>          	        
        <div class="col-sm-2 BoxRowLabel">
            Spedito il 
        </div>
        <div class="col-sm-2 BoxRowCaption">      
            <?= DateOutDB($r_Flow['SendDate']); ?>
        </div> 
                	    
        <div class="clean_row HSpace4"></div>
                	    
        <div class="table_label_H col-sm-1"></div>
        <div class="table_label_H col-sm-2">Cronologico</div>
        <div class="table_label_H col-sm-3">Destinatario</div>
        <div class="table_label_H col-sm-2">Indirizzo</div>				
        <div class="table_label_H col-sm-2">Città/Nazione</div>
        <div class="table_label_H col-sm-2">N° Raccomandata</div>
        
        <div class="clean_row HSpace4"></div>
        
    	<?php if ($RowNumber > 0): ?>
    		<?php $n_Count = 1; ?>
    		<?php while ($r_Fine = $rs->getArrayLine($rs_Fine)): 
        		//Dati del trasgressore
        		$trespassers = $rs->Select('V_Trespasser', "Id=" . $r_Fine['TrespasserId']);
        		$trespasser = mysqli_fetch_array($trespassers);
        		
        		$str_TrespasserAddress =  trim(
        		    $trespasser['Address'] ." ".
        		    $trespasser['StreetNumber'] ." ".
        		    $trespasser['Ladder'] ." ".
        		    $trespasser['Indoor'] ." ".
        		    $trespasser['Plan']
        		    );
        		
        		$rs_City = $rs->Select("sarida.City","Id = '".$r_Fine['CityId']."'");
        		$r_City = mysqli_fetch_assoc($rs_City);
        		
        		$rs_Country = $rs->Select("CountryZone","CountryId = '".$r_Fine['CountryId']."'");
        		$r_Country = mysqli_fetch_assoc($rs_Country);
        		
        		$Cron = $r_Fine['ProtocolId'] .'/'. $r_Fine['ProtocolYear'];
        		$Trespasser = $trespasser['TrespasserFullName'];
        		$City = $r_City['Title'];
        		$Country = ((!isset($r_Country['Title'])) && ($r_Fine['CountryId'] == 'Z000')) ? 'Italia' : (isset($r_Country['Title']) ? $r_Country['Title'] : "");
        		$CityCountry = $City."/".$Country;
        		$LetterNumber = isset($r_Fine['LetterNumber']) ? isset($r_Fine['LetterNumber']) : "";
        		
    		?>
			<div class="tableRow_<?=$n_Count?>">
				<div style="display:none" id="fineid_<?=$n_Count?>"><?=$r_Fine['FineId']?></div>
				<div style="display:none" id="trespasserid_<?=$n_Count?>"><?=$r_Fine['TrespasserId']?></div>
    			<div class="table_caption_H col-sm-1" id="prog">
    				<?= $n_Count; ?>
				</div>
                <div class="table_caption_H col-sm-2" id="cron_<?=$n_Count?>">
                    <?= $Cron ?>
                </div>
                <div class="table_caption_H col-sm-3" id="fullname_<?=$n_Count?>">
                    <?= $Trespasser ?>
                </div>
                <div class="table_caption_H col-sm-2" id="address_<?=$n_Count?>">
                    <?= $str_TrespasserAddress ?>
                </div>
                <div class="table_caption_H col-sm-2" id="city_<?=$n_Count?>">
                    <?= $CityCountry ?>
                </div>
                <div class="table_caption_H col-sm-2" id="letter_<?=$n_Count?>">
                    <input type="text" class="form-control" name="LetterNumber" style="margin-left:10%;width:80%;height:2rem;" value="<?= $r_Fine['LetterNumber'] ?>" maxlength="20">
                </div>  
			</div>
			
			<div class="clean_row HSpace4"></div>
			
			<?php $n_Count++;?>
    		<?php endwhile; ?>
    		
            <div class="table_label_H HSpace4 text-center" style="height:8rem;">
                <button type="button" class="btn btn-success" id="save" style="margin-top:2rem;width:10rem;">
                	Salva
            	</button>
                <button type="button" class="btn btn-default" id="back" style="margin-top:2rem;width:10rem;">
                	Indietro
            	</button>
            </div>
    	<?php else: ?>
	        <div class="table_caption_H col-sm-12 text-center">
            	Nessun record presente.
            </div>
    	<?php endif; ?>
	</form>
</div>

<script type="text/javascript">
    $('document').ready(function () {
        $('#back').click(function () {
            window.location="<?= impostaParametriUrl(array('Filter' => 1), 'mgmt_flow.php'.$str_GET_Parameter); ?>";
        });
    
    	$('#save').on('click', ()=>{
    		if(confirm("Si stanno per salvare/modificare i codici delle raccomandate. Continuare?")){
        			 let contatore = '<?=$n_Count?>';
        			 let jsonBody = compilaJSON(contatore);
        			 
        			 $.ajax({
        			 	url:"ajax/ajx_flow_additional_info.php",
        			 	type:"POST",
        			 	dataType:"json",
        			 	cache:false,
        			 	data:{"Body":jsonBody},
        			 	success: function(data){alert("Operazione avvenuta con successo")},
        			 	error: function(data){console.log(data);alert("Errore nell'esecuzione dell'operazione")}
        			 });
    			 }
    		else {
            	e.preventDefault();
            	return false;
    			}
    	});
    });
    
    function compilaJSON(counter){
    	let jsonBuilt = []; 
    	
    	for(var i = 1; i < counter; i++)
    		{	
    			let fineid = $('#fineid_'+i).text();
    			let trespasserid = $('#trespasserid_'+i).text();
    			let letter = $('#letter_'+i).children('input').val();
    			jsonBuilt.push({"fineid":fineid,"trespasserid":trespasserid,"letter":letter});
    		}
		return JSON.stringify(jsonBuilt);
	}
</script>
<?php
require_once(INC . "/footer.php");