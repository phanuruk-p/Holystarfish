<?php
declare(strict_types=1);
require_once __DIR__.'/email-brand.php';
function emailBaseUrl(): string {
    $url=rtrim(getenv('HOLYSTARFISH_SITE_URL') ?: 'http://localhost:8000','/');
    if(!filter_var($url,FILTER_VALIDATE_URL)||!in_array(parse_url($url,PHP_URL_SCHEME),['http','https'],true)) throw new RuntimeException('Configure HOLYSTARFISH_SITE_URL with an absolute HTTP(S) URL');
    return $url;
}
function renderMemberEmail(?array $data,string $baseUrl): string {
    $welcome=$data!==null; ob_start(); ?>
<!doctype html><html lang="th"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Holystarfish</title></head><body style="margin:0;background:#e9eeec;color:#294b50;font-family:Tahoma,Arial,sans-serif;line-height:1.7">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0"><tr><td align="center" style="padding:24px 0"><table role="presentation" width="600" cellpadding="0" cellspacing="0" style="width:100%;max-width:600px;background:#fffaf2">
<?= emailBrandHeader($baseUrl,$welcome?'WELCOME TO THE HOLYSTARFISH FAMILY':'A LITTLE SPARKLE, JUST FOR YOU') ?>
<tr><td align="center" style="padding:32px 28px 28px"><p style="font:italic 52px Georgia,serif;color:#866332;margin:8px 0 22px"><?= $welcome?'Welcome':'Thank You' ?></p><h1 style="font-size:22px;line-height:1.6;font-weight:500;margin:0 0 14px"><?= $welcome?'ยินดีต้อนรับ คุณ '.e($data['full_name']):'ขอบคุณที่สมัครรับข้อมูลข่าวสาร<br>จาก Holystarfish' ?></h1><p style="font-size:16px"><?= $welcome?'ขอบคุณที่สมัครสมาชิกกับเรา<br>เราเลือกเครื่องประดับจากคำตอบทั้ง 6 ข้อ<br>เพื่อเติมประกายให้วันพิเศษของคุณ':'รับแรงบันดาลใจจากท้องทะเล<br>คอลเลกชันใหม่ และโปรโมชันพิเศษจากเรา' ?></p></td></tr>
<?php if($welcome): foreach($data['products'] as $p): ?><tr><td style="padding:0 24px 32px"><a href="<?= e($baseUrl.'/products.php?q='.urlencode($p['name'])) ?>"><img width="552" height="414" src="<?= e($baseUrl.'/'.$p['image']) ?>" alt="<?= e($p['name']) ?>" style="display:block;width:100%;height:auto;aspect-ratio:4/3;object-fit:cover;border:0"></a><h2 style="font-size:18px;margin:18px 0 8px"><?= e($p['name']) ?></h2><p style="margin:0;font-size:14px"><?= e($p['reason']) ?></p><p style="color:#866332;font-size:20px;font-weight:bold;margin:12px 0"><?= e($p['price']) ?></p><a href="<?= e($baseUrl.'/products.php?q='.urlencode($p['name'])) ?>" style="color:#294b50">ดูสินค้าชิ้นนี้ →</a></td></tr><?php endforeach; else: ?>
<tr><td><img src="<?= e($baseUrl.'/assets/products/starfish-gold-pendant.jpg') ?>" alt="เครื่องประดับดาวทะเล Holystarfish" width="600" height="450" style="display:block;width:100%;height:auto;aspect-ratio:4/3;object-fit:cover"></td></tr><?php endif ?>
<tr><td align="center" style="padding:30px 24px 36px"><a href="<?= e($baseUrl.'/products.php') ?>" style="display:inline-block;padding:15px 28px;background:#234f55;border:1px solid #234f55;border-radius:30px;color:#fff8e9;text-decoration:none;font-size:16px">เยี่ยมชมเว็บไซต์ของเรา →</a></td></tr>
<?= emailBrandFooter($welcome?'ขอบคุณที่เป็นส่วนหนึ่งของครอบครัว Holystarfish':'คุณได้รับข้อความนี้เพราะสมัครรับข่าวสารจากเรา') ?>
</table></td></tr></table></body></html>
<?php return ob_get_clean(); }
