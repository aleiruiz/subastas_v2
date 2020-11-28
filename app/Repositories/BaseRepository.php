<?php
/**
 * Created by PhpStorm.
 * User: rana
 * Date: 7/1/18
 * Time: 11:28 AM
 */

namespace App\Repositories;


use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

abstract class BaseRepository
{
    use RepositoryTrait, QueryBuilderTrait;

    public function getAll($relations = null, $orders = null)
    {
        $queryBuilder = $this->loadQueryBuilder();

        if (!empty($relations)) {
            $queryBuilder->with($this->extractToArray($relations));
        }

        if (!empty($orders) && is_array($orders)) {
            $this->buildOrderBy($queryBuilder, $orders);
        }

        return $queryBuilder->get();

    }

    public function getByConditions(array $conditions, $relations = null, $orders = null)
    {
        $queryBuilder = $this->loadQueryBuilder();

        $this->where($queryBuilder, $conditions);

        if (!empty($relations)) {
            $queryBuilder->with($this->extractToArray($relations));
        }

        if (!empty($orders) && is_array($orders)) {
            $this->buildOrderBy($queryBuilder, $orders);
        }

        return $queryBuilder->get();
    }

    public function findOrFailById(int $id, $relations = null)
    {
        $queryBuilder = $this->loadQueryBuilder();

        if (!empty($relations)) {
            $queryBuilder->with($this->extractToArray($relations));
        }
        return $queryBuilder->findOrFail($id);
    }

    public function findOrFailByConditions(array $conditions, $relations = null)
    {
        $queryBuilder = $this->loadQueryBuilder();

        $this->where($queryBuilder, $conditions);

        if (!empty($relations)) {
            $queryBuilder->with($this->extractToArray($relations));
        }

        $instant = $queryBuilder->first();

        abort_if(empty($instant), 404);

        return $instant;
    }

    public function create(array $attributes)
    {
        $queryBuilder = $this->loadQueryBuilder();
        return $queryBuilder->create(Arr::only($attributes, $this->getFillable()));
    }

    /**
     * @param array $attributes
     * @param int|Model $model
     * @param string $attribute
     * @return bool|Model
     */
    public function update(array $attributes, $model, string $attribute = "id")
    {
        try {
            if ($model instanceof Model) {
                $instance = $model;
            } else if (is_int((int)$model)) {
                $instance = $this->getFirstByConditions([$attribute => $model]);
            }
            if (empty($instance)) {
                return false;
            }
            return $instance->update(Arr::only($attributes, $this->getFillable())) ? $instance : false;
        } catch (Exception $exception) {
            return false;
        }
        return false;
    }

    public function getFirstByConditions(array $conditions, $relations = null, $orders=null)
    {
        $queryBuilder = $this->loadQueryBuilder();

        $this->where($queryBuilder, $conditions);

        if (!empty($orders) && is_array($orders)) {
            $this->buildOrderBy($queryBuilder, $orders);
        }

        if (!empty($relations)) {
            $queryBuilder->with($this->extractToArray($relations));
        }
        return $queryBuilder->first();
    }

    public function updateByConditions(array $attributes, array $conditions, $isSingleRowUpdate = true)
    {

        try {
            $attributes = Arr::only($attributes, $this->getFillable());

            if ($isSingleRowUpdate) {
                $instance = $this->getFirstByConditions($conditions);
                if (empty($instance)) {
                    return false;
                }
                return $instance->update($attributes) ? $instance : false;
            } else {
                $queryBuilder = $this->loadQueryBuilder();
                $this->where($queryBuilder, $conditions);
                return $queryBuilder->update($attributes);
            }
        } catch (Exception $exception) {
            return false;
        }
    }

    /**
     * @param int|Model $model
     * @return bool|null
     * @throws Exception
     */
    public function deleteById($model)
    {
        try {
            if ($model instanceof Model) {
                return $model->delete() ? $model : false;
            } else {
                $instance = $this->getFirstById($model);
                if (empty($instance)) {
                    return false;
                }
                return $instance->delete() ? $instance : false;
            }
        } catch (Exception $e) {
        }

        return false;
    }

    public function getFirstById(int $id, $relations = null)
    {
        $queryBuilder = $this->loadQueryBuilder();

        if (!empty($relations)) {
            $queryBuilder->with($this->extractToArray($relations));
        }

        return $queryBuilder->where('id', $id)->first();
    }

