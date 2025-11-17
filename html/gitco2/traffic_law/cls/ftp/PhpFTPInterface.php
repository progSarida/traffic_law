<?php
interface PhpFTPInterface
{
    const ERR_CONN = "Connessione fallita. (%s)";
    const ERR_CONN_LOGIN = "Login fallito. (%s)";
    const ERR_CONN_PASSIVE = "Errore impostazione connessione FTP in modalità passiva. (%s)";
    const ERR_DSCN = "Errore nella chiusura della connessione. (%s)";
    const ERR_PUT = "Errore nel caricamento del file, locale: '%s' remoto: '%s'. (%s)";
    const ERR_GET = "Errore nel salvataggio del file, locale: '%s' remoto: '%s'. (%s)";
    const ERR_REN = "Errore nella rinominazione del file da '%s' a '%s'. (%s)";
    const ERR_DEL = "Errore nell'eliminazione del file '%s'. (%s)";
    const ERR_MKD = "Errore nella creazione della cartella '%s'. (%s)";
    const ERR_CHD = "Errore nel cambio di cartella '%s'. (%s)";
    const ERR_LST = "Errore nell'elencazione del contenuto di '%s'. (%s)";
    const ERR_PWD = "Errore nel recupero della cartella corrente. (%s)";
    const ERR_MTM = "Errore nel recupero della data di modifica del file '%s'. (%s)";
    
    /**
     * Default Constructor.
     *
     * Istanzia una connessione FTP o SFTP
     *
     * @param string $host 
     * @param string $username
     * @param string $password
     * @param int $port
     * @access public
     */
    public function __construct($host, $username, $password, $port = null);
    
    /**
     * Effettua la connessione al server
     *
     * @return bool true se esito positivo o false in caso contrario
     * @access public
     */
    public function connect();
    
    /**
     * Effettua la disconnessione dal server
     *
     * @return bool true se esito positivo o false in caso contrario
     * @access public
     */
    public function disconnect();
    
    /**
     * Ritorna una lista di errori
     *
     * @return array La lista di errori
     * @access public
     */
    public function errors();
    
    /**
     * Crea un messaggio di errore parametrizzato dinamicamente seguendo la convenzione di vsprintf
     * 
     * @see https://www.php.net/manual/en/function.vsprintf.php
     * @param string ...$messageArgs I parametri del messaggio
     * @param string $message Il messaggio parametrizzabile
     * @return void
     * @access public
     */
    public function handle_error($message, ...$messageArgs);
    
    /**
     * Verifica la connessione
     *
     * @return bool true se esito positivo o false in caso contrario
     * @access public
     */
    public function connection_check();
    
    /**
     * Carica il file specificato sul server
     *
     * @param string $localFile Il nome del file in locale da caricare
     * @param string $remoteFile Il nome con cui verrà salvato il file sul server
     * @return bool true se esito positivo o false in caso contrario
     * @access public
     */
    public function put($localFile, $remoteFile);
    
    /**
     * Scarica il file specificato dal server
     *
     * @param string $localFile Il nome con cui verrà salvato il file remoto in locale
     * @param string $remoteFile Il nome del file remoto
     * @return bool true se esito positivo o false in caso contrario
     * @access public
     */
    public function get($localFile, $remoteFile);
    
    /**
     * Rinomina il file specificato
     *
     * @param string $oldName Il nome del file originale
     * @param string $newName Il nome nuovo del file
     * @return bool true se esito positivo o false in caso contrario
     * @access public
     */
    public function rename($oldName, $newName);
    
    /**
     * Elimina il file specificato
     *
     * @param string $fileToDelete Il file da eliminare
     * @return bool true se esito positivo o false in caso contrario
     * @access public
     */
    public function delete($fileToDelete);
    
    /**
     * Ritorna una lista di file nella cartella specificata (nella cartella corrente se non specificata)
     *
     * @param string $dirToList Il percorso di cui elencare i file
     * @return mixed la lista di file o false se c'è stato un errore
     * @access public
     */
    public function dir_list($dirToList = '.');
    
    /**
     * Ritorna la data di ultima modifica del file specificato (in formato unix)
     *
     * @param string $pathToFile Il file da verificare
     * @return mixed la data in formato unix o false se c'è stato un errore
     * @access public
     */
    public function remote_filemtime($pathToFile);
    
    /**
     * Verifica se il file specificato esiste
     *
     * @param string $pathToFile Il file da verificare
     * @return bool true se esito positivo o false in caso contrario
     * @access public
     */
    public function file_exists($fileName);
    
    /**
     * Crea una cartella 
     *
     * @param string $dirToMake La cartella da creare
     * @return bool true se esito positivo o false in caso contrario
     * @access public
     */
    public function make_dir($dirToMake);
    
    /**
     * Cambia la cartella corrente
     *
     * @param string $dirToMoveTo La cartella su cui spostarsi
     * @return bool true se esito positivo o false in caso contrario
     * @access public
     */
    public function change_dir($dirToMoveTo);
    
    /**
     * Ritorna il nome della cartella corrente
     *
     * @return mixed il nome della cartella corrente o false se c'è stato un errore
     * @access public
     */
    public function pwd();
}