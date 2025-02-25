<!-- ログイン情報入力 -->
@extends('menu.main')

@section('title', 'ログイン情報入力')

@section('content')
    <div id="contents-stage">
        <table class="BaseContainerStyle1">
            <tbody>
                <tr>
                    <td>

                        <div id="UpdatePanel1">

                            <form action="" method="POST" id="form" autocomplete="off" autocomplete="false">
                                @csrf
                                <table class="InputFieldStyle1">
                                    <tbody>
                                        <tr>
                                            <th>社員番号</th>
                                            <td>
                                                <input name="txtEmpCd" tabindex="1" disabled="disabled"
                                                    id="txtEmpCd" style="width: 80px;" type="text"
                                                    maxlength="10" value="{{ $emp_data->EMP_CD }}">
                                                <input type="hidden" name="txtEmpCd" value="{{ $emp_data->EMP_CD }}">
                                                <span id="lblEmpName"
                                                    style="width: 230px; display: inline-block;">
                                                    {{ $emp_data->EMP_NAME }}
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>ログインID</th>
                                            <td>
                                                <input name="txtLoginId" tabindex="2" id="txtLoginId" autocomplete="new-password"
                                                    style="width: 80px; ime-mode:disabled;" type="text" maxlength="10" autofocus
                                                    oninput="value = ngVerticalBar(value)" onfocus="this.select();"
                                                    value="{{ old('txtLoginId',!empty($request_data['txtLoginId']) ? $request_data['txtLoginId'] : $login_datas->LOGIN_ID ?? '') }}">
                                                @if ($errors->has('txtLoginId'))
                                                    <span class="text-danger">{{ $errors->first('txtLoginId') }}</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>変更後パスワード</th>
                                            <td>
                                                <input name="txtNewPassword" tabindex="3" onfocus="this.select();" autocomplete="new-password"
                                                    id="txtNewPassword" style="width: 90px;" type="password" maxlength="10"
                                                    oninput="value = ngVerticalBar(value)">

                                                @if ($errors->has('txtNewPassword'))
                                                    <span
                                                        class="text-danger">{{ $errors->first('txtNewPassword') }}</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>パスワード再入力</th>
                                            <td>
                                                <input name="txtNewPassword2" tabindex="4" onfocus="this.select();"
                                                    id="txtNewPassword2" style="width: 90px;" type="password"
                                                    maxlength="10">
                                                @if ($errors->has('txtNewPassword2'))
                                                    <span
                                                        class="text-danger">{{ $errors->first('txtNewPassword2') }}</span>
                                                @endif

                                            </td>
                                        </tr>
                                        <tr>
                                            <th>機能権限</th>
                                            <td>
                                                <select name="ddlPgAuth" tabindex="5" id="ddlPgAuth" style="width: 170px;"
                                                    value="{{ old('ddlPgAuth', !empty($auth_cd['PG_AUTH_CD']) ? $auth_cd['PG_AUTH_CD'] : '') }}">

                                                    <option value=""></option>
                                                    @foreach ($pg_auth->unique('PG_AUTH_CD') as $auth_cd)
                                                        <option value="{{ $auth_cd->PG_AUTH_CD }}"
                                                            {{ ($auth_cd->PG_AUTH_CD == $emp_data->PG_AUTH_CD && !empty($login_datas->LOGIN_ID) ? 'selected' : '') ??old('ddlPgAuth') }}>
                                                            {{ $auth_cd->PG_AUTH_NAME }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @if ($errors->has('ddlPgAuth'))
                                                    <span
                                                        class="text-danger">{{ $errors->first('ddlPgAuth') }}</span>
                                                @endif
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                                <div class="line"></div>
                                <p class="ButtonField1"><input name="btnSearch" id="btnSearch" tabindex="6"
                                        class="SearchButton submit-form" type="button" value="更新"
                                        data-url="{{ route('MT11LoginEdit.update', ['id' => $emp_data->EMP_CD]) }}"
                                        onclick="return checkSubmit(this)">
                                    <input name="btnCancel" tabindex="7" id="btnCancel" onclick="location.reload();"
                                        type="button" value="キャンセル">
                                </p>

                            </form>
                        </div>

                    </td>
                </tr>
            </tbody>
        </table>
    </div>
@endsection
@section('script')
    <script>
        // キャンセルボタン
        function Cancel() {
            $("#txtEmpCd, #txtEmpKana, #txtDeptCd, #deptName").val('');
        }

        // 全角なら自動で半角へ変更
        $(document).on("change", 'input', function() {
            let str = $(this).val()
            str = str.replace(/[Ａ-Ｚａ-ｚ０-９]/g, function(s) {
                return String.fromCharCode(s.charCodeAt(0) - 0xFEE0);
            });
            str = str.replace(/[^!-~]/g, "");
            $(this).val(str);
        });

        // 入力不可能文字：|
        ngVerticalBar = n => n.replace(/\|/g, '').replace(/[｜]/g, '');

        // 確認ダイアル
        function checkSubmit() {
            if (window.confirm("{{ getArrValue($error_messages, '1005') }}")) {
                // formボタンクリック
                $(document).on('click', '.submit-form', function() {
                    var url = $(this).data('url');
                    $('#form').attr('action', url);
                    $('#form').submit();
                });
            } else {
                return false;
            }
        }
    </script>
@endsection
