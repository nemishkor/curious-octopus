<?php
declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AppController extends AbstractController {

    #[Route(path: '/{wildcard}', requirements: ['wildcard' => '^(?!api).*$'], methods: ['GET'])]
    public function index(): Response {
        return $this->render('app.html.twig');
    }

}
