<?php

namespace App\Controller;

use App\Entity\Formateur;
use App\Entity\Session;
use App\Form\FormateurType;
use App\Repository\FormateurRepository;
use App\Repository\SessionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class FormateurController extends AbstractController
{
  #[Route('/formateur', name: 'app_formateur')]
  public function index(Request $request, EntityManagerInterface $entityManager, FormateurRepository $formateurRepository): Response
  {

    // création du formulaire de création de formateur pour le modal
    $formateur = new Formateur();
    $form = $this->createForm(FormateurType::class, $formateur);

    $form->handleRequest($request);
    if ($form->isSubmitted() && $form->isValid()) {
      $formateur = $form->getData();

      // prepare() and execute()
      $entityManager->persist($formateur);
      $entityManager->flush();

      return $this->redirectToRoute('app_formateur');
    }

    $formateurs = $formateurRepository->findBy([], ["nom" => "ASC"]);

    return $this->render('formateur/index.html.twig', [
      'activePage' => 'formateurs',
      'formateurs' => $formateurs,
      'formAddFormateur' => $form,
    ]);
  }

  #[Route('/formateur/{id}', name: 'show_formateur')]
  public function show(Formateur $formateur): Response
  {
    return $this->render('formateur/show.html.twig', [
      'formateur' => $formateur,
    ]);
  }

  #[Route('/formateur/{id}/delete', name: 'delete_formateur')]
  public function delete(Formateur $formateur, EntityManagerInterface $entityManager): Response
  {
    $entityManager->remove($formateur);
    $entityManager->flush();

    return $this->redirectToRoute('app_formateur');
  }

  #[Route('/formateur/{idFormateur}/remove_session/{idSession}', name: 'delete_formateur_formation')]
  public function removeFormateur(int $idFormateur, int $idSession, SessionRepository $sessionRepository, EntityManagerInterface $entityManager): Response
  {
    $session = $sessionRepository->find($idSession);

    $session->setFormateur(null);
    $entityManager->flush();

    return $this->redirectToRoute('show_formateur', ['id' => $idFormateur]);
  }
}