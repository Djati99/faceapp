<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Attendance;
use App\Models\AccessControl;
use App\Models\AccessControlIn;
use App\Models\AccessControlOut;
use App\Models\RequestLog;

class FaceApiController extends BaseController {

    use AuthorizesRequests,
        DispatchesJobs,
        ValidatesRequests;

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function keep_alive(Request $request) {
  /*       $zone = config('face.API_ZONE');
        if ($zone == 'MY') {
            date_default_timezone_set('Asia/Kuala_Lumpur');
        } else {
            date_default_timezone_set('Asia/Jakarta');
        } */
        $report_setting = DB::table('fa_setting')->latest('fa_setting_id')->first();
        $ip_server = $report_setting->ip_server_fr;
        $ops_unit = $report_setting->unit_name;
//dd($report_setting);
        //Storage::disk('local')->put('_token.txt', 'CHECK');
//        $now = date('Y-m-d H:i:s');
//        $this->log_event([], $ip_server, $now, 'keep_alive <-> start');
//        die();
        if ((Storage::disk('local')->exists('_token.txt'))) {
            $isi_token = Storage::disk('local')->get('_token.txt');

            if ($isi_token) {
                $exploded_isi_token = explode("|", $isi_token);
                if (count($exploded_isi_token) >= 3) {
                    if ($exploded_isi_token[0] == date('Ymd')) {
//            dd($exploded_isi_token[0]);
                        //Sudah pernah looping / pernah run authentication
                        // do heartbeat
                        $datetimestamp = strtotime('+25 minutes', strtotime("$exploded_isi_token[0] $exploded_isi_token[1]"));
                        $is_run_auth = Storage::disk('local')->get('_run_cron.txt');
                        if ($is_run_auth == 'Y') {
                            //Storage::disk('local')->put('_token.txt', $datetimestamp."-".strtotime('now'));
//        $now = date('Y-m-d H:i:s');
//        $this->log_event([], strtotime('now'), $now, 'do-auth-check');
//                            dd([$datetimestamp,strtotime('now')]);
                            if ($datetimestamp < strtotime('now')) {
                                $this->do_auth($ip_server);
                            } else {
                                $this->do_heartbeat($ip_server, $exploded_isi_token);
                            }
                        }
                    } else {
                        //first auth
                        $this->do_auth($ip_server);
                    }
                } else {
                    //re run auth
                    $this->do_auth($ip_server);
                }
            } else {
                //first auth
                $this->do_auth($ip_server);
            }
        } else {
            $this->do_auth($ip_server);
        }
    }

