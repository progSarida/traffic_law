<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
include(INC."/header.php");

require(INC . '/menu_'.$_SESSION['UserMenuType'].'.php');

$str_Back = '';
$str_Front = "";

$rs= new CLS_DB();
$rs->SetCharset('utf8');

$str_out ='
<div class="container-fluid">
    <div class="row-fluid">
        <div class="col-sm-12">
            <div class="col-sm-12" style="background-color: #fff">
                <img src="'.$_SESSION['blazon'].'" style="width:50px;">
                <span class="title_city">'.$_SESSION['citytitle'].' '.$_SESSION['year'].'</span>
            </div>
        </div>    
    </div>
</div>		   	
';

$rs_JudicialOffice = $rs->Select('JudicialOffice', "CityId='" . $_SESSION['cityid'] ."'");

$str_JudicialOffice = 'var a_JudicialOffice = [""';
while ($r_JudicialOffice = mysqli_fetch_array($rs_JudicialOffice)) {
    $str_JudicialOffice .= ', "'.$r_JudicialOffice['City'].'"';
}
$str_JudicialOffice .= '];';


    $str_Fine.= '
            <form name="frm_dispute_add" id="frm_dispute_add" method="post" action="mgmt_dispute_add_exe.php">
            
                
            <div class="col-sm-12 " style="margin-bottom:2rem;margin-top: 2rem;" >

                <div class="col-sm-6">
                    <div class="col-sm-12 BoxRowLabel" style="font-weight:bold;font-size:1.3rem">
                        RICORSO RELATIVO AI SEGUENTI VERBALI                                                   
                    </div>
                    
                    <div class="col-sm-12" id="fine_container" style="border:4px solid #6397e2; max-height:15rem;overflow:auto">
                        <input type="hidden" value="1" name="GradeTypeId" />
                        <input type="hidden" value="0" name="OwnerPresentation" />
                    </div>
                        
                </div>
                <div class="col-sm-6" >
                    <div class="col-sm-2 BoxRowLabel">
                            Trasgressore
                        </div>
                        <div class="col-sm-3 BoxRowCaption">
                            <input name="Search_Trespasser" id="Search_Trespasser" type="text" style="width:90%">
                        </div>
                        <div class="col-sm-2 BoxRowLabel">
                            Cronologico
                        </div>
                        <div class="col-sm-2 BoxRowCaption">
                            <input name="Search_Protocol" id="Search_Protocol" type="text" style="width:90%">
                        </div>
                        
                        <div class="col-sm-1 BoxRowLabel">
                            Targa
                        </div>
                         <div class="col-sm-2 BoxRowCaption">
                            <input name="Search_Plate" id="Search_Plate" type="text" style="width:90%">
                        </div>  
                    <div id="fine_content" class="col-sm-12" style="border:4px solid #6397e2; max-height:15rem; overflow:auto"></div>
                </div>
            </div>
            
            <div class="col-sm-12 ">
                <div class="col-sm-1 BoxRowLabel" style="font-weight:bold;font-size:1.2rem">
                    Registrazione
                </div>          
                <div class="col-sm-1 BoxRowCaption">
                    '.date("d/m/Y").'
                </div> 
                <div class="col-sm-1 BoxRowLabel" style="font-weight:bold;font-size:1.2rem">
                    Ufficio giudicante
                </div>          
                <div class="col-sm-2 BoxRowCaption" >
                    <select name="OfficeId" style="width:95%">
                        <option value="1">Giudice di Pace</option>
                        <option value="2">Prefetto</option>
                        <option value="3">Tribunale</option>
                    </select>
                </div>  
                <div class="col-sm-1 BoxRowCaption">
                    <input type="text" name="OfficeCity" id="OfficeCity" style="width:95%">
                </div>
                <div  class="col-sm-6 BoxRowCaption"></div>
            </div>
            <div class="col-sm-12" >
                <div class="col-sm-1 BoxRowLabel" style="font-weight:bold;font-size:1.2rem">
                    Altri dati ufficio
                </div>   
                <div class="col-sm-11 BoxRowCaption" style="height:7rem">
                    <textarea name="OfficeAdditionalData" style="width:95%;height:5.5rem"></textarea>
				</div>               
            </div>
            <div class="clean_row HSpace48"></div>
            <div class="col-sm-12 BoxRowCaption">
                <div class="col-sm-1 BoxRowCaption" style="font-weight:bold;font-size:1.3rem">
                    RICORSO
                </div> 
                <div class="col-sm-2 BoxRowLabel" style="font-weight:bold;font-size:1.2rem">
                    Data Spedizione
                </div>          
                <div class="col-sm-1 BoxRowCaption" style="font-size:1.2rem">
                    <input type="text" name="DateSend" id="DateSend" class="form-control frm_field_date" style="width:9rem">
                </div>
                <div class="col-sm-2 BoxRowLabel" style="font-weight:bold;font-size:1.2rem">
                    Data Ricezione
                </div>          
                <div class="col-sm-1 BoxRowCaption" style="font-size:1.2rem">
                    <input type="text" name="DateReceive" id="DateReceive" class="form-control frm_field_date" style="width:9rem">
                </div>
                <div class="col-sm-2 BoxRowLabel" style="font-weight:bold;font-size:1.2rem">
                    Data Deposito
                </div>          
                <div class="col-sm-1 BoxRowCaption" style="font-size:1.2rem">
                    <input type="text" name="DateFile" id="DateFile" class="form-control frm_field_date" style="width:9rem">
                </div>
            </div>
            <div class="col-sm-12 BoxRowCaption">
                <div class="col-sm-1 BoxRowCaption"></div>
                <div class="col-sm-1 BoxRowLabel" style="font-weight:bold;font-size:1.2rem">
                    RG
                </div>          
                <div class="col-sm-2 BoxRowCaption" style="font-size:1.2rem">
                    <input type="text" name="Number" class="form-control frm_field_string" style="width:15rem">
                </div>                
                 <div class="col-sm-1 BoxRowLabel" style="font-weight:bold;font-size:1.2rem">
                    SEZ
                </div>          
                <div class="col-sm-2 BoxRowCaption" style="font-size:1.2rem">
                    <input type="text" name="Division" class="form-control frm_field_string" style="width:15rem">
                </div>                
            </div>
            <div class="col-sm-12 BoxRowCaption">
                <div class="col-sm-1 BoxRowCaption"></div>
                <div class="col-sm-2 BoxRowLabel" style="font-weight:bold;font-size:1.2rem">
                    Data provvedimento
                </div>          
                <div class="col-sm-1 BoxRowCaption" style="font-size:1.2rem">
                    <input type="text" name="DateMeasure" id="DateMeasure" class="form-control frm_field_date" style="width:9rem">
                </div>            
                <div class="col-sm-2 BoxRowLabel" style="font-weight:bold;font-size:1.2rem">
                    Numero provvedimento
                </div>          
                <div class="col-sm-2 BoxRowCaption" style="font-size:1.2rem">
                    <input type="text" name="MeasureNumber" id="MeasureNumber" class="form-control frm_field_string" style="width:15rem">
                </div>            
                <div class="col-sm-1 BoxRowLabel" style="font-weight:bold;font-size:1.2rem">
                    Sospensione verbale
                </div>          
                <div class="col-sm-1 BoxRowCaption" style="font-size:1.2rem">
                    <select name="FineSuspension">
                        <option value="1">SI</option>
                        <option value="0">NO</option>
                    </select>
                </div> 
            </div>
            
            <div class="clean_row HSpace48"></div>
            <div class="col-sm-12 BoxRowCaption">
                <div class="col-sm-1 BoxRowCaption" style="font-weight:bold;font-size:1.3rem">
                    ENTE
                </div> 
                <div class="col-sm-2 BoxRowLabel" style="font-weight:bold;font-size:1.2rem">
                    Data Protocollo
                </div>          
                <div class="col-sm-1 BoxRowCaption">
                    <input type="text" name="DateProtocolEntity" id="DateProtocolEntity" class="form-control frm_field_date" style="width:9rem">
                </div>
                <div class="col-sm-2 BoxRowLabel" style="font-weight:bold;font-size:1.2rem">
                    Numero protocollo
                </div>          
                <div class="col-sm-2 BoxRowCaption">
                    <input type="text" name="EntityProtocolNumber" id="EntityProtocolNumber" class="form-control frm_field_string" style="width:15rem">
                </div>                             
            </div>
            <div class="clean_row HSpace48"></div>
            <div class="col-sm-12 BoxRowCaption">
                <div class="col-sm-1 BoxRowCaption" style="font-weight:bold;font-size:1.3rem">
                    PRESA IN CARICO
                </div> 
                  
                <div class="col-sm-2 BoxRowLabel" style="font-weight:bold;font-size:1.2rem">
                    Data Protocollo
                </div>          
                <div class="col-sm-1 BoxRowCaption">
                    <input type="text" name="DateProtocol" id="DateProtocol" class="form-control frm_field_date" style="width:9rem">
                </div>
                <div class="col-sm-2 BoxRowLabel" style="font-weight:bold;font-size:1.2rem">
                    Numero protocollo
                </div>          
                <div class="col-sm-2 BoxRowCaption">
                    <input type="text" name="ProtocolNumber" id="ProtocolNumber" class="form-control frm_field_string" style="width:15rem">
                </div>                        
            </div>       
 
            <div class="clean_row HSpace48"></div>

            <div class="col-sm-12 BoxRow">
                <div class="col-sm-12 BoxRow" style="height:6rem;">
    
                    <div class="col-sm-12 BoxRowLabel" style="text-align:center;line-height:6rem;">
                        <input type="submit" class="btn btn-default" id="save" style="margin-top:1rem;"  value="Salva" />
                    </div>    
                 </div>
            </div>
      	    </form>
