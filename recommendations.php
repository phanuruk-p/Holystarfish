<?php
require_once __DIR__.'/includes/recommendations.php';
require_once __DIR__.'/includes/email-delivery.php';
$customer=currentCustomer(); if(!$customer) redirectTo('login.php');
$data=customerRecommendationData((int)$customer['id']); if(!$data['answers']) redirectTo('questionnaire.php');
$pageTitle='คัดสรรสำหรับคุณ'; require __DIR__.'/includes/member-header.php';
?>
<main class="member-main"><div class="intro"><p class="script">Welcome</p><h1>ยินดีต้อนรับ คุณ <?= e($customer['full_name']) ?></h1><p>ขอบคุณที่เป็นส่วนหนึ่งของ Holystarfish<br>นี่คือเครื่องประดับที่คัดสรรจากสไตล์และงบประมาณของคุณ</p><p class="fine muted">สินค้าและราคาเป็นตัวอย่าง • เรียงตามความใกล้เคียงกับคำตอบของคุณ</p></div>
<div class="cards"><?php foreach($data['products'] as $p): ?><article class="product"><img src="<?= e($p['image']) ?>" alt="<?= e($p['name']) ?>"><p class="eyebrow">SELECTED FOR YOU</p><h2><?= e($p['name']) ?></h2><strong><?= e($p['price']) ?></strong><p><?= e($p['reason']) ?></p><a href="products.php?q=<?= urlencode($p['name']) ?>">ดูสินค้าชิ้นนี้ →</a></article><?php endforeach ?></div>
<?php if(!$data['products']): ?><p>ยังไม่มีสินค้าในงบนี้ ลองปรับงบประมาณเพื่อดูตัวเลือกเพิ่มเติม</p><?php endif ?>
<div class="form-end"><a class="button light" href="questionnaire.php">เปลี่ยนคำตอบ</a><?php if(isset($_SESSION['welcome_email_status'])): ?><p class="fine muted" role="status"><?= e(customerEmailNotice($_SESSION['welcome_email_status'])) ?></p><?php unset($_SESSION['welcome_email_status']); endif ?></div></main><footer class="member-footer">Inspired by the Sea. Crafted to Shine.</footer></body></html>
