<?php

namespace App\Filters;

use App\Filters\Filter;

class EmpWorkStatusRefFilter extends Filter
{
    /**
     * filter properties
     */
    protected $filters = [
        'filter' => [
            'ddlDate',
            'txtDeptCd',
            'ddlStartCompany',
            'ddlEndCompany',
            'txtEmpCd',
            'check'
        ]
    ];


    public function ddlDate($value)
    {
        $this->builder->where('TR01.CALD_DATE', '=', substr($value, 0, 4). substr($value, 7, 2). substr($value, 12, 2));
    }

    public function txtDeptCd($value)
    {
        if (!empty($value)) {
            $this->builder->where('MT10.DEPT_CD', '=', $value);
        }
    }

    /**
     * ddlStartCompany で検索.
     * 検索データがなければスキップ
     */
    public function ddlStartCompany($value)
    {
        if (!empty($value)) {
            $this->builder->where('MT10.COMPANY_CD', '>=', $value);
        }
    }

    /**
     * ddlEndCompany で検索.
     * 検索データがなければスキップ
     */
    public function ddlEndCompany($value)
    {
        if (!empty($value)) {
            $this->builder->where('MT10.COMPANY_CD', '<=', $value);
        }
    }

    public function txtEmpCd($value)
    {
        if (!empty($value)) {
            $this->builder->where('TR01.EMP_CD', '=', $value);
        }
    }

    public function check($value)
    {
        if (!empty($value)) {
            $this->builder->whereIn('TR01.REASON_CD', $value);
        }
    }
}
