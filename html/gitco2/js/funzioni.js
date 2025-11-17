//------------  FUNZIONI PHP IN JAVASCRIPT  ------------\\

function checkdate (m, d, y) {
	  // CONTROLLO ESISTENZA DATA
	  // *     example 1: checkdate(12, 31, 2000);
	  // *     returns 1: true
	  // *     example 2: checkdate(2, 29, 2001);
	  // *     returns 2: false
	  // *     example 3: checkdate(3, 31, 2008);
	  // *     returns 3: true
	  // *     example 4: checkdate(1, 390, 2000);
	  // *     returns 4: false
	  return m > 0 && m < 13 && y > 0 && y < 32768 && d > 0 && d <= (new Date(y, m, 0)).getDate();
}

function strrpos (haystack, needle, offset) {
	  // POSIZIONE SOTTOSTRINGA
	  // *     example 1: strrpos('Kevin van Zonneveld', 'e');
	  // *     returns 1: 16
	  // *     example 2: strrpos('somepage.com', '.', false);
	  // *     returns 2: 8
	  // *     example 3: strrpos('baa', 'a', 3);
	  // *     returns 3: false
	  // *     example 4: strrpos('baa', 'a', 2);
	  // *     returns 4: 2
	  var i = -1;
	  if (offset) {
	    i = (haystack + '').slice(offset).lastIndexOf(needle); // strrpos' offset indicates starting point of range till end,
	    // while lastIndexOf's optional 2nd argument indicates ending point of range from the beginning
	    if (i !== -1) {
	      i += offset;
	    }
	  } else {
	    i = (haystack + '').lastIndexOf(needle);
	  }
	  return i >= 0 ? i : false;
	}
	 

//CONTROLLO DATA
function controlla_data_campo ( data , avviso , stop )
{
	if (data == "" || data == null)
	{
		alert(avviso);
		if(stop == 1)
		{
			return false;
		}
	}
	else if ( data.length == 8 )
	{	
		array_data = new Array();
		array_data[0] = data.substring(0,2);
		array_data[1] = data.substring(2,4);
		array_data[2] = data.substring(4,8);
	}
	else if ( data.length == 10 )			
	{	
		array_data = new Array();	
		array_data = data.split("/");
	}
	else
	{
		alert(avviso);
		if(stop==1)
		{
			return false;
		}
	}
	
	controlladata = checkdate(array_data[1],array_data[0],array_data[2]);
	if(controlladata==true)
	{
		data_corretta = array_data[0]+"/"+array_data[1]+"/"+array_data[2];
		return data_corretta;
	}
	else
	{
		alert(avviso);
		return false;
	}
}

//CALCOLO CODICE FISCALE
function compute_CF(cognome, nome, tipo, data, cod_comune)
{
    alfabeto = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
    vocali = "AEIOU";
    numeri = "0123456789";
    mesi = "ABCDEHLMPRST";
    alfabeto_disp = "BAKPLCQDREVOSFTGUHMINJWZYX";
    numeri_disp = "10   2 3 4   5 6 7 8 9";
    
    CF = "";
    code = 0;
    if(tipo=="D") return "Impossibile generare il Codice Fiscale per una persona giuridica.";
    
    // Determina
    for( var i=0; i<=1; i++)
    {
        if(i==0)
        { 	word = cognome;	}
        else
        { 	word = nome;	}
        
        word = word.replace(" ","");
        word = word.replace("\'","");
        word = word.replace("à","a");
        word = word.replace("è","e");
        word = word.replace("é","e");
        word = word.replace("ì","i");
        word = word.replace("ò","o");
        word = word.replace("ù","u");
        word = word.toUpperCase();
        
        extracted_cons = "";
        extracted_vocs = "";
        
        for( var j=0; j<word.length; j++)
        {
        	char = word.substr(j,1);
            isthere = strrpos(vocali, char);
            if(isthere===false) // NOTA: I tre "=" sono voluti.
               extracted_cons = extracted_cons+char;
            else
               extracted_vocs = extracted_vocs+char;
        }
        
        num_cons = extracted_cons.length;
        num_vocs = extracted_vocs.length;
        
        if    ( num_cons>3 && i==1 )
        {	CF = CF + extracted_cons.substr(0,1) + extracted_cons.substr(2,2);	}
        else if( num_cons>2 )
        {   CF = CF + extracted_cons.substr(0,3);								}
        else if( num_cons==2 && num_vocs>0 )
        {   CF = CF + extracted_cons + extracted_vocs.substr(0,1);				}
        else if( num_cons==1 && num_vocs==1 )
        {   CF = CF + extracted_cons + extracted_vocs+"X";						}
        else if( num_cons==1 && num_vocs>1 )
        {	CF = CF + extracted_cons + extracted_vocs.substr(0,2);				}
        else if( num_cons==0 && num_vocs>2 )
        {	CF = CF + extracted_vocs.substr(0,3);								}
        else if( num_cons==0 && num_vocs==2)
        {	CF = CF + extracted_vocs + "X";										}
        else return "Le lettere che compongono cognome e nome non sono sufficienti per la generazione del Codice Fiscale. Controllare cognome e nome.";
    }

    array_data = new Array();
    array_data = data.split("/");
    anno = array_data[2];
    
    CF = CF + anno.substr(2,2);
    
    CF = CF + mesi.substr( array_data[1]-1 , 1 );
    
    giorno = parseInt(array_data[0]);
    
    if(tipo == 'M')
    {	
    	giorno += 100;
    	giorno = giorno.toString();
    	gg = giorno.substr(1,2);	
    }
    else
    {	
    	giorno += 140;
    	giorno = giorno.toString();
    	gg = giorno.substr(1,2);	
    }
    
    CF = CF + gg;
	
    CF = CF + cod_comune;

    for ( var i=0; i < CF.length; i++ )
    {
        char = CF.substr(i,1);
        if((i%2)==0) // NOTA: se i è pari, cioè se la lettera è dispari.
           code = code + strrpos(numeri_disp,char) + strrpos( alfabeto_disp,char );
        else
           code = code + strrpos(numeri,char) + strrpos( alfabeto,char );
    }
    
    CF = CF + alfabeto.substr((code%26),1);
	
    if(CF.length!=16) return "Non è stato possibile generare il Codice Fiscale.";
    
    return CF;
}

