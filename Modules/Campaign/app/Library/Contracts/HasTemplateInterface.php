<?php

namespace app\Library\Contracts;

interface HasTemplateInterface
{
    public function isStageExcluded(string $name): bool;
}
