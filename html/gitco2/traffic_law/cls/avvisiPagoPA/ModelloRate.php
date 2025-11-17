<?php
require_once("AvvisoBase.php");

class ModelloRate extends AvvisoBase {
    const YPOS_INFOPAGAMENTO = 257;
    const YPOS_DATIPAGAMENTO = 397;
    const YPOS_INFO = 130;
    const YPOS_BOLLETTINO = 602;
    
    const ALTEZZA_PORZIONE_PAGAMENTO = 260;
    const ALTEZZA_BOLLETTINO = 180;
    
    const METODO_DIVISIONE_PER2 = 1;
    const METODO_DIVISIONE_PER3 = 2;
    
    //NOTA: l'ordine conta, es: 6 rate -> valuto prima se divisibile per 3 invece che 2, quindi usa lo spazio in modo più efficente
    const METODI_DIVISIONE = array(
        self::METODO_DIVISIONE_PER3 => 3,
        self::METODO_DIVISIONE_PER2 => 2,
    );
    
    private $avviso;
    private $ente;
    private $destinatario;
    private $importi;
    private $bollettini;
    private $metodoDiDivisone;
    private $pagineBianche;
    
    public function __construct(Avviso $avviso, Ente $ente, Destinatario $destinatario, Importi $importi){
        parent::__construct();
        $this->avviso = $avviso;
        $this->ente = $ente;
        $this->destinatario = $destinatario;
        $this->importi = $importi;
    }
    
    public function costruisci(bool $pagBianche = true){
        $this->pagineBianche = $pagBianche;
        $this->aggiungiIntestazione($this->avviso);
        $this->aggiungiInfoEnte($this->ente, self::YPOS_INFO);
        $this->aggiungiInfoDestinatario($this->destinatario, self::YPOS_INFO);
        $this->aggiungiInfoPagamento();
        $this->aggiungiDatiPagamento();
        if($this->pagineBianche) $this->AddPage();
    }
    
    /**
     * @return Bollettini
     */
    public function getBollettini(){
        return $this->bollettini;
    }
    
    /**
     * @param Bollettini $bollettini
     */
    public function setBollettini(Bollettini $bollettini){
        $this->bollettini = $bollettini;
    }
    
    /**
     * @param Importi $importi
     */
    private function calcolaMetodoDiDivisione(int $numero){
        foreach(self::METODI_DIVISIONE as $metodo => $divisore){
            if($numero % $divisore == 0) return $metodo;
        }
        return self::METODO_DIVISIONE_PER3;
    }
    
    private function raggruppaOggetti(ArrayObject $oggetti, int $raggruppaPer){
        $oggetti = $oggetti->getArrayCopy();
        return array_chunk($oggetti, $raggruppaPer);
    }
    
