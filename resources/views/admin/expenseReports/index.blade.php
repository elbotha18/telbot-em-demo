@extends('layouts.admin')
@section('content')
<div class="row">
    <div class="col">
        <h3 class="page-title">{{ trans('cruds.expenseReport.reports.title') }}</h3>

        <form method="get">
            <div class="row">
                <div class="col-2 form-group">
                    <label class="control-label" for="y">{{ trans('global.year') }}</label>
                    <select name="y" id="y" class="form-control">
                        @foreach(array_combine(range(date("Y"), 2022), range(date("Y"), 2022)) as $year)
                            <option value="{{ $year }}" @if($year===old('y', Request::get('y', date('Y')))) selected @endif>
                                {{ $year }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-3 form-group">
                    <label class="control-label" for="m">{{ trans('global.month') }}</label>
                    <select name="m" for="m" class="form-control">
                        <option value="all">All</option>
                        @foreach(cal_info(0)['months'] as $month)
                            <option value="{{ $month }}" @if($month===old('m', Request::get('m', date('m')))) selected @endif>
                                {{ $month }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-3 form-group">
                    <label class="control-label" for="viewMode">View Mode</label>
                    <select name="viewMode" for="viewMode" class="form-control">
                        <option value="personal" @if(Request::get('viewMode', 'personal') === 'personal') selected @endif>Personal</option>
                        <option value="entity" @if(Request::get('viewMode') === 'entity') selected @endif>Entity</option>
                    </select>
                </div>
                <div class="col-3">
                    <label class="control-label">&nbsp;</label><br>
                    <button class="btn btn-primary" type="submit">{{ trans('global.filterDate') }}</button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">
        {{ trans('cruds.expenseReport.reports.incomeReport') }}
    </div>

    <div class="card-body">
        <div class="row">
            <div class="col">
                <table class="table table-bordered table-striped">
                    <tr>
                        <th>{{ trans('cruds.expenseReport.reports.income') }}</th>
                        <td>{{ number_format($incomesTotal, 2) }}</td>
                    </tr>
                    <tr>
                        <th>{{ trans('cruds.expenseReport.reports.expense') }}</th>
                        <td>{{ number_format($expensesTotal, 2) }}</td>
                    </tr>
                    <tr>
                        <th>{{ trans('cruds.expenseReport.reports.profit') }}</th>
                        <td>{{ number_format($profit, 2) }}</td>
                    </tr>
                </table>
            </div>
            <div class="col">
                <table class="table table-bordered table-striped">
                    <tr>
                        <th>{{ trans('cruds.expenseReport.reports.incomeByCategory') }}</th>
                        <th>{{ number_format($incomesTotal, 2) }}</th>
                    </tr>
                    @foreach($incomesSummary as $inc)
                        <tr>
                            <th>{{ $inc['name'] }}</th>
                            <td>{{ number_format($inc['amount'], 2) }}</td>
                        </tr>
                    @endforeach
                </table>
            </div>
            <div class="col">
                <table class="table table-bordered table-striped">
                    <tr>
                        <th>{{ trans('cruds.expenseReport.reports.expenseByCategory') }}</th>
                        <th>{{ number_format($expensesTotal, 2) }}</th>
                    </tr>
                    @foreach($expensesSummary as $exp)
                        <tr>
                            <th>{{ $exp['name'] }}</th>
                            <td>{{ number_format($exp['amount'], 2) }}</td>
                        </tr>
                    @endforeach
                </table>
            </div>
        </div>

        @if ($expensesTotal > 0 && $monthlyBreakdown) 
            <div class="row">
                <div class="col">
                    <h4>Monthly Breakdown</h4>
                    <canvas class="chart-container" id="monthlyBreakdownChart"></canvas>
                </div>
            </div>
        @endif

        <div class="row">
            @if ($incomesTotal > 0)
            <div class="col">
                <h4>Income/Expense</h4>
                <canvas class="chart-container" id="incomeExpenseChart"></canvas>
            </div>
            @endif

            @if ($expensesTotal > 0)
            <div class="col">
                <h4>Expense Breakdown</h4>
                <canvas class="chart-container" id="expeseBreakdownChart"></canvas>
            </div>
            @endif
        </div>
        @if ($expensesTotal > 0)
        <div class="row">
            <div class="col">
                <h4>Expense Breakdown</h4>
                <canvas class="chart-container" id="expeseBreakdownBars"></canvas>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection

@section('scripts')
@parent
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns/dist/chartjs-adapter-date-fns.bundle.min.js"></script>

<script>
    const incomesTotal = @json($incomesTotal);
    const expensesTotal = @json($expensesTotal);
    const incomesSummary = @json($incomesSummary);
    const expensesSummary = @json($expensesSummary);
    const monthlyBreakdown = @json($monthlyBreakdown);
    const expensesNames = Object.keys(expensesSummary);
    const expensesAmounts = Object.values(expensesSummary).map(expense => expense.amount);
    const backgroundColors = [
        '#FF6384', // pink
        '#36A2EB', // light blue
        '#FFCE56', // yellow
        '#4BC0C0', // turquoise
        '#9966FF', // purple
        '#FF9F40', // orange
        '#28A745', // green
        '#FF66CC', // light pink
        '#00BFFF', // sky blue
        '#FFD700', // gold
    ];
    
    if (incomesTotal > 0 || expensesTotal > 0) 
    {
        // Get the canvas element and create a new Chart object
        var ctx = document.getElementById('incomeExpenseChart').getContext('2d');
        var data = {
            labels: ["Income", "Expense"],
            datasets: [
                {
                data: [incomesTotal, expensesTotal],
                backgroundColor: [
                    "#FF6384",
                    "#36A2EB"
                ]
                }
            ]
        };
        const incomeSpentPieChart = new Chart(ctx, {
            type: 'doughnut',
            data: data,
            options: {
                title: {
                    display: true,
                    text: 'Percentage of Income Spent'
                },
                tooltips: {
                    callbacks: {
                        label: function(tooltipItem, data) {
                        var dataset = data.datasets[tooltipItem.datasetIndex];
                        var total = dataset.data.reduce(function(previousValue, currentValue, currentIndex, array) {
                            return previousValue + currentValue;
                        });
                        var currentValue = dataset.data[tooltipItem.index];
                        var percentage = Math.floor(((currentValue/total) * 100)+0.5);
                        return percentage + "%";
                        }
                    }
                }
            }
        });
    }
    
    if (expensesTotal > 0)
    {
        // Get the canvas element and create a new Chart object
        var ctx = document.getElementById('expeseBreakdownChart').getContext('2d');
        const expensePieChart = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: expensesNames,
                datasets: [{
                    data: expensesAmounts,
                    backgroundColor: backgroundColors
                }]
            },
        });
    }
    
    if (expensesTotal > 0)
    {
        // Get the canvas element and create a new Chart object
        var ctx = document.getElementById('expeseBreakdownBars').getContext('2d');
        const expenseBarChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: expensesNames,
                datasets: [{
                    label: 'Expense Categories',
                    data: expensesAmounts,
                    backgroundColor: backgroundColors
                }]
            },
        });
    }

    if (expensesTotal > 0 && monthlyBreakdown)
    {
        var months = [];
        var monthNames = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
        var categories = [];


        // Loop through monthlyBreakdown and add name to each category
        for (var i = 0; i < monthlyBreakdown.length; i++) {
            var categoryId = monthlyBreakdown[i].expense_category_id;
            
            // If the category is not in the categories object, add it
            if (categoryId && !categories[categoryId]) {
                var selectedSummary = Object.values(expensesSummary).find(function(summary) {
                    return summary.id === categoryId;
                });
                if (selectedSummary !== undefined) {
                    categories[categoryId] = {
                        id: categoryId,
                        name: selectedSummary.name,
                    };
                }
            }
        }
        categories.shift();
        categories = categories.filter(function(){return true;});

        // Create an array of unique months
        for (var i = 0; i < monthlyBreakdown.length; i++) {
            var monthNumber = monthlyBreakdown[i].month;
            var monthName = monthNames[monthNumber - 1];
            var label = monthName;
            if (!months.some(month => month.label === label)) {
                months.push({index: monthNumber, label: label});
            }
        }

        // Create a data object in the format required by Chart.js
        var chartData = {
            labels: months.map(month => month.label),
            datasets: []
        };

        // Add a dataset for each category
        for (var i = 0; i < categories.length; i++) {
            var categoryData = [];
            for (var j = 0; j < months.length; j++) {
                var total = 0;
                for (var k = 0; k < monthlyBreakdown.length; k++) {
                    if (monthlyBreakdown[k].expense_category_id == categories[i].id && monthlyBreakdown[k].month == months[j]['index']) {
                        total = monthlyBreakdown[k].total;
                    }
                }
                categoryData.push(total);
            }

            chartData.datasets.push({
                label: categories[i].name,
                data: categoryData,
                backgroundColor: backgroundColors[i] ?? '#FF6384',
                borderColor: backgroundColors[i] ?? '#FF6384',
            });
        }

        // Create the chart
        var ctx = document.getElementById('monthlyBreakdownChart').getContext('2d');
        var chart = new Chart(ctx, {
            type: 'line',
            data: chartData,
            options: {
                responsive: true,
                title: {
                    display: true,
                    text: 'Monthly Expense Breakdown'
                },
                tooltips: {
                    mode: 'index',
                    intersect: false
                },
                hover: {
                    mode: 'nearest',
                    intersect: true
                },
            }
        });
    }
</script>

<style>
    .chart-container {
        position: relative;
        margin: auto;
        height: 80vh;
        width: 80vw;
        max-width: 1000px;
        max-height: 500px;
    }
    h4 {
        text-align: center;
    }
</style>


@stop