<?php
include("cost-sarida-gitco.php");
session_start();
define('DB_NAME', 'sarida');
define('PATH', 'http://' . $_SERVER['SERVER_ADDR'] . '/admin'); //URL locator
	/*
//if( $_SERVER['SERVER_ADDR'] == "192.168.1.17" ){ //For local server db connection
	
	define('DB_HOST', 'localhost');
	define('DB_USERNAME', 'sarida');
	define('DB_PASSWORD', 'GP~0o>hU@:/:q*');

}else{ //For remote server db connection
	
	define('DB_HOST', 'localhost');
	define('DB_USERNAME', 'minivet_develop');
	define('DB_PASSWORD', 'developer@123');
	define('DB_NAME', 'minivet_demo_sarida');

	define('PATH',''); //URL locator
}*/
//================================CLASS FUNCTIONS STARTS========================================//
class  CLS_DB{
	//DB Connection Open
	function __construct(){
		$this->conn = mysqli_connect(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME) or die('could not connect to database');
	}

	//Settings for table attributes
	public function WriteValue( $value, $type ){
		if( $type == 'str' ) $value = "'" . $value . "'";
		if( $type == 'int' ) $value = str_replace( ",", ".", $value );
		return $value;
	}
	public function GetValues( $field, $type ){
		return $this->WriteValue( $_REQUEST[$field], $type );
	}

	//Insert Function
	public function Insert( $table, $attributes ){
		$attrCount = 0; //Count the number of attributes that needs to be inserted
		$sql = "INSERT INTO " . $table; //Insert query sql syntax starts
		$bracket = '(';
		$values = ' VALUES(';

		foreach ( $attributes as $eachAttribute ): //Getting each attribute that needs to be inserted
			$attrCount++;
			$comma = ( $attrCount < count($attributes) ) ? $comma = ', ' : ''; //Comma seperation logic for attribute values
			$bracket .= $eachAttribute['field'] . $comma; 

			switch ( $eachAttribute['type'] ) {
				case "primary" : //In case of primary key mentioning
					$values .= $eachAttribute['getvaluefield'] . $comma;
					break;
				case "value" :
					$values .= "'" . $eachAttribute['getvaluefield'] . "'" . $comma;
					break;
				case "chkbox" :
					$values .= ( isset( $_REQUEST[ $eachAttribute['getvaluefield'] ] ) ) ? "1" . $comma : "0" . $comma;
					break;
				default:
					$values .= $this->GetValues( $eachAttribute['getvaluefield'] , $eachAttribute['type'] ) . $comma;
			}
		endforeach;
		$bracket .= ')';
		$values .= ');';
		$sql .= $bracket . $values;
		mysqli_query( $this->conn, $sql );
		//return mysqli_insert_id( $this->conn );
	}

	//Update Function
	function Update( $table, $attributes, $whereClause ){
		$attrCount = 0;
		$sql = 'UPDATE ' . $table . ' SET ';
		foreach ( $attributes as $eachAttribute ) :
			if( $attrCount > 0 ) $sql .= ', ';
				$attrCount++;
				$sql .= $eachAttribute['field'] . '='; //Setting the field name for the data to be inserted into the table
			if( $eachAttribute['type'] == "value" ){
				$sql .= "'" . $eachAttribute['getvaluefield'] . "'"; //Assigning the value for that field
			}else{
				$sql .= $this->GetValues( $eachAttribute['getvaluefield'], $eachAttribute['type'] ); //Getting value type
			}
		endforeach;
		$sql .= ' WHERE ' . $whereClause . ';'; //Setting the where clause for update
		mysqli_query( $this->conn, $sql );
	}

	//Delete Function
	function Delete( $table, $whereClause ){
		$sql = 'DELETE FROM ' . $table;
		$sql.= ' WHERE ' . $whereClause . ';';
		mysqli_query($this->conn, $sql);
	}

