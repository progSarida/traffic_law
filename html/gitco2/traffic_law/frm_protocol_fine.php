<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
include(INC."/header.php");

require(INC . '/menu_'.$_SESSION['UserMenuType'].'.php');


$LinkPage = curPageName();

$CurrentYear = $_SESSION['year'];

$aVehicleTypeId = array("","fa fa-car","fa fa-motorcycle","fa fa-desktop","fa fa-truck","fa fa-bus","fa fa-rocket","fa fa-desktop","fa fa-bus","fa fa-bicycle", "fa fa-desktop", "fa fa-desktop","fa fa-desktop");
$str_ErrorPDF = "";
$rs= new CLS_DB();

$s_TypePlate= CheckValue('TypePlate','s');


$str_out ='
	<div class="container-fluid">
    	<div class="row-fluid">
    	<form name="f_print" id="f_print" action="frm_protocol_fine_exe.php" method="post">
        	<div class="col-sm-12">
 	   	    	<div class="table_label_H col-sm-1">Allega</div>
 	   	    	<div class="table_label_H col-sm-4">Nome file senza estensione</div>
 	   	    	<div class="table_label_H col-sm-1">File pdf</div>
				<div class="table_label_H col-sm-1">File p7m</div>
				<div class="table_label_H col-sm-1">Cron</div>
				<div class="table_label_H col-sm-1">Violazione</div>
				<div class="table_label_H col-sm-1">Targa</div>
				<div class="table_label_H col-sm-1">Data</div>
				<div class="table_add_button col-sm-1">&nbsp;</div>
				
				<div class="clean_row HSpace4"></div>';

        if(1) {
            $ftp_connection = false;
            $chk_inp_file = false;
            $server = '89.96.225.74';
            $username = 'velox';
            $password = 'Cd28+PeB';
            $folderName = $_SESSION['username'];


            $conn = @ftp_connect($server);
            if ($conn) {
                $login = @ftp_login($conn, $username, $password);
                if ($login) {
                    $ftp_connection = true;
                    $str_Connection = 'Connessione riuscita';
                    $path = "/";
//                    $contents_on_server = ftp_nlist($conn, $path);
//                    print_r($contents_on_server);
                    $path.= $folderName;
                    $origin = ftp_pwd($conn);
                    // Controllo se esiste la cartella username
                    if (@ftp_chdir($conn, $folderName)) {
                        // Se esiste torno alla cartella originale
                        ftp_chdir($conn, $origin);
                        $contents_on_server = ftp_nlist($conn, $path);

                        $a_files = array();
                        for($i=0;$i<count($contents_on_server);$i++) {
                            $stringExplode = explode("/", $contents_on_server[$i]);
                            $fileName = explode(".", $stringExplode[2]);
                            if($fileName[0]=="Thumbs")
                                continue;

                            $a_files[$fileName[0]][$fileName[count($fileName) - 1]] = $stringExplode[2];
                        }
                        $str_ErrorPDF = "";
                        foreach ($a_files as $key=>$file) {

                            if (isset($file['pdf'])) {
                                $pdf = $file['pdf'];

                                $rs_row = $rs->Select('DocumentationProtocol',"UserId=".$_SESSION['userid']." AND CityId='".$_SESSION['cityid']."' AND Documentation='".$pdf."'");
                                $n_row =  mysqli_num_rows($rs_row);
                                if($n_row!=1){

                                    $str_ErrorPDF .= '
        	                        <div class="col-sm-12">
 	   	    	                        <div class="table_label_H col-sm-12 alert-danger">Problemi nella ricerca del file ' .$pdf.'</div>
 	   	    	                    </div>    
                                     ';

                                }


                                $pdfText = "<span style='color:green;'>PRESENTE</span>";
                            } else {
                                $pdfText = "<span style='color:red;'>MANCANTE</span>";
                                $pdf = "";
                            }

                            if (isset($file['p7m'])) {
                                $p7m = $file['p7m'];
                                $p7mText = "<span style='color:green;'>PRESENTE</span>";
                                $checkedBox = "checked";
                            } else {
                                $p7m = "";
                                $p7mText = "<span style='color:red;'>MANCANTE</span>";
                                $checkedBox = "disabled";
                            }


                            $fineDocs = $rs->Select('FineDocumentation', "Documentation='" . $key . ".pdf'");



                            $fineDoc = mysqli_fetch_array($fineDocs);
                            $table_rows = $rs->Select('V_Fine',"Id=".$fineDoc['FineId']);





                            $table_row = mysqli_fetch_array($table_rows);

                            $str_out .= '
                                <div class="table_caption_button col-sm-1" style="text-align:center">
                                    <input type="checkbox" name="checkbox[]" value="' . $fineDoc['FineId'] . '" '.$checkedBox.' />
                                </div>';

                            $str_out .= '<div class="table_caption_H col-sm-4">' . $key . '</div>';
                            $str_out .= '<div class="table_caption_H col-sm-1">' . $pdfText . '</div>';
                            $str_out .= '<input type=hidden name="pdf[]" value="'.$pdf.'"></input>';
                            $str_out .= '<div class="table_caption_H col-sm-1">' . $p7mText . '</div>';
                            $str_out .= '<input type=hidden name="p7m[]" value="'.$p7m.'"></input>';
                            $str_out .= '<div class="table_caption_H col-sm-1">' . $table_row['ProtocolId'] . ' / ' . $table_row['ProtocolYear'] . '</div>';
                            $str_out .= '<div class="table_caption_H col-sm-1">' . $table_row['ViolationTitle'] . '</div>';
                            $str_out .= '<div class="table_caption_H col-sm-1">' . $table_row['VehiclePlate'] . '<i class="' . $aVehicleTypeId[$table_row['VehicleTypeId']] . '" style="color:#337AB7;position:absolute;right:2px;top:2px;"></i></div>';
                            $str_out .= '<div class="table_caption_H col-sm-1">' . DateOutDB($table_row['FineDate']) . '</div>';


                            $str_out .= '<div class="table_caption_button col-sm-1">';
                            $str_out .= ChkButton($aUserButton, 'viw', '<a href="mgmt_violation_viw.php?Id=' . $table_row['Id'] . '&P=' . $FormPage . '"><span class="glyphicon glyphicon-eye-open" id="' . $table_row['Id'] . '"></span></a>');
                            $str_out .= '&nbsp;';
                            $str_out .= ChkButton($aUserButton, 'prn', '<a href="mgmt_fine_prn.php?Id=' . $table_row['Id'] . '&P=' . $FormPage . '"><span class="fa fa-print" id="' . $table_row['Id'] . '"></span></a>');
                            $str_out .= '&nbsp;';
                            $str_out .= '</div>
			            <div class="clean_row HSpace4"></div>';


                        }


                    }
                    else{
                        // La cartella non esiste
                        $str_Connection.= "<br>Utente non abilitato alla firma o cartella inesistente!";
                    }
                } else {
                    $str_Connection = 'Login fallita';
                }
            }
            else{
                $str_Connection = 'Connessione fallita';
            }
        }
        else {
            $str_Connection = 'Utente non abilitato alla protocollazione!';
        }

