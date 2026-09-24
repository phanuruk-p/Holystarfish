<?php
require_once __DIR__ . '/includes/auth.php';

$brandName = 'Holystarfish';
$customer = currentCustomer();

if (!$customer) {
    redirectTo('login.php');
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>บัญชีของฉัน | <?php echo e($brandName); ?></title>
    <meta name="description" content="ข้อมูลบัญชีลูกค้า Holystarfish">
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
            <a href="account.php" aria-current="page">บัญชีของฉัน</a>
        </nav>
        <div class="header-actions">
            <div class="profile-menu">
                <button class="profile-trigger" type="button" aria-expanded="false" aria-label="เปิดเมนูโปรไฟล์">
                    <span class="profile-avatar"><?php echo e(customerInitial($customer['full_name'])); ?></span>
                    <span class="profile-name">บัญชีของฉัน</span>
                </button>
                <div class="profile-dropdown" role="menu">
                    <a href="account.php" role="menuitem">บัญชีของฉัน</a>
                    <a href="edit-profile.php" role="menuitem">แก้ไขโปรไฟล์</a>
                    <a href="logout.php" role="menuitem">ออกจากระบบ</a>
                </div>
            </div>
        </div>
    </header>

    <main>
        <section class="account-section">
            <div class="account-heading">
                <p class="eyebrow">My Account</p>
                <h1>สวัสดี <?php echo e($customer['full_name']); ?></h1>
                <p>ข้อมูลนี้ถูกบันทึกไว้ในฐานข้อมูลของร้าน สำหรับใช้ติดต่อและจัดส่งสินค้า</p>
                <?php if (isset($_GET['registered'])): ?>
                    <div class="form-success" role="status">สมัครสมาชิกเรียบร้อยแล้ว</div>
                <?php endif; ?>
                <?php if (isset($_GET['updated'])): ?>
                    <div class="form-success" role="status">อัปเดตโปรไฟล์เรียบร้อยแล้ว</div>
                <?php endif; ?>
                <div class="account-actions">
                    <a class="button primary" href="recommendations.php">สินค้าที่แนะนำสำหรับคุณ</a>
                    <a class="button primary" href="orders.php">ประวัติคำสั่งซื้อ</a>
                    <a class="button secondary" href="edit-profile.php">แก้ไขโปรไฟล์</a>
                </div>
            </div>

            <div class="account-panel">
                <div>
                    <span>อีเมล</span>
                    <strong><?php echo e($customer['email']); ?></strong>
                </div>
                <div>
                    <span>เบอร์โทร</span>
                    <strong><?php echo e($customer['phone']); ?></strong>
                </div>
                <div>
                    <span>Line ID</span>
                    <strong><?php echo e($customer['line_id'] ?: '-'); ?></strong>
                </div>
                <div>
                    <span>วันที่สมัคร</span>
                    <strong><?php echo e(date('d/m/Y H:i', strtotime($customer['created_at']))); ?></strong>
                </div>
                <div class="account-wide">
                    <span>ที่อยู่จัดส่ง</span>
                    <strong><?php echo nl2br(e($customer['address'] ?: '-')); ?></strong>
                </div>
                <div class="account-wide">
                    <span>ข้อมูลเพิ่มเติม</span>
                    <strong><?php echo nl2br(e($customer['note'] ?: '-')); ?></strong>
                </div>
            </div>
        </section>
    </main>
    <script src="assets/script.js"></script>
</body>
</html>
