<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Security\AppAuthenticator;
use App\Security\EmailVerifier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;

class RegistrationController extends AbstractController
{
    public function __construct(private EmailVerifier $emailVerifier)
    {
    }

    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, Security $security, EntityManagerInterface $entityManager): Response
    {     
        if ($this->getUser()) {
            return $this->redirectToRoute('app_admin');
        }
        
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            // Check for form errors
            if (!$form->isValid()) {
                foreach ($form->getErrors(true) as $error) {
                    $this->addFlash('error', $error->getMessage());
                }
                return $this->render('registration/register.html.twig', [
                    'registrationForm' => $form,
                ]);
            }

            // Validation: Check if agreeTerms was checked
            if (!$form->get('agreeTerms')->getData()) {
                $this->addFlash('error', 'You must agree to the terms and conditions.');
                return $this->render('registration/register.html.twig', [
                    'registrationForm' => $form,
                ]);
            }

            /** @var string $plainPassword */
            $plainPassword = $form->get('plainPassword')->getData();

            // Validation: Check password strength
            if (empty($plainPassword)) {
                $this->addFlash('error', 'Please enter a password');
                return $this->render('registration/register.html.twig', [
                    'registrationForm' => $form,
                ]);
            }

            if (strlen($plainPassword) < 6) {
                $this->addFlash('error', 'Your password should be at least 6 characters');
                return $this->render('registration/register.html.twig', [
                    'registrationForm' => $form,
                ]);
            }

            try {
                // Encode the password
                $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));
                
                // Set default role - new users are Agricultor by default
                $user->setRoles(['ROLE_AGRICULTOR']);
                
                // Set account status
                $user->setStatutCompte('PENDING_VERIFICATION');
                
                // Set creation date
                $user->setDateCreation(new \DateTime());

                $entityManager->persist($user);
                $entityManager->flush();

                // Generate a signed url and email it to the user
                $this->emailVerifier->sendEmailConfirmation('app_verify_email', $user,
                    (new TemplatedEmail())
                        ->from(new Address('af182678@gmail.com', 'Smart Greenhouse'))
                        ->to((string) $user->getEmail())
                        ->subject('Please Confirm your Email')
                        ->htmlTemplate('registration/confirmation_email.html.twig')
                );

                $this->addFlash('success', 'Account created! Please verify your email to complete registration.');

                return $security->login($user, AppAuthenticator::class, 'main');
            } catch (UniqueConstraintViolationException $e) {
                $this->addFlash('error', 'This email address is already registered. Please use a different email or try logging in.');
                return $this->render('registration/register.html.twig', [
                    'registrationForm' => $form,
                ]);
            } catch (\Exception $e) {
                $this->addFlash('error', 'An error occurred during registration: ' . $e->getMessage());
                return $this->render('registration/register.html.twig', [
                    'registrationForm' => $form,
                ]);
            }
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form,
        ]);
    }

    #[Route('/verify/email', name: 'app_verify_email')]
    public function verifyUserEmail(Request $request, TranslatorInterface $translator): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        // validate email confirmation link, sets User::isVerified=true and persists
        try {
            /** @var User $user */
            $user = $this->getUser();
            $this->emailVerifier->handleEmailConfirmation($request, $user);
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('verify_email_error', $translator->trans($exception->getReason(), [], 'VerifyEmailBundle'));

            return $this->redirectToRoute('app_register');
        }

        // @TODO Change the redirect on success and handle or remove the flash message in your templates
        $this->addFlash('success', 'Your email address has been verified.');

        return $this->redirectToRoute('app_admin');
    }
}
