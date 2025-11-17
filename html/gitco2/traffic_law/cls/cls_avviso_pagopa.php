<?php
/**
 * Classe di generazione dell'avviso di pagamento PagoPA variante CDS
 * @deprecated deprecata in favore di cls/avvisiPagoPA/AvvisoBase.php
 */
class cls_avviso_pagopa extends TCPDF{
    const YPOS_INFO = 120;
    const YPOS_INFOPAGAMENTO = 237;
    const YPOS_DATIPAGAMENTO = 459;
    const YPOS_BOLLETTINO = 625;
    
    const DATAMATRIX_INDIRIZZAMENTO_FASE = 'codfase=';
    const DATAMATRIX_COD_FASE_ACCETTAZIONE = 'NBPA';
    const DATAMATRIX_SEPARATORE = ';';
    const DATAMATRIX_ID = 1;
    const DATAMATRIX_VALORE_FINALE = 'A';
    
    const QRCODE_COD_IDENTIFICATIVO = 'PAGOPA';
    const QRCODE_VERSIONE = '002';
    const QRCODE_SEPARATORE = '|';
    const QRCODE_LUNG_IMPORTO = 11;
    
    const LUNG_CODICE_AVVISO = 18;
    const LUNG_CC  = 12;
    const LUNG_IMPORTO = 10;
    const LUNG_TIPODOC = 3;
    const LUNG_CAUSALE = 110;
    const LUNG_DATAMATRIX = 256;
    const FASE_PAGAMENTO = 'P1';
    
    const ERR_DATAMATRIX = 'Il contenuto del codice DATAMATRIX non rispetta la lunghezza di '.self::LUNG_DATAMATRIX.' caratteri. Contati %s caratteri. (%s)';
    
    const QRCODE_STYLE = array(
        'border' => false,
        'vpadding' => 0,
        'hpadding' => 0,
        'fgcolor' => array(0,0,0),
        'bgcolor' => false, //array(255,255,255)
        'module_width' => 3, // width of a single module in points
        'module_height' => 3 // height of a single module in points
    );
    const DATAMATRIX_STYLE = array(
        'border' => false,
        'vpadding' => 0,
        'hpadding' => 0,
        'fgcolor' => array(0,0,0),
        'bgcolor' => false, //array(255,255,255)
        'module_width' => 3, // width of a single module in points
        'module_height' => 3 // height of a single module in points
    );
    
    private function handle_error($message, ...$messageArgs)
    {
        $messageArgs = array_filter($messageArgs, function($v){return !is_null($v) && $v !== '';});
        $expectedArgs = preg_match_all('~[^%]%[bcdeEfFgGosuxX]~', $message);
        $messageArgs = array_pad($messageArgs, $expectedArgs, '?');
        
        $fullError = vsprintf($message, $messageArgs) ?: $message;
        
        trigger_error("<cls_avviso_pagopa> ERRORE -> $fullError", E_USER_WARNING);
        throw new Exception($fullError);
    }
    
