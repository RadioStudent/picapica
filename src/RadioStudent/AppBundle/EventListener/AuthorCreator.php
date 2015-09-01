<?php

namespace RadioStudent\AppBundle\EventListener;

use Doctrine\ORM\EntityManager;
use FOS\UserBundle\Event\FilterUserResponseEvent;
use FOS\UserBundle\FOSUserEvents;
use RadioStudent\AppBundle\Entity\Author;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class AuthorCreator implements EventSubscriberInterface {
    private $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function onRegistrationCompleted(FilterUserResponseEvent $e) {
        $user = $e->getUser();

        $author = new Author();
        $author->setUser($user);
        $author->setName($user->getUsername());

        $this->entityManager->persist($author);
        $this->entityManager->flush();
    }

    public static function getSubscribedEvents()
    {
        return [
            FOSUserEvents::REGISTRATION_COMPLETED => 'onRegistrationCompleted',
        ];
    }
}