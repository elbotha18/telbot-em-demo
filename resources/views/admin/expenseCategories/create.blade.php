@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.create') }} {{ trans('cruds.expenseCategory.title_singular') }}
    </div>

    <div class="card-body">
        <form action="{{ route("admin.expense-categories.store") }}" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="created_by_id" value="{{ Auth::user()->id }}">
            <div class="form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                <label for="name">{{ trans('cruds.expenseCategory.fields.name') }}*</label>
                <input type="text" id="name" name="name" class="form-control" value="{{ old('name', isset($expenseCategory) ? $expenseCategory->name : '') }}" required>
                @if($errors->has('name'))
                    <em class="invalid-feedback">
                        {{ $errors->first('name') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('cruds.expenseCategory.fields.name_helper') }}
                </p>
            </div>
            <!-- add include in report checkbox -->
            <div class="form-group display-flex">
                <label for="include_in_report">Include in Report</label>
                <input type="checkbox" id="include_in_report" name="include_in_report" class="form-control" value="1">
            </div>
            <div>
                <input class="btn btn-danger" type="submit" value="{{ trans('global.save') }}">
            </div>
        </form>


    </div>
</div>
@endsection

<style>
    .invalid-feedback {
        display: block;
    }
    .display-flex {
        display: flex;
    }
    #include_in_report {
        width: 15px;
        height: 15px;
        margin: auto 10px;
    }
</style>