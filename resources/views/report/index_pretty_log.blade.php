<html lang="en">
    <head>
        <meta charset="utf-8" />
        <title>Log Fail Data</title>
        <meta name="csrf-token" content="{{ csrf_token() }}">        
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta content="width=device-width, initial-scale=1" name="viewport" />
        <meta name="robots" content="noindex,nofollow">
        <meta content="" name="author" />
        <link rel="shortcut icon" href="{{asset('assets/img/ioi_icon.png')}}" />

        <link href="{{asset('vendor/font-awesome-old/css/font-awesome.min.css')}}" rel="stylesheet" type="text/css" />
        <link href="{{asset('vendor/font-awesome/css/fontawesome.css')}}" rel="stylesheet" type="text/css" />
        <link href="{{asset('vendor/font-awesome/css/brands.css')}}" rel="stylesheet" type="text/css" />
        <link href="{{asset('vendor/font-awesome/css/solid.css')}}" rel="stylesheet" type="text/css" />                

        <link href="{{asset('vendor/template_assets/global/plugins/bootstrap/css/bootstrap.min.css')}}" rel="stylesheet" type="text/css" />

        <link href="{{asset('vendor/template_assets/global/plugins/datatables/datatables.min.css')}}" rel="stylesheet" type="text/css" />
        <link href="{{asset('vendor/template_assets/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.css')}}" rel="stylesheet" type="text/css" />
        <link href="{{asset('vendor/template_assets/global/plugins/bootstrap-datepicker/css/bootstrap-datepicker3.min.css')}}" rel="stylesheet" type="text/css" />
        <link href="{{asset('vendor/template_assets/global/plugins/select2/css/select2.min.css')}}" rel="stylesheet" type="text/css" />
        <link href="{{asset('vendor/template_assets/global/plugins/select2/css/select2-bootstrap.min.css')}}" rel="stylesheet" type="text/css" />

        <link href="{{asset('vendor/template_assets/global/plugins/bootstrap-daterangepicker/daterangepicker.min.css')}}" rel="stylesheet" type="text/css" />
        <link href="{{asset('vendor/template_assets/global/plugins/bootstrap-datepicker/css/bootstrap-datepicker3.min.css')}}" rel="stylesheet" type="text/css" />
        <link href="{{asset('vendor/template_assets/global/plugins/bootstrap-timepicker/css/bootstrap-timepicker.min.css')}}" rel="stylesheet" type="text/css" />
        <link href="{{asset('vendor/template_assets/global/plugins/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css')}}"" rel="stylesheet" type="text/css" />

        <link href="{{asset('vendor/template_assets/global/css/components-rounded.min.css')}}" rel="stylesheet" id="style_components" type="text/css" />
        <link href="{{asset('vendor/template_assets/global/css/plugins.min.css')}}" rel="stylesheet" type="text/css" />
        <link href="{{asset('vendor/template_assets/layouts/layout4/css/layout.min.css')}}" rel="stylesheet" type="text/css" />
        <link href="{{asset('vendor/template_assets/layouts/layout4/css/themes/default.min.css')}}" rel="stylesheet" type="text/css" id="style_color" />
        <link href="{{asset('vendor/template_assets/layouts/layout4/css/custom.min.css')}}" rel="stylesheet" type="text/css" />
        <link rel="shortcut icon" type="image/png" href="{{asset('assets/img/ioi_icon.png')}}"/>
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
            .spinner-border {
                margin-top:25%;
                display: inline-block;
                width: 2rem;
                height: 2rem;
                vertical-align: -.125em;
                border: .25em solid currentColor;
                border-right-color: transparent;
                border-radius: 50%;
                /*                -webkit-animation: .75s linear infinite spinner-border;
                                animation: .75s linear infinite spinner-border;*/

                -webkit-animation-name: drive;
                -webkit-animation-duration: 2s;
                -webkit-animation-timing-function: ease-in;
                -webkit-animation-iteration-count: 1;
            }
            .pt-5 {
                padding-top: 3rem !important;
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
                <div class="page-top">
                </div>
            </div>
        </div>
        <div class="clearfix"> </div>
        <div class="page-container" style="background:#EEE;">
            <div class="page-sidebar-wrapper">
                <div class="page-sidebar navbar-collapse collapse">
                    <ul class="page-sidebar-menu" data-keep-expanded="false" data-auto-scroll="true" data-slide-speed="200">
                        <li class="nav-item ">
                            <a href="home" class="nav-link">
                                <i class="fa fa-home"></i>
                                <span class="title">Home</span>
                            </a>
                        </li>
                        <li class="nav-item open">
                            <a href="javascript:;" class="nav-link nav-toggle">
                                <i class="fa fa-chart-bar"></i>
                                <span class="title">Reporting</span>
                                <span class="selected"></span>
                                <span class="arrow "></span>
                            </a>
                            <ul class="sub-menu" style="display: block;">
                                <li class="nav-item ">
                                    <a href="{{ route('setting.index') }}" class="nav-link">
                                        <i class="fa fa-chart-bar"></i>
                                        <span class="title">Setting</span>
                                        <span class="selected"></span>
                                        <span class="arrow "></span>
                                    </a>
                                </li>
                                <li>
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
                        </li>
                    </ul>
                </div>
            </div>
            <div class="page-content-wrapper">
                <div class="page-content">
                    <div class="page-head">
                        <div class="page-title">
                            <h1>Log Fail Data</h1>
                        </div>
                    </div>
                    <ul class="page-breadcrumb breadcrumb">
                        <li>
                            <a href="home">Home</a>
                            <i class="fa fa-circle"></i>
                        </li>   
                        <li>
                            <a href="#">Reporting</a>
                            <i class="fa fa-circle"></i>
                        </li>
                        <li>
                            <span class="active">Report</span>
                        </li>
                    </ul>

                    <div class="row">
                        <div class="col-md-12">
                        </div>
                        <div class="col-md-12">
                            <div class="portlet light form-fit bordered">
                                <div class="portlet-body form">
                                    <form class="form-horizontal form-bordered" action="transactions/oph" method="GET">
                                        <div class="form-group">
                                            <label class="col-md-1 control-label" style="text-align:left;">Date</label>
                                            <div class="col-md-2">
                                                <div class="input-group date-picker input-daterange" data-date-format="yyyy-mm-dd" data-date-end-date="0d">
                                                    <input class="form-control" name="from" placeholder="From" type="text" id="enddate" value="{{date('Y-m-d')}}" autocomplete="off">
                                                    <!--<span class="input-group-addon"> to </span>-->
                                                    <!--<input class="form-control" name="to" placeholder="To" type="text" value="09-12-2022" autocomplete="off">--> 
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <button type="button" class="btn btn-circle btn-block btn-outline btn-md blue doSearch"> <i class="fa fa-search"></i> Filter
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12">

                            <div class="portlet light bordered">
                                <div class="portlet-title">
                                    <div class="caption">
                                        <span class="caption-subject font-blue sbold uppercase blue">Log Fail Data</span>
                                    </div>
                                    <div class="actions">
                                        <div class="btn-group btn-group-devided" >
                                            <button type="button" class="btn btn-outline btn-circle btn-sm blue" data-type="2" id="export_transaction_btn"><i class="fa fa-download" ></i> Excel</button>
                                            <button type="button" class="btn btn-outline btn-circle btn-sm blue" data-type="99" id="export_transaction_btn"><i class="fa fa-send" ></i> Resend Failed Data</button>
                                        </div>
                                    </div>
                                </div>
                                <div class="portlet-body">
                                    <div class="table">
                                        <table class="table table-bordered table-hover" id="log_table">
                                            <thead>
                                                <tr role="row">                                                           
                                                    <th class="all sorting" tabindex="0" aria-controls="fr_table" rowspan="1" colspan="1" width="70px"> Device IP </th>
                                                    <th class="all sorting" tabindex="0" aria-controls="fr_table" rowspan="1" colspan="1" width="70px"> Worker Code </th>
                                                    <th class="all sorting" tabindex="0" aria-controls="fr_table" rowspan="1" colspan="1" style="width: 51px; text-align: center;" > Worker ID </th>
                                                    <th class="all sorting" tabindex="0" aria-controls="fr_table" rowspan="1" colspan="1" style="width: 51px; text-align: center;" > Alarm Time </th>
                                                    <th class="all sorting" tabindex="0" aria-controls="fr_table" rowspan="1" colspan="1" style="width: 51px; text-align: center;" > Clock IN/OUT </th>
                                                    <th class="all sorting" tabindex="0" aria-controls="fr_table" rowspan="1" colspan="1" style="width: 51px; text-align: center;" > Status </th>
                                                    <th class="all sorting" tabindex="0" aria-controls="fr_table" rowspan="1" colspan="1" style="width: 151px; text-align: center;"> Remark </th>
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
        <div class="page-footer">
            <div class="page-footer-inner">
                <a> FR IOI V 1.1.0.1 (20221212)</a>
            </div>
            <div class="scroll-to-top">
                <i class="icon-arrow-up"></i>
            </div>
        </div>
        <div id="spinner-div" class="pt-5">
            <div class="spinner-border text-primary" role="status">
            </div>
        </div>        
        <div class="quick-nav-overlay"></div>

        <script src="{{asset('vendor/template_assets/global/plugins/jquery.min.js')}}" type="text/javascript"></script>
        <script src="{{asset('vendor/template_assets/global/plugins/bootstrap/js/bootstrap.min.js')}}" type="text/javascript"></script>
        <script src="{{asset('vendor/template_assets/global/plugins/js.cookie.min.js')}}" type="text/javascript"></script>
        <script src="{{asset('vendor/template_assets/global/plugins/jquery-slimscroll/jquery.slimscroll.min.js')}}" type="text/javascript"></script>
        <script src="{{asset('vendor/template_assets/global/plugins/jquery.blockui.min.js')}}" type="text/javascript"></script>
        <script src="{{asset('vendor/template_assets/global/plugins/bootstrap-switch/js/bootstrap-switch.min.js')}}" type="text/javascript"></script>

        <script src="{{asset('vendor/template_assets/global/plugins/datatables/datatables.min.js')}}" type="text/javascript"></script>
        <script src="{{asset('vendor/datatables/js/dataTables.rowGroup.min.js')}}" type="text/javascript"></script>

        <script src="{{asset('vendor/template_assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js')}}" type="text/javascript"></script>

        <script src="{{asset('vendor/template_assets/global/plugins/jquery-repeater/jquery.repeater.js')}}" type='text/javascript'></script><script src="{{asset('vendor/template_assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js')}}" type='text/javascript'></script>

        <script src="{{asset('vendor/template_assets/global/plugins/counterup/jquery.waypoints.min.js')}}" type="text/javascript"></script>
        <script src="{{asset('vendor/template_assets/global/plugins/counterup/jquery.counterup.min.js')}}" type="text/javascript"></script>

        <script src="{{asset('vendor/template_assets/global/scripts/app.min.js')}}" type="text/javascript"></script>

        <script src="{{asset('vendor/template_assets/pages/scripts/components-date-time-pickers.min.js')}}" type="text/javascript"></script>

        <script src="{{asset('vendor/template_assets/global/plugins/select2/js/select2.full.min.js')}}" type="text/javascript"></script>
        <script src="{{asset('vendor/template_assets/pages/scripts/components-select2.js')}}" type="text/javascript"></script>

        <script src="{{asset('vendor/template_assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js')}}" type="text/javascript"></script>
        <script src="{{asset('vendor/template_assets/global/plugins/bootstrap-timepicker/js/bootstrap-timepicker.min.js')}}" type="text/javascript"></script>
        <script src="{{asset('vendor/template_assets/global/plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js')}}" type="text/javascript"></script>
        <script src="{{asset('vendor/template_assets/pages/scripts/components-date-time-pickers.js')}}" type="text/javascript"></script>

        <script src="{{asset('vendor/template_assets/layouts/layout4/scripts/layout.min.js')}}" type="text/javascript"></script>
        <script src="{{asset('vendor/template_assets/layouts/layout4/scripts/demo.min.js')}}" type="text/javascript"></script>
        <script src="{{asset('vendor/template_assets/layouts/global/scripts/quick-sidebar.min.js')}}" type="text/javascript"></script>
        <script src="{{asset('vendor/template_assets/layouts/global/scripts/quick-nav.min.js')}}" type="text/javascript"></script>

        <link type="text/css" href="{{asset('vendor/datatables/js/dataTables.checkboxes.css')}}" rel="stylesheet" />
        <script type="text/javascript" src="{{asset('vendor/datatables/js/dataTables.checkboxes.min.js')}}"></script>
        <script type="text/javascript">

            function myFunction() {
            location.reload();
            }
            $(document).ready(function ()
            {
            $.ajaxSetup({
            headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
            });
            })

                    function printDiv(divID) {
                    //Get the HTML of div
                    var divElements = document.getElementById(divID).innerHTML;
                    //Get the HTML of whole page
                    var oldPage = document.body.innerHTML;
                    //Reset the page's HTML with div's HTML only
                    document.body.innerHTML =
                            "<html><head><title></title></head><body>" +
                            divElements + "</body>";
                    //Print Page
                    window.print();
                    //Restore orignal HTML
                    document.body.innerHTML = oldPage;
                    }



        </script>
        <script language="javascript" type="text/javascript">
            var tableAttendance;
            $(document).ready(function () {
            tableAttendance = $('#log_table').DataTable({
//                    processing: true,
            autoFilter: false,
                    language: {
                    loadingRecords: '&nbsp;',
                            lengthMenu: "_MENU_ records",
                            processing: 'Please wait...'
//            processing: '<div class="spinner"></div>'
                    },
                    serverSide: true,
                    autoWidth: true,
//        scrollY:        "300px",
                            scrollX: false,
                            scrollCollapse: false,
//        fixedColumns: true,
//                    fixedColumns: {
//                        leftColumns: 1
//                    },
                    ajax: {
                    url: "{{ url('log/data_beautifullify_log') }}",
                            data: function (d) {
                            d.enddate = $('#enddate').val(),
                                    d.searchbox = $("div.dataTables_filter input").val()
                            },
                            method : 'get',
                            dataType: 'json'
                    },
                    drawCallback: function (settings) {
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
                    text: 'print',
                            extend: 'print',
                            exportOptions: {
                            columns: ':visible:not(.not-export-col)'
                            }
                    },
                    ],
                    columns: [
                    {data: 'devicename', name: 'devicename'},
                    {data: 'personid', name: 'personid'},
                    {data: 'firstname', name: 'firstname'},
                    {data: 'alarmtime', name: 'alarmtime'},
                    {data: 'accesstype', name: 'accesstype'},
//                    {data: 'sent_cpi', name: 'sent_cpi'},
                              {data: null, name:'sent_cpi', render: function (data, type, row) {
                            console.info('data',data,data.sent_cpi);
                            var stts = 'Failed';
                            if(data.sent_cpi == 'Y'){
                                stts = 'Success';
                            }
                            return stts;
//                    return '<div class="form-control1" id="" type="text" style="font-size: 0.75rem;width: 210px;padding: .5rem 0;" >' + data.nama_personnel + '</div>';
                                }},                    
                    {data: 'remark', name: 'remark'},
                    ],
                    lengthMenu: [
                    [10, 25, 50, 100, - 1],
                    [10, 25, 50, 100, 'All'],
                    ],
                    order: [[0, 'desc']]
            });
            $(document).on('click', '.doSearch', function (e) {
            e.preventDefault();
            var e_date = $('#enddate').val();
            console.info('edate', e_date);
            if (e_date == '') {
            return false;
            }
            tableAttendance.draw();
            });
            $(document).on('click', '#export_transaction_btn', function (i) {
            var tipe = i.target.dataset.type;
            if (tipe == 1) {
            tableAttendance.button('.buttons-csv').trigger();
            } else if (tipe == 2) {
            tableAttendance.button('.buttons-excel').trigger();
            } else if (tipe == 3) {
            tableAttendance.button('.buttons-print').trigger();
            } else if (tipe == 99) {
                $.ajax({
                    method: "POST",
                    url: "sendsap.transfer",
                    data: {type: 'resend'},
                    dataType: 'json',
                    beforeSend: function () {
                        $('#spinner-div').show();
                    },
                    success: function (msg) {
                        $('#spinner-div').hide();
                        if (msg.status == 'success') {
                            alert("Transfer completed.");
                        } else {
                            var txt = 'Transfer Completed.' + msg.message
                            alert(txt)
                        }
                    }
                })
            }
            });
            $("div.dataTables_filter input").unbind();
            $("div.dataTables_filter input").on('keydown', function(e) {
            if (e.which == 13) {
            tableAttendance.draw();
            }
            });
            });
        </script>
        <script src="{{asset('assets/js/report/table_log.js')}}" type='text/javascript'></script>
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
    </body>
</html>        