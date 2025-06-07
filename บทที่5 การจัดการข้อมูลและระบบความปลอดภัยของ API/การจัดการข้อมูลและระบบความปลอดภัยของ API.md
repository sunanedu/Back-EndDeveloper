# หน่วยที่ 5 การจัดการข้อมูลและระบบความปลอดภัยของ API

เอกสารนี้เป็นแผนการสอนสำหรับหน่วยที่ 5 ซึ่งมุ่งเน้นการพัฒนา RESTful API ที่ปลอดภัยโดยใช้ PHP PDO และ MySQL ต่อยอดจากหน่วยที่ 2 และ 3 เนื้อหาครอบคลุมการจัดการข้อมูลด้วย HTTP Methods, การใช้ JWT สำหรับ Authentication, การกำหนด CORS, และการป้องกันช่องโหว่ OWASP Top 10 เช่น SQL Injection และ XSS

## ✳️ จุดประสงค์หน่วยการเรียนรู้
1. เข้าใจหลักการจัดการข้อมูลใน API ด้วย HTTP Methods
2. สามารถจัดการข้อมูลแบบ CRUD กับฐานข้อมูล MySQL
3. สามารถเพิ่มระบบความปลอดภัยใน API เช่น CORS, JWT, Authentication
4. มีความรู้เกี่ยวกับการเข้ารหัสและการป้องกันช่องโหว่ทั่วไปใน API

## 📚 สาระการเรียนรู้
- การใช้ HTTP Methods (GET, POST, PUT, DELETE) กับฐานข้อมูล
- การจัดการข้อมูลด้วย SQL (MySQL) และแนวคิด NoSQL
- การใช้ JWT (JSON Web Token) สำหรับ Authentication
- การกำหนดสิทธิ์ผู้ใช้งานเบื้องต้น (Role-based Access)
- การใช้งาน Middleware เพื่อตรวจสอบ Token
- การเข้ารหัสรหัสผ่านด้วย `password_hash()` และ `password_verify()`
- การป้องกันช่องโหว่ OWASP Top 10 เช่น SQL Injection, XSS
- การกำหนด HTTP Headers และ CORS (Cross-Origin Resource Sharing)
- หลักการ Secure API และ Best Practices ในการพัฒนา

## 🧪 กิจกรรมการเรียนรู้
1. อัปเดตฐานข้อมูล `api_db` เพื่อรองรับ JWT (เพิ่มฟิลด์ `token` ในตาราง `users`)
2. สร้างระบบ Login API และออก JWT
3. พัฒนา Middleware สำหรับตรวจสอบ JWT และ Role-based Access
4. ตั้งค่า CORS และ HTTP Headers เพื่อความปลอดภัย
5. ทดสอบ API ด้วย Thunder Client โดยแนบ JWT
6. วิเคราะห์ช่องโหว่ที่อาจเกิดขึ้นและปรับปรุง API

## การอัปเดตฐานข้อมูล

### การเปลี่ยนแปลงในฐานข้อมูล
- เพิ่มฟิลด์ `token` ในตาราง `users` เพื่อเก็บ JWT (ถ้ามีการออก token ใหม่)
- คงตารางเดิมจากหน่วยที่ 3 (`users`, `products`, `categories`, `orders`, `order_items`, `addresses`, `payments`, `reviews`)

### SQL อัปเดตฐานข้อมูล
```sql
USE api_db;

-- เพิ่มฟิลด์ token ในตาราง users
ALTER TABLE users ADD COLUMN token TEXT NULL;

-- อัปเดตข้อมูลตัวอย่างใน users
UPDATE users SET token = NULL WHERE id IN (1, 2);
```

## โครงสร้างไฟล์ (ปรับปรุงจากหน่วยที่ 3)

