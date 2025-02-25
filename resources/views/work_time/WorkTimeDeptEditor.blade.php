<!-- 出退勤入力（部門別） -->
@extends('menu.main')

@section('title','出退勤入力(部門別)')

@section('content')
<div id="contents-stage">
    <table>
        <tbody>
            <tr>
                <td>
                    <div id="UpdatePanel1">
                        <!-- header -->
                        <form action="" method="POST" id="form" >
                            {{ csrf_field() }}
                            <table class="InputFieldStyle1">
                                <tbody>
                                    <tr>
                                        <th>対象年月日</th>
                                        <td>
                                            <input type="text"
                                                name="ddlDate"
                                                id="YearMonth"
                                                autocomplete="off"
                                                value="{{ old('ddlDate', !is_nullorwhitespace(Session::get('ymd_date')) ? Session::get('ymd_date'): date('Y年m月d日') ) }}"
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
                                        <th>部門</th>
                                        <td>
                                            <input type="text"
                                                name="txtDeptCd"
                                                id="txtDeptCd"
                                                class="searchDeptCd txtDeptCd"
                                                style="width: 50px;"
                                                maxlength="6"
                                                onfocus="this.select();"
                                                value="{{ old('txtDeptCd', !is_nullorwhitespace($input_search_data['txtDeptCd']) ? $input_search_data['txtDeptCd'] : '') }}"
                                                oninput="value = onlyHalfNumWord(value)"
                                                autocomplete="off"
                                                @if(!empty($results))
                                                disabled="disabled"
                                                @endif
                                            >
                                            <input name="btnSearchDeptCd"
                                                class="SearchButton"
                                                id="btnSearchDeptCd"
                                                type="button"
                                                value="?"
                                                onclick="SearchDept(this);return false"
                                                @if(!empty($results))
                                                disabled="disabled"
                                                @endif
                                            >
                                            <input type="text"
                                                name="deptName"
                                                id="deptName"
                                                class="txtDeptName"
                                                style="width: 200px; display: inline-block;"
                                                value="{{ old('deptName') }}"
                                                data-dispclscd=01 data-isdeptauth=true
                                                readonly="readonly"
                                                @if(!empty($results))
                                                disabled="disabled"
                                                @endif
                                            >
                                            @error('txtDeptCd')
                                            <span class="text-danger" id="DeptCdValidError">{{ getArrValue($error_messages, $message) }}</span>
                                            @enderror
                                            <span class="text-danger" id="deptNameError">
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>開始所属</th>
                                        <td>
                                            <select name="filter[ddlStartCompany]"
                                                id="ddlStartCompany"
                                                style="width: 300px;"
                                                @if(!empty($results))
                                                disabled="disabled"
                                                @endif
                                            >
                                                <option value=""></option>
                                                @isset($haken_company)
                                                @foreach ($haken_company as $companyName)
                                                    <option value="{{ $companyName->COMPANY_CD }}"
                                                        {{ old('filter.ddlStartCompany', !empty($filterData['ddlStartCompany']) ? $filterData['ddlStartCompany'] : '') == $companyName->COMPANY_CD ? 'selected' : '' }}>
                                                        {{ $companyName->COMPANY_ABR }}
                                                    </option>
                                                @endforeach
                                                @endisset
                                            </select>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>終了所属</th>
                                        <td>
                                            <select name="filter[ddlEndCompany]"
                                                id="ddlEndCompany"
                                                style="width: 300px;"
                                                @if(!empty($results))
                                                disabled="disabled"
                                                @endif
                                            >
                                                <option value=""></option>
                                                @isset($haken_company)
                                                @foreach ($haken_company as $companyName)
                                                    <option value="{{ $companyName->COMPANY_CD }}"
                                                        {{ old('filter.ddlEndCompany', !empty($filterData['ddlEndCompany']) ? $filterData['ddlEndCompany'] : '') == $companyName->COMPANY_CD ? 'selected' : '' }}>
                                                        {{ $companyName->COMPANY_ABR }}
                                                    </option>
                                                @endforeach
                                                @endisset
                                            </select>
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
                                            <input type="button"
                                                name="btnDisp"
                                                id="btnShow"
                                                class="ButtonStyle1 submit-form"
                                                style="width: 80px;"
                                                value="表示"
                                                data-url = "{{ route('wtde.search')}}"
                                                @if(!empty($results))
                                                disabled="disabled"
                                                @endif
                                            >
                                            @if(!empty($results))
                                            <input type="button"
                                                name="btnUpdate"
                                                id="btnUpdate"
                                                class="update"
                                                style="width: 80px;"
                                                value="更新"
                                                data-url = "{{ route('wtde.update')}}"
                                            >
                                            @endif
                                            <input type="button"
                                                name="btnCancel2"
                                                id="btnCancel2"
                                                class="ButtonStyle1 submit-form"
                                                style="width: 80px;"
                                                value="キャンセル"
                                                data-url = "{{ route('wtde.cancel')}}"
                                            >
                                            &nbsp;
                                            <span id="lblNoStampColor" style="background-color: tomato;">　　　</span>
                                            <span id="lblNoStamp">未打刻</span>
                                            &nbsp;
                                            <span id="lblDbStampColor" style="background-color: lightskyblue;">　　　</span>
                                            <span id="lblDbStamp">二重打刻</span>
                                            &nbsp;
                                        </td>
                                    </tr>
                                </tbody>
                            </table>

                            <!-- detail -->
                            <input type="hidden" name="hdnCvClientIdList" id="hdnCvClientIdList" value="">
                            <div class="GridViewStyle1" id="gridview-container">
                                <div class="GridViewPanelStyle1" style="width: 1084px;">
                                    <div id="pnlWorkTime">
                                        <div>
                                            <table class="GridViewBorder GridViewRowCenter GridViewPadding fixRowCol"
                                                   id="gvWorkTime" style="border-collapse: separate;" border="1" rules="all" cellspacing="0">
                                                <tbody id="gridview-warp">
                                                    @isset($results)
                                                        @if(count($results) < 1)
                                                            <tr style="width:70px;">
                                                                <td><span>{{ getArrValue($error_messages, 4029) }}</span></td>
                                                            </tr>
                                                        @else
                                                            <tr class="sticky-top">
                                                                <th class="fixedcol" scope="col" style="background: #4682B4; left: 0px;"> 部門コード </th>
                                                                <th class="fixedcol" scope="col" style="background: #4682B4; left: 80px;"> 部門名 </th>
                                                                <th class="fixedcol" scope="col" style="background: #4682B4; left: 210px;"> 社員番号 </th>
                                                                <th class="fixedcol" scope="col" style="background: #4682B4; left: 290px;"> 社員名 </th>
                                                                <th class="fixedcol" scope="col" style="background: #4682B4; left: 420px;"> 勤務体系 </th>
                                                                <th class="fixedcol" scope="col" style="background: #4682B4; left: 570px;"> 事由 </th>
                                                                <th scope="col"> 出勤 </th>
                                                                <th scope="col"> 退出 </th>
                                                                <th scope="col"> 外出1 </th>
                                                                <th scope="col"> 再入１ </th>
                                                                <th scope="col"> 外出２ </th>
                                                                <th scope="col"> 再入２ </th>
                                                                <th scope="col"> 出勤時間 </th>
                                                                <th scope="col"> 遅刻時間 </th>
                                                                <th scope="col"> 早退時間 </th>
                                                                <th scope="col"> 外出時間 </th>
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
                                                                <th scope="col"> 備考 </th>
                                                            </tr>
                                                            @foreach($results->unique('EMP_CD') as $i=>$result)
                                                                <tr>
                                                                    <td class="fixedcol" style="width: 80px; left: 0px;">
                                                                        <span id="lblDeptCd" style="width: 80px; display: inline-block;">{{ $result->DEPT_CD }}</span>
                                                                        <input type="hidden" name="worktime[{{ $i }}][DEPT_CD]" value="{{ $result->DEPT_CD }}"/>
                                                                    </td>
                                                                    <td class="fixedcol" style="width: 130px; left: 80px;">
                                                                        <span id="lblDeptName" style="width: 130px; display: inline-block;">{{ $result->DEPT_NAME }}</span>
                                                                        <input type="hidden" name="worktime[{{ $i }}][DEPT_NAME]" value="{{ $result->DEPT_NAME }}"/>
                                                                    </td>
                                                                    <td class="fixedcol" style="width: 80px; left: 210px;" >
                                                                        <span id="txtEmpCd" class="txtEmpCd" style="width: 80px; display: inline-block;">{{ $result->EMP_CD }}</span>
                                                                        <input type="hidden" name="worktime[{{ $i }}][EMP_CD]" value="{{ $result->EMP_CD }}"/>
                                                                    </td>
                                                                    <td class="fixedcol" style="width: 130px; left: 290px;">
                                                                        <span id="lblEmpName" style="width: 130px; display: inline-block;">{{ $result->EMP_NAME }}</span>
                                                                        <input type="hidden" name="worktime[{{ $i }}][EMP_NAME]" value="{{ $result->EMP_NAME }}"/>
                                                                    </td>
                                                                    <td class="" style="position:sticky; background:#fff; width: 150px; left: 420px;">
                                                                        <select name="worktime[{{ $i }}][WORKPTN_CD]" class="workPtnCd coloredSelect" style="width: 150px;">
                                                                            @foreach ( $workptn_names as $workptn_name)
                                                                                <option value="{{ $workptn_name->WORKPTN_CD }}"
                                                                                    {{ $workptn_name->WORK_CLS_CD == '00' ? 'class=text-danger' : 'style=color:black;'}}
                                                                                    {{ $workptn_name->WORKPTN_CD == $result->WORKPTN_CD ? "selected" : "" }}
                                                                                >
                                                                                    {{$workptn_name->WORKPTN_NAME }}
                                                                                </option>
                                                                            @endforeach
                                                                        </select>
                                                                    </td>
                                                                    <td class="" style="position:sticky; background:#fff; width: 70px; left: 570px;">
                                                                        <select name="worktime[{{ $i }}][REASON_CD]" class="reasonCd coloredSelect" style="width: 70px;">
                                                                            @foreach ($reason_names as $reason_name)
                                                                                <option
                                                                                    value="{{ $reason_name->REASON_CD }}"
                                                                                    @if($reason_name->REASON_PTN_CD == '01') class="text-danger"
                                                                                    @elseif($reason_name->REASON_PTN_CD == '02') class="text-primary"
                                                                                    @else style="color:black;"
                                                                                    @endif
                                                                                    {{ $reason_name->REASON_CD == $result->REASON_CD ? "selected" : "" }}
                                                                                >
                                                                                    {{ $reason_name->REASON_NAME }}
                                                                                </option>
                                                                            @endforeach
                                                                        </select>
                                                                    </td>
                                                                    <td style="white-space: nowrap;">
                                                                        <input type="text"
                                                                            name="worktime[{{ $i }}][OFC_TIME]"
                                                                            class="ofcTime"
                                                                            maxlength="5"
                                                                            value="{{ $result->OFC_TIME }}"
                                                                            oninput="value=onlyHalfWord(value)"
                                                                            @if ( $result->OFC_CNT >= 2 && !$result->OFC_TIME_HH )
                                                                            style="width: 40px; background-color: lightskyblue;"
                                                                            @elseif ( !$result->OFC_TIME_HH && $result->LEV_TIME_HH )
                                                                            style="width: 40px; background-color: tomato;"
                                                                            @else
                                                                            style="width: 40px;"
                                                                            @endif
                                                                        >
                                                                        <span class="text-danger timeError"></span>
                                                                    </td>
                                                                    <td style="white-space: nowrap;">
                                                                        <input type="text"
                                                                            name="worktime[{{ $i }}][LEV_TIME]"
                                                                            class="levTime"
                                                                            maxlength="5"
                                                                            value="{{ $result->LEV_TIME }}"
                                                                            oninput="value=onlyHalfWord(value)"
                                                                            @if ( $result->LEV_CNT >= 2 && !$result->LEV_TIME_HH )
                                                                            style="width: 40px; background-color: lightskyblue;"
                                                                            @elseif ( $result->OFC_TIME_HH && !$result->LEV_TIME )
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
                                                                        <input type="text"
                                                                            name="worktime[{{ $i }}][OUT1_TIME]"
                                                                            class="out1Time"
                                                                            maxlength="5"
                                                                            value="{{ $result->OUT1_TIME }}"
                                                                            oninput="value=onlyHalfWord(value)"
                                                                            @if ( $result->OUT1_CNT >= 2 && !$result->OUT1_TIME_HH )
                                                                            style="width: 40px; background-color: lightskyblue;"
                                                                            @elseif ( !$result->OUT1_TIME_HH && $result->IN1_TIME_HH )
                                                                            style="width: 40px; background-color: tomato;"
                                                                            @else
                                                                            style="width: 40px;"
                                                                            @endif
                                                                        >
                                                                        <span class="text-danger timeError"></span>
                                                                    </td>
                                                                    <td style="white-space: nowrap;">
                                                                        <input type="text"
                                                                            name="worktime[{{ $i }}][IN1_TIME]"
                                                                            class="in1Time"
                                                                            maxlength="5"
                                                                            value="{{ $result->IN1_TIME }}"
                                                                            oninput="value=onlyHalfWord(value)"
                                                                            @if ( $result->IN1_CNT >= 2 && !$result->IN1_TIME_HH )
                                                                            style="width: 40px; background-color: lightskyblue;"
                                                                            @elseif ( $result->OUT1_TIME_HH && !$result->IN1_TIME_HH )
                                                                            style="width: 40px; background-color: tomato;"
                                                                            @else
                                                                            style="width: 40px;"
                                                                            @endif
                                                                        >
                                                                        <span class="text-danger timeError"></span>
                                                                    </td>
                                                                    <td style="white-space: nowrap;">
                                                                        <input type="text"
                                                                            name="worktime[{{ $i }}][OUT2_TIME]"
                                                                            class="out2Time"
                                                                            maxlength="5"
                                                                            value="{{ $result->OUT2_TIME }}"
                                                                            oninput="value=onlyHalfWord(value)"
                                                                            @if ( $result->OUT2_CNT >= 2 && !$result->OUT2_TIME_HH )
                                                                            style="width: 40px; background-color: lightskyblue;"
                                                                            @elseif ( !$result->OUT2_TIME_HH && $result->IN2_TIME_HH )
                                                                            style="width: 40px; background-color: tomato;"
                                                                            @else
                                                                            style="width: 40px;"
                                                                            @endif
                                                                        >
                                                                        <span class="text-danger timeError"></span>
                                                                    </td>
                                                                    <td style="white-space: nowrap;">
                                                                        <input type="text"
                                                                            name="worktime[{{ $i }}][IN2_TIME]"
                                                                            class="in2Time"
                                                                            maxlength="5"
                                                                            value="{{ $result->IN2_TIME }}"
                                                                            oninput="value=onlyHalfWord(value)"
                                                                            @if ( $result->IN2_CNT >= 2 && !$result->IN2_TIME_HH )
                                                                            style="width: 40px; background-color: lightskyblue;"
                                                                            @elseif ( $result->OUT2_TIME_HH && !$result->IN2_TIME_HH )
                                                                            style="width: 40px; background-color: tomato;"
                                                                            @else
                                                                            style="width: 40px;"
                                                                            @endif
                                                                        >
                                                                        <span class="text-danger timeError"></span>
                                                                    </td>
                                                                    <td style="white-space: nowrap;">
                                                                        <input type="text"
                                                                            name="worktime[{{ $i }}][WORK_TIME]"
                                                                            class="workTime noCalc"
                                                                            style="width: 40px;"
                                                                            maxlength="5"
                                                                            value="{{ $result->WORK_TIME }}"
                                                                            oninput="value=onlyHalfWord(value)"
                                                                        >
                                                                        <span class="text-danger timeError"></span>
                                                                    </td>
                                                                    <td style="white-space: nowrap;">
                                                                        <input type="text"
                                                                            name="worktime[{{ $i }}][TARD_TIME]"
                                                                            class="tardTime noCalc"
                                                                            style="width: 40px;"
                                                                            maxlength="5"
                                                                            value="{{ $result->TARD_TIME }}"
                                                                            oninput="value=onlyHalfWord(value)"
                                                                        >
                                                                        <span class="text-danger timeError"></span>
                                                                    </td>
                                                                    <td style="white-space: nowrap;">
                                                                        <input type="text"
                                                                            name="worktime[{{ $i }}][LEAVE_TIME]"
                                                                            class="leaveTime noCalc"
                                                                            style="width: 40px;"
                                                                            maxlength="5"
                                                                            value="{{ $result->LEAVE_TIME }}"
                                                                            oninput="value=onlyHalfWord(value)"
                                                                        >
                                                                        <span class="text-danger timeError"></span>
                                                                    </td>
                                                                    <td style="white-space: nowrap;">
                                                                        <input type="text"
                                                                            name="worktime[{{ $i }}][OUT_TIME]"
                                                                            class="outTime noCalc"
                                                                            style="width: 40px;"
                                                                            maxlength="5"
                                                                            value="{{ $result->OUT_TIME }}"
                                                                            oninput="value=onlyHalfWord(value)"
                                                                        >
                                                                        <span class="text-danger timeError"></span>
                                                                    </td>
                                                                    <td style="white-space: nowrap;">
                                                                        <input type="text"
                                                                            name="worktime[{{ $i }}][OVTM1_TIME]"
                                                                            class="ovtm1Time noCalc"
                                                                            style="width: 40px;"
                                                                            maxlength="5"
                                                                            value="{{ $result->OVTM1_TIME }}"
                                                                            oninput="value=onlyHalfWord(value)"
                                                                        >
                                                                        <span class="text-danger timeError"></span>
                                                                    </td>
                                                                    <td style="white-space: nowrap;">
                                                                        <input type="text"
                                                                            name="worktime[{{ $i }}][OVTM2_TIME]"
                                                                            class="ovtm2Time noCalc"
                                                                            style="width: 40px;"
                                                                            maxlength="5"
                                                                            value="{{ $result->OVTM2_TIME }}"
                                                                            oninput="value=onlyHalfWord(value)"
                                                                        >
                                                                        <span class="text-danger timeError"></span>
                                                                    </td>
                                                                    <td style="white-space: nowrap;">
                                                                        <input type="text"
                                                                            name="worktime[{{ $i }}][OVTM3_TIME]"
                                                                            class="ovtm3Time noCalc"
                                                                            style="width: 40px;"
                                                                            maxlength="5"
                                                                            value="{{ $result->OVTM3_TIME }}"
                                                                            oninput="value=onlyHalfWord(value)"
                                                                        >
                                                                        <span class="text-danger timeError"></span>
                                                                    </td>
                                                                    <td style="white-space: nowrap;">
                                                                        <input type="text"
                                                                            name="worktime[{{ $i }}][OVTM4_TIME]"
                                                                            class="ovtm4Time noCalc"
                                                                            style="width: 40px;"
                                                                            maxlength="5"
                                                                            value="{{ $result->OVTM4_TIME }}"
                                                                            oninput="value=onlyHalfWord(value)"
                                                                        >
                                                                        <span class="text-danger timeError"></span>
                                                                    </td>
                                                                    <td style="white-space: nowrap;">
                                                                        <input type="text"
                                                                            name="worktime[{{ $i }}][OVTM5_TIME]"
                                                                            class="ovtm5Time noCalc"
                                                                            style="width: 40px;"
                                                                            maxlength="5"
                                                                            value="{{ $result->OVTM5_TIME }}"
                                                                            oninput="value=onlyHalfWord(value)"
                                                                        >
                                                                        <span class="text-danger timeError"></span>
                                                                    </td>
                                                                    <td style="white-space: nowrap;">
                                                                        <input type="text"
                                                                            name="worktime[{{ $i }}][OVTM6_TIME]"
                                                                            class="ovtm6Time noCalc"
                                                                            style="width: 40px;"
                                                                            maxlength="5"
                                                                            value="{{ $result->OVTM6_TIME }}"
                                                                            oninput="value=onlyHalfWord(value)"
                                                                        >
                                                                        <span class="text-danger timeError"></span>
                                                                    </td>
                                                                    <td style="white-space: nowrap;">
                                                                        <input type="text"
                                                                            name="worktime[{{ $i }}][EXT1_TIME]"
                                                                            class="ext1Time noCalc"
                                                                            style="width: 40px;"
                                                                            maxlength="5"
                                                                            value="{{ $result->EXT1_TIME }}"
                                                                            oninput="value=onlyHalfWord(value)"
                                                                        >
                                                                        <span class="text-danger timeError"></span>
                                                                    </td>
                                                                    <td style="white-space: nowrap;">
                                                                        <input type="text"
                                                                            name="worktime[{{ $i }}][EXT2_TIME]"
                                                                            class="ext2Time noCalc"
                                                                            style="width: 40px;"
                                                                            maxlength="5"
                                                                            value="{{ $result->EXT2_TIME }}"
                                                                            oninput="value=onlyHalfWord(value)"
                                                                        >
                                                                        <span class="text-danger timeError"></span>
                                                                    </td>
                                                                    <td style="white-space: nowrap;">
                                                                        <input type="text"
                                                                            name="worktime[{{ $i }}][EXT3_TIME]"
                                                                            class="ext3Time noCalc"
                                                                            style="width: 40px;"
                                                                            maxlength="5"
                                                                            value="{{ $result->EXT3_TIME }}"
                                                                            oninput="value=onlyHalfWord(value)"
                                                                        >
                                                                        <span class="text-danger timeError"></span>
                                                                    </td>
                                                                    <td class="GridViewRowLeft" style="white-space: nowrap;">
                                                                        <input type="text"
                                                                            name="worktime[{{ $i }}][REMARK]"
                                                                            class="remark"
                                                                            style="width: 250px;"
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
                            </div>
                            <!-- ErrorMessage -->
                            @isset($results)
                            <span class="text-danger" id="timeFormatError"></span><br>
                            <span class="text-danger" id="sizeError"></span>
                            @endisset
                            <!-- footer -->
                            <div class="line">
                                <hr>
                            </div>
                            <p class="ButtonField2">
                                <input name="btnCancel2"
                                    class="ButtonStyle1 submit-form"
                                    id="btnCancel2"
                                    type="button" value="キャンセル"
                                    data-url = "{{ route('wtde.cancel')}}"
                                    style="width: 80px;"
                                >
                            </p>
                        </form>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>
</div>
<input type="hidden" id="timeCalUrl" value="{{ url('work_time/WorkTimeDeptEditorTimeCal/')}}">
<input type="hidden" id="dayCalUrl" value="{{ url('work_time/WorkTimeDeptEditorDayCal/')}}">
<input type="hidden" id="formatError" value="{{ getArrValue($error_messages, 2003) }}">
<input type="hidden" id="dataSizeError" value="{{ getArrValue($error_messages, 2009) }}">
@endsection
@section('script')
<script src="{{ asset('js/work_time/WorkTimeEditor.js') }}" defer></script>
<script>
var dept_cd = "{{$input_search_data['txtDeptCd']}}";
// ボタンクリック
$(document).on('click', '.submit-form', function(){
    var url = $(this).data('url');
    $('#form').attr('action', url);
    $('#form').submit();
});

// 更新submit-form
$(document).on('click', '.update', function() {
    if (window.confirm("{{ getArrValue($error_messages, 1005) }}")) {
        var url = $(this).data('url');
        $('#form').attr('action', url);
        $('#form').submit();
    }
    return false;
});

$(function() {
    $('#YearMonth').datepicker({
        format: 'yyyy年mm月dd日',
        autoclose: true,
        language: 'ja',
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
});

</script>

@endsection
