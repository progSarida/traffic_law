<?php
require_once("AvvisoBase.php");

class ModelloBase extends AvvisoBase {
    const YPOS_INFOPAGAMENTO = 279;
    const YPOS_DATIPAGAMENTO = 440;
    const YPOS_INFO = 141;
    const YPOS_BOLLETTINO = 602;
    
    private $avviso;
    private $ente;
    private $destinatario;
    private $importo;
    private $bollettino;
    
    public function __construct(Avviso $avviso, Ente $ente, Destinatario $destinatario, Importo $importo){
        parent::__construct();
        $this->avviso = $avviso;
        $this->ente = $ente;
        $this->destinatario = $destinatario;
        $this->importo = $importo;
    }
    
    public function costruisci($pagBianche = true){
        $this->aggiungiIntestazione($this->avviso);
        $this->aggiungiInfoEnte($this->ente, self::YPOS_INFO);
        $this->aggiungiInfoDestinatario($this->destinatario, self::YPOS_INFO);
        $this->aggiungiInfoPagamento();
        $this->aggiungiDatiPagamento();
        if($this->bollettino instanceof Bollettino) $this->aggiungiBollettino($this->bollettino);
        if($pagBianche) $this->AddPage();
    }
    
    /**
     * @return Bollettino
     */
    public function getBollettino()
    {
        return $this->bollettino;
    }
    
    /**
     * @param Bollettino $bollettino
     */
    public function setBollettino(Bollettino $bollettino)
    {
        $this->bollettino = $bollettino;
    }
    
