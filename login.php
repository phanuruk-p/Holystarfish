<?php
require_once __DIR__ . '/includes/auth.php';

$brandName = 'Holystarfish';
$customer = currentCustomer();
$error = '';

if ($customer) {
    redirectTo('account.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (loginCustomer((string) ($_POST['email'] ?? ''), (string) ($_POST['password'] ?? ''))) {
        redirectTo('account.php');
    }

    $error = 'อีเมลหรือรหัสผ่านไม่ถูกต้อง';
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ล็อกอิน | <?php echo e($brandName); ?></title>
    <meta name="description" content="ล็อกอินบัญชีลูกค้า Holystarfish">
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
            <a href="register.php">สมัครสมาชิก</a>
        </nav>
        <div class="header-actions">
            <a class="header-action light" href="login.php">ล็อกอิน</a>
            <a class="header-action" href="register.php">สมัครสมาชิก</a>
        </div>
    </header>

    <main>
        <section class="auth-section auth-compact">
            <div class="auth-copy">
                <p class="eyebrow">Welcome Back</p>
                <h1>ล็อกอิน</h1>
                <p>เข้าสู่ระบบเพื่อดูข้อมูลสมาชิกและใช้ข้อมูลติดต่อเดิมในการสั่งซื้อครั้งต่อไป</p>
            </div>

            <form class="auth-form" action="login.php" method="post">
                <?php if (isset($_GET['logged_out'])): ?>
                    <div class="form-success" role="status">ออกจากระบบเรียบร้อยแล้ว</div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="form-alert" role="alert">
                        <p><?php echo e($error); ?></p>
                    </div>
                <?php endif; ?>

                <label>
                    อีเมล
                    <input type="email" name="email" value="<?php echo e($_POST['email'] ?? ''); ?>" placeholder="you@example.com" required>
                </label>
                <label>
                    รหัสผ่าน
                    <input type="password" name="password" placeholder="รหัสผ่านของคุณ" required>
                </label>
                <button class="button primary" type="submit">ล็อกอิน</button>
                <p class="auth-switch">ยังไม่มีบัญชี? <a href="register.php">สมัครสมาชิก</a></p>
            </form>
        </section>
    </main>
</body>
</html>
