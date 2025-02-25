<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property string $DEPT_AUTH_CD
 * @property string $DEPT_AUTH_NAME
 * @property string $DEPT_CD
 * @property string $RSV1_CLS_CD
 * @property string $RSV2_CLS_CD
 * @property string $UPD_DATE
 */
class MT13DeptAuth extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'MT13_DEPT_AUTH';

    /**
     * @var array
     */
    protected $fillable = ['DEPT_AUTH_CD','DEPT_AUTH_NAME','DEPT_CD', 'RSV1_CLS_CD', 'RSV2_CLS_CD', 'UPD_DATE'];

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = ['DEPT_AUTH_CD','DEPT_CD'];
    public $incrementing = false;

    /**
     * The connection name for the model.
     *
     * @var string
     */
    protected $connection = 'sqlsrv';
}
