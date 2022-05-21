<?php

namespace Algvo\Router\Exceptions;

class NotFoundException extends HttpException
{
    protected $message = "Page not found";
    protected $code = 404;
}