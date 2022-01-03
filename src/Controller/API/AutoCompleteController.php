<?php

namespace App\Controller\API;

use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as REST;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class AutoCompleteController
 * @package App\Controller\API
 *
 * @REST\Prefix("/api/v1")
 * @REST\NamePrefix("api_1_")
 *
 * @REST\RouteResource("AutoComplete")
 */
class AutoCompleteController extends AbstractFOSRestController
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
        $size   = $request->query->get('size', 5);

        $artistData = $this->container->get('search.repository.artist')->autoComplete($search, [
//            'name',
            'autocompleteName.autocomplete'
        ], $size);

        $albumData  = $this->container->get('search.repository.album')->autoComplete($search, [
//            'name',
            'name.autocomplete',
            'fid.autocomplete',
            'fid.numeric'
        ], $size);

        $trackData  = $this->container->get('search.repository.track')->autoComplete($search, [
//            'name',
            'name.autocomplete',
            'fid.autocomplete',
            'fid.numeric'
        ], $size);

        $data = [
            'query'   => $search,
            'artists' => $artistData,
            'albums'  => $albumData,
            'tracks'  => $trackData,
        ];

        $view = $this
            ->view($data, 200);

        return $this->handleView($view);
    }
}