### โครงสร้างโฟลเดอร์
```
F:\xampp\htdocs\api\
├── config.php         # การเชื่อมต่อฐานข้อมูล
├── index.php          # Router หลัก
├── middleware.php     # Middleware สำหรับตรวจสอบ JWT และ CORS
├── users.php          # จัดการ Endpoint /api/users
├── products.php       # จัดการ Endpoint /api/products
├── categories.php     # จัดการ Endpoint /api/categories
├── orders.php         # จัดการ Endpoint /api/orders
├── order_items.php    # จัดการ Endpoint /api/orders/{id}/items
├── addresses.php      # จัดการ Endpoint /api/addresses
├── payments.php       # จัดการ Endpoint /api/payments
├── reviews.php        # จัดการ Endpoint /api/reviews
├── .htaccess          # Rewrite rules
├── composer.json      # สำหรับติดตั้ง firebase/php-jwt
├── vendor/            # โฟลเดอร์จาก Composer
```

### การติดตั้ง firebase/php-jwt
1. ติดตั้ง Composer ในเครื่อง
2. รันคำสั่งในโฟลเดอร์ `C:\xampp\htdocs\api`:
   ```bash
   composer require firebase/php-jwt
   ```
3. ตรวจสอบว่าโฟลเดอร์ `vendor` และไฟล์ `composer.json` ถูกสร้าง

### ไฟล์ `composer.json`
```json
{
    "require": {
        "firebase/php-jwt": "^6.10"
    }
}
```

### ไฟล์ `.htaccess` (เหมือนหน่วยที่ 3)
```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [L]
```

### ไฟล์ `config.php` (ปรับปรุงเพื่อเพิ่ม JWT Secret Key)
```php
<?php
$host = "localhost";
$username = "root";
$password = "";
$dbname = "api_db";

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->exec("SET NAMES utf8");
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Connection failed: " . $e->getMessage()]);
    exit();
}

// JWT Secret Key (ควรเก็บใน .env ในระบบจริง)
define('JWT_SECRET', 'your-secure-secret-key-1234567890');
define('JWT_ISSUER', 'api_db');
define('JWT_AUDIENCE', 'api_db_client');
?>
```

### ไฟล์ `middleware.php` (สำหรับตรวจสอบ JWT และ CORS)
```php
<?php
require_once "vendor/autoload.php";
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

// ตั้งค่า CORS Headers
header("Access-Control-Allow-Origin: *"); // อนุญาตทุก Origin (ปรับเป็น domain เฉพาะในระบบจริง)
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// จัดการ OPTIONS request สำหรับ CORS preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

function verifyJWT($conn) {
    $headers = apache_request_headers();
    $authHeader = isset($headers['Authorization']) ? $headers['Authorization'] : null;

    if (!$authHeader || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
        http_response_code(401);
        echo json_encode(["error" => "No token provided"]);
        exit();
    }

    $jwt = $matches[1];
    try {
        $decoded = JWT::decode($jwt, new Key(JWT_SECRET, 'HS256'));
        $userId = $decoded->sub;

        // ตรวจสอบว่า token อยู่ในฐานข้อมูลหรือไม่
        $sql = "SELECT id, role FROM users WHERE id = :id AND token = :token";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
        $stmt->bindParam(':token', $jwt, PDO::PARAM_STR);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            http_response_code(401);
            echo json_encode(["error" => "Invalid token"]);
            exit();
        }

        return $user; // คืนค่า user_id และ role
    } catch (Exception $e) {
        http_response_code(401);
        echo json_encode(["error" => "Token verification failed: " . $e->getMessage()]);
        exit();
    }
}

function checkRole($user, $requiredRole) {
    if ($user['role'] !== $requiredRole) {
        http_response_code(403);
        echo json_encode(["error" => "Insufficient permissions"]);
        exit();
    }
}
?>
```

