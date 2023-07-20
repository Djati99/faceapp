<?php

namespace App\Http\Controllers\Web;

use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\AccessControl;
use DataTables;

class LogController extends BaseController {

    use AuthorizesRequests,
        DispatchesJobs,
        ValidatesRequests;

    public function report(Request $request) {
        if ($request->ajax()) {
            $data = DB::table('fa_accesscontrol')
                    ->select('fa_accesscontrol_id', 'devicecode', 'devicename', 'channelid', 'channelname', 'alarmtypeid', 'personid', 'firstname', 'lastname', 'alarmtime','created_at', 'accesstype', 'unit_name')
                    ->whereIn('sent_cpi', ['F', 'Y'])
//                ->offset(0)
                    ->orderBy('alarmtime', 'asc')
//                ->limit(10)
                    ->get();
            return Datatables::of($data)
                            ->make(true);
        }

        return view('report/index_log');
    }

    public function report_formatted(Request $request) {
        if ($request->ajax()) {
            $data = DB::table('fa_accesscontrol')
                    ->select('fa_accesscontrol_id', 'devicecode', 'devicename', 'channelid', 'channelname', 'alarmtypeid', 'personid', 'firstname', 'lastname', 'alarmtime','created_at', 'accesstype', 'unit_name')
                    ->whereIn('sent_cpi', ['F', 'Y'])
                    ->offset(0)
                    ->orderBy('alarmtime', 'asc')
//                ->limit(10)
                    ->get();
            return Datatables::of($data)
                            ->make(true);
        }
//        return view('report/index_pretty_des22');
        return view('report/index_pretty_log');
    }

    public function getData_att(Request $request) {
        if ($request->ajax()) {
            $data = DB::table('fa_accesscontrol')
                    ->select('fa_accesscontrol_id', 'devicecode', 'devicename', 'channelid', 'channelname', 'alarmtypeid', 'personid', 'firstname', 'lastname', 'alarmtime', 'accesstype', 'unit_name')
                    ->whereIn('sent_cpi', ['F', 'Y'])
                    ->offset(0)
                    ->orderBy('alarmtime', 'asc')
//                ->limit(10)
                    ->get();
            return Datatables::of($data)
                            ->make(true);
        }
//        var_dump($request);
    }

    public function getData(Request $request) {
        if ($request->ajax()) {
            $data = DB::table('fa_accesscontrol')
                    ->select('fa_accesscontrol_id', 'devicecode', 'devicename', 'channelid', 'channelname', 'alarmtypeid', 'personid', 'firstname', 'lastname', 'alarmtime', 'accesstype', 'unit_name', 'sent_cpi')
                    ->whereIn('sent_cpi', ['F', 'Y'])
                    ->offset(0)
                    ->orderBy('alarmtime', 'asc')
//                ->limit(10)
                    ->get();
            return Datatables::of($data)
                            ->make(true);
        }
//        var_dump($request);
    }

    public function getDataFormatted(Request $request) {
        if ($request->ajax()) {
            $zone = env('API_ZONE', 'ID');
            if ($zone == 'MY') {
                date_default_timezone_set('Asia/Kuala_Lumpur');
            } else {
                date_default_timezone_set('Asia/Jakarta');
            }
            /**
             * Setting day
             */
            $report_setting = DB::table('fa_setting')->latest('fa_setting_id')->first();
            $setting_sdate = explode(" ", $report_setting->startdate);
            $setting_edate = explode(" ", $report_setting->enddate);

            $status_log = $request->get('status');
            $strdate = $request->get('enddate');

            $search_val_all = $request->get('searchbox');
            $w_personid = '1=1';
//            var_dump($search_val_all);
            if (!empty($search_val_all)) {
                $date_search = \DateTime::createFromFormat('Y-m-d', $search_val_all);
                if ($date_search) {
                    $strdate = $date_search->format('Y-m-d');

                    $searchwhere = "%$search_val_all%";
                    $data = DB::table('fa_accesscontrol')
                            ->where(function ($query) use ($strdate, $enddate, $status_log) {
                                $query->whereRaw("to_char(alarmtime,'YYYY-MM-DD') = '$strdate'");
                                if ($status_log == 'ALL') {
                                    $query->whereIn('sent_cpi', ['F', 'Y']);
                                } else {
                                    $query->where('sent_cpi', '=', $status_log);
                                }
                            })
                            ->get();
                } else {
                    $w_personid = "((personid ilike '%" . $search_val_all . "%')";
                    $w_personid .= " or (firstname ilike '%" . $search_val_all . "%'))";
//                    dd([$w_personid, $search_val_all]);
                    $searchwhere = "%$search_val_all%";
                    $data = DB::table('fa_accesscontrol')
                            ->where(function ($query) use ($strdate, $status_log) {
                                $query->whereRaw("to_char(alarmtime,'YYYY-MM-DD') = '$strdate'");
                                if ($status_log == 'ALL') {
                                    $query->whereIn('sent_cpi', ['F', 'Y']);
                                } else {
                                    $query->where('sent_cpi', '=', $status_log);
                                }
                            })
                            ->where(function ($query1) use ($searchwhere) {
                                $query1->orWhere('personid', 'ilike', $searchwhere);
                                $query1->orWhere('firstname', 'ilike', $searchwhere);
                            })
                            ->get();
                }
            } else {
                $data = DB::table('fa_accesscontrol')
                        ->where(function ($query) use ($status_log) {
                            if ($status_log == 'ALL') {
                                $query->whereIn('sent_cpi', ['F', 'Y']);
                            } else {
                                $query->where('sent_cpi', '=', $status_log);
                            }
                        })
                        ->whereRaw("to_char(alarmtime,'YYYY-MM-DD') = '$strdate'")
                        ->get();
            }

            $arr_data = $data->toArray();
            if (!$arr_data || count($arr_data) < 1) {
                return Datatables::of($data)
                                ->make(true);
            }

            $swipetime = [];
            $new_data = [];
            $format = 'Y-m-d H:i:s';
            foreach ($arr_data as $dt_access) {
                $result[] = $dt_access;
            }

            $no = 1;
            foreach($result as $keyr=>$rslt){
                $result[$keyr]->no_urut = $no;
                $no++;
            }
            $dttable = Datatables::of($result)->make(true);
            return $dttable;
        }
//        var_dump($request);
    }

}
