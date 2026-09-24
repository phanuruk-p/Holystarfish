<?php
declare(strict_types=1);
require_once __DIR__.'/email-template.php';
require_once __DIR__.'/vendor/phpmailer/Exception.php';
require_once __DIR__.'/vendor/phpmailer/PHPMailer.php';
require_once __DIR__.'/vendor/phpmailer/SMTP.php';

function smtpConfigPath(): string {
    return dirname(__DIR__).'/data/smtp.local.php';
}
function smtpConfig(): array {
    $path=smtpConfigPath();
    return is_file($path) ? require $path : ['email'=>'','password'=>'','site_url'=>'http://localhost:8000'];
}
function validatedSmtpConfig(array $input, array $existing): array {
    $email=strtolower(trim(is_string($input['email']??null)?$input['email']:''));
    if(!filter_var($email,FILTER_VALIDATE_EMAIL) || !str_ends_with($email,'@gmail.com')) throw new InvalidArgumentException('กรุณากรอก Gmail ผู้ส่ง เช่น yourname@gmail.com');
    $password=preg_replace('/\s+/','',is_string($input['app_password']??null)?$input['app_password']:'');
    if($password==='') {
        if($email!==($existing['email']??'')) throw new InvalidArgumentException('เมื่อเปลี่ยน Gmail กรุณากรอกรหัสแอปของบัญชีใหม่ด้วย');
        $password=$existing['password']??'';
    }
    if(!preg_match('/^[a-zA-Z]{16}$/D',$password)) throw new InvalidArgumentException('กรุณากรอกรหัสแอป 16 ตัวจาก Google ไม่ใช่รหัสผ่าน Gmail');
    $url=rtrim(trim(is_string($input['site_url']??null)?$input['site_url']:''),'/');
    if(!filter_var($url,FILTER_VALIDATE_URL)||!in_array(parse_url($url,PHP_URL_SCHEME),['http','https'],true)||parse_url($url,PHP_URL_USER)||parse_url($url,PHP_URL_QUERY)||parse_url($url,PHP_URL_FRAGMENT)) throw new InvalidArgumentException('กรุณากรอก URL เว็บไซต์ เช่น http://localhost:8000 หรือ https://โดเมนของร้าน');
    return ['email'=>$email,'password'=>$password,'site_url'=>$url];
}
function saveSmtpConfig(array $config): void {
    $path=smtpConfigPath(); $temporary=$path.'.'.bin2hex(random_bytes(6)).'.tmp';
    if(file_put_contents($temporary,"<?php\n// Private SMTP settings. Never share or publish this file.\nreturn ".var_export($config,true).";\n",LOCK_EX)===false) throw new RuntimeException('ไม่สามารถบันทึกการตั้งค่าได้');
    @chmod($temporary,0600);
    if(!rename($temporary,$path)) { @unlink($temporary); throw new RuntimeException('ไม่สามารถบันทึกการตั้งค่าได้'); }
}
function buildSmtpMessage(array $config,string $recipient,string $subject,string $html): \PHPMailer\PHPMailer\PHPMailer {
    $mail=new \PHPMailer\PHPMailer\PHPMailer(true);
    $mail->isSMTP();
    $mail->Host='smtp.gmail.com';
    $mail->Port=587;
    $mail->SMTPAuth=true;
    $mail->SMTPSecure=\PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Username=$config['email'];
    $mail->Password=$config['password'];
    $mail->SMTPDebug=0;
    $mail->Timeout=20;
    $mail->CharSet='UTF-8';
    $mail->setFrom($config['email'],'Holystarfish');
    $mail->addAddress($recipient);
    $mail->Subject=$subject;
    $mail->isHTML(true);
    $logoUrl=htmlspecialchars($config['site_url'].'/assets/logo-holystarfish.jpg',ENT_QUOTES,'UTF-8');
    if(str_contains($html,'src="'.$logoUrl.'"')) {
        $mail->addEmbeddedImage(dirname(__DIR__).'/assets/logo-holystarfish.jpg','brand-logo@holystarfish','holystarfish-logo.jpg');
        $html=str_replace('src="'.$logoUrl.'"','src="cid:brand-logo@holystarfish"',$html);
    }
    // Embed only catalog images from the local assets directory. No remote fetches.
    $catalog=require __DIR__.'/products.php';
    foreach($catalog as $product) {
        $url=htmlspecialchars($config['site_url'].'/'.$product['image'],ENT_QUOTES,'UTF-8');
        if(!str_contains($html,'src="'.$url.'"')) continue;
        $root=realpath(dirname(__DIR__).'/assets/products');
        $path=realpath(dirname(__DIR__).'/'.$product['image']);
        if(!$root || !$path || !str_starts_with($path,$root.DIRECTORY_SEPARATOR)) throw new RuntimeException('ไม่พบภาพสินค้า');
        $cid='product-'.hash('sha256',$product['image']).'@holystarfish';
        $mail->addEmbeddedImage($path,$cid,basename($path));
        $html=str_replace('src="'.$url.'"','src="cid:'.$cid.'"',$html);
    }
    $mail->Body=$html;
    $mail->AltBody=html_entity_decode(strip_tags(str_replace(['<br>','</p>','</h1>','</h2>'],"\n",$html)),ENT_QUOTES,'UTF-8');
    return $mail;
}
function smtpFailureMessage(Throwable $error): string {
    // Never display/log raw SMTP responses, credentials, or message bodies.
    $message=strtolower($error->getMessage());
    if(str_contains($message,'authenticate')) return 'Gmail ไม่ยอมรับการเข้าสู่ระบบ ตรวจอีเมลผู้ส่งและรหัสแอป 16 ตัว แล้วลองอีกครั้ง';
    if(str_contains($message,'connect')||str_contains($message,'certificate')) return 'เชื่อมต่อ Gmail ไม่สำเร็จ ตรวจอินเทอร์เน็ต การเชื่อมต่อพอร์ต 587 และใบรับรองของ PHP';
    return 'ส่งไม่สำเร็จ กรุณาตรวจการตั้งค่า Gmail และลองอีกครั้ง ยังไม่ยืนยันว่ามีอีเมลถึงปลายทาง';
}
