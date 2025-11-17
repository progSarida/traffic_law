<?php
include_once "cls_help.php";
include_once "cls_LOG.php";

class  cls_db{
    public $conn;
    public $help;
    private $log;
	function __construct($host,$username,$password,$dbName){
		mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
		$this->help = new cls_help();
		$this->log = new LOG();
		try{
			$this->connect($host,$username,$password,$dbName);
		}
		catch (Exception $e) {
            if(DEBUG)
			    $this->help->ErrorAlert("danger",$this->ErrorReporting($e));
            else
                $this->help->ErrorAlert("danger","Errore di connessione. Contattare il Webmaster");
            die;
		}
	}

  public function checkFieldType($field){
        if(is_null($field['value'])||trim($field['value']," ")=="")
            $value = 'null';
        else if($field['type']=='string' || $field['type']=='date')
            $value = '"'.$field['value'].'"';
        else{
            if($field['value']=="")
                $value = 'null';
            else
                $value = $field['value'];
        }

        return $value;
    }

    public function DbInsert($a_params){
        $sql = 'INSERT INTO '.$a_params['table'];
        $sqlFields = '(';
        $sqlValues = ' VALUES(';
        $comma = "";
        foreach ($a_params['fields'] as $field){
            $sqlFields .= $comma.$field['name'];
            $sqlValues .= $comma.$this->checkFieldType($field);
            if($comma=='')
                $comma = ', ';
        }
        $sqlFields .= ')';
        $sqlValues .= ');';

        $sql .= $sqlFields.$sqlValues;

        //echo "<br><br>QUERY: ".$sql."<br>";

        if(!$this->ExecuteQuery($sql))
        {
            return false;
        }
        else {
          return mysqli_insert_id($this->conn);
        }

    }

    function DbUpdate($a_params){

        $flagWhere = true;
        $sql = 'UPDATE '.$a_params['table'].' SET ';

        $comma = "";
        foreach ($a_params['fields'] as $field){
            $sql.= $comma.$field['name']."=".$this->checkFieldType($field);
            if($comma=='')
                $comma = ', ';
        }

        $sql.= ' WHERE ';
        foreach ($a_params['updateField'] as $update)
        {
          if(!isset($update['name'])) { $flagWhere = false; break;}
          $sql.= ' '.$update['name'].'='.$this->checkFieldType($update);
          if(isset($update['operator']))
          {
            if($update['operator'] != null)
            $sql .= ' '.$update['operator'];
          }
        }

        if(!$flagWhere)
        {
          $sql.= $a_params['updateField']['name'].'='.$this->checkFieldType($a_params['updateField']).' ;';
        }
        else $sql .= ' ;';
        //echo "<br><br>QUERY: ".$sql."<br>";
        return $this->ExecuteQuery($sql);
    }

    function DbSave($a_params){
	    if(isset($a_params['updateField']))
	        return $this->DbUpdate($a_params);
	    else
            return $this->DbInsert($a_params);
    }

	function connect($host,$username,$password,$dbName){
		try{
			$this->conn = mysqli_connect($host, $username, $password, $dbName);
		}
		catch (mysqli_sql_exception $e){
			throw $e;
		}
	}

  function GetError()
  {
    return mysqli_error($this->conn);
  }

    function SetCharset($Charset){
        mysqli_set_charset( $this->conn, $Charset);
    }

	function ExecuteQuery($sql){
		try{
			try{
				return mysqli_query($this->conn, $sql);
			}
			catch (mysqli_sql_exception $e){
                $this->log->error("Alla riga ".$e->getLine().".\nCodice: ".$e->getCode().".\nErrore: ".$e->getMessage());
				throw $e;
			}
		}
		catch (Exception $e) {
            //if(DEBUG)
                $this->help->ErrorAlert("danger",$this->ErrorReporting($e,$sql));
            /*else
                $this->help->ErrorAlert("danger","Errore query. Contattare il Webmaster");*/
            die;
		}
	}

	function SelectQuery($sql){
		return $this->ExecuteQuery($sql);
	}

