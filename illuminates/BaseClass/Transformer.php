<?php

namespace Illuminate\BaseClass;

use Illuminate\Traits\SpineTrait;

abstract class Transformer
{
    use SpineTrait;

    /**
     * Need Static memories
     */
    protected static $memories = [];

    /**
     * New static function.
     *
     * @param bool $reset
     *
     * @return object
     */
    final public static function newStatic($reset = true)
    {
        if (isset(self::$memories[static::class])) {
            return self::$memories[static::class];
        }

        $class = new static();
        self::$memories[static::class] = $class;

        return $class;
    }

    /**
     * Cast model to object
     *
     * @return bool
     */
    protected function shouldObject()
    {
        return false;
    }

    /**
     * Transform function.
     *
     * @param array $models
     * @param string $callback
     *
     * @return mixed
     */
    final public static function transform($models, $callback = null, $options = [])
    {
        return (self::newStatic())->handle($models, $callback, $options);
    }

    /**
     * Handle function.
     *
     * @param array $models
     * @param string $callback
     *
     * @return mixed
     */
    public function handle($models, $callback = null, $options = [])
    {
        if (empty($models)) {
            if (is_array($models)) {
                return [];
            }

            return null;
        }

        $array = (array)$models;
        if (isset($array[0]) && is_array($models)) {
            $datas = [];
            foreach ($models as $index => $model) {
                $datas[] = $this->handle($model, $callback, $options);
            }

            return $datas;
        }

        return $this->callback($models, $callback, $options);
    }

    /**
     * callback function.
     *
     * @param array $models
     * @param string $callback
     *
     * @return mixed
     */
    public function callback($model, $callback = null, $options = [])
    {
        //cast to object if model is array
        if ($this->shouldObject()) {
            $model = is_object($model) || !is_array($model) ? $model : (object)$model;
        }

        //call default function
        if (is_null($callback)) {
            return $this->toArray($model, $options);
        }

        //callback
        return $this->$callback($model, $options);
    }

    /**
     * __callStatic function.
     *
     * @param string $method
     * @param array $parameters
     *
     * @return mixed
     */
    public static function __callStatic($method, $parameters)
    {
        $class  = self::newStatic();
        $method = lcfirst(str_replace('call', '', $method));
        if (method_exists($class, $method) && is_callable([$class, $method])) {
            return $class->handle($parameters[0], $method, ($parameters[1] ?? []));
        }

        throw new \Exception(sprintf(__('Method %s not found!', 'hhg-spine'), $method));
    }

    /**
     * toArray function.
     *
     * @param array $model
     * @param array $options
     *
     * @return mixed
     */
    abstract public function toArray($model, $options = []);
}
