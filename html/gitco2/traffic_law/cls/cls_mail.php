<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

require_once(INC.'/phpMailer/src/Exception.php');
require_once(INC.'/phpMailer/src/PHPMailer.php');
require_once(INC.'/phpMailer/src/SMTP.php');

class MAIL_HANDLER extends PHPMailer{
    
    const TIMEOUT = 10;
    
    const NO_RECIPIENTS = 'Nessun destinatario specificato';
    const NO_ATTACHEMENTS = 'Nessun allegato specificato';
    const INVALID_INDEXES = 'Il vettore non contiene tutti gli indici necessari';
    const INVALID_RECIPIENTS = 'Errore nei destinatari';
    const INVALID_ATTACHEMENTS = 'Errore negli allegati';
    const INVALID_SENDER = 'Errore nel mittente';
    const FAIL_CONNECTION = 'Connessione fallita';
    const FAIL_EHLO = 'EHLO fallito';
    const FAIL_ENCRYPTION = 'Inizializzazione cittografia fallita';
    const FAIL_AUTH = 'Autenticazione fallita';
    
    /**
     * mailbox url string
     */
    private $mailbox = "";
    
    /**
     * Required Data
     */
    private $requiredIndexes = array (
        'CityId',
        'MailAddress',
        'ShownName',
        'IncomingSecurity',
        'IncomingUserName',
        'IncomingPassword',
        'IncomingMailServer',
        'IncomingProtocol',
        'IncomingPort',
        'OutgoingSecurity',
        'OutgoingUserName',
        'OutgoingPassword',
        'OutgoingMailServer',
        'OutgoingProtocol',
        'OutgoingPort',
        'UseOutgoingAuthentication',
        'DisableAuthProperties',
    );
    
    /**
     * debug string
     */
    public $debug = '';
    /**
     * imap connection
     */
    public $imap = false;
    
    /**
     * shipment parameter
     */
    public $a_shipment = array();
    
    /**
     * currentfolder
     */
    public $folder = "INBOX";
    
///////////////////////////////////////////////////////////////////////

    public function __construct(Array $a_shipment, int $timeout = self::TIMEOUT){
        if (count(array_diff($this->requiredIndexes, array_keys($a_shipment))) == 0) {
            $this->SMTPDebug = 3;
            $this->Debugoutput = function($str, $level) {
                $this->debug = $str;
            };
            $this->a_shipment =                     $a_shipment;
            $this->Timeout =                        $timeout;
            $this->getSMTPInstance()->Timelimit =   $timeout;
            $this->setOutgoingParams();
        } else throw new Exception(self::INVALID_INDEXES);
    }

    private function setOutgoingParams() {
        $this->Host = $this->a_shipment['OutgoingMailServer'];
        $this->Port = $this->a_shipment['OutgoingPort'];
        if(strtolower($this->a_shipment['OutgoingProtocol'])=="smtp")
        {
            $this->IsSMTP();  // telling the class to use SMTP
            $this->SMTPSecure 	= $this->a_shipment['OutgoingSecurity'];
            if($this->a_shipment['UseOutgoingAuthentication'] == 1)
            {
                $this->SMTPAuth = true;
                $this->Username = $this->a_shipment['OutgoingUserName'];
                $this->Password = $this->a_shipment['OutgoingPassword'];
            }
        }
    }
    
    private function addRecipients(array $recipients){
        foreach ($recipients as $mail => $name){
            if(!$this->addAddress($mail, $name)) return false;
        }
        return true;
    }
    
    private function addAttachements(array $attachements){
        foreach ($attachements as $name => $path){
            if(!$this->addAttachment($path, $name)) return false;
        }
        return true;
    }
    