function verify_CF( cognome, nome, tipo, data, cod_comune, CF_inserito )
{
    alfabeto = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
    vocali = "AEIOU";
    numeri = "0123456789";
    mesi = "ABCDEHLMPRST";
    alfabeto_disp = "BAKPLCQDREVOSFTGUHMINJWZYX";
    numeri_disp = "10   2 3 4   5 6 7 8 9";
        
    CF = "";
    code = 0;
    if(tipo=="D") return "Impossibile generare il Codice Fiscale per una persona giuridica.";
    
    // Determina
    for( var i=0; i<=1; i++)
    {
        if(i==0)
        { 	word = cognome;	}
        else
        { 	word = nome;	}
        
        word = word.replace(" ","");
        word = word.replace("\'","");
        word = word.replace("à","a");
        word = word.replace("è","e");
        word = word.replace("é","e");
        word = word.replace("ì","i");
        word = word.replace("ò","o");
        word = word.replace("ù","u");
        word = word.toUpperCase();
        
        extracted_cons = "";
        extracted_vocs = "";
        
        for( var j=0; j<word.length; j++)
        {
        	char = word.substr(j,1);
            isthere = strrpos(vocali, char);
            if(isthere===false) // NOTA: I tre "=" sono voluti.
               extracted_cons = extracted_cons+char;
            else
               extracted_vocs = extracted_vocs+char;
        }
        
        num_cons = extracted_cons.length;
        num_vocs = extracted_vocs.length;
        
        if    ( num_cons>3 && i==1 )
        {	CF = CF + extracted_cons.substr(0,1) + extracted_cons.substr(2,2);	}
        else if( num_cons>2 )
        {   CF = CF + extracted_cons.substr(0,3);								}
        else if( num_cons==2 && num_vocs>0 )
        {   CF = CF + extracted_cons + extracted_vocs.substr(0,1);				}
        else if( num_cons==1 && num_vocs==1 )
        {   CF = CF + extracted_cons + extracted_vocs+"X";						}
        else if( num_cons==1 && num_vocs>1 )
        {	CF = CF + extracted_cons + extracted_vocs.substr(0,2);				}
        else if( num_cons==0 && num_vocs>2 )
        {	CF = CF + extracted_vocs.substr(0,3);								}
        else if( num_cons==0 && num_vocs==2)
        {	CF = CF + extracted_vocs + "X";										}
    }
    
    if( cognome!="" || nome!="" )
    {
    	if(CF.substr(0,3) != CF_inserito.substr(0,3) && CF_inserito.length==16)
    	{
    		alert("COGNOME non congruente con il codice fiscale.");
    		
    		$('#cognome_utente').val(CF_inserito.substr(0,3));
    	}

    	if(CF.substr(3,3) != CF_inserito.substr(3,3) && CF_inserito.length==16)
    	{
    		alert("NOME non congruente con il codice fiscale.");
    		
    		$('#nome_utente').val(CF_inserito.substr(3,3));
    	}
    }

   	CF = CF_inserito.substr(0,6);

    array_data = new Array();
    array_data = data.split("/");
    anno = array_data[2];
    
    CF = CF + anno.substr(2,2);
    
    CF = CF + mesi.substr( array_data[1]-1 , 1 );
    
    giorno = parseInt(array_data[0]);
    
    if(tipo == 'M')
    {	
    	giorno += 100;
    	giorno = giorno.toString();
    	gg = giorno.substr(1,2);	
    }
    else
    {	
    	giorno += 140;
    	giorno = giorno.toString();
    	gg = giorno.substr(1,2);	
    }
    
    CF = CF + gg;
	
    CF = CF + cod_comune;

    for ( var i=0; i < CF.length; i++ )
    {
        char = CF.substr(i,1);
        if((i%2)==0) // NOTA: se i è pari, cioè se la lettera è dispari.
           code = code + strrpos(numeri_disp,char) + strrpos( alfabeto_disp,char );
        else
           code = code + strrpos(numeri,char) + strrpos( alfabeto,char );
    }
    
    CF = CF + alfabeto.substr((code%26),1);

    if(CF!=CF_inserito)	return false;
    
    return CF;
}

