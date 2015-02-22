<?php

namespace RadioStudent\AppBundle\Controller\API;

use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as REST;
use JMS\Serializer\SerializationContext;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class ArtistController
 * @package RadioStudent\AppBundle\Controller\API
 *
 * @REST\Prefix("/api/v1")
 * @REST\NamePrefix("api_1_")
 *
 * @REST\RouteResource("Artist")
 */
class ArtistController extends FOSRestController
{
    /**
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function cgetAction(Request $request)
    {
        $search  = $request->query->get('search', null);
        $page    = $request->query->get('page', 1);
        $results = $request->query->get('results', 10);

        $repo = $this
            ->container
            ->get('search.repository.artist');

        $data = $repo
            ->search($search, $page, $results)
            ->getCurrentPageResults();

        $view = $this
            ->view($data, 200)
            ->setSerializationContext(
                SerializationContext::create()->setGroups(["artists"])
            );

        return $this->handleView($view);
    }

    public function getAction($id)
    {
        $repo = $this
            ->container
            ->get('doctrine.orm.entity_manager')
            ->getRepository('RadioStudentAppBundle:Artist');

        $artist = $repo->find($id);

        $view = $this
            ->view($artist, 200)
            ->setSerializationContext(
                SerializationContext::create()->setGroups(["artist"])
            );

        return $this->handleView($view);
    }}