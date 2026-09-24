<?php
require_once __DIR__.'/includes/recommendations.php';
header('Content-Type: application/json; charset=utf-8'); header('Cache-Control: private, no-store');
$customer=currentCustomer();
if(!$customer) { http_response_code(401); echo json_encode(['error'=>'กรุณาเข้าสู่ระบบ'],JSON_UNESCAPED_UNICODE); exit; }
echo json_encode(customerRecommendationData((int)$customer['id']),JSON_UNESCAPED_UNICODE|JSON_THROW_ON_ERROR);
