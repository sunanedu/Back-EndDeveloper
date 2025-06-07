# หน่วยที่ 2: การเชื่อมต่อฐานข้อมูลด้วย PHP PDO และการออกแบบฐานข้อมูล

เอกสารบทที่ 2 มุ่งเน้นการพัฒนาทักษะการออกแบบฐานข้อมูลเชิงสัมพันธ์และการเชื่อมต่อฐานข้อมูลด้วย PHP PDO สำหรับสร้าง RESTful API โดยใช้ MySQL บน XAMPP เนื้อหาครอบคลุมการออกแบบฐานข้อมูล, การสร้าง ER Diagram, การเชื่อมต่อ PDO, และการพัฒนา API สำหรับระบบอีคอมเมิร์ซที่มีตาราง `users`, `products`, `categories`, `orders`, และ `order_items`

## ✳️ จุดประสงค์หน่วยการเรียนรู้
1. เข้าใจหลักการทำงานของฐานข้อมูลเชิงสัมพันธ์ (Relational Database)
2. สามารถออกแบบฐานข้อมูลอย่างเป็นระบบ
3. เขียนโค้ด PHP เชื่อมต่อฐานข้อมูลด้วย PDO ได้
4. สร้างตารางและดำเนินการพื้นฐานกับฐานข้อมูล (CRUD) ได้

## 📚 สาระการเรียนรู้
- ความรู้เบื้องต้นเกี่ยวกับฐานข้อมูล MySQL/MariaDB
- การออกแบบฐานข้อมูลเชิงสัมพันธ์ (Entity, Attribute, Relationship)
- การสร้าง ER Diagram อย่างง่าย
- การสร้างฐานข้อมูลและตารางด้วยคำสั่ง SQL
- แนะนำ PDO (PHP Data Objects)
- การเชื่อมต่อฐานข้อมูลด้วย PHP PDO
- การเขียนฟังก์ชันเพื่อทำ CRUD (Create, Read, Update, Delete)
- การจัดการ Exception ด้วย Try...Catch ขณะเชื่อมต่อฐานข้อมูล

## 🧪 กิจกรรมการเรียนรู้
1. สร้าง ER Diagram สำหรับระบบอีคอมเมิร์ซ
2. เขียน SQL เพื่อสร้างฐานข้อมูลและตาราง
3. ทดลองเชื่อมต่อฐานข้อมูลด้วย PHP PDO
4. สร้าง API Endpoint สำหรับตาราง `users`, `products`, `categories`, `orders`, และ `order_items`
5. ทดสอบ API ด้วย Thunder Client ใน VS Code

## การออกแบบฐานข้อมูล

### ER Diagram
```
[users] ----(1:N)---- [orders] ----(1:N)---- [order_items]
  |                                         |
  |                                         |
  |                                         |
 (N:1)                                   (N:1)
  |                                         |
[products] ----(N:1)---- [categories]
```

- **Entities**
  - `users`: id, username, email, password, first_name, last_name, role, created_at, updated_at
  - `products`: id, name, price, description, category_id, stock, created_at, updated_at
  - `categories`: id, name, description, created_at
  - `orders`: id, user_id, total_amount, status, created_at, updated_at
  - `order_items`: id, order_id, product_id, quantity, unit_price, created_at
- **Relationships**
  - `users` → `orders`: One-to-Many
  - `products` → `order_items`: One-to-Many
  - `orders` → `order_items`: One-to-Many
  - `products` → `categories`: Many-to-One

### SQL สร้างฐานข้อมูล
```sql
CREATE DATABASE api_db;
USE api_db;

-- ตาราง categories
CREATE TABLE categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ตาราง users
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

-- ตาราง products
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

-- ตาราง orders
CREATE TABLE orders (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'completed', 'cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ตาราง order_items
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

-- ข้อมูลตัวอย่าง
INSERT INTO categories (name, description) VALUES
('Electronics', 'Electronic devices and gadgets'),
('Clothing', 'Apparel and accessories');

INSERT INTO users (username, email, password, first_name, last_name, role) VALUES
('user1', 'user1@example.com', '$2y$10$hashedpassword', 'John', 'Doe', 'customer'),
('admin1', 'admin@example.com', '$2y$10$hashedpassword', 'Admin', 'User', 'admin');

INSERT INTO products (name, price, description, category_id, stock) VALUES
('Laptop', 25000.00, 'High-performance laptop', 1, 50),
('Smartphone', 15000.00, 'Latest model smartphone', 1, 100),
('T-Shirt', 500.00, 'Cotton T-shirt', 2, 200);

INSERT INTO orders (user_id, total_amount, status) VALUES
(1, 25500.00, 'pending');

INSERT INTO order_items (order_id, product_id, quantity, unit_price) VALUES
(1, 1, 1, 25000.00),
(1, 3, 1, 500.00);
```

