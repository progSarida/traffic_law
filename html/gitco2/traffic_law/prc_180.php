<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
include(INC."/header.php");

require(INC . '/menu_'.$_SESSION['UserMenuType'].'.php');

$query = "SELECT DISTINCT CityYear FROM sarida.UserCity WHERE CityId='".$_SESSION['cityid']."' ORDER BY CityYear ASC";
$selectYears = CreateSelectQuery($query,"Search_Year","CityYear","CityYear",$Search_Year,false);
$a_years = $rs->getArrayLine($rs->ExecuteQuery($query));
$a_ElaborationType = array("","");

$btn_search = CheckValue('btn_search','n');
$ElaborationType = CheckValue('ElaborationType','n');
if(!isset($_REQUEST['Search_FromNotificationDate']))
    $_REQUEST['Search_FromNotificationDate'] = "01/01/".$a_years['CityYear'];
if(!isset($_REQUEST['Search_ToNotificationDate']))
    $_REQUEST['Search_ToNotificationDate'] = date("d/m/Y", strtotime("-2 months"));
$Search_FromNotificationDate = CheckValue('Search_FromNotificationDate','s');
$Search_ToNotificationDate = CheckValue('Search_ToNotificationDate','s');

$a_ElaborationType[$ElaborationType] = " SELECTED";

if(!isset($_REQUEST['ElaborationDate']))
    $_REQUEST['ElaborationDate'] = date("d/m/Y");
if(!isset($_REQUEST['ElaborationTime']))
    $_REQUEST['ElaborationTime'] = date("H:i");

$ElaborationDate = CheckValue('ElaborationDate','s');
$ElaborationTime = CheckValue('ElaborationTime','s');
$str_out .='
<div class="row-fluid">
    <form id="f_Search" action="'.$str_CurrentPage.'" method="post">
    <input type="hidden" name="btn_search" value="1">

    <div class="col-sm-12" >
        <div class="col-sm-11 BoxRow" style="height:4.6rem; border-right:1px solid #E7E7E7;">
        
            <div class="col-sm-1 BoxRowLabel">
                Nazionalità
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <select class="form-control" name="TypePlate" id="TypePlate">
                    <option value="N"'.$s_SelPlateN.'>Nazionali</option>
                    <option value="F"'.$s_SelPlateF.'>Estere</option>								
                </select>
            </div>  
            
            <div class="col-sm-1 BoxRowLabel">
                Da data notifica
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <input class="form-control frm_field_date" type="text" value="'.$Search_FromNotificationDate.'" id="Search_FromNotificationDate" name="Search_FromNotificationDate" style="width:12rem">
            </div>
            <div class="col-sm-1 BoxRowLabel">
                A data notifica
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <input class="form-control frm_field_date" type="text" value="'.$Search_ToNotificationDate.'" id="Search_ToNotificationDate" name="Search_ToNotificationDate" style="width:12rem">
            </div>
            <div class="col-sm-1 BoxRowLabel">
                Da data verbale
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <input class="form-control frm_field_date" type="text" value="'.$Search_FromFineDate.'" name="Search_FromFineDate" style="width:12rem">
            </div>
            <div class="col-sm-1 BoxRowLabel">
                A data verbale
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <input class="form-control frm_field_date" type="text" value="'.$Search_ToFineDate.'" name="Search_ToFineDate" style="width:12rem">
            </div>
            
            <div class="col-sm-2 BoxRowCaption"></div>                                         
            <div class="clean_row HSpace4"></div>                 
                   

             <div class="col-sm-1 BoxRowLabel">
			    Da cron 
            </div>
            <div class="col-sm-1 BoxRowCaption">			
			    <input class="form-control frm_field_numeric" type="text" value="'. $Search_FromProtocolId. '" id="Search_FromProtocolId" name="Search_FromProtocolId" style="width:8rem">
			</div>
            <div class="col-sm-1 BoxRowLabel">		
			    A cron 
            </div>
            <div class="col-sm-1 BoxRowCaption">			
			    <input class="form-control frm_field_numeric" type="text" value="'. $Search_ToProtocolId. '" id="Search_ToProtocolId" name="Search_ToProtocolId" style="width:8rem">
		    </div>
		    <div class="col-sm-1 BoxRowLabel">
			    Anno 
            </div>
            <div class="col-sm-1 BoxRowCaption">			
			     '.$selectYears.'
			</div>         
            <div class="col-sm-6 BoxRowCaption"></div> 
        </div>
        <div class="col-sm-1 BoxRow" style="height:4.6rem;">
            <div class="col-sm-12 BoxRowFilterButton" style="text-align: center">
                <i class="glyphicon glyphicon-search" style="margin-top:0.6rem;font-size:3rem;"></i>	
            </div>
        </div>
    </div>
