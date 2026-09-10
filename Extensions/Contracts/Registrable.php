<?php

namespace Modules\ClickupIntegration\Extensions\Contracts;

interface Registrable {
    /**
     * Indicates this is a registrable instance
     *
     * @return void
     */
    public function register();

    /**
     * Indicates whether an external dependency is needed to register
     *
     * @return bool
     */
    public function canRegister();
}
