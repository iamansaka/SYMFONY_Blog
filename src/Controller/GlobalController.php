<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\ArticleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class GlobalController extends AbstractController
{
    /**
     * @Route("/", name="global")
     */
    public function index(
        ArticleRepository $articleRepository, 
        PaginatorInterface $paginatorInterface, 
        Request $request
    ): Response {
        // $articles = $articleRepository->findAll();
        $articles = $paginatorInterface->paginate(
            $articleRepository->findAllPagination(),
            $request->query->getInt('page', 1), /*page number*/
            2 /*limit per page*/
        );
        return $this->render('global/index.html.twig', [
            'articles' => $articles,
        ]);
    }

    /**
     * @Route("/profile/{id}", name="profile", methods={"GET"})
     */
    public function profile(User $user): Response
    {
        return $this->render('global/profile.html.twig', [
            'profile' => $user,
        ]);
    }

    /**
     * @Route("/profile/{id}/modification", name="profile_edit", methods={"GET", "PUT"})
     */
    public function profileModification(
        User $user, 
        Request $request,
        EntityManagerInterface $entityManagerInterface
    ): Response {
        $form = $this->createForm(UserType::class, $user, [
            'method' => 'put'
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManagerInterface->flush();

            $this->addFlash('success', 'Le profile a bien été modifié.');
    
            return $this->redirectToRoute('profile', [
                'id' => $user->getId()
            ]);
        }

        return $this->render('/global/profil.edit.html.twig', [
            'profile' => $user,
            'form_edit' => $form->createView(),
        ]);
    }
}
