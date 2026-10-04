<?php

namespace Lerd\Debug\Contracts;

/**
 * Who the request ran as: the user's id and email, a display name when there
 * is one, and the guard or firewall that authenticated them.
 */
interface AuthEntry extends Trackable
{
    public function id(): string;

    public function email(): ?string;

    public function name(): ?string;

    public function guard(): ?string;
}
