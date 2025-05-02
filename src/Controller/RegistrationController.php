<?php

namespace App\Controller;

use App\Entity\Users;
use App\Service\JWTService;
use App\Form\RegistrationForm;
use App\Security\EmailVerifier;
use App\Service\SendMailService;
use App\Repository\UsersRepository;
use Symfony\Component\Mime\Address;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

class RegistrationController extends AbstractController
{
    public function __construct(
        private EmailVerifier $emailVerifier){}

    #[Route('/register', name: 'app_register')]
    public function register(
        Request $request, 
        UserPasswordHasherInterface $userPasswordHasher, 
        EntityManagerInterface $entityManager,
        SendMailService $mail,
        JWTService $jwt
    ): Response {

        $user = new Users();
        $user->setCreatedAt( new \DateTimeImmutable());
        $user->setUpdatedAt( new \DateTimeImmutable());  
        $form = $this->createForm(RegistrationForm::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var string $plainPassword */
            $plainPassword = $form->get('plainPassword')->getData();

            // encode the plain password
            $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));

            $entityManager->persist($user);
            $entityManager->flush();

            $header = [
                'type' => 'JWT',
                'alg' => 'HS256',
            ];

            $payload = [
                'user_id' => $user->getId()
            ];

            $token = $jwt->generate($header, $payload, $this->getParameter('app.jwtsecret'));

            // do anything else you need here, like send an email
            $mail->send(
                'no-reply@monsite.net',
                $user->getEmail(),'activation de votre compte sur le site sport-ffa-aero',
                'register',
                compact('user','token')
            );

            return $this->redirectToRoute('home');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form,
        ]);
    }

    #[Route('/verify/{token}', name: 'verify_user')]
    public function verifyUser(
        $token,
        JWTService $jwt,
        UsersRepository $usersRepository,
        EntityManagerInterface $em
    ): Response{
        if ($jwt->isValid($token) && !$jwt->isExpired($token) &&
        $jwt->check($token, $this->getParameter('app.jwtsecret')))
        { 
            $payload = $jwt->getPayload($token);
            $user =$usersRepository->find($payload['user_id']);

            if ($user && !$user->isVerified())
            {  
                $user->setIsVerified(true);
                $em->flush($user);

                $this->addFlash('succes','Cet utilisateur a été validé ');

                return $this->redirectToRoute(('home'));
            } 
            if ($user){
                $this->addFlash('succes','Cet utilisateur est inconnu');
            }
            else{
                $this->addFlash('danger','Utilisateur déjà vérifié !');
            }       
            
            return $this->redirectToRoute(('app_login'));   
        }

        $this->addFlash('danger','Le token est invalide ou il a expiré');

        return $this->redirectToRoute(('app_login'));
    }

    #[Route('/resendVerif', name: 'resend_verif')]
    public function resendVerif( 
        JWTService $jwt,        
        Request $request, 
        SendMailService $mail,
        UsersRepository $usersRepository
    ): Response{

        $user = $this->getUser();

        if (!$user){

            $this->addFlash('danger','Vous devez être connecté pour accéder à cette page');
            
            return $this->redirectToRoute('app_login');
        }

        if ($user->isVerified()){

            $this->addFlash('warning','Cet utilisateur est déjà activé');
            
            return $this->redirectToRoute('app_login');
        }

        $header = [
            'type' => 'JWT',
            'alg' => 'HS256',
        ];

        $payload = [
            'user_id' => $user->getId()
        ];

        $token = $jwt->generate($header, $payload, $this->getParameter('app.jwtsecret'));

        // do anything else you need here, like send an email
        $mail->send(
            'no-reply@monsite.net',
            $user->getEmail(),'activation de votre compte sur le site sport-ffa-aero',
            'register',
            compact('user','token')
        );

        $this->addFlash('sucess','Email de vérification envoyé');

        return $this->redirectToRoute('app_login');
    }
}
