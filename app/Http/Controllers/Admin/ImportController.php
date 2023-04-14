<?php

namespace App\Http\Controllers\Admin;

use Auth;
use Gate;
use App\Income;
use App\Expense;
use App\ExpenseCategory;
use App\IncomeCategory;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Symfony\Component\HttpFoundation\Response;

class ImportController extends Controller
{
    public function index()
    {
        abort_if(Gate::denies('import_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        
        $user = Auth::user();
        $income_categories = IncomeCategory::get(['id', 'name']);
        $expense_categories = ExpenseCategory::get(['id', 'name']);

        return view('admin.import.index', compact('income_categories', 'expense_categories'));
    }

    public function downloadTemplate()
    {
        $user = Auth::user();
        $income_categories = IncomeCategory::where('created_by_id', $user->id)->pluck('name');
        $expense_categories = ExpenseCategory::where('created_by_id', $user->id)->pluck('name');

        $sample = array();
        $today = date('Y-m-d');
        for ($i = 0; $i < count($income_categories); $i++)
        {
            $sample[] = array(
                'entry_date' => $today,
                'description' => $income_categories[$i].' Description',
                'type' => 'Income',
                'category' => $income_categories[$i],
                'amount' => "$i.99"
            );
        }
        for ($i = 0; $i < count($expense_categories); $i++)
        {
            $sample[] = array(
                'entry_date' => $today,
                'description' => $expense_categories[$i].' Description',
                'type' => 'Expense',
                'category' => $expense_categories[$i],
                'amount' => "-$i.99"
            );
        }
        if (count($sample) == 0)
        {
            $sample[] = array(
                'entry_date' => $today,
                'description' => 'Uncategorized Description',
                'type' => 'Income/Expense',
                'category' => 'Uncategorized',
                'amount' => -1.99
            );
        }

        $filename = 'expense_template.csv';
        $handle = fopen($filename, 'w+');
        fputcsv($handle, array('entry_date', 'description', 'type', 'category', 'amount'));

        foreach($sample as $row) {
            fputcsv($handle, array($row['entry_date'], $row['description'], $row['type'], $row['category'], $row['amount']));
        }

        fclose($handle);

        $headers = array(
            'Content-Type' => 'text/csv',
        );

        return response()->download($filename, 'import_template.csv', $headers);
    }

    public function uploadImport(Request $request)
    {
        if (!request()->hasFile('file'))
        {
            return redirect()->back()->with('error', 'Please select a file to import');
        }
        $user = Auth::user();
        $income_categories = IncomeCategory::get(['id', 'name']);
        $expense_categories = ExpenseCategory::get(['id', 'name']);

        $file = $request->file('file');
        $extension = $file->getClientOriginalExtension();

        $valid_extension = array('csv', 'xlsx', 'xls');
        $data = array();

        if(in_array(strtolower($extension),$valid_extension))
        {
            $csvData = file_get_contents($file->path());

            $rows = explode("\n", $csvData);

            $importData_arr = [];

            foreach ($rows as $row) {
                $importData_arr[] = str_getcsv($row);
            }
            // remove header line
            array_shift($importData_arr);

            for ($i = 0; $i < count($importData_arr); $i++)
            {
                $type = $this->getType($importData_arr[$i][2], $importData_arr[$i][1]);
                $data[$i] = array(
                    'Date' => $importData_arr[$i][0],
                    'Amount' => $this->getAmount($importData_arr[$i][4]),
                    'Description' => $importData_arr[$i][1],
                    'Type' => $type,
                    'Category' => $this->getCategory($importData_arr[$i][3], $type->id, $income_categories, $expense_categories)
                );
            }
        }
        else
        {
            return redirect()->back()->with('error', 'Please select a valid file to import');
        }

        return response()->json(['data' => $data], 200);
    }

    private function getType($type, $amount)
    {
        if ($type == 'Income' || $type == 'income')
        {
            return (object) ['id' => 2, 'name' => 'Income'];
        }
        else if ($type == 'Expense' || $type == 'expense')
        {
            return (object) ['id' => 1, 'name' => 'Expense'];
        }
        
        if ($amount > 0)
        {
            return (object) ['id' => 2, 'name' => 'Income'];
        }
        else
        {
            return (object) ['id' => 1, 'name' => 'Expense'];
        }
    }

    private function getAmount($amount)
    {
        if ($amount < 0)
        {
            return $amount * -1;
        }
        return $amount;
    }

    private function getCategory($category, $type, $income_categories, $expense_categories)
    {
        if ($type == 2) // Income
        {
            foreach ($income_categories as $income_category)
            {
                if ($income_category->name == $category)
                {
                    return (object) ['id' => $income_category->id, 'name' => $income_category->name];
                }
            }
        }
        else if ($type == 1) // Expense
        {
            foreach ($expense_categories as $expense_category)
            {
                if ($expense_category->name == $category)
                {
                    return (object) ['id' => $expense_category->id, 'name' => $expense_category->name];
                }
            }
        }
        return (object) ['id' => 0, 'name' => 'Uncategorized'];
    }

    public function storeImport(Request $request)
    {
        $data = $request->all();
        if (count($data) == 0)
        {
            return redirect()->back()->with('error', 'No data found in the file');
        }

        $user = Auth::user();
        $now = date('Y-m-d H:i:s');

        $expenses = [];
        $incomes = [];
        foreach ($data as $item)
        {
            if ($item['type'] == 1)
            {
                $expenses[] = [
                    'entry_date' => $item['date'],
                    'description' => $item['description'],
                    'amount' => (float) $item['amount'],
                    'expense_category_id' => (int) $item['category'],
                    'created_by_id' => $user->id,
                    'created_at' => $now,
                    'updated_at' => $now
                ];
            } 
            else if ($item['type'] == 2)
            {
                $incomes[] = [
                    'entry_date' => $item['date'],
                    'description' => $item['description'],
                    'amount' => (float) $item['amount'],
                    'income_category_id' => (int) $item['category'],
                    'created_by_id' => $user->id,
                    'created_at' => $now,
                    'updated_at' => $now
                ];
            }
        }

        if (count($incomes) > 0)
        {
            Income::insert($incomes);
        }
    
        if (count($expenses) > 0)
        {
            Expense::insert($expenses);
        }
    
        return redirect()->route('admin.expenses.index');
    }
}
