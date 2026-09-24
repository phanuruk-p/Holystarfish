<?php
declare(strict_types=1);
require_once __DIR__.'/auth.php';
require_once __DIR__.'/orders.php';
require_once __DIR__.'/smtp.php';

function orderEmailNumber(int $id): string { return 'HS-'.str_pad((string)$id,6,'0',STR_PAD_LEFT); }
function renderOrderEmail(array $order,string $baseUrl): string {
    $items=json_decode($order['items_json'],true,512,JSON_THROW_ON_ERROR);
    $number=orderEmailNumber((int)$order['id']);
    $date=(new DateTimeImmutable($order['created_at'],new DateTimeZone('UTC')))->setTimezone(new DateTimeZone('Asia/Bangkok'))->format('d/m/Y H:i');
    $money=fn($value)=>'฿'.number_format((float)$value,2);
    ob_start(); ?>
<!doctype html><html lang="th"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>ยืนยันคำสั่งซื้อ <?= e($number) ?></title></head>
<body style="margin:0;background:#e9eeec;font-family:Tahoma,Arial,sans-serif;color:#294b50;line-height:1.65">
<div style="display:none;max-height:0;overflow:hidden">ได้รับคำสั่งซื้อ <?= e($number) ?> แล้ว ยอดชำระทั้งหมด <?= $money($order['total']) ?></div>
<table role="presentation" width="100%" cellspacing="0" cellpadding="0"><tr><td align="center" style="padding:24px 0"><table role="presentation" width="600" cellspacing="0" cellpadding="0" style="width:100%;max-width:600px;background:#fffaf2">
<?= emailBrandHeader($baseUrl,'YOUR HOLYSTARFISH ORDER') ?>
<tr><td align="center" style="padding:30px 24px 24px"><p style="font:italic 44px Georgia,serif;color:#866332;margin:0 0 16px">Thank you for your order</p><h1 style="font-size:23px;margin:0 0 12px">ได้รับคำสั่งซื้อของคุณแล้ว</h1><p style="font-size:16px;margin:0">ขอบคุณ คุณ <?= e($order['full_name']) ?><br>ที่เลือกให้ Holystarfish เติมประกายให้วันของคุณ</p></td></tr>
<tr><td style="padding:0 24px 24px"><table role="presentation" width="100%" cellspacing="0" cellpadding="16" style="background:#234f55;color:#fff"><tr><td><span style="font-size:12px">เลขคำสั่งซื้อ</span><br><strong style="font-size:21px"><?= e($number) ?></strong></td><td align="right"><span style="font-size:12px">วันที่สั่งซื้อ · เวลาไทย</span><br><span style="font-size:14px"><?= e($date) ?></span></td></tr></table></td></tr>
<tr><td style="padding:0 24px"><h2 style="font-size:18px;margin:0 0 12px">ชิ้นโปรดในคำสั่งซื้อของคุณ</h2>
<?php foreach($items as $item): ?><table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border-bottom:1px solid #e1d6c4"><tr><td width="84" valign="top" style="padding:16px 12px 16px 0"><img width="72" height="96" src="<?= e($baseUrl.'/'.$item['image']) ?>" alt="<?= e($item['name']) ?>" style="display:block;width:72px;height:96px;object-fit:cover;border:0"></td><td style="padding:16px 0"><strong style="font-size:15px"><?= e($item['name']) ?></strong><p style="font-size:14px;margin:6px 0;color:#6a777a">จำนวน <?= (int)$item['quantity'] ?> ชิ้น<br>ราคาต่อชิ้น <?= $money($item['price']) ?></p><span style="font-size:15px;color:#866332">รวม <?= $money($item['price']*$item['quantity']) ?></span></td></tr></table><?php endforeach ?>
</td></tr><tr><td style="padding:24px"><table role="presentation" width="100%" cellspacing="0" cellpadding="5" style="font-size:15px">
<tr><td>ยอดรวมสินค้า</td><td align="right"><?= $money($order['subtotal']) ?></td></tr>
<tr><td>ส่วนลด</td><td align="right"><?= (int)$order['discount']>0?'-':'' ?><?= $money($order['discount']) ?></td></tr>
<tr><td>VAT 7%</td><td align="right"><?= $money($order['vat']) ?></td></tr>
<tr><td>ค่าจัดส่ง</td><td align="right"><?= $money($order['shipping']) ?><?= (int)$order['shipping']===0?' (ฟรี)':'' ?></td></tr>
<tr><td colspan="2" style="padding:8px 0"></td></tr>
<tr style="background:#234f55;color:#fff"><td style="padding:16px 12px"><strong>ยอดชำระทั้งหมด</strong></td><td align="right" style="padding:16px 12px;font-size:22px"><strong><?= $money($order['total']) ?></strong></td></tr>
</table></td></tr>
<tr><td style="padding:0 28px 24px"><h2 style="font-size:17px;margin:0 0 8px">การชำระเงิน</h2><p style="font-size:14px;margin:0"><?= e(paymentMethods()[$order['payment_method']]??$order['payment_method']) ?><br>สถานะ: <?= e($order['status']) ?></p><p style="font-size:13px;color:#6a777a">อีเมลนี้ยืนยันการรับคำสั่งซื้อ ไม่ใช่หลักฐานการรับชำระเงิน<?= $order['payment_method']==='cod'?' กรุณาเตรียมยอดชำระเมื่อได้รับสินค้า':' กรุณาติดต่อร้านเพื่อรับรายละเอียดการชำระเงิน' ?></p><h2 style="font-size:17px;margin:20px 0 8px">จัดส่งถึง</h2><p style="font-size:14px;margin:0"><?= e($order['recipient']) ?><br><?= nl2br(e($order['address'])) ?><br>โทร <?= e($order['phone']) ?></p></td></tr>
<tr><td align="center" style="padding:0 24px 30px"><a href="<?= e($baseUrl.'/orders.php?id='.(int)$order['id']) ?>" style="display:inline-block;background:#234f55;color:white;border-radius:28px;padding:14px 26px;text-decoration:none;font-size:16px">ดูรายละเอียดคำสั่งซื้อ →</a><p style="font-size:12px;color:#6a777a">เข้าสู่ระบบด้วยบัญชีที่ใช้สั่งซื้อเพื่อดูรายละเอียด</p></td></tr>
<?= emailBrandFooter('ขอบคุณที่ให้เราเป็นส่วนหนึ่งของวันพิเศษ') ?>
</table></td></tr></table></body></html>
<?php return ob_get_clean(); }

