<?php

require $_SERVER['DOCUMENT_ROOT'] . "/Gitco2/percorsi.php";
include_once LIBRERIE . "/funzioni.php";
include TCPDF . "/tcpdf.php";

class pdf_con_bollettino extends TCPDF
{
	public $bordino_bollettino = array('LTRB' => array('width' => 0.05));
	public $bordo_bollettino = array('LTRB' => array('width' => 0.2));
	public $bordo_tratt_bollettino = array('LTRB' => array('dash' => '5'));
	
	public function crea_bollettino()
	{
		
		////////////////////////   LINEE BOLLETTINO 1	////////////////////////
		$this->SetLineWidth(0.2);
		
		//LINEA SUPERIORE BOLLETTINO 1
		$this->SetXY(132, -19);
		$this->Line($this->GetX(), $this->GetY(), 297, $this->GetY());
		//LINEA INFERIORE BOLLETTINO 1
		$this->SetXY(132, -102);
		$this->Line($this->GetX(), $this->GetY(), $this->GetX(), 210);
		//LINEA VERTICALE BOLLETTINO 1
		$this->SetXY(0, -98);
		$this->Line($this->GetX(), $this->GetY(), 297, $this->GetY());
		
		/////////////	BARRA SUPERIORE IN GRIGIO BOLLETTINO 1		/////////////
		$this->SetXY(0, -102);
		$this->SetFont('Arial','','7');
		$this->SetFillColor(200);
		//SFONDO GRIGIO
		$this->Cell(297,4,"",0,0,'L',true);
		$this->SetXY(0, -102);
		//RICEVUTA DI VERSAMENTO
		$this->Cell(10,4,"");
		$this->Cell(103.5,4,"CONTI CORRENTI POSTALI - Ricevuta di Versamento");
		$this->Cell(7,4,"Banco");
		$this->SetFont('Arial','B','7');
		$this->Cell(7,4,"Posta");
		$this->SetFont('Arial','','7');
		$this->Cell(4.5,4,"");
		//RICEVUTA DI ACCREDITO
		$this->Cell(7.5,4,"");
		$this->Cell(139,4,"CONTI CORRENTI POSTALI - Ricevuta di Accredito");
		$this->Cell(7,4,"Banco");
		$this->SetFont('Arial','B','7');
		$this->Cell(7,4,"Posta");
		$this->SetFont('Arial','','7');
		$this->Cell(4.5,4,"");
		
		////////////	INTESTAZIONE VERSAMENTO BOLLETTINO 1	///////////////
		$this->SetFont('Arial','','7');
		$this->SetXY(31, 210-102 + 7.5);
		//IMG EURO VERSAMENTO
		$this->Image("/gitco2/immagini/euro.png",'', '' ,7);
		//"Sul C/C n."
		$this->SetXY(40, 210-102 + 8.5);
		$this->Cell(7,3,"sul");
		$this->SetXY(40, 210-102 + 11);
		$this->Cell(7,3,"C/C n.");
		//"di euro"
		$this->SetXY(80.5, 210-102 + 11);
		$this->Cell(10,3,"di Euro");
		$this->SetXY(90, 210-102 + 10);
		$this->Cell(3,4.5,"",$this->bordino_bollettino,0,'C');	$this->Cell(1,'',"",0,0,'C');
		$this->Cell(3,4.5,"",$this->bordino_bollettino,0,'C');	$this->Cell(1,'',"",0,0,'C');
		$this->Cell(3,4.5,"",$this->bordino_bollettino,0,'C');	$this->Cell(1,'',"",0,0,'C');
		$this->Cell(3,4.5,"",$this->bordino_bollettino,0,'C');	$this->Cell(1,'',"",0,0,'C');
		$this->Cell(3,4.5,"",$this->bordino_bollettino,0,'C');	$this->Cell(1,'',"",0,0,'C');
		$this->Cell(3,4.5,"",$this->bordino_bollettino,0,'C');	$this->Cell(1,'',"",0,0,'C');
		$this->Cell(3,4.5,"",$this->bordino_bollettino,0,'C');	$this->Cell(1,'',"",0,0,'C');
		$this->Cell(3,4.5,"",$this->bordino_bollettino,0,'C');	
		
		$this->SetFont('ocrb','','11');	
		$this->Cell(1,'',",",0,0,'C');	
		$this->SetFont('Arial','','7');
		
		$this->Cell(3,4.5,"",$this->bordino_bollettino,0,'C');	$this->Cell(1,'',"",0,0,'C');
		$this->Cell(3,4.5,"",$this->bordino_bollettino,0,'C');
		//CODICE IBAN
		$this->SetFont('Arial','','6');
		$this->SetXY(31, 210-102 + 15.5);
		$this->Cell(20,3.5,"CODICE IBAN",0,0,'L');
		for($i=0;$i<27;$i++)
			$this->Cell(2.4,3.4,"",$this->bordino_bollettino);
	
		//INTESTATO A
		$this->SetXY(10, 210-102 + 22);
		$this->Cell(119,2.5,"INTESTATO A:",0,0,'L');
		//CAUSALE
		$this->SetXY(10, 210-102 + 32.5);
		$this->Cell(119,2.5,"CAUSALE:",0,0,'L');
		//ESEGUITO DA
		$this->SetXY(10, 210-102 + 46);
		$this->Cell(67,2.5,"ESEGUITO DA:",0,0,'L');
		
		//INTESTAZIONI BARCODE
		$this->SetFont('Arial','','5');
		$this->SetXY(77, 210-24);
		$this->Cell(55,3,"BOLLO DELL'UFF. POSTALE",0,0,'C');
		
		///////////		INTESTAZIONE ACCREDITO BOLLETTINO 1		/////////////
		//IMG EURO ACCREDITO
		$this->SetXY(139.5, 210-102 + 7.5);
		$this->Image("/gitco2/immagini/euro.png", '', '',7);
		//Sul C/C n.
		$this->SetFont('Arial','','7');
		$this->SetXY(148, 210-102 + 11);
		$this->Cell(20,3,"sul C/C n.",0,0,'C');
		//di euro
		$this->SetXY(236, 210-102 + 11);
		$this->Cell(10,3,"di Euro");
		$this->SetXY(248, 210-102 + 10);
		$this->Cell(3,4.5,"",$this->bordino_bollettino,0,'C');	$this->Cell(1,'',"",0,0,'C');
		$this->Cell(3,4.5,"",$this->bordino_bollettino,0,'C');	$this->Cell(1,'',"",0,0,'C');
		$this->Cell(3,4.5,"",$this->bordino_bollettino,0,'C');	$this->Cell(1,'',"",0,0,'C');
		$this->Cell(3,4.5,"",$this->bordino_bollettino,0,'C');	$this->Cell(1,'',"",0,0,'C');
		$this->Cell(3,4.5,"",$this->bordino_bollettino,0,'C');	$this->Cell(1,'',"",0,0,'C');
		$this->Cell(3,4.5,"",$this->bordino_bollettino,0,'C');	$this->Cell(1,'',"",0,0,'C');
		$this->Cell(3,4.5,"",$this->bordino_bollettino,0,'C');	$this->Cell(1,'',"",0,0,'C');
		$this->Cell(3,4.5,"",$this->bordino_bollettino,0,'C');	
				
		$this->SetFont('ocrb','','11');	
		$this->Cell(1,'',",",0,0,'C');	
		$this->SetFont('Arial','','7');
		
		$this->Cell(3,4.5,"",$this->bordino_bollettino,0,'C');	$this->Cell(1,'',"",0,0,'C');
		$this->Cell(3,4.5,"",$this->bordino_bollettino,0,'C');
		//CODICE IBAN
		$this->SetFont('Arial','','6');
		$this->SetXY(168, -102+15.5);
		$this->Cell(20,3.5,"CODICE IBAN",0,0,'L');
		for($i=0;$i<27;$i++)
			$this->Cell(2.4,3.4,"",$this->bordino_bollettino);
		
		//INTESTATO A
		$this->SetXY(139.5, 210-102 + 22);
		$this->Cell(146,2.5,"INTESTATO A:",0,0,'L');
		//CAUSALE
		$this->SetXY(192, 210-102 + 32.5);
		$this->Cell(95,2.5,"CAUSALE:",0,0,'L');
		//ESEGUITO DA
		$this->SetXY(192, 210-102 + 46);
		$this->Cell(95,2.5,"ESEGUITO DA:",0,0,'L');
		
		//INTESTAZIONI BARCODE
		$this->SetFont('Arial','','5');
		$this->SetXY(132, 210-24);
		$this->Cell(55,3,"BOLLO DELL'UFF. POSTALE",0,0,'C');
		$this->Cell(100,3,"IMPORTANTE NON SCRIVERE NELLA ZONA SOTTOSTANTE",0,0,'C');
		$this->SetFont('Arial','','4');
		$this->SetXY(132, 210-21);
		//codice cliente
		$this->Cell(55,2,"codice cliente",0,0,'C');
		$this->Cell(18.46,2,"",0,0,'C');
		//importo in euro
		$this->Cell(30.54,2,"importo in euro",0,0,'C');
		$this->Cell(5.115,2,"",0,0,'C');
		//numero conto
		$this->Cell(33.085,2,"numero conto",0,0,'C');
		$this->Cell(5.12,2,"",0,0,'C');
		//td
		$this->Cell(10.18,2,"td",0,0,'C');
		
	}
	
