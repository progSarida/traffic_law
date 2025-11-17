<?php

function get_var ( $varname )
{
	/** GET_VAR
	 * Ricezione delle variabili globali $_POST, $_GET e $_SESSION */
	
	if(isset($_POST[$varname]))
	{
		$POST = $_POST[$varname]; return $POST;
	}
	else if(isset($_GET[$varname]))
	{
		$GET = $_GET[$varname]; return $GET;
	}
	else if(isset($_SESSION[$varname]))
	{
		$SESSION = $_SESSION[$varname]; return $SESSION;
	}
	else
	{
		return null;
	}
}

function register_globals($order = 'egpcs')
{
	/** REGISTER GLOBALS
	 * 	Simula il register_global ON
	 * 	puoi selezionare l'ordine di priorita' delle variabili globali	 */
		
    if(!function_exists('register_global_array'))
    {
        function register_global_array ( $superglobal )
        {
            foreach($superglobal as $varname => $value)
            {
                global $$varname;
                $$varname = $value;
            }
        }
    }
   
    $order = explode("\r\n", trim(chunk_split($order, 1)));
    foreach($order as $k)
    {
        switch(strtolower($k))
        {
            case 'e':    register_global_array($_ENV);       break;
            case 'g':    register_global_array($_GET);       break;
            case 'p':    register_global_array($_POST);      break;
            case 'c':    register_global_array($_COOKIE);    break;
            case 's':    register_global_array($_SERVER);    break;
        }
    }
}

function alertAllGlobalVariables()
{
	$stringa = "SESSION:"."\\n";
	$i = 0;
	if(is_array($_SESSION))
	{
		foreach ($_SESSION as $key => $value)
		{
			$i++;
			if ($key != "Motivazione_Contestazione" && $key != "Tar_Sanz_Acc_Uno"  && $key != "Tar_Sanz_Acc_Due")
			{
				//if ($i > 120)
				//if ($key == "aggiungiimmagine" || $key == "percorsoimmagine")
				$stringa = $stringa . addslashes("[$key] => $value")."\\n";
			}
		}
	}

	$stringa = $stringa . "\\nGET:\\n";
	$i = 0;
	if(is_array($_GET))
	{
		foreach ($_GET as $key => $value)
		{
			$i++;
			if ($key != "Motivazione_Contestazione" && $key != "Tar_Sanz_Acc_Uno"  && $key != "Tar_Sanz_Acc_Due")
			{
				//if ($i > 20)
				//if ($key == "aggiungiimmagine" || $key == "percorsoimmagine")
				$stringa = $stringa . addslashes("[$key] => $value")."\\n";
			}
		}
	}
	
	$stringa = $stringa . "\\nPOST:\\n";
	$i = 0;
	
	if(is_array($_POST))
	{
		foreach ($_POST as $key => $value)
		{
			$i++;
			if ($key != "Motivazione_Contestazione" && $key != "Tar_Sanz_Acc_Uno"  && $key != "Tar_Sanz_Acc_Due")
			{
				//if ($i > 80)
				//if ($key == "aggiungiimmagine" || $key == "percorsoimmagine")
				$stringa = $stringa . addslashes("[$key] => $value")."\\n";
			}
		}
	}
	
	$stringa = $stringa . "\\nFILES:\\n";
	$i = 0;
	
	if(is_array($_FILES))
	{
		foreach ($_FILES as $key => $value)
		{
			//$i++;
			//if ($key != "Motivazione_Contestazione" && $key != "Tar_Sanz_Acc_Uno"  && $key != "Tar_Sanz_Acc_Due")
			//{
			//if ($i > 80)
			//if ($key == "aggiungiimmagine" || $key == "percorsoimmagine")
			$stringa = $stringa . addslashes("[$key] => $value")."\\n";
			//}
		}
	}
	echo "<script>alert('$stringa');</script>";
}

function echoAllGlobalVariables()
{
	$stringa = "<br>SESSION:"."<br>";
	$i = 0;
	if(is_array($_SESSION))
	{
		foreach ($_SESSION as $key => $value)
		{
			$i++;
			if ($key != "Motivazione_Contestazione" && $key != "Tar_Sanz_Acc_Uno"  && $key != "Tar_Sanz_Acc_Due")
			{
				//if ($i > 120)
				//if ($key == "aggiungiimmagine" || $key == "percorsoimmagine")
				$stringa = $stringa . addslashes("[$key] => $value")."<br>";
			}
		}
	}

	$stringa = $stringa . "<br>GET:<br>";
	$i = 0;
	if(is_array($_GET))
	{
		foreach ($_GET as $key => $value)
		{
			$i++;
			if ($key != "Motivazione_Contestazione" && $key != "Tar_Sanz_Acc_Uno"  && $key != "Tar_Sanz_Acc_Due")
			{
				//if ($i > 20)
				//if ($key == "aggiungiimmagine" || $key == "percorsoimmagine")
				$stringa = $stringa . addslashes("[$key] => $value")."<br>";
			}
		}
	}
	
	$stringa = $stringa . "<br>POST:<br>";
	$i = 0;
	
	if(is_array($_POST))
	{
		foreach ($_POST as $key => $value)
		{
			$i++;
			if ($key != "Motivazione_Contestazione" && $key != "Tar_Sanz_Acc_Uno"  && $key != "Tar_Sanz_Acc_Due")
			{
				//if ($i > 80)
				//if ($key == "aggiungiimmagine" || $key == "percorsoimmagine")
				$stringa = $stringa . addslashes("[$key] => $value")."<br>";
			}
		}
	}
	
	$stringa = $stringa . "<br>FILES:<br>";
	$i = 0;
	
	if(is_array($_FILES))
	{
		foreach ($_FILES as $key => $value)
		{
			//$i++;
			//if ($key != "Motivazione_Contestazione" && $key != "Tar_Sanz_Acc_Uno"  && $key != "Tar_Sanz_Acc_Due")
			//{
			//if ($i > 80)
			//if ($key == "aggiungiimmagine" || $key == "percorsoimmagine")
			$stringa = $stringa . addslashes("[$key] => $value")."<br>";
			//}
		}
	}
	/*$stringa = $stringa . "<br>SERVER:<br>";
	$i = 0;
	foreach ($_SERVER as $key => $value)
	{
		//$i++;
		//if ($key != "Motivazione_Contestazione" && $key != "Tar_Sanz_Acc_Uno"  && $key != "Tar_Sanz_Acc_Due")
		//{
		//if ($i > 80)
			//if ($key == "aggiungiimmagine" || $key == "percorsoimmagine")
			$stringa = $stringa . addslashes("[$key] => $value")."<br>";
		//}
	}*/
	echo $stringa;
}

function alertAllGlobalVariablesSoloCampi()
{
	$stringa = "SESSION:\\n";
	$i = 0;
	
	if(is_array($_SESSION))
	{
		foreach ($_SESSION as $key => $value)
		{
			$i++;
			if ($i > 100)
				$stringa = $stringa . "[$key] => esiste\\n";
		}
	}
	$stringa = $stringa . "\\nGET:\\n";
	if(is_array($_GET))
	{	
		foreach ($_GET as $key => $value)	
			$stringa = $stringa . "[$key] => esiste\\n";
	}
	
	$stringa = $stringa . "\\nPOST:\\n";
	if(is_array($_POST))
	{
		foreach ($_POST as $key => $value)		
			$stringa = $stringa . "[$key] => esiste\\n";
	}
	echo "<script>alert('$stringa');</script>";
}

function alert($message) 
{
	echo "<script>alert(\"".$message."\");</script>";
}

?>