**หมายเหตุ** แทน `$2y$10$hashedpassword` ด้วยรหัสผ่านที่เข้ารหัสด้วย `password_hash()` ใน PHP

## โครงสร้างไฟล์และโค้ด

### โครงสร้างโฟลเดอร์
```
F:\xampp\htdocs\api\
├── config.php         # การเชื่อมต่อฐานข้อมูล
├── index.php          # Router หลัก
├── users.php          # จัดการ Endpoint /api/users
├── products.php       # จัดการ Endpoint /api/products
├── categories.php     # จัดการ Endpoint /api/categories
├── orders.php         # จัดการ Endpoint /api/orders
├── order_items.php    # จัดการ Endpoint /api/orders/{id}/items
├── .htaccess          # Rewrite rules
```

### ไฟล์ `.htaccess`
```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [L]
```

### ไฟล์ `config.php`
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

### ไฟล์ `index.php` (Router หลัก)
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

### ไฟล์ `users.php`
```php
<?php
if (!isset($conn)) {
    require_once "config.php";
}

if (count($request) >= 2 && $request[0] === 'api' && $request[1] === 'users') {
    if ($method === 'GET') {
        if (isset($request[2]) && is_numeric($request[2])) {
            try {
                $id = $request[2];
                $sql = "SELECT id, username, email, first_name, last_name, role, created_at, updated_at 
                        FROM users WHERE id = :id";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                $stmt->execute();
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($user) {
                    $response = $user;
                    $http_code = 200;
                } else {
                    $response = ["error" => "User not found"];
                    $http_code = 404;
                }
            } catch (PDOException $e) {
                $response = ["error" => "Database error: " . $e->getMessage()];
                $http_code = 500;
            }
        } else {
            try {
                $sql = "SELECT id, username, email, first_name, last_name, role, created_at, updated_at 
                        FROM users WHERE role = 'customer'";
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
        $data = json_decode(file_get_contents("php://input"), true);
        if (!empty($data['username']) && !empty($data['email']) && !empty($data['password'])) {
            try {
                $username = $data['username'];
                $email = $data['email'];
                $password = password_hash($data['password'], PASSWORD_BCRYPT);
                $first_name = $data['first_name'] ?? '';
                $last_name = $data['last_name'] ?? '';
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
        $data = json_decode(file_get_contents("php://input"), true);
        if (!empty($data['email']) && !empty($data['password'])) {
            try {
                $email = $data['email'];
                $sql = "SELECT id, username, email, password, role FROM users WHERE email = :email";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':email', $email, PDO::PARAM_STR);
                $stmt->execute();
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($user && password_verify($data['password'], $user['password'])) {
                    $response = ["message" => "Login successful", "user" => [
                        "id" => $user['id'],
                        "username" => $user['username'],
                        "email" => $user['email'],
                        "role" => $user['role']
                    ]];
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
        $data = json_decode(file_get_contents("php://input"), true);
        try {
            $id = $request[2];
            $username = $data['username'] ?? '';
            $email = $data['email'] ?? '';
            $first_name = $data['first_name'] ?? '';
            $last_name = $data['last_name'] ?? '';
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
    } else {
        $response = ["error" => "Method not allowed"];
        $http_code = 405;
    }
}
?>
```

