<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
include(INC."/header.php");

include(INC."/menu.php");

$LinkPage = curPageName();
$Id= CheckValue('Id','n');


$rs= new CLS_DB();

$charge_rows = $rs->Select('CustomerCharge',"CityId='".$_SESSION['cityid']."' AND ToDate IS NULL", "Id");
$charge_row = mysqli_fetch_array($charge_rows);

$TotalCharge = $charge_row['CustomerNotificationFee'] + $charge_row['CustomerResearchFee'] + $charge_row['NotificationFee'] + $charge_row['ResearchFee'];


$table_rows = $rs->Select('V_Fine',"Id=$Id", "Id");
$table_row = mysqli_fetch_array($table_rows);



$TotalFee = $TotalCharge  + $table_row['Fee'];










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
        <form name="f_violation" id="f_violation" method="post" action="fine_trespasser_exe.php">
        <input type="hidden" id="Id" name="Id" value="'.$Id.'">
        <input type="hidden" id="TrespasserId" name="TrespasserId">
        
    	<div class="row-fluid">
        	<div class="col-sm-6">
	        	<div class="col-sm-12 BoxRow">
        			<div class="col-sm-5 BoxRowLabel">
        				Riferimento
					</div>
					<div class="col-sm-7 BoxRowCaption">
        				'.$table_row['Code'].'
					</div>
  				</div> 
	        	<div class="col-sm-12 BoxRow">
        			<div class="col-sm-5 BoxRowLabel">
        				Data
					</div>
					<div class="col-sm-7 BoxRowCaption">
        				'.DateOutDB($table_row['FineDate']).'
					</div>
  				</div> 
	        	<div class="col-sm-12 BoxRow">
        			<div class="col-sm-5 BoxRowLabel">
        				Ora
					</div>
					<div class="col-sm-7 BoxRowCaption">
        				'.TimeOutDB($table_row['FineTime']).'
					</div>
  				</div> 
	        	<div class="col-sm-12 BoxRow">
        			<div class="col-sm-5 BoxRowLabel">
        				Località
					</div>
					<div class="col-sm-7 BoxRowCaption">
        				'.$table_row['Address'].'	
					</div>
  				</div> 											
	        	<div class="col-sm-12 BoxRow" >
        			<div class="col-sm-2 BoxRowLabel">
        				Articolo
					</div>
					<div class="col-sm-2 BoxRowCaption">
					   '.$table_row['Article'].' 				
 					</div>
 					<div class="col-sm-2 BoxRowLabel">
        				Comma
					</div>
					<div class="col-sm-2 BoxRowCaption">
        				'.$table_row['Paragraph'].'
 					</div>

        			<div class="col-sm-2 BoxRowLabel">
        				Lettera
					</div>
					<div class="col-sm-2 BoxRowCaption">
        				'.$table_row['Letter'].'
 					</div>
  				</div>  
	        	<div class="col-sm-12 BoxRow" style="height:6rem;">
        			<div class="col-sm-12 BoxRowLabel">
            			<span id="span_Article" style="height:6rem;width:40rem;font-size:1.1rem;">'.$table_row['ArticleDescription'.LAN].'</span>
        			</div>
  				</div>  

        		
	        	<div class="col-sm-12 BoxRow">
        			<div class="col-sm-5 BoxRowLabel">
        				Tipo infrazione
					</div>
					<div class="col-sm-7 BoxRowCaption">
        				<input type="hidden" name="ViolationTypeId" id="ViolationTypeId">
        				<span id="span_ViolationTitle" style="height:6rem;width:40rem;font-size:1.1rem;">'.$table_row['ViolationTitle'].'</span>
					</div>
  				</div> 
	        	<div class="col-sm-12 BoxRow">
        			<div class="col-sm-5 BoxRowLabel">
        				Importo infrazione
					</div>
					<div class="col-sm-7 BoxRowCaption">
        				'.$table_row['Fee'].'
					</div>
  				</div> 
	        	<div class="col-sm-12 BoxRow">
        			<div class="col-sm-5 BoxRowLabel">
        				Spese notifica
					</div>
					<div class="col-sm-7 BoxRowCaption">
        				'.$TotalCharge.'
					</div>
  				</div>    			
	        	<div class="col-sm-12 BoxRow">
        			<div class="col-sm-5 BoxRowLabel">
        				Importo totale
					</div>
					<div class="col-sm-7 BoxRowCaption">
        				'. $TotalFee .'
					</div>
  				</div>
	        	<div class="col-sm-12 BoxRow">
        			<div class="col-sm-5 BoxRowLabel">
        				Targa
					</div>
					<div class="col-sm-7 BoxRowCaption">
        				'.$table_row['VehiclePlate'].'
					</div>
  				</div> 
	        	<div class="col-sm-12 BoxRow">
        			<div class="col-sm-5 BoxRowLabel">
        				Marca
					</div>
					<div class="col-sm-7 BoxRowCaption">
        			    '.$table_row['VehicleBrand'].'
					</div>
  				</div> 

  			</div>	     	
        	<div class="col-sm-6">
  	            <div class="col-sm-12 BoxRow" style="height:12.5rem">
        			<div class="col-sm-12 BoxRowLabel" style="text-align:center">
        				Assegnazione trasgressore:<br />
        				&nbsp;<span id="span_name"></span>        				
					</div>
        			<div class="col-sm-4 BoxRowCaption" style="text-align:center">
        				'. CreateSelect("TrespasserType","1=1","Id","TrespasserTypeId","Id","Title","",true) .'		
					</div>
					<div class="col-sm-8 BoxRow" style="height:6.4rem;">
        			    <div class="col-sm-2 BoxRowLabel">
        				    Note
					    </div>
					    <div class="col-sm-10 BoxRowCaption">
                         <textarea name="Note" style="width:30rem;height:5.5rem"></textarea>	
					    </div>
					</div>    
  				</div>
					
	        	<div class="col-sm-12 BoxRow" style="height:38rem;">
                    <ul class="nav nav-tabs" id="mioTab">
                        <li class="active" id="tab_company"><a href="#company" data-toggle="tab">DITTA</a></li>
                        <li id="tab_Trespasser"><a href="#Trespasser" data-toggle="tab">PERSONA</a></li>
                    </ul>
                    <div class="tab-content">
                        <div class="tab-pane active" id="company">
                            <div class="row-fluid">
                                <div class="col-sm-12 BoxRow">
                                    <div class="col-sm-4 BoxRowLabel">
                                        <input type="hidden" name="Genre" id="Genre" value="D">
                                        <input type="hidden" id="VehiclePlate" name="VehiclePlate" value="'.$table_row['VehiclePlate'].'">
                                        Ragione sociale
                                    </div>
                                    <div class="col-sm-5 BoxRowCaption">
                                        <input name="CompanyName" id="CompanyName" type="text" style="width:20rem">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane" id="Trespasser">
                            <div class="row-fluid">
                
                                <div class="col-sm-12 BoxRow">
                                    <div class="col-sm-2 BoxRowLabel">
                                        Cognome
                                    </div>
                                    <div class="col-sm-4 BoxRowCaption">
                                        <input type="text" name="Surname" id="Surname" style="width:12rem">
                                    </div>
                                
                                    <div class="col-sm-2 BoxRowLabel">
                                        Nome
                                    </div>
                                    <div class="col-sm-4 BoxRowCaption">
                                        <input type="text" name="Name" id="Name" style="width:12rem">
                                    </div>
                                    
                                </div>
                                     
                            </div>
                                       
                        </div> 
                        <div id="trespasser_content" class="col-sm-12" style="height:150px;overflow:auto"></div>
                        <div id="plate_content" class="col-sm-12" style="margin-top:2rem;height:100px;overflow:auto"></div>
                    </div>
                </div>

            </div>      	           	
        </div> 
        <div class="col-sm-12">
            <div class="col-sm-12 BoxRow" style="height:6rem;">

                <div class="col-sm-12 BoxRowLabel" style="text-align:center;line-height:6rem;">
                    <button class="btn btn-default" id="update" style="margin-top:1rem;" disabled>Salva</button>
                </div>    
             </div>
        </div>
    </form>	
    </div>';



