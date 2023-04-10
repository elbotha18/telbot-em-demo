<?php

namespace App\Http\Requests;

use App\Entity;
use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Symfony\Component\HttpFoundation\Response;

class UpdateEntityRequest extends FormRequest
{
    public function authorize()
    {
        abort_if(Gate::denies('entity_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return true;
    }

    public function rules()
    {
        return [
            'title' => [
                'required',
            ],
        ];
    }
}
