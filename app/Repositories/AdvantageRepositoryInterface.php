<?php

namespace App\Repositories;

interface AdvantageRepositoryInterface
{
    public function getAll();

    public function getById($id);

    public function createAdvantage($request);

    public function updateAdvantage($request, $id);

    public function deleteAdvantage($id); // Add this
}
