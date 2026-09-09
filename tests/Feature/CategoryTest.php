<?php

namespace Tests\Feature;

use App\Models\ExpenseCategory;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_requires_a_name_and_a_valid_type(): void
    {
        $response = $this->post('/cats', ['cat_type' => 'ไม่มีอยู่จริง']);

        $response->assertSessionHasErrors(['cat_name', 'cat_type']);
        $this->assertSame(0, ExpenseCategory::count());
    }

    public function test_store_creates_a_category(): void
    {
        $this->post('/cats', ['cat_name' => 'อาหาร', 'cat_type' => 'รายจ่าย']);

        $this->assertSame(1, ExpenseCategory::count());
    }

    public function test_update_changes_the_name_and_type(): void
    {
        $cat = ExpenseCategory::create(['cat_name' => 'เก่า', 'cat_type' => 'รายรับ']);

        $response = $this->put("/cats/{$cat->cat_id}", [
            'cat_name' => 'ใหม่',
            'cat_type' => 'รายจ่าย',
        ]);

        $response->assertRedirect(route('home'));
        $this->assertSame('ใหม่', $cat->fresh()->cat_name);
        $this->assertSame('รายจ่าย', $cat->fresh()->cat_type);
    }

    public function test_deleting_a_category_cascades_to_its_transactions(): void
    {
        $cat = ExpenseCategory::create(['cat_name' => 'ลบทิ้ง', 'cat_type' => 'รายจ่าย']);
        Transaction::create(['cat_id' => $cat->cat_id, 'ts_date' => '2026-09-09', 'ts_amount' => 100]);

        $this->delete("/cats/{$cat->cat_id}");

        $this->assertSame(0, ExpenseCategory::count());
        $this->assertSame(0, Transaction::count());
    }

    public function test_deleting_an_already_deleted_category_redirects_instead_of_404ing(): void
    {
        $cat = ExpenseCategory::create(['cat_name' => 'ลบสองครั้ง', 'cat_type' => 'รายจ่าย']);

        $first = $this->delete("/cats/{$cat->cat_id}");
        $second = $this->delete("/cats/{$cat->cat_id}");

        $first->assertRedirect(route('home'));
        $second->assertRedirect(route('home'));
    }

    public function test_the_transaction_form_hints_to_add_a_category_when_none_exist(): void
    {
        $this->get('/')->assertSee('ยังไม่มีหมวดหมู่ กรุณาเพิ่มหมวดหมู่ก่อนบันทึกรายการ');
    }
}
