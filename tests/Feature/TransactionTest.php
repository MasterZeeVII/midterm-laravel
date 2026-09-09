<?php

namespace Tests\Feature;

use App\Models\ExpenseCategory;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionTest extends TestCase
{
    use RefreshDatabase;

    private function income_category(): ExpenseCategory
    {
        return ExpenseCategory::create([
            'cat_name' => 'เงินเดือน',
            'cat_type' => 'รายรับ',
        ]);
    }

    public function test_store_requires_the_core_fields(): void
    {
        $response = $this->post('/trans', []);

        $response->assertSessionHasErrors(['cat_id', 'ts_date', 'ts_amount']);
        $this->assertSame(0, Transaction::count());
    }

    public function test_store_rejects_an_amount_too_large_for_the_column(): void
    {
        $cat = $this->income_category();

        $response = $this->post('/trans', [
            'cat_id' => $cat->cat_id,
            'ts_date' => '2026-09-09',
            'ts_amount' => '999999999999999999',
        ]);

        $response->assertSessionHasErrors('ts_amount');
        $this->assertSame(0, Transaction::count());
    }

    public function test_store_creates_a_transaction(): void
    {
        $cat = $this->income_category();

        $this->post('/trans', [
            'cat_id' => $cat->cat_id, 'ts_date' => '2026-09-09', 'ts_amount' => '100', 'ts_note' => 'x',
        ]);

        $this->assertSame(1, Transaction::where('ts_note', 'x')->count());
    }

    public function test_update_changes_the_stored_values(): void
    {
        $cat = $this->income_category();
        $trans = Transaction::create([
            'cat_id' => $cat->cat_id, 'ts_date' => '2026-09-09', 'ts_amount' => 50,
        ]);

        $this->put("/trans/{$trans->ts_id}", [
            'cat_id' => $cat->cat_id, 'ts_date' => '2026-09-10', 'ts_amount' => '75',
        ]);

        $this->assertSame('75.00', $trans->fresh()->ts_amount);
    }

    public function test_deleting_an_already_deleted_transaction_redirects_instead_of_404ing(): void
    {
        $cat = $this->income_category();
        $trans = Transaction::create([
            'cat_id' => $cat->cat_id, 'ts_date' => '2026-09-09', 'ts_amount' => 50,
        ]);

        $first = $this->delete("/trans/{$trans->ts_id}");
        $second = $this->delete("/trans/{$trans->ts_id}");

        $first->assertRedirect(route('home'));
        $second->assertRedirect(route('home'));
        $this->assertSame(0, Transaction::count());
    }

    public function test_malformed_array_query_params_do_not_crash_the_page(): void
    {
        $this->get('/?edit[]=1')->assertOk();
        $this->get('/?edit_cat[]=1')->assertOk();
    }

    public function test_transactions_on_the_same_date_show_newest_first(): void
    {
        $cat = $this->income_category();
        $earlier = Transaction::create(['cat_id' => $cat->cat_id, 'ts_date' => '2026-09-09', 'ts_amount' => 1, 'ts_note' => 'first_entry_marker']);
        $later = Transaction::create(['cat_id' => $cat->cat_id, 'ts_date' => '2026-09-09', 'ts_amount' => 2, 'ts_note' => 'second_entry_marker']);

        $content = $this->get('/')->getContent();

        $this->assertTrue(strpos($content, 'second_entry_marker') < strpos($content, 'first_entry_marker'));
    }

    public function test_negative_amounts_are_rejected(): void
    {
        $cat = $this->income_category();

        $response = $this->post('/trans', [
            'cat_id' => $cat->cat_id, 'ts_date' => '2026-09-09', 'ts_amount' => '-50',
        ]);

        $response->assertSessionHasErrors('ts_amount');
        $this->assertSame(0, Transaction::count());
    }

    public function test_a_non_numeric_id_in_the_url_redirects_instead_of_erroring(): void
    {
        $response = $this->delete('/trans/not-a-number');

        $response->assertRedirect(route('home'));
    }

    public function test_a_note_containing_html_is_escaped_not_executed(): void
    {
        $cat = $this->income_category();
        Transaction::create([
            'cat_id' => $cat->cat_id, 'ts_date' => '2026-09-09', 'ts_amount' => 1,
            'ts_note' => '<script>alert(1)</script>',
        ]);

        $this->get('/')
            ->assertDontSee('<script>alert(1)</script>', false)
            ->assertSee('<script>alert(1)</script>');
    }
}
