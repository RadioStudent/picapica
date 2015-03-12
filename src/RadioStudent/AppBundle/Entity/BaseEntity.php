<?php

namespace RadioStudent\AppBundle\Entity;

abstract class BaseEntity implements BaseInterface {

    public final static function fieldsToElastic($search)
    {
        if ($search == null) {
            return null;
        }

        $map = static::mapFieldsToElastic();

        $ret = [];
        foreach ($search as $idx=>$fields) {
            $arr = [];
            foreach ($fields as $key=>$val) {
                $arr[isset($map[$key])? $map[$key]: $key] = $val;
            }
            $ret[] = $arr;
        }

        return $ret;
    }
}