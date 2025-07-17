#!/usr/bin/php
<?php
error_reporting(E_ALL & ~E_NOTICE);
ini_set('display_errors','1');
include_once('./parsedown/Parsedown.php');
include_once('./settings.php');

//Mail zusammenbauen

//setup envelop sender & receipint
if ($mailfrom == "") {$mailfrom = $from;};
if ($rcptto == "") {$rcptto = $to;};

//header variablen
if ($mailfrom == "") {$mailfrom = $from;}
if ($rcptto == "") {$rcptto = $to;}
$uid = md5(uniqid(microtime(), true));
if ($ehlo == "") {$ehlo = gethostname();}
$message_id = time() . "-" . $uid . '@' . $ehlo;
$mime_boundary_mix = "*=" . $uid . "_mix=*";
$mime_boundary_alt = "*=" . $uid . "_alt=*";
if ($subject == "") {
  $subject = "Testmail von " . $ehlo . " " . date(DATE_RFC822);
}
//mailstring
$mailfile = "From: $name <$from>\r\n";
$mailfile.= "To: $toname <$to>\r\n";
if ($reply != "") {
  $mailfile.= "Reply-To: $reply\r\n";
}
if ($return != "") {
  $mailfile.= "Return-Path: $return\r\n";
}
$mailfile.= "Date: ". date('r') . "\r\n";
$mailfile.= "Subject: $subject\r\n";
$mailfile.= "Message-ID: $message_id\r\n";
$mailfile.= "MIME-Version: 1.0\r\n";
$mailfile.= "Content-Type: multipart/mixed;\r\n";
$mailfile.= " boundary=\"".$mime_boundary_mix."\"\r\n";

$mailfile.= "--".$mime_boundary_mix."\r\n";
$mailfile.= "Content-Type: multipart/alternative;\r\n";
$mailfile.= " boundary=\"".$mime_boundary_alt."\"\r\n";

//mailtext aus datei laden
$mailtext = file_get_contents($message);
$encoding = mb_detect_encoding($mailtext, "utf-8, iso-8859-1, windows-1252");

$mailfile.= "--".$mime_boundary_alt."\r\n";
$mailfile.= "Content-Type: text/plain; charset=\"$encoding\"\r\n";
$mailfile.= "Content-Transfer-Encoding: 8bit\r\n";
$mailfile.= "Content-Disposition: inline\r\n";
$mailfile.= "\r\n";
$mailfile.= $gtube;
$mailfile.= $mailtext."\r\n";
$mailfile.= "\r\n";

//format Markdown to HTML
$Parsedown = new Parsedown();
$html = $Parsedown->text($mailtext);
$encoding = mb_detect_encoding($html, "utf-8, iso-8859-1, windows-1252");

$mailfile.= "--".$mime_boundary_alt."\r\n";
$mailfile.= "Content-Type: text/html; charset=\"$encoding\"\r\n";
$mailfile.= "Content-Transfer-Encoding: 8bit\r\n";
$mailfile.= "Content-Disposition: inline\r\n";
$mailfile.= "\r\n";
if ($gtube != "") {
  $mailfile.= "<p>".$gtube."</p>\r\n";
}
$mailfile.= $html."\r\n";
$mailfile.= "\r\n";

//Dateiliste bauen
foreach($attach AS $key => $val) {
  if(is_int($key)) {
    $file = $val;
    $attname = basename($file);
  } else {
    $file = $key;
    $attname = basename($val);
  }

  $filesize = filesize($file);
  $filedata = chunk_split(base64_encode(file_get_contents($file)));
  $filetype = mime_content_type($file);

  $mailfile.= "--".$mime_boundary_mix."\r\n";
  $mailfile.= "Content-Disposition: attachment;\r\n";
  $mailfile.= "\tfilename=\"".$attname."\";\r\n";
  $mailfile.= "Content-Length: ".$filesize.";\r\n";
  $mailfile.= "Content-Type: ".$filetype."; name=\"".$attname."\"\r\n";
  $mailfile.= "Content-Transfer-Encoding: base64\r\n\r\n";
  $mailfile.= $filedata."\r\n";
  $mailfile.= "\r\n";
}

if ($eicar != "") {
  $mailfile.= "--".$mime_boundary_mix."\r\n";
  $mailfile.= "Content-Disposition: attachment;\r\n";
  $mailfile.= "\tfilename=\"".$eicar."\";\r\n";
  $mailfile.= "Content-Length: 68;\r\n";
  $mailfile.= "Content-Type: ".$eicar_mime."; name=\"".$eicar."\"\r\n";
  $mailfile.= "Content-Transfer-Encoding: base64\r\n\r\n";
  $mailfile.= "WDVPIVAlQEFQWzRcUFpYNTQoUF4pN0NDKTd9JEVJQ0FSLVNUQU5EQVJELUFOVElWSVJVUy1URVNU\r\n";
  $mailfile.= "LUZJTEUhJEgrSCo=\r\n";
  $mailfile.= "\r\n";
}

$mailfile.= "--".$mime_boundary_mix."--\r\n"; 
$mailfile.= "\r\n";

echo "---------------------------".PHP_EOL;
if ($preview === true) {
  echo "Preview the Mail only!".PHP_EOL;
} else {
  echo "Sending the follwing Mail:".PHP_EOL;
}
echo "---------------------------".PHP_EOL;
echo PHP_EOL;
echo "Envelop Sender: ". $mailfrom .PHP_EOL;
echo "Envelop Receipient: ". $rcptto .PHP_EOL;
echo PHP_EOL;
echo $mailfile;
echo PHP_EOL;
echo "---------------------------".PHP_EOL;
if ($preview !== true) {

  echo "CURL output:".PHP_EOL;
  echo "---------------------------".PHP_EOL;

  $fp = fopen('php://memory', 'r+');
  fwrite($fp, $mailfile);
  rewind($fp);

  $curlsmtp = curl_init();
  curl_setopt($curlsmtp, CURLOPT_URL, $proto."://".$target.":".$port."/".$ehlo);
  curl_setopt_array($curlsmtp, [
    CURLOPT_MAIL_FROM => "<$mailfrom>",
    CURLOPT_MAIL_RCPT => ["<$rcptto>"],
    CURLOPT_USE_SSL => $usessl,
    CURLOPT_READFUNCTION => function ($curlsmtp, $fp, $length) { return fread($fp, $length);},
    CURLOPT_INFILE => $fp,
    CURLOPT_UPLOAD => true,
    CURLOPT_VERBOSE => true,
  ]);
  if ($user != "") {
    curl_setopt($curlsmtp, CURLOPT_USERNAME, $user);
    curl_setopt($curlsmtp, CURLOPT_PASSWORD, $passwd);
  }
  if ($forceipv4 == true && defined('CURLOPT_IPRESOLVE') && defined('CURL_IPRESOLVE_V4')){
    curl_setopt($curlsmtp, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
  }

  $x = curl_exec($curlsmtp);

  echo "---------------------------".PHP_EOL;
  echo "CURL done with exit status:".PHP_EOL;
  echo "---------------------------".PHP_EOL;

  if ($x === false) {
    echo curl_errno($curlsmtp) . ' = ' . curl_strerror(curl_errno($curlsmtp)) . PHP_EOL;
  } else {
    echo "SUCCESS" . PHP_EOL;
  }

  curl_close($curlsmtp);
  fclose($fp);
}

?>
