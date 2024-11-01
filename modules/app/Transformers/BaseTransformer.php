<?php

namespace TranscyApp\Transformers;

use Illuminate\BaseClass\Transformer;
use Illuminate\Exceptions\AppException;

class BaseTransformer extends Transformer
{
    /**
     * toArray function.
     *
     * @param array $model
     * @param array $options
     *
     * @return mixed
     */
    public function toArray($model, $options = [])
    {
        throw new AppException('Please implement method toArray');
    }
}