	function Select($table, $where=null, $order=null, $limit=null){
		$sql = "SELECT * FROM $table";

		if(!is_null($where)) $sql .= " WHERE $where";
		if(!is_null($order)) $sql .= " ORDER BY $order";
		if(!is_null($limit)) $sql .= " LIMIT $limit";

		return $this->ExecuteQuery($sql);
	}

    function SelectArray($table, $where=null, $order=null, $limit=null){
        $result = $this->Select($table, $where, $order, $limit);
        return $this->getArray($result);
    }

    function getArrayLine($results){
        return $results->fetch_array(MYSQLI_ASSOC);
    }

    function getArrayLineNull($results,$table=null){
        $val = $results->fetch_array(MYSQLI_ASSOC);

        if($val != null) return $val;
        else if($table!=null)
        {

          $query = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = '".$table."'";
          $allName = $this->getResults($this->ExecuteQuery($query));

          for($i=0;$i<count($allName);$i++)
          {
            $val[$allName[$i]["COLUMN_NAME"]] = null;
          }
          //mysqli_field_name($res, 0);

          //$finfo = $val->fetch_fields();

          //foreach ($finfo as $el) {
              /*printf("Name:      %s\n",   $val->name);
              printf("Table:     %s\n",   $val->table);
              printf("Max. Len:  %d\n",   $val->max_length);
              printf("Length:    %d\n",   $val->length);
              printf("charsetnr: %d\n",   $val->charsetnr);
              printf("Flags:     %d\n",   $val->flags);
              printf("Type:      %d\n\n", $val->type);*/
              //$val[mysqli_field_name($val, 0)] = null;
        //  }
        }else return null;
        return $val;

    }

    function getObjectLine($results){
        return $results->fetch_object();
    }

    function getObjectLineNull($results,$table=null){
        $val = $results->fetch_object();

        if($val != null) return $val;
        else if($table!=null)
        {
            $query = "SELECT `COLUMN_NAME` FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='".DB_NAME."' AND TABLE_NAME = '".$table."'";
            $allName = $this->getResults($this->ExecuteQuery($query));
            $val = new stdClass();
            for($i=0;$i<count($allName);$i++)
            {
                $key = $allName[$i]["COLUMN_NAME"];
                $val->$key = null;
            }
        }else return null;
        return $val;

    }

    function getResults($results, $resType = "array"){
        $a_res = array();
        if($resType=="array"){
            while($line = $this->getArrayLine($results))
                $a_res[] = $line;
        }
        else if($resType=="object"){
            while($line = $this->getObjectLine($results))
                $a_res[] = $line;
        }

        return $a_res;
    }

    function getResultsNull($results, $table, $resType = "array"){
        $a_res = array();
        if($resType=="array"){
            while($line = $this->getArrayLine($results))
                $a_res[] = $line;
            if(count($a_res) == 0)
                $a_res[0] = $this->getArrayLineNull($results,$table);
        }
        else if($resType=="object"){
            while($line = $this->getObjectLine($results))
                $a_res[] = $line;
            if(count($a_res) == 0)
                $a_res[0] = $this->getObjectLineNull($results,$table);
        }

        return $a_res;
    }


    function bind_array($sql,$types,$a_bind){

            $stmt = mysqli_prepare($this->conn,$sql);
            $bind_names[] = $types;
            for ($i=0; $i<count($a_bind);$i++)
            {
                $bind_name = 'bind' . $i;
                $$bind_name = $a_bind[$i];
                $bind_names[] = &$$bind_name;
            }
//            var_dump($bind_names);
            call_user_func_array(array($stmt,'bind_param'),$bind_names);
            return $stmt->execute();

    }

    function foundRows(){
        $a_return = $this->getArrayLine($this->ExecuteQuery("SELECT FOUND_ROWS() AS numRows"));
        return $a_return['numRows'];
    }

    function bindInsert($tableName, $a_insert, $a_bindTypes){

        $insertFields = "";
        $insertValues = "";
        $bindTypes = "";
        $check = 0;
        $a_values = array();
        foreach($a_insert as $key=>$val){
            if($check>0){
                $insertFields.= ", ";
                $insertValues.= ", ";
            }
            else
                $check = 1;

            $insertFields.= $key;
            $insertValues.= "?";
            $a_values[] = $val;
            $bindTypes.= $a_bindTypes[$key];
        }

        $query = "INSERT INTO ".$tableName." (".$insertFields.") ";
        $query.= "VALUES (".$insertValues.")";

        return $this->bind_array($query,$bindTypes,$a_values);
    }

