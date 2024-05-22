<?php

namespace App\Controller;

use App\Entity\Stagiaire;
use App\Form\StagiaireType;
use App\Repository\StagiaireRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class StagiaireController extends AbstractController
{
  #[Route('/stagiaire', name: 'app_stagiaire')]
  public function index(StagiaireRepository $stagiaireRepository, Request $request, EntityManagerInterface $entityManager): Response
  {
    // création du formulaire de création de stagiaire pour le modal
    $stagiaire = new Stagiaire();
    $form = $this->createForm(StagiaireType::class, $stagiaire);

    $form->handleRequest($request);
    if ($form->isSubmitted() && $form->isValid()) {
      $stagiaire = $form->getData();

      // prepare() and execute()
      $entityManager->persist($stagiaire);
      $entityManager->flush();

      return $this->redirectToRoute('app_stagiaire');
    }

    $stagiaires = $stagiaireRepository->findBy([], ["nom" => "ASC"]);

    return $this->render('stagiaire/index.html.twig', [
      'activePage' => "stagiaires",
      'stagiaires' => $stagiaires,
      'formAddStagiaire' => $form,
    ]);
  }

  #[Route('/stagiaire/{id}', name: 'show_stagiaire')]
  public function show(Stagiaire $stagiaire): Response
  {
    return $this->render('stagiaire/show.html.twig', [
      'stagiaire' => $stagiaire,
    ]);
  }

  #[Route('/stagiaire/{id}/delete', name: 'delete_stagiaire')]
  public function delete(Stagiaire $stagiaire, EntityManagerInterface $entityManager): Response
  {
    $entityManager->remove($stagiaire);
    $entityManager->flush();

    return $this->redirectToRoute('app_stagiaire');
  }
}