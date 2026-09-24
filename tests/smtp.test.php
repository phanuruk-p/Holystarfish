<?php
declare(strict_types=1);
putenv('HOLYSTARFISH_DB_PATH=:memory:');
require __DIR__.'/../includes/auth.php';
require __DIR__.'/../includes/smtp.php';
function smtpCheck(bool $value,string $message): void { if(!$value) throw new RuntimeException($message); echo "PASS $message\n"; }
$config=validatedSmtpConfig(['email'=>'Example@gmail.com','app_password'=>'abcd efgh ijkl mnop','site_url'=>'http://localhost:8000/'],[]);
smtpCheck($config['email']==='example@gmail.com'&&strlen($config['password'])===16,'normalize input');
smtpCheck(validatedSmtpConfig(['email'=>'example@gmail.com','app_password'=>'','site_url'=>'http://localhost:8000'],$config)['password']===$config['password'],'retain saved password');
try { validatedSmtpConfig(['email'=>'different@gmail.com','site_url'=>'http://localhost:8000'],$config); throw new RuntimeException('accepted wrong account'); } catch(InvalidArgumentException $e) { echo "PASS changed account requires password\n"; }
try { validatedSmtpConfig(['email'=>'example@gmail.com','app_password'=>'bad','site_url'=>'http://localhost:8000'],[]); throw new RuntimeException('accepted invalid password'); } catch(InvalidArgumentException $e) { echo "PASS invalid password rejected\n"; }
$html=renderMemberEmail(null,$config['site_url']);
$mail=buildSmtpMessage($config,$config['email'],'ทดสอบ SMTP',$html);
smtpCheck($mail->Host==='smtp.gmail.com'&&$mail->Port===587&&$mail->SMTPSecure==='tls','Gmail STARTTLS configuration');
smtpCheck($mail->preSend(),'build email without network');
$mime=$mail->getSentMIMEMessage();
smtpCheck(str_contains($mime,'Content-ID: <product-'),'embedded product image');
smtpCheck(str_contains($mime,'Content-ID: <brand-logo@holystarfish>')&&str_contains($mail->Body,'src="cid:brand-logo@holystarfish"'),'brand logo attached and referenced');
smtpCheck(str_contains($mail->Body,'src="cid:')&&!str_contains($mail->Body,'src="http://localhost'),'image does not need hosted site');
smtpCheck(count($mail->getToAddresses())===1&&$mail->getToAddresses()[0][0]===$config['email'],'single explicit recipient');
smtpCheck(!str_contains($mime,$config['password']),'password absent from message');
smtpCheck(!str_contains(smtpFailureMessage(new RuntimeException('secret abcd efgh ijkl mnop')),'abcd'),'errors hide sensitive details');
echo "SMTP tests passed. No emails sent.\n";
