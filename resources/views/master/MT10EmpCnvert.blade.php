<!-- 社員番号一括変換 -->
@extends('menu.main')

@section('title', '社員番号一括変換 ')

@section('content')
    <div id="contents-stage">
        <table class="BaseContainerStyle1">
            <tbody>
                <tr>
                    <td>
                        <div id="UpdatePanel1">
                            <form action="{{ route('MT10EmpCnvert.update') }}" method="post"
                                onsubmit="return checkSubmit()">
                                @csrf
                                <table class="InputFieldStyle1">
                                    <tbody>
                                        <tr>
                                            <th>旧社員番号</th>
                                            <td>
                                                <input name="txtEmpCd" id="txtEmpCd" class="searchEmpCd txtEmpCd" tabindex="1" onfocus="this.select();"
                                                    oninput="value = onlyHalfWord(value)" style="width: 80px;" type="text"
                                                    maxlength="10" value="{{ old('txtEmpCd') }}" autofocus>
                                                <input name="btnSearchEmpCd" class="SearchButton" id="btnSearchEmpCd"
                                                    tabindex="2" type="button" value="?" onclick="SearchEmp(this);return false">
                                                <input name="empName" type="text" data-isdeptauth=true
                                                    style="width: 200px; display: inline-block;" id="empName"
                                                    disabled="disabled" class="txtEmpName">
                                                <span class="text-danger" id="EmpCdError"></span>
                                                @if ($errors->has('txtEmpCd'))
                                                <span class="text-danger" id="EmpCdValidError">
                                                    {{ $errors->first('txtEmpCd') }}
                                                </span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>新社員番号</th>
                                            <td>
                                                <input name="txtNewEmpCd" tabindex="3" tabindex="3" onfocus="this.select();"
                                                    oninput="value = onlyHalfWord(value)" id="txtNewEmpCd"
                                                    style="width: 80px;" type="text" maxlength="10" value="{{ old('txtNewEmpCd') }}">
                                                @if ($errors->has('txtNewEmpCd'))
                                                    <span class="text-danger">
                                                        {{ $errors->first('txtNewEmpCd') }}
                                                    </span>
                                                @endif
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                                <div class="line"></div>
                                <p class="ButtonField1">
                                    <input name="btnUpdate" tabindex="4" id="btnUpdate" onclick="" type="submit" value="更新">
                                    <input name="btnCancel" tabindex="5" id="btnCancel"
                                        onclick="location.href='MT10EmpCnvert'" type="button" value="キャンセル">
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
        // キャンセル、削除処理submit-form
        $(document).on('click', '.submit-form', function() {
            var url = $(this).data('url');
            $('#form').attr('action', url);
            $('#form').submit();
        });

        function checkSubmit() {
            $checked = confirm("{{ getArrValue($error_messages, '1005') }}")
            if ($checked == true) {
                return true;
            } else {
                return false;
            }
        }
        $(function() {
            // 旧社員番号英数半角のみ入力可
            onlyHalfWord = n => n.replace(/[０-９Ａ-Ｚａ-ｚ]/g, s => String.fromCharCode(s.charCodeAt(0) - 65248))
                .replace(/[^0-9a-zA-Z]/g, '');

            // 入力不可能文字：|
            ngVerticalBar = n => n.replace(/\|/g, '');
        });
    </script>
@endsection
