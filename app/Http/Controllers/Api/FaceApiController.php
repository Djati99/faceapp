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
        $zone = config('face.API_ZONE');
        if ($zone == 'MY') {
            date_default_timezone_set('Asia/Kuala_Lumpur');
        } else {
            date_default_timezone_set('Asia/Jakarta');
        }
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
                        $datetimestamp = strtotime('+30 minutes', strtotime("$exploded_isi_token[0] $exploded_isi_token[1]"));
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
        $zone = config('face.API_ZONE');
        if ($zone == 'MY') {
            date_default_timezone_set('Asia/Kuala_Lumpur');
        } else {
            date_default_timezone_set('Asia/Jakarta');
        }        
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

    protected function log_event($params, $responses, $saveNow = '', $url = '') {
        $newLog = new RequestLog();

        if (empty($saveNow)) {
            $now = date('Y-m-d H:i:s');
        } else {
            $now = $saveNow;
        }

        $newLog->transaction_type = 'ATTENDANCE';
        $newLog->url = $url;
        $newLog->params = json_encode($params);
        $newLog->response_status = 'OK';
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
	if($datacek->alarmtime < date('Y-m-d H:i')){
		$timestamp_start = strtotime($datacek->alarmtime);
	}else{
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
        
        $result = '{"code":1000,"desc":"Success","data":{"pageData":[{"id":"46595","alarmTime":"1678083999","deviceCode":"1000003","deviceName":"10.10.126.23","channelId":"1000003$7$0$0","channelName":"Door1","alarmTypeId":"600005","personId":"IMRANBINUDDIN","firstName":"1SHL/IOI/0409/6915","lastName":""},{"id":"46594","alarmTime":"1678083877","deviceCode":"1000003","deviceName":"10.10.126.23","channelId":"1000003$7$0$0","channelName":"Door1","alarmTypeId":"600005","personId":"SABARUDDINBINSERE","firstName":"1SHL/IOI/0417/6973","lastName":""},{"id":"46593","alarmTime":"1678083855","deviceCode":"1000003","deviceName":"10.10.126.23","channelId":"1000003$7$0$0","channelName":"Door1","alarmTypeId":"600005","personId":"KARTINIBINTIBUBA","firstName":"1SHL/IOI/0415/6994","lastName":""},{"id":"46592","alarmTime":"1678083810","deviceCode":"1000003","deviceName":"10.10.126.23","channelId":"1000003$7$0$0","channelName":"Door1","alarmTypeId":"600005","personId":"RABANIABINTIYEMMI","firstName":"1SHL/IOI/0817/6970","lastName":""},{"id":"46591","alarmTime":"1678083754","deviceCode":"1000003","deviceName":"10.10.126.23","channelId":"1000003$7$0$0","channelName":"Door1","alarmTypeId":"600005","personId":"SUDIRMANBINBEDDU","firstName":"1SHL/IOI/1222/39504","lastName":""},{"id":"46590","alarmTime":"1678083608","deviceCode":"1000002","deviceName":"10.10.126.24","channelId":"1000002$7$0$0","channelName":"Door1","alarmTypeId":"600005","personId":"MOHDNOORAMIZAN","firstName":"1SHL/IOI/0722/35083","lastName":""},{"id":"46589","alarmTime":"1678083207","deviceCode":"1000003","deviceName":"10.10.126.23","channelId":"1000003$7$0$0","channelName":"Door1","alarmTypeId":"600005","personId":"KARMIANABINTIABDULKDIR","firstName":"1SHL/IOI/1122/38741","lastName":""},{"id":"46588","alarmTime":"1678083059","deviceCode":"1000002","deviceName":"10.10.126.24","channelId":"1000002$7$0$0","channelName":"Door1","alarmTypeId":"600005","personId":"ARSYADRIDWAN","firstName":"1SHL/IOI/0411/6894","lastName":""},{"id":"46587","alarmTime":"1678082798","deviceCode":"1000003","deviceName":"10.10.126.23","channelId":"1000003$7$0$0","channelName":"Door1","alarmTypeId":"600005","personId":"ALBASIRBINAZLAN","firstName":"1SHL/IOI/0715/6886","lastName":""},{"id":"46586","alarmTime":"1678082794","deviceCode":"1000003","deviceName":"10.10.126.23","channelId":"1000003$7$0$0","channelName":"Door1","alarmTypeId":"600005","personId":"IDRUSHAFID","firstName":"1SHL/IOI/0113/6914","lastName":""},{"id":"46585","alarmTime":"1678082617","deviceCode":"1000003","deviceName":"10.10.126.23","channelId":"1000003$7$0$0","channelName":"Door1","alarmTypeId":"600005","personId":"THERESABINTIALIBISUS","firstName":"ISHL/IOI/1205/11570","lastName":""},{"id":"46584","alarmTime":"1678082609","deviceCode":"1000003","deviceName":"10.10.126.23","channelId":"1000003$7$0$0","channelName":"Door1","alarmTypeId":"600005","personId":"JULIANABEED","firstName":"1SHL/IOI/0210/6991","lastName":""},{"id":"46583","alarmTime":"1678082584","deviceCode":"1000003","deviceName":"10.10.126.23","channelId":"1000003$7$0$0","channelName":"Door1","alarmTypeId":"600005","personId":"SHOSHOKWAH","firstName":"1SHL/IOI/0200/6988","lastName":""},{"id":"46582","alarmTime":"1678082046","deviceCode":"1000003","deviceName":"10.10.126.23","channelId":"1000003$7$0$0","channelName":"Door1","alarmTypeId":"600005","personId":"SIBATANTILI","firstName":"1SHL/IOI/0822/35295","lastName":""},{"id":"46581","alarmTime":"1678081874","deviceCode":"1000003","deviceName":"10.10.126.23","channelId":"1000003$7$0$0","channelName":"Door1","alarmTypeId":"600005","personId":"KASRINBINSALLEHWANG","firstName":"1SHL/IOI/0219/6923","lastName":""},{"id":"46580","alarmTime":"1678081844","deviceCode":"1000002","deviceName":"10.10.126.24","channelId":"1000002$7$0$0","channelName":"Door1","alarmTypeId":"600005","personId":"WAHIDAH","firstName":"1SHL/IOI/0417/6985","lastName":""},{"id":"46579","alarmTime":"1678081838","deviceCode":"1000002","deviceName":"10.10.126.24","channelId":"1000002$7$0$0","channelName":"Door1","alarmTypeId":"600005","personId":"MANDONG","firstName":"1SHL/IOI/0817/6929","lastName":""},{"id":"46578","alarmTime":"1678081090","deviceCode":"1000003","deviceName":"10.10.126.23","channelId":"1000003$7$0$0","channelName":"Door1","alarmTypeId":"600005","personId":"HASNIATIBINTIRAMLI","firstName":"1SHL/IOI/0215/6993","lastName":""},{"id":"46577","alarmTime":"1678080943","deviceCode":"1000002","deviceName":"10.10.126.24","channelId":"1000002$7$0$0","channelName":"Door1","alarmTypeId":"600005","personId":"ASRIANIBINTISUDIRMAN","firstName":"1SHL/IOI/0411/6895","lastName":""},{"id":"46576","alarmTime":"1678080939","deviceCode":"1000002","deviceName":"10.10.126.24","channelId":"1000002$7$0$0","channelName":"Door1","alarmTypeId":"600005","personId":"IZAMHARUNA","firstName":"1SHL/IOI/0411/6916","lastName":""},{"id":"46575","alarmTime":"1678080933","deviceCode":"1000002","deviceName":"10.10.126.24","channelId":"1000002$7$0$0","channelName":"Door1","alarmTypeId":"600005","personId":"MUHAMMADSYAMBINABSAN","firstName":"1SHL/IOI/1110/6954","lastName":""},{"id":"46574","alarmTime":"1678080929","deviceCode":"1000002","deviceName":"10.10.126.24","channelId":"1000002$7$0$0","channelName":"Door1","alarmTypeId":"600005","personId":"OMARBINYUNUS","firstName":"1SHL/IOI/0418/6966","lastName":""},{"id":"46573","alarmTime":"1678080570","deviceCode":"1000002","deviceName":"10.10.126.24","channelId":"1000002$7$0$0","channelName":"Door1","alarmTypeId":"600005","personId":"SURIADIBINDARWIS","firstName":"1SHL/IOI/0511/6984","lastName":""},{"id":"46572","alarmTime":"1678080516","deviceCode":"1000002","deviceName":"10.10.126.24","channelId":"1000002$7$0$0","channelName":"Door1","alarmTypeId":"600005","personId":"MOHAMMADASRULBINAMIR","firstName":"1SHL/IOI/0117/6936","lastName":""},{"id":"46571","alarmTime":"1678080510","deviceCode":"1000002","deviceName":"10.10.126.24","channelId":"1000002$7$0$0","channelName":"Door1","alarmTypeId":"600005","personId":"OSMANBINSAHARA","firstName":"1SHL/IOI/0219/6967","lastName":""},{"id":"46570","alarmTime":"1678080391","deviceCode":"1000003","deviceName":"10.10.126.23","channelId":"1000003$7$0$0","channelName":"Door1","alarmTypeId":"600005","personId":"IBRAHIMBINJOHAR","firstName":"1SHL/IOI/0511/6913","lastName":""},{"id":"46569","alarmTime":"1678080386","deviceCode":"1000003","deviceName":"10.10.126.23","channelId":"1000003$7$0$0","channelName":"Door1","alarmTypeId":"600005","personId":"ANSARBINAMBODAI","firstName":"1SHL/IOI/0712/6891","lastName":""},{"id":"46568","alarmTime":"1678080381","deviceCode":"1000003","deviceName":"10.10.126.23","channelId":"1000003$7$0$0","channelName":"Door1","alarmTypeId":"600005","personId":"IBRAHIMBINJOHAR","firstName":"1SHL/IOI/0511/6913","lastName":""},{"id":"46567","alarmTime":"1678080267","deviceCode":"1000002","deviceName":"10.10.126.24","channelId":"1000002$7$0$0","channelName":"Door1","alarmTypeId":"600005","personId":"RABANIABINTIYEMMI","firstName":"1SHL/IOI/0817/6970","lastName":""},{"id":"46566","alarmTime":"1678080263","deviceCode":"1000002","deviceName":"10.10.126.24","channelId":"1000002$7$0$0","channelName":"Door1","alarmTypeId":"600005","personId":"SABARUDDINBINSERE","firstName":"1SHL/IOI/0417/6973","lastName":""},{"id":"46565","alarmTime":"1678080232","deviceCode":"1000002","deviceName":"10.10.126.24","channelId":"1000002$7$0$0","channelName":"Door1","alarmTypeId":"600005","personId":"SUDIRMANBINBEDDU","firstName":"1SHL/IOI/1222/39504","lastName":""},{"id":"46564","alarmTime":"1678080218","deviceCode":"1000002","deviceName":"10.10.126.24","channelId":"1000002$7$0$0","channelName":"Door1","alarmTypeId":"600005","personId":"IMRANBINUDDIN","firstName":"1SHL/IOI/0409/6915","lastName":""},{"id":"46563","alarmTime":"1678080046","deviceCode":"1000002","deviceName":"10.10.126.24","channelId":"1000002$7$0$0","channelName":"Door1","alarmTypeId":"600005","personId":"HASNIATIBINTIRAMLI","firstName":"1SHL/IOI/0215/6993","lastName":""},{"id":"46562","alarmTime":"1678079653","deviceCode":"1000002","deviceName":"10.10.126.24","channelId":"1000002$7$0$0","channelName":"Door1","alarmTypeId":"600005","personId":"KARMIANABINTIABDULKDIR","firstName":"1SHL/IOI/1122/38741","lastName":""},{"id":"46561","alarmTime":"1678079140","deviceCode":"1000002","deviceName":"10.10.126.24","channelId":"1000002$7$0$0","channelName":"Door1","alarmTypeId":"600005","personId":"IDRUSHAFID","firstName":"1SHL/IOI/0113/6914","lastName":""},{"id":"46560","alarmTime":"1678078940","deviceCode":"1000002","deviceName":"10.10.126.24","channelId":"1000002$7$0$0","channelName":"Door1","alarmTypeId":"600005","personId":"ALBASIRBINAZLAN","firstName":"1SHL/IOI/0715/6886","lastName":""},{"id":"46559","alarmTime":"1678078937","deviceCode":"1000002","deviceName":"10.10.126.24","channelId":"1000002$7$0$0","channelName":"Door1","alarmTypeId":"600005","personId":"ALBASIRBINAZLAN","firstName":"1SHL/IOI/0715/6886","lastName":""},{"id":"46558","alarmTime":"1678078932","deviceCode":"1000002","deviceName":"10.10.126.24","channelId":"1000002$7$0$0","channelName":"Door1","alarmTypeId":"600005","personId":"KASRINBINSALLEHWANG","firstName":"1SHL/IOI/0219/6923","lastName":""},{"id":"46557","alarmTime":"1678078867","deviceCode":"1000002","deviceName":"10.10.126.24","channelId":"1000002$7$0$0","channelName":"Door1","alarmTypeId":"600005","personId":"SIBATANTILI","firstName":"1SHL/IOI/0822/35295","lastName":""},{"id":"46556","alarmTime":"1678078161","deviceCode":"1000002","deviceName":"10.10.126.24","channelId":"1000002$7$0$0","channelName":"Door1","alarmTypeId":"600005","personId":"KARTINIBINTIBUBA","firstName":"1SHL/IOI/0415/6994","lastName":""},{"id":"46555","alarmTime":"1678077103","deviceCode":"1000002","deviceName":"10.10.126.24","channelId":"1000002$7$0$0","channelName":"Door1","alarmTypeId":"600005","personId":"ANSARBINAMBODAI","firstName":"1SHL/IOI/0712/6891","lastName":""},{"id":"46554","alarmTime":"1678077092","deviceCode":"1000002","deviceName":"10.10.126.24","channelId":"1000002$7$0$0","channelName":"Door1","alarmTypeId":"600005","personId":"IBRAHIMBINJOHAR","firstName":"1SHL/IOI/0511/6913","lastName":""},{"id":"46553","alarmTime":"1678076739","deviceCode":"1000002","deviceName":"10.10.126.24","channelId":"1000002$7$0$0","channelName":"Door1","alarmTypeId":"600005","personId":"SHOSHOKWAH","firstName":"1SHL/IOI/0200/6988","lastName":""},{"id":"46552","alarmTime":"1678076678","deviceCode":"1000002","deviceName":"10.10.126.24","channelId":"1000002$7$0$0","channelName":"Door1","alarmTypeId":"600005","personId":"JULIANABEED","firstName":"1SHL/IOI/0210/6991","lastName":""},{"id":"46551","alarmTime":"1678076660","deviceCode":"1000002","deviceName":"10.10.126.24","channelId":"1000002$7$0$0","channelName":"Door1","alarmTypeId":"600005","personId":"THERESABINTIALIBISUS","firstName":"ISHL/IOI/1205/11570","lastName":""},{"id":"46550","alarmTime":"1678071683","deviceCode":"1000003","deviceName":"10.10.126.23","channelId":"1000003$7$0$0","channelName":"Door1","alarmTypeId":"600005","personId":"SHAHRULNIZAMBINSAHID","firstName":"1SHL/IOI/0722/35087","lastName":""},{"id":"46549","alarmTime":"1678068111","deviceCode":"1000003","deviceName":"10.10.126.23","channelId":"1000003$7$0$0","channelName":"Door1","alarmTypeId":"600005","personId":"MOHDNOORAMIZAN","firstName":"1SHL/IOI/0722/35083","lastName":""},{"id":"46548","alarmTime":"1678067764","deviceCode":"1000003","deviceName":"10.10.126.23","channelId":"1000003$7$0$0","channelName":"Door1","alarmTypeId":"600005","personId":"MAHMUDDINLAKING","firstName":"1SHL/IOI/0712/6928","lastName":""},{"id":"46547","alarmTime":"1678063572","deviceCode":"1000003","deviceName":"10.10.126.23","channelId":"1000003$7$0$0","channelName":"Door1","alarmTypeId":"600005","personId":"MANDONG","firstName":"1SHL/IOI/0817/6929","lastName":""},{"id":"46546","alarmTime":"1678062961","deviceCode":"1000003","deviceName":"10.10.126.23","channelId":"1000003$7$0$0","channelName":"Door1","alarmTypeId":"600005","personId":"ALFIANFERNANDEZHILVIN","firstName":"1SHL/IOI/0123/40298","lastName":""},{"id":"46545","alarmTime":"1678062878","deviceCode":"1000003","deviceName":"10.10.126.23","channelId":"1000003$7$0$0","channelName":"Door1","alarmTypeId":"600005","personId":"111"},{"id":"46544","alarmTime":"1678062875","deviceCode":"1000003","deviceName":"10.10.126.23","channelId":"1000003$7$0$0","channelName":"Door1","alarmTypeId":"600005","personId":"111"},{"id":"46543","alarmTime":"1678060809","deviceCode":"1000003","deviceName":"10.10.126.23","channelId":"1000003$7$0$0","channelName":"Door1","alarmTypeId":"600005","personId":"OMARBINYUNUS","firstName":"1SHL/IOI/0418/6966","lastName":""},{"id":"46542","alarmTime":"1678060806","deviceCode":"1000003","deviceName":"10.10.126.23","channelId":"1000003$7$0$0","channelName":"Door1","alarmTypeId":"600005","personId":"OSMANBINSAHARA","firstName":"1SHL/IOI/0219/6967","lastName":""},{"id":"46541","alarmTime":"1678060191","deviceCode":"1000003","deviceName":"10.10.126.23","channelId":"1000003$7$0$0","channelName":"Door1","alarmTypeId":"600005","personId":"BURHANBINBUBA","firstName":"1SHL/IOI/0311/6901","lastName":""},{"id":"46540","alarmTime":"1678059787","deviceCode":"1000003","deviceName":"10.10.126.23","channelId":"1000003$7$0$0","channelName":"Door1","alarmTypeId":"600005","personId":"MOHDHAFIZANBINUNDDIN","firstName":"1SHL/IOI/1117/6942","lastName":""},{"id":"46539","alarmTime":"1678058004","deviceCode":"1000003","deviceName":"10.10.126.23","channelId":"1000003$7$0$0","channelName":"Door1","alarmTypeId":"600005","personId":"JAAFARBINBULOH","firstName":"1SHL/IOI/0718/6999","lastName":""},{"id":"46538","alarmTime":"1678057808","deviceCode":"1000003","deviceName":"10.10.126.23","channelId":"1000003$7$0$0","channelName":"Door1","alarmTypeId":"600005","personId":"CONNIEANDREW","firstName":"1SHL/IOI/0212/6992","lastName":""},{"id":"46537","alarmTime":"1678057787","deviceCode":"1000003","deviceName":"10.10.126.23","channelId":"1000003$7$0$0","channelName":"Door1","alarmTypeId":"600005","personId":"MUHAMMADZULKIFLY","firstName":"1SHL/IOI/0417/6955","lastName":""},{"id":"46536","alarmTime":"1678057692","deviceCode":"1000003","deviceName":"10.10.126.23","channelId":"1000003$7$0$0","channelName":"Door1","alarmTypeId":"600005","personId":"JULIANABEED","firstName":"1SHL/IOI/0210/6991","lastName":""},{"id":"46535","alarmTime":"1678057670","deviceCode":"1000003","deviceName":"10.10.126.23","channelId":"1000003$7$0$0","channelName":"Door1","alarmTypeId":"600005","personId":"THERESABINTIALIBISUS","firstName":"ISHL/IOI/1205/11570","lastName":""},{"id":"46534","alarmTime":"1678057662","deviceCode":"1000003","deviceName":"10.10.126.23","channelId":"1000003$7$0$0","channelName":"Door1","alarmTypeId":"600005","personId":"SHOSHOKWAH","firstName":"1SHL/IOI/0200/6988","lastName":""},{"id":"46533","alarmTime":"1678057634","deviceCode":"1000003","deviceName":"10.10.126.23","channelId":"1000003$7$0$0","channelName":"Door1","alarmTypeId":"600005","personId":"KARTINIBINTIBUBA","firstName":"1SHL/IOI/0415/6994","lastName":""},{"id":"46532","alarmTime":"1678057516","deviceCode":"1000003","deviceName":"10.10.126.23","channelId":"1000003$7$0$0","channelName":"Door1","alarmTypeId":"600005","personId":"MOHDZAMANIEBINSUMSUDIN","firstName":"1SHL/IOI/0816/6949","lastName":""},{"id":"46531","alarmTime":"1678057500","deviceCode":"1000003","deviceName":"10.10.126.23","channelId":"1000003$7$0$0","channelName":"Door1","alarmTypeId":"600005","personId":"MAXIMUSMARKSAMUEL","firstName":"1SHL/IOI/0319/6932","lastName":""},{"id":"46530","alarmTime":"1678057296","deviceCode":"1000002","deviceName":"10.10.126.24","channelId":"1000002$7$0$0","channelName":"Door1","alarmTypeId":"600005","personId":"MOHDHAFIZANBINUNDDIN","firstName":"1SHL/IOI/1117/6942","lastName":""},{"id":"46529","alarmTime":"1678057224","deviceCode":"1000002","deviceName":"10.10.126.24","channelId":"1000002$7$0$0","channelName":"Door1","alarmTypeId":"600005","personId":"MOHDDARWISBINABDLATIF","firstName":"1SHL/IOI/1116/6996","lastName":""},{"id":"46528","alarmTime":"1678057190","deviceCode":"1000002","deviceName":"10.10.126.24","channelId":"1000002$7$0$0","channelName":"Door1","alarmTypeId":"600005","personId":"HARMADIBINJOHAR","firstName":"1SHL/IOI/0712/6910","lastName":""},{"id":"46527","alarmTime":"1678057169","deviceCode":"1000003","deviceName":"10.10.126.23","channelId":"1000003$7$0$0","channelName":"Door1","alarmTypeId":"600005","personId":"CHRISCEVINPORINUS","firstName":"1SHL/IOI/0422/33165","lastName":""},{"id":"46526","alarmTime":"1678057164","deviceCode":"1000003","deviceName":"10.10.126.23","channelId":"1000003$7$0$0","channelName":"Door1","alarmTypeId":"600005","personId":"MOHDKHAIRULBINLAILANG","firstName":"1SHL/IOI/0418/6944","lastName":""},{"id":"46525","alarmTime":"1678057162","deviceCode":"1000003","deviceName":"10.10.126.23","channelId":"1000003$7$0$0","channelName":"Door1","alarmTypeId":"13100"},{"id":"46524","alarmTime":"1678057160","deviceCode":"1000002","deviceName":"10.10.126.24","channelId":"1000002$7$0$0","channelName":"Door1","alarmTypeId":"600005","personId":"HARMADIBINJOHAR","firstName":"1SHL/IOI/0712/6910","lastName":""},{"id":"46523","alarmTime":"1678057159","deviceCode":"1000003","deviceName":"10.10.126.23","channelId":"1000003$7$0$0","channelName":"Door1","alarmTypeId":"13100"},{"id":"46522","alarmTime":"1678057155","deviceCode":"1000002","deviceName":"10.10.126.24","channelId":"1000002$7$0$0","channelName":"Door1","alarmTypeId":"600005","personId":"HARMADIBINJOHAR","firstName":"1SHL/IOI/0712/6910","lastName":""},{"id":"46521","alarmTime":"1678057140","deviceCode":"1000003","deviceName":"10.10.126.23","channelId":"1000003$7$0$0","channelName":"Door1","alarmTypeId":"600005","personId":"MOHDHAFIZANBINUNDDIN","firstName":"1SHL/IOI/1117/6942","lastName":""},{"id":"46520","alarmTime":"1678057054","deviceCode":"1000003","deviceName":"10.10.126.23","channelId":"1000003$7$0$0","channelName":"Door1","alarmTypeId":"600005","personId":"NORSIAHBINTILOME","firstName":"1SHL/IOI/1110/6962","lastName":""},{"id":"46519","alarmTime":"1678056998","deviceCode":"1000003","deviceName":"10.10.126.23","channelId":"1000003$7$0$0","channelName":"Door1","alarmTypeId":"600005","personId":"ANDIKABINABIDING","firstName":"1SHL/IOI/0817/6890","lastName":""},{"id":"46518","alarmTime":"1678056974","deviceCode":"1000003","deviceName":"10.10.126.23","channelId":"1000003$7$0$0","channelName":"Door1","alarmTypeId":"600005","personId":"ALBASIRBINAZLAN","firstName":"1SHL/IOI/0715/6886","lastName":""},{"id":"46517","alarmTime":"1678056933","deviceCode":"1000003","deviceName":"10.10.126.23","channelId":"1000003$7$0$0","channelName":"Door1","alarmTypeId":"600005","personId":"SUHARMINBINJOHARI","firstName":"1SHL/IOI/0212/6981","lastName":""},{"id":"46516","alarmTime":"1678056929","deviceCode":"1000003","deviceName":"10.10.126.23","channelId":"1000003$7$0$0","channelName":"Door1","alarmTypeId":"600005","personId":"SURIADIBINDARWIS","firstName":"1SHL/IOI/0511/6984","lastName":""},{"id":"46515","alarmTime":"1678056924","deviceCode":"1000003","deviceName":"10.10.126.23","channelId":"1000003$7$0$0","channelName":"Door1","alarmTypeId":"600005","personId":"MUHAMMADNIRWANBINDELL","firstName":"1SHL/IOI/1222/39173","lastName":""},{"id":"46514","alarmTime":"1678056882","deviceCode":"1000003","deviceName":"10.10.126.23","channelId":"1000003$7$0$0","channelName":"Door1","alarmTypeId":"600005","personId":"ABDULKADIRBINPATANI","firstName":"1SHL/IOI/1299/6986","lastName":""},{"id":"46513","alarmTime":"1678056877","deviceCode":"1000003","deviceName":"10.10.126.23","channelId":"1000003$7$0$0","channelName":"Door1","alarmTypeId":"600005","personId":"ELVINVINCENTBINSAMUEL","firstName":"1SHL/IOI/0817/6902","lastName":""},{"id":"46512","alarmTime":"1678056871","deviceCode":"1000003","deviceName":"10.10.126.23","channelId":"1000003$7$0$0","channelName":"Door1","alarmTypeId":"600005","personId":"RIDZUANSYAHPEDLEY","firstName":"1SHL/IOI/1020/26406","lastName":""},{"id":"46511","alarmTime":"1678056839","deviceCode":"1000003","deviceName":"10.10.126.23","channelId":"1000003$7$0$0","channelName":"Door1","alarmTypeId":"600005","personId":"MOHDSYAHEFFENDI","firstName":"1SHL/IOI/0817/6948","lastName":""},{"id":"46510","alarmTime":"1678056803","deviceCode":"1000003","deviceName":"10.10.126.23","channelId":"1000003$7$0$0","channelName":"Door1","alarmTypeId":"600005","personId":"MOHAMADHAKEMANBINUNDDIN","firstName":"1SHL/IOI/1218/6935","lastName":""},{"id":"46509","alarmTime":"1678056795","deviceCode":"1000003","deviceName":"10.10.126.23","channelId":"1000003$7$0$0","channelName":"Door1","alarmTypeId":"600005","personId":"MOHAMMADFATRABINKODDI","firstName":"1SHL/IOI/0211/6938","lastName":""},{"id":"46508","alarmTime":"1678056787","deviceCode":"1000003","deviceName":"10.10.126.23","channelId":"1000003$7$0$0","channelName":"Door1","alarmTypeId":"600005","personId":"SUPRIADI","firstName":"1SHL/IOI/0412/6983","lastName":""},{"id":"46507","alarmTime":"1678056783","deviceCode":"1000003","deviceName":"10.10.126.23","channelId":"1000003$7$0$0","channelName":"Door1","alarmTypeId":"600005","personId":"UNDDINBINMAJIN","firstName":"1SHL/IOI/0915/6995","lastName":""},{"id":"46506","alarmTime":"1678056775","deviceCode":"1000003","deviceName":"10.10.126.23","channelId":"1000003$7$0$0","channelName":"Door1","alarmTypeId":"600005","personId":"ARMANBINALI","firstName":"1SHL/IOI/0411/6893","lastName":""},{"id":"46505","alarmTime":"1678056766","deviceCode":"1000003","deviceName":"10.10.126.23","channelId":"1000003$7$0$0","channelName":"Door1","alarmTypeId":"600005","personId":"IZAMHARUNA","firstName":"1SHL/IOI/0411/6916","lastName":""},{"id":"46504","alarmTime":"1678056761","deviceCode":"1000003","deviceName":"10.10.126.23","channelId":"1000003$7$0$0","channelName":"Door1","alarmTypeId":"600005","personId":"ASRIANIBINTISUDIRMAN","firstName":"1SHL/IOI/0411/6895","lastName":""},{"id":"46503","alarmTime":"1678056755","deviceCode":"1000003","deviceName":"10.10.126.23","channelId":"1000003$7$0$0","channelName":"Door1","alarmTypeId":"600005","personId":"ILLYRYANJOESANA","firstName":"1SHL/IOI/0123/39902","lastName":""},{"id":"46502","alarmTime":"1678056751","deviceCode":"1000003","deviceName":"10.10.126.23","channelId":"1000003$7$0$0","channelName":"Door1","alarmTypeId":"600005","personId":"NURLINAHBINTITAJANG","firstName":"1SHL/IOI/0113/6965","lastName":""},{"id":"46501","alarmTime":"1678056743","deviceCode":"1000003","deviceName":"10.10.126.23","channelId":"1000003$7$0$0","channelName":"Door1","alarmTypeId":"600005","personId":"MOHAMADSYAHFIZANBINDANELO","firstName":"1SHL/IOI/0322/32542","lastName":""},{"id":"46500","alarmTime":"1678056741","deviceCode":"1000003","deviceName":"10.10.126.23","channelId":"1000003$7$0$0","channelName":"Door1","alarmTypeId":"13100"},{"id":"46499","alarmTime":"1678056738","deviceCode":"1000003","deviceName":"10.10.126.23","channelId":"1000003$7$0$0","channelName":"Door1","alarmTypeId":"13100"},{"id":"46498","alarmTime":"1678056728","deviceCode":"1000003","deviceName":"10.10.126.23","channelId":"1000003$7$0$0","channelName":"Door1","alarmTypeId":"600005","personId":"SAMSIRBINABIDIN","firstName":"1SHL/IOI/0313/6978","lastName":""},{"id":"46497","alarmTime":"1678056719","deviceCode":"1000003","deviceName":"10.10.126.23","channelId":"1000003$7$0$0","channelName":"Door1","alarmTypeId":"600005","personId":"TOMEGADUS","firstName":"1SHL/IOI/1117/6998","lastName":""},{"id":"46496","alarmTime":"1678056716","deviceCode":"1000003","deviceName":"10.10.126.23","channelId":"1000003$7$0$0","channelName":"Door1","alarmTypeId":"600005","personId":"SUDIRMANBINBEDDU","firstName":"1SHL/IOI/1222/39504","lastName":""},{"id":"46495","alarmTime":"1678056711","deviceCode":"1000003","deviceName":"10.10.126.23","channelId":"1000003$7$0$0","channelName":"Door1","alarmTypeId":"600005","personId":"MUHAMMADRISALBINDARWIS","firstName":"1SHL/IOI/0715/6953","lastName":""},{"id":"46494","alarmTime":"1678056707","deviceCode":"1000003","deviceName":"10.10.126.23","channelId":"1000003$7$0$0","channelName":"Door1","alarmTypeId":"600005","personId":"IDRUSHAFID","firstName":"1SHL/IOI/0113/6914","lastName":""},{"id":"46493","alarmTime":"1678056691","deviceCode":"1000003","deviceName":"10.10.126.23","channelId":"1000003$7$0$0","channelName":"Door1","alarmTypeId":"600005","personId":"SUEANNEMARTHILDAKILIP","firstName":"1SHL/IOI/0221/27814","lastName":""},{"id":"46492","alarmTime":"1678056682","deviceCode":"1000003","deviceName":"10.10.126.23","channelId":"1000003$7$0$0","channelName":"Door1","alarmTypeId":"600005","personId":"RAHMATBINSAMSUL","firstName":"1SHL/IOI/0417/6971","lastName":""},{"id":"46491","alarmTime":"1678056662","deviceCode":"1000003","deviceName":"10.10.126.23","channelId":"1000003$7$0$0","channelName":"Door1","alarmTypeId":"600005","personId":"MOHDSALIMKHANABDUL","firstName":"1SHL/IOI/1218/6947","lastName":""},{"id":"46490","alarmTime":"1678056656","deviceCode":"1000003","deviceName":"10.10.126.23","channelId":"1000003$7$0$0","channelName":"Door1","alarmTypeId":"600005","personId":"NURIZAH","firstName":"1SHL/IOI/0218/6963","lastName":""},{"id":"46489","alarmTime":"1678056651","deviceCode":"1000003","deviceName":"10.10.126.23","channelId":"1000003$7$0$0","channelName":"Door1","alarmTypeId":"600005","personId":"JEROMEJOHN","firstName":"1SHL/IOI/0123/40299","lastName":""},{"id":"46488","alarmTime":"1678056638","deviceCode":"1000003","deviceName":"10.10.126.23","channelId":"1000003$7$0$0","channelName":"Door1","alarmTypeId":"600005","personId":"ANSELMUSBINYOSEF","firstName":"1SHL/IOI/0611/6892","lastName":""},{"id":"46487","alarmTime":"1678056633","deviceCode":"1000003","deviceName":"10.10.126.23","channelId":"1000003$7$0$0","channelName":"Door1","alarmTypeId":"600005","personId":"PAULUSREGIRITAN","firstName":"1SHL/IOI/0409/6969","lastName":""},{"id":"46486","alarmTime":"1678056628","deviceCode":"1000003","deviceName":"10.10.126.23","channelId":"1000003$7$0$0","channelName":"Door1","alarmTypeId":"600005","personId":"MOHAMMADASRULBINAMIR","firstName":"1SHL/IOI/0117/6936","lastName":""},{"id":"46485","alarmTime":"1678056621","deviceCode":"1000003","deviceName":"10.10.126.23","channelId":"1000003$7$0$0","channelName":"Door1","alarmTypeId":"600005","personId":"ANDIAMRIBINHUSAINI","firstName":"1SHL/IOI/0409/6889","lastName":""},{"id":"46484","alarmTime":"1678056611","deviceCode":"1000003","deviceName":"10.10.126.23","channelId":"1000003$7$0$0","channelName":"Door1","alarmTypeId":"600005","personId":"KASRINBINSALLEHWANG","firstName":"1SHL/IOI/0219/6923","lastName":""},{"id":"46483","alarmTime":"1678056606","deviceCode":"1000003","deviceName":"10.10.126.23","channelId":"1000003$7$0$0","channelName":"Door1","alarmTypeId":"600005","personId":"ABDULGANIKACO","firstName":"1SHL/IOI/0408/6882","lastName":""},{"id":"46482","alarmTime":"1678056596","deviceCode":"1000003","deviceName":"10.10.126.23","channelId":"1000003$7$0$0","channelName":"Door1","alarmTypeId":"600005","personId":"KOPONGBINKARIM","firstName":"1SHL/IOI/0510/6924","lastName":""},{"id":"46481","alarmTime":"1678056576","deviceCode":"1000003","deviceName":"10.10.126.23","channelId":"1000003$7$0$0","channelName":"Door1","alarmTypeId":"600005","personId":"SABARUDDINBINSERE","firstName":"1SHL/IOI/0417/6973","lastName":""},{"id":"46480","alarmTime":"1678056571","deviceCode":"1000003","deviceName":"10.10.126.23","channelId":"1000003$7$0$0","channelName":"Door1","alarmTypeId":"600005","personId":"HERDIANSABINNURDIN","firstName":"1SHL/IOI/0318/6911","lastName":""},{"id":"46479","alarmTime":"1678056562","deviceCode":"1000003","deviceName":"10.10.126.23","channelId":"1000003$7$0$0","channelName":"Door1","alarmTypeId":"600005","personId":"HASNIATIBINTIRAMLI","firstName":"1SHL/IOI/0215/6993","lastName":""},{"id":"46478","alarmTime":"1678056548","deviceCode":"1000003","deviceName":"10.10.126.23","channelId":"1000003$7$0$0","channelName":"Door1","alarmTypeId":"600005","personId":"JUMRIANIBINTINURDIN","firstName":"1SHL/IOI/0711/6920","lastName":""},{"id":"46477","alarmTime":"1678056542","deviceCode":"1000003","deviceName":"10.10.126.23","channelId":"1000003$7$0$0","channelName":"Door1","alarmTypeId":"600005","personId":"LIANABINTINADUS","firstName":"1SHL/IOI/0311/6925","lastName":""},{"id":"46476","alarmTime":"1678056528","deviceCode":"1000003","deviceName":"10.10.126.23","channelId":"1000003$7$0$0","channelName":"Door1","alarmTypeId":"600005","personId":"BASRIBINSIDE","firstName":"1SHL/IOI/0211/6900","lastName":""},{"id":"46475","alarmTime":"1678056514","deviceCode":"1000003","deviceName":"10.10.126.23","channelId":"1000003$7$0$0","channelName":"Door1","alarmTypeId":"600005","personId":"ARSYADRIDWAN","firstName":"1SHL/IOI/0411/6894","lastName":""},{"id":"46474","alarmTime":"1678056505","deviceCode":"1000003","deviceName":"10.10.126.23","channelId":"1000003$7$0$0","channelName":"Door1","alarmTypeId":"600005","personId":"MULYONO","firstName":"1SHL/IOI/0218/6957","lastName":""},{"id":"46473","alarmTime":"1678056495","deviceCode":"1000003","deviceName":"10.10.126.23","channelId":"1000003$7$0$0","channelName":"Door1","alarmTypeId":"600005","personId":"RABANIABINTIYEMMI","firstName":"1SHL/IOI/0817/6970","lastName":""},{"id":"46472","alarmTime":"1678056486","deviceCode":"1000003","deviceName":"10.10.126.23","channelId":"1000003$7$0$0","channelName":"Door1","alarmTypeId":"600005","personId":"HERIYANIBINTIJUHARDI","firstName":"1SHL/IOI/0114/6912","lastName":""},{"id":"46471","alarmTime":"1678056475","deviceCode":"1000003","deviceName":"10.10.126.23","channelId":"1000003$7$0$0","channelName":"Door1","alarmTypeId":"600005","personId":"MARIANADUS","firstName":"1SHL/IOI/0311/6930","lastName":""},{"id":"46470","alarmTime":"1678056447","deviceCode":"1000003","deviceName":"10.10.126.23","channelId":"1000003$7$0$0","channelName":"Door1","alarmTypeId":"600005","personId":"MUHAMMADAKMALMANDONG","firstName":"1SHL/IOI/0811/6951","lastName":""},{"id":"46469","alarmTime":"1678056442","deviceCode":"1000003","deviceName":"10.10.126.23","channelId":"1000003$7$0$0","channelName":"Door1","alarmTypeId":"600005","personId":"ANSARBINAMBODAI","firstName":"1SHL/IOI/0712/6891","lastName":""},{"id":"46468","alarmTime":"1678056434","deviceCode":"1000003","deviceName":"10.10.126.23","channelId":"1000003$7$0$0","channelName":"Door1","alarmTypeId":"600005","personId":"MOHDKHAIRULAZIMAN","firstName":"1SHL/IOI/0122/31424","lastName":""},{"id":"46467","alarmTime":"1678056401","deviceCode":"1000003","deviceName":"10.10.126.23","channelId":"1000003$7$0$0","channelName":"Door1","alarmTypeId":"600005","personId":"ALFIANFERNANDEZHILVIN","firstName":"1SHL/IOI/0123/40298","lastName":""},{"id":"46466","alarmTime":"1678056397","deviceCode":"1000003","deviceName":"10.10.126.23","channelId":"1000003$7$0$0","channelName":"Door1","alarmTypeId":"600005","personId":"111"},{"id":"46465","alarmTime":"1678056391","deviceCode":"1000003","deviceName":"10.10.126.23","channelId":"1000003$7$0$0","channelName":"Door1","alarmTypeId":"600005","personId":"WAHIDAH","firstName":"1SHL/IOI/0417/6985","lastName":""},{"id":"46464","alarmTime":"1678056380","deviceCode":"1000003","deviceName":"10.10.126.23","channelId":"1000003$7$0$0","channelName":"Door1","alarmTypeId":"600005","personId":"IBRAHIMBINJOHAR","firstName":"1SHL/IOI/0511/6913","lastName":""},{"id":"46463","alarmTime":"1678056377","deviceCode":"1000003","deviceName":"10.10.126.23","channelId":"1000003$7$0$0","channelName":"Door1","alarmTypeId":"600005","personId":"MADABINRAHMAN","firstName":"1SHL/IOI/0219/7000","lastName":""},{"id":"46462","alarmTime":"1678056373","deviceCode":"1000003","deviceName":"10.10.126.23","channelId":"1000003$7$0$0","channelName":"Door1","alarmTypeId":"600005","personId":"IMRANBINUDDIN","firstName":"1SHL/IOI/0409/6915","lastName":""},{"id":"46461","alarmTime":"1678056373","deviceCode":"1000003","deviceName":"10.10.126.23","channelId":"1000003$7$0$0","channelName":"Door1","alarmTypeId":"13100"},{"id":"46460","alarmTime":"1678056353","deviceCode":"1000003","deviceName":"10.10.126.23","channelId":"1000003$7$0$0","channelName":"Door1","alarmTypeId":"600005","personId":"MUHAMMADSYAMBINABSAN","firstName":"1SHL/IOI/1110/6954","lastName":""},{"id":"46459","alarmTime":"1678056345","deviceCode":"1000003","deviceName":"10.10.126.23","channelId":"1000003$7$0$0","channelName":"Door1","alarmTypeId":"600005","personId":"PARMANBINNORANI","firstName":"1SHL/IOI/0916/6968","lastName":""},{"id":"46458","alarmTime":"1678056323","deviceCode":"1000003","deviceName":"10.10.126.23","channelId":"1000003$7$0$0","channelName":"Door1","alarmTypeId":"600005","personId":"HANAFIEBINSHALIMSHA","firstName":"1SHL/IOI/1214/6909","lastName":""},{"id":"46457","alarmTime":"1678056305","deviceCode":"1000003","deviceName":"10.10.126.23","channelId":"1000003$7$0$0","channelName":"Door1","alarmTypeId":"600005","personId":"FREDDYNANPIRANJR","firstName":"1SHL/IOI/0412/6905","lastName":""},{"id":"46456","alarmTime":"1678056264","deviceCode":"1000003","deviceName":"10.10.126.23","channelId":"1000003$7$0$0","channelName":"Door1","alarmTypeId":"600005","personId":"ASADIBINENLAR","firstName":"1SHL/IOI/0222/31885","lastName":""},{"id":"46455","alarmTime":"1678056262","deviceCode":"1000003","deviceName":"10.10.126.23","channelId":"1000003$7$0$0","channelName":"Door1","alarmTypeId":"13100"},{"id":"46454","alarmTime":"1678056213","deviceCode":"1000003","deviceName":"10.10.126.23","channelId":"1000003$7$0$0","channelName":"Door1","alarmTypeId":"600005","personId":"HAMSIRBINAHMADKADIR","firstName":"1SHL/IOI/0411/6908","lastName":""},{"id":"46453","alarmTime":"1678056209","deviceCode":"1000003","deviceName":"10.10.126.23","channelId":"1000003$7$0$0","channelName":"Door1","alarmTypeId":"600005","personId":"MUSTAPABINAMBOTUO","firstName":"1SHL/IOI/0409/6958","lastName":""},{"id":"46452","alarmTime":"1678056197","deviceCode":"1000003","deviceName":"10.10.126.23","channelId":"1000003$7$0$0","channelName":"Door1","alarmTypeId":"600005","personId":"MUHAMMADFAHMIBINAZIM","firstName":"1SHL/IOI/0418/6952","lastName":""},{"id":"46451","alarmTime":"1678056191","deviceCode":"1000003","deviceName":"10.10.126.23","channelId":"1000003$7$0$0","channelName":"Door1","alarmTypeId":"13100"},{"id":"46450","alarmTime":"1678056188","deviceCode":"1000003","deviceName":"10.10.126.23","channelId":"1000003$7$0$0","channelName":"Door1","alarmTypeId":"13100"},{"id":"46449","alarmTime":"1678056184","deviceCode":"1000003","deviceName":"10.10.126.23","channelId":"1000003$7$0$0","channelName":"Door1","alarmTypeId":"13100"},{"id":"46448","alarmTime":"1678056181","deviceCode":"1000003","deviceName":"10.10.126.23","channelId":"1000003$7$0$0","channelName":"Door1","alarmTypeId":"13100"},{"id":"46447","alarmTime":"1678056175","deviceCode":"1000003","deviceName":"10.10.126.23","channelId":"1000003$7$0$0","channelName":"Door1","alarmTypeId":"600005","personId":"AMIRULLAHBINABDULRAHMAN","firstName":"1SHL/IOI/0114/6888","lastName":""},{"id":"46446","alarmTime":"1678056150","deviceCode":"1000003","deviceName":"10.10.126.23","channelId":"1000003$7$0$0","channelName":"Door1","alarmTypeId":"600005","personId":"ALFONSIUSBINNADUS","firstName":"1SHL/IOI/0118/6887","lastName":""},{"id":"46445","alarmTime":"1678056080","deviceCode":"1000003","deviceName":"10.10.126.23","channelId":"1000003$7$0$0","channelName":"Door1","alarmTypeId":"600005","personId":"AZIMBINHUGHES","firstName":"1SHL/IOI/1207/27166","lastName":""},{"id":"46444","alarmTime":"1678055979","deviceCode":"1000003","deviceName":"10.10.126.23","channelId":"1000003$7$0$0","channelName":"Door1","alarmTypeId":"600005","personId":"KARMIANABINTIABDULKDIR","firstName":"1SHL/IOI/1122/38741","lastName":""},{"id":"46443","alarmTime":"1678055934","deviceCode":"1000003","deviceName":"10.10.126.23","channelId":"1000003$7$0$0","channelName":"Door1","alarmTypeId":"600005","personId":"BAHARIBINBADDU","firstName":"1SHL/IOI/0409/6899","lastName":""},{"id":"46442","alarmTime":"1678055355","deviceCode":"1000003","deviceName":"10.10.126.23","channelId":"1000003$7$0$0","channelName":"Door1","alarmTypeId":"600005","personId":"SIBATANTILI","firstName":"1SHL/IOI/0822/35295","lastName":""},{"id":"46441","alarmTime":"1678055285","deviceCode":"1000003","deviceName":"10.10.126.23","channelId":"1000003$7$0$0","channelName":"Door1","alarmTypeId":"600005","personId":"ADRIANBINPETRUS","firstName":"1SHL/IOI/1008/6884","lastName":""},{"id":"46440","alarmTime":"1678054308","deviceCode":"1000003","deviceName":"10.10.126.23","channelId":"1000003$7$0$0","channelName":"Door1","alarmTypeId":"600005","personId":"NOORHASSIAHBINTIHASSIM","firstName":"1SHL/IOI/1210/6960","lastName":""},{"id":"46439","alarmTime":"1678054110","deviceCode":"1000003","deviceName":"10.10.126.23","channelId":"1000003$7$0$0","channelName":"Door1","alarmTypeId":"600005","personId":"JIFRANBINJUHON","firstName":"1SHL/IOI/1108/6917","lastName":""},{"id":"46438","alarmTime":"1678053631","deviceCode":"1000003","deviceName":"10.10.126.23","channelId":"1000003$7$0$0","channelName":"Door1","alarmTypeId":"600005","personId":"MAZLANBINMAJUKI","firstName":"1SHL/IOI/0518/6933","lastName":""},{"id":"46437","alarmTime":"1678050591","deviceCode":"1000002","deviceName":"10.10.126.24","channelId":"1000002$7$0$0","channelName":"Door1","alarmTypeId":"600005","personId":"HAMSABINUMERENG","firstName":"1SHL/IOI/0712/6907","lastName":""},{"id":"46436","alarmTime":"1678040271","deviceCode":"1000002","deviceName":"10.10.126.24","channelId":"1000002$7$0$0","channelName":"Door1","alarmTypeId":"600005","personId":"MOHDSHAFIQIQMALBINAZEMAN","firstName":"1SHL/IOI/0818/11558","lastName":""},{"id":"46435","alarmTime":"1678024274","deviceCode":"1000003","deviceName":"10.10.126.23","channelId":"1000003$7$0$0","channelName":"Door1","alarmTypeId":"600005","personId":"HARMADIBINJOHAR","firstName":"1SHL/IOI/0712/6910","lastName":""},{"id":"46434","alarmTime":"1678016360","deviceCode":"1000003","deviceName":"10.10.126.23","channelId":"1000003$7$0$0","channelName":"Door1","alarmTypeId":"600005","personId":"HAMSABINUMERENG","firstName":"1SHL/IOI/0712/6907","lastName":""},{"id":"46433","alarmTime":"1678014598","deviceCode":"1000002","deviceName":"10.10.126.24","channelId":"1000002$7$0$0","channelName":"Door1","alarmTypeId":"600005","personId":"MOHDHAFIZANBINUNDDIN","firstName":"1SHL/IOI/1117/6942","lastName":""},{"id":"46432","alarmTime":"1678014049","deviceCode":"1000002","deviceName":"10.10.126.24","channelId":"1000002$7$0$0","channelName":"Door1","alarmTypeId":"600005","personId":"TOMEGADUS","firstName":"1SHL/IOI/1117/6998","lastName":""},{"id":"46431","alarmTime":"1678014031","deviceCode":"1000002","deviceName":"10.10.126.24","channelId":"1000002$7$0$0","channelName":"Door1","alarmTypeId":"600005","personId":"WAHIDAH","firstName":"1SHL/IOI/0417/6985","lastName":""},{"id":"46430","alarmTime":"1678014028","deviceCode":"1000002","deviceName":"10.10.126.24","channelId":"1000002$7$0$0","channelName":"Door1","alarmTypeId":"600005","personId":"WAHIDAH","firstName":"1SHL/IOI/0417/6985","lastName":""},{"id":"46429","alarmTime":"1678014018","deviceCode":"1000002","deviceName":"10.10.126.24","channelId":"1000002$7$0$0","channelName":"Door1","alarmTypeId":"600005","personId":"ARSYADRIDWAN","firstName":"1SHL/IOI/0411/6894","lastName":""},{"id":"46428","alarmTime":"1678014011","deviceCode":"1000002","deviceName":"10.10.126.24","channelId":"1000002$7$0$0","channelName":"Door1","alarmTypeId":"600005","personId":"MAHMUDDINLAKING","firstName":"1SHL/IOI/0712/6928","lastName":""},{"id":"46427","alarmTime":"1678013182","deviceCode":"1000003","deviceName":"10.10.126.23","channelId":"1000003$7$0$0","channelName":"Door1","alarmTypeId":"600005","personId":"MOHDDARWISBINABDLATIF","firstName":"1SHL/IOI/1116/6996","lastName":""},{"id":"46426","alarmTime":"1678010676","deviceCode":"1000002","deviceName":"10.10.126.24","channelId":"1000002$7$0$0","channelName":"Door1","alarmTypeId":"600005","personId":"ABDULGANIKACO","firstName":"1SHL/IOI/0408/6882","lastName":""},{"id":"46425","alarmTime":"1678010587","deviceCode":"1000002","deviceName":"10.10.126.24","channelId":"1000002$7$0$0","channelName":"Door1","alarmTypeId":"600005","personId":"MUHAMMADRISALBINDARWIS","firstName":"1SHL/IOI/0715/6953","lastName":""},{"id":"46424","alarmTime":"1678010579","deviceCode":"1000002","deviceName":"10.10.126.24","channelId":"1000002$7$0$0","channelName":"Door1","alarmTypeId":"600005","personId":"RIDZUANSYAHPEDLEY","firstName":"1SHL/IOI/1020/26406","lastName":""},{"id":"46423","alarmTime":"1678010571","deviceCode":"1000003","deviceName":"10.10.126.23","channelId":"1000003$7$0$0","channelName":"Door1","alarmTypeId":"600005","personId":"MOHDSHAFIQIQMALBINAZEMAN","firstName":"1SHL/IOI/0818/11558","lastName":""},{"id":"46422","alarmTime":"1678010530","deviceCode":"1000002","deviceName":"10.10.126.24","channelId":"1000002$7$0$0","channelName":"Door1","alarmTypeId":"600005","personId":"SABARUDDINBINSERE","firstName":"1SHL/IOI/0417/6973","lastName":""},{"id":"46421","alarmTime":"1678010525","deviceCode":"1000002","deviceName":"10.10.126.24","channelId":"1000002$7$0$0","channelName":"Door1","alarmTypeId":"600005","personId":"SAMSIRBINABIDIN","firstName":"1SHL/IOI/0313/6978","lastName":""},{"id":"46420","alarmTime":"1678010492","deviceCode":"1000002","deviceName":"10.10.126.24","channelId":"1000002$7$0$0","channelName":"Door1","alarmTypeId":"600005","personId":"MUSTAPABINAMBOTUO","firstName":"1SHL/IOI/0409/6958","lastName":""},{"id":"46419","alarmTime":"1678010434","deviceCode":"1000002","deviceName":"10.10.126.24","channelId":"1000002$7$0$0","channelName":"Door1","alarmTypeId":"600005","personId":"MUHAMMADZULKIFLY","firstName":"1SHL/IOI/0417/6955","lastName":""},{"id":"46418","alarmTime":"1678010408","deviceCode":"1000002","deviceName":"10.10.126.24","channelId":"1000002$7$0$0","channelName":"Door1","alarmTypeId":"600005","personId":"AMIRULLAHBINABDULRAHMAN","firstName":"1SHL/IOI/0114/6888","lastName":""},{"id":"46417","alarmTime":"1678010405","deviceCode":"1000002","deviceName":"10.10.126.24","channelId":"1000002$7$0$0","channelName":"Door1","alarmTypeId":"600005","personId":"ALFONSIUSBINNADUS","firstName":"1SHL/IOI/0118/6887","lastName":""},{"id":"46416","alarmTime":"1678010386","deviceCode":"1000002","deviceName":"10.10.126.24","channelId":"1000002$7$0$0","channelName":"Door1","alarmTypeId":"600005","personId":"ANSELMUSBINYOSEF","firstName":"1SHL/IOI/0611/6892","lastName":""},{"id":"46415","alarmTime":"1678010378","deviceCode":"1000002","deviceName":"10.10.126.24","channelId":"1000002$7$0$0","channelName":"Door1","alarmTypeId":"600005","personId":"ANSELMUSBINYOSEF","firstName":"1SHL/IOI/0611/6892","lastName":""},{"id":"46414","alarmTime":"1678010369","deviceCode":"1000002","deviceName":"10.10.126.24","channelId":"1000002$7$0$0","channelName":"Door1","alarmTypeId":"600005","personId":"ELVINVINCENTBINSAMUEL","firstName":"1SHL/IOI/0817/6902","lastName":""},{"id":"46413","alarmTime":"1678010365","deviceCode":"1000002","deviceName":"10.10.126.24","channelId":"1000002$7$0$0","channelName":"Door1","alarmTypeId":"600005","personId":"ELVINVINCENTBINSAMUEL","firstName":"1SHL/IOI/0817/6902","lastName":""},{"id":"46412","alarmTime":"1678010354","deviceCode":"1000002","deviceName":"10.10.126.24","channelId":"1000002$7$0$0","channelName":"Door1","alarmTypeId":"600005","personId":"ANSELMUSBINYOSEF","firstName":"1SHL/IOI/0611/6892","lastName":""},{"id":"46411","alarmTime":"1678010351","deviceCode":"1000002","deviceName":"10.10.126.24","channelId":"1000002$7$0$0","channelName":"Door1","alarmTypeId":"600005","personId":"ANSELMUSBINYOSEF","firstName":"1SHL/IOI/0611/6892","lastName":""},{"id":"46410","alarmTime":"1678010343","deviceCode":"1000002","deviceName":"10.10.126.24","channelId":"1000002$7$0$0","channelName":"Door1","alarmTypeId":"600005","personId":"IMRANBINUDDIN","firstName":"1SHL/IOI/0409/6915","lastName":""},{"id":"46409","alarmTime":"1678010340","deviceCode":"1000002","deviceName":"10.10.126.24","channelId":"1000002$7$0$0","channelName":"Door1","alarmTypeId":"600005","personId":"MUHAMMADAKMALMANDONG","firstName":"1SHL/IOI/0811/6951","lastName":""},{"id":"46408","alarmTime":"1678010326","deviceCode":"1000002","deviceName":"10.10.126.24","channelId":"1000002$7$0$0","channelName":"Door1","alarmTypeId":"600005","personId":"FREDDYNANPIRANJR","firstName":"1SHL/IOI/0412/6905","lastName":""},{"id":"46407","alarmTime":"1678009138","deviceCode":"1000003","deviceName":"10.10.126.23","channelId":"1000003$7$0$0","channelName":"Door1","alarmTypeId":"600005","personId":"ARSYADRIDWAN","firstName":"1SHL/IOI/0411/6894","lastName":""},{"id":"46406","alarmTime":"1678009103","deviceCode":"1000003","deviceName":"10.10.126.23","channelId":"1000003$7$0$0","channelName":"Door1","alarmTypeId":"600005","personId":"WAHIDAH","firstName":"1SHL/IOI/0417/6985","lastName":""},{"id":"46405","alarmTime":"1678008547","deviceCode":"1000003","deviceName":"10.10.126.23","channelId":"1000003$7$0$0","channelName":"Door1","alarmTypeId":"600005","personId":"MAHMUDDINLAKING","firstName":"1SHL/IOI/0712/6928","lastName":""},{"id":"46404","alarmTime":"1678006974","deviceCode":"1000002","deviceName":"10.10.126.24","channelId":"1000002$7$0$0","channelName":"Door1","alarmTypeId":"600005","personId":"LIANABINTINADUS","firstName":"1SHL/IOI/0311/6925","lastName":""},{"id":"46403","alarmTime":"1678005473","deviceCode":"1000002","deviceName":"10.10.126.24","channelId":"1000002$7$0$0","channelName":"Door1","alarmTypeId":"600005","personId":"WAHIDAH","firstName":"1SHL/IOI/0417/6985","lastName":""},{"id":"46402","alarmTime":"1678005470","deviceCode":"1000002","deviceName":"10.10.126.24","channelId":"1000002$7$0$0","channelName":"Door1","alarmTypeId":"600005","personId":"WAHIDAH","firstName":"1SHL/IOI/0417/6985","lastName":""},{"id":"46401","alarmTime":"1678005455","deviceCode":"1000002","deviceName":"10.10.126.24","channelId":"1000002$7$0$0","channelName":"Door1","alarmTypeId":"600005","personId":"ARSYADRIDWAN","firstName":"1SHL/IOI/0411/6894","lastName":""},{"id":"46400","alarmTime":"1678005448","deviceCode":"1000002","deviceName":"10.10.126.24","channelId":"1000002$7$0$0","channelName":"Door1","alarmTypeId":"600005","personId":"MAHMUDDINLAKING","firstName":"1SHL/IOI/0712/6928","lastName":""},{"id":"46399","alarmTime":"1678002191","deviceCode":"1000002","deviceName":"10.10.126.24","channelId":"1000002$7$0$0","channelName":"Door1","alarmTypeId":"600005","personId":"MUHAMMADFAHMIBINAZIM","firstName":"1SHL/IOI/0418/6952","lastName":""},{"id":"46398","alarmTime":"1678000415","deviceCode":"1000002","deviceName":"10.10.126.24","channelId":"1000002$7$0$0","channelName":"Door1","alarmTypeId":"600005","personId":"MOHDZAMANIEBINSUMSUDIN","firstName":"1SHL/IOI/0816/6949","lastName":""},{"id":"46397","alarmTime":"1678000209","deviceCode":"1000002","deviceName":"10.10.126.24","channelId":"1000002$7$0$0","channelName":"Door1","alarmTypeId":"600005","personId":"HAMSIRBINAHMADKADIR","firstName":"1SHL/IOI/0411/6908","lastName":""},{"id":"46396","alarmTime":"1677999793","deviceCode":"1000002","deviceName":"10.10.126.24","channelId":"1000002$7$0$0","channelName":"Door1","alarmTypeId":"600005","personId":"JUMRIANIBINTINURDIN","firstName":"1SHL/IOI/0711/6920","lastName":""}],"totalCount":"-283"}}';
//        dd([$result]);
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

    protected function crawling_face_recognition_ori(Request $request, $_token) {
//        $x_ploded_data = json_decode('{"code":1000,"desc":"Success","data":{"nextPage":"2","totalCount":"2147483647","pageData":[{"id":"1","swipeTime":"2018-09-20 10:23:23","code":"12341","name":"test12341","deptName":"Root","cardNumber":"12345676","personPic":"","swipeLocation":"test","eventName":"1"},{"id":"2","swipeTime":"2018-09-20 11:23:23","code":"12345","name":"test12345","deptName":"Root","cardNumber":"12345677","personPic":"","swipeLocation":"test","eventName":"1"},{"id":"3","swipeTime":"2018-09-20 12:23:23","code":"12342","name":"test12342","deptName":"Root","cardNumber":"12345678","personPic":"","swipeLocation":"test","eventName":"1"}]}}', 1);
//        $return['status'] = 1;
//        $return['data'] = $x_ploded_data['data'];
//        $return['code'] = 200;
//        $return['message'] = 'sukses';
//
//        return $return;
        /**
         * H- 1
         */
        $data_now = date(date('Y-m-d'), strtotime(' -1 day'));    // previous day ;
        $server = config('face.API_FACEAPI_DOMAIN');
        $params = urlencode("page?startTime=$data_now 00:00:00&endTime=$data_now 23:59:59&personName=&personId=&deptId=&eventType=0&page=1&pageSize=100&displayServerTimezoneOffset=0");
//        dd("https://172.16.8.79:443/brms/api/v1.0/attendance/swiping-card-report/$params");
        $ch = curl_init("https://$server/brms/api/v1.0/attendance/swiping-card-report/$params");
//        $ch = curl_init("https://172.16.8.79:443/brms/api/v1.0/attendance/swiping-card-report/$params");

        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'X-Subject-Token:' . $_token,
            'charset:UTF-8',
            'Time-Zone:Asia/Shanghai'
                )
        );
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        $result = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
//        dd([$result,$httpcode]);
        curl_close($ch);

        if ($httpcode == 200) {
            $return['status'] = 1;
            $parse_result = json_decode($result, 1);
            if ($parse_result['code'] == 1000) {
                $return['data'] = $result;
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
                $this->log_event($params, $responses, '', 'crawling_face_recognition_ori');
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
        $ch = curl_init("https://" . $server . "/admin/API/v1.0/accounts/keepalive");
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
        $enddate = $strdate;
//        $strdate = date('Y-m-d 00:00:01');
//        $enddate = date('Y-m-d 23:59:59');


//        $strdate = '2023-01-17';
//        $enddate = '2023-01-26';
        $data = DB::table('fa_accesscontrol')
                ->select('fa_accesscontrol_id', 'devicecode', 'devicename', 'channelid', 'channelname', 'alarmtypeid', 'personid', 'firstname', 'lastname', 'alarmtime', 'accesstype', 'unit_name')
                ->where(function ($query) use ($strdate) {
                    $query->whereRaw("to_char(alarmtime::date, 'YYYY-MM-DD') = '$strdate'");
                })
                ->where('sent_cpi', '=', 'N')
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
//                    echo "$i <br/>";
                }
                $dtsent1 = $sent_data[$prfnr_list[count($prfnr_list) - 1]];
                $dtsent = $dtsent1;
                foreach ($dtsent as $ksent => $oksent) {
                    unset($dtsent[$ksent]['RECORD_ID']);
                }
                $response1 = send_time_attendance_to_cpi($dtsent, $prfnr_list[count($prfnr_list) - 1], true);
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
                //  dd($response1['feedback']);                
            } else {
                $dtsent1 = $sent_data[$prfnr_list[0]];
//                dd($dtsent1);
                $dtsent = $dtsent1;
                foreach ($dtsent as $ksent => $oksent) {
                    unset($dtsent[$ksent]['RECORD_ID']);
                }
                $response2 = send_time_attendance_to_cpi($dtsent, $prfnr_list[0], true);
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
                                ->update(['sent_cpi' => 'F','remark' => $derr['REMARK']]);
                    }
                }
                $this->log_event($sent_data, $responses, '', 'passing_to_cpi_oto');
            }
        }
        return $responses;
    }

    protected function passing_to_cpi_ori(Request $request) {
        /**
         * BTP Integration Suite
         */
        $data_string = '{
            "urn:ZEPMS_EM_VRA_OUT": {
                "BUKRS": "*",
                "AUART": "*"
            }
        }';
        $ch = curl_init("https://domain_server_cpi/sent");

        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_USERPWD, "S0020948634:IOISCP#3");
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/xml'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

        $result = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

