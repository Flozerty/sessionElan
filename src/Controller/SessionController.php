<?php

namespace App\Controller;

use App\Entity\Session;
use App\Repository\ModuleRepository;
use App\Repository\SessionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class SessionController extends AbstractController
{
    #[Route('/session', name: 'app_session')]
    public function index(SessionRepository $sessionRepository): Response
    {
        $sessions = $sessionRepository->findBy([], ["intitule" => "ASC"]);

        return $this->render('session/index.html.twig', [
            'activePage' => 'sessions',
            "sessions" => $sessions,
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
}
