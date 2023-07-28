<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Time Attendance Report</title>
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <link href="{{asset('assets/css/components-rounded.min.css')}}" rel="stylesheet" type="text/css" />
        <link href="{{asset('assets/css/bootstrap-datepicker3.min.css')}}" rel="stylesheet" type="text/css" />
        <link href="{{asset('assets/css/layout.min.css')}}" rel="stylesheet" type="text/css" />
        <link href="{{asset('assets/css/themes/default.min.css')}}" rel="stylesheet" type="text/css" id="style_color" />        
        <link href="{{asset('assets/css/custom.min.css')}}" rel="stylesheet" type="text/css" />
        <link rel="shortcut icon" type="image/png" href="{{asset('assets/img/ioi_icon.png')}}"/>

        <script src="{{asset('assets/js/jquery-22.min.js')}}"></script>
        <link rel="stylesheet" href="{{ asset('vendor/datatables/css/bootstrap3.3.6.min.css') }}">         
        <script src="{{ asset('vendor/datatables/js/jquery.dataTables.min.js') }}"></script>
        <script src="{{ asset('vendor/datatables/js/dataTables.bootstrap.min.js') }}"></script>
        <script src="{{ asset('vendor/datatables/js/bootstrap3.3.6.min.js') }}"></script>
        <link rel="stylesheet" href="{{ asset('vendor/datatables/css/dataTables.bootstrap.min.css') }}">         
        <script src="{{ asset('vendor/datatables/js/bootstrap3.3.6.min.js') }}"></script> 
        <link rel="stylesheet" href="{{ asset('vendor/datatables/css/buttons.dataTables.min.css') }}"> 
        <script src="{{ asset('vendor/datatables/js/dataTables.buttons.min.js') }}"></script>
        <script src="{{ asset('vendor/datatables/js/buttons.server-side.js') }}"></script>
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
<!--                        <div class="col-md-12">
                            <div class="portlet light form-fit bordered">
                                <div class="portlet-body form"><div class="form-group">
                                        <label class="col-md-2 control-label" style="text-align:left;">Date</label>
                                        <div class="col-md-6">
                                            <div class="input-group date-picker input-daterange" data-date-format="dd-mm-yyyy" data-date-end-date="0d">
                                                <input class="form-control" id="startdate" name="from" placeholder="From" type="date" value="{{date('Y-m-d')}}" autocomplete="off">
                                                <span class="input-group-addon"> to </span>
                                                <input class="form-control" id="enddate" name="to" placeholder="To" type="date" value="{{date('Y-m-d')}}" autocomplete="off"> 
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>-->
                        <div class="col-md-12">
                            <div class="portlet light bordered">
                                <div class="portlet-title">
                                    <div class="caption">
                                        <span class="caption-subject font-blue sbold uppercase blue">Time Attendance</span>
                                    </div>
                                    <div class="actions">
<!--                                        <div class="btn-group btn-group-devided">
                                                @csrf
                                                <input type="hidden" name="from" value="09-11-2022">
                                                <input type="hidden" name="to" value="09-11-2022">
                                            <button type="button" class="btn btn-outline btn-circle btn-sm blue" id="export_transaction_btn"><i class="fa fa-download"></i> Export Data</button>
                                        </div>-->
                                    </div>
                                </div>
                                <div class="portlet-body">
                                    <div class="table">
                                        <div id="fr_table_wrapper" class="dataTables_wrapper no-footer">
                                            <div class="table-scrollable"> 
                                            <div class="panel-body"> {!! $dataTable->table() !!} {!! $dataTable->scripts() !!} </div> 
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
    </html>
