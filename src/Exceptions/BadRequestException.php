<?php

namespace Algvo\Router\Exceptions;

class BadRequestException extends HttpException
{
    protected $message = "Bad Request";
    protected $code = 400;
}