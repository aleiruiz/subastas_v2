<?php
/**
 * Created by PhpStorm.
 * User: rana
 * Date: 2/9/19
 * Time: 3:45 PM
 */

namespace App\Repositories;


use Carbon\Carbon;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\QueryException;

trait QueryBuilderTrait
{
    public $operators = [
        '=', '<', '>', '<=', '>=', '<>', '!=', '<=>',
        'like', 'like binary', 'not like', 'ilike',
        '&', '|', '^', '<<', '>>',
        'rlike', 'regexp', 'not regexp',
        '~', '~*', '!~', '!~*', 'similar to',
        'not similar to', 'not ilike', '~~*', '!~~*',
    ];

    public function buildOrderBy(&$queryBuilder, array $orders)
    {
        foreach ($orders as $column => $value) {
            $queryBuilder->orderBy($column, $value);
        }
    }

    public function getFillable()
    {
        return $this->model->getFillable();
    }

    public function extractToArray($relations)
    {
        if (is_string($relations)) {
            return explode(',', $relations);
        }
        return is_array($relations) ? $relations : [];
    }

    protected function buildFilters($searchFields, $orderFields = null, $conditions = null, $filters=null, $selectData = null, $joinArray = null, $relations=null, $orderBy = null, $groupBy = null, $itemPerPage = null, $paginationKey = 'p', $dateField = 'created_at')
    {
        $queryBuilder = $this->loadQueryBuilder();
        $tableName = $this->getTableName();

        if (!empty($relations)) {
            $queryBuilder->with($this->extractToArray($relations));
        }

        if (!is_null($joinArray) && is_array($joinArray)) {
            $dateFieldChecker = explode('.', $dateField);
            if (count($dateFieldChecker) == 1) {
                $dateField = $tableName . '.' . $dateField;
            }
        }

        $order = !is_array(\Request::get($paginationKey . '_ord')) ? \Request::get($paginationKey . '_ord') : null;
        $col = !is_array(\Request::get($paginationKey . '_sort')) ? \Request::get($paginationKey . '_sort') : null;
        $search = !is_array(\Request::get($paginationKey . '_srch')) ? \Request::get($paginationKey . '_srch') : null;
        $frm = !is_array(\Request::get($paginationKey . '_frm')) ? \Request::get($paginationKey . '_frm') : null;
        $to = !is_array(\Request::get($paginationKey . '_to')) ? \Request::get($paginationKey . '_to') : null;
        $comp = !is_array(\Request::get($paginationKey . '_comp')) ? \Request::get($paginationKey . '_comp') : null;
        $ssf = !is_array(\Request::get($paginationKey . '_ssf')) ? \Request::get($paginationKey . '_ssf') : null;
        $filterRequest = \Request::get($paginationKey . '_fltr');

        if ($order == 'a') {
            $order = 'asc';
        } else {
            $order = 'desc';
        }

        $comparison = ['e' => '=', 'lk' => 'like', 'l' => '<', 'le' => '<=', 'm' => '>', 'me' => '>=', 'ne' => '!='];
        $comparison = array_key_exists($comp, $comparison) ? $comparison[$comp] : $comparison = 'LIKE';
        if ($orderFields) {
            $allcol = $orderFields;
        }

        //Condition
        $whereFields = array_column($searchFields, 0);
        $whereFields = array_key_exists($ssf, $whereFields) ? $whereFields[$ssf] : array_values($whereFields);

        //Filter Code
        if($filters && is_array($filterRequest)){
            $filterRequest = array_intersect_key($filterRequest,$filters);
            foreach ($filterRequest as $key=>$value){
                if(is_array($value)){
                    $this->where($queryBuilder, [[$filters[$key][0],'in',$value]]);
                }
                else{
                    unset($filterRequest[$key]);
                }
            }
        }

        $getelements = [$paginationKey . '_srch' => $search, $paginationKey . '_ord' => $order, $paginationKey . '_sort' => $col, $paginationKey . '_frm' => $frm, $paginationKey . '_to' => $to, $paginationKey . '_ssf' => $ssf, $paginationKey . '_comp' => $comp, $paginationKey . '_fltr' => $filterRequest];
        if (isset($allcol)) {
            $column = $this->validationOrderByFields($allcol, $paginationKey . '_sort', $tableName . '.id');
        }

        $srcharr = strtolower($comparison) == 'like' ? explode(' ', $search) : $search;

        foreach ($getelements as $key => $val) {
            if ($val == '') {
                unset($getelements[$key]);
            } elseif ($key == $paginationKey . '_frm' || $key == $paginationKey . '_to') {
                if (validate_date($val) == false) {
                    unset($getelements[$key]);
                }
            }
        }


        if ($joinArray != null && is_array($joinArray[0])) {
            foreach ($joinArray as $arr) {
                if (isset($arr[4])) {
                    $queryBuilder->leftJoin($arr[0], function ($join) use ($arr) {
                        $join->on($arr[1], $arr[2], $arr[3])
                            ->where($arr[4]);
                    });
                } else {
                    $queryBuilder->leftJoin($arr[0], $arr[1], $arr[2], $arr[3]);
                }
            }
        } elseif ($joinArray != null) {
            if (isset($joinArray[4])) {
                $queryBuilder->leftJoin($joinArray[0], function ($join) use ($joinArray) {
                    $join->on($joinArray[1], $joinArray[2], $joinArray[3])
                        ->where($joinArray[4]);
                });
            } else {
                $queryBuilder->leftJoin($joinArray[0], $joinArray[1], $joinArray[2], $joinArray[3]);
            }
        }

        if (isset($getelements[$paginationKey . '_frm']) && validate_date($getelements[$paginationKey . '_frm'])) {
            $queryBuilder->where($dateField, '>=', $frm);
        }
        if (isset($getelements[$paginationKey . '_to']) && validate_date($getelements[$paginationKey . '_to'])) {
            $queryBuilder->where($dateField, '<', Carbon::parse($to)->addDay());
        }

        if (!is_null($conditions)) {
            $this->where($queryBuilder, $conditions);
        }

        if (!empty($search)) {
            $queryBuilder->where(function ($query) use ($srcharr, $whereFields, $comparison) {
                $firstQuery = 1;
                if (is_array($srcharr) && strtolower($comparison) == 'like') {
                    foreach ($srcharr as $wh) {
                        if (is_array($whereFields)) {
                            foreach ($whereFields as $sf) {
                                if ($firstQuery == 1) {
                                    $query->where($sf, 'like', '%' . $wh . '%');
                                } else {
                                    $query->orWhere($sf, 'like', '%' . $wh . '%');
                                }
                                $firstQuery = 0;
                            }
                        } else {
                            if ($firstQuery == 1) {
                                $query->where($whereFields, 'like', '%' . $wh . '%');
                            } else {
                                $query->orWhere($whereFields, 'like', '%' . $wh . '%');
                            }
                            $firstQuery = 0;
                        }
                    }
                } else {
                    if (is_array($whereFields)) {
                        foreach ($whereFields as $sf) {
                            if ($firstQuery == 1) {
                                $query->where($sf, $comparison, $srcharr);
                            } else {
                                $query->orWhere($sf, $comparison, $srcharr);
                            }
                            $firstQuery = 0;
                        }
                    } else {
                        $query->where($whereFields, $comparison, $srcharr);
                    }
                }
            });
        }
        if ($selectData != null) {
            $queryBuilder->select($selectData);
        }
        if (!empty($groupBy)) {
            $queryBuilder->groupBy($groupBy);
        }

        if (!empty($column)) {
            $queryBuilder->orderBy($column, $order);
        }
        else if(is_array($orderBy)){
            if(is_array($orderBy[0])){
                foreach ($orderBy as $oB){
                    $queryBuilder->orderBy($oB[0], $oB[1]);
                }
            }
            else{
                $queryBuilder->orderBy($orderBy[0], $orderBy[1]);
            }
        }
        else if (empty($groupBy)) {
            $queryBuilder->orderBy($tableName . '.id', $order);
        }

        if (is_null($itemPerPage)) {

            return $queryBuilder->get();
        }
        return $queryBuilder->paginate($itemPerPage, ['*'], $paginationKey)->appends($getelements);
    }

