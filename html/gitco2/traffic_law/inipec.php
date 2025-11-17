<?php 
$wsdl = "https://fpecws.infocamere.it/fpec/ServizioFornituraPec?wsdl";
$http_headers = array(
    'POST /fpec/ServizioFornituraPec HTTP/1.1\r\n',
    'Host: fpecws.infocamere.it\r\n',
    'Accept-Encoding: gzip,deflate\r\n',
    'Content-Type: multipart/related; type="application/xop+xml"; start-info="text/xml; start="<gitco@sarida.it>"\r\n',
    'SOAPAction: "richiestaFornituraPec"\r\n',
    'Authorization: Basic S1NWMDAyOmdlbW9zYXZvbmExOA==\r\n',
    'MIME-Version: 1.0\r\n',
    'Connection: Keep-Alive\r\n\r\n'
);




$file = file_get_contents("prova.zip");
$fp = fsockopen($wsdl,80,$errno, $errstr, 30);
if ($fp) {
    $out = "";
    for($i=0;$i<count($http_headers);$i++){
        $out.= $http_headers[$i];
    }

    fwrite($fp, $out);
//    fwrite($fp, $file);
    $response = '';
    while (!feof($fp)) {
        $response .= fgets($fp, 128);
    }
    fclose($fp);

//    if (preg_match_all('/\{"upload":\{"token":"(.+)"\}\}/ms', $response, $o)) {
//        return $o[1][0];
//    }
}else{
    echo "$errstr ($errno)";
    
}

echo "dsa".$response;