    function bindUpdate($tableName, $a_update, $a_bindTypes, $filter=null){

        $setQuery = "";
        $bindTypes = "";
        $check = 0;
        $a_values = array();
        foreach($a_update as $key=>$val){
            if($check>0)
                $setQuery.= ", ";
            else
                $check = 1;

            $setQuery.= $key."=?";
            $bindTypes.= $a_bindTypes[$key];
            $a_values[] = $val;
        }

        $query = "UPDATE ".$tableName." SET ".$setQuery;
        if($filter!=null)
            $query.= " ".$filter;

//        var_dump($query);
//        var_dump($bindTypes);
//        var_dump($a_values);
        return $this->bind_array($query,$bindTypes,$a_values);
    }

    function realEscapeString($string){
        return mysqli_real_escape_string($this->conn, $string);
    }

	function Insert($t, $aI){
		$sql = 'INSERT INTO '.$t;
		$qT = '(';
		$qI = ' VALUES(';
        $comma=', ';
		foreach ($aI as $F) :
            $value = $this->ValueWriting($F);
            if(is_null($value))
                $value = "null";

            $qT .= $F['field'].$comma;
            $qI .= $value.$comma;

		endforeach;
        $qT = substr($qT,0,-2).')';
        $qI = substr($qI,0,-2).');';

		$sql .=$qT.$qI;

//echo $sql."<br />";
//return false;
        $this->ExecuteQuery($sql);
		return mysqli_insert_id($this->conn);
	}

    function InsertFromKey($t, $aI){
        $sql = 'INSERT INTO '.$t;
        $qT = '(';
        $qI = ' VALUES(';
        $comma=', ';
        foreach ($aI as $key=>$value) :
            if(is_null($value))
                $value = "null";

            $qT .= $key.$comma;
            $qI .= "'".$value."'".$comma;

        endforeach;
        $qT = substr($qT,0,-2).')';
        $qI = substr($qI,0,-2).');';

        $sql .=$qT.$qI;

//echo $sql."<br />";
//return false;
        //$this->ExecuteQuery($sql);
        return array("id" => mysqli_insert_id($this->conn), "result" => $this->ExecuteQuery($sql));
    }

    function UpdateFromKey($t, $aI, $w){
        $sql = 'UPDATE '.$t.' SET ';

        $writevalues = "";
        foreach ($aI as $key=>$value) :
            if(is_null($value))
                $value = "null";

            $writevalues.= $key."= '";
            $writevalues.= $value."'";
            $writevalues.= ', ';

        endforeach;
        if($writevalues!=""){
            $sql.= $writevalues;
            $sql = substr($sql,0,-2);
        }

        $sql.= ' WHERE '.$w.';';
//echo $sql."<br />";
//return false;
        return $this->ExecuteQuery($sql);
    }

	function Update($t, $aI, $w){
		$sql = 'UPDATE '.$t.' SET ';

        $writevalues = "";
		foreach ($aI as $F) :
            $value = $this->ValueWriting($F);
            if(is_null($value))
                $value = "null";

            $writevalues.= $F['field'].'=';
            $writevalues.= $value;
            $writevalues.= ', ';

		endforeach;
        if($writevalues!=""){
            $sql.= $writevalues;
            $sql = substr($sql,0,-2);
        }

		$sql.= ' WHERE '.$w.';';
//echo $sql."<br />";
//return false;
        $this->ExecuteQuery($sql);
	}

	function Delete($t, $w){
		$sql = 'DELETE FROM '.$t;
		$sql.= ' WHERE '.$w.';';

    return $this->ExecuteQuery($sql);
	}

    function ValueWriting(array $a_Value){
        $value = $this->ValueSelector($a_Value);
        $this->ErrorMsg($value,$a_Value);
        $value = $this->ValueFormat($value,$a_Value['type']);
        $this->ErrorMsg($value,$a_Value);
        return $value;
    }

