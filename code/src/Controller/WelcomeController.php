<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class WelcomeController extends AbstractController
{
    public function index(): Response
    {
        return $this->json([
            'name' => 'Joke API',
            'version' => '1.0.0',
        ]);
    }
}