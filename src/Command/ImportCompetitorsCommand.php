<?php

namespace App\Command;

use App\Entity\Crews;
use DateTimeImmutable;
use App\Entity\Competitors;
use App\Entity\Enum\CRAList;
use App\Entity\Enum\Category;
use App\Entity\Enum\Polosize;
use App\Entity\Enum\SpeedList;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\CompetitorsRepository;
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
    name: 'ImportCompetitors',
    description: 'Impotation des compétiteurs du fichier .csv du Google Sheet de la FFA',
)]
class ImportCompetitorsCommand extends Command
{
    private EntityManagerInterface $entityManager;

    private string $dataDirectory;

    private SymfonyStyle $io;

    private CompetitorsRepository $competitorRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        string $dataDirectory,
        CompetitorsRepository $competitorRepository
    )
    {
        parent::__construct();
        $this->entityManager =$entityManager;
        $this->dataDirectory = $dataDirectory;
        $this->competitorRepository = $competitorRepository;
    }

    protected function initialize(InputInterface $input, OutputInterface $output):void
    {
        $this->io = new SymfonyStyle($input,$output);

    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
//         $this->uploadPilots();
       $this->uploadNavigators();

        return Command::SUCCESS;
    }

    private function getDataFromFile(): array
    {
        $file = $this->dataDirectory . 'IRRA-2025-1.csv';
        $fileExtension = pathinfo($file,PATHINFO_EXTENSION);

        $normalizers = [new ObjectNormalizer];
        $encoders = [ 
           new CsvEncoder(),
           new JsonEncoder()
        ];
        $serializer = new Serializer($normalizers,$encoders);

        /** $var string $fileString */
        $fileString = file_get_contents($file);
        $data = $serializer->decode($fileString,$fileExtension);
        
        return $data;
    }

    private function uploadPilots(): void
    {
        $competitorCreated = 0;
        foreach($this->getdataFromFile() as $row){          
            if (array_key_exists('Adresse e-mail',$row) && !empty($row['Adresse e-mail'])) {
                $competitor = $this->competitorRepository->findOneBy([
                    'email' => $row['Adresse e-mail'],
                ] );

                if  (!$competitor) {                          
                    $competitor = new Competitors;
                    $pos = strrpos($row['Taille_polo_pilote'],'X',0);
                    if (!empty($pos) and ($pos > 0)){
                        $polo = $pos + 1 . 'XL';
                    }
                    else{
                        $polo = $row['Taille_polo_pilote'];
                    };
                    $competitor->setEmail($row['Adresse e-mail'])
                                ->setLastname($row['Nom pilote'])
                                ->setFirstname($row['Prénom_pilote'])
                                ->setFfaLicence($row['N°_licence FFA_pilote'])
                                ->setFlyingclub($row['Aéro-club_prise_de_licence_pilote'])
                                ->setPhone($row['Téléphone_portable_pilote'])
                                ->setCommittee(CRAList::from($row['CRA_Licence_pilote']))                              
                                ->setPoloSize(Polosize::from($polo));
                  
                    $this->entityManager->persist($competitor);
                    $competitorCreated++;
                }
            } 
        }                  
        $this->entityManager->flush();

        if ($competitorCreated > 0 ){           
            $string1 = '{$competitorCreated} compétiteurs d\'ajoutés';
        }
        else {
            $string1 = '';
        }

        $this->io->success($string1);
    }

    private function uploadNavigators(): void
    {
        $competitorCreated = 0;
        foreach($this->getdataFromFile() as $row){          
            if (array_key_exists('adresse_mail_navigateur',$row) && !empty($row['adresse_mail_navigateur'])) {
                $competitor = $this->competitorRepository->findOneBy([
                    'email' => $row['adresse_mail_navigateur'],
                ] );

                if  (!$competitor) {
                    $pos = strrpos($row['Taille_polo_navigateur'],'X',0);
                    if (!empty($pos) and ($pos > 0)){
                        $polo = $pos + 1 . 'XL';
                    }
                    else{
                        $polo = $row['Taille_polo_navigateur'];
                    };
                    $competitor = new Competitors;
                    $competitor->setEmail($row['adresse_mail_navigateur'])
                                ->setLastname($row['Nom_navigateur'])
                                ->setFirstname($row['Prénom_navigateur'])
                                ->setFfaLicence($row['N°_licence FFA_navigateur'])
                                ->setFlyingclub($row['Aéro-club_prise_de_licence_navigateur'])
                                ->setPhone($row['Téléphone_portable_navigateur'])
                                ->setCommittee(CRAList::from($row['CRA_Licence_navigateur']))
                                ->setPoloSize(Polosize::from($polo));
                  
                    $this->entityManager->persist($competitor);
                    $competitorCreated++;
                } 
            } 
        }                  
        $this->entityManager->flush();

        if ($competitorCreated > 0 ){           
            $string2 = '{$competitorCreated} compétiteurs d\'ajoutés';
        }
        else {
            $string2 = '';
        }

        $this->io->success($string2);
    }
}
