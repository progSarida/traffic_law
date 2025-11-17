<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
include(INC."/header.php");

require(INC . '/menu_'.$_SESSION['UserMenuType'].'_forprint.php');

$AdvancedUser=$_SESSION['userlevel']>51?TRUE:FALSE;

$Search_Customer='';
if (isset($_GET['Search_Customer'])){
    $Search_Customer = $_GET['Search_Customer'];
}
else{
    $Search_Customer = CheckValue('Search_Customer','s');
}
$Search_Customer_Where='';

if($AdvancedUser){
    $Search_Customer_Where = empty($Search_Customer)?"1":"CityId='" . $Search_Customer."'";
    $rs_Row = $rs->Select('V_SafoTrespasser',$Search_Customer_Where,"ProtocolYear, FineId DESC");
    $str_out .= '
    <div class="col-sm-12" >
       <div class="col-sm-11 BoxRow" style="height:4.6rem; border-right:1px solid #E7E7E7;">
           <div class="col-sm-1 BoxRowLabel">
               Ente:
            </div>
            <div class="col-sm-2 BoxRowCaption">
                '. CreateSelect("Customer","1","CityId","Search_Customer","CityId","ManagerName",$Search_Customer,false) .'
            </div>
        </div>
    </div>
        ';
}else{
    $Search_Customer_Where="CityId='".$_SESSION['cityid']."'";
    $rs_Row = $rs->Select('V_SafoTrespasser',$Search_Customer_Where,"ProtocolYear, FineId DESC");
}

$str_out.='
    <div class="col-sm-12 BoxRowLabel" style="height:2rem;">
        <div class="col-sm-12 BoxRowLabel" style="text-align:center;line-height:2rem;">
            Elenco SAFO inviabili.
        </div>    
    </div>
    ';

$str_out.='
    <div class="row-fluid">
        <form id="f_Print" action="prc_safo_exe.php" method="post">
            <div class="col-sm-12">
                <div class="table_label_H col-sm-1">FineId</div>
                <div class="table_label_H col-sm-1">Codice</div>
                <div class="table_label_H col-sm-2">Nome Compagnia</div>
                <div class="table_label_H col-sm-1">Data</div>
                <div class="table_label_H col-sm-1">Ora</div>
                <div class="table_label_H col-sm-1">Targa</div>
                <div class="table_label_H col-sm-2">Note</div>
                <div class="table_label_H col-sm-1">Seleziona</div>
                <div class="clean_row HSpace4"></div>
    ';

$RowNumber = mysqli_num_rows($rs_Row);
if ($RowNumber == 0) {
	$str_out.='Nessun record presente';
} else {
    $safo_arr = array();
    $within15 = date('Y-m-d', strtotime("+15 day"));
    while ($r_Row = mysqli_fetch_array($rs_Row)) {
        $str_out.='
                <div class="table_caption_H col-sm-1 text-center">' . $r_Row['FineId'] .'</div>
                <div class="table_caption_H col-sm-1 text-center">' . $r_Row['Code'] .'</div>
                <div class="table_caption_H col-sm-2 text-center">' . $r_Row['CompanyName'] .'</div>
                <div class="table_caption_H col-sm-1 text-center">' . DateConvert($r_Row['FineDate']) .'</div>
                <div class="table_caption_H col-sm-1 text-center">' . DateConvert($r_Row['FineTime'],'H:i') .'</div>
                <div class="table_caption_H col-sm-1 text-center">' . $r_Row['VehiclePlate'] .'</div>
                <div class="table_caption_H col-sm-2 text-center">' . $r_Row['Note'] .'</div>
                <div class="table_caption_H col-sm-1 text-center"> 
                    <input type="checkbox" name="checkBox[]" value="'.$r_Row["FineId"].'" checked/>
                </div>
                <div class="clean_row HSpace4" style="height:1rem"></div>
            ';
        $safo_arr[] = array("FineId"=>$r_Row['FineId'],
                            "NumeroVerbale"=>$r_Row['ProtocolId']."/".$r_Row['ProtocolYear'],
                            "Targa"=>$r_Row['VehiclePlate'],
                            "TipoVeicolo"=>$r_Row['VehicleTitleIta'][0],
                            "DataInfrazione"=>$r_Row['FineDate'],
                            "OraInfrazione"=>DateConvert($r_Row['FineTime'],'H:i'),
                            "DataScadenza"=>$within15,
                            "SocietaDiNoleggio"=>$r_Row['CompanyName'],
                            "ArticleEComma"=>$r_Row['Article']." ".$r_Row['Letter'],
                            "DescrizioneInfrazione"=>CommaReplace($r_Row['ArticleDescriptionIta']),
                            "Localita"=>$r_Row['Locality']);
    }
    $serialized_safo_arr = serialize($safo_arr);
    $str_out.= '<textarea name="export_data" style="display: none;">'. $serialized_safo_arr .'</textarea>';
}
$str_out.= '
                <div class="col-sm-12 BoxRowLabel" style="height:6rem;">
                    <div class="col-sm-12 BoxRowLabel" style="text-align:center;line-height:6rem;">
                        <button type="submit" class="btn btn-default" style="margin-top:1rem;">Invia</button>
                    </div>    
                </div>
            </div>
        </form>
    </div>
    ';

$table_users_number = $rs->Select('V_SafoTrespasser',$Search_Customer_Where,"ProtocolYear, FineId DESC");
$UserNumberTotal = mysqli_num_rows($table_users_number);

//$str_out.=CreatePagination(PAGE_NUMBER, $UserNumberTotal, $page, $str_CurrentPage,"");

echo $str_out;

?>
<script type="text/javascript">
    $(document).ready(function () {
        $('#Search_Customer').change(function(){
            var Search_Customer = $( "#Search_Customer" ).val();
            window.location = window.location.href+'&Search_Customer='+Search_Customer; // redirect
        });
    });
</script>
<?php
include(INC . "/footer.php");

function DateConvert($original_date, $format = "d/m/Y"){
    // Creating timestamp from given date
    $timestamp = strtotime($original_date);
    // Creating new date format from that timestamp
    $new_date = date($format, $timestamp);
    return $new_date; 
}

function CommaReplace($string){
    return str_replace(",", " ", $string);
}