//DECODIFICA CODICE FISCALE
function decode_CF( CF )
{
	array_CF = new Array();
	
	lettere_mesi = "ABCDEHLMPRST";
	
	cognome = CF.substr(0, 3);
	array_CF['cognome'] = cognome;
	
	nome = CF.substr(3, 3);
	array_CF['nome'] = nome;
	
	anno = CF.substr(6, 2);
	anno_int =  parseInt(anno);
	mese = CF.substr(8, 1);
	mese = String(lettere_mesi.indexOf(mese)+1);
	mese_int = parseInt(mese);
	
	giorno = CF.substr(9, 2);
	giorno_int =  parseInt(giorno);
	
	CC = CF.substr(11, 4);
	
	controllo = CF.substr(15, 1);
	
	if(giorno_int > 40) 
	{
		array_CF['sesso'] = "F";
		giorno = String(giorno_int - 40);
	}
	else	
	{
		array_CF['sesso'] = "M";
	}
	
	if(giorno.length<2)		array_CF['giorno'] = "0"+giorno;
	else					array_CF['giorno'] = giorno;
	
	if(mese.length<2)		array_CF['mese'] = "0"+mese;
	else					array_CF['mese'] = mese;
	
	data_odierna = new Date();
	anno_odierno = String(data_odierna.getFullYear());
	pref_anno = anno_odierno.substr(0,2);
	pref_anno_int = parseInt(pref_anno);
	post_anno = anno_odierno.substr(2,2);
	post_anno_int = parseInt(post_anno);
	
	if(anno>post_anno_int-10)	
		pref_anno = String( pref_anno_int - 1 );
	
	array_CF['anno'] = pref_anno + anno;
	array_CF['data'] = array_CF['giorno']+"/"+array_CF['mese']+"/"+array_CF['anno'];
	array_CF['CC'] = CC;
	
	$.ajax({  
		  type: "POST",  
		  async: false,
		  url: "ajax/ajax_anagrafe.php",  
		  data: {	
			  		CC_CF: array_CF['CC'],
			  		inutile: "inutile"
				}, 
				
		  success: function(value) {
	            array_ritorno = value.split('**');
	            array_CF['stato'] = array_ritorno[0];
	            array_CF['comune'] = array_ritorno[1];
		  }
		});
	
	return array_CF;
}

function number_format (number, decimals, dec_point, thousands_sep) {
	 
	  number = (number + '').replace(/[^0-9+\-Ee.]/g, '');
	  var n = !isFinite(+number) ? 0 : +number,
	    prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
	    sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
	    dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
	    s = '',
	    toFixedFix = function (n, prec) {
	      var k = Math.pow(10, prec);
	      return '' + Math.round(n * k) / k;
	    };
	  // Fix for IE parseFloat(0.55).toFixed(0) = 0;
	  s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
	  if (s[0].length > 3) {
	    s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
	  }
	  if ((s[1] || '').length < prec) {
	    s[1] = s[1] || '';
	    s[1] += new Array(prec - s[1].length + 1).join('0');
	  }
	  return s.join(dec);
}

