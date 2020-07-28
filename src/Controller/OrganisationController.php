<?php


namespace App\Controller;


use App\Entity\DocumentType;
use App\Entity\RequestOrganisationDocument;
use App\Entity\RequestOrganisationInfo;
use App\Entity\RequestStatus;
use App\Entity\User;
use App\Form\CreateOrganisationType;
use App\Form\EditDocumentOrganisationType;
use App\Form\OrganisationInfoType;
use App\Repository\RequestStatusRepository;
use App\Service\Mailer;
use App\Service\UploadService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Annotation\Route;

class OrganisationController extends \Symfony\Bundle\FrameworkBundle\Controller\AbstractController
{


    /**
     * @Route("/create_organisation", name="app_create_organisation")
     */
    public function askForRoleOrganisation(Request $request, EntityManagerInterface $em, UploadService $uploadService, Mailer $mailer, RequestStatusRepository $requestStatusRepository){
        $user = $this->getUser();
        //  dd($user);
        $requestInfo = $em->getRepository(RequestOrganisationInfo::class)->findOneBy([
            'user' => $user
        ]);
        if($requestInfo){
            $this->addFlash('echec', "Bạn đã tạo tài khoản tổ chức, 1 tài khoản chỉ có thể tạo 1 tổ chức.");
            return $this->redirectToRoute('app_homepage');
        }
        $form = $this->createForm(CreateOrganisationType::class);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){

            $repo = $em->getRepository(User::class);
            $user = $repo->findOneBy([
                'email' => $user->getUsername()
            ]);
            $user->setAskOrganisation(true);
            $em->persist($user);
            $em->flush();

            $organisationInfo = new RequestOrganisationInfo();

            $request = $requestStatusRepository->findOneBy([
                'id' => RequestStatus::Request_Sent
            ]);

            $organisationInfo   ->setOrganisationName($form['OrganisationName']->getData())
                ->setAddress($form['Address']->getData())
                ->setCity($form['City']->getData())
                ->setZipCode($form['ZipCode']->getData())
                ->setCountry($form['Country']->getData())
                ->setPhoneNumber($form['PhoneNumber']->getData())
                ->setUser($this->getUser())
                ->setRequestStatus($request);

            $em->persist($organisationInfo);
            $em->flush();

            $documentTypeRepo  = $em->getRepository(DocumentType::class);

            $certificateOrganisation = $form['CertificateOrganisation']->getData();
            $bankAccountInfo = $form['BankAccountInformation']->getData();
            for($i=0;$i<5;$i++){
                $award = 'Awards'.$i;
                if(!is_null($form[$award]->getData())){
                    $award_document[$i] = $form[$award]->getData();
                }
            }

            if(!is_null($certificateOrganisation)){
                $uploadService->UploadRequestOrganisationDocumentByType(DocumentType::Certificate_organisation,$certificateOrganisation, $user,null);
                //  dd($uploadService);
            }
            if(!is_null($bankAccountInfo)){
                $uploadService->UploadRequestOrganisationDocumentByType(DocumentType::Bank_account_information,$bankAccountInfo, $user,null);
                //  dd($uploadService);
            }

            for($i=0;$i< sizeof($award_document);$i++){
                if(!is_null($award_document[$i])){
                    $uploadService->UploadRequestOrganisationDocumentByType(DocumentType::Awards_justification,$award_document[$i], $user,null);

                }
            }

            $mailer->sendMailAlertToAdminWhenCreatingOrganisation($user->getUsername());
           $mailer->ThankToCreateOrganisation($user);

            $this->addFlash('success','Yêu cầu tạo tài khoản cho phép đăng dự án của bạn đã gửi tới chúng tôi. Chúng tôi sẽ kiểm tra và gửi phản hồi lại cho bạn trong thời gian 24h');
            return $this->redirectToRoute('app_homepage');
        }


