<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
  #[Route(path: '/login', name: 'app_login')]
  public function login(AuthenticationUtils $authenticationUtils): Response
  {
    // redirection si déjà connecté
    if ($this->getUser()) {
      return $this->redirectToRoute('app_profile');
    }

    // get the login error if there is one
    $error = $authenticationUtils->getLastAuthenticationError();
    // last username entered by the user
    $lastUsername = $authenticationUtils->getLastUsername();

    return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
  }

  #[Route(path: '/logout', name: 'app_logout')]
  public function logout(): void
  {
    throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
  }

  #[Route(path: '/profile', name: 'app_profile')]
  public function profile(): Response
  {
    return $this->render('security/profile.html.twig', [
    ]);
  }

  #[Route(path: '/profile/delete', name: 'app_delete_account')]
  public function deleteAccount(EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage): Response
  {
    $user = $this->getUser();

    if ($user) {

      // suppression de l'user en db
      $entityManager->remove($user);
      $entityManager->flush();

      // on supprime l'user connecté
      $tokenStorage->setToken(null);

      $this->addFlash(
        'warning',
        'Votre compte a été supprimé avec succès'
      );
      return $this->redirectToRoute('app_logout');
    } else {
      $this->addFlash(
        'warning',
        'Une erreur est survenue.'
      );
      return $this->redirectToRoute('app_profile');
    }
  }
}