### ไฟล์ `index.php` (ปรับปรุงเพื่อใช้ Middleware)
```php
<?php
header("Content-Type: application/json; charset=UTF-8");
require_once "config.php";
require_once "middleware.php";

$method = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];
$path = parse_url($uri, PHP_URL_PATH);
$request = explode('/', trim($path, '/'));
$response = [];
$http_code = 200;

// ตรวจสอบ JWT สำหรับ Endpoint ที่ต้องการ Authentication (ยกเว้น /api/users/login)
if (count($request) >= 2 && $request[0] === 'api' && !($request[1] === 'users' && isset($request[2]) && $request[2] === 'login')) {
    $user = verifyJWT($conn);
}

if (count($request) >= 2 && $request[0] === 'api') {
    $resource = $request[1] ?? '';
    switch ($resource) {
        case 'users':
            require_once "users.php";
            break;
        case 'products':
            require_once "products.php";
            break;
        case 'categories':
            require_once "categories.php";
            break;
        case 'orders':
            if (isset($request[3]) && $request[3] === 'items') {
                require_once "order_items.php";
            } else {
                require_once "orders.php";
            }
            break;
        case 'addresses':
            require_once "addresses.php";
            break;
        case 'payments':
            require_once "payments.php";
            break;
        case 'reviews':
            require_once "reviews.php";
            break;
        default:
            $response = ["error" => "Endpoint not found"];
            $http_code = 404;
            break;
    }
} else {
    $response = ["error" => "Invalid API endpoint"];
    $http_code = 404;
}

http_response_code($http_code);
echo json_encode($response);
?>
```

