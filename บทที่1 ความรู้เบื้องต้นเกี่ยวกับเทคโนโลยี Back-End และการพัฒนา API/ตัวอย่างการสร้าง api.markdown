# การทดสอบ API สำหรับ Endpoint `http://127.0.0.1/api/products`

เอกสารนี้ให้คำอธิบายและขั้นตอนการทดสอบ RESTful API ที่พัฒนาด้วย PHP 8.2 บน XAMPP ในระบบ Windows 10 โดยใช้ Visual Studio Code (VS Code) และส่วนขยาย Thunder Client สำหรับทดสอบ Endpoint `http://127.0.0.1/api/products` ซึ่งจัดการข้อมูลในตาราง `products` ในฐานข้อมูล MySQL

## ภาพรวม
API นี้มีสองการทำงานหลัก:
- **GET `/api/products`**: ดึงข้อมูลสินค้าทั้งหมดจากตาราง `products` ในรูปแบบ JSON
- **POST `/api/products`**: สร้างสินค้าใหม่ในตาราง `products` โดยรับข้อมูล JSON

### โครงสร้างตาราง `products`
ตาราง `products` ในฐานข้อมูล `api_db` มีโครงสร้างดังนี้:
```sql
CREATE TABLE products (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    description TEXT
);
```

### ไฟล์ที่เกี่ยวข้อง
- **index.php**: จัดการคำขอ GET และ POST สำหรับ Endpoint `/api/products`
- **config.php**: จัดการการเชื่อมต่อฐานข้อมูล MySQL
- **.htaccess**: กำหนดกฎ rewrite เพื่อส่งคำขอทั้งหมดไปยัง `index.php`
- โฟลเดอร์: `C:\xampp\htdocs\api` (จากข้อมูล error log ที่ระบุ)

## การตั้งค่าสภาพแวดล้อม
1. **ติดตั้ง XAMPP**:
   - ดาวน์โหลด XAMPP (PHP 8.2) และติดตั้งที่ `C:\xampp`
   - เริ่ม Apache และ MySQL ใน XAMPP Control Panel
   - ตรวจสอบการทำงานที่ `http://localhost`

2. **ตั้งค่าฐานข้อมูล**:
   - เข้า `http://localhost/phpmyadmin`
   - สร้างฐานข้อมูล `api_db`
   - รันคำสั่ง SQL เพื่อสร้างตาราง `products` (ด้านบน)
   - เพิ่มข้อมูลตัวอย่าง:
     ```sql
     INSERT INTO products (name, price, description) VALUES
     ('Laptop', 25000.00, 'High-performance laptop'),
     ('Smartphone', 15000.00, 'Latest model smartphone');
     ```

3. **ตั้งค่าไฟล์**:
   - สร้างโฟลเดอร์ `C:\xampp\htdocs\api`
   - สร้างไฟล์:
     - `config.php` (การเชื่อมต่อฐานข้อมูล)
     - `index.php` (จัดการ API)
     - `.htaccess` (กำหนด rewrite rules)

4. **ตั้งค่า Apache**:
   - เปิด `C:\xampp\apache\conf\httpd.conf`
   - ตรวจสอบว่า `LoadModule rewrite_module modules/mod_rewrite.so` เปิดใช้งาน (ไม่มี `#`)
   - ตรวจสอบว่า `<Directory "F:/xampp/htdocs">` มี `AllowOverride All`
   - รีสตาร์ท Apache

## โค้ด API
### config.php
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
    echo "Connection failed: " . $e->getMessage();
    exit();
}
?>
```

### .htaccess
```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [L]
```

### index.php
```php
<?php
// ส่วนที่ 1: โครงสร้าง
header("Content-Type: application/json; charset=UTF-8");
require_once "config.php";
$method = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];
$path = parse_url($uri, PHP_URL_PATH);
$request = explode('/', trim($path, '/'));
$response = [];
$http_code = 200;

// ตรวจสอบ Endpoint
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
```

## ขั้นตอนการทดสอบ API
การทดสอบใช้ **Thunder Client** ใน VS Code ซึ่งเป็นส่วนขยายสำหรับทดสอบ API

### 1. ทดสอบ GET Request
- **วัตถุประสงค์**: ดึงข้อมูลสินค้าทั้งหมด
- **ขั้นตอน**:
  1. เปิด VS Code และ Thunder Client
  2. สร้าง request ใหม่
  3. ตั้ง Method เป็น **GET**
  4. ตั้ง URL เป็น `http://127.0.0.1/api/products`
  5. คลิก **Send**