    //https://business.poste.it/business/files/1476471333939/caratteristiche-del-bollettino.pdf
    //pg. 59
    private function sanitizePosteString($string){
        $string = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $string);
        //Bug 2809 rimosso "." dall'espressione regolare dei caratteri accettati in quanto TCPDF genera un datamatrix malformato
        //$string = preg_replace( "/[^\'\-\ &,.0123456789:ABCDEFGHIJKLMNOPQRSTUVWXYZ_abcdefghijklmnopqrstuvwxyz]/", " ", $string);
        $string = preg_replace( "/[^\'\-\ &,0123456789:ABCDEFGHIJKLMNOPQRSTUVWXYZ_abcdefghijklmnopqrstuvwxyz]/", " ", $string);
        return $string;
    }
    
    //https://business.poste.it/business/files/1476471333939/caratteristiche-del-bollettino.pdf
    //pg. 56-57-58
    private function buildDataMatrix($codiceAvviso, $cc, float $importo, $tipoDocBollettino, $codiceFiscaleEnte, $codiceFiscaleTrasgr, $nominativoTrasgr, $causaleVersamento){        
        $cc = str_pad($cc, self::LUNG_CC, 0, STR_PAD_LEFT);
        $importo = str_pad(str_replace('.', '', number_format($importo, 2)), self::LUNG_IMPORTO, 0, STR_PAD_LEFT);
        
        $codeLine = self::LUNG_CODICE_AVVISO.$codiceAvviso.self::LUNG_CC.$cc.self::LUNG_IMPORTO.$importo.self::LUNG_TIPODOC.$tipoDocBollettino;
        
        $codiceFiscaleTrasgr = substr(str_pad($codiceFiscaleTrasgr ?: 'ANONIMO', 16, ' ', STR_PAD_RIGHT), 0, 16);
        $nominativoTrasgr = substr(str_pad($this->sanitizePosteString($nominativoTrasgr) ?: 'ANONIMO', 40, ' ', STR_PAD_RIGHT), 0 , 40);
        $causaleVersamento = substr(str_pad($this->sanitizePosteString($causaleVersamento), 110, ' ', STR_PAD_RIGHT), 0, 110);
        
        $filler = str_repeat(' ', 12);
        
        $dataMatrixContent = 
            self::DATAMATRIX_INDIRIZZAMENTO_FASE.
            self::DATAMATRIX_COD_FASE_ACCETTAZIONE.
            self::DATAMATRIX_SEPARATORE.
        strtoupper(
            $codeLine.
            self::DATAMATRIX_ID.
            self::FASE_PAGAMENTO.
            $codiceFiscaleEnte.
            $codiceFiscaleTrasgr.
            $nominativoTrasgr.
            $causaleVersamento.
            $filler.
            self::DATAMATRIX_VALORE_FINALE);
        
        if(strlen($dataMatrixContent) != self::LUNG_DATAMATRIX){
            $this->handle_error(self::ERR_DATAMATRIX, strlen($dataMatrixContent), $dataMatrixContent);
        }
        
        return $dataMatrixContent;
    }
    
    public function larghezzaDoc(){
        $dimensions = [
            //unità di default (PDF_UNIT) in millimetri
            "margins"   => $this->GetMargins(),
            "width"     => $this->getPageWidth(),
        ];
        return $dimensions["width"] - ($dimensions["margins"]["left"] + $dimensions["margins"]["right"]);
    }
    
    //<CODICE IDENTIFICATIVO>|<VERSIONE>|<CODICE AVVISO>|<CODICE FISCALE ENTE CREDITORE>|<IMPORTO>
    public static function buildQRCode($codiceAvviso, $codiceFiscaleEnte, float $importo){
        $importo = str_pad(str_replace('.', '', number_format($importo, 2)), self::QRCODE_LUNG_IMPORTO, 0, STR_PAD_LEFT);
        return implode(self::QRCODE_SEPARATORE, array(self::QRCODE_COD_IDENTIFICATIVO, self::QRCODE_VERSIONE, $codiceAvviso, $codiceFiscaleEnte, $importo));
    }
    
    public function __construct(){
        parent::__construct('P', 'pt', 'A4', true, 'UTF-8', false, true);
        $this->setPrintHeader(false);
        $this->setPrintFooter(false);
        $this->SetMargins(29, 29, 29, true);
        $this->SetAutoPageBreak(false, 0);
        $this->setCellPaddings(0,0,0,0);
        $this->AddPage();
    }
    
    public function aggiungiIntestazione($oggettoAvviso, $percorsoLogoEnte){
        $dimLogo = 85;
        $this->SetFillColorArray(array(255,0,0));
        $this->ImageSVG(IMG.'/pagopa/LogoPagoPA.svg');
        $this->setRTL ( true );
        $this->Image($percorsoLogoEnte, '', '', $dimLogo, $dimLogo);
        $this->setRTL ( false );
        $this->SetFont('titilliumweb', 'B', 10);
        $this->MultiCell($this->larghezzaDoc()-$dimLogo, 0,'AVVISO DI PAGAMENTO', 0, 'L', false, 1, 73, '');
        $this->SetFont('titilliumweb', 'B', 16);
        $this->MultiCell($this->larghezzaDoc()-$dimLogo, 0, $oggettoAvviso, 0, 'L', false, 1, '', 73);
    }
    
    public function aggiungiInfoEnte($codiceFiscaleEnte, $nomeEnte, $settoreEnte, $infoEnte){
        $YPOS = self::YPOS_INFO;
        
        $this->Rect('', $YPOS, 296, 20, 'DF', array('all' => 0), array(0,0,0,7));
        $this->SetFont('titilliumweb', 'B', 10);
        $this->MultiCell(0, 0,'ENTE CREDITORE', 0, 'L', false, 1, '', $YPOS+4);
        $this->setRTL ( true );
        $this->SetFont('robotomono', '', 8);
        $this->MultiCell(0, 0,$codiceFiscaleEnte, 0, 'R', false, 1, 310, $YPOS+5);
        $this->setRTL ( false );
        $this->SetFont('titilliumweb', 'B', 14);
        $this->writeHTMLCell(0, 0, '', $YPOS+37, $nomeEnte, 0, 1);
        $this->SetFont('titilliumweb', '', 12);
        $this->writeHTMLCell(0, 0, '', $YPOS+67, $settoreEnte, 0, 1);
        $this->SetFont('titilliumweb', '', 8);
        $this->writeHTMLCell(296-$this->getMargins()['left'], 0, '', $YPOS+92, $infoEnte, 0, 1);
    }
    
    public function aggiungiInfoDestinatario($codiceFiscaleTrasgr, $nominativoTrasgr, $indirizzoTrasgr){
        $YPOS = self::YPOS_INFO;
        
        $this->Rect(300, $YPOS, 296, 20, 'DF', array('all' => 0), array(0,0,0,7));
        $this->SetFont('titilliumweb', 'B', 10);
        $this->MultiCell(0, 0,'DESTINATARIO', 0, 'L', false, 1, 319, $YPOS+4);
        $this->setRTL ( true );
        $this->SetFont('robotomono', '', 8);
        $this->MultiCell(0, 0,$codiceFiscaleTrasgr, 0, 'R', false, 1, '', $YPOS+5);
        $this->setRTL ( false );
        $this->SetFont('titilliumweb', 'B', 14);
        $this->MultiCell(0, 0,$nominativoTrasgr, 0, 'L', false, 1, 319, $YPOS+37);
        $this->SetFont('titilliumweb', '', 12);
        $this->MultiCell(0, 0,$indirizzoTrasgr, 0, 'L', false, 1, 319, $YPOS+68);
    }
    
    public function aggiungiInfoPagamento(float $importo, $infoImporto, $nomeEnte){
        $YPOS = self::YPOS_INFOPAGAMENTO;
        
        $this->Rect('', $YPOS, 296, 20, 'DF', array('all' => 0), array(0,0,0,7));
        $this->SetFont('titilliumweb', 'B', 10);
        $this->MultiCell(0, 0,'QUANTO E QUANDO PAGARE?', 0, 'L', false, 1, '', $YPOS+4);
        $this->setY($YPOS+38);
        $this->SetFont('titilliumweb', 'B', 16);
        $this->writeHTML(number_format($importo, 2, ',', ''), 0);
        $this->setX($this->GetX()+4);
        $this->writeHTML('Euro', 0);
        $this->SetFont('titilliumweb', '', 8);
        $this->writeHTMLCell(156, 0, $this->GetX()+16, '', "<strong>$infoImporto</strong> dalla notifica del verbale", 0, 1);
        $this->ImageSVG(IMG.'/pagopa/Info.svg', '', $YPOS+74);
        $this->writeHTMLCell(246, 0, $this->GetX()+19, $YPOS+74, "Scegli l'importo corretto per i termini previsti, altrimenti riceverai altre
        richieste di pagamento per le somme residue ed eventuali somme
        aggiuntive.", 0, 1);
        $this->Image(IMG.'/pagopa/Calendar.png', '', $YPOS+112, 12, 12);
        $this->SetFont('titilliumweb', 'B', 8);
        $this->writeHTMLCell(246, 0, $this->GetX()+19, $YPOS+112, 'Come si calcolano le scadenze?', 0, 1);
        $this->SetFont('titilliumweb', '', 8);
        $this->writeHTMLCell(246, 0, $this->GetX()+19, '', 'Il giorno della notifica non si conta. Se la scadenza è di domenica
        (o festivo), puoi pagare anche il primo giorno feriale successivo.', 0, 1);
        $this->Image(IMG.'/pagopa/Giacenza.png', '', $YPOS+152, 12, 12);
        $this->SetFont('titilliumweb', 'B', 8);
        $this->writeHTMLCell(246, 0, $this->GetX()+19, $YPOS+152, 'Cosa succede in caso di giacenza?', 0, 1);
        $this->SetFont('titilliumweb', '', 8);
        $this->writeHTMLCell(246, 0, $this->GetX()+19, '', 'La notifica corrisponde al giorno del ritiro, se questo avviene entro 10
        giorni dalla data di deposito (la data più vecchia sull’avviso di giacenza).
        Se non ritiri il verbale entro i 10 giorni, calcola le scadenze iniziando a
        contare dall’undicesimo giorno.', 0, 1);
        
        $this->Rect(300, $YPOS, 296, 20, 'DF', array('all' => 0), array(0,0,0,7));
        $this->SetFont('titilliumweb', 'B', 10);
        $this->MultiCell(0, 0,'DOVE PAGARE?', 0, 'L', false, 1, 319, $YPOS+4);
        $this->setRTL ( true );
        $this->setY($YPOS+4);
        $this->SetFont('titilliumweb', 'B', 10);
        $this->writeHTML('pagopa.gov.it', 0);
        $this->setX($this->GetX()+3);
        $this->SetFont('titilliumweb', '', 8);
        $this->writeHTML('Vai su', 1);
        $this->setRTL ( false );
        $this->SetFont('titilliumweb', 'B', 10);
        $this->writeHTMLCell(206, 0, 319, $YPOS+35, 'PAGA CON L\'APP IO', 0, 1);
        $this->SetFont('titilliumweb', '', 8);
        $this->writeHTMLCell(206, 0, 319, '', "oppure sul sito di $nomeEnte, dal tuo Home Banking,
        con la tua app di pagamento o con gli altri canali abilitati.", 0, 1);
        $this->ImageSVG(IMG.'/pagopa/Dispositivi.svg', 538, $YPOS+36);
        $this->SetFont('titilliumweb', 'B', 10);
        $YPOS2 = $this->GetY();
        $this->writeHTMLCell(206, 0, 319, $YPOS2+14, 'PAGA SUL TERRITORIO', 0, 1);
        $this->SetFont('titilliumweb', '', 8);
        $this->writeHTMLCell(206, 0, 319, '', 'presso Banche e Sportelli ATM, negli Uffici Postali e Punti
        Postali, nei Bar, Edicole, Ricevitorie, Supermercati,
        Tabaccherie e altri Esercenti Convenzionati.', 0, 1);
        $this->ImageSVG(IMG.'/pagopa/PuntiFisici.svg', 538, $YPOS2+14);
    }
    
    public function aggiungiDatiPagamento($infoImporto, $cbill, $codiceAvviso, float $importo, $codiceFiscaleEnte, $nominativoTrasgr, $nomeEnte, $oggettoAvviso){
        $YPOS = self::YPOS_DATIPAGAMENTO;
        $evidenzia = false;
        
        if(empty($codiceAvviso)){
            $codiceAvviso = '000000000000000000';
            $evidenzia = true;
        }
        
        $this->Rect('', $YPOS, $this->getPageWidth(), 20, 'DF', array('all' => 0), array(0,0,0,7));
        $this->SetFont('titilliumweb', 'B', 10);
        $this->MultiCell(0, 0,'DATI PER IL PAGAMENTO', 0, 'L', false, 1, '', $YPOS+4);
        $this->setRTL ( true );
        $this->setY($YPOS+4);
        $this->SetFont('titilliumweb', '', 10);
        $this->writeHTML('dalla notifica del verbale', 0);
        $this->setX($this->GetX()+3);
        $this->SetFont('titilliumweb', 'B', 10);
        $this->writeHTML($infoImporto, 1);
        $this->setRTL ( false );
        $this->write2DBarcode($this->buildQRCode($codiceAvviso, $codiceFiscaleEnte, $importo), 'QRCODE,M', '', $YPOS+41, 70, 70, self::QRCODE_STYLE, 'N');
        $this->SetFont('titilliumweb', '', 8);
        $this->writeHTMLCell('', '', '', $YPOS+122, 'Inquadra il <strong>codice QR</strong> con la tua app di<br>pagamento o usa i dati accanto.', 0, 1);
        $this->writeHTMLCell('', '', 195, $YPOS+37, "Destinatario<br><strong>$nominativoTrasgr</strong>", 0, 1);
        $this->writeHTMLCell('', '', 195, $YPOS+62, "Ente Creditore<br><strong>$nomeEnte</strong>", 0, 1);
        $this->writeHTMLCell('', '', 195, $YPOS+87, "Oggetto del pagamento<br><strong>$oggettoAvviso</strong>", 0, 1);
        $this->MultiCell(0, 0,'Cod. CBILL', 0, 'L', false, 1, 195, $YPOS+123);
        $this->MultiCell(0, 0,'Cod. Avviso', 0, 'L', false, 1, 277, $YPOS+123);
        $this->SetFont('robotomono', 'B', 10);
        $this->MultiCell(0, 0,$cbill, 0, 'L', false, 1, 195, $YPOS+136);
        $this->SetFillColorArray($evidenzia ? array(255,0,0) : array(255,255,255));
        $this->MultiCell(140, 0,chunk_split($codiceAvviso, 4, ' '), 0, 'L', $evidenzia, 1, 277, $YPOS+136);
        $this->SetFillColorArray(array(255,255,255));
        $this->setRTL ( true );
        $this->setY($YPOS+34);
        $this->SetFont('robotomono', 'B', 12);
        $this->writeHTML(number_format($importo, 2, ',', ''), 0, false);
        $this->setX($this->GetX()+5);
        $this->SetFont('titilliumweb', '', 10);
        $this->writeHTML('Euro', 1);
        $this->SetFont('titilliumweb', '', 8);
        $this->MultiCell(0, 0,'Cod. Fiscale Ente', 0, 'R', false, 1, '', $YPOS+123);
        $this->SetFont('robotomono', 'B', 10);
        $this->MultiCell(0, 0,$codiceFiscaleEnte, 0, 'R', false, 1, '', $YPOS+136);
        $this->setRTL ( false );
    }
    
    public function aggiungiBollettino($infoImporto, $autorizzazione, $cc, $ccIntestatario, $nominativoTrasgr, $codiceFiscaleTrasgr, $oggettoPagamento, $codiceAvviso, $codiceFiscaleEnte, float $importo, $tipoBollettino, $causaleVersamento){
        $YPOS = self::YPOS_BOLLETTINO;
        $evidenzia = false;
        
        if(empty($codiceAvviso)){
            $codiceAvviso = '000000000000000000';
            $evidenzia = true;
        }
        
        $this->ImageSVG(IMG.'/poste/Scissors.svg', 547, $YPOS);
        $this->Line(0, $YPOS+10, $this->getPageWidth(), $YPOS+10, array('width' => 0.8, 'color' => array(0, 0, 0, 47)));
        $this->Rect(0, $YPOS+11, $this->getPageWidth(), 18, 'DF', array('all' => 0), array(0,0,0,7));
        $this->SetFont('titilliumweb', 'B', 10);
        $this->MultiCell(0, 0,'BOLLETTINO POSTALE PA', 0, 'L', false, 1, '', $YPOS+14);
        $this->ImageSVG(IMG.'/poste/BancoPosta.svg', 179, $YPOS+15);
        $this->setRTL ( true );
        $this->setY($YPOS+13);
        $this->SetFont('titilliumweb', '', 10);
        $this->writeHTML('dalla notifica del verbale', 0);
        $this->setX($this->GetX()+3);
        $this->SetFont('titilliumweb', 'B', 10);
        $this->writeHTML($infoImporto, 1);
        $this->setRTL ( false );
        $this->ImageSVG(IMG.'/poste/PosteGroupIcons.svg', $this->GetX()+3, $YPOS+64);
        $this->ImageSVG(IMG.'/poste/PosteItaliane.svg', $this->GetX()+3, $YPOS+65);
        $this->SetFont('titilliumweb', '', 8);
        $this->writeHTMLCell(120, 0, $this->GetX()+2, $YPOS+131, 'Bollettino Postale pagabile in tutti
        gli Uffici Postali e sui canali fisici o
        digitali abilitati di Poste Italiane e
        dell’Ente Creditore', 0, 1);
        $this->SetFont('titilliumweb', '', 6);
        $this->SetTextColor(0,0,0,56);
        $this->MultiCell(0, 0, $autorizzazione, 0, 'L', false, 1, $this->GetX()+2, $YPOS+175);
        $this->SetTextColor(0,0,0);
        $this->SetFillColor(0,255,0);
        $this->SetFont('titilliumweb', '', 10);
        $this->writeHTMLCell(45, 0, 206, $YPOS+68, 'sul C/C n.', 0, 0);
        $this->SetFont('robotomono', 'B', 12);
        $this->writeHTMLCell(200, 0, $this->GetX(), $YPOS+66, $cc, 0, 1);
        $this->SetFont('titilliumweb', '', 8);
        $this->writeHTMLCell(0, 0, 179.5, $YPOS+101, "Intestato a  <strong>".strtoupper($ccIntestatario)."</strong>", 0, 1);
        //Da doc poste: Saranno stampate sul Bollettino PA esclusivamente i primi 35 caratteri
        $this->writeHTMLCell(0, 0, 179.5, $this->GetY()+8, "Destinatario  <strong>".strtoupper(substr($nominativoTrasgr, 0, 35))."</strong>", 0, 1);
        //Da doc poste: Saranno stampate sul Bollettino PA esclusivamente i primi 60 caratteri
        $this->writeHTMLCell(0, 0, 179.5, $this->GetY()+8, "Oggetto pagamento  <strong>".strtoupper(substr($oggettoPagamento, 0, 60))."</strong>", 0, 1);
        $this->writeHTMLCell(149, 0, 179.5, $YPOS+159, 'Codice Avviso', 0, 0);
        $this->writeHTMLCell(20, 0, '', '', 'Tipo', 0, 0);
        $this->writeHTMLCell(108, 0, '', '', 'Cod. Fiscale Ente Creditore', 0, 1, false, true, 'R');
        $this->SetFont('robotomono', 'B', 10);
        $this->SetFillColorArray($evidenzia ? array(255,0,0) : array(255,255,255));
        $this->writeHTMLCell(150, 0, 179.5, $YPOS+167, $codiceAvviso, 0, 0, $evidenzia);
        $this->SetFillColorArray(array(255,255,255));
        $this->writeHTMLCell(20, 0, '', '', self::FASE_PAGAMENTO, 0, 0);
        $this->writeHTMLCell(107, 0, '', '', $codiceFiscaleEnte, 0, 1, false, true, 'R');
        $this->setRTL ( true );
        $this->SetFont('robotomono', 'B', 12);
        $this->writeHTMLCell(86, 0, '', $YPOS+66, number_format($importo, 2, ',', ''), 0, 0, false, true, 'R');
        $this->SetFont('titilliumweb', '', 10);
        $this->writeHTMLCell(30, 0, '', '', 'Euro', 0, 1, false, false, 'R');
        $this->setRTL ( false );
        
        $dataMatrix = $this->buildDataMatrix($codiceAvviso, $cc, $importo, $tipoBollettino, $codiceFiscaleEnte, $codiceFiscaleTrasgr, $nominativoTrasgr, $causaleVersamento);
        
        $this->write2DBarcode($dataMatrix, 'DATAMATRIX', 491, $YPOS+109, 70, 70, self::DATAMATRIX_STYLE, 'N');
    }
}