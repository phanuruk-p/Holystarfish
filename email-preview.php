<?php
require_once __DIR__.'/includes/recommendations.php';
require_once __DIR__.'/includes/email-template.php';
header('Cache-Control: private, no-store');
$newsletter=($_GET['type']??'')==='newsletter'; $data=null;
if(!$newsletter) {
    $customer=currentCustomer(); if(!$customer) redirectTo('login.php');
    $data=customerRecommendationData((int)$customer['id']); if(!$data['answers']) redirectTo('questionnaire.php');
}
$html=renderMemberEmail($data,emailBaseUrl());
if(isset($_GET['download'])) { header('Content-Type: text/html; charset=utf-8'); header('Content-Disposition: attachment; filename="holystarfish-'.($newsletter?'thank-you':'welcome').'.html"'); echo $html; exit; }
if(isset($_GET['render'])) { header('Content-Type: text/html; charset=utf-8'); echo $html; exit; }
$pageTitle='ตัวอย่างอีเมล'; require __DIR__.'/includes/member-header.php'; $query=$newsletter?'type=newsletter&':'';
?>
<main class="member-main"><div class="intro"><p class="eyebrow">A NOTE FROM HOLYSTARFISH</p><h1>ตัวอย่างอีเมล<?= $newsletter?'ขอบคุณ':'ต้อนรับ' ?></h1><p>ขนาดกว้าง 600 พิกเซล · ภาพสินค้า 4:3<br>นี่คือตัวอย่างเท่านั้น ยังไม่ได้ส่งอีเมล</p></div><div class="email-tools"><a class="button" href="?<?= $query ?>download=1">ดาวน์โหลดอีเมล HTML</a><a href="<?= $newsletter?'newsletter.php':'recommendations.php' ?>">กลับ</a></div><iframe class="email-frame" title="ตัวอย่างเนื้อหาอีเมล" src="?<?= $query ?>render=1"></iframe></main></body></html>
