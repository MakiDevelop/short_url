<?php

namespace App\Repositories;

use App\Models\ClickLog;
use DB;

class ClickLogRepository extends BaseRepository
{
    protected $model;

    public function __construct(ClickLog $model)
    {
        $this->model = $model;
    }

    public function list($params = [])
    {
        // return $this->model->paginate(config('constants.per_page'));
    }

    public function analyticsReferral($code)
    {
        $analyticsData = [
            'label' => [],
            'data' => [],
        ];
        $datas = $this->model->select(DB::raw('count(id) as num, referral'))
                    ->where('short_url', $code)
                    ->groupBy('referral')
                    ->get();
        if ($datas) {
            foreach ($datas as $index=>$data) {
                $analyticsData['label'][$index] = $data->referral;
                $analyticsData['data'][$index] = $data->num;
            }
        }
        
        return $analyticsData;
    }

    public function analyticsOs($code)
    {
        $analyticsData = [
            'label' => [],
            'data' => [],
        ];
        $datas = $this->model->select(DB::raw('count(id) as num, os'))
                    ->where('short_url', $code)
                    ->groupBy('os')
                    ->get();

        if ($datas) {
            foreach ($datas as $index=>$data) {
                $analyticsData['label'][$index] = $data->referral;
                $analyticsData['data'][$index] = $data->num;
            }
        }

        return $analyticsData;
    }
}
