<?php
   include $DOCUMENT_ROOT . "/gitco/percorsi.php";
   include LIBRERIE . "/form_lib.php";
   include LIBRERIE . "/funzioni_db.php";
   include LIBRERIE . "/cds_lib.php";
   include LIBRERIE . "/connessione_db.php";
   include CLASSI . "/parametri_classi.php";   
?>
<!doctype html public "-//W3C//DTD HTML 4.0 //EN">
<html>
<head>
   <title>Documentazione Atto</title>
   <LINK REL=StyleSheet HREF="../stili.css" TYPE="text/css" MEDIA=screen>
   <script type="text/javascript" language="javascript" src="../cds.js"></script>
   <script>var modifica=0;</script>
   <script>
   
   //queste due funzioni servono per gestire due pop-up di interrogazione
   
   function sceltaconfirm(c,a,trasf,preinserimento,scelta_app_1)
   {
       flag = confirm("Si sta sovrascrivendo una richiesta dati a cui non � ancora arrivata risposta. Si vuole procedere con la sovrascrittura dei dati presenti?");
       if (flag==true)
       {
           //document.cds_trasf_dati.scelta_app.value='1';
           self.location.href="cds_trasferimento_dati_anci_stampa.php?c="+c+"&a="+a+"&trasf="+trasf+"&preinserimento="+preinserimento+"&scelta_app_1="+scelta_app_1+"&scelta_app="+1;
           
       }
       
   }
   function sceltaconfirm_1(c,a,trasf,preinserimento,scelta_app)
   {
       flag = confirm("Si sta sovrascrivendo una richiesta dati a cui non si � ancora eseguita la procedura di importazione. Si vuole procedere con la sovrascrittura dei dati presenti?");
       if (flag==true)
       {
           //document.cds_trasf_dati.scelta_app.value="SI";
           self.location.href="cds_trasferimento_dati_anci_stampa.php?c="+c+"&a="+a+"&trasf="+trasf+"&preinserimento="+preinserimento+"&scelta_app="+scelta_app+"&scelta_app_1="+1;
       }
       
   }
   </script>

</head>
<body class="moduli">
<form name="cds_trasf_dati" method=post action="cds_trasferimento_dati_anci_stampa.php">
<?php
echo <<< END
<input type="hidden" name="c" value="$c">
<input type="hidden" name="a" value="$a">
<input type="hidden" name="tipo" value="$tipo">
<input type="hidden" name="nuovo" value="$nuovo">
<input type="hidden" name="regi_progr" value="$regi_progr">
<input type="hidden" name="scelta_app" value="$scelta_app">
<input type="hidden" name="scelta_app_1" value="$scelta_app_1">
<input type="hidden" name="trasf" value="$trasf">
END;

if ($scelta_app==NULL)
		$scelta_app=0;;			// definizioni per il corretto funzionamento delle funzioni di sceltaconfirm
if ($scelta_app_1==NULL)
$scelta_app_1=0;


$cur_par = new parametri($c,$a,"CDS");

// variabili di definizione per il modulo "moto" per il trasferimento dati via ftp
// al sito del ministero

		/*  $ftp_server="62.94.231.186";
        $ftp_user_name="ftpusers";
        $ftp_user_pass="ftpusers";*/		
		$ftp_server='infoftp.dtt';
        $ftp_user_name='sar45';
        $ftp_user_pass='sar45';
        
        $nome_semaforo='OK-SA';
	    $nome_inp='INP-SA';
		$nome_outp='OUT-SA';

$iop='MOTO';
    $percorso_file="$DOCUMENT_ROOT/trasf_ministero/";
		  
if($preinserimento==1)
{
		$db_com_cc=sprintf("%s.anci_pra",$c);
		$pre_frase='preinserimento '; //utilizzata nei file trasm_ftp_control
												//per controllare se ho ins o pre
}
else
{
		$db_com_cc=sprintf("%s.anci_pra_ins",$c);
		$pre_frase='inserimento ';
}
if($preinserimento==1)
{
	$pre_file_pccsa="_pre_$c";
}
else
{
	$pre_file_pccsa="_anci_$c";
}


//Se � selezionata la motorizzazione non devo usare il PCCSA
if($cur_par->Par_Tipo_Richiesta_Dati=="MOTO")
{		
	/*********************************************************************/
			// controllo che il file out-xx in locale sia stato importato
			if ((file_exists($percorso_file.$nome_outp))and ($scelta_app_1!="1"))  
			{
   			//echo"<script>sceltaconfirm_1('$c',$a,$trasf,$preinserimento,$scelta_app) </script>";
			
			//leggo il nome dell'ultimo comune che ha ricevuto dati ma non li ha importati
					 				 				
				if (file_exists("$DOCUMENT_ROOT/trasf_ministero/trasm_ftp_control_inp"))
				{
					$fp_control=fopen("$DOCUMENT_ROOT/trasf_ministero/trasm_ftp_control_inp",'r');
					$frase_control=fgets($fp_control);
					fclose($fp_control);
				} 
				echo"$frase_control";	
				echo"<script>alert('� presente il file di risposta $percorso_file.$nome_outp : non ancora importato ');self.close(); </script>";

				//if ($scelta_app_1!="1") 
					die;					
			}
	
	// Controllo che il file semaforo trasferito dal ministero sia output
			/* $fp_semaforo=fopen($percorso_file.$nome_semaforo,"r+"); 
			
			if ((strpos(fgets($fp_semaforo), "OUTPUT COMPLETO")===FALSE)and ($scelta_app!="1"))  
					 

		      //echo"<script>sceltaconfirm('$c',$a,$trasf,$preinserimento,$scelta_app_1) </script>";
					
					//leggo il nome dell'ultimo comune che ha trasmesso dati
						*/ 		 		
			if (file_exists("$DOCUMENT_ROOT/trasf_ministero/trasm_ftp_control_inp"))
			{
				$fp_control=fopen("$DOCUMENT_ROOT/trasf_ministero/trasm_ftp_control_inp",'r');
				$frase_control=fgets($fp_control);
				fclose($fp_control);
				
				echo "$frase_control<br><br>";
				
				$splittato = explode (" ", $frase_control);
				$comune_attuale = get_Com_Nome($c);
				$comune_attuale = strtoupper($comune_attuale);
				if ($splittato[3] == $c)
				{
					echo <<< FRASEAIUTO
						La precedente richiesta � stata effettuata dal Comune di $comune_attuale,<br>
						lo stesso da cui stai lavorando.<br>
						Puoi rimuovere la precedente richiesta, andando su<br>
						Richiesta Dati --> Annulla Richiesta Dati Precedente
						
FRASEAIUTO;
				}
				echo "<script>alert('Non � possibile inviare richieste perch� � presente una richiesta precedente'); self.close();</script>";
				
				die;					
			}
			 	
 		//fclose($fp_semaforo);  
        
 /*********************************************************************/

    //Apertura dei file che servono: serve il file INP-XX e il file semaforo
    
    $fh=fopen($percorso_file.$nome_inp, "w");
    // apro e creo il file "semaforo". Nel software di invio cambiare il nome
    // del file con "semaforo"
    
    $fh_semaforo=fopen($percorso_file.$nome_semaforo, "w");
    $data_richiesta=date("dmY"); 	
    fputs($fh_semaforo,"INPUT COMPLETO $data_richiesta");
    fclose($fh_semaforo);
}
else
{ 
    //**** scrittura del file default.drt del Pccsa *******/
    //$fh_file=fopen("/www/html/PCCSAClient/classes/default.drt", "w");
    $fh_file=fopen("$DOCUMENT_ROOT/PCCSAClient/classes/default.drt", "w");
    fputs($fh_file,"USER user_$c.txt\nDATA dati$pre_file_pccsa.txt default$pre_file_pccsa.out");
    fclose($fh_file);
    /********** fine ********************/
    //**** scrittura del file user_$c.txt del Pccsa per inserimento Utente e Psw del comune in esame**/
    //$fh_user=fopen("/www/html/PCCSAClient/user_$c.txt", "w");
    $fh_user=fopen("$DOCUMENT_ROOT/PCCSAClient/user_$c.txt", "w");
    fputs($fh_user,"JJ+;APLO+$cur_par->Par_Utente_Pccsa+$cur_par->Par_Psw_Pccsa;");
    fclose($fh_user);
    /********** fine selezione Utente e psw  ********************/
    //$fh=fopen("/www/html/PCCSAClient/dati$pre_file_pccsa.txt", "w");
    $fh=fopen("$DOCUMENT_ROOT/PCCSAClient/dati$pre_file_pccsa.txt", "w");
}

