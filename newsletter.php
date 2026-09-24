<?php
require_once __DIR__.'/includes/recommendations.php'; $error='';
require_once __DIR__.'/includes/email-delivery.php';
if($_SERVER['REQUEST_METHOD']==='POST') {
    try {
        if(!validFormToken()) throw new InvalidArgumentException('แบบฟอร์มหมดอายุ กรุณาลองอีกครั้ง');
        if(($_POST['consent']??'')!=='yes') throw new InvalidArgumentException('กรุณายินยอมรับข่าวสารก่อนสมัคร');
        subscribeNewsletter(is_string($_POST['email']??null)?$_POST['email']:'');
        $_SESSION['newsletter_email_status']=deliverCustomerEmail($_POST['email'],'newsletter');
        $_SESSION['newsletter_success']=true; redirectTo('newsletter.php?thanks=1');
    } catch(InvalidArgumentException $ex) { $error=$ex->getMessage(); }
}
$thanks=isset($_GET['thanks'])&&!empty($_SESSION['newsletter_success']);
$pageTitle=$thanks?'ขอบคุณที่สมัครรับข่าวสาร':'รับข่าวสารจากท้องทะเล'; require __DIR__.'/includes/member-header.php';
?>
<main class="member-main split"><img class="split-art" src="assets/products/starfish-gold-pendant.jpg" alt="สร้อยจี้ดาวทะเลโทนทอง"><section><p class="eyebrow">LET THE SEA INSPIRE YOU</p>
<?php if($thanks): ?><p class="script">Thank You</p><h1>ขอบคุณที่สมัครรับข่าวสาร<br>จาก Holystarfish</h1><p>บันทึกการสมัครของคุณแล้ว เตรียมพบกับคอลเลกชันใหม่ โปรโมชันพิเศษ และเรื่องราวดี ๆ จากโลกของเครื่องประดับ</p><p class="fine muted" role="status"><?= e(customerEmailNotice($_SESSION['newsletter_email_status']??'draft')) ?></p><a class="button" href="products.php">เยี่ยมชมคอลเลกชันของเรา →</a>
<?php else: ?><p class="script">A little sparkle</p><h1>เรื่องราวดี ๆ<br>ส่งตรงถึงคุณ</h1><p>รับข่าวคอลเลกชันใหม่ แรงบันดาลใจจากท้องทะเล และสิทธิพิเศษจาก Holystarfish</p>
<?php if($error): ?><div class="alert" role="alert"><?= e($error) ?></div><?php endif ?>
<form method="post" class="subscribe"><input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>"><label>อีเมลของคุณ<input type="email" name="email" autocomplete="email" maxlength="254" placeholder="you@example.com" required value="<?= e(is_string($_POST['email']??null)?$_POST['email']:'') ?>"></label><label class="consent"><input type="checkbox" name="consent" value="yes" required><span>ฉันยินยอมให้ Holystarfish บันทึกอีเมลเพื่อส่งข่าวสารและโปรโมชัน</span></label><button class="button">สมัครรับข่าวสาร →</button></form><?php endif ?></section></main><footer class="member-footer">Inspired by the Sea. Crafted to Shine.</footer></body></html>
