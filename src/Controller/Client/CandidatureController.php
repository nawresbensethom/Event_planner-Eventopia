<?php
namespace App\Controller\Client;

use App\Entity\Candidature;
use App\Form\CandidatureType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\Filesystem\Filesystem;
use Doctrine\ORM\EntityManagerInterface;

#[Route('/client/candidature')]
class CandidatureController extends AbstractController
{
    #[Route('/new', name: 'client_candidature_new')]
    public function new(Request $request,EntityManagerInterface $entityManager): Response
    {
        $candidature = new Candidature();
        $form = $this->createForm(CandidatureType::class, $candidature);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Récupérer le fichier CV téléchargé
            $cvFile = $form->get('cv')->getData();

            if ($cvFile) {
                $originalFilename = pathinfo($cvFile->getClientOriginalName(), PATHINFO_FILENAME);
                $newFilename = uniqid() . '.' . $cvFile->guessExtension();

                // Déplacez le fichier dans le répertoire voulu
                try {
                    $cvFile->move(
                        $this->getParameter('cv_directory'),
                        $newFilename
                    );
                    $candidature->setCv($newFilename); // Sauvegarder le nom du fichier dans l'entité
                } catch (FileException $e) {
                    // Gérer l'exception si le fichier n'a pas pu être déplacé
                }
            }

            $entityManager->persist($candidature);
            $entityManager->flush();

            return $this->redirectToRoute('client_candidature_success');
        }

        return $this->render('frontoffice/candidature/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/', name: 'client_candidature_index')]
    public function showAll(EntityManagerInterface $entityManager): Response
    {
        $candidatures = $entityManager->getRepository(Candidature::class)->findAll();

        return $this->render('frontoffice/candidature/show_all.html.twig', [
            'candidatures' => $candidatures,
        ]);
    }
}