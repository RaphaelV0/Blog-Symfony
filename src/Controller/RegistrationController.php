<?php
namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Contracts\Translation\TranslatorInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class RegistrationController extends AbstractController
{
    private $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    #[Route('/register', name: 'app_register')]
    public function register(Request $request, TranslatorInterface $translator, EntityManagerInterface $em, UserRepository $userRepository): Response
    {
        $form = $this->createFormBuilder(null)
            ->add('username', TextType::class, [
                'label' => $translator->trans('Username (Email)', [], 'messages'),
                'constraints' => [
                    new Assert\NotBlank(['message' => $translator->trans('This field cannot be blank', [], 'validators')]),
                    new Assert\Email(['message' => $translator->trans('Please enter a valid email address.', [], 'validators')]),
                ],
            ])
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'first_options' => ['label' => $translator->trans('Password', [], 'messages')],
                'second_options' => ['label' => $translator->trans('Confirm Password', [], 'messages')],
                'invalid_message' => $translator->trans('Passwords must match.', [], 'validators'),
                'constraints' => [
                    new Assert\NotBlank(['message' => $translator->trans('This field cannot be blank', [], 'validators')]),
                    new Assert\Length([
                        'min' => 8,
                        'minMessage' => $translator->trans('Your password must be at least {{ limit }} characters long.', [], 'validators'),
                        'max' => 4096,
                    ]),
                    new Assert\Regex([
                        'pattern' => '/[A-Z]/',
                        'message' => $translator->trans('Your password must contain at least one uppercase letter.', [], 'validators'),
                    ]),
                    new Assert\Regex([
                        'pattern' => '/\d/',
                        'message' => $translator->trans('Your password must contain at least one number.', [], 'validators'),
                    ]),
                    new Assert\Regex([
                        'pattern' => '/[\W_]/', // Caractères spéciaux
                        'message' => $translator->trans('Your password must contain at least one special character.', [], 'validators'),
                    ]),
                ],
            ])
            ->add('submit', SubmitType::class, [
                'label' => $translator->trans('Create Account', [], 'messages')
            ])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            // Vérifier si l'email existe déjà dans la base de données
            $existingUser = $userRepository->findOneBy(['username' => $data['username']]);
            if ($existingUser) {
                $this->addFlash('error', $translator->trans('This email is already in use.', [], 'messages'));
                return $this->redirectToRoute('app_register');
            }

            // Hash le mot de passe
            $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);

            // Créer un nouvel utilisateur
            $user = new User();
            $user->setUsername($data['username']);
            $user->setPassword($hashedPassword);
            $user->setRoles(['ROLE_USER']);  // Ajoute le rôle par défaut ici

            // Persister l'utilisateur dans la base de données
            $em->persist($user);
            $em->flush();

            // Rediriger vers la page de connexion
            return $this->redirectToRoute('app_login');
        }

        return $this->render('registration/register.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/login', name: 'app_login')]
    public function login(Request $request, UserRepository $userRepository, TranslatorInterface $translator): Response
    {
        // Formulaire de connexion simple
        $form = $this->createFormBuilder(null)
            ->add('username', TextType::class, ['label' => $translator->trans('Username', [], 'messages')])
            ->add('password', PasswordType::class, ['label' => $translator->trans('Password', [], 'messages')])
            ->add('submit', SubmitType::class, ['label' => $translator->trans('Log In', [], 'messages')])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            // Rechercher l'utilisateur avec le username (email)
            $user = $userRepository->findOneBy(['username' => $data['username']]);

            if ($user && password_verify($data['password'], $user->getPassword())) {
                // Connecter l'utilisateur (enregistrer en session)
                $this->requestStack->getSession()->set('is_logged_in', true);
                $this->requestStack->getSession()->set('username', $data['username']);
    
                return $this->redirectToRoute('home');
            }

            // Afficher un message d'erreur si le login est invalide
            $this->addFlash('error', $translator->trans('Invalid username or password.', [], 'messages'));
        }

        return $this->render('registration/login.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout(): Response
    {
        // Déconnecter l'utilisateur en supprimant la session
        $this->requestStack->getSession()->clear();
        return $this->redirectToRoute('home');
    }
}
