# หน่วยที่ 3: การพัฒนา API พื้นฐานด้วย PHP PDO

เอกสารนี้เป็นแผนการสอนสำหรับหน่วยที่ 3 ซึ่งมุ่งเน้นการพัฒนา RESTful API ด้วย PHP PDO สำหรับระบบอีคอมเมิร์ซ โดยต่อยอดจากฐานข้อมูล `api_db` ในหน่วยที่ 2 เนื้อหาครอบคลุมการออกแบบ API, การจัดการ HTTP Methods, การใช้ JSON, และการขยายฐานข้อมูลด้วยตาราง `addresses`, `payments`, และ `reviews`

## ✳️ จุดประสงค์หน่วยการเรียนรู้
1. เข้าใจหลักการทำงานของ RESTful API เบื้องต้น
2. สามารถพัฒนา API Endpoint ด้วย PHP และ PDO
3. สร้าง API ที่รองรับ HTTP Methods: GET, POST, PUT, DELETE
4. สามารถส่งและรับข้อมูลรูปแบบ JSON ได้อย่างถูกต้อง

## 📚 สาระการเรียนรู้
- แนวคิด RESTful API และ HTTP Methods (GET, POST, PUT, DELETE)
- การตั้งค่า Header เพื่อใช้งาน JSON API (Content-Type: application/json)
- การสร้างไฟล์ PHP สำหรับให้บริการ API:
  - GET: ดึงข้อมูล
  - POST: เพิ่มข้อมูล
  - PUT: แก้ไขข้อมูล
  - DELETE: ลบข้อมูล
- การรับและแปลงข้อมูล JSON จากคำขอ (Request)
- การใช้ SQL กับ PDO อย่างปลอดภัย (Prepared Statement)
- การจัดการ Response และรหัสสถานะ (HTTP Status Codes)
- การทดสอบ API ด้วย Thunder Client

## 🧪 กิจกรรมการเรียนรู้
1. ขยายฐานข้อมูล `api_db` โดยเพิ่มตาราง `addresses`, `payments`, และ `reviews`
2. ออกแบบ ER Diagram สำหรับตารางใหม่
3. พัฒนา API Endpoint สำหรับตารางทั้งหมดในระบบ
4. ทดสอบ API ด้วย Thunder Client โดยส่งข้อมูล JSON
5. วิเคราะห์และปรับปรุง API เพื่อความปลอดภัย (เช่น การใช้ Prepared Statements)

## การขยายฐานข้อมูล

### ER Diagram (ขยายจากหน่วยที่ 2)
```
[users] ----(1:N)---- [orders] ----(1:N)---- [order_items]
  |                     |                       |
  |                     |                       |
 (N:1)                 (N:1)                 (N:1)
  |                     |                       |
[products] ----(N:1)---- [categories]       [reviews]
  |                                             |
  |                                             |
 (1:N)                                        (N:1)
  |                                             |
[addresses] ----(1:N)---- [payments]
```

- **ตารางใหม่**
  - `addresses`: id, user_id, address_line, city, postal_code, country, created_at
  - `payments`: id, order_id, amount, method, status, created_at
  - `reviews`: id, product_id, user_id, rating, comment, created_at
- **ความสัมพันธ์ใหม่**
  - `users` → `addresses`: One-to-Many (ผู้ใช้ 1 คนมีหลายที่อยู่)
  - `orders` → `payments`: One-to-Many (คำสั่งซื้อ 1 รายการมีหลายการชำระเงิน)
  - `products` → `reviews`: One-to-Many (สินค้า 1 ชิ้นมีหลายรีวิว)
  - `users` → `reviews`: One-to-Many (ผู้ใช้ 1 คนเขียนหลายรีวิว)

### SQL ขยายฐานข้อมูล
```sql
USE api_db;

-- ตาราง addresses
CREATE TABLE addresses (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    address_line VARCHAR(255) NOT NULL,
    city VARCHAR(100) NOT NULL,
    postal_code VARCHAR(20) NOT NULL,
    country VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ตาราง payments
CREATE TABLE payments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    method ENUM('credit_card', 'bank_transfer', 'cash') NOT NULL,
    status ENUM('pending', 'completed', 'failed') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
);

-- ตาราง reviews
CREATE TABLE reviews (
    id INT PRIMARY KEY AUTO_INCREMENT,
    product_id INT NOT NULL,
    user_id INT NOT NULL,
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ข้อมูลตัวอย่าง
INSERT INTO addresses (user_id, address_line, city, postal_code, country) VALUES
(1, '123 Main St', 'Bangkok', '10110', 'Thailand'),
(2, '456 Admin Rd', 'Chiang Mai', '50200', 'Thailand');

INSERT INTO payments (order_id, amount, method, status) VALUES
(1, 25500.00, 'credit_card', 'completed');

INSERT INTO reviews (product_id, user_id, rating, comment) VALUES
(1, 1, 5, 'Great laptop, very fast!'),
(2, 1, 4, 'Good smartphone, but battery could be better.');
```

