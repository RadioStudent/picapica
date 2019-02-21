<?php

namespace RadioStudent\AppBundle\Entity;

abstract class BaseEntity implements BaseInterface {

    public final static function fieldsToElastic($data, $type = 'default')
    {
        if ($data == null) {
            return null;
        }

        $ret = [];
        $map = static::mapFieldsToElastic($type);

        if ($type == 'default') {
            foreach ($data as $idx => $fields) {
                $arr = [];
                foreach ($fields as $key => $val) {
                    $arr[isset($map[$key]) ? $map[$key] : $key] = $val;
                }
                $ret[] = $arr;
            }

        } elseif ($type == 'sort') {
            foreach ($data as $field => $dir) {
                $ret[isset($map[$field]) ? $map[$field] : $field] = $dir;
            }
        }

        return $ret;
    }
}
