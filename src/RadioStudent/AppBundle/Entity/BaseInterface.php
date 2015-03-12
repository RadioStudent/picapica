<?php

namespace RadioStudent\AppBundle\Entity;

interface BaseInterface {
    public static function mapFieldsToElastic();

    public function getFlat();
}