<?php

namespace App\Command;


use DateTimeImmutable;
use App\Entity\users;
use App\Entity\Enum\CRAList;
use App\Entity\Enum\Category;
use App\Entity\Enum\Polosize;
use App\Entity\Enum\SpeedList;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\UsersRepository;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

#[AsCommand(
    name: 'Importusers',
    description: 'Impotation des compétiteurs du fichier .csv du Google Sheet de la FFA',
)]
class ImportUsersCommand extends Command
{
    private EntityManagerInterface $entityManager;

    private DateTimeImmutable $now;

    private string $dataDirectory;

    private SymfonyStyle $io;

    private usersRepository $userRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        string $dataDirectory,
        UsersRepository $userRepository
    )
    {
        parent::__construct();
        $this->entityManager =$entityManager;
        $this->dataDirectory = $dataDirectory;
        $this->userRepository = $userRepository;
        $date = new \DateTimeImmutable();
        $this->now = $date->setTimestamp(time());
    }

    protected function initialize(InputInterface $input, OutputInterface $output):void
    {
        $this->io = new SymfonyStyle($input,$output);

    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
//        $this->uploadPilots();
        $this->uploadNavigators();
         return Command::SUCCESS;
    }

    private function getDataFromFile(): array
    {
        try{
            $file = $this->dataDirectory . 'CDF_RA_2025.csv';
            $fileExtension = pathinfo($file,PATHINFO_EXTENSION);

            $normalizers = [new ObjectNormalizer];
            $encoders = [ 
            new CsvEncoder(),
            new JsonEncoder()
            ];
            $serializer = new Serializer($normalizers,$encoders);

            /** $var string $fileString */
            $fileString = file_get_contents($file);
        } finally {
            // Fermer le fichier
            if ($file !== null) {
                $file = null; // \SplFileObject est automatiquement fermé quand il est mis à null
            }
        }
        $data = $serializer->decode($fileString,$fileExtension);
        
        return $data;
    }

    private function uploadPilots(): void
    {
        $userCreated = 0;
    //    foreach($this->getdataFromFile() as $row){          
    //        if (array_key_exists('adresse_mail_pilote',$row) && !empty($row['adresse_mail_pilote'])) {
    //            $user = $this->userRepository->findOneBy([
    //                'email' => $row['adresse_mail_pilote'],
        foreach($this->getdataFromFile() as $row){          
                if (array_key_exists('adresse_mail_navigateur',$row) && !empty($row['adresse_mail_navigateur'])) {
                    $user = $this->userRepository->findOneBy([
                        'email' => $row['adresse_mail_navigateur'],
                ] );

                if  (!$user) {                          
                    $user = new Users;
                    $pos = strrpos($row['Taille_polo_pilote'],'X',0);
                    if (!empty($pos) and ($pos > 0)){
                        $polo = $pos + 1 . 'XL';
                    }
                    else{
                        $polo = $row['Taille_polo_pilote'];
                    };
                  
                    $birthdateArray = explode('/',$row['date_de_naissance_pilote']);
                    $newDate= new DateTimeImmutable();              
                    $birthdate = $newDate->setDate($birthdateArray[2],$birthdateArray[1],$birthdateArray[0]);


                    $user->setLastname($row['Nom pilote'])
                        ->setFirstname($row['Prénom_pilote'])
                        ->setPassword('abhbdcjdbcjdbcjdsbcjdcbq')
                        ->setEmail($row['adresse_mail_pilote'])
                        ->setPhone('0' . $row['Téléphone_portable_pilote'])
                        ->setLicenseFfa($row['N°_licence FFA_pilote'])
                        ->setDateBirth($birthdate)
                        ->setFlyingclub($row['Aéro-club_prise_de_licence_pilote'])
                        ->setCommittee(CRAList::from($row['CRA_Licence_pilote']))                              
                        ->setPoloSize(Polosize::from($polo))
                        ->setIsCompetitor(1)
                        ->setIsVerified(0)
                        ->setCreatedAt($this->now)
                        ->setUpdatedAt($this->now);

                    $this->entityManager->persist($user);
                    $userCreated++;
                }
            } 
        }           
        
        $this->entityManager->flush();

        if ($userCreated > 0 ){           
            $string1 = '{$userCreated} compétiteurs d\'ajoutés';
        }
        else {
            $string1 = '';
        }

        $this->io->success($string1);
    }

    private function uploadNavigators(): void
    {
        $userCreated = 0;
        foreach($this->getdataFromFile() as $row){          
            if (array_key_exists('adresse_mail_navigateur',$row) && !empty($row['adresse_mail_navigateur'])) {
                $user = $this->userRepository->findOneBy([
                    'email' => $row['adresse_mail_navigateur'],
                ]);

                if  (!$user) {
                    $pos = strrpos($row['Taille_polo_navigateur'],'X',0);
                    if (!empty($pos) and ($pos > 0)){
                        $polo = $pos + 1 . 'XL';
                    }
                    else{
                        $polo = $row['Taille_polo_navigateur'];
                    };
                    $birthdateArray = explode('/',$row['date_navigateur']);
                    $newDate= new DateTimeImmutable();                     
                    if (count($birthdateArray) == 0){
                        $birthdate = $newDate->setDate($birthdateArray[2],$birthdateArray[1],$birthdateArray[0]);
                    } else {
                        $birthdate = $this->now;
                    }            
                    $user = new users;
                    $user->setLastname($row['Nom_navigateur'])
                        ->setFirstname($row['Prénom_navigateur'])
                        ->setPassword('abhbdcjdbcjdbcjdsbcjdcbq')
                        ->setEmail($row['adresse_mail_navigateur'])
                        ->setPhone('0' . $row['Téléphone_portable_navigateur'])
                        ->setDateBirth($birthdate)
                        ->setLicenseFfa($row['N°_licence FFA_navigateur'])
                        ->setFlyingclub($row['Aéro-club_prise_de_licence_navigateur'])
                        ->setCommittee(CRAList::from($row['CRA_Licence_navigateur']))
                        ->setPoloSize(Polosize::from($polo))
                        ->setIsCompetitor(1)              
                        ->setIsVerified(0)
                        ->setCreatedAt($this->now)
                        ->setUpdatedAt($this->now);

                    $this->entityManager->persist($user);
                    $userCreated++;
                } 
            }     
        }                  

        $this->entityManager->flush();

        if ($userCreated > 0 ){           
            $string2 = '{$userCreated} compétiteurs d\'ajoutés';
        }
        else {
            $string2 = '';
        }

        $this->io->success($string2);
    }
}