	public function crea_bollettino_inverso()
	{
		////////////////////////   LINEE BOLLETTINO 2	////////////////////////
		$this->SetLineWidth(0.2);
		
		//LINEA SUPERIORE BOLLETTINO 1
		$this->SetXY(-132, 19);
		$this->Line($this->GetX(), $this->GetY(), 0, $this->GetY());
		//LINEA INFERIORE BOLLETTINO 1
		$this->SetXY(-132, 102);
		$this->Line($this->GetX(), $this->GetY(), $this->GetX(), 0);
		//LINEA VERTICALE BOLLETTINO 1
		$this->SetXY(0, 98);
		$this->Line($this->GetX(), $this->GetY(), 297, $this->GetY());
		
		/////////////	BARRA SUPERIORE IN GRIGIO BOLLETTINO 2		/////////////
		$this->SetFont('Arial','','7');
		$this->SetFillColor(200);
		$this->SetXY(297, 102);
		$this->StartTransform();
		$this->Rotate(180);
		//SFONDO GRIGIO
		$this->Cell(297,4,"",0,0,'L',true);
		$this->StopTransform();
		
		$this->SetXY(297, 102);
		$this->StartTransform();
		$this->Rotate(180);
		//RICEVUTA DI VERSAMENTO
		$this->Cell(10,4,"");
		$this->Cell(103.5,4,"CONTI CORRENTI POSTALI - Ricevuta di Versamento");
		$this->Cell(7,4,"Banco");
		$this->SetFont('Arial','B','7');
		$this->Cell(7,4,"Posta");
		$this->SetFont('Arial','','7');
		$this->Cell(4.5,4,"");
		//RICEVUTA DI ACCREDITO
		$this->Cell(7.5,4,"");
		$this->Cell(139,4,"CONTI CORRENTI POSTALI - Ricevuta di Accredito");
		$this->Cell(7,4,"Banco");
		$this->SetFont('Arial','B','7');
		$this->Cell(7,4,"Posta");
		$this->SetFont('Arial','','7');
		$this->Cell(4.5,4,"");
		$this->StopTransform();
		
		
		////////////	INTESTAZIONE VERSAMENTO BOLLETTINO 1	///////////////
		$this->SetFont('Arial','','7');
		
		//IMG EURO VERSAMENTO
		$this->SetXY(297-31, 102 - 7.5);
		$this->StartTransform();
		$this->Rotate(180);		
		$this->Image("/gitco2/immagini/euro.png",'', '' ,7);
		$this->StopTransform();
		
		//"Sul C/C n."
		$this->SetXY(297-40, 102 - 8.5);
		$this->StartTransform();
		$this->Rotate(180);
		$this->Cell(7,3,"sul");
		$this->StopTransform();		
		$this->SetXY(297-40, 102 - 11);
		$this->StartTransform();
		$this->Rotate(180);
		$this->Cell(7,3,"C/C n.");
		$this->StopTransform();
		
		//"di euro"
		$this->SetXY(297-80.5, 102 - 11);
		$this->StartTransform();
		$this->Rotate(180);
		$this->Cell(10,3,"di Euro");
		$this->StopTransform();
		$this->SetXY(297-90, 102 - 10);
		$this->StartTransform();
		$this->Rotate(180);
		$this->Cell(3,4.5,"",$this->bordino_bollettino,0,'C');	$this->Cell(1,'',"",0,0,'C');
		$this->Cell(3,4.5,"",$this->bordino_bollettino,0,'C');	$this->Cell(1,'',"",0,0,'C');
		$this->Cell(3,4.5,"",$this->bordino_bollettino,0,'C');	$this->Cell(1,'',"",0,0,'C');
		$this->Cell(3,4.5,"",$this->bordino_bollettino,0,'C');	$this->Cell(1,'',"",0,0,'C');
		$this->Cell(3,4.5,"",$this->bordino_bollettino,0,'C');	$this->Cell(1,'',"",0,0,'C');
		$this->Cell(3,4.5,"",$this->bordino_bollettino,0,'C');	$this->Cell(1,'',"",0,0,'C');
		$this->Cell(3,4.5,"",$this->bordino_bollettino,0,'C');	$this->Cell(1,'',"",0,0,'C');
		$this->Cell(3,4.5,"",$this->bordino_bollettino,0,'C');			
		
		$this->SetFont('ocrb','','11');	
		$this->Cell(1,'',",",0,0,'C');	
		$this->SetFont('Arial','','7');
		
		$this->Cell(3,4.5,"",$this->bordino_bollettino,0,'C');	$this->Cell(1,'',"",0,0,'C');
		$this->Cell(3,4.5,"",$this->bordino_bollettino,0,'C');
		$this->StopTransform();

		//CODICE IBAN
		$this->SetFont('Arial','','6');
		$this->SetXY(297-31, 102 - 15.5);
		$this->StartTransform();
		$this->Rotate(180);
		$this->Cell(20,3.5,"CODICE IBAN",0,0,'L');
		for($i=0;$i<27;$i++)
			$this->Cell(2.4,3.4,"",$this->bordino_bollettino);
		$this->StopTransform();
		
		//INTESTATO A
		$this->SetXY(297-10, 102 - 22);
		$this->StartTransform();
		$this->Rotate(180);
		$this->Cell(119,2.5,"INTESTATO A:",0,0,'L');
		$this->StopTransform();
		//CAUSALE
		$this->SetXY(297-10, 102 - 32.5);
		$this->StartTransform();
		$this->Rotate(180);
		$this->Cell(119,2.5,"CAUSALE:",0,0,'L');
		$this->StopTransform();
		//ESEGUITO DA
		$this->SetXY(297-10, 102 - 46);
		$this->StartTransform();
		$this->Rotate(180);
		$this->Cell(67,2.5,"ESEGUITO DA:",0,0,'L');
		$this->StopTransform();
		//INTESTAZIONI BARCODE
		$this->SetFont('Arial','','5');
		$this->SetXY(297-77, 24);
		$this->StartTransform();
		$this->Rotate(180);
		$this->Cell(55,3,"BOLLO DELL'UFF. POSTALE",0,0,'C');
		$this->StopTransform();
		
		///////////		INTESTAZIONE ACCREDITO BOLLETTINO 1		/////////////
		//IMG EURO ACCREDITO
		$this->SetXY(297-139.5, 102 - 7.5);
		$this->StartTransform();
		$this->Rotate(180);
		$this->Image("/gitco2/immagini/euro.png", '', '',7);
		$this->StopTransform();
		//Sul C/C n.
		$this->SetFont('Arial','','7');
		$this->SetXY(297-148, 102 - 11);
		$this->StartTransform();
		$this->Rotate(180);
		$this->Cell(20,3,"sul C/C n.",0,0,'C');
		$this->StopTransform();
		//di euro
		$this->SetXY(297-236, 102 - 11);
		$this->StartTransform();
		$this->Rotate(180);
		$this->Cell(10,3,"di Euro");
		$this->StopTransform();
		$this->SetXY(297-248, 102 - 10);
		$this->StartTransform();
		$this->Rotate(180);
		$this->Cell(3,4.5,"",$this->bordino_bollettino,0,'C');	$this->Cell(1,'',"",0,0,'C');
		$this->Cell(3,4.5,"",$this->bordino_bollettino,0,'C');	$this->Cell(1,'',"",0,0,'C');
		$this->Cell(3,4.5,"",$this->bordino_bollettino,0,'C');	$this->Cell(1,'',"",0,0,'C');
		$this->Cell(3,4.5,"",$this->bordino_bollettino,0,'C');	$this->Cell(1,'',"",0,0,'C');
		$this->Cell(3,4.5,"",$this->bordino_bollettino,0,'C');	$this->Cell(1,'',"",0,0,'C');
		$this->Cell(3,4.5,"",$this->bordino_bollettino,0,'C');	$this->Cell(1,'',"",0,0,'C');
		$this->Cell(3,4.5,"",$this->bordino_bollettino,0,'C');	$this->Cell(1,'',"",0,0,'C');
		$this->Cell(3,4.5,"",$this->bordino_bollettino,0,'C');	$this->SetFont('ocrb','','11');	$this->Cell(1,'',",",0,0,'C');	$this->SetFont('Arial','','7');
		$this->Cell(3,4.5,"",$this->bordino_bollettino,0,'C');	$this->Cell(1,'',"",0,0,'C');
		$this->Cell(3,4.5,"",$this->bordino_bollettino,0,'C');
		$this->StopTransform();
		
		//CODICE IBAN
		$this->SetFont('Arial','','6');
		$this->SetXY(-168, 102-15.5);
		$this->StartTransform();
		$this->Rotate(180);
		$this->Cell(20,3.5,"CODICE IBAN",0,0,'L');
		for($i=0;$i<27;$i++)
			$this->Cell(2.4,3.4,"",$this->bordino_bollettino);
		$this->StopTransform();
			
		//INTESTATO A
		$this->SetXY(-139.5, 102 - 22);
		$this->StartTransform();
		$this->Rotate(180);
		$this->Cell(146,2.5,"INTESTATO A:",0,0,'L');
		$this->StopTransform();
		//CAUSALE
		$this->SetXY(-192, 102 - 32.5);
		$this->StartTransform();
		$this->Rotate(180);
		$this->Cell(95,2.5,"CAUSALE:",0,0,'L');
		$this->StopTransform();
		//ESEGUITO DA
		$this->SetXY(-192, 102 - 46);
		$this->StartTransform();
		$this->Rotate(180);
		$this->Cell(95,2.5,"ESEGUITO DA:",0,0,'L');
		$this->StopTransform();
		
		//INTESTAZIONI BARCODE
		$this->SetFont('Arial','','5');
		$this->SetXY(297-132, 24);
		$this->StartTransform();
		$this->Rotate(180);
		$this->Cell(55,3,"BOLLO DELL'UFF. POSTALE",0,0,'C');
		$this->Cell(100,3,"IMPORTANTE NON SCRIVERE NELLA ZONA SOTTOSTANTE",0,0,'C');
		$this->StopTransform();
		$this->SetFont('Arial','','4');
		$this->SetXY(297-132, 21);
		$this->StartTransform();
		$this->Rotate(180);
		//codice cliente
		$this->Cell(55,2,"codice cliente",0,0,'C');
		$this->Cell(18.46,2,"",0,0,'C');
		//importo in euro
		$this->Cell(30.54,2,"importo in euro",0,0,'C');
		$this->Cell(5.115,2,"",0,0,'C');
		//numero conto
		$this->Cell(33.085,2,"numero conto",0,0,'C');
		$this->Cell(5.12,2,"",0,0,'C');
		//td
		$this->Cell(10.18,2,"td",0,0,'C');
		$this->StopTransform();
	}
	
