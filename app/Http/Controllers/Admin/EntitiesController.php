<?php

namespace App\Http\Controllers\Admin;

use Gate;
use App\Entity;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEntityRequest;
use App\Http\Requests\UpdateEntityRequest;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Requests\MassDestroyEntityRequest;

class EntitiesController extends Controller
{
    public function index()
    {
        abort_if(Gate::denies('entity_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $entities = Entity::all();

        return view('admin.entities.index', compact('entities'));
    }

    public function create()
    {
        abort_if(Gate::denies('entity_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.entities.create');
    }

    public function store(StoreEntityRequest $request)
    {
        $entity = Entity::create($request->all());

        return redirect()->route('admin.entities.index');
    }

    public function edit(Entity $entity)
    {
        abort_if(Gate::denies('entity_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.entities.edit', compact('entity'));
    }

    public function update(UpdateEntityRequest $request, Entity $entity)
    {
        $entity->update($request->all());

        return redirect()->route('admin.entities.index');
    }

    public function show(Entity $entity)
    {
        abort_if(Gate::denies('entity_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.entities.show', compact('entity'));
    }

    public function destroy(Entity $entity)
    {
        abort_if(Gate::denies('entity_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $entity->delete();

        return back();
    }

    public function massDestroy(MassDestroyExpenseRequest $request)
    {
        Expense::whereIn('id', request('ids'))->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }
}
