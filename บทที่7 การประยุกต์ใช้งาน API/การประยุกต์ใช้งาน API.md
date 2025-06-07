# บทที่ 7 การประยุกต์ใช้งาน API ในระบบจริง

เอกสารบทที่ 7 ซึ่งมุ่งเน้นการประยุกต์ใช้งาน RESTful API ที่พัฒนาในหน่วยที่ 5 โดยเชื่อมต่อกับ Front-end ที่ใช้ **Bootstrap 5** และ **JavaScript Fetch** ออกแบบ UI/UX สำหรับสมาร์ทโฟนโดยอ้างอิงจาก **Bootstrap 5 Mobile App Templates** (เช่น Rawal, Kartero) และรันบน XAMPP ใน Windows 10 เนื้อหาครอบคลุมการออกแบบระบบ API-Centric, การจัดการข้อมูลผ่านฟอร์ม, การแสดงผลเรียลไทม์, และการสร้าง API Documentation

## ✳️ จุดประสงค์หน่วยการเรียนรู้
1. สามารถนำ API ที่พัฒนาขึ้นไปใช้งานร่วมกับ Front-end ได้
2. ประยุกต์ใช้ API ในงานอาชีพจริง เช่น ระบบจัดการผู้ใช้งาน
3. มีทักษะการเชื่อมต่อและส่งข้อมูลระหว่าง Front-end กับ Back-end
4. ทำงานร่วมกันเป็นทีมเพื่อพัฒนาระบบที่สมบูรณ์แบบ

## 📚 สาระการเรียนรู้
- การออกแบบระบบที่ใช้ API เป็นศูนย์กลาง (API-Centric Design)
- การเชื่อมต่อ API กับ Front-end (ใช้ Bootstrap 5 + JavaScript Fetch)
- การจัดการข้อมูลในแบบฟอร์ม ส่งผ่าน API ไปยัง Back-end
- การแสดงผลข้อมูลจาก API แบบเรียลไทม์
- การวิเคราะห์เคสตัวอย่างจริง เช่น ระบบลงทะเบียนผู้ใช้งาน
- แนวทาง Deploy ระบบ API สู่เครื่อง Server หรือ Hosting
- การทำเอกสารประกอบการใช้งาน API (API Documentation ด้วย Markdown)

## 🧪 กิจกรรมการเรียนรู้
1. ออกแบบ UI/UX สำหรับสมาร์ทโฟนด้วย Bootstrap 5 Mobile App Template
2. พัฒนา Front-end เพื่อเชื่อมต่อกับ API สำหรับระบบจัดการผู้ใช้งาน (Login, Register, View Profile)
3. สร้างฟอร์มสำหรับส่งข้อมูลไปยัง API และแสดงผลข้อมูลจาก API
4. ทดสอบระบบบนสมาร์ทโฟนจำลอง (Chrome DevTools)
5. ทำงานเป็นทีมเพื่อพัฒนาระบบสมบูรณ์
6. เขียน API Documentation ด้วย Markdown
7. อภิปรายแนวทางการ Deploy ไปยัง Server

## ฐานข้อมูลและ Back-end
- **ฐานข้อมูล** ใช้ `api_db` จากหน่วยที่ 5 (รวมตาราง `users`, `products`, `categories`, `orders`, `order_items`, `addresses`, `payments`, `reviews`)
- **Back-end** ใช้โครงสร้างไฟล์และโค้ดจากหน่วยที่ 5 (`config.php`, `index.php`, `middleware.php`, `users.php`, ฯลฯ) ซึ่งรวม JWT, CORS, และ Role-based Access
- **การเปลี่ยนแปลง** ไม่ต้องปรับฐานข้อมูลหรือ Back-end เพิ่มเติม เพราะรองรับการจัดการผู้ใช้งานแล้ว

## โครงสร้างไฟล์ (เพิ่ม Front-end)

