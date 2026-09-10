# ระบบบันทึกรายรับ-รายจ่ายตามหมวดหมู่

> **Expense Tracker** — บริบท: การจัดการการเงินส่วนบุคคล

CRUD ล้วน ๆ: หมวดหมู่ (`expense_categories`) กับรายการ (`transactions`) เพิ่ม/แก้ไข/ลบได้ทั้งคู่ ไม่มีระบบกรอง ไม่มีกราฟ ไม่มีการจัดกลุ่มตามวัน — แค่ลิสต์รายการทั้งหมดเรียงตามวันที่ล่าสุดก่อน บวกยอดรวมรายรับ/รายจ่าย/คงเหลือด้านบน

## โครงสร้างฐานข้อมูล

ความสัมพันธ์แบบ **One-to-Many** — หนึ่งหมวดหมู่มีได้หลายรายการ

```
expense_categories (Parent)
        │
        └──< transactions (Child)
```

### ตารางที่ 1 — `expense_categories` (Parent)

| คอลัมน์      | ชนิดข้อมูล | คำอธิบาย               |
|--------------|---|------------------------|
| `cat_id`     | bigint | รหัสหมวดหมู่ (PK)      |
| `cat_name`   | string | ชื่อหมวดหมู่           |
| `cat_type`       | enum | `รายรับ` / `รายจ่าย`   |
| `timestamps` | timestamp | created_at, updated_at |

### ตารางที่ 2 — `transactions` (Child)

| คอลัมน์      | ชนิดข้อมูล | คำอธิบาย |
|--------------|---|---|
| `ts_id`      | bigint | รหัสรายการ (PK) |
| `cat_id`     | bigint | อ้างอิงหมวดหมู่ (FK) |
| `ts_amount`  | decimal | จำนวนเงิน |
| `ts_date`    | date | วันที่ทำรายการ |
| `ts_note`    | text | หมายเหตุ |
| `timestamps` | timestamp | created_at, updated_at |

## ฟีเจอร์

- **หมวดหมู่**: เพิ่ม / แก้ไข / ลบ (ลบหมวดหมู่แล้วรายการในหมวดหมู่นั้นถูกลบตามด้วย — cascade ที่ระดับฐานข้อมูล)
- **รายการ**: เพิ่ม / แก้ไข / ลบ ผูกกับหมวดหมู่หนึ่งรายการเสมอ
- **สรุปยอด**: รายรับรวม, รายจ่ายรวม, คงเหลือ — คำนวณสดจากรายการทั้งหมดทุกครั้งที่โหลดหน้า

ไม่มีการล็อกอิน ไม่มี JavaScript (โมดัลยืนยัน ฯลฯ ก็ไม่มี — กดลบคือลบเลย) — เว็บเพจเดียว โหลดครั้งเดียว ฟอร์มทุกอันคือ HTML `<form>` ธรรมดา

## โครงสร้างโค้ด

คอนโทรลเลอร์เดียว `App\Http\Controllers\DoEveryThing` จัดการทุก route:

| Method | Route | Action |
|---|---|---|
| GET  | `/` | `index` — แสดงหน้าเดียวทั้งหมด |
| POST | `/trans` | `store_trans` |
| PUT  | `/trans/{tran}` | `update_trans` |
| DELETE | `/trans/{tran}` | `destroy_trans` |
| POST | `/cats` | `store_cat` |
| PUT  | `/cats/{cat}` | `update_cat` |
| DELETE | `/cats/{cat}` | `destroy_cat` |

การ "แก้ไข" ทำผ่าน query string (`?edit=<id>` หรือ `?edit_cat=<id>`) — คลิก "แก้ไข" แล้วฟอร์มด้านซ้ายจะเปลี่ยนมาเติมข้อมูลเดิมให้ พร้อมสลับปุ่ม submit เป็นโหมดอัปเดต (`@method('PUT')`) แทนที่จะมีหน้า/route แยกสำหรับแก้ไขต่างหาก

Views อยู่ที่ `resources/views/index.blade.php` (เลย์เอาต์หลัก) + `resources/views/content/*.blade.php` (ฟอร์มรายการ, ฟอร์ม+ลิสต์หมวดหมู่, ตารางรายการ, แถบสรุปยอด)

## วิธีรัน

```
php artisan migrate
php artisan serve
```

ตั้งค่าฐานข้อมูลใน `.env` ตามปกติของ Laravel (MySQL, ตาราง `expense_categories` และ `transactions` ตามด้านบน)