        return $this->render('organisation/create_organisation.html.twig', [
            'userInfo' => $this->getUser(),
            'form' => $form->createView()
        ]);
    }


    /**
     * @Route("/edit_organisation_info/{id}", name="app_edit_organisation_info")
     */
    public function editInformationOrganisation($id, EntityManagerInterface $em, Request $request, Mailer $mailer){

        $user = $em->getRepository(User::class)->findOneBy([
            'id' => $id
        ]);
        if($this->getUser()->getId() != $id){
            $this->addFlash("echec", "Xin lỗi bạn. Bạn không được quyền truy cập vào trang này.");
            return $this->redirectToRoute("app_homepage");
        }else{


            $userDocumentId = [DocumentType::Certificate_organisation,DocumentType::Bank_account_information];
            // $userdocumentId  = $userDocument->getId();

            for($i = 0; $i <sizeof($userDocumentId); $i++){
                $document[$i] = $em->getRepository(RequestOrganisationDocument::class)->findLastDocumentByUserIdAndTypeDoc($id,$userDocumentId[$i]);
            }

            $award_documents = $em->getRepository(RequestOrganisationDocument::class)->findAllDocumentByUserId($id,DocumentType::Awards_justification);


            $infoOrganisaton = $em->getRepository(RequestOrganisationInfo::class)->findOneBy([
                'user' => $id
            ]);

            $form = $this->createForm(OrganisationInfoType::class,$infoOrganisaton);
            $form->handleRequest($request);
            if($form->isSubmitted() && $form->isValid()){
                $updateInfo = $form->getData();
                $em->persist($updateInfo);
                $em->flush();

                $requestInfo = $em->getRepository(RequestOrganisationInfo::class)->findOneBy([
                    'user' => $id
                ]);
                $status = $em->getRepository(RequestStatus::class)->findOneBy([
                    'id' => RequestStatus::Request_Sent
                ]);
                $requestInfo->setRequestStatus($status);
                $em->persist($requestInfo);
                $em->flush();

                $arrayRole = $user->getRoles();


                if(in_array("ROLE_ORGANISATION",$arrayRole)){
                    $this->addFlash("success", "Thông tin của tổ chức đã được thay đổi thành công");
                    return $this->redirectToRoute('app_edit_organisation_info', [
                        'id' => $id
                    ]);
                }else{
                    $user->setAskOrganisation(true);
                    $mailer->sendMailAlertToAdminWhenCreatingOrganisation($user->getUsername());
                    $mailer->ThankToCreateOrganisation($user);
                    $this->addFlash("success", "Thông tin của bạn đã cập nhật thành công và được gửi cho chúng tôi. Cảm ơn bạn");
                    return $this->redirectToRoute("app_homepage");
                }


            }


            return $this->render('organisation/edit_info_organisation.html.twig',[
                'userInfo' => $this->getUser(),
                'document' => $document,
                'award_document' => $award_documents,
                'form' => $form->createView()
            ]);
        }
    }


    /**
     * @Route("/edit_document_organisation/{id}", name="app_edit_document_organisation")
     */
    public function editDocumentOrganisation($id, EntityManagerInterface $em, Request $request, UploadService $uploadService, Mailer $mailer){

        $user = $em->getRepository(User::class)->findOneBy([
            'id' => $id
        ]);
        if($this->getUser()->getId() != $id){
            $this->addFlash("echec", "Xin lỗi bạn. Bạn không được quyền truy cập vào trang này.");
            return $this->redirectToRoute("app_homepage");
        }

        $form = $this->createForm(EditDocumentOrganisationType::class);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){

            for($i=0;$i<10;$i++){
                $doc = 'Document'.$i;
                $docType = 'DocType'.$i;
                if(!is_null($form[$doc]->getData()) and !is_null($form[$docType]->getData())){
                    $document[$i] = $form[$doc]->getData();
                    $documentType[$i] = $form[$docType]->getData();
                }
            }

            for($i=0;$i< sizeof($document);$i++){
                // dd($documentType[$i]);
                switch ($documentType[$i]) {
                    case 'Certificate_organisation':
                        $type = DocumentType::Certificate_organisation;
                        break;
                    case 'Awards_justification':
                        $type = DocumentType::Awards_justification;
                        break;
                    case 'Bank_account_information':
                        $type = DocumentType::Bank_account_information;
                        break;
                }

                if(!is_null($document[$i])){
                    $existingFilename = $em->getRepository(RequestOrganisationDocument::class)->findLastDocumentByUserIdAndTypeDoc($id,$type);
                    $uploadService->UploadRequestOrganisationDocumentByType($type,$document[$i], $this->getUser(),$existingFilename->getFilename());

                }else{
                    $this->addFlash("echec", "Bạn cần đính kèm file và chọn dạng tài liệu bạn muốn thay đổi cho tài liệu");
                    return $this->redirectToRoute("app_edit_document_organisation", [
                        'id' => $id
                    ]);
                }
            }


            $requestInfo = $em->getRepository(RequestOrganisationInfo::class)->findOneBy([
                'user' => $id
            ]);
            $status = $em->getRepository(RequestStatus::class)->findOneBy([
                'id' => RequestStatus::Request_Sent
            ]);
            $requestInfo->setRequestStatus($status);
            $em->persist($requestInfo);
            $em->flush();

            $arrayRole = $user->getRoles();


            if(in_array("ROLE_ORGANISATION",$arrayRole)){
                $this->addFlash('success','Cảm ơn bạn. Các tài liệu liên quan tới tổ chức đã được lưu lại trên hệ thống');
                return $this->redirectToRoute('app_my_organisation');
            }else{
                $user->setAskOrganisation(true);
                $mailer->sendMailAlertToAdminWhenCreatingOrganisation($user->getUsername());
                $mailer->ThankToCreateOrganisation($user);
                $this->addFlash("success", "Thông tin của bạn đã cập nhật thành công và được gửi cho chúng tôi. Cảm ơn bạn");
                return $this->redirectToRoute('app_edit_organisation_info', [
                    'id' => $id
                ]);
            }

        }

        return $this->render('organisation/edit_document_organisation.html.twig',[
            'userInfo' => $this->getUser(),
            'form' => $form->createView()
        ]);

    }

    /**
     * @Route("/download/user/document/{id}", name="app_download_user_document",methods={"GET"})
     */
    public function DownloadUserDocument(RequestOrganisationDocument $requestOrganisationDocument, UploadService $uploadService, EntityManagerInterface $em){


        if($requestOrganisationDocument->getUser() == $this->getUser() || in_array("ROLE_ADMIN",$this->getUser()->getRoles())){
            $response = new StreamedResponse(function() use ($requestOrganisationDocument, $uploadService) {
                $outputStream = fopen('php://output', 'wb');
                $fileStream = $uploadService->readStream($requestOrganisationDocument->getUploadsDownloadDocumentPath(), true);
                stream_copy_to_stream($fileStream, $outputStream);
            });
            $response->headers->set('Content-Type', $requestOrganisationDocument->getMimeType());

            // Forced download instead of show in the new table
            $disposition = HeaderUtils::makeDisposition(
                HeaderUtils::DISPOSITION_ATTACHMENT,
                $requestOrganisationDocument->getOriginalFilename()
            );
            $response->headers->set('Content-Disposition', $disposition);

            return $response;
        }else{
            $this->addFlash("echec","Bạn không có quyền tải tài liệu này");
            return $this->redirectToRoute("app_homepage");
        }



    }
}