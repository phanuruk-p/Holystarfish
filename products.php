<?php
require_once __DIR__ . '/includes/auth.php';

$brandName = 'Holystarfish';
$customer = currentCustomer();

function priceValue(string $price): int { return (int) preg_replace('/\D+/', '', $price); }

$products = require __DIR__ . '/includes/products.php';
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="<?php echo e($_SESSION['csrf_token']); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>สินค้าทั้งหมด | <?php echo $brandName; ?></title>
    <meta name="description" content="เลือกชมเครื่องประดับ Holystarfish แบรนด์ตัวเอง ดีไซน์คัดสรร และเซ็ตของขวัญ">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=IBM+Plex+Sans+Thai:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/style.css?v=orders-1">
</head>
<body data-customer-logged-in="<?php echo $customer ? '1' : '0'; ?>">
    <header class="site-header" id="top">
        <a class="brand" href="index.php" aria-label="กลับไปหน้าแรก">
            <img src="assets/logo-holystarfish.jpg" alt="โลโก้ Holystarfish">
            <span><?php echo $brandName; ?></span>
        </a>
        <nav class="nav-links" aria-label="เมนูหลัก">
            <a href="index.php">หน้าแรก</a>
            <a href="products.php" aria-current="page">สินค้าทั้งหมด</a>
            <a href="index.php#service">บริการ</a>
            <a href="index.php#contact">ติดต่อ</a>
            <?php if (!$customer): ?>
                <a href="register.php">สมัครสมาชิก</a>
                <a href="login.php">ล็อกอิน</a>
            <?php endif; ?>
        </nav>
        <div class="header-actions">
            <button class="cart-open-button header-cart-button" type="button" aria-label="เปิดตะกร้าสินค้า">
                <span class="cart-icon" aria-hidden="true"></span>
                <span>ตะกร้า</span>
                <strong class="cart-count">0</strong>
            </button>
            <?php if ($customer): ?>
                <div class="profile-menu">
                    <button class="profile-trigger" type="button" aria-expanded="false" aria-label="เปิดเมนูโปรไฟล์">
                        <span class="profile-avatar"><?php echo e(customerInitial($customer['full_name'])); ?></span>
                        <span class="profile-name">บัญชีของฉัน</span>
                    </button>
                    <div class="profile-dropdown" role="menu">
                        <a href="account.php" role="menuitem">บัญชีของฉัน</a>
                        <a href="orders.php" role="menuitem">ประวัติคำสั่งซื้อ</a>
                        <a href="edit-profile.php" role="menuitem">แก้ไขโปรไฟล์</a>
                        <a href="logout.php" role="menuitem">ออกจากระบบ</a>
                    </div>
                </div>
            <?php else: ?>
                <a class="header-action light" href="login.php">ล็อกอิน</a>
                <a class="header-action" href="register.php">สมัครสมาชิก</a>
            <?php endif; ?>
        </div>
    </header>

    <main>
        <section class="catalog-hero">
            <p class="eyebrow">THE EVERYDAY GLOW COLLECTION</p>
            <h1>ประกายที่ใช่<br><span>ในแบบของคุณ</span></h1>
            <p>จากเครื่องประดับชิ้นเล็กสำหรับทุกวัน ถึงของขวัญแทนความรู้สึกดี ๆ</p>
        </section>

        <section class="catalog-section" aria-labelledby="catalog-title">
            <div class="catalog-toolbar">
                <div>
                    <p class="eyebrow">Our Pieces</p>
                    <h2 id="catalog-title">เลือกชมเครื่องประดับ</h2>
                </div>
                <div class="product-filters" aria-label="กรองหมวดสินค้า">
                    <button class="filter-button active" type="button" data-filter="all">ทั้งหมด</button>
                    <button class="filter-button" type="button" data-filter="signature">แบรนด์ตัวเอง</button>
                    <button class="filter-button" type="button" data-filter="selected">ดีไซน์คัดสรร</button>
                    <button class="filter-button" type="button" data-filter="gift">ของขวัญ</button>
                </div>
            </div>

            <form class="catalog-search" id="catalog-search" role="search">
                <label for="product-query"><strong>กำลังมองหาเครื่องประดับแบบไหน?</strong></label>
                <p id="search-help">บอกประเภท โทนสี สไตล์ หรือโอกาสที่อยากใส่ พร้อมงบประมาณ ระบบจะคัดกรองจากคำสำคัญในสินค้า</p>
                <div class="search-row">
                    <input id="product-query" type="search" value="<?= e(is_string($_GET['q'] ?? null) ? $_GET['q'] : '') ?>" placeholder="เช่น ต่างหูไข่มุก ใส่ทำงาน งบไม่เกิน 1,500" aria-describedby="search-help">
                    <button class="button primary" type="submit">ค้นหาสินค้า</button>
                </div>
                <div class="search-examples" aria-label="ตัวอย่างการค้นหา">
                    <button type="button" data-query="ต่างหูไข่มุก ทำงาน ไม่เกิน 1500">ต่างหูไข่มุกใส่ทำงาน</button>
                    <button type="button" data-query="มินิมอล ไม่เกิน 1000">มินิมอล งบ 1,000</button>
                    <button type="button" data-query="ของขวัญ โทนโรสโกลด์">ของขวัญโทนโรสโกลด์</button>
                </div>
                <details open>
                    <summary>ตัวกรองแบบละเอียด</summary>
                    <div class="search-fields">
                        <?php foreach (['kind' => ['ประเภท', ['ต่างหู', 'สร้อยคอ', 'แหวน', 'กำไล', 'เซ็ตของขวัญ']], 'color' => ['โทนสี', ['ทอง', 'เงิน', 'ขาว', 'โรสโกลด์', 'ดำ', 'ฟ้า']], 'style' => ['สไตล์', ['มินิมอล', 'คลาสสิก', 'หวาน', 'หรูหรา', 'แฟชั่น']], 'occasion' => ['โอกาส', ['ทุกวัน', 'ทำงาน', 'งานเลี้ยง', 'เที่ยว', 'ของขวัญ']]] as $key => [$label, $options]): ?>
                            <label><?php echo $label; ?><select name="<?php echo $key; ?>"><option value="">ทั้งหมด</option><?php foreach ($options as $option): ?><option><?php echo $option; ?></option><?php endforeach; ?></select></label>
                        <?php endforeach; ?>
                        <label>ราคาต่ำสุด (บาท)<input name="min" type="number" min="0" step="1" placeholder="0"></label>
                        <label>ราคาสูงสุด (บาท)<input name="max" type="number" min="0" step="1" placeholder="ไม่จำกัด"></label>
                        <label>เรียงสินค้า<select name="sort"><option value="relevance">ตรงความต้องการ</option><option value="low">ราคาต่ำไปสูง</option><option value="high">ราคาสูงไปต่ำ</option><option value="name">ชื่อสินค้า A–Z</option></select></label>
                    </div>
                </details>
                <button class="search-reset" type="reset">ล้างการค้นหาและตัวกรองทั้งหมด</button>
            </form>
            <p class="catalog-count" aria-live="polite">แสดงสินค้า <?php echo count($products); ?> รายการ</p>
            <p class="search-summary" aria-live="polite"></p>
            <div class="search-empty" hidden><h3>ยังไม่พบสินค้าที่ตรงความต้องการ</h3><p>ลองลดคำค้น เปลี่ยนโทนสี หรือเพิ่มงบประมาณ แล้วค้นหาอีกครั้ง</p><button type="button" class="button secondary" id="clear-empty">ดูสินค้าทั้งหมด</button></div>
            <p class="catalog-demo-note">คอลเลกชันตัวอย่าง • ภาพจำลองและราคาตัวอย่างสำหรับทดลองเลือกซื้อ</p>

            <div class="product-grid catalog-grid">
                <?php foreach ($products as $product): ?>
                    <article
                        class="product-card"
                        data-kind="<?php echo e($product['kind']); ?>"
                        data-color="<?php echo e($product['color']); ?>"
                        data-style="<?php echo e($product['style']); ?>"
                        data-occasion="<?php echo e($product['occasion']); ?>"
                        data-tags="<?php echo e($product['tags']); ?>"
                        data-legacy-name="<?php echo e($product['legacy_name']); ?>"
                        data-category="<?php echo htmlspecialchars($product['category'], ENT_QUOTES, 'UTF-8'); ?>"
                        data-name="<?php echo htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8'); ?>"
                        data-type="<?php echo htmlspecialchars($product['type'], ENT_QUOTES, 'UTF-8'); ?>"
                        data-price="<?php echo htmlspecialchars($product['price'], ENT_QUOTES, 'UTF-8'); ?>"
                        data-price-value="<?php echo priceValue($product['price']); ?>"
                        data-image="<?php echo htmlspecialchars($product['image'], ENT_QUOTES, 'UTF-8'); ?>"
                        data-badge="<?php echo htmlspecialchars($product['badge'], ENT_QUOTES, 'UTF-8'); ?>"
                        data-detail="<?php echo htmlspecialchars($product['detail'], ENT_QUOTES, 'UTF-8'); ?>"
                    >
                        <button class="product-image product-open" type="button" aria-label="ดูรายละเอียด <?php echo htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8'); ?>">
                            <img loading="lazy" decoding="async" width="1000" height="1000" src="<?php echo htmlspecialchars($product['image'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8'); ?>">
                            <span><?php echo htmlspecialchars($product['badge'], ENT_QUOTES, 'UTF-8'); ?></span>
                        </button>
                        <div class="product-info">
                            <p><?php echo htmlspecialchars($product['type'], ENT_QUOTES, 'UTF-8'); ?></p>
                            <p class="product-traits"><?php echo e($product['kind'] . ' · โทน' . $product['color'] . ' · ' . $product['style']); ?></p>
                            <button class="product-title product-open" type="button">
                                <h3><?php echo htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8'); ?></h3>
                            </button>
                            <div class="product-row">
                                <strong><?php echo htmlspecialchars($product['price'], ENT_QUOTES, 'UTF-8'); ?></strong>
                                <button class="icon-button add-to-cart" type="button" aria-label="เพิ่ม <?php echo htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8'); ?> ลงรายการสนใจ" data-product="<?php echo htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8'); ?>">+</button>
                            </div>
                            <button class="view-detail product-open" type="button">ดูรายละเอียด <span aria-hidden="true">→</span></button>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>
    </main>

    <footer class="site-footer">
        <div>
            <strong><?php echo $brandName; ?></strong>
            <span>JEWELRY THAT SHINES LIKE THE SEA</span>
        </div>
        <a href="index.php">กลับหน้าแรก</a>
    </footer>

    <div class="cart-toast" role="status" aria-live="polite"></div>
    <dialog class="cart-modal" aria-labelledby="cart-title">
        <div class="cart-modal-head">
            <div>
                <p class="eyebrow">Shopping Cart</p>
                <h2 id="cart-title">ตะกร้าสินค้า</h2>
            </div>
            <button class="modal-close cart-close" type="button" aria-label="ปิดตะกร้า">×</button>
        </div>
        <div class="cart-body">
            <div class="cart-empty">
                <strong>ยังไม่มีสินค้าในตะกร้า</strong>
                <span>กดปุ่ม + ที่สินค้าที่ต้องการ แล้วรายการจะมาอยู่ตรงนี้</span>
            </div>
            <div class="cart-items" aria-live="polite"></div>
        </div>
        <div class="cart-summary">
            <div>
                <span>ยอดสินค้า</span>
                <strong class="cart-subtotal">฿0</strong>
            </div>
            <div>
                <span>VAT 7%</span>
                <strong class="cart-vat">฿0</strong>
            </div>
            <div class="cart-grand-total">
                <span>ยอดรวมสุทธิ</span>
                <strong class="cart-total">฿0</strong>
            </div>
            <button class="button primary cart-checkout" type="button">เลือกช่องทางชำระเงิน</button>
            <button class="button secondary cart-reset" type="button">รีเซ็ตตะกร้า</button>
        </div>
    </dialog>
    <dialog class="product-modal" aria-labelledby="modal-product-name">
        <button class="modal-close" type="button" aria-label="ปิดหน้าต่าง">×</button>
        <div class="modal-layout">
            <div class="modal-image-wrap">
                <img class="modal-image" src="" alt="">
                <span class="modal-badge"></span>
            </div>
            <div class="modal-content">
                <p class="modal-type"></p>
                <h2 id="modal-product-name"></h2>
                <p class="modal-detail"></p>
                <div class="modal-meta">
                    <span>ราคา</span>
                    <strong class="modal-price"></strong>
                </div>
                <button class="button primary modal-interest" type="button">เพิ่มลงตะกร้า</button>
            </div>
        </div>
    </dialog>
    <script type="application/json" id="current-catalog"><?php echo json_encode(require __DIR__ . '/includes/products.php', JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?></script>
    <script src="assets/script.js?v=orders-1"></script>
    <script src="assets/catalog-search.js"></script>
</body>
</html>