</div>
<div class="clean_row HSpace4"></div>
';

$str_out .='
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
    	<div class="row-fluid">
        	<div class="col-sm-12">
                <div class="table_label_H col-sm-1">Riga</div>
				<div class="table_label_H col-sm-1">Crono Verbale Orig.</div>
				<div class="table_label_H col-sm-1">Rif.to Verbale Orig.</div>
				<div class="table_label_H col-sm-1">Data Verbale</div>
				<div class="table_label_H col-sm-1">Data notifica</div>
				<div class="table_label_H col-sm-1">Targa</div>
				<div class="table_label_H col-sm-1">Articolo</div>
				<div class="table_label_H col-sm-3">Proprietario/Obbligato</div>
				<div class="table_label_H col-sm-2">Conducente</div>
				<div class="clean_row HSpace4"></div>';


if($btn_search==1){

    if($s_TypePlate=="") {
        $str_out.=
            '<div class="table_caption_H col-sm-12">
			Scegliere nazionalità
		    </div>';
    } else {

        $rs_CustomerParameter = $rs->Select('V_CustomerParameter',"CityId='".$_SESSION['cityid']."'");
        $r_CustomerParameter = mysqli_fetch_array($rs_CustomerParameter);

        $str_ProcessingTable = ($s_TypePlate=="N") ? "National" : "Foreign";
        $controllerId = ($s_TypePlate=="N") ? $r_CustomerParameter['Data180NationalControllerId'] : $r_CustomerParameter['Data180ForeignControllerId'];
        $rs_ProcessingData = $rs->Select('ProcessingData180'.$str_ProcessingTable, "CityId='".$_SESSION['cityid']."' AND Disabled=0 AND Automatic=0");
        $r_ProcessingData = mysqli_fetch_array($rs_ProcessingData);

        $controllerQuery = "SELECT Name, Id FROM Controller WHERE CityId = '".$_SESSION['cityid']."'";
        $controller180 = CreateSelectQuery($controllerQuery,"ControllerId","Id","Name",$controllerId,false);

        $str_WhereCountry = ($s_TypePlate=="N") ? " AND CountryId='Z000'" : " AND CountryId!='Z000'";
        $str_Where .= " AND CityId='".$_SESSION['cityid']."'";
        if($Search_FromNotificationDate!="")
            $str_Where .= " AND NotificationDate>='".DateInDB($Search_FromNotificationDate)."' ";
        if($Search_ToNotificationDate!="")
            $str_Where .= " AND NotificationDate<='".DateInDB($Search_ToNotificationDate)."' ";
        if($Search_Year!="")
            $str_Where .= " AND ProtocolYear=$Search_Year ";
        
        //condizione aggiunta per escludere i verbali creati da avviso bonario che hanno un record 30 in FineHistory
        $str_Where .= " AND Id not in (select FH30.FineId from FineHistory FH30 where FH30.NotificationTypeId = 30)";

        $strOrder = "ProtocolYear DESC, ProtocolId DESC";



        $rs_FineProcedure = $rs->Select('V_180Procedure',$str_Where.$str_WhereCountry, $strOrder);
        $RowNumber = mysqli_num_rows($rs_FineProcedure);

        if ($RowNumber == 0) {
            $str_out.=
                '<div class="table_caption_H col-sm-12">
			        Nessun record presente
		        </div>';
        } else {
            $n_Row = 1;
            while ($r_FineProcedure = mysqli_fetch_array($rs_FineProcedure)) {
                $trespasser = array(1=>null,2=>null,3=>null,10=>null,11=>null);
                $a_trespasser = array(1=>null,2=>null);
                $rs_FineTrespasser = $rs->SelectQuery('SELECT TrespasserTypeId, TrespasserId, CompanyName, Surname, Name FROM V_FineTrespasser WHERE FineId='.$r_FineProcedure['Id']);
                while ($r_FineTrespasser = mysqli_fetch_array($rs_FineTrespasser)) {
                    $trespasser[$r_FineTrespasser['TrespasserTypeId']] = $r_FineTrespasser['CompanyName'].$r_FineTrespasser['Surname']." ".$r_FineTrespasser['Name'];
                }

                if($trespasser[1]!=""){
                    $a_trespasser[1] = $trespasser[1];
                    $a_trespasser[2] = "";
                }
                else if($trespasser[2]!=""){
                    $a_trespasser[1] = $trespasser[2];
                    $a_trespasser[2] = $trespasser[3];
                }

                $str_out.= '<div class="tableRow">';
                $str_out.= '<div class="table_caption_H col-sm-1">' . $n_Row ++ .'</div>';
                $str_out.= '<div class="table_caption_H col-sm-1">' . $r_FineProcedure['ProtocolId']."/".$r_FineProcedure['ProtocolYear'] .'</div>';
                $str_out.= '<div class="table_caption_H col-sm-1">' . $r_FineProcedure['Code'] .'</div>';
                $str_out.= '<div class="table_caption_H col-sm-1">' . DateOutDB($r_FineProcedure['FineDate']) .' ' . TimeOutDB($r_FineProcedure['FineTime']) .'</div>';
                $str_out.= '<div class="table_caption_H col-sm-1">' . DateOutDB($r_FineProcedure['NotificationDate']) .'</div>';
                $str_out.= '<div class="table_caption_H col-sm-1">' . $r_FineProcedure['VehiclePlate'] .'</div>';
                $str_out.= '<div class="table_caption_H col-sm-1">' . $r_FineProcedure['Article'] .' ' . $r_FineProcedure['Paragraph'] .' ' . $r_FineProcedure['Letter'] .'</div>';
                $str_out.= '<div class="table_caption_H col-sm-3">' . $a_trespasser[1] .'</div>';
                $str_out.= '<div class="table_caption_H col-sm-2">' . $a_trespasser[2] .'</div>';
                $str_out.= '</div><div class="clean_row HSpace4"></div>';
            }
            $str_Buttons =
                '
            <div class="col-sm-1 BoxRowLabel">
                Data elaborazione
            </div>
            <div class="col-sm-1 BoxRowCaption">
               <input class="form-control frm_field_date" type="text" id=ElaborationDate name="ElaborationDate" value="'. $ElaborationDate .'" style="width:12rem">
            </div>
            <div class="col-sm-1 BoxRowLabel">
                Ora elaborazione
            </div>
            <div class="col-sm-1 BoxRowCaption">
               <input class="form-control frm_field_time" type="text" id=ElaborationTime name="ElaborationTime" value="'. $ElaborationTime .'" style="width:12rem">
            </div>
            <div class="col-sm-1 BoxRowLabel">
                Accertatore
            </div>
            <div class="col-sm-2  BoxRowCaption">
                    '.$controller180.'
            </div>
            <div class="col-sm-2 BoxRowLabel">
                Tipo elaborazione / destinazione
            </div>
            <div class="col-sm-1 BoxRowCaption">
				<select  name="ElaborationType"  id="ElaborationType">
					<option value="0" '. $a_ElaborationType[0] .'>Preinserimenti</option>
					<option value="1" '. $a_ElaborationType[1] .'>Verbali</option>				 
				</select>	              
            </div> 
            <div class="col-sm-2 BoxRowCaption"></div> 
            <div class="clean_row"></div>     
            <div class="col-sm-12" style="display: flex; align-items: center;">
                <div class="col-sm-5" > 
                    <div id="selectFileType" style="float:right;">
                    File
                        <select name="fileType">
                            <option value="0">PDF</option>
                            <option value="1">CSV</option>
                        </select>
                    </div>
                </div>
                <div class="col-sm-2">
                   
                   <button id="act_button" progress-tick="500" type="button" class="btn btn-success" style="margin-top:1rem;">Elaborazione provvisoria</i></button>
                   
                </div>
                <div class="col-sm-5">
                    <div style="float:left;">
                        <input class="ultimate" type="checkbox" value=1 name="ultimate" id="ultimate" > <label class="ultimate">DEFINITIVA</label>
                    </div>
                </div>
            </div> ';

            $str_out.= '
            <div class="col-sm-12 table_caption_H" style="height:10rem;text-align:center;line-height:6rem;">
            '.ChkButton($aUserButton, 'act',$str_Buttons) .'    		 		
            </div>
            ';

        }

        $str_out.= '
		    </form>
	    </div>
	    ';
    }

}

	$str_out.= '<div>
