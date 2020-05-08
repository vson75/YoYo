<?php

namespace App\Controller;

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
    public function index()
    {
        return $this->render('user_profil/profil.html.twig', [
            'controller_name' => 'UserProfilController',
        ]);
    }
}
