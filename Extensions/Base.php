<?php

namespace Modules\ClickupIntegration\Extensions;

use Modules\ClickupIntegration\Extensions\Contracts\Registrable;

abstract class Base implements Registrable
{
    /**
     * If defined, registering can only occurs if the dependent class exists
     *
     * @var array
     */
    protected static $dependentClasses = [];

    /**
     * @inheritDoc
     *
     * @return void
     */
    public function register()
    {
        if ($this->canRegister()) {
            $this->load();
        }
    }

    /**
     * Specific extension load function
     *
     * @return void
     */
    abstract protected function load();

    /**
     * @inheritDoc
     *
     * @return boolean
     */
    public function canRegister()
    {
        $canRegister = true;

        /**
         * By default it can register, but if some dependentClass
         * does not exists then we won't register the extension
         */
        foreach (static::$dependentClasses as $dependentClass) {
            $canRegister = $canRegister && class_exists($dependentClass);
        }

        return $canRegister;
    }
}
