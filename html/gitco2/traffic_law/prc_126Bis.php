<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(CLS."/cls_elaboration.php");
require_once(CLS."/cls_dispute.php");
require_once(CLS."/cls_view.php");
include(INC."/function.php");
include(INC."/header.php");

require(INC . '/menu_'.$_SESSION['UserMenuType'].'.php');

$disputeView = new CLS_VIEW(MGMT_DISPUTE);
$cls_dispute = new cls_dispute();
$cls_126Bis = new cls_elaboration("126Bis");

$query = "SELECT DISTINCT CityYear FROM sarida.UserCity WHERE CityId='".$_SESSION['cityid']."' ORDER BY CityYear ASC";
$selectYears = CreateSelectQuery($query,"Search_Year","CityYear","CityYear",$Search_Year,false);
$a_years = $rs->getArrayLine($rs->ExecuteQuery($query));
$a_ElaborationType = array("","");

$btn_search = CheckValue('btn_search','n');
$PageTitle = CheckValue('PageTitle', 's');
$ElaborationType = CheckValue('ElaborationType','n');

if(!isset($_REQUEST['Search_FromNotificationDate'])) $_REQUEST['Search_FromNotificationDate'] = "01/01/".$a_years['CityYear'];
if(!isset($_REQUEST['Search_ToNotificationDate'])) $_REQUEST['Search_ToNotificationDate'] = date("d/m/Y", strtotime("-3 months"));

$Search_FromNotificationDate = CheckValue('Search_FromNotificationDate','s');
$Search_ToNotificationDate = CheckValue('Search_ToNotificationDate','s');
$Search_Dispute = CheckValue('Search_Dispute','s');
$Search_Outcome = CheckValue('Search_Outcome','s');

$a_ElaborationType[$ElaborationType] = " SELECTED";

if(!isset($_REQUEST['Search_Date'])) $_REQUEST['Search_Date'] = date("d/m/Y");
if(!isset($_REQUEST['Search_Time'])) $_REQUEST['Search_Time'] = date("H:i");
        
$ElaborationDate = CheckValue('Search_Date','s');
$ElaborationTime = CheckValue('Search_Time','s');

$r_ProcessingNational = $rs->getArrayLine($rs->Select('ProcessingData126BisNational', "CityId='".$_SESSION['cityid']."' AND Disabled=0 AND Automatic=0"));
$r_ProcessingForeign = $rs->getArrayLine($rs->Select('ProcessingData126BisForeign', "CityId='".$_SESSION['cityid']."' AND Disabled=0 AND Automatic=0"));

$checkParams = array("N"=>false,"F"=>false,"N_Address"=>false,"F_Address"=>false);

if($r_ProcessingNational==null){
    $checkParams['N'] = true;
} else if($r_ProcessingForeign==null){
    $checkParams['F'] = true;
} else {
    if($r_ProcessingNational['Address']=="") $checkParams['N_Address'] = true;
    if($r_ProcessingForeign['Address']=="") $checkParams['F_Address'] = true;
}
                    
if($_SESSION['usercity'] == 'SRDA')
    $controllerNumber = 1; //assegno un controllerId 1 che non esiste a sistema
else 
{
    $controllerRs=$rs->Select("Controller","Id ={$_SESSION['controllerid']} and CityId='{$_SESSION['cityid']}' and (ToDate is null or ToDate>=curdate()) and (FromDate is null or FromDate<=curdate())");
    $controllerNumber=mysqli_num_rows($controllerRs);
}

