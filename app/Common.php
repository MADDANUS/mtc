<?php

/**
 * The goal of this file is to allow developers a location
 * where they can overwrite core procedural functions and
 * replace them with their own. This file is loaded during
 * the bootstrap process and is called during the framework's
 * execution.
 *
 * This can be looked at as a `master helper` file that is
 * loaded early on, and may also contain additional functions
 * that you'd like to use throughout your entire application
 *
 * @see: https://codeigniter.com/user_guide/extending/common.html
 */

if (!function_exists('has_role')) {
    function has_role(string $roleCheck): bool {
        $roleSession = session()->get('role');
        if (!$roleSession) return false;
        $roles = array_map('trim', explode(',', $roleSession));
        return in_array($roleCheck, $roles, true);
    }
}

if (!function_exists('has_any_role')) {
    function has_any_role(array $rolesCheck): bool {
        $roleSession = session()->get('role');
        if (!$roleSession) return false;
        $roles = array_map('trim', explode(',', $roleSession));
        return count(array_intersect($rolesCheck, $roles)) > 0;
    }
}
