<?php
namespace App\Exception;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Controller\ErrorController;

class ExceptionController extends Erro
{
    public function show(): bool
    {
        return false;
    }
}