<?php

namespace Octave\PasswordBundle\Controller;

use Doctrine\ORM\EntityManager;
use FOS\UserBundle\Model\UserInterface;
use FOS\UserBundle\Model\UserManager;
use Octave\PasswordBundle\Form\ChangePasswordType;
use Octave\PasswordBundle\Model\ResetUserInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PasswordChangeController extends AbstractController
{
    /**
     * @param Request $request
     * @param UserManager $userManager
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function changePassword(Request $request, UserManager $userManager)
    {
        /** @var ResetUserInterface $user */
        $user = $this->getUser();

        $form = $this->createForm(ChangePasswordType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $user->setPasswordChangedAt(new \DateTime());
            $userManager->updateUser($user);
        }

        return $this->render('change_password.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @param $token
     * @param Request $request
     * @param UserManager $userManager
     * @param EntityManager $entityManager
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function renewPassword($token, Request $request, UserManager $userManager, EntityManager $entityManager)
    {
        if (!$token) {
            throw new NotFoundHttpException();
        }

        /** @var ResetUserInterface $user */
        $user = $entityManager->getRepository($this->getParameter('octave.password.user.class'))
            ->findOneBy(['passwordChangeToken' => $token]);
        if (!$user) {
            throw new NotFoundHttpException();
        }

        $form = $this->createForm(ChangePasswordType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPasswordChangedAt(new \DateTime());
            $userManager->updateUser($user);
        }

        return $this->render('renew_password.html.twig', [
            'form' => $form->createView()
        ]);
    }
}