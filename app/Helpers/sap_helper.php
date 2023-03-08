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
//    unset($data_need_to_delivered_to_cpi['RECORD_ID']);
//    dd($data_need_to_delivered_to_cpi);
    $request_body[$cpi_att_f]['I_SWIPE']["item"] = $data_need_to_delivered_to_cpi;
//        dd($request_body);
//    echo json_encode($request_body);
//    exit();
//        echo json_encode($request_body, 1);die();
    //DEV
    // $ch = curl_init(config);
    //QA
    //$ch = curl_init("https://l200335-iflmap.hcisbp.ap1.hana.ondemand.com/http/epmsdataflow220");
    //$json = '{"urn:ZCH_FR_SWIPE_IN":{"I_SWIPE":{"item":[{"PRFNR":"POM SAKILAN","EMPNR":"1SHL\/IOI\/0712\/6910","SOURCE":"D","SDATE":"2023-03-01","STIME":"19:44:14","TYPE":"I","ERNAM":"","ERDAT":"","ERZET":"","REMARK":NULL},{"PRFNR":"POM SAKILAN","EMPNR":"1SHL\/IOI\/0418\/6944","SOURCE":"D","SDATE":"2023-03-01","STIME":"19:45:11","TYPE":"O","ERNAM":"","ERDAT":"","ERZET":"","REMARK":"01.0001"}]}}}';
//    $json = '{"urn:ZCH_FR_SWIPE_IN":{"I_SWIPE":{"item":[{"PRFNR":"POM GOMALI","EMPNR":"1PDP\/IOI\/0219\/26160","SOURCE":"D","SDATE":"2023-01-17","STIME":"15:53:12","TYPE":"I","ERNAM":"","ERDAT":"","ERZET":"","REMARK":""},{"PRFNR":"POM GOMALI","EMPNR":"1PDP\/IOI\/0219\/26160","SOURCE":"D","SDATE":"2023-01-17","STIME":"15:55:49","TYPE":"I","ERNAM":"","ERDAT":"","ERZET":"","REMARK":""},{"PRFNR":"POM GOMALI","EMPNR":"1PDP\/IOI\/0219\/26160","SOURCE":"D","SDATE":"2023-01-17","STIME":"15:55:53","TYPE":"I","ERNAM":"","ERDAT":"","ERZET":"","REMARK":""},{"PRFNR":"POM GOMALI","EMPNR":"1PDP\/IOI\/0219\/26160","SOURCE":"D","SDATE":"2023-01-17","STIME":"15:56:04","TYPE":"I","ERNAM":"","ERDAT":"","ERZET":"","REMARK":""},{"PRFNR":"POM GOMALI","EMPNR":"1PDP\/IOI\/0219\/26160","SOURCE":"D","SDATE":"2023-01-17","STIME":"15:56:05","TYPE":"I","ERNAM":"","ERDAT":"","ERZET":"","REMARK":""}]}}}';
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($request_body));
    curl_setopt($ch, CURLOPT_USERPWD, "$cpi_att_u:$cpi_att_p");
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type:application/json;charset=UTF-8',
//        'Accept:text/html',
//        'Content-Type: text/html;charset=UTF-8',
//        'Host:ioics4q88.ioigroup.com'
    ));
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

    $result = curl_exec($ch);
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if ($close_soap) {
        curl_close($ch);
    }
//    var_dump($httpcode);echo " \n";
//    dd($httpcode);
//        dd($result);
    $response = [];
    if ($httpcode == 200) {
//$result = '<n0:ZCH_FR_SWIPE_INResponse xmlns:n0="urn:sap-com:document:sap:rfc:functions" xmlns:soap-env="http://schemas.xmlsoap.org/soap/envelope/"><E_SWIPE><item><PRFNR>POM SAKILAN</PRFNR><EMPNR>1PDP/IOI/0219/26160</EMPNR><SOURCE>D</SOURCE><SDATE>2023-01-17</SDATE><STIME>15:56:05</STIME><TYPE>I</TYPE><ERNAM/><ERDAT>0000-00-00</ERDAT><ERZET>00:00:00</ERZET><REMARK>Period available only 02 . 2023</REMARK></item><item><PRFNR>POM SAKILAN</PRFNR><EMPNR>1PDP/IOI/0219/26160</EMPNR><SOURCE>D</SOURCE><SDATE>2023-01-17</SDATE><STIME>15:53:12</STIME><TYPE>O</TYPE><ERNAM/><ERDAT>0000-00-00</ERDAT><ERZET>00:00:00</ERZET><REMARK>Period available only 02 . 2023</REMARK></item></E_SWIPE></n0:ZCH_FR_SWIPE_INResponse>';        
        $data = new SimpleXMLElement($result);
        $jml_feedback = count($data->E_SWIPE->item);
        $errors = [];
        for ($x = 0; $x < $jml_feedback; $x++) {
            $item = (array) $data->E_SWIPE->item[$x];
            if (empty($item['REMARK'])) {
                
            } else {
                foreach ($item as $k_item => $v_item) {
                    $errors['ERROR'][$x][$k_item] = $v_item;
                }
                //$errors['ERROR'][$x]['msg'] = (string) $item['REMARK'];
            }
        }
//        dd($errors);
        $response["feedback"] = $errors;
        $response["data"] = $data;
        $response["status_code"] = $httpcode;
    } else if ($httpcode == 500) {
        $response["feedback"] = $result;
        $response["data"] = [];
        $response["status_code"] = $httpcode;
    } else {
        $data = $result;
        $response["feedback"] = [];
//        dd($result);
        $response["data"] = $data;
        $response["status_code"] = $httpcode;
    }
//        dd($data);
    return $response;
}
