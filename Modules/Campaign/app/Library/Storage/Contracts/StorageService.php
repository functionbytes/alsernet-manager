<?php

namespace app\Library\Storage\Contracts;

interface StorageService
{
    public function store(Storable $object);
}
