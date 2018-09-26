<?php

namespace App\Entity;

/**
 * A DataProvider response.
 */
class Response
{
    const DEFAULT_CODE = 404;
    
    /**
     * @var int $code The response code.
     */
    private $code;
    
    /**
     * @var object|array $value The value of the response.
     */
    private $value;
    
    public function __construct(int $code=self::DEFAULT_CODE, $value=null)
    {
        $this->setCode($code);
        $this->setValue($value);
    }
    
    public function getCode(): int
    {
        return $this->code;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function setCode(int $code)
    {
        $this->code = $code;
    }

    public function setValue($value)
    {
        $this->value = $value;
    }
}