- **ผลลัพธ์ที่คาดหวัง**:
  ```json
  [
      {"id": 1, "name": "Laptop", "price": "25000.00", "description": "High-performance laptop"},
      {"id": 2, "name": "Smartphone", "price": "15000.00", "description": "Latest model smartphone"}
  ]
  ```
- **HTTP Status**: 200 OK
- **กรณีผิดพลาด**:
  - **500 Internal Server Error**: เกิดจากปัญหาการเชื่อมต่อฐานข้อมูล ตรวจสอบ `config.php` และ MySQL
  - **404 Not Found**: Endpoint ไม่ถูกต้อง ตรวจสอบ `.htaccess` และ URL

### 2. ทดสอบ POST Request
- **วัตถุประสงค์**: สร้างสินค้าใหม่
- **ขั้นตอน**:
  1. ใน Thunder Client สร้าง request ใหม่
  2. ตั้ง Method เป็น **POST**
  3. ตั้ง URL เป็น `http://127.0.0.1/api/products`
  4. ในแท็บ **Body** เลือก **Raw** และ **JSON**
  5. ป้อนข้อมูล:
     ```json
     {
         "name": "Tablet",
         "price": 12000.00,
         "description": "Portable tablet"
     }
     ```
  6. ตั้ง Header: `Content-Type: application/json`
  7. คลิก **Send**
- **ผลลัพธ์ที่คาดหวัง**:
  ```json
  {"message": "Product created successfully"}
  ```
- **HTTP Status**: 201 Created
- **การตรวจสอบ**:
  - เข้า `http://localhost/phpmyadmin`
  - ตรวจสอบตาราง `products` ใน `api_db` ว่ามีแถวใหม่ (Tablet, 12000.00, Portable tablet)
- **กรณีผิดพลาด**:
  - **400 Bad Request**: ข้อมูล JSON ขาด `name` หรือ `price`
  - **500 Internal Server Error**: ปัญหาการ insert ลงฐานข้อมูล ตรวจสอบตาราง `products` และ `config.php`
  - **405 Method Not Allowed**: ใช้ HTTP Method อื่นที่ไม่ใช่ GET หรือ POST

## การแก้ไขปัญหาทั่วไป
1. **ข้อผิดพลาด 404 Not Found**:
   - ตรวจสอบว่า `.htaccess` อยู่ใน `C:\xampp\htdocs\api`
   - ตรวจสอบว่า `AllowOverride All` ใน `httpd.conf`
   - รีสตาร์ท Apache
2. **ข้อผิดพลาด 500 Database Error**:
   - ตรวจสอบว่า MySQL รันใน XAMPP
   - ตรวจสอบว่า `api_db` และตาราง `products` มีอยู่
   - รัน `http://127.0.0.1/api/config.php` เพื่อตรวจสอบการเชื่อมต่อ
3. **Fatal Error จาก bindParam**:
   - ตรวจสอบว่าใช้ตัวแปร reference ใน `bindParam` (เช่น `$name`, `$price`)
   - หากยังมีปัญหา ลองใช้ `bindValue`:
     ```php
     $stmt->bindValue(':name', $data['name'], PDO::PARAM_STR);
     ```

## การดีบัก
- **ดู log**:
  - Apache: `C:\xampp\apache\logs\error.log`
  - PHP: `C:\xampp\php\logs\php_error_log`
- **เพิ่ม debug ใน index.php**:
  ```php
  error_log(print_r($request, true));
  // หรือ
  echo json_encode(["debug_path" => $path, "debug_request" => $request]); exit;
  ```
- **ตรวจสอบ JSON input**:
  - ใช้ Thunder Client ตรวจสอบว่า JSON body และ `Content-Type` ถูกต้อง

## หมายเหตุ
- วันที่ทดสอบ: 6 มิถุนายน 2568
- ใช้ PHP 8.2.12 และ Apache 2.4.58 (จากข้อมูล error log ก่อนหน้า)
- ตรวจสอบว่า XAMPP และ VS Code อัปเดตเป็นเวอร์ชันล่าสุด
- หากมีปัญหาเพิ่มเติม บันทึกผลลัพธ์จาก Thunder Client หรือ error log เพื่อวิเคราะห์ต่อ