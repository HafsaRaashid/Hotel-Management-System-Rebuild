<?php

// DI/composition pillar: every application service provider is registered
// here. This is the mechanism only - no provider below binds or boots any
// domain-specific behaviour, per the foundation-only boundary.
return [
    App\Providers\AppServiceProvider::class,
];
