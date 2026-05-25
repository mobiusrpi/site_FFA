<?php

namespace App\Controller;

use App\Entity\Enum\CRAList;
use App\Entity\Users;
use App\Form\EditProfilType;
use App\Form\RegistrationForm;
use App\Repository\UsersRepository;
use App\Security\EmailVerifier;
use App\Service\JWTService;
use App\Service\SendMailService;
use App\Service\SmileService;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Security;


class RegistrationController extends AbstractController
{
    public function __construct(
        private EmailVerifier $emailVerifier,
        private SmileService $smileService,
        private LoggerInterface $logger
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

        $licenseValid = !$user->isCompetitor();

        if ($form->isSubmitted() && $user->isCompetitor()) {
            $license = $form->get('licenseFfa')->getData(); 
            $birthdate = $form->get('birthdate')->getData(); 
            $lastname =  $form->get('lastname')->getData();
            if ($license !== null && $birthdate !== null) {

                $dataSmile = $this->smileService->verifyLicense($license, $birthdate);
                if (!$dataSmile['isValid']) {
                    $form->addError(new FormError(
                        $dataSmile['error'] ?? 'Erreur de validation Smile'
                    ));
                } else {    
                    if (
                        isset($dataSmile['nom']) &&
                        mb_strtoupper($dataSmile['nom']) !== mb_strtoupper($lastname)
                    ) {
                        $form->addError(
                            new FormError("Votre nom ne correspond pas à celui associé à votre numéro de licence")
                        );
                    } else {
                        $user->setEndValidity($dataSmile['endingDate']);                    
                        $user->setFlyingclub($dataSmile['nom_aeroclub']);  
                        
                        $codeFna = $dataSmile['code_fna'] ?? null;
                        if ($codeFna !== null) {
                            $user->setIdClub($codeFna);
                        }

                        // CRA enum
                        if ($dataSmile['committee'] instanceof CRAList) {
                            $user->setCommittee($dataSmile['committee']);
                        }

                        $licenseValid = true;
                    }
                }
            } else {
                $form->addError(new FormError('Licence ou date de naissance manquante.'));
            }
        }

        // On persiste seulement si la licence est valide
        if ($form->isSubmitted() && $form->isValid() && $licenseValid) {
            $plainPassword = $form->get('plainPassword')->getData();
            $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));

            $entityManager->persist($user);
            $entityManager->flush();
            
            $now = time();
            $token = $jwt->generate(
                ['type' => 'JWT', 'alg' => 'HS256'],
                [
                    'user_id' => $user->getId(),
                    'iat' => $now,
                    'exp' => $now + 7200,                
                ],
                $this->getParameter('app.jwtsecret')
            );

            $mail->sendEmail(
                $user->getEmail(),
               'Activation de votre compte sur le site sport-ffa-aero',
                'register',
                compact('user','token')
            );
            $this->addFlash('success', 'Votre compte a été créé avec succès. Vérifiez votre email pour activer votre compte.');

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
        Security $security ,
        JWTService $jwt
    ): Response {
       
        /** @var Users|null $user */
        $user = $security->getUser();
        $user->setUpdatedAt( new \DateTimeImmutable());  
        $form = $this->createForm(EditProfilType::class, $user);
        $form->handleRequest($request);
        $licenseValid = true;
        if ($form->isSubmitted()) {            
            $license = $form->get('licenseFfa')->getData(); 
            $birthdate = $form->get('birthdate')->getData(); 
            $user = $form->getData();    
            $lastname = $user->getLastName();        
            $formattedDate = $birthdate?->format('d/m/Y');

            if ($license !== null && $birthdate !== null){
            // Check if SmileService validates the user
                $dataSmile = $this->smileService->verifyLicense($license, $birthdate);
                if (!$dataSmile['isValid']) {
                    $form->addError(new FormError(
                        $dataSmile['error'] ?? 'Erreur de validation Smile'
                    ));
                } else {    
                    if (
                        isset($dataSmile['nom']) &&
                        mb_strtoupper($dataSmile['nom']) !== mb_strtoupper($lastname)
                    ) {
                        $form->addError(
                            new FormError('Votre nom ne correspond pas à celui associé à votre numéro de licence')
                        );
                    } else {
                        if (!empty($dataSmile['code_fna'])) {
                            $user->setIdClub($dataSmile['code_fna']);
                        }

                        if (!empty($dataSmile['nom_aeroclub'])) {
                            $user->setFlyingclub($dataSmile['nom_aeroclub']);
                        }

                        if ($dataSmile['committee'] instanceof CRAList) {
                            $user->setCommittee($dataSmile['committee']);
                        }

                        $user->setEndValidity($dataSmile['endingDate']);
      
                        $this->logger->info('License updated', [
                            'License :' => $license,
                            'Birthdate :'=>  $formattedDate,
                            'endValidity' => $dataSmile['endingDate']?->format('Y-m-d'),
                        ]);
                    }
                }   
            } else {
                $this->logger->error('Licence number or Birthdate missing', [
                    'License :' => $license,
                    'Birthdate'=>  $formattedDate,
                ]);
            }    
        }  
        if ($form->isSubmitted() && $form->isValid()) {        
            $license = $form->get('licenseFfa')->getData(); 

            /** @var string $plainPassword */
            $plainPassword = $form->get('plainPassword')->getData();
            $isCompetitorChecked = $form->get('isCompetitor')->getData();            
            // encode the plain password
            if ($plainPassword) {
                $user->setPassword(
                    $userPasswordHasher->hashPassword($user, $plainPassword)
                );
            }
            if (!$isCompetitorChecked){
                $user->setLicenseFfa(null);
                $user->setBirthdate(null);                
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
            $user->getEmail(),
            'Validation de votre adresse email – Sports FF-Aéro',
            'register',
            compact('user','token')
        );

        $this->addFlash('success','Email de vérification envoyé');

        return $this->redirectToRoute('login');
    }
}  