echo $str_out;
?>
<script type="text/javascript">
    var namespan;
    $('document').ready(function(){
        $('#tab_company').click(function(){
            $('#Genre').val('D');
            $('#Surname').val('');
            $('#Name').val('');

        });

        $('#tab_Trespasser').click(function(){
            $('#Genre').val('M');
            $('#CompanyName').val('');
        });



        var min_length = 2; // min caracters to display the autocomplete
        $('#CompanyName').keyup(function(){

            var CompanyName = $(this).val();
            var Genre = $('#Genre').val();
            var VehiclePlate = $('#VehiclePlate').val();
            var Id = $('#Id').val();
            
            if (CompanyName.length >= min_length) {
                $.ajax({
                    url: 'ajax/search_trespasser.php',
                    type: 'POST',
                    dataType: 'json',
                    cache: false,
                    data: {CompanyName:CompanyName, Genre:Genre, VehiclePlate:VehiclePlate, Id:Id},
                    success:function(data){

                        $('#trespasser_content').show();
                        $('#trespasser_content').html(data.Trespasser);

                        $('#plate_content').show();
                        $('#plate_content').html(data.Plate);
                    }
                });
            } else {
                $('#trespasser_content').hide();
                $('#plate_content').hide();
            }
        });

        $('#Name').keyup(function(){
            var Name = $(this).val();
            var Surname = $('#Surname').val();
            var Genre = $('#Genre').val();
            var VehiclePlate = $('#VehiclePlate').val();
            var Id = $('#Id').val();

            if (Name.length >= min_length) {
                $.ajax({
                    url: 'ajax/search_trespasser.php',
                    type: 'POST',
                    dataType: 'json',
                    cache: false,
                    data: {Surname:Surname, Name:Name, Genre:Genre, VehiclePlate:VehiclePlate, Id:Id},
                    success:function(data){

                        $('#trespasser_content').show();
                        $('#trespasser_content').html(data.Trespasser);

                        $('#plate_content').show();
                        $('#plate_content').html(data.Plate);
                    }
                });
            } else {
                $('#trespasser_content').hide();
                $('#plate_content').hide();
            }
        });

        $('#Surname').keyup(function(){
            var Name = $('#Name').val();
            var Surname = $(this).val();
            var Genre = $('#Genre').val();
            var VehiclePlate = $('#VehiclePlate').val();
            var Id = $('#Id').val();

            if (Surname.length >= min_length) {
                $.ajax({
                    url: 'ajax/search_trespasser.php',
                    type: 'POST',
                    dataType: 'json',
                    cache: false,
                    data: {Surname:Surname, Name:Name, Genre:Genre, VehiclePlate:VehiclePlate, Id:Id},
                    success:function(data){

                        $('#trespasser_content').show();
                        $('#trespasser_content').html(data.Trespasser);

                        $('#plate_content').show();
                        $('#plate_content').html(data.Plate);
                    }
                });
            } else {
                $('#trespasser_content').hide();
                $('#plate_content').hide();
            }
        });



    });

</script>
<?php
include(INC."/footer.php");
