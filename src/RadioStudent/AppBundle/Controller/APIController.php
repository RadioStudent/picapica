<?php

namespace RadioStudent\AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class APIController extends Controller
{
    /**
     * @Route("/api/test")
     * @Template()
     */
    public function indexAction()
    {
        $artistsRepo = $this->getDoctrine()->getRepository('RadioStudentAppBundle:Artist');
        $artists = $artistsRepo->findBy(['id' => [14214, 14215]]);

        var_dump($artists);
        die;

        return $this->render(
            '::index.html.twig',
            [
                'data' => $name
            ]
        );
    }
}
