<?php

namespace Solar\Object\Property;

class Visibility
{
    const ALL = null;

    const PARENT_ACCESSIBLE = \ReflectionProperty::IS_PUBLIC | \ReflectionProperty::IS_PUBLIC;

    const PRIVATE = \ReflectionProperty::IS_PRIVATE;

    const PROTECTED = \ReflectionProperty::IS_PROTECTED;

    const PUBLIC = \ReflectionProperty::IS_PUBLIC;
}