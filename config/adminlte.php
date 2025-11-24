<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Title
    |--------------------------------------------------------------------------
    |
    | Here you can change the default title of your admin panel.
    |
    | For detailed instructions you can look the title section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'title' => 'SPARTAN',
    'title_prefix' => 'SPARTAN LTI | ',
    'title_postfix' => '',

    /*
    |--------------------------------------------------------------------------
    | Favicon
    |--------------------------------------------------------------------------
    |
    | Here you can activate the favicon.
    |
    | For detailed instructions you can look the favicon section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'use_ico_only' => false,
    'use_full_favicon' => false,

    /*
    |--------------------------------------------------------------------------
    | Google Fonts
    |--------------------------------------------------------------------------
    |
    | Here you can allow or not the use of external google fonts. Disabling the
    | google fonts may be useful if your admin panel internet access is
    | restricted somehow.
    |
    | For detailed instructions you can look the google fonts section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'google_fonts' => [
        'allowed' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Admin Panel Logo
    |--------------------------------------------------------------------------
    |
    | Here you can change the logo of your admin panel.
    |
    | For detailed instructions you can look the logo section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'logo' => '<b>SPARTAN</b>',
    'logo_img' => 'img/SPARTAN.png', // Ganti dengan nama file logo Anda
    'logo_img_class' => 'brand-image img-circle elevation-3',
    'logo_img_xl' => null,
    'logo_img_xl_class' => 'brand-image-xs',
    'logo_img_alt' => 'Admin Logo',

    /*
    |--------------------------------------------------------------------------
    | Authentication Logo
    |--------------------------------------------------------------------------
    |
    | Here you can setup an alternative logo to use on your login and register
    | screens. When disabled, the admin panel logo will be used instead.
    |
    | For detailed instructions you can look the auth logo section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'auth_logo' => [
        'enabled' => false, // Aktifkan
        'img' => [
            'path' => 'img/SPARTAN.png', // Ganti dengan logo yang lebih besar
            'alt' => 'SPARTAN Logo',
            'class' => '',
            'width' => 200, // Sesuaikan ukurannya
            'height' => 100, // Sesuaikan ukurannya
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Preloader Animation
    |--------------------------------------------------------------------------
    |
    | Here you can change the preloader animation configuration. Currently, two
    | modes are supported: 'fullscreen' for a fullscreen preloader animation
    | and 'cwrapper' to attach the preloader animation into the content-wrapper
    | element and avoid overlapping it with the sidebars and the top navbar.
    |
    | For detailed instructions you can look the preloader section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'preloader' => [
        'enabled' => true,
        'mode' => 'fullscreen',
        'img' => [
            'path' => 'img/SPARTAN.png',
            'alt' => 'SPARTAN Logo',
            'effect' => 'animation__shake',
            'width' => 200,
            'height' => 200,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | User Menu
    |--------------------------------------------------------------------------
    |
    | Here you can activate and change the user menu.
    |
    | For detailed instructions you can look the user menu section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'usermenu_enabled' => false,
    'usermenu_header' => false,
    'usermenu_header_class' => 'bg-primary',
    'usermenu_image' => false,
    'usermenu_desc' => true,


    /*
    |--------------------------------------------------------------------------
    | Layout
    |--------------------------------------------------------------------------
    |
    | Here we change the layout of your admin panel.
    |
    | For detailed instructions you can look the layout section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Layout-and-Styling-Configuration
    |
    */

    'layout_topnav' => null,
    'layout_boxed' => null,
    'layout_fixed_sidebar' => null,
    'layout_fixed_navbar' => null,
    'layout_fixed_footer' => null,
    'layout_dark_mode' => null,

    /*
    |--------------------------------------------------------------------------
    | Authentication Views Classes
    |--------------------------------------------------------------------------
    |
    | Here you can change the look and behavior of the authentication views.
    |
    | For detailed instructions you can look the auth classes section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Layout-and-Styling-Configuration
    |
    */

    'classes_auth_card' => '',
    'classes_auth_header' => '',
    'classes_auth_body' => '',
    'classes_auth_footer' => '',
    'classes_auth_icon' => '',
    'classes_auth_btn' => 'btn-flat btn-primary',

    /*
    |--------------------------------------------------------------------------
    | Admin Panel Classes
    |--------------------------------------------------------------------------
    |
    | Here you can change the look and behavior of the admin panel.
    |
    | For detailed instructions you can look the admin panel classes here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Layout-and-Styling-Configuration
    |
    */

    'classes_body' => '',
    'classes_brand' => '',
    'classes_brand_text' => '',
    'classes_content_wrapper' => '',
    'classes_content_header' => '',
    'classes_content' => '',
    'classes_sidebar' => 'sidebar-light-yamaha elevation-4',
    'classes_sidebar_nav' => '',
    'classes_topnav'  => 'navbar-white navbar-light',
    'classes_topnav_nav' => 'navbar-expand',
    'classes_topnav_container' => 'container',

    /*
    |--------------------------------------------------------------------------
    | Sidebar
    |--------------------------------------------------------------------------
    |
    | Here we can modify the sidebar of the admin panel.
    |
    | For detailed instructions you can look the sidebar section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Layout-and-Styling-Configuration
    |
    */

    'sidebar_mini' => 'lg',
    'sidebar_collapse' => false,
    'sidebar_collapse_auto_size' => false,
    'sidebar_collapse_remember' => false,
    'sidebar_collapse_remember_no_transition' => true,
    'sidebar_scrollbar_theme' => 'os-theme-light',
    'sidebar_scrollbar_auto_hide' => 'l',
    'sidebar_nav_accordion' => true,
    'sidebar_nav_animation_speed' => 300,

    /*
    |--------------------------------------------------------------------------
    | Control Sidebar (Right Sidebar)
    |--------------------------------------------------------------------------
    |
    | Here we can modify the right sidebar aka control sidebar of the admin panel.
    |
    | For detailed instructions you can look the right sidebar section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Layout-and-Styling-Configuration
    |
    */

    'right_sidebar' => false,
    'right_sidebar_icon' => 'fas fa-cogs',
    'right_sidebar_theme' => 'dark',
    'right_sidebar_slide' => true,
    'right_sidebar_push' => true,
    'right_sidebar_scrollbar_theme' => 'os-theme-light',
    'right_sidebar_scrollbar_auto_hide' => 'l',

    /*
    |--------------------------------------------------------------------------
    | URLs
    |--------------------------------------------------------------------------
    |
    | Here we can modify the url settings of the admin panel.
    |
    | For detailed instructions you can look the urls section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'use_route_url' => false,
    'dashboard_url' => '#',
    'logout_url' => 'logout',
    'login_url' => 'login',
    'register_url' => 'register',
    'password_reset_url' => 'password/reset',
    'password_email_url' => 'password/email',
    'profile_url' => false,
    'disable_darkmode_routes' => false,

    /*
    |--------------------------------------------------------------------------
    | Laravel Asset Bundling
    |--------------------------------------------------------------------------
    |
    | Here we can enable the Laravel Asset Bundling option for the admin panel.
    | Currently, the next modes are supported: 'mix', 'vite' and 'vite_js_only'.
    | When using 'vite_js_only', it's expected that your CSS is imported using
    | JavaScript. Typically, in your application's 'resources/js/app.js' file.
    | If you are not using any of these, leave it as 'false'.
    |
    | For detailed instructions you can look the asset bundling section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Other-Configuration
    |
    */

    'laravel_asset_bundling' => false,
    'laravel_css_path' => 'css/app.css',
    'laravel_js_path' => 'js/app.js',

    /*
    |--------------------------------------------------------------------------
    | Menu Items
    |--------------------------------------------------------------------------
    |
    | Here we can modify the sidebar/top navigation of the admin panel.
    |
    | For detailed instructions you can look here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Menu-Configuration
    |
    */

    // config/adminlte.php

