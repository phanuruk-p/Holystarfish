<?php
// Shared display catalog for the home page and product search.
$catalog = json_decode(file_get_contents(__DIR__ . '/../data/products.json'), true, 512, JSON_THROW_ON_ERROR);
foreach ($catalog as &$item) {
    $item['price'] = '฿' . number_format($item['price_value']);
}
unset($item);
return $catalog;
