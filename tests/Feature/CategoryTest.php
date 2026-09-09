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

    public function test_type_can_be_changed_while_the_category_has_no_transactions(): void
    {
        $cat = ExpenseCategory::create(['cat_name' => 'ทดสอบ', 'cat_type' => 'รายรับ']);

        $response = $this->put("/cats/{$cat->cat_id}", [
            'cat_name' => 'ทดสอบ',
            'cat_type' => 'รายจ่าย',
        ]);

        $response->assertRedirect(route('home'));
        $this->assertSame('รายจ่าย', $cat->fresh()->cat_type);
    }

    public function test_type_cannot_be_changed_once_the_category_has_transactions(): void
    {
        $cat = ExpenseCategory::create(['cat_name' => 'เงินเดือน', 'cat_type' => 'รายรับ']);
        Transaction::create(['cat_id' => $cat->cat_id, 'ts_date' => '2026-09-09', 'ts_amount' => 100]);

        $response = $this->put("/cats/{$cat->cat_id}", [
            'cat_name' => 'เงินเดือน',
            'cat_type' => 'รายจ่าย',
        ]);

        $response->assertSessionHasErrors('cat_type');
        $this->assertSame('รายรับ', $cat->fresh()->cat_type);
    }

    public function test_the_name_can_still_be_changed_once_the_category_has_transactions(): void
    {
        $cat = ExpenseCategory::create(['cat_name' => 'เก่า', 'cat_type' => 'รายรับ']);
        Transaction::create(['cat_id' => $cat->cat_id, 'ts_date' => '2026-09-09', 'ts_amount' => 100]);

        $response = $this->put("/cats/{$cat->cat_id}", [
            'cat_name' => 'ใหม่',
            'cat_type' => 'รายรับ',
        ]);

        $response->assertRedirect(route('home'));
        $this->assertSame('ใหม่', $cat->fresh()->cat_name);
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

    public function test_two_genuinely_different_categories_both_get_created(): void
    {
        $this->post('/cats', ['cat_name' => 'หนึ่ง', 'cat_type' => 'รายรับ']);
        $this->post('/cats', ['cat_name' => 'สอง', 'cat_type' => 'รายรับ']);

        $this->assertSame(2, ExpenseCategory::count());
    }

    public function test_the_transaction_form_hints_to_add_a_category_when_none_exist(): void
    {
        $this->get('/')->assertSee('ยังไม่มีหมวดหมู่ กรุณาเพิ่มหมวดหมู่ก่อนบันทึกรายการ');
    }
}
