<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\MassDestroyIncomeCategoryRequest;
use App\Http\Requests\StoreIncomeCategoryRequest;
use App\Http\Requests\UpdateIncomeCategoryRequest;
use App\IncomeCategory;
use Gate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Auth;

class IncomeCategoryController extends Controller
{
    public function index()
    {
        abort_if(Gate::denies('income_category_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $viewMode = request('view_mode') ?? 'personal';
        if ($viewMode == 'personal') {
            $incomeCategories = IncomeCategory::where('created_by_id', Auth::id())->get();
        } else {
            $incomeCategories = IncomeCategory::all();
        }

        return view('admin.incomeCategories.index', compact('incomeCategories', 'viewMode'));
    }

    public function create()
    {
        abort_if(Gate::denies('income_category_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.incomeCategories.create');
    }

    public function store(StoreIncomeCategoryRequest $request)
    {
        $incomeCategory = IncomeCategory::create($request->all());

        return redirect()->route('admin.income-categories.index');
    }

    public function edit($id)
    {
        abort_if(Gate::denies('income_category_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $incomeCategory = IncomeCategory::findOrFail($id);
        $incomeCategory->load('created_by');

        return view('admin.incomeCategories.edit', compact('incomeCategory'));
    }

    public function update(UpdateIncomeCategoryRequest $request,$id)
    {
        $incomeCategory = IncomeCategory::findOrFail($id);
        $incomeCategory->update($request->all());

        return redirect()->route('admin.income-categories.index');
    }

    public function show($id)
    {
        abort_if(Gate::denies('income_category_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $incomeCategory = IncomeCategory::findOrFail($id);
        $incomeCategory->load('created_by');

        return view('admin.incomeCategories.show', compact('incomeCategory'));
    }

    public function destroy($id)
    {
        abort_if(Gate::denies('income_category_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $incomeCategory = IncomeCategory::findOrFail($id);
        $incomeCategory->delete();

        return back();
    }

    public function massDestroy(MassDestroyIncomeCategoryRequest $request)
    {
        IncomeCategory::whereIn('id', request('ids'))->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }
}
