# 🚀 E-Commerce Project (Laravel 12 + Vue 3)

Dự án gồm 2 phần:
- **Backend:** Laravel 12 (API)
- **Frontend:** Vue 3 (Vite)

---

## 📦 Yêu cầu hệ thống

- PHP >= 8.2
- Composer
- Node.js >= 18.x
- MySQL hoặc MariaDB

---

## 📁 Cấu trúc thư mục

project-root/
├── backend/ # Laravel 12
└── frontend/ # Vue 3 (Vite)

---


## ⚙️ Cài đặt Backend (Laravel)

```bash
cd backend

# Cài thư viện PHP(chạy lệnh này là dc, thiếu j nó tự cài vào ko cần chạy lệnh cài sanctum hôm trước nữa)
composer install(chạy trong terminal laragon cho nhanh)

# Tạo file .env từ template
cp .env.example .env

# Tạo khóa ứng dụng
php artisan key:generate

# Thiết lập DB trong file .env
# Sau đó chạy migrate + seed (nếu có)
php artisan migrate --seed
php artisan db:seed --class=VietnamAdministrativeSeeder
# Chạy server Laravel
php artisan serve

📌 Laravel chạy ở: http://localhost:8000 !!!

---

🌐 Cài đặt Frontend (Vue 3)

cd ../frontend

# Cài dependency
npm install

# Tạo file cấu hình nếu cần
cp .env.example .env

🔧 Trong file .env, bạn cần cấu hình đường dẫn API:

VITE_API_URL=http://localhost:8000/api

# Chạy server Vue 
npm run dev

📌 Vue chạy ở: http://localhost:5173 !!!


⚠️ Ghi chú
Đảm bảo Laravel cho phép CORS (Cấu hình trong middleware hoặc thủ công).

Nếu Vue không lấy được dữ liệu, hãy kiểm tra:

Đúng URL API trong .env

Laravel đang chạy

Có dữ liệu trong database

```mermaid
erDiagram
    PRODUCTS ||--o{ PRODUCT_VARIANTS : "has"
    PRODUCT_VARIANTS ||--o{ PRODUCT_VARIANT_ATTRIBUTE_VALUE : "has"
    ATTRIBUTES ||--o{ PRODUCT_VARIANT_ATTRIBUTE_VALUE : "defined by"
    ATTRIBUTE_VALUES ||--o{ PRODUCT_VARIANT_ATTRIBUTE_VALUE : "selected as"
    ATTRIBUTES ||--o{ ATTRIBUTE_VALUES : "has values"

    PRODUCTS {
        BIGINT id PK
        VARCHAR name
        VARCHAR slug
        DECIMAL price
        -- ... other product general fields
    }

    PRODUCT_VARIANTS {
        BIGINT id PK
        BIGINT product_id FK
        VARCHAR sku UK
        DECIMAL price
        INT stock
        INT sold
        ENUM status
        VARCHAR barcode UK
        VARCHAR description
        -- ... other variant specific fields
    }

    ATTRIBUTES {
        BIGINT id PK
        VARCHAR name
        VARCHAR slug UK
    }

    ATTRIBUTE_VALUES {
        BIGINT id PK
        BIGINT attribute_id FK
        VARCHAR value
    }

    PRODUCT_VARIANT_ATTRIBUTE_VALUE {
        BIGINT id PK
        BIGINT product_variant_id FK
        BIGINT attribute_id FK
        BIGINT attribute_value_id FK
        VARCHAR unique_constraint_on_variant_attribute "UK(product_variant_id, attribute_id)"
    }
    ```