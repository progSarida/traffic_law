<?php 
class PagoPAERConst
{
    const SOAP_VERSION = SOAP_1_2;
    
    const KEYT_IUV = 'IUV';
    const KEYT_CDS = 'CDS';
    const KEYT_EXTRACDS = 'ExtraCDS';
    const KEYT_IMMAGINE = 'Immagine';
    
    const IMGT_HTML = 'HTML';
    const IMGT_PNG = 'PNG';
    const IMGT_WEBURL = 'WebUrl';

    const ESITI = array(
        1 => 'OK',
        2 => 'IMPORTO_NON_VALIDO',
        3 => 'PAGAMENTO_ANNULLATO',
        4 => 'PAGAMENTO_SCADUTO',
        5 => 'PAGAMENTO_CONCLUSO',
        6 => 'RICHIESTA_SCONOSCIUTA',
        7 => 'SYSTEM_ERROR',
        8 => 'PAGAMENTO_DUPLICATO',
        9 => 'NON_INVIATO',
        10 => 'RICONCILIAZIONE_IN_CORSO'
    );
    
    const SOGGETTO_TIPONATURAGIURIDICA = array(
        1 => 'AmministrazioniPubbliche',
        2 => 'Famiglie',
        3 => 'Imprese',
        4 => 'IstituzioniSocialiPrivate',
    );
    
    const SCORPORO_ALIQUOTAIVA = array(
        1 => 'IVA_22',
        2 => 'IVA_10',
        3 => 'IVA_4',
        4 => 'E01',
        5 => 'E02',
        6 => 'E03',
        7 => 'E04',
        8 => 'E05',
        9 => 'E06',
        10=> 'IVA_5'
    );
    
    const SCORPORO_CAUSALEIMPORTO = array(
        1 => 'Servizi',
        2 => 'Sanzioni',
        3 => 'Spese',
        4 => 'Bollo',
        5 => 'Interessi',
        6 => 'Arrotondamento',
        7 => 'DepositiCauzionali',
        8 => 'RimborsoDepositiCauzionali',
        9 => 'RimborsoServizi',
        10 => 'SpeseTenutaConto',
        11 => 'ImpostaRegistro',
        12 => 'Commissioni',
        13 => 'InteressiPassiviCCP',
        14 => 'SpeseDomiciliazione',
        15 => 'CommissioniBolloSpeseTenutaConto',
        16 => 'CommissioniSpeseTenutaConto',
        17 => 'Urgenza',
        18 => 'SanzioniInfedele',
        19 => 'SanzioniOmessa',
        20 => 'SanzioniLiquidazione',
        21 => 'Addizionali',
        22 => 'BolloACaricoEnte',
        23 => 'Sanzioni', //'CDS_SpeseDiAccertamento' sanzione pura
        24 => 'CDS_SpeseDiNotifica', //'CDS_SpeseDiNotifica', spese di notifica
        25 => 'Interessi', //'CDS_SpeseVarie', interessi
        26 => 'NotaIntegrativa'
    );
    
    const VOCE_COSTO_SANZIONE = 'CDS-TANG0101';
    const VOCE_COSTO_INTERESSI = 'CDS-TANG0102';
    const VOCE_COSTO_SPESE_NOTIFICA = 'CDS-TANG0103';
    const VOCE_COSTO_MAGG_SEMESTRALE = 'CDS-MAG6-10';
    
    const MODALITA_PAYMENTTYPEID = array( //TODO Verificare le mappature, per ora impostate a 9 (PagoPA)
        'CSCT' => 4,
        'CSBA' => 4,
        'CSCC' => 4,
        'PREP' => 9,
        'ONCC' => 9,
        'PT123' => 1,
        'PT451' => 1,
        'PT674' => 1,
        'PTBD' => 1,
        'PTBT' => 18,
        'PTBI' => 2,
        'PTBE' => 2,
        'BABO' => 15,
        'BAMA' => 15,
        'BARI' => 9,
        'LOTT' => 8,
        'COOP' => 9,
        'ESNT' => 9,
        'PTRI' => 9,
        'TESO' => 9,
        'SISL' => 9,
        'PAYPAL' => 3,
        'BAFR' => 9,
        'BAPO' => 9,
        'F24' => 9,
        'PPA1' => 9,
        'PPA3' => 9,
        'PPA1B' => 9,
        'PPA3B' => 9,
        'PRVI' => 9,
        'CVIS' => 4,
        'CMSC' => 4,
        'CMST' => 4,
        'PosPagoPA' => 4, //?9
        'MNTM' => 4,
        'RIBA' => 4,
        'WELAPP18' => 4,
        'WELCDD' => 4,
    );
    
    const STATUSTYPEID_PREINSERIMENTI = array(1,2);
    const STATUSTYPEID_ESCLUSICDS = array(1,2,33,34,35,36);
    const STATUSTYPEID_NONPAGABILE = array(30,32,35,36,37,40);
    const STATUSTYPEID_PAGAMENTO_ANNULLATO = array(32,33,34,35,36,37);
    const STATUSTYPEID_PAGAMENTO_CONCLUSO = 30;
    const STATUSTYPEID_VERBALE_CHIUSO = 32;
    
    const REGEX_DATI_VERBALE_CAUSALE = '/(\d+)\/(\d{4})\/([^\s]+)/';
}