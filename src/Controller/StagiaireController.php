<?php

namespace App\Controller;

use App\Entity\Stagiaire;
use App\Form\StagiaireType;
use App\Repository\SessionRepository;
use App\Repository\StagiaireRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class StagiaireController extends AbstractController
{
  //////////////////// Page de liste stagiaires ////////////////////
  #[Route('/stagiaire', name: 'app_stagiaire')]
  public function index(StagiaireRepository $stagiaireRepository, Request $request, EntityManagerInterface $entityManager): Response
  {
    // création du formulaire de création de stagiaire pour le modal

    $stagiaire = new Stagiaire();
    $form = $this->createForm(StagiaireType::class, $stagiaire);

    $form->handleRequest($request);
    if ($form->isSubmitted() && $form->isValid()) {
      $stagiaire = $form->getData();

      // notif
      $this->addFlash(
        'success',
        '"' . $stagiaire . '" a été créé'
      );

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

  ////////////////////// Page de show stagiaire //////////////////////
  #[Route('/stagiaire/{id}', name: 'show_stagiaire')]
  public function show(Stagiaire $stagiaire = null, Request $request, EntityManagerInterface $entityManager): Response
  {
    if ($stagiaire) {
      $form = $this->createForm(StagiaireType::class, $stagiaire);

      $form->handleRequest($request);
      if ($form->isSubmitted() && $form->isValid()) {
        $stagiaire = $form->getData();

        // notif
        $this->addFlash(
          'success',
          'Informations de "' . $stagiaire . '" mises à jour'
        );

        // prepare() and execute()
        $entityManager->persist($stagiaire);
        $entityManager->flush();

        return $this->redirectToRoute('show_stagiaire', ['id' => $stagiaire->getId()]);
      }

      return $this->render('stagiaire/show.html.twig', [
        'stagiaire' => $stagiaire,
        'formAddStagiaire' => $form,
      ]);
    } else {
      return $this->redirectToRoute("app_stagiaire");
    }
  }

  ////////////////////// suppression stagiaire //////////////////////
  #[Route('/stagiaire/{id}/delete', name: 'delete_stagiaire')]
  public function delete(Stagiaire $stagiaire = null, EntityManagerInterface $entityManager): Response
  {
    if ($stagiaire) {

      // notif
      $this->addFlash(
        'warning',
        'Vous avez supprimé "' . $stagiaire . '"'
      );

      $entityManager->remove($stagiaire);
      $entityManager->flush();
    }
    // redirection auto si {id non existant}
    return $this->redirectToRoute('app_stagiaire');
  }

  //////////////// suppression stagiaire d'une session ////////////////
  #[Route('/stagiaire/{idStagiaire}/remove_session/{idSession}', name: 'remove_stagiaire_session')]
  public function removeStagiaire(int $idStagiaire, int $idSession, StagiaireRepository $stagiaireRepository, SessionRepository $sessionRepository, EntityManagerInterface $entityManager): Response
  {
    $stagiaire = $stagiaireRepository->find($idStagiaire);
    $session = $sessionRepository->find($idSession);

    // notif
    $this->addFlash(
      'warning',
      '"' . $stagiaire . '" ne participe plus à "' . $session->getIntitule() . '"'
    );

    $stagiaire->removeSession($session);
    $entityManager->flush();

    return $this->redirectToRoute('show_stagiaire', ['id' => $idStagiaire]);
  }
}