<?php

namespace App\Controller;

use App\Entity\Formation;
use App\Entity\Module;
use App\Entity\Programme;
use App\Entity\Session;
use App\Form\ProgrammeType;
use App\Form\SessionType;
use App\Repository\FormationRepository;
use App\Repository\ModuleRepository;
use App\Repository\ProgrammeRepository;
use App\Repository\SessionRepository;
use App\Repository\StagiaireRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class SessionController extends AbstractController
{
  //////////////////// Page de liste sessions ////////////////////
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

  //////////////////// Page de show session ////////////////////
  #[Route('/session/{id}', name: 'show_session')]
  public function show(Session $session = null, ModuleRepository $moduleRepository, Request $request, EntityManagerInterface $entityManager): Response
  {
    if ($session) {
      // création du formulaire de création de session pour le modal
      $form = $this->createForm(SessionType::class, $session);

      $form->handleRequest($request);
      if ($form->isSubmitted() && $form->isValid()) {
        $session = $form->getData();

        // prepare() and execute()
        $entityManager->persist($session);
        $entityManager->flush();

        return $this->redirectToRoute('show_session', ['id' => $session->getId()]);
      }
    } else {
      return $this->redirectToRoute('app_session');
    }


    // calcul de la durée totale des modules
    $dureeTotale = 0;
    $progs = $session->getProgrammes();

    $modulesSession = [];
    $autresModules = [];

    // On récupère l'ensemble de tous les modules
    $modules = $moduleRepository->findBy([], ["nom_module" => "ASC"]);
    foreach ($progs as $prog) {
      // incrémentation de duréé totale
      $dureeTotale += $prog->getDuree();
      // on récupère tous les modules de la session
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
      'formAddSession' => $form,
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

    $programme->setModule($module);
    $programme->setSession($session);
    $programme->setDuree(0);

    $entityManager->persist($programme);
    $entityManager->flush();

    return $this->redirectToRoute('show_session', ['id' => $session->getId()]);
  }

  /////////////// update le programme d'une  session ///////////////
  #[Route('/session/{idSession}/update_programme/{idProgramme}', name: 'session_update_programme')]
  public function updateProgramme(int $idSession, int $idProgramme, Request $request, SessionRepository $sessionRepository, ProgrammeRepository $programmeRepository, EntityManagerInterface $entityManager)
  {
    $session = $sessionRepository->find($idSession);
    $programme = $programmeRepository->find($idProgramme);

    // Vérifier si le programme existe et appartient à la session
    if (!$programme) {
      // notif
      $this->addFlash(
        'warning',
        'programme inexistant'
      );
      return $this->redirectToRoute('app_session');
    }

    $duree = $request->request->get('duree');

    if (is_numeric($duree) && !empty($duree) && $duree >= 0) {

      $programme->setDuree($duree);
      // notif
      $this->addFlash(
        'success',
        'durée du programme mise à jour'
      );
      $entityManager->flush();
    } else {
      // notif
      $this->addFlash(
        'warning',
        'durée non conforme'
      );
    }
    return $this->redirectToRoute('show_session', ['id' => $idSession]);
  }

  ////////////////////////// supprimer session //////////////////////////
  #[Route('/session/{id}/delete', name: 'delete_session')]
  public function delete(Session $session = null, EntityManagerInterface $entityManager): Response
  {
    if ($session) {
      $entityManager->remove($session);
      $entityManager->flush();
    }
    return $this->redirectToRoute('app_session');
  }

  ////////////////// supprimer session depuis formation //////////////////
  #[Route('/formation/{idFormation}/deleteSession/{idSession}', name: 'delete_session_formation')]
  public function deleteSessionFormation(int $idFormation, int $idSession, EntityManagerInterface $entityManager, SessionRepository $sessionRepository, FormationRepository $formationRepository): Response
  {
    $session = $sessionRepository->find($idSession);
    $formation = $formationRepository->find($idFormation);

    $entityManager->remove($session);
    $entityManager->flush();

    return $this->redirectToRoute('show_formation', ['id' => $idFormation]);
  }

  #[Route('/session/{idSession}/removeStagiaire/{idStagiaire}', name: 'remove_session_stagiaire')]
  public function removeStagiaireSession(int $idSession, int $idStagiaire, SessionRepository $sessionRepository, StagiaireRepository $stagiaireRepository, EntityManagerInterface $entityManager): Response
  {
    $session = $sessionRepository->find($idSession);
    $stagiaire = $stagiaireRepository->find($idStagiaire);

    $session->removeStagiaire($stagiaire);
    $entityManager->persist($session);
    $entityManager->flush();

    return $this->redirectToRoute('show_session', ['id' => $idSession]);
  }


}
