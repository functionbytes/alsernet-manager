<?php

namespace Modules\Warehouse\Tests\Unit\Entities;

use Modules\Warehouse\Entities\Warehouse;
use Modules\Warehouse\Entities\WarehouseFloor;
use Modules\Warehouse\Entities\WarehouseLocation;
use Modules\Warehouse\Entities\WarehouseLocationSection;
use Modules\Warehouse\Entities\WarehouseLocationStyle;
use Modules\Warehouse\Entities\WarehouseInventorySlot;
use Modules\Warehouse\Entities\WarehouseInventoryOperation;
use Modules\Warehouse\Entities\WarehouseOperationItem;
use App\Models\User;
use Tests\TestCase;
use Illuminate\Support\Str;

class WarehouseInventoryOperationTest extends TestCase
{
    /**
     * Test: WarehouseInventoryOperation has correct fillable attributes
     */
    public function test_operation_has_correct_fillable_attributes(): void
    {
        $operation = new WarehouseInventoryOperation();

        $expectedFillable = [
            'uid',
            'warehouse_id',
            'user_id',
            'started_at',
            'closed_at',
            'closed_by',
            'description',
            'status',
            'total_items',
            'validated_items',
            'discrepancy_items',
        ];

        $this->assertEquals($expectedFillable, $operation->getFillable());
    }

    /**
     * Test: WarehouseInventoryOperation has correct casts
     */
    public function test_operation_has_correct_casts(): void
    {
        $operation = new WarehouseInventoryOperation();
        $casts = $operation->getCasts();

        $this->assertEquals('datetime', $casts['started_at']);
        $this->assertEquals('datetime', $casts['closed_at']);
        $this->assertEquals('integer', $casts['total_items']);
        $this->assertEquals('integer', $casts['validated_items']);
        $this->assertEquals('integer', $casts['discrepancy_items']);
    }

    /**
     * Test: WarehouseInventoryOperation has correct status constants
     */
    public function test_operation_has_correct_status_constants(): void
    {
        $this->assertEquals('pending', WarehouseInventoryOperation::STATUS_PENDING);
        $this->assertEquals('in_progress', WarehouseInventoryOperation::STATUS_IN_PROGRESS);
        $this->assertEquals('completed', WarehouseInventoryOperation::STATUS_COMPLETED);
        $this->assertEquals('cancelled', WarehouseInventoryOperation::STATUS_CANCELLED);
    }

    /**
     * Test: WarehouseInventoryOperation belongsTo warehouse
     */
    public function test_operation_belongs_to_warehouse(): void
    {
        $warehouse = Warehouse::factory()->create();
        $user = User::factory()->create();
        $operation = WarehouseInventoryOperation::factory()->create([
            'warehouse_id' => $warehouse->id,
            'user_id' => $user->id,
        ]);

        $this->assertInstanceOf(Warehouse::class, $operation->warehouse);
        $this->assertEquals($warehouse->id, $operation->warehouse->id);
    }

    /**
     * Test: WarehouseInventoryOperation belongsTo user
     */
    public function test_operation_belongs_to_user(): void
    {
        $warehouse = Warehouse::factory()->create();
        $user = User::factory()->create();
        $operation = WarehouseInventoryOperation::factory()->create([
            'warehouse_id' => $warehouse->id,
            'user_id' => $user->id,
        ]);

        $this->assertInstanceOf(User::class, $operation->user);
        $this->assertEquals($user->id, $operation->user->id);
    }

    /**
     * Test: WarehouseInventoryOperation belongsTo closedByUser
     */
    public function test_operation_belongs_to_closed_by_user(): void
    {
        $warehouse = Warehouse::factory()->create();
        $user = User::factory()->create();
        $closedByUser = User::factory()->create();
        $operation = WarehouseInventoryOperation::factory()->create([
            'warehouse_id' => $warehouse->id,
            'user_id' => $user->id,
            'closed_by' => $closedByUser->id,
        ]);

        $this->assertInstanceOf(User::class, $operation->closedByUser);
        $this->assertEquals($closedByUser->id, $operation->closedByUser->id);
    }

