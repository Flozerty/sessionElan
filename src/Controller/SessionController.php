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
    // calcul de la durée totale des modules
    $dureeTotale = 0;
    $progs = $session->getProgrammes();

    // On récupère l'ensemble de tous les modules pour comparaison future
    $modules = $moduleRepository->findBy([], ["nom_module" => "ASC"]);

    $modulesSession = [];

    foreach ($progs as $prog) {
      // incrémentation de duréé totale
      $dureeTotale += $prog->getDuree();
      // on récupère tous les modules de la session pour récupérer ensuite tous les modules qui ne sont pas dedans
      $modulesSession[] = $prog->getModule();
    }

    $autresModules = [];

    foreach ($modules as $module) {
      // si le module n'est pas dans 
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

  #[Route('/session/{idSession}/delete/programme/{idProgramme}', name: 'remove_session_programme')]
  public function remove(int $idSession, int $idProgramme, SessionRepository $sessionRepository, ProgrammeRepository $programmeRepository, EntityManagerInterface $entityManager)
  {
    $session = $sessionRepository->find($idSession);
    $programme = $programmeRepository->find($idProgramme);
    $session->removeProgramme($programme);
    $entityManager->flush();
    return $this->redirectToRoute('show_session', ['id' => $session->getId()]);
  }

  #[Route('/session/{id}/add/programme', name: 'add_session_programme')]
  public function add(Session $session, Programme $programme, EntityManagerInterface $entityManager)
  {
    // $entityManager->add($programme);
    // $entityManager->flush();
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
