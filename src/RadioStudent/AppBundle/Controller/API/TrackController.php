<?php

namespace RadioStudent\AppBundle\Controller\API;

use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as REST;
use JMS\Serializer\SerializationContext;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class TrackController
 * @package RadioStudent\AppBundle\Controller\API
 *
 * @REST\Prefix("/api/v1")
 * @REST\NamePrefix("api_1_")
 *
 * @REST\RouteResource("Track")
 */
class TrackController extends FOSRestController
{
    /**
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function cgetAction(Request $request)
    {
        $search = $request->query->get('search', null);
        $from   = $request->query->get('from', 0);
        $size   = $request->query->get('size', 10);

        $repo = $this
            ->container
            ->get('search.repository.track');

        $data = $repo->search($search, $from, $size);

        $view = $this
            ->view($data, 200)
            ->setSerializationContext(
                SerializationContext::create()->setGroups(["tracks"])
            );

        return $this->handleView($view);
    }

    public function getAction($id)
    {
        $repo = $this
            ->container
            ->get('doctrine.orm.entity_manager')
            ->getRepository('RadioStudentAppBundle:Track');

        $track = $repo->find($id);

        $view = $this
            ->view($track, 200)
            ->setSerializationContext(
                SerializationContext::create()->setGroups(["track"])
            );

        return $this->handleView($view);
    }
}