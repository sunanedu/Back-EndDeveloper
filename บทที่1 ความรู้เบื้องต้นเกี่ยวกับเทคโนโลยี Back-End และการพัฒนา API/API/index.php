<?php
// ส่วนที่ 1: โครงสร้าง
header("Content-Type: application/json; charset=UTF-8");
require_once "config.php";

// รับ HTTP Method และ URI
$method = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

// แยก path จาก URI
$path = parse_url($uri, PHP_URL_PATH);
$request = explode('/', trim($path, '/'));

// ตัวแปรสำหรับเก็บ response
$response = [];
$http_code = 200;

// ตรวจสอบว่า Endpoint เป็น /api/products
if (count($request) >= 2 && $request[0] === 'api' && $request[1] === 'products') {
    // ส่วนที่ 2: รับค่า GET
    if ($method === 'GET') {
        try {
            $stmt = $conn->query("SELECT * FROM products");
            $response = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $http_code = 200;
        } catch (PDOException $e) {
            $response = ["error" => "Database error: " . $e->getMessage()];
            $http_code = 500;
        }
    }
    // ส่วนที่ 3: รับค่า POST
    elseif ($method === 'POST') {
        $data = json_decode(file_get_contents("php://input"), true);
        if (!empty($data['name']) && !empty($data['price'])) {
            // กำหนดตัวแปรสำหรับ bindParam
            $name = $data['name'];
            $price = $data['price'];
            $description = $data['description'] ?? '';

            // ส่วนที่ 4: Insert ลงฐานข้อมูล
            try {
                $sql = "INSERT INTO products (name, price, description) VALUES (:name, :price, :description)";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':name', $name, PDO::PARAM_STR);
                $stmt->bindParam(':price', $price, PDO::PARAM_STR);
                $stmt->bindParam(':description', $description, PDO::PARAM_STR);
                $stmt->execute();
                $response = ["message" => "Product created successfully"];
                $http_code = 201;
            } catch (PDOException $e) {
                $response = ["error" => "Database error: " . $e->getMessage()];
                $http_code = 500;
            }
        } else {
            $response = ["error" => "Invalid input"];
            $http_code = 400;
        }
    } else {
        $response = ["error" => "Method not allowed"];
        $http_code = 405;
    }
} else {
    $response = ["error" => "Endpoint not found"];
    $http_code = 404;
}

// ส่วนที่ 5: ตอบกลับ
http_response_code($http_code);
echo json_encode($response);
?>