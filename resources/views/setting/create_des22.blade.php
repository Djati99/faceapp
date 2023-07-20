<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Face Recognition - Day Cutoff Setting</title>
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <script src="{{asset('assets/js/jquery-3.5.1.min.js')}}"></script>
        <script src="{{asset('assets/js/datetimepicker.js')}}"></script>
        <link href="{{asset('assets/css/bootstrap.min.css')}}" rel="stylesheet" type="text/css" />
        <link href="{{asset('assets/css/layout.min.css')}}" rel="stylesheet" type="text/css" />
        <link href="{{asset('assets/css/datetimepicker.css')}}" rel="stylesheet" type="text/css" />
        <link href="{{asset('assets/css/themes/default.min.css')}}" rel="stylesheet" type="text/css" id="style_color" />        
        <link href="{{asset('assets/css/custom.min.css')}}" rel="stylesheet" type="text/css" />
        <link href="{{asset('vendor/font-awesome-old/css/font-awesome.min.css')}}" rel="stylesheet" type="text/css" />
        <link href="{{asset('vendor/font-awesome/css/fontawesome.css')}}" rel="stylesheet" type="text/css" />
        <link href="{{asset('vendor/font-awesome/css/brands.css')}}" rel="stylesheet" type="text/css" />
        <link href="{{asset('vendor/font-awesome/css/solid.css')}}" rel="stylesheet" type="text/css" />                
        <link rel="shortcut icon" type="image/png" href="{{asset('assets/img/ioi_icon.png')}}"/>
    </head>
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
                                <i class="fa fa fa-file-alt"></i>
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
                    <div class="row">
                        <div class="col-md-12">
                        </div>
                        <div class="col-md-12">
                            <div class="portlet light bordered">
                                <div class="portlet-title">
                                    <div class="caption">
                                        <span class="caption-subject font-blue sbold uppercase blue">Setting for middleware</span>
                                    </div>
                                </div>
                                @if ($message = Session::get('success'))
                                <div class="alert alert-success">
                                    <p>{{ $message }}</p>
                                </div>
                                @endif                    
                                <div class="portlet-body form">
                                    @if(session('status'))
                                    <div class="alert alert-success mb-1 mt-1">
                                        {{ session('status') }}
                                    </div>
                                    @endif
                                    <form action="{{ route('setting.store') }}" method="POST" enctype="multipart/form-data">                                    
                                        @csrf                            
                                        <div class="form-body">
                                            <div class="row">
                                                <div class="col-md-6">                                                 
                                                    <div class="form-group">
                                                        <label class="col-md-5 control-label"><strong>Date Time Start</strong> </label>
                                                        <div class="col-md-7">
                                                            <input type="text" name="startdate" value="{{$setting->startdate}}" id="startdate" class="form-control" placeholder="Start Date">
                                                            @error('startdate')
                                                            <div class="alert alert-danger mt-1 mb-1">{{ $message }}</div>
                                                            @enderror
                                                        </div>
                                                    </div>                                                  
                                                    <div class="form-group">
                                                        <label class="col-md-5 control-label"><strong>Date Time End</strong> </label>
                                                        <div class="col-md-7">
                                                            <input type="text" name="enddate" value="{{$setting->enddate}}" id="enddate" class="form-control" placeholder="End Date">
                                                        </div>
                                                        @error('enddate')
                                                        <div class="alert alert-danger mt-1 mb-1">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                    <div class="form-group">
                                                        <label class="col-md-5 control-label"> IP server FR</label>
                                                        <div class="col-md-7">
                                                            <table>
                                                                <tbody><tr>
                                                                        <td>
                                                                            <input type="text" name="ip_server_fr" value="{{$setting->ip_server_fr}}" id="ip_server_fr" class="form-control" placeholder="172.16.8.79" style="width: 130px;">

                                                                        </td>
                                                                        <td>
                                                                            <button class="btn btn-primary ml-3" type="button" style="width: 150px;" id="test_conn">Test connection</button>
                                                                        </td>
                                                                    </tr>
                                                                </tbody>
                                                            </table>                                                            
                                                        </div>
                                                        @error('ip_server_fr')
                                                        <div class="alert alert-danger mt-1 mb-1">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                    <div class="form-group">
                                                        <label class="col-md-5 control-label">IP Clock In </label>
                                                        <div class="col-md-7">
                                                            <input type="text" name="ip_clock_in" value="{{$setting->ip_clock_in}}" id="ip_clock_in" class="form-control" placeholder="172.16.8.251">
                                                        </div>
                                                        @error('ip_clock_in')
                                                        <div class="alert alert-danger mt-1 mb-1">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                    <div class="form-group">
                                                        <label class="col-md-5 control-label">IP Clock Out</label>
                                                        <div class="col-md-7">
                                                            <input type="text" name="ip_clock_out" value="{{$setting->ip_clock_out}}" id="ip_clock_out" class="form-control" placeholder="172.16.8.252">
                                                        </div>
                                                        @error('ip_clock_out')
                                                        <div class="alert alert-danger mt-1 mb-1">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                    <div class="form-group">
                                                        <label class="col-md-5 control-label">Operating Unit</label>
                                                        <div class="col-md-7">
                                                            <input type="text" name="unit_name" value="{{$setting->unit_name}}" id="unit_name" class="form-control" placeholder="Estate Mill">
                                                        </div>
                                                        @error('unit_name')
                                                        <div class="alert alert-danger mt-1 mb-1">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <hr>
                                        <div class="form-actions right">
                                            <!--                                            <button type="submit" class="btn btn-outline btn-circle btn-md blue">
                                            <button type="submit" class="btn btn-primary ml-3">
                                                Save
                                            </button>
                                            <!--<a class="btn btn-primary" href="{{ route('setting.index') }}"> Back</a>-->
                                        </div>
                                    </form>
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
        <div id="spinner-div" class="pt-5">
            <div class="spinner-border text-primary" role="status">
            </div>
        </div>        
    </body>
</html>
<script type="text/javascript">
$(function () {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    $('#startdate').datetimepicker();
    $('#enddate').datetimepicker();
    $('#test_conn').click(function () {
        if (this.value !== 'undefined') {
            $.ajax({
                method: "POST",
                url: "setting.check",
                data: {ip_check: $('#ip_server_fr').val()},
                dataType: 'json',
                beforeSend: function () {
                    $('#spinner-div').show();
                },
                success: function (msg) {
                    $('#spinner-div').hide();
                    if (msg.status == 'success') {
                        alert("Check Completed. System success to connect FR API.");
                    } else {
                        alert('Check Completed. System fail to connect FR API')
                    }
                }
            })
        }
    })
});
</script> 