	public function scelta_td_bollettino($td , $codice_cliente, $importo, $ctrl_importo, $num_conto, $id_bollettino = 'uno')
	{
		switch($td)
		{
			case "123":	$this->td_123_bollettino($num_conto, $importo, $ctrl_importo, $id_bollettino);								break;
			case "451":	$this->td_451_bollettino($num_conto, $importo, $ctrl_importo, $id_bollettino);								break;
			case "674":	$this->td_674_bollettino($codice_cliente, $importo, $ctrl_importo, $num_conto, $id_bollettino);				break;
			case "896":	$this->td_896_bollettino($codice_cliente, $importo, $num_conto, $id_bollettino);	break;
		}
	}
	
	public function td_896_bollettino($codice_cliente, $importo, $num_conto, $id_bollettino = 'uno')
	{
		$this->set_codice_cliente_bollettino($codice_cliente, $id_bollettino);
		$this->set_importo_bollettino($importo, 'si', $id_bollettino);
		$this->set_num_conto_bollettino($num_conto, $id_bollettino);
		$this->set_td_bollettino('896',$id_bollettino);
		
		$this->codice_barcode_bollettino($codice_cliente, $importo, $num_conto, '896', $id_bollettino);
	}
	
	public function td_674_bollettino($codice_cliente, $importo, $ctrl_importo, $num_conto, $id_bollettino = 'uno')
	{
		
		$this->importo_in_lettere_bollettino($id_bollettino);
		$this->set_codice_cliente_bollettino($codice_cliente, $id_bollettino);
		$this->set_num_conto_bollettino($num_conto, $id_bollettino);
		$this->set_td_bollettino('674',$id_bollettino);
		
		if($ctrl_importo == 'si')	$this->set_importo_bollettino($importo, 'no', $id_bollettino);
	}
	
	public function td_451_bollettino($num_conto, $importo, $ctrl_importo, $id_bollettino = 'uno')
	{
		$this->importo_in_lettere_bollettino($id_bollettino);
		$this->set_td_bollettino('451',$id_bollettino);
		$this->set_num_conto_bollettino($num_conto, $id_bollettino);	

		if($ctrl_importo == 'si')	$this->set_importo_bollettino($importo, 'no', $id_bollettino);
	}
	
	public function td_123_bollettino($num_conto, $importo, $ctrl_importo, $id_bollettino = 'uno')
	{
		$this->set_num_conto_bollettino($num_conto, $id_bollettino, true);
		$this->linee_testi_bollettino($id_bollettino);
		$this->importo_in_lettere_bollettino($id_bollettino);
		$this->set_td_bollettino('123',$id_bollettino);
		
		if($ctrl_importo == 'si')	$this->set_importo_bollettino($importo, 'no', $id_bollettino);
	}
	
