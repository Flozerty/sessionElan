<?php

namespace App\Controller;

use App\Entity\Module;
use App\Entity\Programme;
use App\Entity\Session;
use App\Form\SessionType;
use App\Repository\ModuleRepository;
use App\Repository\ProgrammeRepository;
use App\Repository\SessionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class SessionController extends AbstractController
{
  #[Route('/session', name: 'app_session')]
  public function index(SessionRepository $sessionRepository, Request $request, EntityManagerInterface $entityManager): Response
  {
    // création du formulaire de création de session pour le modal
    $session = new Session();
    $form = $this->createForm(SessionType::class, $session);

    $form->handleRequest($request);
    if ($form->isSubmitted() && $form->isValid()) {
      $session = $form->getData();

      // prepare() and execute()
      $entityManager->persist($session);
      $entityManager->flush();

      return $this->redirectToRoute('app_session');
    }

    $sessions = $sessionRepository->findBy([], ["intitule" => "ASC"]);

    return $this->render('session/index.html.twig', [
      'activePage' => 'sessions',
      "sessions" => $sessions,
      'formAddSession' => $form,
    ]);
  }

  #[Route('/session/{id}', name: 'show_session')]
  public function show(Session $session, ModuleRepository $moduleRepository): Response
  {
    // On récupère l'ensemble de tous les modules pour comparaison future
    $modules = $moduleRepository->findBy([], ["nom_module" => "ASC"]);

    // calcul de la durée totale des modules
    $dureeTotale = 0;
    $progs = $session->getProgrammes();

    $modulesSession = [];
    $autresModules = [];

    foreach ($progs as $prog) {
      // incrémentation de duréé totale
      $dureeTotale += $prog->getDuree();
      // on récupère tous les modules de la session pour récupérer ensuite tous les modules qui ne sont pas dedans
      $modulesSession[] = $prog->getModule();
    }

    foreach ($modules as $module) {
      // si le module n'est pas dans la session
      if (!in_array($module, $modulesSession)) {
        $autresModules[] = $module;
      }
    }

    return $this->render('session/show.html.twig', [
      'session' => $session,
      'dureeTotale' => $dureeTotale,
      'autresModules' => $autresModules,

    ]);
  }

  /////////////// Supprimer un programme de la session ///////////////
  #[Route('/session/{idSession}/delete_programme/{idProgramme}', name: 'remove_session_programme')]
  public function remove(int $idSession, int $idProgramme, SessionRepository $sessionRepository, ProgrammeRepository $programmeRepository, EntityManagerInterface $entityManager)
  {
    $session = $sessionRepository->find($idSession);
    $programme = $programmeRepository->find($idProgramme);
    $session->removeProgramme($programme);
    $entityManager->flush();
    return $this->redirectToRoute('show_session', ['id' => $session->getId()]);
  }

  /////////////// Ajouter (créer) un programme a la session ///////////////
  #[Route('/session/{idSession}/create_programme/{idModule}', name: 'create_session_programme')]
  public function add(int $idSession, int $idModule, SessionRepository $sessionRepository, ModuleRepository $moduleRepository, ProgrammeRepository $programmeRepository, EntityManagerInterface $entityManager)
  {
    $session = $sessionRepository->find($idSession);
    $module = $moduleRepository->find($idModule);

    $programme = new Programme();

    // un Programme ne peut pas avoir d'id NULL, on 'persist' le nouveau Programme pour lui attribuer un id automatiquement et qu'il n'y ait pas de problème.
    $entityManager->persist($programme);
    $programme->setModule($module);
    $programme->setSession($session);
    $programme->setDuree(0);

    $entityManager->flush();

    return $this->redirectToRoute('show_session', ['id' => $session->getId()]);
  }

  #[Route('/session/{id}/delete', name: 'delete_session')]
  public function delete(Session $session, EntityManagerInterface $entityManager): Response
  {
    $entityManager->remove($session);
    $entityManager->flush();

    return $this->redirectToRoute('app_session');
  }

}
