<?php
include("_path.php");
include(INC . "/parameter.php");
include(CLS . "/cls_db.php");
include(INC . "/function.php");
require(INC . "/initialization.php");
include(INC . "/header.php");

require(INC . '/menu_'.$_SESSION['UserMenuType'].'.php');

echo $str_out;
?>

<div class="col-sm-12">
    <div class="col-sm-12 alert alert-danger" style="display: flex;margin: 0px;align-items: center;">
        <i class="fas fa-2x fa-info-circle col-sm-1" style="text-align:center;"></i>
        <div class="col-sm-11" style="font-size: 1.2rem;">
            <ul style="list-style-position: inside;">
                <li>Nota: Se il verbale da cercare era incluso tra quelli con anomalie va prima tolto dalle anomalie con <i class="fa fa-history fa-fw"></i> (Reincludi nell'elaborazione) prima di esser sanato con la funzione di registrazione.</li>
            </ul>
        </div>
    </div>
</div>

<div class="row-fluid">
    <form name="f_decurtation" id="f_decurtation" action="mgmt_licensepoint_add_exe.php" method="post">
    <input type="hidden" name="Search_FineId" id="Search_FineId"  value="">
    <div class="col-sm-12" >
        <div class="col-sm-4 BoxRowLabel">
            Inserimento decurtazione/riattribuzione verbale
        </div>
        <div id="span_name" class="col-sm-8 BoxRowCaption">
        </div>
        
        <div class="clean_row HSpace4"></div>
        
        <div class="col-sm-1 BoxRowLabel">
            Operazione
        </div>
        <div class="col-sm-5 BoxRowCaption">
                <input type="radio" name="operation"  id="operation_decurtation" value="decurtation" checked style="top:0;"><span style="position:relative;top:-0.3rem"> Decurtazione</span>
                <input type="radio" name="operation" id="operation_reattribution" value="reattribution" style="top:0;"><span style="position:relative;top:-0.3rem"> Riattribuzione</span>
        </div>
        <div class="col-sm-1 BoxRowLabel">
            Punti
        </div>
        <div class="col-sm-1 BoxRowCaption">
            <input class="form-control frm_field_numeric frm_field_required" type="text" name="DecurtationPoint" id="DecurtationPoint" style="width:5rem">	
        </div>

        <div class="col-sm-2 BoxRowLabel">
            Data decurtazione
        </div>
        <div class="col-sm-1 BoxRowCaption">
            <input class="form-control frm_field_date frm_field_required" type="text" name="DecurtationDate" id="DecurtationDate">	
        </div>
        <div class="col-sm-1 BoxRowCaption">
        </div>
        
        <div class="clean_row HSpace4"></div>
        
        <div class="col-sm-1 BoxRowLabel">
            Anno
        </div>
        <div class="col-sm-1 BoxRowCaption">
            <input class="form-control frm_field_numeric" name="Search_Year" id="Search_Year" type="text" style="width:10rem">
        </div>
        <div class="col-sm-1 BoxRowLabel">
            Cron.
        </div>
        <div class="col-sm-1 BoxRowCaption">
            <input class="form-control frm_field_numeric" name="Search_Protocol" id="Search_Protocol" type="text">
        </div>
        <div class="col-sm-1 BoxRowLabel">
            Riferimento
        </div>
        <div class="col-sm-1 BoxRowCaption">
            <input class="form-control frm_field_string" name="Search_Code" id="Search_Code" type="text">
        </div>
        <div class="col-sm-1 BoxRowLabel">
            Trasgressore
        </div>                            
        <div class="col-sm-2 BoxRowCaption">
            <input class="form-control frm_field_string" name="Search_Trespasser" id="Search_Trespasser" type="text">
        </div>
        <div class="col-sm-1 BoxRowLabel">
            Targa
        </div>                              
        <div class="col-sm-1 BoxRowCaption">
            <input class="form-control frm_field_string" name="Search_Plate" id="Search_Plate" type="text">
        </div>
        <div class="col-sm-1 BoxRowCaption">
            <button id="search" type="button" data-container="body" data-toggle="tooltip" data-placement="top" title="Cerca verbale" class="btn btn-primary tooltip-r" style="margin:0;width:100%;height:100%;padding:0;">
                <i class="glyphicon glyphicon-search"></i>
            </button>
        </div> 

        <div class="col-sm-12 BoxRow" style="height:auto;">
            <div id="fine_content" class="col-sm-12" style="height:150px;overflow:auto"></div>
            <div id="payment_content" class="col-sm-12" style="margin-top:2rem;height:100px;overflow:auto"></div>            
        </div>
    </div>
    
    <div class="clean_row HSpace4"></div>
    
    <div class="col-sm-12">
        <div class="col-sm-12 table_label_H HSpace4" style="height:6rem;">
            <div class="col-sm-12" style="text-align:center;line-height:6rem;">
                <button class="btn btn-success" type="submit" id="save"disabled><i class="fa fa-fw fa-save"></i> Salva</button>
                <button class="btn btn-default" type="button" id="back">Indietro</button>
             </div>    
        </div>
    </div>      
    </form>
            
</div> 

<script type="text/javascript">
    $('document').ready(function () {

        $('#f_decurtation').on('submit', function (e) {
            $('#save i').toggleClass('fa-circle-notch fa-spin fa-save');
            $('#save').prop('disabled', true);
        });

        $('#f_decurtation').on('keyup keypress', function(e) {
            var keyCode = e.keyCode || e.which;
            if (keyCode === 13) {
                e.preventDefault();
                return false;
            }
        });

        $('#search').click(function () {
            var button = $(this);
            var Search_Protocol = $('#Search_Protocol').val();
            var Search_Trespasser = $('#Search_Trespasser').val();
            var Search_Plate = $('#Search_Plate').val();
            var Search_Code = $('#Search_Code').val();
            var Search_Year = $('#Search_Year').val();
            var Amount= $('#Amount').val();

            button.html('<i class="fa fa-circle-notch fa-spin"></i>');
            button.prop('disabled', true);

            $.ajax({
                url: 'ajax/ajx_src_decurtationfine.php',
                type: 'POST',
                dataType: 'json',
                cache: false,
                data: {Amount:Amount, Search_Code: Search_Code,SearchYear:Search_Year, Search_Protocol: Search_Protocol, Search_Trespasser: Search_Trespasser, Search_Plate:Search_Plate},
                success: function (data) {
                    $('#fine_content').show();
                    $('#fine_content').html(data.Trespasser);
                    button.html('<i class="glyphicon glyphicon-search"></i>');
                    button.prop('disabled', false);
                },
                error: function (data) {
                    console.log(data);
                    alert("error: " + data.responseText);
                    button.html('<i class="glyphicon glyphicon-search"></i>');
                    button.prop('disabled', false);
                }
            });
        });
        
        $('#back').click(function () {
        	window.location = "<?=impostaParametriUrl(array('btn_search' => 1), $str_BackPage)?>";
            return false;
        });
        
        $('#operation_decurtation').change(function(){
            $('#DecurtationPoint').prop('disabled',false);
            $('#DecurtationDate').prop('disabled',false);
            $('#DecurtationPoint').prop('class','form-control frm_field_numeric frm_field_required');
            $('#DecurtationDate').prop('class','form-control frm_field_numeric frm_field_required');

        });
        
        $('#operation_reattribution').change(function(){
            $('#DecurtationPoint').prop('class','form-control frm_field_numeric');
            $('#DecurtationDate').prop('class','form-control frm_field_numeric');
            $('#DecurtationPoint').prop('disabled',false);
            $('#DecurtationDate').prop('disabled',false);;
        });
    });
</script>

<?php
include(INC . "/footer.php");
