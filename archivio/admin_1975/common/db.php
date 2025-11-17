<?php
class  CLS_DB{

	function __construct(){
		$this->conn = mysqli_connect(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME) or die('could not connect to database');
	}

	function SelectQuery($sql){
		return mysqli_query($this->conn, $sql);
	}

	function Select($table, $where=null, $order=null, $limit=null){
		$sql = "SELECT * FROM $table";

		if(!is_null($where)) $sql .= " WHERE $where";
		if(!is_null($order)) $sql .= " ORDER BY $order";
		if(!is_null($limit)) $sql .= " LIMIT $limit";
		
		return mysqli_query($this->conn, $sql);
	}

	function Insert($t, $aI){
		$c = 0;
		$sql = 'INSERT INTO '.$t;
		$qT = '(';
		$qI = ' VALUES(';

		foreach ($aI as $F) :
			$c++;
			if($c<count($aI)) $comma=', ';
			else $comma='';

			$qT .= $F['field'].$comma;

			switch ($F['type']) {
				case "value":
					$qI .= "'".$F['getvaluefield']."'".$comma;
					break;
				case "chkbox":
					$qI .= (isset($_REQUEST[$F['getvaluefield']])) ? "1".$comma : "0".$comma;
					break;
				default:
					$qI .= $this->GetValues($F['getvaluefield'],$F['type']).$comma;

			}
 
		endforeach;
		$qT .= ')';
		$qI .= ');';
		$sql .=$qT.$qI;

//echo $sql."<br />";
//DIE;
		mysqli_query($this->conn, $sql);
		return mysqli_insert_id($this->conn);
	}

	function Update($t, $aI, $w){

		$c = 0;
		$sql = 'UPDATE '.$t.' SET ';
		foreach ($aI as $F) :
			if($c>0) $sql.= ', ';
			$c++;

			$sql .= $F['field'].'=';
			if($F['type']=="value"){
				$sql .= $F['getvaluefield'];
			}else{
				$sql .= $this->GetValues($F['getvaluefield'],$F['type']);
			}
		endforeach;
		$sql.= ' WHERE '.$w.';';
//echo $sql."<br />";
		mysqli_query($this->conn, $sql);
	}

	function Delete($t, $w){
		$sql = 'DELETE FROM '.$t;
		$sql.= ' WHERE '.$w.';';

		mysqli_query($this->conn, $sql);
	}


	function Start_Transaction(){
		$this->conn->autocommit(FALSE);
	}
	
	function End_Transaction(){
		$this->conn->commit();
	}



	function GetValues($f,$t){
		return $this->WriteValue($_REQUEST[$f],$t);
	}

	function WriteValue($v, $t){
		if($t=='str') $v = '\''.addslashes($v).'\'';
		if($t=='int') $v = str_replace(",",".",$v);

		return $v;
	}
	function __destruct(){
		mysqli_close($this->conn);
	}
}