function verifica_cf (CDF)
{
	pattern_cf = /^[a-zA-Z]{6}[0-9]{2}[abcdehlmprstABCDEHLMPRST]{1}[0-9]{2}[a-zA-Z]{1}[0-9]{3}[a-zA-Z]{1}$/;
	
	control_cf = CDF.search(pattern_cf);

	if(control_cf == -1 || CDF.length != 16)
	{
		alert("Il Codice Fiscale non \xE8 stato inserito correttamente.");
		return false;
	}
	
	return true;

}

function verifica_pi (PI)
{
	pattern_numeri = /[^0-9]/;
	
	control_pi = PI.match(pattern_numeri);
	if(control_pi != null || PI.length != 11)
	{
		alert("La Partita Iva della ditta non \xE8 stata inserita correttamente.");
		return false;
	}
	
	return true;
}

function verifica_mail (mail, value)
{
	if(typeof(value)==='undefined') value = "email";
	
	pattern_mail = /^[^\x40]{1,40}[\x40]{1}[^\x40]{1,20}[.]{1}[a-zA-Z.]{1,40}$/;
	
	if(mail!=null && mail!=undefined && mail!="")
	{
		control_mail = mail.search(pattern_mail);
		if(control_mail)
		{
	  		alert("Inserire un indirizzo "+value+" valido");
	   		return false;
		}
	}
	
	return true;
}

function obbligatorio (campo, value)
{	
	if ( ( campo == "" ) || ( campo == "undefined" ) ) 
	{
		if(typeof(value)==='undefined') 
		{
			value = "";
			alert("Un campo obbligatorio non \xE8 stato compilato.");
			return false;
		}
		else
		{
			alert("Il campo "+value+" \xE8 obbligatorio.");
			return false;
		}
	}
	return true;
}

function verifica_testo (testo , value)
{
	pattern_nome = /[^A-Za-z \x27]/;
	
	control_testo = testo.match(pattern_nome);
	if(control_testo)
	{
		alert("Il campo "+value+" non puo' contenere caratteri speciali o numerici.");
		return false;
	}
	
	return true;
}

function verifica_numero (numero , value)
{
	pattern_numero = /[^0-9]/;
	
	control_numero = numero.match(pattern_numero);
	if(control_numero)
	{
		alert("Il campo "+value+" puo' contenere solo caratteri numerici.");
		return false;
	}
	
	return true;
}

function verifica_alfanum (campo , value)
{
	pattern_alfanum = /[^A-Za-z0-9 ,;:.\x27]/;
	
	control_numero = campo.match(pattern_alfanum);
	if(control_numero)
	{
		alert("Il campo "+value+" non puo' contenere caratteri speciali.");
		return false;
	}
	
	return true;
}

function sleep(milliseconds) {
  var start = new Date().getTime();
  for (var i = 0; i < 1e7; i++) {
    if ((new Date().getTime() - start) > milliseconds){
      break;
    }
  }
}

function control_numero(campo)
{
	area = $("#"+campo);
	valore = area.val();
	
	//Controllo se è un numero regolare
	if(isFinite(valore))
	{
		//Se è un numero correggo se è negativo
		if (valore.charAt(0) == "-")	
			{	valore *= -1; 	}
		
		if(valore.indexOf(".")==-1)
			{
				return valore;
			}
		else
			{
				return number_format(valore,2,",","");
			}
			
	}
	
	//Controllo se ho un numero con la virgola non rilevato come numero dalla precedente funzione
	array_valore = valore.split(",");	
	//Controllo se ho solo una virgola
	if(array_valore.length==2)
	{
		//Controllo elemento a sinistra della virgola
		if(isFinite(array_valore[0]))
		{
			//Correggo primo elemento se è negativo
			if (array_valore[0].charAt(0) == "-")	
				{	array_valore[0] *= -1; 	}
			
			//Controllo elemento a destra della virgola
			if(isFinite(array_valore[1]))
			{
				//Correggo secondo elemento se è negativo
				if (array_valore[1].charAt(0) == "-")	
					{	array_valore[1] *= -1; 	}
				
				valore = array_valore[0]+"."+array_valore[1];
				return number_format(valore,2,",","");
			}
		}		
	}
	
	//Non è un numero
	return false;
}