	function ValueSelector(array $a_Value){
        // 'field', 'selector', 'type', 'value', 'settype'
        switch($a_Value['selector']){
            case "value":   $value = $a_Value['value']; break;
            case "field":   $value = $_REQUEST[$a_Value['field']];break;
            case "chkbox":  $value = (isset($_REQUEST[$a_Value['field']])) ? 1 : 0; break;
            default:
                $value = "ERR_SELECTOR";
        }

        if(isset($a_Value['settype']) && $value!="ERR_SELECTOR")
            return $this->ValueSetType($value,$a_Value['settype']);
        else
            return $value;
	}

    function ValueSetType($value,$setType){
        if(isset($value)){
            if($setType=="int"){
                if(is_numeric($value)){
                    $val = intval($value);
                }
                else{
                    $val=0;
                }
            }
            else if($setType=="flt"){
                $value = str_replace(",",".",$value);
                $val = ToFloat($value);
            }
            else if($setType=="time"){


                if (strpos($value, ':') === false) {
                    $val = null;
                }else{
                    $val = $value;
                }
            }
            else
                $val = $value;
        }
        else{
            if($setType=="int")
                $val = 0;
            else if($setType=="flt")
                $val = 0.00;
            else if($setType=="time")
                $val = null;
            else
                $val = "";

        }
        return $val;
    }

    function ValueFormat($value,$type){
        $ctrlCheck = $this->ValueCheck($value,$type);
        if($ctrlCheck!==true)
            return $ctrlCheck;
        else if(is_null($value))
            return null;

        switch($type){
            case "str":
                return "'".addslashes($value)."'";
                break;
            case "time":
                if($value=="" || !isset($value) || is_null($value)){
                    return null;
                } else {
                    return "'".addslashes($value)."'";
                }
                break;
            case "date":
                if($value=="0000-00-00" || $value=="" || !isset($value) || is_null($value))
                    return null;
                else
                    if (strpos($value, '/') !== false) {
                        $value = DateInDB($value);
                    }
                    return "'".addslashes($value)."'";
                break;
            case "int":
                if($value=="") $value=0;
                return number_format($value,0,".","");
                break;
            case "flt":
                if($value=="") $value=0.00;
                return number_format($value,2,".","");
                break;
            case "year":
                return $value;
                break;

            default:
                return "ERROR_TYPE*".$value;
        }
    }

    function ValueCheck($value,$type){
        if(is_null($value))
            return true;
//        echo "**".$value." ".gettype($value)."**";
        switch($type){
            case "str":
                if(!$this->TypeCheck($value,"string"))
                    return "ERROR_STRING*".$value;
                break;
            case "date":
                if($value=="" || !isset($value) || is_null($value)){
                    break;
                } else {
                    if (strpos($value, '/') !== false) {
                        $value = DateInDB($value);
                    }

                    if(!$this->TypeCheck($value,"string"))
                        return "ERROR_STRING*".$value;
                    if(date('Y-m-d', strtotime($value)) != $value)
                        return "ERROR_DATE*".$value;
                    break;

                }

            case "time":
                if(!$this->TypeCheck($value,"string"))
                    return "ERROR_STRING*".$value;
                if(date('H:i', strtotime($value)) != $value && date('H:i:s', strtotime($value)) != $value)
                    return "ERROR_TIME*".$value;
                break;
            case "int":
                if(!$this->TypeCheck($value,"integer"))
                    return "ERROR_INT*".$value;
                break;
            case "flt":
                if(!$this->TypeCheck($value,"double"))
                    return "ERROR_FLOAT*".$value;
                break;
            case "year":
                if(!$this->TypeCheck($value,"string") && !$this->TypeCheck($value,"integer"))
                    return "ERROR_YEAR*".$value;
                break;
            default:
                return "ERROR_TYPE*".$value;
        }
        return true;
    }

    function TypeCheck($value,$type){
        $getType = gettype($value);
        if($getType==$type)
            return true;
        else if($type=="double" && $getType=="float")
            return true;
        else
            return false;
    }