    public function aggiungiInfoPagamento(){
        $YPOS = self::YPOS_INFOPAGAMENTO;
        $XPOS = $this->getMargins()['left'];
        
        $this->Rect('', $YPOS, 296, 20, 'DF', array('all' => 0), array(0,0,0,7));
        $this->SetFont('titilliumweb', 'B', 10);
        $this->MultiCell(0, 0,'QUANTO E QUANDO PAGARE?', 0, 'L', false, 1, $XPOS, $YPOS+4);
        $this->setX($XPOS);
        $this->ImageSVG("@".base64_decode(self::IMG_INFO), '', $YPOS+38);
        $this->SetFont('titilliumweb', '', 8);
        $this->writeHTMLCell(246, 0, $this->GetX()+19, $YPOS+38, "Importi e scadenze sono riportati nel dettaglio di ogni singola rata.", 0, 1);
        $this->setX($XPOS);
        $this->ImageSVG("@".base64_decode(self::IMG_INFO), '', $YPOS+57);
        $this->SetFont('titilliumweb', '', 8);
        $this->writeHTMLCell(246, 0, $this->GetX()+19, $YPOS+57, "In fase di pagamento, se previsto dall’ente, l'importo potrebbe essere<br />
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
        
        $this->Rect('', $YPOS, $this->getPageWidth(), 20, 'DF', array('all' => 0), array(0,0,0,7));
        $this->SetFont('titilliumweb', 'B', 10);
        $this->MultiCell(0, 0,'DATI PER IL PAGAMENTO', 0, 'L', false, 1, '', $YPOS+4);
        
        $this->aggiungiPagamenti($this->importi, $YPOS+35);
        if($this->bollettini instanceof Bollettini) {
            $this->aggiungiBollettini($this->importi, $this->bollettini, $this->GetY());
        }
    }
    
    public function aggiungiPagamenti(Importi $importi, float $YPOS){
        $nRate = $importi->count();
        $contatoreRigaPagina = 0;
        $metodoDivisione = $this->calcolaMetodoDiDivisione($nRate);
        $divisore = self::METODI_DIVISIONE[$metodoDivisione];
        $righeImporti = $this->raggruppaOggetti($importi, $divisore);
        $margine = $this->GetMargins()['left'];
        $dimRiquadro = $this->getPageWidth()/$divisore;
        
        foreach($righeImporti as $rigaImporti){
            $YPOS += (self::ALTEZZA_PORZIONE_PAGAMENTO * $contatoreRigaPagina);
            
            //Se non c'è spazio sufficente per scrivere una nuova riga di qrcode, genero una nuova pagina
            if($this->GetY() + self::ALTEZZA_PORZIONE_PAGAMENTO > $this->getPageHeight()){
                $contatoreRigaPagina = 0;
                if($this->pagineBianche) $this->AddPage();
                $this->AddPage();
                $this->aggiungiIntestazione($this->avviso);
                $YPOS = $this->GetY()+35;
            }
            
            /** @var $importo Importo */
            foreach($rigaImporti as $indiceImporto => $importo){
                $evidenzia = false;
                
                if(empty($importo->getCodiceAvviso())){
                    $codiceAvviso = '000000000000000000';
                    $evidenzia = true;
                } else $codiceAvviso = $importo->getCodiceAvviso();
                $XScarto = ($indiceImporto > 0 ? 9*$indiceImporto : 0);
                $XPOS = $margine+($dimRiquadro*$indiceImporto)-$XScarto;
                
                $this->Rect($XPOS-9, $YPOS, $dimRiquadro-$margine+4, 20, 'DF', array('all' => 0), array(0,0,0,7));
                $this->SetFont('titilliumweb', 'B', 10);
                $this->MultiCell(0, 0,'Rata '.$importo->getNumeroRata(), 0, 'L', false, 1, $XPOS, $YPOS+4);
                $this->SetFont('titilliumweb', '', 8);
                $this->MultiCell(0, 0,'Euro', 0, 'L', false, 1, $XPOS, $YPOS+35);
                $this->SetFont('robotomono', 'B', 10);
                $this->MultiCell(0, 0,number_format($importo->getImporto(), 2, ',', '.'), 0, 'L', false, 1, $XPOS, $YPOS+47);
                $this->SetFont('titilliumweb', '', 8);
                $this->MultiCell(0, 0,'Entro il', 0, 'L', false, 1, $XPOS, $YPOS+65);
                $this->SetFont('robotomono', 'B', 10);
                $this->MultiCell(0, 0,$importo->getDataScadenza(), 0, 'L', false, 1, $XPOS, $YPOS+77);
                $this->write2DBarcode($this->buildQRCode($codiceAvviso, $this->ente->getCfEnte(), $importo->getImporto()), 'QRCODE,M', ($dimRiquadro*($indiceImporto+1))-$XScarto-90, $YPOS+35, 71, 71, self::QRCODE_STYLE, 'N');
                $this->SetFont('titilliumweb', '', 8);
                $this->MultiCell(0, 0,'Cod. Avviso', 0, 'L', false, 1, $XPOS, $YPOS+110);
                $this->SetFont('robotomono', 'B', 10);
                $this->SetFillColorArray($evidenzia ? array(255,0,0) : array(255,255,255));
                $this->MultiCell($dimRiquadro-$margine, 0,chunk_split($codiceAvviso, 4, ' '), 0, 'L', $evidenzia, 1, $XPOS, $YPOS+122);
                $this->SetFillColorArray(array(255,255,255));
                $this->SetFont('titilliumweb', '', 8);
                $this->MultiCell(0, 0,'Cod. Fiscale Ente', 0, 'L', false, 1, $XPOS, $YPOS+140);
                $this->MultiCell(0, 0,'Cod. CBILL', 0, 'L', false, 1, $XPOS+84, $YPOS+140);
                $this->SetFont('robotomono', 'B', 10);
                $this->MultiCell(0, 0,$this->ente->getCfEnte(), 0, 'L', false, 1, $XPOS, $YPOS+152);
                $this->MultiCell(0, 0,$this->ente->getCBill(), 0, 'L', false, 1, $XPOS+84, $YPOS+152);
                $this->SetFont('titilliumweb', '', 8);
                $this->MultiCell(0, 0,'Destinatario', 0, 'L', false, 1, $XPOS, $YPOS+170);
                $this->SetFont('titilliumweb', 'B', 8);
                $this->MultiCell(0, 0,$this->destinatario->getNominativoDest(), 0, 'L', false, 1, $XPOS, $YPOS+180);
                $this->SetFont('titilliumweb', '', 8);
                $this->MultiCell(0, 0,'Ente Creditore', 0, 'L', false, 1, $XPOS, $YPOS+195);
                $this->SetFont('titilliumweb', 'B', 8);
                $this->MultiCell(0, 0,$this->ente->getNomeEnte(), 0, 'L', false, 1, $XPOS, $YPOS+205);
                $this->SetFont('titilliumweb', '', 8);
                $this->MultiCell(0, 0,'Oggetto del pagamento', 0, 'L', false, 1, $XPOS, $YPOS+220);
                $this->SetFont('titilliumweb', 'B', 8);
                $this->MultiCell(0, 0,$this->avviso->getOggettoAvviso(), 0, 'L', false, 1, $XPOS, $YPOS+230);
                $this->SetFont('titilliumweb', '', 7);
                $this->writeHTMLCell(0, 0, $XPOS, $YPOS+245, "Inquadra il <strong>codice QR</strong> con la tua app o usa i dati sopra.", 0, 1);
            }
            
            $contatoreRigaPagina++;
        }
    }
    
    public function aggiungiBollettini(Importi $importi, Bollettini $bollettini, float $YPOS){
        $this->SetY($YPOS);
        
        foreach($bollettini as $indiceBollettino => $bollettino){
            $evidenzia = false;
            $YPOS = $this->GetY()+10;
            
            /** @var $importo Importo */
            $importo = $importi->offsetGet($indiceBollettino);
            
            //Se non c'è spazio sufficente per scrivere una nuova riga di qrcode, genero una nuova pagina
            if($this->GetY() + self::ALTEZZA_BOLLETTINO > $this->getPageHeight()){
                if($this->pagineBianche) $this->AddPage();
                $this->AddPage();
                $this->aggiungiIntestazione($this->avviso);
                $YPOS = $this->GetY()+35;
            }
            
            if(empty($importo->getCodiceAvviso())){
                $codiceAvviso = '000000000000000000';
                $evidenzia = true;
            } else $codiceAvviso = $importo->getCodiceAvviso();
            
            $this->ImageSVG("@".base64_decode(self::IMG_FORBICI), 547, $YPOS);
            $this->Line(0, $YPOS+10, $this->getPageWidth(), $YPOS+10, array('width' => 0.8, 'color' => array(0, 0, 0, 47)));
            $this->Rect(0, $YPOS+11, $this->getPageWidth(), 18, 'DF', array('all' => 0), array(0,0,0,7));
            $this->SetFont('titilliumweb', 'B', 10);
            $this->MultiCell(0, 0,'BOLLETTINO POSTALE PA', 0, 'L', false, 1, '', $YPOS+14);
            $this->ImageSVG("@".base64_decode(self::IMG_BANCOPOSTA), 179, $YPOS+15);
            $this->setRTL ( true );
            $this->setY($YPOS+13);
            $this->SetFont('titilliumweb', 'B', 10);
            $this->writeHTML("Rata {$importo->getNumeroRata()} entro il ".$importo->getDataScadenza(), 0);
            $this->setRTL ( false );
            $this->ImageSVG("@".base64_decode(self::IMG_POSTE_BOLLETTINO_ICONE), $this->GetX()+3, $YPOS+39);
            $this->ImageSVG("@".base64_decode(self::IMG_POSTE_ITALIANE), $this->GetX()+3, $YPOS+40);
            $this->SetFont('titilliumweb', '', 8);
            $this->writeHTMLCell(120, 0, $this->GetX()+2, $YPOS+106, 'Bollettino Postale pagabile in tutti
            gli Uffici Postali e sui canali fisici o
            digitali abilitati di Poste Italiane e
            dell’Ente Creditore', 0, 1);
            $this->SetFont('titilliumweb', '', 6);
            $this->SetTextColor(0,0,0,56);
            $this->MultiCell(0, 0, $bollettino->getAutorizzazione(), 0, 'L', false, 1, $this->GetX()+2, $YPOS+149);
            $this->SetTextColor(0,0,0);
            $this->SetFillColor(0,255,0);
            $this->SetFont('titilliumweb', '', 10);
            $this->writeHTMLCell(45, 0, 206, $YPOS+43, 'sul C/C n.', 0, 0);
            $this->SetFont('robotomono', 'B', 12);
            $this->writeHTMLCell(200, 0, $this->GetX(), $YPOS+41, $bollettino->getNumeroCc(), 0, 1);
            $this->SetFont('titilliumweb', '', 8);
            $this->writeHTMLCell(270, 0, 179.5, $YPOS+76, "Intestato a  <strong>".strtoupper($bollettino->getIntestatarioCc())."</strong>", 0, 1);
            //Da doc poste: Saranno stampate sul Bollettino PA esclusivamente i primi 35 caratteri
            $this->writeHTMLCell(270, 0, 179.5, $this->GetY()+8, "Destinatario  <strong>".strtoupper(substr($this->destinatario->getNominativoDest(), 0, 35))."</strong>", 0, 1);
            //Da doc poste: Saranno stampate sul Bollettino PA esclusivamente i primi 60 caratteri
            $this->writeHTMLCell(270, 0, 179.5, $this->GetY()+8, "Oggetto pagamento  <strong>".strtoupper(substr($this->avviso->getOggettoAvviso(), 0, 60))."</strong>", 0, 1);
            $this->writeHTMLCell(149, 0, 179.5, $YPOS+134, 'Codice Avviso', 0, 0);
            $this->writeHTMLCell(20, 0, '', '', 'Tipo', 0, 0);
            $this->writeHTMLCell(108, 0, '', '', 'Cod. Fiscale Ente Creditore', 0, 1, false, true, 'R');
            $this->SetFont('robotomono', 'B', 10);
            $this->SetFillColorArray($evidenzia ? array(255,0,0) : array(255,255,255));
            $this->writeHTMLCell(150, 0, 179.5, $YPOS+142, $codiceAvviso, 0, 0, $evidenzia);
            $this->SetFillColorArray(array(255,255,255));
            $this->writeHTMLCell(20, 0, '', '', self::FASE_PAGAMENTO, 0, 0);
            $this->writeHTMLCell(107, 0, '', '', $this->ente->getCfEnte(), 0, 1, false, true, 'R');
            $this->setRTL ( true );
            $this->SetFont('robotomono', 'B', 12);
            $this->writeHTMLCell(86, 0, '', $YPOS+41, number_format($importo->getImporto(), 2, ',', '.'), 0, 0, false, true, 'R');
            $this->SetFont('titilliumweb', '', 10);
            $this->writeHTMLCell(30, 0, '', '', 'Euro', 0, 1, false, false, 'R');
            $this->setRTL ( false );
            
            $dataMatrix = $this->buildDataMatrix($codiceAvviso, $bollettino->getNumeroCc(), $importo->getImporto(), $bollettino->getTipoBollettino(), $this->ente->getCfEnte(), $this->destinatario->getCfDest(), $this->destinatario->getNominativoDest(), $bollettino->getCausale());
            
            $this->write2DBarcode($dataMatrix, 'DATAMATRIX', 491, $YPOS+84, 70, 70, self::DATAMATRIX_STYLE, 'N');
        }
    }
}