	//Select Function
	function Select( $table, $whereClause=null, $orderBy=null, $limit=null ){
		$sql = "SELECT * FROM " . $table;
		if( !is_null($whereClause) ) $sql .= " WHERE " . $whereClause;
		if( !is_null($orderBy) ) $sql .= " ORDER BY " . $orderBy;
		if( !is_null($limit) ) $sql .= " LIMIT " . $limit;
		$queryString = mysqli_query( $this->conn, $sql );
		while( $eachrow = mysqli_fetch_array( $queryString, MYSQLI_ASSOC ) ){ //Fetching all rows from tables
			$rows[] = $eachrow;
		}
		if( isset( $rows ) ){
			return $rows; //Returning all rows
		}
	}

	//Login Function
	public function isLogin( $table, $attributes ){
		$sql = "SELECT Id, UserName FROM " . $table . " WHERE UserName='" . $attributes[0] . "' AND Password='" . md5( $attributes[1] ) . "'";

		$queryString = mysqli_query( $this->conn, $sql );
		$isValid = mysqli_num_rows($queryString);
		$authKey = mysqli_fetch_assoc( $queryString );
		if( $isValid > 0 ){
			$_SESSION['authKey'] = $authKey['Id'];
			$_SESSION['authName'] = $authKey['User'];
			$loginDate = array(
				array( 'field'=>'LoginDate','type'=>'value','getvaluefield'=>date("Y-m-d") )
			);
			//$this->Update( $table, $loginDate, $authKey['Id'] ); //Updating latest login date for logged in user
		}
	}

	// function Start_Transaction(){
	// 	$this->conn->autocommit(FALSE);
	// }
	
	// function End_Transaction(){
	// 	$this->conn->commit();
	// }

	//DB Connection Close
	function __destruct(){
		mysqli_close($this->conn);
	}
}
//================================CLASS FUNCTIONS ENDS========================================//

$obj = new CLS_DB(); //Defining object of the mentioned class

//Login Function
if( isset( $_REQUEST['login'] ) ){
	$obj->isLogin("User", $_REQUEST['loginAttr']);
	@header('location:../mainMenuSetting.php');
}
//--------------

//Add Function - Main menu
if( isset( $_REQUEST['addMainMenu'] ) ){
	$insertVal = array(
		array('field'=>'Id','type'=>'primary','getvaluefield'=>'NULL'),
		array('field'=>'Description','type'=>'value','getvaluefield'=>$_REQUEST['mainMenu'][0]),
		array('field'=>'Path','type'=>'value','getvaluefield'=>$_REQUEST['mainMenu'][1]),
		array('field'=>'Disabled','type'=>'value','getvaluefield'=>$_REQUEST['mainMenu'][2]),
	);
	$obj->Insert( "MainMenu", $insertVal );
	@header('location:../mainMenuSetting.php');
}
//Edit Function - Main Menu
if( isset( $_REQUEST['updateMainMenu'] ) ){
	$insertVal = array(
		array('field'=>'Description','type'=>'value','getvaluefield'=>$_REQUEST['mainMenu'][0]),
		array('field'=>'Path','type'=>'value','getvaluefield'=>$_REQUEST['mainMenu'][1]),
		array('field'=>'Disabled','type'=>'value','getvaluefield'=>$_REQUEST['mainMenu'][2]),
	);
	$obj->Update( "MainMenu", $insertVal, "Id=" . $_REQUEST['mainMenu']['id'] );
	@header('location:../mainMenuSetting.php');
}
//Delete Function - Main Menu
if( isset( $_REQUEST['deleteMainMenu'] ) ){
	$obj->Delete( "MainMenu", "Id=" . $_REQUEST['deleteMainMenu'] );
	@header('location:../mainMenuSetting.php');
}
//----------------------