if($str_ErrorPDF != ""){
    $str_out .= '
        <div class="col-sm-12">
            <div class="table_label_H col-sm-12 alert-danger">Problemi riscontrati:</div>
        </div>'. $str_ErrorPDF;
}else{
    $strButtons = '<input type="submit" class="btn btn-default" id="ProtocolButton" style="margin-top:1rem;" value="Protocolla" />';

    $str_out .= '
		<div class="col-sm-12 table_caption_H" style="height:6rem;text-align:center;line-height:6rem;" id="Protocol">
    		' . ChkButton($aUserButton, 'viw', $strButtons) . '
		</div>
	</div>
	</form>';

}



echo $str_out;
?>

<script type="text/javascript">
	$(document).ready(function () {


        $('#f_print').submit(function() {
            $('ProtocolButton').attr("disabled", true);
            $('#Protocol').html("<img src='<?= IMG ?>/progress.gif' />");
        });


		$(".glyphicon-eye-open").click(function(){
			var id = $(this).attr("id");
			window.location.href = "mgmt_violation_viw.php?Id="+id+"&P=<?= $FormPage ?>";
		});

        $('#TypePlate').change(function(){
            var TypePlate = $( "#TypePlate" ).val();
            $(window.location).attr('href', "<?= $LinkPage ?>?TypePlate="+TypePlate);
        });




        
        
	});
</script>
<?php
include(INC."/footer.php");
