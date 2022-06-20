<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserFormType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

/**
 * @IsGranted("ROLE_USER")
 * @Route("/profil", name="user_")
 */
class UserController extends AbstractController
{
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    /**
     * Permet d'editer un utilisateur
     *
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return Response
     *
     * @Route("/edit", name="edit")
     */
    public function edit_user(Request $request, EntityManagerInterface $manager): Response
    {
        $user = $this->security->getUser();
        /** @var \App\Entity\User $user */

        $form = $this->createForm(UserFormType::class, $user);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $user = $form->getData();

            $manager->persist($user);
            $manager->flush();

            return $this->redirectToRoute('blog_index');
        }

        return $this->renderForm('user/editProfil.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
