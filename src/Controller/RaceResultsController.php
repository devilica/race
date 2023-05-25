<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RaceResultsController extends AbstractController
{
    #[Route('/', name: 'app_race_results')]
    public function index(): Response
    {
        return $this->render('race_results/index.html.twig', [
            'controller_name' => 'RaceResultsController',
        ]);
    }
}