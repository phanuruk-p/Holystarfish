<?php
require_once __DIR__ . '/includes/auth.php';

$brandName = 'Holystarfish';
$tagline = 'Jewelry that shines like the sea';
$customer = currentCustomer();

function priceValue(string $price): int
{
    return (int) preg_replace('/\D+/', '', $price);
}

$categories = [
    [
        'title' => 'Holystarfish Signature',
        'description' => 'เครื่องประดับดีไซน์ของแบรนด์ เน้นความละมุน หรู และใส่ได้ทุกวัน',
        'items' => ['ต่างหูไข่มุก', 'สร้อยดาวทะเล', 'แหวนคลื่นทะเล'],
    ],
    [
        'title' => 'Curated Classics',
        'description' => 'ดีไซน์คลาสสิกในโทนทองและเงิน เติมรายละเอียดให้ลุคที่มั่นใจ',
        'items' => ['สร้อยคอดีไซน์คลาสสิก', 'กำไลและกำไลข้อมือ', 'แหวนและจี้สะสม'],
    ],
    [
        'title' => 'Gift & Occasion',
        'description' => 'เซ็ตของขวัญสำหรับวันเกิด ครบรอบ รับปริญญา และโอกาสพิเศษ',
        'items' => ['Gift Box', 'Mini Card', 'Premium Wrapping'],
    ],
];

$products = require __DIR__ . '/includes/products.php';
$products = array_slice($products, 0, 4);
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="<?php echo e($_SESSION['csrf_token']); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $brandName; ?> | ร้านเครื่องประดับ</title>
    <meta name="description" content="ร้านเครื่องประดับ Holystarfish จำหน่ายเครื่องประดับดีไซน์แบรนด์ตัวเองและสินค้าดีไซน์คัดสรร">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=IBM+Plex+Sans+Thai:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/style.css?v=orders-1">