## โครงสร้างไฟล์และโค้ด

### โครงสร้างโฟลเดอร์ (ต่อยอดจากหน่วยที่ 2)
```
F:\xampp\htdocs\api\
├── config.php         # การเชื่อมต่อฐานข้อมูล
├── index.php          # Router หลัก
├── users.php          # จัดการ Endpoint /api/users
├── products.php       # จัดการ Endpoint /api/products
├── categories.php     # จัดการ Endpoint /api/categories
├── orders.php         # จัดการ Endpoint /api/orders
├── order_items.php    # จัดการ Endpoint /api/orders/{id}/items
├── addresses.php      # จัดการ Endpoint /api/addresses
├── payments.php       # จัดการ Endpoint /api/payments
├── reviews.php        # จัดการ Endpoint /api/reviews
├── .htaccess          # Rewrite rules
```

### ไฟล์ `.htaccess` (เหมือนหน่วยที่ 2)
```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [L]
```

### ไฟล์ `config.php` (เหมือนหน่วยที่ 2)
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
?>
```

### ไฟล์ `index.php` (ปรับปรุงเพื่อรองรับตารางใหม่)
```php
<?php
header("Content-Type: application/json; charset=UTF-8");
require_once "config.php";

$method = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];
$path = parse_url($uri, PHP_URL_PATH);
$request = explode('/', trim($path, '/'));
$response = [];
$http_code = 200;

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

### ไฟล์ `addresses.php`
```php
<?php
if (!isset($conn)) {
    require_once "config.php";
}

if (count($request) >= 2 && $request[0] === 'api' && $request[1] === 'addresses') {
    if ($method === 'GET') {
        if (isset($request[2]) && is_numeric($request[2])) {
            try {
                $id = $request[2];
                $sql = "SELECT * FROM addresses WHERE id = :id";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                $stmt->execute();
                $address = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($address) {
                    $response = $address;
                    $http_code = 200;
                } else {
                    $response = ["error" => "Address not found"];
                    $http_code = 404;
                }
            } catch (PDOException $e) {
                $response = ["error" => "Database error: " . $e->getMessage()];
                $http_code = 500;
            }
        } else {
            try {
                $stmt = $conn->query("SELECT * FROM addresses");
                $response = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $http_code = 200;
            } catch (PDOException $e) {
                $response = ["error" => "Database error: " . $e->getMessage()];
                $http_code = 500;
            }
        }
    } elseif ($method === 'POST') {
        $data = json_decode(file_get_contents("php://input"), true);
        if (!empty($data['user_id']) && !empty($data['address_line']) && !empty($data['city']) && !empty($data['postal_code']) && !empty($data['country'])) {
            try {
                $user_id = $data['user_id'];
                $address_line = $data['address_line'];
                $city = $data['city'];
                $postal_code = $data['postal_code'];
                $country = $data['country'];

                $sql = "INSERT INTO addresses (user_id, address_line, city, postal_code, country) 
                        VALUES (:user_id, :address_line, :city, :postal_code, :country)";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
                $stmt->bindParam(':address_line', $address_line, PDO::PARAM_STR);
                $stmt->bindParam(':city', $city, PDO::PARAM_STR);
                $stmt->bindParam(':postal_code', $postal_code, PDO::PARAM_STR);
                $stmt->bindParam(':country', $country, PDO::PARAM_STR);
                $stmt->execute();
                $response = ["message" => "Address created successfully"];
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
        $data = json_decode(file_get_contents("php://input"), true);
        if (!empty($data['user_id']) && !empty($data['address_line']) && !empty($data['city']) && !empty($data['postal_code']) && !empty($data['country'])) {
            try {
                $id = $request[2];
                $user_id = $data['user_id'];
                $address_line = $data['address_line'];
                $city = $data['city'];
                $postal_code = $data['postal_code'];
                $country = $data['country'];

                $sql = "UPDATE addresses SET user_id = :user_id, address_line = :address_line, 
                        city = :city, postal_code = :postal_code, country = :country WHERE id = :id";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
                $stmt->bindParam(':address_line', $address_line, PDO::PARAM_STR);
                $stmt->bindParam(':city', $city, PDO::PARAM_STR);
                $stmt->bindParam(':postal_code', $postal_code, PDO::PARAM_STR);
                $stmt->bindParam(':country', $country, PDO::PARAM_STR);
                $stmt->execute();
                if ($stmt->rowCount() > 0) {
                    $response = ["message" => "Address updated successfully"];
                    $http_code = 200;
                } else {
                    $response = ["error" => "Address not found"];
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
        try {
            $id = $request[2];
            $sql = "DELETE FROM addresses WHERE id = :id";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            if ($stmt->rowCount() > 0) {
                $response = ["message" => "Address deleted successfully"];
                $http_code = 200;
            } else {
                $response = ["error" => "Address not found"];
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

### ไฟล์ `payments.php`
```php
<?php
if (!isset($conn)) {
    require_once "config.php";
}

