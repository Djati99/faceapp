<?php

namespace App\DataTables;

use Illuminate\Support\Facades\DB;
use App\Models\AccessControl;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Illuminate\Http\JsonResponse;
use Yajra\DataTables\Services\DataTable;
use DataTables;

class ExportAccessControlDTableDataTable extends DataTable {

    /**
     * Build DataTable class.
     *
     * @param mixed $query Results from query() method.
     * @return \Yajra\DataTables\DataTableAbstract
     */
    public function dataTable($query) {
//        return new JsonResponse(
//            ['tes' => 'nganu'],
//            200,
//            0,
//            []
//        );    
//        dd($query);
        $return = datatables()
                        ->eloquent($query)
                        ->addColumn('action', '');
//        dd($return);
        return $return;
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\Models\AccessControl $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(AccessControl $model) {
        $query = $model->newQuery();
//        return $query;
        $search_val_all = $this->request->all();
        if(empty($search_val_all['search']['value'])){
            $data = $model::select('*');
//            dd($return_response);
        }else{
            $search_val = $search_val_all['search']['value'];
            
            $date_search = \DateTime::createFromFormat('Y-m-d', $search_val);
            if($date_search){
                $alarmtime_search = $date_search->format('Y-m-d');
            }else{
                $alarmtime_search = date('Y-m-d');
            }
            $data = $model::select('*')
                    ->where('alarmtime','>=',$alarmtime_search)
                    ->orwhere('personid','like','%'.$search_val.'%')
                    ->orwhere('firstname','like','%'.$alarmtime_search.'%')
                    ->get();
//            dd($data);
//            $now = date('Y-m-d');
//            $strdate = "$now 00:00:01";
//            $enddate = "$now 00:00:01";
//            $data = $model
//                    ->where('alarmtime', '>=', $strdate)
//                    ->where('alarmtime', '<=', $enddate)
//                    ->get();
            $arr_data = $data->toArray();
            
            $return_response = [];
            if (!$arr_data || count($arr_data) < 1) {
                $return_response = [];
            }

            $swipetime = [];
            $new_data = [];
            $format = 'Y-m-d H:i:s';
            foreach ($arr_data as $dt_access) {
                if (empty($new_data[$dt_access['personid']])) {
                    $new_data[$dt_access['personid']] = $dt_access;
                    $new_data[$dt_access['personid']]['nama_personnel'] = $dt_access['personid'] . PHP_EOL . '[' . $dt_access['firstname'] . ']';
                    for ($loopInOt = 0; $loopInOt < 5; $loopInOt++) {
                        $new_data[$dt_access['personid']]["time_in_$loopInOt"] = '';
                        $new_data[$dt_access['personid']]["time_ot_$loopInOt"] = '';
                    }
                }
                $swipetime[$dt_access['personid']][$dt_access['accesstype']][] = $dt_access['alarmtime'];
            }
            
            /** sorting first IN and/or Last OUT * */
            $default_element_time = ['', '', '', '', ''];
            foreach ($swipetime as $personid => $direct) {
                //Per direction IN/OUT
                $swipetime[$personid]['IN'] = $default_element_time;
                $swipetime[$personid]['OUT'] = $default_element_time;
                $swipetime[$personid]['first_in'] = '0';
                $swipetime[$personid]['last_out'] = '0';
                $new_data[$personid]['work_date'] = '-';
                $new_data[$personid]['ot'] = '-';
                $new_data[$personid]['first_in'] = '0';
                $new_data[$personid]['last_out'] = '0';

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
                    $new_data[$personid]['first_in'] = $inning_sorted_up[0];
                    if (!empty($outing_sorted_down[0])) {
                        $swipetime[$personid]['last_out'] = $outing_sorted_down[0];
                        $new_data[$personid]['last_out'] = $outing_sorted_down[0];
                        $date_in = \DateTimeImmutable::createFromFormat($format, $inning_sorted_up[0]);
                        $date_ot = \DateTimeImmutable::createFromFormat($format, $outing_sorted_down[0]);
                        $interval = $date_in->diff($date_ot);
                        if ($interval) {
                            $new_data[$personid]['duration'] = $interval->format('%hH%iM');
                        } else {
                            $new_data[$personid]['duration'] = '0';
                        }
                    } else {
                        $new_data[$personid]['duration'] = '0';
                    }
                } else {
                    $new_data[$personid]['duration'] = '0';
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
                        $new_data[$personid]["time_ot_$loopOt"] = $dttime;
                        $loopOt++;
                        $start_sum_total_rest = 1;
                        $start_sum_out = $dttime;
                    }
                    if ($kode == 'I') {
                        if($start_sum_total_rest == 1){
                            $start_sum_out1 = \DateTimeImmutable::createFromFormat("Y-m-d H:i:s", $start_sum_out);
                            $start_sum_in1 = \DateTimeImmutable::createFromFormat("Y-m-d H:i:s", $dttime);
                            if($start_sum_out1 && $start_sum_in1){
//                                var_dump([$start_sum_out1,$start_sum_in1]);
//                                dd([$start_sum_out1,$start_sum_in1]);
                                $interval = date_diff($start_sum_out1,$start_sum_in1);
//                            $interval = ((DateTimeImmutable)$start_sum_out1)->diff($start_sum_in1);
                            $hours_rests = $hours_rests + ($interval)->format("%h");
                            $minutes_rests = $minutes_rests + ($interval)->format("%i");
                            }
                            $start_sum_total_rest = 0;
                            $start_sum_out = "";
                        }
//                        $datetime = \DateTimeImmutable::createFromFormat($format, $dttime);
                        $new_data[$personid]["time_in_$loopIn"] = $dttime;
                        $loopOt = $loopIn;
                        $loopIn++;
                    }
                }
                //Rest Total
                $new_data[$personid]['total_rest'] = ($hours_rests + ($minutes_rests/60)) . "h" .($minutes_rests%60)."m";
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
//            dd($new_data);
            $return_response = array_values($new_data);            
            $data = collect($return_response)->toQuery();
        }
        return $this->applyScopes($data);
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html() {
        return $this->builder()
                        ->setTableId('exportaccesscontroldtable-table')
                        ->columns($this->getColumns())
                        ->minifiedAjax()
                        ->dom('Bfrtip')
                        ->orderBy(1)
                        ->buttons(
                                Button::make('export'),
//                                Button::make('print'),
//                                Button::make('reset'),
//                                Button::make('reload')
        );
    }

    /**
     * Get columns.
     *
     * @return array
     */
    protected function getColumns() {
        return [
                    Column::computed('action')
                    ->exportable(false)
                    ->printable(false)
                    ->width(60)
                    ->addClass('text-center'),
            Column::make('personid'),
            Column::make('created_at'),
        ];
    }

    /**
     * Get filename for export.
     *
     * @return string
     */
    protected function filename() {
        return 'AccessControl_' . date('YmdHis');
    }

}
