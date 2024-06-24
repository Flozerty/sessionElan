<?php

namespace App\Controller;

use App\Entity\Formation;
use App\Entity\Session;
use App\Form\FormationType;
use App\Form\SessionType;
use App\Repository\FormationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class FormationController extends AbstractController
{
  //////////////////// Page de liste formations ////////////////////
  #[Route('/formation', name: 'app_formation')]
  public function index(FormationRepository $formationRepository, Request $request, EntityManagerInterface $entityManager): Response
  {
    // création du formulaire de création de formation pour le modal
    $formation = new Formation();
    $form = $this->createForm(FormationType::class, $formation);

    $form->handleRequest($request);
    if ($form->isSubmitted() && $form->isValid()) {
      $formation = $form->getData();

      // notif
      $this->addFlash(
        'success',
        'La formation "' . $formation->getNomFormation() . '" a été créée'
      );

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

  ///////////////////// Page de show formation /////////////////////
  #[Route('/formation/{id}', name: 'show_formation')]
  public function show(Formation $formation = null, Request $request, EntityManagerInterface $entityManager): Response
  {
    // redirection si url indique un id non existant
    if ($formation) {

      // création du formulaire de modification de formation pour le modal
      $formFormation = $this->createForm(FormationType::class, $formation);
      $formFormation->handleRequest($request);

      if ($formFormation->isSubmitted() && $formFormation->isValid()) {
        $formation = $formFormation->getData();

        // notif
        $this->addFlash(
          'modif',
          'La formation "' . $formation->getNomFormation() . '" a été mise à jour'
        );

        // prepare() and execute()
        $entityManager->persist($formation);
        $entityManager->flush();

        return $this->redirectToRoute('show_formation', ['id' => $formation->getId()]);
      }


      // création du formulaire de création de session pour le modal
      $session = new Session();

      $formSession = $this->createForm(SessionType::class, $session);

      $formSession->handleRequest($request);
      if ($formSession->isSubmitted() && $formSession->isValid()) {
        $session = $formSession->getData();

        // notif
        $this->addFlash(
          'success',
          'La session "' . $session->getIntitule() . '" a été créée'
        );

        // prepare() and execute()
        $entityManager->persist($session);
        $entityManager->flush();

        return $this->redirectToRoute('show_formation', ['id' => $formation->getId()]);
      }


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
        'activePage' => 'formations',
        'formation' => $formation,
        "sessionsNow" => $sessionsNow,
        "sessionsFuture" => $sessionsFuture,
        "sessionsPast" => $sessionsPast,
        'formAddFormation' => $formFormation,
        'formAddSession' => $formSession,
      ]);
    } else {
      return $this->redirectToRoute('app_formation');
    }
  }

  ///////////////////// suppression de formation /////////////////////
  #[Route('/formation/{id}/delete', name: 'delete_formation')]
  public function delete(Formation $formation = null, EntityManagerInterface $entityManager): Response
  {
    if ($formation) {
      // notif
      $this->addFlash(
        'success',
        'La formation "' . $formation->getNomFormation() . '" et ses sessions ont été supprimées'
      );

      $entityManager->remove($formation);
      $entityManager->flush();
    }
    return $this->redirectToRoute('app_formation');
  }
}
