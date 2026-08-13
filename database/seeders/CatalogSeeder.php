<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Listing;
use App\Models\PricingPlan;
use Illuminate\Database\Seeder;

class CatalogSeeder extends Seeder
{
    public function run(): void
    {
        $networking = Category::create([
            'name' => 'Networking Device',
            'slug' => 'networking-device',
            'type_hint' => 'hardware',
            'icon' => 'networking',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $cctv = Category::create([
            'name' => 'CCTV Camera',
            'slug' => 'cctv-camera',
            'type_hint' => 'hardware',
            'icon' => 'cctv',
            'sort_order' => 2,
            'is_active' => true,
        ]);

        $software = Category::create([
            'name' => 'Software & Services',
            'slug' => 'software-services',
            'type_hint' => 'software_service',
            'icon' => 'software',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $this->hardwareListing($networking, [
            'sku' => 'MWK-NET-001',
            'name' => 'Mikrotik hAP ax³ Gigabit',
            'slug' => 'mikrotik-hap-ax3-gigabit',
            'short_description' => 'Wi-Fi 6 gigabit router built for small business networks.',
            'price' => 2150000,
            'is_featured' => true,
            'stock_qty' => 14,
        ]);

        $this->hardwareListing($networking, [
            'sku' => 'MWK-NET-002',
            'name' => 'TP-Link Archer AX55',
            'slug' => 'tp-link-archer-ax55',
            'short_description' => 'Dual-band AX3000 Wi-Fi 6 router for retail and office use.',
            'price' => 1250000,
            'is_featured' => false,
            'stock_qty' => 22,
        ]);

        $this->hardwareListing($networking, [
            'sku' => 'MWK-NET-003',
            'name' => 'Ubiquiti UniFi AP AC Lite',
            'slug' => 'ubiquiti-unifi-ap-ac-lite',
            'short_description' => 'Ceiling-mount access point for multi-room coverage.',
            'price' => 1580000,
            'is_featured' => true,
            'stock_qty' => 9,
        ]);

        $this->hardwareListing($networking, [
            'sku' => 'MWK-NET-004',
            'name' => 'Mikrotik CCR2004-1G-12S+2XS',
            'slug' => 'mikrotik-ccr2004',
            'short_description' => 'Cloud core router for larger sites and multi-outlet WANs.',
            'price' => 9800000,
            'is_featured' => false,
            'stock_qty' => 3,
        ]);

        $this->hardwareListing($cctv, [
            'sku' => 'MWK-CCTV-001',
            'name' => '4-Channel NVR + Camera Set',
            'slug' => '4-channel-nvr-camera-set',
            'short_description' => 'Entry-level surveillance bundle for small outlets.',
            'price' => 4850000,
            'is_featured' => true,
            'stock_qty' => 7,
        ]);

        $this->hardwareListing($cctv, [
            'sku' => 'MWK-CCTV-002',
            'name' => '8-Channel NVR Kit',
            'slug' => '8-channel-nvr-kit',
            'short_description' => 'Mid-size site coverage with 8 channels and 2TB storage.',
            'price' => 8200000,
            'is_featured' => false,
            'stock_qty' => 5,
        ]);

        $this->hardwareListing($cctv, [
            'sku' => 'MWK-CCTV-003',
            'name' => 'Dome Camera 5MP',
            'slug' => 'dome-camera-5mp',
            'short_description' => 'Indoor dome camera with night vision, sold individually.',
            'price' => 620000,
            'is_featured' => false,
            'stock_qty' => 30,
        ]);

        $this->hardwareListing($cctv, [
            'sku' => 'MWK-CCTV-004',
            'name' => 'Wireless CCTV Starter Kit',
            'slug' => 'wireless-cctv-starter-kit',
            'short_description' => 'Battery-powered wireless kit for quick deployment.',
            'price' => 3100000,
            'is_featured' => false,
            'stock_qty' => 11,
        ]);

        $restoflow = $this->softwareListing($software, [
            'name' => 'Restoflow — Pro Plan',
            'slug' => 'restoflow-pro-plan',
            'short_description' => 'Multi-tenant restaurant POS with kitchen display and reporting.',
            'is_featured' => true,
        ]);
        PricingPlan::create([
            'listing_id' => $restoflow->id,
            'plan_name' => 'Pro — Monthly',
            'billing_cycle' => 'monthly',
            'price' => 750000,
            'currency' => 'IDR',
            'is_default' => true,
            'sort_order' => 1,
        ]);
        PricingPlan::create([
            'listing_id' => $restoflow->id,
            'plan_name' => 'Pro — Yearly',
            'billing_cycle' => 'yearly',
            'price' => 7500000,
            'currency' => 'IDR',
            'is_default' => false,
            'sort_order' => 2,
        ]);

        $driverloka = $this->softwareListing($software, [
            'name' => 'DriverLoka',
            'slug' => 'driverloka',
            'short_description' => 'Bali tour & driver dispatch platform.',
            'is_featured' => false,
        ]);
        PricingPlan::create([
            'listing_id' => $driverloka->id,
            'plan_name' => 'Standard — Monthly',
            'billing_cycle' => 'monthly',
            'price' => 450000,
            'currency' => 'IDR',
            'is_default' => true,
            'sort_order' => 1,
        ]);

        $gymwms = $this->softwareListing($software, [
            'name' => 'Gym WMS',
            'slug' => 'gym-wms',
            'short_description' => 'Membership and workout management for gyms and studios.',
            'is_featured' => false,
        ]);
        PricingPlan::create([
            'listing_id' => $gymwms->id,
            'plan_name' => 'Standard — Monthly',
            'billing_cycle' => 'monthly',
            'price' => 350000,
            'currency' => 'IDR',
            'is_default' => true,
            'sort_order' => 1,
        ]);

        $ehios = $this->softwareListing($software, [
            'name' => 'E-HIOS — Starter',
            'slug' => 'e-hios-starter',
            'short_description' => 'Hospitality intelligence platform, custom-scoped per property.',
            'is_featured' => true,
        ]);
        PricingPlan::create([
            'listing_id' => $ehios->id,
            'plan_name' => 'Starter — Custom Quote',
            'billing_cycle' => 'custom_quote',
            'price' => null,
            'currency' => 'IDR',
            'is_default' => true,
            'sort_order' => 1,
        ]);
    }

    private function hardwareListing(Category $category, array $attrs): Listing
    {
        $listing = Listing::create([
            'category_id' => $category->id,
            'sku' => $attrs['sku'],
            'name' => $attrs['name'],
            'slug' => $attrs['slug'],
            'listing_type' => 'physical_product',
            'short_description' => $attrs['short_description'],
            'status' => 'active',
            'is_featured' => $attrs['is_featured'],
            'stock_qty' => $attrs['stock_qty'],
            'track_stock' => true,
        ]);

        PricingPlan::create([
            'listing_id' => $listing->id,
            'plan_name' => 'Standard',
            'billing_cycle' => 'one_time',
            'price' => $attrs['price'],
            'currency' => 'IDR',
            'is_default' => true,
            'sort_order' => 1,
        ]);

        return $listing;
    }

    private function softwareListing(Category $category, array $attrs): Listing
    {
        return Listing::create([
            'category_id' => $category->id,
            'sku' => null,
            'name' => $attrs['name'],
            'slug' => $attrs['slug'],
            'listing_type' => 'software_service',
            'short_description' => $attrs['short_description'],
            'status' => 'active',
            'is_featured' => $attrs['is_featured'],
            'stock_qty' => null,
            'track_stock' => false,
        ]);
    }
}
