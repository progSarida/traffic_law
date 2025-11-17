<?php
session_start();


if($_POST){



	$strPaypal = '';
	$strPayment = '';
	$strSpeed = '';
	$strLight = '';
	//$conn = mysqli_connect("localhost","root","","polizia_municipale");
    $conn = mysqli_connect("62.149.150.179","Sql627048","41c608f5","Sql627048_1");

	$cv = (isset($_POST['c'])) ? $_POST['c'] : null;//cod catastale
	$tv = (isset($_POST['tv'])) ? $_POST['tv'] : null;//lettera cod
	$nv = (isset($_POST['nv'])) ? $_POST['nv'] : null;//numero cod
	$yv = (isset($_POST['yv'])) ? $_POST['yv'] : null;//anno cod
	$dvd = (isset($_POST['dvd'])) ? $_POST['dvd'] : null;//giorni
	$dvm = (isset($_POST['dvm'])) ? $_POST['dvm'] : null;//mesi
	$dvy = (isset($_POST['dvy'])) ? $_POST['dvy'] : null;//anno



	$hv = (isset($_POST['hv'])) ? $_POST['hv'] : null;//ore
	$mv = (isset($_POST['mv'])) ? $_POST['mv'] : null;//minuti
	$pv = (isset($_POST['pv'])) ? strtoupper($_POST['pv']) : null;//targa



	$str_PrintButton ='
	<form name="prn" action="prn.php" method="post">
	<input type="hidden" name="c" value="'.$cv.'" />
	<input type="hidden" name="tv" value="'.$tv.'" />
	<input type="hidden" name="nv" value="'.$nv.'" />
	<input type="hidden" name="yv" value="'.$yv.'" />
	<input type="hidden" name="dvd" value="'.$dvd.'" />
	<input type="hidden" name="dvm" value="'.$dvm.'" />
	<input type="hidden" name="dvy" value="'.$dvy.'" />
	
	<input type="hidden" name="hv" value="'.$hv.'" />
	<input type="hidden" name="mv" value="'.$mv.'" />
	<input type="hidden" name="pv" value="'.$pv.'" />
	<input type="submit" value="PRINT" /><br />
	<span style="color:red;font-weight:bold">Immagini solo per visione dell\'infrazione.</span><br />
	<span style="color:red;font-weight:bold">Per ricorsi richiedere copia immagini all\'Ufficio Polizia Municipale competente.</span>
	
	
	
	</form>
	';


	$query = "SELECT * FROM comune_gestito_dettagli WHERE Com_CC='$cv'";
	$rs= mysqli_query($conn, $query);

	$row = mysqli_fetch_array($rs);

	$appeal_city = $row['Com_Nome_Comune'];
	$appeal_province = $row['Com_Prov_Comune'];
	$appeal_prefect = $row['Com_Nome_Prefetto'];
	$appeal_prefect_province = $row['Com_Prov_Prefetto'];
	$appeal_police = $row['Com_Nome_PM'];
	$appeal_police_province = $row['Com_Prov_PM'];
	$appeal_judge = $row['Com_Nome_Giudice_Pace'];
	$appeal_judge_province = $row['Com_Prov_Giudice_Pace'];

	$payment_owner = $row['Com_CC_Intestato'];
	$payment_number = $row['Com_CC_Num'];
	$payment_iban = $row['Com_CC_Iban'];
	$payment_swift = $row['Com_CC_Swift'];

	$payment_paypal = $row['Com_Paypal'];
	$payment_out = $row['Com_Pagamento'];
	$payment_link = $row['Link_Pagamento'];

	$aPaypalLanguage= array(
		'ita'=>'IT',
		'eng'=>'GB',
		'ger'=>'DE',
		'fre'=>'FR',
		'spa'=>'ES',
	);


	$aDocumentTitle = array(
		'ita'=>'DOCUMENTI VERBALE',
		'eng'=>'FINE DOCUMENTATION',
		'ger'=>'FINE DOKUMENTATION',
		'fre'=>'DOCUMENTATION FINE',
		'spa'=>'DOCUMENTACIÓN FINA',
	);
	$aInfoTitle = array(
		'ita'=>'DOCUMENTI VERBALE',
		'eng'=>'FINE DETAILS',
		'ger'=>'FINE DETAILS',
		'fre'=>'DETAILS FINE',
		'spa'=>'LOS DETALLES FINOS',
	);





	$aAppealTitle = array(
		'ita'=>'MODALITA DI RICORSO',
		'eng'=>'RULES OF OPPOSITION',
		'ger'=>'EINSPRUCH',
		'fre'=>'PROCEDURE  D\'OPPOSITION',
		'spa'=>'PROCEDIMIENTO DE OPOSICIÓN',
	);



	$aAppeal = array(
		'ita'=>
			'
			<p>Il ricorso può essere presentato entro 60 giorni  dalla notifica del verbale.</p>
			<p>L\'impugnazione può essere inviata al Prefetto del distretto in cui la violazione della legge  ha avuto luogo 
			<span class="appeal">Prefetto di '.$appeal_prefect.' ('.$appeal_prefect_province.')</span> anche tramite invio del ricorso  
			al Distretto di suddetta stazione di <span class="appeal">Polizia  Municipale di '.$appeal_police.' ('.$appeal_police_province.')</span>.</p>
			<p>Se il Prefetto respinge il ricorso emette un  ingiunzione  di pagamento pari al doppio  dell\'importo della somma di questo verbale.<br>
				Entro 60 giorni dalla notifica del  verbale può essere presentato ricorso anche  direttamente al 
				<span class="appeal">Giudice di pace del distretto  in cui la violazione della legge ha avuto luogo '.$appeal_judge.' ('.$appeal_judge_province.')</span>. </p>
			<p>Le predette  impugnazioni si presentano  ai sensi degli  art. 203 e segg. del codice della strada (Cost. C. inviato. N. 255 20/06/1994 e  n. 311 del 06/07/1994). </p>
			',
		'eng'=>
		'
			<p>The appeal can be  lodged within 60 days from the claim or notification term of the lawbreaking.  
			The appeal should be sent to the local prefect of police of the district where 
			the lawbreaking took place and should be filed to the above mentioned 
			<span class="appeal">District Police Station'.$appeal_police.' ('.$appeal_police_province.')</span>.</p>
			<p>If the prefect of  police believes the tests to be sound, he will issue an order requiring the 
			payment equal to the double amount of the sum of this court record to be made<span class="appeal">'.$appeal_prefect.' ('.$appeal_prefect_province.')</span>.</p>
			Within 60 days from the  claim or notification terms of the lawbreaking an appeal against the sentence  can be lodge and directly given to the Justice of the Peace in the district <span class="appeal">'.$appeal_judge.' ('.$appeal_judge_province.')</span>. </p>
			<p>where the lawbreaking  took place in compliance with art. 205 of the rules of the road (Cost. C. sent.  N. 255 20/06/1994 and n. 311 06/07/1994). The claim against the sentence has to  be lodge if admissible in the office of the court within the afore mentioned  term (Superior C. sent.-sez. III, n. 10766 04/06-29/09/1999). Should no appeal  be filed to the prefect and no payment take place within the afore mentioned  terms, this document will represent a writ of execution for the compulsory  payment equal to 50% of the maximum penalty amount and process costs.</p>
	
		',
		'ger'=>
			'
			<p>Wenn die  Strafe innerhalb der vorgeschriebenen Frist (60 Tage ab Zustellung dieses  Berichtes) 
			in ihrer vorgeschriebenen Mindestbetrag nicht bezahlt wird und  keinen Einspruch vor der für den Ort des Verstoßes zuständigen Behörde 
			(im  Selbstschutzweg bei der <span class="appeal">Gemeinde von '.$appeal_police.' ('.$appeal_police_province.')</span>.</p>
			<p>oder bei der <span class="appeal">Präfektur von '.$appeal_prefect.' ('.$appeal_prefect_province.')</span>.</p>
			<p>oder bei dem <span class="appeal">Friedensrichter von '.$appeal_judge.' ('.$appeal_judge_province.')</span>.</p>
			<p>erhoben  wird, tritt das Protokoll in Kraft und die Strafe  wird die Hälfte des gesetzlich vorgeschriebenen Höchstbetrages (gemäß Artikel 202. StVO)  berechnet.</p>
			',
		'fre'=>
		'
		<p > Avant 60 jours à partir de la contestation ou de  la notification de la violation, l\'intéressé peut faire recours . Il doit etre  adressé au Préfet du lieu où la violation s\'est déroulée
		<span class="appeal">'.$appeal_prefect.' ('.$appeal_prefect_province.')</span> et doit être présenté au «Comando Polizia  Municipale» de
		<span class="appeal">'.$appeal_police.' ('.$appeal_police_province.')</span> déjà cité<br>
		If the prefect of  police believes the tests to be sound, he will issue an order requiring the  payment equal to the double amount of the sum of this court record to be made<br>
		Si le Préfet, retiendra la constatation fondée,  il émettra une ordonnance intimant le paiement d’une somme non inférieure au  double de celle indiquée sur le procés-verbal présent. Avaint le meme délai de 60  jours à partir de la contestation ou notification de la violation l\'intéressé  peut toutefois faire opposition directement   au juge de Paix du lieu ou la violation s\'est déroulée <span
		class="appeal">'.$appeal_judge.' ('.$appeal_judge_province.')</span><br>
		aux  termes de l\'art. 205 du Code de la   Route (arr. C. Const. n. 255 du 20/06/1994 et n. 311 du  06/07/1994). L\'opposition doit être déposée, sous peine d’inadmissibilité, au Greffe  du Juge, avant la fin du délai mentionnè ci-dessus (arr. C Cass.-sect. III, n:  10768 du 4-6/29_9/1999). Si entre les délais indiqués aucun recours n\'a été  présenté au Préfet, ni aucune opposition n\'a été présentée  au Juge de Paix et le paiement n\'a pas été  fait, l\'acte présent constituera le titre exécutif pour le recouvrement forcé  de la somme égale à la moitié du maximum de la sanction qui a été  appliquée  et pour les frais de procédure.</p>
			',
		'spa'=>
		'
		<p>Antes de  60 días de protesta o de aviso de la violación, la persona puede apelar. Debe  ser dirigida al Prefecto del lugar donde tuvo lugar la violación, y se debe  presentar a la &quot;Polizia Municipale Commando <span class="appeal">'.$appeal_police.' ('.$appeal_police_province.')</span>. <br>
		Si el  Prefecto <span class="appeal">'.$appeal_prefect.' ('.$appeal_prefect_province.')</span> conservará  la conclusión basada, emitirá una orden que exija el pago de una suma no  inferior al doble del indicado en el disco ahora. Avaint el mismo período de 60  días desde la notificación de la controversia o la infracción, sin embargo, la  persona puede estar en oposición directa a la Justicia de Paz del lugar  donde tuvo lugar la violación <span class="appeal">'.$appeal_judge.' ('.$appeal_judge_province.')</span><br>
		en virtud del art. 205 del Código de la Carretera (arr. C. Const. N. 255 del 20.06.1994 y n. 311 de  06/07/1994). La oposición deberá presentarse, bajo pena de inhabilitación, la Secretaría de Justicia,  antes de que finalice el período mencionado anteriormente (arr. Cass C-secc.  III, N º 10768 de 4-6/29_9/1999). Si entre el tiempo indicado no cabe recurso  ha sido presentado al Prefecto, y no hay objeción se ha presentado a la Justicia de Paz y el pago  no se ha hecho, este acto será la capacidad ejecutiva para la ejecución de suma  igual a la mitad de la pena máxima aplicada fue así como los gastos.</p>
		',
	);





	$aPaymentTitle = array(
		'ita'=>'MODALITA DI PAGAMENTO',
		'eng'=>'METHOD OF PAYMENT',
		'ger'=>'REGELN DER ZAHLUNGS',
		'fre'=>'MODE DE PAIEMENT',
		'spa'=>'FORMA DE PAGO',
	);

	$aPayment = array(
		'ita'=>
			'<h2>(Non è possibile pagare in contanti o tramite assegno)</h2>
			<p>La sanzione indicata è comprensiva delle spese di notifica e di ricerca dei dati</p>
			',
		'eng'=>
			'<h2>(Not possible to pay in cash or by check)</h2>
			<p>The type of sanction is inclusive of service costs and data search</p>
			',
		'ger'=>
			'<h2>(Nicht möglich in bar oder per Scheck bezahlen)</h2>
			<p>Die Art der Sanktion ist inklusive Servicekosten und Datensuche </p>
			',
		'fre'=>
			'<h2>(Pas possible de payer en espèces ou par chèque)</h2>
			<p>Le type de sanction est y compris les frais de service et recherche de données</p>
			',
		'spa'=>
			'<h2>(No es posible pagar en efectivo o con cheque)</h2>
			<p>El tipo de sanción es inclusivo de los costes de servicio y búsqueda de datos</p>
			',
	);




	$aPaymentPaypalInfo = array(
		'ita'=>'<h2>La sanzione indicata è comprensiva delle spese di notifica, di ricerca dei dati e di incasso.</h2>',
		'eng'=>'<h2>The sanction referred to also including the costs of service, data search and collection.</h2>',
		'ger'=>'<h2>Die Sanktion bezeichnet auch einschließlich der Kosten für Service, Datensuche und Sammlung.</h2>',
		'fre'=>'<h2>La sanction appelée incluant également les coûts du service, la recherche de données et la collecte.</h2>',
		'spa'=>'<h2>La sanción que se refiere a la inclusión de los costes de servicio, la búsqueda y recopilación de datos también.</h2>',
	);




    $aPaymentPaypal1 = array(
        'ita'=>'<p>Pagamento Ridotto</p> 
            <p>(entro 5 giorni dalla Data di Notifica riportata sul Verbale)</p>
            <p style="color: red;font-weight: bold;">MODALITA\' DI PAGAMENTO DISABILITATA</p>',
        'eng'=>'<p>Reduced payment</p> 
            <p>(Within 5 days of the date stated on the verbal notification)</p>
            <p style="color: red;font-weight: bold;">PAYMENT METHOD DISABLED</p>',
        'ger'=>'<p>Reduzierte Zahlung</p> 
            <p>(Innerhalb von 5 Tagen nach dem Datum, an dem die mündliche Benachrichtigung angegeben)</p>
            <p style="color: red;font-weight: bold;">ZAHLUNGSMETHODE DEAKTIVIERT</p>',
        'fre'=>'<p>Paiement réduit</p> 
            <p>(entro 5 giorni dalla Data di Notifica riportata sul Verbale)</p>
            <p style="color: red;font-weight: bold;">MOYEN DE PAIEMENT DÉSACTIVÉ</p>',
        'spa'=>'<p>Pago reducido</p> 
            <p>(Dans les 5 jours suivant la date indiquée sur la notification verbale)</p>
            <p style="color: red;font-weight: bold;">MÉTODO DE PAGO DESHABILITADO</p>',
    );
    $aPaymentPaypal2 = array(
        'ita'=>'<p>Pagamento al Minimo Edittale</p> 
            <p>(entro 60 giorni dalla Data di Notifica riportata sul Verbale)</p>
            <p style="color: red;font-weight: bold;">MODALITA\' DI PAGAMENTO DISABILITATA</p>',
        'eng'=>'<p>Payment to the minimum prescribed by law</p> 
            <p>(Within 60 days of the date stated on the verbal notification)</p>
            <p style="color: red;font-weight: bold;">PAYMENT METHOD DISABLED</p>',
        'ger'=>'<p>Die Zahlung auf das Minimum gesetzlich vorgeschrieben</p> 
            <p>(Innerhalb von 60 Tagen nach dem Datum, an dem die mündliche Benachrichtigung angegeben)</p>
            <p style="color: red;font-weight: bold;">ZAHLUNGSMETHODE DEAKTIVIERT</p>',
        'fre'=>'<p>Paiement au minimum prescrit par la loi</p> 
            <p>(Dans les 60 jours suivant la date indiquée sur la notification verbale)</p>
            <p style="color: red;font-weight: bold;">MOYEN DE PAIEMENT DÉSACTIVÉ</p>',
        'spa'=>'<p>Pago al mínimo establecido por la ley</p> 
            <p>(Dentro de los 60 días siguientes a la fecha indicada en la notificación verbal)</p>
            <p style="color: red;font-weight: bold;">MÉTODO DE PAGO DESHABILITADO</p>',
    );
    $aPaymentPaypal3 = array(
        'ita'=>'<p>Pagamento alla metà del Massimo Edittale</p> 
            <p>(dopo 60 giorni dalla Data di Notifica riportata sul Verbale)</p>
            <p style="color: red;font-weight: bold;">MODALITA\' DI PAGAMENTO DISABILITATA</p>',
        'eng'=>'<p>Payment to the standard maximum Half</p> 
            <p>(After 60 days of the date stated on the verbal notification)</p>
            <p style="color: red;font-weight: bold;">PAYMENT METHOD DISABLED</p>',
        'ger'=>'<p>Die Zahlung an den Standard maximal Halb</p> 
            <p>(Nach 60 Tagen nach dem Datum, an dem die mündliche Benachrichtigung angegeben)</p>
            <p style="color: red;font-weight: bold;">ZAHLUNGSMETHODE DEAKTIVIERT</p>',
        'fre'=>'<p>Paiement à la moitié standard maximale</p> 
            <p>(Après 60 jours de la date indiquée sur la notification verbale)</p>
            <p style="color: red;font-weight: bold;">MOYEN DE PAIEMENT DÉSACTIVÉ</p>',
        'spa'=>'<p>Pago a la mitad del máximo nivel</p> 
            <p>(Después de 60 días de la fecha indicada en la notificación verbal)</p>
            <p style="color: red;font-weight: bold;">MÉTODO DE PAGO DESHABILITADO</p>',
    );













	$aPaymentOUT= array(
		'ita'=>'Può essere inoltre pagata on line facendo click <a href="'.$payment_link.'" target="_blank">qui</a>',
		'eng'=>'It can also be paid online by clicking <a href="'.$payment_link.'" target="_blank">here</a>',
		'ger'=>'Es kann auch online bezahlt werden, indem Sie auf <a href="'.$payment_link.'" target="_blank">hier</a>',
		'fre'=>'Il peut également être payé en ligne en cliquant <a href="'.$payment_link.'" target="_blank">ici</a>',
		'spa'=>'También se puede pagar en línea haciendo clic <a href="'.$payment_link.'" target="_blank">aqui</a>',
	);



	$aContactTitle = array(
		'ita'=>'CONTATTACI',
		'eng'=>'CONTACT US',
		'ger'=>'KONTAKT',
		'fre'=>'CONTACTEZ-NOUS',
		'spa'=>'CONTÁCTENOS',
	);

	$aContact = array(
		'ita'=>'Clicca <a href="#/Contact" id="more_info">QUI</a> per scriverci in merito a questo verbale.',
		'eng'=>'Click <a href="#/Contact" id="more_info">HERE</a> to write about this report.',
		'ger'=>'Klicken Sie <a href="#/Contact" id="more_info">HIER</a>, um diesen Bericht zu schreiben.',
		'fre'=>'Cliquez <a href="#/Contact" id="more_info">ICI</a> pour écrire au sujet de ce rapport.',
		'spa'=>'Haga clic <a href="#/Contact" id="more_info">AQUÍ</a> para escribir sobre este informe.',
	);



	$aNotFound = array(
		'ita'=>'Verbale non trovato. Controllare i dati inseriti.',
		'eng'=>'Verbal not found. Check your details.',
		'ger'=>'Verbal nicht gefunden. Überprüfen Sie Ihre Angaben.',
		'fre'=>'Verbal not found. Vérifiez vos informations.',
		'spa'=>'Verbal no encontrado. Ver sus detalles.',
	);


	if(strlen($hv)==1) $hv = '0'.$hv;
	if(strlen($mv)==1) $mv = '0'.$mv;

    $v_t= $hv.":".$mv;

/*
	$IP = ($tv=="ES"||$dvy==2019) ? "http://62.94.231.188/traffic_law/" : "http://www.gitco.it/gitco/gen/";



	$IP_DOC = ($tv=="ES"||$dvy==2019) ? "http://62.94.231.188/traffic_law/doc/" : "http://www.gitco.it/fotocds/";
	$IP_PH = ($tv=="ES") ? "http://62.94.231.187:8080/FotoTargheEstere/" : "http://www.gitco.it/fotocds/";


*/

    if($cv=='D711')
    {
        /*$IP = "https://gitcocoll.ovunque-si.it/formigine/";
        $IP_DOC =  "https://gitcocoll.ovunque-si.it/formigine/doc";
        $IP_PH =  "https://gitcocoll.ovunque-si.it/formigine/doc/";*/
        $IP = "https://formigine.ovunque-si.it/traffic_law/";
        $IP_DOC =  "https://formigine.ovunque-si.it/traffic_law/doc";
        $IP_PH =  "https://formigine.ovunque-si.it/traffic_law/doc/";
    }
    else {
        $IP = "https://gitco.ovunque-si.it/traffic_law/";
        $IP_DOC = "https://gitco.ovunque-si.it/traffic_law/doc";
        $IP_PH =  "https://gitco.ovunque-si.it/traffic_law/doc/";
    }


	if(strlen($dvd)==1) $dvd = '0'.$dvd;
	if(strlen($dvm)==1) $dvm = '0'.$dvm;



	$v_c= $cv;
	$v_n= $nv;
	$v_y= $yv;
	$v_d=$dvy."-".$dvm."-".$dvd;
	$v_p=$pv;





	$url = $IP."demon_query.php?v_c=$v_c&v_n=$v_n&v_y=$v_y&v_d=$v_d&v_t=$v_t&v_p=$v_p";
    //echo $url;
	$ch = curl_init();

	curl_setopt($ch, CURLOPT_URL, $url);


	curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)" );
	curl_setopt($ch, CURLOPT_HTTPHEADER, array("Accept-Language: it-it,en") ); curl_setopt($ch, CURLOPT_TIMEOUT, 10);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION,1);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
	$result = curl_exec($ch);
	$error = curl_error($ch);


	curl_close($ch);

    //echo htmlspecialchars($result);
	//die;
	$aPage = explode('<result>', $result);
    //var_dump($aPage);
    //echo "<br><br>".$result;
    //die;

	if($result) {
        if ($aPage[1] == 1) {
            $id_div = 'content_query';
            $content = '<div class="span12 content">';

            $aDocument = explode('<document>', $result);
            $Documents = str_replace('Noleggio', 'Fotogramma', $aDocument[1]);
            $Documents = str_replace('VerbaleOriginario', 'Fotogramma', $Documents);
            $Documents = str_replace('Fotogramma**', '', $Documents);

            $aImages = explode('<image>', $result);

            if (trim(strlen($Documents)) > 0 || trim(strlen($aImages[1])) > 0) {
                $aDocument = explode('**', $Documents);
                $aImage = explode('**', isset($aImages[1]) ? $aImages[1] : "");
                $content .=
                    '<div class="row-fluid">
                    <div class="menu">' . $aDocumentTitle[$_SESSION['lan']] . '</div>
					<div class="span12">
						<div class="row-fluid">
							<div class="row-fluid" style="min-height:300px;">';
                if (trim(strlen($Documents)) > 0) {
                    for ($i = 0; $i < count($aDocument); $i++) {
                        if ($aDocument[$i] != "") {
                            if (pathinfo($aDocument[$i], PATHINFO_EXTENSION) == "pdf") {
                                $content .= '
                        <a href="#" onclick="window.open(\'' . $IP_DOC . '/' . $aDocument[$i] . '\');">
                        CLICCA QUI PER VISUALIZZARE IL PDF
                        </a>';
                            } else if (pathinfo($aDocument[$i], PATHINFO_EXTENSION) == "jpg") {

                               /* if($cv=='D711'){
                                    $content .= '
                       
                        <div style="width:300px;height:250px;"><p style="margin-top: 120px;"><a href="#" onclick="window.open(\'' . $IP_DOC . '/' . $aDocument[$i] . '\');">
                        CLICCA QUI PER VISUALIZZARE L\'IMMAGINE
                        </a></p></div>';
                                }
                                else{ */
                                    $content .= '
                       
                        <a href="#" onclick="window.open(\'' . $IP_DOC . '/' . $aDocument[$i] . '\');">
                        <img title="0" src="' . $IP_DOC . '/' . $aDocument[$i] . '" style="width:300px;height:250px;float:left;" />
                        </a>';
                           //     }

                            } else if (pathinfo($aDocument[$i], PATHINFO_EXTENSION) == "mp4") {
                                $content .= '
                            <video width="300" height="250" style="float:right;margin-right:6%;" controls>
                              <source src="' . $IP_DOC . '/' . $aDocument[$i] . '" type="video/mp4">
                            </video>
                            ';
                            }
                        }


                    }
                }


                if (isset($aImages[1]))
                    if (trim(strlen($aImages[1])) > 0) {
                        for ($i = 0; $i < count($aImage); $i++) {
                            $content .= '
                        <a href="#" title="1" onclick="window.open(\'' . $IP_PH . $v_c . '/' . $v_d . '/' . $aImage[$i] . '\');">
                        <img src="' . $IP_PH . $v_c . '/' . $v_d . '/' . $aImage[$i] . '" style="width:300px;height:250px" />
                        </a>';

                        }
                    }
                $content .=
                    '</div>
								' . $str_PrintButton . '
						</div>
					</div>
				</div>';
            }


            $aSpeedLimit = array(
                'ita' => 'Limite di velocità',
                'eng' => 'Speed limit',
                'ger' => 'Geschwindigkeitsbegrenzung ',
                'fre' => 'Limitation de vitesse',
                'spa' => 'Límite de velocidad',
            );
            $aSpeed = array(
                'ita' => 'Velocità effettiva',
                'eng' => 'Actual speed',
                'ger' => 'Ist-Geschwindigkeit',
                'fre' => 'La vitesse réelle',
                'spa' => 'Velocidad real',
            );

            $aTrafficLight = array(
                'ita' => 'Tempo dal rosso: foto (prima/seconda)',
                'eng' => 'Time red: photos (first / second)',
                'ger' => 'Zeit rot: Fotos (erste / Sekunde)',
                'fre' => 'Temps rouge: photos (première / seconde)',
                'spa' => 'Rojo tiempo: fotos (primer / segundo)',
            );


            if (!strpos($result, "speedlimit") === False) {
                $strSpeed =
                    '<div class="row-fluid" style="min-height:80px;">' .
                    $aSpeedLimit[$_SESSION['lan']]
                    . '</div>
						<div class="row-fluid" style="min-height:80px;">' .
                    $aSpeed[$_SESSION['lan']]
                    . '</div>
						';
            }


            if (!strpos($result, "tlight1") === False) {
                $strLight =
                    '<div class="row-fluid" style="min-height:80px;">' .
                    $aTrafficLight[$_SESSION['lan']]
                    . '</div>
						';
            }


            $aFineDescription = array(
                'ita' => '
			<div class="row-fluid" style="min-height:80px;">
				Verbale
			</div>
			<div class="row-fluid" style="min-height:80px;">
				Data
			</div>
			<div class="row-fluid" style="min-height:80px;">
				Ora
			</div>
			<div class="row-fluid" style="min-height:80px;">
				Targa
			</div>
			<div class="row-fluid" style="min-height:80px;">
				Citta
			</div>
			<div class="row-fluid" style="min-height:80px;">
				Localita
			</div>
			<div class="row-fluid" style="min-height:80px;">
				Articolo violato
			</div>' .
                    $strSpeed . $strLight
            ,
                'eng' => '
			<div class="row-fluid" style="min-height:80px;">
				Fine
			</div>
			<div class="row-fluid" style="min-height:80px;">
				Date
			</div>
			<div class="row-fluid" style="min-height:80px;">
				Time
			</div>
			<div class="row-fluid" style="min-height:80px;">
				Plate
			</div>
			<div class="row-fluid" style="min-height:80px;">
				City
			</div>
			<div class="row-fluid" style="min-height:80px;">
				Resort
			</div>
			<div class="row-fluid" style="min-height:80px;">
				violated Article
			</div>' .
                    $strSpeed . $strLight
            ,
                'ger' => '
			<div class="row-fluid" style="min-height:80px;">
				Fein
			</div>
			<div class="row-fluid" style="min-height:80px;">
				Datum
			</div>
			<div class="row-fluid" style="min-height:80px;">
				Zeit
			</div>
			<div class="row-fluid" style="min-height:80px;">
				Kennzeichen
			</div>
			<div class="row-fluid" style="min-height:80px;">
				Stadt
			</div>
			<div class="row-fluid" style="min-height:80px;">
				Urlaubsort
			</div>
			<div class="row-fluid" style="min-height:80px;">
				Gegen Artikel
			</div>' .
                    $strSpeed . $strLight
            ,
                'fre' => '
			<div class="row-fluid" style="min-height:80px;">
				Amende
			</div>
			<div class="row-fluid" style="min-height:80px;">
				Date
			</div>
			<div class="row-fluid" style="min-height:80px;">
				Temps
			</div>
			<div class="row-fluid" style="min-height:80px;">
				Plaque d\'immatriculation
			</div>
			<div class="row-fluid" style="min-height:80px;">
				Ville
			</div>
			<div class="row-fluid" style="min-height:80px;">
				Recours
			</div>
			<div class="row-fluid" style="min-height:80px;">
				A violé l\'article
			</div>' .
                    $strSpeed . $strLight
            ,
                'spa' => '
			<div class="row-fluid" style="min-height:80px;">
				Multa
			</div>
			<div class="row-fluid" style="min-height:80px;">
				Fecha
			</div>
			<div class="row-fluid" style="min-height:80px;">
				Tiempo
			</div>
			<div class="row-fluid" style="min-height:80px;">
				Placa
			</div>
			<div class="row-fluid" style="min-height:80px;">
				Ciudad
			</div>
			<div class="row-fluid" style="min-height:80px;">
				Recurso
			</div>
			<div class="row-fluid" style="min-height:80px;">
				Artículo violado
			</div>' .
                    $strSpeed . $strLight
            ,
            );


            $content .=
                '<div class="span12 content">
				<div class="row-fluid">
					<div class="span5">
						  <div class="menu">' . $aInfoTitle[$_SESSION['lan']] . '</div>
					</div>	
					<div class="span7">
						  <div class="menu">' . $aPaymentTitle[$_SESSION['lan']] . '</div>
					</div>

				<div class="row-fluid">
					<div class="span2">
						<div class="row-fluid">
							' . $aFineDescription[$_SESSION['lan']] . '
						</div>
					</div>';


            $aId = explode('<id>', $result);
            $aDate = explode('<date>', $result);
            $aTime = explode('<time>', $result);
            $aPlate = explode('<plate>', $result);
            $aCity = explode('<city>', $result);
            $aLocation = explode('<location>', $result);
            $aArticle = explode('<article>', $result);
            $aParagraph = explode('<paragraph>', $result);

            $aAmount1 = explode('<amount1>', $result);
            $aAmount2 = explode('<amount2>', $result);
            $aAmount3 = explode('<amount3>', $result);


            if ($aAmount2[1] == "0.00") {
                $aPaymentPaypal1 = array(
                    'ita' => '<p>Pagamento </p> 
				<p></p>',
                    'eng' => '<p>Payment</p> 
				<p></p>',
                    'ger' => '<p>Zahlung</p> 
				<p></p>',
                    'fre' => '<p>Paiement</p> 
				<p></p>',
                    'spa' => '<p>Pago</p> 
				<p></p>',
                );
            }

            $AbilitaPagamenti = false;

            if(!$AbilitaPagamenti) {
                $action = "";
                $submitclickF1 = "";
                $submitclickF2 = "";
                $submitclickF3 = "";
            }
            else {
                $action = ' action="https://www.paypal.com/cgi-bin/webscr" ';
                $submitclickF1 = ' onclick="document.f1.submit();" ';
                $submitclickF2 = ' onclick="document.f2.submit();" ';
                $submitclickF3 = ' onclick="document.f3.submit();" ';
            }


            if ($payment_paypal == "S") {
                $strPaypal = '			<div class="menu">PAYPAL</div>
				<div class="row-fluid">
					' . $aPaymentPaypalInfo[$_SESSION['lan']] . '
				</div>';
                if (strpos($result, "amount1_CAN") === False) {
                    $strPaypal = '
				<div class="row-fluid" style="min-height:100px;">
					<div class="span6">
						<div class="row-fluid">
							' . $aPaymentPaypal1[$_SESSION['lan']] . '
						</div>
					</div>
					<div class="span6">
					<form name="f1" '.$action.' method="post" target="_blank">
						<input type="hidden" name="cmd" value="_xclick">
						<input type="hidden" name="business" value="payments@sarida.it">
						<input type="hidden" name="lc" value="' . $aPaypalLanguage[$_SESSION['lan']] . '">
						<input type="hidden" name="item_name" value="' . $cv . '/' . $nv . '/' . $yv . '/' . $tv . '">
						<input type="hidden" name="amount" value="' . $aAmount1[1] . '">
						<input type="hidden" name="currency_code" value="EUR">
						<input type="hidden" name="button_subtype" value="services">
						<input type="hidden" name="no_note" value="0">
						<div class="row-fluid">	
							<span style="font-size:18px;color:#ff2118;font-weight: bold;line-height:30px;">&euro; ' . $aAmount1[1] . '</span>
							<img  src="img/paypal.png" '.$submitclickF1.' style="height:40px;position:relative;right:2px;cursor: pointer;">	
						</div>
					</form>
					</div>
				</div>
				';
                    if ($aAmount2[1] != "0.00") {
                        $strPaypal .= '
                        <div class="row-fluid" style="min-height:100px;">
                            <div class="span6">
                                <div class="row-fluid">
                                    ' . $aPaymentPaypal2[$_SESSION['lan']] . '
                                </div>
                            </div>
                            <div class="span6">
                            <form name="f2" '.$action.' method="post" target="_blank">
                                <input type="hidden" name="cmd" value="_xclick">
                                <input type="hidden" name="business" value="payments@sarida.it">
                                <input type="hidden" name="lc" value="' . $aPaypalLanguage[$_SESSION['lan']] . '">
                                <input type="hidden" name="item_name" value="' . $cv . '/' . $nv . '/' . $yv . '/' . $tv . '">
                                <input type="hidden" name="amount" value="' . $aAmount2[1] . '">
                                <input type="hidden" name="currency_code" value="EUR">
                                <input type="hidden" name="button_subtype" value="services">
                                <input type="hidden" name="no_note" value="0">
                                <div class="row-fluid">	
                                    <span style="font-size:18px;color:#ff2118;font-weight: bold;line-height:30px;">&euro; ' . $aAmount2[1] . '</span>
                                    <img '.$submitclickF2.' src="img/paypal.png" style="height:40px;position:relative;right:2px;cursor: pointer;">	
                                </div>
                            </form>
                            </div>
                        </div>
                        <div class="row-fluid" style="min-height:100px;">
                            <div class="span6">
                                <div class="row-fluid">
                                    ' . $aPaymentPaypal3[$_SESSION['lan']] . '
                                </div>
                            </div>
                            <div class="span6">
                            <form name="f3" '.$action.' method="post" target="_blank">
                                <input type="hidden" name="cmd" value="_xclick">
                                <input type="hidden" name="business" value="payments@sarida.it">
                                <input type="hidden" name="lc" value="' . $aPaypalLanguage[$_SESSION['lan']] . '">
                                <input type="hidden" name="item_name" value="' . $cv . '/' . $nv . '/' . $yv . '/' . $tv . '">
                                <input type="hidden" name="amount" value="' . $aAmount3[1] . '">
                                <input type="hidden" name="currency_code" value="EUR">
                                <input type="hidden" name="button_subtype" value="services">
                                <input type="hidden" name="no_note" value="0">
                                <div class="row-fluid">	
                                    <span style="font-size:18px;color:#ff2118;font-weight: bold;line-height:30px;">&euro; ' . $aAmount3[1] . '</span>
                                    <img '.$submitclickF3.' src="img/paypal.png"  style="height:40px;position:relative;right:2px;cursor: pointer;">	
                                </div>
                            </form>
                            </div>
                        </div>
                    ';
                    }


                } else {
                    $aAmount1_CAN = explode('<amount1_CAN>', $result);
                    $aAmount1_CAD = explode('<amount1_CAD>', $result);

                    $aAmount2_CAN = explode('<amount2_CAN>', $result);
                    $aAmount2_CAD = explode('<amount2_CAD>', $result);

                    $aAmount3_CAN = explode('<amount3_CAN>', $result);
                    $aAmount3_CAD = explode('<amount3_CAD>', $result);


                    $strCAN_CAD1 = '
				<select name="amount" style="font-size:11px; width:350px;">
					<option value="' . $aAmount1[1] . '">&euro;' . $aAmount1[1] . ' - Piego ritirato all\'indirizzo di destinazione personalmente del destinatario</option>
					<option value="' . $aAmount1_CAN[1] . '">&euro;' . $aAmount1_CAN[1] . ' - Piego ritirato all\'indirizzo di destinazione da altra persona</option>
					<option value="' . $aAmount1_CAD[1] . '">&euro;' . $aAmount1_CAD[1] . ' - Piego ritirato presso l\'ufficio postale</option>
				</select>';

                    $strCAN_CAD2 = '
				<select name="amount" style="font-size:11px; width:350px;">
					<option value="' . $aAmount2[1] . '">&euro;' . $aAmount2[1] . ' - Piego ritirato all\'indirizzo di destinazione personalmente del destinatario</option>
					<option value="' . $aAmount2_CAN[1] . '">&euro;' . $aAmount2_CAN[1] . ' - Piego ritirato all\'indirizzo di destinazione da altra persona</option>
					<option value="' . $aAmount2_CAD[1] . '">&euro;' . $aAmount2_CAD[1] . ' - Piego ritirato presso l\'ufficio postale</option>
				</select>';

                    $strCAN_CAD3 = '
				<select name="amount" style="font-size:11px; width:350px;">
					<option value="' . $aAmount3[1] . '">&euro;' . $aAmount3[1] . ' - Piego ritirato all\'indirizzo di destinazione personalmente del destinatario</option>
					<option value="' . $aAmount3_CAN[1] . '">&euro;' . $aAmount3_CAN[1] . ' - Piego ritirato all\'indirizzo di destinazione da altra persona</option>
					<option value="' . $aAmount3_CAD[1] . '">&euro;' . $aAmount3_CAD[1] . ' - Piego ritirato presso l\'ufficio postale</option>
				</select>';


                    $strPaypal = '
				<div class="row-fluid">
					<div class="span12">
						<div class="row-fluid" style="border 1px solid #4880ff">
							' . $aPaymentPaypal1[$_SESSION['lan']] . '
						</div>
					</div>
				</div>	
				<div class="row-fluid" style="min-height:100px;">
					<div class="span12">
						<div class="row-fluid">
							<form name="f1" '.$action.' method="post" target="_blank">
								<input type="hidden" name="cmd" value="_xclick">
								<input type="hidden" name="business" value="payments@sarida.it">
								<input type="hidden" name="lc" value="' . $aPaypalLanguage[$_SESSION['lan']] . '">
								<input type="hidden" name="item_name" value="' . $cv . '/' . $nv . '/' . $yv . '/' . $tv . '">
								<input type="hidden" name="currency_code" value="EUR">
								<input type="hidden" name="button_subtype" value="services">
								<input type="hidden" name="no_note" value="0">
								 ' . $strCAN_CAD1 . '
		
								<img src="img/paypal.png" '.$submitclickF1.' style="height:40px;position:relative;right:2px;cursor: pointer;">	
							</form>
						</div>
					</div>
				</div>

				<div class="row-fluid">
					<div class="span12">
						<div class="row-fluid" style="border 1px solid #4880ff">
							' . $aPaymentPaypal2[$_SESSION['lan']] . '
						</div>
					</div>
				</div>	
				<div class="row-fluid" style="min-height:100px;">
					<div class="span12">
						<div class="row-fluid">
							<form name="f2" '.$action.' method="post" target="_blank">
								<input type="hidden" name="cmd" value="_xclick">
								<input type="hidden" name="business" value="payments@sarida.it">
								<input type="hidden" name="lc" value="' . $aPaypalLanguage[$_SESSION['lan']] . '">
								<input type="hidden" name="item_name" value="' . $cv . '/' . $nv . '/' . $yv . '/' . $tv . '">
								<input type="hidden" name="currency_code" value="EUR">
								<input type="hidden" name="button_subtype" value="services">
								<input type="hidden" name="no_note" value="0">
								' . $strCAN_CAD2 . '
		
								<img src="img/paypal.png" '.$submitclickF2.' style="height:40px;position:relative;right:2px;cursor: pointer;">	
							</form>
						</div>
					</div>
				</div>


				<div class="row-fluid">
					<div class="span12">
						<div class="row-fluid" style="border 1px solid #4880ff">
							' . $aPaymentPaypal3[$_SESSION['lan']] . '
						</div>
					</div>
				</div>	
				<div class="row-fluid" style="min-height:100px;">
					<div class="span12">
						<div class="row-fluid">
							<form name="f3" '.$action.' method="post" target="_blank">
								<input type="hidden" name="cmd" value="_xclick">
								<input type="hidden" name="business" value="payments@sarida.it">
								<input type="hidden" name="lc" value="' . $aPaypalLanguage[$_SESSION['lan']] . '">
								<input type="hidden" name="item_name" value="' . $cv . '/' . $nv . '/' . $yv . '/' . $tv . '">
								<input type="hidden" name="currency_code" value="EUR">
								<input type="hidden" name="button_subtype" value="services">
								<input type="hidden" name="no_note" value="0">
								' . $strCAN_CAD3 . '
		
								<img src="img/paypal.png" '.$submitclickF3.' style="height:40px;position:relative;right:2px;cursor: pointer;">	
							</form>
						</div>
					</div>
				</div>
';


                }


            }

            if ($payment_out == "Y") {
                $strPayment = $aPaymentOUT[$_SESSION['lan']];
            }


            $content .=
                '<div class="span3">
				<div class="row-fluid">
					<div class="row-fluid" style="min-height:80px;">
						' . $aId[1] . '
					</div>
					<div class="row-fluid" style="min-height:80px;">
						' . $aDate[1] . '
					</div>
					<div class="row-fluid" style="min-height:80px;">
						' . $aTime[1] . '
					</div>
					<div class="row-fluid" style="min-height:80px;">
						' . $aPlate[1] . '
					</div>
					<div class="row-fluid" style="min-height:80px;">
						' . $aCity[1] . '
					</div>
					<div class="row-fluid" style="min-height:80px;">
						' . $aLocation[1] . '
					</div>
					<div class="row-fluid" style="min-height:80px;">
						' . $aArticle[1] . '/' . $aParagraph[1] . '
					</div>';


            if (!strpos($result, "speedlimit") === False) {
                $aSpeedLimit = explode('<speedlimit>', $result);
                $aSpeed = explode('<speed>', $result);

                $content .= '					
					<div class="row-fluid" style="min-height:80px;">
						' . $aSpeedLimit[1] . ' Km/h
					</div>					
					<div class="row-fluid" style="min-height:80px;">
						' . $aSpeed[1] . ' Km/h
					</div>';
            }

            if (!strpos($result, "tlight1") === False) {
                $aLight1 = explode('<tlight1>', $result);
                $aLight2 = explode('<tlight2>', $result);
                $content .= '									
					<div class="row-fluid" style="min-height:80px;">
						(' . $aLight1[1] . ' /10 sec) - (' . $aLight2[1] . ' /10 sec)
					</div>';
            }


            $content .= '
				</div>
			</div>';


            $content .=
                '<div class="span7">
				<div class="row-fluid">
					<div class="row-fluid" style="min-height:80px;">
						' . $aPayment[$_SESSION['lan']] . '
					</div>
					<div class="row-fluid" style="min-height:30px;">
						' . $strPayment . '
					</div>
					<div class="row-fluid">
						' . $strPaypal . '
					</div>

				</div>
			</div>
		</div>';


            $content .=
                '<div class="row-fluid">
					<div class="span12 content">
						<div class="row-fluid">
							<div class="menu">' . $aAppealTitle[$_SESSION['lan']] . '</div>
						</div>	
						<div class="row-fluid">
							' . $aAppeal[$_SESSION['lan']] . '
						</div>
					</div>
				</div>';


            $content .=
                '<div class="row-fluid">
				<div class="span12 content">
					<div class="row-fluid">
						<div class="menu">' . $aContactTitle[$_SESSION['lan']] . '</div>
					</div>	
					<div class="row-fluid">
						' . $aContact[$_SESSION['lan']] . '
					</div>
				</div>
			</div>	
				
			<script>
				$("#more_info").click(function(){
				
					$("#s_obj").val("' . $nv . '/' . $yv . '/' . $tv . '");
				});
			</script>
';

        } else if ($v_p == "PROVA") {
            if (($v_n == "1234" && $v_y == 2014) || ($v_n == "4567" && $v_y == 2014) || ($v_n == "9999" && $v_y == 2015) || ($v_n == "9896" && $v_y == 2015)) {

                $id_div = 'content_query';
                $content = '<div class="span12 content">';


                $content .=
                    '<div class="row-fluid">
                    <div class="menu">' . $aDocumentTitle[$_SESSION['lan']] . '</div>
					<div class="span12">
						<div class="row-fluid">
							<div class="row-fluid" style="min-height:300px;">';


                $content .= '
							<a href="#" onclick="window.open(\'http://18.197.117.205/tl/img.jpg\');">
							<img title="2" src="http://18.197.117.205/tl/img.jpg" style="width:300px;height:250px" />
							</a>';


                $content .=
                    '</div>
						</div>
					</div>
				</div>';


                $aSpeedLimit = array(
                    'ita' => 'Limite di velocità',
                    'eng' => 'Speed limit',
                    'ger' => 'Geschwindigkeitsbegrenzung ',
                    'fre' => 'Limitation de vitesse',
                    'spa' => 'Límite de velocidad',
                );
                $aSpeed = array(
                    'ita' => 'Velocità effettiva',
                    'eng' => 'Actual speed',
                    'ger' => 'Ist-Geschwindigkeit',
                    'fre' => 'La vitesse réelle',
                    'spa' => 'Velocidad real',
                );

                $strSpeed =
                    '<div class="row-fluid" style="min-height:80px;">' .
                    $aSpeedLimit[$_SESSION['lan']]
                    . '</div>
					<div class="row-fluid" style="min-height:80px;">' .
                    $aSpeed[$_SESSION['lan']]
                    . '</div>
					';


                $aFineDescription = array(
                    'ita' => '
			<div class="row-fluid" style="min-height:80px;">
				Verbale
			</div>
			<div class="row-fluid" style="min-height:80px;">
				Data
			</div>
			<div class="row-fluid" style="min-height:80px;">
				Ora
			</div>
			<div class="row-fluid" style="min-height:80px;">
				Targa
			</div>
			<div class="row-fluid" style="min-height:80px;">
				Citta
			</div>
			<div class="row-fluid" style="min-height:80px;">
				Localita
			</div>
			<div class="row-fluid" style="min-height:80px;">
				Articolo violato
			</div>' .
                        $strSpeed
                ,
                    'eng' => '
			<div class="row-fluid" style="min-height:80px;">
				Fine
			</div>
			<div class="row-fluid" style="min-height:80px;">
				Date
			</div>
			<div class="row-fluid" style="min-height:80px;">
				Time
			</div>
			<div class="row-fluid" style="min-height:80px;">
				Plate
			</div>
			<div class="row-fluid" style="min-height:80px;">
				City
			</div>
			<div class="row-fluid" style="min-height:80px;">
				Resort
			</div>
			<div class="row-fluid" style="min-height:80px;">
				violated Article
			</div>' .
                        $strSpeed
                ,
                    'ger' => '
			<div class="row-fluid" style="min-height:80px;">
				Fein
			</div>
			<div class="row-fluid" style="min-height:80px;">
				Datum
			</div>
			<div class="row-fluid" style="min-height:80px;">
				Zeit
			</div>
			<div class="row-fluid" style="min-height:80px;">
				Kennzeichen
			</div>
			<div class="row-fluid" style="min-height:80px;">
				Stadt
			</div>
			<div class="row-fluid" style="min-height:80px;">
				Urlaubsort
			</div>
			<div class="row-fluid" style="min-height:80px;">
				Gegen Artikel
			</div>' .
                        $strSpeed
                ,
                    'fre' => '
			<div class="row-fluid" style="min-height:80px;">
				Amende
			</div>
			<div class="row-fluid" style="min-height:80px;">
				Date
			</div>
			<div class="row-fluid" style="min-height:80px;">
				Temps
			</div>
			<div class="row-fluid" style="min-height:80px;">
				Plaque d\'immatriculation
			</div>
			<div class="row-fluid" style="min-height:80px;">
				Ville
			</div>
			<div class="row-fluid" style="min-height:80px;">
				Recours
			</div>
			<div class="row-fluid" style="min-height:80px;">
				A violé l\'article
			</div>' .
                        $strSpeed
                ,
                    'spa' => '
			<div class="row-fluid" style="min-height:80px;">
				Multa
			</div>
			<div class="row-fluid" style="min-height:80px;">
				Fecha
			</div>
			<div class="row-fluid" style="min-height:80px;">
				Tiempo
			</div>
			<div class="row-fluid" style="min-height:80px;">
				Placa
			</div>
			<div class="row-fluid" style="min-height:80px;">
				Ciudad
			</div>
			<div class="row-fluid" style="min-height:80px;">
				Recurso
			</div>
			<div class="row-fluid" style="min-height:80px;">
				Artículo violado
			</div>' .
                        $strSpeed
                ,
                );


                $content .=
                    '<div class="span12 content">
				<div class="row-fluid">
					<div class="span5">
						  <div class="menu">' . $aInfoTitle[$_SESSION['lan']] . '</div>
					</div>	
					<div class="span7">
						  <div class="menu">' . $aPaymentTitle[$_SESSION['lan']] . '</div>
					</div>

				<div class="row-fluid">
					<div class="span2">
						<div class="row-fluid">
							' . $aFineDescription[$_SESSION['lan']] . '
						</div>
					</div>';

                $content .=
                    '<div class="span3">
				<div class="row-fluid">
					<div class="row-fluid" style="min-height:80px;">
						' . $v_n . '/' . $v_y . '
					</div>
					<div class="row-fluid" style="min-height:80px;">
						01/01/' . $v_y . '
					</div>
					<div class="row-fluid" style="min-height:80px;">
						' . $hv . ':' . $mv . '
					</div>
					<div class="row-fluid" style="min-height:80px;">
						PROVA
					</div>
					<div class="row-fluid" style="min-height:80px;">
						XXXXXXXXXXX
					</div>
					<div class="row-fluid" style="min-height:80px;">
						XXXXXXXXXXXXX
					</div>
					<div class="row-fluid" style="min-height:80px;">
						XXXX/XX
					</div>';


                $content .= '					
					<div class="row-fluid" style="min-height:80px;">
						XXX Km/h
					</div>					
					<div class="row-fluid" style="min-height:80px;">
						XXX Km/h
					</div>';


                $content .= '
				</div>
			</div>';


                $content .=
                    '<div class="span7">
				<div class="row-fluid">
					<div class="row-fluid" style="min-height:80px;">
						' . $aPayment[$_SESSION['lan']] . '
					</div>
				</div>
			</div>
		</div>';


                $content .=
                    '<div class="row-fluid">
					<div class="span12 content">
						<div class="row-fluid">
							<div class="menu">' . $aAppealTitle[$_SESSION['lan']] . '</div>
						</div>	
						<div class="row-fluid">
							' . $aAppeal[$_SESSION['lan']] . '
						</div>
					</div>
				</div>';


                $content .=
                    '<div class="row-fluid">
				<div class="span12 content">
					<div class="row-fluid">
						<div class="menu">' . $aContactTitle[$_SESSION['lan']] . '</div>
					</div>	
					<div class="row-fluid">
						' . $aContact[$_SESSION['lan']] . '
					</div>
				</div>
			</div>	
				
			<script>
				$("#more_info").click(function(){
				
					$("#s_obj").val("' . $nv . '/' . $yv . '/' . $tv . '");
				});
			</script>
';
            } else {
                $id_div = 'message_query';
                $content = '<div class="alert alert-danger">' . $aNotFound[$_SESSION['lan']] . '</div>';
            }


        } else {
            $id_div = 'message_query';
            $content = '<div class="alert alert-danger">' . $aNotFound[$_SESSION['lan']] . '</div>';
        }
    }
	else {
        $id_div = 'message_query';
        $content = '<div class="alert alert-danger">' . $aNotFound[$_SESSION['lan']] . '</div>';
    }

    echo json_encode(
        array(
            "id_div" => $id_div,
            "content" => $content)
    );



}