    private function getPart($imap, $uid, $mimetype, $structure = false, $partNumber = false)
    {
        if (!$structure) {
            $structure = imap_fetchstructure($imap, $uid, FT_UID);
        }
        if ($structure) {
            if ($mimetype == $this->getMimeType($structure)) {
                if (!$partNumber) {
                    $partNumber = 1;
                }
                $text = imap_fetchbody($imap, $uid, $partNumber, FT_UID | FT_PEEK);
                switch ($structure->encoding) {
                    case 3:
                        $extractedText = imap_base64($text);
                        break;
                    case 4:
                        $extractedText = imap_qprint($text);
                        break;
                    default:
                        $extractedText = $text;
                }
                
                foreach ($structure->parameters as $obj_param) {
                    if (($obj_param->attribute == "charset") && (mb_strtoupper($obj_param->value) != "UTF-8")) {
                        $extractedText = utf8_encode($extractedText);
                        break;
                    }
                }
                
                return $extractedText;
            }
            
            // multipart
            if ($structure->type == 1) {
                foreach ($structure->parts as $index => $subStruct) {
                    $prefix = "";
                    if ($partNumber) {
                        $prefix = $partNumber . ".";
                    }
                    $data = $this->getPart($imap, $uid, $mimetype, $subStruct, $prefix . ($index + 1));
                    if ($data) {
                        return $data;
                    }
                }
            }
        }
        return false;
    }
    
    private function getMimeType($structure)
    {
        $primaryMimetype = ["TEXT", "MULTIPART", "MESSAGE", "APPLICATION", "AUDIO", "IMAGE", "VIDEO", "OTHER"];
        
        if ($structure->subtype) {
            return $primaryMimetype[(int)$structure->type] . "/" . $structure->subtype;
        }
        return "TEXT/PLAIN";
    }
    
    public function getMailbox(){
        return $this->mailbox;
    }
    
    public function mailboxOpening($validation = false) {
        $enc = '';
        $imapError = '';
        
        if($this->a_shipment['IncomingPort']!=null){
            $enc.= ':'.$this->a_shipment['IncomingPort'];
        }
            
        switch(strtolower($this->a_shipment['IncomingProtocol'])){
            case 'imap':		$enc.= '/imap';				break;
            case 'pop3':		$enc.= '/pop3';				break;
        }
        
        switch(strtolower($this->a_shipment['IncomingSecurity'])){
            case 'ssl':			$enc.= '/ssl';				break;
            case 'tls':			$enc.= '/tls';				break;
            case 'notls':		$enc.= '/notls';			break;
        }
        
        switch($validation){
            case false:			$enc.= '/novalidate-cert';	break;
            case true:			$enc.= '/validate-cert';	break;
        }
        
        $this->mailbox = "{" . $this->a_shipment['IncomingMailServer'] . $enc . "}";
        
        if(!empty($this->a_shipment['DisableAuthProperties'])){
            $this->imap = @imap_open($this->mailbox, $this->a_shipment['IncomingUserName'], $this->a_shipment['IncomingPassword'], null, null, array('DISABLE_AUTHENTICATOR' => $this->a_shipment['DisableAuthProperties']));
        } else {
            $this->imap = @imap_open($this->mailbox, $this->a_shipment['IncomingUserName'], $this->a_shipment['IncomingPassword']);
        }
        
        if(!$this->imap){
            foreach(imap_errors() as $error){
                $imapError .= $error.'<br>';
            }
            imap_alerts();
            return $imapError;
        }
        return true;
    }
    
    public function mailboxClosing(bool $expunge = false){
        imap_close($this->imap, $expunge ? CL_EXPUNGE : 0);
    }
    
    public function mailboxSelectFolder(string $folder) {
        $result = imap_reopen($this->imap, $this->mailbox . $folder);
        if($result === true){
            if (!empty(imap_last_error())){
                $result = imap_last_error();
                imap_errors();
            } else {
                $this->folder = $folder;
            }
        }
        return $result;
    }
    
    public function mailboxGetMails() {
        return imap_sort($this->imap, SORTARRIVAL, 0, SE_UID, 'ALL', "UTF-8");
    }
    
    public function mailboxGetNumMails() {
        return imap_num_msg($this->imap);
    }
    
    public function mailboxGetMailBySubject(string $subject) {
        return imap_sort($this->imap, SORTARRIVAL, false, SE_UID,  'SUBJECT "'.$subject.'"', "UTF-8");
    }
    
