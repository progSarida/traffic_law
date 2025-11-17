<?php
require_once("_path.php");
require_once(INC."/parameter.php");
require_once(CLS."/cls_db.php");
require_once(INC."/function.php");
require_once(INC."/header.php");
require_once(PGFN."/fn_cron_processaFirmati.php");

require_once(INC . '/menu_'.$_SESSION['UserMenuType'].'.php');

$FineId = CheckValue("FineId", "n");
$Filter = CheckValue("Filter", "n");
$Messaggio = "";
$FineIdPreinserimento = -1;

if($Filter)
    {
    $rs_Fine = $rs->Select("Fine","Id = $FineId AND CityId = '".$_SESSION['cityid']."'");
    $r_Fine = $rs->getArrayLine($rs_Fine);
    //Pre-condizioni per la creazione del preinserimento
    if(mysqli_num_rows($rs_Fine) > 0){
        $rs_Preinserimenti = $rs->Select("Fine","Code = '".$r_Fine['Code']."' AND StatusTypeId =10 AND CityId = '".$_SESSION['cityid']."'");
        if(mysqli_num_rows($rs_Preinserimenti) == 0){
            //echo json_encode($r_Fine);
            $FineIdPreinserimento = generateFineAfterPECFailure($rs,$FineId);
            $Messaggio = "Il FineId del preinserimento appena inserito è: ".$FineIdPreinserimento;
            }
        else{
            $Messaggio = "E' già presente un preinserimento associato a questo FineId";        
            }
        }
    else 
        $Messaggio = "Il FineId inserito è inesistente";
    }
?>
<div class="col-sm-12 BoxRowLabel">
<b>Funzione di creazione nuovo preinserimento a partire da un verbale esistente</b>
</div>
<div class="clean_row HSpace4"></div>
<form action="z_creaPreinserimenti.php">
    <div class="col-sm-12 BoxRowLabel">
        FineId
    </div>
    <div class="clean_row HSpace4"></div>
    <div class="col-sm-12 BoxRowCaption searchField">
        <input type="text" name="FineId" id="FineId" value="<?=$FineId?>">
    </div>
    <div class="clean_row HSpace4"></div>
    <div class="col-sm-1" style="margin-right:1rem;">
    		<label for="singleSearch">Esegui</label>
    		<button type="submit" data-toggle="tooltip" data-container="body" data-placement="top" title="Ricerca singola" class="tooltip-r btn btn-primary pull-left" id="singleSearch" name="SearchType" style="margin-top:0;width:100%;height:100%;" value="singleSearch"><i class="glyphicon glyphicon-play" style="font-size:2.5rem;"></i></button>
	</div>
	<div class="clean_row HSpace4"></div>
    <div>
    	<div class="col-sm-12 BoxRowLabel">
    	Risposta
    	</div>
    	<div class="clean_row HSpace4"></div>
    	<input type="hidden" name="Filter" value=1>
    	<div>
    	FineId preinserimento creato: <?php echo $Messaggio?>
    	</div>
    </div>
</form>

<script type="text/javascript">
</script>
<?php
include(INC."/footer.php");
