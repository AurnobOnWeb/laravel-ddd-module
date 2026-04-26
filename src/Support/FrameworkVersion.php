<?php

declare(strict_types=1);

namespace Aurnob\LaravelDddModular\Support;

final class FrameworkVersion
{
    public function __construct(
        private readonly string $version,
    ) {
    }

    public static function fromApplication(object $app): self
    {
        $version = method_exists($app, 'version') ? (string) $app->version() : '0.0.0';

        return new self($version);
    }

    public function full(): string
    {
        return $this->version;
    }

    public function major(): int
    {
        preg_match('/(\d+)/', $this->version, $matches);

        return (int) ($matches[1] ?? 0);
    }

    public function atLeast(int $major): bool
    {
        return $this->major() >= $major;
    }
}