    function ErrorMsg($error, array $a_Value, $msgType = "danger"){
        try{
            $errorExplode = explode("*",$error);
            $text = "Field: ". $a_Value['field']." - Type: ".$a_Value['type'];
            if($errorExplode[0]=="ERROR_SELECTOR")
                throw new Exception("ERROR SELECTOR! ".$text );
            else{
                switch($errorExplode[0]){
                    case "ERROR_TYPE":      $error_text = "ERROR TYPE! ";                 break;
                    case "ERROR_CHECK":     $error_text = "ERROR CHECK! ";                break;
                    case "ERROR_DATE":      $error_text = "ERROR DATE FORMAT! ";          break;
                    case "ERROR_TIME":      $error_text = "ERROR TIME FORMAT! ";          break;
                    case "ERROR_YEAR":      $error_text = "ERROR CHECK YEAR! ";           break;
                    case "ERROR_STRING":    $error_text = "ERROR CHECK STRING! ";         break;
                    case "ERROR_INT":       $error_text = "ERROR CHECK INTEGER! ";        break;
                    case "ERROR_FLOAT":     $error_text = "ERROR CHECK DOUBLE/FLOAT! ";   break;
                    default:                return true;
                }
                $error_text = $error_text.$text." - Value: ".$errorExplode[1]." - GetType: ".gettype($errorExplode[1]);
                throw new Exception($error_text);
            }
        }
        catch (Exception $e){
            $this->help->ErrorAlert($msgType, $this->ErrorReporting($e));
            die;
        }
    }

    function ErrorReporting($e,$query=null){
        $report = "<strong>ERROR REPORTING</strong><br>";
        if($query!=null)
            $report.= "<strong>QUERY: </strong>".$query."<br>";
        $msg = "<strong>MESSAGE: </strong>".$e->getMessage()."<br>";
        $code = "<strong>CODE: </strong>".$e->getCode()."<br>";
        $file = "<strong>FILE: </strong>".$e->getFile()."<br>";
        $line = "<strong>LINE: </strong>".$e->getLine()."<br>";

        return $report.$msg.$code.$file.$line;
    }

    function getNumberRow($result){
      return mysqli_num_rows($result);
    }

	function Start_Transaction(){
		$this->conn->autocommit(FALSE);
	}

    function Begin_Transaction(){
        $this->conn->begin_transaction();
    }

	function End_Transaction(){
		$this->conn->commit();
	}

    function Rollback(){
        $this->conn->rollback();
    }

    function LockTables($a_Tables){
        try{
            try{
                $comma = "";
                $str_Sql = "LOCK TABLES ";
                foreach($a_Tables as  $value){
                    $str_Sql .= $comma;
                    $str_Sql .= "$value ";
                    $comma = ",";
                }
                $str_Sql .= ";";

                return mysqli_query($this->conn, $str_Sql);
            }
            catch (mysqli_sql_exception $e){
                throw $e;
            }
        }
        catch (Exception $e) {
            if(DEBUG)
                $this->help->ErrorAlert("danger",$this->ErrorReporting($e,$str_Sql));
            else
                $this->help->ErrorAlert("danger","Errore query. Contattare il Webmaster");
            die;
        }
    }

    function UnlockTables(){
        try{
            try{
                $str_Sql = "UNLOCK TABLES;";
                return mysqli_query($this->conn, $str_Sql);
            }
            catch (mysqli_sql_exception $e){
                throw $e;
            }
        }
        catch (Exception $e) {
            if(DEBUG)
                $this->help->ErrorAlert("danger",$this->ErrorReporting($e,$str_Sql));
            else
                $this->help->ErrorAlert("danger","Errore query. Contattare il Webmaster");
            die;
        }
    }

	function __destruct(){
        if($this->conn)
		    mysqli_close($this->conn);
	}

	function lastInsertId(){
	    return mysqli_insert_id($this->conn);
    }

    function getColumnsArray($table){
	    $results = $this->ExecuteQuery("SELECT COLUMN_NAME FROM information_schema.columns WHERE table_schema='gitco2' AND table_name='".$table."'");
	    $a_columns = $this->getResults($results);
	    $a_return = array();
	    for($i=0;$i<count($a_columns);$i++){
            $a_return[$a_columns[$i]['COLUMN_NAME']] = null;
        }
        return $a_return;
    }
}
?>
