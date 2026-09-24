<?php
declare(strict_types=1);
putenv('HOLYSTARFISH_DB_PATH=:memory:');
require __DIR__ . '/../includes/database.php';
require __DIR__ . '/../includes/orders.php';
function check(bool $condition, string $message): void { if (!$condition) throw new RuntimeException($message); }
function rejected(callable $action): void { try { $action(); } catch (InvalidArgumentException $e) { return; } throw new RuntimeException('Expected validation failure'); }
$pdo = db();
$pdo->exec("INSERT INTO customers (full_name,email,phone,password_hash) VALUES ('Test','test@example.invalid','0800000000','unused'),('Other','other@example.invalid','0800000001','unused')");
$input = ['recipient' => 'ลูกค้าทดสอบ', 'phone' => '0800000000', 'address' => 'ที่อยู่ตัวอย่าง', 'payment' => 'bank'];
$cart = [['id' => 'pearl-tide-earrings', 'quantity' => 2, 'price' => 1]];
$summary = orderSummary($cart);
check($summary['subtotal'] === 1780 && $summary['vat'] === 125 && $summary['total'] === 1905, 'Authoritative price and rounded VAT');
$id = placeOrder($pdo, 1, 'test-bank', $cart, $input);
check(placeOrder($pdo, 1, 'test-bank', $cart, $input) === $id, 'Duplicate checkout must reuse order');
check((int) $pdo->query('SELECT COUNT(*) FROM orders')->fetchColumn() === 1, 'No duplicate records');
$order = $pdo->query('SELECT * FROM orders')->fetch();
check($order['status'] === 'รอชำระเงิน' && json_decode($order['items_json'], true)[0]['quantity'] === 2, 'Snapshot and pending status');
foreach (['promptpay', 'cod'] as $method) {
    $id = placeOrder($pdo, 1, 'test-' . $method, $cart, array_replace($input, ['payment' => $method]));
    check($id > 1, 'All offered payment methods supported');
}
check($pdo->query("SELECT status FROM orders WHERE payment_method='cod'")->fetchColumn() === 'รอจัดส่ง · ชำระปลายทาง', 'COD status');
$stmt = $pdo->prepare('SELECT * FROM orders WHERE customer_id = ?'); $stmt->execute([2]);
check($stmt->fetchAll() === [], 'Other customer cannot see history');
foreach ([[], [['id'=>'missing','quantity'=>1]], [['id'=>'pearl-tide-earrings','quantity'=>-1]], [['id'=>'pearl-tide-earrings','quantity'=>1.5]], [['id'=>'pearl-tide-earrings','quantity'=>100]]] as $bad) rejected(fn() => orderSummary($bad));
rejected(fn() => placeOrder($pdo, 1, 'bad-payment', $cart, array_replace($input, ['payment'=>'card'])));
rejected(fn() => placeOrder($pdo, 1, 'bad-address', $cart, array_replace($input, ['address'=>''])));
check((int) $pdo->query('SELECT COUNT(*) FROM orders')->fetchColumn() === 3, 'Invalid requests never saved');
echo "Orders checks passed: prices, VAT, snapshots, retries, payment methods, ownership, and validation.\n";
