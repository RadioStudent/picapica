<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as Extra;
use Symfony\Component\HttpFoundation\Request;

class TestController extends Controller
{
    /**
     * @Extra\Route("/test")
     * @Extra\Template()
     */
    public function indexAction()
    {
        $artistsRepo = $this->getDoctrine()->getRepository('RadioStudentAppBundle:Track');
        $tracks = $artistsRepo->findBy(['id' => [46, 5]]);

        var_dump($tracks[0]->getDuration());

        return $this->render(
            '::index.html.twig',
            [
                'data' => $tracks
            ]
        );
    }

    /**
     *
     * @Extra\Route("/test2/")
     * @Extra\Template()
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
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


        return $this->render(
            '::index.html.twig',
            [
                'data' => $data
            ]
        );
    }

}
