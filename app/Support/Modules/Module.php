<?php

declare(strict_types=1);

namespace App\Support\Modules;

/**
 * Read-only description of a module, loaded from Modules/<Name>/module.json.
 */
final class Module
{
    /**
     * @param  array<int, string>  $dependencies
     * @param  array<int, array<string, mixed>>  $menu
     * @param  array<string, array<int, string>>  $permissions  entity => actions
     */
    public function __construct(
        public readonly string $name,
        public readonly string $key,
        public readonly string $label,
        public readonly string $description,
        public readonly string $version,
        public readonly string $path,
        public readonly string $provider,
        public readonly bool $core,
        public readonly bool $enabledByDefault,
        public readonly int $order,
        public readonly string $icon,
        public readonly array $dependencies,
        public readonly array $menu,
        public readonly array $permissions,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(string $path, array $data): self
    {
        $name = (string) $data['name'];

        return new self(
            name: $name,
            key: (string) ($data['key'] ?? strtolower($name)),
            label: (string) ($data['label'] ?? $name),
            description: (string) ($data['description'] ?? ''),
            version: (string) ($data['version'] ?? '1.0.0'),
            path: $path,
            provider: (string) ($data['provider'] ?? "Modules\\{$name}\\Providers\\{$name}ServiceProvider"),
            core: (bool) ($data['core'] ?? false),
            enabledByDefault: (bool) ($data['enabled_by_default'] ?? true),
            order: (int) ($data['order'] ?? 100),
            icon: (string) ($data['icon'] ?? 'squares'),
            dependencies: array_values($data['dependencies'] ?? []),
            menu: array_values($data['menu'] ?? []),
            permissions: $data['permissions'] ?? [],
        );
    }

    /**
     * Flattened permission names: "{module}.{entity}.{action}".
     *
     * @return array<int, string>
     */
    public function permissionNames(): array
    {
        $names = [];
        foreach ($this->permissions as $entity => $actions) {
            foreach ($actions as $action) {
                $names[] = "{$this->key}.{$entity}.{$action}";
            }
        }

        return $names;
    }
}