### ไฟล์ `users.php` (ปรับปรุงเพื่อรองรับ JWT และ Role-based Access)
```php
<?php
if (!isset($conn)) {
    require_once "config.php";
}
require_once "vendor/autoload.php";
use Firebase\JWT\JWT;

if (count($request) >= 2 && $request[0] === 'api' && $request[1] === 'users') {
    if ($method === 'GET') {
        if (isset($request[2]) && is_numeric($request[2])) {
            // ต้องเป็น admin หรือผู้ใช้ที่เป็นเจ้าของ ID
            if ($user['role'] !== 'admin' && $user['id'] != $request[2]) {
                $response = ["error" => "Insufficient permissions"];
                $http_code = 403;
            } else {
                try {
                    $id = $request[2];
                    $sql = "SELECT id, username, email, first_name, last_name, role, created_at, updated_at 
                            FROM users WHERE id = :id";
                    $stmt = $conn->prepare($sql);
                    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                    $stmt->execute();
                    $userData = $stmt->fetch(PDO::FETCH_ASSOC);
                    if ($userData) {
                        $response = $userData;
                        $http_code = 200;
                    } else {
                        $response = ["error" => "User not found"];
                        $http_code = 404;
                    }
                } catch (PDOException $e) {
                    $response = ["error" => "Database error: " . $e->getMessage()];
                    $http_code = 500;
                }
            }
        } else {
            // เฉพาะ admin เท่านั้นที่ดูรายชื่อผู้ใช้ทั้งหมดได้
            checkRole($user, 'admin');
            try {
                $sql = "SELECT id, username, email, first_name, last_name, role, created_at, updated_at 
                        FROM users";
                $stmt = $conn->prepare($sql);
                $stmt->execute();
                $response = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $http_code = 200;
            } catch (PDOException $e) {
                $response = ["error" => "Database error: " . $e->getMessage()];
                $http_code = 500;
            }
        }
    } elseif ($method === 'POST' && !isset($request[2])) {
        // อนุญาตให้ทุกคนสมัครสมาชิกได้ (ไม่ต้องใช้ JWT)
        $data = json_decode(file_get_contents("php://input"), true);
        if (!empty($data['username']) && !empty($data['email']) && !empty($data['password'])) {
            try {
                $username = filter_var($data['username'], FILTER_SANITIZE_STRING);
                $email = filter_var($data['email'], FILTER_SANITIZE_EMAIL);
                $password = password_hash($data['password'], PASSWORD_BCRYPT);
                $first_name = filter_var($data['first_name'] ?? '', FILTER_SANITIZE_STRING);
                $last_name = filter_var($data['last_name'] ?? '', FILTER_SANITIZE_STRING);
                $role = $data['role'] ?? 'customer';

                $sql = "INSERT INTO users (username, email, password, first_name, last_name, role) 
                        VALUES (:username, :email, :password, :first_name, :last_name, :role)";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':username', $username, PDO::PARAM_STR);
                $stmt->bindParam(':email', $email, PDO::PARAM_STR);
                $stmt->bindParam(':password', $password, PDO::PARAM_STR);
                $stmt->bindParam(':first_name', $first_name, PDO::PARAM_STR);
                $stmt->bindParam(':last_name', $last_name, PDO::PARAM_STR);
                $stmt->bindParam(':role', $role, PDO::PARAM_STR);
                $stmt->execute();
                $response = ["message" => "User created successfully"];
                $http_code = 201;
            } catch (PDOException $e) {
                $response = ["error" => "Database error: " . $e->getMessage()];
                $http_code = 500;
            }
        } else {
            $response = ["error" => "Invalid input"];
            $http_code = 400;
        }
    } elseif ($method === 'POST' && isset($request[2]) && $request[2] === 'login') {
        // ระบบ Login ออก JWT
        $data = json_decode(file_get_contents("php://input"), true);
        if (!empty($data['email']) && !empty($data['password'])) {
            try {
                $email = filter_var($data['email'], FILTER_SANITIZE_EMAIL);
                $sql = "SELECT id, username, email, password, role FROM users WHERE email = :email";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':email', $email, PDO::PARAM_STR);
                $stmt->execute();
                $userData = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($userData && password_verify($data['password'], $userData['password'])) {
                    // สร้าง JWT
                    $payload = [
                        'iss' => JWT_ISSUER,
                        'aud' => JWT_AUDIENCE,
                        'iat' => time(),
                        'exp' => time() + (60 * 60), // หมดอายุใน 1 ชั่วโมง
                        'sub' => $userData['id'],
                        'role' => $userData['role']
                    ];
                    $jwt = JWT::encode($payload, JWT_SECRET, 'HS256');

                    // อัปเดต token ในฐานข้อมูล
                    $sql = "UPDATE users SET token = :token WHERE id = :id";
                    $stmt = $conn->prepare($sql);
                    $stmt->bindParam(':token', $jwt, PDO::PARAM_STR);
                    $stmt->bindParam(':id', $userData['id'], PDO::PARAM_INT);
                    $stmt->execute();

                    $response = [
                        "message" => "Login successful",
                        "token" => $jwt,
                        "user" => [
                            "id" => $userData['id'],
                            "username" => $userData['username'],
                            "email" => $userData['email'],
                            "role" => $userData['role']
                        ]
                    ];
                    $http_code = 200;
                } else {
                    $response = ["error" => "Invalid email or password"];
                    $http_code = 401;
                }
            } catch (PDOException $e) {
                $response = ["error" => "Database error: " . $e->getMessage()];
                $http_code = 500;
            }
        } else {
            $response = ["error" => "Invalid input"];
            $http_code = 400;
        }
    } elseif ($method === 'PUT' && isset($request[2]) && is_numeric($request[2])) {
        // ต้องเป็น admin หรือผู้ใช้ที่เป็นเจ้าของ ID
        if ($user['role'] !== 'admin' && $user['id'] != $request[2]) {
            $response = ["error" => "Insufficient permissions"];
            $http_code = 403;
        } else {
            $data = json_decode(file_get_contents("php://input"), true);
            try {
                $id = $request[2];
                $username = filter_var($data['username'] ?? '', FILTER_SANITIZE_STRING);
                $email = filter_var($data['email'] ?? '', FILTER_SANITIZE_EMAIL);
                $first_name = filter_var($data['first_name'] ?? '', FILTER_SANITIZE_STRING);
                $last_name = filter_var($data['last_name'] ?? '', FILTER_SANITIZE_STRING);
                $role = $data['role'] ?? 'customer';

                $sql = "UPDATE users SET username = :username, email = :email, 
                        first_name = :first_name, last_name = :last_name, role = :role 
                        WHERE id = :id";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                $stmt->bindParam(':username', $username, PDO::PARAM_STR);
                $stmt->bindParam(':email', $email, PDO::PARAM_STR);
                $stmt->bindParam(':first_name', $first_name, PDO::PARAM_STR);
                $stmt->bindParam(':last_name', $last_name, PDO::PARAM_STR);
                $stmt->bindParam(':role', $role, PDO::PARAM_STR);
                $stmt->execute();
                if ($stmt->rowCount() > 0) {
                    $response = ["message" => "User updated successfully"];
                    $http_code = 200;
                } else {
                    $response = ["error" => "User not found"];
                    $http_code = 404;
                }
            } catch (PDOException $e) {
                $response = ["error" => "Database error: " . $e->getMessage()];
                $http_code = 500;
            }
        }
    } else {
        $response = ["error" => "Method not allowed"];
        $http_code = 405;
    }
}
?>
```

