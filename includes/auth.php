<?php
declare(strict_types=1);

require_once __DIR__ . '/database.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    $sessionDir = dirname(__DIR__) . '/data/sessions';
    if (!is_dir($sessionDir)) mkdir($sessionDir, 0700, true);
    session_save_path($sessionDir);
    ini_set('session.use_strict_mode', '1');
    session_set_cookie_params(['httponly'=>true, 'samesite'=>'Lax', 'secure'=>!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off']);
    session_start();
}
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function currentCustomer(): ?array
{
    if (empty($_SESSION['customer_id'])) {
        return null;
    }

    $stmt = db()->prepare('SELECT id, full_name, email, phone, address, line_id, note, created_at FROM customers WHERE id = :id');
    $stmt->execute(['id' => $_SESSION['customer_id']]);
    $customer = $stmt->fetch();

    if (!$customer) {
        unset($_SESSION['customer_id']);
        return null;
    }

    return $customer;
}

function customerInitial(string $name): string
{
    $name = trim($name);
    if ($name === '') {
        return 'U';
    }

    if (preg_match('/^./u', $name, $match)) {
        return $match[0];
    }

    return strtoupper(substr($name, 0, 1));
}

function registerCustomer(array $data): array
{
    $fullName = trim((string) ($data['full_name'] ?? ''));
    $email = strtolower(trim((string) ($data['email'] ?? '')));
    $phone = trim((string) ($data['phone'] ?? ''));
    $password = (string) ($data['password'] ?? '');
    $confirmPassword = (string) ($data['confirm_password'] ?? '');
    $address = trim((string) ($data['address'] ?? ''));
    $lineId = trim((string) ($data['line_id'] ?? ''));
    $note = trim((string) ($data['note'] ?? ''));
    $errors = [];

    if ($fullName === '') {
        $errors[] = 'กรุณากรอกชื่อ-นามสกุล';
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'กรุณากรอกอีเมลให้ถูกต้อง';
    }

    if (!preg_match('/^[0-9+\-\s()]{8,20}$/', $phone)) {
        $errors[] = 'กรุณากรอกเบอร์โทรให้ถูกต้อง';
    }

    if (strlen($password) < 8) {
        $errors[] = 'รหัสผ่านควรมีอย่างน้อย 8 ตัวอักษร';
    }

    if ($password !== $confirmPassword) {
        $errors[] = 'รหัสผ่านและการยืนยันรหัสผ่านไม่ตรงกัน';
    }

    if ($errors) {
        return ['success' => false, 'errors' => $errors];
    }

    try {
        $stmt = db()->prepare(
            'INSERT INTO customers (full_name, email, phone, password_hash, address, line_id, note)
             VALUES (:full_name, :email, :phone, :password_hash, :address, :line_id, :note)'
        );
        $stmt->execute([
            'full_name' => $fullName,
            'email' => $email,
            'phone' => $phone,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'address' => $address,
            'line_id' => $lineId,
            'note' => $note,
        ]);

        session_regenerate_id(true);
        $_SESSION['customer_id'] = (int) db()->lastInsertId();
        return ['success' => true, 'errors' => []];
    } catch (PDOException $exception) {
        if ($exception->getCode() === '23000') {
            return ['success' => false, 'errors' => ['อีเมลนี้ถูกใช้สมัครสมาชิกแล้ว']];
        }

        throw $exception;
    }
}

function loginCustomer(string $email, string $password): bool
{
    $stmt = db()->prepare('SELECT id, password_hash FROM customers WHERE email = :email LIMIT 1');
    $stmt->execute(['email' => strtolower(trim($email))]);
    $customer = $stmt->fetch();

    if (!$customer || !password_verify($password, $customer['password_hash'])) {
        return false;
    }

    session_regenerate_id(true);
    $_SESSION['customer_id'] = (int) $customer['id'];
    return true;
}

function updateCustomerProfile(int $customerId, array $data): array
{
    $fullName = trim((string) ($data['full_name'] ?? ''));
    $email = strtolower(trim((string) ($data['email'] ?? '')));
    $phone = trim((string) ($data['phone'] ?? ''));
    $address = trim((string) ($data['address'] ?? ''));
    $lineId = trim((string) ($data['line_id'] ?? ''));
    $note = trim((string) ($data['note'] ?? ''));
    $password = (string) ($data['password'] ?? '');
    $confirmPassword = (string) ($data['confirm_password'] ?? '');
    $errors = [];

    if ($fullName === '') {
        $errors[] = 'กรุณากรอกชื่อ-นามสกุล';
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'กรุณากรอกอีเมลให้ถูกต้อง';
    }

    if (!preg_match('/^[0-9+\-\s()]{8,20}$/', $phone)) {
        $errors[] = 'กรุณากรอกเบอร์โทรให้ถูกต้อง';
    }

    if ($password !== '' && strlen($password) < 8) {
        $errors[] = 'รหัสผ่านใหม่ควรมีอย่างน้อย 8 ตัวอักษร';
    }

    if ($password !== '' && $password !== $confirmPassword) {
        $errors[] = 'รหัสผ่านใหม่และการยืนยันรหัสผ่านไม่ตรงกัน';
    }

    if ($errors) {
        return ['success' => false, 'errors' => $errors];
    }

    try {
        if ($password !== '') {
            $stmt = db()->prepare(
                'UPDATE customers
                 SET full_name = :full_name, email = :email, phone = :phone, address = :address,
                     line_id = :line_id, note = :note, password_hash = :password_hash, updated_at = CURRENT_TIMESTAMP
                 WHERE id = :id'
            );
            $stmt->execute([
                'full_name' => $fullName,
                'email' => $email,
                'phone' => $phone,
                'address' => $address,
                'line_id' => $lineId,
                'note' => $note,
                'password_hash' => password_hash($password, PASSWORD_DEFAULT),
                'id' => $customerId,
            ]);
        } else {
            $stmt = db()->prepare(
                'UPDATE customers
                 SET full_name = :full_name, email = :email, phone = :phone, address = :address,
                     line_id = :line_id, note = :note, updated_at = CURRENT_TIMESTAMP
                 WHERE id = :id'
            );
            $stmt->execute([
                'full_name' => $fullName,
                'email' => $email,
                'phone' => $phone,
                'address' => $address,
                'line_id' => $lineId,
                'note' => $note,
                'id' => $customerId,
            ]);
        }

        return ['success' => true, 'errors' => []];
    } catch (PDOException $exception) {
        if ($exception->getCode() === '23000') {
            return ['success' => false, 'errors' => ['อีเมลนี้ถูกใช้กับบัญชีอื่นแล้ว']];
        }

        throw $exception;
    }
}

function logoutCustomer(): void
{
    unset($_SESSION['customer_id']);
}

function redirectTo(string $path): never
{
    header('Location: ' . $path);
    exit;
}
