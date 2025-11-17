<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
include(INC."/header.php");

require(INC . '/menu_'.$_SESSION['UserMenuType'].'.php');


$CityId = CheckValue('CityId','s');






$str_Order = "CityId";


if($CityId!=""){
    $str_Where = " AND CityId='".$CityId."'";
    $str_CurrentPage .= "&CityId=$CityId";
}


$str_out .='

<div class="row-fluid">
        <div class="col-sm-12">
            <div class="col-sm-12 BoxRow" >
                <div class="col-sm-3 BoxRowLabel">
                    Ente
                </div>
                <div class="col-sm-9 BoxRowCaption">
                '. CreateSelect("Customer","ManagerCity IS NOT NULL","ManagerName","CityId","CityId","ManagerName",$CityId,false) .'  				
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
</div>    
    <div class="clean_row HSpace4"></div>
    	<div class="row-fluid" style="margin-top:2rem;">
        	<div class="col-sm-12">
				<div class="table_label_H col-sm-2" style="height:6rem;">CityId</div>
        	    <div class="table_label_H col-sm-2" style="height:6rem;">ManagerName</div>
				<div class="table_label_H col-sm-2" style="height:6rem;">ManagerTaxCode</div>
				<div class="table_label_H col-sm-2" style="height:6rem;">ManagerVat</div>
				<div class="table_label_H col-sm-2" style="height:6rem;">ManagerCity</div>
				<div class="table_label_H col-sm-1" style="height:6rem;">ManagerProvince</div>
						
        		<div class="table_add_button col-sm-1 right" style="height:6rem;">
        			<a href="tbl_customer_add.php">
        				<span class="glyphicon glyphicon-plus-sign add_button" style="height:2.5rem;margin-right:0.3rem; line-height:2.3rem;"></span>
					</a>
				</div>
				<div class="clean_row HSpace4"></div>
                
                <div class="clean_row HSpace4"></div>
                ';



$customers = $rs->Select("Customer", $str_Where, $str_Order);
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

      

        $str_out.= '
            <div class="table_caption_button col-sm-1">
            <a href="tbl_customer_viw.php?CityId=' . $customer['CityId'] . '"><span class="glyphicon glyphicon-eye-open" id="' . $customer['CityId'] . '"></span></a>&nbsp;
            <a href="tbl_customer_upd.php?Id=' . $customer['CityId'] . '"><span class="glyphicon glyphicon-pencil" id="' . $customer['CityId'] . '"></span></a>&nbsp;
            
            </div>
            <div class="clean_row HSpace4"></div>

            
            <div class="clean_row HSpace48"></div>
            ';


    }
}
$table_users_number = $rs->Select('Article',$str_Where, 'Id');
$UserNumberTotal = mysqli_num_rows($table_users_number);

$str_out.=CreatePagination(PAGE_NUMBER, $UserNumberTotal, $page, $str_CurrentPage,"");
$str_out.= '<div>
	</div>';


echo $str_out;
?>
<script>
    $(document).ready(function () {
        $(".glyphicon-pencil").click(function(){
            var id = $(this).attr("id");
            window.location.href = "tbl_customer_upd.php?Id="+id;
        });
    });


</script>



<?php
include(INC."/footer.php");