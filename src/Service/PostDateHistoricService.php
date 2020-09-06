<?php


namespace App\Service;


use App\Entity\Post;
use App\Entity\PostDateHistoric;
use App\Entity\PostDateType;
use App\Entity\PostDocument;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class PostDateHistoricService
{

    private $em;
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function InsertNewPostDateHistorical(Post $post, User $user, $PostDateTypeID, ?PostDocument $postDocument)
    {
        $repoPostDate = $this->em->getRepository(PostDateType::class);
        $postDate = $repoPostDate->findOneBy([
            'id' => $PostDateTypeID
        ]);
        //dd($postDate);
        $postHistoric = new PostDateHistoric();
        $postHistoric->setPost($post)
            ->setUser($user)
            ->setPostDateType($postDate)
            ->setDate(new \DateTime('now'));
        if(!is_null($postDocument)){
            $postHistoric->setPostDocument($postDocument);
        }
        $this->em->persist($postHistoric);
        $this->em->flush();
    }
}