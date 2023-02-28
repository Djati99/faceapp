<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Time Attendance Report</title>
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <link  href="https://cdn.datatables.net/1.10.21/css/jquery.dataTables.min.css" rel="stylesheet" type="text/css">
        <script src="{{asset('assets/js/jquery-3.5.1.min.js')}}"></script>
        <!--<script src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>-->
        <link href="{{asset('assets/css/bootstrap.min.css')}}" rel="stylesheet" type="text/css" />
        <link href="{{asset('assets/css/components-rounded.min.css')}}" rel="stylesheet" type="text/css" />
        <link href="{{asset('assets/css/bootstrap-datepicker3.min.css')}}" rel="stylesheet" type="text/css" />
        <link href="{{asset('assets/css/layout.min.css')}}" rel="stylesheet" type="text/css" />
        <link href="{{asset('assets/css/themes/default.min.css')}}" rel="stylesheet" type="text/css" id="style_color" />        
        <link href="{{asset('assets/css/custom.min.css')}}" rel="stylesheet" type="text/css" />
        <link rel="shortcut icon" type="image/png" href="{{asset('assets/img/ioi_icon.png')}}"/>
        <!-- DataTable -->
        <script src="{{asset('vendor/datatables/1.10.22/js/jquery.dataTables.min.js')}}" type="text/javascript"></script>
        <script src="{{asset('vendor/datatables/1.10.22/js/dataTables.bootstrap4.min.js')}}" type="text/javascript"></script>
        <script src="{{asset('vendor/datatables/buttons/1.6.5/dataTables.buttons.min.js')}}" type="text/javascript"></script>
        <script src="{{asset('vendor/datatables/buttons/1.6.5/buttons.flash.min.js')}}" type="text/javascript"></script>
        <script src="{{asset('vendor/cloudflare/ajax/libs/jszip/3.1.3/jszip.min.js')}}" type="text/javascript"></script>
        <script src="{{asset('vendor/cloudflare/ajax/libs/pdfmake/0.1.53/pdfmake.min.js')}}" type="text/javascript"></script>
        <script src="{{asset('vendor/cloudflare/ajax/libs/pdfmake/0.1.53/vfs_fonts.js')}}" type="text/javascript"></script>
        <script src="{{asset('vendor/datatables/buttons/1.6.5/buttons.html5.min.js')}}" type="text/javascript"></script>
        <script src="{{asset('vendor/datatables/buttons/1.6.5/buttons.print.min.js')}}" type="text/javascript"></script>
        <script src="{{asset('vendor/datatables/fixedcolumns/4.2.1/js/dataTables.fixedColumns.min.js')}}" type="text/javascript"></script>
        <link href="{{asset('vendor/datatables/fixedcolumns/4.2.1/css/fixedColumns.dataTables.min.css')}}" rel="stylesheet" type="text/css" />
    <style>
        #spinner-div {
            position: fixed;
            display: none;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            text-align: center;
            background-color: rgba(255, 255, 255, 0.8);
            z-index: 2;
        }
    </style>        
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
                        <li class="nav-item ">
                            <a href="/report" class="nav-link nav-toggle">
                                <i class="fa fa-chart-bar"></i>
                                <span class="title">Report</span>
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

                    <div class="row">
                        <div class="col-md-12">
                            <div class="card-header">
                                <div class="pull-left">
                                    <!--<h2 class="card-title"> Quizzes</h2>-->
                                </div>
                                <div class="pull-right">
                                    <div class="pull-left">
                                        <nav role="navigation">

                                        </nav>
                                    </div>
                                </div>
                            </div>                            
                        </div>
                        <div class="col-md-12">
                            <div class="portlet light form-fit bordered">
                                <div class="portlet-body form">
                                    <div class="row">
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label>Date</label>
                                                <div class="input-group date-picker input-daterange" data-date-format="dd-mm-yyyy">
                                                    <input class="form-control" id="enddate" name="to" placeholder="To" type="date" value="{{date('Y-m-d')}}" autocomplete="off"> 
                                                </div>   
                                            </div>
                                        </div>


                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label></label>
                                                <br>
                                                <div class="input-group">
                                                    <button type="button" class="btn btn-outline btn-circle btn-md blue doSearch"><i class="fa fa-filter"></i>Filter</button>
                                                </div>
                                            </div>
                                        </div>      
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="portlet light bordered">
                                <div class="portlet-title">
                                    <div class="caption">
                                        <span class="caption-subject font-blue sbold uppercase blue">Attendance</span>
                                    </div>
                                    <div class="actions">
                                        <div class="btn-group btn-group-devided">
                                            @csrf

                                            <!--<button type="button" data-type="1" class="btn btn-outline btn-circle btn-sm blue export_transaction_btn" id="csv_transaction_btn"><i class="fa fa-download"></i> Export CSV</button>-->
                                            <button type="button" data-type="2" class="btn btn-outline btn-circle btn-sm blue export_transaction_btn" id="excel_transaction_btn"><i class="fa fa-excel"></i> Export Excel</button>
                                            <!--<button type="button" data-type="3" class="btn btn-outline btn-circle btn-sm blue export_transaction_btn" id="pdf_transaction_btn"><i class="fa fa-pdf"></i> PDF</button>-->
                                            <button type="button" data-type="4" class="btn btn-outline btn-circle btn-sm blue export_transaction_btn" id="pdf_transaction_btn"><i class="fa fa-print"></i> Print</button>
                                        </div>
                                    </div>
                                </div>
                                <div class="portlet-body">
                                    <div class="table">
                                        <div id="fr_table_wrapper" class="dataTables_wrapper no-footer">
                                            <div class="table-scrollable">
                                                <table class="display" id="fr_table" role="grid" aria-describedby="fr_table_info" style="">
                                                    <thead>
                                                        <tr role="row">                                                           
                                                            <th class="all sorting" tabindex="0" aria-controls="fr_table" rowspan="2" colspan="1" width="300px" aria-label=" ID"> Name Of Staff </th>
                                                            <th class="all sorting" tabindex="0" aria-controls="fr_table" rowspan="2" colspan="1" width="240px" aria-label=" work_date"> Work Date </th>
                                                            <th class="all sorting" tabindex="0" aria-controls="fr_table" rowspan="1" colspan="2" style="width: 200px; text-align: center;" aria-label="Time"> Time </th>
                                                            <th class="all sorting" tabindex="0" aria-controls="fr_table" rowspan="1" colspan="2" style="width: 51px; text-align: center;" aria-label="Time"> Time </th>
                                                            <th class="all sorting" tabindex="0" aria-controls="fr_table" rowspan="1" colspan="2" style="width: 51px; text-align: center;" aria-label="Time"> Time </th>
                                                            <th class="all sorting" tabindex="0" aria-controls="fr_table" rowspan="1" colspan="2" style="width: 51px; text-align: center;" aria-label="Time"> Time </th>
                                                            <th class="all sorting" tabindex="0" aria-controls="fr_table" rowspan="1" colspan="2" style="width: 51px; text-align: center;" aria-label="Time"> Time </th>
                                                            <th class="all sorting" tabindex="0" aria-controls="fr_table" rowspan="2" colspan="1" style="width: 51px;" aria-label="First IN">First IN</th>
                                                            <th class="all sorting" tabindex="0" aria-controls="fr_table" rowspan="2" colspan="1" style="width: 34px;" aria-label="Last OUT">Last OUT</th>
                                                            <th class="all sorting" tabindex="0" aria-controls="fr_table" rowspan="2" colspan="1" style="width: 49px;" aria-label="Total Duration">Total Duration</th>
                                                            <th class="all sorting" tabindex="0" aria-controls="fr_table" rowspan="2" colspan="1" style="width: 42px;" aria-label="Total Rest">Total Rest</th>
                                                            <th class="all sorting not-export-col" tabindex="0" aria-controls="fr_table" rowspan="2" colspan="1" style="width: 49px;" aria-label="OT Hours">OT Hours</th>
                                                            <!--<th class="all sorting_disabled" rowspan="1" colspan="1" style="width: 52px;" aria-label=" Action "> Action </th>-->
                                                        </tr>
                                                        <tr role="row">                                                           
                                                            <th class="all sorting" tabindex="0" aria-controls="fr_table" rowspan="1" colspan="1" style="width: 104px;" aria-label="IN"> IN </th>
                                                            <th class="all sorting" tabindex="0" aria-controls="fr_table" rowspan="1" colspan="1" style="width: 104px;" aria-label="OUT"> OUT </th>
                                                            <th class="all sorting" tabindex="0" aria-controls="fr_table" rowspan="1" colspan="1" style="width: 104px;" aria-label="IN"> IN </th>
                                                            <th class="all sorting" tabindex="0" aria-controls="fr_table" rowspan="1" colspan="1" style="width: 104px;" aria-label="OUT"> OUT </th>
                                                            <th class="all sorting" tabindex="0" aria-controls="fr_table" rowspan="1" colspan="1" style="width: 104px;" aria-label="IN"> IN </th>
                                                            <th class="all sorting" tabindex="0" aria-controls="fr_table" rowspan="1" colspan="1" style="width: 104px;" aria-label="OUT"> OUT </th>
                                                            <th class="all sorting" tabindex="0" aria-controls="fr_table" rowspan="1" colspan="1" style="width: 104px;" aria-label="IN"> IN </th>
                                                            <th class="all sorting" tabindex="0" aria-controls="fr_table" rowspan="1" colspan="1" style="width: 104px;" aria-label="OUT"> OUT </th>
                                                            <th class="all sorting" tabindex="0" aria-controls="fr_table" rowspan="1" colspan="1" style="width: 104px;" aria-label="IN"> IN </th>
                                                            <th class="all sorting" tabindex="0" aria-controls="fr_table" rowspan="1" colspan="1" style="width: 104px;" aria-label="OUT"> OUT </th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
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
                <a> FaceApi IOI V 3.2.0.1 (20221101)</a>
            </div>
            <div class="scroll-to-top" style="display: block;">
                <i class="icon-arrow-up"></i>
            </div>
        </div>        
    </body>
    <style>
        .dt-buttons {
            display: none;
        }
        .pull-left ul {
            list-style: none;
            margin: 0;
            padding-left: 0;
        }
        .pull-left a {
            text-decoration: none;
            color: #ffffff;
        }
        .pull-left li {
            color: #ffffff;
            background-color: #2f2f2f;
            border-color: #2f2f2f;
            display: block;
            float: left;
            position: relative;
            text-decoration: none;
            transition-duration: 0.5s;
            padding: 12px 30px;
            font-size: .75rem;
            font-weight: 400;
            line-height: 1.428571;
        }
        .pull-left li:hover {
            cursor: pointer;
        }
        .pull-left ul li ul {
            visibility: hidden;
            opacity: 0;
            min-width: 9.2rem;
            position: absolute;
            transition: all 0.5s ease;
            margin-top: 8px;
            left: 0;
            display: none;
        }
        .pull-left ul li:hover>ul,
        .pull-left ul li ul:hover {
            visibility: visible;
            opacity: 1;
            display: block;
        }
        .pull-left ul li ul li {
            clear: both;
            width: 100%;
            color: #ffffff;
        }
        .ul-dropdown {
            margin: 0.3125rem 1px !important;
            outline: 0;
        }
        .firstli {
            border-radius: 0.2rem;
        }
        .firstli .material-icons {
            position: relative;
            display: inline-block;
            top: 0;
            margin-top: -1.1em;
            margin-bottom: -1em;
            font-size: 0.8rem;
            vertical-align: middle;
            margin-right: 5px;
        }
    </style>
    <script type="text/javascript">