    /**
     * Test: WarehouseInventoryOperation hasMany items
     */
    public function test_operation_has_many_items(): void
    {
        $warehouse = Warehouse::factory()->create();
        $user = User::factory()->create();
        $operation = WarehouseInventoryOperation::factory()->create([
            'warehouse_id' => $warehouse->id,
            'user_id' => $user->id,
        ]);

        WarehouseOperationItem::factory(3)->create(['operation_id' => $operation->id]);

        $items = $operation->items()->get();

        $this->assertCount(3, $items);
    }

    /**
     * Test: pending scope returns only pending operations
     */
    public function test_pending_scope_returns_pending_operations(): void
    {
        $warehouse = Warehouse::factory()->create();
        $user = User::factory()->create();

        WarehouseInventoryOperation::factory(2)->create([
            'warehouse_id' => $warehouse->id,
            'user_id' => $user->id,
            'status' => 'pending',
        ]);
        WarehouseInventoryOperation::factory()->create([
            'warehouse_id' => $warehouse->id,
            'user_id' => $user->id,
            'status' => 'completed',
        ]);

        $pending = WarehouseInventoryOperation::pending()->get();

        $this->assertCount(2, $pending);
        $pending->each(fn ($op) => $this->assertEquals('pending', $op->status));
    }

    /**
     * Test: inProgress scope returns only in_progress operations
     */
    public function test_in_progress_scope_returns_in_progress_operations(): void
    {
        $warehouse = Warehouse::factory()->create();
        $user = User::factory()->create();

        WarehouseInventoryOperation::factory(2)->create([
            'warehouse_id' => $warehouse->id,
            'user_id' => $user->id,
            'status' => 'in_progress',
        ]);
        WarehouseInventoryOperation::factory()->create([
            'warehouse_id' => $warehouse->id,
            'user_id' => $user->id,
            'status' => 'completed',
        ]);

        $inProgress = WarehouseInventoryOperation::inProgress()->get();

        $this->assertCount(2, $inProgress);
        $inProgress->each(fn ($op) => $this->assertEquals('in_progress', $op->status));
    }

    /**
     * Test: completed scope returns only completed operations
     */
    public function test_completed_scope_returns_completed_operations(): void
    {
        $warehouse = Warehouse::factory()->create();
        $user = User::factory()->create();

        WarehouseInventoryOperation::factory(2)->create([
            'warehouse_id' => $warehouse->id,
            'user_id' => $user->id,
            'status' => 'completed',
        ]);
        WarehouseInventoryOperation::factory()->create([
            'warehouse_id' => $warehouse->id,
            'user_id' => $user->id,
            'status' => 'pending',
        ]);

        $completed = WarehouseInventoryOperation::completed()->get();

        $this->assertCount(2, $completed);
    }

    /**
     * Test: active scope returns pending and in_progress operations
     */
    public function test_active_scope_returns_pending_and_in_progress(): void
    {
        $warehouse = Warehouse::factory()->create();
        $user = User::factory()->create();

        WarehouseInventoryOperation::factory()->create([
            'warehouse_id' => $warehouse->id,
            'user_id' => $user->id,
            'status' => 'pending',
        ]);
        WarehouseInventoryOperation::factory()->create([
            'warehouse_id' => $warehouse->id,
            'user_id' => $user->id,
            'status' => 'in_progress',
        ]);
        WarehouseInventoryOperation::factory()->create([
            'warehouse_id' => $warehouse->id,
            'user_id' => $user->id,
            'status' => 'completed',
        ]);

        $active = WarehouseInventoryOperation::active()->get();

        $this->assertCount(2, $active);
    }

