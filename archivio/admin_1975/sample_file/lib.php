<?php
include("../common/config.php");
class main {
/*===================================================CORE FUNCTIONS========================================================*/
	public function __construct( ){
  		$conn = mysqli_connect( DB_SERVER , DB_USER , DB_PASS ) or die( 'localhost connection problem'.mysql_error() );
  		mysqli_select_db( DB_NAME , $conn );
 	}
	
	public function insupd( $table , $val , $id , $return_page ){
		$all_values = "";
		$count = count($val);
		$inc = 0;
		if( $id == "" ){
			foreach ( $val as $values ){
				$inc++;
				if($inc == $count ){
					$all_values = $all_values . "'" . $values . "' ";
				}else{
					$all_values = $all_values . "'" . $values . "', ";
				}
			}
			mysqli_query( " INSERT INTO " . $table . " VALUES(NULL, " . $all_values . " ) " );
		}else{
			$fields = array();
			$query = mysql_query( " SHOW COLUMNS FROM `" . $table . "` " );
			while( $row = mysql_fetch_array( $query ) )
			$fields[] = $row['Field'];
			$id_field = array_shift($fields);
			$setvalues = array_combine($fields,$val);
			$update_statement = "";
			foreach( $setvalues as $setkey=>$setval ){
				$inc++;
				if($inc == $count ){
					$update_statement = $update_statement . $setkey . "='" . $setval . "' ";
				}else{
					$update_statement = $update_statement . $setkey . "='" . $setval . "', ";
				}
			}
			mysqli_query( "UPDATE `" . $table . "` SET " . $update_statement . " WHERE " . $id_field . " = '" . $id . "' " );
		}
		if( $return_page!="" ){
			echo " <script>window.location='../pages/" . $return_page . "'</script> ";
		}
	}

	public function customupd( $table , $val , $id , $return_page ){
		$update_statement = "";
		$count = count($val);
		$fields = array();
		$query = mysql_query( " SHOW COLUMNS FROM `" . $table . "` " );
		while( $row = mysql_fetch_array( $query ) )
		$fields[] = $row['Field'];
		$id_field = array_shift($fields);
		$inc = 0;
		foreach( $val as $setkey=>$setval ){
			$inc++;
			if($inc == $count ){
				$update_statement = $update_statement . $setkey . "='" . $setval . "' ";
			}else{
				$update_statement = $update_statement . $setkey . "='" . $setval . "', ";
			}
		}
		mysql_query( "UPDATE `" . $table . "` SET " . $update_statement . " WHERE " . $id_field . " = '" . $id . "' " );
		if( $return_page!="" ){
			echo " <script>window.location='../pages/" . $return_page . "'</script> ";
		}
	}

	public function show($table,$return_page,$not_needed_fields){
		$get = mysql_query("select * from `".$table."`");
		$rows = array();
		if(mysql_num_rows($get)>0){
			while($fet = mysql_fetch_array($get))	
			$rows[] = $fet;
		}
		
		$all_values = "";
		if(!empty($not_needed_fields)){	
			$count = count($not_needed_fields);
			$inc = 0;
			foreach ( $not_needed_fields as $values ){
				$inc++;
				if($inc == $count ){
					$all_values = $all_values . "'" . $values . "' ";
				}else{
					$all_values = $all_values . "'" . $values . "', ";
				}
			}
			$all_values = "WHERE Field NOT IN ( " . $all_values . " )";
		}	

		foreach($rows as $fet){
			echo "<tr>";
			$result = mysql_query( "SHOW COLUMNS FROM `".$table."` " . $all_values ." ");
			//$num_of_columns = mysql_num_rows($result);
			$id = 0;
			while($row = mysql_fetch_array($result)){
				$id++;
				$fields = $row['Field'];
				
				if(substr($fet[$fields],-3)=="jpg" || substr($fet[$fields],-3)=="png" || substr($fet[$fields],-3)=="gif"){
					echo "<td><img src='".$fet[$fields]."' height='100' width='100'></td>";
				}else{
					echo "<td>".$fet[$fields]."</td>";
				}
				if($id==1){
					echo "<td><a href='".$return_page."?edit=".$fet[$fields]."'>EDIT</a></td>";
					echo "<td><a href='../class/lib.php?delete=".$fet[$fields]."&table=".$table."&return=".$return_page."'>DELETE</a></td>";
				}				
				
			}
			echo "</tr>";
        }
	}
	
