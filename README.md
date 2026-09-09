# ระบบบันทึกรายรับ-รายจ่ายตามหมวดหมู่

> **Expense Tracker** — บริบท: การจัดการการเงินส่วนบุคคล

## โครงสร้างฐานข้อมูล

ความสัมพันธ์แบบ **One-to-Many** — หนึ่งหมวดหมู่มีได้หลายรายการ

```
expense_categories (Parent)
        │
        └──< transactions (Child)
```

### ตารางที่ 1 — `expense_categories` (Parent)

| คอลัมน์      | ชนิดข้อมูล | คำอธิบาย |
|--------------|---|---|
| `cat_id`     | bigint | รหัสหมวดหมู่ (PK) |
| `cat_name`   | string | ชื่อหมวดหมู่ |
| `cat_type`       | enum | `income` / `expense` |
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
