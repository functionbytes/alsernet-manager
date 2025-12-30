<?php

namespace PhpOffice\PhpSpreadsheet\Calculation\Logical;

class Boolean
{
    /**
     * TRUE.
     *
     * Return the boolean TRUE.
     *
     * Excel Function:
     *        =TRUE()
     *
     * @return bool True
     */
    public static function true(): bool
    {
        return true;
    }

    /**
     * FALSE.
     *
     * Return the boolean FALSE.
     *
     * Excel Function:
     *        =FALSE()
     *
     * @return bool False
     */
    public static function false(): bool
    {
        return false;
    }
}