	public function codice_barcode_bollettino ($codice_cliente, $importo, $num_conto, $td, $id_bollettino)
	{
		$codice_barcode = strlen($codice_cliente).$codice_cliente;
		
		$codice_barcode.='12';
		$conto = str_split($num_conto);
		
		for($i=0;$i<12-count($conto);$i++)
			$codice_barcode.='0';
		for($i=0;$i<count($conto);$i++)
			$codice_barcode.=$conto[$i];
		
		$codice_barcode.='10';
		$importo = explode(',',$importo);
		$interi = str_split($importo[0]);
		$decimali = str_split($importo[1]);
		
		for($i=0;$i<8-count($interi);$i++)
			$codice_barcode.='0'; 
		for($i=0;$i<count($interi);$i++)
			$codice_barcode.=$interi[$i]; 
		for($i=0;$i<count($decimali);$i++)
			$codice_barcode.=$decimali[$i];
		
		$codice_barcode.=strlen($td).$td;
		
		if($id_bollettino=='due')
		{
			$this->SetXY(-82, +18);
			$this->StartTransform();
			$this->Rotate(180);
			$this->write2DBarcode($codice_barcode, 'DATAMATRIX', 297-82, 18, 45, 16);
			$this->StopTransform();
			
			$this->SetXY(-192, -171);
			$this->StartTransform();
			$this->Rotate(180);
			$this->write1DBarcode($codice_barcode,'C128C', 297-192, 210-171, 95, 12);
			$this->StopTransform();
			
			$this->SetXY(-192, -183);
			$this->StartTransform();
			$this->Rotate(180);
			$this->SetFont('Arial','','6');
			$this->Cell(95,2,$codice_barcode,0,0,'C');
			
			$this->StopTransform();
		}
		else if($id_bollettino=='uno')
		{
			$this->SetXY(82, -18);
			$this->write2DBarcode($codice_barcode, 'DATAMATRIX', 82, 210-18,45,16);
		
			$this->write1DBarcode($codice_barcode,'C128C', 192, 171, 95, 12);
			$this->SetXY(192, 183);
			$this->SetFont('Arial','','6');
			$this->Cell(95,2,$codice_barcode,0,0,'C');
		}				
	}
	
	public function importo_in_lettere_bollettino($id_bollettino = 'uno')
	{
		$this->SetFont('Arial','','6');
		
		if($id_bollettino=='due')
		{
			$this->SetXY(-31, 102-19.5);
			$this->StartTransform();
			$this->Rotate(180);
		}
		else if($id_bollettino=='uno')
		{
			$this->SetXY(31, -102+19.5);
		}
		
		$this->Cell(25,5,"IMPORTO IN LETTERE",0,0,'L');
		$this->Line($this->GetX(), $this->GetY()+4, $this->GetX()+73, $this->GetY()+4);
		
		if($id_bollettino=='due')
		{
			$this->StopTransform();
			$this->SetXY(-168, 102-19.5);
			$this->StartTransform();
			$this->Rotate(180);
		}
		else if($id_bollettino=='uno')
		{
			$this->SetXY(168, -102+19.5);
		}
		
		$this->Cell(25,5,"IMPORTO IN LETTERE",0,0,'L');
		$this->Line($this->GetX(), $this->GetY()+4, $this->GetX()+94, $this->GetY()+4);
		
		if($id_bollettino=='due')
		{
			$this->StopTransform();
		}
	}
	
	public function set_codice_cliente_bollettino( $codice, $id_bollettino = 'uno' )
	{		
		if(strlen($codice)!=18)	return false;
		
		$this->SetFont('ocrb','','11');
		
		if($id_bollettino=='due')
		{
			$this->SetXY(-139.5, 8.5);
			$this->StartTransform();
			$this->Rotate(180);
		}
		else if($id_bollettino=='uno')
		{
			$this->SetXY(139.5, -8.5);
		}
			
		$cod = str_split($codice);
		
		$this->Cell(2.545,'',"<");	
		
		for($i=0;$i<count($cod);$i++)
			$this->Cell(2.545,'',$cod[$i]);
			
		$this->Cell(2.545,'',">");
		
		if($id_bollettino=='due')
		{
			$this->StopTransform();
		}
		
		if($id_bollettino=='due')
		{
			$this->SetXY(-139.5, 102-42);
			$this->StartTransform();
			$this->Rotate(180);
		}
		else if($id_bollettino=='uno')
		{
			$this->SetXY(139.5, -102+42);
		}
		
		for($i=0;$i<count($cod);$i++)
			$this->Cell(2.545,'',$cod[$i]);
		
		if($id_bollettino=='due')
		{
			$this->StopTransform();
		}
		
	}
	
	public function set_quinto_campo($td, $quinto_campo, $codeline = false )
	{
		if($td == "896" || $td == "674")
		{
			$ritorno = "";
			
			if($codeline === true)
				$ritorno.= "<";
			
			$ritorno.= $quinto_campo;
			
			if($codeline === true)
				$ritorno.= ">";
		}
		else
			$ritorno = "";
		
		return $ritorno;
	} 
	
	public function barcode_importo_bollettino($td, $importo)
	{
		if($td == "896")
		{
			$importo = explode(',',$importo);
			$interi = str_split($importo[0]);
			$decimali = str_split($importo[1]);
		
			$ritorno = "";
			for($i=0;$i<8-count($interi);$i++)
				$ritorno .= "0";			
			for($i=0;$i<count($interi);$i++)
				$ritorno .= $interi[$i];
	
			$ritorno .= "+";
			
			for($i=0;$i<count($decimali);$i++)
				$ritorno .= $decimali[$i];
			
			$ritorno .= ">";
		}
		else 
			$ritorno = "";
		
		return $ritorno;
	}
	
	public function set_importo_bollettino( $importo, $barcode='si', $id_bollettino = 'uno' )
	{	
		$importo = explode(',',$importo);
		$interi = str_split($importo[0]);
		$decimali = str_split($importo[1]);
		
		$this->SetFont('ocrb','','11');
	
		if($barcode == "si")
		{
			
		if($id_bollettino=='due')
		{
			$this->SetXY(-205.46, 8.5);
			$this->StartTransform();
			$this->Rotate(180);
		}
		else if($id_bollettino=='uno')
		{
			$this->SetXY(205.46, -8.5);
		}
			
		for($i=0;$i<8-count($interi);$i++)
			$this->Cell(2.545,'','0');
		for($i=0;$i<count($interi);$i++)
			$this->Cell(2.545,'',$interi[$i]);
				
			$this->Cell(2.545,'',"+");
			
		for($i=0;$i<count($decimali);$i++)
			$this->Cell(2.545,'',$decimali[$i]);
	
			$this->Cell(2.545,'',">");
			
		if($id_bollettino=='due')
		{
			$this->StopTransform();
		}
		
		}
		
		if($id_bollettino=='due')
		{
			$this->SetXY(-90, 102-10);
			$this->StartTransform();
			$this->Rotate(180);
		}
		else if($id_bollettino=='uno')
		{
			$this->SetXY(90, -102+10);
		}
		
		for($i=0;$i<8-count($interi);$i++)
		{
			$this->Cell(3,'','',0,0,'C');
			$this->Cell(1,'','');
		}
		for($i=0;$i<count($interi);$i++)
		{
			$this->Cell(3,'',$interi[$i],0,0,'C');
			$this->Cell(1,'','');
		}									
		for($i=0;$i<count($decimali);$i++)
		{
			$this->Cell(3,'',$decimali[$i],0,0,'C');
			$this->Cell(1,'','');
		}
		
		if($id_bollettino=='due')
		{
			$this->StopTransform();
			$this->SetXY(-248, 102-10);
			$this->StartTransform();
			$this->Rotate(180);
		}
		else if($id_bollettino=='uno')
		{
			$this->SetXY(248, -102+10);
		}
		
		for($i=0;$i<8-count($interi);$i++)
		{
			$this->Cell(3,'','',0,0,'C');
			$this->Cell(1,'','');
		}
		for($i=0;$i<count($interi);$i++)
		{
			$this->Cell(3,'',$interi[$i],0,0,'C');
			$this->Cell(1,'','');
		}
		for($i=0;$i<count($decimali);$i++)
		{
			$this->Cell(3,'',$decimali[$i],0,0,'C');
			$this->Cell(1,'','');
		}
		
		if($id_bollettino=='due')
		{
			$this->StopTransform();
		}
	}
	