### ไฟล์ `products.php` (ปรับปรุงเพื่อใช้ Role-based Access)
```php
<?php
if (!isset($conn)) {
    require_once "config.php";
}

if (count($request) >= 2 && $request[0] === 'api' && $request[1] === 'products') {
    if ($method === 'GET') {
        if (isset($request[2]) && is_numeric($request[2])) {
            try {
                $id = $request[2];
                $sql = "SELECT * FROM products WHERE id = :id";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                $stmt->execute();
                $product = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($product) {
                    $response = $product;
                    $http_code = 200;
                } else {
                    $response = ["error" => "Product not found"];
                    $http_code = 404;
                }
            } catch (PDOException $e) {
                $response = ["error" => "Database error: " . $e->getMessage()];
                $http_code = 500;
            }
        } else {
            try {
                $stmt = $conn->query("SELECT * FROM products");
                $response = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $http_code = 200;
            } catch (PDOException $e) {
                $response = ["error" => "Database error: " . $e->getMessage()];
                $http_code = 500;
            }
        }
    } elseif ($method === 'POST') {
        // เฉพาะ admin เท่านั้นที่เพิ่มสินค้าได้
        checkRole($user, 'admin');
        $data = json_decode(file_get_contents("php://input"), true);
        if (!empty($data['name']) && !empty($data['price'])) {
            try {
                $name = filter_var($data['name'], FILTER_SANITIZE_STRING);
                $price = filter_var($data['price'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
                $description = filter_var($data['description'] ?? '', FILTER_SANITIZE_STRING);
                $category_id = $data['category_id'] ?? null;
                $stock = $data['stock'] ?? 0;

                $sql = "INSERT INTO products (name, price, description, category_id, stock) 
                        VALUES (:name, :price, :description, :category_id, :stock)";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':name', $name, PDO::PARAM_STR);
                $stmt->bindParam(':price', $price, PDO::PARAM_STR);
                $stmt->bindParam(':description', $description, PDO::PARAM_STR);
                $stmt->bindParam(':category_id', $category_id, PDO::PARAM_INT);
                $stmt->bindParam(':stock', $stock, PDO::PARAM_INT);
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
    } elseif ($method === 'PUT' && isset($request[2]) && is_numeric($request[2])) {
        // เฉพาะ admin เท่านั้นที่แก้ไขสินค้าได้
        checkRole($user, 'admin');
        $data = json_decode(file_get_contents("php://input"), true);
        if (!empty($data['name']) && !empty($data['price'])) {
            try {
                $id = $request[2];
                $name = filter_var($data['name'], FILTER_SANITIZE_STRING);
                $price = filter_var($data['price'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
                $description = filter_var($data['description'] ?? '', FILTER_SANITIZE_STRING);
                $category_id = $data['category_id'] ?? null;
                $stock = $data['stock'] ?? 0;

                $sql = "UPDATE products SET name = :name, price = :price, description = :description, 
                        category_id = :category_id, stock = :stock WHERE id = :id";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                $stmt->bindParam(':name', $name, PDO::PARAM_STR);
                $stmt->bindParam(':price', $price, PDO::PARAM_STR);
                $stmt->bindParam(':description', $description, PDO::PARAM_STR);
                $stmt->bindParam(':category_id', $category_id, PDO::PARAM_INT);
                $stmt->bindParam(':stock', $stock, PDO::PARAM_INT);
                $stmt->execute();
                if ($stmt->rowCount() > 0) {
                    $response = ["message" => "Product updated successfully"];
                    $http_code = 200;
                } else {
                    $response = ["error" => "Product not found"];
                    $http_code = 404;
                }
            } catch (PDOException $e) {
                $response = ["error" => "Database error: " . $e->getMessage()];
                $http_code = 500;
            }
        } else {
            $response = ["error" => "Invalid input"];
            $http_code = 400;
        }
    } elseif ($method === 'DELETE' && isset($request[2]) && is_numeric($request[2])) {
        // เฉพาะ admin เท่านั้นที่ลบสินค้าได้
        checkRole($user, 'admin');
        try {
            $id = $request[2];
            $sql = "DELETE FROM products WHERE id = :id";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            if ($stmt->rowCount() > 0) {
                $response = ["message" => "Product deleted successfully"];
                $http_code = 200;
            } else {
                $response = ["error" => "Product not found"];
                $http_code = 404;
            }
        } catch (PDOException $e) {
            $response = ["error" => "Database error: " . $e->getMessage()];
            $http_code = 500;
        }
    } else {
        $response = ["error" => "Method not allowed"];
        $http_code = 405;
    }
}
?>
```