</head>
<body data-customer-logged-in="<?php echo $customer ? '1' : '0'; ?>">
    <header class="site-header" id="top">
        <a class="brand" href="#top" aria-label="กลับไปหน้าแรก">
            <img src="assets/logo-holystarfish.jpg" alt="โลโก้ Holystarfish">
            <span><?php echo $brandName; ?></span>
        </a>
        <nav class="nav-links" aria-label="เมนูหลัก">
            <a href="#collections">คอลเลกชัน</a>
            <a href="#products">สินค้า</a>
            <a href="products.php">สินค้าอื่นๆ</a>
            <a href="#service">บริการ</a>
            <a href="newsletter.php">ข่าวสาร</a>
            <a href="#contact">ติดต่อ</a>
            <?php if ($customer): ?>
            <?php else: ?>
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
        <section class="hero">
            <div class="hero-copy">
                <p class="eyebrow">Everyday Jewelry • Thoughtful Gifts</p>
                <h1><?php echo $brandName; ?></h1>
                <div class="hero-message">
                    <p class="hero-slogan">ทุกประกาย <span>บอกเล่าเรื่องราวของคุณ</span></p>
                    <p class="hero-description">
                        เครื่องประดับที่สะท้อนตัวตน ผ่านงานดีไซน์ของ Holystarfish
                        และดีไซน์คัดสรรสำหรับทุกช่วงเวลาที่มีความหมาย
                    </p>
                </div>
                <div class="hero-actions">
                    <a class="button primary" href="products.php#catalog-search">ค้นหาสินค้าที่ใช่</a>
                    <a class="button secondary" href="#collections">สำรวจคอลเลกชัน</a>
                </div>
            </div>
            <div class="hero-visual jewelry-hero" aria-label="คอลเลกชันเครื่องประดับ Holystarfish">
                <img src="assets/products/pearl-tide-earrings.jpg" alt="ต่างหูมุกหยดน้ำ ละอองคลื่น บนพื้นหินสีครีม" width="1000" height="1000" fetchpriority="high">
                <a class="hero-product-caption" href="products.php"><span>THE PEARL EDIT</span><strong>ความละมุนที่สวมใส่ได้</strong><span>สำรวจคอลเลกชัน ↗</span></a>
            </div>
        </section>

        <section class="trust-strip" aria-label="จุดเด่นร้าน">
            <div>
                <strong>Authentic Check</strong>
                <span>ตรวจเช็กสินค้าแบรนด์เนมก่อนส่ง</span>
            </div>
            <div>
                <strong>Premium Packing</strong>
                <span>แพ็กของขวัญหรูทุกออเดอร์</span>
            </div>
            <div>
                <strong>Thai Support</strong>
                <span>คุยง่าย ดูแลหลังการขาย</span>
            </div>
        </section>

        <section class="section" id="collections">
            <div class="section-heading">
                <p class="eyebrow">Collections</p>
                <h2>เลือกประกายที่ใช่สำหรับคุณ</h2>
                <p>จัดหมวดสินค้าให้ลูกค้าเข้าใจง่าย ทั้งงานแบรนด์ตัวเอง แบรนด์เนม และเซ็ตของขวัญ</p>
            </div>
            <div class="collection-grid">
                <?php foreach ($categories as $category): ?>
                    <article class="collection-card">
                        <h3><?php echo $category['title']; ?></h3>
                        <p><?php echo $category['description']; ?></p>
                        <ul>
                            <?php foreach ($category['items'] as $item): ?>
                                <li><?php echo $item; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>

        <section class="section product-section" id="products">
            <div class="section-heading">
                <p class="eyebrow">Featured Pieces</p>
                <h2>ชิ้นโปรดที่อยากให้คุณลอง</h2>
                <p>ประกายเล็ก ๆ ที่ทำให้ทุกวันพิเศษขึ้น เลือกชิ้นที่เป็นคุณ</p>
            </div>
            <div class="product-grid">
                <?php foreach ($products as $product): ?>
                    <article
                        class="product-card"
                        data-legacy-name="<?php echo e($product['legacy_name']); ?>"
                        data-name="<?php echo htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8'); ?>"
                        data-type="<?php echo htmlspecialchars($product['type'], ENT_QUOTES, 'UTF-8'); ?>"
                        data-price="<?php echo htmlspecialchars($product['price'], ENT_QUOTES, 'UTF-8'); ?>"
                        data-price-value="<?php echo priceValue($product['price']); ?>"
                        data-image="<?php echo htmlspecialchars($product['image'], ENT_QUOTES, 'UTF-8'); ?>"
                        data-badge="<?php echo htmlspecialchars($product['badge'], ENT_QUOTES, 'UTF-8'); ?>"
                        data-detail="<?php echo htmlspecialchars($product['detail'], ENT_QUOTES, 'UTF-8'); ?>"
                    >
                        <button class="product-image product-open" type="button" aria-label="ดูรายละเอียด <?php echo htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8'); ?>">
                            <img loading="lazy" decoding="async" width="1000" height="1000" src="<?php echo $product['image']; ?>" alt="<?php echo $product['name']; ?>">
                            <span><?php echo $product['badge']; ?></span>
                        </button>
                        <div class="product-info">
                            <p><?php echo $product['type']; ?></p>
                            <button class="product-title product-open" type="button">
                                <h3><?php echo $product['name']; ?></h3>
                            </button>
                            <div class="product-row">
                                <strong><?php echo $product['price']; ?></strong>
                                <button class="icon-button add-to-cart" type="button" aria-label="เพิ่ม <?php echo $product['name']; ?> ลงตะกร้า" data-product="<?php echo $product['name']; ?>">
                                    +
                                </button>
                            </div>
                            <button class="view-detail product-open" type="button">
                                ดูรายละเอียด <span aria-hidden="true">→</span>
                            </button>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
            <div class="section-more">
                <a class="button secondary" href="products.php">ดูสินค้าทั้งหมด</a>
            </div>
        </section>

        <section class="service-band" id="service">
            <div>
                <p class="eyebrow">Service</p>
                <h2>ซื้อเครื่องประดับให้มั่นใจขึ้น</h2>
            </div>
            <div class="service-list">
                <div>
                    <span>01</span>
                    <h3>ปรึกษาก่อนซื้อ</h3>
                    <p>ช่วยเลือกโทน สี และชิ้นที่เหมาะกับสไตล์หรือโอกาสพิเศษ</p>
                </div>
                <div>
                    <span>02</span>
                    <h3>ตรวจเช็กสินค้า</h3>
                    <p>สินค้าแบรนด์เนมผ่านการตรวจสภาพและความเรียบร้อยก่อนจัดส่ง</p>
                </div>
                <div>
                    <span>03</span>
                    <h3>แพ็กพรีเมียม</h3>
                    <p>กล่อง ของตกแต่ง และการ์ดข้อความสำหรับมอบเป็นของขวัญ</p>
                </div>
            </div>
        </section>

        <section class="section contact-section" id="contact">
            <div class="contact-copy">
                <p class="eyebrow">Contact</p>
                <h2>สนใจชิ้นไหน ทักมาคุยได้เลย</h2>
                <p>กรอกข้อมูลไว้เป็นแบบฟอร์มหน้าร้าน หรือเปลี่ยนเป็นลิงก์ Line / Facebook / Instagram ของร้านจริงได้ทันที</p>
                <div class="contact-methods">
                    <a href="tel:0000000000">โทร: 000-000-0000</a>
                    <a href="mailto:hello@holystarfish.com">hello@holystarfish.com</a>
                    <a href="https://www.instagram.com/" target="_blank" rel="noreferrer">Instagram</a>
                </div>
            </div>
            <form class="contact-form" action="#" method="post">
                <label>
                    ชื่อ
                    <input type="text" name="name" placeholder="ชื่อของคุณ" value="<?php echo e($customer['full_name'] ?? ''); ?>">
                </label>
                <label>
                    อีเมล
                    <input type="email" name="email" placeholder="you@example.com" value="<?php echo e($customer['email'] ?? ''); ?>">
                </label>
                <label>
                    เบอร์โทร
                    <input type="tel" name="phone" placeholder="0812345678" value="<?php echo e($customer['phone'] ?? ''); ?>">
                </label>
                <label>
                    สินค้าที่สนใจ
                    <select name="interest">
                        <option>Holystarfish Signature</option>
                        <option>Brand Name Selection</option>
                        <option>Gift Set</option>
                    </select>
                </label>
                <label>
                    ข้อความ
                    <textarea name="message" rows="4" placeholder="อยากสอบถามสินค้า รุ่น สี หรือราคา"></textarea>
                </label>
                <button class="button primary" type="submit">ส่งข้อความ</button>
            </form>
        </section>
    </main>

    <footer class="site-footer">
        <div>
            <strong><?php echo $brandName; ?></strong>
            <span><?php echo strtoupper($tagline); ?></span>
        </div>
        <a href="#top">กลับขึ้นด้านบน</a>
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
</body>
</html>
