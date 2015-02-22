<?php

namespace RadioStudent\AppBundle\Controller\API;

use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as REST;
use JMS\Serializer\SerializationContext;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class AutoCompleteController
 * @package RadioStudent\AppBundle\Controller\API
 *
 * @REST\Prefix("/api/v1")
 * @REST\NamePrefix("api_1_")
 *
 * @REST\RouteResource("AutoComplete")
 */
class AutoCompleteController extends FOSRestController
{
    /**
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @REST\Get("/ac", name="get_ac", options={ "method_prefix" = false})
     */
    public function getAction(Request $request)
    {
        $search = $request->query->get('q', null);

        $artistData = $this->container->get('search.repository.artist')->quickSearch($search, 10);
        $albumData  = $this->container->get('search.repository.album')->quickSearch($search, 10);
        $trackData  = $this->container->get('search.repository.track')->quickSearch($search, 10);

        $data = [
            'artists' => $artistData,
            'albums'  => $albumData,
            'tracks'  => $trackData,
        ];

        $view = $this
            ->view($data, 200)
            ->setSerializationContext(
                SerializationContext::create()->setGroups(["tracks"])
            );

        return $this->handleView($view);
    }
}