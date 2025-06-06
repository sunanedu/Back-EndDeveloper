# การออกแบบฐานข้อมูลสำหรับระบบ Back-End และ API

เอกสารนี้แสดงการออกแบบฐานข้อมูลเต็มรูปแบบสำหรับระบบ Back-End และ API โดยครอบคลุม **สมาชิก**, **สินค้า**, **ข้อมูลการขาย**, และ **ข้อมูลอื่น ๆ ที่จำเป็น** เพื่อรองรับระบบอีคอมเมิร์ซหรือแอปพลิเคชันที่ต้องการจัดการผู้ใช้, สินค้า, และการทำธุรกรรม ฐานข้อมูลใช้ MySQL และออกแบบให้เหมาะสมกับการพัฒนา API ด้วย PHP 8.2 บน XAMPP

## ภาพรวม
ระบบนี้ประกอบด้วยตารางหลัก 5 ตาราง
1. **users**: เก็บข้อมูลสมาชิก (ผู้ใช้)
2. **products**: เก็บข้อมูลสินค้า (อิงจากโครงสร้างที่ให้มา)
3. **orders**: เก็บข้อมูลการสั่งซื้อ
4. **order_items**: เก็บรายละเอียดสินค้าในแต่ละคำสั่งซื้อ
5. **categories**: เก็บหมวดหมู่สินค้า

### ความสัมพันธ์ระหว่างตาราง
- **users** และ **orders**: ความสัมพันธ์แบบ one-to-many (ผู้ใช้ 1 คนสามารถมีหลายคำสั่งซื้อ)
- **products** และ **order_items**: ความสัมพันธ์แบบ one-to-many (สินค้า 1 ชิ้นสามารถอยู่ในหลายรายการสั่งซื้อ)
- **orders** และ **order_items**: ความสัมพันธ์แบบ one-to-many (คำสั่งซื้อ 1 รายการมีหลายรายการสินค้า)
- **products** และ **categories**: ความสัมพันธ์แบบ many-to-one (สินค้าหลายชิ้นอยู่ในหมวดหมู่เดียว)

## โครงสร้างฐานข้อมูล

### 1. ตาราง `users`
เก็บข้อมูลสมาชิก เช่น ชื่อ, อีเมล, รหัสผ่าน, และบทบาท

```sql
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    first_name VARCHAR(100),
    last_name VARCHAR(100),
    role ENUM('customer', 'admin') DEFAULT 'customer',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

- **id**: รหัสผู้ใช้ (primary key, อัตโนมัติ)
- **username**: ชื่อผู้ใช้ (ไม่ซ้ำ)
- **email**: อีเมล (ไม่ซ้ำ)
- **password**: รหัสผ่าน (เข้ารหัส เช่น ใช้ bcrypt)
- **first_name**, **last_name**: ชื่อและนามสกุล
- **role**: บทบาท (customer หรือ admin)
- **created_at**, **updated_at**: วันที่สร้างและอัปเดต

### 2. ตาราง `products`
เก็บข้อมูลสินค้า (อิงจากโครงสร้างที่ให้มา) และเพิ่มการเชื่อมโยงกับหมวดหมู่

```sql
CREATE TABLE products (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    description TEXT,
    category_id INT,
    stock INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);
```

- **id**: รหัสสินค้า
- **name**: ชื่อสินค้า
- **price**: ราคา (ทศนิยม 2 ตำแหน่ง)
- **description**: คำอธิบาย
- **category_id**: รหัสหมวดหมู่ (เชื่อมโยงกับตาราง `categories`)
- **stock**: จำนวนสินค้าคงคลัง
- **created_at**, **updated_at**: วันที่สร้างและอัปเดต
- **FOREIGN KEY**: เชื่อมโยงกับ `categories`

### 3. ตาราง `categories`
เก็บข้อมูลหมวดหมู่สินค้า

```sql
CREATE TABLE categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

- **id**: รหัสหมวดหมู่
- **name**: ชื่อหมวดหมู่ (ไม่ซ้ำ)
- **description**: คำอธิบายหมวดหมู่
- **created_at**: วันที่สร้าง

### 4. ตาราง `orders`
เก็บข้อมูลคำสั่งซื้อของสมาชิก

