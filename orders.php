<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/orders.php';
$customer = currentCustomer();
if (!$customer) redirectTo('login.php');
$stmt = db()->prepare('SELECT * FROM orders WHERE customer_id = ? ORDER BY id DESC');
$stmt->execute([(int) $customer['id']]);
$orders = $stmt->fetchAll();
$successId = $_SESSION['order_success'] ?? null;
$emailResult=$_SESSION['order_email_result']??null;
unset($_SESSION['order_email_result']);
unset($_SESSION['order_success']);
$successOrder = null;
foreach ($orders as $order) if ((int) $order['id'] === $successId) $successOrder = $order;
$pageTitle = 'ประวัติคำสั่งซื้อ';
require __DIR__ . '/includes/order-header.php';
?>
<main class="order-page"><p class="eyebrow">MY ORDERS</p><h1>ประวัติคำสั่งซื้อ</h1><p class="order-intro">รายการที่คุณสั่งซื้อถูกบันทึกไว้ที่นี่ กลับมาดูได้ทุกเมื่อ</p>
<?php if($successOrder && ($emailResult['id']??null)===$successId): ?><p role="status" class="order-note"><?= ($emailResult['status']??'')==='sent'?'ส่งรายละเอียดคำสั่งซื้อทางอีเมลแล้ว กรุณาตรวจกล่องจดหมายและจดหมายขยะ':'บันทึกคำสั่งซื้อสำเร็จแล้ว แต่ยังยืนยันการส่งอีเมลไม่ได้ ดูรายละเอียดคำสั่งซื้อด้านล่างได้เลย' ?></p><?php endif ?>
<?php if ($successOrder): ?><div class="form-success" role="status"><strong>สั่งซื้อสำเร็จ · HS-<?php echo str_pad((string) $successId, 6, '0', STR_PAD_LEFT); ?></strong><p>บันทึกคำสั่งซื้อเรียบร้อยแล้ว ช่องทางชำระเงิน: <?php echo e(paymentMethods()[$successOrder['payment_method']]); ?></p></div>
<script>
// Clear purchased quantities only after the server confirms persistence.
try {
 const purchased = <?php echo $successOrder['items_json']; ?>;
 const catalog = <?php echo json_encode(require __DIR__ . '/includes/products.php', JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
 const cart = JSON.parse(localStorage.getItem('holystarfishCart') || '[]');
 const remaining = cart.map(item => { const product = catalog.find(p => p.name === item.name || p.legacy_name === item.name); const bought = purchased.find(p => p.id === product?.id); return { ...item, quantity: item.quantity - (bought?.quantity || 0) }; }).filter(item => item.quantity > 0);
 localStorage.setItem('holystarfishCart', JSON.stringify(remaining));
} catch (_) { /* A storage failure must not hide a successfully saved order. */ }
</script><?php endif; ?>
<?php if (!$orders): ?><div class="order-panel"><h2>ยังไม่มีคำสั่งซื้อ</h2><p>เมื่อยืนยันสั่งซื้อ รายการจะปรากฏที่นี่</p></div><?php endif; ?>
<?php foreach ($orders as $order): ?>
<article class="order-panel order-history" id="order-<?php echo (int) $order['id']; ?>">
<div class="order-heading"><div><h2>HS-<?php echo str_pad((string) $order['id'], 6, '0', STR_PAD_LEFT); ?></h2><p><?php echo e((new DateTimeImmutable($order['created_at'], new DateTimeZone('UTC')))->setTimezone(new DateTimeZone('Asia/Bangkok'))->format('d/m/Y H:i')); ?></p></div><span class="order-status"><?php echo e($order['status']); ?></span></div>
<?php foreach (json_decode($order['items_json'], true) as $item): ?><div class="order-item"><img src="<?php echo e($item['image']); ?>" alt="<?php echo e($item['name']); ?>" loading="lazy"><div><strong><?php echo e($item['name']); ?></strong><p><?php echo (int) $item['quantity']; ?> ชิ้น × ฿<?php echo number_format($item['price']); ?></p></div><strong>฿<?php echo number_format($item['price'] * $item['quantity']); ?></strong></div><?php endforeach; ?>
<div class="order-totals"><p><span>ยอดสินค้า / VAT 7%</span><span>฿<?php echo number_format($order['subtotal']); ?> / ฿<?php echo number_format($order['vat']); ?></span></p><p class="order-total"><span>ยอดรวม · จัดส่งฟรี</span><strong>฿<?php echo number_format($order['total']); ?></strong></p></div>
<p><strong>ชำระผ่าน:</strong> <?php echo e(paymentMethods()[$order['payment_method']]); ?></p>
<?php if ($order['payment_method'] !== 'cod'): ?><p class="order-note">รอชำระเงิน — กรุณาติดต่อร้านเพื่อรับรายละเอียดการโอนเงิน ระบบยังไม่ยืนยันว่าชำระแล้ว</p><?php endif; ?>
<details><summary>ข้อมูลจัดส่ง</summary><p><?php echo e($order['recipient']); ?> · <?php echo e($order['phone']); ?></p><p><?php echo nl2br(e($order['address'])); ?></p></details>
</article><?php endforeach; ?>
<a class="button primary" href="products.php">เลือกซื้อสินค้าต่อ</a></main></body></html>