$str_out .='
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

	<form id="f_search" action="prc_126Bis.php" method="post" autocomplete="off">
        <input type="hidden" name="btn_search" value="1">
		<input type="hidden" name="PageTitle" value="'.$PageTitle.'">
		
        <div class="col-sm-11">
            <div class="col-sm-1 BoxRowLabel">
                Nazionalità
            </div>
            <div class="col-sm-1 BoxRowCaption">
                '.CreateArraySelect(array('' => 'Tutte', 'N' => 'Nazionali', 'F' => 'Estere'), true, 'TypePlate', 'TypePlate', $btn_search > 0 ? $s_TypePlate : 'N', true).'
            </div>
            <div class="col-sm-1 BoxRowLabel" style="font-size: 0.9rem;">
                Da data accertamento
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <input class="form-control frm_field_date" type="text" value="'.$Search_FromFineDate.'" name="Search_FromFineDate">
            </div>
            <div class="col-sm-1 BoxRowLabel" style="font-size: 0.9rem;">
                A data accertamento
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <input class="form-control frm_field_date" type="text" value="'.$Search_ToFineDate.'" name="Search_ToFineDate">
            </div>
            <div class="col-sm-1 BoxRowLabel">
                Da data notifica
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <input class="form-control frm_field_date" type="text" value="'.$Search_FromNotificationDate.'" id="Search_FromNotificationDate" name="Search_FromNotificationDate">
            </div>
            <div class="col-sm-1 BoxRowLabel">
                A data notifica
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <input class="form-control frm_field_date" type="text" value="'.$Search_ToNotificationDate.'" id="Search_ToNotificationDate" name="Search_ToNotificationDate">
            </div>
            <div class="col-sm-2 BoxRowLabel"></div>          
                             
            <div class="clean_row HSpace4"></div>                 
                  
            <div class="col-sm-1 BoxRowLabel">
			    Da cron 
            </div>
            <div class="col-sm-1 BoxRowCaption">			
			    <input class="form-control frm_field_numeric" type="text" value="'. $Search_FromProtocolId. '" id="Search_FromProtocolId" name="Search_FromProtocolId">
			</div>
            <div class="col-sm-1 BoxRowLabel">		
			    A cron 
            </div>
            <div class="col-sm-1 BoxRowCaption">			
			    <input class="form-control frm_field_numeric" type="text" value="'. $Search_ToProtocolId. '" id="Search_ToProtocolId" name="Search_ToProtocolId">
		    </div>
		    <div class="col-sm-1 BoxRowLabel">
			    Anno 
            </div>
            <div class="col-sm-1 BoxRowCaption">		
                '.$selectYears.'	
			</div>     
			<div class="col-sm-4 BoxRowLabel font_small">
                In assenza di notifica considera il verbale notificato dalla data di pagamento
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <input disabled type="checkbox" value="4" name="Search_PaymentDate" ' . ChkCheckButton($Search_PaymentDate) . '>
            </div>
  			<div class="col-sm-1 BoxRowLabel">  
            </div>

            <div class="clean_row HSpace4"></div>

            <div class="col-sm-1 BoxRowLabel font_small">
                Data elaborazione
            </div>
            <div class="col-sm-1 BoxRowCaption">
               <input class="form-control frm_field_date" type="text" id="Search_Date" name="Search_Date" value="'. $ElaborationDate .'">
            </div>
            <div class="col-sm-1 BoxRowLabel font_small">
                Ora elaborazione
            </div>
            <div class="col-sm-1 BoxRowCaption">
               <input class="form-control frm_field_time" type="text" id="Search_Date" name="Search_Time" value="'. $ElaborationTime .'">
            </div>
		    <div class="col-sm-1 BoxRowLabel">
			    Ricorso 
            </div>
            <div class="col-sm-2 BoxRowCaption">		
                '.CreateArraySelect(array('with' => 'Posizioni con ricorso', 'without' => 'Posizioni senza ricorso'), true, 'Search_Dispute', 'Search_Dispute', $Search_Dispute, false).'
            </div>
            <div class="col-sm-1 BoxRowLabel">
                Esito
            </div>    
            <div class="col-sm-2 BoxRowCaption">
                '.CreateArraySelect(array('positive' => 'Positivo', 'negative' => 'Negativo'), true, 'Search_Outcome', 'Search_Outcome', $btn_search > 0 ? $Search_Outcome : 'positive', false).'
            </div> 
		    <div class="col-sm-2 BoxRowLabel">
            </div>
        </div>
        <div class="col-sm-1">
            <div class="col-sm-12 BoxRowFilterButton" style="height:6.8rem">
            	<button type="submit" data-toggle="tooltip" data-placement="top" title="Cerca" class="tooltip-r btn btn-primary" id="search" name="search" style="margin-top:0;width:100%;height:100%"><i class="glyphicon glyphicon-search" style="font-size:3rem;"></i></button>
            </div>
        </div>
    </form>
