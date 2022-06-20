<?php

namespace App\Controller;

use App\Data\SearchData;
use App\Entity\Article;
use App\Entity\Comment;
use App\Form\ArticleFormType;
use App\Form\CommentFormType;
use App\Form\SearchFormType;
use App\Repository\ArticleRepository;
use App\Repository\CategoryRepository;
use App\Repository\CommentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

/**
 * @Route("", name="blog_")
 */

class BlogController extends AbstractController
{

    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    /**
     * Permet de lister les articles
     *
     * @param ArticleRepository $repo
     * @param Request $request
     * @return Response
     *
     * @Route("/", name="index")
     */
    public function index(ArticleRepository $repo, Request $request)
    {
        $data = new SearchData();
        $form = $this->createForm(SearchFormType::class, $data);
        $form->handleRequest($request);
        $articles = $repo->findSearch($data);
        return $this->render('blog/index.html.twig', [
            'articles' => $articles,
            'form' => $form->createView()
        ]);
    }

    /**
     * Permet d'ajouter, modifier un article et de lier une categorie
     *
     * @param Article|null $article
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @param CategoryRepository $repo
     * @return RedirectResponse|Response
     *
     * @IsGranted("ROLE_USER")
     * @Route("/article/new", name="add_article")
     * @Route("/article/edit/{id}", name="edit_article")
     */

    public function form_article(Article $article = null, Request $request,
                                 EntityManagerInterface $manager, CategoryRepository $repo)
    {
        date_default_timezone_set('Europe/Paris');
        if(!$article) {
            $article = new Article();
            $article->setCreateAt(new \DateTime());
            $user = $this->security->getUser();
            /** @var \App\Entity\User $user */
            $article->setUsers($user);
            $label = 'Ajouter Article';
        }
        else {
            $label = 'Modifier Article';
        }

        $form = $this->createForm(ArticleFormType::class, $article);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $article = $form->getData();

            $id = $form->get('categories')->getViewData();
            if ($id == null) {
                $id = 4; // id (no category)
            } else {
                $id = intval($id[0]);
            }
            $category = $repo->find($id);
            $category->addArticle($article);

            $manager->persist($article);
            $manager->flush();

            return $this->redirectToRoute('blog_index');
        }

        return $this->renderForm('article/new_article.html.twig', [
            'form'       => $form->createView(),
            'label'      => $label,
        ]);
    }

    /**
     * Permet de supprimer tout les articles, commentaire lier
     *
     * @param CommentRepository $repoComment
     * @param ArticleRepository $repoArticle
     * @param CategoryRepository $repoCategory
     * @param EntityManagerInterface $manager
     * @return RedirectResponse
     *
     * @IsGranted("ROLE_USER")
     * @Route("/article/removeAll", name="delete_all")
     */
    public function deleteAllArticles(CommentRepository $repoComment, ArticleRepository $repoArticle, CategoryRepository $repoCategory, EntityManagerInterface $manager)
    {
        $comments = $repoComment->findAll();
        foreach ($comments as $comment){
            $manager->remove($comment);
        }
        $categories = $repoCategory->findAll();
        $articles = $repoArticle->findAll();
        foreach ($categories as $category) {
            foreach ($articles as $article) {
                $manager->remove($article);
            }
        }
        $manager->flush();

        return $this->redirectToRoute("blog_index");
    }

    /**
     * Permet d'afficher un article avec ses différents commentaires et en ajouter
     *
     * @param Article $article
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return RedirectResponse|Response
     *
     * @Route("/article/{id}", name="show_article")
     */
    public function show_article(Article $article, Request $request, EntityManagerInterface $manager)
    {
        $comments = $article->getComments();
        $count = \count($comments);
        $comments = $count == 0 ? null : $comments;

        // creation comment
        $comment = new Comment();
        $user = $this->security->getUser();
        /** @var \App\Entity\User $user */
        $label = 'Poster';

        $form = $this->createForm(CommentFormType::class, $comment);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $comment->setArticles($article);
            $comment->setAuthor($user);

            $comment = $form->getData();
            $manager->persist($comment);
            $manager->flush();

            return $this->redirectToRoute('blog_show_article', array(
                'id' => $article->getId()
            ));
        }

        return $this->render('article/show_article.html.twig', [
            'id' => $article->getId(),
            'article' => $article,
            'form'    => $form->createView(),
            'label'   => $label,
            'comments'=> $comments,
            ]);
    }

}