    /**
     * Test: byWarehouse scope filters by warehouse
     */
    public function test_by_warehouse_scope_filters_correctly(): void
    {
        $warehouse1 = Warehouse::factory()->create();
        $warehouse2 = Warehouse::factory()->create();
        $user = User::factory()->create();

        WarehouseInventoryOperation::factory(3)->create([
            'warehouse_id' => $warehouse1->id,
            'user_id' => $user->id,
        ]);
        WarehouseInventoryOperation::factory(2)->create([
            'warehouse_id' => $warehouse2->id,
            'user_id' => $user->id,
        ]);

        $operations = WarehouseInventoryOperation::byWarehouse($warehouse1->id)->get();

        $this->assertCount(3, $operations);
        $operations->each(fn ($op) => $this->assertEquals($warehouse1->id, $op->warehouse_id));
    }

    /**
     * Test: getActiveByWarehouse returns active operation for warehouse
     */
    public function test_get_active_by_warehouse_returns_active_operation(): void
    {
        $warehouse = Warehouse::factory()->create();
        $user = User::factory()->create();
        $operation = WarehouseInventoryOperation::factory()->create([
            'warehouse_id' => $warehouse->id,
            'user_id' => $user->id,
            'closed_at' => null,
        ]);

        $active = WarehouseInventoryOperation::getActiveByWarehouse($warehouse->id);

        $this->assertNotNull($active);
        $this->assertEquals($operation->id, $active->id);
    }

    /**
     * Test: getActiveByWarehouse returns null when no active operation
     */
    public function test_get_active_by_warehouse_returns_null_when_closed(): void
    {
        $warehouse = Warehouse::factory()->create();
        $user = User::factory()->create();
        WarehouseInventoryOperation::factory()->create([
            'warehouse_id' => $warehouse->id,
            'user_id' => $user->id,
            'closed_at' => now(),
        ]);

        $active = WarehouseInventoryOperation::getActiveByWarehouse($warehouse->id);

        $this->assertNull($active);
    }

    /**
     * Test: startProgress changes status from pending to in_progress
     */
    public function test_start_progress_changes_status(): void
    {
        $warehouse = Warehouse::factory()->create();
        $user = User::factory()->create();
        $operation = WarehouseInventoryOperation::factory()->create([
            'warehouse_id' => $warehouse->id,
            'user_id' => $user->id,
            'status' => 'pending',
        ]);

        $operation->startProgress();
        $operation->refresh();

        $this->assertEquals('in_progress', $operation->status);
    }

    /**
     * Test: startProgress does nothing if already in progress
     */
    public function test_start_progress_does_nothing_if_already_in_progress(): void
    {
        $warehouse = Warehouse::factory()->create();
        $user = User::factory()->create();
        $operation = WarehouseInventoryOperation::factory()->create([
            'warehouse_id' => $warehouse->id,
            'user_id' => $user->id,
            'status' => 'in_progress',
        ]);

        $operation->startProgress();
        $operation->refresh();

        $this->assertEquals('in_progress', $operation->status);
    }

    /**
     * Test: close marks operation as completed
     */
    public function test_close_marks_operation_as_completed(): void
    {
        $warehouse = Warehouse::factory()->create();
        $user = User::factory()->create();
        $closedByUser = User::factory()->create();
        $operation = WarehouseInventoryOperation::factory()->create([
            'warehouse_id' => $warehouse->id,
            'user_id' => $user->id,
            'status' => 'in_progress',
        ]);

        $result = $operation->close($closedByUser->id);
        $operation->refresh();

        $this->assertTrue($result);
        $this->assertEquals('completed', $operation->status);
        $this->assertNotNull($operation->closed_at);
        $this->assertEquals($closedByUser->id, $operation->closed_by);
    }

    /**
     * Test: cancel marks operation as cancelled
     */
    public function test_cancel_marks_operation_as_cancelled(): void
    {
        $warehouse = Warehouse::factory()->create();
        $user = User::factory()->create();
        $cancelledByUser = User::factory()->create();
        $operation = WarehouseInventoryOperation::factory()->create([
            'warehouse_id' => $warehouse->id,
            'user_id' => $user->id,
            'status' => 'in_progress',
        ]);

        $result = $operation->cancel('Reason for cancellation', $cancelledByUser->id);
        $operation->refresh();

        $this->assertTrue($result);
        $this->assertEquals('cancelled', $operation->status);
        $this->assertNotNull($operation->closed_at);
    }

