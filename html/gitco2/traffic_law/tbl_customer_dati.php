<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(CLS."/cls_table.php");
include(INC."/function.php");
include(INC."/header.php");

require(INC . '/menu_'.$_SESSION['UserMenuType'].'.php');

echo $str_out;

//  $file_blazon = glob(ROOT.'/img/blazon/'.$_SESSION['cityid'].'/*.png');

//if(count($file_blazon)==0) $file_blazon = glob(ROOT.'/img/blazon/'.$_SESSION['cityid'].'/*.jpg');

//$blazon = explode("traffic_law/",$file_blazon[0]);


$CityId = CheckValue('CityId','s');

$ManagerName = CheckValue('ManagerName','s');
$ManagerCity = CheckValue('ManagerCity','s');



if (isset($_GET['answer'])){
    $answer = $_GET['answer'];
    $class = strlen($answer)>50?"alert-warning":"alert-success";
    echo "<div class='alert $class message'>$answer</div>";
    ?>
    <script>
        setTimeout(function(){ $('.message').hide()}, 4000);
    </script>
    <?php
}




$strOrder = "CityId, ManagerName, ManagerCity, ManagerProvince";


$rs= new CLS_DB();
$str_GET_Parameter = "";

if($_SESSION['usertype']>50){

    $str_GET_Parameter = '
    
    
    
    
    <div class="row-fluid">
        	<div class="col-sm-12">
        	  	<div class="col-sm-12 BoxRow" >
        			<div class="col-sm-12 BoxRowLabel" style="text-align:center">
        				Ricerca Customer
					</div>
  				</div>
  				<form name="search_article" action="'.$str_CurrentPage.'" method="get">
        	    <div class="col-sm-12 BoxRow" >
        			<div class="col-sm-2 BoxRowLabel">
        				Codice catastale
					</div>
					<div class="col-sm-2 BoxRowCaption">
					    <input type="text" name="CityId" value="'.$CityId.'" style="width:8rem">    				
 					</div>
 					<div class="col-sm-2 BoxRowLabel">
        				Nome ente
					</div> 
					<div class="col-sm-2 BoxRowCaption">
                        <input type="text" name="ManagerName" value="'.$ManagerName.'" style="width:6rem">
                    </div>
        			<div class="col-sm-2 BoxRowLabel">
        				Città
					</div>
                    <div class="col-sm-2 BoxRowCaption">
                        <input type="text" name="ManagerCity" value="'.$ManagerCity.'" style="width:6rem">
                    </div>
  				</div>
  				<div class="col-sm-12 BoxRow">
        			<div class="col-sm-2 BoxRowLabel">
        				Ente gestito
					</div>
					<div class="col-sm-8 BoxRowCaption">
					'. CreateSelect("Customer","ManagerCity IS NOT NULL","ManagerName","CityId","CityId","ManagerName",$CityId,false) .'  				
 					</div>
        			<div class="col-sm-2 BoxRowLabel">
        				&nbsp;
					</div>
  				</div>
                <div class="col-sm-12 BoxRow" style="height:6rem;">
                    <div class="col-sm-12 BoxRowLabel" style="text-align:center;line-height:6rem;">
                        <button type="submit" class="btn btn-default" style="margin-top:1rem;">Cerca</button>
                    </div>
                 </div>
  				</form>
            </div>
        </div>
    ';
    if($CityId!=""){
        $str_Where .= " AND CityId='$CityId'";
        $str_CurrentPage .= "&CityId=$CityId";
    }
}else{
    $str_Where .= " AND CityId='".$_SESSION['CityId']."'";
}



$str_out ='
	<div class="container-fluid" style="padding: 0px">
    	<div class="row-fluid">
        	<div class="col-sm-12">
        		<div class="col-sm-12" style="background-color: #fff">
        		   
					
				</div>
         	</div>
        </div>
        '.$str_GET_Parameter.'
        
    </div>    
    <div style="height:5rem;"></div>
    <div class="container-fluid" style="padding: 0px"> 
    	<div class="row-fluid" style="margin-top:2rem;">
        	<div class="col-sm-12">
				<div class="table_label_H col-sm-2" style="height:6rem;">Codice ente</div>
        	    <div class="table_label_H col-sm-2" style="height:6rem;">Nome</div>
				<div class="table_label_H col-sm-2" style="height:6rem;">Codice fiscale</div>
				<div class="table_label_H col-sm-2" style="height:6rem;">Partita iva</div>
				<div class="table_label_H col-sm-2" style="height:6rem;">Città</div>
				<div class="table_label_H col-sm-1" style="height:6rem;">Proviincia</div>
						
        		<div class="table_add_button col-sm-1 right" style="height:6rem;">
        			
				</div>
				<div class="clean_row HSpace4"></div>
                
                <div class="clean_row HSpace4"></div>
                ';

//$page = CheckValue("page","n");
//
//$pagelimit = $page * PAGE_NUMBER;

$customers = $rs->SelectQuery("SELECT * FROM Customer WHERE " . $str_Where, $pagelimit . ',' . PAGE_NUMBER);
$RowNumber = mysqli_num_rows($customers);

if ($RowNumber == 0) {
    $str_out.= 'Nessun record presente';
} else {
    while ($customer = mysqli_fetch_array($customers)) {

        $str_out.= '        
            <div class="table_caption_H col-sm-2">' . $customer['CityId'] .'</div>
            <div class="table_caption_H col-sm-2">' . $customer['ManagerName'] .'</div>
            <div class="table_caption_H col-sm-2">' . $customer['ManagerTaxCode'] .'</div>
            <div class="table_caption_H col-sm-2">' . $customer['ManagerVAT'] .'</div>
            <div class="table_caption_H col-sm-2">' . $customer['ManagerCity'] .'</div>
            <div class="table_caption_H col-sm-1">' . $customer['ManagerProvince'] .'</div>
            ';


        $add = ChkButton($aUserButton, "upd", '<a href="tbl_customer_dati_upd.php?Id=' . $customer['CityId'] . '&tab=1&PageTitle=Gestione/Customer"><span class="glyphicon glyphicon-pencil" id="' . $customer['CityId'] . '"></span></a>&nbsp;');
        $viw = ChkButton($aUserButton, "viw", '<a href="tbl_customer_dati_viw.php?Id=' . $customer['CityId'] . '&PageTitle=Gestione/Customer"><span class="glyphicon glyphicon-eye-open" id="' . $customer['CityId'] . '"></span></a>&nbsp;');
        $str_out.= '
            <div class="table_caption_button col-sm-1">
            '.$viw.$add.'
            </div>
            <div class="clean_row HSpace4"></div>

            
            <div class="clean_row HSpace48"></div>
            ';
    }
}
$table_users_number = $rs->Select('Customer',$str_Where);
$UserNumberTotal = mysqli_num_rows($table_users_number);

$str_out.=CreatePagination(PAGE_NUMBER, $UserNumberTotal, $page, $str_CurrentPage,"");
$str_out.= '<div>
	</div>';
echo $str_out;
include(INC."/footer.php");