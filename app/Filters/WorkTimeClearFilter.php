<?php

namespace App\Filters;

use App\Filters\Filter;
use Illuminate\Http\Request;

class WorkTimeClearFilter extends Filter
{
    /**
     * filter properties.
     */
    protected $filters = [
        'filter' => [
            'startDate',
            'endDate'
        ]
    ];

    public function startDate($value)
    {
        if (!empty($value)) {
            $this->builder
                ->where('TR01.CALD_DATE', '>=', substr($value, 0, 4). substr($value, 7, 2). substr($value, 12, 2));
        }
    }

    public function endDate($value)
    {
        if (!empty($value)) {
            $this->builder
                ->where('TR01.CALD_DATE', '<=', substr($value, 0, 4). substr($value, 7, 2). substr($value, 12, 2));
        }
    }
}
