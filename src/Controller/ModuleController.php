<?php

namespace App\Controller;

use App\Repository\ModuleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ModuleController extends AbstractController
{
  #[Route('/module', name: 'app_module')]
  public function index(ModuleRepository $moduleRepository): Response
  {
    $modules = $moduleRepository->findBy([], ["nom_module" => "ASC"]);

    return $this->render('module/index.html.twig', [
      'activePage' => 'modules',
      'modules' => $modules

    ]);
  }
}
