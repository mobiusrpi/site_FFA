<?php

namespace App\Controller;

use App\Entity\Users;
use DateTimeImmutable;
use App\Service\JWTService;
use App\Form\EditProfilType;
use App\Service\SmileService;
use App\Form\RegistrationForm;
use App\Security\EmailVerifier;
use App\Service\SendMailService;
use App\Repository\UsersRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;


class RegistrationController extends AbstractController
{
    public function __construct(
        private EmailVerifier $emailVerifier){}

    #[Route('/verify-smile/{license}', name: 'verify_identity')]
    public function verifyLicense(string $license, SmileService $smileService): Response
    {
        $result = $smileService->verifyLicense($license);
dd($result);
        return $this->json($result);
    }

    #[Route('/register', name: 'new_register')]
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
        $defaultDateBirth = new DateTimeImmutable('1970-01-01');
        $user->setDateBirth($defaultDateBirth);  
        $form = $this->createForm(RegistrationForm::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var string $plainPassword */
            $plainPassword = $form->get('plainPassword')->getData();
            $competitorChecked = $form->get('isCompetitor');
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
    
    #[Route('/profil/edit', name: 'edit_profil')]
    public function editRegister(
        Request $request, 
        UserPasswordHasherInterface $userPasswordHasher, 
        EntityManagerInterface $entityManager,
        SendMailService $mail,      
        Security $security  ,
        JWTService $jwt
    ): Response {
       
        /** @var Users|null $user */
        $user = $security->getUser();
        $user->setUpdatedAt( new \DateTimeImmutable());  
        $form = $this->createForm(EditProfilType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var string $plainPassword */
            $plainPassword = $form->get('plainPassword')->getData();
            $competitorChecked = $form->get('isCompetitor');

            // encode the plain password
            $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));

            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('home');
        }

        return $this->render('registration/edit.profil.html.twig', [
            'profilForm' => $form,
        ]);
    }

    #[Route('/verify/{token}', name: 'verify_user')]
    public function verifyUser(
        $token,
        JWTService $jwt,
        UsersRepository $usersRepository,
        EntityManagerInterface $em
    ): Response{
   if ($jwt->isValid($token) && !$jwt->isExpired($token) && $jwt->check($token, $this->getParameter('app.jwtsecret'))) {
        $payload = $jwt->getPayload($token);
        $user = $usersRepository->find($payload['user_id']);

        if ($user && !$user->isVerified())
        {  
            $user->setIsVerified(true);
            if (empty($user->getRoles())) {
                $user->setRoles(['ROLE_USER']);                 
            }

            $em->flush($user);

            $this->addFlash('success','Cet utilisateur a été validé ');

            return $this->redirectToRoute(('home'));
        } 
        if ($user){
            $this->addFlash('success','Cet utilisateur est inconnu');
        }
        else{
            $this->addFlash('danger','Utilisateur déjà vérifié !');
        }       
        
            return $this->redirectToRoute(('login'));   
        }

        $this->addFlash('danger','Le token est invalide ou il a expiré');

        return $this->redirectToRoute(('login'));
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
            
            return $this->redirectToRoute('login');
        }

        if ($user->isVerified()){

            $this->addFlash('warning','Cet utilisateur est déjà activé');
            
            return $this->redirectToRoute('login');
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

        $this->addFlash('success','Email de vérification envoyé');

        return $this->redirectToRoute('login');
    }
}  
