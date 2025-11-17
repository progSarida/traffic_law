<?php
require_once('cls_db_gestoreErrori.php');
require_once('cls_db_gestoreErroriHTML.php');
require_once('cls_db_gestoreErroriXML.php');
require_once('cls_db_gestoreErroriJSON.php');
require_once('cls_db_gestoreErroriRaccogli.php');

class  CLS_DB{
    public $conn;
    public $gestoreErrori;
    
	function __construct($gestoreErrori = null){
	    if($gestoreErrori === null){
	        $this->gestoreErrori = new cls_db_gestoreErroriHTML();
	    } else {
	        $this->gestoreErrori = $gestoreErrori;
	    }
	    
		mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
		try{
			$this->connect();
		}
		catch (Exception $e) {
            if(DEBUG)
			    $this->gestoreErrori->ErrorAlert("danger",$this->ErrorReporting($e));
            else
                $this->gestoreErrori->ErrorAlert("danger","Errore di connessione. Contattare il Webmaster");
            
            $this->gestoreErrori->esci();
		}
	}

	function connect(){
		try{
			$this->conn = mysqli_connect(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);
		}
		catch (mysqli_sql_exception $e){
			throw $e;
		}
	}
	
	function SetSessionModes(...$modes){
	    $this->ExecuteQuery("SET SESSION sql_mode = '".implode(',', $modes)."'");
	}

    function SetCharset($Charset){
        mysqli_set_charset( $this->conn, $Charset);
    }

    //TODO togliere il try catch aggiuntivo
	function ExecuteQuery($sql){
		try{
			try{
                $resultQuery = mysqli_query($this->conn, $sql);
//                 if($this->queryType($sql)!=='SELECT'){
//                     $this->saveTrace($sql);
//                 }
                $this->gestoreErrori->EsecuzioneRiuscita();
                return $resultQuery;
			}
			catch (mysqli_sql_exception $e){
				throw $e;
			}
		}
		catch (Exception $e) {
            if(DEBUG)
                $this->gestoreErrori->ErrorAlert("danger",$this->ErrorReporting($e,$sql));
            else
                $this->gestoreErrori->ErrorAlert("danger","Errore query. Contattare il Webmaster");

            $this->gestoreErrori->esci();
		}
	}

    function ExecuteBind($stmt,$sql=null){
        try{
            try{
                return $stmt->execute();
            }
            catch (mysqli_sql_exception $e){
                throw $e;
            }
        }
        catch (Exception $e) {
            if(DEBUG)
                $this->gestoreErrori->ErrorAlert("danger",$this->ErrorReporting($e,$sql));
            else
                $this->gestoreErrori->ErrorAlert("danger","Errore query. Contattare il Webmaster");
            
            $this->gestoreErrori->esci();
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
        return $this->ExecuteBind($stmt,$sql);

    }

    function foundRows(){
        $a_return = $this->getArrayLine($this->ExecuteQuery("SELECT FOUND_ROWS() AS numRows"));
        return $a_return['numRows'];
    }

    function bindInsertArray($tableName, $a_insert){

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
            $a_values[] = $val[1];
            $bindTypes.= $val[0];
        }

        $query = "INSERT INTO ".$tableName." (".$insertFields.") ";
        $query.= "VALUES (".$insertValues.")";

        return $this->bind_array($query,$bindTypes,$a_values);
    }

    function bindUpdateArray($tableName, $a_update, $filter=null){

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
            $bindTypes.= $val[0];
            $a_values[] = $val[1];
        }

