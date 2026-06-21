<?php

namespace App\Services;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileUploader
{
    private string $targetDirectory;
    private EntityManagerInterface $entityManager;

    public function __construct(string $targetDirectory, EntityManagerInterface $entityManager)
    {
        $this->targetDirectory = $targetDirectory;
        $this->entityManager = $entityManager;
    }

    public function upload(UploadedFile $file)
    {
        $filename = uniqid() . '.' . $file->guessExtension();
        $file->move($this->targetDirectory, $filename);
        return $filename;
    }

    public function removeProductAdminImage($imageCurrent)
    {
        $fileExist = $this->targetDirectory . '/' . $imageCurrent->getFileName();

        if (file_exists($fileExist)) {
            unlink($fileExist);
        }

        $this->entityManager->remove($imageCurrent);
    }

    public function removeProductImage($images)
    {
        if (!$images) return;

        foreach ($images as $picture) {
            $fileName = $this->targetDirectory . '/' . $picture->getFileName();
            if (file_exists($fileName)) {
                unlink($fileName);
            }

            $this->entityManager->remove($picture);
        }
    }

    public function removeTestimonialImage($images)
    {
        if (!$images) return;

        foreach ($images as $picture) {
            $fileName = $this->targetDirectory . '/' . $picture->getFileName();
            if (file_exists($fileName)) {
                unlink($fileName);
            }

            $this->entityManager->remove($picture);
        }
    }
}
