<!-- 所属情報入力 -->
@extends('menu.main')

@section('title', '所属情報入力')

@section('content')
    <div id="contents-stage">
        <table class="BaseContainerStyle1">
            <tbody>
                <tr>
                    <td>
                        <div id="UpdatePanel1">
                            <p class="FunctionMenu1">
                                <a id="AddSplyer" href="{{ route('MT23.registerIndex') }}">新規所属登録</a>
                            </p>
                            <div class="line"></div>
                            <div class="GridViewStyle1">
                                <table style="border-collapse: separate;">
                                    <tbody>
                                        <tr>
                                            <th>
                                                所属
                                            </th>
                                            <th>
                                                所属
                                            </th>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="GridViewStyle1">
                                <div>
                                    {{ csrf_field() }}
                                    @if (session('err_msg'))
                                        <p class="text-danger">
                                            {{ session('err_msg') }}
                                        </p>
                                    @endif
                                    <table tabindex="7" class="GridViewSpace" id="gvEmp" style="border-collapse: collapse;"
                                        border="1" rules="all" cellspacing="0">
                                        <tbody>
                                            @if (!empty($haken_company))
                                                @for($i = 0; $i < count($haken_company) && $i < 20; $i++)
                                                <tr>
                                                    <td class="col-sm-4">
                                                        <a href="{{ url('master/MT23CompanyEditor/'. $haken_company[$i]->COMPANY_CD )}}">
                                                            {{ $haken_company[$i]->COMPANY_CD }} : {{ $haken_company[$i]->COMPANY_NAME }}
                                                        </a>
                                                    </td>
                                                    <td class="col-sm-4">
                                                        @if($haken_company[$i + 20] != null )
                                                        <a href="{{ url('master/MT23CompanyEditor/'. $haken_company[$i + 20]->COMPANY_CD )}}">
                                                            {{ $haken_company[$i + 20]->COMPANY_CD }} : {{ $haken_company[$i + 20]->COMPANY_NAME }}
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
                            <div class="line"></div>
                            <div class="d-flex justify-content-center">
                                {{ $haken_company->appends(request()->input())->render('pagination::bootstrap-4') }}
                            </div>
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
    </script>
@endsection
