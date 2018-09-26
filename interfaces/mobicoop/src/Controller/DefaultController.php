<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Util\Calculator;

class DefaultController extends AbstractController
{
    public function index()
    {
        // Generate a square randomed value between min-max
        $calculs = new Calculator();
        $nb = $calculs->randAndSquare(1, 5);
        return $this->render('default/index.html.twig', [
            'controller_name' => 'DefaultController',
            'nb' => $nb
        ]);
    }
}
