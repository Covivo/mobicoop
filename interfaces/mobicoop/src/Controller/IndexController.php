<?php
/**
 * Created by PhpStorm.
 * User: Sofiane Belaribi
 * Date: 04/09/2018
 * Time: 15:48
 */

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class IndexController extends AbstractController
{
    /**
     * @Route("/index")
     */
    public function test()
    {
        return $this->render('index.html.twig');
    }
}