	public function linee_testi_bollettino($id_bollettino = 'uno')
	{
		$this->SetLineWidth(0.05);
		if($id_bollettino=='due')
		{
			$this->SetXY(-48, 102-10);
			$this->Line($this->GetX()-28, $this->GetY()-4, $this->GetX(), $this->GetY()-4);
			$this->SetXY(-168, 102-10);
			$this->Line($this->GetX()-35, $this->GetY()-4, $this->GetX(), $this->GetY()-4);
			$this->SetXY(-10, 102-25.5);
			$this->Line($this->GetX()-119, $this->GetY()-4, $this->GetX(), $this->GetY()-4);
			$this->SetXY(-139.5, 102-25.5);
			$this->Line($this->GetX()-147.5, $this->GetY()-4, $this->GetX(), $this->GetY()-4);
			$this->SetXY(-10, 102-36);
			$this->Line($this->GetX()-119, $this->GetY()-4, $this->GetX(), $this->GetY()-4);
			$this->Line($this->GetX()-119, $this->GetY()-8, $this->GetX(), $this->GetY()-8);
			$this->SetXY(-192, 102-36);
			$this->Line($this->GetX()-95, $this->GetY()-4, $this->GetX(), $this->GetY()-4);
			$this->Line($this->GetX()-95, $this->GetY()-8, $this->GetX(), $this->GetY()-8);
			$this->SetXY(-10, 102-50);
			$this->Line($this->GetX()-67, $this->GetY()-4, $this->GetX(), $this->GetY()-4);
			$this->Line($this->GetX()-67, $this->GetY()-8, $this->GetX(), $this->GetY()-8);
			$this->Line($this->GetX()-67, $this->GetY()-12, $this->GetX(), $this->GetY()-12);
			$this->SetXY(-192, 102-50);
			$this->Line($this->GetX()-95, $this->GetY()-4, $this->GetX(), $this->GetY()-4);
			$this->Line($this->GetX()-95, $this->GetY()-8, $this->GetX(), $this->GetY()-8);
			$this->Line($this->GetX()-95, $this->GetY()-12, $this->GetX(), $this->GetY()-12);
		}
		else if($id_bollettino=='uno')
		{
			$this->SetXY(48, -102+10);
			$this->Line($this->GetX(), $this->GetY()+4, $this->GetX()+28, $this->GetY()+4);
			$this->SetXY(168, -102+10);
			$this->Line($this->GetX(), $this->GetY()+4, $this->GetX()+35, $this->GetY()+4);
			$this->SetXY(10, -102+25.5);
			$this->Line($this->GetX(), $this->GetY()+4, $this->GetX()+119, $this->GetY()+4);
			$this->SetXY(139.5, -102+25.5);
			$this->Line($this->GetX(), $this->GetY()+4, $this->GetX()+147.5, $this->GetY()+4);
			$this->SetXY(10, -102+36);
			$this->Line($this->GetX(), $this->GetY()+4, $this->GetX()+119, $this->GetY()+4);
			$this->Line($this->GetX(), $this->GetY()+8, $this->GetX()+119, $this->GetY()+8);
			$this->SetXY(192, -102+36);
			$this->Line($this->GetX(), $this->GetY()+4, $this->GetX()+95, $this->GetY()+4);
			$this->Line($this->GetX(), $this->GetY()+8, $this->GetX()+95, $this->GetY()+8);
			$this->SetXY(10, -102+50);
			$this->Line($this->GetX(), $this->GetY()+4, $this->GetX()+67, $this->GetY()+4);
			$this->Line($this->GetX(), $this->GetY()+8, $this->GetX()+67, $this->GetY()+8);
			$this->Line($this->GetX(), $this->GetY()+12, $this->GetX()+67, $this->GetY()+12);
			$this->SetXY(192, -102+50);
			$this->Line($this->GetX(), $this->GetY()+4, $this->GetX()+95, $this->GetY()+4);
			$this->Line($this->GetX(), $this->GetY()+8, $this->GetX()+95, $this->GetY()+8);
			$this->Line($this->GetX(), $this->GetY()+12, $this->GetX()+95, $this->GetY()+12);
		}
		
		
	}
	
	public function barcode_conto_bollettino($td, $num_conto)
	{
		if($td == "896" || $td == "674" || $td == "451")
		{
			$conto = str_split($num_conto);
			
			$ritorno = "";
			for($i=0;$i<12-count($conto);$i++)
				$ritorno .= "0";
			for($i=0;$i<count($conto);$i++)
				$ritorno .= $conto[$i];
			$ritorno .= "<";
		}
		else 
			$ritorno = "";
		
		return $ritorno;
	}
	
	public function set_num_conto_bollettino( $num_conto, $id_bollettino = 1, $no_barcode = false )
	{		
		$this->SetFont('ocrb','','11');
		$conto = str_split($num_conto);
		
		if($no_barcode === false)
		{
			
		if($id_bollettino=='due')
		{
			$this->SetXY(-241.115, 8.5);
			$this->StartTransform();
			$this->Rotate(180);
		}
		else if($id_bollettino=='uno')
		{
			$this->SetXY(241.115, -8.5);
		}		
				
		for($i=0;$i<12-count($conto);$i++)
			$this->Cell(2.545,'','0');
		for($i=0;$i<count($conto);$i++)
			$this->Cell(2.545,'',$conto[$i]);
		
		$this->Cell(2.545,'',"<");
		
		if($id_bollettino == 'due')
		{
			$this->StopTransform();
		}
		
		}
		
		if($id_bollettino=='due')
		{
			$this->SetXY(-48, 102-10 );
			$this->StartTransform();
			$this->Rotate(180);
		}
		else if($id_bollettino == 'uno')
		{
			$this->SetXY(48, -102+10 );
		}		
			
		for($i=0;$i<count($conto);$i++)
			$this->Cell(2.545,'',$conto[$i]);
		
		if($id_bollettino == 'due')
		{
			$this->StopTransform();
		}
		
		if($id_bollettino=='due')
		{
			$this->SetXY(-168, 102-10);
			$this->StartTransform();
			$this->Rotate(180);
		}
		else if($id_bollettino == 'uno')
		{
			$this->SetXY(168, -102+10);
		}
			
		for($i=0;$i<count($conto);$i++)
			$this->Cell(2.545,'',$conto[$i]);
		
		if($id_bollettino == 'due')
		{
			$this->StopTransform();
		}
	}
	
	public function set_td_bollettino( $td, $id_bollettino = 'uno' )
	{
		if(strlen($td)!=3)	return false;

		$this->SetFont('ocrb','','11');
		
		if($id_bollettino=='due')
		{
			$this->SetXY(-139.5, 102-16.5);
			$this->StartTransform();
			$this->Rotate(180);
		}
		else if($id_bollettino=='uno')
		{
			$this->SetXY(139.5, -102+16.5);
		}
		
		$this->Cell(20,'',"TD ".$td);
		
		if($id_bollettino=='due')
		{
			$this->StopTransform();
			$this->SetXY(-279.32, 8.5);
			$this->StartTransform();
			$this->Rotate(180);
		}
		else if($id_bollettino=='uno')
		{
			$this->SetXY(279.32, -8.5);
		}
			
		$td = str_split($td);
	
		for($i=0;$i<count($td);$i++)
			$this->Cell(2.545,'',$td[$i]);
	
		$this->Cell(2.545,'',">");
	
		if($id_bollettino=='due')
		{
			$this->StopTransform();
		}
	}
	
	public function iban_bollettino( $iban, $id_bollettino = 'uno' )
	{	
		$iban_arr = str_split($iban);
		if($iban_arr[0]=='*' || $iban_arr[0] == "")
		{
			$iban = '***************************';
			$iban_arr = str_split($iban);
		}
		
		if(strlen($iban)!=27)	return false;
		
		$this->SetFont('ocrb','','9');
	
		if($id_bollettino=='due')
		{
			$this->SetXY(-51, 102-15.5);
			$this->StartTransform();
			$this->Rotate(180);
		}
		else if($id_bollettino=='uno')
		{
			$this->SetXY(51, -102+15.5);
		}		
	
		for($i=0;$i<count($iban_arr);$i++)
			$this->Cell(2.4,'',$iban_arr[$i],0,0,'C');
		
		if($id_bollettino=='due')
		{
			$this->StopTransform();
			$this->SetXY(-188, 102-15.5);
			$this->StartTransform();
			$this->Rotate(180);
		}
		else if($id_bollettino=='uno')
		{
			$this->SetXY(188, -102+15.5);
		}		
	
		for($i=0;$i<count($iban_arr);$i++)
			$this->Cell(2.4,'',$iban_arr[$i],0,0,'C');
		
		if($id_bollettino=='due')
		{
			$this->StopTransform();
		}
	}
	
