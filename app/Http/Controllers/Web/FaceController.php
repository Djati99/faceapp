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

class FaceController extends BaseController {

    use AuthorizesRequests,
        DispatchesJobs,
        ValidatesRequests;

    public function report(Request $request) {
        if ($request->ajax()) {
            $data = AccessControl::latest()->get();
            return Datatables::of($data)
                            ->make(true);
        }

        return view('report/index');
    }

    public function report_formatted(Request $request) {
        if ($request->ajax()) {
            $data = AccessControl::latest()->get();
            return Datatables::of($data)
                            ->make(true);
        }
//        return view('report/index_pretty_des22');
        return view('report/index_pretty');
    }

    public function getData_att(Request $request) {
        if ($request->ajax()) {
            $data = Attendance::latest()->get();
            return Datatables::of($data)
                            ->make(true);
        }
//        var_dump($request);
    }

    public function getData(Request $request) {
        if ($request->ajax()) {
            $data = AccessControl::latest()->get();
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
                            })
                            ->get();
                } else {
                    $w_personid = "((personid ilike '%" . $search_val_all . "%')";
                    $w_personid .= " or (firstname ilike '%" . $search_val_all . "%'))";
//                    dd([$w_personid, $search_val_all]);
                    $searchwhere = "%$search_val_all%";
                    $data = DB::table('fa_accesscontrol')
                            ->where(function ($query) use ($strdate, $enddate) {
                                $query->where('alarmtime', '>=', $strdate);
                                $query->where('alarmtime', '<=', $enddate);
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
                            $query->where('alarmtime', '>=', $strdate);
                            $query->where('alarmtime', '<=', $enddate);
                        })
//                    ->orWhere('personid','ilike',"%".$search_val_all."%")
//                    ->whereRaw($w_personid)
                        ->get();
            }
