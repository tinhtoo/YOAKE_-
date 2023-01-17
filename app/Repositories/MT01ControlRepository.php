<?php

namespace App\Repositories;

use Illuminate\Http\Request;
use App\Models\MT01Control;

class MT01ControlRepository
{
    public function getMt01()
    {
        return MT01Control::where("CONTROL_CD", "1")->first();
    }
}
