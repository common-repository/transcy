<?php

namespace Illuminate\BaseClass;

use Illuminate\Traits\MemorySingletonTrait;
use Illuminate\Traits\ResponseTrait;
use Illuminate\Traits\SpineTrait;

class Controller
{
    use MemorySingletonTrait;
    use ResponseTrait;
    use SpineTrait;
}
