<?php

namespace App\Http\Controllers;

use App\Models\ExpenseCategory;
use App\Models\Transaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;

class DoEveryThing extends Controller {
    public function index(Request $request) {
        $cats = ExpenseCategory::all();
        $trans = Transaction::with('category')
            ->orderByDesc('ts_date')
            ->orderByDesc('ts_id')
            ->get();

        return view('index', [
            'cats' => $cats,
            'trans' => $trans,
            'totals' => $this->totals($trans),
            // Cast so a hand-edited URL like ?edit[]=1 can't reach find()
            // as an array and blow up the page.
            'editing' => Transaction::find((int) $request->query('edit')),
            'editingCat' => ExpenseCategory::find((int) $request->query('edit_cat')),
        ]);
    }

    private function totals(Collection $trans): array {
        $income = 0;
        $expense = 0;

        foreach ($trans as $tran) {
            if ($tran->category?->isExpense()) {
                $expense += $tran->ts_amount;
            } else {
                $income += $tran->ts_amount;
            }
        }

        return ['income' => $income, 'expense' => $expense, 'balance' => $income - $expense];
    }

    public function store_trans(Request $request): RedirectResponse {
        Transaction::create($this->validate_trans($request));

        return redirect()->route('home');
    }

    public function update_trans(Request $request, Transaction $tran): RedirectResponse {
        $tran->update($this->validate_trans($request));

        return redirect()->route('home');
    }

    public function destroy_trans(Transaction $tran): RedirectResponse {
        $tran->delete();

        return redirect()->route('home');
    }

    private function validate_trans(Request $request): array {
        $data = $request->validate([
            'cat_id' => ['required', 'exists:expense_categories,cat_id'],
            'ts_date' => ['required', 'date'],
            'ts_amount' => ['required', 'numeric', 'min:0', 'max:99999999999999.99'],
            'ts_note' => ['nullable', 'string', 'max:16000'],
        ]);

        $data['ts_note'] ??= null;

        return $data;
    }

    public function store_cat(Request $request): RedirectResponse {
        ExpenseCategory::create($this->validate_cat($request));

        return redirect()->route('home');
    }

    public function update_cat(Request $request, ExpenseCategory $cat): RedirectResponse {
        $cat->update($this->validate_cat($request));

        return redirect()->route('home');
    }

    public function destroy_cat(ExpenseCategory $cat): RedirectResponse {
        $cat->delete();

        return redirect()->route('home');
    }

    private function validate_cat(Request $request): array {
        return $request->validate([
            'cat_name' => ['required', 'string', 'max:255'],
            'cat_type' => ['required', Rule::in([ExpenseCategory::INCOME, ExpenseCategory::EXPENSE])],
        ]);
    }
}