</div>';


	echo $str_out;
?>
    <script src="<?= JS ?>/progressbar.js" type="text/javascript"></script>

    <script type="text/javascript">
        $(document).ready(function () {
            $('.glyphicon-search').click(function(){
                $('#f_Search').submit();
            });

            $('#f_Search').on('keyup keypress', function(e) {
                var keyCode = e.keyCode || e.which;
                if (keyCode === 13) {
                    $("#f_Search").submit();
                }
            });

            $('#ultimate').click(function(){
                if($('#ultimate').is(":checked")) {
                    $('#selectFileType').hide();
                    $('#act_button').val('Elaborazione definitiva');
                    $('#act_button').removeClass( "btn-success" ).addClass( "btn-warning" );
                }else{
                    $('#selectFileType').show();
                    $('#act_button').val('Elaborazione provvisoria');
                    $('#act_button').removeClass( "btn-warning" ).addClass( "btn-success" );
                }
            });

          	$(".tableRow").mouseover(function(){
          		$( this ).find( '.table_caption_H, .table_caption_button' ).css("background-color", "#cfeaf7c7");
          	});
          	$(".tableRow").mouseout(function(){
          		$( this ).find( '.table_caption_H, .table_caption_button' ).css("background-color", "");
          	});

            $('#act_button').click(function() {
                if($('#ultimate').is(":checked")) {

                        var c = confirm("Si stanno per elaborare i 180/8 in maniera definitiva. Continuare?");
                        if(c){
                            $('#DIV_Progress').show();

                            $('#act_button').hide();
                            $('#ultimate').hide();
                            var getString = "<?=$str_GET_Back_Page?>";
                            getString+= "&ElaborationDate="+$("#ElaborationDate").val();
                            getString+= "&ElaborationTime="+$("#ElaborationTime").val();
                            getString+= "&ElaborationType="+$("#ElaborationType").val();
                            getString+= "&ControllerId="+$("#ControllerId").val();
                            getString+= "&Search_FromNotificationDate="+$("#Search_FromNotificationDate").val();
                            getString+= "&Search_ToNotificationDate="+$("#Search_ToNotificationDate").val();
                            getString+= "&ultimate="+$("#ultimate").val();
                            progressBar_start('prc_180_exe.php', this, getString);
                        }

                }else{
                    $('#f_Search').attr('action', 'prc_180_PDF_exe.php<?= $str_GET_Back_Page ?>');
                    $('#f_Search').submit();
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