$numeroRighe = 0;
if($preinserimento==1)
{
    if($trasf==1)
    { 	
        $query="select * from $db_com_cc where 1";        
        $res=safe_query($query);
        $num_res=mysql_num_rows($res);
        //Serve il record di logon dell'utente
        //(da sapere il codice utente e la password assegnateci da ACI/PRA):
        //fput_margine_new($c,$marg,$fh,"JJ+;APLO");
        //Questo tracciato record deve essere inserito in un file (es:user.in) che
        //dovr� essere criptato con l'apposito comando da dos:crypt user.in user.cry.
        //Il file crypt.exe che permette l'utilizzo del comando deriva direttamente
        //dall'installazione del PC/CSA

        if($num_res>0)
        {		
            while($dati_anci=mysql_fetch_array($res,MYSQL_ASSOC))
            {
            	$stringa_terminatori='JJ+;';
                $tipo_record='APVI+';
                if($cur_par->Par_Tipo_Richiesta_Dati=="MOTO")
                {
                    fput_margine_new($c,$marg,$fh,"90");
                }
                else
                {
                    fput_margine_new($c,$marg,$fh,"$stringa_terminatori$tipo_record");
                }			
                $query="select * from preinserimento_verbali_cds where Pre_Progr='$dati_anci[Anc_Progr_Registro]'";
                $ris=safe_query($query);
                $registro=mysql_fetch_array($ris);
               
                $data_reg=$registro[Pre_Data_Verbale];
                extract_date($data_reg);
                $reg_data=str_replace("/","",$data_reg);

                $tipo_veicolo=strtoupper($registro[Pre_Tipologia_Veicolo]);
                if($registro[Pre_Targa_Veicolo]!=NULL and $registro[Pre_Telaio_Veicolo]!=NULL)
                {
                	$tipo_richiesta='T';
                    $lung=strlen($registro[Pre_Targa_Veicolo]);
                    if($registro[Pre_Tipologia_Veicolo]=='autovettura')
                    {
                        if($lung==7)
                        {
                            //Separo le prime 2 lettere
                            $prov=substr($registro[Pre_Targa_Veicolo],0,2);
                            $numeri=substr($registro[Pre_Targa_Veicolo],2);
                            if(is_numeric($numeri))
                            {
                                $num="0$numeri";
                                $targa="$prov$num";
                            }
                            else
                            {
                                $targa=$registro[Pre_Targa_Veicolo];
                            }
                            $dopo=substr($registro[Pre_Targa_Veicolo],5);
                            if(ereg("[[:alpha:]]",$dopo))
                            {
                                //Targa nuovo formato quindi non aggiungo zero
                                $targB_motor="$numeri ";
                            }
                            else
                            {
                                //Per la motorizzazione se la targa � vecchia devo inserire uno 0 tra la provincia
                                //e i numeri
                                $query="select Pro_Progr from provincia where Pro_Sigla='$prov'";
                                $pro_progr=single_answer_query($query);
                                
                                if ($registro[Pre_Tipologia_Veicolo] != 'motoveicolo')
                                {
	                                switch (Ultime2Lettere($numeri))
	                                {
	                                	case "TUTTELETTERE": $targB_motor = "$numeri "; break; //Targa nuova
	                                	case "NUMERIELETTERE": $targB_motor = "0$numeri"; break; //Targa vecchia: aggiungo uno 0 davanti ai numeri
	                                }
                                }
                                else $targB_motor = "$numeri ";
                                
                                /*if($pro_progr==0 or $pro_progr==NULL)
                                {
                                    //Targa nuova
                                    $targB_motor="$numeri ";
                                }
                                else
                                {
                                    //Targa vecchia: aggiungo uno 0 davanti ai numeri
                                    $targB_motor="0$numeri";
                                }*/
                            }
                        }
                        else
                        {
                            $prov=substr($registro[Pre_Targa_Veicolo],0,2);
                            $targa=$registro[Pre_Targa_Veicolo];
                            $targ_motor=substr($registro[Pre_Targa_Veicolo],2);
                            $targB_motor="$targ_motor";
                        }
                    }
                    elseif($registro[Pre_Tipologia_Veicolo]=='ciclomotore')
                    {
                        $prov="  ";
                        //$targB_motor="$registro[Pre_Targa_Veicolo] ";
                        $targB_motor = $registro[Pre_Targa_Veicolo];
                    }
                    else
                    {
                    	if($lung==7)
                        {
                            //Se sono lunghe 7 caratteri devo vedere se sono nuove (ok)
                            //o vecchie (aggiungo uno zero).
                            $prov=substr($registro[Pre_Targa_Veicolo],0,2);
                            $numeri=substr($registro[Pre_Targa_Veicolo],2);
                            $query="select Pro_Progr from provincia where Pro_Sigla='$prov'";
                            $pro_progr=single_answer_query($query);
                            
                            if ($registro[Pre_Tipologia_Veicolo] != 'motoveicolo')
                            {
                            	switch (Ultime2Lettere($numeri))
	                            {
	                            	case "TUTTELETTERE": //Targa nuova
	                            		$targa = $registro[Pre_Targa_Veicolo];
	                            		$targB_motor = "$numeri ";
	                            		break;
	                            	case "NUMERIELETTERE": //Targa vecchia: aggiungo uno 0 davanti ai numeri
	                            		$num = "0$numeri";
	                            		$targa = "$prov$num";
	                            		$targB_motor = "0$numeri";
	                            		break;
	                            }
                            }
                            else
                            {
                            	$targa = $registro[Pre_Targa_Veicolo];
                            	$targB_motor = "$numeri ";
                            }
                            
                            /*if($pro_progr!=0 and $pro_progr!=NULL)
                            {
                                //Sono targhe vecchie
                                $num="0$numeri";
                                $targa="$prov$num";
                                $targB_motor="0$numeri";
                            }
                            else
                            {
                                //Sono targhe nuove
                                $targa=$registro[Pre_Targa_Veicolo];
                                $targB_motor="$numeri ";
                            }*/
                        }
                        else
                        {
                            $prov=substr($registro[Pre_Targa_Veicolo],0,2);
                            $targa=$registro[Pre_Targa_Veicolo];
                            $targ_motor=substr($registro[Pre_Targa_Veicolo],2);
                            $targB_motor="$targ_motor";
                        }
                    }
                }
                elseif($registro[Pre_Targa_Veicolo]!=NULL and $registro[Pre_Telaio_Veicolo]==NULL)
                {
                	$tipo_richiesta='T';
                    $lung=strlen($registro[Pre_Targa_Veicolo]);
                    if($registro[Pre_Tipologia_Veicolo]=='autovettura')
                    {
                        if($lung==7)
                        {
                            //Separo le prime 2 lettere
                            $prov=substr($registro[Pre_Targa_Veicolo],0,2);
                            $numeri=substr($registro[Pre_Targa_Veicolo],2);
                            if(is_numeric($numeri))
                            {
                                $num="0$numeri";
                                $targa="$prov$num";
                            }
                            else
                            {
                                $targa=$registro[Pre_Targa_Veicolo];
                            }
                            //Per la motorizzazione se la targa � vecchia devo inserire uno 0 tra la provincia
                            //e i numeri
                            $dopo=substr($registro[Pre_Targa_Veicolo],5);
                            if(ereg("[[:alpha:]]",$dopo))
                            {
                                //Targa nuovo formato quindi non aggiungo zero
                                $targB_motor="$numeri ";
                            }
                            else
                            {
                                //Per la motorizzazione se la targa � vecchia devo inserire uno 0 tra la provincia
                                //e i numeri
                                $query="select Pro_Progr from provincia where Pro_Sigla='$prov'";
                                $pro_progr=single_answer_query($query);
                                
                                if ($registro[Pre_Tipologia_Veicolo] != 'motoveicolo')
	                            {
	                            	switch (Ultime2Lettere($numeri))
	                                {
	                                	case "TUTTELETTERE": $targB_motor = "$numeri "; break; //Targa nuova
	                                	case "NUMERIELETTERE": $targB_motor = "0$numeri"; break; //Targa vecchia: aggiungo uno 0 davanti ai numeri
	                                }
	                            }
	                            else $targB_motor = "$numeri ";
                                
                                /*if($pro_progr==0 or $pro_progr==NULL)
                                {
                                    //Targa nuova
                                    $targB_motor="$numeri ";
                                }
                                else
                                {
                                    //Targa vecchia: aggiungo uno 0 davanti ai numeri
                                    $targB_motor="0$numeri";
                                }*/
                            }
                        }
                        else
                        {
                        	$prov=substr($registro[Pre_Targa_Veicolo],0,2);
                            $targa=$registro[Pre_Targa_Veicolo];
                            $targ_motor=substr($registro[Pre_Targa_Veicolo],2);
                            $targB_motor="$targ_motor";
                        }
                    }
                    elseif($registro[Pre_Tipologia_Veicolo]=='ciclomotore')
                    {
                        $prov="  ";
                        //$targB_motor="$registro[Pre_Targa_Veicolo] ";
                        $targB_motor = $registro[Pre_Targa_Veicolo];
                    }
                    else
                    {
                        if($lung==7)
                        {
                            //Se sono lunghe 7 caratteri devo vedere se sono nuove (ok)
                            //o vecchie (aggiungo uno zero).
                            $prov=substr($registro[Pre_Targa_Veicolo],0,2);
                            $numeri=substr($registro[Pre_Targa_Veicolo],2);
                            $query="select Pro_Progr from provincia where Pro_Sigla='$prov'";
                            $pro_progr=single_answer_query($query);
                            
                            if ($registro[Pre_Tipologia_Veicolo] != 'motoveicolo')
                            {
                            	switch (Ultime2Lettere($numeri))
	                            {
	                            	case "TUTTELETTERE": //Targa nuova
	                            		$targa = $registro[Pre_Targa_Veicolo];
	                            		$targB_motor = "$numeri ";
	                            		break;
	                            	case "NUMERIELETTERE": //Targa vecchia: aggiungo uno 0 davanti ai numeri
	                            		$num = "0$numeri";
	                            		$targa = "$prov$num";
	                            		$targB_motor = "0$numeri";
	                            		break;
	                            }
                            }
                            else 
                            {
                            	$targa = $registro[Pre_Targa_Veicolo];
                            	$targB_motor = "$numeri ";
                            }
                            
                            /*if($pro_progr!=0 and $pro_progr!=NULL)
                            {
                                //Sono targhe vecchie
                                $num="0$numeri";
                                $targa="$prov$num";
                                $targB_motor="0$numeri";
                            }
                            else
                            {
                                //Sono targhe nuove
                                $targa=$registro[Pre_Targa_Veicolo];
                                $targB_motor="$numeri ";
                            }*/
                        }
                        else
                        {
                            $prov=substr($registro[Pre_Targa_Veicolo],0,2);
                            $targa=$registro[Pre_Targa_Veicolo];
                            $targ_motor=substr($registro[Pre_Targa_Veicolo],2);
                            $targB_motor="$targ_motor";
                        }
                    }
                }
                elseif($registro[Pre_Targa_Veicolo]==NULL and $registro[Pre_Telaio_Veicolo]!=NULL)
                {
                    $tipo_richiesta='L';
                    $targa=$registro[Pre_Telaio_Veicolo];
                }
                $fine_record="&";
                if($registro[Pre_Tipologia_Veicolo]=='autovettura' or $registro[Pre_Tipologia_Veicolo]=='autocarro' or $registro[Pre_Tipologia_Veicolo]=='bus')
                {
                    $tipo_veic='1';
                    $tipo_veic_motor='A';
                }
                elseif($registro[Pre_Tipologia_Veicolo]=='motoveicolo' or $registro[Pre_Tipologia_Veicolo]=='ciclomotore')
                {
                    $tipo_veic='4';
                    if($registro[Pre_Tipologia_Veicolo]=='ciclomotore')
                    {
                        $tipo_veic_motor='C';
                    }
                    else
                    {
                        $tipo_veic_motor='M';
                    }
                }
                elseif($registro[Pre_Tipologia_Veicolo]=='rimorchio' or $registro[Pre_Tipologia_Veicolo]=='altro')
                {
                    $tipo_veic='2';
                    $tipo_veic_motor='A';
                }
                if($cur_par->Par_Tipo_Richiesta_Dati=="MOTO")
                {
                    
					
					$aa_data_reg=substr($registro[Pre_Data_Verbale],0,4);					
                    $mm_data_reg=substr($registro[Pre_Data_Verbale],5,2);
                    $gg_data_reg=substr($registro[Pre_Data_Verbale],8,2);
                    $data_richiesta=date("dmY");
                    $data_avviso="$gg_data_reg$mm_data_reg$aa_data_reg";
                    $query="select Com_Prov from comune where Com_CC='$c'";
                    $com_prov=single_answer_query($query);
                    $pro_sigla = $cur_par->Par_Provincia_Richiedente; 
                    $com_nome = $cur_par->Par_Comune_Richiedente; 
                    $lung_com_nome=strlen($com_nome); 
                    if($lung_com_nome>22)
                    {
					
                        $nome_comune=substr($com_nome,0,21);						
                        $n_spazi=0;						
					
                    }
                    elseif($lung_com_nome==22)
                    {
                        $nome_comune=$com_nome;
                        $n_spazi=0;						
                    }
                    elseif($lung_com_nome<22)
                    {
                        $nome_comune="$com_nome";
                        $n_spazi=22-$lung_com_nome;						
                    }
                    //Inserisco il numero del verbale come progressivo della tabela del preinserimento
                    $numero_verbale=$registro[Pre_Progr];
                    $lung=strlen($numero_verbale);
                    
                    while (strlen($targB_motor) < 6)
                    	$targB_motor .= " ";

                    fput_margine_new($c,$marg,$fh,"$prov$tipo_veic_motor$targB_motor$data_richiesta      $data_avviso$numero_verbale");
                    stamp_spazi((6-$lung),0,$fh);
                    fput_margine_new($c,$marg,$fh,"  $pro_sigla$nome_comune");
                    
                    for($ss=0;$ss<($n_spazi+19);$ss++)
                    {
                        fput_margine_new($c,$marg,$fh," ");
                    }
                }
                else
                {
                    fput_margine_new($c,$marg,$fh,"$reg_data+$tipo_richiesta+$tipo_veic+$targa;");
                }
                
                $numeroRighe ++;
                if ($numeroRighe != $num_res)
                    fput_margine_new($c,$marg,$fh,"\n");
                
            }  //  fine while
        }
        //return;
        //alert ($cur_par->Par_Tipo_Richiesta_Dati);
        if($cur_par->Par_Tipo_Richiesta_Dati=="MOTO")
        {	
			//***************************************************************** INIZIO CONESSIONE FTP CON LA MOTORIZZAZIONE **************************************************//	
			//********************************************************* Ricezione del file OK-SA ****************************************************************************//
			//exec ("C:/WINDOWS/System32/rasdial Motorizzazione 185450860 185450860"); //Conessione della linea ISDN		
			$local_file = $percorso_file.$nome_semaforo;  //Nome del file locale 	
			$server_file = $nome_semaforo; //Nome del file del server ftp	
			$conn_id = ftp_connect($ftp_server); //Conessione con il server ftp				
			ob_start($conn_id); 			
			if($conn_id=='')
			{
				echo "<br>";
				echo "<br>";
				echo "ALERT1!!! conessione non riuscita con il server FTP della Motorizzazione, ripovare pi� tardi, altrimenti provare a riavviare il Server";	
				echo "<br>";
				//Chiudo la conessione ISDN con la Motorizzazione
				//exec ("C:/WINDOWS/System32/rasdial Motorizzazione /d");	
				exit;
			}	
			$login_result = ftp_login($conn_id, $ftp_user_name, $ftp_user_pass); //Login con utente e password		
			ob_start($login_result); 
			if($login_result=='')
			{
				echo "<br>";
				echo "<br>";
				echo "ALERT2!!! conessione non riuscita con il server FTP della Motorizzazione";	
				echo "<br>";
				//Chiudo la conessione ISDN con la Motorizzazione
				//exec ("C:/WINDOWS/System32/rasdial Motorizzazione /d");	
				exit;
			}
			if($_SESSION['username']=='emanueleb')
			{
				//echo $conn_id, ' --- ', $login_result;
				//echo "<br>";
				//exit;
			}			
					
		//********************************************************* Invio del file OK-SA ****************************************************************************//		
		if (ftp_put($conn_id, $server_file, $local_file, FTP_BINARY)) //Scarico il file del server $server_file e lo salvo in locale con il nome $local_file
		{
			//echo "File $local_file inviato con sucesso alla Motorizzazione";	
			//echo "<br>";
			/*
			//Visualiza i file che della cartella 
			$contents = ftp_nlist($conn_id, ".");		
			// output $contents
			var_dump($contents);
			*/
		} 
		else 
		{
			echo "ATTENZIONE!!! E' stato un problema nell'invio del file $local_file";	
			echo "<br>";
			//Chiudo la conessione ISDN con la Motorizzazione
			//exec ("C:/WINDOWS/System32/rasdial Motorizzazione /d");			
			exit;
		}	
		
		//********************************************************* Invio del file INP-SA ****************************************************************************//	
		$local_file = $percorso_file.$nome_inp; //Nome del file locale 	
		$server_file = $nome_inp; //Nome del file del server ftp
		
		if (ftp_put($conn_id, $server_file, $local_file, FTP_BINARY)) //Scarico il file del server $server_file e lo salvo in locale con il nome $local_file
		{			
			echo "<br>";
			echo "Il file di richiesta dati ($nome_inp) � stato inviato con successo alla Motorizzazione";	
			echo "<br>";

			/*
			//Visualiza i file che della cartella 
			$contents = ftp_nlist($conn_id, ".");		
			// output $contents
			var_dump($contents);
			*/
		} 
		else 
		{
			echo "ATTENZIONE!!! E' stato un problema nell'invio del file $local_file";	
			echo "<br>";
			//Chiudo la conessione ISDN con la Motorizzazione
			//exec ("C:/WINDOWS/System32/rasdial Motorizzazione /d");
			exit;
		}	
		
		//Chiudo la conessione del FTP con la Motorizzazione
		ftp_close($conn_id);
		//Chiudo la conessione ISDN con la Motorizzazione
		//exec ("C:/WINDOWS/System32/rasdial Motorizzazione /d");
		//***************************************************************** FINE CONESSIONE FTP CON LA MOTORIZZAZIONE ***************************************************//
			
 		copy ($percorso_file.$nome_inp,$percorso_file.'/backup/'.$nome_inp."_".date("dmy")."_".$c); 
      
		  //creo un file di controllo per sapere quale comune ha inviato per ultimo una 
		  //richiesta 
		  $fh_ftp_control_inp=fopen("$DOCUMENT_ROOT/trasf_ministero/trasm_ftp_control_inp","w");
		  fputs($fh_ftp_control_inp,$pre_frase.$nome_outp.' '.date("d/m/y").' '.$c.' '.get_Com_Nome($c)); 
		  fclose($fh_ftp_control_inp);
      }  
		else
		{ 
			//funzione per esecuzione PCCSA
			 //chdir('/www/html/PCCSAClient/');
			 chdir('$DOCUMENT_ROOT/PCCSAClient/');
			 $str_pccsa="./StartPccsa.sh";
			 system($str_pccsa);
		}
        //echo "<script> self.close() </script> ";
    }
    else 
    {
        $query="select * from $db_com_cc where 1";
        $res=safe_query($query);
        $num_res=mysql_num_rows($res);
        if($num_res>0)
        {		
            echo <<< END
            <table class="minima" width="600" cellpadding="0" cellspacing="0" border="0">
END;
            while($dati_anci=mysql_fetch_array($res,MYSQL_ASSOC))
            {
                $query="select * from preinserimento_verbali_cds where Pre_Progr='$dati_anci[Anc_Progr_Registro]'";
                $ris=safe_query($query);
                $registro=mysql_fetch_array($ris);
                if($registro[Pre_Tipo]=='P')
                {
                    $nome="Preavviso";
                }
                elseif($registro[Pre_Tipo]=='A')
                {
                    $nome="Avviso";
                }
                elseif($registro[Pre_Tipo]=='V')
                {
                    $nome="Verbale";
                }
                $anno=$registro[Pre_Anno_Verbale];
                $tipo_veicolo=strtoupper($registro[Pre_Tipologia_Veicolo]);
                echo <<< END
                <tr>
                <td width="80">
                <b>$nome</b>
                </td>
                <td width="200">
                Anno <b>$anno</b>
                </td>
                <td width="320">
                <b>$tipo_veicolo</b>&nbsp;&nbsp;
                Tg. <b>$registro[Pre_Targa_Veicolo]</b>&nbsp;
                Tel.<b>$registro[Pre_Telaio_Veicolo]</b>
                </td>
                </tr>
                <tr>
                <td width="600" colspan="3">
                <hr>
                </td>
                </tr>
END;
            }
            echo"</table>";
        }
    }
}
else
{
    if($trasf==1)
    {        
        $query="select * from $db_com_cc where 1";
        $res=safe_query($query);
        $num_res=mysql_num_rows($res);
        //Serve il record di logon dell'utente
        //(da sapere il codice utente e la password assegnateci da ACI/PRA):
        //fput_margine_new($c,$marg,$fh,"JJ+;APLO");
        //Questo tracciato record deve essere inserito in un file (es:user.in) che
        //dovr� essere criptato con l'apposito comando da dos:crypt user.in user.cry.
        //Il file crypt.exe che permette l'utilizzo del comando deriva direttamente
        //dall'installazione del PC/CSA
        
        if($num_res>0)
        {
            while($dati_anci=mysql_fetch_array($res,MYSQL_ASSOC))
            {
                $stringa_terminatori='JJ+;';
                $tipo_record='APVI+';
                if($cur_par->Par_Tipo_Richiesta_Dati=="MOTO")
                {
                    fput_margine_new($c,$marg,$fh,"90");
                }
                else
                {
                    fput_margine_new($c,$marg,$fh,"$stringa_terminatori$tipo_record");
                }

                $query="select * from registro_cronologico_cds where Reg_Progr='$dati_anci[Anc_Progr_Registro]'";
                $ris=safe_query($query);
                $registro=mysql_fetch_array($ris);

                if($registro[Reg_Tipo]=='P')
                {
                    $data_reg=$registro[Reg_Data_Preavviso];
                }
                elseif($registro[Reg_Tipo]=='A')
                {
                    $data_reg=$registro[Reg_Data_Avviso];
                }
                elseif($registro[Reg_Tipo]=='V')
                {
                    $data_reg=$registro[Reg_Data_Verbale];
                }
                extract_date($data_reg);
                $reg_data=str_replace("/","",$data_reg);
                $tipo_veicolo=strtoupper($registro[Reg_Tipologia_Veicolo]);
                if($registro[Reg_Targa_Veicolo]!=NULL and $registro[Reg_Telaio_Veicolo]!=NULL)
                {
                    $tipo_richiesta='T';
                    $lung=strlen($registro[Reg_Targa_Veicolo]);
                    if($registro[Reg_Tipologia_Veicolo]=='autovettura')
                    {
                        if($lung==7)
                        {
                            //Separo le prime 2 lettere
                            $prov=substr($registro[Reg_Targa_Veicolo],0,2);
                            $numeri=substr($registro[Reg_Targa_Veicolo],2);
                            if(is_numeric($numeri))
                            {
                                $num="0$numeri";
                                $targa="$prov$num";
                            }
                            else
                            {
                                $targa=$registro[Reg_Targa_Veicolo];
                            }
                            $dopo=substr($registro[Reg_Targa_Veicolo],5);
                            if(ereg("[[:alpha:]]",$dopo))
                            {
                                //Targa nuovo formato quindi non aggiungo zero
                                $targB_motor="$numeri ";
                            }
                            else
                            {
                                //Per la motorizzazione se la targa � vecchia devo inserire uno 0 tra la provincia
                                //e i numeri
                                $query="select Pro_Progr from provincia where Pro_Sigla='$prov'";
                                $pro_progr=single_answer_query($query);
                                
                                if ($registro[Reg_Tipologia_Veicolo] != 'motoveicolo')
	                            {
	                            	switch (Ultime2Lettere($numeri))
	                                {
	                                	case "TUTTELETTERE": $targB_motor = "$numeri "; break; //Targa nuova
	                                	case "NUMERIELETTERE": $targB_motor = "0$numeri"; break; //Targa vecchia: aggiungo uno 0 davanti ai numeri
	                                }
	                            }
	                            else $targB_motor = "$numeri ";
                                
                                /*if($pro_progr==0 or $pro_progr==NULL)
                                {
                                    //Targa nuova
                                    $targB_motor="$numeri ";
                                }
                                else
                                {
                                    //Targa vecchia: aggiungo uno 0 davanti ai numeri
                                    $targB_motor="0$numeri";
                                }*/
                            }
                        }
                        else
                        {
                            $prov=substr($registro[Reg_Targa_Veicolo],0,2);
                            $targa=$registro[Reg_Targa_Veicolo];
                            $targ_motor=substr($registro[Reg_Targa_Veicolo],2);
                            $targB_motor="$targ_motor";
                        }
                    }
                    elseif($registro[Reg_Tipologia_Veicolo]=='ciclomotore')
                    {
                        $prov="  ";
                        //$targB_motor="$registro[Pre_Targa_Veicolo] ";
                        $targB_motor = $registro[Pre_Targa_Veicolo];
                    }
                    else
                    {
                        if($lung==7)
                        {
                            //Se sono lunghe 7 caratteri devo vedere se sono nuove (ok)
                            //o vecchie (aggiungo uno zero).
                            $prov=substr($registro[Reg_Targa_Veicolo],0,2);
                            $numeri=substr($registro[Reg_Targa_Veicolo],2);
                            $query="select Pro_Progr from provincia where Pro_Sigla='$prov'";
                            $pro_progr=single_answer_query($query);
                            
                            if ($registro[Reg_Tipologia_Veicolo] != 'motoveicolo')
                            {
                            	switch (Ultime2Lettere($numeri))
	                            {
	                            	case "TUTTELETTERE": //Targa nuova
	                            		$targa = $registro[Pre_Targa_Veicolo];
	                            		$targB_motor = "$numeri ";
	                            		break;
	                            	case "NUMERIELETTERE": //Targa vecchia: aggiungo uno 0 davanti ai numeri
	                            		$num = "0$numeri";
	                            		$targa = "$prov$num";
	                            		$targB_motor = "0$numeri";
	                            		break;
	                            }
                            }
                            else 
                            {
                            	$targa = $registro[Pre_Targa_Veicolo];
                            	$targB_motor = "$numeri ";
                            }
                            
                            /*if($pro_progr!=0 and $pro_progr!=NULL)
                            {
                                //Sono targhe vecchie
                                $num="0$numeri";
                                $targa="$prov$num";
                                $targB_motor="0$numeri";
                            }
                            else
                            {
                                //Sono targhe nuove
                                $targa=$registro[Reg_Targa_Veicolo];
                                $targB_motor="$numeri ";
                            }*/

                        }
                        else
                        {
                            $prov=substr($registro[Reg_Targa_Veicolo],0,2);
                            $targa=$registro[Reg_Targa_Veicolo];
                            $targ_motor=substr($registro[Reg_Targa_Veicolo],2);
                            $targB_motor="$targ_motor";
                        }
                    }
                }
                elseif($registro[Reg_Targa_Veicolo]!=NULL and $registro[Reg_Telaio_Veicolo]==NULL)
                {
                    $tipo_richiesta='T';
                    $lung=strlen($registro[Reg_Targa_Veicolo]);
                    if($registro[Reg_Tipologia_Veicolo]=='autovettura')
                    {
                        if($lung==7)
                        {
                            //Separo le prime 2 lettere
                            $prov=substr($registro[Reg_Targa_Veicolo],0,2);
                            $numeri=substr($registro[Reg_Targa_Veicolo],2);
                            if(is_numeric($numeri))
                            {
                                $num="0$numeri";
                                $targa="$prov$num";
                            }
                            else
                            {
                                $targa=$registro[Reg_Targa_Veicolo];
                            }
                            $dopo=substr($registro[Reg_Targa_Veicolo],5);
                            if(ereg("[[:alpha:]]",$dopo))
                            {
                                //Targa nuovo formato quindi non aggiungo zero
                                $targB_motor="$numeri ";
                            }
                            else
                            {
                                //Per la motorizzazione se la targa � vecchia devo inserire uno 0 tra la provincia
                                //e i numeri
                                $query="select Pro_Progr from provincia where Pro_Sigla='$prov'";
                                $pro_progr=single_answer_query($query);
                                
                                if ($registro[Reg_Tipologia_Veicolo] != 'motoveicolo')
	                            {
	                            	switch (Ultime2Lettere($numeri))
	                                {
	                                	case "TUTTELETTERE": $targB_motor = "$numeri "; break; //Targa nuova
	                                	case "NUMERIELETTERE": $targB_motor = "0$numeri"; break; //Targa vecchia: aggiungo uno 0 davanti ai numeri
	                                }
	                            }
	                            else $targB_motor = "$numeri ";
                                
                                /*if($pro_progr==0 or $pro_progr==NULL)
                                {
                                    //Targa nuova
                                    $targB_motor="$numeri ";
                                }
                                else
                                {
                                    //Targa vecchia: aggiungo uno 0 davanti ai numeri
                                    $targB_motor="0$numeri";
                                }*/
                            }
                        }
                        else
                        {
                            $prov=substr($registro[Reg_Targa_Veicolo],0,2);
                            $targa=$registro[Reg_Targa_Veicolo];
                            $targ_motor=substr($registro[Reg_Targa_Veicolo],2);
                            $targB_motor="$targ_motor";
                        }
                    }
                    elseif($registro[Reg_Tipologia_Veicolo]=='ciclomotore')
                    {
                        $prov="  ";
                        //$targB_motor="$registro[Pre_Targa_Veicolo] ";
                        $targB_motor = $registro[Pre_Targa_Veicolo];
                    }
                    else
                    {
                        if($lung==7)
                        {
                            //Se sono lunghe 7 caratteri devo vedere se sono nuove (ok)
                            //o vecchie (aggiungo uno zero).
                            $prov=substr($registro[Reg_Targa_Veicolo],0,2);
                            $numeri=substr($registro[Reg_Targa_Veicolo],2);
                            $query="select Pro_Progr from provincia where Pro_Sigla='$prov'";
                            $pro_progr=single_answer_query($query);
                            
                            if ($registro[Reg_Tipologia_Veicolo] != 'motoveicolo')
                            {
                            	switch (Ultime2Lettere($numeri))
	                            {
	                            	case "TUTTELETTERE": //Targa nuova
	                            		$targa = $registro[Pre_Targa_Veicolo];
	                            		$targB_motor = "$numeri ";
	                            		break;
	                            	case "NUMERIELETTERE": //Targa vecchia: aggiungo uno 0 davanti ai numeri
	                            		$num = "0$numeri";
	                            		$targa = "$prov$num";
	                            		$targB_motor = "0$numeri";
	                            		break;
	                            }
                            }
                            else 
                            {
                            	//$targa = $registro[Pre_Targa_Veicolo];
                            	$targa = $prov . "0" . $numeri;
                            	$targB_motor = "0" . $numeri;
                            }
                            
                            
                            /*if ($prov == "BT")
                            {
                                //Sono targhe nuove
                                $targa=$registro[Reg_Targa_Veicolo];
                                $targB_motor="$numeri ";
                            }
                            else if($pro_progr!=0 and $pro_progr!=NULL)
                            {
                                //Sono targhe vecchie
                                $num="0$numeri";
                                $targa="$prov$num";
                                $targB_motor="0$numeri";
                            }
                            else
                            {
                                //Sono targhe nuove
                                $targa=$registro[Reg_Targa_Veicolo];
                                $targB_motor="$numeri ";
                            }*/
                        }
                        else
                        {
                            $prov=substr($registro[Reg_Targa_Veicolo],0,2);
                            $targa=$registro[Reg_Targa_Veicolo];
                            $targ_motor=substr($registro[Reg_Targa_Veicolo],2);
                            $targB_motor="$targ_motor";
                        }
                    }
                }
                elseif($registro[Reg_Targa_Veicolo]==NULL and $registro[Reg_Telaio_Veicolo]!=NULL)
                {
                    $tipo_richiesta='L';
                    $targa=$registro[Reg_Telaio_Veicolo];
                }
                $fine_record="&";
                if($registro[Reg_Tipologia_Veicolo]=='autovettura' or $registro[Reg_Tipologia_Veicolo]=='autocarro' or $registro[Reg_Tipologia_Veicolo]=='bus')
                {
                    $tipo_veic='1';
                    $tipo_veic_motor='A';
                }
                elseif($registro[Reg_Tipologia_Veicolo]=='motoveicolo' or $registro[Reg_Tipologia_Veicolo]=='ciclomotore')
                {
                    $tipo_veic='4';
                    if($registro[Reg_Tipologia_Veicolo]=='ciclomotore')
                    {
                        $tipo_veic_motor='C';
                    }
                    else
                    {
                        $tipo_veic_motor='M';
                    }
                }
                elseif($registro[Reg_Tipologia_Veicolo]=='rimorchio' or $registro[Reg_Tipologia_Veicolo]=='altro')
                {
                    $tipo_veic='2';
                    $tipo_veic_motor='A';
                }
                if($cur_par->Par_Tipo_Richiesta_Dati=="MOTO")
                {
                    if($registro[Reg_Tipo]=='P')
                    {
                        $data_reg=$registro[Reg_Data_Preavviso];
                    }
                    elseif($registro[Reg_Tipo]=='A')
                    {
                        $data_reg=$registro[Reg_Data_Avviso];
                    }
                    elseif($registro[Reg_Tipo]=='V')
                    {
                        $data_reg=$registro[Reg_Data_Verbale];
                    }
                    $aa_data_reg=substr($data_reg,0,4);
                    $mm_data_reg=substr($data_reg,5,2);
                    $gg_data_reg=substr($data_reg,8,2);
                    $data_richiesta=date("dmY");
                    $data_avviso="$gg_data_reg$mm_data_reg$aa_data_reg";
                    $query="select Com_Prov from comune where Com_CC='$c'";
                    $com_prov=single_answer_query($query);
                    $pro_sigla = $cur_par->Par_Provincia_Richiedente; 
                    $com_nome = $cur_par->Par_Comune_Richiedente; 
                    $lung_com_nome=strlen($com_nome);
                    if($lung_com_nome>22)
                    {
                        $nome_comune=substr($com_nome,0,21);
                        $n_spazi=0;
                    }
                    elseif($lung_com_nome==22)
                    {
                        $nome_comune=$com_nome;
                        $n_spazi=0;
                    }
                    elseif($lung_com_nome<22)
                    {
                        $nome_comune="$com_nome";
                        $n_spazi=22-$lung_com_nome;
                    }
                    //Inserisco il numero del verbale come progressivo della tabela del cronologico
                    $numero_verbale=$registro[Reg_Progr];
                    $lung=strlen($numero_verbale);
                    
                    while (strlen($targB_motor) < 6)
                    	$targB_motor .= " ";

                    fput_margine_new($c,$marg,$fh,"$prov$tipo_veic_motor$targB_motor$data_richiesta      $data_avviso$numero_verbale");
                    stamp_spazi((6-$lung),0,$fh);
                    fput_margine_new($c,$marg,$fh,"  $pro_sigla$nome_comune");

                    for($ss=0;$ss<($n_spazi+19);$ss++)
                    {
                        fput_margine_new($c,$marg,$fh," ");
                    }
                }
                else
                {
                    fput_margine_new($c,$marg,$fh,"$reg_data+$tipo_richiesta+$tipo_veic+$targa;");
                }
                
                $numeroRighe ++;
                if ($numeroRighe != $num_res)
                    fput_margine_new($c,$marg,$fh,"\n");
            }
        }
        if($cur_par->Par_Tipo_Richiesta_Dati=="MOTO") 
        {				
        
			//***************************************************************** INIZIO CONESSIONE FTP CON LA MOTORIZZAZIONE **************************************************//		
			//exec ("C:/WINDOWS/System32/rasdial Motorizzazione 185450860 185450860"); //Conessione della linea ISDN		
		
			//********************************************************* Ricezione del file OK-SA ****************************************************************************//	
			$local_file = $percorso_file.$nome_semaforo;  //Nome del file locale 	
			$server_file = $nome_semaforo; //Nome del file del server ftp	
			$conn_id = ftp_connect($ftp_server); //Conessione con il server ftp						
			if($conn_id=='')
			{
				echo "ALERT1!!! conessione non riuscita con il server FTP della Motorizzazione, provare pi� tardi, altrimenti riprovare a riavviare il Server";	
				echo "<br>";
				//Chiudo la conessione ISDN con la Motorizzazione
				//exec ("C:/WINDOWS/System32/rasdial Motorizzazione /d");	
				exit;
			}	
			$login_result = ftp_login($conn_id, $ftp_user_name, $ftp_user_pass); //Login con utente e password		
			if($login_result=='')
			{
				echo "ALERT2!!! conessione non riuscita con il server FTP della Motorizzazione";	
				echo "<br>";
				//Chiudo la conessione ISDN con la Motorizzazione
				//exec ("C:/WINDOWS/System32/rasdial Motorizzazione /d");	
				exit;
			}
			
			if($_SESSION['username']=='emanueleb')
			{
				//echo $conn_id, ' --- ', $login_result;
				//echo "<br>";
				//exit;
			}
		
		//********************************************************* Invio del file OK-SA ****************************************************************************//		
		if (ftp_put($conn_id, $server_file, $local_file, FTP_BINARY)) //Scarico il file del server $server_file e lo salvo in locale con il nome $local_file
		{
			//echo "Invio con esito del file $local_file";	
			//echo "<br>";
			/*
			//Visualiza i file che della cartella 
			$contents = ftp_nlist($conn_id, ".");		
			// output $contents
			var_dump($contents);
			*/
		} 
		else 
		{
			echo "ATTENZIONE!!! E' stato un problema nell'invio del file $local_file";	
			echo "<br>";
			//Chiudo la conessione ISDN con la Motorizzazione
			//exec ("C:/WINDOWS/System32/rasdial Motorizzazione /d");
			exit;
		}	
		
		//********************************************************* Invio del file INP-SA ****************************************************************************//	
		$local_file = $percorso_file.$nome_inp; //Nome del file locale 	
		$server_file = $nome_inp; //Nome del file del server ftp
		
		if (ftp_put($conn_id, $server_file, $local_file, FTP_BINARY)) //Scarico il file del server $server_file e lo salvo in locale con il nome $local_file
		{
			//echo "Invio con esito del file $local_file";	
			//echo "<br>";
			/*
			//Visualiza i file che della cartella 
			$contents = ftp_nlist($conn_id, ".");		
			// output $contents
			var_dump($contents);
			*/
		} 
		else 
		{
			echo "ATTENZIONE!!! E' stato un problema nell'invio del file $local_file";	
			echo "<br>";
			//Chiudo la conessione ISDN con la Motorizzazione
			//exec ("C:/WINDOWS/System32/rasdial Motorizzazione /d");
			exit;
		}	
		
		//Chiudo la conessione del FTP con la Motorizzazione
		ftp_close($conn_id);
		//Chiudo la conessione ISDN con la Motorizzazione
		//exec ("C:/WINDOWS/System32/rasdial Motorizzazione /d");
		//***************************************************************** FINE CONESSIONE FTP CON LA MOTORIZZAZIONE ***************************************************//		
				
		
			copy ($percorso_file.$nome_inp,$percorso_file.'/backup/'.$nome_inp."_".date("dmy")."_".$c); 
		  
			//creo un file di controllo per sapere quale comune ha inviato per ultimo una 
			//richiesta 
			$fh_ftp_control_inp=fopen("$DOCUMENT_ROOT/trasf_ministero/trasm_ftp_control_inp","w");
			fputs($fh_ftp_control_inp,$pre_frase.$nome_outp.' '.date("d/m/y").' '.$c.' '.get_Com_Nome($c)); 
			fclose($fh_ftp_control_inp);
             
        }
        else
        {
	       // funzione per esecuzione PCCSA
	       chdir('/www/html/PCCSAClient/');
	       $str_pccsa="./StartPccsa.sh";
	       system($str_pccsa); 
        }
        //esec_stampa(2,$nome_file_app,$tipo_stampante);
    }
    else
    {  
        
        $query="select * from $db_com_cc where 1";
        $res=safe_query($query);
        $num_res=mysql_num_rows($res);
        if($num_res>0)
        {  
            echo <<< END
            <table class="minima" width="600" cellpadding="0" cellspacing="0" border="0">
END;
            while($dati_anci=mysql_fetch_array($res,MYSQL_ASSOC))
            {
                $query="select * from registro_cronologico_cds where Reg_Progr='$dati_anci[Anc_Progr_Registro]'";
                $ris=safe_query($query);
                $registro=mysql_fetch_array($ris);
                if($registro[Reg_Tipo]=='P')
                {
                    $nome="Preavviso";
                    $anno=substr($registro[Reg_Anno_Preavviso],2);
                    $numero="$registro[Reg_Numero_Preavviso]/$registro[Reg_Rif_Numero_Preavviso]/$anno";
                }
                elseif($registro[Reg_Tipo]=='A')
                {
                    $nome="Avviso";
                    $anno=substr($registro[Reg_Anno_Avviso],2);
                    $numero="$registro[Reg_Numero_Avviso]/$registro[Reg_Rif_Numero_Avviso]/$anno";
                }
                elseif($registro[Reg_Tipo]=='V')
                {
                    $nome="Verbale";
                    $anno=substr($registro[Reg_Anno_Verbale],2);
                    $numero="$registro[Reg_Numero_Verbale]/$registro[Reg_Rif_Numero_Verbale]/$anno";
                }
                $tipo_veicolo=strtoupper($registro[Reg_Tipologia_Veicolo]);
                echo <<< END
                <tr>
                <td width="80">
                <b>$nome</b>
                </td>
                <td width="200">
                N� <b>$numero</b>
                </td>
                <td width="320">
                <b>$tipo_veicolo</b>&nbsp;&nbsp;
                Tg. <b>$registro[Reg_Targa_Veicolo]</b>&nbsp;
                Tel.<b>$registro[Reg_Telaio_Veicolo]</b>
                </td>
                </tr>
                <tr>
                <td width="600" colspan="3">
                <hr>
                </td>
                </tr> 
END;
            }
            echo"</table>";
        }
    }    
}
//echo"<script> self.close(); </script>";

alert ("Richiesta terminata");
echo "<script> self.close(); </script>";

function Ultime2Lettere ($targa)
{
	$len = strlen($targa);
	$penultima = substr ($targa, $len-2, 1);
	$ultima = substr ($targa, $len-1, 1);
	$tuttelettere = true;
	if ($penultima >= '0' && $penultima <= '9') $tuttelettere = false;
	if ($ultima >= '0' && $ultima <= '9') $tuttelettere = false;
	
	if ($tuttelettere == true) return "TUTTELETTERE";
	else return "NUMERIELETTERE";
}


?>
</form>
</body>
</html>
