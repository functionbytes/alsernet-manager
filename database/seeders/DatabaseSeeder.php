<?php

namespace Database\Seeders;

use Database\Seeders\Core\CategorySeeder;
use Database\Seeders\Core\CountrySeeder;
use Database\Seeders\Core\LangSeeder;
use Database\Seeders\Core\MailVariableSeeder;
use Database\Seeders\Core\MediaFolderSeeder;
use Illuminate\Database\Seeder;
use Modules\Document\Database\Seeders\CreateDocumentPermissionsSeeder;
use Modules\Document\Database\Seeders\DocumentConfigurationSeeder;
use Modules\Document\Database\Seeders\DocumentGroupSeeder;
use Modules\Document\Database\Seeders\DocumentLoadSeeder;
use Modules\Document\Database\Seeders\DocumentSourceSeeder;
use Modules\Document\Database\Seeders\DocumentStatusSeeder;
use Modules\Document\Database\Seeders\DocumentStatusTransitionSeeder;
use Modules\Document\Database\Seeders\DocumentSyncSeeder;
use Modules\Document\Database\Seeders\DocumentUploadTypeSeeder;
use Modules\Document\Database\Seeders\DocumentValidatorGroupConfigurationSeeder;
use Modules\Document\Database\Seeders\DocumentValidatorGroupSeeder;
use Modules\Document\Database\Seeders\StageEmailActionSeeder;
use Modules\Helpdesk\Database\Seeders\HelpCenterSeeder;
use Modules\Helpdesk\Database\Seeders\HelpdeskCannedReplySeeder;
use Modules\Helpdesk\Database\Seeders\HelpdeskConversationStatusSeeder;
use Modules\Helpdesk\Database\Seeders\HelpdeskGroupSeeder;
use Modules\Helpdesk\Database\Seeders\HelpdeskTicketCategorySeeder;
use Modules\Helpdesk\Database\Seeders\HelpdeskTicketSlaPolicySeeder;
use Modules\Helpdesk\Database\Seeders\HelpdeskTicketStatusSeeder;
use Modules\Warehouse\Database\Seeders\Coruna1LocationsSeeder;
use Modules\Warehouse\Database\Seeders\WarehouseExampleSeeder;
use Modules\Warehouse\Database\Seeders\WarehouseLocationConditionSeeder;
use Modules\Warehouse\Database\Seeders\WarehouseLocationStyleSeeder;
use Modules\Warehouse\Database\Seeders\WarehouseSeedersV2;
use Modules\Webhook\Database\Seeders\WebhookEventCatalogSeeder;
use seeders\ReturnPolicySeeder;
use seeders\RolesAndUsersSeeder;
use seeders\SupplierAutomationSettingSeeder;
use seeders\SupplierPromptSeeder;
use seeders\SupplierSeeder;
use seeders\SupplierSourceOptionSeeder;
use seeders\SupplierSourceSeeder;
use seeders\SupplierSourceTemplateSeeder;

// Warehouse Seeders

// Document Seeders

// Supplier Seeders

// Helpdesk Seeders

// Return Seeders

// Permissions & Optional Seeders

// Webhooks

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
