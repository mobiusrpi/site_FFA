<?php

namespace App\Controller;

use App\Entity\Results;
use App\Form\CsvUploadFileType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CsvUploadController extends AbstractController
{
    #[Route(path: '/upload_csv_file/', name: "upload_csv", methods:['GET','POST'])]
    public function uploadCsv(
        Request $request,
        EntityManagerInterface $entityManager)
    {
        $form = $this->createForm(CsvUploadFileType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $csvFile = $form->get('csv_file')->getData();

            if ($csvFile) {
                $filePath = $csvFile->getRealPath();

                if (($handle = fopen($filePath, "r")) !== FALSE) 
                {
                    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                        dd($data);
                        if ($data !== null) {
                            if ($data['Catégorie :'] = "Elite") {
                                $result = new Results();
                                $result->setName('Sample result');
                                $result->setPrice(19.99);
                                $result->setDescription('This is a sample result.');            
                            } 
                            elseif($data['Catégorie :'] = "Honneur") {

                            }
                            else{

                            }; 
                                // Tell Doctrine to manage the result entity
                            $entityManager->persist($result);
                        }   
                    }     
                    // Execute the insert query
                    $entityManager->flush();                
                    fclose($handle);                
                }
                $this->addFlash('success', 'Fichier chargé avec succes');
            }
        }

        return $this->render('pages/uploadCsv/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}