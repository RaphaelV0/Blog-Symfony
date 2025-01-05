<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class SecurityController extends AbstractController
{
    #[Route('/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // Récupérer l'erreur de connexion, s'il y en a une
        $error = $authenticationUtils->getLastAuthenticationError();

        // Récupérer le dernier nom d'utilisateur saisi
        $lastUsername = $authenticationUtils->getLastUsername();

        // Créer le formulaire de connexion manuellement
        $form = $this->createFormBuilder()
            ->add('username', TextType::class, [
                'label' => 'Nom d\'utilisateur',
                'attr' => ['class' => 'form-control'],
                'data' => $lastUsername, // Assurer que le dernier nom d'utilisateur est pré-rempli
            ])
            ->add('password', PasswordType::class, [
                'label' => 'Mot de passe',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Connexion',
                'attr' => ['class' => 'btn btn-primary'],
            ])
            ->getForm();

        // Si le formulaire est soumis et valide
        if ($form->isSubmitted() && $form->isValid()) {
            // Symfony va gérer l'authentification via les informations fournies dans le formulaire
            // L'utilisateur sera redirigé automatiquement après la connexion, en fonction de la configuration de `security.yaml`
            return $this->redirectToRoute('app_home'); // Rediriger vers la page d'accueil après la connexion
        }

        // Affichage de la vue avec le formulaire, l'erreur (s'il y en a) et le dernier nom d'utilisateur
        return $this->render('registration/login.html.twig', [
            'form' => $form->createView(),
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout(): void
    {
        // Cette méthode ne sera jamais appelée, Symfony gère la déconnexion automatiquement.
        throw new \Exception('Don\'t forget to activate logout in security.yaml');
    }
}
