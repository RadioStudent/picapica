<?php

namespace RadioStudent\AppBundle\Controller\API;

use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as REST;
use RadioStudent\AppBundle\Entity\Tracklist;
use RadioStudent\AppBundle\Entity\TracklistTrack;
use Symfony\Component\HttpFoundation\Request;

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
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function cgetAction(Request $request)
    {
        //TODO: serve tracklists
        $repo = $this
            ->container
            ->get('doctrine.orm.entity_manager')
            ->getRepository('RadioStudentAppBundle:Tracklist');

        $tracklists = $repo->findAll();

        $data = [];
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
        $repo = $this
            ->container
            ->get('doctrine.orm.entity_manager')
            ->getRepository('RadioStudentAppBundle:Tracklist');

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
        $params = $request->request;

        $em = $this->container->get('doctrine.orm.entity_manager');

        $tracklist = new Tracklist();
        $tracklist->setName($params->get("name"));
        $tracklist->setAuthor($em->getRepository('RadioStudentAppBundle:Author')->find($params->get("authorId")));
        $tracklist->setTerm($em->getRepository('RadioStudentAppBundle:Term')->find($params->get("termId")));
        $tracklist->setDate(new \DateTime($params->get("date")));

        $trackIds = [];
        foreach ($params->get("tracks") as $track) {
            $trackIds[] = $track["id"];
        }

        $tracks = $em->getRepository('RadioStudentAppBundle:Track')->findBy(["id" => $trackIds]);

        foreach ($tracks as $i=>$track) {
            $tracklistTrack = new TracklistTrack();
            $tracklistTrack->setTrack($track);
            $tracklistTrack->setTrackNum($i);
            $tracklistTrack->setTracklist($tracklist);

            $em->persist($tracklistTrack); //TODO: cascade persist
        }

        $view = $this->view($em->persist($tracklist), 200);
        $em->flush();

        return $this->handleView($view);
    }

    /**
     * @param $id
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function putAction($id, Request $request)
    {
        $params = $request->request;

        $view = $this->view([$id, $params]);

        return $this->handleView($view);
    }
}