<?php
require_once("AvvisoBase.php");

class ModelloCDS extends AvvisoBase {
    const YPOS_INFOPAGAMENTO = 237;
    const YPOS_DATIPAGAMENTO1 = 480;
    const YPOS_DATIPAGAMENTO2 = 660;
    const YPOS_INFO = 120;
    const YPOS_BOLLETTINO = 640;
    
    private $avviso;
    private $ente;
    private $destinatario;
    private $importo1;
    private $importo2;
    private $bollettino;
    
    public function __construct(Avviso $avviso, Ente $ente, Destinatario $destinatario, Importo $importo1, Importo $importo2){
        parent::__construct();
        $this->avviso = $avviso;
        $this->ente = $ente;
        $this->destinatario = $destinatario;
        $this->importo1 = $importo1;
        $this->importo2 = $importo2;
    }
    
    public function costruisci($pagBianche = true){
        $this->aggiungiIntestazione($this->avviso);
        $this->aggiungiInfoEnte($this->ente, self::YPOS_INFO);
        $this->aggiungiInfoDestinatario($this->destinatario, self::YPOS_INFO);
        if($this->bollettino instanceof Bollettino) {
            $this->aggiungiInfoPagamento($this->importo1);
            $this->aggiungiDatiPagamento($this->importo1, self::YPOS_DATIPAGAMENTO1);
            $this->aggiungiBollettino($this->importo1, $this->bollettino);
            if($pagBianche) $this->AddPage();
            $this->AddPage();
            $this->aggiungiIntestazione($this->avviso);
            $this->aggiungiInfoEnte($this->ente, self::YPOS_INFO);
            $this->aggiungiInfoDestinatario($this->destinatario, self::YPOS_INFO);
            $this->aggiungiInfoPagamento($this->importo2);
            $this->aggiungiDatiPagamento($this->importo2, self::YPOS_DATIPAGAMENTO1);
            $this->aggiungiBollettino($this->importo2, $this->bollettino);
            
        } else {
            $this->aggiungiInfoPagamento($this->importo1, $this->importo2);
            $this->aggiungiDatiPagamento($this->importo1, self::YPOS_DATIPAGAMENTO1);
            $this->aggiungiDatiPagamento($this->importo2, self::YPOS_DATIPAGAMENTO2);
        }
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
    
    public function aggiungiInfoPagamento(Importo $importo1, ?Importo $importo2 = null){
        $YPOS = self::YPOS_INFOPAGAMENTO;
        $XPOS = $this->getMargins()['left'];
        $XPOS2 = 174;
        
        $this->Rect('', $YPOS, 296, 20, 'DF', array('all' => 0), array(0,0,0,7));
        $this->SetFont('titilliumweb', 'B', 10);
        $this->MultiCell(0, 0,'QUANTO E QUANDO PAGARE?', 0, 'L', false, 1, '', $YPOS+4);
        if($importo2){
            $this->setY($YPOS+36);
            $this->SetFont('titilliumweb', 'B', 8);
            $this->writeHTML($importo1->getNomeImporto(), 0);
            $this->setX($XPOS2);
            $this->writeHTML($importo2->getNomeImporto(), 0);
            $this->setY($YPOS+50);
            $this->setX($XPOS);
            $this->SetFont('titilliumweb', 'B', 16);
            $this->writeHTML(number_format($importo1->getImporto(), 2, ',', '.'), 0);
            $this->setX($this->GetX()+4);
            $this->writeHTML('Euro', 0);
            $this->setX($XPOS2);
            $this->writeHTML(number_format($importo2->getImporto(), 2, ',', '.'), 0);
            $this->setX($this->GetX()+4);
            $this->writeHTML('Euro', 0);
            $this->SetFont('titilliumweb', '', 8);
            $this->setY($YPOS+73);
            $this->writeHTMLCell(120, 0, $XPOS, '', "<strong>{$importo1->getDescImporto()}</strong><br />dalla notifica del verbale", 0, 1);
            $this->setY($YPOS+73);
            $this->writeHTMLCell(120, 0, $XPOS2, '', "<strong>{$importo2->getDescImporto()}</strong><br />dalla notifica del verbale", 0, 1);
        } else {
            $this->setY($YPOS+38);
            $this->SetFont('titilliumweb', 'B', 16);
            $this->writeHTML(number_format($importo1->getImporto(), 2, ',', '.'), 0);
            $this->setX($this->GetX()+4);
            $this->writeHTML('Euro', 0);
            $this->SetFont('titilliumweb', '', 8);
            $this->writeHTMLCell(156, 0, $this->GetX()+16, '', "<strong>{$importo1->getNomeImporto()}<br />{$importo1->getDescImporto()}</strong> dalla notifica del verbale", 0, 1);
            $this->ImageSVG("@".base64_decode(self::IMG_INFO), '', $YPOS+74);
            $this->writeHTMLCell(246, 0, $this->GetX()+19, $YPOS+74, "Scegli l'importo corretto per i termini previsti, altrimenti riceverai altre
            richieste di pagamento per le somme residue ed eventuali somme
            aggiuntive.", 0, 1);
        }

        $this->SetX($XPOS);
        $this->Image("@".base64_decode(self::IMG_CALENDARIO), '', $YPOS+112, 12, 12);
        $this->SetFont('titilliumweb', 'B', 8);
        $this->writeHTMLCell(246, 0, $this->GetX()+19, $YPOS+112, 'Come si calcolano le scadenze?', 0, 1);
        $this->SetFont('titilliumweb', '', 8);
        $this->writeHTMLCell(246, 0, $this->GetX()+19, '', 'Il giorno della notifica non si conta. Se la scadenza è di domenica
        (o festivo), puoi pagare anche il primo giorno feriale successivo.', 0, 1);
        $this->Image("@".base64_decode(self::IMG_GIACENZA), '', $YPOS+152, 12, 12);
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
        
        if($importo2){
            $this->ImageSVG("@".base64_decode(self::IMG_FRECCIAGIU), $XPOS-3, $YPOS+220);
            $this->ImageSVG("@".base64_decode(self::IMG_FRECCIAGIU), $this->larghezzaDoc()+17, $YPOS+220);
            $this->writeHTMLCell($this->larghezzaDoc(), 0, '', $YPOS+223, "Scegli l'importo corretto per i termini previsti, altrimenti riceverai altre richieste di pagamento per le somme residue ed eventuali somme aggiuntive.", 0, 1, false, true, 'C');
        }
    }
    
    public function aggiungiDatiPagamento(Importo $importo, float $YPos){
        $YPOS = $YPos;
        $evidenzia = false;
        
        if(empty($importo->getCodiceAvviso())){
            $codiceAvviso = '000000000000000000';
            $evidenzia = true;
        } else $codiceAvviso = $importo->getCodiceAvviso();
        
        $this->Rect('', $YPOS, $this->getPageWidth(), 20, 'DF', array('all' => 0), array(0,0,0,7));
        $this->SetFont('titilliumweb', 'B', 10);
        $this->MultiCell(0, 0, strtoupper($importo->getNomeImporto()), 0, 'L', false, 1, '', $YPOS+4);
        $this->setRTL ( true );
        $this->setY($YPOS+4);
        $this->SetFont('titilliumweb', '', 10);
        $this->writeHTML('dalla notifica del verbale', 0);
        $this->setX($this->GetX()+3);
        $this->SetFont('titilliumweb', 'B', 10);
        $this->writeHTML("Se paghi {$importo->getDescImporto()}", 1);
        $this->setRTL ( false );
        $this->write2DBarcode($this->buildQRCode($codiceAvviso, $this->ente->getCfEnte(), $importo->getImporto()), 'QRCODE,M', '', $YPOS+41, 70, 70, self::QRCODE_STYLE, 'N');
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
        $this->writeHTML(number_format($importo->getImporto(), 2, ',', '.'), 0, false);
        $this->setX($this->GetX()+5);
        $this->SetFont('titilliumweb', '', 10);
        $this->writeHTML('Euro', 1);
        $this->SetFont('titilliumweb', '', 8);
        $this->MultiCell(0, 0,'Cod. Fiscale Ente', 0, 'R', false, 1, '', $YPOS+123);
        $this->SetFont('robotomono', 'B', 10);
        $this->MultiCell(0, 0, $this->ente->getCfEnte(), 0, 'R', false, 1, '', $YPOS+136);
        $this->setRTL ( false );
    }
    
    public function aggiungiBollettino(Importo $importo, Bollettino $bollettino){
        $YPOS = self::YPOS_BOLLETTINO;
        $evidenzia = false;
        
        if(empty($importo->getCodiceAvviso())){
            $codiceAvviso = '000000000000000000';
            $evidenzia = true;
        } else $codiceAvviso = $importo->getCodiceAvviso();
        
        $this->ImageSVG("@".base64_decode(self::IMG_FORBICI), 547, $YPOS);
        $this->Line(0, $YPOS+10, $this->getPageWidth(), $YPOS+10, array('width' => 0.8, 'color' => array(0, 0, 0, 47)));
        $this->Rect(0, $YPOS+11, $this->getPageWidth(), 18, 'DF', array('all' => 0), array(0,0,0,7));
        $this->SetFont('titilliumweb', 'B', 10);
        $this->MultiCell(0, 0, "BOLLETTINO POSTALE PA", 0, 'L', false, 1, '', $YPOS+14);
        $this->ImageSVG("@".base64_decode(self::IMG_BANCOPOSTA), 179, $YPOS+15);
        $this->setRTL ( true );
        $this->setY($YPOS+13);
        $this->SetFont('titilliumweb', '', 10);
        $this->writeHTML('dalla notifica del verbale', 0);
        $this->setX($this->GetX()+3);
        $this->SetFont('titilliumweb', 'B', 10);
        $this->writeHTML("Se paghi {$importo->getDescImporto()}", 1);
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
        $this->writeHTMLCell(86, 0, '', $YPOS+66, number_format($importo->getImporto(), 2, ',', '.'), 0, 0, false, true, 'R');
        $this->SetFont('titilliumweb', '', 10);
        $this->writeHTMLCell(30, 0, '', '', 'Euro', 0, 1, false, false, 'R');
        $this->setRTL ( false );
        
        $dataMatrix = $this->buildDataMatrix($codiceAvviso, $bollettino->getNumeroCc(), $importo->getImporto(), $bollettino->getTipoBollettino(), $this->ente->getCfEnte(), $this->destinatario->getCfDest(), $this->destinatario->getNominativoDest(), $bollettino->getCausale());
        
        $this->write2DBarcode($dataMatrix, 'DATAMATRIX', 491, $YPOS+109, 70, 70, self::DATAMATRIX_STYLE, 'N');
    }
}