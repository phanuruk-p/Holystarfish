<?php
declare(strict_types=1);

function db(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $dataDir = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'data';
    if (!is_dir($dataDir)) {
        mkdir($dataDir, 0775, true);
    }

    $databasePath = getenv('HOLYSTARFISH_DB_PATH') ?: $dataDir . DIRECTORY_SEPARATOR . 'holystarfish.sqlite';
    $pdo = new PDO('sqlite:' . $databasePath);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->exec('PRAGMA foreign_keys = ON');

    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS customers (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            full_name TEXT NOT NULL,
            email TEXT NOT NULL UNIQUE,
            phone TEXT NOT NULL,
            password_hash TEXT NOT NULL,
            address TEXT,
            line_id TEXT,
            note TEXT,
            created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
        )'
    );

    $pdo->exec('CREATE TABLE IF NOT EXISTS orders (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        customer_id INTEGER NOT NULL REFERENCES customers(id),
        request_token TEXT NOT NULL UNIQUE,
        recipient TEXT NOT NULL,
        phone TEXT NOT NULL,
        address TEXT NOT NULL,
        payment_method TEXT NOT NULL,
        status TEXT NOT NULL,
        items_json TEXT NOT NULL,
        subtotal INTEGER NOT NULL,
        vat INTEGER NOT NULL,
        total INTEGER NOT NULL,
        created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
    )');
    $pdo->exec('CREATE INDEX IF NOT EXISTS orders_customer ON orders(customer_id, id)');
    $columns=array_column($pdo->query('PRAGMA table_info(orders)')->fetchAll(),'name');
    foreach(['discount','shipping'] as $column) {
        if(!in_array($column,$columns,true)) $pdo->exec("ALTER TABLE orders ADD COLUMN $column INTEGER NOT NULL DEFAULT 0");
    }
    $pdo->exec("CREATE TABLE IF NOT EXISTS order_email_outbox (
        order_id INTEGER PRIMARY KEY REFERENCES orders(id),
        recipient TEXT NOT NULL,
        payload_json TEXT NOT NULL,
        status TEXT NOT NULL DEFAULT 'draft',
        created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
    )");
    return $pdo;
}