### โครงสร้างโฟลเดอร์
```
F:\xampp\htdocs\api\
├── backend/                    # โฟลเดอร์ Back-end (ย้ายจากหน่วยที่ 5)
│   ├── config.php
│   ├── index.php
│   ├── middleware.php
│   ├── users.php
│   ├── products.php
│   ├── categories.php
│   ├── orders.php
│   ├── order_items.php
│   ├── addresses.php
│   ├── payments.php
│   ├── reviews.php
│   ├── .htaccess
│   ├── composer.json
│   ├── vendor/
├── frontend/                   # โฟลเดอร์ Front-end
│   ├── index.html             # หน้า Login
│   ├── register.html          # หน้า Register
│   ├── profile.html           # หน้า Profile
│   ├── assets/
│   │   ├── css/
│   │   │   ├── styles.css     # Custom CSS สำหรับ Mobile UI
│   │   ├── js/
│   │   │   ├── auth.js        # JavaScript สำหรับ Login/Register
│   │   │   ├── profile.js     # JavaScript สำหรับ Profile
├── docs/
│   ├── api_documentation.md   # API Documentation
├── .htaccess                  # Rewrite rules สำหรับ Front-end
```

### ไฟล์ `.htaccess` (ใน `F:\xampp\htdocs\api`)
```apache
RewriteEngine On

# Redirect API requests to backend
RewriteCond %{REQUEST_URI} ^/api/
RewriteRule ^api/(.*)$ backend/index.php [L]

# Serve frontend files
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ frontend/index.html [L]
```

### ไฟล์ `frontend/index.html` (หน้า Login)
```html
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/styles.css" rel="stylesheet">
</head>
<body>
    <div class="container d-flex flex-column min-vh-100 justify-content-center">
        <div class="card shadow-lg border-0 rounded-4 mx-auto" style="max-width: 400px;">
            <div class="card-body p-4">
                <h3 class="text-center mb-4 fw-bold">Login</h3>
                <div id="error-message" class="alert alert-danger d-none"></div>
                <form id="login-form">
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control rounded-pill" id="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control rounded-pill" id="password" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 rounded-pill">Login</button>
                </form>
                <p class="text-center mt-3">Don't have an account? <a href="register.html">Register</a></p>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/auth.js"></script>
</body>
</html>
```

### ไฟล์ `frontend/register.html` (หน้า Register)
```html
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/styles.css" rel="stylesheet">
</head>
<body>
    <div class="container d-flex flex-column min-vh-100 justify-content-center">
        <div class="card shadow-lg border-0 rounded-4 mx-auto" style="max-width: 400px;">
            <div class="card-body p-4">
                <h3 class="text-center mb-4 fw-bold">Register</h3>
                <div id="error-message" class="alert alert-danger d-none"></div>
                <form id="register-form">
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control rounded-pill" id="username" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control rounded-pill" id="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control rounded-pill" id="password" required>
                    </div>
                    <div class="mb-3">
                        <label for="first_name" class="form-label">First Name</label>
                        <input type="text" class="form-control rounded-pill" id="first_name">
                    </div>
                    <div class="mb-3">
                        <label for="last_name" class="form-label">Last Name</label>
                        <input type="text" class="form-control rounded-pill" id="last_name">
                    </div>
                    <button type="submit" class="btn btn-primary w-100 rounded-pill">Register</button>
                </form>
                <p class="text-center mt-3">Already have an account? <a href="index.html">Login</a></p>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/auth.js"></script>
</body>
</html>
```

### ไฟล์ `frontend/profile.html` (หน้า Profile)
```html
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/styles.css" rel="stylesheet">
</head>
<body>
    <div class="container py-4">
        <div class="card shadow-lg border-0 rounded-4 mb-4">
            <div class="card-body text-center">
                <h3 class="fw-bold">User Profile</h3>
                <div id="error-message" class="alert alert-danger d-none"></div>
                <div id="profile-info">
                    <p><strong>Username:</strong> <span id="username"></span></p>
                    <p><strong>Email:</strong> <span id="email"></span></p>
                    <p><strong>First Name:</strong> <span id="first_name"></span></p>
                    <p><strong>Last Name:</strong> <span id="last_name"></span></p>
                    <p><strong>Role:</strong> <span id="role"></span></p>
                </div>
                <button id="logout-btn" class="btn btn-danger w-100 rounded-pill">Logout</button>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/profile.js"></script>
</body>
</html>
```

