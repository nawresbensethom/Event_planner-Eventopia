<?php

namespace App\Controller;

use App\Entity\Profil;
use App\Form\ProfilType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/profil')]
class ProfilController extends AbstractController
{
    #[Route('/edit', name: 'app_profil_edit')]
    public function edit(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $user = $this->getUser();
        $profil = $user->getProfil();

        if (!$profil) {
            $profil = new Profil();
            $profil->setUser($user);
        }

        // Store the old photo filename
        $oldPhoto = $profil->getPhoto();

        $form = $this->createForm(ProfilType::class, $profil);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Handle photo upload
            $photoFile = $form->get('photo')->getData();
            $removePhoto = $form->get('remove_photo')->getData();

            if ($photoFile) {
                $originalFilename = pathinfo($photoFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$photoFile->guessExtension();

                try {
                    $photoFile->move(
                        $this->getParameter('photos_directory'),
                        $newFilename
                    );

                    // Delete old photo if exists
                    if ($oldPhoto) {
                        $oldPhotoPath = $this->getParameter('photos_directory').'/'.$oldPhoto;
                        if (file_exists($oldPhotoPath)) {
                            unlink($oldPhotoPath);
                        }
                    }

                    $profil->setPhoto($newFilename);
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Une erreur est survenue lors du téléchargement de la photo');
                }
            } elseif ($removePhoto && $oldPhoto) {
                // Remove the old photo if checkbox is checked
                $oldPhotoPath = $this->getParameter('photos_directory').'/'.$oldPhoto;
                if (file_exists($oldPhotoPath)) {
                    unlink($oldPhotoPath);
                }
                $profil->setPhoto(null);
            }

            // Get coordinates from the request
            $latitude = $request->request->get('latitude');
            $longitude = $request->request->get('longitude');

            if ($latitude && $longitude) {
                $profil->setLatitude((float)$latitude);
                $profil->setLongitude((float)$longitude);
            }

            $entityManager->persist($profil);
            $entityManager->flush();

            $this->addFlash('success', 'Profil mis à jour avec succès');
            return $this->redirectToRoute('app_profil_show');
        }

        return $this->render('frontoffice/profil/edit.html.twig', [
            'form' => $form->createView(),
            'profil' => $profil
        ]);
    }
} 