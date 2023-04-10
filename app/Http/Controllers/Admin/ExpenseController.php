<?php

namespace App\Http\Controllers\Admin;

use Auth;
use Gate;
use DateTime;
use App\Expense;
use App\Income;
use App\ExpenseCategory;
use App\IncomeCategory;
use Illuminate\Http\Request;
use Smalot\PdfParser\Parser;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreExpenseRequest;
use App\Http\Requests\UpdateExpenseRequest;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Requests\MassDestroyExpenseRequest;

class ExpenseController extends Controller
{
    public function index()
    {
        abort_if(Gate::denies('expense_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $viewMode = request('view_mode') ?? 'personal';
        if ($viewMode == 'personal') {
            $expenses = Expense::where('created_by_id', Auth::id())->get();
        } else {
            $expenses = Expense::all();
        }

        return view('admin.expenses.index', compact('expenses', 'viewMode'));
    }

    public function create()
    {
        abort_if(Gate::denies('expense_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $expense_categories = ExpenseCategory::where('created_by_id', Auth::id())->pluck('name', 'id')->prepend(trans('global.pleaseSelect'), '');;

        return view('admin.expenses.create', compact('expense_categories'));
    }

    public function store(StoreExpenseRequest $request)
    {
        $expense = Expense::create($request->all());

        return redirect()->route('admin.expenses.index');
    }

    public function edit($id)
    {
        abort_if(Gate::denies('expense_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $expense = Expense::findOrFail($id);

        $expense_categories = ExpenseCategory::where('created_by_id', Auth::id())->pluck('name', 'id')->prepend(trans('global.pleaseSelect'), '');

        $expense->load('expense_category', 'created_by');

        return view('admin.expenses.edit', compact('expense_categories', 'expense'));
    }

    public function update(UpdateExpenseRequest $request, $id)
    {
        $expense = Expense::findOrFail($id);
        $expense->update($request->all());

        return redirect()->route('admin.expenses.index');
    }

    public function show($id)
    {
        abort_if(Gate::denies('expense_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $expense = Expense::findOrFail($id);
        $expense->load('expense_category', 'created_by');

        return view('admin.expenses.show', compact('expense'));
    }

    public function destroy($id)
    {
        abort_if(Gate::denies('expense_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $expense = Expense::findOrFail($id);
        $expense->delete();

        return back();
    }

    public function massDestroy(MassDestroyExpenseRequest $request)
    {
        Expense::whereIn('id', request('ids'))->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }
    
}