	public function intestatario_bollettino ($intestatario , $id_bollettino = 'uno')
	{
		$intestatario = strtoupper($intestatario);
		$this->SetFont('ocrb','B','9');
		
		if($id_bollettino=='due')
		{
			$this->SetXY(-10, 102-25.5);
			$this->StartTransform();
			$this->Rotate(180);
		}
		else if($id_bollettino=='uno')
		{
			$this->SetXY(10, -102+25.5);
		}
		
		$this->Cell(119,4,$intestatario);
		
		if($id_bollettino=='due')
		{
			$this->StopTransform();
			$this->SetXY(-139.5, 102-25.5);
			$this->StartTransform();
			$this->Rotate(180);
		}
		else if($id_bollettino=='uno')
		{
			$this->SetXY(139.5, -102+25.5);
		}
			
		$this->SetFont('ocrb','B','11');
		
		$this->Cell(146,4,$intestatario);
		
		if($id_bollettino=='due')
		{
			$this->StopTransform();
		}
	}
	
	public function causale_bollettino ($riga1 , $riga2, $id_bollettino = 'uno')
	{
		$riga1 = strtoupper($riga1);
		$riga2 = strtoupper($riga2);
		$this->SetFont('ocrb','B','7');
	
		if($id_bollettino=='due')
		{
			$this->SetXY(-10, 102-36);
			$this->StartTransform();
			$this->Rotate(180);
		}
		else if($id_bollettino=='uno')
		{
			$this->SetXY(10, -102+36);
		}
	
		$this->Cell(119,4,$riga1);
		
		if($id_bollettino=='due')
		{
			$this->StopTransform();
			$this->SetXY(-10, 102-40);
			$this->StartTransform();
			$this->Rotate(180);
		}
		else if($id_bollettino=='uno')
		{
			$this->SetXY(10, -102+40);
		}
		
		$this->Cell(119,4,$riga2);
	
		if($id_bollettino=='due')
		{
			$this->StopTransform();
		}
		
		if($id_bollettino=='due')
		{
			$this->SetXY(-192, 102-36);
			$this->StartTransform();
			$this->Rotate(180);
		}
		else if($id_bollettino=='uno')
		{
			$this->SetXY(192, -102+36);
		}
		
		$this->Cell(95,4,$riga1);
		
		if($id_bollettino=='due')
		{
			$this->StopTransform();
			$this->SetXY(-192, 102-40);
			$this->StartTransform();
			$this->Rotate(180);
		}
		else if($id_bollettino=='uno')
		{
			$this->SetXY(192, -102+40);
		}
		
		$this->Cell(95,4,$riga2);
		
		if($id_bollettino=='due')
		{
			$this->StopTransform();
		}
	}
	
	public function zona_cliente_bollettino ($nome_utente , $indirizzo_destinatario , $id_bollettino = 'uno')
	{
		$riga1 = strtoupper($nome_utente);
		$riga2 = strtoupper($indirizzo_destinatario['Riga1']." - ".$indirizzo_destinatario['Riga2']);
		if($indirizzo_destinatario['Riga4']!="")
			$riga3 = strtoupper($indirizzo_destinatario['Riga3'].", ".$indirizzo_destinatario['Riga4']);
		else 
			$riga3 = strtoupper($indirizzo_destinatario['Riga3']);
		$this->SetFont('ocrb','B','7');
	
		if($id_bollettino=='due')
		{
			$this->SetXY(-10, 102-50);
			$this->StartTransform();
			$this->Rotate(180);
		}
		else if($id_bollettino=='uno')
		{
			$this->SetXY(10, -102+50);
		}
	
		$this->Cell(67,4,$riga1);
	
		if($id_bollettino=='due')
		{
			$this->StopTransform();
			$this->SetXY(-10, 102-54);
			$this->StartTransform();
			$this->Rotate(180);
		}
		else if($id_bollettino=='uno')
		{
			$this->SetXY(10, -102+54);
		}
	
		$this->Cell(67,4,$riga2);
	
		if($id_bollettino=='due')
		{
			$this->StopTransform();
			$this->SetXY(-10, 102-58);
			$this->StartTransform();
			$this->Rotate(180);
		}
		else if($id_bollettino=='uno')
		{
			$this->SetXY(10, -102+58);
		}
		
		$this->Cell(67,4,$riga3);
		
		if($id_bollettino=='due')
		{
			$this->StopTransform();
		}
		
		if($id_bollettino=='due')
		{
			$this->SetXY(-192, 102-50);
			$this->StartTransform();
			$this->Rotate(180);
		}
		else if($id_bollettino=='uno')
		{
			$this->SetXY(192, -102+50);
		}
		
		$this->Cell(95,4,$riga1);
		
		if($id_bollettino=='due')
		{
			$this->StopTransform();
			$this->SetXY(-192, 102-54);
			$this->StartTransform();
			$this->Rotate(180);
		}
		else if($id_bollettino=='uno')
		{
			$this->SetXY(192, -102+54);
		}
		
		$this->Cell(95,4,$riga2);
		
		if($id_bollettino=='due')
		{
			$this->StopTransform();
			$this->SetXY(-192, 102-58);
			$this->StartTransform();
			$this->Rotate(180);
		}
		else if($id_bollettino=='uno')
		{
			$this->SetXY(192, -102+58);
		}
		
		$this->Cell(95,4,$riga3);
		
		if($id_bollettino=='due')
		{
			$this->StopTransform();
		}
	}
	
	function logo_bollettino( $logo, $id_bollettino = 'uno' )
	{
		$dim_img = limita_dim_immagine($logo, 20, 11);
				
		if($id_bollettino=='uno')
		{
			$this->SetXY(10, 210-102 + 5.5 + (5.5-$dim_img[1]/2));
		}
		else if($id_bollettino=='due')
		{
			$this->SetXY(297-10, 102 - 5.5 - (5.5-$dim_img[1]/2));
			$this->StartTransform();
			$this->Rotate(180);
		}
		
		$this->Image($logo,'', '' ,$dim_img[0]);
		
		if($id_bollettino=='due')
		{
			$this->StopTransform();
		}
	}
	
	public function autorizzazione_bollettino( $testo, $id_bollettino = 'uno' )
	{
		$this->SetFont('ocrb','','6');
		if($id_bollettino=='uno')
		{
			$this->SetXY(-5, -19);
			$this->StartTransform();
			$this->Rotate(90);
		}
		else if($id_bollettino=='due')
		{
			$this->SetXY(5, 19);
			$this->StartTransform();
			$this->Rotate(-90);
		}
		
		
		$this->Cell(79,4,$testo,0,0,'C');
		$this->StopTransform();
	}
	
	public function stampa_provvisoria()
	{
		$x = $this->GetX();
		$y = $this->GetY();
		$size = $this->getFontSize();
		$font = $this->getFontFamily();
		
		if($this->CurOrientation=="P")
			$this->SetXY(60, 200);
		else if($this->CurOrientation=="L")
			$this->SetXY(93, 155);
		
		$this->StartTransform();
		$this->Rotate(50);
		$this->SetFont('Helvetica', '', 32);
		$this->SetTextColor(190);
		$this->Cell('130','','STAMPA PROVVISORIA',0,0,'C');
		$this->StopTransform();
		
		$this->SetFont($font, '', $size);
		$this->SetTextColor(0);
		$this->SetXY($x, $y);
	}
	
