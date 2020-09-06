<?php


namespace App\Service;


use App\Entity\DocumentType;
use App\Entity\Post;
use App\Entity\PostDocument;
use App\Entity\RequestOrganisationDocument;
use App\Entity\RequestStatus;
use App\Entity\User;
use App\Entity\UserDocument;
use App\Repository\RequestStatusRepository;
use Doctrine\ORM\EntityManagerInterface;
use Gedmo\Sluggable\Util\Urlizer;
use League\Flysystem\FilesystemInterface;
use PhpParser\Comment\Doc;
use Symfony\Component\HttpFoundation\File\UploadedFile;


class UploadService
{
    const Post_image = '/post/image';
    const User_icon = '/user/icon';
    // const to upload document Organisation
    const Organisation_document_Upload_Download_Path = '/user/documents_request/';
    //const to download document Organisation
    const Organisation_document_path = 'uploads/user/documents_request/';

    const Post_Proof_Transfer_Fund = '/post/';
    const Proof_transfert = '/proof_transfer/';
    //const to upload proof received
    const Proof_received = '/proof_received/';

    //const to upload proof received
    const Proof_received_document_path = 'uploads/post/';
    //const to download proof received
    const Proof_received_download_path = '/post/';


    private $publicUploadFilesystem;
    private $em;
    private $publicAssetBaseUrl;
    private $privateUploadsFilesystem;
    private $requestStatusRepository;

    public function __construct(EntityManagerInterface $em, FilesystemInterface $publicUploadsFilesystem,string $uploadedAssetsBaseUrl, FilesystemInterface $privateUploadsFilesystem, RequestStatusRepository $requestStatusRepository)
    {
        $this->em = $em;
        $this->publicUploadFilesystem = $publicUploadsFilesystem;
        $this->publicAssetBaseUrl = $uploadedAssetsBaseUrl;
        $this->privateUploadsFilesystem = $privateUploadsFilesystem;
        $this->requestStatusRepository = $requestStatusRepository;
    }

    public function UploadPostImage(UploadedFile $uploadedFile,?string $existingFilename): string {

        $destination = self::Post_image;
        $origineFilename = pathinfo($uploadedFile->getClientOriginalName(),PATHINFO_FILENAME);
        $newFilename = Urlizer::urlize($origineFilename).'-'.uniqid().'.'.$uploadedFile->guessExtension();

        $stream = fopen($uploadedFile->getPathname(), 'r');
        $result = $this->publicUploadFilesystem->writeStream($destination.'/'.$newFilename,$stream);

        if ($result === false) {
            throw new \Exception(sprintf('Could not write uploaded file "%s"', $newFilename));
        }

        if (is_resource($stream)) {
            fclose($stream);
        }
        if ($existingFilename) {
            $this->publicUploadFilesystem->delete(self::Post_image.'/'.$existingFilename);
        }
        return $newFilename;
    }

    public function UploadIconImage(UploadedFile $uploadedFile, $userID,?string $existingFilename): string{

        $destination = self::User_icon.'/'.$userID;
        $origineFilename = pathinfo($uploadedFile->getClientOriginalName(),PATHINFO_FILENAME);
        $newFilename = Urlizer::urlize($origineFilename).'-'.uniqid().'.'.$uploadedFile->guessExtension();

        $stream = fopen($uploadedFile->getPathname(), 'r');
        $result = $this->publicUploadFilesystem->writeStream($destination.'/'.$newFilename,$stream);

        if ($result === false) {
            throw new \Exception(sprintf('Could not write uploaded file "%s"', $newFilename));
        }

        if (is_resource($stream)) {
            fclose($stream);
        }
        if ($existingFilename) {
            $this->publicUploadFilesystem->delete(self::User_icon.'/'.$userID.'/'.$existingFilename);
        }
        return $newFilename;
    }