### ไฟล์ `products.php`
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
        $data = json_decode(file_get_contents("php://input"), true);
        if (!empty($data['name']) && !empty($data['price'])) {
            $name = $data['name'];
            $price = $data['price'];
            $description = $data['description'] ?? '';
            $category_id = $data['category_id'] ?? null;
            $stock = $data['stock'] ?? 0;

            try {
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
        $data = json_decode(file_get_contents("php://input"), true);
        if (!empty($data['name']) && !empty($data['price'])) {
            try {
                $id = $request[2];
                $name = $data['name'];
                $price = $data['price'];
                $description = $data['description'] ?? '';
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

### ไฟล์ `categories.php`
```php
<?php
if (!isset($conn)) {
    require_once "config.php";
}

if (count($request) >= 2 && $request[0] === 'api' && $request[1] === 'categories') {
    if ($method === 'GET') {
        if (isset($request[2]) && is_numeric($request[2])) {
            try {
                $id = $request[2];
                $sql = "SELECT * FROM categories WHERE id = :id";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                $stmt->execute();
                $category = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($category) {
                    $response = $category;
                    $http_code = 200;
                } else {
                    $response = ["error" => "Category not found"];
                    $http_code = 404;
                }
            } catch (PDOException $e) {
                $response = ["error" => "Database error: " . $e->getMessage()];
                $http_code = 500;
            }
        } else {
            try {
                $stmt = $conn->query("SELECT * FROM categories");
                $response = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $http_code = 200;
            } catch (PDOException $e) {
                $response = ["error" => "Database error: " . $e->getMessage()];
                $http_code = 500;
            }
        }
    } elseif ($method === 'POST') {
        $data = json_decode(file_get_contents("php://input"), true);
        if (!empty($data['name'])) {
            try {
                $name = $data['name'];
                $description = $data['description'] ?? '';

                $sql = "INSERT INTO categories (name, description) VALUES (:name, :description)";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':name', $name, PDO::PARAM_STR);
                $stmt->bindParam(':description', $description, PDO::PARAM_STR);
                $stmt->execute();
                $response = ["message" => "Category created successfully"];
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
        if (!empty($data['name'])) {
            try {
                $id = $request[2];
                $name = $data['name'];
                $description = $data['description'] ?? '';

                $sql = "UPDATE categories SET name = :name, description = :description WHERE id = :id";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                $stmt->bindParam(':name', $name, PDO::PARAM_STR);
                $stmt->bindParam(':description', $description, PDO::PARAM_STR);
                $stmt->execute();
                if ($stmt->rowCount() > 0) {
                    $response = ["message" => "Category updated successfully"];
                    $http_code = 200;
                } else {
                    $response = ["error" => "Category not found"];
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
            $sql = "DELETE FROM categories WHERE id = :id";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            if ($stmt->rowCount() > 0) {
                $response = ["message" => "Category deleted successfully"];
                $http_code = 200;
            } else {
                $response = ["error" => "Category not found"];
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

### ไฟล์ `orders.php`
```php
<?php
if (!isset($conn)) {
    require_once "config.php";
}

if (count($request) >= 2 && $request[0] === 'api' && $request[1] === 'orders') {
    if ($method === 'GET') {
        if (isset($request[2]) && is_numeric($request[2])) {
            try {
                $id = $request[2];
                $sql = "SELECT * FROM orders WHERE id = :id";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                $stmt->execute();
                $order = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($order) {
                    $response = $order;
                    $http_code = 200;
                } else {
                    $response = ["error" => "Order not found"];
                    $http_code = 404;
                }
            } catch (PDOException $e) {
                $response = ["error" => "Database error: " . $e->getMessage()];
                $http_code = 500;
            }
        } else {
            try {
                $stmt = $conn->query("SELECT * FROM orders");
                $response = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $http_code = 200;
            } catch (PDOException $e) {
                $response = ["error" => "Database error: " . $e->getMessage()];
                $http_code = 500;
            }
        }
    } elseif ($method === 'POST') {
        $data = json_decode(file_get_contents("php://input"), true);
        if (!empty($data['user_id']) && !empty($data['total_amount'])) {
            try {
                $user_id = $data['user_id'];
                $total_amount = $data['total_amount'];
                $status = $data['status'] ?? 'pending';

                $sql = "INSERT INTO orders (user_id, total_amount, status) 
                        VALUES (:user_id, :total_amount, :status)";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
                $stmt->bindParam(':total_amount', $total_amount, PDO::PARAM_STR);
                $stmt->bindParam(':status', $status, PDO::PARAM_STR);
                $stmt->execute();
                $response = ["message" => "Order created successfully", "order_id" => $conn->lastInsertId()];
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

                $sql = "UPDATE orders SET status = :status WHERE id = :id";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                $stmt->bindParam(':status', $status, PDO::PARAM_STR);
                $stmt->execute();
                if ($stmt->rowCount() > 0) {
                    $response = ["message" => "Order status updated successfully"];
                    $http_code = 200;
                } else {
                    $response = ["error" => "Order not found"];
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
            $sql = "DELETE FROM orders WHERE id = :id";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            if ($stmt->rowCount() > 0) {
                $response = ["message" => "Order cancelled successfully"];
                $http_code = 200;
            } else {
                $response = ["error" => "Order not found"];
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

### ไฟล์ `order_items.php`
```php
<?php
if (!isset($conn)) {
    require_once "config.php";
}

if (count($request) >= 4 && $request[0] === 'api' && $request[1] === 'orders' && is_numeric($request[2]) && $request[3] === 'items') {
    $order_id = $request[2];
    if ($method === 'GET') {
        try {
            $sql = "SELECT * FROM order_items WHERE order_id = :order_id";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':order_id', $order_id, PDO::PARAM_INT);
            $stmt->execute();
            $response = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $http_code = 200;
        } catch (PDOException $e) {
            $response = ["error" => "Database error: " . $e->getMessage()];
            $http_code = 500;
        }
    } elseif ($method === 'POST') {
        $data = json_decode(file_get_contents("php://input"), true);
        if (!empty($data['product_id']) && !empty($data['quantity']) && !empty($data['unit_price'])) {
            try {
                $product_id = $data['product_id'];
                $quantity = $data['quantity'];
                $unit_price = $data['unit_price'];

                $sql = "INSERT INTO order_items (order_id, product_id, quantity, unit_price) 
                        VALUES (:order_id, :product_id, :quantity, :unit_price)";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':order_id', $order_id, PDO::PARAM_INT);
                $stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
                $stmt->bindParam(':quantity', $quantity, PDO::PARAM_INT);
                $stmt->bindParam(':unit_price', $unit_price, PDO::PARAM_STR);
                $stmt->execute();
                $response = ["message" => "Order item added successfully"];
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
}
?>
```


## การทดสอบ API
1. **GET /api/users**
   - **Request** `GET http://127.0.0.1/api/users`
   - **Response**
     ```json
     [
         {"id": 1, "username": "user1", "email": "user1@example.com", "first_name": "John", "last_name": "Doe", "role": "customer"},
         {"id": 2, "username": "admin1", "email": "admin@example.com", "first_name": "Admin", "last_name": "User", "role": "admin"}
     ]
     ```
2. **POST /api/users/login**
   - **Request**
     ```json
     {"email": "user1@example.com", "password": "password123"}
     ```
   - **Response**
     ```json
     {"message": "Login successful", "user": {"id": 1, "username": "user1", "email": "user1@example.com", "role": "customer"}}
     ```
3. **POST /api/products**
   - **Request**
     ```json
     {"name": "Tablet", "price": 12000.00, "description": "Portable tablet", "category_id": 1, "stock": 30}
     ```
   - **Response**
     ```json
     {"message": "Product created successfully"}
     ```
4. **POST /api/orders**
   - **Request**
     ```json
     {"user_id": 1, "total_amount": 15000.00, "status": "pending"}
     ```
   - **Response**
     ```json
     {"message": "Order created successfully", "order_id": "2"}
     ```
5. **POST /api/orders/2/items**
   - **Request**
     ```json
     {"product_id": 2, "quantity": 1, "unit_price": 15000.00}
     ```
   - **Response**
     ```json
     {"message": "Order item added successfully"}
     ```

## การแก้ไขปัญหา
- **ฐานข้อมูล**:
  - ตรวจสอบ MySQL ใน XAMPP
  - ตรวจสอบ `config.php`
- **404**:
  - ตรวจสอบ `.htaccess` และ URL
- **500**:
  - ดู log ที่ `C:\php\logs\php_error_log`

## หมายเหตุ
- วันที่: 6 มิถุนายนยม 2568
- สภาพแวดล้อม: PHP 8.2, XAMPP, Windows 10
- เพิ่ม JWT สำหรับการตรวจสอบสิทธิ์ในอนาคต