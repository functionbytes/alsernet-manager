<?php

namespace App\Library\Traits;

trait HasUid
{
    /**
     * Register the UID generation listener
     * Call this from the model's boot method
     */
    protected static function bootHasUid(): void
    {
        static::creating(function ($item) {
            if (is_null($item->uid)) {
                $item->generateUid();
            }
        });
    }

    public static function findByUid($uid)
    {
        return self::where('uid', '=', $uid)->first();
    }

    public function generateUid(): void
    {
        $this->uid = uniqid();
    }

    public function getUid(): ?string
    {
        return $this->uid;
    }
}
