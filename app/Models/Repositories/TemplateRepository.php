<?php

namespace App\Models\Repositories;

use App\User;
use Illuminate\Support\Facades\DB;

class TemplateRepository
{
    public function get($id)
    {
        return User::find($id);
    }

    public function all()
    {
        return User::all();
    }

    public function delete($id)
    {
        User::destroy($id);
    }

    public function update($id, array $data)
    {
        User::find($id)->update($data);
    }

    public function register($input)
    {
        return User::create($input);
    }
}
