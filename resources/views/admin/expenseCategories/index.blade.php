@extends('layouts.admin')
@section('content')
@can('expense_category_create')
    <div style="margin-bottom: 10px;" class="row">
        <div class="col-lg-12">
            <a class="btn btn-success" href="{{ route("admin.expense-categories.create") }}">
                {{ trans('global.add') }} {{ trans('cruds.expenseCategory.title_singular') }}
            </a>
            <div class="btn-group" role="group" aria-label="View mode">
                <a href="{{ route('admin.expense-categories.index', ['view_mode' => 'personal']) }}" class="btn btn-secondary{{ $viewMode == 'personal' ? ' active' : '' }}">Personal</a>
                <a href="{{ route('admin.expense-categories.index', ['view_mode' => 'entity']) }}" class="btn btn-secondary{{ $viewMode == 'entity' ? ' active' : '' }}">Entity</a>
            </div>
        </div>
    </div>
@endcan
<div class="card">
    <div class="card-header">
        {{ trans('cruds.expenseCategory.title_singular') }} {{ trans('global.list') }}
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class=" table table-bordered table-striped table-hover datatable datatable-ExpenseCategory">
                <thead>
                    <tr>
                        <th width="10">

                        </th>
                        <th>
                            {{ trans('cruds.expenseCategory.fields.id') }}
                        </th>
                        <th>
                            {{ trans('cruds.expenseCategory.fields.name') }}
                        </th>
                        <th>
                            Include in Report
                        <th>
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($expenseCategories as $key => $expenseCategory)
                        <tr data-entry-id="{{ $expenseCategory->id }}">
                            <td>

                            </td>
                            <td>
                                {{ $expenseCategory->id ?? '' }}
                            </td>
                            <td>
                                {{ $expenseCategory->name ?? '' }}
                            </td>
                            <td style="display:flex; justify-content: center">
                                {{ $expenseCategory->include_in_report ? 'Yes' : 'No'}}
                            </td>
                            <td>
                                @can('expense_category_show')
                                    <a class="btn btn-xs btn-primary" href="{{ route('admin.expense-categories.show', $expenseCategory->id) }}">
                                        {{ trans('global.view') }}
                                    </a>
                                @endcan

                                @can('expense_category_edit')
                                    <a class="btn btn-xs btn-info" href="{{ route('admin.expense-categories.edit', $expenseCategory->id) }}">
                                        {{ trans('global.edit') }}
                                    </a>
                                @endcan

                                @can('expense_category_delete')
                                    <form action="{{ route('admin.expense-categories.destroy', $expenseCategory->id) }}" method="POST" onsubmit="return confirm('{{ trans('global.areYouSure') }}');" style="display: inline-block;">
                                        <input type="hidden" name="_method" value="DELETE">
                                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                        <input type="submit" class="btn btn-xs btn-danger" value="{{ trans('global.delete') }}">
                                    </form>
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
<script>
    $(function () {
  let dtButtons = $.extend(true, [], $.fn.dataTable.defaults.buttons)
@can('expense_category_delete')
  let deleteButtonTrans = '{{ trans('global.datatables.delete') }}'
  let deleteButton = {
    text: deleteButtonTrans,
    url: "{{ route('admin.expense-categories.massDestroy') }}",
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
  $('.datatable-ExpenseCategory:not(.ajaxTable)').DataTable({ buttons: dtButtons })
    $('a[data-toggle="tab"]').on('shown.bs.tab', function(e){
        $($.fn.dataTable.tables(true)).DataTable()
            .columns.adjust();
    });
})

</script>
@endsection