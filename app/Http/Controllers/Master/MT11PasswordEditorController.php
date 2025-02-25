<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Http\Requests\MT11PasswordEditorRequest;
use App\Models\MT11Login;
use App\Repositories\MT93PgRepository;

class MT11PasswordEditorController extends Controller
{
    public function __construct(MT93PgRepository $pg_repository)
    {
        parent::__construct($pg_repository, '000014');
    }

    public function index()
    {
        return parent::viewWithMenu('master.MT11PasswordEditor');
    }

    public function update(MT11PasswordEditorRequest $request)
    {
        // 入力したデータを受け取る
        $inputs = $request->all();

        // SessionでログインIDの取得
        $loginUserId = session('id');
        // MT11_LOGIN(EMP_CD)を取得
        $emp_cd = MT11Login::where(['LOGIN_ID' => $loginUserId])
                            ->where(['PASSWORD' => $request->txtPassword])
                            ->pluck('EMP_CD')
                            ->first();
        // 取得したEMP_CDでDB検索
        $data = MT11Login::find($emp_cd);
        // Pass変更
        $data->PASSWORD = $request->txtNewPassword;

        date_default_timezone_set('Asia/Tokyo');
        $data->UPD_DATE = now();


        $data->timestamps = false;
        $data->save();

        return redirect('/');
    }
}
