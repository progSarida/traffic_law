<?php
require_once("_path.php");
require_once(INC."/parameter.php");
require_once(CLS."/cls_db.php");
require_once(INC."/cli_function.php");
require_once(INC."/pagopa.php");
include(INC.'/phpSecLib3/autoload.php');
ini_set('memory_limit', '2000M');

$glob=new CkGlobal();

$success = $glob->UnlockBundle(CHILKAT_CODICE_LICENZA);
$rs=new CLS_DB();
trigger_error("Inizio importazione pagamenti",E_USER_NOTICE);
//TODO: verificare perché qui è cablacto con il CityId di Garlasco ma se lanciato da Browser ci va il cityId corretto
$rs_cities=$rs->Select("Customer","PagoPAService is not null and cityId='D925'");
while($customer=mysqli_fetch_array($rs_cities)){
    trigger_error("Importazione pagamenti per cityId {$customer['CityId']}",E_USER_NOTICE);
    importPayment($rs,$customer,E_USER_NOTICE);
}
trigger_error("Fine importazione pagamenti");
?>