    public function crawling_passing_attendance(Request $request) {
        /**
         * 
         * To-do:
         * 1. hit api attandance
         * 2. if success, pass data to CPI, then log
         * 3. if fail,log the error, back to no.1
         */
        $zone = config('face.API_ZONE');
        if ($zone == 'MY') {
            date_default_timezone_set('Asia/Kuala_Lumpur');
        } else {
            date_default_timezone_set('Asia/Jakarta');
        }
        $report_setting = DB::table('fa_setting')->latest('fa_setting_id')->first();
        $ip_server = $report_setting->ip_server_fr;
        $ops_unit = $report_setting->unit_name;

        $now = date('Y-m-d H:i:s');
        $isi_token = Storage::disk('local')->get('_token.txt');
        if ($isi_token) {
            $exploded_isi_token = explode("|", $isi_token);
            if (count($exploded_isi_token) >= 3) {
                if ($exploded_isi_token[0] == date('Ymd')) {
                    $_token = $exploded_isi_token[2];

                    //Access IN
                    $response_fr = $this->crawling_face_recognition_in($request, $_token, $ip_server);
                    if ($response_fr['status'] == 1) {
//                        dd($response_fr);
                        $list_attendance = $response_fr['data']['pageData'];
                        /**
                         * Ada 2 device absensi :
                         * 1. clock IN
                         * 2. clock OUT
                         * 
                         * harus disimpan FLAG IN/OUT
                         */
                        if (count($list_attendance) > 0) {
                            foreach ($list_attendance as $dt_att) {
                                if (empty($dt_att['personId'])) {
                                    continue;
                                }
                                if (empty($dt_att['firstName'])) {
                                    continue;
                                }
                                /**
                                 * 2023/06/06
                                 * ada case ID worker lebih dari 30 char
                                 * 
                                 * bikin error
                                 * 
                                 * harus dibuang
                                 */
                                if (strlen($dt_att['firstName']) > 30) {
                                    continue;
                                }                                
                                unset($dt_att['id']);
                                if (strtoupper($dt_att['deviceName']) == $report_setting->ip_clock_in) {
                                    $direction = "IN";
                                } else {
                                    $direction = "OUT";
                                }
                                $obj_data = (object) $dt_att;
                                $this->insert_access($ops_unit, $obj_data, $direction, $now);
//                            $this->insert_access_in($obj_data, 'IN', $now);
                            }
                            $responses = array(
                                'status' => 'success',
                                'data' => [
                                    array(
                                        'code' => 200,
                                        'message' => 'OK - Access control - Data FR Terambil'
                                    )
                                ]
                            );
                        } else {

                            $responses = array(
                                'status' => 'success',
                                'data' => [
                                    array(
                                        'code' => 200,
                                        'message' => 'OK - Access control - Data FR Kosong'
                                    )
                                ]
                            );
                        }
                        $this->log_event($request, $responses, $now, 'crawling_passing_attendance <-> success');
                    } else {
                        $str_log = date('Y-m-d H:i:s') . ":[GET-Access Control][" . $response_fr['code'] . "][" . $response_fr['message'] . "]";
                        $this->log_event([], $response_fr, $now, 'crawling_passing_attendance <-> fail');
//                        Log::info($str_log);
                    }
                }
            }
        }
    }

    protected function insert_attendance($att, $direction, $now) {
        $newAttendance = new Attendance();

        $newAttendance->personnelcode = $att->code;
        $newAttendance->personnelname = $att->name;
        $newAttendance->deptname = $att->deptName;
        $newAttendance->cardnumber = $att->cardNumber;
        $newAttendance->eventname = $att->eventName;
        $newAttendance->swipelocation = $att->swipeLocation;
        $newAttendance->swipdirection = $direction;
        $newAttendance->swipetime = $att->swipeTime;
        $newAttendance->created_at = $now;
        $newAttendance->save();
    }

    protected function insert_access($ops_unit, $att, $direction, $now) {
        if (AccessControl::where(
                        [
                            ['personid', '=', $att->personId],
                            ['channelname', '=', $att->channelName],
                            ['alarmtime', '=', date('Y-m-d H:i:s', $att->alarmTime)],
                        ])->count() > 0) {
            // user found
        } else {
            $newAccess = new AccessControl();
            foreach ($att as $idx => $vals) {
                $newAccess->{strtolower($idx)} = $vals;
            }
            $newAccess->alarmtime = date('Y-m-d H:i:s', $att->alarmTime);

//            if (strtoupper($att->channelName) == 'DOOR2') {
//                $direction = "OUT";
//            } else {
//                $direction = "IN";
//            }
            unset($newAccess->alarmtypename);
            unset($newAccess->captureimageurl);
            
            $newAccess->accesstype = $direction;
            $newAccess->unit_name = $ops_unit;
            $newAccess->created_at = $now;
            $newAccess->save();
        }
    }

    protected function insert_access_out($att, $direction, $now) {
        if (AccessControlOut::where(
                        [
                            ['personid', '=', $att->personId],
                            ['channelname', '=', $att->channelName],
                            ['alarmtime', '=', date('Y-m-d H:i:s', $att->alarmTime)],
                        ])->count() > 0) {
            // user found
        } else {
            $newAccess = new AccessControlOut();
            foreach ($att as $idx => $vals) {
                $newAccess->{strtolower($idx)} = $vals;
            }

            $newAccess->alarmtime = date('Y-m-d H:i:s', $att->alarmTime);
            $newAccess->accesstype = 'OUT';
            $newAccess->created_at = $now;
            $newAccess->save();
        }
    }

