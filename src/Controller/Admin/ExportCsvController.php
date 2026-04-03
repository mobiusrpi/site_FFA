<?php 
namespace App\Controller\Admin;

use App\Repository\CrewsRepository;
use App\Service\CsvExporter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ExportCsvController extends AbstractController
{
    
    private function DateFormated(?\DateTimeInterface $date): string {
        return $date ? $date->format('d-m-Y') : '';
    }

    #[Route('/admin/crews/export_pipper/{competitionId}', name: 'admin_export_pipper')]
    public function exportPipper(
        int $competitionId,
        CrewsRepository $repositoryCrew,
        CsvExporter $csvExporter
    ): Response {
        $crews = $repositoryCrew->getQueryCrews($competitionId);
                $competition = $crews[0]->getCompetition();

        $competType = $competition->getTypecompetition();    
        $competName = $competition->getName();
        $filename = 'ExportPipper_' . preg_replace('/[^a-zA-Z0-9_-]/', '_', $competName) . '.csv';

        foreach ($crews as $crew) {
            if ($competType->getId() == 2) {
                $rows[] = [
                    'registration_number' => (string) $crew->getId(),
                    'category' => $crew->getCategory()?->value ?? '',   
                    'pilot_lastname' => $crew->getPilot()?->getLastname(),
                    'pilot_firstname' =>  $crew->getPilot()?->getFirstname() ,
                    'pilot_sex' => $crew->getPilot()->getGender()?->value[0] ?? '',
                    'pilot_club' =>  (string) $crew->getPilot()?->getFlyingclub() ?? '',
                    'pilot_cra' => $crew->getPilot()->getCommittee()?->getCode() ?? '', 
                    'copilot_lastname' => '' ,
                    'copilot_firstname' => '',
                    'copilot_sex' => '',
                    'copilot_club' => '',
                    'copilot_cra' => '', 
                    'aircraft_brand' => (string) $crew->getAircraftBrand() ? $crew->getAircraftBrand() : '',
                    'aircraft_type' => (string) $crew->getAircraftType() ? $crew->getAircraftType() : '',
                    'aircraft_matriculation' => (string) $crew->getCallsign() ? $crew->getCallSign() : '', 
                    'aircraft_colors' => '', 
                    'aircraft_oaci' => (string) $crew->getAircraftOaci() ? $crew->getAircraftOaci() : '', 
                    'aircraft_speed' => $crew->getAircraftSpeed() ?->value ?? '', 
                ];
            } else{
               $rows[] = [
                    'registration_number' => (string) $crew->getId(),
                    'category' => $crew->getCategory()?->value ?? '',   
                    'pilot_lastname' => $crew->getPilot()?->getLastname(),
                    'pilot_firstname' =>  $crew->getPilot()?->getFirstname() ,
                    'pilot_sex' => $crew->getPilot()->getGender()?->value[0] ?? '',
                    'pilot_club' =>  (string) $crew->getPilot()?->getFlyingclub() ?? '',
                    'pilot_cra' =>  $crew->getPilot()->getCommittee()?->getCode() ?? '',  
                    'copilot_lastname' => $crew->getNavigator()?->getLastname(),
                    'copilot_firstname' =>  $crew->getNavigator()?->getFirstname() ,
                    'copilot_sex' => $crew->getNavigator()->getGender()?->value[0] ?? '',
                    'copilot_club' => $crew->getNavigator()?->getFlyingclub() ?? '',
                    'copilot_cra' => $crew->getNavigator()->getCommittee()?->getCode() ?? '',  
                    'aircraft_brand' => (string) $crew->getAircraftBrand() ? $crew->getAircraftBrand() : '',
                    'aircraft_type' => (string) $crew->getAircraftType() ? $crew->getAircraftType() : '',
                    'aircraft_matriculation' => (string) $crew->getCallsign() ? $crew->getCallSign() : '', 
                    'aircraft_colors' => '', 
                    'aircraft_oaci' => (string) $crew->getAircraftOaci() ? $crew->getAircraftOaci() : '', 
                    'aircraft_speed' => $crew->getAircraftSpeed() ?->value ?? '', 
                ];
            }
        }
        $csvContent = $csvExporter->exportCsv($rows);

        // Conversion LF -> CRLF pour compatibilité Windows/Excel
        $csvContent = str_replace("\n", "\r\n", $csvContent);
        
        // Conversion UTF-8 -> Windows-1252 (ANSI)
        $csvContent = iconv("UTF-8", "Windows-1252//TRANSLIT", $csvContent);

        // Retour d’une Response normale avec headers CSV
        return new Response(
            $csvContent,
            200,
            [
                'Content-Type'        => 'text/csv; charset=Windows-1252',
                'Content-Disposition' => 'attachment; filename='.$filename,
            ]
        );
    }

    #[Route('/admin/crews/export_crews/{competitionId}', name: 'admin_export_crews')]
    public function exportCrews(
        int $competitionId,
        CrewsRepository $repositoryCrew,
        CsvExporter $csvExporter
    ): Response {
        $crews = $repositoryCrew->getQueryCrews($competitionId);
        
        if (empty($crews)) {
            throw $this->createNotFoundException('Aucun équipage trouvé.');
        }
        $competition = $crews[0]->getCompetition();

        $competName = $competition->getName(); 

        $map = [
            'À'=>'A','Á'=>'A','Â'=>'A','Ã'=>'A','Ä'=>'A','Å'=>'A','Æ'=>'AE','Ç'=>'C','È'=>'E','É'=>'E','Ê'=>'E','Ë'=>'E',
            'Ì'=>'I','Í'=>'I','Î'=>'I','Ï'=>'I','Ð'=>'D','Ñ'=>'N','Ò'=>'O','Ó'=>'O','Ô'=>'O','Õ'=>'O','Ö'=>'O','Ø'=>'O',
            'Ù'=>'U','Ú'=>'U','Û'=>'U','Ü'=>'U','Ý'=>'Y','Þ'=>'TH','ß'=>'ss',
            'à'=>'a','á'=>'a','â'=>'a','ã'=>'a','ä'=>'a','å'=>'a','æ'=>'ae','ç'=>'c','è'=>'e','é'=>'e','ê'=>'e','ë'=>'e',
            'ì'=>'i','í'=>'i','î'=>'i','ï'=>'i','ð'=>'d','ñ'=>'n','ò'=>'o','ó'=>'o','ô'=>'o','õ'=>'o','ö'=>'o','ø'=>'o',
            'ù'=>'u','ú'=>'u','û'=>'u','ü'=>'u','ý'=>'y','þ'=>'th','ÿ'=>'y'
        ];
        $competName = strtr($competName, $map);
        $competName = preg_replace('/[^a-zA-Z0-9_-]+/', '_', $competName);
        $competName = trim($competName, '_-');
        $competName = preg_replace('/[_-]{2,}/', '_', $competName);

        $filename = 'ExportCrews_' . $competName . '.csv';

        $competitionAccommodations = [];
        foreach ($competition->getCompetitionAccommodation() as $compAcc) {
            $accommodation = $compAcc->getAccommodation();
            if ($accommodation && $accommodation->getRoom()) {
                $competitionAccommodations[$accommodation->getId()] = $accommodation->getRoom();
            }
        }
        $rows = [];
        
        foreach ($crews as $crew) {
            // check if pilot is null
            $pilot = $crew->getPilot();
            $navigator = $crew->getNavigator();
            $pilFullname = $pilot ? trim(($pilot->getFullname() ?? '')) : '';
            $navFullname = $navigator ? trim(($navigator->getFullname() ?? '')) : '';
            $row = [
                'Concurrent' => $crew->getId(),
                'Categorie' => $crew->getCategory()?->value ?? '',   
                'Pilote' => $pilFullname,
                'Pilote_Licence_FFA' => $pilot?->getLicenseFfa() ?? '',
                'Pilote_Telephone' => $pilot?->getPhone() ?? '','Pilote_Email' => $pilot?->getEmail() ?? '',
                'Pilote_Date_Naissance' => $this->DateFormated($pilot?->getBirthdate()),
                'Pilote_Aeroclub' => $pilot?->getFlyingclub() ?? '',
                'Pilote_CRA' => $pilot?->getCommittee()?->value ?? '',
                'Pilote_Sexe' => $pilot?->getGender()?->value ?? '',
                'Pilote_taille_polo' => $pilot?->getPoloSize()?->value ?? '',
                'Navigateur' => $navFullname,
                'Navigateur_Licence_FFA' => $navigator?->getLicenseFfa() ?? '',
                'Navigateur_Telephone' => $navigator?->getPhone() ?? '',
                'Navigateur_Email' => $navigator?->getEmail() ?? '',
                'Navigateur_Date_Naissance' => $this->DateFormated($navigator?->getBirthdate()),
                'Navigateur_Aeroclub' => $navigator?->getFlyingclub() ?? '',
                'Navigateur_CRA' => $navigator?->getCommittee()?->value ?? '',
                'Navigateur_taille_polo' => $navigator?->getPoloSize()?->value ?? '',
                'Immatriculation' => $crew->getCallsign() ? $crew->getCallSign() : '',
                'Vitesse' => $crew->getAircraftSpeed() ?->value ?? '', 
                'OACI' => $crew->getAircraftOaci() ?$crew->getAircraftOaci() : '', 
                'Marque_avion' => $crew->getAircraftBrand() ? $crew->getAircraftBrand() : '',
                'Type_avion' => $crew->getAircraftType() ? $crew->getAircraftType() : '',
                'Avion_partage' => $crew->isAircraftSharing() ? 'Oui' : 'Non',
                'Pilote_de_partage' => $crew->getPilotShared() ? $crew->getPilotShared() : '' ,
                'Creation' => $this->DateFormated($crew->getRegisteredAt()),
                'Enregistre_par' => $crew->getRegisteredBy()
                    ? $crew->getRegisteredBy()->getLastname() . ' ' . $crew->getRegisteredBy()->getFirstname()
                    : '',
            ];
            // On indexe les accommodations du crew pour éviter double boucle lourde
            $crewAccIds = [];

            foreach ($crew->getCompetitionAccommodation() as $crewAcc) {
                if ($crewAcc->getAccommodation()) {
                    $crewAccIds[] = $crewAcc->getAccommodation()->getId();
                }
            }

            foreach ($competitionAccommodations as $accId => $accName) {
                $crewAccPrice = ''; // par défaut vide
                foreach ($crew->getCompetitionAccommodation() as $crewAcc) {
                    $accommodation = $crewAcc->getAccommodation();
                    if ($accommodation && $accommodation->getId() == $accId) {
                        $crewAccPrice = $crewAcc->getPrice(); // récupère le montant
                        break;
                    }
                }            
                $row[$accName] = $crewAccPrice !== null
                    ? number_format((float)$crewAccPrice / 100, 2, ',', '') // 150,00
                    : ''
                ;  
             }

            $rows[] = $row;

        }
        $csvContent = $csvExporter->exportCsv($rows);

        // Forcer CRLF
        $csvContent = str_replace("\n", "\r\n", $csvContent);

        // Ajouter le BOM UTF-8 pour Excel
        $csvContent = "\xEF\xBB\xBF" . $csvContent;


        // Retour d’une Response normale avec headers CSV
        return new Response(
            $csvContent,
            200,
            [
                'Content-Type'        => 'text/csv; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename='.$filename,
            ]
        );
    }
}
