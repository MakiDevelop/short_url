<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\Model;

class BaseRepository
{
    protected $model;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    public function insert($datas)
    {
        $data = new $this->model();
        // return $data->fill($datas)->save();
        return $this->model->create($datas);
    }

    public function update($id, $datas)
    {
        $data = $this->getByID($id);
        return $data->fill($datas)->save();
    }

    public function delete($data)
    {
        return $data->delete();
    }

    public function getByID($id)
    {
        return $this->model->find($id);
    }
}
