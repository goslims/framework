<?php
namespace SLiMS\Http\Middleware;

use Closure;
use SLiMS\Http\Request;

abstract class Base
{
    abstract public function handle(Request $request, Closure $next);
}   