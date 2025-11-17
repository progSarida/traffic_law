<?php
echo '
	<div class="container-fluid">
    	<div class="row-fluid">
        	<div class="col-sm-12">
        		<div class="col-sm-12" style="background-color: #fff">
        		    <img src="'.$_SESSION['blazon'].'" style="width:50px;">
					<span class="title_city">'.$_SESSION['citytitle'].' '.$_SESSION['year'].' ( '.$_SESSION['cityid'].' ) </span>
				</div>
			</div>	
			<div class="col-sm-12 alert alert-success">
					'.$_SESSION['Message'].'
			</div>
		</div>
	</div>		
	';
$_SESSION['Message'] = "";