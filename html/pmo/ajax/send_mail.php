<?php
session_start();

$aEmailMessageYes = array(
    'ita'=>'Email spedita correttamente!',
    'eng'=>'Email sent successfully!',
    'ger'=>'E-Mail erfolgreich gesendet!',
    'fre'=>'Email envoyé avec succès!',
    'spa'=>'Email enviado con éxito!',
);

$aEmailMessageNo = array(
    'ita'=>'Problemi nell\'invio della mail. Scrivere una mail direttamente all\'indirizzo informazioni@poliziamunicipale-online.it',
    'eng'=>'Problems in sending the mail. Write an email directly to the email address informazioni@poliziamunicipale-online.it',
    'ger'=>'Probleme in die E-Mail zu senden. Schreiben Sie direkt eine E-Mail an die E-Mail-Adresse informazioni@poliziamunicipale-online.it',
    'fre'=>'Problèmes dans l\'envoi de l\'e-mail. Ecrire un email directement à l\'adresse e-mail informazioni@poliziamunicipale-online.it',
    'spa'=>'Los problemas en el envío del correo. Escribir un correo electrónico directamente a la dirección de correo electrónico informazioni@poliziamunicipale-online.it',
);



if($_POST)
{

    $obj = $_POST['s_obj'];
    $to = "informazioni@poliziamunicipale-online.it";
    $from = $_POST['s_mail'];
    $mex = "(".$_POST['s_name'].")".$_POST['s_message'];
    
    
    $res = mail($to, $obj, $mex, "From: ".$from."\r\nReply-To: ".$from."\r\nX-Mailer: DT_formmail");
    
    $data = ($res) ? $aEmailMessageYes[$_SESSION['lan']] : $aEmailMessageNo[$_SESSION['lan']];
    
    echo $data;
}

