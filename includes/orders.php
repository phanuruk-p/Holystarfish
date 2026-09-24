<?php
declare(strict_types=1);

function paymentMethods(): array {
    return ['bank' => 'โอนผ่านธนาคาร', 'promptpay' => 'พร้อมเพย์', 'cod' => 'เก็บเงินปลายทาง'];
}

function orderSummary(array $requested): array {
    if (!$requested || count($requested) > 100) throw new InvalidArgumentException('กรุณาเลือกสินค้า 1–100 รายการ');
    $catalog = require __DIR__ . '/products.php';
    $quantities = [];
    foreach ($requested as $row) {
        if (!is_array($row)) throw new InvalidArgumentException('ข้อมูลสินค้าไม่ถูกต้อง');
        $matches = array_values(array_filter($catalog, fn($p) => isset($row['id']) ? $p['id'] === $row['id'] : ($p['name'] === ($row['name'] ?? '') || $p['legacy_name'] === ($row['name'] ?? ''))));
        $qty = $row['quantity'] ?? null;
        if (!$matches || !is_int($qty) || $qty < 1 || $qty > 99) throw new InvalidArgumentException('สินค้าหรือจำนวนไม่ถูกต้อง กรุณากลับไปตรวจสอบตะกร้า');
        $id = $matches[0]['id'];
        $quantities[$id] = ($quantities[$id] ?? 0) + $qty;
        if ($quantities[$id] > 99) throw new InvalidArgumentException('สั่งซื้อได้สูงสุด 99 ชิ้นต่อสินค้า');
    }
    $items = []; $subtotal = 0;
    foreach ($catalog as $product) {
        if (!isset($quantities[$product['id']])) continue;
        $qty = $quantities[$product['id']];
        $items[] = ['id' => $product['id'], 'name' => $product['name'], 'image' => $product['image'], 'price' => $product['price_value'], 'quantity' => $qty];
        $subtotal += $product['price_value'] * $qty;
    }
    $vat = (int) round($subtotal * .07);
    return ['items' => $items, 'subtotal' => $subtotal, 'discount'=>0, 'shipping'=>0, 'vat' => $vat, 'total' => $subtotal + $vat];
}

function placeOrder(PDO $pdo, int $customerId, string $token, array $requested, array $input): int {
    // Retries of the same confirmed checkout return the existing order.
    $existing = $pdo->prepare('SELECT id FROM orders WHERE customer_id = ? AND request_token = ?');
    $existing->execute([$customerId, $token]);
    if ($id = $existing->fetchColumn()) return (int) $id;
    $summary = orderSummary($requested);
    $recipient = trim((string) ($input['recipient'] ?? ''));
    $phone = trim((string) ($input['phone'] ?? ''));
    $address = trim((string) ($input['address'] ?? ''));
    $payment = (string) ($input['payment'] ?? '');
    if ($recipient === '' || strlen($recipient) > 300) throw new InvalidArgumentException('กรุณากรอกชื่อผู้รับไม่เกิน 100 ตัวอักษร');
    if (!preg_match('/^[0-9+\-\s()]{8,20}$/', $phone)) throw new InvalidArgumentException('กรุณากรอกเบอร์โทรให้ถูกต้อง');
    if ($address === '' || strlen($address) > 3000) throw new InvalidArgumentException('กรุณากรอกที่อยู่จัดส่งไม่เกิน 1,000 ตัวอักษร');
    if (!isset(paymentMethods()[$payment])) throw new InvalidArgumentException('กรุณาเลือกช่องทางการชำระเงิน');
    $pdo->beginTransaction();
    try {
    $stmt = $pdo->prepare('INSERT INTO orders (customer_id, request_token, recipient, phone, address, payment_method, status, items_json, subtotal, vat, total) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
    $stmt->execute([$customerId, $token, $recipient, $phone, $address, $payment, $payment === 'cod' ? 'รอจัดส่ง · ชำระปลายทาง' : 'รอชำระเงิน', json_encode($summary['items'], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR), $summary['subtotal'], $summary['vat'], $summary['total']]);
    $id=(int)$pdo->lastInsertId();
    $query=$pdo->prepare('SELECT o.*,c.email,c.full_name FROM orders o JOIN customers c ON c.id=o.customer_id WHERE o.id=?');
    $query->execute([$id]); $snapshot=$query->fetch();
    $pdo->prepare('INSERT INTO order_email_outbox(order_id,recipient,payload_json) VALUES (?,?,?)')->execute([$id,$snapshot['email'],json_encode($snapshot,JSON_UNESCAPED_UNICODE|JSON_THROW_ON_ERROR)]);
    $pdo->commit();
    return $id;
    } catch(Throwable $error) { $pdo->rollBack(); throw $error; }
}
