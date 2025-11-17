
<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(CLS . "/cls_message.php");
include(INC."/function.php");
include(INC."/header.php");
require(INC . '/menu_'.$_SESSION['UserMenuType'].'.php');

$outSa = false;
$message=new CLS_MESSAGE();
$rs_ProcessingMCTC = $rs->Select('ProcessingMCTC', "CityId='{$_SESSION['cityid']}'", 'Position');
$r_ProcessingMCTC = mysqli_fetch_array($rs_ProcessingMCTC);
checkPasswordExpiration($rs,'XXXX','MCTC VPN',$message,$str_out);
$expirationDate=date_create($r_Customer['MCTCDate']);
checkPasswordExpirationDate("MCTC Visure",$expirationDate,$message,$str_out);

if (PRODUCTION){
    $ftp_connection = false;
    $chk_inp_file = false;
    $chk_out_file = false;
    $server         = $r_ProcessingMCTC['FTP'];
    $username       = $r_ProcessingMCTC['Username'];
    $password       = $r_ProcessingMCTC['Password'];

    $path = "/";

    $conn = @ftp_connect($server);
    if (! $conn) {
        $output = shell_exec('sudo '.PERCORSO_VPN.' > /dev/null 2>/dev/null &');
        sleep(3);
    }
    $conn = @ftp_connect($server);
    if ($conn) {
        $login = @ftp_login($conn, $username, $password);
        ftp_pasv($conn, FTP_PASSIVE_MODE_MCTC);


        if ($login) {
            $ftp_connection = true;
            $message->addInfo("Connessione riuscita");
        } else
            $message->addError("Tentativo di login fallito");
    } else
        $message->addError("Tentativo di connessione fallito");
} else
    $message->addWarning("Il collegamento FTP è disponibile solo in ambiente di produzione, lettura/scrittura su cartella locale TESTVISURE_FOLDER");


$str_out .= '
        	<div class="col-sm-12" id="DIV_Progress" style="display:none;">
				<div class="table_label_H col-sm-12">AVANZAMENTO DELL\'OPERAZIONE</div>
				<div class="clean_row HSpace4"></div>
                <div class="col-sm-12 table_caption_H"  style="height:auto;text-align:center;">
                    <div class="progress" style="margin-bottom:0;">
            			<div id="progressbar" class="progress-bar progress-bar-striped progress-bar-info active" role="progressbar" style="width: 0%;" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100"></div>
            		</div>
                    <div id="DIV_Rows" class="col-sm-12">Records elaborati: <span></span></div>
                    <div id="DIV_Messages" class="col-sm-12"></div>
                </div>
			</div>
        	<div class="col-sm-12">
				<div class="table_label_H col-sm-12">CONNESSIONE FTP</div>
				<div class="clean_row HSpace4"></div>
			</div>
            <div class="col-sm-12">
                ' . $message->getMessagesString() . '
			    <div class="clean_row HSpace4"></div>
			</div>';


echo $str_out;


$str_out = '
        	<div class="col-sm-12">
				<div class="table_label_H col-sm-12">CONTROLLO RICHIESTE DA SCARICARE</div>
				<div class="clean_row HSpace4"></div>
			</div>

            ';

if(PRODUCTION){
    $file = $r_ProcessingMCTC['FileOutput'];
} else {
    $file = "test_response";
}
if (PRODUCTION){
    $check_file_exist = $path . $file;
    $contents_on_server = ftp_nlist($conn, $path);
    $fileExists = in_array($check_file_exist, $contents_on_server);
}
else {
    $fileExists = file_exists(TESTVISURE_FOLDER . "/" . $file);
}

if ($fileExists) {
    $chk_inp_file = true;
    $str_out .= '
        <div class="table_caption_H col-sm-12 alert-warning">
            File di risposta a richiesta precedente da scaricare per l\'importazione presente sul server
        </div>
        <div class="clean_row HSpace4"></div>

		<form name="f_print" id="f_print" action="mgmt_mctc_exe.php" method="post">
		<input name="action" value="import" type="hidden">

        ';

    $strButtons =
        '
        <button id="visure" progress-tick="500" onclick="progressBar_start(\'mgmt_mctc_exe.php\', this, \'action=import\')" type="button" class="btn btn-success" style="margin-top:1rem;">Scarica dati MCTC</i></button>
        <!--<input type="submit" id="sub_Button" class="btn btn-success" style="width:20rem;margin-top:1rem;" value="Scarica dati MCTC" />-->
        ';


    $str_out.= '
	<div class="col-sm-12 table_caption_H"  style="height:6rem;text-align:center;line-height:6rem;">
		'.ChkButton($aUserButton, 'act',$strButtons) .'
    </div>
	</form>';
    $outSa = true;

}else{
    $str_out .= '
        <div class="table_caption_H col-sm-12 alert-success">
            Nessuna risposta a richiesta precedente presente sul server
        </div>
         <div class="clean_row HSpace4"></div>
        ';
}