function GeneraAlertModale70PerCento ()
{
	sWidth=0;
	sHeight=0;
	browserNome = navigator.appName;
	ind = navigator.appVersion.indexOf("MSIE");
	versione = parseFloat(navigator.appVersion.substr(ind+5));
	if (navigator.javaEnabled())
	{
	      sWidth=screen.width;
	      sHeight=screen.height-25;
	}
	if (sWidth == 0 || sHeight == 0)
	{
	    sWidth = 800;
	    sHeight = 550;
	}
	//alert (sWidth + "  " + sHeight);
	sWidth = sWidth * 70 / 100;
	sHeight = sHeight * 70 / 100;

	var setupPagina = "dialogWidth:" + sWidth + "px";
	setupPagina += "; dialogHeight:" + sHeight + "px";
	setupPagina += "; dialogTop:70px; dialogLeft:70px; status:yes;";

	return setupPagina;
}

function creaData( data ){
	//ACCETTA QUALSIASI FORMATO!!!
	
	var anno = null;
	var mese = null;
	var giorno = null;
	
	num_caratteri = data.length;
	if(num_caratteri == 8)
	{
		giorno = data.substr(0,2);
		mese = data.substr(2,2);
		anno = data.substr(4,4);
	}
	else if(num_caratteri == 10)
	{
		if(data.substr(2,1)== "/" || data.substr(2,1)== "-")
		{
			giorno = data.substr(0,2);
			mese = data.substr(3,2);
			anno = data.substr(6,4);
		}
		else if(data.substring(4,1)== "/" || data.substring(4,1)== "-")
		{
			anno = data.substr(0,4);
			mese = data.substr(5,2);
			giorno = data.substr(8,2);
		}
		else
			return false;
	}
	else
		return false;
	
	dt1   = parseInt(giorno);
	mon1  = parseInt(mese)-1;
	yr1   = parseInt(anno);
	
	date1 = new Date(yr1, mon1, dt1);
	
	return date1;
}

function comparaDate( data_inizio , data_fine ){
	
	if(data_fine > data_inizio)
		return true;
	else
		return false;

}

function aggiungiGiorni(data, giorni) {
    return new Date(data.getTime() + giorni*24*60*60*1000);
}

function stringaData( data, formato )
{
	//FORMATO FACOLTATIVO. SE VUOTO PRENDE FORMATO ITALIANO SE "mysql" PRENDE FORMATO DB
	formato = (typeof formato === "undefined") ? "ita" : formato;
	
	month = data.getMonth() + 1;
	if(month<10)	month = "0" + month;
	day = data.getDate();
	if(day<10)	day = "0" + day;
	year = data.getFullYear();	 

	if(formato=="ita")
		stringa = day + "/" + month + "/" + year;
	else if(formato=="mysql")
		stringa = year + "-" + month + "-" + day;
	
	return stringa;
	
}

// da oggetto data a formato MYSQL (YYYY-mm-dd)
function stringaDataMysql(data)
{

	month = data.getMonth() + 1;
	if(month<10)	month = "0" + month;
	day = data.getDate();
	if(day<10)	day = "0" + day;
	year = data.getFullYear();	 

	stringa = year + "-" + month + "-" + day;
	
	return stringa;
	
}
/*
function CheckRiferimentoEsistente ()
{
	var uscita = "";
	$.ajax
	(
		{
			type: "GET",  
			async: false,
			url: "ajax/ajax_targhe_estere.php",
			data:
			{ "tabella" : "registro_cronologico_cds",
				"richiestaJquery" : "SI",
				"tipo" : "checkriferimento",
				"dato" : $("#oldnumero").val(),
				"comune" : $("#sceglicomune").val()
			},
			success: function (checkkRif)
			{
				//alert (checkkRif);
				if (checkkRif == "OK_NON_PRESENTE") uscita = "NONPRESENTE";
				else if (checkkRif == "") uscita = "NONPRESENTE";
				else if (checkkRif == "PRESENTE")
				{
					alert ("Il riferimento inserito è già in un altro verbale");
					uscita = "ERROREPRESENTE";
				}
			}
		}
	);
	return uscita;
}
*/
function CheckRifEsistente (numero, comune, attuale)
{
	var uscita = "";
	$.ajax
	(
		{
			type: "GET",  
			async: false,
			url: "ajax/ajax_targhe_estere.php",
			data:
			{ "tabella" : "registro_cronologico_cds",
				"richiestaJquery" : "SI",
				"tipo" : "checkriferimento",
				"dato" : numero,
				"comune" : comune,
				"attuale" : attuale
			},
			success: function (checkkRif)
			{
				if (checkkRif == "OK_NON_PRESENTE") uscita = "NONPRESENTE";
				else if (checkkRif == "") uscita = "NONPRESENTE";
				else if (checkkRif == "PRESENTE")
				{
					alert ("Il riferimento inserito è già in un altro verbale");
					uscita = "ERROREPRESENTE";
				}
			}
		}
	);
	return uscita;
}

