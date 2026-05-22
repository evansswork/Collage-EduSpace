<?php

namespace Database\Seeders;

use App\Models\Group;
use App\Models\GroupMessage;
use App\Models\Nudge;
use App\Models\User;
use Illuminate\Database\Seeder;

class GroupMessageSeeder extends Seeder
{
    public function run(): void
    {
        $group = Group::first();
        if (!$group) return;

        $dimas    = User::where('email', 'dimas@eduspace.id')->first();
        $raka     = User::where('email', 'raka@eduspace.id')->first();
        $sinta    = User::where('email', 'sinta@eduspace.id')->first();
        $bayu     = User::where('email', 'bayu@eduspace.id')->first();

        // ========== GROUP MESSAGES (percakapan tim) ==========
        // Decision (pinned)
        GroupMessage::create([
            'group_id'    => $group->id,
            'user_id'     => $dimas->id,
            'body'        => "Pembagian tugas:\n• Raka — Frontend (React)\n• Sinta — Backend & API\n• Bayu — Dokumentasi & Testing\n• Saya — Coordinator + Slide\n\nDeadline internal: 2 hari sebelum deadline akhir, biar ada waktu buffer.",
            'is_decision' => true,
            'created_at'  => now()->subDays(4),
        ]);

        // Conversation
        GroupMessage::create([
            'group_id'   => $group->id,
            'user_id'    => $raka->id,
            'body'       => "Oke noted, aku mulai dari halaman login dulu ya.",
            'created_at' => now()->subDays(4)->addHours(1),
        ]);

        GroupMessage::create([
            'group_id'   => $group->id,
            'user_id'    => $sinta->id,
            'body'       => "Aku setup express + JWT auth dulu. Endpoint contract menyusul setelah Raka kasih kebutuhan datanya.",
            'created_at' => now()->subDays(3),
        ]);

        GroupMessage::create([
            'group_id'   => $group->id,
            'user_id'    => $dimas->id,
            'body'       => "Mantap. Bayu, jangan lupa README-nya ya. Template-nya aku push ke sandbox nanti.",
            'created_at' => now()->subDays(3)->addHours(2),
        ]);

        GroupMessage::create([
            'group_id'   => $group->id,
            'user_id'    => $dimas->id,
            'body'       => "@Bayu, gimana progress dokumentasi? Sudah mulai belum?",
            'created_at' => now()->subDays(1),
        ]);

        // ========== NUDGES (simulasi: Bayu udah di-nudge 2x) ==========
        Nudge::create([
            'group_id'     => $group->id,
            'from_user_id' => $dimas->id,
            'to_user_id'   => $bayu->id,
            'type'         => 'gentle',
            'created_at'   => now()->subDays(2),
        ]);

        Nudge::create([
            'group_id'     => $group->id,
            'from_user_id' => $dimas->id,
            'to_user_id'   => $bayu->id,
            'type'         => 'gentle',
            'created_at'   => now()->subHours(20),
        ]);

        $this->command->info('Group messages & nudges seeded.');
    }
}
