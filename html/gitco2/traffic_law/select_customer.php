<?php
require_once("_path.php");
require_once(INC."/parameter.php");
require_once(CLS."/cls_db.php");
require_once(INC."/function.php");
require_once(CLS."/cls_flow.php");

$rs= new CLS_DB();
$rs->SetCharset("utf8");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta charset="UTF-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" id="viewport"/>

        <title>SARIDA - Traffic Law</title>
        <link href="<?= LIB ?>/datepicker/css/bootstrap-datepicker.css" rel="stylesheet" media="screen"/>
        <link rel="stylesheet" href="<?= FONT ?>/font-awesome/css/font-awesome.min.css"/>
        <link rel="stylesheet" href="<?= CSS ?>/bootstrap.css" type="text/css" media="all" />
        <link rel="stylesheet" href="<?= CSS ?>/bootstrap-theme.css" type="text/css" media="all" />
        <link rel="stylesheet" href="<?= CSS ?>/sarida.css" type="text/css" media="all" />

        <script src="<?= JS ?>/jquery-1.12.0.js" type="text/javascript"></script>
        <script src="<?= JS ?>/bootstrap.js" type="text/javascript"></script>
        <script type="text/javascript" src="<?= LIB ?>/datepicker/js/bootstrap-datepicker.js" charset="UTF-8"></script>
        <script type="text/javascript" src="<?= LIB ?>/validator/js/bootstrapValidator.js"></script>

        <link rel="stylesheet" href="<?= LIB ?>/filetree/css/jqueryFileTree.css" type="text/css" media="all" />
        <script type="text/javascript" src="<?= LIB ?>/filetree/js/jqueryFileTree.js"></script>

        <script src="<?= LIB ?>/zoom/js/zoom.js"></script>
        <link rel="stylesheet" href="<?= LIB ?>/upload/css/style.css" type="text/css" media="all" />
        <script type="text/javascript" src="<?= LIB ?>/upload/js/jquery.knob.js"></script>
    	<style>
		  #mainSelect .BoxRowLabel,.BoxRowCaption {height:5rem;line-height:5rem;font-size:3rem;}
		  #mainSelect .BoxRowLabel > select,.BoxRowCaption > select {height:100%;font-size:3rem;background:white}
		  .detail_flow:hover {color:white;cursor:pointer;}
        </style>
	</head>
	<body>
        <nav class="navbar navbar-default">
        	<!-- Brand and toggle get grouped for better mobile display -->
        	<div class="navbar-header FN_Menu">
        		<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar-collapse-1" aria-expanded="false">
        			<span class="sr-only">Toggle navigation</span>
        			<span class="icon-bar"></span>
        			<span class="icon-bar"></span>
        			<span class="icon-bar"></span>
        		</button>
        		<a class="navbar-brand" href="#">
        			<img alt="SARIDA" src="../img/sarida_logo_medium.png" class="FN_Logo_Menu"/>
        		</a>
        	</div>
        
        	<!-- Collect the nav links, forms, and other content for toggling -->
        	<div class="collapse navbar-collapse FN_Menu">
        
        		<ul class="nav navbar-nav navbar-right" id="menu_r">
        			<li><a href="#"><span class="glyphicon glyphicon-user"></span><?= '&nbsp;'.$_SESSION['username']; ?></a></li>
        			<li><a href="logout.php"><span class="glyphicon glyphicon-log-out"></span> Logout</a></li>
        		</ul>
        	</div><!-- /.navbar-collapse -->
        </nav>
        
        <div class="container-fluid">
        	<div class="row">
        		<div class="col-sm-12 alert alert-info" style="margin:0">
        			<div id="mainSelect" class="col-sm-6 col-sm-offset-3">
	            		<div class="col-sm-6 BoxRowLabel text-center">
                			Scegliere ente:
            			</div>
	        			<div class="col-sm-6 BoxRowCaption">
        					<?= CreateArraySelect(array_column($_SESSION['CityArray'][MENU_ID], 'CityTitle', 'CityId'), true, "cityid", "cityid", null, false); ?>
        				</div>
        				
    					<div class="clean_row HSpace4"></div>
        				
	            		<div class="col-sm-6 BoxRowLabel text-center">
                			Scegliere regolamento:
            			</div>
	        			<div class="col-sm-6 BoxRowCaption">
        					<select disabled id="ruletype" name="ruletype" class="form-control"></select>
        				</div>
        				
        				<div class="clean_row HSpace4"></div>
        				
	            		<div class="col-sm-6 BoxRowLabel text-center">
                			Scegliere anno:
            			</div>
	        			<div class="col-sm-6 BoxRowCaption">
        					<select disabled id="year" name="year" class="form-control"></select>
        				</div>
        			</div>
        		</div>
        	</div>
        	<div class="row">
            	<?php if($_SESSION['userlevel'] >= 3): ?>
            		<?php 
            		$cls_flow = new cls_flow();
            		$a_flows = $cls_flow->getFlowsNumber();
            		?>
                    <div class="table_label_H col-sm-12" style="height:3rem;font-size: 1.6rem;line-height: 3rem;">
                    	<b>ANALISI FLUSSI</b>
                	</div>
                	
                    <div class="clean_row HSpace4"></div>
                    
                    <div class="col-sm-12">
                        <ul class="nav nav-tabs" id="mioTab">
                            <li tab_position="1" class="tab_button active" id="tab_AG"><a href="#AG" data-toggle="tab">AG</a></li>
                            <li tab_position="2" class="tab_button" id="tab_PEC"><a href="#PEC" data-toggle="tab">PEC</a></li>
                            <li tab_position="3" class="tab_button" id="tab_AR"><a href="#AR" data-toggle="tab">AR</a></li>
                            <li tab_position="4" class="tab_button" id="tab_AvvisoBonario"><a href="#AvvisoBonario" data-toggle="tab">Avviso Bonario</a></li>
                            <li tab_position="5" class="tab_button" id="tab_LetteraOrdinaria"><a href="#LetteraOrdinaria" data-toggle="tab">Lettera Ordinaria</a></li>
                            
                       </ul>
                    </div>
                    <div class="tab-content">
                        <div class="tab-pane active" id="AG"><?= $cls_flow->htmlFlowNumber($a_flows,1) ?></div>
                        <div class="tab-pane" id="PEC"><?= $cls_flow->htmlFlowNumber($a_flows,7) ?></div>
                        <div class="tab-pane" id="AR"><?= $cls_flow->htmlFlowNumber($a_flows,2) ?></div>
                        <div class="tab-pane" id="AvvisoBonario"><?= $cls_flow->htmlFlowNumber($a_flows,3) ?></div>
                        <div class="tab-pane" id="LetteraOrdinaria"><?= $cls_flow->htmlFlowNumber($a_flows,4) ?></div>            
                    </div>
            	<?php endif; ?>
    	        <div id="DIV_FlowContent" style="display: none; position:absolute;top:10%;left:30%; z-index: 900; width:40rem; height:50rem; background-color:#fff">
                    <div class="col-sm-12">
                        <div class="col-sm-12 table_label_H" style="text-align:center">
                            Lista Flussi
                        </div>
                        <span class="fa fa-times-circle close_window" style="color:#fff;position:absolute; right:10px;top:2px;font-size:20px; "></span>
                    </div>
                    
                    <div class="clean_row HSpace4"></div>
                    
                    <div class="col-sm-12" id="DIV_FlowContentDetail" style="height:40rem"></div>
                </div>
            </div>
        </div>
        <script type="text/javascript">
    		$(document).ready(function () {
                $('.detail_flow').click(function () {
                    let id_city = $( this ).attr('id_city');
                    let id_print_type = $( this ).attr('id_print_type');
                    let id_type = $( this ).attr('id_type');
                    
                    
                    let start_status = $( this ).attr('start_status');
                    let end_status = $( this ).attr('end_status');
                    let day_n = $( this ).attr('day_n');
                    
                    //alert(id_city+ ' ' + id_print_type + ' '+id_type + ' '+start_status+' '+ end_status + '  '+  day_n);
                    
                    $.ajax({
                      url: 'ajax/ajx_src_flowcontent.php',
                      type: 'POST',
                      dataType: 'json',
                      cache: false,
                      data: {CityId:id_city, PrintTypeId: id_print_type, TypeId: id_type, StartStatusId:start_status, EndStatusId:end_status, Day:day_n},
                      success: function (data) {
                        $('#DIV_FlowContentDetail').html(data.content);
                        $('#DIV_FlowContent').show();
                      },
                      error: function (data) {
                        console.log(data);
                      }
                    });
                });
        
                $(".close_window").click(function () {
                	$('#DIV_FlowContent').hide();
                });
        
            	$('#cityid').change(function(){
            		$('#ruletype, #year').html('').prop('disabled', true);
            		
            		if($(this).val() != ""){
	            		var y = $.ajax({
                			url: "ajax/year.php",
                			type: "POST",
                			data: {id:$(this).val()},
                			dataType: "text"
                		});
                		
                		var r = $.ajax({
                			url: "ajax/ruletype.php",
                			type: "POST",
                			data: {CityId:$(this).val()},
                            dataType: 'json',
                            cache: false,
                		});
                		
                		r.done(function(data){
                			$.each(data.Result, function( index, result ) {
                				var option = $('<option>', {value: result.Id, text: result.Title});
                				option.appendTo('#ruletype');
                			});
                			$('#ruletype').prop('disabled', !data.Result.length > 0);
                		});
                		r.fail(function(jqXHR, textStatus){
                			alert("Request failed: " + textStatus);
                			console.log(jqXHR);
                		});
                		
                		y.done(function(data){
                			$('#year').prop('disabled', false);
                			$('#year').html(data);
                		});
                		y.fail(function(jqXHR, textStatus){
                			alert("Request failed: " + textStatus);
                		});
            		}
            	});
        
        		$('#year, #ruletype').change(function(){
        			if($('#year').val() && $('#ruletype').val()){
        				var citytitle = $( "#cityid option:selected" ).text();
            			var cityid = $( "#cityid" ).val();
            			var year = $("#year").val();
            			var ruletypeid = $("#ruletype").val();
            			var ruletypetitle = $( "#ruletype option:selected" ).text();
            			$(window.location).attr('href', 'index.php?cityid='+cityid+'&citytitle='+citytitle+'&year='+year+'&ruletypeid='+ruletypeid+'&ruletypetitle='+ruletypetitle);
        			}
        		});
        	});
    	</script>
    </body>
</html>
<?php
include(INC."/footer.php");
