<?php
declare(strict_types=1);

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Environment as Twig;

readonly class AppController {

    public function __construct(
        private Twig $twig
    ){}

    #[Route(path: '/{wildcard}', requirements: ['wildcard' => '^(?!api).*$'], methods: ['GET'])]
    public function index(): Response {
        return new Response(
            $this->twig->render('app.html.twig')
        );
    }

}
