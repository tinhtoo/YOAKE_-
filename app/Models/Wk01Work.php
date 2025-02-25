<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property string $LOGIN_ID
 * @property string $EMP_CD
 * @property string $CALD_YEAR
 * @property string $CALD_MONTH
 * @property string $CALD_DATE
 * @property string $WORKPTN_CD
 * @property string $WORKPTN_STR_TIME
 * @property string $WORKPTN_END_TIME
 * @property string $REASON_CD
 * @property integer $OFC_TIME_HH
 * @property integer $OFC_TIME_MI
 * @property integer $OFC_CNT
 * @property integer $LEV_TIME_HH
 * @property integer $LEV_TIME_MI
 * @property integer $LEV_CNT
 * @property integer $OUT1_TIME_HH
 * @property integer $OUT1_TIME_MI
 * @property integer $OUT1_CNT
 * @property integer $IN1_TIME_HH
 * @property integer $IN1_TIME_MI
 * @property integer $IN1_CNT
 * @property integer $OUT2_TIME_HH
 * @property integer $OUT2_TIME_MI
 * @property integer $OUT2_CNT
 * @property integer $IN2_TIME_HH
 * @property integer $IN2_TIME_MI
 * @property integer $IN2_CNT
 * @property integer $WORK_TIME_HH
 * @property integer $WORK_TIME_MI
 * @property integer $TARD_TIME_HH
 * @property integer $TARD_TIME_MI
 * @property integer $LEAVE_TIME_HH
 * @property integer $LEAVE_TIME_MI
 * @property integer $OUT_TIME_HH
 * @property integer $OUT_TIME_MI
 * @property integer $OVTM1_TIME_HH
 * @property integer $OVTM1_TIME_MI
 * @property integer $OVTM2_TIME_HH
 * @property integer $OVTM2_TIME_MI
 * @property integer $OVTM3_TIME_HH
 * @property integer $OVTM3_TIME_MI
 * @property integer $OVTM4_TIME_HH
 * @property integer $OVTM4_TIME_MI
 * @property integer $OVTM5_TIME_HH
 * @property integer $OVTM5_TIME_MI
 * @property integer $OVTM6_TIME_HH
 * @property integer $OVTM6_TIME_MI
 * @property integer $OVTM7_TIME_HH
 * @property integer $OVTM7_TIME_MI
 * @property integer $OVTM8_TIME_HH
 * @property integer $OVTM8_TIME_MI
 * @property integer $OVTM9_TIME_HH
 * @property integer $OVTM9_TIME_MI
 * @property integer $OVTM10_TIME_HH
 * @property integer $OVTM10_TIME_MI
 * @property integer $EXT1_TIME_HH
 * @property integer $EXT1_TIME_MI
 * @property integer $EXT2_TIME_HH
 * @property integer $EXT2_TIME_MI
 * @property integer $EXT3_TIME_HH
 * @property integer $EXT3_TIME_MI
 * @property integer $EXT4_TIME_HH
 * @property integer $EXT4_TIME_MI
 * @property integer $EXT5_TIME_HH
 * @property integer $EXT5_TIME_MI
 * @property integer $RSV1_TIME_HH
 * @property integer $RSV1_TIME_MI
 * @property integer $RSV2_TIME_HH
 * @property integer $RSV2_TIME_MI
 * @property integer $RSV3_TIME_HH
 * @property integer $RSV3_TIME_MI
 * @property float $WORKDAY_CNT
 * @property float $HOLWORK_CNT
 * @property float $SPCHOL_CNT
 * @property float $PADHOL_CNT
 * @property float $ABCWORK_CNT
 * @property float $COMPDAY_CNT
 * @property float $RSV1_CNT
 * @property float $RSV2_CNT
 * @property float $RSV3_CNT
 * @property string $UPD_CLS_CD
 * @property string $FIX_CLS_CD
 * @property string $RSV1_CLS_CD
 * @property string $RSV2_CLS_CD
 * @property string $ADD_DATE
 * @property string $UPD_DATE
 * @property string $REMARK
 * @property float $SUBHOL_CNT
 * @property float $SUBWORK_CNT
 */
class Wk01Work extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'WK01_WORK';

    /**
     * @var array
     */
    protected $fillable = ['CALD_YEAR', 'CALD_MONTH', 'WORKPTN_CD', 'WORKPTN_STR_TIME', 'WORKPTN_END_TIME', 'REASON_CD', 'OFC_TIME_HH', 'OFC_TIME_MI', 'OFC_CNT', 'LEV_TIME_HH', 'LEV_TIME_MI', 'LEV_CNT', 'OUT1_TIME_HH', 'OUT1_TIME_MI', 'OUT1_CNT', 'IN1_TIME_HH', 'IN1_TIME_MI', 'IN1_CNT', 'OUT2_TIME_HH', 'OUT2_TIME_MI', 'OUT2_CNT', 'IN2_TIME_HH', 'IN2_TIME_MI', 'IN2_CNT', 'WORK_TIME_HH', 'WORK_TIME_MI', 'TARD_TIME_HH', 'TARD_TIME_MI', 'LEAVE_TIME_HH', 'LEAVE_TIME_MI', 'OUT_TIME_HH', 'OUT_TIME_MI', 'OVTM1_TIME_HH', 'OVTM1_TIME_MI', 'OVTM2_TIME_HH', 'OVTM2_TIME_MI', 'OVTM3_TIME_HH', 'OVTM3_TIME_MI', 'OVTM4_TIME_HH', 'OVTM4_TIME_MI', 'OVTM5_TIME_HH', 'OVTM5_TIME_MI', 'OVTM6_TIME_HH', 'OVTM6_TIME_MI', 'OVTM7_TIME_HH', 'OVTM7_TIME_MI', 'OVTM8_TIME_HH', 'OVTM8_TIME_MI', 'OVTM9_TIME_HH', 'OVTM9_TIME_MI', 'OVTM10_TIME_HH', 'OVTM10_TIME_MI', 'EXT1_TIME_HH', 'EXT1_TIME_MI', 'EXT2_TIME_HH', 'EXT2_TIME_MI', 'EXT3_TIME_HH', 'EXT3_TIME_MI', 'EXT4_TIME_HH', 'EXT4_TIME_MI', 'EXT5_TIME_HH', 'EXT5_TIME_MI', 'RSV1_TIME_HH', 'RSV1_TIME_MI', 'RSV2_TIME_HH', 'RSV2_TIME_MI', 'RSV3_TIME_HH', 'RSV3_TIME_MI', 'WORKDAY_CNT', 'HOLWORK_CNT', 'SPCHOL_CNT', 'PADHOL_CNT', 'ABCWORK_CNT', 'COMPDAY_CNT', 'RSV1_CNT', 'RSV2_CNT', 'RSV3_CNT', 'UPD_CLS_CD', 'FIX_CLS_CD', 'RSV1_CLS_CD', 'RSV2_CLS_CD', 'ADD_DATE', 'UPD_DATE', 'REMARK', 'SUBHOL_CNT', 'SUBWORK_CNT'];

    /**
     * The connection name for the model.
     *
     * @var string
     */
    protected $connection = 'sqlsrv';

    /**
     * Query scope.
     */
    public function scopeFilter($query, $filter)
    {
        $filter->apply($query);
    }
}