### ไฟล์ `frontend/assets/css/styles.css` (Custom CSS สำหรับ Mobile UI)
```css
body {
    background-color: #f8f9fa;
    font-family: 'Roboto', sans-serif;
}

.card {
    transition: all 0.3s ease;
}

.card:hover {
    transform: translateY(-5px);
}

.btn-primary {
    background-color: #007bff;
    border: none;
    transition: background-color 0.3s ease;
}

.btn-primary:hover {
    background-color: #0056b3;
}

.form-control {
    border: 1px solid #ced4da;
    transition: border-color 0.3s ease;
}

.form-control:focus {
    border-color: #007bff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

/* Mobile-first design inspired by Rawal/Kartero */
@media (max-width: 576px) {
    .container {
        padding: 15px;
    }

    .card {
        border-radius: 20px;
        padding: 20px;
    }

    .btn {
        font-size: 1rem;
        padding: 10px;
    }

    .form-control {
        font-size: 0.9rem;
        padding: 12px;
    }

    h3 {
        font-size: 1.5rem;
    }
}
```

### ไฟล์ `frontend/assets/js/auth.js` (จัดการ Login/Register)
```javascript
const API_URL = 'http://127.0.0.1/api';

// แสดงข้อความ error
function showError(message) {
    const errorDiv = document.getElementById('error-message');
    errorDiv.textContent = message;
    errorDiv.classList.remove('d-none');
}

// ฟังก์ชัน Login
if (document.getElementById('login-form')) {
    document.getElementById('login-form').addEventListener('submit', async (e) => {
        e.preventDefault();
        const email = document.getElementById('email').value;
        const password = document.getElementById('password').value;

        try {
            const response = await fetch(`${API_URL}/users/login`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ email, password })
            });
            const data = await response.json();

            if (response.ok) {
                localStorage.setItem('token', data.token);
                localStorage.setItem('userId', data.user.id);
                window.location.href = 'profile.html';
            } else {
                showError(data.error || 'Login failed');
            }
        } catch (error) {
            showError('Network error');
        }
    });
}

// ฟังก์ชัน Register
if (document.getElementById('register-form')) {
    document.getElementById('register-form').addEventListener('submit', async (e) => {
        e.preventDefault();
        const username = document.getElementById('username').value;
        const email = document.getElementById('email').value;
        const password = document.getElementById('password').value;
        const first_name = document.getElementById('first_name').value;
        const last_name = document.getElementById('last_name').value;

        try {
            const response = await fetch(`${API_URL}/users`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ username, email, password, first_name, last_name })
            });
            const data = await response.json();

            if (response.ok) {
                window.location.href = 'index.html';
            } else {
                showError(data.error || 'Registration failed');
            }
        } catch (error) {
            showError('Network error');
        }
    });
}
```

### ไฟล์ `frontend/assets/js/profile.js` (จัดการ Profile)
```javascript
const API_URL = 'http://127.0.0.1/api';

function showError(message) {
    const errorDiv = document.getElementById('error-message');
    errorDiv.textContent = message;
    errorDiv.classList.remove('d-none');
}

// ดึงข้อมูลผู้ใช้
async function fetchProfile() {
    const token = localStorage.getItem('token');
    const userId = localStorage.getItem('userId');

    if (!token || !userId) {
        window.location.href = 'index.html';
        return;
    }

    try {
        const response = await fetch(`${API_URL}/users/${userId}`, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${token}`
            }
        });
        const data = await response.json();

        if (response.ok) {
            document.getElementById('username').textContent = data.username;
            document.getElementById('email').textContent = data.email;
            document.getElementById('first_name').textContent = data.first_name || '-';
            document.getElementById('last_name').textContent = data.last_name || '-';
            document.getElementById('role').textContent = data.role;
        } else {
            showError(data.error || 'Failed to load profile');
        }
    } catch (error) {
        showError('Network error');
    }
}

// Logout
document.getElementById('logout-btn').addEventListener('click', () => {
    localStorage.removeItem('token');
    localStorage.removeItem('userId');
    window.location.href = 'index.html';
});

// โหลดข้อมูลเมื่อหน้าโหลด
window.onload = fetchProfile;
```

### ไฟล์ `docs/api_documentation.md` (API Documentation)
```markdown
# API Documentation

## Base URL
```
http://127.0.0.1/api
```

## Authentication
- **JWT**: Most endpoints require a Bearer token in the `Authorization` header.
- **Login**: Use `POST /users/login` to obtain a token.

### POST /users/login
**Description**: Authenticate user and return JWT.
**Request**:
```json
{
    "email": "user1@example.com",
    "password": "password123"
}
```
**Response** (200):
```json
{
    "message": "Login successful",
    "token": "eyJ0...",
    "user": {
        "id": 1,
        "username": "user1",
        "email": "user1@example.com",
        "role": "customer"
    }
}
```
**Error** (401):
```json
{
    "error": "Invalid email or password"
}
```

