<?php

namespace App\Controller;

use App\Entity\Module;
use App\Form\ModuleType;
use App\Repository\ModuleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ModuleController extends AbstractController
{
  #[Route('/module', name: 'app_module')]
  public function index(ModuleRepository $moduleRepository, Request $request, EntityManagerInterface $entityManager): Response
  {
    // création du formulaire de création de module pour le modal
    $module = new Module();
    $form = $this->createForm(ModuleType::class, $module);

    $form->handleRequest($request);
    if ($form->isSubmitted() && $form->isValid()) {
      $module = $form->getData();

      // prepare() and execute()
      $entityManager->persist($module);
      $entityManager->flush();

      return $this->redirectToRoute('app_module');
    }

    $modules = $moduleRepository->findBy([], ["nom_module" => "ASC"]);

    return $this->render('module/index.html.twig', [
      'activePage' => 'modules',
      'modules' => $modules,
      'formAddModule' => $form,
    ]);
  }

  #[Route('/module/{id}/delete', name: 'delete_module')]
  public function delete(Module $module, EntityManagerInterface $entityManager): Response
  {
    $entityManager->remove($module);
    $entityManager->flush();

    return $this->redirectToRoute('app_module');
  }
}