        $query = "UPDATE ".$tableName." SET ".$setQuery;
        if($filter!=null)
            $query.= " ".$filter;

//        var_dump($query);
//        var_dump($bindTypes);
//        var_dump($a_values);
        return $this->bind_array($query,$bindTypes,$a_values);
    }

    function InsertOrUpdateIfExist($t, $aI){
        $sql = 'INSERT INTO '.$t;
        $qT = '(';
        $qI = ' VALUES(';
        $comma=', ';

        $writevalues = "";
        foreach ($aI as $F) :
            $value = $this->ValueWriting($F);
            if(is_null($value))
                $value = "null";

            $qT .= $F['field'].$comma;
            $qI .= $value.$comma;

            if($writevalues!="")
                $writevalues.= ', ';

            $writevalues.= $F['field'].'=';
            $writevalues.= $value;

        endforeach;
        $qT = substr($qT,0,-2).')';
        $qI = substr($qI,0,-2).')';

        $sql .=$qT.$qI;

        $sql.= " ON DUPLICATE KEY UPDATE ".$writevalues.";";

        return $this->ExecuteQuery($sql);
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

        $this->ExecuteQuery($sql);
	}


	function LastId(){
	    return $this->conn->insert_id;
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

        if($value!="ERR_SELECTOR"){
            if(isset($a_Value['nullable']) && $a_Value['nullable'] === true && is_null($value)){
                return null;
            }
            if(isset($a_Value['settype'])){
                return $this->ValueSetType($value,$a_Value['settype']);
            }
        }
            
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
                if($value==0 || $value=="0" || $value=="") $value = 0.00;

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
                $val = str_replace("\t"," ",$value);;
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

                if (strpos($value, '/') !== false) {
                    $value = DateInDB($value);
                }

                if(b_ValidateDate($value)) return "'".addslashes($value)."'";
                else return null;



                break;
            case "int":
                if($value=="") $value=0;
                return number_format($value,0,".","");
                break;
            case "flt":
                if($value==0 || $value=="0" || $value=="") $value = 0.00;
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

                break;
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
            $this->gestoreErrori->ErrorAlert($msgType, $this->ErrorReporting($e));

            $this->gestoreErrori->esci();
        }
    }

    function ErrorReporting($e,$query=null){
        $reportHTML = "<strong>ERROR REPORTING</strong><br>";
        if($query!=null) $reportHTML.= "<strong>QUERY: </strong>".$query."<br>";
        $reportHTML.= "<strong>MESSAGE: </strong>".$e->getMessage()."<br>";
        $reportHTML.= "<strong>CODE: </strong>".$e->getCode()."<br>";
        $reportHTML.= "<strong>FILE: </strong>".$e->getFile()."<br>";
        $reportHTML.= "<strong>LINE: </strong>".$e->getLine()."<br>";
        
        $report = "";
        if($query!=null) $report.= "QUERY: ".$query;
        $report.= " | MESSAGE: ".$e->getMessage();
        $report.= " | CODE: ".$e->getCode();

//         //TELEGRAM
//         $token = "552795312:AAF6CG15tmhOgpYrBFUdkDvoO12R6ILUiSs";


//         $chatIds = array("304222168");
//         foreach($chatIds as $chatId) {
//             // Send Message To chat id

//             $data = [
//                 'text' => 'error: '.$report.$msg.$code.$file.$line,
//                 'chat_id' => $chatId
//             ];


//             file_get_contents("https://api.telegram.org/bot$token/sendMessage?" . http_build_query($data));

//         }

        error_log("ERRORE SQL: ".$report);

        return $reportHTML;
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
                $this->gestoreErrori->ErrorAlert("danger",$this->ErrorReporting($e,$str_Sql));
            else
                $this->gestoreErrori->ErrorAlert("danger","Errore query. Contattare il Webmaster");
            
            $this->gestoreErrori->esci();
        }
    }

    function getArrayLine($results, $mode = MYSQLI_ASSOC){
        return $results ? $results->fetch_array($mode) : null;
    }

    function getObjectLine($results){
        return $results ? $results->fetch_object() : null;
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
                $this->gestoreErrori->ErrorAlert("danger",$this->ErrorReporting($e,$str_Sql));
            else
                $this->gestoreErrori->ErrorAlert("danger","Errore query. Contattare il Webmaster");
            
            $this->gestoreErrori->esci();
        }
    }

	function __destruct(){
        if($this->conn)
		    mysqli_close($this->conn);
	}
    function saveTrace($sql){
        $uid = $_SESSION['username']!=null?$_SESSION['username']:'SISTEMA';
        $current_date =  date('Y-m-d'); //Date format --> 2022-10-27
        $current_time = date('H:i:s'); //Time format --> 17:13:00
        $query_type = $this->queryType($sql);
        $cityid = $_SESSION['cityid']!=null?$_SESSION['cityid']:'';
        //echo $current_date." - ".$current_time." - ".$query_type." - ".$uid." ";
        //echo $uid." - ";
        $query = str_replace(array("\r", "\n"), '', mysqli_real_escape_string($this->conn,$sql));
        //echo $query.'<br>';
        try {
            //mysqli_query($this->conn,"INSERT INTO QueryLog(Type, Query, UserId, RegDate, RegTime) VALUES('".$query_type."','".$sql."','".$uid."',".$current_date.",".$current_time.")");
            mysqli_query($this->conn,"INSERT INTO QueryLog(Type, Query, UserId, RegDate, RegTime, CityId) VALUES('".$query_type."','".$query."','".$uid."','".$current_date."','".$current_time."','".$cityid."')");
        }
        catch (mysqli_sql_exception $e) {
            throw $e;
        }
    }
    
    function queryType($sql){
        if(strpos($sql,'SELECT')!==false || strpos($sql,'select')!==false)
            return 'SELECT';
            if(strpos($sql,'INSERT')!==false || strpos($sql,'insert')!==false)
                return 'INSERT';
                if(strpos($sql,'UPDATE')!==false || strpos($sql,'update')!==false)
                    return 'UPDATE';
                    if(strpos($sql,'DELETE')!==false || strpos($sql,'delete')!==false)
                        return 'DELETE';
                        return 'NONE';
    }
}
function b_ValidateDate($date, $format = 'Y-m-d'){
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) == $date;
}


