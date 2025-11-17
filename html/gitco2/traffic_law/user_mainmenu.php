<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
include(INC."/header.php");



$rs = new CLS_DB();
$rs->SetCharset('utf8');

$rs_Menu = $rs->Select(MAIN_DB.".V_UserMenu", "(UserId=".$_SESSION['userid']." OR UserId IS NULL)","MenuOrder");


echo '

<body>
<div class="row-fluid">
        <div class="col-sm-12" style="height:8rem; padding:1rem;">
			<span style="color:#338CB5; font-weight:600;display:inline-block; font-size:5rem; padding-left:2rem;;width:45rem;height:7rem; background: #ADBAC8; border-radius: 40px;">
				SARIDA S.r.l.  
			</span>
			<div style="display:inline-block; font-size:1.1rem;position:absolute; bottom:0.2rem;left:11rem;">Servizio Accertamento e Riscossione Imposte e Diritti Accessori</div>
		</div>
</div>

<div class="row-fluid">	
	<div class="col-sm-12" style="height:7rem; padding:1rem;"></div>
';
   

		while($r_Menu = mysqli_fetch_array($rs_Menu)){
			if($r_Menu['UserId']!=""){
				echo '
					<a href="'.$MainPath.$r_Menu['Path'].'">
					<div class="col-sm-3 BoxRow active" style="height:13rem; text-align:center; padding:2rem; border-right:1px solid #E7E7E7;">
						'.$r_Menu['Icon'].'	
						<br />
						<span style="font-size:1.4rem;width:12rem;">'.$r_Menu['Description'].'</span>
					</div>
					</a>
				';				
				}else{
				echo '
					<div class="col-sm-3 BoxRow disabled" style="height:13rem; text-align:center; padding:2rem; border-right:1px solid #E7E7E7;">
						'.$r_Menu['Icon'].'	
						<br />
						<span style="font-size:1.4rem;width:12rem;">'.$r_Menu['Description'].'</span>
					</div>
				';
				}


		}
		
		if($_SESSION['userlevel']>=7){
			echo '
				<a href="'.$MainPath.'/adm_sar/">
				<div class="col-sm-3 BoxRow active" style="height:13rem; text-align:center; padding:2rem; border-right:1px solid #E7E7E7;">
						<i class="fa fa-user-secret" style="font-size:7rem;"></i>
					<br />
					<span style="font-size:1.4rem;width:12rem;">Admin</span>
				</div>
				</a>
				';
		}
		

echo '
            </div>
    </body>
';


?>
<script>

$(".active").hover(function(){
    $(this).css("color","#61F5F4");
    $(this).css("cursor","pointer");    
  },function(){
    $(this).css("color","#fff");
    $(this).css("cursor",""); 
  });
  
$(".disabled").hover(function(){
    $(this).css("cursor","not-allowed");    
  },function(){
    $(this).css("cursor",""); 
  });  
</script>	


