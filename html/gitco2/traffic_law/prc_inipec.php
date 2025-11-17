<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
include(INC."/header.php");
require(INC . '/menu_'.$_SESSION['UserMenuType'].'.php');

$str_out .= '	
        	<div class="col-sm-12">
				<div class="table_label_H col-sm-12">CONTROLLO RICHIESTE DA SCARICARE</div>
				<div class="clean_row HSpace4"></div>	
			</div>';

$rs= new CLS_DB();
$PaymentId = 'e';
$TipoPersona='';
if(isset($_POST['TipoPersona']))
$TipoPersona =$_POST['TipoPersona'];
$str_fisica='';
$str_ditta='';
$str_entrambe='';
if($TipoPersona=='') $TipoPersona = 'e';
if($TipoPersona=='f')
    $str_fisica = " SELECTED ";
else if ($TipoPersona=='d')
    $str_ditta = " SELECTED ";
else
    $str_entrambe=" SELECTED ";

  //  echo $TipoPersona;
$str_out .= '
        <div class="col-sm-12">

                <div class="col-sm-12">
                    <div class="col-sm-6 BoxRowLabel">
                        Tipo soggetto
                    </div>
                    <div class="col-sm-6 BoxRowCaption">
                                        <form id="f_type" action="'.$str_CurrentPage.'" method="post">

                        <select class="form-control" id="TipoPersona" name="TipoPersona" onchange="this.form.submit()">
                            <option value="f" '.$str_fisica.'>FISICA</option>
                            <option value="d" '.$str_ditta.'>DITTA</option>
                            <option value="e" '.$str_entrambe.'>ENTRAMBE</option>
                        </select>
                                    </form>

                    </div>
                </div>			
        <div class="clean_row HSpace4"></div>';

$rs_password=$rs->Select("IniPecProcessing","CityId='{$_SESSION['cityid']}'");
$userName='';

if($inipecProcessing=mysqli_fetch_array($rs_password))
    $userName=$inipecProcessing['UserName'];

$rs_inipecstatus=$rs->Select('richieste_servizi_esterni', "rse_tipo=4 AND rse_esito IS NULL and rse_utente_servizio='$userName'");
$r_inipecstatus = mysqli_fetch_array($rs_inipecstatus);

if (!empty($r_inipecstatus)) {
    $strButtons =
    '   <button type="submit" id="download_Inipec" class="btn btn-success" style="width:20rem;margin-top:1rem;">Scarica dati INIPEC</button>
        ';
	$str_out .= '
        <div class="table_caption_H col-sm-12 alert-warning">
            Richiesta precedente da scaricare presente sul server
        </div>
        <div class="clean_row HSpace4"></div>
		<form name="f_inipec_download" id="f_inipec_download" action="prc_inipec_download_exe.php" method="post">
		<input name="IdRichiesta" value="'.$r_inipecstatus['rse_id_richiesta'].'" type="hidden">
	<div class="col-sm-12 table_caption_H"  style="height:6rem;text-align:center;line-height:6rem;">
		'.ChkButton($aUserButton, 'act',$strButtons) .'
    </div>
	</form>';
}else{
    $str_out .= '
        <div class="table_caption_H col-sm-12 alert-success">
            Nessuna richiesta precedente da scaricare
        </div>
         <div class="clean_row HSpace4"></div>';
    
    $str_out .='
	<form name="f_inipec_upload" id="f_inipec_upload" action="prc_inipec_exe.php" method="post">
		<input type="hidden" name="P" value="prc_inipec.php" />
		<div class="col-sm-12">
			<div class="table_label_H col-sm-1"></div>
			<div class="table_label_H col-sm-1">Richiesta</div>
			<div class="table_label_H col-sm-3">Ente</div>
			<div class="table_label_H col-sm-7">Numero record</div>
			<div class="clean_row HSpace4"></div>';
    $n_ContRow =0;
    $queryString="SELECT c.CityId as CityId,c.ManagerName as Title,count(ft.TrespasserId) as TotRequest
FROM Fine f JOIN
FineTrespasser ft on (f.Id=ft.FineId) join
Trespasser t ON (ft.TrespasserId =t.Id) join Customer c on (c.CityId=f.CityId) join IniPecProcessing ipp on (ipp.CityId=c.CityId)
where ipp.UserName='$userName' and f.ProtocolYear ='{$_SESSION['year']}' and
f.StatusTypeId  in (10,14) and(
ft.TrespasserTypeId in (1,11)or
( ft.TrespasserTypeId  in(2,3,15,16) and
ft.FineCreateDate is null))
and (InipecLoaded is null or datediff('".date('Y-m-d')."',t.InipecLoaded)>7)";
    if($TipoPersona=='f')
        $queryString.=" and not(genre='D')";
        else if($TipoPersona=='d')
            $queryString.=" and genre='D'";
            
            $queryString.= " group by CityId,ManagerName";
            $table_rows=$rs->SelectQuery($queryString);
            $RowNumber=mysqli_num_rows($table_rows);
            if ($RowNumber == 0) {
                $str_out.=
                '<div class="table_caption_H col-sm-12">
			Nessuna richiesta da inviare
		</div>';
            } else {
                while ($table_row = mysqli_fetch_array($table_rows)) {
                    $n_ContRow++;
                    $str_out.= '
		<div class="table_caption_H col-sm-1">' . $n_ContRow .'</div>
		<div class="table_caption_button col-sm-1" style="text-align:center">
			<input type="checkbox" name="checkbox[]" value="' . $table_row['CityId'] . '"/>
		</div>
		<div class="table_caption_H col-sm-3">' . $table_row['CityId'] .' - '. $table_row['Title'] .'</div>
		<div class="table_caption_H col-sm-7">' . $table_row['TotRequest'] .'</div>
		<div class="clean_row HSpace4"></div>';
                }
                $strButtons =
                '			<input type="hidden" name="TipoPersona" value="' . $TipoPersona . '"/>
                    
    <button disabled type="submit" id="upload_Inipec" class="btn btn-success" style="width:20rem;margin-top:1rem;">Carica richiesta PEC</button>';
                $str_out.= '
	<div class="col-sm-12 table_caption_H"  style="height:6rem;text-align:center;line-height:6rem;">
	'.ChkButton($aUserButton, 'act',$strButtons) .'
	</div>
</form>';
            }
}


$str_out .= '</div>';

echo $str_out;
?>

<script type="text/javascript">
$(document).ready(function () {
	$('#f_inipec_download').submit(function(e){
		$('#download_Inipec').html('<i class="fas fa-circle-notch fa-spin" style="font-size:2rem;">');
		$('#upload_Inipec, #download_Inipec').prop('disabled', true);
	});
	$('#f_inipec_upload').submit(function(e){
		$('#upload_Inipec').html('<i class="fas fa-circle-notch fa-spin" style="font-size:2rem;">');
		$('#download_Inipec, #upload_Inipec').prop('disabled', true);
	});
	
	$('input[name=checkbox\\[\\]]').change(function() {
        $("#f_inipec_upload").trigger( "check" );
    });
	
    $("#f_inipec_upload").on('check', function(){
    console.log(this);
    	if ($('input[name=checkbox\\[\\]]:checked').length > 0)
    		$('#upload_Inipec').prop('disabled', false);
    	else
    		$('#upload_Inipec').prop('disabled', true);
    });
});
</script>

<?php
include(INC."/footer.php");