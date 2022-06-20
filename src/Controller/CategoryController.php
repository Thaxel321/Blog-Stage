<?php

namespace App\Controller;

use App\Entity\Category;
use App\Form\CategoryFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CategoryController extends AbstractController
{
    /**
     * Permet d'ajouter et modifier des categories
     *
     * @param Category|null $category
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return Response
     *
     * @Route("/category/new", name="new_category")
     * @Route("/category/edit/{id}", name="edit_category")
     */
    public function form_category(Category $category= null, Request $request, EntityManagerInterface $manager): Response
    {
        if(!$category) {
            $category = new Category();
            $label = 'Ajouter Categorie';
        }
        else {
            $label = 'Modifier Categorie';
        }

        $form = $this->createForm(CategoryFormType::class, $category);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $category = $form->getData();

            $manager->persist($category);
            $manager->flush();

            return $this->redirectToRoute('blog_index');
        }

        return $this->renderForm('category/new_category.html.twig', [
            'form' => $form->createView(),
            'label' => $label,
        ]);
    }
}
