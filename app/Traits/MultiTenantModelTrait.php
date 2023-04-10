<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

trait MultiTenantModelTrait
{
    public static function bootMultiTenantModelTrait()
    {
        if (!app()->runningInConsole() && auth()->check()) {
            $user = auth()->user();
            $isAdmin = $user->roles->contains(1);
            $userEntities = $user->entities->pluck('id')->toArray();

            static::creating(function ($model) use ($isAdmin, $user, $userEntities) {
                if (!$isAdmin && in_array($user->current_entity_id, $userEntities)) {
                    $model->created_by_id = $user->id;
                }
            });

            if (!$isAdmin) {
                static::addGlobalScope('created_by_id', function (Builder $builder) use ($user, $userEntities) {
                    $tableName = $builder->getModel()->getTable();
                    $viewMode = request()->query('view_mode');
                    $builder->where(function ($query) use ($user, $userEntities, $tableName, $viewMode) {
                        if ($viewMode == 'personal') {
                            $query->where('created_by_id', $user->id);
                        }
                        else {
                            $query->where('created_by_id', $user->id)
                                ->orWhere(function ($query) use ($userEntities) {
                                    $query->whereIn('entities.id', $userEntities);
                                })
                                ->orWhereNull('created_by_id')
                                ->select($tableName.'.*')
                                ->groupBy($tableName.'.id');
                        }
                    })
                    ->leftJoin('users', 'users.id', '=', 'created_by_id')
                    ->leftJoin('entity_user', 'entity_user.user_id', '=', 'users.id')
                    ->leftJoin('entities', 'entities.id', '=', 'entity_user.entity_id')
                    ->select($tableName . '.*', 'users.id AS user_id', 'users.name AS user_name', 'entities.id AS entity_id', 'entities.title AS entity_title');
                });
            }
        }
    }
}
