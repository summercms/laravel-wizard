<?php

use Illuminate\Support\Str;

if (! function_exists('wizard_action')) {
    /**
     * @param  string|array  $method
     */
    function wizard_action($method, array $parameters = [], bool $absolute = true): string
    {
        if (is_string($method) && ! Str::contains($method, '@')) {
            $method = [get_class(request()->route()->getController()), $method];
        }

        return action($method, $parameters, $absolute);
    }
}
