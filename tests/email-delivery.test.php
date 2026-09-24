<?php
ob_start();
putenv('HOLYSTARFISH_DB_PATH=:memory:');
require __DIR__.'/../includes/email-delivery.php';
function checkDelivery(bool $ok,string $label): void { if(!$ok) throw new RuntimeException($label); echo "PASS $label\n"; }
$config=['email'=>'sender@gmail.com','password'=>'abcdefghijklmnop','site_url'=>'https://shop.example.test'];
$calls=0;
$transport=function($mail)use(&$calls){ $calls++; if(count($mail->getToAddresses())!==1) throw new RuntimeException('Wrong recipients'); };
subscribeNewsletter('new@example.test');
checkDelivery(deliverCustomerEmail('new@example.test','newsletter',$transport,[])==='not_configured'&&$calls===0,'missing config does not send');
checkDelivery(deliverCustomerEmail('new@example.test','newsletter',$transport,$config)==='sent'&&$calls===1,'newsletter sends once');
subscribeNewsletter('new@example.test');
checkDelivery(deliverCustomerEmail('new@example.test','newsletter',$transport,$config)==='sent'&&$calls===1,'repeat subscription does not resend');
subscribeNewsletter('failure@example.test');
checkDelivery(deliverCustomerEmail('failure@example.test','newsletter',function(){throw new RuntimeException('Timeout');},$config)==='unknown','uncertain delivery recorded');
checkDelivery(deliverCustomerEmail('failure@example.test','newsletter',$transport,$config)==='unknown'&&$calls===1,'uncertain delivery not retried automatically');
$result=registerCustomer(['full_name'=>'ทดสอบ ระบบ','email'=>'member@example.test','phone'=>'0800000000','password'=>'Test123456','confirm_password'=>'Test123456']);
$id=(int)$_SESSION['customer_id'];
$answers=['kind'=>'สร้อยคอ','style'=>'แฟชั่น','color'=>'ทอง','occasion'=>'เที่ยว','budget'=>'1000','recipient'=>'self'];
savePreferences($id,$answers);
checkDelivery(deliverCustomerEmail('member@example.test','welcome',function($mail)use(&$calls){$calls++;checkDelivery(str_contains($mail->Body,'ทดสอบ ระบบ')&&str_contains($mail->Body,'src="cid:'),'welcome contains name and embedded products');},$config)==='sent','welcome sends');
savePreferences($id,array_replace($answers,['budget'=>'500']));
checkDelivery(deliverCustomerEmail('member@example.test','welcome',$transport,$config)==='sent'&&$calls===2,'editing survey preserves sent status');
echo "Delivery tests passed; no network or real customer data used.\n";
