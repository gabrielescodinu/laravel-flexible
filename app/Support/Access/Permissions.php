<?php

namespace App\Support\Access;

final class Permissions
{
    public const MANAGE_PAGES = 'manage_pages';
    public const MANAGE_ARTICLES = 'manage_articles';
    public const MANAGE_CATEGORIES = 'manage_categories';
    public const MANAGE_MEDIA = 'manage_media';
    public const MANAGE_MENUS = 'manage_menus';
    public const MANAGE_USERS = 'manage_users';
    public const MANAGE_PRODUCTS = 'manage_products';
    public const MANAGE_SETTINGS = 'manage_settings';

    /**
     * @return array<int, array{name: string, label: string, group: string}>
     */
    public static function definitions(): array
    {
        return [
            [
                'name' => self::MANAGE_PAGES,
                'label' => 'Modifica pagine',
                'group' => 'Contenuti',
            ],
            [
                'name' => self::MANAGE_ARTICLES,
                'label' => 'Modifica articoli',
                'group' => 'Blog',
            ],
            [
                'name' => self::MANAGE_CATEGORIES,
                'label' => 'Modifica categorie',
                'group' => 'Blog',
            ],
            [
                'name' => self::MANAGE_MEDIA,
                'label' => 'Gestione media library',
                'group' => 'Asset',
            ],
            [
                'name' => self::MANAGE_MENUS,
                'label' => 'Modifica menu visitatori',
                'group' => 'Navigazione',
            ],
            [
                'name' => self::MANAGE_USERS,
                'label' => 'Gestione utenti, ruoli e permessi',
                'group' => 'Amministrazione',
            ],
            [
                'name' => self::MANAGE_PRODUCTS,
                'label' => 'Gestione prodotti ecommerce',
                'group' => 'Shop',
            ],
            [
                'name' => self::MANAGE_SETTINGS,
                'label' => 'Gestione impostazioni sito',
                'group' => 'Amministrazione',
            ],
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function allNames(): array
    {
        return array_map(
            static fn (array $definition): string => $definition['name'],
            self::definitions()
        );
    }

    /**
     * @return array<int, array{name: string, slug: string, description: string, permissions: array<int, string>}>
     */
    public static function roleTemplates(): array
    {
        return [
            [
                'name' => 'Administrator',
                'slug' => 'administrator',
                'description' => 'Accesso completo al pannello.',
                'permissions' => self::allNames(),
            ],
            [
                'name' => 'Editor',
                'slug' => 'editor',
                'description' => 'Gestisce pagine, articoli e categorie.',
                'permissions' => [
                    self::MANAGE_PAGES,
                    self::MANAGE_ARTICLES,
                    self::MANAGE_CATEGORIES,
                    self::MANAGE_PRODUCTS,
                ],
            ],
            [
                'name' => 'Media Manager',
                'slug' => 'media-manager',
                'description' => 'Gestisce media e menu visitatori.',
                'permissions' => [
                    self::MANAGE_MEDIA,
                    self::MANAGE_MENUS,
                ],
            ],
            [
                'name' => 'Author',
                'slug' => 'author',
                'description' => 'Può creare e aggiornare articoli.',
                'permissions' => [
                    self::MANAGE_ARTICLES,
                ],
            ],
        ];
    }
}