    public function UploadRequestOrganisationDocument(UploadedFile $uploadedFile, $userID, $documentType, ?string $existingFilename): string {
        $destination = self::Organisation_document_Upload_Download_Path.$userID;
        $origineFilename = pathinfo($uploadedFile->getClientOriginalName(),PATHINFO_FILENAME);
        $newFilename = $documentType.'-'.Urlizer::urlize($origineFilename).'-'.uniqid().'.'.$uploadedFile->guessExtension();

        $stream = fopen($uploadedFile->getPathname(), 'r');
        $result = $this->publicUploadFilesystem->writeStream($destination.'/'.$newFilename,$stream);
        if ($result === false) {
            throw new \Exception(sprintf('Could not write uploaded file "%s"', $newFilename));
        }

        if (is_resource($stream)) {
            fclose($stream);
        }
        // Delete old document except Awards_justification
        if($documentType != DocumentType::Awards_justification && $existingFilename){
            $this->publicUploadFilesystem->delete(self::Organisation_document_Upload_Download_Path.'/'.$userID.'/'.$existingFilename);
        }

        return $newFilename;
    }


    public function UploadRequestOrganisationDocumentByType($documentType, UploadedFile $uploadedFile, User $user, ?string $existingFilename){
        $documentTypeRepo  = $this->em->getRepository(DocumentType::class);
        $userDocument = new RequestOrganisationDocument();
        $document_type =  $documentTypeRepo->findOneBy([
            'id' => $documentType
        ]);

        $status = $this->requestStatusRepository->findOneBy([
            'id' => RequestStatus::Request_Sent
        ]);

    //     dd($user);
            $newFileName = $this->UploadRequestOrganisationDocument($uploadedFile,$user->getId(), $documentType, $existingFilename);


        $userDocument->setUser($user);
       // dd($userDocument);
        $userDocument->setUser($user)
            ->setFilename($newFileName)
            ->setOriginalFilename($uploadedFile->getClientOriginalName())
            ->setDepositeDate(new \DateTime('now'))
            ->setMimeType($uploadedFile->getMimeType() ?? 'application/octet-stream')
            ->setDocumentType($document_type)
            ->setRequestStatus($status)
            ->setIsDeleted(false);

        $this->em->persist($userDocument);
        $this->em->flush();
    }


    public function readStream(string $path,bool $isPublic){

        $filesystem = $isPublic ? $this->publicUploadFilesystem : $this->privateUploadsFilesystem;
       // dd($filesystem);
        $resource = $filesystem->readStream($path);
        if ($resource === false) {
            throw new \Exception(sprintf('Error opening stream for "%s"', $path));
        }
        return $resource;
    }

    public function deleteDocument($documentName,$userID){
        $this->publicUploadFilesystem->delete(self::Organisation_document_Upload_Download_Path.'/'.$userID.'/'.$documentName);
    }


    public function uploadPrivateProofOfTransfert(UploadedFile $uploadedFile, Post $post){

        $destination = self::Post_Proof_Transfer_Fund.$post->getId().self::Proof_transfert;


        $origineFilename = pathinfo($uploadedFile->getClientOriginalName(),PATHINFO_FILENAME);
        $newFilename = Urlizer::urlize($origineFilename).'-'.uniqid().'.'.$uploadedFile->guessExtension();

        $stream = fopen($uploadedFile->getPathname(), 'r');
        $result = $this->privateUploadsFilesystem->writeStream($destination.$newFilename,$stream);
        if ($result === false) {
            throw new \Exception(sprintf('Could not write uploaded file "%s"', $newFilename));
        }

        if (is_resource($stream)) {
            fclose($stream);
        }

        return $newFilename;

    }


    public function uploadProofOfReceivedBank(UploadedFile $uploadedFile, Post $post){

        $destination = self::Post_Proof_Transfer_Fund.$post->getId().self::Proof_received;
        $origineFilename = pathinfo($uploadedFile->getClientOriginalName(),PATHINFO_FILENAME);
        $newFilename = Urlizer::urlize($origineFilename).'-'.uniqid().'.'.$uploadedFile->guessExtension();

        $stream = fopen($uploadedFile->getPathname(), 'r');
        $result = $this->publicUploadFilesystem->writeStream($destination.$newFilename,$stream);
        if ($result === false) {
            throw new \Exception(sprintf('Could not write uploaded file "%s"', $newFilename));
        }

        if (is_resource($stream)) {
            fclose($stream);
        }

        return $newFilename;

    }

}