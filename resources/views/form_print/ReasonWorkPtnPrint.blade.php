<!-- 事由／勤怠一覧表画面 -->
@extends('menu.main')

@section('title', '事由／勤怠一覧表')

@section('content')
    <div id="contents-stage">
        <table class="BaseContainerStyle2">
            <tbody>
                <tr>
                    <td>
                        <div id="UpdatePanel1">
                            <form action="" method="post" id="form">
                                {{ csrf_field() }}
                                <table class="InputFieldStyle1">
                                    <tbody>
                                        <tr>
                                            <th>帳票区分</th>
                                            <td>
                                                <div class="GroupBox1">
                                                    <input type="radio"
                                                        name="ReportCategory"
                                                        id="rbReason"
                                                        class="rbReason"
                                                        tabindex="1"
                                                        value="rbReason"
                                                        {{ old('ReportCategory',!empty( $input_datas['ReportCategory']) ? $input_datas['ReportCategory'] : '') == 'rbReason'? 'checked': '' }}
                                                        @if(empty( $input_datas['ReportCategory']))
                                                        checked
                                                        @endif
                                                    >
                                                    <label for="rbReason" style="padding: 1.5px 0 0 3px;">事由</label>
                                                    <input type="radio"
                                                        name="ReportCategory"
                                                        id="rbWorkPtn"
                                                        class="rbWorkPtn"
                                                        tabindex="2"
                                                        value="rbWorkPtn"
                                                        {{ old('ReportCategory',!empty( $input_datas['ReportCategory']) ? $input_datas['ReportCategory'] : '') == 'rbWorkPtn'? 'checked': '' }}
                                                    >
                                                    <label for="rbWorkPtn" style="padding: 1.5px 0 0 3px;">勤怠</label>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                                <table class="InputFieldStyle1">
                                    <tbody>
                                        <tr>
                                            <th>出力区分</th>
                                            <td>
                                                <div class="GroupBox1">
                                                    <input type="radio"
                                                        name="OutputCls"
                                                        id="rbDateRange"
                                                        class="rbDateRange"
                                                        checked="checked"
                                                        tabindex="3"
                                                        value="rbDateRange"
                                                        {{ old('OutputCls',!empty( $input_datas['OutputCls']) ? $input_datas['OutputCls'] : '') == 'rbDateRange'? 'checked': '' }}
                                                        @if(empty( $input_datas['OutputCls']))
                                                        checked
                                                        @endif
                                                    >
                                                    <label for="rbDateRange" style="padding: 1.5px 0 0 3px;">日付範囲</label>
                                                    <input type="radio"
                                                        name="OutputCls"
                                                        id="rbMonthCls"
                                                        class="rbMonthCls"
                                                        tabindex="4"
                                                        value="rbMonthCls"
                                                        {{ old('OutputCls',!empty( $input_datas['OutputCls']) ? $input_datas['OutputCls'] : '') == 'rbMonthCls'? 'checked': '' }}
                                                    >
                                                    <label for="rbMonthCls" style="padding: 1.5px 0 0 3px;">月度</label>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                                <table class="InputFieldStyle1">
                                    <tbody>
                                    <tr>
                                        <th>開始対象日</th>
                                        <td>
                                            <input type="text"
                                                    name="filter[startDate]"
                                                    id="YearMonth"
                                                    class="date"
                                                    autocomplete="off"
                                                    onfocus="this.select();"
                                                    tabindex="5"
                                                    value="{{ old('filter.startDate') }}"
                                                    @isset($input_datas)
                                                    @if($input_datas['OutputCls'] == 'rbMonthCls')
                                                    disabled
                                                    @endif
                                                    @endisset
                                            />
                                            @error('filter.startDate')
                                            <span class="text-danger Date-Error">{{ getArrValue($error_messages, $errors->first('filter.startDate')) }}</span>
                                            @enderror
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>終了対象日</th>
                                        <td>
                                            <input type="text"
                                                    name="filter[endDate]"
                                                    id="YearMonth"
                                                    class="date"
                                                    autocomplete="off"
                                                    onfocus="this.select();"
                                                    tabindex="6"
                                                    value="{{ old('filter.endDate') }}"
                                                    @isset($input_datas)
                                                    @if($input_datas['OutputCls'] == 'rbMonthCls' )
                                                    disabled
                                                    @endif
                                                    @endisset
                                            />
                                            @error('filter.endDate')
                                            <span class="text-danger Date-Error">{{ getArrValue($error_messages, $errors->first('filter.endDate')) }}</span>
                                            @enderror
                                        </td>
                                    </tr>
                                        <tr>
                                            <th>対象月度</th>
                                            <td>
                                                <input type="text"
                                                    name="filter[yearMonthDate]"
                                                    tabindex="7"
                                                    id="yearMonth"
                                                    class="yearMonth"
                                                    autocomplete="off"
                                                    value="{{ old('filter.yearMonthDate') }}"
                                                    disabled
                                                    @isset($input_datas)
                                                    @if($input_datas['OutputCls'] == 'rbDateRange' )
                                                    disabled
                                                    @endif
                                                    @endisset
                                                />
                                                @error('filter.yearMonthDate')
                                                <span class="text-danger YearMonth-Error">{{ getArrValue($error_messages, $errors->first('filter.yearMonthDate')) }}</span>
                                                @enderror
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>開始部門コード</th>
                                            <td>
                                                <input type="text"
                                                    name="filter[txtStartDeptCd]"
                                                    tabindex="8"
                                                    id="txtDeptCd"
                                                    class="txtDeptCd searchDeptCd startDeptCd"
                                                    style="width: 50px;"
                                                    onfocus="this.select();"
                                                    oninput="value=onlyHalfWord(value)"
                                                    autocomplete="off"
                                                    maxlength="6"
                                                    value="{{ old('filter.txtStartDeptCd', !empty( $input_datas['filter']['txtStartDeptCd'])? $input_datas['filter']['txtStartDeptCd'] : '')}}"
                                                >
                                                <input type="button"
                                                    name="btnSearchStartDeptCd"
                                                    tabindex="9"
                                                    id="btnSearchStartDeptCd"
                                                    class="SearchButton"
                                                    onclick="SearchDept(this);return false"
                                                    value="?"
                                                >
                                                <input class="txtDeptName" id="deptName"
                                                    data-dispclscd=01 data-isdeptauth=true
                                                    style="width: 200px; display: inline-block;">
                                                <span class="text-danger" id="deptNameError"></span>
                                                @error('filter.txtStartDeptCd')
                                                <span class="text-danger" id="DeptCdValidError">{{ getArrValue($error_messages, $errors->first('filter.txtStartDeptCd')) }}</span>
                                                @enderror
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>終了部門コード</th>
                                            <td>
                                                <input type="text"
                                                    name="filter[txtEndDeptCd]"
                                                    tabindex="10"
                                                    id="txtDeptCd"
                                                    class="txtDeptCd searchDeptCd endDeptCd"
                                                    style="width: 50px;"
                                                    onfocus="this.select();"
                                                    oninput="value=onlyHalfWord(value)"
                                                    autocomplete="off"
                                                    maxlength="6"
                                                    value="{{ old('filter.txtEndDeptCd', !empty( $input_datas['filter']['txtEndDeptCd'])? $input_datas['filter']['txtEndDeptCd'] : '')}}"
                                                >
                                                <input type="button"
                                                    name="btnSearchEndDeptCd"
                                                    tabindex="11"
                                                    id="btnSearchEndDeptCd"
                                                    class="SearchButton"
                                                    onclick="SearchDept(this);return false"
                                                    value="?"
                                                >
                                                <input class="txtDeptName" id="deptName"
                                                    data-dispclscd=01 data-isdeptauth=true
                                                    style="width: 200px; display: inline-block;">
                                                <span class="text-danger" id="deptNameError"></span>
                                                @error('filter.txtEndDeptCd')
                                                <span class="text-danger" id="DeptCdValidError">{{ getArrValue($error_messages, $errors->first('filter.txtEndDeptCd')) }}</span>
                                                @enderror
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>開始社員番号 </th>
                                            <td>
                                                <input type="text"
                                                    name="filter[txtStartEmpCd]"
                                                    tabindex="12"
                                                    id="txtEmpCd"
                                                    class="txtEmpCd searchEmpCd"
                                                    style="width: 80px;"
                                                    onfocus="this.select();"
                                                    oninput="value=onlyHalfWord(value)"
                                                    autocomplete="off"
                                                    maxlength="10"
                                                    value="{{ old('filter.txtStartEmpCd', !empty( $input_datas['filter']['txtStartEmpCd'])? $input_datas['filter']['txtStartEmpCd'] : '')}}"
                                                >
                                                <input type="button"
                                                    name="btnSearchStartEmpCd"
                                                    tabindex="13"
                                                    id="btnSearchStartEmpCd"
                                                    class="SearchButton"
                                                    onclick="SearchEmp(this);return false"
                                                    value="?"
                                                >
                                                <input class="txtEmpName" id="empName"
                                                    style="width: 200px; display: inline-block;"
                                                    @if(old('filter.chkRegCls', empty($errors->all()))) data-regclscd=00 @endif
                                                    data-isdeptauth=true
                                                >
                                                <span class="text-danger" id="EmpCdError"></span>
                                                @error('filter.txtStartEmpCd')
                                                <span class="text-danger" id="EmpCdValidError">{{ getArrValue($error_messages, $errors->first('filter.txtStartEmpCd')) }}</span>
                                                @enderror
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>終了社員番号</th>
                                            <td>
                                                <input type="text"
                                                    name="filter[txtEndEmpCd]"
                                                    tabindex="14"
                                                    id="txtEmpCd"
                                                    class="txtEmpCd searchEmpCd"
                                                    style="width: 80px;"
                                                    onfocus="this.select();"
                                                    oninput="value=onlyHalfWord(value)"
                                                    autocomplete="off"
                                                    maxlength="10"
                                                    value="{{ old('filter.txtEndEmpCd', !empty( $input_datas['filter']['txtEndEmpCd'])? $input_datas['filter']['txtEndEmpCd'] : '')}}"
                                                >
                                                <input type="button"
                                                    name="btnSearchEndEmpCd"
                                                    tabindex="15"
                                                    id="btnSearchEndEmpCd"
                                                    class="SearchButton"
                                                    onclick="SearchEmp(this);return false"
                                                    value="?"
                                                >
                                                <input class="txtEmpName" id="empName"
                                                    style="width: 200px; display: inline-block;"
                                                    @if(old('filter.chkRegCls', empty($errors->all()))) data-regclscd=00 @endif
                                                    data-isdeptauth=true
                                                >
                                                <span class="text-danger" id="EmpCdError"></span>
                                                @error('filter.txtEndEmpCd')
                                                <span class="text-danger" id="EmpCdValidError">{{ getArrValue($error_messages, $errors->first('filter.txtEndEmpCd')) }}</span>
                                                @enderror
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                                <table>
                                    <tbody>
                                        <tr>
                                            <td>
                                                <input type="checkbox"
                                                    name="filter[chkRegCls]"
                                                    id="chkRegCls"
                                                    tabindex="17"
                                                    @if(old('filter.chkRegCls', empty($errors->all()))) checked="checked" @endif
                                                    style="vertical-align: middle;"
                                                >
                                                <label for="chkRegCls">在籍のみ表示</label>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>

                                <div class="line"></div>
                                <p class="ButtonField1">
                                    <input type="button"
                                        name="btnPrint"
                                        tabindex="18"
                                        id="btnPrint"
                                        class="ButtonStyle1 print"
                                        value="印刷"
                                        data-url="{{ route('ReasonWorkPtnPrint.print')}}"
                                    >
                                    <input type="button"
                                        name="btnCancel"
                                        tabindex="19"
                                        id="btnCancel"
                                        class="ButtonStyle1"
                                        onclick=" location.href='{{ route('ReasonWorkPtnPrint.index') }}' "
                                        value="キャンセル"
                                    >
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
$(function() {
    // 出力区分
    function toggleRadio(ele, first=false) {
        $(".date").prop("disabled", false);
        $(".yearMonth").prop("disabled", true);
        if (!first) {
            $(".yearMonth").val('');
            $(".YearMonth-Error").text('');
        }
        if (ele.hasClass('rbMonthCls')) {
            $(".date").prop("disabled", true);
            $(".yearMonth").prop("disabled", false);
            $(".date").val('');
            $(".Date-Error").text('');
        } else {
            $(".yearMonth").val('');
            $(".YearMonth-Error").text('');
        }
    }
    toggleRadio($("input[type='radio']:checked"), true);

    // 無効化
    $("input[type='radio'][name='OutputCls']").on('click', function() {
        toggleRadio($(this));
    })

    // 印刷
    $(document).on('click', '.print', function() {
        var noTimeStamp = $('.rbReason').prop('checked');
        var dbTimeStamp = $('.rbWorkPtn').prop('checked');
        var message = "{{ getArrValue($error_messages, 1011) }}";

        if (noTimeStamp) {
            if (window.confirm(message.replace('{0}','事由一覧表'))) {
                var url = $(this).data('url');
                $('#form').attr('action', url);
                $('#form').submit();
            }
            return false;
        }

        if (dbTimeStamp) {
            if (window.confirm(message.replace('{0}','勤怠一覧表'))) {
                var url = $(this).data('url');
                $('#form').attr('action', url);
                $('#form').submit();
            }
            return false;
        }
    });

    // 年月日
    $('.date').datepicker({
        format: 'yyyy年mm月dd日',
        autoclose: true,
        language: 'ja',
        startDate: '1900年01月01日',
        endDate: '2100年12月31日'
    });

    // 年月
    $('.yearMonth').datepicker({
        format: 'yyyy年mm月',
        autoclose: true,
        language: 'ja',
        minViewMode : 1,
        startDate: '1900年01月',
        endDate: '2100年12月'
    });

    // 入力可能文字：半角英数
    onlyHalfWord = n => n.replace(/[０-９Ａ-Ｚａ-ｚ]/g, s => String.fromCharCode(s.charCodeAt(0) - 65248))
            .replace(/[^0-9a-zA-Z]/g, '');

    $('#chkRegCls').change(ele => {
        if (ele.target.checked) {
            $(".txtEmpName").data('regclscd', '00');
        } else {
            $(".txtEmpName").data('regclscd', '')
        }
    })
})

</script>
@endsection
