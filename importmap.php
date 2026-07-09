<?php

/**
 * Returns the importmap for this application.
 *
 * - "path" is a path inside the asset mapper system. Use the
 *     "debug:asset-map" command to see the full list of paths.
 *
 * - "entrypoint" (JavaScript only) set to true for any module that will
 *     be used as an "entrypoint" (and passed to the importmap() Twig function).
 *
 * The "importmap:require" command can be used to add new entries to this file.
 */
return [
    'app' => [
        'path' => './assets/app.js',
        'entrypoint' => true,
    ],
    'admin' => [
        'path' => './assets/admin.js',
        'entrypoint' => true,
    ],
    '@symfony/stimulus-bundle' => [
        'path' => './vendor/symfony/stimulus-bundle/assets/dist/loader.js',
    ],
    '@symfony/ux-live-component' => [
        'path' => './vendor/symfony/ux-live-component/assets/dist/live_controller.js',
    ],
    '@symfony/ux-leaflet-map' => [
        'path' => './vendor/symfony/ux-leaflet-map/assets/dist/map_controller.js',
    ],
    'bootstrap' => [
        'version' => '5.3.8',
    ],
    '@popperjs/core' => [
        'version' => '2.11.8',
    ],
    'bootstrap/dist/css/bootstrap.min.css' => [
        'version' => '5.3.8',
        'type' => 'css',
    ],
    '@hotwired/stimulus' => [
        'version' => '3.2.2',
    ],
    'chart.js' => [
        'version' => '4.5.1',
    ],
    'leaflet' => [
        'version' => '1.9.4',
    ],
    'leaflet/dist/leaflet.min.css' => [
        'version' => '1.9.4',
        'type' => 'css',
    ],
    'tom-select' => [
        'version' => '2.5.2',
    ],
    '@orchidjs/sifter' => [
        'version' => '1.1.0',
    ],
    '@orchidjs/unicode-variants' => [
        'version' => '1.1.2',
    ],
    'tom-select/dist/css/tom-select.default.min.css' => [
        'version' => '2.5.2',
        'type' => 'css',
    ],
    'tom-select/dist/css/tom-select.default.css' => [
        'version' => '2.5.2',
        'type' => 'css',
    ],
    'tom-select/dist/css/tom-select.bootstrap4.css' => [
        'version' => '2.5.2',
        'type' => 'css',
    ],
    'tom-select/dist/css/tom-select.bootstrap5.css' => [
        'version' => '2.5.2',
        'type' => 'css',
    ],
    'datatables.net-bs5' => [
        'version' => '2.3.7',
    ],
    'jquery' => [
        'version' => '4.0.0',
    ],
    'datatables.net' => [
        'version' => '2.3.7',
    ],
    'datatables.net-bs5/css/dataTables.bootstrap5.min.css' => [
        'version' => '2.3.7',
        'type' => 'css',
    ],
    'datatables.net-buttons' => [
        'version' => '3.2.6',
    ],
    'datatables.net-buttons-bs5' => [
        'version' => '3.2.6',
    ],
    'datatables.net-buttons-bs5/css/buttons.bootstrap5.min.css' => [
        'version' => '3.2.6',
        'type' => 'css',
    ],
    'datatables.net-buttons-dt' => [
        'version' => '3.2.6',
    ],
    'datatables.net-dt' => [
        'version' => '2.3.7',
    ],
    'datatables.net-buttons-dt/css/buttons.dataTables.min.css' => [
        'version' => '3.2.6',
        'type' => 'css',
    ],
    'datatables.net-dt/css/dataTables.dataTables.min.css' => [
        'version' => '2.3.7',
        'type' => 'css',
    ],
    'datatables.net-colreorder-bs5' => [
        'version' => '2.1.2',
    ],
    'datatables.net-colreorder' => [
        'version' => '2.1.2',
    ],
    'datatables.net-colreorder-bs5/css/colReorder.bootstrap5.min.css' => [
        'version' => '2.1.2',
        'type' => 'css',
    ],
    'datatables.net-colreorder-dt' => [
        'version' => '2.1.2',
    ],
    'datatables.net-colreorder-dt/css/colReorder.dataTables.min.css' => [
        'version' => '2.1.2',
        'type' => 'css',
    ],
    'datatables.net-columncontrol-bs5' => [
        'version' => '1.2.1',
    ],
    'datatables.net-columncontrol' => [
        'version' => '1.2.1',
    ],
    'datatables.net-columncontrol-bs5/css/columnControl.bootstrap5.min.css' => [
        'version' => '1.2.1',
        'type' => 'css',
    ],
    'datatables.net-columncontrol-dt' => [
        'version' => '1.2.1',
    ],
    'datatables.net-columncontrol-dt/css/columnControl.dataTables.min.css' => [
        'version' => '1.2.1',
        'type' => 'css',
    ],
    'datatables.net-buttons/js/buttons.colVis' => [
        'version' => '3.2.6',
    ],
    'datatables.net-buttons/js/buttons.html5' => [
        'version' => '3.2.6',
    ],
    'datatables.net-buttons/js/buttons.print' => [
        'version' => '3.2.6',
    ],
    'datatables.net-fixedcolumns-bs5' => [
        'version' => '5.0.5',
    ],
    'datatables.net-fixedcolumns' => [
        'version' => '5.0.5',
    ],
    'datatables.net-fixedcolumns-bs5/css/fixedColumns.bootstrap5.min.css' => [
        'version' => '5.0.5',
        'type' => 'css',
    ],
    'datatables.net-fixedcolumns-dt' => [
        'version' => '5.0.5',
    ],
    'datatables.net-fixedcolumns-dt/css/fixedColumns.dataTables.min.css' => [
        'version' => '5.0.5',
        'type' => 'css',
    ],
    'datatables.net-keytable-bs5' => [
        'version' => '2.12.2',
    ],
    'datatables.net-keytable' => [
        'version' => '2.12.2',
    ],
    'datatables.net-keytable-bs5/css/keyTable.bootstrap5.min.css' => [
        'version' => '2.12.2',
        'type' => 'css',
    ],
    'datatables.net-keytable-dt' => [
        'version' => '2.12.2',
    ],
    'datatables.net-keytable-dt/css/keyTable.dataTables.min.css' => [
        'version' => '2.12.2',
        'type' => 'css',
    ],
    'datatables.net-scroller-bs5' => [
        'version' => '2.4.3',
    ],
    'datatables.net-scroller' => [
        'version' => '2.4.3',
    ],
    'datatables.net-scroller-bs5/css/scroller.bootstrap5.min.css' => [
        'version' => '2.4.3',
        'type' => 'css',
    ],
    'datatables.net-scroller-dt' => [
        'version' => '2.4.3',
    ],
    'datatables.net-scroller-dt/css/scroller.dataTables.min.css' => [
        'version' => '2.4.3',
        'type' => 'css',
    ],
    'datatables.net-select-bs5' => [
        'version' => '3.1.3',
    ],
    'datatables.net-select' => [
        'version' => '3.1.3',
    ],
    'datatables.net-select-bs5/css/select.bootstrap5.min.css' => [
        'version' => '3.1.3',
        'type' => 'css',
    ],
    'datatables.net-select-dt' => [
        'version' => '3.1.3',
    ],
    'datatables.net-select-dt/css/select.dataTables.min.css' => [
        'version' => '3.1.3',
        'type' => 'css',
    ],
    'datatables.net-responsive-bs5' => [
        'version' => '3.0.8',
    ],
    'datatables.net-responsive' => [
        'version' => '3.0.8',
    ],
    'datatables.net-responsive-bs5/css/responsive.bootstrap5.min.css' => [
        'version' => '3.0.8',
        'type' => 'css',
    ],
    'datatables.net-responsive-dt' => [
        'version' => '3.0.8',
    ],
    'datatables.net-responsive-dt/css/responsive.dataTables.min.css' => [
        'version' => '3.0.8',
        'type' => 'css',
    ],
    'jszip' => [
        'version' => '3.10.1',
    ],
    'pdfmake' => [
        'version' => '0.3.5',
    ],
    'pdfmake/build/vfs_fonts' => [
        'version' => '0.3.5',
    ],
    'datatables.net-rowgroup-bs5' => [
        'version' => '1.6.0',
    ],
    'datatables.net-rowgroup-bs5/css/rowGroup.bootstrap5.min.css' => [
        'version' => '1.6.0',
        'type' => 'css',
    ],
    'datatables.net-rowgroup' => [
        'version' => '1.6.0',
    ],
    '@kurkle/color' => [
        'version' => '0.3.4',
    ],
    'cropperjs' => [
        'version' => '1.6.2',
    ],
    'cropperjs/dist/cropper.min.css' => [
        'version' => '1.6.2',
        'type' => 'css',
    ],
    'typed.js' => [
        'version' => '2.1.0',
    ],
    '@hotwired/turbo' => [
        'version' => '7.3.0',
    ],
];