//            dd([$strdate, $enddate, $w_personid]);

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
                if (empty($new_data[$dt_access->personid])) {
                    $new_data[$dt_access->personid] = $dt_access;
                    $new_data[$dt_access->personid]->nama_personnel = (($dt_access->personid . '[' . $dt_access->firstname . ']'));
//                    $new_data[$dt_access->personid]->nama_personnel = '-';
                    for ($loopInOt = 0; $loopInOt < 5; $loopInOt++) {
                        $new_data[$dt_access->personid]->{"time_in_$loopInOt"} = '';
                        $new_data[$dt_access->personid]->{"time_ot_$loopInOt"} = '';
                    }
                }
                $swipetime[$dt_access->personid][$dt_access->accesstype][] = $dt_access->alarmtime;
            }
            /** sorting first IN and/or Last OUT * */
            $default_element_time = ['', '', '', '', ''];
            foreach ($swipetime as $personid => $direct) {
                //Per direction IN/OUT
                $swipetime[$personid]['IN'] = $default_element_time;
                $swipetime[$personid]['OUT'] = $default_element_time;
                $swipetime[$personid]['first_in'] = '0';
                $swipetime[$personid]['last_out'] = '0';
                $new_data[$personid]->work_date = '-';
                $new_data[$personid]->ot = '-';
                $new_data[$personid]->first_in = '0';
                $new_data[$personid]->last_out = '0';

//                var_dump($direct);echo PHP_EOL;
                foreach ($direct as $dir => $listtime) {
                    if ($dir == 'IN') {
                        sort($listtime);
                    } else {
                        rsort($listtime);
                    }
                    $listtime = array_slice($listtime, 0, 5);
                    $listtime = $listtime + $default_element_time;
                    $swipetime[$personid][$dir] = $listtime;
                }

                $inning_sorted_up = array_filter($swipetime[$personid]['IN']);
                $outing_sorted_down = array_filter($swipetime[$personid]['OUT']);
                sort($inning_sorted_up);
                rsort($outing_sorted_down);
                if (!empty($inning_sorted_up[0])) {
                    $swipetime[$personid]['first_in'] = $inning_sorted_up[0];
                    $new_data[$personid]->first_in = $inning_sorted_up[0];
                    $date_in = \DateTimeImmutable::createFromFormat($format, $inning_sorted_up[0]);
                    $new_data[$personid]->work_date = $date_in->format('Y-m-d');
                    if (!empty($outing_sorted_down[0])) {
                        $swipetime[$personid]['last_out'] = $outing_sorted_down[0];
                        $new_data[$personid]->last_out = $outing_sorted_down[0];
                        $date_ot = \DateTimeImmutable::createFromFormat($format, $outing_sorted_down[0]);
                        $interval = $date_in->diff($date_ot);
                        if ($interval) {
                            $new_data[$personid]->duration = $interval->format('%hH%iM');
                        } else {
                            $new_data[$personid]->duration = '0';
                        }
                    } else {
                        $new_data[$personid]->duration = '0';
                    }
                } else {
                    $new_data[$personid]->duration = '0';
                }
//var_dump($personid); echo PHP_EOL;
//dd($swipetime);
                //Time IN/OUT sorting for 5 columns
                $time_in_out = [];
                foreach ($swipetime[$personid]['IN'] as $dt_ins) {
                    $time_in_out[] = "$dt_ins" . "I";
                }
                foreach ($swipetime[$personid]['OUT'] as $dt_ins) {
                    $time_in_out[] = "$dt_ins" . "O";
                }
//                if($personid == 'SAPRI'){
//                    var_dump($time_in_out);
//                }
                sort($time_in_out);
//                if($personid == 'SAPRI'){
//                    var_dump($time_in_out);
//                }
                $loopIn = 0;
                $loopOt = 0;

                $total_rest = 0;
                $start_sum_total_rest = 0;
                $start_sum_out = "";
                $hours_rests = 0;
                $minutes_rests = 0;
                foreach ($time_in_out as $timing) {
                    $kode = substr($timing, -1);
                    $dttime = str_replace($kode, '', $timing);
                    if (empty($dttime)) {
                        continue;
                    }
                    if ($kode == 'O') {
//                        var_dump($dttime);echo PHP_EOL;
//                        $datetime = \DateTimeImmutable::createFromFormat($format, $dttime);
                        $new_data[$personid]->{"time_ot_$loopOt"} = $dttime;
                        $loopOt++;
                        $start_sum_total_rest = 1;
                        $start_sum_out = $dttime;
                    }
                    if ($kode == 'I') {
                        if ($start_sum_total_rest == 1) {
                            $start_sum_out1 = \DateTimeImmutable::createFromFormat("Y-m-d H:i:s", $start_sum_out);
                            $start_sum_in1 = \DateTimeImmutable::createFromFormat("Y-m-d H:i:s", $dttime);
                            if ($start_sum_out1 && $start_sum_in1) {
//                                var_dump([$start_sum_out1,$start_sum_in1]);
//                                dd([$start_sum_out1,$start_sum_in1]);
                                $interval = date_diff($start_sum_out1, $start_sum_in1);
//                            $interval = ((DateTimeImmutable)$start_sum_out1)->diff($start_sum_in1);
                                $hours_rests = $hours_rests + ($interval)->format("%h");
                                $minutes_rests = $minutes_rests + ($interval)->format("%i");
                            }
                            $start_sum_total_rest = 0;
                            $start_sum_out = "";
                        }
//                        $datetime = \DateTimeImmutable::createFromFormat($format, $dttime);
                        $new_data[$personid]->{"time_in_$loopIn"} = $dttime;
                        $loopOt = $loopIn;
                        $loopIn++;
                    }
                }
                //Rest Total
                $sisa_jam = round($hours_rests + ($minutes_rests / 60));
                if ($sisa_jam < 1) {
                    $sisa_jam = 0;
                }
//                $new_data[$personid]->no_urut = count($swipetime) + 1;
                $new_data[$personid]->total_rest = $sisa_jam . "h" . ($minutes_rests % 60) . "m";
            }

//            dd([$new_data]);

            /**
             * 1. First IN and LAST OUT
             * 2. 
             */
            $total_duration = 0;