    public function loadQueryBuilder()
    {
        return $this->model::query();
    }

    public function getTableName()
    {
        return $this->model->getTable();
    }

    protected function validationOrderByFields($columns, $key = 'sort', $default = null)
    {
        $data = \Request::get($key);
        if (is_numeric($data) && array_key_exists($data, array_column($columns, 0))) {
            return $columns[$data][0];
        }
        return false;
    }

    public function where(& $queryBuilder, array $conditions)
    {
        foreach ($conditions as $column => $value) {
            if (is_string($column)) {
                $this->buildWhere($queryBuilder, [$column, $value]);
            } else if (is_array($value) && is_string($value[0])) {
                if (in_array(strtolower($value[1]), $this->operators)) {
                    $this->buildWhere($queryBuilder, $value);
                } else if (in_array(strtolower($value[1]), ['in', 'notin'])) {
                    $this->buildWhereIn($queryBuilder, $value, array_search(strtolower($value[1]), ['in', 'notin']));
                } elseif (in_array(strtolower($value[1]), ['between', 'notbetween'])) {
                    $this->buildWhereBetween($queryBuilder, $value, array_search(strtolower($value[1]), ['between', 'notbetween']));
                }
            } else if (is_array($value) && count($value) != count($value, COUNT_RECURSIVE)) {
                $whereType = 'where';
                if (last($value) == 'or') {
                    $whereType = 'orWhere';
                    array_pop($value);
                }

                $queryBuilder->{$whereType}(function ($query) use ($value) {
                    foreach ($value as $arrayValue) {
                        $subWhereType = 'where';

                        if (is_array($arrayValue) && last($arrayValue) == 'or') {
                            $subWhereType = 'orWhere';
                            array_pop($arrayValue);
                        }
                        $query->{$subWhereType}(function ($subQuery) use ($arrayValue) {
                            foreach ($arrayValue as $arrayColumn => $columnValue) {
                                if (is_string($arrayColumn)) {
                                    $this->buildWhere($subQuery, [$arrayColumn, $columnValue], $subQuery);
                                } elseif (is_array($columnValue) && is_string($columnValue[0])) {
                                    if (in_array(strtolower($columnValue[1]), $this->operators)) {
                                        $this->buildWhere($subQuery, $columnValue, $subQuery);
                                    } else if (in_array(strtolower($columnValue[1]), ['in', 'notin'])) {
                                        $this->buildWhereIn($subQuery, $columnValue, array_search(strtolower($columnValue[1]), ['in', 'notin']), $subQuery);
                                    } elseif (in_array(strtolower($columnValue[1]), ['between', 'notbetween'])) {
                                        $this->buildWhereBetween($subQuery, $columnValue, array_search(strtolower($columnValue[1]), ['between', 'notbetween']), $subQuery);
                                    }
                                }

                            }
                        });


                    }
                });

            }
        }
    }

