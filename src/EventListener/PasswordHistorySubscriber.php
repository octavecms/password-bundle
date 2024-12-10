<?php

namespace Octave\PasswordBundle\EventListener;

use Doctrine\ORM\EntityManagerInterface;
use Octave\PasswordBundle\Entity\PasswordHistory;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class PasswordHistorySubscriber implements EventSubscriberInterface
{
    private EntityManagerInterface $entityManager;
    private bool $keepHistory;

    public function __construct(EntityManagerInterface $entityManager, bool $keepHistory)
    {
        $this->entityManager = $entityManager;
        $this->keepHistory = $keepHistory;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            FormEvents::POST_SUBMIT => 'onPostSubmit',
        ];
    }

    public function onPostSubmit(FormEvent $event): void
    {
        if (!$this->keepHistory) {
            return;
        }

        $form = $event->getForm();
        if (!$form->isValid()) {
            return;
        }

        $plainPassword = $form->get('plainPassword')->getData();
        if (!$plainPassword) {
            return;
        }

        $user = $event->getData();

        $passwordHistory = new PasswordHistory($user, hash('sha256', $plainPassword));

        $this->entityManager->persist($passwordHistory);
        $this->entityManager->flush();
    }
}