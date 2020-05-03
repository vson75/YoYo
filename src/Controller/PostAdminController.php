<?php


namespace App\Controller;


use App\Entity\Post;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PostAdminController extends AbstractController
{
    /**
     * @Route("/admin/post/new")
     */
    public function new(EntityManagerInterface $em){
        $post = new Post();
        $post->setTitle('Saving the world together')
            ->setSlug('Saving-the-world-'.rand(1,100))
            ->setContent(<<<EOF
Spicy **jalapeno bacon** ipsum dolor amet veniam shank in dolore. Ham hock nisi landjaeger cow,
lorem proident [beef ribs](https://google.com/) aute enim veniam ut cillum pork chuck picanha. Dolore reprehenderit
labore minim pork belly spare ribs cupim short loin in. Elit l'exercitation eiusmod dolore cow
turkey shank eu pork belly meatball non cupim...azae
EOF
            )
            ->setPublishedAt(new \DateTime('now'))
            ->setAuthor('Viet Son')
            ->setUpVote(rand(1,100))
            ->setImageFilename('com_co_thit.jpg');

        $em->persist($post);
        $em->flush();
        return new Response('the new insert is done ');
    }


}