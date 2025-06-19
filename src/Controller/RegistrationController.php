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
use Symfony\Component\Form\FormError;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;


class RegistrationController extends AbstractController
{
    public function __construct(
        private EmailVerifier $emailVerifier,
        private SmileService $smileService
    ) {}

/**
 * New user registration function
 *
 * @param Request $request
 * @param UserPasswordHasherInterface $userPasswordHasher
 * @param EntityManagerInterface $entityManager
 * @param SendMailService $mail
 * @param JWTService $jwt
 * @return Response
 */    
    #[Route('/register', name: 'new_user_registration')]    
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

        if ($form->isSubmitted()) {
            $license = $form->get('licenseFfa')->getData(); 
            $birthdate = $form->get('dateBirth')->getData(); 

            if (!$license === null || !$birthdate === null){
            // Check if SmileService validates the user
                $dataSmile = $this->smileService->verifyLicense($license, $birthdate);

                if (isset($dataSmile['error'])) {
                    $form->addError(new FormError('La licence ne corespond pas à celle enregistrée dans Smile'));
                } elseif (!$dataSmile['isValid']) {
                    $form->addError(new FormError('Licence invalide ou expirée : fin le ' . $dataSmile['endingDate']));
                } else {
                    $user = $form->getData();
                    $dateValidity = \DateTimeImmutable::createFromFormat('Y-m-d', $dataSmile['endingDate']);
                    $user->setEndValidity($dateValidity);
                }
            }      
        }
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
    

/**
 * Edit user's profil function
 *
 * @param Request $request
 * @param UserPasswordHasherInterface $userPasswordHasher
 * @param EntityManagerInterface $entityManager
 * @param SendMailService $mail
 * @param Security $security
 * @param JWTService $jwt
 * @return Response
 */    
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

        if ($form->isSubmitted()) {            
            $license = $form->get('licenseFfa')->getData(); 
            $birthdate = $form->get('dateBirth')->getData(); 

            if (!$license === null || !$birthdate === null){
            // Check if SmileService validates the user
                $dataSmile = $this->smileService->verifyLicense($license, $birthdate);

            if (isset($dataSmile['error'])) {
                $form->addError(new FormError('La licence ne corespond pas à celle enregistrée dans Smile'));
            } elseif (!$dataSmile['isValid'] ) {
                if ($dataSmile['isExist'] ) {
                    $form->addError(new FormError('Votre licence est expirée : ' . $dataSmile['endingDate']));
                }else{
                    $form->addError(new FormError('La licence est invalide'));
                }
            } else {
                $user = $form->getData();
                $dateValidity = \DateTimeImmutable::createFromFormat('Y-m-d', $dataSmile['endingDate']);
                $user->setEndValidity($dateValidity);
                } 
            }     
        }
        if ($form->isSubmitted() && $form->isValid()) {        
            $license = $form->get('licenseFfa')->getData(); 

            /** @var string $plainPassword */
            $plainPassword = $form->get('plainPassword')->getData();
            $isCompetitorChecked = $form->get('isCompetitor');
            // encode the plain password
            $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));
            if (!$isCompetitorChecked){
                $user->setLicenseFfa(null);
                $user->setBirthDate(null);                
                $user->setFlyingclub(null);               
                $user->setPhone(null);                   
                $user->setCommittee(null);                    
                $user->setGender(null);
                $user->setPoloSize(null);        
            }
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('home');
        }

        return $this->render('registration/edit.profil.html.twig', [
            'profilForm' => $form,
            'user' => $user,
        ]);
    }


/**
 * Verification is the email come from autorized user function
 *
 * @param [type] $token
 * @param JWTService $jwt
 * @param UsersRepository $usersRepository
 * @param EntityManagerInterface $em
 * @return Response
 */
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


/**
 * Resend email confirmation function
 *
 * @param JWTService $jwt
 * @param Request $request
 * @param SendMailService $mail
 * @param UsersRepository $usersRepository
 * @return Response
 */
    #[Route('/resendVerif', name: 'resend_verif')]    
    public function resendVerif( 
        JWTService $jwt,        
        Request $request, 
        SendMailService $mail,
        UsersRepository $usersRepository
    ): Response{

        /** @var Users|null $user */
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