'menu' => [
        ['search' => true, 'topnav' => true],
        ['type' => 'fullscreen-widget', 'topnav_right' => true],
        [
            'text' => 'Dashboard',
            'route'  => 'admin.home',
            'icon' => 'fas fa-fw fa-tachometer-alt',
        ],
        [
            'text' => 'Profil Saya',
            'route'  => 'admin.profile.show',
            'icon' => 'fas fa-fw fa-user',
        ],

        // SUBMENU: Master Data
        [
            'text'    => 'Master Data',
            'icon'    => 'fas fa-fw fa-database',
            'can'     => 'access-master-data',
            'submenu' => [
                ['text' => 'Brand', 'route'  => 'admin.brands.index', 'icon' => 'fas fa-fw fa-copyright', 'can' => 'is-super-admin'],
                ['text' => 'Kategori', 'route'  => 'admin.categories.index', 'icon' => 'fas fa-fw fa-tags', 'can' => 'is-super-admin'],
                ['text' => 'Gudang', 'route'  => 'admin.gudangs.index', 'icon' => 'fas fa-fw fa-warehouse', 'can' => 'is-super-admin'],
                ['text' => 'Rak', 'route'  => 'admin.raks.index', 'icon' => 'fas fa-fw fa-pallet', 'can' => 'is-super-admin'],
                ['text' => 'Supplier', 'route'  => 'admin.suppliers.index', 'icon' => 'fas fa-fw fa-truck', 'can' => 'is-super-admin'],
                ['text' => 'Part', 'route'  => 'admin.parts.index', 'icon' => 'fas fa-fw fa-cogs', 'can' => 'access-master-data'],
            ],
        ],

        // SUBMENU: Transaksi Gudang
        [
            'text'    => 'Transaksi Gudang',
            'icon'    => 'fas fa-fw fa-exchange-alt',
            'can'     => 'access-gudang-transaksi',
            'submenu' => [
                ['text' => 'Purchase Order (PO)', 'route' => 'admin.purchase-orders.index', 'icon' => 'fas fa-fw fa-shopping-cart', 'can' => 'view-purchase-orders'],
                // MENU REKOMENDASI PO DIHAPUS DARI SINI
                ['text' => 'Penerimaan Barang', 'route' => 'admin.receivings.index', 'icon' => 'fas fa-fw fa-box-open', 'can' => 'can-receive'],
                ['text' => 'Penerimaan Mutasi', 'route' => 'admin.mutation-receiving.index', 'icon' => 'fas fa-fw fa-people-carry', 'can' => 'can-receive-mutation'],
                ['text' => 'Quality Control (QC)', 'route' => 'admin.qc.index', 'icon' => 'fas fa-fw fa-check-circle', 'can' => 'can-qc'],
                ['text' => 'Putaway / Penyimpanan', 'route' => 'admin.putaway.index', 'icon' => 'fas fa-fw fa-dolly-flatbed', 'can' => 'can-putaway'],
                ['text' => 'Adjusment Stok', 'route' => 'admin.stock-adjustments.index', 'icon' => 'fas fa-fw fa-edit', 'can' => 'view-stock-management'],
                ['text' => 'Mutasi Gudang', 'route' => 'admin.stock-mutations.index', 'icon' => 'fas fa-fw fa-truck-loading', 'can' => 'view-stock-management'],
                ['text' => 'Stok Karantina', 'route' => 'admin.quarantine-stock.index', 'icon' => 'fas fa-fw fa-shield-virus', 'can' => 'can-process-quarantine'],
                ['text' => 'Retur Pembelian', 'route' => 'admin.purchase-returns.index', 'icon' => 'fas fa-fw fa-undo', 'can' => 'manage-purchase-returns'],
            ],
        ],

        // SUBMENU: Penjualan (Tanpa Menu Konsumen)
        [
            'text' => 'Penjualan',
            'icon' => 'fas fa-fw fa-users',
            'can'  => 'access-penjualan-pelanggan',
            'submenu' => [
                ['text' => 'Daftar Penjualan', 'route' => 'admin.penjualans.index', 'icon' => 'fas fa-fw fa-cash-register', 'can' => 'view-sales'],
                ['text' => 'Retur Penjualan', 'route' => 'admin.sales-returns.index', 'icon' => 'fas fa-fw fa-undo', 'can' => 'view-sales-returns'],
                // MENU KONSUMEN DIHAPUS DARI SINI
            ],
        ],

        // SUBMENU: Laporan (Tetap sama)
        [
            'text'    => 'Laporan',
            'icon'    => 'fas fa-fw fa-chart-pie',
            'can'     => 'view-reports',
            'submenu' => [
                ['text' => 'Kartu Stok', 'route' => 'admin.reports.stock-card'],
                ['text' => 'Stok Gudang', 'route' => 'admin.reports.stock-by-warehouse', 'can' => 'is-kepala-gudang-only'],
                ['text' => 'Jurnal Penjualan', 'route' => 'admin.reports.sales-journal'],
                ['text' => 'Jurnal Pembelian', 'route' => 'admin.reports.purchase-journal'],
                ['text' => 'Nilai Persediaan', 'route' => 'admin.reports.inventory-value'],
                ['text' => 'Analisis Penjualan', 'route' => 'admin.reports.sales-purchase-analysis'],
                ['text' => 'Laporan Stok Keseluruhan', 'route' => 'admin.reports.stock-report', 'can' => 'is-not-kepala-gudang'],
            ],
        ],

        // SUBMENU: Pengaturan
        [
            'text'    => 'Pengaturan',
            'icon'    => 'fas fa-fw fa-cogs',
            'can'     => 'is-super-admin',
            'submenu' => [
                ['text' => 'Pengguna', 'route'  => 'admin.users.index', 'icon' => 'fas fa-fw fa-user-cog'],
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Menu Filters
    |--------------------------------------------------------------------------
    |
    | Here we can modify the menu filters of the admin panel.
    |
    | For detailed instructions you can look the menu filters section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Menu-Configuration
    |
    */

    'filters' => [
        JeroenNoten\LaravelAdminLte\Menu\Filters\GateFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\HrefFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\SearchFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\ActiveFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\ClassesFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\LangFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\DataFilter::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Plugins Initialization
    |--------------------------------------------------------------------------
    |
    | Here we can modify the plugins used inside the admin panel.
    |
    | For detailed instructions you can look the plugins section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Plugins-Configuration
    |
    */

    'plugins' => [
        'Datatables' => [
            'active' => true,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js',
                ],
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdn.datatables.net/1.10.19/js/dataTables.bootstrap4.min.js',
                ],
                [
                    'type' => 'css',
                    'asset' => false,
                    'location' => '//cdn.datatables.net/1.10.19/css/dataTables.bootstrap4.min.css',
                ],
            ],
        ],
        'Select2' => [
            'active' => true,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.min.js',
                ],
                [
                    'type' => 'css',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/css/select2.css',
                ],
                [
                    'type' => 'css',
                    'asset' => true, // Ganti dari false ke true jika sebelumnya false
                    'location' => 'vendor/select2-bootstrap4-theme/select2-bootstrap4.min.css',
                ],
            ],
        ],
        'Chartjs' => [
            'active' => true,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.0/Chart.bundle.min.js',
                ],
            ],
        ],
        'Sweetalert2' => [
            'active' => true,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdn.jsdelivr.net/npm/sweetalert2@8',
                ],
            ],
        ],
        'Pace' => [
            'active' => false,
            'files' => [
                [
                    'type' => 'css',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/pace/1.0.2/themes/blue/pace-theme-center-radar.min.css',
                ],
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/pace/1.0.2/pace.min.js',
                ],
            ],
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | IFrame
    |--------------------------------------------------------------------------
    |
    | Here we change the IFrame mode configuration. Note these changes will
    | only apply to the view that extends and enable the IFrame mode.
    |
    | For detailed instructions you can look the iframe mode section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/IFrame-Mode-Configuration
    |
    */

    'iframe' => [
        'default_tab' => [
            'url' => null,
            'title' => null,
        ],
        'buttons' => [
            'close' => true,
            'close_all' => true,
            'close_all_other' => true,
            'scroll_left' => true,
            'scroll_right' => true,
            'fullscreen' => true,
        ],
        'options' => [
            'loading_screen' => 1000,
            'auto_show_new_tab' => true,
            'use_navbar_items' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Livewire
    |--------------------------------------------------------------------------
    |
    | Here we can enable the Livewire support.
    |
    | For detailed instructions you can look the livewire here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Other-Configuration
    |
    */

    'livewire' => false,

];