//Add Function - Application menu
if( isset( $_REQUEST['addAppMenu'] ) ){
	$insertVal = array( 
		array('field'=>'MainMenuId','type'=>'value','getvaluefield'=>$_REQUEST['applicationMenu'][0]),
		array('field'=>'Id','type'=>'primary','getvaluefield'=>'NULL'),
		array('field'=>'Title','type'=>'value','getvaluefield'=>$_REQUEST['applicationMenu'][1]),
		array('field'=>'Disabled','type'=>'value','getvaluefield'=>$_REQUEST['applicationMenu'][2]),
		array('field'=>'Description','type'=>'value','getvaluefield'=>$_REQUEST['applicationMenu'][3]),
		array('field'=>'MenuOrder','type'=>'value','getvaluefield'=>$_REQUEST['applicationMenu'][4]),
	);
	$obj->Insert( "ApplicationMenu", $insertVal );
	@header('location:../applicationMenuSetting.php');
}
//Edit Function - Application Menu
if( isset( $_REQUEST['updateAppMenu'] ) ){
	$insertVal = array(
		array('field'=>'MainMenuId','type'=>'value','getvaluefield'=>$_REQUEST['applicationMenu'][0]),
		array('field'=>'Title','type'=>'value','getvaluefield'=>$_REQUEST['applicationMenu'][1]),
		array('field'=>'Disabled','type'=>'value','getvaluefield'=>$_REQUEST['applicationMenu'][2]),
		array('field'=>'Description','type'=>'value','getvaluefield'=>$_REQUEST['applicationMenu'][3]),
		array('field'=>'MenuOrder','type'=>'value','getvaluefield'=>$_REQUEST['applicationMenu'][4]),
	);
	$obj->Update( "ApplicationMenu", $insertVal, "Id=" . $_REQUEST['applicationMenu']['id'] );
	@header('location:../applicationMenuSetting.php');
}
//Delete Function - Application Menu
if( isset( $_REQUEST['deleteApplicationMenu'] ) ){
	$obj->Delete( "ApplicationMenu", "Id=" . $_REQUEST['deleteApplicationMenu'] );
	@header('location:../applicationMenuSetting.php');
}
//----------------------

//Add Function - Application sub menu
if( isset( $_REQUEST['addAppSubMenu'] ) ){
	$insertVal = array( 
		array('field'=>'ApplicationMenuId','type'=>'value','getvaluefield'=>$_REQUEST['applicationSubMenu'][0]),
		array('field'=>'Id','type'=>'primary','getvaluefield'=>'NULL'),
		array('field'=>'Title','type'=>'value','getvaluefield'=>$_REQUEST['applicationSubMenu'][1]),
		array('field'=>'LinkPage','type'=>'value','getvaluefield'=>$_REQUEST['applicationSubMenu'][2]),
		array('field'=>'Disabled','type'=>'value','getvaluefield'=>$_REQUEST['applicationSubMenu'][3]),
		array('field'=>'Description','type'=>'value','getvaluefield'=>$_REQUEST['applicationSubMenu'][4]),
		array('field'=>'SubMenuOrder','type'=>'value','getvaluefield'=>$_REQUEST['applicationSubMenu'][5]),
	);
	$obj->Insert( "ApplicationSubMenu", $insertVal );
	@header('location:../applicationSubMenuSetting.php');
}
//Edit Function - Application sub Menu
if( isset( $_REQUEST['updateAppSubMenu'] ) ){
	$insertVal = array(
		array('field'=>'ApplicationMenuId','type'=>'value','getvaluefield'=>$_REQUEST['applicationSubMenu'][0]),
		array('field'=>'Title','type'=>'value','getvaluefield'=>$_REQUEST['applicationSubMenu'][1]),
		array('field'=>'LinkPage','type'=>'value','getvaluefield'=>$_REQUEST['applicationSubMenu'][2]),
		array('field'=>'Disabled','type'=>'value','getvaluefield'=>$_REQUEST['applicationSubMenu'][3]),
		array('field'=>'Description','type'=>'value','getvaluefield'=>$_REQUEST['applicationSubMenu'][4]),
		array('field'=>'SubMenuOrder','type'=>'value','getvaluefield'=>$_REQUEST['applicationSubMenu'][5]),
	);
	$obj->Update( "ApplicationSubMenu", $insertVal, "Id=" . $_REQUEST['applicationSubMenu']['id'] );
	@header('location:../applicationSubMenuSetting.php');
}
//Delete Function - Application sub Menu
if( isset( $_REQUEST['deleteApplicationSubMenu'] ) ){
	$obj->Delete( "ApplicationSubMenu", "Id=" . $_REQUEST['deleteApplicationSubMenu'] );
	@header('location:../applicationSubMenuSetting.php');
}
//----------------------