var tableAttendance;
function myFunction() {
    location.reload();
}
$(document).ready(function () {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    tableAttendance = $('#fr_table').DataTable({
        processing: true,
        language: {
            loadingRecords: '&nbsp;',
            lengthMenu: "Display _MENU_ records per page",
            processing: 'Please wait...'
//            processing: '<div class="spinner"></div>'
        },
        serverSide: true,
        autoWidth: false,
//        scrollY:        "300px",
                scrollX: true,
                scrollCollapse: true,
//        fixedColumns: true,
        fixedColumns: {
            leftColumns: 1
        },
        ajax: {
            url: "{{ url('report/data_beautifullify') }}",
            data: function (d) {
                d.enddate = $('#enddate').val()
            }
        },
        drawCallback: function (settings) {
            console.info('drawCallback')
//    console.log('table',tableAttendance);
//    console.log('table',tableAttendance.fixedColumns().left());
//   console.log(settings.json);
            //do whatever  
        },
//
        dom: 'Blfrtip',
//        dom: '<"float-left"B><"float-right"f>rt<"row"<"col-sm-4"l><"col-sm-4"i><"col-sm-4"p>>',
        buttons: [
            {
                text: 'csv',
                extend: 'csvHtml5',
                exportOptions: {
                    columns: ':visible:not(.not-export-col)'
                }
            },
            {
                text: 'excel',
                extend: 'excelHtml5',
                exportOptions: {
                    columns: ':visible:not(.not-export-col)'
                }
            },
            {
                text: 'pdf',
                extend: 'pdfHtml5',
                exportOptions: {
                    columns: ':visible:not(.not-export-col)'
                }
            },
            {
                text: 'print',
                extend: 'print',
                exportOptions: {
                    columns: ':visible:not(.not-export-col)'
                }
            },
        ],
        columns: [
                      {data: null, render: function (data, type, row) {
//                    console.info('info',data);
                    return '<div class="form-control1" id="" type="text" style="font-size: 0.75rem;width: 210px;padding: .5rem 0;" >' + data.nama_personnel + '</div>';
                                    // Combine the first and last names into a single table field
//                return data.nama_personnel;
                            }},
            {data: 'work_date', name: 'work_date'},
            {data: 'time_in_0', name: 'time_in_0'},
            {data: 'time_ot_0', name: 'time_ot_0'},
            {data: 'time_in_1', name: 'time_in_1'},
            {data: 'time_ot_1', name: 'time_ot_1'},
            {data: 'time_in_2', name: 'time_in_2'},
            {data: 'time_ot_2', name: 'time_ot_2'},
            {data: 'time_in_3', name: 'time_in_3'},
            {data: 'time_ot_3', name: 'time_ot_3'},
            {data: 'time_in_4', name: 'time_in_'},
            {data: 'time_ot_4', name: 'time_ot_4'},
            {data: 'first_in', name: 'first_in'},
            {data: 'last_out', name: 'last_out'},
            {data: 'duration', name: 'duration'},
            {data: 'total_rest', name: 'total_rest'},
            {data: 'ot', name: 'ot'}
        ],
        order: [[0, 'desc']]
    });
//$.fn.dataTable
//.tables({ visible: true, api: true })
//.columns.adjust()
//.fixedColumns().relayout();

    $('.doSearch').click(function () {
        var e_date = $('#enddate').value;
        if (e_date == '') {
            return false;
        }
        tableAttendance.draw();
    });
    $(document).on('click', '.export_transaction_btn', function (i) {
        var tipe = i.target.dataset.type;
        if (tipe == 1) {
            tableAttendance.button('.buttons-csv').trigger();
        } else if (tipe == 2) {
            tableAttendance.button('.buttons-excel').trigger();
        } else if (tipe == 3) {
            tableAttendance.button('.buttons-pdf').trigger();
        } else if (tipe == 4) {
            tableAttendance.button('.buttons-print').trigger();
        }
    });
});
    </script>
</html>