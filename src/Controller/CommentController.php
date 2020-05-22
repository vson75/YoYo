<?php


namespace App\Controller;


use App\Entity\Post;
use App\Form\CommentFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class CommentController extends AbstractController
{
    /**
     * @Route("/comments/{id}/vote/{direction<up|down>}", name="comment_vote", methods="POST")
     * @param $id
     * @param $direction
     * @return JsonResponse
     * this is just a exemple of code. No impact in our project
     */
    public function commentVote($id, $direction){

        if($direction ==='up'){
            $currentVoteCount = rand(7,100);
        }else{
            $currentVoteCount = rand(0,5);
        }


        return $this->json(['votes'=>$currentVoteCount]);
    }

}