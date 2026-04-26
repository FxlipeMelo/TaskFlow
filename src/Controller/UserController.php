<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserProfileFormType;
use App\Form\UserSecurityFormType;
use App\Repository\UserRepository;
use App\Security\EmailVerifier;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

final class UserController extends AbstractController
{

    public function __construct(private UserRepository $userRepository, private UserPasswordHasherInterface $userPasswordHasher, private EmailVerifier $emailVerifier)
    {
    }

    #[Route('/user', name: 'app_user')]
    public function index(): Response
    {
        $userList = $this->userRepository->findAll();

        $user = $this->getUser();

        $formProfile = $this->createForm(UserProfileFormType::class, $user, ['method' => 'PATCH']);
        $formSecurity = $this->createForm(UserSecurityFormType::class, $user, ['method' => 'PATCH']);

        return $this->render('user/index.html.twig', compact('userList', 'formProfile', 'formSecurity'));
    }

    #[Route('/user/profile/edit/{user}', name: 'app_user_profile_edit', methods: ['PATCH'])]
    public function profileEdit(Request $request, User $user): Response
    {
        if ($user !== $this->getUser()) {
            throw $this->createAccessDeniedException('Access Denied. You cannot edit other users.');
        }
        $formProfile = $this->createForm(UserProfileFormType::class, $user, ['method' => 'PATCH'])->handleRequest($request);

        if ($formProfile->isSubmitted() && $formProfile->isValid()) {
            $this->userRepository->update($user, true);
            $this->addFlash('success', 'Profile updated successfully!');

            return $this->redirectToRoute('app_user');
        }

        $userList = $this->userRepository->findAll();
        $formSecurity = $this->createForm(UserSecurityFormType::class, $user, ['method' => 'PATCH']);
        $openModal = 'editProfileModal-' . $user->getId();

        return $this->render('user/index.html.twig', compact('formProfile', 'userList', 'formSecurity', 'openModal'))
            ->setStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    #[Route('/user/security/edit/{user}', name: 'app_user_security_edit', methods: ['PATCH'])]
    public function securityEdit(Request $request, User $user): Response
    {
        if ($user !== $this->getUser()) {
            throw $this->createAccessDeniedException('Access Denied. You cannot edit other users.');
        }

        $emailUser = $user->getEmail();

        $formSecurity = $this->createForm(UserSecurityFormType::class, $user, ['method' => 'PATCH'])->handleRequest($request);

        if ($formSecurity->isSubmitted() && $formSecurity->isValid()) {
            if ($formSecurity->get('newPassword')->getData() !== null ) {
                $password = $formSecurity->get('newPassword')->getData();
                $user->setPassword($this->userPasswordHasher->hashPassword($user, $password));
            }

            if ($emailUser !== $user->getEmail()) {

                $user->setIsVerified(false);

                $this->emailVerifier->sendEmailConfirmation('app_verify_email', $user,
                    new TemplatedEmail()
                        ->from(new Address('mailer@teste.com', 'Mailer Confirmations'))
                        ->to((string) $user->getEmail())
                        ->subject('Please Confirm your Email')
                        ->htmlTemplate('registration/confirmation_email.html.twig')
                );

                $this->addFlash('warning', 'Important: We sent a confirmation link to your new email address. Please check your inbox.');
            }

            $this->userRepository->update($user, true);
            $this->addFlash('success', 'Security updated successfully!');

            return $this->redirectToRoute('app_user');
        }

        $userList = $this->userRepository->findAll();
        $formProfile = $this->createForm(UserProfileFormType::class, $user, ['method' => 'PATCH']);
        $openModal = 'securityModal-' . $user->getId();

        return $this->render('user/index.html.twig', compact('formSecurity', 'userList', 'formProfile', 'openModal'))
            ->setStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);
    }
}
