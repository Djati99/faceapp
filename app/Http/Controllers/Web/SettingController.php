<?php

namespace App\Http\Controllers\Web;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use App\Models\Setting;

class SettingController extends BaseController {

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index() {
        $setting = Setting::orderBy('fa_setting_id', 'desc')->first();
        return view('setting.create', compact('setting'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create() {
        return view('setting.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request) {
        $request->validate([
            'startdate' => 'required',
            'enddate' => 'required',
        ]);

        $post = $request->post();
        $post['created_at'] = date('Y-m-d H:i:s');

        Setting::create($post);

        return redirect()->route('setting.index')->with('success', 'Setting has been created successfully.');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\setting  $setting
     * @return \Illuminate\Http\Response
     */
    public function show(Setting $setting) {
        return view('setting.show', compact('setting'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Setting  $setting
     * @return \Illuminate\Http\Response
     */
    public function edit(Setting $setting) {
        return view('setting.edit', compact('setting'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\setting  $setting
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Setting $setting) {
        $request->validate([
            'startdate' => 'required',
            'enddate' => 'required',
        ]);

        $setting->fill($request->post())->save();

        return redirect()->route('setting.index')->with('success', 'Setting Has Been updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Setting  $setting
     * @return \Illuminate\Http\Response
     */
    public function destroy(Setting $setting) {
        $setting->delete();
        return redirect()->route('setting.index')->with('success', 'Setting has been deleted successfully');
    }

    public function check_connection(Request $request) {
        $ip_server = $request->get('ip_check');
        $res = $this->test_conn_with_auth($ip_server);
//        return redirect()->route('setting.index')->with('success', 'Connection succeed.' . $id_setting);
    }

    protected function test_conn_with_auth($ip_server = null) {
        $data_post_auth = '{
                "userName": "system",
                "ipAddress": "",
                "clientType": "WINPC_V2"}';

        /** $server AMBIL DARI SETTINGAN di table setting* */
        $ch_token = curl_init("https://$ip_server/brms/api/v1.0/accounts/authorize");

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
        if (!$decoded_res_token) {
            echo json_encode(
                    array(
                        'status' => 'fail',
                        'message' => 'connection fail'
                    )
            );
        } else {
            if (empty($decoded_res_token['randomKey'])) {

                echo json_encode(
                        array(
                            'status' => 'fail',
                            'message' => 'connection fail. Data not available'
                        )
                );
            }
            echo json_encode(
                    array(
                        'status' => 'success',
                        'message' => 'connection succeed'
                    )
            );
        }
    }

}
