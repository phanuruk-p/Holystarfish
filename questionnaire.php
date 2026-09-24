<?php
require_once __DIR__.'/includes/recommendations.php';
require_once __DIR__.'/includes/email-delivery.php';
$customer = currentCustomer(); if (!$customer) redirectTo('login.php');
$data = customerRecommendationData((int)$customer['id']); $answers = $data['answers'] ?? []; $error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (!validFormToken()) throw new InvalidArgumentException('แบบฟอร์มหมดอายุ กรุณาลองอีกครั้ง');
        savePreferences((int)$customer['id'], $_POST);
        $_SESSION['welcome_email_status']=deliverCustomerEmail($customer['email'],'welcome');
        redirectTo('recommendations.php');
    } catch (InvalidArgumentException $ex) { $error=$ex->getMessage(); $answers=$_POST; }
}
$pageTitle='ค้นหาเครื่องประดับที่เป็นคุณ'; require __DIR__.'/includes/member-header.php';
?>
<main class="member-main"><div class="intro"><p class="eyebrow">YOUR PERSONAL EDIT</p><h1>เครื่องประดับที่บอกความเป็นคุณ</h1><p>ยินดีต้อนรับ คุณ <?= e($customer['full_name']) ?><br>ตอบ 6 คำถามสั้น ๆ ให้เราช่วยเลือกชิ้นที่เหมาะกับคุณ</p><div class="steps"><span>01 สมัครสมาชิก ✓</span><strong>02 บอกสไตล์ของคุณ</strong><span>03 พบชิ้นโปรด</span></div></div>
<?php if($error): ?><div class="alert" role="alert"><?= e($error) ?></div><?php endif ?>
<form method="post"><input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>"><div class="survey-grid">
<?php $n=0; foreach(surveyQuestions() as $key=>[$title,$options]): ?><fieldset class="question"><legend><span class="number">0<?= ++$n ?></span><?= e($title) ?></legend><div class="options">
<?php foreach($options as $value=>$label): $value=array_is_list($options)?$label:(string)$value; ?><label class="choice"><input type="radio" name="<?= e($key) ?>" value="<?= e($value) ?>" required <?= ($answers[$key]??'')===$value?'checked':'' ?>><span><?= e($label) ?></span></label><?php endforeach ?>
</div></fieldset><?php endforeach ?></div><div class="form-end"><button class="button">ดูเครื่องประดับที่เหมาะกับฉัน →</button><p class="fine muted">เราจะบันทึกคำตอบเพื่อแนะนำสินค้า คุณกลับมาเปลี่ยนคำตอบได้เสมอ</p></div></form></main><footer class="member-footer">Inspired by the Sea. Crafted to Shine.</footer></body></html>