if (!$outSa){
    $str_out .='
    	<div class="row-fluid">
    	<form name="f_print" id="f_print" action="mgmt_mctc_exe.php" method="post">
        	<input type="hidden" name="P" value="mgmt_mctc.php" />
        	<div class="col-sm-12">
            	<div class="table_label_H col-sm-1"></div>
        	    <div class="table_label_H col-sm-1">Richiesta</div>
        	    <div class="table_label_H col-sm-3">Ente</div>
				<div class="table_label_H col-sm-7">Numero record</div>

				<div class="clean_row HSpace4"></div>';


    //La join su FineAnomaly è stata messa per evitare di fare richieste su atti che per qualche motivo hanno una anomalia non ancora sanata
    //visto che ci può essere una sola anomalia per atto, altrimenti se lo elaborano più volte e ci sono più anomalie va in violazione di chiave
    $table_rows = $rs->SelectQuery("
  SELECT DISTINCT COUNT(*) TotRequest, C.Title,F.CityId
  FROM Fine F
  LEFT JOIN sarida.City C on F.CityId = C.Id join ProcessingMCTC mp on mp.CityId=F.CityId
  WHERE
  (F.StatusTypeId=1 OR F.StatusTypeId=14)
  AND F.ControllerId IS NOT NULL
  AND F.CountryId='Z000'
  AND F.ProtocolYear>2018
  AND F.Id NOT IN (SELECT FineId FROM FineTrespasser) 
  AND F.Id NOT IN(SELECT FineId AS Id FROM FineAnomaly)
  AND mp.Password='{$r_ProcessingMCTC['Password']}'
  GROUP BY C.Title,F.CityId Order BY C.Title ");
    $RowNumber = mysqli_num_rows($table_rows);

    $n_ContRow =0;

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
				<input type="checkbox" name="checkbox[]" value="' . $table_row['CityId'] . '" checked />
			</div>
			<div class="table_caption_H col-sm-3">' . $table_row['CityId'] .' - '. $table_row['Title'] .'</div>
			<div class="table_caption_H col-sm-7">' . $table_row['TotRequest'] .'</div>
			<div class="clean_row HSpace4"></div>';
        }

        $strButtons =
            '
            <input type="submit" id="sub_Button" class="btn btn-success" style="width:20rem;margin-top:1rem;" value="Carica richiesta MCTC" />
            ';


        $str_out.= '
		<div class="col-sm-12 table_caption_H"  style="height:6rem;text-align:center;line-height:6rem;">
		'.ChkButton($aUserButton, 'act',$strButtons) .'
		<img src="'.IMG.'/progress.gif" style="display: none;" id="Progress"/>
		</div>
		</form>
	</div>';

    }
}


echo $str_out;
?>

<script src="<?= JS ?>/progressbar.js" type="text/javascript"></script>

<script type="text/javascript">


    $('#visure').on('click', function() {
        $('#DIV_Progress').show()
    });

    $('#visure').on('progressDone', function(e, data){
        $('#progressbar').removeClass('progress-bar-info active');
        $('#progressbar').addClass('progress-bar-success');
        $('#DIV_Messages').html(data.Messaggio);
    });
    $('#visure').on('progressFail', function(e, data){
        $('#progressbar').removeClass('progress-bar-info active');
        $('#progressbar').addClass('progress-bar-danger');
        $('#DIV_Messages').html(data.responseText);
    });
    $('#visure').on('progressGet', function(e, data){
        $('#DIV_Rows span').html(data.Contati + ' / ' + data.Totali);
    });


    $(document).ready(function () {

        $('#sub_Button').click(function() {

            $('#f_print').submit();
        });

    });
</script>


<?php
include(INC."/footer.php");
