<?php

namespace App\Controller;


use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;




/**
 * @IsGranted("ROLE_USER")
 */
class UserProfilController extends AbstractController
{

    /**
     * @Route("/profil", name="app_profil")
     */
    public function index(EntityManagerInterface $em)
    {
        // use the methode getUser() existe in AbstractController
        $userEmail = $this->getUser()->getUsername();

        $repository = $em->getRepository(User::class);
        $userInfo = $repository->findOneBy(['email' => $userEmail]);

      //  dd($userInfo);
        return $this->render('user_profil/user_profil.html.twig', [
            'controller_name' => 'UserProfilController',
            'userInfo' => $userInfo,
        ]);
    }

    /**
     * @Route("/profil_document")
     */
    public function userDocument(){

    }
}
