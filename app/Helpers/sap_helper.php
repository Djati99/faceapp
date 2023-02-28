<?php

//if (!defined('BASEPATH'))
//    exit('No direct script access allowed');


function send_time_attendance_to_sap_from_soap($data_need_to_delivered_to_cpi, $prfnr, $close_soap = false) {
    $cpi_att_f = 'urn:ZCH_FR_SWIPE_IN';
    $cpi_att_r = config('face.CPI_URL');
    $cpi_att_u = config('face.CPI_USER');
    $cpi_att_p = config('face.CPI_PWD');
    $cpi_transaction_code = "T_SWIPE";
    $request_body['I_SWIPE']["item"] = $data_need_to_delivered_to_cpi;
//    echo json_encode($request_body);
//        $client = new SoapClient($cpi_att_r);

    try {
        $opts = array(
            'http' => array(
                'user_agent' => 'PHPSoapClient'
            )
        );
        $context = stream_context_create($opts);

        $soapClientOptions = array(
//            'stream_context' => $context,
            'cache_wsdl' => WSDL_CACHE_NONE,
            'trace' => true,
        );
//    $file_wsdl_path  = dirname(__FILE__) .'storage'. DIRECTORY_SEPARATOR . 'zch_fr_wsdl.xml';
        $path_file_wsdl = storage_path('app' . DIRECTORY_SEPARATOR . 'zch_fr_wsdl.xml');
//        dd($path_file_wsdl);
//    C:\xampp7433\htdocs\faceapp\storage\app\_token.txt

        $client = new SoapClient($path_file_wsdl);
        $response = $client->ZCH_FR_SWIPE_IN($request_body);
        dd($response);
    } catch (Exception $e) {
        echo $e->getMessage();
        exit();
    }
//        dd($client);
}

function send_time_attendance_to_cpi($data_need_to_delivered_to_cpi, $prfnr, $close_soap = false) {

    $cpi_att_f = 'urn:ZCH_FR_SWIPE_IN';
    $cpi_att_r = config('face.CPI_URL');
    $cpi_att_u = config('face.CPI_USER');
    $cpi_att_p = config('face.CPI_PWD');
    $cpi_transaction_code = "T_SWIPE";

//        dd([$cpi_att_p,$cpi_att_r,$cpi_att_u]);
    //DEV
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $cpi_att_r);
//        var_dump($data_need_to_delivered_to_cpi[0]);
//        $request_body[$cpi_att_f]['PRFNR']= $prfnr;
//        unset($data_need_to_delivered_to_cpi['PRFNR']);
    $request_body[$cpi_att_f]['I_SWIPE']["item"] = $data_need_to_delivered_to_cpi;
//        dd($request_body);
//    echo json_encode($request_body);
//    exit();
//        echo json_encode($request_body, 1);die();
    //DEV
    // $ch = curl_init(config);
    //QA
    //$ch = curl_init("https://l200335-iflmap.hcisbp.ap1.hana.ondemand.com/http/epmsdataflow220");
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($request_body));
    curl_setopt($ch, CURLOPT_USERPWD, "$cpi_att_u:$cpi_att_p");
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type:application/json;charset=UTF-8',
        'Accept:text/html',
//        'Content-Type: text/html;charset=UTF-8',
//        'Host:ioics4q88.ioigroup.com'
    ));
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

    $result = curl_exec($ch);
    $httpcode = curl_getinfo($ch);
    if ($close_soap) {
        curl_close($ch);
    }
//    echo ".".json_encode($httpcode)." \n";
//    dd($httpcode);
//        dd($result);
    if ($httpcode == 200) {
        $data = new SimpleXMLElement($result);
    } else {
        $data = $result;
    }
//        dd($data);
    $response["data"] = $data;
    $response["status_code"] = $httpcode;
    return $response;
}
