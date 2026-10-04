<?php

namespace Lerd\Debug;

use Lerd\Debug\Contracts\AuthEntry;
use Lerd\Debug\Rendering\Renderers;

/**
 * Who the request ran as, shown in the request's header in lerd.
 */
class AuthUser implements AuthEntry
{
    public function __construct(
        protected string $id,
        protected ?string $email = null,
        protected ?string $name = null,
        protected ?string $guard = null,
    ) {
    }

    public function type(): string
    {
        return 'auth';
    }

    public function id(): string
    {
        return $this->id;
    }

    public function email(): ?string
    {
        return $this->email;
    }

    public function name(): ?string
    {
        return $this->name;
    }

    public function guard(): ?string
    {
        return $this->guard;
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return Renderers::latest()->render($this);
    }
}
