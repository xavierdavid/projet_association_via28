<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileUploader
{
    private $targetDirectory;

    public function __construct($targetDirectory) {
        // Constructeur de classe permettant de récupérer le répertoire cible pour le téléchargement de fichiers configuré dans 'service.yaml'
        $this->targetDirectory = $targetDirectory;
    }

    /**
     * Permet de gérer l'upload de fichiers
     *
     * @param UploadedFile $file
     * @return void
     */
    public function upload(UploadedFile $file)
    {
       // On récupère le nom original du fichier à l'aide du composant UploadedFile $file de Symfony
       $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
       // On laisse symfony affecter la bonne extension au fichier final
       $newFilename = $originalFilename.'.'. $file->guessExtension();
       // On déplace le fichier vers le répertoire de stockage
       try {
           $file->move($this->getTargetDirectory(), $newFilename);
       
       } catch (FileException $e) {
           // ... On lance une exception en cas d'anomalie
       } 

       return $newFilename;
    }

    /**
     * Permet de retourner le répertoire cible pour l'upload de fichiers
     *
     * @return void
     */
    public function getTargetDirectory(){
        return $this->targetDirectory;
    }
}