	public function intestazione_pdf($tipo_gestore , $image_file, $intest_gestore, $intest_ufficio )
	{
		$this->SetMargins(0, 0, 0);	$this->ln(0);
		$this->SetLineWidth(0.2);
		$this->Line(7, 8, 203, 8);//Linea di testa
		
		$dim = limita_dim_immagine($image_file, 18, 22);
		$offsetx = 7 + (20-$dim[0])/2;
		$offsety = 8 + (26-$dim[1])/2;
// 		$this->Image($image_file, $offsetx, 6+$offsety, $dim[0], $dim[1],'','','C' );//Logo

		$this->Image($image_file, $offsetx, $offsety, $dim[0], $dim[1],'','','C' );//Logo
		
		//GESTORE
		$this->SetMargins(28.0, 10.0, 7.0);	$this->ln(0);
		
		$this->SetXY(28, 10);
		$this->SetFont('Arial', 'B', 7);
		$this->Cell (85.0, 0, $intest_gestore['Riga1'], 0, 1, "L");
		$this->SetFont('Arial', '', 7);
		$this->Cell (85.0, 0, $intest_gestore['Riga2'], 0, 1, "L");
		$this->Cell (85.0, 0, $intest_gestore['Riga3'], 0, 1, "L");
		$this->Cell (85.0, 0, $intest_gestore['Riga4'], 0, 1, "L");
		$this->Cell (85.0, 0, $intest_gestore['Riga5'], 0, 1, "L");
		$this->Cell (85.0, 0, $intest_gestore['Riga6'], 0, 1, "L");
		$this->Cell (85.0, 0, $intest_gestore['Riga7'], 0, 1, "L");
			
		//UFFICIO
		$this->SetMargins(115.0, 10.0,7.0);	$this->ln(0);
		$this->SetXY( 115 , 10 );
		
		$this->SetFont('Arial', 'B', 7);
		$this->Cell (85.0, 0, $intest_ufficio['Riga1'], 0, 1, "L");
		$this->SetFont('Arial', '', 7);
		$this->Cell (85.0, 0, $intest_ufficio['Riga2'], 0, 1, "L");
		$this->Cell (85.0, 0, $intest_ufficio['Riga3'], 0, 1, "L");
		$this->Cell (85.0, 0, $intest_ufficio['Riga4'], 0, 1, "L");
		$this->Cell (85.0, 0, $intest_ufficio['Riga5'], 0, 1, "L");
		$this->Cell (85.0, 0, $intest_ufficio['Riga6'], 0, 1, "L");
		
		$this->SetLineWidth(0.2);
		$this->Line(7, 34, 203, 34);//Linea di chiusura
	}
	
	public function destinatario_intestazione_pdf($utente_id,$c,$nome_utente,$ID_partita,$anno_rif,$indirizzo_destinatario, $luogo_data, $tipo_codice = null, $protocollo=null, $data_protocollo = null )
	{
		if($tipo_codice == null)
			$tipo_codice = "UTENTE";
		else 
			$tipo_codice = strtoupper($tipo_codice);
		
		$array_utente = array('');
		if(is_array($nome_utente))
		{
			for($x_utente = 0;$x_utente<count($nome_utente);$x_utente++)
			{
				$array_utente[$x_utente] = $nome_utente[$x_utente];
			}
		}
		else if(isset($indirizzo_destinatario['Destinatario']))
			$array_utente[0] = $indirizzo_destinatario['Destinatario'];
		else 
			$array_utente[0] = $nome_utente;
		
		$this->SetFont('Arial', '', 7.8);
		
		//LUOGO E DATA
		$this->SetXY( 7 , 52 );
        $this->Cell ( 35 , 5, "CODICE ".$tipo_codice.":", 0, 0, "L");
        $this->Cell ( 81 , 5, $utente_id." / ".$c, 0, 0, "L");
		$this->Cell (90, 5, $luogo_data, 0, 1, "L");//Data e luogo
        $this->SetXY( 7 , 57 );
        if($ID_partita!="")
        {
            $this->Cell (35, 5, "PARTITA NUMERO :" ,0, 0, "L");
            $this->Cell (60 , 5, $ID_partita." / ".$anno_rif, 0, 0, "L");
        }

        $this->SetXY( 7 , 62 );
        if($protocollo!="")
        {
            $this->Cell (35, 5, "PROTOCOLLO :" ,0, 0, "L");
            $this->Cell (60 , 5, $protocollo, 0, 0, "L");
        }
        else
            $this->Cell (95, 5, "", 0, 0, "L");//Data e luogo
		if($array_utente[0]!="")
			$this->Cell ( 18 , 5, "Spett.le", 0, 0, "R");		
		
		//DESTINATARIO 1
		$this->SetMargins(123.0, 62.0);	$this->Ln(0);
		$this->Cell (90, 5, $array_utente[0], 0, 1, "L");//Nome Destinatario
        $this->SetMargins(7.0, 10.0,7.0);	$this->Ln(0);
        if($protocollo!="")
        {
            $this->Cell (35, 5, "DEL :" ,0, 0, "L");
            $this->Cell (60 , 5, from_mysql_date($data_protocollo), 0, 0, "L");
        }

		//DESTINATARIO 2
		$this->SetMargins(123.0, 62.0);	$this->ln(0);
		for($x_utente = 1;$x_utente<count($array_utente);$x_utente++)
		{
			$this->Cell (90, 5, $array_utente[$x_utente] , 0, 1, "L");//Righe aggiuntive
		}
		
		if($indirizzo_destinatario!="")
		{
			$this->Cell (90, 5, $indirizzo_destinatario['Riga1'] , 0, 1, "L");//Indirizzo 1 Destinatario
			$this->Cell (90, 5, $indirizzo_destinatario['Riga2'] , 0, 1, "L");//Indirizzo 2 Destinatario
			$this->Cell (90, 5, $indirizzo_destinatario['Riga3'] , 0, 1, "L");//Cap Comune Destinatario
			$this->Cell (90, 5, $indirizzo_destinatario['Riga4'] , 0, 1, "L");//Stato Destinatario
		}
		else 
		{
			$this->Cell (90, 5, "" , 0, 1, "L");//Indirizzo 1 Destinatario
			$this->Cell (90, 5, "" , 0, 1, "L");//Indirizzo 2 Destinatario
			$this->Cell (90, 5, "" , 0, 1, "L");//Cap Comune Destinatario
			$this->Cell (90, 5, "" , 0, 1, "L");//Stato Destinatario
		}
		
		$this->Ln(10);
	}
	
	public function oggetto_pdf( $titolo , $sottotitolo , $primoTesto )
	{
		//OGGETTO
		$this->SetMargins(7.0, 10.0, 7.0);	$this->Ln(0);
		$this->SetFont('Arial', 'B', 9);
		$this->Cell(20, 0, "OGGETTO:" , 0, 0, 'L', 0, '', 0);
		$this->MultiCell(175, 0, $titolo."\n" , 0, 'J', 0, 1);
		
		$this->SetFont('Arial', '', 9);
		if($sottotitolo!="")
		{
			$this->Cell(20, 0, "" , 0, 0, 'L', 0, '', 0);
			$this->MultiCell(175, 0, $sottotitolo."\n" , 0, 'J', 0, 1);
		}
		
		if($primoTesto!="")
		{
			$this->Ln(2);
			$this->MultiCell(0, 0, $primoTesto , 0, 'L', 0, 1);
			$this->Ln(2);
		}	
		
	}
	
