<?php
require("_path.php");
require(INC ."/parameter.php");
require(CLS ."/cls_db.php");
require(INC ."/function.php");
require(INC ."/header.php");
require(INC .'/menu_'.$_SESSION['UserMenuType'].'.php');

$a_Print_Type = array();
$rs_Print_Type = $rs->Select('Print_Type',"1=1");

while ($r_Print_Type = mysqli_fetch_array($rs_Print_Type)) {
    $a_Print_Type[$r_Print_Type['Id']] = $r_Print_Type['Name'] . ' - ' . $r_Print_Type['Description'];
}




	$rs_FlowDeadline = $rs->Select('FlowDeadlines',"1=1", "PrintTypeId, StartStatusId");



    $PrintTypeId = 0;
    while ($r_FlowDeadline = mysqli_fetch_array($rs_FlowDeadline)) {
        if($PrintTypeId != $r_FlowDeadline['PrintTypeId']){
            $PrintTypeId = $r_FlowDeadline['PrintTypeId'];
            $str_out.= '
                <div class="col-sm-12 BoxRowLabel table_caption_I" style="text-align:center">
                    '. $a_Print_Type[$r_FlowDeadline['PrintTypeId']] .'
                </div>
        
                <div class="clean_row HSpace4"></div> 
                
                <div class="col-sm-8 table_label_H">
                    Tipo atto
                </div>
                <div class="col-sm-2 table_label_H">
                    Italiano
                </div>
                <div class="col-sm-2 table_label_H">
                    Estero
                </div>
             
                <div class="clean_row HSpace4"></div> 
                
                ';

        }








        $str_out.= '

            <div class="col-sm-8 BoxRowLabel">
                '. $r_FlowDeadline['Label'] .' 
            </div> 
            <div class="col-sm-1 BoxRowCaption">
                <input class="form-control frm_field_numeric frm_field_required" type="text" name="N_flow_day_'. $r_FlowDeadline['Id'] .'" id="N_flow_day_'. $r_FlowDeadline['Id'] .'" style="max-width:10rem" value="'. $r_FlowDeadline['Days_N'] .'">
            </div> 
            <div class="col-sm-1 BoxRowCaption">
                <button data-toggle="tooltip" data-container="body" data-placement="left" title="Aggiorna" flow_deadline_id="'. $r_FlowDeadline['Id'] .'" flow_deadline_type="N" class="tooltip-r btn btn-info flow_procedure" style="width:100%;height:100%;padding:0"><i class="fa fa-refresh" ></i></button>
            </div>

            <div class="col-sm-1 BoxRowCaption">
                <input class="form-control frm_field_numeric frm_field_required" type="text" name="F_flow_day_'. $r_FlowDeadline['Id'] .'" id="F_flow_day_'. $r_FlowDeadline['Id'] .'" style="max-width:10rem" value="'. $r_FlowDeadline['Days_F'] .'">
            </div> 
            <div class="col-sm-1 BoxRowCaption">
                <button data-toggle="tooltip" data-container="body" data-placement="left" title="Aggiorna" flow_deadline_id="'. $r_FlowDeadline['Id'] .'" flow_deadline_type="F" class="tooltip-r btn btn-info flow_procedure" style="width:100%;height:100%;padding:0"><i class="fa fa-refresh" ></i></button>
            </div>                                
            <div class="clean_row HSpace4"></div>

            ';
        }



        $str_out.= '
	</div>';








echo $str_out;
?>

<script type="text/javascript">
  $('document').ready(function(){

      $('.flow_procedure').click(function () {
        let flow_type = $(this).attr("flow_deadline_type");
        let flow_id = $(this).attr("flow_deadline_id");
        let flow_day = $("#"+flow_type+"_flow_day_"+flow_id).val();
        let target = $(this);

        target.prop('disabled', true);
        target.find('i').toggleClass('fa-spin');

        $.ajax({
          url: 'ajax/ajx_upd_flowdeadline_exe.php',
          type: 'POST',
          dataType: 'json',
          cache: false,
          data: {Id: flow_id, Type: flow_type, Day: flow_day},
          success: function (data) {
            target.toggleClass('btn-info btn-success');
            target.find('i').toggleClass('fa-refresh fa-check fa-spin');
            setTimeout(function(){
              target.toggleClass('btn-info btn-success');
              target.find('i').toggleClass('fa-refresh fa-check');
              target.prop('disabled', false);
            }, 2000);
          },
        });

      });

    });
</script>
<?php
include(INC."/footer.php");
