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
            $data = AccessControl::latest()->get();
        $data = DB::table('fa_accesscontrol')
                ->select('fa_accesscontrol_id', 'devicecode', 'devicename', 'channelid', 'channelname', 'alarmtypeid', 'personid', 'firstname', 'lastname', 'alarmtime', 'accesstype', 'unit_name')
                ->where('sent_cpi', '=', 'F')
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
                ->select('fa_accesscontrol_id', 'devicecode', 'devicename', 'channelid', 'channelname', 'alarmtypeid', 'personid', 'firstname', 'lastname', 'alarmtime', 'accesstype', 'unit_name')
                ->where('sent_cpi', '=', 'F')
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
                ->where('sent_cpi', '=', 'F')
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
                ->select('fa_accesscontrol_id', 'devicecode', 'devicename', 'channelid', 'channelname', 'alarmtypeid', 'personid', 'firstname', 'lastname', 'alarmtime', 'accesstype', 'unit_name')
                ->where('sent_cpi', '=', 'F')
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

            /**
             * kalau enddate antara 00:01 - 11:59 pagi,
             * brarti in / out di range tsb, masih masuk ke hari sebelumnya
             */
            if ($setting_sdate[0] !== $setting_edate[0]) {
                //kalau tanggalnya beda, brarti ada jam day worknya kelewat hari berjalan
            }

//            dd($report_setting->startdate);
//            $strdate = $request->get('startdate');
            $enddate = $request->get('enddate');
            $strdate = "$enddate $setting_sdate[1]";
//            var_dump([intval(date('His')), intval(str_replace(":", "", $setting_edate[1]))]);
//            echo "<br/>";
            if (intval(date('His')) >= intval(str_replace(":", "", $setting_edate[1]))) {
//                var_dump(date('His'));
//                echo "<br/>";
                $enddate1 = new \DateTime($enddate);
                $enddate1->modify('+1 day');
                $enddate = "{$enddate1->format('Y-m-d')} $setting_edate[1]";
//                $strdate = "$enddate 00:00:01";
//                $enddate = "$enddate 23:59:59";
                //kalau tanggalnya beda, brarti ada jam day worknya kelewat hari berjalan
            } else {
                if ($setting_sdate[0] !== $setting_edate[0]) {
                    $startdate1 = new \DateTime($enddate);
                    $enddate = $startdate1->format("Y-m-d") . " $setting_edate[1]";
                    $startdate1->modify('-1 day');
                    $startdate = $startdate1->format("Y-m-d") . " $setting_sdate[1]";
                } else {
                    $enddate = "$enddate $setting_edate[1]";
                }
            }
//            var_dump([$setting_sdate, $setting_edate]);
//            echo "<br/>";
//            var_dump([$setting_sdate[0], $setting_edate[0]]);
//            echo "<br/>";

            $search_val_all = $request->get('searchbox');
            $w_personid = '1=1';
//            var_dump($search_val_all);
            if (!empty($search_val_all)) {
                $date_search = \DateTime::createFromFormat('Y-m-d', $search_val_all);
                if ($date_search) {
                    $strdate1 = $date_search->format('Y-m-d');
                    $strdate = "$strdate1 $setting_sdate[1]";
                    $enddate1 = date('Y-m-d', strtotime($strdate1 . ' +1 day'));
                    $enddate = "$enddate1 $setting_edate[1]";
//                    var_dump([$strdate, $enddate]);
                    $searchwhere = "%$search_val_all%";
                    $data = DB::table('fa_accesscontrol')
                            ->where(function ($query) use ($strdate, $enddate) {
                                $query->where('alarmtime', '>=', $strdate);
                                $query->where('alarmtime', '<=', $enddate);
                                $query->where('sent_cpi', '=', 'F');
                            })
                            ->get();
                } else {
                    $w_personid = "((personid ilike '%" . $search_val_all . "%')";
                    $w_personid .= " or (firstname ilike '%" . $search_val_all . "%'))";
//                    dd([$w_personid, $search_val_all]);
                    $searchwhere = "%$search_val_all%";
                    $data = DB::table('fa_accesscontrol')
                            ->where(function ($query) use ($strdate, $enddate) {
//                                $query->where('alarmtime', '>=', $strdate);
//                                $query->where('alarmtime', '<=', $enddate);
                                $query->where('sent_cpi', '=', 'F');
                            })
                            ->where(function ($query1) use ($searchwhere) {
                                $query1->orWhere('personid', 'ilike', $searchwhere);
                                $query1->orWhere('firstname', 'ilike', $searchwhere);
                            })
                            ->get();
                }
            } else {
                $data = DB::table('fa_accesscontrol')
                        ->where(function ($query) use ($strdate, $enddate) {
//                            $query->where('alarmtime', '>=', $strdate);
//                            $query->where('alarmtime', '<=', $enddate);
                            $query->where('sent_cpi', '=', 'F');
                        })
//                    ->orWhere('personid','ilike',"%".$search_val_all."%")
//                    ->whereRaw($w_personid)
                        ->get();
            }

            $arr_data = $data->toArray();
//            dd($arr_data);
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

           
            $dttable = Datatables::of($result)->make(true);
            return $dttable;
        }
//        var_dump($request);
    }

}
