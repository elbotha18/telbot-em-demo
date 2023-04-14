<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\MassDestroyIncomeRequest;
use App\Http\Requests\StoreIncomeRequest;
use App\Http\Requests\UpdateIncomeRequest;
use App\Income;
use App\IncomeCategory;
use Gate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Auth;

class IncomeController extends Controller
{
    public function index()
    {
        abort_if(Gate::denies('income_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $viewMode = request('view_mode') ?? 'personal';
        if ($viewMode == 'personal') {
            $incomes = Income::where('created_by_id', Auth::id())->get();
        } else {
            $incomes = Income::all();
        }

        return view('admin.incomes.index', compact('incomes', 'viewMode'));
    }

    public function create()
    {
        abort_if(Gate::denies('income_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $income_categories = IncomeCategory::where('created_by_id', Auth::id())->pluck('name', 'id')->prepend(trans('global.pleaseSelect'), '');

        return view('admin.incomes.create', compact('income_categories'));
    }

    public function store(StoreIncomeRequest $request)
    {
        $income = Income::create($request->all());

        return redirect()->route('admin.incomes.index');
    }

    public function edit($id)
    {
        abort_if(Gate::denies('income_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $income = Income::findOrFail($id);
        
        $income_categories = IncomeCategory::pluck('name', 'id')->prepend(trans('global.pleaseSelect'), '');

        $income->load('income_category', 'created_by');

        return view('admin.incomes.edit', compact('income_categories', 'income'));
    }

    public function update(UpdateIncomeRequest $request, $id)
    {
        $income = Income::findOrFail($id);
        $income->update($request->all());

        return redirect()->route('admin.incomes.index');
    }

    public function show($id)
    {
        abort_if(Gate::denies('income_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $income = Income::findOrFail($id);
        $income->load('income_category', 'created_by');

        return view('admin.incomes.show', compact('income'));
    }

    public function destroy($id)
    {
        abort_if(Gate::denies('income_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $income = Income::findOrFail($id);
        $income->delete();

        return back();
    }

    public function massDestroy(MassDestroyIncomeRequest $request)
    {
        Income::whereIn('id', request('ids'))->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }
}
