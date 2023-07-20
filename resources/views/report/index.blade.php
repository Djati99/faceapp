<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Time Attendance Report</title>
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <link  href="https://cdn.datatables.net/1.10.21/css/jquery.dataTables.min.css" rel="stylesheet" type="text/css">
        <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
        <script src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" type="text/css">
        <link href="{{asset('assets/css/layout.min.css')}}" rel="stylesheet" type="text/css" />
        <link href="{{asset('assets/css/themes/default.min.css')}}" rel="stylesheet" type="text/css" id="style_color" />        
        <link href="{{asset('assets/css/custom.min.css')}}" rel="stylesheet" type="text/css" />
        <link rel="shortcut icon" type="image/png" href="{{asset('assets/img/ioi_icon.png')}}"/>
    </head>
    <body class="page-container-bg-solid page-header-fixed page-sidebar-closed-hide-logo" onafterprint="myFunction()">
        <div class="page-header navbar navbar-fixed-top">
            <div class="page-header-inner ">
                <div class="page-logo">
                    <a href="home">
                        <img src="{{asset('assets/img/logo-w-logo.png')}}" style="height:65px;width:auto; margin:4px 70px;" alt="logo" /> 
                    </a>
                </div>
                <a href="javascript: return false;" class="menu-toggler responsive-toggler" data-toggle="collapse" data-target=".navbar-collapse"> 
                </a>
                <div class="page-top">
                    <div class="top-menu">
                    </div>
                </div>
            </div>
        </div>      
        <div class="clearfix"> </div>        
        <div class="page-container" style="background:#EEE;margin : 0;">
            <div class="page-sidebar-wrapper">
                <div class="page-sidebar navbar-collapse">
                    <ul class="page-sidebar-menu" data-keep-expanded="false" data-auto-scroll="true" data-slide-speed="200">
                        <li class="nav-item ">
                            <a href="home" class="nav-link">
                                <i class="fa fa-home"></i>
                                <span class="title">Home</span>
                            </a>
                        </li>
                        <li class="nav-item ">
                            <a href="{{ route('setting.index') }}" class="nav-link nav-toggle">
                                <i class="fa fa-chart-bar"></i>
                                <span class="title">Setting</span>
                                <span class="selected"></span>
                                <span class="arrow "></span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('webreport') }}" class="nav-link">
                                <i class="fa fa-chart-bar"></i>
                                <span class="title">Report</span>
                                <span class="selected"></span>
                                <span class="arrow "></span>
                            </a>                                    
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('weblog') }}" class="nav-link">
                                <i class="fa fa-chart-bar"></i>
                                <span class="title">Log</span>
                                <span class="selected"></span>
                                <span class="arrow "></span>
                            </a>                                    
                        </li>                        
                        <li class="nav-item">
                            <a href="{{ route('sendsapindex') }}" class="nav-link">
                                <i class="fa fa-chart-bar"></i>
                                <span class="title">Send Data</span>
                                <span class="selected"></span>
                                <span class="arrow "></span>
                            </a>                                    
                        </li>                        

                    </ul>
                </div>
            </div>
            <div class="page-content-wrapper">
                <div class="page-content" style="min-height: 485px; ">
                    <div class="page-head">
                        <div class="page-title">
                            <h1>Time Attendance Report</h1>
                        </div>
                    </div>
                    <!--                    <ul class="page-breadcrumb breadcrumb">
                                            <li>
                                                <a href="home">Home</a>
                                                <i class="fa fa-circle"></i>
                                            </li>   
                                            <li>
                                                <span class="active">OPH Mill Grader</span>
                                            </li>
                                        </ul>-->

                    <div class="row">
                        <div class="col-md-12">
                        </div>
                        <div class="col-md-12">
                            <div class="portlet light form-fit bordered">
                                <!--                                <div class="portlet-body form">
                                                                    <form class="form-horizontal form-bordered" action="transactions/fr_mill_grader" method="GET">
                                                                        <div class="form-group">
                                                                            <label class="col-md-2 control-label" style="text-align:left;">Date</label>
                                                                            <div class="col-md-6">
                                                                                <div class="input-group date-picker input-daterange" data-date-format="dd-mm-yyyy" data-date-end-date="0d">
                                                                                    <input class="form-control" name="from" placeholder="From" type="text" value="09-11-2022" autocomplete="off">
                                                                                    <span class="input-group-addon"> to </span>
                                                                                    <input class="form-control" name="to" placeholder="To" type="text" value="09-11-2022" autocomplete="off"> 
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-md-3">
                                                                                <button type="submit" class="btn btn-circle btn-block  btn-outline btn-md blue"> <i class="fa fa-search"></i> Search OPH - Mill Grader
                                                                                </button>
                                                                            </div>
                                                                        </div>
                                                                    </form>
                                                                </div>-->
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="portlet light bordered">
                                <!--                                <div class="portlet-title">
                                                                    <div class="caption">
                                                                        <span class="caption-subject font-blue sbold uppercase blue">OPH - Mill Grader transaction</span>
                                                                    </div>
                                                                    <div class="actions">
                                                                        <div class="btn-group btn-group-devided">
                                                                            <form action="transactions/fr_mill_grader/export" method="post" id="form_export">
                                                                                <input type="hidden" name="csrf_test_name" value="8b33e4e3224943eda894f611655f13f3">
                                                                                <input type="hidden" name="from" value="09-11-2022">
                                                                                <input type="hidden" name="to" value="09-11-2022">
                                                                            </form>
                                                                            <button type="button" class="btn btn-outline btn-circle btn-sm blue" id="export_transaction_btn"><i class="fa fa-download"></i> Export Data</button>
                                                                        </div>
                                                                    </div>
                                                                </div>-->
                                <div class="portlet-body">
                                    <div class="table">
                                        <div id="fr_table_wrapper" class="dataTables_wrapper no-footer">
                                            <div class="table-scrollable">
                                                <table class="table table-bordered table-hover dataTable no-footer dtr-inline" id="fr_table" role="grid" aria-describedby="fr_table_info" style="width: 1014px;">
                                                    <thead>
                                                        <tr role="row">                                                           
                                                            <th class="all sorting" tabindex="0" aria-controls="fr_table" rowspan="1" colspan="1" style="width: 104px;" aria-label=" ID"> ID </th>
                                                            <th class="all sorting" tabindex="0" aria-controls="fr_table" rowspan="1" colspan="1" style="width: 51px;" aria-label=" Swipe Time"> Swipe Time </th>
                                                            <th class="all sorting" tabindex="0" aria-controls="fr_table" rowspan="1" colspan="1" style="width: 51px;" aria-label=" Personnel Code"> Personnel ID </th>
                                                            <th class="all sorting" tabindex="0" aria-controls="fr_table" rowspan="1" colspan="1" style="width: 34px;" aria-label=" Personnel Name"> Personnel Name </th>
                                                            <th class="all sorting" tabindex="0" aria-controls="fr_table" rowspan="1" colspan="1" style="width: 49px;" aria-label=" Event Name"> Event Name </th>
                                                            <th class="all sorting" tabindex="0" aria-controls="fr_table" rowspan="1" colspan="1" style="width: 42px;" aria-label=" Swipping In/Out"> Swipping In/Out </th>
                                                            <th class="all sorting" tabindex="0" aria-controls="fr_table" rowspan="1" colspan="1" style="width: 49px;" aria-label=" Card Number"> Created </th>
                                                            <!--<th class="all sorting_disabled" rowspan="1" colspan="1" style="width: 52px;" aria-label=" Action "> Action </th>-->
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr class="odd"><td colspan="9" class="dataTables_empty" valign="top">No data available in table</td></tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="page-footer">
            <div class="page-footer-inner">
                <a> FaceApi IOI V 3.3.0.3 (20230720)</a>
            </div>
            <div class="scroll-to-top" style="display: block;">
                <i class="icon-arrow-up"></i>
            </div>
        </div>        
    </body>
    <script type="text/javascript">
        function myFunction() {
            location.reload();
        }
        $(document).ready(function () {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $('#fr_table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ url('report/data') }}",
                columns: [
                    {data: 'fa_attendance_id', name: 'id'},
                    {data: 'swipetime', name: 'swipetime'},
                    {data: 'personnelcode', name: 'personnelcode'},
                    {data: 'personnelname', name: 'personnelname'},
                    {data: 'eventname', name: 'eventname'},
                    {data: 'swipdirection', name: 'swipdirection'},
                    {data: 'created_at', name: 'created_at'}
                ],
        lengthMenu: [
            [10, 25, 50, 100, -1],
            [10, 25, 50, 100, 'All'],
        ],                
                order: [[0, 'desc']]
            });
        });
    </script>
</html>