';



echo $str_Fine;

?>


    <script type="text/javascript">

        <?= $str_JudicialOffice ?>

        $('document').ready(function () {
            $('#OfficeCity').val(a_JudicialOffice[1]);






            $("input:radio[name='OfficeId']").click(function() {
                $('#OfficeCity').val(a_JudicialOffice[$("input:radio[name='OfficeId']:checked").val()]);
            });







            $('#Search_Protocol').keyup(function () {

                var Search_Protocol = $(this).val();
                var Search_Trespasser = $('#Search_Trespasser').val();
                var Search_Plate = $('#Search_Plate').val();


                $.ajax({
                    url: 'ajax/search_finedispute.php',
                    type: 'POST',
                    dataType: 'json',
                    cache: false,
                    data: {Search_Protocol: Search_Protocol, Search_Trespasser: Search_Trespasser, Search_Plate:Search_Plate},
                    success: function (data) {
                        $('#fine_content').show();
                        $('#fine_content').html(data.Trespasser);
                    }
                });

            });
            $('#Search_Trespasser').keyup(function () {

                var Search_Trespasser = $(this).val();
                var Search_Protocol = $('#Search_Protocol').val();
                var Search_Plate = $('#Search_Plate').val();


                if (Search_Trespasser.length >= 3) {
                    $.ajax({
                        url: 'ajax/search_finedispute.php',
                        type: 'POST',
                        dataType: 'json',
                        cache: false,
                        data: {Search_Protocol: Search_Protocol, Search_Trespasser: Search_Trespasser, Search_Plate:Search_Plate},
                        success: function (data) {
                            console.log(data);

                            $('#fine_content').html(data.Trespasser);
                            $('#fine_content').show();
                        },
                        error: function (data) {
                            console.log(data);
                        }
                    });
                }
            });

            $('#Search_Plate').keyup(function () {

                var Search_Plate = $(this).val();
                var Search_Protocol = $('#Search_Protocol').val();
                var Search_Trespasser = $('#Search_Trespasser').val();

                if (Search_Plate.length >= 3) {
                    $.ajax({
                        url: 'ajax/search_finedispute.php',
                        type: 'POST',
                        dataType: 'json',
                        cache: false,
                        data: {Search_Protocol: Search_Protocol, Search_Trespasser: Search_Trespasser, Search_Plate:Search_Plate},
                        success: function (data) {
                            $('#fine_content').show();
                            $('#fine_content').html(data.Trespasser);
                        }
                    });
                }
            });


        });



        $('#frm_dispute_add').bootstrapValidator({
            live: 'disabled',
            fields: {

                DateSend: {
                    validators: {
                        date: {
                            format: 'DD/MM/YYYY',
                            message: 'Data non valida'
                        }

                    }
                },
                DateReceive: {
                    validators: {
                        notEmpty: {message: 'Obbligatorio'},

                        date: {
                            format: 'DD/MM/YYYY',
                            message: 'Data non valida'
                        }

                    },

                },
                DateFile: {
                    validators: {
                        notEmpty: {message: 'Obbligatorio'},

                        date: {
                            format: 'DD/MM/YYYY',
                            message: 'Data non valida'
                        }

                    },

                },
                DateHearing:{
                    validators: {
                        date: {
                            format: 'DD/MM/YYYY',
                            message: 'Data non valida'
                        }

                    },

                },


            }
        });


    </script>
<?php


include(INC."/footer.php");