function CalcolaDimensioniSchermo ()
{
	sWidth=0;
	sHeight=0;
	browserNome = navigator.appName;
	ind = navigator.appVersion.indexOf("MSIE");
	versione = parseFloat(navigator.appVersion.substr(ind+5));
	if (navigator.javaEnabled())
	{
		sWidth=screen.width;
		sHeight=screen.height-25;
	}
	if (sWidth == 0 || sHeight == 0)
	{
		sWidth = 800;
		sHeight = 550;
	}
	//alert (sWidth + "," + sHeight);
	return sWidth.toString() + "," + sHeight.toString();
	//return 0;
}

function CercaTrasgressore (linkJquery, miocognome, mionome)
{
	var ID = "";
	var Cognome = miocognome;
	var Nome = mionome;
	var Indirizzo1 = "";
	var Indirizzo2 = "";
	var Indirizzo3 = "";
	var Indirizzo4 = "";
	var Indirizzo5 = "";
	var Indirizzo6 = "";
	var Genere = "";
	var Data_Nascita = "";
	var Comune_Nascita = "";
	var Zona_Postale = "";
	
	//alert (Cognome + " e " + Nome);
	
	$.ajax(
			{
				type: "GET",  
				async: false,
				url: linkJquery,
				data:
				{
					"tabella" : "targhe_estere_utenti",
					"richiestaJquery" : "SI",
					"tipo" : "sceltautente",
					"dato" : Cognome,
					"dato2" : Nome
				},
				success: function (selectTrasgr)
				{
					if (selectTrasgr != "")
					{
						var splittare = selectTrasgr.split("**");
						var lunghArray  = splittare.length;  //  12
						var scorro = 0;
						var frase = "";

						while (scorro < lunghArray)
						{
							ID = splittare[scorro++];
							Cognome = splittare[scorro++];
							Nome = splittare[scorro++];
							Indirizzo1 = splittare[scorro++];
							Indirizzo2 = splittare[scorro++];
							Indirizzo3 = splittare[scorro++];
							Indirizzo4 = splittare[scorro++];
							Indirizzo5 = splittare[scorro++];
							Indirizzo6 = splittare[scorro++];
							Genere = splittare[scorro++];
							Data_Nascita = splittare[scorro++];
							Comune_Nascita = splittare[scorro++];
							Zona_Postale = splittare[scorro++];
		
							frase = "1In memoria esiste già un utente con dati:\n\n";
							frase += "Cognome: " + Cognome + "\n";
							frase += "Nome: " + Nome + "\n";
							frase += "Genere: " + Genere + "\n";
							frase += "Data_Nascita: " + Data_Nascita + "\n";
							frase += "Comune_Nascita: " + Comune_Nascita + "\n";
							frase += "Indirizzo1: " + Indirizzo1 + "\n";
							frase += "Indirizzo2: " + Indirizzo2 + "\n";
							frase += "Indirizzo3: " + Indirizzo3 + "\n";
							frase += "Indirizzo4: " + Indirizzo4 + "\n";
							frase += "Indirizzo5: " + Indirizzo5 + "\n";
							frase += "Indirizzo6: " + Indirizzo6 + "\n\n";
		
							frase += "Vuoi usare questo utente?";
		
							var risp = confirm (frase);
		
							if (risp == true)
							{
								var arrayRet = new Array ( 
									ID,
									Cognome, 
									Nome,
									Indirizzo1,
									Indirizzo2,
									Indirizzo3,
									Indirizzo4,
									Indirizzo5,
									Indirizzo6,
									Genere,
									Data_Nascita,
									Comune_Nascita,
									Zona_Postale
								);
								// QUESTA FUNZIONE DEVE ESISTERE NEL FILE CHE CHIAMA QUESTA FUNZIONE
								PosizionaDatiNellaPagina (arrayRet);
								return null;
							}
						}
						return null;
					}
					return null;
				}
			}
	);
	
	
	
	/*
	$.get(linkJquery,
			{ "tabella" : "targhe_estere_utenti",
				"richiestaJquery" : "SI",
				"tipo" : "sceltautente",
				"dato" : Cognome,
				"dato2" : Nome
			},
			function (selectTrasgr)
			{
				//alert (selectTrasgr);
				if (selectTrasgr != "")
				{
					var splittare = selectTrasgr.split("**");
					var lunghArray  = splittare.length;  //  12
					var scorro = 0;
					var frase = "";

					while (scorro < lunghArray)
					{
						ID = splittare[scorro++];
						Cognome = splittare[scorro++];
						Nome = splittare[scorro++];
						Indirizzo1 = splittare[scorro++];
						Indirizzo2 = splittare[scorro++];
						Indirizzo3 = splittare[scorro++];
						Indirizzo4 = splittare[scorro++];
						Indirizzo5 = splittare[scorro++];
						Indirizzo6 = splittare[scorro++];
						Genere = splittare[scorro++];
						Data_Nascita = splittare[scorro++];
						Comune_Nascita = splittare[scorro++];
	
						frase = "In memoria esiste già un utente con dati:\n\n";
						frase += "Cognome: " + Cognome + "\n";
						frase += "Nome: " + Nome + "\n";
						frase += "Genere: " + Genere + "\n";
						frase += "Data_Nascita: " + Data_Nascita + "\n";
						frase += "Comune_Nascita: " + Comune_Nascita + "\n";
						frase += "Indirizzo1: " + Indirizzo1 + "\n";
						frase += "Indirizzo2: " + Indirizzo2 + "\n";
						frase += "Indirizzo3: " + Indirizzo3 + "\n";
						frase += "Indirizzo4: " + Indirizzo4 + "\n";
						frase += "Indirizzo5: " + Indirizzo5 + "\n";
						frase += "Indirizzo6: " + Indirizzo6 + "\n\n";
	
						frase += "Vuoi usare lo stesso utente?";
	
						var risp = confirm (frase);
	
						if (risp == true)
						{
							//alert ("array1");
							var arrayRet = new Array ( 
								Cognome, 
								Nome,
								Indirizzo1,
								Indirizzo2,
								Indirizzo3,
								Indirizzo4,
								Indirizzo5,
								Indirizzo6,
								Genere,
								Data_Nascita,
								Comune_Nascita
							);
							//alert ("array");
							return arrayRet;
						}

						//alert (scorro + "<" + lunghArray)
					}
					return null;
				}
				return null;
			}
	);
	*/
}

