<?php



$url = "https://nodopagamenti.regione.liguria.it/portale/nodopagamenti/servlet/avvisopagamento";


$str_Iuv = "iuv=002000002544039";



$ch = curl_init( $url );
curl_setopt( $ch, CURLOPT_POST, 1);
curl_setopt( $ch, CURLOPT_POSTFIELDS, $str_Iuv);
curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1);
curl_setopt( $ch, CURLOPT_HEADER, 0);
curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1);

$response = curl_exec( $ch );


header('Content-type: application/pdf');
header('Content-Disposition: attachment; filename="service.pdf"');
echo $response;