    protected function insert_access_in($att, $direction, $now) {
//        $strcek = "select * from fa_accesscontrol where personid ='HON' and channelname ='Door1' and alarmtime ='2022-11-22 08:07:29'";
        if (AccessControlIn::where(
                        [
                            ['personid', '=', $att->personId],
                            ['channelname', '=', $att->channelName],
                            ['alarmtime', '=', date('Y-m-d H:i:s', $att->alarmTime)],
                        ])->count() > 0) {
            // user found
        } else {
            $newAccess = new AccessControlIn();
            foreach ($att as $idx => $vals) {
                $newAccess->{strtolower($idx)} = $vals;
            }

            $newAccess->alarmtime = date('Y-m-d H:i:s', $att->alarmTime);
            $newAccess->accesstype = 'IN';
            $newAccess->created_at = $now;
            $newAccess->save();
        }
    }

    protected function log_event($params, $responses, $saveNow = '', $url = '', $sts = "OK") {
        $newLog = new RequestLog();

        if (empty($saveNow)) {
            $now = date('Y-m-d H:i:s');
        } else {
            $now = $saveNow;
        }

        $newLog->transaction_type = 'ATTENDANCE';
        $newLog->url = $url;
        $newLog->params = json_encode($params);
        $newLog->response_status = $sts;
        $newLog->response_message = json_encode($responses);
        $newLog->created_at = $now;
        $newLog->save();
    }

