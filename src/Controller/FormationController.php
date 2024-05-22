<?php

namespace App\Controller;

use App\Entity\Formation;
use App\Repository\FormationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class FormationController extends AbstractController
{
  #[Route('/formation', name: 'app_formation')]
  public function index(FormationRepository $formationRepository): Response
  {
    $formations = $formationRepository->findBy([], ["nom_formation" => "ASC"]);

    return $this->render('formation/index.html.twig', [
      'activePage' => 'formations',
      "formations" => $formations,
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
}
