<?php

// preview only, not sending real mail. set to false for sending.
$preview = true;

// connection setup
// possible smtp, smtps
$target = "mail.server.tld";
$proto = "smtp";
$port = "25";
// ehlo hostname, if empty -> gethotname()
$ehlo = "";
// STARTTLS usage (_NONE, _TRY, _CONTROL, _ALL), see libcurl for details
$usessl = CURLUSESSL_NONE;
// Login credentials
$user = "";
$passwd = "";

// Set From information
$name = "John Doe";
$from = "john@doe.net";
// reply & return only used if set
$reply = "";
$return = "";
// envelope sender, if empty -> $from
$mailfrom = "";

// Set Destiantion
$toname = "Jane Doe";
$to = "Jane@doe.net";
// envelope recipient, if empty -> $to
$rcptto = "";

// Set Message Subject
// if empty -> "Testmail von $ehlo DATE_RFC822";
$subject = "";

// Set Markdown File for Mail Text
$message = "mail.md";

// Set Attachments
$attach = array("mail.md");
//Versand mehrerer Dateien, die sich im Unterordner befinden:
//$attach = array("pfad/zu/datei1.zip", "pfad/zu/datei2.png");
//Dateien vor dem Versenden umbennen
#attach = array("pfad/zu/alterName.zip" => "neuerName.zip");

// Add EICAR Test-Virus Signature as attachment, disabled if empty
// string is used as filename for attachment.
//$eicar = "eicar.com"
$eicar = "";
// set mimetype for eicar attachment, default "application/octet-stream", maybe "text/plain"
$eicar_mime = "application/octet-stream";

// Add GTUBE for spam testing, disabled if empty
//$gtube = "\r\nGTUBE for spam testing\r\n\r\nXJS*C4JDBQADN1.NSBN3*2IDNEN*GTUBE-STANDARD-ANTI-UBE-TEST-EMAIL*C.34X\r\n\r\n";
$gtube = "";

?>