</div>
<div class="clean_row HSpace4"></div>
';
//<div class="col-sm-12" id="DIV_Progress" style="display:none;">
//				<div class="table_label_H col-sm-12">AVANZAMENTO DELL\'OPERAZIONE</div>
//				<div class="clean_row HSpace4"></div>
//                <div class="col-sm-12 table_caption_H"  style="height:auto;text-align:center;">
//                    <div class="progress" style="margin-bottom:0;">
//            			<div id="progressbar" class="progress-bar progress-bar-striped progress-bar-info active" role="progressbar" style="width: 0%;" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100"></div>
//            		</div>
//                    <div id="DIV_Rows" class="col-sm-12">Righe elaborate: <span></span></div>
//                    <div id="DIV_Messages" class="col-sm-12"></div>
//                </div>
//			</div>
$str_out .='        
        
    	<div class="row-fluid">
        	<div class="col-sm-12">
                <div class="table_label_H col-sm-1">Riga</div>
				<div class="table_label_H col-sm-1 font_small"><strong>Cron. Verbale Orig.</strong></div>
				<div class="table_label_H col-sm-2 font_small"><strong>Rif.to Verbale Orig.</strong></div>
				<div class="table_label_H col-sm-1">Data Verbale</div>
				<div class="table_label_H col-sm-1">Data notifica</div>
				<div class="table_label_H col-sm-1">Targa</div>
				<div class="table_label_H col-sm-1">Articolo</div>
				<div class="table_label_H col-sm-4">Obbligato in solido</div>
				<div class="clean_row HSpace4"></div>';