//        var_dump($result);
//        var_dump($httpcode);
//        exit;
        curl_close($ch);

        if ($httpcode == 200) {
            $data = json_decode($result);
        } else {
            $data = $result;
        }
    }

    public function populate(Request $request) {
        $zone = config('face.API_ZONE');
        if ($zone == 'MY') {
            date_default_timezone_set('Asia/Kuala_Lumpur');
        } else {
            date_default_timezone_set('Asia/Jakarta');
        }
//        var_dump(['ok' => 'tes']);exit;

        $newLog = new RequestLog();
//        '',
//        'params',
//        'response_status',
//        'response_message',
//        'created_at'      
        $params = array(
            'start_check' => date('Y-m-d 00:00:01'),
            'end_check' => date('Y-m-d 23:59:59'),
        );
        $responses = array(
            'status' => 'ok',
            'data' => [
                array(
                    'employee_sap_id' => 'WAL-WIL',
                    'swip_time' => date('Y-m-d H:i:s')
                )
            ]
        );
        $now = date('Y-m-d H:i:s');

        $newLog->transaction_type = 'ATTENDANCE';
        $newLog->url = 'faceapi.test/populate';
        $newLog->params = json_encode($params);
        $newLog->response_status = 'OK';
        $newLog->response_message = json_encode($responses);
        $newLog->created_at = $now;
        $newLog->save();

//        RequestLog::create($request->all());
//        $request->validate([
//            'name' => 'required',
//            'description' => 'required',
//            'price' => 'required'
//        ]);
        $str = '{"code":1000,"desc":"Success","data":{"nextPage":"2","totalCount":"2147483647","pageData":[{"id":"1","swipeTime":"2018-09-20 10:23:23","code":"12341","name":"test12341","deptName":"Root","cardNumber":"12345676","personPic":"","swipeLocation":"test","eventName":"1"},{"id":"2","swipeTime":"2018-09-20 11:23:23","code":"12345","name":"test12345","deptName":"Root","cardNumber":"12345677","personPic":"","swipeLocation":"test","eventName":"1"},{"id":"3","swipeTime":"2018-09-20 12:23:23","code":"12342","name":"test12342","deptName":"Root","cardNumber":"12345678","personPic":"","swipeLocation":"test","eventName":"1"}]}}';
        $decoded_att = json_decode($str, 1);

//        var_dump($decoded_att);
        if (!empty($decoded_att['data']['pageData'])) {
//            var_dump($decoded_att['data']['pageData']);
            $list_attendance = $decoded_att['data']['pageData'];
            foreach ($list_attendance as $dt_att) {
                $att = array(
                    'personnelCode' => $dt_att['code'],
                    'personnelName' => $dt_att['name'],
                    'deptName' => $dt_att['deptName'],
                    'cardNumber' => $dt_att['cardNumber'],
                    'swipeLocation' => $dt_att['swipeLocation'],
                    'swipeTime' => $dt_att['swipeTime'],
                    'swipDirection' => 'OUT',
                    'eventName' => $dt_att['eventName'],
                );
                $this->insert_attendance($att, $now);
            }
        }
        echo json_encode(['status' => 'ok', 'attendance' => 'updated']);
//        return redirect()->route('products.index')
//            ->with('success', 'Product created successfully.');
    }

    public function destroy(Product $product) {
        $product->delete();

        return redirect()->route('products.index')
                        ->with('success', 'Product deleted successfully');
    }

}
