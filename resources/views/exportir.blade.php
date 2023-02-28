<html> 
    <head> 
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Export Time Attendance</title>
        <script src="{{asset('assets/js/jquery-22.min.js')}}"></script>
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" /> 
        <script src="https://cdn.datatables.net/1.10.12/js/jquery.dataTables.min.js"></script> 
        <script src="https://cdn.datatables.net/1.10.12/js/dataTables.bootstrap.min.js"></script> 
        <link rel="stylesheet" href="https://cdn.datatables.net/1.10.12/css/dataTables.bootstrap.min.css" /> 
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script> 
        <link rel="stylesheet" href="{{ asset('vendor/datatables/css/buttons.dataTables.min.css') }}"> 
        <script src="{{ asset('vendor/datatables/js/dataTables.buttons.min.js') }}"></script>
        <script src="{{ asset('vendor/datatables/js/buttons.server-side.js') }}"></script>
    </head> 
    <body>
        <div class="container"> <br />
            <h3 align="center">Time Attendance</h3>
            <br /> 
            <div class="table-responsive"> <div class="panel panel-default">
                    <div class="panel-heading">Sample Data</div> 
                    <div class="panel-body"> {!! $dataTable->table() !!} {!! $dataTable->scripts() !!} </div> 
                </div>
            </div> <br /> <br />
        </div>
    </body> 
</html>
