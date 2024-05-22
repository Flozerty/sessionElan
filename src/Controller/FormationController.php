<?php

namespace App\Controller;

use App\Entity\Formation;
use App\Form\FormationType;
use App\Repository\FormationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class FormationController extends AbstractController
{
  #[Route('/formation', name: 'app_formation')]
  public function index(FormationRepository $formationRepository, Request $request, EntityManagerInterface $entityManager): Response
  {
    // création du formulaire de création de formation pour le modal
    $formation = new Formation();
    $form = $this->createForm(FormationType::class, $formation);

    $form->handleRequest($request);
    if ($form->isSubmitted() && $form->isValid()) {
      $formation = $form->getData();

      // prepare() and execute()
      $entityManager->persist($formation);
      $entityManager->flush();

      return $this->redirectToRoute('app_formation');
    }

    $formations = $formationRepository->findBy([], ["nom_formation" => "ASC"]);

    return $this->render('formation/index.html.twig', [
      'activePage' => 'formations',
      "formations" => $formations,
      'formAddFormation' => $form,
    ]);
  }

  #[Route('/formation/{id}', name: 'show_formation')]
  public function show(Formation $formation): Response
  {

    // répartition de toutes les sessions en "en cours / passées /futures"
    $now = new \DateTime();
    $sessionsNow = [];
    $sessionsFuture = [];
    $sessionsPast = [];

    $allSessions = $formation->getSessions();

    foreach ($allSessions as $session) {
      if ($session->getDateDebut() > $now) {
        $sessionsFuture[] = $session;
      } elseif ($session->getDateFin() < $now) {
        $sessionsPast[] = $session;
      } else {
        $sessionsNow[] = $session;
      }
    }

    return $this->render('formation/show.html.twig', [
      'formation' => $formation,
      "sessionsNow" => $sessionsNow,
      "sessionsFuture" => $sessionsFuture,
      "sessionsPast" => $sessionsPast,
    ]);
  }

  #[Route('/formation/{id}/delete', name: 'delete_formation')]
  public function delete(Formation $formation, EntityManagerInterface $entityManager): Response
  {
    $entityManager->remove($formation);
    $entityManager->flush();

    return $this->redirectToRoute('app_formation');
  }

}
