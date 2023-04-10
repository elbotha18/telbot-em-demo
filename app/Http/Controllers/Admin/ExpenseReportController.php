<?php

namespace App\Http\Controllers\Admin;

use Auth;
use App\Income;
use App\Expense;
use Carbon\Carbon;
use App\IncomeCategory;
use App\ExpenseCategory;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class ExpenseReportController extends Controller
{
    public function index()
    {
        if (request('m') === 'all')
        {
            $from = Carbon::parse(sprintf(
                '%s-01-01',
                request()->query('y', Carbon::now()->year)
            ));
            $to      = clone $from;
            $to->day = $to->daysInYear;
        }
        else {
            $from = Carbon::parse(sprintf(
                '%s-%s-01',
                request()->query('y', Carbon::now()->year),
                request()->query('m', Carbon::now()->month)
            ));
            $to      = clone $from;
            $to->day = $to->daysInMonth;
        }

        $viewMode = request('viewMode') ?? 'personal';
        if ($viewMode == 'personal') {
            $expenses = Expense::where('created_by_id', Auth::id())
                ->with('expense_category')
                ->whereBetween('entry_date', [$from, $to]);

            $incomes = Income::where('created_by_id', Auth::id())
                ->with('income_category')
                ->whereBetween('entry_date', [$from, $to]);
        } else {
            $expenses = Expense::whereBetween('entry_date', [$from, $to]);
            $expenses->expense_category = ExpenseCategory::all();
            
            $incomes = Income::whereBetween('entry_date', [$from, $to]);
            $incomes->income_category = IncomeCategory::all();
        }

        $expensesTotal   = $expenses->sum('amount');
        $incomesTotal    = $incomes->sum('amount');
        $groupedExpenses = $expenses->whereNotNull('expense_category_id')->orderBy('amount', 'desc')->get()->groupBy('expense_category_id');
        $groupedIncomes  = $incomes->whereNotNull('income_category_id')->orderBy('amount', 'desc')->get()->groupBy('income_category_id');
        $profit          = $incomesTotal - $expensesTotal;
        
        $monthlyBreakdown = null;
        if (request('m') === 'all')
        {
            $monthlyBreakdown = DB::table('expenses')
            ->select(DB::raw('expense_category_id, MONTH(entry_date) as month, YEAR(entry_date) as year, SUM(amount) as total'))
            ->groupBy('expense_category_id', 'year', 'month')
            ->orderBy('year', 'asc')
            ->orderBy('month', 'asc')
            ->get();
        }

        $expensesSummary = [];

        foreach ($groupedExpenses as $exp) {
            foreach ($exp as $line) {
                if (!isset($expensesSummary[$line->expense_category->name])) {
                    $expensesSummary[$line->expense_category->name] = [
                        'id' => $line->expense_category->id,
                        'name'   => $line->expense_category->name,
                        'amount' => 0,
                    ];
                }

                $expensesSummary[$line->expense_category->name]['amount'] += $line->amount;
            }
        }

        $incomesSummary = [];

        foreach ($groupedIncomes as $inc) {
            foreach ($inc as $line) {
                if (!isset($incomesSummary[$line->income_category->name])) {
                    $incomesSummary[$line->income_category->name] = [
                        'name'   => $line->income_category->name,
                        'amount' => 0,
                    ];
                }

                $incomesSummary[$line->income_category->name]['amount'] += $line->amount;
            }
        }

        return view('admin.expenseReports.index', compact(
            'expensesSummary',
            'incomesSummary',
            'expensesTotal',
            'incomesTotal',
            'profit',
            'monthlyBreakdown',
            'viewMode'
        ));
    }
}
