<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Soft Deletes on Master Data tables
        if (Schema::hasTable('vendors') && !Schema::hasColumn('vendors', 'deleted_at')) {
            Schema::table('vendors', function (Blueprint $table) {
                $table->softDeletes();
            });
        }

        if (Schema::hasTable('departments') && !Schema::hasColumn('departments', 'deleted_at')) {
            Schema::table('departments', function (Blueprint $table) {
                $table->softDeletes();
            });
        }

        if (Schema::hasTable('measurement_units') && !Schema::hasColumn('measurement_units', 'deleted_at')) {
            Schema::table('measurement_units', function (Blueprint $table) {
                $table->softDeletes();
            });
        }

        if (Schema::hasTable('users') && !Schema::hasColumn('users', 'deleted_at')) {
            Schema::table('users', function (Blueprint $table) {
                $table->softDeletes();
            });
        }

        // 2. Short Close fields on Purchase Orders
        if (Schema::hasTable('purchase_orders')) {
            Schema::table('purchase_orders', function (Blueprint $table) {
                if (!Schema::hasColumn('purchase_orders', 'is_short_closed')) {
                    $table->boolean('is_short_closed')->default(false)->after('status');
                }
                if (!Schema::hasColumn('purchase_orders', 'short_closed_at')) {
                    $table->timestamp('short_closed_at')->nullable()->after('is_short_closed');
                }
                if (!Schema::hasColumn('purchase_orders', 'short_closed_by')) {
                    $table->foreignId('short_closed_by')->nullable()->constrained('users')->nullOnDelete()->after('short_closed_at');
                }
                if (!Schema::hasColumn('purchase_orders', 'short_close_reason')) {
                    $table->text('short_close_reason')->nullable()->after('short_closed_by');
                }
            });
        }

        // 3. Approver Re-assignment on PR Approvals
        if (Schema::hasTable('pr_approvals')) {
            Schema::table('pr_approvals', function (Blueprint $table) {
                if (!Schema::hasColumn('pr_approvals', 'assigned_approver_id')) {
                    $table->foreignId('assigned_approver_id')->nullable()->constrained('users')->nullOnDelete()->after('department_id');
                }
                if (!Schema::hasColumn('pr_approvals', 'reassigned_at')) {
                    $table->timestamp('reassigned_at')->nullable()->after('assigned_approver_id');
                }
                if (!Schema::hasColumn('pr_approvals', 'reassigned_by')) {
                    $table->foreignId('reassigned_by')->nullable()->constrained('users')->nullOnDelete()->after('reassigned_at');
                }
                if (!Schema::hasColumn('pr_approvals', 'reassign_reason')) {
                    $table->text('reassign_reason')->nullable()->after('reassigned_by');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('vendors') && Schema::hasColumn('vendors', 'deleted_at')) {
            Schema::table('vendors', function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        }

        if (Schema::hasTable('departments') && Schema::hasColumn('departments', 'deleted_at')) {
            Schema::table('departments', function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        }

        if (Schema::hasTable('measurement_units') && Schema::hasColumn('measurement_units', 'deleted_at')) {
            Schema::table('measurement_units', function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        }

        if (Schema::hasTable('users') && Schema::hasColumn('users', 'deleted_at')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        }

        if (Schema::hasTable('purchase_orders')) {
            Schema::table('purchase_orders', function (Blueprint $table) {
                $table->dropForeign(['short_closed_by']);
                $table->dropColumn(['is_short_closed', 'short_closed_at', 'short_closed_by', 'short_close_reason']);
            });
        }

        if (Schema::hasTable('pr_approvals')) {
            Schema::table('pr_approvals', function (Blueprint $table) {
                $table->dropForeign(['assigned_approver_id']);
                $table->dropForeign(['reassigned_by']);
                $table->dropColumn(['assigned_approver_id', 'reassigned_at', 'reassigned_by', 'reassign_reason']);
            });
        }
    }
};
