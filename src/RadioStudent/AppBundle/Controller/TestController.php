<?php

namespace RadioStudent\AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as Extra;

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
}