function CercaTargaTrasgressore (linkJquery, arrayCognomiUtenti, arrayNomiUtenti, targa)
{
	var finefor = "";
	var ID = "";
	var Cognome = "";
	var Nome = "";
	var Indirizzo1 = "";
	var Indirizzo2 = "";
	var Indirizzo3 = "";
	var Indirizzo4 = "";
	var Indirizzo5 = "";
	var Indirizzo6 = "";
	var Genere = "";
	var Data_Nascita = "";
	var Comune_Nascita = "";
	var Zona_Postale = "";
	
	for (var k = 0; k < arrayCognomiUtenti.length; k++)
	{
		if (finefor != "") return;
		ID = "";
		Cognome = arrayCognomiUtenti[k];
		Nome = arrayNomiUtenti[k];
		Indirizzo1 = "";
		Indirizzo2 = "";
		Indirizzo3 = "";
		Indirizzo4 = "";
		Indirizzo5 = "";
		Indirizzo6 = "";
		Genere = "";
		Data_Nascita = "";
		Comune_Nascita = "";
		Zona_Postale = "";
		if (Cognome == "" && Nome == "")
		{
			return;
		}
		
		$.ajax(
				{
					type: "GET",  
					async: false,
					url: linkJquery,
					data:
					{
						"tabella" : "targhe_estere_utenti",
						"richiestaJquery" : "SI",
						"tipo" : "sceltautente",
						"dato" : Cognome,
						"dato2" : Nome
					},
					success: function (selectTrasgr)
					{
						//alert (selectTrasgr);
						if (selectTrasgr != "")
						{
							var splittare = selectTrasgr.split("**");
							var lunghArray  = splittare.length;  //  12
							var scorro = 0;
							var frase = "";
	
							while (scorro < lunghArray)
							{
								ID = splittare[scorro++];
								Cognome = splittare[scorro++];
								Nome = splittare[scorro++];
								Indirizzo1 = splittare[scorro++];
								Indirizzo2 = splittare[scorro++];
								Indirizzo3 = splittare[scorro++];
								Indirizzo4 = splittare[scorro++];
								Indirizzo5 = splittare[scorro++];
								Indirizzo6 = splittare[scorro++];
								Genere = splittare[scorro++];
								Data_Nascita = splittare[scorro++];
								Comune_Nascita = splittare[scorro++];
								Zona_Postale = splittare[scorro++];
			
								frase = "2In memoria esiste già un utente collegato alla targa " + targa + ":\n\n";
								frase += "Cognome: " + Cognome + "\n";
								frase += "Nome: " + Nome + "\n";
								frase += "Genere: " + Genere + "\n";
								frase += "Data_Nascita: " + Data_Nascita + "\n";
								frase += "Comune_Nascita: " + Comune_Nascita + "\n";
								frase += "Indirizzo1: " + Indirizzo1 + "\n";
								frase += "Indirizzo2: " + Indirizzo2 + "\n";
								frase += "Indirizzo3: " + Indirizzo3 + "\n";
								frase += "Indirizzo4: " + Indirizzo4 + "\n";
								frase += "Indirizzo5: " + Indirizzo5 + "\n";
								frase += "Indirizzo6: " + Indirizzo6 + "\n\n";
			
								frase += "Vuoi usare questo utente?";
			
								var risp = confirm (frase);
			
								if (risp == true)
								{
									var arrayRet = new Array ( 
										ID,
										Cognome, 
										Nome,
										Indirizzo1,
										Indirizzo2,
										Indirizzo3,
										Indirizzo4,
										Indirizzo5,
										Indirizzo6,
										Genere,
										Data_Nascita,
										Comune_Nascita,
										Zona_Postale
									);
									// QUESTA FUNZIONE DEVE ESISTERE NEL FILE CHE CHIAMA QUESTA FUNZIONE
									PosizionaDatiNellaPagina (arrayRet);
									finefor = "fine";
									return null;
								}
							}
							return null;
						}
						return null;
					}
				}
		);
	}
}

