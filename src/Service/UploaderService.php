<?php

namespace App\Service;

use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class UploaderService 
{
    //on va lui passer un objet de type uploadedFile
    //et elle doit nous retourner le nom de ce 'File'
    public function __construct(private SluggerInterface $slugger) {}
    public function uploadFile(
        UploadedFile $file,
        string $directoryFolder
    ) 
    {
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        // nécessaire pour inclure en toute sécurité le nom du fichier dans l'URL
        $safeFilename = $this->slugger->slug($originalFilename);
        $newFilename = $safeFilename.'-'.uniqid().'.'.$file->guessExtension();

        // Déplacer le fichier dans le dossier où sont stockées les images
        try {
            $file->move(
                $directoryFolder,
                $newFilename
            );
        } catch (FileException $e) {
            // ... gérer l'exception si quelque chose se passe pendant le téléchargement du fichier
        }
        return $newFilename;
    }
}