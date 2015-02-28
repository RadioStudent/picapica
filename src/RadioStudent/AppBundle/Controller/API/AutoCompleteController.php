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
        $search = $request->query->get('search', null);
        $size   = $request->query->get('size', 10);

        $artistData = $this->container->get('search.repository.artist')->autoComplete($search, [
            'name',
            'name.autocomplete'
        ], $size);

        $albumData  = $this->container->get('search.repository.album')->autoComplete($search, [
            'name',
            'name.autocomplete',
            'fid.autocomplete',
            'fid.numeric'
        ], $size);

        $trackData  = $this->container->get('search.repository.track')->autoComplete($search, [
            'name',
            'name.autocomplete',
            'fid.autocomplete',
            'fid.numeric'
        ], $size);

        $data = [
            'artists' => $artistData,
            'albums'  => $albumData,
            'tracks'  => $trackData,
        ];

        $view = $this
            ->view($data, 200)
            ->setSerializationContext(
                SerializationContext::create()->setGroups(["autocomplete"])
            );

        return $this->handleView($view);
    }
}