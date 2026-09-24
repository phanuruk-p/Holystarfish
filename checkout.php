<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/orders.php';
require_once __DIR__ . '/includes/order-email.php';
$customer = currentCustomer();
if (!$customer) redirectTo('login.php');
$error = '';
$values = ['recipient' => $customer['full_name'], 'phone' => $customer['phone'], 'address' => $customer['address'] ?? '', 'payment' => ''];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (!is_string($_POST['csrf'] ?? null) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf'])) throw new InvalidArgumentException('หน้าเว็บหมดอายุ กรุณารีเฟรชแล้วลองอีกครั้ง');
        $action = $_POST['action'] ?? '';
        if ($action === 'prepare') {
            $requested = json_decode((string) ($_POST['cart'] ?? ''), true, 32, JSON_THROW_ON_ERROR);
            if (!is_array($requested)) throw new InvalidArgumentException('ข้อมูลตะกร้าไม่ถูกต้อง');
            orderSummary($requested);
            $_SESSION['checkout'] = ['customer_id' => (int) $customer['id'], 'items' => $requested, 'token' => bin2hex(random_bytes(24))];
            redirectTo('checkout.php');
        }
        if ($action === 'place') {
            $values = array_intersect_key($_POST, $values) + $values;
            $draft = $_SESSION['checkout'] ?? [];
            if (($draft['customer_id'] ?? null) !== (int) $customer['id'] || !is_string($_POST['token'] ?? null) || !hash_equals($draft['token'] ?? '', $_POST['token'])) throw new InvalidArgumentException('ตะกร้าชุดนี้หมดอายุ กรุณาเริ่มสั่งซื้ออีกครั้ง');
            $orderId = placeOrder(db(), (int) $customer['id'], $draft['token'], $draft['items'], $values);
            $_SESSION['order_success'] = $orderId;
            try {
                $_SESSION['order_email_result']=['id'=>$orderId,'status'=>deliverOrderEmail($orderId,(int)$customer['id'])];
            } catch(Throwable $emailError) {
                // The saved order stays successful even if email delivery/storage fails.
                $_SESSION['order_email_result']=['id'=>$orderId,'status'=>'unknown'];
            }
            redirectTo('orders.php?id=' . $orderId);
        }
    } catch (InvalidArgumentException | JsonException $exception) {
        $error = $exception instanceof JsonException ? 'ข้อมูลตะกร้าไม่ถูกต้อง กรุณาลองอีกครั้ง' : $exception->getMessage();
    } catch (PDOException $exception) {
        error_log($exception->getMessage());
        $error = 'ยังบันทึกคำสั่งซื้อไม่ได้ กรุณาลองอีกครั้ง ตะกร้าของคุณยังอยู่';
    }
}
$draft = $_SESSION['checkout'] ?? [];
$summary = null;
if (($draft['customer_id'] ?? null) === (int) $customer['id']) {
    try { $summary = orderSummary($draft['items']); } catch (InvalidArgumentException $e) { $error = $e->getMessage(); }
}
$pageTitle = 'ยืนยันคำสั่งซื้อ';
require __DIR__ . '/includes/order-header.php';
?>
<main class="order-page">
<p class="eyebrow">CHECKOUT</p><h1>อีกขั้นเดียว ก็ได้ชิ้นโปรด</h1><p class="order-intro">ตรวจสอบรายการ กรอกที่อยู่ และเลือกช่องทางชำระเงิน</p>
<?php if ($error): ?><div class="form-errors" role="alert"><?php echo e($error); ?></div><?php endif; ?>
<?php if (!$summary): ?>
<div class="order-panel"><h2>ยังไม่มีรายการพร้อมสั่งซื้อ</h2><a class="button primary" href="products.php">กลับไปเลือกสินค้า</a></div>
<?php else: ?>
<form method="post" class="checkout-layout" id="checkout-form">
<input type="hidden" name="action" value="place"><input type="hidden" name="csrf" value="<?php echo e($_SESSION['csrf_token']); ?>"><input type="hidden" name="token" value="<?php echo e($draft['token']); ?>">
<div class="order-panel checkout-fields">
<h2>1. ข้อมูลจัดส่ง</h2>
<label>ชื่อผู้รับ<input name="recipient" required maxlength="100" autocomplete="name" value="<?php echo e($values['recipient']); ?>"></label>
<label>เบอร์โทร<input name="phone" type="tel" required maxlength="20" autocomplete="tel" value="<?php echo e($values['phone']); ?>"></label>
<label>ที่อยู่จัดส่ง<textarea name="address" required maxlength="1000" rows="4" autocomplete="street-address" placeholder="บ้านเลขที่ ถนน ตำบล อำเภอ จังหวัด และรหัสไปรษณีย์"><?php echo e($values['address']); ?></textarea></label>
<fieldset class="payment-options"><legend>2. ช่องทางชำระเงิน</legend>
<?php foreach (paymentMethods() as $key => $label): ?>
<label class="payment-option"><input type="radio" name="payment" value="<?php echo $key; ?>" required <?php echo $values['payment'] === $key ? 'checked' : ''; ?>><span><strong><?php echo e($label); ?></strong><small><?php echo $key === 'cod' ? 'ชำระเมื่อได้รับสินค้า' : 'บันทึกคำสั่งซื้อก่อน แล้วติดต่อร้านเพื่อรับรายละเอียดชำระเงิน'; ?></small></span></label>
<?php endforeach; ?>
</fieldset>
<p class="order-note">การยืนยันคำสั่งซื้อยังไม่มีการตัดเงินอัตโนมัติ</p>
</div>
<aside class="order-panel"><h2>สรุปคำสั่งซื้อ</h2>
<?php foreach ($summary['items'] as $item): ?><div class="order-item"><img src="<?php echo e($item['image']); ?>" alt="<?php echo e($item['name']); ?>"><div><strong><?php echo e($item['name']); ?></strong><p><?php echo $item['quantity']; ?> ชิ้น × ฿<?php echo number_format($item['price']); ?></p></div><strong>฿<?php echo number_format($item['price'] * $item['quantity']); ?></strong></div><?php endforeach; ?>
<div class="order-totals"><p><span>ยอดสินค้า</span><strong>฿<?php echo number_format($summary['subtotal']); ?></strong></p><p><span>ส่วนลด</span><strong>฿<?php echo number_format($summary['discount']); ?></strong></p><p><span>VAT 7%</span><strong>฿<?php echo number_format($summary['vat']); ?></strong></p><p><span>ค่าจัดส่ง</span><strong>ฟรี</strong></p><p class="order-total"><span>รวมทั้งสิ้น</span><strong>฿<?php echo number_format($summary['total']); ?></strong></p></div>
<button class="button primary" type="submit" id="place-order">ยืนยันสั่งซื้อ</button>
<button class="button secondary" type="reset">รีเซ็ตข้อมูล</button>
<p class="order-note">รีเซ็ตจะคืนข้อมูลจัดส่งจากบัญชีและล้างช่องทางชำระเงิน</p>
<a class="view-detail" href="products.php">← กลับไปแก้ไขตะกร้า</a>
</aside></form>
<script>
const checkoutForm = document.querySelector('#checkout-form');
checkoutForm.addEventListener('submit', () => { const button = document.querySelector('#place-order'); button.disabled = true; button.textContent = 'กำลังบันทึกคำสั่งซื้อ…'; });
checkoutForm.addEventListener('reset', event => { event.preventDefault(); const defaults = <?php echo json_encode(['recipient' => $customer['full_name'], 'phone' => $customer['phone'], 'address' => $customer['address'] ?? ''], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>; Object.entries(defaults).forEach(([name,value]) => checkoutForm.elements[name].value = value); checkoutForm.querySelectorAll('[name="payment"]').forEach(input => input.checked = false); });
window.addEventListener('pageshow', () => { const button = document.querySelector('#place-order'); button.disabled = false; button.textContent = 'ยืนยันสั่งซื้อ'; });
</script>
<?php endif; ?></main></body></html>
