<!DOCTYPE html>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Setting Day Cutoff</title>
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <link  href="https://cdn.datatables.net/1.10.21/css/jquery.dataTables.min.css" rel="stylesheet" type="text/css">
        <script src="{{asset('assets/js/jquery-3.5.1.min.js')}}"></script>
        <script src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
        <link href="{{asset('assets/css/bootstrap.min.css')}}" rel="stylesheet" type="text/css" />
        <link href="{{asset('assets/css/components-rounded.min.css')}}" rel="stylesheet" type="text/css" />
        <link href="{{asset('assets/css/bootstrap-datepicker3.min.css')}}" rel="stylesheet" type="text/css" />
        <link href="{{asset('assets/css/layout.min.css')}}" rel="stylesheet" type="text/css" />
        <link href="{{asset('assets/css/themes/default.min.css')}}" rel="stylesheet" type="text/css" id="style_color" />        
        <link href="{{asset('assets/css/custom.min.css')}}" rel="stylesheet" type="text/css" />
        <link rel="shortcut icon" type="image/png" href="{{asset('assets/img/ioi_icon.png')}}"/>
        <!-- DataTable -->
        <script src="https://cdn.datatables.net/1.10.22/js/jquery.dataTables.min.js" type="text/javascript"></script>
        <script src="https://cdn.datatables.net/1.10.22/js/dataTables.bootstrap4.min.js" type="text/javascript"></script>
        <script src="https://cdn.datatables.net/buttons/1.6.5/js/dataTables.buttons.min.js" type="text/javascript"></script>
        <script src="https://cdn.datatables.net/buttons/1.6.5/js/buttons.flash.min.js" type="text/javascript"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js" type="text/javascript"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js" type="text/javascript"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js" type="text/javascript"></script>
        <script src="https://cdn.datatables.net/buttons/1.6.5/js/buttons.html5.min.js" type="text/javascript"></script>
        <script src="https://cdn.datatables.net/buttons/1.6.5/js/buttons.print.min.js" type="text/javascript"></script>        
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
                            <h1>Setting Day Cutoff</h1>
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
                                <div class="portlet-body form"><div class="form-group">
                                        <div class="col-md-6">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="portlet light bordered">
                                @if ($message = Session::get('success'))
                                <div class="alert alert-success">
                                    <p>{{ $message }}</p>
                                </div>
                                @endif                                
                                <div class="portlet-title">
                                    <div class="caption">
                                        <span class="caption-subject font-blue sbold uppercase blue">Setting</span>
                                    </div>
                                    <div class="actions">
                                        <div class="btn-group btn-group-devided">
                                            @csrf
                                            <a class="btn btn-success" href="{{ route('setting.create') }}"> Create Setting</a>
                                        </div>
                                    </div>
                                </div>
                                <div class="portlet-body">
                                    <div class="table">
                                        <div id="fr_table_wrapper" class="dataTables_wrapper no-footer">
                                            <div class="table-scrollable">
                                                <table class="table table-bordered" id="setting_table">
                                                    <thead>
                                                        <tr>
                                                            <th>No</th>
                                                            <th>Start Date</th>
                                                            <th>End Date</th>
                                                            <th>IP Server FR</th>
                                                            <th>IP Device clock In</th>
                                                            <th>IP Device clock Out</th>
                                                            <th>Unit Name</th>
                                                            <th>Created Date</th>
                                                            <th width="280px">Action</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach ($setting as $setting)
                                                        <tr>
                                                            <td>{{ $setting->fa_setting_id }}</td>
                                                            <td>{{ $setting->startdate }}</td>
                                                            <td>{{ $setting->enddate }}</td>
                                                            <td>{{ $setting->ip_server_fr }}</td>
                                                            <td>{{ $setting->ip_clock_in }}</td>
                                                            <td>{{ $setting->ip_clock_out }}</td>
                                                            <td>{{ $setting->unit_name }}</td>
                                                            <td>{{ $setting->created_at }}</td>
<!--                                                            <td>
                                                                <form action="{{ route('setting.destroy',$setting->fa_setting_id) }}" method="Post">
                                                                    <a class="btn btn-primary" href="{{ route('setting.edit',$setting->fa_setting_id) }}">Edit</a>
                                                                    @csrf
                                                                                                    @method('DELETE')
                                                                                                    <button type="submit" class="btn btn-danger">Delete</button>
                                                                </form>
                                                            </td>-->
                                                            <td>
                                                                    
                                                            </td>
                                                        </tr>
                                                        @endforeach
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
</html>
    <script type="text/javascript">
    $(document).ready(function () {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        $('#setting_table').DataTable({
            processing: true,
            serverSide: false,
            order: [[0, 'desc']]
        });
    });
    </script>