## Users
### POST /users
**Description**: Register a new user.
**Request**:
```json
{
    "username": "newuser",
    "email": "newuser@example.com",
    "password": "password123",
    "first_name": "New",
    "last_name": "User"
}
```
**Response** (201):
```json
{
    "message": "User created successfully"
}
```

### GET /users/{id}
**Description**: Get user details (requires JWT, admin or own ID).
**Headers**:
```
Authorization: Bearer <token>
```
**Response** (200):
```json
{
    "id": 1,
    "username": "user1",
    "email": "user1@example.com",
    "first_name": "John",
    "last_name": "Doe",
    "role": "customer",
    "created_at": "2025-06-07 12:00:00",
    "updated_at": "2025-06-07 12:00:00"
}
```
**Error** (403):
```json
{
    "error": "Insufficient permissions"
}
```

## Products
### GET /products
**Description**: Get all products.
**Response** (200):
```json
[
    {
        "id": 1,
        "name": "Laptop",
        "price": 25000.00,
        "description": "High-performance laptop",
        "category_id": 1,
        "stock": 50
    },
    ...
]
```

### POST /products
**Description**: Create a product (requires JWT, admin only).
**Headers**:
```
Authorization: Bearer <token>
```
**Request**:
```json
{
    "name": "Tablet",
    "price": 12000.00,
    "description": "Portable tablet",
    "category_id": 1,
    "stock": 30
}
```
**Response** (201):
```json
{
    "message": "Product created successfully"
}
```

## HTTP Status Codes
- **200**: OK
- **201**: Created
- **400**: Bad Request
- **401**: Unauthorized
- **403**: Forbidden
- **404**: Not Found
- **500**: Internal Server Error
```

## การออกแบบ UI/UX
- **แรงบันดาลใจ** อ้างอิงจาก **Rawal** และ **Kartero** ซึ่งเน้น UI ที่สะอาด, ปุ่มโค้งมน, และการ์ดที่มีเงา
- **ลักษณะ**
  - การ์ดมีขอบโค้ง (border-radius: 20px)
  - ปุ่มแบบ rounded-pill และสี primary (#007bff)
  - Input field มี animation เมื่อ focus
  - Responsive design สำหรับสมาร์ทโฟน (max-width: 576px)
- **ทดสอบ Mobile View**
  - ใช้ Chrome DevTools (เลือกอุปกรณ์ เช่น iPhone SE)
  - ตรวจสอบว่าฟอร์มและปุ่มใช้งานง่ายบนหน้าจอเล็ก

## การแก้ไขปัญหา
- **API**
  - ตรวจสอบ Back-end ว่าทำงานปกติ (`http://127.0.0.1/api/users/login`)
  - ตรวจสอบ JWT Secret ใน `config.php`
- **Front-end**
  - ตรวจสอบ Console ใน Chrome DevTools
  - ตรวจสอบว่า Bootstrap CDN โหลดสำเร็จ
- **CORS**
  - ตรวจสอบ `middleware.php` ว่า `Access-Control-Allow-Origin` ถูกตั้งค่า
- **Mobile View**
  - ใช้ Chrome DevTools ตรวจสอบ Responsive Design

## การ Deploy (แนวทาง)
1. **เลือก Hosting**
   - Shared Hosting (เช่น Hostinger) หรือ VPS (เช่น DigitalOcean)
2. **ตั้งค่า Server**
   - ติดตั้ง PHP, MySQL, Apache/Nginx
   - อัปโหลดโฟลเดอร์ `backend` และ `frontend`
3. **ปรับแต่ง**
   - อัปเดต `API_URL` ใน JavaScript
   - จำกัด CORS เฉพาะ domain ใน `middleware.php`
   - ใช้ `.env` สำหรับ JWT Secret
4. **ฐานข้อมูล**
   - Export `api_db` จาก phpMyAdmin และนำเข้าไปยัง Server
5. **SSL**
   - เปิดใช้งาน HTTPS ด้วย Let’s Encrypt

## หมายเหตุ
- วันที่: 7 มิถุนายน 2568
- สภาพแวดล้อม: PHP 8.1, XAMPP, MySQL, Windows 10
- UI/UX ออกแบบสำหรับสมาร์ทโฟน (576px) โดยใช้ Bootstrap 5
- ในระบบจริง ควรเพิ่ม Rate Limiting และใช้ `.env` สำหรับ Secret Key