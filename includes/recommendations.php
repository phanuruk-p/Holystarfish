<?php
declare(strict_types=1);
require_once __DIR__ . '/auth.php';

function recommendationSchema(): void {
    db()->exec('CREATE TABLE IF NOT EXISTS customer_preferences (customer_id INTEGER PRIMARY KEY REFERENCES customers(id), answers_json TEXT NOT NULL, updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP)');
    db()->exec('CREATE TABLE IF NOT EXISTS newsletter_subscribers (id INTEGER PRIMARY KEY AUTOINCREMENT, email TEXT NOT NULL UNIQUE, consent_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP)');
    db()->exec("CREATE TABLE IF NOT EXISTS email_outbox (id INTEGER PRIMARY KEY AUTOINCREMENT, customer_id INTEGER REFERENCES customers(id), recipient TEXT NOT NULL, kind TEXT NOT NULL, payload_json TEXT NOT NULL, status TEXT NOT NULL DEFAULT 'draft', created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP, UNIQUE(recipient, kind))");
}
function surveyQuestions(): array {
    return [
        'kind' => ['เครื่องประดับชิ้นไหนที่คุณชอบ?', ['ต่างหู','สร้อยคอ','แหวน','กำไล','เซ็ตของขวัญ']],
        'style' => ['สไตล์ไหนที่เป็นคุณ?', ['มินิมอล','คลาสสิก','หรูหรา','หวาน','แฟชั่น']],
        'color' => ['คุณชอบเครื่องประดับโทนสีไหน?', ['ทอง','เงิน','ขาว','โรสโกลด์']],
        'occasion' => ['คุณอยากใส่ในโอกาสไหน?', ['ทุกวัน','ทำงาน','เที่ยว','งานเลี้ยง']],
        'budget' => ['งบประมาณต่อชิ้นของคุณ?', ['500' => 'ไม่เกิน 500 บาท','1000' => 'ไม่เกิน 1,000 บาท','1500' => 'ไม่เกิน 1,500 บาท','2000' => 'ไม่เกิน 2,000 บาท']],
        'recipient' => ['คุณกำลังเลือกให้ใคร?', ['self' => 'เลือกให้ตัวเอง','gift' => 'มอบเป็นของขวัญ']],
    ];
}
function validateAnswers(array $input): array {
    $answers = [];
    foreach (surveyQuestions() as $key => [$title, $options]) {
        $value = $input[$key] ?? null;
        $allowed = array_is_list($options) ? $options : array_map('strval', array_keys($options));
        if (!is_string($value) || !in_array($value, $allowed, true)) throw new InvalidArgumentException('กรุณาตอบคำถามให้ครบทั้ง 6 ข้อ');
        $answers[$key] = $value;
    }
    return $answers;
}
function recommendedProducts(array $answers): array {
    $catalog = require __DIR__ . '/products.php';
    $ranked = [];
    foreach ($catalog as $product) {
        if ($product['price_value'] > (int)$answers['budget']) continue;
        $score = 0; $reasons = [];
        foreach (['kind' => 4, 'style' => 3, 'color' => 2, 'occasion' => 2] as $key => $weight) {
            if (str_contains($product[$key], $answers[$key])) { $score += $weight; $reasons[] = $answers[$key]; }
        }
        if ($answers['recipient'] === 'gift' && str_contains($product['occasion'], 'ของขวัญ')) { $score += 3; $reasons[] = 'เหมาะเป็นของขวัญ'; }
        if ($answers['recipient'] === 'self' && str_contains($product['occasion'], 'ทุกวัน')) { $score++; $reasons[] = 'ใส่เองได้ทุกวัน'; }
        $product['score'] = $score;
        $product['reason'] = $reasons ? implode(' · ', array_unique($reasons)) : 'ตัวเลือกเพิ่มเติมในงบของคุณ';
        $ranked[] = $product;
    }
    usort($ranked, fn($a,$b) => ($b['score'] <=> $a['score']) ?: ($a['price_value'] <=> $b['price_value']));
    return array_slice($ranked, 0, 3);
}
function customerRecommendationData(int $customerId): ?array {
    recommendationSchema();
    $stmt = db()->prepare('SELECT c.id, c.full_name, c.email, p.answers_json FROM customers c LEFT JOIN customer_preferences p ON c.id=p.customer_id WHERE c.id=?');
    $stmt->execute([$customerId]); $row = $stmt->fetch();
    if (!$row) return null;
    $row['answers'] = $row['answers_json'] ? json_decode($row['answers_json'], true, 512, JSON_THROW_ON_ERROR) : null;
    unset($row['answers_json']);
    $row['products'] = $row['answers'] ? recommendedProducts($row['answers']) : [];
    return $row;
}
function savePreferences(int $customerId, array $input): void {
    $answers = validateAnswers($input); recommendationSchema(); $pdo = db();
    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare('INSERT INTO customer_preferences(customer_id,answers_json) VALUES (?,?) ON CONFLICT(customer_id) DO UPDATE SET answers_json=excluded.answers_json, updated_at=CURRENT_TIMESTAMP');
        $stmt->execute([$customerId,json_encode($answers, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR)]);
        $data = customerRecommendationData($customerId);
        $stmt = $pdo->prepare("INSERT INTO email_outbox(customer_id,recipient,kind,payload_json) VALUES (?,?, 'welcome',?) ON CONFLICT(recipient,kind) DO UPDATE SET payload_json=excluded.payload_json WHERE email_outbox.status='draft'");
        $stmt->execute([$customerId,$data['email'],json_encode($data, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR)]);
        $pdo->commit();
    } catch (Throwable $error) { $pdo->rollBack(); throw $error; }
}
function subscribeNewsletter(string $email): void {
    $email = strtolower(trim($email));
    if (strlen($email) > 254 || !filter_var($email, FILTER_VALIDATE_EMAIL)) throw new InvalidArgumentException('กรุณากรอกอีเมลให้ถูกต้อง');
    recommendationSchema(); $pdo = db(); $pdo->beginTransaction();
    try {
        $pdo->prepare('INSERT INTO newsletter_subscribers(email) VALUES (?) ON CONFLICT(email) DO NOTHING')->execute([$email]);
        $pdo->prepare("INSERT INTO email_outbox(recipient,kind,payload_json) VALUES (?,'newsletter',?) ON CONFLICT(recipient,kind) DO NOTHING")->execute([$email,json_encode(['email'=>$email], JSON_THROW_ON_ERROR)]);
        $pdo->commit();
    } catch (Throwable $error) { $pdo->rollBack(); throw $error; }
}
function validFormToken(): bool {
    return isset($_POST['csrf_token']) && is_string($_POST['csrf_token']) && hash_equals($_SESSION['csrf_token'], $_POST['csrf_token']);
}
