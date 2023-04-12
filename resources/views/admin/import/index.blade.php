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
                headerCell.innerText = headers[i];
            }
            var type_options = [{id: 1, name: "Expense"}, {id: 2, name: "Income"}]; // Add your options here

            // Create the table body rows
            for (var i = 0; i < lines.length; i++) {
                var line = lines[i];
                var row = datatable.insertRow();
                for (var j = 0; j < 5; j++) {
                    var cell = row.insertCell();
                    if (j === 3) { // Type column
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
                    } else if (j === 4) { // Category column
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
                        console.log(line[headers[j]].id);
                        select.val(line[headers[j]].id);
                        cell.appendChild(select[0]);
                    } else {
                        cell.innerText = line[headers[j]];
                    }
                }
                // Add checkbox cell
                var checkboxCell = row.insertCell();
                var checkbox = document.createElement('input');
                checkbox.type = 'checkbox';
                checkbox.name = 'checkbox[]';
                checkbox.value = 'false';
                checkboxCell.appendChild(checkbox);
            }

            var datatableContainer = document.getElementById("datatable");
            datatableContainer.appendChild(datatable);
            // show datatable
            datatableContainer.style.display = "block";
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

// Function to update the options in the category select based on the selected type
function updateCategoryOptions(select, selectedType) {
  // Clear the existing options
  select.innerHTML = '';
  console.log(select);
  console.log(selectedType);
  
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
          rowData[headers[j]] = cells[j].textContent.trim();
        }
      }
      dataToImport.push(rowData);
    }
  }
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
            location.reload();
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
    #import-btn {
        display: none;
    }
    table tr:first-child {
      font-weight: 600;
    }
    .category-select {
      width: 90%;
    }
</style>
@endsection