<?php

namespace App\Repositories;

trait RepositoryTrait
{


    protected function _where_builder($where)
    {
        $model = $this->model;
        $output = $this->_subWhere($model, $where);
        return $output;
    }

    protected function _subWhere($model, $where)
    {
        if (count($where) <= 0) {
            return $model;
        }
        if (
            !isset($where['where_type']) &&
            !isset($where['group_query']) &&
            !isset($where[0])
        ) {
            $model = $model->where($where);
        } elseif (
            !isset($where['where_type']) &&
            !isset($where['group_query']) &&
            isset($where[0])
        ) {
            if (!is_array($where[0])) {
                $model = $model->where([$where]);
            } else {
                foreach ($where as $where_single) {
                    $model = $this->_subWhere($model, $where_single);
                }
            }
        } elseif (
            isset($where['where_type']) &&
            in_array($where['where_type'], ['whereBetween', 'whereNotBetween', 'whereIn', 'whereNotIn'])
        ) {
            $model = $model->{$where['where_type']}($where[0][0], $where[0][1]);
        } elseif (
            !isset($where['group_query']) ||
            (isset($where['group_query']) && $where['group_query'] !== true)
        ) {
            $wheretype = 'where';
            if ($where['where_type'] == 'orWhere') {
                $wheretype = 'orWhere';
            }
            if (isset($where[0][0])) {
                if (is_array($where[0][0])) {
                    $model = $model->{$wheretype}($where[0]);
                } else {
                    $model = $model->{$wheretype}([$where[0]]);
                }
            } else {
                $model = $model->{$wheretype}($where[0]);
            }
        } elseif (isset($where['group_query']) && $where['group_query'] === true) {
            $wheretype = 'where';
            if ($where['where_type'] == 'orWhere') {
                $wheretype = 'orWhere';
            }
            $model = $model->{$wheretype}(function ($query) use ($where) {
                foreach ($where[0] as $sub_key => $sub_val) {
                    $query = $this->_subWhere($query, $sub_val);
                }
            });
        }
        return $model;
    }




}
