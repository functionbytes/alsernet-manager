<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DocumentStatusTransitionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Define valid transitions
        $transitions = [
            // From PENDING
            ['from' => 'pending', 'to' => 'incomplete', 'permission' => null],
            ['from' => 'pending', 'to' => 'awaiting_documents', 'permission' => null],
            ['from' => 'pending', 'to' => 'cancelled', 'permission' => null],

            // From INCOMPLETE
            ['from' => 'incomplete', 'to' => 'awaiting_documents', 'permission' => null],
            ['from' => 'incomplete', 'to' => 'rejected', 'permission' => 'documents.reject'],
            ['from' => 'incomplete', 'to' => 'cancelled', 'permission' => null],

            // From AWAITING_DOCUMENTS
            ['from' => 'awaiting_documents', 'to' => 'approved', 'permission' => 'documents.approve', 'requires_all_documents_uploaded' => true],
            ['from' => 'awaiting_documents', 'to' => 'incomplete', 'permission' => null],
            ['from' => 'awaiting_documents', 'to' => 'cancelled', 'permission' => null],

            // From APPROVED
            ['from' => 'approved', 'to' => 'completed', 'permission' => 'documents.complete'],
            ['from' => 'approved', 'to' => 'rejected', 'permission' => 'documents.reject'],

            // From REJECTED
            ['from' => 'rejected', 'to' => 'awaiting_documents', 'permission' => null],
            ['from' => 'rejected', 'to' => 'cancelled', 'permission' => null],

            // From COMPLETED (final state - no transitions)

            // From CANCELLED (final state - no transitions)
        ];

        foreach ($transitions as $transition) {
            $fromStatus = \App\Models\Document\DocumentStatus::where('key', $transition['from'])->first();
            $toStatus = \App\Models\Document\DocumentStatus::where('key', $transition['to'])->first();

            if ($fromStatus && $toStatus) {
                \App\Models\Document\DocumentStatusTransition::firstOrCreate(
                    [
                        'from_status_id' => $fromStatus->id,
                        'to_status_id' => $toStatus->id,
                    ],
                    [
                        'permission' => $transition['permission'] ?? null,
                        'requires_all_documents_uploaded' => $transition['requires_all_documents_uploaded'] ?? false,
                        'is_active' => true,
                    ]
                );
            }
        }
    }
}
