<?php
putenv('HOLYSTARFISH_DB_PATH=:memory:');
require __DIR__.'/../includes/recommendations.php';
require __DIR__.'/../includes/email-template.php';
function verify(bool $ok,string $label): void { if(!$ok) throw new RuntimeException($label); echo "PASS $label\n"; }
$result=registerCustomer(['full_name'=>'ทดสอบ <สมาชิก>','email'=>'member@example.test','phone'=>'0800000000','password'=>'Test-only-9382','confirm_password'=>'Test-only-9382']);
verify($result['success'],'registration');
$id=(int)$_SESSION['customer_id'];
$answers=['kind'=>'สร้อยคอ','style'=>'แฟชั่น','color'=>'ทอง','occasion'=>'เที่ยว','budget'=>'1000','recipient'=>'self'];
savePreferences($id,$answers);
$data=customerRecommendationData($id);
verify($data['answers']===$answers,'six answers persist');
verify($data['products'][0]['id']==='starfish-gold-pendant','personalized ranking');
verify(count(array_filter($data['products'],fn($p)=>$p['price_value']>1000))===0,'budget respected');
savePreferences($id,array_replace($answers,['budget'=>'500']));
verify((int)db()->query('SELECT COUNT(*) FROM email_outbox')->fetchColumn()===1,'draft update is idempotent');
verify(db()->query('SELECT status FROM email_outbox')->fetchColumn()==='draft','no email sent');
try { savePreferences($id,['budget'=>'999999']); throw new RuntimeException('invalid accepted'); } catch(InvalidArgumentException $e) { echo "PASS reject incomplete answers\n"; }
subscribeNewsletter('Subscriber@example.test'); subscribeNewsletter('subscriber@example.test');
verify((int)db()->query('SELECT COUNT(*) FROM newsletter_subscribers')->fetchColumn()===1,'newsletter duplicate safe');
try { subscribeNewsletter('bad'); throw new RuntimeException('invalid accepted'); } catch(InvalidArgumentException $e) { echo "PASS reject invalid email\n"; }
$html=renderMemberEmail($data,'https://shop.example.test');
verify(str_contains($html,'ทดสอบ &lt;สมาชิก&gt;')&&!str_contains($html,'ทดสอบ <สมาชิก>'),'email escapes customer name');
verify(str_contains($html,'https://shop.example.test/products.php?q='),'email absolute product links');
verify(!isset($data['password_hash']),'customer query omits password');
verify(customerRecommendationData(999999)===null,'unknown customer');
echo "All recommendation tests passed.\n";