    public function mailboxFilterByHeaderField(array $uids, string $fieldName, string $value = '') {
        $filtered = array();
        foreach($uids as $uid){
            $header = $this->mailboxGetMailHeader($uid);
            
            if(isset($header[$fieldName])){
                if($value != ''){
                    if($header[$fieldName] == $value) $filtered[] = $uid;
                } else $filtered[] = $uid;
            }
        }
        return $filtered;
    }
    
    public function mailboxGetMailBody($uid)
    {
        $body = $this->getPart($this->imap, $uid, "TEXT/HTML");
        // if HTML body is empty, try getting text body
        if ($body == "") {
            $body = $this->getPart($this->imap, $uid, "TEXT/PLAIN");
        }
        return $body;
    }
    
    public function mailboxGetMailHeaderInfo($uid)
    {
        $msgno = imap_msgno($this->imap, $uid);
        $headerInfo = imap_headerinfo($this->imap, $msgno);
        return $headerInfo;
    }
    
    public function mailboxGetMailHeader($uid)
    {
        $header = $matches = array();
        $headerString = imap_fetchheader($this->imap, $uid, FT_UID);
        preg_match_all('/([^:\s]+): (.*?(?:\r\n\s(?:.+?))*)\r\n/m', $headerString, $matches);
        
        foreach ($matches[1] as $i => $k) {
            $header[$k][] = iconv_mime_decode($matches[2][$i],0,"UTF-8");
        }
        array_walk($header,
            function (&$v) {
                $v = (count($v) === 1) ? array_pop($v): $v;
            }
        );
        return $header;
    }
    
    public function mailboxDeleteMail($uid){
        imap_delete($this->imap, $uid, FT_UID);
    }
    
