<!-- ログイン情報入力 -->
@extends('menu.main')

@section('title', 'ログイン情報入力 ')

@section('content')
    <div id="contents-stage">
        <table class="BaseContainerStyle1">
            <tbody>
                <tr>
                    <td>
                        <div id="UpdatePanel1">
                            <form action="{{ route('MT11LoginRef.search') }}" method="get" id="form">
                                @csrf
                                <table class="InputFieldStyle1">
                                    <tbody>
                                        <tr>
                                            <th>社員番号</th>
                                            <td>
                                                <input name="filter[txtEmpCd]" id="txtEmpCd" onfocus="this.select();" autofocus
                                                    style="width: 80px;" type="search" style="ime-inactive;" maxlength="10"
                                                    value="{{ old('filter[txtEmpCd]', isset($search_data['txtEmpCd']) ? $search_data['txtEmpCd'] : '') }}"
                                                    oninput="value = onlyHalfWord(value)">
                                                @if ($errors->has('txtEmpCd'))
                                                    <span class="text-danger">{{ $errors->first('txtEmpCd') }}</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>社員カナ名</th>
                                            <td>
                                                <input name="filter[txtEmpKana]" id="txtEmpKana"
                                                    style="width: 160px;" type="search" maxlength="20" onfocus="this.select();"
                                                    value="{{ old('filter[txtEmpKana]', isset($search_data['txtEmpKana']) ? $search_data['txtEmpKana'] : '') }}">
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>部門</th>
                                            <td>
                                                <input name="filter[txtDeptCd]" id="txtDeptCd" class="searchDeptCd txtDeptCd" onfocus="this.select();"
                                                    style="width: 50px;" type="text" maxlength="6" style="ime-inactive;"
                                                    value="{{ old('filter[txtDeptCd]', !empty($search_data['txtDeptCd']) ? $search_data['txtDeptCd'] : '') }}"
                                                    oninput="value = onlyHalfWord(value)">
                                                <input name="btnSearchDeptCd" class="SearchButton" id="btnSearchDeptCd"
                                                    type="button" value="?" onclick="SearchDept(this);return false">
                                                <input name="deptName" type="text" class="txtDeptName" id="deptName"
                                                    style="width: 200px; height: 23px; display: inline-block;"
                                                    value="{{ old('deptName', !empty($request_data['deptName']) ? $request_data['deptName'] : '') }}"
                                                    data-dispclscd=01 data-isdeptauth=true readonly="readonly">
                                                @if ($errors->has('filter.txtDeptCd'))
                                                    <span class="text-danger">{{ $errors->first('filter.txtDeptCd') }}</span>
                                                @endif
                                                <span class="text-danger" id="deptNameError"></span>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>

                                <p class="FunctionMenu1">
                                    <input class="SearchButton" type="submit" value="検索" onclick="return (!$('#deptNameError').text())">
                                    <input name="btnCancel" class="SearchButton" id="btnCancel" type="button"
                                        onclick="Cancel()" value="キャンセル">
                                </p>

                                <div class="clearBoth"></div>

                                <div class="line"></div>

                                <ul class="HolizonListMenu1">
                                    <li><input type="submit" name="button_ALL" value="全件" onclick="return (!$('#deptNameError').text())"></li>
                                    <li><input type="submit" name="button_A" value="あ" onclick="return (!$('#deptNameError').text())"></li>
                                    <li><input type="submit" name="button_KA" value="か" onclick="return (!$('#deptNameError').text())"></li>
                                    <li><input type="submit" name="button_SA" value="さ" onclick="return (!$('#deptNameError').text())"></li>
                                    <li><input type="submit" name="button_TA" value="た" onclick="return (!$('#deptNameError').text())"></li>
                                    <li><input type="submit" name="button_NA" value="な" onclick="return (!$('#deptNameError').text())"></li>
                                    <li><input type="submit" name="button_HA" value="は" onclick="return (!$('#deptNameError').text())"></li>
                                    <li><input type="submit" name="button_MA" value="ま" onclick="return (!$('#deptNameError').text())"></li>
                                    <li><input type="submit" name="button_YA" value="や" onclick="return (!$('#deptNameError').text())"></li>
                                    <li><input type="submit" name="button_RA" value="ら" onclick="return (!$('#deptNameError').text())"></li>
                                    <li><input type="submit" name="button_WA" value="わ" onclick="return (!$('#deptNameError').text())"></li>
                                    <li><input type="submit" name="button_EN" value="英字" onclick="return (!$('#deptNameError').text())"></li>
                                </ul>
                                <div class="line"></div>
                            </form>

                            <div class="GridViewStyle1">
                                <table style="border-collapse: separate;">
                                    <tbody>
                                        <tr>
                                            <th>
                                                社員
                                            </th>
                                            <th>
                                                社員
                                            </th>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            @isset($search_results)
                                <div class="GridViewStyle1">
                                    <div>
                                        <table class="GridViewSpace" id="gvEmp" style="border-collapse: collapse;"
                                            border="1" cellspacing="0">
                                            <tbody>
                                                @if (count($search_results) < 1)
                                                    <tr style="width:70px; text-align:center;">
                                                        <td><span>{{ getArrValue($error_messages, '2000') }}</span></td>
                                                    </tr>
                                                @else
                                                @for($i = 0; $i < count($search_results) && $i < 20; $i++)
                                                <tr>
                                                    <td class="col-sm-4">
                                                        <a href="{{ url('master/MT11LoginEditor/'. $search_results[$i]->EMP_CD )}}">
                                                            {{ $search_results[$i]->EMP_CD }} : {{ $search_results[$i]->EMP_NAME }}
                                                        </a>
                                                    </td>
                                                    <td class="col-sm-4">
                                                        @if($search_results[$i + 20] != null )
                                                        <a href="{{ url('master/MT11LoginEditor/'. $search_results[$i + 20]->EMP_CD )}}">
                                                            {{ $search_results[$i + 20]->EMP_CD }} : {{ $search_results[$i + 20]->EMP_NAME }}
                                                        </a>
                                                        @endif
                                                    </td>
                                                </tr>
                                                @endfor
                                                @endif
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @endisset

                            <div class="line"></div>
                            <div class="d-flex justify-content-center" id="pegination">
                                @isset($search_results)
                                    {{ $search_results->appends(request()->query())->links() }}
                                @endisset
                            </div>
                            <div class="line"></div>
                            <p class="ButtonField1">
                                <input name="btnCancel" class="SearchButton" id="btnCancel" type="button"
                                    onclick="Cancel();" value="キャンセル">
                            </p>

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
            window.location.replace("MT11LoginReference");
        }

        $(function() {
            // 入力可能文字：半角英数
            onlyHalfWord = n => n.replace(/[０-９Ａ-Ｚａ-ｚ]/g, s => String.fromCharCode(s.charCodeAt(0) - 65248))
                .replace(/[^0-9a-zA-Z]/g, '');
        });
    </script>
@endsection
