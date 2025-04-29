<?php

namespace App\Command;

use App\Entity\Competitors;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\CompetitorsRepository;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
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
        $this->uploadCompetitor();

        return Command::SUCCESS;
    }

    private function getDataFromFile(): array
    {
        $file = $this->dataDirectory . 'IRRA-2025-2.csv';
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
        
//       if (array_key_exist('results',$data)) {
//            return $data['result'];
//       }
        return $data;
    }

    private function uploadCompetitor(): void
    {
        $competitorCreated = 0;
        foreach($this->getdataFromFile() as $row){          
            if (array_key_exists('Adresse e-mail',$row) && !empty($row['Adresse e-mail'])) {
                $competitor = $this->competitorRepository->findOneBy([
                    'email' => $row['Adresse e-mail'],
                ] );

                if  (!$competitor) {
                    $competitor = new Competitors;
                    $competitor->setEmail($row['Adresse e-mail'])
                                ->setLastname($row['Nom pilote'])
                                ->setFirstname($row['Prénom_pilote'])
                                ->setFfaLicence($row['N°_licence FFA_pilote']);
                    $this->entityManager->persist($competitor);
                    $competitorCreated++;
                } 
            } 
        }         
        $this->entityManager->flush();

        if ($competitorCreated > 0 ){           
            $string = '{$userCreated} compétiteurs d\'ajoutés';
        }

        $this->io->success($string);
    }
}
