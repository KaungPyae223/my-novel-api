<?php

namespace App\Repositories;

use App\Models\User;

class UserRepository
{

    protected $user;

    public function __construct() {
        $this->user = new User();
    }

    public function findUser($id)
    {
        return $this->user->find($id);
    }


    public function updateUser($id, $data)
    {

        $user = $this->findUser($id);

        return $user->update($data);
    }

}
