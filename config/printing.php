<?php

return [
    // Default printer type: 'thermal', 'regular', 'network'
    'default_printer_type' => env('DEFAULT_PRINTER_TYPE', 'thermal'),

    // Thermal printer settings
    'thermal_connection_type' => env('THERMAL_CONNECTION_TYPE', 'windows'), // windows, cups, file, network
    'thermal_printer_name' => env('THERMAL_PRINTER_NAME', 'POS-80'), // Windows printer name or CUPS printer
    'thermal_device_path' => env('THERMAL_DEVICE_PATH', '/dev/usb/lp0'), // For direct file connection (Linux)
    'thermal_network_host' => env('THERMAL_NETWORK_HOST', '192.168.1.100'),
    'thermal_network_port' => env('THERMAL_NETWORK_PORT', 9100),

    // Regular printer settings
    'regular_printer_name' => env('REGULAR_PRINTER_NAME', 'Microsoft Print to PDF'),

    // Network printer settings
    'network_printer_host' => env('NETWORK_PRINTER_HOST', '192.168.1.100'),
    'network_printer_port' => env('NETWORK_PRINTER_PORT', 9100),

    // Print settings
    'auto_print_invoices' => env('AUTO_PRINT_INVOICES', false),
    'print_copies' => env('PRINT_COPIES', 1),
    'enable_print_queue' => env('ENABLE_PRINT_QUEUE', true),
];