    protected function crawling_face_recognition_in(Request $request, $_token, $ip_server = null) {
        $data_now = date(date('Y-m-d'), strtotime(' -1 day'));    // previous day ;
        $server = config('face.API_FACEAPI_DOMAIN');

        if (empty($ip_server)) {
            
        } else {
            $server = $ip_server;
        }
        $urlinit = "https://$server/obms/api/v1.1/acs/access/record/fetch/page";
        $ch = curl_init($urlinit);
//        $timestamp_start = strtotime('2022-11-22' . " 00:00:01");
//        $timestamp_end = strtotime('2022-11-22' . " 23:59:59");
        /**
         * Get Data from 30 minutes before now
         */
        $zone = config('face.API_ZONE');
        if ($zone == 'MY') {
            date_default_timezone_set('Asia/Kuala_Lumpur');
            $mundur_setengah_jam = "-90 minutes";
        } else {
            $mundur_setengah_jam = "-30 minutes";
            date_default_timezone_set('Asia/Jakarta');
        }
        /*
          get the latest timestamp data successfully pulled from dss
         */
        $datacek = DB::table('fa_accesscontrol')
                ->select('fa_accesscontrol_id', 'alarmtime')
                ->whereRaw('alarmtime is not null')
                ->offset(0)
                ->orderBy('alarmtime', 'desc')
                ->limit(1)
                ->first();
        //dd($datacek);
        //$arr_data = $datacek->toArray();
        if ($datacek->alarmtime < date('Y-m-d H:i')) {
            $timestamp_start = strtotime($datacek->alarmtime);
        } else {
            $timestamp_start = strtotime(date('Y-m-d H:i:s', strtotime("$mundur_setengah_jam")));
        }

//	$date_start = date('Y-m-d');
        //      $date_start = \DateTime::createFromFormat('Y-m-d H:i A', "2023-03-05 00:01 am");
        //    $timestamp_start = (int)$date_start->format('U');
        //$timestamp_start = strtotime(date('Y-m-d'). " 00:01:01 PM");
        $timestamp_end = strtotime(date('Y-m-d H:i:s'));

        // dd([$timestamp_start,$timestamp_end]);
//        $timestamp_start = strtotime ("2023-01-31 00:00:01");
//        $timestamp_end = strtotime ("2023-01-31 23:59:59");        
        $body_posted = '{
                        "page": "1",
                        "pageSize": "200",
                        "channelIds": [],
                        "personId": "",
                        "startTime": "' . $timestamp_start . '",
                        "endTime": "' . $timestamp_end . '"
                    }';
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'X-Subject-Token:' . $_token,
            'charset:UTF-8',
            'Content-Type:application/json'
                )
        );
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body_posted);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        $result = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        // dd([$result]);
        if ($httpcode == 200) {
            $return['status'] = 1;
            $parse_result = json_decode($result, 1);
//        dd([$parse_result]);
            if ($parse_result['code'] == 1000) {
                $return['data'] = $parse_result['data'];
            } else {
                $return['status'] = 0;
                $return['data'] = [];
                $return['code'] = $parse_result['code'];
                $return['message'] = $parse_result['desc'];
                $params = array(
                    'start_check' => "$data_now 00:00:01",
                    'end_check' => "$data_now 23:59:59",
                );
                $responses = array(
                    'status' => 'error',
                    'data' => [
                        array(
                            'code' => $parse_result['code'],
                            'message' => $parse_result['desc']
                        )
                    ]
                );
//                $this->log_event($params, $responses);
            }
        } else {
            $return['status'] = 0;
            $return['data'] = [];
            $return['code'] = $httpcode;
            $return['message'] = 'Connection error';
        }
        return $return;
    }

    protected function do_auth_false() {
        $_token = date('Ymd') . "|" . date('H:i:s') . "|" . "XXX-YYY-ZZZ";
        Storage::disk('local')->put('_token.txt', $_token);
        Storage::disk('local')->put('_run_cron.txt', "Y");
    }

    protected function do_auth($ip_server = null) {
        $data_post_auth = '{
                "userName": "system",
                "ipAddress": "",
                "clientType": "WINPC_V2"}';
        $server = config('face.API_FACEAPI_DOMAIN');
        if (empty($ip_server)) {
            
        } else {
            $server = $ip_server;
        }
        /** $server AMBIL DARI SETTINGAN di table setting* */
        $ch_token = curl_init("https://" . $server . "/brms/api/v1.0/accounts/authorize");

        curl_setopt($ch_token, CURLOPT_POSTFIELDS, $data_post_auth);
        curl_setopt($ch_token, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
        curl_setopt($ch_token, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch_token, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch_token, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch_token, CURLOPT_CUSTOMREQUEST, 'POST');
        $result_token = curl_exec($ch_token);
//        var_dump($result_token);exit;
//        $httpcode_token = curl_getinfo($ch_token, CURLINFO_HTTP_CODE);
        curl_close($ch_token);
        $decoded_res_token = json_decode($result_token, 1);
//        dd($decoded_res_token);
        $_realm = empty($decoded_res_token["realm"]) ? 'DSS' : $decoded_res_token["realm"];
        $_rndkey = $decoded_res_token["randomKey"];
        $_pubkey = $decoded_res_token["publickey"];
        $_enctyp = $decoded_res_token["encryptType"];
//dd($decoded_res_token);
        $userName = config('face.API_DSS_USER');
        $password = config('face.API_DSS_PWD');

        $md5_1 = md5($password);
        $md5_2 = md5("$userName$md5_1");
        $md5_3 = md5($md5_2);
        $prm_md5_4 = "$userName:$_realm:$md5_3";
        $md5_4 = md5($prm_md5_4);

        $signature = md5("$md5_4:$_rndkey");

        /**
         * B. Second authentication
         */
        $data_post2 = array(
            "mac" => "",
            "signature" => "$signature",
            "userName" => "system",
            "randomKey" => "$_rndkey",
            "publicKey" => "$_pubkey",
            "encryptType" => "MD5",
            "ipAddress" => "",
            "clientType" => "WINPC_V2",
            "userType" => "0"
        );
        $encoded_post2 = json_encode($data_post2);
        $ch_token2 = curl_init("https://" . $server . "/brms/api/v1.0/accounts/authorize");
//        $ch_token2 = curl_init("https://" . $server . "/admin/API/v1.0/accounts/authorize");

        curl_setopt($ch_token2, CURLOPT_POSTFIELDS, $encoded_post2);
        curl_setopt($ch_token2, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
        curl_setopt($ch_token2, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch_token2, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch_token2, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch_token2, CURLOPT_CUSTOMREQUEST, 'POST');
        $result_token2 = curl_exec($ch_token2);

        curl_close($ch_token2);
        $decoded_res_token2 = json_decode($result_token2, 1);
//dd($decoded_res_token2);
        /**
         * C. populate attendance
         * C.1 create Heartbeat every 22 seconds
         */
        $_token = $decoded_res_token2["token"];
        $_token = date('Ymd') . "|" . date('H:i:s') . "|$_token";
        $now = date('Y-m-d H:i:s');
        $this->log_event([], $_token, $now, 'do-auth');
        Storage::disk('local')->put('_token.txt', $_token);
        Storage::disk('local')->put('_run_cron.txt', "Y");
    }

    protected function do_heartbeat_false($exploded_isi_token) {
        $_token = $exploded_isi_token[2];
        $_token = date('Ymd') . "|" . date('H:i:s') . "|$_token";
        Storage::disk('local')->put('_token.txt', $_token);
    }

    protected function do_heartbeat($ip_server = null, $exploded_isi_token) {
        $_token = trim($exploded_isi_token[2]);
        $server = config('face.API_FACEAPI_DOMAIN');
        if (empty($ip_server)) {
            
        } else {
            $server = $ip_server;
        }
        $ch = curl_init("https://" . $server . "/brms/api/v1.0/accounts/keepalive");
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type:application/json',
            'X-Subject-Token:' . $_token
                )
        );
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
        $result = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        $exploded_isi_token[3] = strtotime('now');
        $imploded_isi = implode("|", $exploded_isi_token);
        Storage::disk('local')->put('_token.txt', $imploded_isi);
        $now = date('Y-m-d H:i:s');
