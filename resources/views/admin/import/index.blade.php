@extends('layouts.admin')
@section('content')
<div class="row">
  <div class="col-md-10">
    <h3 class="page-title">{{ trans('global.import') }}</h3>
  </div>
  <div class="col-md-2">
    <button type="button" class="btn btn-success" id="download-template">Download Template</button>
  </div>
</div>



<div class="card" style="margin-top: 50px">
    <div class="card-header">
        {{ trans('global.import') }}
    </div>

    <div class="card-body">
        <div class="row">
          <div class="col-md-12">
            <input type="file" class="form-control mb-3" id="import-file" accept=".csv,.xls,.xlsx">
            <button type="button" class="btn btn-primary float-right" id="upload-btn">Upload</button>
          </div>
          <div class="col-md-12">
            <div id="datatable">
              <!-- datatable inserted here -->
            </div>
            <button type="button" class="btn btn-success float-right" id="import-btn">Import</button>
            <button type="button" class="btn btn-primary float-right" id="add-row-btn">Add Row</button>
          </div>
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
    var income_categories = @json($income_categories);
    var expense_categories = @json($expense_categories);
    var type_options = [{id: 1, name: "Expense"}, {id: 2, name: "Income"}];

$(document).ready(function() {
    $('#download-template').click(function() {
        $.ajax({
        url: '{{ route("admin.import.template") }}',
        type: 'GET',
        success: function(data) {
            // On success, create a new blob and download the file
            var blob = new Blob([data], {type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'});
            var link = document.createElement('a');
            link.href = window.URL.createObjectURL(blob);
            link.download = 'import-template.csv';
            link.click();
        },
        error: function(xhr, status, error) {
            // Handle error
            console.log(error);
        }
        });
    });
});

$(document).ready(function() {
  
  // Click event listener for Upload button
  $('#upload-btn').click(function() {
    // Get the file
    var file = $('#import-file')[0].files[0];
    if (file == undefined) {
      swal.fire({
        title: 'Error',
        text: 'Please select a file to upload',
        icon: 'error',
        confirmButtonText: 'Ok'
      });
      return;
    }

    // Create a new form data object
    var formData = new FormData();
    formData.append('file', file);

    // Make the AJAX request
    $.ajax({
        url: '{{ route("admin.import.upload") }}',
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      },
      type: 'POST',
      data: formData,
      processData: false,
      contentType: false,
      success: function(data, status, xhr) {
        if (xhr.status === 200) {
            lines = data.data;
            // sweet alert data.message
            swal.fire({
                title: 'Success',
                text: data.message,
                icon: 'success',
                toast: true,
                position: 'top',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
            });

            // clear the datatable
            $('#datatable').empty();

            // Create the datatable element
            var datatable = document.createElement("table");
            datatable.classList.add("datatable");
            datatable.classList.add("table");
            datatable.classList.add("table-bordered");
            datatable.classList.add("table-striped");
            datatable.classList.add("table-hover");
            datatable.classList.add("dataTable");
            datatable.classList.add("no-footer");

            // Create the table header row
            var headerRow = datatable.insertRow();
            var headers = ["Date", "Amount", "Description", "Type", "Category", "Import"];
            for (var i = 0; i < headers.length; i++) {
                var headerCell = headerRow.insertCell();
                if (i == 5)
                {
                  // set cell display flex and min-width 40px
                  headerCell.style.display = 'flex';
                  headerCell.style.minWidth = '60px';
                  // create checkbox next to import
                    var input = $('<input>', {
                        class: 'form-check-input',
                        type: 'checkbox',
                        id: 'import-all',
                        checked: true,
                    });
                    // set checkbox to margin-left 2vw
                    input.css('margin-left', '50px');

                    // create label for checkbox
                    var label = $('<label>', {
                        class: 'form-check-label',
                        for: 'import-all',
                        text: 'Import'
                    });

                    // Add text and checkbox to the header cell
                    headerCell.appendChild(label[0]);
                    headerCell.appendChild(input[0]);
                }
                else {
                  headerCell.innerText = headers[i];
                }
            }

            // Create the table body rows
            for (var i = 0; i < lines.length; i++) {
                var line = lines[i];
                var row = datatable.insertRow();
                for (var j = 0; j < 5; j++) {
                    var cell = row.insertCell();
                    switch (j) {
                        case 0: // Date column
                          // Add a unique class to the date-input element
                          var date = new Date(line[headers[j]]);
                          var formattedDate = date.toISOString().slice(0,10);
                          var input = $('<input>', {
                              class: 'form-control date-input line-' + i,
                              'data-line': 'line-' + i,
                              type: 'date',
                              min: '2018-01-01',
                              max: '2030-12-31',
                              required: true,
                              value: formattedDate
                          });
                          cell.appendChild(input[0]);
                          break;
                        case 1: // Amount column
                          // Add a unique class to the amount-input element
                          var input = $('<input>', {
                              class: 'form-control amount-input line-' + i,
                              'data-line': 'line-' + i,
                              type: 'number',
                              step: '0.01',
                              min: '0',
                              required: true,
                              value: line[headers[j]]
                          });
                          cell.appendChild(input[0]);
                          break;
                        case 2: // Description column
                          // Add a unique class to the description-input element
                          var input = $('<input>', {
                              class: 'form-control description-input line-' + i,
                              'data-line': 'line-' + i,
                              type: 'text',
                              required: true,
                              value: line[headers[j]]
                          });
                          cell.appendChild(input[0]);
                          break;
                        case 3: // Type column
                          // Add a unique class to the type-select element
                          var select = $('<select>', {
                              class: 'form-select type-select line-' + i,
                              'data-line': 'line-' + i
                          });
                          $.each(type_options, function(index, type) {
                              select.append($('<option>', {
                              value: type.id,
                              text: type.name
                              }));
                          });
                          // Set the selected value based on the id of the type in the line object
                          select.val(line[headers[j]].id);
                          cell.appendChild(select[0]);
                          break;
                        case 4: // Category column
                          // Add a unique class to the category-select element
                          var select = $('<select>', {
                              class: 'form-select category-select line-' + i,
                              'data-line': 'line-' + i
                          });
                          let array = line[headers[3]].name === 'Expense' ? expense_categories : income_categories;
                          $.each(array, function(index, type) {
                              select.append($('<option>', {
                              value: type.id,
                              text: type.name
                              }));
                          });
                          // Set the selected value based on the id of the category in the line object
                          select.val(line[headers[j]].id);
                          cell.appendChild(select[0]);
                          break;
                    }
                }
                // Add checkbox cell
                var checkboxCell = row.insertCell();
                checkboxCell.style.display = 'flex';
                checkboxCell.style.justifyContent = 'center';
                var checkbox = document.createElement('input');
                checkbox.type = 'checkbox';
                checkbox.name = 'checkbox[]';
                checkbox.setAttribute('checked', true);
                // align checkbox to center
                checkbox.style.display = 'flex';
                checkboxCell.appendChild(checkbox);
            }

            var datatableContainer = document.getElementById("datatable");
            datatableContainer.appendChild(datatable);
            // show datatable
            datatableContainer.style.display = "block";
            // show add row button
            document.getElementById("add-row-btn").style.display = "block";
            // show import button
            document.getElementById("import-btn").style.display = "block";

            // Get the type and category selects
            var typeSelects = document.querySelectorAll('.type-select');
            var categorySelects = document.querySelectorAll('.category-select');

            // Set up the event listener for each type select
            typeSelects.forEach(function(select) {
                select.addEventListener('change', function() {
                    // Get the selected type
                    var selectedType = this.value;
                    
                    // Find the corresponding category select and update its options
                    var categorySelect = this.closest('tr').querySelector('.category-select');
                    updateCategoryOptions(categorySelect, selectedType);
                });
            });
        } else {
            // On success, reload the page
            swal.fire({
                title: 'Failed',
                text: 'Failed to import',
                icon: 'danger',
            });
        }
      },
      error: function(xhr, status, error) {
        // Handle error
        console.log(error);
      }
    });
  });
});

$(document).on('change', '#import-all', function() {
    var isChecked = $(this).is(':checked');
    var checkboxes = $('input[type="checkbox"]');
    checkboxes.prop('checked', isChecked);
});

// Function to update the options in the category select based on the selected type
function updateCategoryOptions(select, selectedType) {
  // Clear the existing options
  select.innerHTML = '';
  
  // Add the new options based on the selected type
  if (selectedType == 1) {
    expense_categories.forEach(function(category) {
      var option = document.createElement('option');
      option.value = category.id;
      option.innerText = category.name;
      select.appendChild(option);
    });    
  } else if (selectedType == 2) {
    income_categories.forEach(function(category) {
      var option = document.createElement('option');
      option.value = category.id;
      option.innerText = category.name;
      select.appendChild(option);
    });    
  }
}

// add row
const addRowButton = document.getElementById('add-row-btn');
addRowButton.addEventListener('click', function() {
  const table = document.getElementById('datatable').querySelector('table');
  const rowCount = table.rows.length;
  const row = table.insertRow(rowCount);

  row.insertCell(-1).innerHTML = `
    <input type="date" name="txtbox[]" class="form-control" min="2018-01-01" max="2030-12-31" required>
  `;

  row.insertCell(-1).innerHTML = `
    <input type="number" name="txtbox[]" class="form-control amount-input line-${rowCount}" data-line="line-${rowCount}" step="0.01" min="0" required>
  `;

  row.insertCell(-1).innerHTML = `
    <input type="text" name="txtbox[]" class="form-control description-input line-${rowCount}" data-line="line-${rowCount}" required>
  `;

  const typeSelect = document.createElement("select");
  typeSelect.name = "txtbox[]";
  typeSelect.className = "form-select type-select line-" + rowCount;
  type_options.forEach(option => {
    const optionEl = document.createElement("option");
    optionEl.value = option.id;
    optionEl.text = option.name;
    typeSelect.appendChild(optionEl);
  });

  typeSelect.addEventListener('change', function() {
    const selectedType = this.value;
    const categorySelect = this.closest('tr').querySelector('.category-select');
    updateCategoryOptions(categorySelect, selectedType);
  });

  row.insertCell(-1).appendChild(typeSelect);

  const categorySelect = document.createElement("select");
  categorySelect.name = "txtbox[]";
  categorySelect.className = "form-select category-select line-" + rowCount;
  expense_categories.forEach(category => {
    const optionEl = document.createElement("option");
    optionEl.value = category.id;
    optionEl.text = category.name;
    categorySelect.appendChild(optionEl);
  });

  row.insertCell(-1).appendChild(categorySelect);

  const cell6 = row.insertCell(-1);
  cell6.style.display = "flex";
  cell6.style.justifyContent = "center";

  const checkbox = document.createElement("input");
  checkbox.type = "checkbox";
  checkbox.name = "chkbox[]";
  checkbox.className = "form-check-input";
  checkbox.checked = true;
  checkbox.style.marginLeft = "3px";

  cell6.appendChild(checkbox);
});

// Get the "Import" button and add an event listener
var importButton = document.getElementById('import-btn');
importButton.addEventListener('click', function() {
  var rows = document.querySelectorAll('#datatable tbody tr');
  var dataToImport = [];
  var headers = ["date", "amount", "description", "type", "category"];
  for (var i = 1; i < rows.length; i++) {
    var row = rows[i];
    var checkbox = row.querySelector('input[type="checkbox"]');
    if (checkbox.checked) {
      var cells = row.querySelectorAll('td');
      var rowData = {};
      for (var j = 0; j < cells.length - 1; j++) {
        if (j === 3 || j === 4) {
          rowData[headers[j]] = cells[j].querySelector('select').value;
        } else {
          rowData[headers[j]] = cells[j].querySelector('input').value;
        }
      }
      dataToImport.push(rowData);
    }
  }
  // Check if there is data to import
  if (dataToImport.length > 0) {
    // Send the data to the API using an AJAX request
    $.ajax({
      url: '{{ route("admin.store.import") }}',
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      },
      type: 'POST',
      data: JSON.stringify(dataToImport),
      processData: false,
      contentType: 'application/json',
      success: function(data, status, xhr) {
        if (xhr.status === 200) {
          // On success, reload the page
          swal.fire({
            title: 'Success',
            text: data.message,
            icon: 'success',
            toast: true,
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
          }).then(function() {
            // redirect to expenses index
            location.href = '{{ route("admin.expenses.index") }}';
          });
        } else {
          // On success, reload the page
          swal.fire({
            title: 'Failed',
            text: 'Failed to import',
            icon: 'error',
          });
        }
      },
      error: function(xhr, status, error) {
        // Handle error
        console.log(error);
      }
    });
  } else {
    swal.fire({
      title: 'Failed',
      text: 'No data to import',
      icon: 'error',
    });
  }
});

</script>

<style>
    .btn-close {
        background-color: transparent;
        border: none;
        padding: 0;
        font-size: 1.5rem;
        font-weight: 700;
        line-height: 1;
        color: #000;
        text-shadow: 0 1px 0 #fff;
        opacity: .5;
    }
    #datatable {
        display: none;
        padding: 10px;
    }
    #add-row-btn {
        display: none;
    }
    #import-btn {
        display: none;
        margin: 0 10px;
    }
    table tr:first-child {
      font-weight: 600;
    }
    .category-select {
      width: 90%;
    }
</style>
@endsection