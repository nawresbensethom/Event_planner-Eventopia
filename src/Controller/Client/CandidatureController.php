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
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

#[Route('/client/candidature')]
class CandidatureController extends AbstractController
{
    #[Route('/new', name: 'client_candidature_new')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
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
                        $this->getParameter('cvs_directory'),
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

    #[Route('/dashboard', name: 'client_candidature_dashboard', methods: ['GET'])]
    public function dashboard(EntityManagerInterface $entityManager, \App\Service\OcrService $ocrService): Response
    {
        $candidatures = $entityManager->getRepository(Candidature::class)->findAll();
        $skillsByCandidature = [];

        foreach ($candidatures as $candidature) {
            $cvFilename = $candidature->getCv();
            $cvPath = $this->getParameter('cvs_directory') . '/' . $cvFilename;

            if (file_exists($cvPath)) {
                $text = $ocrService->extractTextFromFile($cvPath);
                $skills = $ocrService->extractSkillsSection($text);
            } else {
                $skills = 'CV non trouvé';
            }

            $skillsByCandidature[$candidature->getIdCandidature()] = $skills;
        }

        return $this->render('frontoffice/candidature/dashboard.html.twig', [
            'candidatures' => $candidatures,
            'skillsByCandidature' => $skillsByCandidature,
        ]);
    }


    #[Route('/{id}/change-statut/{statut}', name: 'client_candidature_change_statut', methods: ['POST'])]
    public function changeStatut(
        int $id,
        string $statut,
        EntityManagerInterface $entityManager,
        MailerInterface $mailer
    ): Response {
        $candidature = $entityManager->getRepository(Candidature::class)->find($id);

        if (!$candidature) {
            throw $this->createNotFoundException('Candidature introuvable.');
        }

        if (!in_array($statut, ['acceptée', 'refusée'])) {
            throw $this->createNotFoundException('Statut invalide.');
        }

        $candidature->setStatut($statut);
        $entityManager->flush();
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        $email = $user->getEmail();

        // Envoi de l'e-mail uniquement si acceptée
        if ($statut === 'acceptée') {
            $email = (new Email())
                ->from('contact@eventopia.com')
                ->to($email) 
                ->subject('Votre candidature a été acceptée')
                ->html('<p>Félicitations ! Votre candidature a été acceptée. Nous vous contacterons prochainement pour la suite du processus.</p>');

            $mailer->send($email);
        }

        return $this->redirectToRoute('client_candidature_dashboard');
    }
}