if($btn_search==1){
    
    if(CheckValue('ControllerId', 'n') > 0){
        $controllerId = CheckValue('ControllerId', 'n');
    } else {
        $rs_CustomerParameter = $rs->Select('V_CustomerParameter',"CityId='".$_SESSION['cityid']."'");
        $r_CustomerParameter = mysqli_fetch_array($rs_CustomerParameter);
        if($s_TypePlate=="N")
            $controllerId = $r_CustomerParameter['Data126BisNationalControllerId'];
        else if($s_TypePlate=="F")
            $controllerId = $r_CustomerParameter['Data126BisForeignControllerId'];
        else
            $controllerId = null;
    }

    $controllerQuery = "SELECT Name, Id FROM Controller WHERE CityId = '".$_SESSION['cityid']."'";
    $controller126Bis = CreateSelectQuery($controllerQuery,"ControllerId","Id","Name",$controllerId,false);

    switch($s_TypePlate){
        case "N":
            $str_WhereCountry = " AND CountryId='Z000'";
            $r_ProcessingData = $r_ProcessingNational;
            break;
        case "F":
            $str_WhereCountry = " AND CountryId!='Z000'";
            $r_ProcessingData = $r_ProcessingForeign;
            break;
        default:
            $str_WhereCountry = "";
            $r_ProcessingData = null;
            break;
    }

    $str_Where .= " AND CityId='".$_SESSION['cityid']."'";
    if($Search_FromNotificationDate=="" || DateInDB($Search_FromNotificationDate) <= DateInDB("30/03/2021"))
        $Search_FromNotificationDate="30/03/2021";
    if($Search_FromNotificationDate!="")
        $str_Where .= " AND NotificationDate>='".DateInDB($Search_FromNotificationDate)."' ";
    if($Search_ToNotificationDate!="")
        $str_Where .= " AND NotificationDate<='".DateInDB($Search_ToNotificationDate)."' ";
    if($Search_Year!="")
        $str_Where .= " AND ProtocolYear=$Search_Year ";

    $strOrder = "ProtocolYear DESC, ProtocolId DESC";

    $rs_FineProcedure = $rs->Select('V_126BisProcedure',$str_Where.$str_WhereCountry, $strOrder);
    //echo $str_Where.$str_WhereCountry;
    $RowNumber = mysqli_num_rows($rs_FineProcedure);
    //echo "<br>numero righe trovate: ".$RowNumber;

    if ($RowNumber > 0) {
        $n_Row = 1;

        while ($r_FineProcedure = mysqli_fetch_array($rs_FineProcedure)) {
            
            //CONTROLLI PER FILTRI RICORSO E ESITO/////////////////////////////////////////////
            if($s_TypePlate=="" || $r_ProcessingData==null){
                if($r_FineProcedure['CountryId']!="Z000")
                    $r_ProcessingData = $r_ProcessingForeign;
                else
                    $r_ProcessingData = $r_ProcessingNational;
            }
            
            $params = array_merge(
                array(
                    "NotificationDate" => $r_FineProcedure['NotificationDate'],
                    "CommunicationDate" => $r_FineProcedure['CommunicationDate'],
                    "IncompletedCommunicationFlag" => $r_FineProcedure['IncompletedCommunication'],
                    "CurrentDate" => DateInDB($ElaborationDate),
                    "DisputeDays" => 0,
                    "DisputeCheck" => true,
                    "DisputeMsg" => null
                ),
                $r_ProcessingData
            );
            
            $rs_FineDispute= $rs->selectQuery($disputeView->generateSelect("F.Id=".$r_FineProcedure['Id'],null, "GradeTypeId DESC",1));
            if(mysqli_num_rows($rs_FineDispute) > 0){
                if($Search_Dispute=="without"){
                    $RowNumber--;
                    continue;
                } else {
                    $r_FineDispute = mysqli_fetch_array($rs_FineDispute);
                }
                        
                $cls_dispute->setDispute($r_FineDispute, $params['DisputeCheckType']);
                
                $params['DisputeDays'] = $cls_dispute->a_info['days'];
                $params['DisputeCheck'] = $cls_dispute->a_info['check'];
                $params['DisputeMsg'] = $cls_dispute->a_info['msg'];
            } else {
                if($Search_Dispute=="with"){
                    $RowNumber--;
                    continue;
                }
                $cls_dispute->resetInfo();
            }
        
            if($params['DisputeCheck']===false && $Search_Outcome=="positive"){
                $RowNumber--;
                continue;
            }
            
            $cls_126Bis->checkMissingData($params);
            if($cls_126Bis->a_missingData['check']){
                $cls_126Bis->checkDaysLimitation();
                if($cls_126Bis->a_daysLimitation['check']){
                    if($Search_Outcome=="negative"){
                        $RowNumber--;
                        continue;
                    }
                } else if($Search_Outcome=="positive") {
                    $RowNumber--;
                    continue;
                }
            } else if($Search_Outcome=="positive") {
                $RowNumber--;
                continue;
            }
            ////////////////////////////////////////////////////////////////////////////////7
            
            $a_trespasser = array(1=>null,2=>null,11=>null,15=>null);
            $rs_FineTrespasser = $rs->SelectQuery('SELECT TrespasserTypeId, TrespasserId, CompanyName, Surname, Name FROM V_FineTrespasser WHERE FineId='.$r_FineProcedure['Id']);
            while ($r_FineTrespasser = mysqli_fetch_array($rs_FineTrespasser)) {
                $a_trespasser[$r_FineTrespasser['TrespasserTypeId']] = $r_FineTrespasser['CompanyName'].$r_FineTrespasser['Surname']." ".$r_FineTrespasser['Name'];
            }
        
            if($a_trespasser[1]!="")
                $trespasser = $a_trespasser[1];
            else if($a_trespasser[2]!="")
                $trespasser = $a_trespasser[2];
            else if($a_trespasser[11]!="")
                $trespasser = $a_trespasser[11];
            else if($a_trespasser[15]!="")
                $trespasser = $a_trespasser[15];
        
            $str_out.= '<div class="tableRow">';
            $str_out.= '<div class="table_caption_H col-sm-1">' . $n_Row ++ .'</div>';
            $str_out.= '<div class="table_caption_H col-sm-1">' . $r_FineProcedure['ProtocolId']."/".$r_FineProcedure['ProtocolYear'] .'</div>';
            $str_out.= '<div class="table_caption_H col-sm-2">' . $r_FineProcedure['Code'] .'</div>';
            $str_out.= '<div class="table_caption_H col-sm-1">' . DateOutDB($r_FineProcedure['FineDate']) .' ' . TimeOutDB($r_FineProcedure['FineTime']) .'</div>';
            $str_out.= '<div class="table_caption_H col-sm-1">' . DateOutDB($r_FineProcedure['NotificationDate']) .'</div>';
            $str_out.= '<div class="table_caption_H col-sm-1">' . $r_FineProcedure['VehiclePlate'] .'</div>';
            $str_out.= '<div class="table_caption_H col-sm-1">' . $r_FineProcedure['Article'] .' ' . $r_FineProcedure['Paragraph'] .' ' . $r_FineProcedure['Letter'] .'</div>';
            $str_out.= '<div class="table_caption_H col-sm-4">' . $trespasser .'</div>';
            $str_out.= '</div><div class="clean_row HSpace4"></div>';
        }

        $str_Buttons = '
    <form id="f_Print" action="prc_126Bis_new_exe.php'.$str_GET_Parameter.'&FinalElaboration=0" method="post">
        <input type="hidden" name="isController" id="isController" value="'.(($controllerNumber>0)? "1": "0").'">
        <div class="col-sm-1 BoxRowLabel">
            Accertatore
        </div>
        <div class="col-sm-2  BoxRowCaption">
            '.$controller126Bis.'
        </div>
        <div class="col-sm-2 BoxRowLabel">
            Tipo elaborazione / destinazione
        </div>
        <div class="col-sm-2 BoxRowCaption">
            <select class="form-control" name="ElaborationType" id="ElaborationType">
                <option value="0" '. $a_ElaborationType[0] .'>Preinserimenti</option>
                <option value="1" '. $a_ElaborationType[1] .'>Verbali</option>				 
            </select>	              
        </div>
           
        <div class="col-sm-5 BoxRowLabel"></div> 

        <div class="clean_row"></div>

        <div class="col-sm-12 table_caption_H" style="display: flex; align-items: center; height:10rem">
            <div class="col-sm-5 text-right"> 
                <div id="selectFileType">
                    File
                    <select name="fileType">
                        <option value="0">PDF</option>
                        <option value="1">CSV</option>
                    </select>
                </div>
            </div>
            <div class="col-sm-2 text-center">
               <button id="act_button" progress-tick="500" type="button" class="btn btn-success" style="width:20rem;">Elaborazione provvisoria</i></button>
            </div>
            <div class="col-sm-5">
                <input class="ultimate" type="checkbox" value=1 name="ultimate" id="ultimate" > <label class="ultimate">DEFINITIVA</label>
            </div>
        </div>   
        <div class="col-sm-12" id="DIV_Progress" style="display:none;">
			<div class="table_label_H col-sm-12">AVANZAMENTO DELL\'OPERAZIONE</div>
			<div class="clean_row HSpace4"></div>	
            <div class="col-sm-12 table_caption_H"  style="height:auto;text-align:center;">
                <div class="progress" style="margin-bottom:0;">
        			<div id="progressbar" class="progress-bar progress-bar-striped progress-bar-info active" role="progressbar" style="width: 0%;" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100"></div>
        		</div>
                <div id="DIV_Rows" class="col-sm-12">Righe elaborate: <span></span></div>
                <div id="DIV_Messages" class="col-sm-12"></div>
            </div>
		</div>
    </form>';
    }
    
    if($RowNumber <= 0){
        $str_out.= '
            <div class="table_caption_H col-sm-12">
                Nessun record presente
            </div>';
    } else {
        $str_out.= '
        '.ChkButton($aUserButton, 'act',$str_Buttons) .'
        ';
    }
} else {
    $str_out.= '
        <div class="table_caption_H col-sm-12 text-center">
    		Inserire criteri ricerca
    	</div>';
}
$str_out.= '
    </div>
