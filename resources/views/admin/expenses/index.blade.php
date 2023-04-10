@extends('layouts.admin')
@section('content')
@can('expense_create')
    <div style="margin-bottom: 10px;" class="row">
        <div class="col-lg-12">
            <a class="btn btn-success" href="{{ route("admin.expenses.create") }}">
                {{ trans('global.add') }} {{ trans('cruds.expense.title_singular') }}
            </a>
            <div class="btn-group" role="group" aria-label="View mode">
                <a href="{{ route('admin.expenses.index', ['view_mode' => 'personal']) }}" class="btn btn-secondary{{ $viewMode == 'personal' ? ' active' : '' }}">Personal</a>
                <a href="{{ route('admin.expenses.index', ['view_mode' => 'entity']) }}" class="btn btn-secondary{{ $viewMode == 'entity' ? ' active' : '' }}">Entity</a>
            </div>
        </div>
    </div>
@endcan
<div class="card">
    <div class="card-header">
        {{ trans('cruds.expense.title_singular') }} {{ trans('global.list') }}
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class=" table table-bordered table-striped table-hover datatable datatable-Expense">
                <thead>
                    <tr>
                        <th width="10">

                        </th>
                        <th>
                            {{ trans('cruds.expense.fields.id') }}
                        </th>
                        <th>
                            {{ trans('cruds.expense.fields.expense_category') }}
                        </th>
                        <th>
                            {{ trans('cruds.expense.fields.entry_date') }}
                        </th>
                        <th>
                            {{ trans('cruds.expense.fields.amount') }}
                        </th>
                        <th>
                            {{ trans('cruds.expense.fields.description') }}
                        </th>
                        <th>
                            &nbsp;
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($expenses as $key => $expense)
                        <tr data-entry-id="{{ $expense->id }}">
                            <td>

                            </td>
                            <td>
                                {{ $expense->id ?? '' }}
                            </td>
                            <td>
                                {{ $expense->expense_category->name ?? '' }}
                            </td>
                            <td>
                                {{ $expense->entry_date ?? '' }}
                            </td>
                            <td>
                                {{ $expense->amount ?? '' }}
                            </td>
                            <td>
                                {{ $expense->description ?? '' }}
                            </td>
                            <td>
                                @can('expense_show')
                                    <a class="btn btn-xs btn-primary" href="{{ route('admin.expenses.show', $expense->id) }}">
                                        {{ trans('global.view') }}
                                    </a>
                                @endcan

                                @can('expense_edit')
                                    @if ($expense->created_by_id == Auth::user()->id)
                                        <a class="btn btn-xs btn-info" href="{{ route('admin.expenses.edit', $expense->id) }}">
                                            {{ trans('global.edit') }}
                                        </a>
                                    @else 
                                        <button class="btn btn-xs btn-info" disabled>
                                            {{ trans('global.edit')}}
                                        </button>
                                    @endif
                                @endcan

                                @can('expense_delete')
                                    @if ($expense->created_by_id == Auth::user()->id)
                                        <form action="{{ route('admin.expenses.destroy', $expense->id) }}" method="POST" onsubmit="return confirm('{{ trans('global.areYouSure') }}');" style="display: inline-block;">
                                            <input type="hidden" name="_method" value="DELETE">
                                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                            <input type="submit" class="btn btn-xs btn-danger" value="{{ trans('global.delete') }}">
                                        </form>
                                    @else 
                                        <button class="btn btn-xs btn-danger" disabled>
                                            {{ trans('global.delete')}}
                                        </button>
                                    @endif
                                @endcan

                            </td>

                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection
@section('scripts')
@parent
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<meta name="csrf-token" content="{{ csrf_token() }}">

<script>
    var lines = [];
    var income_category = [];
    var expense_category = [];

    $(function () {
  let dtButtons = $.extend(true, [], $.fn.dataTable.defaults.buttons)
@can('expense_delete')
  let deleteButtonTrans = '{{ trans('global.datatables.delete') }}'
  let deleteButton = {
    text: deleteButtonTrans,
    url: "{{ route('admin.expenses.massDestroy') }}",
    className: 'btn-danger',
    action: function (e, dt, node, config) {
      var ids = $.map(dt.rows({ selected: true }).nodes(), function (entry) {
          return $(entry).data('entry-id')
      });

      if (ids.length === 0) {
        alert('{{ trans('global.datatables.zero_selected') }}')

        return
      }

      if (confirm('{{ trans('global.areYouSure') }}')) {
        $.ajax({
          headers: {'x-csrf-token': _token},
          method: 'POST',
          url: config.url,
          data: { ids: ids, _method: 'DELETE' }})
          .done(function () { location.reload() })
      }
    }
  }
  dtButtons.push(deleteButton)
@endcan

$.extend(true, $.fn.dataTable.defaults, {
    order: [[ 1, 'desc' ]],
    pageLength: 100,
});

$('.datatable-Expense:not(.ajaxTable)').DataTable({ buttons: dtButtons })

$('a[data-toggle="tab"]').on('shown.bs.tab', function(e){
    $($.fn.dataTable.tables(true)).DataTable().columns.adjust();});
})

</script>
@endsection