```sql
CREATE TABLE orders (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'completed', 'cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

- **id**: รหัสคำสั่งซื้อ
- **user_id**: รหัสผู้ใช้ที่สั่งซื้อ
- **total_amount**: ยอดรวมของคำสั่งซื้อ
- **status**: สถานะคำสั่งซื้อ (รอดำเนินการ, เสร็จสิ้น, ยกเลิก)
- **created_at**, **updated_at**: วันที่สร้างและอัปเดต
- **FOREIGN KEY**: เชื่อมโยงกับ `users`

### 5. ตาราง `order_items`
เก็บรายละเอียดสินค้าในแต่ละคำสั่งซื้อ

```sql
CREATE TABLE order_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    unit_price DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);
```

- **id**: รหัสของรายการสินค้า
- **order_id**: รหัสคำสั่งซื้อ
- **product_id**: รหัสสินค้า
- **quantity**: จำนวนสินค้า
- **unit_price**: ราคาต่อหน่วย ณ เวลาสั่งซื้อ
- **created_at**: วันที่สร้าง
- **FOREIGN KEY**: เชื่อมโยงกับ `orders` และ `products`

## ตัวอย่างข้อมูลเริ่มต้น
เพื่อให้สามารถทดสอบ API ได้ทันที ต่อไปนี้คือคำสั่ง SQL สำหรับเพิ่มข้อมูลตัวอย่าง:

```sql
-- เพิ่มหมวดหมู่
INSERT INTO categories (name, description) VALUES
('Electronics', 'Electronic devices and gadgets'),
('Clothing', 'Apparel and accessories');

-- เพิ่มผู้ใช้
INSERT INTO users (username, email, password, first_name, last_name, role) VALUES
('user1', 'user1@example.com', '$2y$10$hashedpassword', 'John', 'Doe', 'customer'),
('admin1', 'admin@example.com', '$2y$10$hashedpassword', 'Admin', 'User', 'admin');

-- เพิ่มสินค้า
INSERT INTO products (name, price, description, category_id, stock) VALUES
('Laptop', 25000.00, 'High-performance laptop', 1, 50),
('Smartphone', 15000.00, 'Latest model smartphone', 1, 100),
('T-Shirt', 500.00, 'Cotton T-shirt', 2, 200);

-- เพิ่มคำสั่งซื้อ
INSERT INTO orders (user_id, total_amount, status) VALUES
(1, 25500.00, 'pending');

-- เพิ่มรายการสินค้าในคำสั่งซื้อ
INSERT INTO order_items (order_id, product_id, quantity, unit_price) VALUES
(1, 1, 1, 25000.00),
(1, 3, 1, 500.00);
```

**หมายเหตุ** รหัสผ่านใน `users` ควรเข้ารหัสด้วย `password_hash()` ใน PHP (เช่น bcrypt) และแทนที่ `$2y$10$hashedpassword` ด้วยรหัสผ่านจริง

## การออกแบบ API
ต่อไปนี้คือตัวอย่าง Endpoint สำหรับระบบนี้

### 1. สมาชิก (Users)
- **GET /api/users**: ดึงข้อมูลผู้ใช้ทั้งหมด (สำหรับ admin)
- **GET /api/users/{id}**: ดึงข้อมูลผู้ใช้ตาม ID
- **POST /api/users**: สร้างผู้ใช้ใหม่
- **PUT /api/users/{id}**: อัปเดตข้อมูลผู้ใช้
- **POST /api/login**: ตรวจสอบการล็อกอิน

### 2. สินค้า (Products)
- **GET /api/products**: ดึงข้อมูลสินค้าทั้งหมด
- **GET /api/products/{id}**: ดึงข้อมูลสินค้าตาม ID
- **POST /api/products**: สร้างสินค้าใหม่
- **PUT /api/products/{id}**: อัปเดตข้อมูลสินค้า
- **DELETE /api/products/{id}**: ลบสินค้า

### 3. หมวดหมู่ (Categories)
- **GET /api/categories**: ดึงข้อมูลหมวดหมู่ทั้งหมด
- **GET /api/categories/{id}**: ดึงข้อมูลหมวดหมู่ตาม ID
- **POST /api/categories**: สร้างหมวดหมู่ใหม่
- **PUT /api/categories/{id}**: อัปเดตข้อมูลหมวดหมู่
- **DELETE /api/categories/{id}**: ลบหมวดหมู่

### 4. คำสั่งซื้อ (Orders)
- **GET /api/orders**: ดึงข้อมูลคำสั่งซื้อทั้งหมด (สำหรับ admin หรือผู้ใช้ตาม ID)
- **GET /api/orders/{id}**: ดึงข้อมูลคำสั่งซื้อตาม ID
- **POST /api/orders**: สร้างคำสั่งซื้อใหม่
- **PUT /api/orders/{id}**: อัปเดตสถานะคำสั่งซื้อ
- **DELETE /api/orders/{id}**: ยกเลิกคำสั่งซื้อ

### 5. รายการสินค้าในคำสั่งซื้อ (Order Items)
- **GET /api/orders/{order_id}/items**: ดึงรายการสินค้าในคำสั่งซื้อ
- **POST /api/orders/{order_id}/items**: เพิ่มรายการสินค้าในคำสั่งซื้อ

## การใช้งานฐานข้อมูล
1. **สร้างฐานข้อมูล**
   - เข้า `http://localhost/phpmyadmin`
   - สร้างฐานข้อมูลชื่อ `api_db`
   - รันคำสั่ง SQL ด้านบนเพื่อสร้างตารางทั้ง 5 ตาราง
   - เพิ่มข้อมูลตัวอย่างตามที่ระบุ

