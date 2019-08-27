<?php

namespace RadioStudent\AppBundle\Controller\API;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as REST;
use Symfony\Component\HttpFoundation\Request;

use RadioStudent\AppBundle\Entity\Author;
use RadioStudent\AppBundle\Entity\Tracklist;

/**
 * Class TracklistController
 * @package RadioStudent\AppBundle\Controller\API
 *
 * @REST\Prefix("/api/v1")
 * @REST\NamePrefix("api_1_")
 *
 * @REST\RouteResource("Tracklist")
 */
class TracklistController extends FOSRestController
{
    /**
     * @var EntityManager
     */
    var $em;

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function cgetAction()
    {
        /** @var EntityManager $em */
        $em = $this->container->get('doctrine.orm.entity_manager');

        $author = $em->getRepository("RadioStudentAppBundle:Author")->findOneBy(["user" => $this->getUser()]);
        $tracklists = $em->getRepository('RadioStudentAppBundle:Tracklist')->findBy(["author" => $author], ['date' => 'DESC']);

        $data = [];
        /** @var Tracklist $tracklist */
        foreach ($tracklists as $tracklist) {
            $data[] = $tracklist->getFlat("short");
        }

        $view = $this->view($data, 200);

        return $this->handleView($view);
    }

    /**
     * @param $id
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getAction($id)
    {
        /** @var EntityManager $em */
        $em = $this->container->get('doctrine.orm.entity_manager');

        /** @var EntityRepository $repo */
        $repo = $em->getRepository('RadioStudentAppBundle:Tracklist');

        $tracklist = $repo->find($id);

        $view = $this->view($tracklist->getFlat('long'), 200);

        return $this->handleView($view);
    }

    /**
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function postAction(Request $request)
    {
        /** @var EntityManager $em */
        $em = $this->container->get('doctrine.orm.entity_manager');

        $author = $em->getRepository("RadioStudentAppBundle:Author")->findOneBy(["user" => $this->getUser()]);

        // Create author entry if missing
        if (!$author) {
            $author = new Author();
            $author->setUser($this->getUser());
            $author->setName($this->getUser()->getUsername());
            $em->persist($author);
            $em->flush();
        }

        $tracklist = $em->getRepository("RadioStudentAppBundle:Tracklist")->create($author, $request->request);

        $view = $this->view($tracklist->getFlat('long'));

        return $this->handleView($view);
    }

    /**
     * @param Tracklist $tracklist
     * @param Request   $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function putAction(Tracklist $tracklist, Request $request)
    {
        /** @var EntityManager $em */
        $em = $this->container->get('doctrine.orm.entity_manager');

        $em->getRepository("RadioStudentAppBundle:Tracklist")->update($tracklist, $request->request);

        $view = $this->view($tracklist->getFlat('long'));
        return $this->handleView($view);
    }
}