    public function mailboxExportMails(array $uids, $savePath, $delete = false){
        $zip = new ZipArchive();
        if ($zip->open($savePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
            $eml = tmpfile();
            foreach ($uids as $uid){
                imap_savebody($this->imap, $eml, $uid, null, FT_UID);
                fseek($eml, 0);
                $zip->addFromString($this->mailboxGetMailHeaderInfo($uid, $this->imap)->subject.'.eml', file_get_contents(stream_get_meta_data($eml)['uri']));
            }
            
            $zip->close();
            fclose($eml);
        }
        
        if(file_exists($savePath) && $delete){
            foreach ($uids as $uid){
                $this->mailboxDeleteMail($uid);
            }
        }
    }
    
    public function mailboxExportSingleMail($uid, $savePath, $fileName, $delete = false){
        $eml = fopen($savePath.$fileName.'.eml', "w");
        imap_savebody($this->imap, $eml, $uid, null, FT_UID);
        fclose($eml);
        
        if(file_exists($savePath) && $delete){
            $this->mailboxDeleteMail($uid);
        }
    }
    
    public function mailboxGetSpaceUsageAndLimit(){
        $quota = imap_get_quotaroot($this->imap, $this->folder);
        return $quota ? ($quota['STORAGE'] ?? false) : false;
    }
    
    public function sendMail(array $recipients, string $subject, string $body, bool $isHTML = false, array $attachements = null) {
        $this->clearAllRecipients();
        $this->clearAttachments();
        $this->clearBCCs();
        $this->clearCCs();
        
        $senderName = $this->a_shipment['ShownName'];
        $senderMail = $this->a_shipment['MailAddress'];
        
        try {
            if (!empty($recipients)){
                //Controlla gli allegati
                if(!is_null($attachements)){
                    if(!empty($attachements)){
                        if(!$this->addAttachements($attachements)) throw new Exception(self::INVALID_ATTACHEMENTS.': '.$this->ErrorInfo);
                    } else throw new Exception(self::NO_ATTACHEMENTS);
                }
                //Controlla il mittente
                if(!$this->setFrom($senderMail, $senderName)) throw new Exception(self::INVALID_SENDER.': '.$this->ErrorInfo);
                //Controlla i destinatari
                if(!$this->addRecipients($recipients)) throw new Exception(self::INVALID_RECIPIENTS.': '.$this->ErrorInfo);
                
                $this->Subject = $subject;
                $this->isHTML($isHTML);
                $this->Body = $body;
                
                if (!$this->send()) {
                    return $this->ErrorInfo;
                } else return true;
                
            } else throw new Exception(self::NO_RECIPIENTS);
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }
    
    public function preSendMail(array $recipients, string $subject, string $body, bool $isHTML = false, array $attachements = null) {
        $this->clearAllRecipients();
        $this->clearAttachments();
        $this->clearBCCs();
        $this->clearCCs();
        
        $senderName = $this->a_shipment['ShownName'];
        $senderMail = $this->a_shipment['MailAddress'];
        
        try {
            if (!empty($recipients)){
                //Controlla gli allegati
                if(!is_null($attachements)){
                    if(!empty($attachements)){
                        if(!$this->addAttachements($attachements)) throw new Exception(self::INVALID_ATTACHEMENTS.': '.$this->ErrorInfo);
                    } else throw new Exception(self::NO_ATTACHEMENTS);
                }
                //Controlla il mittente
                if(!$this->setFrom($senderMail, $senderName)) throw new Exception(self::INVALID_SENDER.': '.$this->ErrorInfo);
                //Controlla i destinatari
                if(!$this->addRecipients($recipients)) throw new Exception(self::INVALID_RECIPIENTS.': '.$this->ErrorInfo);
                
                $this->Subject = $subject;
                $this->isHTML($isHTML);
                $this->Body = $body;
                
                if (!$this->preSend()) {
                    return $this->ErrorInfo;
                } else return true;

            } else throw new Exception(self::NO_RECIPIENTS);
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }
    
    public function testOutgoingConnection() {
        try {
            if(!$this->smtpConnect()) return self::FAIL_CONNECTION.': '.$this->debug;
        } catch (Exception $e) {
            return $e->getMessage();
        }
        return true;
    }
    
    /**
     * @deprecated
     *
     * @return $this
     */
    public function testSmtpConnection($host, $security=null, $port = null, $username = null, $password = null) {
        $smtp = new SMTP();
        
        switch(strtolower($security)){
            case 'ssl':			$security = 'ssl://';				break;
            case 'tls':			$security = 'tls://';				break;
            case 'starttls':    $security = 'notls://';			    break;
            default:            $security = '';
        }
        
        try {
            //Connect to an SMTP server
            if (!$smtp->connect($security.$host, $port, $this->Timeout)) {
                throw new Exception(self::FAIL_CONNECTION.': '.$security.$host.':'.$port);
            }
            //Say hello
            if (!$smtp->hello(gethostname())) {
                throw new Exception(self::FAIL_EHLO.': ' . $smtp->getError()['error']);
            }
            //Get the list of ESMTP services the server offers
            $e = $smtp->getServerExtList();
            //If server can do TLS encryption, use it
            if (is_array($e) && array_key_exists('STARTTLS', $e)) {
                $tlsok = $smtp->startTLS();
                if (!$tlsok) {
                    throw new Exception(self::FAIL_ENCRYPTION.': ' . $smtp->getError()['error']);
                }
                //Repeat EHLO after STARTTLS
                if (!$smtp->hello(gethostname())) {
                    throw new Exception(self::FAIL_EHLO.': ' . $smtp->getError()['error']);
                }
                //Get new capabilities list, which will usually now include AUTH if it didn't before
                $e = $smtp->getServerExtList();
            }
            //If server supports authentication, do it (even if no encryption)
            if (is_array($e) && array_key_exists('AUTH', $e)) {
                if (!$smtp->authenticate($username, $password)) {
                    throw new Exception(self::FAIL_AUTH.': ' . $smtp->getError()['error']);
                }
            }
        } catch (Exception $e) {
            return $e->getMessage();
        }
        return true;
    }
    
    //NUOVI METODI PER LEGGERE IL CONTENUTO DELLE MAIL
    
    private function flattenParts($messageParts, $flattenedParts = array(), $prefix = '', $index = 1, $fullPrefix = true) {
        foreach($messageParts as $part) {
            $flattenedParts[$prefix.$index] = $part;
            if(isset($part->parts)) {
                if($part->type == 2) {
                    $flattenedParts = $this->flattenParts($part->parts, $flattenedParts, $prefix.$index.'.', 0, false);
                }
                elseif($fullPrefix) {
                    $flattenedParts = $this->flattenParts($part->parts, $flattenedParts, $prefix.$index.'.');
                }
                else {
                    $flattenedParts = $this->flattenParts($part->parts, $flattenedParts, $prefix);
                }
                unset($flattenedParts[$prefix.$index]->parts);
            }
            $index++;
        }
        return $flattenedParts;
    }
    
    private function getBodyPart($connection, $messageNumber, $partNumber, $encoding) {
        $data = imap_fetchbody($connection, $messageNumber, $partNumber, FT_UID | FT_PEEK);
        switch($encoding) {
            case ENC7BIT: 
                $data = quoted_printable_decode($data); // 7BIT
                return iconv(mb_detect_encoding($data, 'ISO-8859-1, ISO-8859-2'), 'UTF-8', $data);
            case ENC8BIT: 
                $data = quoted_printable_decode(imap_8bit($data)); // 8BIT
                return iconv(mb_detect_encoding($data, 'ISO-8859-1, ISO-8859-2'), 'UTF-8', $data);
            case ENCBINARY: return imap_binary($data); // BINARY
            case ENCBASE64: return imap_base64($data); // BASE64
            case ENCQUOTEDPRINTABLE: 
                $data = quoted_printable_decode($data); // QUOTED_PRINTABLE
                return iconv(mb_detect_encoding($data, 'ISO-8859-1, ISO-8859-2'), 'UTF-8', $data);
            case ENCOTHER: return $data; // OTHER
        }
    }
    
    private function getFilenameFromPart($part) {
        $filename = '';
        if($part->ifdparameters) {
            foreach($part->dparameters as $object) {
                if(strtolower($object->attribute) == 'filename') {
                    $filename = mb_convert_encoding($object->value, 'UTF-8', mb_detect_encoding($object->value, null, true) ?: 'ISO-8859-15');
                }
            }
        }
        if(!$filename && $part->ifparameters) {
            foreach($part->parameters as $object) {
                if(strtolower($object->attribute) == 'name') {
                    $filename = mb_convert_encoding($object->value, 'UTF-8', mb_detect_encoding($object->value, null, true) ?: 'ISO-8859-15');
                }
            }
        }
        
        $filename = imap_mime_header_decode($filename);
        $filename = reset($filename);
        $filename = $filename->text;
        
        return $filename;
    }
    
    public function mailboxGetMailFullBody($uid, $includeAttachments = true){
        $structure = imap_fetchstructure($this->imap, $uid, FT_UID);
        $flattenedParts = $this->flattenParts($structure->parts);
        $a_Parts = array();
        
        foreach($flattenedParts as $partNumber => $part) {
            switch($part->type) {
                case 0:
                    // the HTML or plain text part of the email
                    $a_Parts['Text'][$partNumber] = imap_utf8($this->getBodyPart($this->imap, $uid, $partNumber, $part->encoding));
                    break;
                case 1:
                    // multi-part headers, can ignore
                    break;
                case 2:
                    // attached message headers, can ignore
                    break;
                case 3: // application
                case 4: // audio
                case 5: // image
                case 6: // video
                case 7: // other
                    if($includeAttachments){
                        $filename = $this->getFilenameFromPart($part);
                        if($filename) {
                            // it's an attachment
                            $a_Parts['Attachments'][$partNumber]['Name'] = $filename;
                            $a_Parts['Attachments'][$partNumber]['Data'] = $this->getBodyPart($this->imap, $uid, $partNumber, $part->encoding);
                        }
                        else {
                            // don't know what it is
                        }
                    }
                    break;
            }
        }
        return $a_Parts;
    }
}
?>