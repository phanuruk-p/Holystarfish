<?php
declare(strict_types=1);
require_once __DIR__.'/recommendations.php';
require_once __DIR__.'/smtp.php';

/** Claim exactly one draft; never drain historic messages from a public request. */
function deliverCustomerEmail(string $recipient,string $kind,?callable $transport=null,?array $settings=null): string {
    recommendationSchema();
    if(!in_array($kind,['welcome','newsletter'],true)) throw new InvalidArgumentException('Invalid email kind');
    $recipient=strtolower(trim($recipient));
    $pdo=db();
    $select=$pdo->prepare('SELECT * FROM email_outbox WHERE recipient=? AND kind=?');
    $select->execute([$recipient,$kind]); $row=$select->fetch();
    if(!$row) return 'missing';
    if($row['status']!=='draft') return $row['status'];
    $config=$settings??smtpConfig();
    try {
        $config=validatedSmtpConfig(['email'=>$config['email']??'','app_password'=>$config['password']??'','site_url'=>$config['site_url']??''],[]);
    } catch(InvalidArgumentException $error) { return 'not_configured'; }
    // Reserve atomically before network I/O, preventing double sends on repeated requests.
    $claim=$pdo->prepare("UPDATE email_outbox SET status='sending' WHERE id=? AND status='draft'");
    $claim->execute([$row['id']]);
    if($claim->rowCount()!==1) { $select->execute([$recipient,$kind]); return $select->fetch()['status']??'missing'; }
    $attempted=false;
    try {
        $data=$kind==='welcome'?json_decode($row['payload_json'],true,512,JSON_THROW_ON_ERROR):null;
        $html=renderMemberEmail($data,$config['site_url']);
        $subject=$kind==='welcome'?'ยินดีต้อนรับสู่ Holystarfish — เครื่องประดับที่คัดสรรสำหรับคุณ':'ขอบคุณที่สมัครรับข่าวสารจาก Holystarfish';
        $mail=buildSmtpMessage($config,$recipient,$subject,$html);
        $mail->preSend();
        $attempted=true;
        if($transport) { $transport($mail); } else { if(!$mail->postSend()) throw new RuntimeException('SMTP delivery not confirmed'); }
        $status='sent'; // Accepted by SMTP; inbox placement is not guaranteed.
    } catch(Throwable $error) {
        // A timeout may occur after Gmail accepted the message. Do not retry automatically.
        $status=$attempted?'unknown':'failed';
    }
    $pdo->prepare('UPDATE email_outbox SET status=? WHERE id=?')->execute([$status,$row['id']]);
    return $status;
}
function customerEmailNotice(string $status): string {
    return match($status) {
        'sent'=>'ส่งอีเมลแล้ว กรุณาตรวจกล่องจดหมายและจดหมายขยะ',
        'sending'=>'กำลังดำเนินการส่งอีเมล',
        'unknown'=>'บันทึกข้อมูลแล้ว แต่ยังยืนยันการส่งอีเมลไม่ได้ กรุณาตรวจกล่องจดหมายหรือติดต่อร้าน',
        default=>'บันทึกข้อมูลแล้ว แต่ยังส่งอีเมลไม่ได้ กรุณาติดต่อร้านหากต้องการความช่วยเหลือ',
    };
}
