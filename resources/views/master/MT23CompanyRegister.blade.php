<!-- 所属情報登録 -->
@extends('menu.main')

@section('title', '所属情報登録')

@section('content')
    <div id="contents-stage">
        <table class="BaseContainerStyle1">
            <tbody>
                <tr>
                    <td>
                        <div id="UpdatePanel1">
                            <form action="{{ route('MT23.companyRegister') }}" method="POST" enctype="multipart/form-data" id="form">
                                @csrf
                                <table class="InputFieldStyle1">
                                    <tbody>
                                        <tr>
                                            <th>所属番号</th>
                                            <td>
                                                <input name="regCompanyCd" tabindex="1" oninput="value = onlyNumbers(value)"
                                                    id="regCompanyCd" style="width: 80px;" type="text" maxlength="6" onfocus="this.select();"
                                                    autofocus>
                                                @if ($errors->has('regCompanyCd'))
                                                    <span class="text-danger">
                                                        {{ getArrValue($error_messages, $errors->first('regCompanyCd')) }}
                                                    </span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>所属先名</th>
                                            <td>
                                                <input name="regCompanyName" tabindex="2"
                                                    id="regCompanyName" style="width: 370px;" onfocus="this.select();"
                                                    type="text" maxlength="20" oninput="value = ngVerticalBar(value)">
                                                @if ($errors->has('regCompanyName'))
                                                    <span class="text-danger">
                                                        {{ getArrValue($error_messages, $errors->first('regCompanyName')) }}
                                                    </span>
                                                @endif

                                            </td>
                                        </tr>
                                        <tr>
                                            <th>所属先カナ名</th>
                                            <td>
                                                <input name="regCompanyKana" tabindex="3"
                                                    id="regCompanyKana" style="width: 370px;" onfocus="this.select();"
                                                    type="text" maxlength="20">
                                                @if ($errors->has('regCompanyKana'))
                                                    <span class="text-danger">
                                                        {{ getArrValue($error_messages, $errors->first('regCompanyKana')) }}
                                                    </span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>所属先略名</th>
                                            <td>
                                                <input name="regCompanyAbr" tabindex="4"
                                                    id="regCompanyAbr" style="width: 180px;" onfocus="this.select();"
                                                    type="text" maxlength="10">
                                                @if ($errors->has('regCompanyAbr'))
                                                    <span class="text-danger">
                                                        {{ getArrValue($error_messages, $errors->first('regCompanyAbr')) }}
                                                    </span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>郵便番号</th>
                                            <td>
                                                <input name="txtPostCd" tabindex="5" id="txtPostCd"
                                                    style="width: 70px;" onfocus="this.select();" type="text" maxlength="8"
                                                    oninput="value = onlyNubersBar(value)">
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>住所１</th>
                                            <td>
                                                <input name="txtAddress1" tabindex="6"
                                                    id="txtAddress1" style="width: 430px;" onfocus="this.select();"
                                                    type="text" maxlength="30">
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>住所２</th>
                                            <td>
                                                <input name="txtAddress2" tabindex="7"
                                                    id="txtAddress2" style="width: 430px;" onfocus="this.select();"
                                                    type="text" maxlength="30">
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>住所３</th>
                                            <td>
                                                <input name="txtAddress3" tabindex="8"
                                                    id="txtAddress3" style="width: 430px;" onfocus="this.select();"
                                                    type="text" maxlength="30">
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>電話番号</th>
                                            <td>
                                                <input name="txtTel" tabindex="9" id="txtTel"
                                                    style="width: 120px;" onfocus="this.select();" type="text"
                                                    maxlength="15" oninput="value = onlyNubersBarParen(value)">
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>ＦＡＸ番号</th>
                                            <td>
                                                <input name="txtFax" tabindex="10" id="txtFax"
                                                    style="width: 120px;" onfocus="this.select();" type="text"
                                                    maxlength="15" oninput="value = onlyNubersBarParen(value)">
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>表示区分</th>
                                            <td>
                                                <select name="ddlDispCls" tabindex="11" id="ddlDispCls"
                                                    style="width: 100px;">
                                                    @foreach ($dispcls_cd as $dispclsCd)
                                                        <option value="{{ $dispclsCd->CLS_DETAIL_CD }}"
                                                            {{ $dispclsCd->CLS_DETAIL_CD == '01' ? 'selected' : '' }}>
                                                            {{ $dispclsCd->CLS_DETAIL_NAME }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <span id="cvDispCls" style="color: red; display: none;">
                                                    ErrorMessage
                                                </span>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>

                                <div class="line"></div>
                                <div class="ButtonField1">
                                    <input type="submit" class="ButtonStyle1" id="btnUpdate" name="btnUpdate"
                                        tabindex="12" value="更新" onclick="return checkSubmit(this)">
                                    <input name="btnCancel" tabindex="13" id="btnCancel"
                                        onclick="location.href='MT23CompanyReference'" type="button" value="キャンセル">
                                </div>
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
        $(document).on('click', '.submit-form', function() {
            var url = $(this).data('url');
            $('#form').attr('action', url);
            $('#form').submit();
        });

        // ENTER時に送信されないようにする
        $(function() {
            $('input').not('[type="button"]').not('[type="submit"]').keypress(function(e) {
                if (e.which == 13) {
                    return false;
                }
            });
        });

        // 確認ダイアル
        function checkSubmit() {
            if (window.confirm("{{ getArrValue($error_messages, '1005') }}")) {
                return true;
            } else {
                return false;
            }
        }

        $(function() {
            // 所属番号英数半角のみ入力可
            onlyNumbers = n => n.replace(/[０-９]/g, s => String.fromCharCode(s.charCodeAt(0) - 65248))
                .replace(/\D/g, '');

            // 入力可能文字：数値、ハイフン
            onlyNubersBar = n => n.replace(/[０-９]/g, s => String.fromCharCode(s.charCodeAt(0) - 65248))
                .replace(/[ー]/g, '-')
                .replace(/[^-\d]/g, '');

            // 入力不可能文字：|
            ngVerticalBar = n => n.replace(/\|/g, '');

            // 入力可能文字：数値、ハイフン
            onlyNubersBarParen = n => n.replace(/[０-９（）]/g, s => String.fromCharCode(s.charCodeAt(0) - 65248))
                .replace(/[ー]/g, '-')
                .replace(/[^-()\d]/g, '');
        });
    </script>

@endsection
