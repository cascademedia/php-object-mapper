<?php

namespace Cascade\Mapper\Map\Reference;

interface ReferenceInterface
{
    /**
     * @param mixed $instance
     * @return mixed
     */
    public function getValue($instance);

    /**
     * @param mixed $instance
     * @param mixed $value
     * @return mixed
     */
    public function setValue($instance, $value);
}