function deliverOrderEmail(int $orderId,int $customerId,?callable $transport=null,?array $settings=null): string {
    $pdo=db();
    $query=$pdo->prepare('SELECT m.* FROM order_email_outbox m JOIN orders o ON o.id=m.order_id WHERE m.order_id=? AND o.customer_id=?');
    $query->execute([$orderId,$customerId]); $row=$query->fetch();
    if(!$row) return 'missing';
    if($row['status']!=='draft') return $row['status'];
    try {
        $config=$settings??smtpConfig();
        $config=validatedSmtpConfig(['email'=>$config['email']??'','app_password'=>$config['password']??'','site_url'=>$config['site_url']??''],[]);
    } catch(Throwable $error) { return 'not_configured'; }
    $claim=$pdo->prepare("UPDATE order_email_outbox SET status='sending' WHERE order_id=? AND status='draft'");
    $claim->execute([$orderId]);
    if($claim->rowCount()!==1) return 'sending';
    $attempted=false;
    try {
        $order=json_decode($row['payload_json'],true,512,JSON_THROW_ON_ERROR);
        $mail=buildSmtpMessage($config,$row['recipient'],'ยืนยันคำสั่งซื้อ '.orderEmailNumber($orderId).' | Holystarfish',renderOrderEmail($order,$config['site_url']));
        $mail->preSend(); $attempted=true;
        if($transport) $transport($mail); else if(!$mail->postSend()) throw new RuntimeException('SMTP delivery not confirmed');
        $status='sent';
    } catch(Throwable $error) { $status=$attempted?'unknown':'failed'; }
    $pdo->prepare('UPDATE order_email_outbox SET status=? WHERE order_id=?')->execute([$status,$orderId]);
    return $status;
}
