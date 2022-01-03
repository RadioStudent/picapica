<?php

namespace App\Entity;

interface BaseInterface {

    public static function mapFieldsToElastic($type);

    public function getFlat($preset = null);

}