    public function aggiungiInfoPagamento(){
        $YPOS = self::YPOS_INFOPAGAMENTO;
        $XPOS = $this->getMargins()['left'];
        $X = '';
        
        $this->Rect('', $YPOS, 296, 20, 'DF', array('all' => 0), array(0,0,0,7));
        $this->SetFont('titilliumweb', 'B', 10);
        $this->MultiCell(0, 0,'QUANTO E QUANDO PAGARE?', 0, 'L', false, 1, $XPOS, $YPOS+4);
        $this->setY($YPOS+36);
        $this->SetFont('titilliumweb', 'B', 8);
        $this->writeHTML("Importo", 0);
        if(!empty($this->avviso->getDataScadenza())){
            $this->setX($X = $this->GetX()+140);
            $this->writeHTML('entro il', 0);
        }
        $this->setX($XPOS);
        $this->setY($YPOS+50);
        $this->SetFont('titilliumweb', 'B', 16);
        $this->writeHTML(number_format($this->importo->getImporto(), 2, ',', '.'), 0);
        $this->setX($this->GetX()+4);
        $this->writeHTML('Euro', 0);
        if(!empty($this->avviso->getDataScadenza())){
            $this->setX($X);
            $this->writeHTML($this->avviso->getDataScadenza(), 0);
        }
        $this->setX($XPOS);
        $this->ImageSVG("@".base64_decode(self::IMG_INFO), '', $YPOS+83);
        $this->SetFont('titilliumweb', '', 8);
        $this->writeHTMLCell(246, 0, $this->GetX()+19, $YPOS+83, "In fase di pagamento, se previsto dall’ente, l'importo potrebbe essere<br />
        aggiornato automaticamente e subire variazioni in diminuzione<br />
        (per sgravi, note di credito) o in aumento (per sanzioni, interessi, ecc.).", 0, 1);
        
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
        $this->writeHTMLCell(206, 0, 319, '', "oppure sul sito di {$this->ente->getNomeEnte()}, dal tuo Home Banking,
        con la tua app di pagamento o con gli altri canali abilitati.", 0, 1);
        $this->ImageSVG("@".base64_decode(self::IMG_DISPOSITIVI), 538, $YPOS+36);
        $this->SetFont('titilliumweb', 'B', 10);
        $YPOS2 = $this->GetY();
        $this->writeHTMLCell(206, 0, 319, $YPOS2+14, 'PAGA SUL TERRITORIO', 0, 1);
        $this->SetFont('titilliumweb', '', 8);
        $this->writeHTMLCell(206, 0, 319, '', 'presso Banche e Sportelli ATM, negli Uffici Postali e Punti
        Postali, nei Bar, Edicole, Ricevitorie, Supermercati,
        Tabaccherie e altri Esercenti Convenzionati.', 0, 1);
        $this->ImageSVG("@".base64_decode(self::IMG_PUNTI_FISICI), 538, $YPOS2+14);
    }
    
    public function aggiungiDatiPagamento(){
        $YPOS = self::YPOS_DATIPAGAMENTO;
        $evidenzia = false;
        
        if(empty($this->importo->getCodiceAvviso())){
            $codiceAvviso = '000000000000000000';
            $evidenzia = true;
        } else $codiceAvviso = $this->importo->getCodiceAvviso();
        
        $this->Rect('', $YPOS, $this->getPageWidth(), 20, 'DF', array('all' => 0), array(0,0,0,7));
        $this->SetFont('titilliumweb', 'B', 10);
        $this->MultiCell(0, 0,'DATI PER IL PAGAMENTO', 0, 'L', false, 1, '', $YPOS+4);
        $this->setRTL ( true );
        $this->setY($YPOS+4);
        $this->SetFont('titilliumweb', 'B', 10);
        $this->writeHTML('Rata unica'.(!empty($this->avviso->getDataScadenza()) ? ' entro il '.$this->avviso->getDataScadenza() : ''), 0);
        $this->setRTL ( false );
        $this->write2DBarcode($this->buildQRCode($codiceAvviso, $this->ente->getCfEnte(), $this->importo->getImporto()), 'QRCODE,M', '', $YPOS+41, 70, 70, self::QRCODE_STYLE, 'N');
        $this->SetFont('titilliumweb', '', 8);
        $this->writeHTMLCell('', '', '', $YPOS+122, 'Inquadra il <strong>codice QR</strong> con la tua app di<br>pagamento o usa i dati accanto.', 0, 1);
        $this->writeHTMLCell('', '', 195, $YPOS+37, "Destinatario<br><strong>{$this->destinatario->getNominativoDest()}</strong>", 0, 1);
        $this->writeHTMLCell('', '', 195, $YPOS+62, "Ente Creditore<br><strong>{$this->ente->getNomeEnte()}</strong>", 0, 1);
        $this->writeHTMLCell('', '', 195, $YPOS+87, "Oggetto del pagamento<br><strong>{$this->avviso->getOggettoAvviso()}</strong>", 0, 1);
        $this->MultiCell(0, 0,'Cod. CBILL', 0, 'L', false, 1, 195, $YPOS+123);
        $this->MultiCell(0, 0,'Cod. Avviso', 0, 'L', false, 1, 277, $YPOS+123);
        $this->SetFont('robotomono', 'B', 10);
        $this->MultiCell(0, 0, $this->ente->getCBill(), 0, 'L', false, 1, 195, $YPOS+136);
        $this->SetFillColorArray($evidenzia ? array(255,0,0) : array(255,255,255));
        $this->MultiCell(140, 0,chunk_split($codiceAvviso, 4, ' '), 0, 'L', $evidenzia, 1, 277, $YPOS+136);
        $this->SetFillColorArray(array(255,255,255));
        $this->setRTL ( true );
        $this->setY($YPOS+34);
        $this->SetFont('robotomono', 'B', 12);
        $this->writeHTML(number_format($this->importo->getImporto(), 2, ',', '.'), 0, false);
        $this->setX($this->GetX()+5);
        $this->SetFont('titilliumweb', '', 10);
        $this->writeHTML('Euro', 1);
        $this->SetFont('titilliumweb', '', 8);
        $this->MultiCell(0, 0,'Cod. Fiscale Ente', 0, 'R', false, 1, '', $YPOS+123);
        $this->SetFont('robotomono', 'B', 10);
        $this->MultiCell(0, 0, $this->ente->getCfEnte(), 0, 'R', false, 1, '', $YPOS+136);
        $this->setRTL ( false );
    }
    
    public function aggiungiBollettino(Bollettino $bollettino){
        $YPOS = self::YPOS_BOLLETTINO;
        $evidenzia = false;
        
        if(empty($this->importo->getCodiceAvviso())){
            $codiceAvviso = '000000000000000000';
            $evidenzia = true;
        } else $codiceAvviso = $this->importo->getCodiceAvviso();
        
        $this->ImageSVG("@".base64_decode(self::IMG_FORBICI), 547, $YPOS);
        $this->Line(0, $YPOS+10, $this->getPageWidth(), $YPOS+10, array('width' => 0.8, 'color' => array(0, 0, 0, 47)));
        $this->Rect(0, $YPOS+11, $this->getPageWidth(), 18, 'DF', array('all' => 0), array(0,0,0,7));
        $this->SetFont('titilliumweb', 'B', 10);
        $this->MultiCell(0, 0,'BOLLETTINO POSTALE PA', 0, 'L', false, 1, '', $YPOS+14);
        $this->ImageSVG("@".base64_decode(self::IMG_BANCOPOSTA), 179, $YPOS+15);
        $this->setRTL ( true );
        $this->setY($YPOS+13);
        $this->SetFont('titilliumweb', 'B', 10);
        $this->writeHTML('Rata unica'.(!empty($this->avviso->getDataScadenza()) ? ' entro il '.$this->avviso->getDataScadenza() : ''), 0);
        $this->setRTL ( false );
        $this->ImageSVG("@".base64_decode(self::IMG_POSTE_BOLLETTINO_ICONE), $this->GetX()+3, $YPOS+64);
        $this->ImageSVG("@".base64_decode(self::IMG_POSTE_ITALIANE), $this->GetX()+3, $YPOS+65);
        $this->SetFont('titilliumweb', '', 8);
        $this->writeHTMLCell(120, 0, $this->GetX()+2, $YPOS+131, 'Bollettino Postale pagabile in tutti
        gli Uffici Postali e sui canali fisici o
        digitali abilitati di Poste Italiane e
        dell’Ente Creditore', 0, 1);
        $this->SetFont('titilliumweb', '', 6);
        $this->SetTextColor(0,0,0,56);
        $this->MultiCell(0, 0, $bollettino->getAutorizzazione(), 0, 'L', false, 1, $this->GetX()+2, $YPOS+175);
        $this->SetTextColor(0,0,0);
        $this->SetFillColor(0,255,0);
        $this->SetFont('titilliumweb', '', 10);
        $this->writeHTMLCell(45, 0, 206, $YPOS+68, 'sul C/C n.', 0, 0);
        $this->SetFont('robotomono', 'B', 12);
        $this->writeHTMLCell(200, 0, $this->GetX(), $YPOS+66, $bollettino->getNumeroCc(), 0, 1);
        $this->SetFont('titilliumweb', '', 8);
        $this->writeHTMLCell(270, 0, 179.5, $YPOS+101, "Intestato a  <strong>".strtoupper($bollettino->getIntestatarioCc())."</strong>", 0, 1);
        //Da doc poste: Saranno stampate sul Bollettino PA esclusivamente i primi 35 caratteri
        $this->writeHTMLCell(270, 0, 179.5, $this->GetY()+8, "Destinatario  <strong>".strtoupper(substr($this->destinatario->getNominativoDest(), 0, 35))."</strong>", 0, 1);
        //Da doc poste: Saranno stampate sul Bollettino PA esclusivamente i primi 60 caratteri
        $this->writeHTMLCell(270, 0, 179.5, $this->GetY()+8, "Oggetto pagamento  <strong>".strtoupper(substr($this->avviso->getOggettoAvviso(), 0, 60))."</strong>", 0, 1);
        $this->writeHTMLCell(149, 0, 179.5, $YPOS+159, 'Codice Avviso', 0, 0);
        $this->writeHTMLCell(20, 0, '', '', 'Tipo', 0, 0);
        $this->writeHTMLCell(108, 0, '', '', 'Cod. Fiscale Ente Creditore', 0, 1, false, true, 'R');
        $this->SetFont('robotomono', 'B', 10);
        $this->SetFillColorArray($evidenzia ? array(255,0,0) : array(255,255,255));
        $this->writeHTMLCell(150, 0, 179.5, $YPOS+167, $codiceAvviso, 0, 0, $evidenzia);
        $this->SetFillColorArray(array(255,255,255));
        $this->writeHTMLCell(20, 0, '', '', self::FASE_PAGAMENTO, 0, 0);
        $this->writeHTMLCell(107, 0, '', '', $this->ente->getCfEnte(), 0, 1, false, true, 'R');
        $this->setRTL ( true );
        $this->SetFont('robotomono', 'B', 12);
        $this->writeHTMLCell(86, 0, '', $YPOS+66, number_format($this->importo->getImporto(), 2, ',', '.'), 0, 0, false, true, 'R');
        $this->SetFont('titilliumweb', '', 10);
        $this->writeHTMLCell(30, 0, '', '', 'Euro', 0, 1, false, false, 'R');
        $this->setRTL ( false );
        
        $dataMatrix = $this->buildDataMatrix($codiceAvviso, $bollettino->getNumeroCc(), $this->importo->getImporto(), $bollettino->getTipoBollettino(), $this->ente->getCfEnte(), $this->destinatario->getCfDest(), $this->destinatario->getNominativoDest(), $bollettino->getCausale());
        
        $this->write2DBarcode($dataMatrix, 'DATAMATRIX', 491, $YPOS+109, 70, 70, self::DATAMATRIX_STYLE, 'N');
    }
}