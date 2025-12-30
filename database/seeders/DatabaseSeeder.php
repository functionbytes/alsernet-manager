<?php

namespace Database\Seeders;

use Database\Seeders\Core\CategorySeeder;
use Database\Seeders\Core\CountrySeeder;
use Database\Seeders\Core\LangSeeder;
use Database\Seeders\Core\MailVariableSeeder;
use Database\Seeders\Core\MediaFolderSeeder;

// Warehouse Seeders
use Database\Seeders\Warehouse\WarehouseLocationStyleSeeder;
use Database\Seeders\Warehouse\WarehouseLocationConditionSeeder;
use Database\Seeders\Warehouse\WarehouseExampleSeeder;
use Database\Seeders\Warehouse\WarehouseSeedersV2;
use Database\Seeders\Locations\Coruna1LocationsSeeder;

// Document Seeders
use Database\Seeders\Documents\CreateDocumentPermissionsSeeder;
use Database\Seeders\Documents\DocumentConfigurationSeeder;
use Database\Seeders\Documents\DocumentUploadTypeSeeder;
use Database\Seeders\Documents\DocumentStatusSeeder;
use Database\Seeders\Documents\DocumentSourceSeeder;
use Database\Seeders\Documents\DocumentLoadSeeder;
use Database\Seeders\Documents\DocumentSyncSeeder;
use Database\Seeders\Documents\DocumentGroupSeeder;
use Database\Seeders\Documents\DocumentStatusTransitionSeeder;
use Database\Seeders\Documents\DocumentValidatorGroupSeeder;
use Database\Seeders\Documents\DocumentValidatorGroupConfigurationSeeder;
use Database\Seeders\Documents\StageEmailActionSeeder;

// Supplier Seeders
use Database\Seeders\Suppliers\SupplierSeeder;
use Database\Seeders\Suppliers\SupplierSourceSeeder;
use Database\Seeders\Suppliers\SupplierSourceOptionSeeder;
use Database\Seeders\Suppliers\SupplierSourceTemplateSeeder;
use Database\Seeders\Suppliers\SupplierPromptSeeder;
use Database\Seeders\Suppliers\SupplierAutomationSettingSeeder;

// Helpdesk Seeders
use Database\Seeders\Helpdesk\HelpdeskTicketStatusSeeder;
use Database\Seeders\Helpdesk\HelpdeskConversationStatusSeeder;
use Database\Seeders\Helpdesk\HelpdeskTicketCategorySeeder;
use Database\Seeders\Helpdesk\HelpdeskGroupSeeder;
use Database\Seeders\Helpdesk\HelpdeskTicketSlaPolicySeeder;
use Database\Seeders\Helpdesk\HelpdeskCannedReplySeeder;
use Database\Seeders\Helpdesk\HelpCenterSeeder;

// Return Seeders
use Database\Seeders\Returns\ReturnPolicySeeder;

// Permissions & Optional Seeders
use Database\Seeders\Permissions\CompleteRolesAndPermissionsSeeder;
use Database\Seeders\Permissions\RolesAndUsersSeeder;
use Database\Seeders\Campaigns\CampaignSeeder;

// Webhooks
use Modules\Webhook\Database\Seeders\WebhookEventCatalogSeeder;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * Seeding order is CRITICAL - dependencies must run before dependents.
     *
     * PHASE 1: FOUNDATIONAL (No dependencies)
     * - Languages must exist before any translations
     * - Countries for return/warehouse locations
     * - Categories for products and policies
     * - Webhook events catalog for integrations
     *
     * PHASE 2: CORE CONFIGURATIONS
     * - Warehouse infrastructure (locations, styles)
     * - Document statuses and sources
     * - Mail variables and templates
     *
     * PHASE 3: DOMAIN-SPECIFIC
     * - Document validator groups and configurations
     * - Return policies
     * - Helpdesk statuses, groups, and SLA policies
     *
     * PHASE 4: ADVANCED FEATURES
     * - Supplier automation
     * - Integrations
     */
    public function run(): void
    {
        $this->call([
            // ========================================
            // PHASE 1: FOUNDATIONAL (No dependencies)
            // ========================================

            // Core system data
            LangSeeder::class,
            CountrySeeder::class,
            CategorySeeder::class,
            WebhookEventCatalogSeeder::class,
            MailVariableSeeder::class,
            MediaFolderSeeder::class,

            // ========================================
            // PHASE 2: WAREHOUSE INFRASTRUCTURE
            // ========================================
            WarehouseLocationStyleSeeder::class,
            WarehouseLocationConditionSeeder::class,
            WarehouseExampleSeeder::class,
            WarehouseSeedersV2::class,
            Coruna1LocationsSeeder::class,

            // ========================================
            // PHASE 3: DOCUMENT SYSTEM
            // ========================================
            DocumentConfigurationSeeder::class,
            DocumentUploadTypeSeeder::class,

            // Document Catalog (independent)
            DocumentStatusSeeder::class,
            DocumentSourceSeeder::class,
            DocumentLoadSeeder::class,
            DocumentSyncSeeder::class,

            // Document Groups & Transitions (depends on DocumentStatusSeeder)
            DocumentGroupSeeder::class,
            DocumentStatusTransitionSeeder::class,

            // Document Validation (depends on DocumentStatusSeeder)
            DocumentValidatorGroupSeeder::class,
            DocumentValidatorGroupConfigurationSeeder::class,
            StageEmailActionSeeder::class,

            // ========================================
            // PHASE 4: RETURN SYSTEM
            // ========================================
            ReturnPolicySeeder::class,

            // ========================================
            // PHASE 5: HELPDESK SYSTEM
            // ========================================
            HelpdeskTicketStatusSeeder::class,
            HelpdeskConversationStatusSeeder::class,
            HelpdeskTicketCategorySeeder::class,
            HelpdeskGroupSeeder::class,
            HelpdeskTicketSlaPolicySeeder::class,
            HelpdeskCannedReplySeeder::class,
            HelpCenterSeeder::class,

            // ========================================
            // PHASE 6: SUPPLIER & AUTOMATION
            // ========================================
            SupplierSeeder::class,
            SupplierSourceSeeder::class,
            SupplierSourceOptionSeeder::class,
            SupplierSourceTemplateSeeder::class,
            SupplierPromptSeeder::class,
            SupplierAutomationSettingSeeder::class,

            // ========================================
            // PHASE 7: ROLES & PERMISSIONS
            // ========================================
            CreateDocumentPermissionsSeeder::class,
            RolesAndUsersSeeder::class,

            // ========================================
            // PHASE 8: OPTIONAL FEATURES
            // ========================================
            // (Uncomment these to enable)
            // CompleteRolesAndPermissionsSeeder::class,
            // CampaignSeeder::class,
        ]);
    }
}
