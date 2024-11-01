<?php

namespace Illuminate\Utils;

class ValidationHelper
{
    /**
     * @var array $patterns
     */
    public $patterns = array(
        'uri'           => '[A-Za-z0-9-\/_?&=]+',
        'url'           => '[A-Za-z0-9-:.\/_?&=#]+',
        'alpha'         => '[\p{L}]+',
        'words'         => '[\p{L}\s]+',
        'alphanum'      => '[\p{L}0-9]+',
        'int'           => '[0-9]+',
        'float'         => '[0-9\.,-]+',
        'tel'           => '[0-9+\s()-]+',
        'text'          => '[\p{L}0-9\s-.,;:!"%&()?+\'°#\/@]+',
        'file'          => '[\p{L}\s0-9-_!%&()=\[\]#@,.;+]+\.[A-Za-z0-9]{2,4}',
        'folder'        => '[\p{L}\s0-9-_!%&()=\[\]#@,.;+]+',
        'address'       => '[\p{L}0-9\s.,()°-]+',
        'date_dmy'      => '[0-9]{1,2}\-[0-9]{1,2}\-[0-9]{4}',
        'date_ymd'      => '[0-9]{4}\-[0-9]{1,2}\-[0-9]{1,2}',
        'email'         => '[a-zA-Z0-9_.-]+@[a-zA-Z0-9-]+.[a-zA-Z0-9-.]+[.]+[a-z-A-Z]'
    );

    /**
     * @var array $errors
     */
    public $errors = [];

    /**
     * @var array $params
     */
    public $params = [];

    protected $name;

    protected $value;

    public function __construct(array $params = [])
    {
        $this->params = $params;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return this
     */
    public function name($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * set value
     *
     * @param mixed $value
     * @return this
     */
    public function value($value)
    {
        $this->value = $value;
        return $this;
    }

    /**
     * set rule
     *
     * @param mixed $value
     * @return this
     */
    public function rule($key)
    {
        return $this->name($key)->value(($this->params[$key] ?? ''));
    }

    /**
     * Pattern to be applied to the recognition of the regular expression
     *
     * @param string $name pattern name
     * @return this
     */
    public function pattern($name)
    {

        $regex = '/^(' . $this->patterns[$name] . ')$/u';
        if ($this->value != '' && !preg_match($regex, $this->value)) {
            $this->errors[] = 'Field format ' . $this->name . ' invalid.';
        }

        return $this;
    }

    /**
     * Custom pattern
     *
     * @param string $pattern
     * @return this
     */
    public function customPattern($pattern)
    {
        $regex = '/^(' . $pattern . ')$/u';
        if ($this->value != '' && !preg_match($regex, $this->value)) {
            $this->errors[] = 'Field format ' . $this->name . ' invalid.';
        }
        return $this;
    }

    /**
     * Check required value
     *
     * @return this
     */
    public function required()
    {
        if ((isset($this->file) && $this->file['error'] == 4) || ($this->value == '' || $this->value == null)) {
            $this->errors[] = 'Field value ' . $this->name . ' not matching. ';
        }

        return $this;
    }

    /**
     * Check in array
     *
     * @return this
     */
    public function inArray(array $arr)
    {
        if (!\in_array($this->value, $arr)) {
            $this->errors[] = 'Field value ' . $this->name . ' not matching. ';
        }

        return $this;
    }

    /**
     * Compare with the value of another field
     *
     * @param mixed $value
     * @return this
     */
    public function equal($value)
    {
        if ($this->value != $value) {
            $this->errors[] = 'Field value ' . $this->name . ' not matching. ';
        }

        return $this;
    }

    /**
     * Comparison operators - less than
     *
     * @param mixed $value
     *
     * @return this
     */
    public function lt($value)
    {
        if ($this->value >= $value) {
            $this->errors[] = 'The ' . $this->name . ' must be less than ' . $value . '.';
        }

        return $this;
    }

    /**
     * Comparison operators - less than or equal
     *
     * @param mixed $value
     *
     * @return this
     */
    public function lte($value)
    {
        if ($this->value > $value) {
            $this->errors[] = 'The ' . $this->name . ' must be less than or equal ' . $value . '.';
        }

        return $this;
    }

    /**
     * Comparison operators - greater than
     *
     * @param mixed $value
     *
     * @return this
     */
    public function gt($value)
    {
        if ($this->value <= $value) {
            $this->errors[] = 'The ' . $this->name . ' must be greater than ' . $value . '.';
        }

        return $this;
    }

    /**
     * Comparison operators - greater than or equal
     *
     * @param mixed $value
     *
     * @return this
     */
    public function gte($value)
    {
        if ($this->value < $value) {
            $this->errors[] = 'The ' . $this->name . ' must be greater than or equal ' . $value . '.';
        }

        return $this;
    }

    /**
     * Purifies to prevent XSS attacks
     *
     * @param string $string
     * @return $string
     */
    public function purify($string)
    {
        return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Validated fields
     *
     * @return boolean
     */
    public function isSuccess()
    {
        if (empty($this->errors)) {
            return true;
        }
    }

    /**
     * Validated fields
     *
     * @return boolean
     */
    public function isFailed()
    {
        if (!empty($this->errors)) {
            return true;
        }
    }

    /**
     * Validation errors
     *
     * @return array $this->errors
     */
    public function getErrors()
    {
        if (!$this->isSuccess()) {
            return $this->errors;
        }
    }
}
