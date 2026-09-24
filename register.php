<?php
require_once __DIR__ . '/includes/auth.php';

$brandName = 'Holystarfish';
$customer = currentCustomer();
$errors = [];

if ($customer) {
    redirectTo('account.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = isset($_POST['csrf_token']) && is_string($_POST['csrf_token']) && hash_equals($_SESSION['csrf_token'], $_POST['csrf_token']) ? registerCustomer($_POST) : ['success'=>false, 'errors'=>['แบบฟอร์มหมดอายุ กรุณาลองอีกครั้ง']];
    if ($result['success']) {
        redirectTo('questionnaire.php');
    }

    $errors = $result['errors'];
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>สมัครสมาชิก | <?php echo e($brandName); ?></title>
    <meta name="description" content="สมัครสมาชิก Holystarfish เพื่อบันทึกข้อมูลลูกค้าสำหรับการสั่งซื้อเครื่องประดับ">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=IBM+Plex+Sans+Thai:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <header class="site-header" id="top">
        <a class="brand" href="index.php" aria-label="กลับไปหน้าแรก">
            <img src="assets/logo-holystarfish.jpg" alt="โลโก้ Holystarfish">
            <span><?php echo e($brandName); ?></span>
        </a>
        <nav class="nav-links" aria-label="เมนูหลัก">
            <a href="index.php">หน้าแรก</a>
            <a href="products.php">สินค้าทั้งหมด</a>
            <a href="login.php">ล็อกอิน</a>
        </nav>
        <div class="header-actions">
            <a class="header-action light" href="login.php">ล็อกอิน</a>
            <a class="header-action" href="register.php">สมัครสมาชิก</a>
        </div>
    </header>

    <main>
        <section class="auth-section">
            <div class="auth-copy">
                <p class="eyebrow">Member Account</p>
                <h1>สมัครสมาชิก</h1>
                <p>กรอกข้อมูลสำหรับให้ร้านติดต่อ ยืนยันคำสั่งซื้อ และจัดส่งสินค้าได้สะดวกขึ้น</p>
            </div>

            <form class="auth-form" action="register.php" method="post">
                <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
                <p>สมัครสมาชิก → ตอบคำถาม 6 ข้อ → รับคำแนะนำสินค้า</p>
                <?php if ($errors): ?>
                    <div class="form-alert" role="alert">
                        <?php foreach ($errors as $error): ?>
                            <p><?php echo e($error); ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <div class="form-grid">
                    <label>
                        ชื่อ-นามสกุล
                        <input type="text" name="full_name" value="<?php echo e($_POST['full_name'] ?? ''); ?>" placeholder="ชื่อสำหรับติดต่อ" required>
                    </label>
                    <label>
                        อีเมล
                        <input type="email" name="email" value="<?php echo e($_POST['email'] ?? ''); ?>" placeholder="you@example.com" required>
                    </label>
                    <label>
                        เบอร์โทร
                        <input type="tel" name="phone" value="<?php echo e($_POST['phone'] ?? ''); ?>" placeholder="0812345678" required>
                    </label>
                    <label>
                        Line ID
                        <input type="text" name="line_id" value="<?php echo e($_POST['line_id'] ?? ''); ?>" placeholder="@holystarfish หรือ Line ID">
                    </label>
                    <label>
                        รหัสผ่าน
                        <input type="password" name="password" placeholder="อย่างน้อย 8 ตัวอักษร" required>
                    </label>
                    <label>
                        ยืนยันรหัสผ่าน
                        <input type="password" name="confirm_password" placeholder="กรอกรหัสผ่านอีกครั้ง" required>
                    </label>
                </div>

                <label>
                    ที่อยู่จัดส่ง
                    <textarea name="address" rows="3" placeholder="บ้านเลขที่ ถนน แขวง/ตำบล เขต/อำเภอ จังหวัด รหัสไปรษณีย์"><?php echo e($_POST['address'] ?? ''); ?></textarea>
                </label>
                <label>
                    ข้อมูลเพิ่มเติม
                    <textarea name="note" rows="3" placeholder="เช่น ช่วงเวลาที่สะดวกให้ติดต่อ สไตล์สินค้าที่ชอบ หรือคำขอพิเศษ"><?php echo e($_POST['note'] ?? ''); ?></textarea>
                </label>
                <button class="button primary" type="submit">สมัครสมาชิก</button>
                <p class="auth-switch">มีบัญชีแล้ว? <a href="login.php">ล็อกอินที่นี่</a></p>
            </form>
        </section>
    </main>
</body>
</html>
