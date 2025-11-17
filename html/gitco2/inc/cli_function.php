<?php
function splitUrl($url){
    $elementi = explode('/', $url);
    $path="";
    $protocol=str_replace(":","",$elementi[0]);
    $domain=$elementi[2];
    for($i=3;$i<sizeof($elementi);$i++)
        $path.="/".$elementi[$i];
    $domainPort=explode(':',$domain);
    if(isset($domainPort[1]))
        $port=$domainPort[1];
    else
        $port=null;
    return array("protocol"=>$protocol,"domain"=>$domainPort[0],"port"=>$port,"path"=>$path);
}


function utf8_string_array_encode(&$array)
{
    $func = function (&$value, &$key) {
        if (is_string($value)) {
            $value = utf8_encode($value);
        }
        if (is_string($key)) {
            $key = utf8_encode($key);
        }
        if (is_array($value)) {
            utf8_string_array_encode($value);
        }
    };
    array_walk($array, $func);
    return $array;
}

function AddNZeroToNumber($n, $nZero){

    $n_Diff = $nZero - strlen($n);

    for($i=0; $i<($n_Diff); $i++){
        $n = "0".$n;

    }
    return $n;
}

function DateTimeOutDB($dt){
    $dateTime = new DateTime($dt);
    $date = array(
        'day' => $dateTime->format('d'),
        'month' => $dateTime->format('m'),
        'year' => $dateTime->format('Y'),
        'hour' => $dateTime->format('H'),
        'minute' => $dateTime->format('i'),
    );

    $dt = $date['day']."/".$date['month']."/".$date['year']." ".$date['hour'].":".$date['minute'];

    return $dt;

}

//Controllo UTF8 sperimentale
function isUTF8($string) {
    return (@iconv('utf-8', 'utf-8//IGNORE', $string) == $string);
}

function callSoapUrlWithSoapBody($url,$verb,$body,$soapAction,$certificate=null,$password=null,$contentType=null,$auth=null){
    $glob=new CkGlobal();
    $success = $glob->UnlockBundle(CHILKAT_CODICE_LICENZA);
    
    $http = new CkHttp();
    $http->put_SessionLogFilename(LOG."/servizi.log");
    chmod(LOG."/servizi.log", 0770);
    
    $urlParts=splitUrl($url);
    $req = new CkHttpRequest();
    $req->put_HttpVerb($verb);
    $req->put_path($urlParts["path"]);
    $req->LoadBodyFromString($body->getXml(),'utf8');
    $req->AddHeader('SOAPAction',$soapAction);
    if($auth!=null)
        $req->AddHeader('Authorization',$auth);
    if($contentType!=null)
        $req->put_ContentType($contentType);
    else
        $req->put_ContentType('application/soap+xml');
    $ssl=$urlParts["protocol"]=="https";
    if($certificate!=null){
        $success=$http->SetSslClientCertPfx($certificate,$password);
        if ($success != true) {
            trigger_error("Certificato errato $certificate",E_USER_WARNING);
            return false;
        }
    }
    if(isset($urlParts['port']))
        $port=$urlParts['port'];
    else if($ssl)
        $port=443;
    else
        $port=80;
    $resp= $http->SynchronousRequest($urlParts["domain"],$port,$ssl,$req);
    if ($http->get_LastMethodSuccess() != true) {
        trigger_error($http->lastHeader(),E_USER_WARNING);
        trigger_error($body->getXml(),E_USER_WARNING);
        trigger_error($http->lastErrorXml(),E_USER_WARNING);
        return false;
    }
    $xmlResponse=new CkXml();
    $xmlResponse->LoadXml($resp->bodyStr());
    return $xmlResponse;
}

function callSoapUrl($url,$verb,$body,$soapAction,$certificate=null,$password=null,$contentType=null,$auth=null){
    $soapBody=createSoapXml($body);
    return callSoapUrlWithSoapBody($url,$verb,$soapBody,$soapAction,$certificate,$password,$contentType,$auth);
}

function getFileFromUrl($url){
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_HEADER, false);
    $data = curl_exec($curl);
    curl_close($curl);
    return $data;
}
function createSoapXml(CkXml $bodyContent,CkXml $headerContent=null, array $attributes=null){
    $soapXml = new CkXml();
    $soapXml->put_EmitXmlDecl(false);
    $soapXml->put_Tag('soap:Envelope');
    $attributes['xmlns:soap']='http://schemas.xmlsoap.org/soap/envelope/';
    $keys=array_keys($attributes);
    foreach ($keys as $key )
        $soapXml->AddAttribute($key,$attributes[$key]);
    if($headerContent!=null){
        $header=$soapXml->NewChild("soap:Header", '');
        $header->AddChildTree($headerContent);
    }
    $body=$soapXml->NewChild('soap:Body','');
    $body->AddChildTree($bodyContent);
    return $soapXml->GetRoot();
}

function debugArray($array){
    echo '<pre>'; print_r($array); echo '</pre>';
}

?>