	public function firma_pdf($firma, $ctrl_testo = null)
	{
		$testo_firma = "F.to art. 3 comma 2 D.lgs. 39 del 12/02/1993";
		
		$xfirma = $this->GetX();
		$yfirma = $this->GetY()+3;

		if($firma[1]['firma']!="" && substr_count($firma[1]['firma'],' ')<=4)
		{
			$dim_1 = limita_dim_immagine($firma[1]['firma'], 60, 18);
			$offset = (60-$dim_1[0])/2;
			$this->Image($firma[1]['firma'], $xfirma+$offset, $yfirma, $dim_1[0], $dim_1[1],'','','C' );//Firma1
		}
		else 
		{
			$dim_1[0]= 0;
			$dim_1[1]= 0;
			$offset = "";
		}
		
		if(substr_count($firma[1]['firma'],' ')>4){
			$firma_testo_1_1 = strtoupper($firma[1]['nome']);
			if($ctrl_testo=="no")
				$firma_testo_1_2 = "";
			else
				$firma_testo_1_2 = $firma[1]['firma'];
		}			
		else{
			$firma_testo_1_1 = "";
			$firma_testo_1_2 = strtoupper($firma[1]['nome']);
		}
		
		if($firma[2]['firma']!="" && substr_count($firma[2]['firma'],' ')<=4)
		{
			$dim_2 = limita_dim_immagine($firma[2]['firma'], 60, 18);
			$offset2 = (60-$dim_2[0])/2;
			$this->Image($firma[2]['firma'], $xfirma+$offset2+122, $yfirma, $dim_2[0], $dim_2[1],'','','C' );//Firma2
		}
		else
		{
			$dim_2[0]= 0;
			$dim_2[1]= 0;
			$offset2 = "";
		}		
		
		if(substr_count($firma[2]['firma'],' ')>4){
			$firma_testo_2_1 = strtoupper($firma[2]['nome']);
			if($ctrl_testo=="no")
				$firma_testo_2_2 = "";
			else
				$firma_testo_2_2 = $firma[2]['firma'];
		}			
		else{
			$firma_testo_2_1 = "";
			$firma_testo_2_2 = strtoupper($firma[2]['nome']);
		}
		
		if(substr_count($firma[1]['firma'],' ')>4 && substr_count($firma[2]['firma'],' ')>4){
			$interlinea_1 = 3;
			$interlinea_2 = 3;
		}
		else{
			$interlinea_1 = 7;
			$interlinea_2 = 7;
		}		
		
		$this->Cell(60, 0, $firma[1]['intestazione'] , 0, 0, 'C', 0, '', 0);
		$this->Cell(62,0, "" , 0, 0,'C',0,'',0 );
		$this->Cell(60,0, $firma[2]['intestazione'] , 0, 1,'C',0,'',0 );
		
		$this->Ln($interlinea_1);
		$this->SetFont('Arial', '', 7);
		
		$this->Cell(60, 0, $firma_testo_1_1 , 0, 0, 'C', 0, '', 0);
		$this->Cell(62,0, "" , 0, 0,'C',0,'',0 );
		$this->Cell(60,0, $firma_testo_2_1 , 0, 1,'C',0,'',0 );
		
		$this->Ln($interlinea_2);
		$this->SetFont('Arial', '', 7);

        $x = $this->GetX();
        $y = $this->GetY();
		if(strlen($firma_testo_1_2)>45){
            $pos = intval(substr_count($firma_testo_1_2,' ')/2);
            $a_firma1 = $this->split($firma_testo_1_2,' ',$pos);
            $this->Cell(60, 0, $a_firma1[0] , 0, 1, 'C', 0, '', 0);
            $this->Cell(60, 0, $a_firma1[1] , 0, 1, 'C', 0, '', 0);
        }
        else
		    $this->Cell(60, 0, $firma_testo_1_2 , 0, 0, 'C', 0, '', 0);

        $this->SetXY( $x+60 , $y );
		$this->Cell(62,0, "" , 0, 0,'C',0,'',0 );

        if(strlen($firma_testo_2_2)>45){
            $pos = intval(substr_count($firma_testo_2_2,' ')/2);
            $a_firma2 = $this->split($firma_testo_1_2,' ',$pos);
            $this->Cell(60, 0, $a_firma2[0] , 0, 1, 'C', 0, '', 0);
            $this->Cell(122,0, "" , 0, 0,'C',0,'',0 );
            $this->Cell(60, 0, $a_firma2[1] , 0, 1, 'C', 0, '', 0);
        }
        else
            $this->Cell(60,0, $firma_testo_2_2 , 0, 1,'C',0,'',0 );

	}
	
	public function firma_destra($firma, $ctrl_testo = null)
	{
		$xfirma = $this->GetX();
		$yfirma = $this->GetY()+3;
		
		if($firma['firma']!="" && substr_count($firma['firma'],' ')<=4)
		{
			$dim_1 = limita_dim_immagine($firma['firma'], 60, 18);
			$offset = (60-$dim_1[0])/2;
			$this->Image($firma['firma'], $xfirma+$offset+122, $yfirma, $dim_1[0], $dim_1[1],'','','C' );
		}
		else
		{
			$dim_1[0]= 0;
			$dim_1[1]= 0;
			$offset = 0;
		}		
		
		if(substr_count($firma['firma'],' ')>4){
			$firma_testo_1_1 = strtoupper($firma['nome']);
			if($ctrl_testo=="no")
				$firma_testo_1_2 = "";
			else
				$firma_testo_1_2 = $firma['firma'];
			$interlinea_1 = 3;
			$interlinea_2 = 3;
		}
		else{
			$firma_testo_1_1 = "";
			$firma_testo_1_2 = strtoupper($firma['nome']);
			$interlinea_1 = 7;
			$interlinea_2 = 7;
		}
			
		$this->Cell(60, 0, '' , 0, 0, 'C', 0, '', 0);
		$this->Cell(62,0, "" , 0, 0,'C',0,'',0 );
		$this->Cell(60,0, $firma['intestazione'] , 0, 1,'C',0,'',0 );
	
		$this->Ln($interlinea_1);
		$this->SetFont('Arial', '', 7);
		$this->Cell(60, 0, '' , 0, 0, 'C', 0, '', 0);
		$this->Cell(62,0, "" , 0, 0,'C',0,'',0 );
		$this->Cell(60,0, $firma_testo_1_1 , 0, 1,'C',0,'',0 );
		
		$this->Ln($interlinea_2);
		$this->SetFont('Arial', '', 7);
		$this->Cell(60, 0, '' , 0, 0, 'C', 0, '', 0);
		$this->Cell(62,0, "" , 0, 0,'C',0,'',0 );

        if(strlen($firma_testo_1_2)>45){
            $pos = intval(substr_count($firma_testo_1_2,' ')/2);
            $a_firma1 = $this->split($firma_testo_1_2,' ',$pos);
            $this->Cell(60, 0, $a_firma1[0] , 0, 1, 'C', 0, '', 0);
            $this->Cell(60, 0, $a_firma1[1] , 0, 1, 'C', 0, '', 0);
        }
        else
            $this->Cell(60, 0, $firma_testo_1_2 , 0, 1, 'C', 0, '', 0);
	}
	
	public function firma_destra_senza_img($firma)
	{
		$this->Cell(60, 0, '' , 0, 0, 'C', 0, '', 0);
		$this->Cell(62,0, "" , 0, 0,'C',0,'',0 );
		$this->Cell(60,0, $firma['intestazione'] , 0, 1,'C',0,'',0 );
	
		$this->Ln(14);
		$this->SetFont('Arial', '', 7);
		$this->Cell(60, 0, '' , 0, 0, 'C', 0, '', 0);
		$this->Cell(62,0, "" , 0, 0,'C',0,'',0 );
		$this->Cell(60,0, strtoupper($firma['nome']) , 0, 1,'C',0,'',0 );
	}

	public function split($string,$needle,$nth){
        $max = strlen($string);
        $n = 0;
        for($i=0;$i<$max;$i++){
            if($string[$i]==$needle){
                $n++;
                if($n>=$nth){
                    break;
                }
            }
        }
        $arr[] = substr($string,0,$i);
        $arr[] = substr($string,$i+1,$max);

        return $arr;
    }
}

?>