### ไฟล์ `categories.php`, `orders.php`, `order_items.php`, `addresses.php`, `payments.php`, `reviews.php`
- คงโค้ดจากหน่วยที่ 3 แต่เพิ่มการตรวจสอบ Role-based Access ใน Endpoint ที่ต้องการ เช่น:
  - `POST`, `PUT`, `DELETE` ใน `categories.php`, `orders.php`, `payments.php` จำกัดเฉพาะ admin
  - `POST` ใน `reviews.php`, `addresses.php` อนุญาตสำหรับ customer แต่ต้องตรวจสอบว่า `user_id` ตรงกับ JWT
- ตัวอย่างการปรับ `reviews.php`:
```php
<?php
if (!isset($conn)) {
    require_once "config.php";
}

if (count($request) >= 2 && $request[0] === 'api' && $request[1] === 'reviews') {
    if ($method === 'GET') {
        // ทุกคนสามารถดูรีวิวได้
        if (isset($request[2]) && is_numeric($request[2])) {
            try {
                $id = $request[2];
                $sql = "SELECT * FROM reviews WHERE id = :id";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                $stmt->execute();
                $review = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($review) {
                    $response = $review;
                    $http_code = 200;
                } else {
                    $response = ["error" => "Review not found"];
                    $http_code = 404;
                }
            } catch (PDOException $e) {
                $response = ["error" => "Database error: " . $e->getMessage()];
                $http_code = 500;
            }
        } else {
            try {
                $stmt = $conn->query("SELECT * FROM reviews");
                $response = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $http_code = 200;
            } catch (PDOException $e) {
                $response = ["error" => "Database error: " . $e->getMessage()];
                $http_code = 500;
            }
        }
    } elseif ($method === 'POST') {
        // เฉพาะ customer สามารถเขียนรีวิวได้
        if ($user['role'] !== 'customer') {
            $response = ["error" => "Insufficient permissions"];
            $http_code = 403;
        } else {
            $data = json_decode(file_get_contents("php://input"), true);
            if (!empty($data['product_id']) && !empty($data['rating']) && $data['user_id'] == $user['id']) {
                try {
                    $product_id = $data['product_id'];
                    $user_id = $data['user_id'];
                    $rating = filter_var($data['rating'], FILTER_SANITIZE_NUMBER_INT);
                    $comment = filter_var($data['comment'] ?? '', FILTER_SANITIZE_STRING);

                    $sql = "INSERT INTO reviews (product_id, user_id, rating, comment) 
                            VALUES (:product_id, :user_id, :rating, :comment)";
                    $stmt = $conn->prepare($sql);
                    $stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
                    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
                    $stmt->bindParam(':rating', $rating, PDO::PARAM_INT);
                    $stmt->bindParam(':comment', $comment, PDO::PARAM_STR);
                    $stmt->execute();
                    $response = ["message" => "Review created successfully"];
                    $http_code = 201;
                } catch (PDOException $e) {
                    $response = ["error" => "Database error: " . $e->getMessage()];
                    $http_code = 500;
                }
            } else {
                $response = ["error" => "Invalid input or unauthorized user"];
                $http_code = 400;
            }
        }
    } elseif ($method === 'PUT' && isset($request[2]) && is_numeric($request[2])) {
        // เฉพาะ customer เจ้าของรีวิวสามารถแก้ไขได้
        try {
            $id = $request[2];
            $sql = "SELECT user_id FROM reviews WHERE id = :id";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $review = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$review || $review['user_id'] != $user['id']) {
                $response = ["error" => "Insufficient permissions"];
                $http_code = 403;
            } else {
                $data = json_decode(file_get_contents("php://input"), true);
                if (!empty($data['rating'])) {
                    $rating = filter_var($data['rating'], FILTER_SANITIZE_NUMBER_INT);
                    $comment = filter_var($data['comment'] ?? '', FILTER_SANITIZE_STRING);

                    $sql = "UPDATE reviews SET rating = :rating, comment = :comment WHERE id = :id";
                    $stmt = $conn->prepare($sql);
                    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                    $stmt->bindParam(':rating', $rating, PDO::PARAM_INT);
                    $stmt->bindParam(':comment', $comment, PDO::PARAM_STR);
                    $stmt->execute();
                    if ($stmt->rowCount() > 0) {
                        $response = ["message" => "Review updated successfully"];
                        $http_code = 200;
                    } else {
                        $response = ["error" => "Review not found"];
                        $http_code = 404;
                    }
                } else {
                    $response = ["error" => "Invalid input"];
                    $http_code = 400;
                }
            }
        } catch (PDOException $e) {
            $response = ["error" => "Database error: " . $e->getMessage()];
            $http_code = 500;
        }
    } elseif ($method === 'DELETE' && isset($request[2]) && is_numeric($request[2])) {
        // เฉพาะ customer เจ้าของรีวิวหรือ admin สามารถลบได้
        try {
            $id = $request[2];
            $sql = "SELECT user_id FROM reviews WHERE id = :id";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $review = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$review || ($review['user_id'] != $user['id'] && $user['role'] !== 'admin')) {
                $response = ["error" => "Insufficient permissions"];
                $http_code = 403;
            } else {
                $sql = "DELETE FROM reviews WHERE id = :id";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                $stmt->execute();
                if ($stmt->rowCount() > 0) {
                    $response = ["message" => "Review deleted successfully"];
                    $http_code = 200;
                } else {
                    $response = ["error" => "Review not found"];
                    $http_code = 404;
                }
            }
        } catch (PDOException $e) {
            $response = ["error" => "Database error: " . $e->getMessage()];
            $http_code = 500;
        }
    } else {
        $response = ["error" => "Method not allowed"];
        $http_code = 405;
    }
}
?>
```

