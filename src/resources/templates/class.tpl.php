<?php

{{ namespace }}

class {{ class }} {{ extends }}
{
    public static function make($params = []): {{ model }}
    {
        return factory({{ model }}::class)->make($params);
    }
}
