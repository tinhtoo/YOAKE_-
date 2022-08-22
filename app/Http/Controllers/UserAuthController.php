<?php

namespace App\Http\Controllers;

use GuzzleHttp\Promise\Create;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Foundation\Exceptions\Handler;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;
use App\Models\MT11Login;
use App\Models\MT10Emp;

use App\Repositories\MT93PgRepository;

class UserAuthController extends Controller
{
    public function __construct(MT93PgRepository $pg_repository)
    {
        parent::__construct($pg_repository, 'main');
    }

    //////***** 勤怠管理 *****//////
    /**
     * ログイン画面処理
     *
     * @return view
     */
    public function login()
    {
        return view('auth.login');
    }

    /**
     * ログアウトユーザ検証チェック
     *
     * @param Request $request
     * @return void
     */
    public function loginCheck(Request $request){
        // 検証チェック

        // ログインId の取得
        $loginId = $request->txtLoginId;
        $loginPw = $request->password;
        $loginUser = MT11Login::WHERE('LOGIN_ID', $loginId)->first();

        if($loginUser){
            if($request->password == $loginUser->PASSWORD){

                $request->session()->put('id', $loginUser->LOGIN_ID);

                return redirect('main');
            }else{
                return back()->with('fail','ログインIDまたは、パスワードに誤りがあります。');
            }
        }else{
            return back()->with('fail','ログインIDまたは、パスワードに誤りがあります。');
        }
    }

    ////// ***** 勤怠管理 *****//////
    /**
     * メインメニュー画面
     *
     * @return view
     */
    function main(Request $request)
    {
        $loginId = session()->get('id');
        $loginUser = MT11Login::WHERE('LOGIN_ID', $loginId)->pluck('EMP_CD');
        $loginName = MT10Emp::WHERE('EMP_CD', $loginUser)->pluck('EMP_NAME');
        $request->session()->put('name', $loginName[0]);

        return parent::viewWithMenu('menu.main');
    }

    /**
     * ログアウト処理
     *
     * @param Request $request
     * @return void
     */
    public function logout(Request $request)
    {

        Session::flush();

        Auth::logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