if (count($request) >= 2 && $request[0] === 'api' && $request[1] === 'payments') {
    if ($method === 'GET') {
        if (isset($request[2]) && is_numeric($request[2])) {
            try {
                $id = $request[2];
                $sql = "SELECT * FROM payments WHERE id = :id";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                $stmt->execute();
                $payment = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($payment) {
                    $response = $payment;
                    $http_code = 200;
                } else {
                    $response = ["error" => "Payment not found"];
                    $http_code = 404;
                }
            } catch (PDOException $e) {
                $response = ["error" => "Database error: " . $e->getMessage()];
                $http_code = 500;
            }
        } else {
            try {
                $stmt = $conn->query("SELECT * FROM payments");
                $response = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $http_code = 200;
            } catch (PDOException $e) {
                $response = ["error" => "Database error: " . $e->getMessage()];
                $http_code = 500;
            }
        }
    } elseif ($method === 'POST') {
        $data = json_decode(file_get_contents("php://input"), true);
        if (!empty($data['order_id']) && !empty($data['amount']) && !empty($data['method'])) {
            try {
                $order_id = $data['order_id'];
                $amount = $data['amount'];
                $method = $data['method'];
                $status = $data['status'] ?? 'pending';

                $sql = "INSERT INTO payments (order_id, amount, method, status) 
                        VALUES (:order_id, :amount, :method, :status)";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':order_id', $order_id, PDO::PARAM_INT);
                $stmt->bindParam(':amount', $amount, PDO::PARAM_STR);
                $stmt->bindParam(':method', $method, PDO::PARAM_STR);
                $stmt->bindParam(':status', $status, PDO::PARAM_STR);
                $stmt->execute();
                $response = ["message" => "Payment created successfully"];
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
        $data = json_decode(file_get_contents("php://input"), true);
        if (!empty($data['status'])) {
            try {
                $id = $request[2];
                $status = $data['status'];

                $sql = "UPDATE payments SET status = :status WHERE id = :id";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                $stmt->bindParam(':status', $status, PDO::PARAM_STR);
                $stmt->execute();
                if ($stmt->rowCount() > 0) {
                    $response = ["message" => "Payment status updated successfully"];
                    $http_code = 200;
                } else {
                    $response = ["error" => "Payment not found"];
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
    } else {
        $response = ["error" => "Method not allowed"];
        $http_code = 405;
    }
}
?>
```

### ไฟล์ `reviews.php`
```php
<?php
if (!isset($conn)) {
    require_once "config.php";
}

if (count($request) >= 2 && $request[0] === 'api' && $request[1] === 'reviews') {
    if ($method === 'GET') {
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
        $data = json_decode(file_get_contents("php://input"), true);
        if (!empty($data['product_id']) && !empty($data['user_id']) && !empty($data['rating'])) {
            try {
                $product_id = $data['product_id'];
                $user_id = $data['user_id'];
                $rating = $data['rating'];
                $comment = $data['comment'] ?? '';

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
            $response = ["error" => "Invalid input"];
            $http_code = 400;
        }
    } elseif ($method === 'PUT' && isset($request[2]) && is_numeric($request[2])) {
        $data = json_decode(file_get_contents("php://input"), true);
        if (!empty($data['rating'])) {
            try {
                $id = $request[2];
                $rating = $data['rating'];
                $comment = $data['comment'] ?? '';

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
            } catch (PDOException $e) {
                $response = ["error" => "Database error: " . $e->getMessage()];
                $http_code = 500;
            }
        } else {
            $response = ["error" => "Invalid input"];
            $http_code = 400;
        }
    } elseif ($method === 'DELETE' && isset($request[2]) && is_numeric($request[2])) {
        try {
            $id = $request[2];
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

## การแก้ไขปัญหา
- **ฐานข้อมูล**:
  - ตรวจสอบ MySQL และ `config.php`
- **404**:
  - ตรวจสอบ `.htaccess` และ URL
- **500**:
  - ดู log ที่ `F:\xampp\php\logs\php_error_log`

## หมายเหตุ
- วันที่: 7 มิถุนายน 2568
- สภาพแวดล้อม: PHP 8.2, XAMPP, Windows 10
- แนะนำเพิ่ม JWT สำหรับการตรวจสอบสิทธิ์ในอนาคต