    public function buildWhere(& $queryBuilder, $value, $whereType = 'where')
    {
        switch (count($value)) {
            case 2:
                {
                    $queryBuilder->{$whereType}($value[0], $value[1]);
                    break;
                }
            case 3 :
                {
                    $queryBuilder->{$whereType}($value[0], $value[1], $value[2]);
                    break;
                }
            case 4:
                {
                    $queryBuilder->{$whereType}($value[0], $value[1], $value[2], $value[3]);
                    break;
                }
            default :
                {
                    $this->throwException($queryBuilder, 'Invalid where conditions.');
                }
        }

    }

    public function throwException($queryBuilder, $message = 'Invalid argument.')
    {
        throw new QueryException($queryBuilder->toSql(), $queryBuilder->getBindings(), new \Exception($message));
    }

    public function buildWhereIn(& $queryBuilder, $value, $notIn = false)
    {
        $queryBuilder = is_null($queryBuilder) ? $queryBuilder : $queryBuilder;

        if (!is_array($value[2])) {
            $this->throwException($queryBuilder, 'Invalid where in conditions. The value must be an array.');
        }

        $logicalOperator = 'and';

        if (isset($value[3])) {
            $logicalOperator = $value[3];
        }

        $queryBuilder->whereIn($value[0], $value[2], $logicalOperator, $notIn);

    }

    public function buildWhereBetween(& $queryBuilder, $value, $notBetween = false)
    {
        if (!is_array($value[2])) {
            $this->throwException($queryBuilder, 'Invalid where in conditions. The value must be an array.');
        }

        $logicalOperator = 'and';

        if (isset($value[3])) {
            $logicalOperator = $value[3];
        }

        $queryBuilder->whereBetween($value[0], $value[2], $logicalOperator, $notBetween);
    }

    private function getUpdatableFields($values)
    {
        $fields = [];
        $conditions = [];
        foreach ($values as $value) {
            if (count(array_intersect_key($value['fields'], $value['conditions'])) >= 2) {
                return false;
            }
            $fields = array_merge($fields, $value['fields']);
            $conditions = array_merge($conditions, $value['conditions']);
        }
        $fields = array_keys($fields);
        $conditions = array_keys($conditions);
        $commonFields = array_intersect($fields, $conditions);
        if (count($commonFields) >= 1) {
            $fields = array_merge(array_diff($fields, $commonFields), $commonFields);
        }
        return $fields;
    }
}