//        $this->log_event([], 'do_heartbeat', $now, 'do_heartbeat');        
//        dd($httpcode);
    }

    protected function passing_to_cpi(Request $request) {

        $zone = config('face.API_ZONE');
        if ($zone == 'MY') {
            date_default_timezone_set('Asia/Kuala_Lumpur');
            $mundur_setengah_jam = "-90 minutes";
        } else {
            $mundur_setengah_jam = "-30 minutes";
            date_default_timezone_set('Asia/Jakarta');
        }
        $report_setting = DB::table('fa_setting')->latest('fa_setting_id')->first();
        $ip_server = $report_setting->ip_server_fr;
        $ops_unit = $report_setting->unit_name;

//        $strdate = "2022-11-23";
        $strdate = date('Y-m-d');
//        $enddate = $strdate;
//        $strdate = date('Y-m-d 00:00:01');
//        $enddate = date('Y-m-d 23:59:59');
//        $strdate = '2022-12-07';
//        $enddate = '2023-01-26';
        $data = DB::table('fa_accesscontrol')
                ->select('fa_accesscontrol_id', 'devicecode', 'devicename', 'channelid', 'channelname', 'alarmtypeid', 'personid', 'firstname', 'lastname', 'alarmtime', 'accesstype', 'unit_name')
                ->where(function ($query) use ($strdate) {
                    $query->whereRaw("to_char(alarmtime::date, 'YYYY-MM-DD') = '$strdate'");
                })
                ->whereIn('sent_cpi', ['F', 'N'])
//                ->where('sent_cpi', '!=', 'F')
                ->offset(0)
                ->orderBy('alarmtime', 'asc')
                ->limit(200)
                ->get();
        $arr_data = $data->toArray();
        if (!$data || (count($arr_data) < 1)) {
            $responses = array(
                'status' => 'success',
                'data' => [
                    array(
                        'code' => 200,
                        'message' => 'OK - data tidak ada'
                    )
                ]
            );
            $this->log_event([], $responses, '', 'passing_to_cpi_oto');
        } else {
            //dd($arr_data);

            $sent_data = [];
            $updated_ids = [];

            $list_prfnr = [];
            foreach ($arr_data as $dt_attendance) {
                $updated_ids[] = $dt_attendance->fa_accesscontrol_id;
//                $att['MANDT'] = '';
                $att['RECORD_ID'] = $dt_attendance->fa_accesscontrol_id;
                $att['PRFNR'] = $ops_unit;
                $att['EMPNR'] = $dt_attendance->firstname;
                $att['SOURCE'] = "D";
//                            }
                $att_time = explode(" ", $dt_attendance->alarmtime);
//                $att['SDATE'] = str_replace("-","",$att_time[0]);
                $att['SDATE'] = $att_time[0];
//                $att['STIME'] = str_replace(":","",$att_time[1]);
                $att['STIME'] = $att_time[1];

                if ($dt_attendance->accesstype == "OUT") {
                    $att['TYPE'] = "O";
                } else {
                    $att['TYPE'] = "I";
                }
                $att['ERNAM'] = "";
                $att['ERDAT'] = "";
                $att['ERZET'] = "";
                $att['REMARK'] = "";
//                $att['AENAM'] = "";
//                $att['AEDAT'] = "";
//                $att['AEZET'] = "";
//                $att['APNAM'] = "";
//                $att['APDAT'] = "";
//                $att['APZET'] = "";
//                $att['DELETED'] = "";

                $sent_data[$att['PRFNR']][] = $att;
            }

//            dd($sent_data);
            //sent to cpi
            $prfnr_list = array_keys($sent_data);
            $res = [];
            $error_count = [];
            $delivered_ids = [];
            if (count($prfnr_list) > 1) {
                for ($i = 0; $i < count($prfnr_list) - 1; $i++) {
                    $dtsent1 = $sent_data[$prfnr_list[$i]];
                    $dtsent = $dtsent1;
                    foreach ($dtsent as $ksent => $oksent) {
                        unset($dtsent[$ksent]['RECORD_ID']);
                    }
                    $response0 = send_time_attendance_to_cpi($dtsent, $prfnr_list[$i], false);
                    if (!empty($response0['status_code'])) {
                        if ($response0['status_code'] == 500) {
                            $this->log_event($sent_data, $response0['feedback'], '', 'passing_to_cpi_oto', 'FAIL');
                            $responses = array(
                                'status' => 'success',
                                'data' => [
                                    array(
                                        'code' => 500,
                                        'message' => 'Fail - data not transfered',
                                        'original' => $response0['feedback']
                                    )
                                ]
                            );
                            return $responses;
                        } elseif ($response0['status_code'] == 200) {
                            if (empty($response0['feedback']['ERROR'])) {
                                $res[] = $response0;
                                $delivered_ids[] = $dtsent1[0]['RECORD_ID'];
                            } else {
                                $error_count[] = $response0['feedback']['ERROR'];
                                $res[] = $response0;
                                /**
                                 * Handdle error
                                 */
                            }
                        } else {
                            $this->log_event($sent_data, $response0['data'], '', 'passing_to_cpi_oto', 'FAIL');
                            $responses = array(
                                'status' => 'success',
                                'data' => [
                                    array(
                                        'code' => $response0['status_code'],
                                        'message' => 'Fail - data not transfered',
                                        'original' => $response0['data']
                                    )
                                ]
                            );
                            return $responses;
                        }
                    } else {
                        if (empty($response0['feedback']['ERROR'])) {
                            $res[] = $response0;
                            $delivered_ids[] = $dtsent1[0]['RECORD_ID'];
                        } else {
                            $error_count[] = $response0['feedback']['ERROR'];
                            $res[] = $response0;
                            /**
                             * Handdle error
                             */
                        }
                    }
//                    echo "$i <br/>";
                }
                $dtsent1 = $sent_data[$prfnr_list[count($prfnr_list) - 1]];
                $dtsent = $dtsent1;
                foreach ($dtsent as $ksent => $oksent) {
                    unset($dtsent[$ksent]['RECORD_ID']);
                }
                $response1 = send_time_attendance_to_cpi($dtsent, $prfnr_list[count($prfnr_list) - 1], true);
                if (!empty($response1['status_code'])) {
                    if ($response1['status_code'] == 500) {
                        $this->log_event($sent_data, $response1['feedback'], '', 'passing_to_cpi_oto', 'FAIL');
                        $responses = array(
                            'status' => 'success',
                            'data' => [
                                array(
                                    'code' => 500,
                                    'message' => 'Fail - data not transfered',
                                    'original' => $response1['feedback']
                                )
                            ]
                        );
                        return $responses;
                    } elseif ($response1['status_code'] == 200) {
                        if (empty($response1['feedback']['ERROR'])) {
                            $res[] = $response1;
                            $delivered_ids[] = $dtsent1[0]['RECORD_ID'];
                        } else {
                            $error_count[] = $response1['feedback']['ERROR'];
                            $res[] = $response1;
                            /**
                             * Handdle error
                             */
                        }
                    } else {
                        $this->log_event($sent_data, $response1['data'], '', 'passing_to_cpi_oto', 'FAIL');
                        $responses = array(
                            'status' => 'success',
                            'data' => [
                                array(
                                    'code' => $response1['status_code'],
                                    'message' => 'Fail - data not transfered',
                                    'original' => $response1['data']
                                )
                            ]
                        );
                        return $responses;
                    }
                } else {
                    if (empty($response1['feedback']['ERROR'])) {
                        $res[] = $response1;
                        $delivered_ids[] = $dtsent1[0]['RECORD_ID'];
                    } else {
                        $error_count[] = $response1['feedback']['ERROR'];
                        $res[] = $response1;
                        /**
                         * Handdle error
                         */
                    }
                }
                //  dd($response1['feedback']);                
            } else {
                $dtsent1 = $sent_data[$prfnr_list[0]];
//                dd($dtsent1);
                $dtsent = $dtsent1;
                foreach ($dtsent as $ksent => $oksent) {
                    unset($dtsent[$ksent]['RECORD_ID']);
                }
                $response2 = send_time_attendance_to_cpi($dtsent, $prfnr_list[0], true);
                if (!empty($response2['status_code'])) {
                    if ($response2['status_code'] == 500) {
                        $this->log_event($sent_data, $response2['feedback'], '', 'passing_to_cpi_oto', 'FAIL');
                        $responses = array(
                            'status' => 'success',
                            'data' => [
                                array(
                                    'code' => 500,
                                    'message' => 'Fail - data not transfered',
                                    'original' => $response2['feedback']
                                )
                            ]
                        );
                        return $responses;
                    } elseif ($response2['status_code'] == 200) {
                        if (empty($response2['feedback']['ERROR'])) {
                            foreach ($dtsent1 as $oksent) {
                                $delivered_ids[] = $oksent['RECORD_ID'];
                            }
                            $res[] = $response2;
                        } else {
                            $error_count[] = $response2['feedback']['ERROR'];
                            $res[] = $response2;
                            /**
                             * Handdle error
                             */
                        }
                    } else {
                        $this->log_event($sent_data, $response2['data'], '', 'passing_to_cpi_oto', 'FAIL');
                        $responses = array(
                            'status' => 'success',
                            'data' => [
                                array(
                                    'code' => $response2['status_code'],
                                    'message' => 'Fail - data not transfered',
                                    'original' => $response2['data']
                                )
                            ]
                        );
                        return $responses;
                    }
                } else {
                    if (empty($response2['feedback']['ERROR'])) {
                        foreach ($dtsent1 as $oksent) {
                            $delivered_ids[] = $oksent['RECORD_ID'];
                        }
                        $res[] = $response2;
                    } else {
                        $error_count[] = $response2['feedback']['ERROR'];
                        $res[] = $response2;
                        /**
                         * Handdle error
                         */
                    }
                }
                //   dd($response2['feedback']);                
            }


            $responses = array(
                'status' => 'success',
                'data' => [
                    array(
                        'code' => 200,
                        'message' => 'OK - Data transferred',
                        'original' => $res
                    )
                ]
            );

            $affected = DB::table('fa_accesscontrol')
                    ->whereIn('fa_accesscontrol_id', $updated_ids)
                    ->update(['sent_cpi' => 'Y']);
            $this->log_event($sent_data, $responses, '', 'passing_to_cpi_oto');
            if (count($error_count) > 0) {
                $responses['status'] = 'fail';
                foreach ($error_count as $err) {
                    foreach ($err as $derr) {
                        $affected = DB::table('fa_accesscontrol')
                                ->where('firstname', $derr['EMPNR'])
                                ->where('alarmtime', "$derr[SDATE] $derr[STIME]")
                                ->update(['sent_cpi' => 'F', 'remark' => $derr['REMARK']]);
                    }
                }
                $this->log_event($sent_data, $responses, '', 'passing_to_cpi_oto');
            }
        }
        return $responses;
    }

    public function destroy(Product $product) {
        $product->delete();

        return redirect()->route('products.index')
                        ->with('success', 'Product deleted successfully');
    }

}