//Add Function - Application Page
if( isset( $_REQUEST['addAppPage'] ) ){
	$insertVal = array(
		array('field'=>'Id','type'=>'primary','getvaluefield'=>'NULL'),
		array('field'=>'Title','type'=>'value','getvaluefield'=>$_REQUEST['applicationPage'][0]),
		array('field'=>'Description','type'=>'value','getvaluefield'=>$_REQUEST['applicationPage'][1]),
		array('field'=>'Disabled','type'=>'value','getvaluefield'=>$_REQUEST['applicationPage'][2]),
	);
	$obj->Insert( "ApplicationPage", $insertVal );
	@header('location:../applicationPageSetting.php');
}
//Edit Function - Application Page
if( isset( $_REQUEST['updateAppPage'] ) ){
	$insertVal = array(
		array('field'=>'Title','type'=>'value','getvaluefield'=>$_REQUEST['applicationPage'][0]),
		array('field'=>'Description','type'=>'value','getvaluefield'=>$_REQUEST['applicationPage'][1]),
		array('field'=>'Disabled','type'=>'value','getvaluefield'=>$_REQUEST['applicationPage'][2]),
	);
	$obj->Update( "ApplicationPage", $insertVal, "Id=" . $_REQUEST['applicationPage']['id'] );
	@header('location:../applicationPageSetting.php');
}
//Delete Function - Application Page
if( isset( $_REQUEST['deleteAppPage'] ) ){
	$obj->Delete( "ApplicationPage", "Id=" . $_REQUEST['deleteAppPage'] );
	@header('location:../applicationPageSetting.php');
}
//----------------------

//Add Function - User
if( isset( $_REQUEST['addUser'] ) ){
	$insertVal = array(
		array('field'=>'Id','type'=>'primary','getvaluefield'=>'NULL'),
		array('field'=>'UserName','type'=>'value','getvaluefield'=>$_REQUEST['user'][0]),
		array('field'=>'Password','type'=>'value','getvaluefield'=>md5( $_REQUEST['user'][1] ) ),
		array('field'=>'Mail','type'=>'value','getvaluefield'=>$_REQUEST['user'][2])
	);
	$obj->Insert( "User", $insertVal );
	$_SESSION['success'] = "Operation Successful"; //Setting Success Message
	@header('location:../userSetting.php');
}
//Edit Function - User
if( isset( $_REQUEST['updateUser'] ) ){
	$insertVal = array(
		array('field'=>'UserName','type'=>'value','getvaluefield'=>$_REQUEST['user'][0]),
		array('field'=>'Password','type'=>'value','getvaluefield'=>md5( $_REQUEST['user'][1] ) ),
		array('field'=>'Mail','type'=>'value','getvaluefield'=>$_REQUEST['user'][2])
	);
	$obj->Update( "User", $insertVal, "Id=" . $_REQUEST['user']['id'] );
	$_SESSION['success'] = "Operation Successful"; //Setting Success Message
	@header('location:../userSetting.php');
}
//Delete Function - User
if( isset( $_REQUEST['deleteUser'] ) ){
	//Deleting records from user application and user page table for this particular user
	$obj->Delete( "UserPage", "UserId=" . $_REQUEST['deleteUser'] );
	$obj->Delete( "UserApplication", "UserId=" . $_REQUEST['deleteUser'] );
	$obj->Delete( "User", "Id=" . $_REQUEST['deleteUser'] );
	$_SESSION['success'] = "Operation Successful"; //Setting Success Message
	@header('location:../userSetting.php');
}
//----------------------

