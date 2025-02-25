<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property string $EMP_CD
 * @property string $CRT_DATE
 * @property integer $TERM_NO
 * @property string $WORKTIME_CLS_CD
 * @property string $WORK_DATE
 * @property integer $WORK_TIME_HH
 * @property integer $WORK_TIME_MI
 * @property string $DATA_OUT_CLS_CD
 * @property string $DATA_OUT_DATE
 * @property string $CALD_DATE
 */
class TR50WorkTime extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'TR50_WORKTIME';

    /**
     * @var array
     */
    protected $fillable = ['WORKTIME_CLS_CD', 'WORK_DATE', 'WORK_TIME_HH', 'WORK_TIME_MI', 'DATA_OUT_CLS_CD', 'DATA_OUT_DATE', 'CALD_DATE'];

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = ['EMP_CD', 'CRT_DATE', 'TERM_NO'];

    /**
     * The connection name for the model.
     *
     * @var string
     */
    protected $connection = 'sqlsrv';

    // created_atとupdated_atを無効化
    public $timestamps = false;
    public $incrementing = false;
}
