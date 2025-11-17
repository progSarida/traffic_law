<?php
$strAcertatore= '';
$str_CssDisplayA = '';
$str_Buttons = '
    <i class="fa fa-caret-up" style="position:absolute; top:3px;right:3px;font-size: 2rem; display:none" id="upace_upd"></i>
    <i class="fa fa-caret-down" style="position:absolute; bottom:3px;right:3px;font-size: 2rem;" id="downace_upd"></i>
';
$cityid = $_SESSION['cityid'];
$rs = new CLS_DB();

$all_controllers = $rs->SelectQuery("SELECT Id,Code, CONCAT(Code,' ',Name) AS Name FROM Controller WHERE CityId ='".$cityid."' and disabled = 0 order by name");

$first_controller = $rs->SelectQuery("select Controller.*,CONCAT(Controller.Code,' ',Controller.Name) AS Name,Fine.Id,Fine.ControllerId from Controller left join Fine on Controller.Id = Fine.ControllerId where traffic_law.Fine.Id ='".$Id."'");
$first_controller_array= array();
while($row=mysqli_fetch_array($first_controller)){

    $first_controller_array=array( 'Id'=>$row['ControllerId'],'Name'=>$row['Name'],'Code'=>$row['Code']);
}
$results_many = $rs->SelectQuery("select FineAdditionalController.*,Controller.Id,Controller.Code,CONCAT(Controller.Code,' ',Controller.Name) AS Name FROM FineAdditionalController LEFT JOIN Controller ON FineAdditionalController.ControllerId = Controller.Id WHERE FineAdditionalController.FineId = '.$Id.'");
$selected_controllers = array();
while($row_many=mysqli_fetch_array($results_many)){
    array_push($selected_controllers, array( 'Id'=>$row_many['Id'],'Name'=>$row_many['Name'],'Code'=>$row_many['Code']));
}
array_unshift($selected_controllers,$first_controller_array);

$nr = count($selected_controllers);
$j = 0;
$strAcertatore .= '<div id="Controllers">';

foreach ($selected_controllers as $row_select){
    $j++;
    $id = $row_select['Id'];
    $value = $row_select['Name'];
    $code = $row_select['Code'];

    $strAcertatore .= '<div class id="BoxAccertatore_' . $j . '"">	
                        <div class="col-sm-4 BoxRowLabel" style="height:4rem;background-color: rgb(40, 114, 150);">
        				Accertatore
        				<input type="hidden" id="nr" value="'.$nr.'">
					</div>
        			<div class="col-sm-2 BoxRowCaption" style="height:4rem;background-color: rgb(40, 114, 150);">
        				<input class="form-control frm_field_numeric input_accertatore_number" data-number="'.$j.'"  type="text" name="ControllerCode" id="ControllerCode" style="width:5rem">
					</div>				
					<div class="col-sm-6 BoxRowCaption datas_'.$j.'" style="height:4rem;background-color: rgb(40, 114, 150);">
					    <span id="span_acce_'.$j.'"></span>
                        <select class="form-control frm_field_required select_controller_'.$j.'" name="ControllerId[]" id="ControllerId[]" order="'.$j.'">';
                        foreach ($all_controllers as $row_selected ){

                            $id_all = $row_selected['Id'];
                            $value_all = $row_selected['Name'];
                            $code_all = $row_selected['Code'];
                            if ($value_all != $value) {
                                $strAcertatore .= "<option  value=\"$id_all\">$value_all</option>";
                            }
                        }
                        $strAcertatore .='<option  value="'.$id.'" selected>'.$value.'</option>';




    $strAcertatore .= '</select>
                    </div>
					<div style="height:3rem;">
                        <div class="col-sm-12 BoxRowLabel" style="height:4rem;background-color: rgb(40, 114, 150);">
                            <span id="span_Article_' . $j . '" style="font-size:1.1rem;">&nbsp;</span>
                            ' . $str_Buttons . '
                            
                        </div>
                    </div></div> ';




    $str_CssDisplayA = 'style="display: none;"';
    $str_Buttons = '';




}

$strAcertatore .= '</div>';
?>
<script>
    $('document').ready(function () {
        $(document).on('keyup','.input_accertatore_number',function (e) {
            const code = $(this).val();
            const index = $(this).data('number');
            let isFound = false;
            if(!code){
                $('.select_controller_' +index).val('');
                return;
            }
            $('.select_controller_' +index+' option').each(function(){
                $('.select_controller_'+index).trigger("change");
                if ($(this).html().startsWith(code)) {
                    $('.select_controller_' +index).val($(this).val());
                    isFound = true;
                }

            })
            if(!isFound){
                $('.select_controller_' +index).val('');
                return;
            }

        })
    });

</script>