    /**
     * Test: updateStats calculates correct counts
     */
    public function test_update_stats_calculates_correct_counts(): void
    {
        $warehouse = Warehouse::factory()->create();
        $user = User::factory()->create();
        $operation = WarehouseInventoryOperation::factory()->create([
            'warehouse_id' => $warehouse->id,
            'user_id' => $user->id,
            'total_items' => 0,
            'validated_items' => 0,
            'discrepancy_items' => 0,
        ]);

        WarehouseOperationItem::factory(2)->create(['operation_id' => $operation->id, 'status' => 'validated']);
        WarehouseOperationItem::factory(1)->create(['operation_id' => $operation->id, 'status' => 'discrepancy']);
        WarehouseOperationItem::factory(2)->create(['operation_id' => $operation->id, 'status' => 'pending']);

        $operation->updateStats();
        $operation->refresh();

        $this->assertEquals(5, $operation->total_items);
        $this->assertEquals(2, $operation->validated_items);
        $this->assertEquals(1, $operation->discrepancy_items);
    }

    /**
     * Test: checkCompletion returns true when all items processed
     */
    public function test_check_completion_returns_true_when_all_processed(): void
    {
        $warehouse = Warehouse::factory()->create();
        $user = User::factory()->create();
        $operation = WarehouseInventoryOperation::factory()->create([
            'warehouse_id' => $warehouse->id,
            'user_id' => $user->id,
            'total_items' => 3,
            'status' => 'in_progress',
        ]);

        WarehouseOperationItem::factory(2)->create(['operation_id' => $operation->id, 'status' => 'validated']);
        WarehouseOperationItem::factory(1)->create(['operation_id' => $operation->id, 'status' => 'discrepancy']);

        $result = $operation->checkCompletion();
        $operation->refresh();

        $this->assertTrue($result);
        $this->assertEquals('completed', $operation->status);
    }

    /**
     * Test: checkCompletion returns false when items still pending
     */
    public function test_check_completion_returns_false_when_pending_items(): void
    {
        $warehouse = Warehouse::factory()->create();
        $user = User::factory()->create();
        $operation = WarehouseInventoryOperation::factory()->create([
            'warehouse_id' => $warehouse->id,
            'user_id' => $user->id,
            'total_items' => 3,
        ]);

        WarehouseOperationItem::factory(2)->create(['operation_id' => $operation->id, 'status' => 'validated']);
        WarehouseOperationItem::factory(1)->create(['operation_id' => $operation->id, 'status' => 'pending']);

        $result = $operation->checkCompletion();

        $this->assertFalse($result);
    }

    /**
     * Test: getProgressPercentage returns correct percentage
     */
    public function test_get_progress_percentage_returns_correct_value(): void
    {
        $warehouse = Warehouse::factory()->create();
        $user = User::factory()->create();
        $operation = WarehouseInventoryOperation::factory()->create([
            'warehouse_id' => $warehouse->id,
            'user_id' => $user->id,
            'total_items' => 10,
        ]);

        WarehouseOperationItem::factory(5)->create(['operation_id' => $operation->id, 'status' => 'validated']);
        WarehouseOperationItem::factory(3)->create(['operation_id' => $operation->id, 'status' => 'discrepancy']);
        WarehouseOperationItem::factory(2)->create(['operation_id' => $operation->id, 'status' => 'pending']);

        $percentage = $operation->getProgressPercentage();

        $this->assertEquals(80, $percentage);
    }

    /**
     * Test: getProgressPercentage returns zero when no items
     */
    public function test_get_progress_percentage_returns_zero_when_no_items(): void
    {
        $warehouse = Warehouse::factory()->create();
        $user = User::factory()->create();
        $operation = WarehouseInventoryOperation::factory()->create([
            'warehouse_id' => $warehouse->id,
            'user_id' => $user->id,
            'total_items' => 0,
        ]);

        $percentage = $operation->getProgressPercentage();

        $this->assertEquals(0, $percentage);
    }

