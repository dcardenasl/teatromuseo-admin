<?php

declare(strict_types=1);

namespace Tests\Support\Fixtures;

final class AdminFixtureFactory
{
    private int $sequence = 0;

    private string $scope;

    public function __construct(string $scope = 'case')
    {
        $normalized = preg_replace('/[^a-z0-9]+/i', '-', strtolower($scope)) ?? 'case';
        $this->scope = substr(trim($normalized, '-'), 0, 16) ?: 'case';
    }

    /** @return list<array{id:int,code:string,label:string,name:string,native_name:string,is_default:bool,is_active:bool}> */
    public function languages(int $count = 3, int $defaultPosition = 0): array
    {
        $languages = [];
        for ($position = 0; $position < $count; $position++) {
            $languages[] = [
                'id' => 100 + $position,
                'code' => $this->localeCode($position),
                'label' => $this->makeValue('language', (string) ($position + 1)),
                'name' => $this->makeValue('language-name', (string) ($position + 1)),
                'native_name' => $this->makeValue('language-native-name', (string) ($position + 1)),
                'is_default' => $position === $defaultPosition,
                'is_active' => true,
            ];
        }

        return $languages;
    }

    /** @param list<array<string, mixed>> $translations */
    public function collection(array $translations): array
    {
        return [
            'id' => 1000 + ++$this->sequence,
            'collection_key' => $this->makeValue('collection-key'),
            'translations' => $translations,
        ];
    }

    /** @return array{id:int,menu_key:string,location:string,is_active:bool,created_at:string} */
    public function menu(string $location = 'header'): array
    {
        return [
            'id' => 2000 + ++$this->sequence,
            'menu_key' => $this->makeValue('menu-key'),
            'location' => $location,
            'is_active' => true,
            'created_at' => '2026-01-01 00:00:00',
        ];
    }

    /** @return array{id:int,menu_id:int,sort_order:int} */
    public function menuItem(int $menuId, int $sortOrder = 1): array
    {
        return [
            'id' => 3000 + ++$this->sequence,
            'menu_id' => $menuId,
            'sort_order' => $sortOrder,
        ];
    }

    /** @return array{id:int,title:string,translations:list<array<string,mixed>>} */
    public function page(array $translations = []): array
    {
        return [
            'id' => $this->id('page'),
            'title' => $this->value('page-title'),
            'translations' => $translations,
        ];
    }

    /** @return array{id:int,title:string} */
    public function entry(): array
    {
        return [
            'id' => $this->id('entry'),
            'title' => $this->value('entry-title'),
        ];
    }

    /** @return array{id:int,form_key:string,translations:list<array<string,mixed>>} */
    public function form(array $translations = []): array
    {
        return [
            'id' => $this->id('form'),
            'form_key' => $this->value('form-key'),
            'translations' => $translations,
        ];
    }

    /** @return array{language_id:int,label:string} */
    public function translation(int $languageId, string $field = 'label'): array
    {
        return [
            'language_id' => $languageId,
            $field => $this->value('translation', $field),
        ];
    }

    public function value(string $role, string $variant = ''): string
    {
        return $this->makeValue($role, $variant);
    }

    public function id(string $role): int
    {
        return 4000 + ++$this->sequence;
    }

    /** @param mixed $data */
    public function response(mixed $data): array
    {
        return [
            'ok' => true,
            'status' => 200,
            'data' => $data,
            'raw' => '',
            'headers' => [],
            'messages' => [],
            'fieldErrors' => [],
        ];
    }

    private function makeValue(string $role, string $variant = ''): string
    {
        ++$this->sequence;
        $parts = ['fixture', $this->scope, $role];
        if ($variant !== '') {
            $parts[] = $variant;
        }

        $parts[] = (string) $this->sequence;

        return strtolower(implode('-', array_map(
            static fn (string $part): string => trim($part, '-_ '),
            $parts,
        )));
    }

    private function localeCode(int $position): string
    {
        return chr(97 + intdiv($position, 26)) . chr(97 + ($position % 26));
    }
}
