<?php

namespace App\Http\Requests;

use App\IncomeCategory;
use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Symfony\Component\HttpFoundation\Response;

class UpdateIncomeCategoryRequest extends FormRequest
{
    public function authorize()
    {
        abort_if(Gate::denies('income_category_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return true;
    }

    public function prepareForValidation()
    {
        $this->merge([
            'include_in_report' => $this->include_in_report == 'on' ? true : false,
        ]);
    }


    public function rules()
    {
        return [
            'name' => [
                'required',
            ],
            'include_in_report' => 'boolean'
        ];
    }
}
