<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Security\Http\Authentication\AuthenticatorManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(Request $request, TranslatorInterface $translator, EntityManagerInterface $em): Response
    {
        $form = $this->createFormBuilder(null)
            ->add('email', EmailType::class, [
                'label' => $translator->trans('E-mail (Login)', [], 'messages'),
                'constraints' => [
                    new Assert\NotBlank(['message' => $translator->trans('This field cannot be blank', [], 'validators')]),
                    new Assert\Email(['message' => $translator->trans('This is not a valid email address', [], 'validators')]),
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
                        'max' => 20,
                        'minMessage' => $translator->trans('Your password must be at least {{ limit }} characters long.', [], 'validators'),
                        'maxMessage' => $translator->trans('Your password cannot be longer than {{ limit }} characters.', [], 'validators'),
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

            // Hash the password for storage
            $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);

            // Create a new User object and save it to the database
            $user = new User();
            $user->setEmail($data['email']);
            $user->setPassword($hashedPassword);

            // Persist the user in the database
            $em->persist($user);
            $em->flush();

            // Redirect to the success page with email parameter
            return $this->redirectToRoute('app_register_success', ['email' => $data['email']]);
        }

        return $this->render('registration/register.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/register/success/{email}', name: 'app_register_success')]
    public function success(string $email, TranslatorInterface $translator): Response
    {
        return new Response(sprintf($translator->trans('Thank you %s for registering', [], 'messages'), $email));
    }


    #[Route('/', name: 'app_home')]
    public function home(SessionInterface $session, TranslatorInterface $translator): Response
    {
        return $this->render('home/index.html.twig', [
            'is_logged_in' => $session->get('is_logged_in', false),
            'login' => $session->get('login', null),  // Correctly retrieving the login variable (username or email)
        ]);
    }
}

