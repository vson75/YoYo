<?php


namespace App\Service;


use App\Entity\DocumentType;
use App\Entity\User;
use App\Entity\UserDocument;
use Doctrine\ORM\EntityManagerInterface;
use Gedmo\Sluggable\Util\Urlizer;
use League\Flysystem\FilesystemInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;


class UploadService
{
    const Post_image = '/post/image';
    const User_icon = '/user/icon';
    const User_document = '/user/documents/';

    private $filesystem;
    private $em;
    private $publicAssetBaseUrl;
    private $privateUploadsFilesystem;

    public function __construct(EntityManagerInterface $em, FilesystemInterface $publicUploadsFilesystem,string $uploadedAssetsBaseUrl, FilesystemInterface $privateUploadsFilesystem)
    {
        $this->em = $em;
        $this->filesystem = $publicUploadsFilesystem;
        $this->publicAssetBaseUrl = $uploadedAssetsBaseUrl;
        $this->privateUploadsFilesystem = $privateUploadsFilesystem;
    }

    public function UploadPostImage(UploadedFile $uploadedFile,?string $existingFilename): string {

        $destination = self::Post_image;
        $origineFilename = pathinfo($uploadedFile->getClientOriginalName(),PATHINFO_FILENAME);
        $newFilename = Urlizer::urlize($origineFilename).'-'.uniqid().'.'.$uploadedFile->guessExtension();

        $stream = fopen($uploadedFile->getPathname(), 'r');
        $result = $this->filesystem->writeStream($destination.'/'.$newFilename,$stream);

        if ($result === false) {
            throw new \Exception(sprintf('Could not write uploaded file "%s"', $newFilename));
        }

        if (is_resource($stream)) {
            fclose($stream);
        }
        if ($existingFilename) {
            $this->filesystem->delete(self::Post_image.'/'.$existingFilename);
        }
        return $newFilename;
    }

    public function UploadIconImage(UploadedFile $uploadedFile, $userID,?string $existingFilename): string{

        $destination = self::User_icon.'/'.$userID;
        $origineFilename = pathinfo($uploadedFile->getClientOriginalName(),PATHINFO_FILENAME);
        $newFilename = Urlizer::urlize($origineFilename).'-'.uniqid().'.'.$uploadedFile->guessExtension();

        $stream = fopen($uploadedFile->getPathname(), 'r');
        $result = $this->filesystem->writeStream($destination.'/'.$newFilename,$stream);

        if ($result === false) {
            throw new \Exception(sprintf('Could not write uploaded file "%s"', $newFilename));
        }

        if (is_resource($stream)) {
            fclose($stream);
        }
        if ($existingFilename) {
            $this->filesystem->delete(self::User_icon.'/'.$userID.'/'.$existingFilename);
        }
        return $newFilename;
    }


    public function UploadUserDocument(UploadedFile $uploadedFile, $userID, $documentType): string {
        $destination = self::User_document.$userID;
        $origineFilename = pathinfo($uploadedFile->getClientOriginalName(),PATHINFO_FILENAME);
        $newFilename = $documentType.'-'.Urlizer::urlize($origineFilename).'-'.uniqid().'.'.$uploadedFile->guessExtension();

        $stream = fopen($uploadedFile->getPathname(), 'r');
        $result = $this->privateUploadsFilesystem->writeStream($destination.'/'.$newFilename,$stream);
        if ($result === false) {
            throw new \Exception(sprintf('Could not write uploaded file "%s"', $newFilename));
        }

        if (is_resource($stream)) {
            fclose($stream);
        }

        return $newFilename;
    }


    public function UploadUserDocumentByType($documentType, UploadedFile $uploadedFile, User $user){
        $documentTypeRepo  = $this->em->getRepository(DocumentType::class);
        $userDocument = new UserDocument();
        $document_type =  $documentTypeRepo->findOneBy([
            'id' => $documentType
        ]);

        $newFileName = $this->UploadUserDocument($uploadedFile,$user->getId(), $documentType);
        $userDocument->setUser($user)
            ->setFilename($newFileName)
            ->setOriginalFilename($uploadedFile->getClientOriginalName())
            ->setDepositDate(new \DateTime('now'))
            ->setMimeType($uploadedFile->getMimeType() ?? 'application/octet-stream')
            ->setDocumentType($document_type);

        $this->em->persist($userDocument);
        $this->em->flush();
    }


    public function readStream(string $path,bool $isPublic){

        $filesystem = $isPublic ? $this->filesystem : $this->privateUploadsFilesystem;
        $resource = $filesystem->readStream($path);
        if ($resource === false) {
            throw new \Exception(sprintf('Error opening stream for "%s"', $path));
        }
        return $resource;
    }

}