## การป้องกันช่องโหว่ OWASP Top 10
1. **SQL Injection**: ใช้ Prepared Statements และ `bindParam` ในทุก Query
2. **Broken Authentication**: ใช้ JWT และเก็บ Secret Key อย่างปลอดภัย
3. **Sensitive Data Exposure**: เข้ารหัสรหัสผ่านด้วย `password_hash()` และไม่ส่งรหัสผ่านใน Response
4. **XML External Entities (XXE)**: ไม่ใช้ XML ใน API นี้
5. **Broken Access Control**: ใช้ Role-based Access และตรวจสอบ `user_id`
6. **Security Misconfiguration**: ตั้งค่า CORS เฉพาะ domain ในระบบจริง
7. **Cross-Site Scripting (XSS)**: ใช้ `filter_var` และ `FILTER_SANITIZE_STRING` สำหรับ input
8. **Insecure Deserialization**: ใช้ `json_decode` อย่างปลอดภัย
9. **Using Components with Known Vulnerabilities**: อัปเดต `firebase/php-jwt` ผ่าน Composer
10. **Insufficient Logging & Monitoring**: บันทึก error log ใน `C:\xampp\php\logs\php_error_log`

## การแก้ไขปัญหา
- **JWT**:
  - ตรวจสอบการติดตั้ง `firebase/php-jwt`
  - ตรวจสอบ Secret Key ใน `config.php`
- **CORS**:
  - ตรวจสอบ Headers ใน `middleware.php`
- **500**:
  - ดู log ที่ `C:\xampp\php\logs\php_error_log`

## หมายเหตุ
- วันที่: 7 มกราคม 2568
- สภาพแวดล้อม: PHP 8.2, XAMPP, MySQL, Windows 10
- ในระบบจริง ควรเก็บ JWT Secret ใน `.env` และจำกัด CORS เฉพาะ domain ที่อนุญาต
- พิจารณาเพิ่มการ Rate Limiting ในอนาคต