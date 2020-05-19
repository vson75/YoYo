<?php


namespace App\Service;


use Gedmo\Sluggable\Util\Urlizer;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class UploadService
{

    private $uploadsPath;

    public function __construct(string $uploadsPath)
    {
        $this->uploadsPath = $uploadsPath;
    }

    public function UploadPostImage(UploadedFile $uploadedFile): string {

        $destination = $this->uploadsPath.'/post/image';
        $origineFilename = pathinfo($uploadedFile->getClientOriginalName(),PATHINFO_FILENAME);
        $newFilename = Urlizer::urlize($origineFilename).'-'.uniqid().'.'.$uploadedFile->guessExtension();

        $uploadedFile->move(
            $destination,
            $newFilename
        );

        return $newFilename;
    }
}