    public function deleteByConditions(array $conditions, $isSingleRowDelete = true)
    {
        try {
            if ($isSingleRowDelete) {
                $instance = $this->getFirstByConditions($conditions);
                if (empty($instance)) {
                    return false;
                }
                return $instance->delete() ? $instance : false;
            } else {
                $queryBuilder = $this->loadQueryBuilder();

                $this->where($queryBuilder, $conditions);
                return $queryBuilder->delete();
            }

        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * @param int|Model $model
     * @param string $attribute
     * @return bool|Model
     */
    public function toggleStatus($model, string $attribute = 'is_active')
    {
        if ($model instanceof Model) {
            $instance = $model;
        } else if (is_int((int)$model)) {
            $instance = $this->getFirstById($model);
        } else {
            return false;
        }

        if (empty($instance)) {
            return false;
        }

        $instance->{$attribute} = $instance->{$attribute} ? 0 : 1;

        return $instance->update() ? $instance : false;
    }

    public function toggleStatusByConditions(array $conditions, string $attribute = 'is_active', $isSingleRowUpdate = true)
    {
        if ($isSingleRowUpdate) {
            $instance = $this->getFirstByConditions($conditions);
            if (empty($instance)) {
                return false;
            }
            $instance->{$attribute} = $instance->{$attribute} ? 0 : 1;
            return $instance->update() ? $instance : false;
        } else {
            $queryBuilder = $this->loadQueryBuilder();

            $this->where($queryBuilder, $conditions);
            return $queryBuilder->update([$attribute => DB::raw('abs((' . $attribute . '-1) * (-1))')]);
        }
    }

    public function insert(array $attributes)
    {
        $queryBuilder = $this->loadQueryBuilder();

        return $queryBuilder->insert($attributes);
    }

    public function bulkUpdate(array $values)
    {

        if (empty($values)) {
            return false;
        }

        $table = $this->getTableName();

        $sql = "UPDATE {$table} SET ";
        $updatableFieldSeparator = '';
        $rowCount = count($values);

        $updatableFields = $this->getUpdatableFields($values);

        if (empty($updatableFields)) {
            return false;
        }


        foreach ($updatableFields as $updatableField) {
            //for each updatable field
            $sql .= $updatableFieldSeparator;
            $sql .= "{$updatableField} = (CASE";
            for ($i = 0; $i < $rowCount; $i++) {
                if (!isset($values[$i]['fields'][$updatableField])) {
                    continue;
                }
                $sql .= " WHEN ";
                $conditionalSyntax = '';

                // for each condition
                foreach ($values[$i]['conditions'] as $conditionalKey => $conditionalValue) {
                    if (is_array($conditionalValue)) {
                        $sql .= $conditionalSyntax . "{$conditionalKey} {$conditionalValue[0]} {$conditionalValue[1]}";
                    } else {
                        $sql .= $conditionalSyntax . "{$conditionalKey}='{$conditionalValue}'";
                    }
                    $conditionalSyntax = ' AND ';
                }

                $updatableFieldValue = $values[$i]['fields'][$updatableField];
                if (is_array($updatableFieldValue)) {
                    if ($updatableFieldValue[0] == 'increment') {
                        $sql .= " THEN {$updatableField} + {$updatableFieldValue[1]}";
                    } elseif ($updatableFieldValue[0] == 'decrement') {
                        $sql .= " THEN {$updatableField} - {$updatableFieldValue[1]}";
                    } else {
                        $sql .= " THEN {$updatableFieldValue[1]}";
                    }
                } else {
                    $sql .= " THEN '{$updatableFieldValue}'";
                }
            }
            $sql .= " ELSE {$updatableField} END) ";
            $updatableFieldSeparator = ', ';
        }

        $conditionalClause = "WHERE ";
        $conditionalFieldSeparator = '(';

        foreach ($values as $value) {
            $innerSeparator = '';
            $conditionalClause .= $conditionalFieldSeparator;
            foreach ($value['conditions'] as $conditionalKey => $conditionalValue) {
                if (is_array($conditionalValue)) {
                    $conditionalClause .= $innerSeparator . "{$conditionalKey} {$conditionalValue[0]} {$conditionalValue[1]}";
                } else {
                    $conditionalClause .= $innerSeparator . "{$conditionalKey}='{$conditionalValue}'";
                }
                $innerSeparator = ' AND ';
            }
            $conditionalClause .= ')';
            $conditionalFieldSeparator = ' OR (';
        }
        $sql .= $conditionalClause;
        return DB::update($sql);
    }

    public function countByConditions(array $conditions)
    {
        $queryBuilder = $this->loadQueryBuilder();

        $this->where($queryBuilder, $conditions);

        return $queryBuilder->count();
    }

    public function filters($searchFields, $orderFields = null, $conditions = null, $filters=null, $selectData = null, $joinArray = null, $relations = null, $groupBy = null, $paginationKey = 'p', $dateField = 'created_at')
    {
        return $this->buildFilters($searchFields, $orderFields, $conditions, $filters, $selectData, $joinArray, $relations, null, $groupBy, null, $paginationKey, $dateField);
    }

    public function paginate(array $conditions = [], array $columns = ['*'], int $perPage = ITEM_PER_PAGE, string $paginationKey = 'p')
    {
        $queryBuilder = $this->loadQueryBuilder();

        if (!empty($conditions)) {
            $this->where($queryBuilder, $conditions);
        }

        return $queryBuilder->paginate($perPage, $columns, $paginationKey);
    }

    public function simplePaginate(array $conditions = [], array $columns = ['*'], int $perPage = ITEM_PER_PAGE, string $paginationKey = 'p')
    {
        $queryBuilder = $this->loadQueryBuilder();

        if (!empty($conditions)) {
            $this->where($queryBuilder, $conditions);
        }

        return $queryBuilder->simplePaginate($perPage, $columns, $paginationKey);
    }

    public function paginateWithFilters($searchFields, $orderFields = null, $conditions = null, $filters=null, $selectData = null, $joinArray = null, $relations = null, $orderBy = null, $groupBy = null, $itemPerPage = null, $paginationKey = 'p', $dateField = 'created_at')
    {
        $itemPerPage = empty($itemPerPage) ? settings('item_per_page') : $itemPerPage;
        $itemPerPage = filter_var($itemPerPage, FILTER_VALIDATE_INT) != false && $itemPerPage > 0 ? $itemPerPage : ITEM_PER_PAGE;

        return $this->buildFilters($searchFields, $orderFields, $conditions, $filters, $selectData, $joinArray, $relations, $orderBy, $groupBy, $itemPerPage, $paginationKey, $dateField);
    }
}
