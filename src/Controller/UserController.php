<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserProfileFormType;
use App\Form\UserRoleFormType;
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
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class UserController extends AbstractController
{

    public function __construct(private UserRepository $userRepository, private UserPasswordHasherInterface $userPasswordHasher, private EmailVerifier $emailVerifier)
    {
    }

    #[Route('/user', name: 'app_user')]
    public function index(): Response
    {
        $userList = $this->userRepository->findAll();

        $formProfile = [];
        $formSecurity = [];
        $formRole = [];

        foreach ($userList as $user) {
            $formProfile[$user->getId()] = $this->createForm(UserProfileFormType::class, $user, ['method' => 'PATCH'])->createView();
            $formSecurity[$user->getId()] = $this->createForm(UserSecurityFormType::class, $user, ['method' => 'PATCH'])->createView();
            $formRole[$user->getId()] = $this->createForm(UserRoleFormType::class, $user, ['method' => 'PATCH'])->createView();
        }

        return $this->render('user/index.html.twig', compact('userList', 'formProfile', 'formSecurity', 'formRole'));
    }

    #[Route('/user/profile/edit/{user}', name: 'app_user_profile_edit', methods: ['PATCH'])]
    #[IsGranted('edit', 'user')]
    public function profileEdit(Request $request, User $user): Response
    {
        $formProfile = $this->createForm(UserProfileFormType::class, $user, ['method' => 'PATCH'])->handleRequest($request);

        if ($formProfile->isSubmitted() && $formProfile->isValid()) {
            $this->userRepository->update($user, true);
            $this->addFlash('success', 'Profile updated successfully!');

            return $this->redirectToRoute('app_user');
        }

        $userList = $this->userRepository->findAll();

        $formProfileArray = [];
        $formSecurityArray = [];
        $formRoleArray = [];

        foreach ($userList as $u) {
            if ($u->getId() === $user->getId()) {
                $formProfileArray[$u->getId()] = $formProfile->createView();
            } else {
                $formProfileArray[$u->getId()] = $this->createForm(UserProfileFormType::class, $u, ['method' => 'PATCH'])->createView();
            }
            $formSecurityArray[$u->getId()] = $this->createForm(UserSecurityFormType::class, $u, ['method' => 'PATCH'])->createView();
            $formRoleArray[$u->getId()] = $this->createForm(UserRoleFormType::class, $u, ['method' => 'PATCH'])->createView();
        }

        $openModal = 'editProfileModal-' . $user->getId();



        return $this->render('user/index.html.twig', [
            'formProfile' => $formProfileArray,
            'formSecurity' => $formSecurityArray,
            'formRole' => $formRoleArray,
            'openModal' => $openModal,
            'userList' => $userList
        ])
            ->setStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    #[Route('/user/security/edit/{user}', name: 'app_user_security_edit', methods: ['PATCH'])]
    #[IsGranted('edit', 'user')]
    public function securityEdit(Request $request, User $user): Response
    {
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

        $formProfileArray = [];
        $formSecurityArray = [];
        $formRoleArray = [];

        foreach ($userList as $u) {
            if ($u->getId() === $user->getId()) {
                $formSecurityArray[$u->getId()] = $formSecurity->createView();
            } else {
                $formSecurityArray[$u->getId()] = $this->createForm(UserSecurityFormType::class, $u, ['method' => 'PATCH'])->createView();
            }
            $formProfileArray[$u->getId()] = $this->createForm(UserProfileFormType::class, $u, ['method' => 'PATCH'])->createView();
            $formRoleArray[$u->getId()] = $this->createForm(UserRoleFormType::class, $u, ['method' => 'PATCH'])->createView();
        }

        $openModal = 'securityModal-' . $user->getId();



        return $this->render('user/index.html.twig', [
            'formProfile' => $formProfileArray,
            'formSecurity' => $formSecurityArray,
            'formRole' => $formRoleArray,
            'openModal' => $openModal,
            'userList' => $userList,
        ])
            ->setStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    #[Route('/user/role/edit/{user}', name: 'app_user_role_edit', methods: ['PATCH'])]
    #[IsGranted('ROLE_ADMIN')]
    public function roleEdit(Request $request, User $user): Response
    {
        $userLastRole = $user->getRoles();
        $userLastWorkspace = $user->getWorkspace()->toArray();
        $formRole = $this->createForm(UserRoleFormType::class, $user, ['method' => 'PATCH'])->handleRequest($request);

        if ($formRole->isSubmitted()) {
            if ($formRole->isValid()) {
                if ($user === $this->getUser() && !in_array('ROLE_ADMIN', $user->getRoles())) {
                    $this->addFlash('danger', 'Action Denied: You cannot remove your own Admin privileges!');
                    return $this->redirectToRoute('app_user');
                }

                $hasChanges = false;

                if ($userLastRole !== $user->getRoles()) {
                    $this->addFlash('success', 'Role updated successfully!');
                    $hasChanges = true;
                }

                if ($userLastWorkspace !== $user->getWorkspace()->toArray()) {
                    $this->addFlash('success', 'Workspace updated successfully!');
                    $hasChanges = true;
                }

                if($hasChanges) {
                    $this->userRepository->update($user, true);
                }

                return $this->redirectToRoute('app_user');
            }

            foreach ($formRole->getErrors(true) as $error) {
                $this->addFlash('danger', $error->getMessage());
            }
        }

        $userList = $this->userRepository->findAll();

        $formProfileArray = [];
        $formSecurityArray = [];
        $formRoleArray = [];

        foreach ($userList as $u) {
            if ($u->getId() === $user->getId()) {
                $formRoleArray[$u->getId()] = $formRole->createView();
            } else {
                $formRoleArray[$u->getId()] = $this->createForm(UserRoleFormType::class, $u, ['method' => 'PATCH'])->createView();
            }
            $formProfileArray[$u->getId()] = $this->createForm(UserProfileFormType::class, $u, ['method' => 'PATCH'])->createView();
            $formSecurityArray[$u->getId()] = $this->createForm(UserSecurityFormType::class, $u, ['method' => 'PATCH'])->createView();
        }

        $openModal = 'userRoleModal-' . $user->getId();

        return $this->render('user/index.html.twig', [
            'formRole' => $formRoleArray,
            'formProfile' => $formProfileArray,
            'formSecurity' => $formSecurityArray,
            'userList' => $userList,
            'openModal' => $openModal,
        ])
            ->setStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);
    }
}
