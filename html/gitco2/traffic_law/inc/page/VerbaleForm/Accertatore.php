<?php
$str_AccertatoreBox = '';
$str_CssDisplayA = '';
$str_Test = '
    <i class="fa fa-caret-up" style="position:absolute; top:3px;right:3px;font-size: 2rem; display:none" id="upace"></i>
    <i class="fa fa-caret-down" style="position:absolute; bottom:3px;right:3px;font-size: 2rem;" id="downace"></i>
';
$rs = new CLS_DB();
$cityid = $_SESSION['cityid'];
$nr_Controller = 0;
if (isset($fineId) && $fineId > 0){
    $count_controller = $rs->SelectQuery("SELECT * FROM FineAdditionalController WHERE FineId = ".$fineId."");
    $nr_Controller = mysqli_num_rows($count_controller);
}


if ($nr_Controller > 0){
   $selected_controllers = $rs->SelectQuery("select FineAdditionalController.*,Controller.Id,Controller.Code,Controller.Name from FineAdditionalController LEFT JOIN Controller on FineAdditionalController.ControllerId = Controller.Id WHERE FineAdditionalController.FineId = ".$fineId."");


}
$results = $rs->SelectQuery("SELECT Id,Code, CONCAT(Code,' ',Name) AS Name FROM Controller WHERE CityId='".$cityid."' and disabled = 0 order by name");
$k = 0;
for($j=1; $j<=5; $j++){
    if ($j ==1){
        $insered_controller_id = $nr>0?$controller_id:"";
        $insered_controller_name = $nr>0?$controller_name:"";
    }else{
        if ($nr_Controller > 0) {
            if ($j - 1 <= $nr_Controller) {
                $controllers[$k] = mysqli_fetch_array($selected_controllers);

                $insered_controller_id = $controllers[$k]['Id'];
                $insered_controller_name = $controllers[$k]['Name'];
            } else {
                $insered_controller_id = "";
                $insered_controller_name = "";
            }
        }else{
            $insered_controller_id = "";
            $insered_controller_name = "";
        }

    }


    $str_AccertatoreBox .= '
                <div class="col-sm-6 pull-right"  id="BoxAccertatore_'.$j.'" '.$str_CssDisplayA.'>
                    <div class="col-sm-4 BoxRowLabel">
                        Accertatore
                    </div>          
                    <div class="col-sm-2 BoxRowCaption">
                        <input class="form-control frm_field_numeric input_accertatore_number" data-number="'.$j.'" type="text" name="ControllerCode" id="ControllerCode" style="width:5rem">
                    </div>
                    <div class="col-sm-6 BoxRowCaption">
                     <span id="span_acce_'.$j.'"></span>
                        <select class="form-control frm_field_required select_controller_'.$j.'" id="ControllerId[]" name="ControllerId[]" order="'.$j.'">
                        <option value="'.$insered_controller_id.'">'.$insered_controller_name.'</option>';
                            foreach ($results as $row_selected ){
                                $id = $row_selected['Id'];
                                $value = $row_selected['Name'];
                                $str_AccertatoreBox .= "<option  value=\"$id\">$value</option>";
                            }

    $str_AccertatoreBox .= '      </select>                        
                    </div>
                    <div style="height:4rem;">
                        <div class="col-sm-12 BoxRowLabel" style="height:4rem;background-color: rgb(40, 114, 150);">
                            <span id="span_Article_'.$j.'" style="font-size:1.1rem;">&nbsp;</span>
                            '. $str_Test .'
                            
                        </div>
                    </div> 
                </div> 
            <div class="clean_row HSpace4"></div>
';

    $str_CssDisplayA = $j <= $nr_Controller?"":'style="display: none;"';
    $str_Test = '';
}
?>

<script>
    $('document').ready(function () {
        var num = '<?php echo $nr_Controller+1;?>';
        $("#AccertatoreNumber").val(num);
        $('.input_accertatore_number').keyup(function (e) {
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