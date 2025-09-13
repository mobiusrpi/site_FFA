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
                $competition = $crews[0]->getCompetition();

        $competName = $competition->getName();
        $filename = 'ExportCrews_' . preg_replace('/[^a-zA-Z0-9_-]/', '_', $competName) . '.csv';

        foreach ($crews as $crew) {
            // check if pilot is null
            $pilot = $crew->getPilot();
            if ($pilot) {
                $pilLastname  = $pilot->getLastname();
                $pilFirstname = $pilot->getFirstname();
                $pilFullname  = trim(($pilLastname ?? '') . ' ' . ($pilFirstname ?? ''));
            } else {
                $pilFullname = '';
            }
            // check if navigator is null
            $navigator = $crew->getNavigator();
            if ($navigator) {
                $navLastname  = $navigator->getLastname();
                $navFirstname = $navigator->getFirstname();
                $navFullname  = trim(($navLastname ?? 'Inconnu') . ' ' . ($navFirstname ?? ''));
            } else {
                $navFullname = '';
            }
            $rows[] = [
                'Concurrent' => $crew->getId(),
                'Categorie' => $crew->getCategory()?->value ?? '',   
                'Pilote' => $pilFullname,
                'Pilote_Licence_FFA' => $crew->getPilot()->getLicenseFfa() ,
                'Pilote_Telephone' => $crew->getPilot()->getPhone() ? $crew->getPilot()->getPhone() : '',
                'Pilote_Email' => $crew->getPilot()->getEmail() ,
                'Pilote_Date_Naissance' => $this->DateFormated($crew->getPilot()->getDateBirth()),
                'Pilote_Aeroclub' => $crew->getPilot()->getFlyingclub() ? $crew->getPilot()->getFlyingclub() : '',
                'Pilote_CRA' => $crew->getPilot()->getCommittee()?->value ?? '',                          
                'Pilote_Sexe' => $crew->getPilot()->getGender()?->value ?? '',
                'Pilote_taille_polo' => $crew->getPilot()->getPoloSize() ?->value ?? '',
                'Pilote_Sexe' => $crew->getPilot()->getGender()?->value ?? '',
                'Navigateur' => $navFullname,
                'Navigateur_Licence_FFA' => $crew->getNavigator()->getLicenseFfa() ?? '',
                'Navigateur_Telephone' => $crew->getNavigator()->getPhone() ? $crew->getNavigator()->getPhone() : '',
                'Navigateur_Email' => $crew->getNavigator()->getEmail() ??'',
                'Navigateur_Date_Naissance' => $this->DateFormated($crew->getNavigator()->getDateBirth()),
                'Navigateur_Aeroclub' => $crew->getNavigator()->getFlyingclub() ? $crew->getNavigator()->getFlyingclub() : '',
                'Navigateur_CRA' => $crew->getNavigator()->getCommittee() ?->value ?? '',
                'Navigateur_taille_polo' => $crew->getNavigator()->getPoloSize() ?->value ?? '',
                'Immatriculation' => $crew->getCallsign() ? $crew->getCallSign() : '',
                'Vitesse' => $crew->getAircraftSpeed() ?->value ?? '', 
                'OACI' => $crew->getAircraftOaci() ?$crew->getAircraftOaci() : '', 
                'Marque_avion' => $crew->getAircraftBrand() ? $crew->getAircraftBrand() : '',
                'Type_avion' => $crew->getAircraftType() ? $crew->getAircraftType() : '',
                'Avion_partage' => $crew->isAircraftSharing() ? 'Oui' : 'Non',
                'Pilote_de_partage' => $crew->getPilotShared() ? $crew->getPilotShared() : '' ,
                'Creation' => $this->DateFormated($crew->getRegisteredAt()),
                'Enregistre_par' => $crew->getRegisteredBy()->getLastname() . ' ' . $crew->getRegisteredBy()->getFirstname(),
            ];
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