    /**
     * Test: getSummary returns correct array structure
     */
    public function test_get_summary_returns_correct_structure(): void
    {
        $warehouse = Warehouse::factory()->create();
        $user = User::factory()->create();
        $operation = WarehouseInventoryOperation::factory()->create([
            'warehouse_id' => $warehouse->id,
            'user_id' => $user->id,
            'total_items' => 5,
            'validated_items' => 3,
            'discrepancy_items' => 1,
        ]);

        $summary = $operation->getSummary();

        $this->assertIsArray($summary);
        $this->assertArrayHasKey('id', $summary);
        $this->assertArrayHasKey('uid', $summary);
        $this->assertArrayHasKey('warehouse', $summary);
        $this->assertArrayHasKey('status', $summary);
        $this->assertArrayHasKey('progress', $summary);
        $this->assertArrayHasKey('user', $summary);
    }

    /**
     * Test: getStatusColor returns correct color for each status
     */
    public function test_get_status_color_returns_correct_colors(): void
    {
        $warehouse = Warehouse::factory()->create();
        $user = User::factory()->create();

        $pending = WarehouseInventoryOperation::factory()->create([
            'warehouse_id' => $warehouse->id,
            'user_id' => $user->id,
            'status' => 'pending',
        ]);
        $inProgress = WarehouseInventoryOperation::factory()->create([
            'warehouse_id' => $warehouse->id,
            'user_id' => $user->id,
            'status' => 'in_progress',
        ]);
        $completed = WarehouseInventoryOperation::factory()->create([
            'warehouse_id' => $warehouse->id,
            'user_id' => $user->id,
            'status' => 'completed',
        ]);
        $cancelled = WarehouseInventoryOperation::factory()->create([
            'warehouse_id' => $warehouse->id,
            'user_id' => $user->id,
            'status' => 'cancelled',
        ]);

        $this->assertEquals('#FFC107', $pending->getStatusColor());
        $this->assertEquals('#17A2B8', $inProgress->getStatusColor());
        $this->assertEquals('#28A745', $completed->getStatusColor());
        $this->assertEquals('#DC3545', $cancelled->getStatusColor());
    }

    /**
     * Test: getStatusLabel returns correct label for each status
     */
    public function test_get_status_label_returns_correct_labels(): void
    {
        $warehouse = Warehouse::factory()->create();
        $user = User::factory()->create();

        $pending = WarehouseInventoryOperation::factory()->create([
            'warehouse_id' => $warehouse->id,
            'user_id' => $user->id,
            'status' => 'pending',
        ]);
        $inProgress = WarehouseInventoryOperation::factory()->create([
            'warehouse_id' => $warehouse->id,
            'user_id' => $user->id,
            'status' => 'in_progress',
        ]);
        $completed = WarehouseInventoryOperation::factory()->create([
            'warehouse_id' => $warehouse->id,
            'user_id' => $user->id,
            'status' => 'completed',
        ]);

        $this->assertEquals('Pendiente', $pending->getStatusLabel());
        $this->assertEquals('En Progreso', $inProgress->getStatusLabel());
        $this->assertEquals('Completada', $completed->getStatusLabel());
    }

    /**
     * Test: WarehouseInventoryOperation auto-generates UUID on creation
     */
    public function test_operation_auto_generates_uuid(): void
    {
        $warehouse = Warehouse::factory()->create();
        $user = User::factory()->create();
        $operation = WarehouseInventoryOperation::factory()->create([
            'warehouse_id' => $warehouse->id,
            'user_id' => $user->id,
        ]);

        $this->assertNotNull($operation->uid);
        $this->assertTrue(Str::isUuid($operation->uid));
    }

    /**
     * Test: WarehouseInventoryOperation table configuration
     */
    public function test_operation_table_configuration(): void
    {
        $operation = new WarehouseInventoryOperation();

        $this->assertEquals('warehouse_inventory_operations', $operation->getTable());
    }
}
