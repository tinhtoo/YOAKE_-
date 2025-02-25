<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property string $CALD_YEAR
 * @property string $CALD_MONTH
 * @property string $EMP_CD
 * @property string $LAST_PTN_CD
 * @property integer $LAST_DAY_NO
 */
class TR02EmpCalendar extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'TR02_EMPCALENDAR';

    /**
     * @var array
     */
    protected $fillable = ['LAST_PTN_CD', 'LAST_DAY_NO'];

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = ['CALD_YEAR','CALD_MONTH','EMP_CD'];

    /**
     * The connection name for the model.
     *
     * @var string
     */
    protected $connection = 'sqlsrv';

    /**
     * The "type" of the auto-incrementing ID.
     *
     * @var string
     */
    protected $keyType = 'string';

    public $incrementing = false;
    public $timestamps = false;
}
