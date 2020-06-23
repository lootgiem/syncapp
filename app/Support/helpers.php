<?php

use Illuminate\Support\Collection;

function isCollection($obj)
{
    return $obj instanceof Illuminate\Database\Eloquent\Collection
        || $obj instanceof Collection;
}




