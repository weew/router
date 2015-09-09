<?php

namespace Weew\Router\Exceptions;

use Exception;

class FilterException extends Exception {
    /**
     * @var Exception
     */
    protected $originalException;

    /**
     * @param Exception $exception
     */
    public function __construct(Exception $exception) {
        parent::__construct();

        $this->originalException = $exception;
    }

    /**
     * @return Exception
     */
    public function getOriginalException() {
        return $this->originalException;
    }
}
