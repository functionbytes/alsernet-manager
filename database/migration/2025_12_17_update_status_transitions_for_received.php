<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Updates status transitions to:
     * 1. Add transitions for new "received" status (Documentos Recibidos)
     * 2. Replace "completed" transitions with "approved"
     * 3. Fix transition flow for new status hierarchy
     */
    public function up(): void
    {
        $statuses = DB::table('document_statuses')
            ->whereIn('key', ['pending', 'awaiting_documents', 'received', 'incomplete', 'approved', 'rejected', 'cancelled', 'completed'])
            ->pluck('id', 'key');

        // Delete old "completed" transitions
        DB::table('document_status_transitions')
            ->where('to_status_id', $statuses['completed'])
            ->delete();

        // Update "completed" transitions to use "approved" instead
        DB::table('document_status_transitions')
            ->where('from_status_id', $statuses['completed'])
            ->delete();

        // Add transitions FROM pending
        DB::table('document_status_transitions')->insertOrIgnore([
            [
                'from_status_id' => $statuses['pending'],
                'to_status_id' => $statuses['received'],
                'permission' => null,
                'requires_all_documents_uploaded' => false,
                'auto_transition_after_days' => null,
                'is_active' => true,
            ],
        ]);

        // Add transitions FROM awaiting_documents
        DB::table('document_status_transitions')->insertOrIgnore([
            [
                'from_status_id' => $statuses['awaiting_documents'],
                'to_status_id' => $statuses['received'],
                'permission' => null,
                'requires_all_documents_uploaded' => false,
                'auto_transition_after_days' => null,
                'is_active' => true,
            ],
        ]);

        // Add transitions FROM received (NEW STATUS)
        $receivedTransitions = [
            [
                'from_status_id' => $statuses['received'],
                'to_status_id' => $statuses['approved'],
                'permission' => null,
                'requires_all_documents_uploaded' => true,
                'auto_transition_after_days' => null,
                'is_active' => true,
            ],
            [
                'from_status_id' => $statuses['received'],
                'to_status_id' => $statuses['incomplete'],
                'permission' => null,
                'requires_all_documents_uploaded' => false,
                'auto_transition_after_days' => null,
                'is_active' => true,
            ],
            [
                'from_status_id' => $statuses['received'],
                'to_status_id' => $statuses['rejected'],
                'permission' => null,
                'requires_all_documents_uploaded' => false,
                'auto_transition_after_days' => null,
                'is_active' => true,
            ],
            [
                'from_status_id' => $statuses['received'],
                'to_status_id' => $statuses['cancelled'],
                'permission' => null,
                'requires_all_documents_uploaded' => false,
                'auto_transition_after_days' => null,
                'is_active' => true,
            ],
        ];

        foreach ($receivedTransitions as $transition) {
            DB::table('document_status_transitions')->insertOrIgnore($transition);
        }

        // Add transitions FROM incomplete
        DB::table('document_status_transitions')->insertOrIgnore([
            [
                'from_status_id' => $statuses['incomplete'],
                'to_status_id' => $statuses['received'],
                'permission' => null,
                'requires_all_documents_uploaded' => false,
                'auto_transition_after_days' => null,
                'is_active' => true,
            ],
            [
                'from_status_id' => $statuses['incomplete'],
                'to_status_id' => $statuses['approved'],
                'permission' => null,
                'requires_all_documents_uploaded' => true,
                'auto_transition_after_days' => null,
                'is_active' => true,
            ],
        ]);

        // Update approved → approved to approved → (nothing, it's final)
        // Change: approved can only go to rejected or cancelled (for corrections)
        DB::table('document_status_transitions')
            ->where('from_status_id', $statuses['approved'])
            ->where('to_status_id', $statuses['completed'])
            ->delete();

        // Ensure approved transitions are correct
        DB::table('document_status_transitions')->insertOrIgnore([
            [
                'from_status_id' => $statuses['approved'],
                'to_status_id' => $statuses['rejected'],
                'permission' => null,
                'requires_all_documents_uploaded' => false,
                'auto_transition_after_days' => null,
                'is_active' => true,
            ],
            [
                'from_status_id' => $statuses['approved'],
                'to_status_id' => $statuses['cancelled'],
                'permission' => null,
                'requires_all_documents_uploaded' => false,
                'auto_transition_after_days' => null,
                'is_active' => true,
            ],
        ]);

        // Update pending → incomplete to pending → awaiting_documents
        // Since "pending" now means initial request sent, next should be awaiting
        DB::table('document_status_transitions')
            ->where('from_status_id', $statuses['pending'])
            ->where('to_status_id', $statuses['incomplete'])
            ->delete();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This is a structural change that's hard to revert properly
        // Keeping all transitions for safety
    }
};
