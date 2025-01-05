<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
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
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(Request $request, TranslatorInterface $translator, EntityManagerInterface $em, UserRepository $userRepository, UserPasswordHasherInterface $passwordHasher): Response
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
                        'pattern' => '/[\W_]/',
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
            $hashedPassword = $passwordHasher->hashPassword(new User(), $data['password']);

            // Créer un nouvel utilisateur
            $user = new User();
            $user->setUsername($data['username']);
            $user->setPassword($hashedPassword);
            $user->setRoles(['ROLE_USER']);  // Rôle par défaut

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
}