2. **เชื่อมต่อกับ API**
   - ใช้ไฟล์ `config.php` สำหรับการเชื่อมต่อ MySQL
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

3. **ตัวอย่างการพัฒนา API**
   - อิงจาก `index.php` ที่พัฒนาก่อนหน้านี้ สามารถขยาย Endpoint สำหรับตารางอื่น ๆ เช่น `users`, `categories`, `orders`, และ `order_items`
   - ใช้ `bindParam` หรือ `bindValue` ใน PDO เพื่อป้องกัน SQL Injection
   - ส่ง response ในรูปแบบ JSON พร้อม HTTP status codes (200, 201, 400, 404, 500)

## การทดสอบ API
ใช้ **Thunder Client** ใน VS Code หรือ Postman สำหรับทดสอบ Endpoint

### ตัวอย่างการทดสอบ
1. **GET /api/products**
   - **Request** `GET http://127.0.0.1/api/products`
   - **Response**
     ```json
     [
         {"id": 1, "name": "Laptop", "price": "25000.00", "description": "High-performance laptop", "category_id": 1, "stock": 50},
         {"id": 2, "name": "Smartphone", "price": "15000.00", "description": "Latest model smartphone", "category_id": 1, "stock": 100},
         {"id": 3, "name": "T-Shirt", "price": "500.00", "description": "Cotton T-shirt", "category_id": 2, "stock": 200}
     ]
     ```
   - **Status**: 200 OK

2. **POST /api/products**
   - **Request**
     ```json
     {
         "name": "Tablet",
         "price": 12000.00,
         "description": "Portable tablet",
         "category_id": 1,
         "stock": 30
     }
     ```
   - **Response**
     ```json
     {"message": "Product created successfully"}
     ```
   - **Status**: 201 Created

## การแก้ไขปัญหา
1. **ฐานข้อมูลไม่เชื่อมต่อ**
   - ตรวจสอบว่า MySQL รันใน XAMPP
   - ตรวจสอบ `config.php` ว่ากำหนด `host`, `username`, `password`, และ `dbname` ถูกต้อง
2. **Endpoint ไม่พบ (404)**
   - ตรวจสอบ `.htaccess` และ `AllowOverride All` ใน `httpd.conf`
   - ตรวจสอบว่า URL ถูกต้อง (เช่น `/api/products`)
3. **ข้อผิดพลาด SQL**
   - ตรวจสอบโครงสร้างตารางและ foreign keys
   - รันคำสั่ง SQL ใน phpMyAdmin เพื่อหาข้อผิดพลาด
4. **ดู log**
   - Apache: `C:\xampp\apache\logs\error.log`
   - PHP: `C:\xampp\php\logs\php_error_log`

## หมายเหตุ
- ฐานข้อมูลนี้ออกแบบให้ยืดหยุ่นและรองรับการขยาย เช่น การเพิ่มตารางสำหรับที่อยู่, การชำระเงิน, หรือรีวิวสินค้า
- ควรเพิ่มการเข้ารหัส JWT หรือ OAuth สำหรับการตรวจสอบสิทธิ์ใน API
- วันที่ออกแบบ: 6 มิถุนายน 2568
- สภาพแวดล้อม: PHP 8.2.12, Apache 2.4.58, XAMPP บน Windows 10