</div>';

echo $str_out;
?>
<script src="<?= JS ?>/progressbar.js" type="text/javascript"></script>

<script type="text/javascript">
		var checkParams = <?= json_encode($checkParams); ?>

        $(document).ready(function () {
          	$(".tableRow").mouseover(function(){
          		$( this ).find( '.table_caption_H, .table_caption_button' ).css("background-color", "#cfeaf7c7");
          	});
          	$(".tableRow").mouseout(function(){
          		$( this ).find( '.table_caption_H, .table_caption_button' ).css("background-color", "");
          	});

            $('#ultimate').click(function(){
                if($('#ultimate').is(":checked")) {
                    $('#selectFileType').hide();
                    $('#act_button').text('Elaborazione definitiva');
                    $('#act_button').removeClass( "btn-success" ).addClass( "btn-warning" );
                }else{
                    $('#selectFileType').show();
                    $('#act_button').text('Elaborazione provvisoria');
                    $('#act_button').removeClass( "btn-warning" ).addClass( "btn-success" );
                }
            });



            $('#act_button').click(function() {
                if($('#ultimate').is(":checked")) {
                        if($('#isController').val()!=1){
                            alert("L'utente corrente non è un accertatore valido per <?=$_SESSION['citytitle']?>, impossibile proseguire");
                            return false;
                        }

                        if(checkParams['N'] || checkParams['F'] || checkParams['N_Address'] || checkParams['F_Address']){
                            if(checkParams['N']){
                                alert("Parametri 126Bis Nazionale assenti! Impossibile elaborare.");
                            }
                            if(checkParams['F']){
                                alert("Parametri 126Bis Estero assenti! Impossibile elaborare.");
                            }
                            if(checkParams['N_Address']){
                                alert("Parametri 126Bis Nazionale incompleti! Luogo di elaborazione dei verbali vuoto. Impossibile elaborare");
                            }
                            if(checkParams['F_Address']){
                                alert("Parametri 126Bis Estero incompleti! Luogo di elaborazione dei verbali vuoto. Impossibile elaborare");
                            }
                        }
                        else if($('#ControllerId').val()=="" && $('#ElaborationType').val()=="1")
                            alert("Selezionare un accertatore per elaborare il verbale!");
                        else{
                            var c = confirm("Si stanno per elaborare i 126Bis in maniera definitiva. Continuare?");
                            if (c) {
                                $('#DIV_Progress').show();
                                $('.ultimate').hide();
                                var getString = "<?=$str_GET_Parameter?>";
                                getString += "&ElaborationType=" + $("#ElaborationType").val();
                                getString += "&ControllerId=" + $("#ControllerId").val();
                                getString += "&FinalElaboration=1";
                                progressBar_start('prc_126Bis_new_exe.php', this, getString);
                            }
                        }
                }else{
                    if(checkParams['N'] || checkParams['F'] || checkParams['N_Address'] || checkParams['F_Address']){
                        if(checkParams['N']){
                            alert("Parametri 126Bis Nazionale assenti! Impossibile elaborare.");
                        }
                        if(checkParams['F']){
                            alert("Parametri 126Bis Estero assenti! Impossibile elaborare.");
                        }
                        if(checkParams['N_Address']){
                            alert("Parametri 126Bis Nazionale incompleti! Luogo di elaborazione dei verbali vuoto. Impossibile elaborare");
                        }
                        if(checkParams['F_Address']){
                            alert("Parametri 126Bis Estero incompleti! Luogo di elaborazione dei verbali vuoto. Impossibile elaborare");
                        }
                    }
                    else{
                        $('#f_Print').submit();
                    }

                }
            });

            $('#act_button').on('progressDone', function(e, data){
                $('#progressbar').removeClass('progress-bar-info active');
                $('#progressbar').addClass('progress-bar-success');
                $('#DIV_Messages').html(data.Messaggio);
            });
            $('#act_button').on('progressFail', function(e, data){
                $('#progressbar').removeClass('progress-bar-info active');
                $('#progressbar').addClass('progress-bar-danger');
                $('#DIV_Messages').html(data.responseText);
            });
            $('#act_button').on('progressGet', function(e, data){
                $('#DIV_Rows span').html(data.Contati + ' / ' + data.Totali);
            });

        });
    </script>
<?php
include(INC."/footer.php");
