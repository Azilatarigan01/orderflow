<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Create acting_delegations table for Plt (Acting Approver) management
        Schema::create('acting_delegations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delegator_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('delegatee_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('role_delegated', 50); // e.g. 'manager', 'finance', 'hod'
            $table->date('start_date');
            $table->date('end_date');
            $table->string('reason'); // e.g. 'Cuti Tahunan / Dinas Luar Kota'
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['delegator_user_id', 'is_active', 'start_date', 'end_date'], 'acting_active_idx');
        });

        // 2. Add Plt acting tracking columns to pr_approvals
        Schema::table('pr_approvals', function (Blueprint $table) {
            $table->boolean('is_acting')->default(false)->after('approver_id');
            $table->foreignId('acting_for_user_id')->nullable()->after('is_acting')->constrained('users')->nullOnDelete();
        });

        // 3. Add SHA-256 tamper-evident integrity hash columns to audit_trails
        Schema::table('audit_trails', function (Blueprint $table) {
            $table->string('previous_hash', 64)->nullable()->after('user_agent');
            $table->string('record_hash', 64)->nullable()->after('previous_hash');
        });

        // 4. Backfill existing audit trail rows with a valid deterministic hash chain
        $trails = DB::table('audit_trails')->orderBy('id')->get();
        $prevHash = hash('sha256', 'ORDERFLOW_GENESIS_BLOCK');

        foreach ($trails as $trail) {
            $payload = implode('|', [
                $prevHash,
                $trail->user_id ?? '0',
                $trail->action,
                $trail->entity_type,
                $trail->entity_id,
                $trail->before_state ?? '',
                $trail->after_state ?? '',
                $trail->created_at,
            ]);
            $recordHash = hash('sha256', $payload);

            DB::table('audit_trails')->where('id', $trail->id)->update([
                'previous_hash' => $prevHash,
                'record_hash'   => $recordHash,
            ]);

            $prevHash = $recordHash;
        }
    }

    public function down(): void
    {
        Schema::table('audit_trails', function (Blueprint $table) {
            $table->dropColumn(['previous_hash', 'record_hash']);
        });

        Schema::table('pr_approvals', function (Blueprint $table) {
            $table->dropForeign(['acting_for_user_id']);
            $table->dropColumn(['is_acting', 'acting_for_user_id']);
        });

        Schema::dropIfExists('acting_delegations');
    }
};
