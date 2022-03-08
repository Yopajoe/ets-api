<?php

namespace Core;

interface ICrud
{
    public function create();
    public function read(int $id = null, array $condition);
    public function update();
    public function delete();
}