//Taking users for page Assignment
if( isset( $_REQUEST['assign'] ) ){
	@header( 'location:../userSetting.php?users=' . base64_encode( json_encode($_REQUEST['assignUser']) ) );
}
//----------------------

//Getting application menu id from ajax call - UserSetting page
if( isset( $_GET['menuId'] ) ){
	$applicationMenuItems = $obj->Select( "ApplicationMenu", "MainMenuId=" . $_GET['menuId'] );
	print_r( json_encode( $applicationMenuItems ) ); //Returning JSON encoded result
}
//----------------------

//Getting application sub menu id from ajax call - UserSetting page
if( isset( $_GET['appMenuId'] ) ){
	$applicationSubMenuId = $obj->Select( "ApplicationSubMenu", "ApplicationMenuId=" . $_GET['appMenuId'] );
	print_r( json_encode( $applicationSubMenuId ) ); //Returning JSON encoded result
}
//----------------------

//Update Function - Access Filter For User
if( isset( $_REQUEST['updAccessFilter'] ) ){ 
	//Deleting records for this access filter
	for( $d=1; $d<=7; $d++ ){
		$obj->Delete( "UserApplication", "UserId='".$_REQUEST['hiddenAF'][1]."' AND MainMenuId='".$_REQUEST['hiddenAF'][0]."' AND ApplicationSubMenuId='".$_REQUEST['hiddenAF'][2]."'" );

		$obj->Delete( "UserPage", "UserId='".$_REQUEST['hiddenAF'][1]."' AND MainMenuId='".$_REQUEST['hiddenAF'][0]."' AND ApplicationSubMenuId='".$_REQUEST['hiddenAF'][2]."'" );
	}
	//Insert Function - Access Filter For User
	$userId = $_REQUEST['filter'][0];
	$mainMenuId = $_REQUEST['filter'][1];
	$applicationMenuId = $_REQUEST['filter'][2];
	$applicationSubMenuId = $_REQUEST['filter'][3];

	for( $c=4; $c<=10; $c++ ){
		if( isset( $_REQUEST['filter'][$c] ) && $userId != "0" ){
			//Inserting data into UserApplication Table
			$appInsertVal = array(
				array('field'=>'MainMenuId','type'=>'value','getvaluefield'=>$mainMenuId),
				array('field'=>'UserId','type'=>'value','getvaluefield'=>$userId),
				array('field'=>'ApplicationSubMenuId','type'=>'value','getvaluefield'=>$applicationSubMenuId )
			);
			$obj->Insert( "UserApplication", $appInsertVal );

			//Inserting data into UserPage Table
			$pageInsertVal = array(
				array('field'=>'MainMenuId','type'=>'value','getvaluefield'=>$mainMenuId),
				array('field'=>'UserId','type'=>'value','getvaluefield'=>$userId),
				array('field'=>'ApplicationSubMenuId','type'=>'value','getvaluefield'=>$applicationSubMenuId ),
				array('field'=>'ApplicationPageId','type'=>'value','getvaluefield'=>$_REQUEST['filter'][$c] )
			);
			$obj->Insert( "UserPage", $pageInsertVal );
		}
	}
	$_SESSION['success'] = "Operation Successful"; //Setting Success Message
	@header('location:../userSetting.php');
}
//----------------------

//Delete Function  - Access Filter
if( isset( $_GET['deleteAccFilter'] ) && isset( $_GET['mid'] ) && isset( $_GET['uid'] ) && isset( $_GET['smid'] ) && isset( $_GET['apid'] ) ){
	//Deleting records for this access filter
	$obj->Delete( "UserApplication", "MainMenuId=".$_GET['mid']." AND UserId=".$_GET['uid']." AND ApplicationSubMenuId=".$_GET['smid'] );
	$obj->Delete( "UserPage", "MainMenuId=".$_GET['mid']." AND UserId=".$_GET['uid']." AND ApplicationSubMenuId=".$_GET['smid']." AND ApplicationPageId=".$_GET['apid'] );
	$_SESSION['success'] = "Operation Successful"; //Setting Success Message
	@header('location:../userSetting.php');
}
//----------------------
