<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property string $EMP_CD
 * @property string $LOGIN_ID
 * @property string $PASSWORD
 * @property string $UPD_DATE
 */
class MT11Login extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'MT11_LOGIN';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'EMP_CD';

    /**
     * The "type" of the auto-incrementing ID.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * @var array
     */
    protected $fillable = ['LOGIN_ID', 'PASSWORD', 'UPD_DATE'];

    /**
     * The connection name for the model.
     *
     * @var string
     */
    protected $connection = 'sqlsrv';

    public function EmpDept()
    {
        return $this->hasMany('App\Models\MT10Emp');
    }

    // created_atとupdated_atを無効化
    public $timestamps = false;
}
