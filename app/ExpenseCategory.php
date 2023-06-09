<?php

namespace App;

use App\Traits\MultiTenantModelTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ExpenseCategory extends Model
{
    use SoftDeletes, MultiTenantModelTrait;

    public $table = 'expense_categories';

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $fillable = [
        'name',
        'include_in_report',
        'created_at',
        'updated_at',
        'deleted_at',
        'created_by_id',
    ];

    public function toArray()
{
    $array = parent::toArray();

    if ($this->user) {
        $array['name'] = $this->name;
    }

    return $array;
}

    public function expenses()
    {
        return $this->hasMany(Expense::class, 'expense_category_id', 'id');
    }

    public function created_by()
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }
}
