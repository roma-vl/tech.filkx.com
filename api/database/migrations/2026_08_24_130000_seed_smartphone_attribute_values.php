<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Only 6 of 222 seeded products carried any attribute value at all (and
     * those 6 were leftover CatalogSeeder demo rows, not the Sota-scraped
     * catalog), even though the smartphones branch has a full attribute
     * schema wired up (category_attribute) - the attribute filter sidebar
     * had nothing real to filter on. Fills in real-ish specs for the 32
     * products under "smartphones" (its only branch with an attribute
     * schema; other top-level categories have none defined yet - a
     * separate, bigger task if that's wanted too), keyed by product slug so
     * this stays stable across environments with different ids.
     *
     * Attribute values are resolved by (attribute_id, label) rather than a
     * hardcoded attribute_value_id, auto-creating ones that don't exist yet
     * (new colors, and cleaner "8 ГБ"/"12 ГБ"/"16 ГБ" memory values - the
     * two pre-existing ones were inconsistently formatted, e.g. "16гб" with
     * no space) - safe to run on any environment regardless of what's
     * already been seeded there.
     */
    private const PRODUCT_ATTRIBUTES = [
        'b-v-apple-iphone-17-256gb-esim-lavender-idealnij-stan-260176' => [
            'memory' => ['8 ГБ', '8 GB'],
            'storage' => ['256 ГБ', '256 GB'],
            'screen_size' => ['6" – 6.4"', '6" – 6.4"'],
            'display_type' => ['OLED', 'OLED'],
            'refresh_rate' => ['60 Гц', '60 Hz'],
            'processor_brand' => ['Apple', 'Apple'],
            'core_count' => ['6 ядер', '6 cores'],
            'battery_capacity' => ['4000 – 4999 мАг', '4000 – 4999 mAh'],
            'main_camera' => ['49 – 64 Мп', '49 – 64 MP'],
            'front_camera' => ['9 – 19 Мп', '9 – 19 MP'],
            'os' => ['iOS', 'iOS'],
            'nfc' => ['Є', 'Yes'],
            'wireless_charging' => ['Є', 'Yes'],
            'mobile_network' => ['5G', '5G'],
            'sim_count' => ['eSIM', 'eSIM'],
            'connectivity' => ['USB Type-C', 'USB Type-C'],
            'color' => ['#C9A8E0', '#C9A8E0'],
        ],
        'b-v-apple-iphone-17-256gb-esim-mist-blue-idealnij-stan-260419' => [
            'memory' => ['8 ГБ', '8 GB'],
            'storage' => ['256 ГБ', '256 GB'],
            'screen_size' => ['6" – 6.4"', '6" – 6.4"'],
            'display_type' => ['OLED', 'OLED'],
            'refresh_rate' => ['60 Гц', '60 Hz'],
            'processor_brand' => ['Apple', 'Apple'],
            'core_count' => ['6 ядер', '6 cores'],
            'battery_capacity' => ['4000 – 4999 мАг', '4000 – 4999 mAh'],
            'main_camera' => ['49 – 64 Мп', '49 – 64 MP'],
            'front_camera' => ['9 – 19 Мп', '9 – 19 MP'],
            'os' => ['iOS', 'iOS'],
            'nfc' => ['Є', 'Yes'],
            'wireless_charging' => ['Є', 'Yes'],
            'mobile_network' => ['5G', '5G'],
            'sim_count' => ['eSIM', 'eSIM'],
            'connectivity' => ['USB Type-C', 'USB Type-C'],
            'color' => ['#9FC1D9', '#9FC1D9'],
        ],
        'b-v-apple-iphone-17-256gb-esim-sage-idealnij-stan-260435' => [
            'memory' => ['8 ГБ', '8 GB'],
            'storage' => ['256 ГБ', '256 GB'],
            'screen_size' => ['6" – 6.4"', '6" – 6.4"'],
            'display_type' => ['OLED', 'OLED'],
            'refresh_rate' => ['60 Гц', '60 Hz'],
            'processor_brand' => ['Apple', 'Apple'],
            'core_count' => ['6 ядер', '6 cores'],
            'battery_capacity' => ['4000 – 4999 мАг', '4000 – 4999 mAh'],
            'main_camera' => ['49 – 64 Мп', '49 – 64 MP'],
            'front_camera' => ['9 – 19 Мп', '9 – 19 MP'],
            'os' => ['iOS', 'iOS'],
            'nfc' => ['Є', 'Yes'],
            'wireless_charging' => ['Є', 'Yes'],
            'mobile_network' => ['5G', '5G'],
            'sim_count' => ['eSIM', 'eSIM'],
            'connectivity' => ['USB Type-C', 'USB Type-C'],
            'color' => ['#9CAF88', '#9CAF88'],
        ],
        'b-v-apple-iphone-17-pro-1tb-silver-idealnij-stan-251647' => [
            'memory' => ['12 ГБ', '12 GB'],
            'storage' => ['1 ТБ', '1 TB'],
            'screen_size' => ['6" – 6.4"', '6" – 6.4"'],
            'display_type' => ['LTPO OLED', 'LTPO OLED'],
            'refresh_rate' => ['120 Гц', '120 Hz'],
            'processor_brand' => ['Apple', 'Apple'],
            'core_count' => ['6 ядер', '6 cores'],
            'battery_capacity' => ['4000 – 4999 мАг', '4000 – 4999 mAh'],
            'main_camera' => ['49 – 64 Мп', '49 – 64 MP'],
            'front_camera' => ['9 – 19 Мп', '9 – 19 MP'],
            'os' => ['iOS', 'iOS'],
            'nfc' => ['Є', 'Yes'],
            'wireless_charging' => ['Є', 'Yes'],
            'mobile_network' => ['5G', '5G'],
            'sim_count' => ['Nano-SIM + eSIM', 'Nano-SIM + eSIM'],
            'connectivity' => ['USB Type-C', 'USB Type-C'],
            'color' => ['#C7C7C7', '#C7C7C7'],
        ],
        'b-v-apple-iphone-17-pro-256gb-cosmic-orange-idealnij-stan-251653' => [
            'memory' => ['12 ГБ', '12 GB'],
            'storage' => ['256 ГБ', '256 GB'],
            'screen_size' => ['6" – 6.4"', '6" – 6.4"'],
            'display_type' => ['LTPO OLED', 'LTPO OLED'],
            'refresh_rate' => ['120 Гц', '120 Hz'],
            'processor_brand' => ['Apple', 'Apple'],
            'core_count' => ['6 ядер', '6 cores'],
            'battery_capacity' => ['4000 – 4999 мАг', '4000 – 4999 mAh'],
            'main_camera' => ['49 – 64 Мп', '49 – 64 MP'],
            'front_camera' => ['9 – 19 Мп', '9 – 19 MP'],
            'os' => ['iOS', 'iOS'],
            'nfc' => ['Є', 'Yes'],
            'wireless_charging' => ['Є', 'Yes'],
            'mobile_network' => ['5G', '5G'],
            'sim_count' => ['Nano-SIM + eSIM', 'Nano-SIM + eSIM'],
            'connectivity' => ['USB Type-C', 'USB Type-C'],
            'color' => ['#E8734A', '#E8734A'],
        ],
        'b-v-apple-iphone-17-pro-256gb-esim-cosmic-orange-idealnij-stan-260445' => [
            'memory' => ['12 ГБ', '12 GB'],
            'storage' => ['256 ГБ', '256 GB'],
            'screen_size' => ['6" – 6.4"', '6" – 6.4"'],
            'display_type' => ['LTPO OLED', 'LTPO OLED'],
            'refresh_rate' => ['120 Гц', '120 Hz'],
            'processor_brand' => ['Apple', 'Apple'],
            'core_count' => ['6 ядер', '6 cores'],
            'battery_capacity' => ['4000 – 4999 мАг', '4000 – 4999 mAh'],
            'main_camera' => ['49 – 64 Мп', '49 – 64 MP'],
            'front_camera' => ['9 – 19 Мп', '9 – 19 MP'],
            'os' => ['iOS', 'iOS'],
            'nfc' => ['Є', 'Yes'],
            'wireless_charging' => ['Є', 'Yes'],
            'mobile_network' => ['5G', '5G'],
            'sim_count' => ['eSIM', 'eSIM'],
            'connectivity' => ['USB Type-C', 'USB Type-C'],
            'color' => ['#E8734A', '#E8734A'],
        ],
        'b-v-apple-iphone-17-pro-256gb-esim-deep-blue-idealnij-stan-260446' => [
            'memory' => ['12 ГБ', '12 GB'],
            'storage' => ['256 ГБ', '256 GB'],
            'screen_size' => ['6" – 6.4"', '6" – 6.4"'],
            'display_type' => ['LTPO OLED', 'LTPO OLED'],
            'refresh_rate' => ['120 Гц', '120 Hz'],
            'processor_brand' => ['Apple', 'Apple'],
            'core_count' => ['6 ядер', '6 cores'],
            'battery_capacity' => ['4000 – 4999 мАг', '4000 – 4999 mAh'],
            'main_camera' => ['49 – 64 Мп', '49 – 64 MP'],
            'front_camera' => ['9 – 19 Мп', '9 – 19 MP'],
            'os' => ['iOS', 'iOS'],
            'nfc' => ['Є', 'Yes'],
            'wireless_charging' => ['Є', 'Yes'],
            'mobile_network' => ['5G', '5G'],
            'sim_count' => ['eSIM', 'eSIM'],
            'connectivity' => ['USB Type-C', 'USB Type-C'],
            'color' => ['#1B3A5C', '#1B3A5C'],
        ],
        'b-v-apple-iphone-17-pro-256gb-esim-silver-idealnij-stan-260447' => [
            'memory' => ['12 ГБ', '12 GB'],
            'storage' => ['256 ГБ', '256 GB'],
            'screen_size' => ['6" – 6.4"', '6" – 6.4"'],
            'display_type' => ['LTPO OLED', 'LTPO OLED'],
            'refresh_rate' => ['120 Гц', '120 Hz'],
            'processor_brand' => ['Apple', 'Apple'],
            'core_count' => ['6 ядер', '6 cores'],
            'battery_capacity' => ['4000 – 4999 мАг', '4000 – 4999 mAh'],
            'main_camera' => ['49 – 64 Мп', '49 – 64 MP'],
            'front_camera' => ['9 – 19 Мп', '9 – 19 MP'],
            'os' => ['iOS', 'iOS'],
            'nfc' => ['Є', 'Yes'],
            'wireless_charging' => ['Є', 'Yes'],
            'mobile_network' => ['5G', '5G'],
            'sim_count' => ['eSIM', 'eSIM'],
            'connectivity' => ['USB Type-C', 'USB Type-C'],
            'color' => ['#C7C7C7', '#C7C7C7'],
        ],
        'b-v-apple-iphone-17-pro-512gb-esim-cosmic-orange-idealnij-stan-260448' => [
            'memory' => ['12 ГБ', '12 GB'],
            'storage' => ['512 ГБ', '512 GB'],
            'screen_size' => ['6" – 6.4"', '6" – 6.4"'],
            'display_type' => ['LTPO OLED', 'LTPO OLED'],
            'refresh_rate' => ['120 Гц', '120 Hz'],
            'processor_brand' => ['Apple', 'Apple'],
            'core_count' => ['6 ядер', '6 cores'],
            'battery_capacity' => ['4000 – 4999 мАг', '4000 – 4999 mAh'],
            'main_camera' => ['49 – 64 Мп', '49 – 64 MP'],
            'front_camera' => ['9 – 19 Мп', '9 – 19 MP'],
            'os' => ['iOS', 'iOS'],
            'nfc' => ['Є', 'Yes'],
            'wireless_charging' => ['Є', 'Yes'],
            'mobile_network' => ['5G', '5G'],
            'sim_count' => ['eSIM', 'eSIM'],
            'connectivity' => ['USB Type-C', 'USB Type-C'],
            'color' => ['#E8734A', '#E8734A'],
        ],
        'b-v-apple-iphone-17-pro-512gb-esim-deep-blue-idealnij-stan-260449' => [
            'memory' => ['12 ГБ', '12 GB'],
            'storage' => ['512 ГБ', '512 GB'],
            'screen_size' => ['6" – 6.4"', '6" – 6.4"'],
            'display_type' => ['LTPO OLED', 'LTPO OLED'],
            'refresh_rate' => ['120 Гц', '120 Hz'],
            'processor_brand' => ['Apple', 'Apple'],
            'core_count' => ['6 ядер', '6 cores'],
            'battery_capacity' => ['4000 – 4999 мАг', '4000 – 4999 mAh'],
            'main_camera' => ['49 – 64 Мп', '49 – 64 MP'],
            'front_camera' => ['9 – 19 Мп', '9 – 19 MP'],
            'os' => ['iOS', 'iOS'],
            'nfc' => ['Є', 'Yes'],
            'wireless_charging' => ['Є', 'Yes'],
            'mobile_network' => ['5G', '5G'],
            'sim_count' => ['eSIM', 'eSIM'],
            'connectivity' => ['USB Type-C', 'USB Type-C'],
            'color' => ['#1B3A5C', '#1B3A5C'],
        ],
        'b-v-apple-iphone-17-pro-max-1tb-cosmic-orange-garnij-stan-251688' => [
            'memory' => ['12 ГБ', '12 GB'],
            'storage' => ['1 ТБ', '1 TB'],
            'screen_size' => ['6.5" – 6.9"', '6.5" – 6.9"'],
            'display_type' => ['LTPO OLED', 'LTPO OLED'],
            'refresh_rate' => ['120 Гц', '120 Hz'],
            'processor_brand' => ['Apple', 'Apple'],
            'core_count' => ['6 ядер', '6 cores'],
            'battery_capacity' => ['5000 – 5999 мАг', '5000 – 5999 mAh'],
            'main_camera' => ['49 – 64 Мп', '49 – 64 MP'],
            'front_camera' => ['9 – 19 Мп', '9 – 19 MP'],
            'os' => ['iOS', 'iOS'],
            'nfc' => ['Є', 'Yes'],
            'wireless_charging' => ['Є', 'Yes'],
            'mobile_network' => ['5G', '5G'],
            'sim_count' => ['Nano-SIM + eSIM', 'Nano-SIM + eSIM'],
            'connectivity' => ['USB Type-C', 'USB Type-C'],
            'color' => ['#E8734A', '#E8734A'],
        ],
        'b-v-apple-iphone-17-pro-max-256gb-deep-blue-garnij-stan-251696' => [
            'memory' => ['12 ГБ', '12 GB'],
            'storage' => ['256 ГБ', '256 GB'],
            'screen_size' => ['6.5" – 6.9"', '6.5" – 6.9"'],
            'display_type' => ['LTPO OLED', 'LTPO OLED'],
            'refresh_rate' => ['120 Гц', '120 Hz'],
            'processor_brand' => ['Apple', 'Apple'],
            'core_count' => ['6 ядер', '6 cores'],
            'battery_capacity' => ['5000 – 5999 мАг', '5000 – 5999 mAh'],
            'main_camera' => ['49 – 64 Мп', '49 – 64 MP'],
            'front_camera' => ['9 – 19 Мп', '9 – 19 MP'],
            'os' => ['iOS', 'iOS'],
            'nfc' => ['Є', 'Yes'],
            'wireless_charging' => ['Є', 'Yes'],
            'mobile_network' => ['5G', '5G'],
            'sim_count' => ['Nano-SIM + eSIM', 'Nano-SIM + eSIM'],
            'connectivity' => ['USB Type-C', 'USB Type-C'],
            'color' => ['#1B3A5C', '#1B3A5C'],
        ],
        'google-pixel-7-8-128gb-snow-119799' => [
            'memory' => ['8 ГБ', '8 GB'],
            'storage' => ['128 ГБ', '128 GB'],
            'screen_size' => ['6.5" – 6.9"', '6.5" – 6.9"'],
            'display_type' => ['OLED', 'OLED'],
            'refresh_rate' => ['90 Гц', '90 Hz'],
            'processor_brand' => ['Google', 'Google'],
            'core_count' => ['8 ядер', '8 cores'],
            'battery_capacity' => ['4000 – 4999 мАг', '4000 – 4999 mAh'],
            'main_camera' => ['49 – 64 Мп', '49 – 64 MP'],
            'front_camera' => ['9 – 19 Мп', '9 – 19 MP'],
            'os' => ['Android', 'Android'],
            'nfc' => ['Є', 'Yes'],
            'wireless_charging' => ['Є', 'Yes'],
            'mobile_network' => ['5G', '5G'],
            'sim_count' => ['Nano-SIM + eSIM', 'Nano-SIM + eSIM'],
            'connectivity' => ['USB Type-C', 'USB Type-C'],
            'color' => ['#F2F1EC', '#F2F1EC'],
        ],
        'nomi-i1850-black-ua-147160' => [
            'screen_size' => ['4" – 4.9"', '4" – 4.9"'],
            'battery_capacity' => ['До 2999 мАг', 'Up to 2999 mAh'],
            'mobile_network' => ['2G', '2G'],
            'connectivity' => ['Bluetooth', 'Bluetooth'],
            'color' => ['#1C1C1C', '#1C1C1C'],
        ],
        'nomi-i1890-blue-ua-147163' => [
            'screen_size' => ['4" – 4.9"', '4" – 4.9"'],
            'battery_capacity' => ['До 2999 мАг', 'Up to 2999 mAh'],
            'mobile_network' => ['2G', '2G'],
            'connectivity' => ['Bluetooth', 'Bluetooth'],
            'color' => ['#2255AA', '#2255AA'],
        ],
        'nomi-i220-red-ua-68442' => [
            'screen_size' => ['4" – 4.9"', '4" – 4.9"'],
            'battery_capacity' => ['До 2999 мАг', 'Up to 2999 mAh'],
            'mobile_network' => ['2G', '2G'],
            'connectivity' => ['Bluetooth', 'Bluetooth'],
            'color' => ['#C81E2C', '#C81E2C'],
        ],
        'nomi-i2403-black-ua-151657' => [
            'screen_size' => ['4" – 4.9"', '4" – 4.9"'],
            'battery_capacity' => ['До 2999 мАг', 'Up to 2999 mAh'],
            'mobile_network' => ['2G', '2G'],
            'connectivity' => ['Bluetooth', 'Bluetooth'],
            'color' => ['#1C1C1C', '#1C1C1C'],
        ],
        'nomi-i2403-red-ua-151659' => [
            'screen_size' => ['4" – 4.9"', '4" – 4.9"'],
            'battery_capacity' => ['До 2999 мАг', 'Up to 2999 mAh'],
            'mobile_network' => ['2G', '2G'],
            'connectivity' => ['Bluetooth', 'Bluetooth'],
            'color' => ['#C81E2C', '#C81E2C'],
        ],
        'smartfon-apple-iphone-17-256gb-esim-sage-mg4a4-228654' => [
            'memory' => ['8 ГБ', '8 GB'],
            'storage' => ['256 ГБ', '256 GB'],
            'screen_size' => ['6" – 6.4"', '6" – 6.4"'],
            'display_type' => ['OLED', 'OLED'],
            'refresh_rate' => ['60 Гц', '60 Hz'],
            'processor_brand' => ['Apple', 'Apple'],
            'core_count' => ['6 ядер', '6 cores'],
            'battery_capacity' => ['4000 – 4999 мАг', '4000 – 4999 mAh'],
            'main_camera' => ['49 – 64 Мп', '49 – 64 MP'],
            'front_camera' => ['9 – 19 Мп', '9 – 19 MP'],
            'os' => ['iOS', 'iOS'],
            'nfc' => ['Є', 'Yes'],
            'wireless_charging' => ['Є', 'Yes'],
            'mobile_network' => ['5G', '5G'],
            'sim_count' => ['eSIM', 'eSIM'],
            'connectivity' => ['USB Type-C', 'USB Type-C'],
            'color' => ['#9CAF88', '#9CAF88'],
        ],
        'smartfon-apple-iphone-17-512gb-esim-sage-mg4q4-247751' => [
            'memory' => ['8 ГБ', '8 GB'],
            'storage' => ['512 ГБ', '512 GB'],
            'screen_size' => ['6" – 6.4"', '6" – 6.4"'],
            'display_type' => ['OLED', 'OLED'],
            'refresh_rate' => ['60 Гц', '60 Hz'],
            'processor_brand' => ['Apple', 'Apple'],
            'core_count' => ['6 ядер', '6 cores'],
            'battery_capacity' => ['4000 – 4999 мАг', '4000 – 4999 mAh'],
            'main_camera' => ['49 – 64 Мп', '49 – 64 MP'],
            'front_camera' => ['9 – 19 Мп', '9 – 19 MP'],
            'os' => ['iOS', 'iOS'],
            'nfc' => ['Є', 'Yes'],
            'wireless_charging' => ['Є', 'Yes'],
            'mobile_network' => ['5G', '5G'],
            'sim_count' => ['eSIM', 'eSIM'],
            'connectivity' => ['USB Type-C', 'USB Type-C'],
            'color' => ['#9CAF88', '#9CAF88'],
        ],
        'smartfon-apple-iphone-17-pro-256gb-esim-silver-mg7k4-227396' => [
            'memory' => ['12 ГБ', '12 GB'],
            'storage' => ['256 ГБ', '256 GB'],
            'screen_size' => ['6" – 6.4"', '6" – 6.4"'],
            'display_type' => ['LTPO OLED', 'LTPO OLED'],
            'refresh_rate' => ['120 Гц', '120 Hz'],
            'processor_brand' => ['Apple', 'Apple'],
            'core_count' => ['6 ядер', '6 cores'],
            'battery_capacity' => ['4000 – 4999 мАг', '4000 – 4999 mAh'],
            'main_camera' => ['49 – 64 Мп', '49 – 64 MP'],
            'front_camera' => ['9 – 19 Мп', '9 – 19 MP'],
            'os' => ['iOS', 'iOS'],
            'nfc' => ['Є', 'Yes'],
            'wireless_charging' => ['Є', 'Yes'],
            'mobile_network' => ['5G', '5G'],
            'sim_count' => ['eSIM', 'eSIM'],
            'connectivity' => ['USB Type-C', 'USB Type-C'],
            'color' => ['#C7C7C7', '#C7C7C7'],
        ],
        'smartfon-apple-iphone-17-pro-512gb-cosmic-orange-mg8m4-224697' => [
            'memory' => ['12 ГБ', '12 GB'],
            'storage' => ['512 ГБ', '512 GB'],
            'screen_size' => ['6" – 6.4"', '6" – 6.4"'],
            'display_type' => ['LTPO OLED', 'LTPO OLED'],
            'refresh_rate' => ['120 Гц', '120 Hz'],
            'processor_brand' => ['Apple', 'Apple'],
            'core_count' => ['6 ядер', '6 cores'],
            'battery_capacity' => ['4000 – 4999 мАг', '4000 – 4999 mAh'],
            'main_camera' => ['49 – 64 Мп', '49 – 64 MP'],
            'front_camera' => ['9 – 19 Мп', '9 – 19 MP'],
            'os' => ['iOS', 'iOS'],
            'nfc' => ['Є', 'Yes'],
            'wireless_charging' => ['Є', 'Yes'],
            'mobile_network' => ['5G', '5G'],
            'sim_count' => ['Nano-SIM + eSIM', 'Nano-SIM + eSIM'],
            'connectivity' => ['USB Type-C', 'USB Type-C'],
            'color' => ['#E8734A', '#E8734A'],
        ],
        'smartfon-apple-iphone-17-pro-512gb-esim-deep-blue-mg7q4-227146' => [
            'memory' => ['12 ГБ', '12 GB'],
            'storage' => ['512 ГБ', '512 GB'],
            'screen_size' => ['6" – 6.4"', '6" – 6.4"'],
            'display_type' => ['LTPO OLED', 'LTPO OLED'],
            'refresh_rate' => ['120 Гц', '120 Hz'],
            'processor_brand' => ['Apple', 'Apple'],
            'core_count' => ['6 ядер', '6 cores'],
            'battery_capacity' => ['4000 – 4999 мАг', '4000 – 4999 mAh'],
            'main_camera' => ['49 – 64 Мп', '49 – 64 MP'],
            'front_camera' => ['9 – 19 Мп', '9 – 19 MP'],
            'os' => ['iOS', 'iOS'],
            'nfc' => ['Є', 'Yes'],
            'wireless_charging' => ['Є', 'Yes'],
            'mobile_network' => ['5G', '5G'],
            'sim_count' => ['eSIM', 'eSIM'],
            'connectivity' => ['USB Type-C', 'USB Type-C'],
            'color' => ['#1B3A5C', '#1B3A5C'],
        ],
        'smartfon-apple-iphone-17-pro-max-1tb-deep-blue-mfyx4-224709' => [
            'memory' => ['12 ГБ', '12 GB'],
            'storage' => ['1 ТБ', '1 TB'],
            'screen_size' => ['6.5" – 6.9"', '6.5" – 6.9"'],
            'display_type' => ['LTPO OLED', 'LTPO OLED'],
            'refresh_rate' => ['120 Гц', '120 Hz'],
            'processor_brand' => ['Apple', 'Apple'],
            'core_count' => ['6 ядер', '6 cores'],
            'battery_capacity' => ['5000 – 5999 мАг', '5000 – 5999 mAh'],
            'main_camera' => ['49 – 64 Мп', '49 – 64 MP'],
            'front_camera' => ['9 – 19 Мп', '9 – 19 MP'],
            'os' => ['iOS', 'iOS'],
            'nfc' => ['Є', 'Yes'],
            'wireless_charging' => ['Є', 'Yes'],
            'mobile_network' => ['5G', '5G'],
            'sim_count' => ['Nano-SIM + eSIM', 'Nano-SIM + eSIM'],
            'connectivity' => ['USB Type-C', 'USB Type-C'],
            'color' => ['#1B3A5C', '#1B3A5C'],
        ],
        'smartfon-apple-iphone-17e-256gb-esim-soft-pink-mhrq4-250720' => [
            'memory' => ['8 ГБ', '8 GB'],
            'storage' => ['256 ГБ', '256 GB'],
            'screen_size' => ['6" – 6.4"', '6" – 6.4"'],
            'display_type' => ['OLED', 'OLED'],
            'refresh_rate' => ['60 Гц', '60 Hz'],
            'processor_brand' => ['Apple', 'Apple'],
            'core_count' => ['6 ядер', '6 cores'],
            'battery_capacity' => ['3000 – 3999 мАг', '3000 – 3999 mAh'],
            'main_camera' => ['24 – 48 Мп', '24 – 48 MP'],
            'front_camera' => ['9 – 19 Мп', '9 – 19 MP'],
            'os' => ['iOS', 'iOS'],
            'nfc' => ['Є', 'Yes'],
            'wireless_charging' => ['Немає', 'No'],
            'mobile_network' => ['5G', '5G'],
            'sim_count' => ['eSIM', 'eSIM'],
            'connectivity' => ['USB Type-C', 'USB Type-C'],
            'color' => ['#F4C2C2', '#F4C2C2'],
        ],
        'smartfon-google-pixel-9-pro-16-256gb-hazel-192612' => [
            'memory' => ['16 ГБ', '16 GB'],
            'storage' => ['256 ГБ', '256 GB'],
            'screen_size' => ['6.5" – 6.9"', '6.5" – 6.9"'],
            'display_type' => ['LTPO OLED', 'LTPO OLED'],
            'refresh_rate' => ['120 Гц', '120 Hz'],
            'processor_brand' => ['Google', 'Google'],
            'core_count' => ['9 ядер', '9 cores'],
            'battery_capacity' => ['4000 – 4999 мАг', '4000 – 4999 mAh'],
            'main_camera' => ['49 – 64 Мп', '49 – 64 MP'],
            'front_camera' => ['20 – 31 Мп', '20 – 31 MP'],
            'os' => ['Android', 'Android'],
            'nfc' => ['Є', 'Yes'],
            'wireless_charging' => ['Є', 'Yes'],
            'mobile_network' => ['5G', '5G'],
            'sim_count' => ['Nano-SIM + eSIM', 'Nano-SIM + eSIM'],
            'connectivity' => ['USB Type-C', 'USB Type-C'],
            'color' => ['#8E7038', '#8E7038'],
        ],
        'smartfon-nothing-phone-3-12-256gb-white-227330' => [
            'memory' => ['16 ГБ', '16 GB'],
            'storage' => ['256 ГБ', '256 GB'],
            'screen_size' => ['6.5" – 6.9"', '6.5" – 6.9"'],
            'display_type' => ['LTPO OLED', 'LTPO OLED'],
            'refresh_rate' => ['120 Гц', '120 Hz'],
            'processor_brand' => ['Qualcomm Snapdragon', 'Qualcomm Snapdragon'],
            'core_count' => ['8 ядер', '8 cores'],
            'battery_capacity' => ['5000 – 5999 мАг', '5000 – 5999 mAh'],
            'main_camera' => ['49 – 64 Мп', '49 – 64 MP'],
            'front_camera' => ['9 – 19 Мп', '9 – 19 MP'],
            'os' => ['Android', 'Android'],
            'nfc' => ['Є', 'Yes'],
            'wireless_charging' => ['Є', 'Yes'],
            'mobile_network' => ['5G', '5G'],
            'sim_count' => ['Nano-SIM + eSIM', 'Nano-SIM + eSIM'],
            'connectivity' => ['USB Type-C', 'USB Type-C'],
            'color' => ['#F5F5F5', '#F5F5F5'],
        ],
        'smartfon-nothing-phone-4a-8-128gb-black-252248' => [
            'memory' => ['8 ГБ', '8 GB'],
            'storage' => ['128 ГБ', '128 GB'],
            'screen_size' => ['6" – 6.4"', '6" – 6.4"'],
            'display_type' => ['AMOLED', 'AMOLED'],
            'refresh_rate' => ['120 Гц', '120 Hz'],
            'processor_brand' => ['MediaTek', 'MediaTek'],
            'core_count' => ['8 ядер', '8 cores'],
            'battery_capacity' => ['4000 – 4999 мАг', '4000 – 4999 mAh'],
            'main_camera' => ['49 – 64 Мп', '49 – 64 MP'],
            'front_camera' => ['20 – 31 Мп', '20 – 31 MP'],
            'os' => ['Android', 'Android'],
            'nfc' => ['Є', 'Yes'],
            'wireless_charging' => ['Немає', 'No'],
            'mobile_network' => ['5G', '5G'],
            'sim_count' => ['Nano-SIM + eSIM', 'Nano-SIM + eSIM'],
            'connectivity' => ['USB Type-C', 'USB Type-C'],
            'color' => ['#1C1C1C', '#1C1C1C'],
        ],
        'telefon-maxcom-mm142-black-215875' => [
            'screen_size' => ['4" – 4.9"', '4" – 4.9"'],
            'battery_capacity' => ['3000 – 3999 мАг', '3000 – 3999 mAh'],
            'mobile_network' => ['4G', '4G'],
            'connectivity' => ['Bluetooth', 'Bluetooth'],
            'color' => ['#1C1C1C', '#1C1C1C'],
        ],
        'telefon-maxcom-mm35d-black-215881' => [
            'screen_size' => ['4" – 4.9"', '4" – 4.9"'],
            'battery_capacity' => ['3000 – 3999 мАг', '3000 – 3999 mAh'],
            'mobile_network' => ['4G', '4G'],
            'connectivity' => ['Bluetooth', 'Bluetooth'],
            'color' => ['#1C1C1C', '#1C1C1C'],
        ],
        'telefon-nomi-i1820-black-ua-227575' => [
            'screen_size' => ['4" – 4.9"', '4" – 4.9"'],
            'battery_capacity' => ['До 2999 мАг', 'Up to 2999 mAh'],
            'mobile_network' => ['2G', '2G'],
            'connectivity' => ['Bluetooth', 'Bluetooth'],
            'color' => ['#1C1C1C', '#1C1C1C'],
        ],
        'telefon-nomi-i1820-red-ua-227576' => [
            'screen_size' => ['4" – 4.9"', '4" – 4.9"'],
            'battery_capacity' => ['До 2999 мАг', 'Up to 2999 mAh'],
            'mobile_network' => ['2G', '2G'],
            'connectivity' => ['Bluetooth', 'Bluetooth'],
            'color' => ['#C81E2C', '#C81E2C'],
        ],
    ];

    public function up(): void
    {
        $attributeIds = DB::table('attributes')->pluck('id', 'code');

        foreach (self::PRODUCT_ATTRIBUTES as $slug => $attrs) {
            $productId = DB::table('products')->where('slug', $slug)->value('id');
            if (! $productId) {
                continue;
            }

            foreach ($attrs as $code => [$labelUk, $labelEn]) {
                $attributeId = $attributeIds[$code] ?? null;
                if (! $attributeId) {
                    continue;
                }

                $valueId = $this->resolveAttributeValueId($attributeId, $labelUk, $labelEn);

                $exists = DB::table('product_attribute_values')
                    ->where('product_id', $productId)
                    ->where('attribute_id', $attributeId)
                    ->whereNull('variant_id')
                    ->exists();

                if ($exists) {
                    continue;
                }

                DB::table('product_attribute_values')->insert([
                    'product_id' => $productId,
                    'variant_id' => null,
                    'attribute_id' => $attributeId,
                    'attribute_value_id' => $valueId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        $productIds = DB::table('products')
            ->whereIn('slug', array_keys(self::PRODUCT_ATTRIBUTES))
            ->pluck('id');

        DB::table('product_attribute_values')
            ->whereIn('product_id', $productIds)
            ->whereNull('variant_id')
            ->delete();

        // The attribute_values rows this may have created (new colors, clean
        // memory sizes) are left in place - other products/environments may
        // already reference them, and re-running up() will just reuse them.
    }

    private function resolveAttributeValueId(int $attributeId, string $labelUk, string $labelEn): int
    {
        $existing = DB::table('attribute_values')
            ->where('attribute_id', $attributeId)
            ->get(['id', 'value'])
            ->first(function ($row) use ($labelUk) {
                $decoded = json_decode($row->value, true);

                return ($decoded['uk'] ?? null) === $labelUk;
            });

        if ($existing) {
            return $existing->id;
        }

        return DB::table('attribute_values')->insertGetId([
            'attribute_id' => $attributeId,
            'value' => json_encode(['uk' => $labelUk, 'en' => $labelEn], JSON_UNESCAPED_UNICODE),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
};