//            foreach ($swipetime as $personid => $direct) {
//
//            }
            /**
             * ingat format laporan ada TIME IN - TIME OUT
             */
            $result = array_values($new_data);
            $no = 1;
            foreach($result as $keyr=>$rslt){
                $result[$keyr]->no_urut = $no;
                $no++;
            }
//            dd($new_data);
           
//            dd($result);
            $dttable = Datatables::of($result)->make(true);
            return $dttable;
        }
//        var_dump($request);
    }

    public function getDataFormatted_prev(Request $request) {
        if ($request->ajax()) {
            $strdate = $request->get('startdate');
            $strdate = "$strdate 00:00:01";
            $enddate = $request->get('enddate');
            $enddate = "$enddate 23:59:59";

            $data = DB::table('fa_accesscontrol')
                    ->where('alarmtime', '>=', $strdate)
                    ->where('alarmtime', '<=', $enddate)
                    ->get();
            $arr_data = $data->toArray();
//            dd($data);  
            if (!$arr_data || count($arr_data) < 1) {
                return Datatables::of($data)
                                ->make(true);
            }

            $swipetime = [];
            $new_data = [];
            $format = 'Y-m-d H:i:s';
            foreach ($arr_data as $dt_access) {
                if (empty($new_data[$dt_access->personid])) {
                    $new_data[$dt_access->personid] = $dt_access;
//                    $new_data[$dt_access->personid]->nama_personnel = '-';
                    $new_data[$dt_access->personid]->nama_personnel = ($dt_access->personid . PHP_EOL . '[' . $dt_access->firstname . ']');
                    for ($loopInOt = 0; $loopInOt < 5; $loopInOt++) {
                        $new_data[$dt_access->personid]->{"time_in_$loopInOt"} = '';
                        $new_data[$dt_access->personid]->{"time_ot_$loopInOt"} = '';
                    }
                }
                $swipetime[$dt_access->personid][$dt_access->accesstype][] = $dt_access->alarmtime;
            }
//dd([$new_data,$swipetime]);
            /** sorting first IN and/or Last OUT * */
            $default_element_time = ['', '', '', '', ''];
            foreach ($swipetime as $personid => $direct) {
                //Per direction IN/OUT
                $swipetime[$personid]['IN'] = $default_element_time;
                $swipetime[$personid]['OUT'] = $default_element_time;
                $swipetime[$personid]['first_in'] = '0';
                $swipetime[$personid]['last_out'] = '0';
                $new_data[$personid]->first_in = '0';
                $new_data[$personid]->last_out = '0';

//                var_dump($direct);echo PHP_EOL;
                foreach ($direct as $dir => $listtime) {
                    if ($dir == 'IN') {
                        sort($listtime);
                    } else {
                        rsort($listtime);
                    }
                    $listtime = array_slice($listtime, 0, 5);
                    $listtime = $listtime + $default_element_time;
                    $swipetime[$personid][$dir] = $listtime;
                }

                $inning_sorted_up = array_filter($swipetime[$personid]['IN']);
                $outing_sorted_down = array_filter($swipetime[$personid]['OUT']);
                sort($inning_sorted_up);
                rsort($outing_sorted_down);
                if (!empty($inning_sorted_up[0])) {
                    $swipetime[$personid]['first_in'] = $inning_sorted_up[0];
                    $new_data[$personid]->first_in = $inning_sorted_up[0];
                    if (!empty($outing_sorted_down[0])) {
                        $swipetime[$personid]['last_out'] = $outing_sorted_down[0];
                        $new_data[$personid]->last_out = $outing_sorted_down[0];
                        $date_in = \DateTimeImmutable::createFromFormat($format, $inning_sorted_up[0]);
                        $date_ot = \DateTimeImmutable::createFromFormat($format, $outing_sorted_down[0]);
                        $interval = $date_in->diff($date_ot);
                        if ($interval) {
                            $new_data[$personid]->duration = $interval->format('%hH%iM');
                        } else {
                            $new_data[$personid]->duration = '0';
                        }
                    } else {
                        $new_data[$personid]->duration = '0';
                    }
                } else {
                    $new_data[$personid]->duration = '0';
                }
//var_dump($personid); echo PHP_EOL;
//dd($swipetime);
                //Time IN/OUT sorting for 5 columns
                $time_in_out = [];
                foreach ($swipetime[$personid]['IN'] as $dt_ins) {
                    $time_in_out[] = "$dt_ins" . "I";
                }
                foreach ($swipetime[$personid]['OUT'] as $dt_ins) {
                    $time_in_out[] = "$dt_ins" . "O";
                }
//                if($personid == 'SAPRI'){
//                    var_dump($time_in_out);
//                }
                sort($time_in_out);
//                if($personid == 'SAPRI'){
//                    var_dump($time_in_out);
//                }
                $loopIn = 0;
                $loopOt = 0;

                $total_rest = 0;
                $start_sum_total_rest = 0;
                $start_sum_out = "";
                $hours_rests = 0;
                $minutes_rests = 0;
                foreach ($time_in_out as $timing) {
                    $kode = substr($timing, -1);
                    $dttime = str_replace($kode, '', $timing);
                    if (empty($dttime)) {
                        continue;
                    }
                    if ($kode == 'O') {
//                        var_dump($dttime);echo PHP_EOL;
//                        $datetime = \DateTimeImmutable::createFromFormat($format, $dttime);
                        $new_data[$personid]->{"time_ot_$loopOt"} = $dttime;
                        $loopOt++;
                        $start_sum_total_rest = 1;
                        $start_sum_out = $dttime;
                    }
                    if ($kode == 'I') {
                        if ($start_sum_total_rest == 1) {
                            $start_sum_out1 = \DateTimeImmutable::createFromFormat("Y-m-d H:i:s", $start_sum_out);
                            $start_sum_in1 = \DateTimeImmutable::createFromFormat("Y-m-d H:i:s", $dttime);
                            $interval  =  ($start_sum_out1)->diff($start_sum_in1);
                            $hours_rests = $hours_rests + ($interval)->format("%h");
                            $minutes_rests = $minutes_rests + ($interval)->format("%i");
                            $start_sum_total_rest = 0;
                            $start_sum_out = "";
                        }
//                        $datetime = \DateTimeImmutable::createFromFormat($format, $dttime);
                        $new_data[$personid]->{"time_in_$loopIn"} = $dttime;
                        $loopOt = $loopIn;
                        $loopIn++;
                    }
                }
                //Rest Total
                $new_data[$personid]->total_rest = ($hours_rests + ($minutes_rests / 60)) . "h" . ($minutes_rests % 60) . "m";
            }

//            dd([$new_data]);

            /**
             * 1. First IN and LAST OUT
             * 2. 
             */
            $total_duration = 0;
//            foreach ($swipetime as $personid => $direct) {
//
//            }
            /**
             * ingat format laporan ada TIME IN - TIME OUT
             */
            $result = array_values($new_data);
            return Datatables::of($result)
                            ->make(true);
        }
//        var_dump($request);
    }

    public function getDataFormatted_att(Request $request) {
        if ($request->ajax()) {
            $data = DB::table('fa_attendance')
                    ->where('swipetime', '>=', $request->get('startdate'))
                    ->where('swipetime', '<=', $request->get('enddate'))
//                    ->where('personnelname', '=', '1PDP/IOI/0422/33013 MUHAMMAD SY')
//                    ->where('personnelname', '=', '1PDP/IOI/0704/26019 AZHARI BIN')
                    ->get();
            $arr_data = $data->toArray();
//            dd($data);
            if (!$arr_data || count($arr_data) < 1) {
                return Datatables::of($data)
                                ->make(true);
            }

            $swipetime = [];
            $new_data = [];
            $format = 'Y-m-d H:i:s';
            foreach ($arr_data as $dt_attendance) {
                if (empty($new_data[$dt_attendance->personnelcode])) {
                    $new_data[$dt_attendance->personnelcode] = $dt_attendance;
                    for ($loopInOt = 0; $loopInOt < 5; $loopInOt++) {
                        $new_data[$dt_attendance->personnelcode]->{"time_in_$loopInOt"} = '';
                        $new_data[$dt_attendance->personnelcode]->{"time_ot_$loopInOt"} = '';
                    }
                }
                $swipetime[$dt_attendance->personnelcode][$dt_attendance->swipdirection][] = $dt_attendance->swipetime;
            }
//dd([$new_data,$swipetime]);
            /** sorting first IN and/or Last OUT * */
            $default_element_time = ['', '', '', '', ''];
            foreach ($swipetime as $personnelcode => $direct) {
                //Per direction IN/OUT
                $swipetime[$personnelcode]['IN'] = $default_element_time;
                $swipetime[$personnelcode]['OUT'] = $default_element_time;
                foreach ($direct as $dir => $listtime) {
                    if ($dir == 'IN') {
                        sort($listtime);
                    } else {
                        rsort($listtime);
                    }
                    $listtime = array_slice($listtime, 0, 5);
                    $listtime = $listtime + $default_element_time;
                    $swipetime[$personnelcode][$dir] = $listtime;
                }

                $swipetime['first_in'] = '0';
                $swipetime['last_out'] = '0';
                $new_data[$personnelcode]->first_in = '0';
                $new_data[$personnelcode]->last_out = '0';
                $inning_sorted_up = array_filter($swipetime[$personnelcode]['IN']);
                $outing_sorted_down = array_filter($swipetime[$personnelcode]['OUT']);
                sort($inning_sorted_up);
                rsort($outing_sorted_down);
                if (!empty($inning_sorted_up[0]) && !empty($outing_sorted_down[0])) {
                    $swipetime['first_in'] = $inning_sorted_up[0];
                    $swipetime['last_out'] = $outing_sorted_down[0];
                    $new_data[$personnelcode]->first_in = $inning_sorted_up[0];
                    $new_data[$personnelcode]->last_out = $outing_sorted_down[0];
                    $date_in = \DateTimeImmutable::createFromFormat($format, $inning_sorted_up[0]);
                    $date_ot = \DateTimeImmutable::createFromFormat($format, $outing_sorted_down[0]);
                    $interval = $date_in->diff($date_ot);
                    if ($interval) {
                        $new_data[$personnelcode]->duration = $interval->format('%hH%iM');
                    } else {
                        $new_data[$personnelcode]->duration = '0';
                    }
                } else {
                    $new_data[$personnelcode]->duration = '0';
                }
                //Time IN/OUT sorting for 5 columns
                $time_in_out = [];
                foreach ($swipetime[$personnelcode]['IN'] as $dt_ins) {
                    $time_in_out[] = "$dt_ins" . "I";
                }
                foreach ($swipetime[$personnelcode]['OUT'] as $dt_ins) {
                    $time_in_out[] = "$dt_ins" . "O";
                }
                sort($time_in_out);
                $loopIn = 0;
                $loopOt = 0;
                foreach ($time_in_out as $timing) {
                    $kode = substr($timing, -1);
                    $dttime = str_replace($kode, '', $timing);
                    if ($kode == 'I') {
                        $datetime = \DateTimeImmutable::createFromFormat('%m%A', $dttime);
                        $new_data[$personnelcode]->{"time_in_$loopIn"} = $dttime;
//                        $new_data[$personnelcode]->{"time_ot_$loopOt"} = '';
                        $loopIn++;
                    }
                    if ($kode == 'O') {
//                        $new_data[$personnelcode]->{"time_in_$loopIn"} = '';
                        \DateTimeImmutable::createFromFormat($format2, $inning_sorted_up[0]);
                        $new_data[$personnelcode]->{"time_ot_$loopOt"} = $dttime;
                        $loopOt++;
                    }
                }
//var_dump($time_in_out);
//                $swipetime['duration'] = $interval->format('%hH%iM');
            }
//            dd([$new_data]);

            /**
             * 1. First IN and LAST OUT
             * 2. 
             */
            $total_duration = 0;
//            foreach ($swipetime as $personnelcode => $direct) {
//
//            }
            /**
             * ingat format laporan ada TIME IN - TIME OUT
             */
            $result = array_values($new_data);
            return Datatables::of($result)
                            ->make(true);
        }
//        var_dump($request);
    }

}
