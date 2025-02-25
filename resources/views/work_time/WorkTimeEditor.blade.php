<!-- 出退勤入力画面 -->
@extends('menu.main')

@section('title','出退勤入力')
@section('content')
<div id="contents-stage">
    <table>
        <tbody>
            <tr>
                <td>
                    <div id="UpdatePanel1">
                        <!-- header -->
                        <form action="" method="post" id="form">
                            {{ csrf_field() }}
                            <table class="InputFieldStyle1">
                                <tbody>
                                    <tr>
                                        <th>対象月度</th>
                                        <td>
                                            <input name="ddlDate"
                                            id="ddlDate"
                                            type="text"
                                            autocomplete="off"
                                            value="{{ old('ddlDate', !is_nullorwhitespace(Session::get('date')) ? Session::get('date') : date('Y年m月') ) }}"
                                            @if(!empty($results))
                                            disabled="disabled"
                                            @else
                                            autofocus
                                            @endif
                                            />
                                            @error('ddlDate')
                                            <span class="text-danger">{{ getArrValue($error_messages, $message) }}</span>
                                            @enderror
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>社員番号</th>
                                        <td>
                                            <input name="txtEmpCd"
                                                id="txtEmpCd"
                                                class="searchEmpCd txtEmpCd"
                                                style="width: 80px;"
                                                type="text"
                                                maxlength="10"
                                                value="{{ old('txtEmpCd', !is_nullorwhitespace($search_data['txtEmpCd']) ? $search_data['txtEmpCd'] : '') }}"
                                                oninput="value = onlyHalfNumWord(value)"
                                                autocomplete="off"
                                                @if(!empty($results))
                                                disabled="disabled"
                                                @endif
                                                >
                                            <input name="btnSearchEmpCd"
                                                class="SearchButton"
                                                id="btnSearchEmpCd"
                                                type="button"
                                                value="?"
                                                onclick="SearchEmp(this);return false"
                                                @if(!empty($results))
                                                disabled="disabled"
                                                @endif
                                            >
                                            <input name="empName"
                                                class="OutlineLabel txtEmpName"
                                                type="text"
                                                style="width: 200px; display: inline-block;"
                                                id="empName"
                                                value="{{ old('empName') }}"
                                                readonly="readonly"
                                                data-regclscd=00 data-isdeptauth=true
                                                @if(!empty($results))
                                                disabled="disabled"
                                                @endif
                                            >
                                            @error('txtEmpCd')
                                            <span class="text-danger" id="EmpCdValidError">{{ getArrValue($error_messages, $message) }}</span>
                                            @enderror
                                            <span class="text-danger" id="EmpCdError"></span>
                                        </td>
                                        <td>

                                        </td>
                                    </tr>
                                    <tr>
                                        <th>部門</th>
                                        <td>
                                        <input name ="deptName"
                                            class="OutlineLabel"
                                            type="text"
                                            style="width: 200px; display: inline-block;"
                                            id="deptNameWithEmp"
                                            value="{{ old('deptName') }}"
                                            readonly="readonly"
                                            @if(!empty($results))
                                            disabled="disabled"
                                            @endif
                                        >
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            <div class="line">
                                <hr>
                            </div>
                            <table>
                                <tbody>
                                    <tr>
                                        <td style="width: auto;">
                                            <input
                                                name="btnDisp"
                                                id="btnShow"
                                                class="ButtonStyle1 submit-form"
                                                type="button"
                                                value="表示"
                                                data-url = "{{ route('wte.search')}}"
                                                onclick="return (!$('#empCdError').text())"
                                                @if(!empty($results))
                                                disabled="disabled"
                                                @endif
                                            >
                                            @if(!empty($results))
                                                <input
                                                name="btnEdit"
                                                id="btnUpdate"
                                                type="button"
                                                value="更新"
                                                class="ButtonStyle1 update"
                                                data-url = "{{ route('wte.update')}}"
                                            >
                                            @endif
                                            <input
                                                name="btnCancel2"
                                                class="ButtonStyle1 submit-form"
                                                id="btnCancel2"
                                                type="button"
                                                value="キャンセル"
                                                data-url = "{{ route('wte.cancel')}}"
                                            >
                                            &nbsp;
                                            <span id="lblNoStampColor" style="background-color: tomato;">　　　</span>
                                            <span id="lblNoStamp">未打刻</span>
                                            &nbsp;
                                            <span id="lblDbStampColor" style="background-color: lightskyblue;">　　　</span>
                                            <span id="lblDbStamp">二重打刻</span>
                                            &nbsp;
                                            @isset($results)
                                            @if(count($results) > 1)
                                            @if(isset($confirmCheck))
                                            <span class="font-style2" id="lblFixMsg">{{ config('consts.fix_msg')}}</span>
                                            @endif
                                            @endif
                                            @endisset
                                        </td>
                                        <td class="right">
                                            <span class="font-style1" id="lblDispCaldDate"></span>
                                            &nbsp;
                                            <span class="font-style1" id="lblDispOfcTime"></span>
                                            &nbsp;
                                            <span class="font-style1" id="lblDispLevTime"></span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            <!-- detail -->
                            <input name="hdnCvClientIdList" id="hdnCvClientIdList" type="hidden" value="">
                            <div class="GridViewStyle1" id="gridview-container">
                                <div class="GridViewPanelStyle1">
                                    <div id="pnlWorkTime">
                                        <div>
                                            <table class="GridViewBorder GridViewRowCenter GridViewPadding fixRowCol" id="gvWorkTime" style="border-collapse: separate;" border="1" rules="all" cellspacing="0">
                                                <tbody id="gridview-warp">
                                                    @isset($results)
                                                        @if(count($results) < 1)
                                                            <tr style="width:70px;">
                                                                <td><span>{{ $messages }}</span></td>
                                                            </tr>
                                                        @else
                                                            <input type="hidden" name="empCd" value="{{ $emp_cd }}">
                                                            <tr class="sticky-head">
                                                                <th class="fixedcol" scope="col" style="background: #4682B4; left: 0px;">
                                                                    日付
                                                                </th>
                                                                <th class="fixedcol" scope="col" style="background: #4682B4; left: 80px;">
                                                                    曜日
                                                                </th>
                                                                <th class="fixedcol" scope="col" style="background: #4682B4; left: 110px;">
                                                                    勤務体系
                                                                </th>
                                                                <th class="fixedcol" scope="col" style="background: #4682B4; left: 260px;">
                                                                    事由
                                                                </th>
                                                                <th scope="col">
                                                                    出勤
                                                                </th>
                                                                <th scope="col">
                                                                    退出
                                                                </th>
                                                                <th scope="col">
                                                                    外出1
                                                                </th>
                                                                <th scope="col">
                                                                    再入１
                                                                </th>
                                                                <th scope="col">
                                                                    外出２
                                                                </th>
                                                                <th scope="col">
                                                                    再入２
                                                                </th>
                                                                <th scope="col">
                                                                    出勤時間
                                                                </th>
                                                                <th scope="col">
                                                                    遅刻時間
                                                                </th>
                                                                <th scope="col">
                                                                    早退時間
                                                                </th>
                                                                <th scope="col">
                                                                    外出時間
                                                                </th>
                                                                @for ($i = 0; $i < 6; $i++)
                                                                <th scope="col">
                                                                    @if(key_exists($i, $ovtm_header_names))
                                                                    {{ $ovtm_header_names[$i]['WORK_DESC_NAME'] }}
                                                                    @endif
                                                                </th>
                                                                @endfor
                                                                @for ($i = 0; $i < 3; $i++)
                                                                <th scope="col">
                                                                    @if(key_exists($i, $ext_header_names))
                                                                    {{ $ext_header_names[$i]['WORK_DESC_NAME'] }}
                                                                    @endif
                                                                </th>
                                                                @endfor
                                                                <th scope="col">
                                                                    備考
                                                                </th>
                                                            </tr>
                                                            @foreach($results->unique('CALD_DATE') as $i=>$result)
                                                            <tr>
                                                                <td class="fixedcol" style="width: 80px; left: 0px;">
                                                                    <span id="lblCaldDate" class="{{ in_array(date('md', strtotime($result->CALD_DATE)), $holidays) || config('consts.weeks')[date('w', strtotime($result->CALD_DATE))] == '土' || config('consts.weeks')[date('w', strtotime($result->CALD_DATE))] == '日'? 'text-danger':''}}" style="width: 80px; display: inline-block;">
                                                                        {{ date('Y/m/d', strtotime($result->CALD_DATE)) }}
                                                                    </span>
                                                                    <input type="hidden" class="calDate" name="worktime[{{ $i }}][CALD_DATE]" value="{{ date('Y-m-d', strtotime($result->CALD_DATE)) }}"/>
                                                                </td>
                                                                <td class="fixedcol dayOfWeek" style="width: 30px; left: 80px;">
                                                                    <span id="lblDayOfWeek" class="{{ in_array(date('md', strtotime($result->CALD_DATE)), $holidays) || config('consts.weeks')[date('w', strtotime($result->CALD_DATE))] == '土' || config('consts.weeks')[date('w', strtotime($result->CALD_DATE))] == '日'? 'text-danger':''}}" style="width: 30px; display: inline-block;">
                                                                        {{ config('consts.weeks')[date('w', strtotime($result->CALD_DATE))] }}
                                                                    </span>
                                                                </td>
                                                                <td class="" style="position:sticky; background:#fff; border-right: 1px solid #aaa; width: 150px; left: 110px;">
                                                                    <select
                                                                        name="worktime[{{ $i }}][WORKPTN_CD]"
                                                                        id="txtWorkPtnCd"
                                                                        class="workPtnCd coloredSelect"
                                                                        style="width: 150px;"
                                                                    >
                                                                        @foreach ( $workptn_names as $workptn_name)
                                                                            <option
                                                                                value="{{ $workptn_name->WORKPTN_CD }}"
                                                                                {{ $workptn_name->WORK_CLS_CD == '00' ? 'class=text-danger' : 'style=color:black;'}}
                                                                                {{ $workptn_name->WORKPTN_NAME == $result->WORKPTN_NAME ? "selected" : "" }}
                                                                            >
                                                                                {{$workptn_name->WORKPTN_NAME }}
                                                                            </option>
                                                                        @endforeach
                                                                    </select>
                                                                </td>
                                                                <td class="" style="position:sticky; background:#fff; border-right: 1px solid #aaa; width: 70px; left: 260px;">
                                                                    <select
                                                                        name="worktime[{{ $i }}][REASON_CD]"
                                                                        id="txtReasonCd"
                                                                        class="reasonCd coloredSelect"
                                                                        style="width: 70px;"
                                                                    >
                                                                        @foreach ($reason_names as $reason_name)
                                                                            <option
                                                                                value="{{ $reason_name->REASON_CD }}"
                                                                                @if($reason_name->REASON_PTN_CD == '01') class="text-danger"
                                                                                @elseif($reason_name->REASON_PTN_CD == '02') class="text-primary"
                                                                                @else style="color:black;"
                                                                                @endif
                                                                                {{ $reason_name->REASON_NAME == $result->REASON_NAME ? "selected" : "" }}
                                                                            >
                                                                                {{ $reason_name->REASON_NAME }}
                                                                            </option>
                                                                        @endforeach
                                                                    </select>
                                                                </td>
                                                                <td style="white-space: nowrap;">
                                                                    <input
                                                                        name="worktime[{{ $i }}][OFC_TIME]"
                                                                        class="ofcTime"
                                                                        id="txtOfcTime"
                                                                        type="text"
                                                                        maxlength="5"
                                                                        oninput="value=onlyHalfWord(value)"
                                                                        value="{{ $result->OFC_TIME }}"
                                                                        @if ( $result->OFC_CNT >= 2 && !$result->OFC_TIME_HH )
                                                                        style="width: 40px; background-color: lightskyblue;"
                                                                        @elseif ( !$result->OFC_TIME_HH && $result->LEV_TIME_HH )
                                                                        style="width: 40px; background-color: tomato;"
                                                                        @elseif ( !$result->OFC_TIME_HH && !$result->LEV_TIME_HH )
                                                                        style="width: 40px;"
                                                                        @else
                                                                        style="width: 40px;"
                                                                        @endif
                                                                    >
                                                                    <span class="text-danger timeError"></span>
                                                                </td>
                                                                <td style="white-space: nowrap;">
                                                                    <input
                                                                        name="worktime[{{ $i }}][LEV_TIME]"
                                                                        class="levTime"
                                                                        id="txtLevTime"
                                                                        type="text"
                                                                        maxlength="5"
                                                                        oninput="value=onlyHalfWord(value)"
                                                                        value="{{ $result->LEV_TIME }}"
                                                                        @if ( $result->LEV_CNT >= 2 && !$result->LEV_TIME_HH )
                                                                        style="width: 40px; background-color: lightskyblue;"
                                                                        @elseif ( $result->OFC_TIME_HH && !($result->LEV_TIME_HH) )
                                                                        style="width: 40px; background-color: tomato;"
                                                                        @elseif ( !$result->OFC_TIME_HH && !$result->LEV_TIME_HH )
                                                                        style="width: 40px;"
                                                                        @else
                                                                        style="width: 40px;"
                                                                        @endif
                                                                    >
                                                                    <span class="text-danger timeError"></span>
                                                                </td>
                                                                <td style="white-space: nowrap;">
                                                                    <input
                                                                        name="worktime[{{ $i }}][OUT1_TIME]"
                                                                        class="out1Time"
                                                                        id="txtOut1Time"
                                                                        type="text"
                                                                        maxlength="5"
                                                                        oninput="value=onlyHalfWord(value)"
                                                                        value="{{ $result->OUT1_TIME }}"
                                                                        @if ( $result->OUT1_CNT >= 2 && !$result->OUT1_TIME_HH )
                                                                        style="width: 40px; background-color: lightskyblue;"
                                                                        @elseif ( !$result->OUT1_TIME_HH && $result->IN1_TIME_HH )
                                                                        style="width: 40px; background-color: tomato;"
                                                                        @elseif ( !$result->OUT1_TIME_HH && !$result->IN1_TIME_HH )
                                                                        style="width: 40px;"
                                                                        @else
                                                                        style="width: 40px;"
                                                                        @endif
                                                                    >
                                                                    <span class="text-danger timeError"></span>
                                                                </td>
                                                                <td style="white-space: nowrap;">
                                                                    <input
                                                                        name="worktime[{{ $i }}][IN1_TIME]"
                                                                        class="in1Time"
                                                                        id="txtIn1Time"
                                                                        type="text"
                                                                        maxlength="5"
                                                                        oninput="value=onlyHalfWord(value)"
                                                                        value="{{ $result->IN1_TIME }}"
                                                                        @if ( $result->IN1_CNT >= 2 && !$result->IN1_TIME_HH )
                                                                        style="width: 40px; background-color: lightskyblue;"
                                                                        @elseif ( $result->OUT1_TIME_HH && !($result->IN1_TIME_HH) )
                                                                        style="width: 40px; background-color: tomato;"
                                                                        @elseif ( !$result->OUT1_TIME_HH && !$result->IN1_TIME_HH )
                                                                        style="width: 40px;"
                                                                        @else
                                                                        style="width: 40px;"
                                                                        @endif
                                                                    >
                                                                    <span class="text-danger timeError"></span>
                                                                </td>
                                                                <td style="white-space: nowrap;">
                                                                    <input
                                                                        name="worktime[{{ $i }}][OUT2_TIME]"
                                                                        class="out2Time"
                                                                        id="txtOut2Time"
                                                                        type="text"
                                                                        maxlength="5"
                                                                        oninput="value=onlyHalfWord(value)"
                                                                        value="{{ $result->OUT2_TIME }}"
                                                                        @if ( $result->OUT2_CNT >= 2 && !$result->OUT2_TIME_HH )
                                                                        style="width: 40px; background-color: lightskyblue;"
                                                                        @elseif ( !$result->OUT2_TIME_HH && $result->IN2_TIME_HH )
                                                                        style="width: 40px; background-color: tomato;"
                                                                        @elseif ( !$result->OUT2_TIME_HH && !$result->IN2_TIME_HH )
                                                                        style="width: 40px;"
                                                                        @else
                                                                        style="width: 40px;"
                                                                        @endif
                                                                    >
                                                                    <span class="text-danger timeError"></span>
                                                                </td>
                                                                <td style="white-space: nowrap;">
                                                                    <input
                                                                        name="worktime[{{ $i }}][IN2_TIME]"
                                                                        class="in2Time"
                                                                        id="txtIn2Time"
                                                                        type="text"
                                                                        maxlength="5"
                                                                        oninput="value=onlyHalfWord(value)"
                                                                        value="{{ $result->IN2_TIME }}"
                                                                        @if ( $result->IN2_CNT >= 2 && !$result->IN2_TIME_HH )
                                                                        style="width: 40px; background-color: lightskyblue;"
                                                                        @elseif ( $result->OUT2_TIME_HH && !($result->IN2_TIME_HH) )
                                                                        style="width: 40px; background-color: tomato;"
                                                                        @elseif ( !$result->OUT2_TIME_HH && !$result->IN2_TIME_HH )
                                                                        style="width: 40px;"
                                                                        @else
                                                                        style="width: 40px;"
                                                                        @endif
                                                                    >
                                                                    <span class="text-danger timeError"></span>
                                                                </td>
                                                                <td style="white-space: nowrap;">
                                                                    <input
                                                                        name="worktime[{{ $i }}][WORK_TIME]"
                                                                        class="workTime noCalc"
                                                                        id="txtWorkTime"
                                                                        style="width: 40px;"
                                                                        type="text"
                                                                        maxlength="5"
                                                                        oninput="value=onlyHalfWord(value)"
                                                                        value="{{ $result->WORK_TIME }}"
                                                                    >
                                                                    <span class="text-danger timeError"></span>
                                                                </td>
                                                                <td style="white-space: nowrap;">
                                                                    <input
                                                                        name="worktime[{{ $i }}][TARD_TIME]"
                                                                        class="tardTime noCalc"
                                                                        id="txtTardTime"
                                                                        style="width: 40px;"
                                                                        type="text"
                                                                        maxlength="5"
                                                                        oninput="value=onlyHalfWord(value)"
                                                                        value="{{ $result->TARD_TIME }}"
                                                                    >
                                                                    <span class="text-danger timeError"></span>
                                                                </td>
                                                                <td style="white-space: nowrap;">
                                                                    <input
                                                                        name="worktime[{{ $i }}][LEAVE_TIME]"
                                                                        class="leaveTime noCalc"
                                                                        id="txtLeaveTime"
                                                                        style="width: 40px;"
                                                                        type="text"
                                                                        maxlength="5"
                                                                        oninput="value=onlyHalfWord(value)"
                                                                        value="{{ $result->LEAVE_TIME }}"
                                                                    >
                                                                    <span class="text-danger timeError"></span>
                                                                </td>
                                                                <td style="white-space: nowrap;">
                                                                    <input
                                                                        name="worktime[{{ $i }}][OUT_TIME]"
                                                                        class="outTime noCalc"
                                                                        id="txtOutTime"
                                                                        style="width: 40px;"
                                                                        type="text"
                                                                        maxlength="5"
                                                                        oninput="value=onlyHalfWord(value)"
                                                                        value="{{ $result->OUT_TIME }}"
                                                                    >
                                                                    <span class="text-danger timeError"></span>
                                                                </td>
                                                                <td style="white-space: nowrap;">
                                                                    <input
                                                                        name="worktime[{{ $i }}][OVTM1_TIME]"
                                                                        class="ovtm1Time noCalc"
                                                                        id="txtOvtm1Time"
                                                                        style="width: 40px;"
                                                                        type="text"
                                                                        maxlength="5"
                                                                        oninput="value=onlyHalfWord(value)"
                                                                        value="{{ $result->OVTM1_TIME }}"
                                                                    >
                                                                    <span class="text-danger timeError"></span>
                                                                </td>
                                                                <td style="white-space: nowrap;">
                                                                    <input
                                                                        name="worktime[{{ $i }}][OVTM2_TIME]"
                                                                        class="ovtm2Time noCalc"
                                                                        id="txtOvtm2Time"
                                                                        style="width: 40px;"
                                                                        type="text"
                                                                        maxlength="5"
                                                                        oninput="value=onlyHalfWord(value)"
                                                                        value="{{ $result->OVTM2_TIME }}"
                                                                    >
                                                                    <span class="text-danger timeError"></span>
                                                                </td>
                                                                <td style="white-space: nowrap;">
                                                                    <input
                                                                        name="worktime[{{ $i }}][OVTM3_TIME]"
                                                                        class="ovtm3Time noCalc"
                                                                        id="txtOvtm3Time"
                                                                        style="width: 40px;"
                                                                        type="text"
                                                                        maxlength="5"
                                                                        oninput="value=onlyHalfWord(value)"
                                                                        value="{{ $result->OVTM3_TIME }}"
                                                                    >
                                                                    <span class="text-danger timeError"></span>
                                                                </td>
                                                                <td style="white-space: nowrap;">
                                                                    <input
                                                                        name="worktime[{{ $i }}][OVTM4_TIME]"
                                                                        class="ovtm4Time noCalc"
                                                                        id="txtOvtm4Time"
                                                                        style="width: 40px;"
                                                                        type="text"
                                                                        maxlength="5"
                                                                        oninput="value=onlyHalfWord(value)"
                                                                        value="{{ $result->OVTM4_TIME }}"
                                                                    >
                                                                    <span class="text-danger timeError"></span>
                                                                </td>
                                                                <td style="white-space: nowrap;">
                                                                    <input
                                                                        name="worktime[{{ $i }}][OVTM5_TIME]"
                                                                        class="ovtm5Time noCalc"
                                                                        id="txtOvtm5Time"
                                                                        style="width: 40px;"
                                                                        type="text"
                                                                        maxlength="5"
                                                                        oninput="value=onlyHalfWord(value)"
                                                                        value="{{ $result->OVTM5_TIME }}"
                                                                    >
                                                                    <span class="text-danger timeError"></span>
                                                                </td>
                                                                <td style="white-space: nowrap;">
                                                                    <input
                                                                        name="worktime[{{ $i }}][OVTM6_TIME]"
                                                                        class="ovtm6Time noCalc"
                                                                        id="txtOvtm6Time"
                                                                        style="width: 40px;"
                                                                        type="text"
                                                                        maxlength="5"
                                                                        oninput="value=onlyHalfWord(value)"
                                                                        value="{{ $result->OVTM6_TIME }}"
                                                                    >
                                                                    <span class="text-danger timeError"></span>
                                                                </td>
                                                                <td style="white-space: nowrap;">
                                                                    <input
                                                                        name="worktime[{{ $i }}][EXT1_TIME]"
                                                                        class="ext1Time noCalc"
                                                                        id="txtExt1Time"
                                                                        style="width: 40px;"
                                                                        type="text"
                                                                        maxlength="5"
                                                                        oninput="value=onlyHalfWord(value)"
                                                                        value="{{ $result->EXT1_TIME }}"
                                                                    >
                                                                    <span class="text-danger timeError"></span>
                                                                </td>
                                                                <td style="white-space: nowrap;">
                                                                    <input
                                                                        name="worktime[{{ $i }}][EXT2_TIME]"
                                                                        class="ext2Time noCalc"
                                                                        id="txtExt2Time"
                                                                        style="width: 40px;"
                                                                        type="text"
                                                                        maxlength="5"
                                                                        oninput="value=onlyHalfWord(value)"
                                                                        value="{{ $result->EXT2_TIME }}"
                                                                    >
                                                                    <span class="text-danger timeError"></span>
                                                                </td>
                                                                <td style="white-space: nowrap;">
                                                                    <input
                                                                        name="worktime[{{ $i }}][EXT3_TIME]"
                                                                        class="ext3Time noCalc"
                                                                        id="txtExt3Time"
                                                                        style="width: 40px;"
                                                                        type="text"
                                                                        maxlength="5"
                                                                        oninput="value=onlyHalfWord(value)"
                                                                        value="{{ $result->EXT3_TIME }}"
                                                                    >
                                                                    <span class="text-danger timeError"></span>
                                                                </td>
                                                                <td class="GridViewRowLeft" style="white-space: nowrap;">
                                                                    <input
                                                                        name="worktime[{{ $i }}][REMARK]"
                                                                        class="remark"
                                                                        id="txtRemark"
                                                                        style="width: 250px;"
                                                                        type="text"
                                                                        maxlength="30"
                                                                        value="{{ $result->REMARK }}"
                                                                    >
                                                                </td>
                                                            </tr>
                                                            @endforeach
                                                        @endif
                                                    @endisset
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <!-- ErrorMessage -->
                                    <span class="text-danger" id="timeFormatError"></span><br>
                                    <span class="text-danger" id="sizeError"></span>
                                </div>
                                <br>
                                <!-- footer -->
                                <div class="GridViewStyle3">
                                @if(Session::has('id'))
                                    <table>
                                        <tbody>
                                            <tr>
                                                <th>出勤</th>
                                                <th>休出</th>
                                                <th>特休</th>
                                                <th>有休</th>
                                                <th>欠勤</th>
                                                <th>代休</th>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <span id="lblSumWorkdayCnt" style="display: inline-block;">@isset($workdaycnt){{ ($workdaycnt['SUM_WORKDAY_CNT'] == 0 ? '' :($workdaycnt['SUM_WORKDAY_CNT'] ? $workdaycnt['SUM_WORKDAY_CNT'] : '')) }} @endisset</span>
                                                </td>
                                                <td>
                                                    <span id="lblSumHolworkCnt" style="display: inline-block;">@isset($holdaycnt){{ ($holdaycnt['SUM_HOLWORK_CNT'] == 0 ? '' :($holdaycnt['SUM_HOLWORK_CNT'] ? $holdaycnt['SUM_HOLWORK_CNT'] : '')) }} @endisset</span>
                                                </td>
                                                <td>
                                                    <span id="lblSumSpcholCnt" style="display: inline-block;">@isset($spcholcnt){{ ($spcholcnt['SUM_SPCHOL_CNT'] == 0 ? '' :($spcholcnt['SUM_SPCHOL_CNT'] ? $spcholcnt['SUM_SPCHOL_CNT'] : '')) }} @endisset</span>
                                                </td>
                                                <td>
                                                    <span id="lblSumPadholCnt" style="display: inline-block;">@isset($padholcnt){{ ($padholcnt['SUM_PADHOL_CNT'] == 0 ? '' :($padholcnt['SUM_PADHOL_CNT'] ? $padholcnt['SUM_PADHOL_CNT'] : '')) }} @endisset</span>
                                                </td>
                                                <td>
                                                    <span id="lblSumAbcworkCnt" style="display: inline-block;">@isset($abcworkcnt){{ ($abcworkcnt['SUM_ABCWORK_CNT'] == 0 ? '' :($abcworkcnt['SUM_ABCWORK_CNT'] ? $abcworkcnt['SUM_ABCWORK_CNT'] : '')) }} @endisset</span>
                                                </td>
                                                <td>
                                                    <span id="lblSumCompdayCnt" style="display: inline-block;">@isset($compdaycnt){{ ($compdaycnt['SUM_COMPDAY_CNT'] == 0 ? '' :($compdaycnt['SUM_COMPDAY_CNT'] ? $compdaycnt['SUM_COMPDAY_CNT'] : '')) }} @endisset</span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>出勤時間</th>
                                                <th>遅刻時間</th>
                                                <th>早退時間</th>
                                                <th>外出時間</th>
                                                @for ($i = 0; $i < 6; $i++)
                                                <th>
                                                    @if(key_exists($i, $ovtm_header_names))
                                                    {{$ovtm_header_names[$i]['WORK_DESC_NAME']}}
                                                    @endif
                                                </th>
                                                @endfor
                                                <th>合計</th>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <span id="lblSumWorkTime" style="display: inline-block;">@isset($worktime){{ ($worktime['SUM_WORK_TIME'] == '0:00' ? '' : ($worktime['SUM_WORK_TIME'] ? $worktime['SUM_WORK_TIME'] : '')) }} @endisset</span>
                                                </td>
                                                <td>
                                                    <span id="lblSumTardTime" style="display: inline-block;">@isset($tardtime){{ ($tardtime['SUM_TARD_TIME'] == '0:00' ? '' : ($tardtime['SUM_TARD_TIME'] ? $tardtime['SUM_TARD_TIME'] : '')) }} @endisset</span>
                                                </td>
                                                <td>
                                                    <span id="lblSumLeaveTime" style="display: inline-block;">@isset($leavetime){{ ($leavetime['SUM_LEAVE_TIME'] == '0:00' ? '' : ($leavetime['SUM_LEAVE_TIME'] ? $leavetime['SUM_LEAVE_TIME'] : '')) }} @endisset</span>
                                                </td>
                                                <td>
                                                    <span id="lblSumOut1Time" style="display: inline-block;">@isset($outtime){{ ($outtime['SUM_OUT_TIME'] == '0:00' ? '' : ($outtime['SUM_OUT_TIME'] ? $outtime['SUM_OUT_TIME'] : '')) }} @endisset</span>
                                                </td>
                                                <td>
                                                    <span id="lblSumOvtm1Time" style="display: inline-block;">@isset($ovtm1time){{ ($ovtm1time['SUM_OVTM1_TIME'] == '0:00' ? '' : ($ovtm1time['SUM_OVTM1_TIME'] ? $ovtm1time['SUM_OVTM1_TIME'] : '')) }} @endisset</span>
                                                </td>
                                                <td>
                                                    <span id="lblSumOvtm2Time" style="display: inline-block;">@isset($ovtm2time){{ ($ovtm2time['SUM_OVTM2_TIME'] == '0:00' ? '' : ($ovtm2time['SUM_OVTM2_TIME'] ? $ovtm2time['SUM_OVTM2_TIME'] : '')) }} @endisset</span>
                                                </td>
                                                <td>
                                                    <span id="lblSumOvtm3Time" style="display: inline-block;">@isset($ovtm3time){{ ($ovtm3time['SUM_OVTM3_TIME'] == '0:00' ? '' : ($ovtm3time['SUM_OVTM3_TIME'] ? $ovtm3time['SUM_OVTM3_TIME'] : '')) }} @endisset</span>
                                                </td>
                                                <td>
                                                    <span id="lblSumOvtm4Time" style="display: inline-block;">@isset($ovtm4time){{ ($ovtm4time['SUM_OVTM4_TIME'] == '0:00' ? '' : ($ovtm4time['SUM_OVTM4_TIME'] ? $ovtm4time['SUM_OVTM4_TIME'] : '')) }} @endisset</span>
                                                </td>
                                                <td>
                                                    <span id="lblSumOvtm5Time" style="display: inline-block;">@isset($ovtm5time){{ ($ovtm5time['SUM_OVTM5_TIME'] == '0:00' ? '' : ($ovtm5time['SUM_OVTM5_TIME'] ? $ovtm5time['SUM_OVTM5_TIME'] : '')) }} @endisset</span>
                                                </td>
                                                <td>
                                                    <span id="lblSumOvtm6Time" style="display: inline-block;">@isset($ovtm6time){{ ($ovtm6time['SUM_OVTM6_TIME'] == '0:00' ? '' : ($ovtm6time['SUM_OVTM6_TIME'] ? $ovtm6time['SUM_OVTM6_TIME'] : '')) }} @endisset</span>
                                                </td>
                                                <td>
                                                    <span id="lblSumTimes" style="display: inline-block;">@isset($getSumTimes){{ ($getSumTimes['SUM_TIMES'] == '0:00' ? '' : ($getSumTimes['SUM_TIMES'] ? $getSumTimes['SUM_TIMES'] : '')) }} @endisset</span>
                                                </td>
                                            </tr>
                                            <tr>
                                                @for ($i = 0; $i < 3; $i++)
                                                <th scope="col">
                                                    @if(key_exists($i, $ext_header_names))
                                                    {{ $ext_header_names[$i]['WORK_DESC_NAME'] }}
                                                    @endif
                                                </th>
                                                @endfor
                                                <th>合計</th>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <span id="lblSumExt1Time" style="display: inline-block;">@isset($ext1time){{ ($ext1time['SUM_EXT1_TIME'] == '0:00' ? '' : ($ext1time['SUM_EXT1_TIME'] ? $ext1time['SUM_EXT1_TIME'] : '')) }} @endisset</span>
                                                </td>
                                                <td>
                                                    <span id="lblSumExt2Time" style="display: inline-block;">@isset($ext2time){{ ($ext2time['SUM_EXT2_TIME'] == '0:00' ? '' : ($ext2time['SUM_EXT2_TIME'] ? $ext2time['SUM_EXT2_TIME'] : '')) }} @endisset</span>
                                                </td>
                                                <td>
                                                    <span id="lblSumExt3Time" style="display: inline-block;">@isset($ext3time){{ ($ext3time['SUM_EXT3_TIME'] == '0:00' ? '' : ($ext3time['SUM_EXT3_TIME'] ? $ext3time['SUM_EXT3_TIME'] : '')) }} @endisset</span>
                                                </td>
                                                <td>
                                                    <span id="lblSumExtTimes" style="display: inline-block;">@isset($getSumExtTimes){{ ($getSumExtTimes['SUM_EXT_TIMES'] == '0:00' ? '' : ($getSumExtTimes['SUM_EXT_TIMES'] ? $getSumExtTimes['SUM_EXT_TIMES'] : '')) }} @endisset</span>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                @endif
                                </div>
                                <div class="line"><hr></div>
                                <p class="ButtonField2">
                                    <input
                                        name="btnCancel2"
                                        class="ButtonStyle1 submit-form"
                                        id="btnCancel2"
                                        type="button"
                                        value="キャンセル"
                                        data-url = "{{ route('wte.cancel')}}"
                                    >
                                </p>
                            </div>
                        </form>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>
