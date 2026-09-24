<?php
require_once __DIR__ . '/includes/auth.php';

$brandName = 'Holystarfish';
$customer = currentCustomer();
$errors = [];

if (!$customer) {
    redirectTo('login.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = updateCustomerProfile((int) $customer['id'], $_POST);
    if ($result['success']) {
        redirectTo('account.php?updated=1');
    }

    $errors = $result['errors'];
    $customer = array_merge($customer, [
        'full_name' => (string) ($_POST['full_name'] ?? ''),
        'email' => (string) ($_POST['email'] ?? ''),
        'phone' => (string) ($_POST['phone'] ?? ''),
        'line_id' => (string) ($_POST['line_id'] ?? ''),
        'address' => (string) ($_POST['address'] ?? ''),
        'note' => (string) ($_POST['note'] ?? ''),
    ]);
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>แก้ไขโปรไฟล์ | <?php echo e($brandName); ?></title>
    <meta name="description" content="แก้ไขข้อมูลบัญชีลูกค้า Holystarfish">
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
            <a href="account.php">บัญชีของฉัน</a>
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
        <section class="auth-section">
            <div class="auth-copy">
                <p class="eyebrow">Edit Profile</p>
                <h1>แก้ไขโปรไฟล์</h1>
                <p>ปรับข้อมูลติดต่อและที่อยู่จัดส่งของคุณ ข้อมูลนี้จะถูกบันทึกลงฐานข้อมูลของร้าน</p>
            </div>

            <form class="auth-form" action="edit-profile.php" method="post">
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
                        <input type="text" name="full_name" value="<?php echo e($customer['full_name']); ?>" required>
                    </label>
                    <label>
                        อีเมล
                        <input type="email" name="email" value="<?php echo e($customer['email']); ?>" required>
                    </label>
                    <label>
                        เบอร์โทร
                        <input type="tel" name="phone" value="<?php echo e($customer['phone']); ?>" required>
                    </label>
                    <label>
                        Line ID
                        <input type="text" name="line_id" value="<?php echo e($customer['line_id']); ?>">
                    </label>
                    <label>
                        รหัสผ่านใหม่
                        <input type="password" name="password" placeholder="เว้นว่างไว้ถ้าไม่ต้องการเปลี่ยน">
                    </label>
                    <label>
                        ยืนยันรหัสผ่านใหม่
                        <input type="password" name="confirm_password" placeholder="กรอกเมื่อเปลี่ยนรหัสผ่าน">
                    </label>
                </div>

                <label>
                    ที่อยู่จัดส่ง
                    <textarea name="address" rows="3"><?php echo e($customer['address']); ?></textarea>
                </label>
                <label>
                    ข้อมูลเพิ่มเติม
                    <textarea name="note" rows="3"><?php echo e($customer['note']); ?></textarea>
                </label>
                <div class="form-actions">
                    <button class="button primary" type="submit">บันทึกโปรไฟล์</button>
                    <a class="button secondary" href="account.php">ยกเลิก</a>
                </div>
            </form>
        </section>
    </main>
    <script src="assets/script.js"></script>
</body>
</html>