	public function remove($table,$delete_id,$return_page){
		mysql_query("delete from ".$table." where id='".$delete_id."'");
		echo "<script>window.location='../pages/".$return_page."'</script>";
	}

	public function unique_id( $before , $tablename , $after ){
		$a = mysql_fetch_assoc( mysql_query( "SHOW TABLE STATUS LIKE  '" . $tablename . "'" ) );
		return $before . $a['Auto_increment'] . $after;
	}
	
	public function single_access( $field , $table , $where , $val ){
		$get = mysql_fetch_assoc( mysql_query( " SELECT `" . $field . "` FROM `" . $table . "` WHERE `" . $where . "` = '" . $val . "'" ) );
		return $get[$field];
	}

	public function multiple_access( $table , $where , $val ){
		if($where!=""){
			$where = "` WHERE `" . $where . "` = '" . $val . "'";
		}else{
			$where = "";
		}
		$get = mysql_query( " SELECT * FROM `" . $table . $where );
		$rows = array();
		while($fet = mysql_fetch_array($get))
		$rows[] = $fet;
		return $rows;
	}
/*===================================================CORE FUNCTIONS========================================================*/

	public function loginprocess($a,$b){
		if($a=="" || $b==""){
			echo "<script>window.location='../index.php?err=1'</script>";
		}
		else{
			$attmpt=1;
			$get = mysql_query( "select * from login where 
								username='".$a."' and 
								password='".$b."' and 
								(attempt!='10' or permission!='N')");
			$num = mysql_num_rows($get);
			if($num>0){
				$fet = mysql_fetch_array($get);
				if($fet['attempt']<=10){
					$attmpt = $attmpt+$fet['attempt'];
					mysql_query("update login set attempt='".$attmpt."' where id='".$fet['id']."'");
				}
				$_SESSION['log'] = $fet['id'];
				echo "<script>window.location='../pages/home.php'</script>";
			}
			else {
				echo "<script>window.location='../index.php?err=1'</script>";
			}
		}
	}

	public function product_show($a,$b,$c){
		$get = mysql_query("select * from `".$a."`");
		$rows = array();
		if(mysql_num_rows($get)>0){
			while($fet = mysql_fetch_array($get))	
			$rows[] = $fet;
		}

		$all_values = "";
		if(!empty($c)){	
			$count = count($c);
			$inc = 0;
			foreach ( $c as $values ){
				$inc++;
				if($inc == $count ){
					$all_values = $all_values . "'" . $values . "' ";
				}else{
					$all_values = $all_values . "'" . $values . "', ";
				}
			}
			$all_values = "WHERE Field NOT IN ( " . $all_values . " )";
		}

		foreach($rows as $fet){
			echo "<tr>";
			$result = mysql_query("SHOW COLUMNS FROM `".$a."` " . $all_values ." ");
			//$num_of_columns = mysql_num_rows($result);
			$id = 0;
			while($row = mysql_fetch_array($result)){
				$id++;
				$fields = $row['Field'];
				
				if(substr($fet[$fields],-3)=="jpg" || substr($fet[$fields],-3)=="png" || substr($fet[$fields],-3)=="gif"){
					echo "<td><img src='".$fet[$fields]."' height='100' width='100'></td>";
				}else{
					echo "<td>".$fet[$fields]."</td>";
				}
				if($id==1){
					echo "<td><a href='".$b."?edit=".$fet[$fields]."'>EDIT</a></td>";
					echo "<td><a href='../class/lib.php?delete=".$fet[$fields]."&table=".$a."&return=".$b."'>DELETE</a></td>";
					echo "<td><a href='stock_report.php?productid=".$fet[$fields]."'>GO</a></td>";
				}				
				
			}
			echo "</tr>";
        }
	}
}

//For Delete Record//
if(isset($_REQUEST['delete'])){
	$obj = new main();
	$obj->remove($_REQUEST['table'],$_REQUEST['delete'],$_REQUEST['return']);
}
//----------------//

//For Login//
if(isset($_REQUEST['log'])){
	$mail = $_REQUEST['email'];
	$pass = $_REQUEST['password'];
	$obj = new main();
	$obj->loginprocess($mail,$pass);
}
//-------------------//

//For Company Details Updation//
if( isset( $_REQUEST['settings'] ) ){
	$company_details = array( 	$_REQUEST['heading'],
								$_REQUEST['name'],
								$_REQUEST['address'],
								$_REQUEST['mail'],
								$_REQUEST['phn'],
								date("Y-m-d"),
								$_REQUEST['term'],
								$_REQUEST['logid'],  );
	$obj = new main();
	$obj->insupd( "company_details" , $company_details , $_REQUEST['hid'] , "home.php" );
}
//-------------------//

//For Vendor Add Edit//
if( isset( $_REQUEST['vendor_add'] ) ){
	$vendor_details = array( $_REQUEST['vendor_name'], $_REQUEST['vendor_address'], $_REQUEST['vendor_contact'] );
	$obj = new main();
	$obj->insupd( "vendor" , $vendor_details , "" , "vendor.php" );
}
if( isset( $_REQUEST['vendor_upd'] ) ){
	$vendor_details = array( $_REQUEST['vendor_name'], $_REQUEST['vendor_address'], $_REQUEST['vendor_contact'] );
	$obj = new main();
	$obj->insupd( "vendor" , $vendor_details , $_REQUEST['hid'] , "vendor.php" );
}
//-----------------//

//For Category Add Edit//
if( isset( $_REQUEST['category_add'] ) ){
	$details = array( $_REQUEST['category_name'] );
	$obj = new main();
	$obj->insupd( "category" , $details , "" , "category.php" );
}
if( isset( $_REQUEST['category_upd'] ) ){
	$details = array( $_REQUEST['category_name'] );
	$obj = new main();
	$obj->insupd( "category" , $details , $_REQUEST['hid'] , "category.php" );
}
//-----------------//

//For Brand Add Edit//
if( isset( $_REQUEST['brand_add'] ) ){
	$details = array( $_REQUEST['brand_name'] );
	$obj = new main();
	$obj->insupd( "brand" , $details , "" , "brand.php" );
}
if( isset( $_REQUEST['brand_upd'] ) ){
	$details = array( $_REQUEST['brand_name'] );
	$obj = new main();
	$obj->insupd( "brand" , $details , $_REQUEST['hid'] , "brand.php" );
}
//-----------------//

//For Product Add Edit
if( isset( $_REQUEST['product_add'] ) ){
	if( $_REQUEST['unit'] == 4 ){
		$product_details = array(	$_REQUEST['category_name'],
									$_REQUEST['brand_name'],
									$_REQUEST['product_name'],
									$_REQUEST['unit'],
									mysql_real_escape_string($_REQUEST['size']),
									$_REQUEST['perqty1'],
									$_REQUEST['perqty2'],
									$_REQUEST['per_box'],
									$_REQUEST['ini_qty'],
									$_REQUEST['tot_qty'],
									$_REQUEST['rec_qty'],
									$_REQUEST['price'],
									date("Y-m-d")
								);
	}else{
		$product_details = array(	$_REQUEST['category_name'],
									$_REQUEST['brand_name'],
									$_REQUEST['product_name'],
									$_REQUEST['unit'], 
									'', '', '', '',
									$_REQUEST['ini_qty'],
									$_REQUEST['tot_qty'],
									$_REQUEST['rec_qty'],
									$_REQUEST['price'],
									date("Y-m-d")
								);
	}
	$obj = new main();
	$obj->insupd( "product" , $product_details , "" , "" );

	$product_id = $obj->single_access("id" , "product" , "name" , $_REQUEST['product_name'] );
	$stock_report_details = array(	$product_id,
									$_REQUEST['product_name'],
									date("Y-m-d"),
									$_REQUEST['tot_qty'],
									"",
									"",
									"",
									"",
									"0"
								);
	$obj->insupd( "stock_report" , $stock_report_details , "" , "product.php" );
}

if( isset( $_REQUEST['product_upd'] ) ){
	if( $_REQUEST['unit'] == 4 ){
		$product_details = array(	$_REQUEST['category_name'],
									$_REQUEST['brand_name'],
									$_REQUEST['product_name'],
									$_REQUEST['unit'],
									mysql_real_escape_string($_REQUEST['size']),
									$_REQUEST['perqty1'],
									$_REQUEST['perqty2'],
									$_REQUEST['per_box'],
									$_REQUEST['ini_qty'],
									$_REQUEST['tot_qty'],
									$_REQUEST['rec_qty'],
									$_REQUEST['price'],
									date("Y-m-d")
								);
	}else{
		$product_details = array(	$_REQUEST['category_name'],
									$_REQUEST['brand_name'],
									$_REQUEST['product_name'],
									$_REQUEST['unit'], 
									'', '', '', '',
									$_REQUEST['ini_qty'],
									$_REQUEST['tot_qty'],
									$_REQUEST['rec_qty'],
									$_REQUEST['price'],
									date("Y-m-d")
								);
	}
	$obj = new main();
	$obj->insupd( "product" , $product_details , $_REQUEST['hid'] , "" );

	$product_id = $obj->single_access("id" , "product" , "name" , $_REQUEST['product_name'] );
	$stock_report_details = array(	$product_id,
									$_REQUEST['product_name'],
									date("Y-m-d"),
									$_REQUEST['tot_qty'],
									"",
									"",
									"",
									"",
									"0"
								);
	$obj->insupd( "stock_report" , $stock_report_details , "" , "product.php" );
}
//----------------------//

//For Sale entry//
if( isset( $_REQUEST['sale_add'] ) ){
	$obj = new main();
	$billid = $obj->unique_id( "" , "bill_details" , "" );
	$rows = $_REQUEST['row_count'];
	for($c=1; $c<=$rows; $c++){
		if(!empty($_REQUEST['item_' . $c])){
			$product_id = $obj->single_access("id" , "product" , "name" , $_REQUEST['item_' . $c] );
			$product_qty = $obj->single_access("recent_quantity" , "product" , "name" , $_REQUEST['item_' . $c] );
			$sale_item_details = array(	$billid,
										$product_id,
										$_REQUEST['qty_' . $c],
										$_REQUEST['tot_amt_' . $c],
										$_REQUEST['bas_disc_' . $c],
										$_REQUEST['cd_disc_' . $c],
										$_REQUEST['tax_f_' . $c],
										$_REQUEST['tax_ff_' . $c],
										$_REQUEST['tax_tot_' . $c],
									);
			$obj->insupd( "bill_item_details" , $sale_item_details , "" , "" );

			$stock_report_details = array(	$product_id,
											$_REQUEST['item_' . $c],
											"",
											"",
											date("Y-m-d"),
											$billid,
											$_REQUEST['vendor_name'],
											$_REQUEST['qty_' . $c],
											"0"
										);
			$obj->insupd( "stock_report" , $stock_report_details , "" , "" );

			$new_qty = $product_qty - $_REQUEST['qty_' . $c];
			$product_qty_details = array( 	"recent_quantity" => $new_qty,
											"total_quantity" => $new_qty,
										);
			$obj->customupd( "product" , $product_qty_details , $product_id , "" );
		}
	}
	$customerid = $obj->single_access("id" , "vendor" , "vendor_name" , $_REQUEST['vendor_name'] );
	$bill_details = array(	$_REQUEST['invoice_no'],
							date("Y-m-d",strtotime($_REQUEST['invoice_date'])),
							$_REQUEST['challan_no'],
							date("Y-m-d",strtotime($_REQUEST['challan_date'])),
							$_REQUEST['order_no'],
							date("Y-m-d",strtotime($_REQUEST['order_date'])),
							$_REQUEST['vat_no'],
							$_REQUEST['cst_no'],
							$_REQUEST['pan_no'],
							$_REQUEST['buyer_vat_no'],
							$customerid,
							$_REQUEST['all_total'],
							date("Y-m-d")
						);
	if( !empty($_REQUEST['vendor_name']) ){
		$obj->insupd( "bill_details" , $bill_details , "" , "bill_entry.php" ); 
	}else{
		echo " <script>window.location='../pages/bill_entry.php'</script> ";
	}
}
//-------------------//
if( isset( $_REQUEST['make_payment'] ) ){
	$obj = new main();
	$count = count( $_REQUEST['payment'] );
	for( $p=1; $p<=$count; $p++ ){
		if( $_REQUEST['payment'][$p]['rs'] != "" ){
			$_REQUEST['payment'][$p]['date'] = date( "Y-m-d",strtotime( $_REQUEST['payment'][$p]['date'] ) );
			$payment_details = $_REQUEST['payment'][$p];
			$obj->insupd( "payment_details" , $payment_details , "" , "payment_entry.php" );
		}
	}
}
?>