function CtrlLettereTesto (carattere, maiuscolo) //  maiuscolo TRUE o FALSE
{
	//for (var i = 0; i < lungTesto; i++)
	{
		//var carattere = testo.charAt(i);
		if (carattere >= 'a' && carattere <= 'z') {}
		else if (carattere >= 'A' && carattere <= 'Z') {}
		else if (carattere >= '0' && carattere <= '9') {}
		else if (carattere == " ") {}
		//else if (carattere == "'" || carattere == "/") {}
		else if (carattere == "'") {}
		else if (carattere == "/") {}
		else if (carattere == '.' || carattere == ',' || carattere == ';' || carattere == ':') {}
		else if (carattere == '+' || carattere == '-' || carattere == '*') {}
		else if (carattere == '!' || carattere == '?') {}
		else if (carattere == '<' || carattere == '>') {}
		else if (carattere == '%' || carattere == '@' || carattere == '#') {}
		else if (carattere == '€' || carattere == '$' || carattere == '&' || carattere == '^') {}
		else if (carattere == '(' || carattere == ')') {}
		else if (carattere == '{' || carattere == '}' || carattere == '[' || carattere == ']') {}

		//else if (carattere == 'à' || carattere == 'è' || carattere == 'ì') {}
		//else if (carattere == 'ò' || carattere == 'ù') {}

		//else if (carattere == 'à' || carattere == 'ä' || carattere == 'Ä') {}
		//else if (carattere == 'è' || carattere == 'é' || carattere == 'ê') {}
		//else if (carattere == 'ì') {}
		//else if (carattere == 'ò' || carattere == 'ö' || carattere == 'Ö') {}
		//else if (carattere == 'ù' || carattere == 'ü' || carattere == 'Ü') {}
		//else if (carattere == 'ß') {}
		else
		{
			if (carattere == String.fromCharCode(13)) carattere = "INVIO";
			if (carattere == String.fromCharCode(10)) carattere = "INVIO";
			var messageError = "Hai inserito il carattere ' " + carattere + " ': carattere non accettato";
			alert (messageError);
			return "";
		}
		if (maiuscolo == true) return carattere.toUpperCase();
		else return carattere;
	}
}

function cambio_comune_js(pagina)
{
	/**
	 * FUNZIONE CAMBIO COMUNE 
	 * 
	 */
	
	c = $("#select_comune").val();
	a = $("#select_anno").val();
	strLink = pagina+".php?";
	strLink += "c=" + c;
	strLink += "&a=" + a;
	location.href = strLink;
}

function conferma_scelte_js(pagina)
{
	c = $("#select_comune").val();
	a = $("#select_anno").val();
	strLink = pagina+".php?";
	strLink += "c=" + c;
	strLink += "&a=" + a;
	
	if(a.length!=4)
	{
		alert("Selezionare l'anno!");
	}
	else if(a.length==4)
	{
		location.href = strLink;
	}
}

function conferma_anno_js(pagina, c)
{
	a = $("#select_anno_veloce").val();
	strLink = pagina+".php?";
	strLink += "c=" + c;
	strLink += "&a=" + a;
	
	if(a.length!=4)
	{
		alert("Selezionare l'anno!");
	}
	else if(a.length==4)
	{
		location.href = strLink;
	}
}