</div>
<input type="hidden" id="timeCalUrl" value="{{ url('/work_time/WorkTimeEditorTimeCal')}}">
<input type="hidden" id="dayCalUrl" value="{{ url('/work_time/WorkTimeEditorDayCal')}}">
<input type="hidden" id="formatError" value="{{ getArrValue($error_messages, 2003) }}">
<input type="hidden" id="dataSizeError" value="{{ getArrValue($error_messages, 2009) }}">
@endsection
@section('script')
<script>
var emp_cd = "{{$search_data['txtEmpCd']}}";

// formボタンクリック
$(document).on('click', '.submit-form', function(){
    var url = $(this).data('url');
    $('#form').attr('action', url);
    $('#form').submit();
});

// 更新の時ダイアログ
$(document).on('click', '.update', function() {
    if (window.confirm("{{ getArrValue($error_messages, 1005) }}")) {
        var url = $(this).data('url');
        $('#form').attr('action', url);
        $('#form').submit();
    }
    return false;
});

$(function() {
    $('#ddlDate').datepicker({
        format: 'yyyy年mm月',
        autoclose: true,
        language: 'ja',
        minViewMode : 1
    });

    // プルダウンの色設定
    var changeColor = function() {
        $(".coloredSelect").each(function(i,e){
            $(e).css('color', $(e).children("option:selected").css("color"))
        });
    };
    changeColor();
    $(".coloredSelect").on('change', (ele) => {
        $(ele.target).css('color', $(ele.target).children("option:selected").css("color"))
    });
})
</script>
<script src="{{ asset('js/work_time/WorkTimeEditor.js') }}" defer></script>
@endsection
