<?php

namespace Tests\Feature;

use App\Filament\Resources\TicketResource;
use App\Filament\Resources\TicketResource\Pages\ListTickets;
use App\Models\Client;
use App\Models\Division;
use App\Models\Support;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class TicketScopeTest extends TestCase
{
    use DatabaseTransactions;

    public function test_pegawai_only_sees_own_tickets(): void
    {
        $rolePegawai = Role::firstOrCreate(['name' => 'pegawai', 'guard_name' => 'web']);
        $division = Division::create(['name' => 'Sekretariat']);

        $user1 = User::factory()->create();
        $user1->assignRole($rolePegawai);
        $client1 = Client::create(['user_id' => $user1->id, 'division_id' => $division->id]);

        $user2 = User::factory()->create();
        $user2->assignRole($rolePegawai);
        $client2 = Client::create(['user_id' => $user2->id, 'division_id' => $division->id]);

        $ticket1 = Ticket::create([
            'client_id' => $client1->id,
            'subject' => 'Ticket 1 Pegawai 1',
            'description' => 'Desc 1',
            'priority' => 'medium',
            'status' => 'open',
        ]);

        $ticket2 = Ticket::create([
            'client_id' => $client2->id,
            'subject' => 'Ticket 2 Pegawai 2',
            'description' => 'Desc 2',
            'priority' => 'low',
            'status' => 'open',
        ]);

        $this->actingAs($user1);

        $results = TicketResource::getEloquentQuery()->get();

        $this->assertTrue($results->contains($ticket1));
        $this->assertFalse($results->contains($ticket2));
    }

    public function test_it_support_can_only_assign_to_self(): void
    {
        $roleSupport = Role::firstOrCreate(['name' => 'it_support', 'guard_name' => 'web']);
        $userSupport = User::factory()->create();
        $userSupport->assignRole($roleSupport);
        $support = Support::create(['user_id' => $userSupport->id, 'phone' => '08123456789']);

        $division = Division::create(['name' => 'IT']);
        $clientUser = User::factory()->create();
        $client = Client::create(['user_id' => $clientUser->id, 'division_id' => $division->id]);

        $ticket = Ticket::create([
            'client_id' => $client->id,
            'subject' => 'Issue Ticket',
            'description' => 'Help needed',
            'priority' => 'medium',
            'status' => 'open',
        ]);

        $this->actingAs($userSupport);

        $ticket->update(['support_id' => $userSupport->support->id]);

        $this->assertEquals($support->id, $ticket->fresh()->support_id);
    }

    public function test_only_admin_can_reopen_resolved_ticket(): void
    {
        $roleAdmin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $userAdmin = User::factory()->create();
        $userAdmin->assignRole($roleAdmin);

        $roleSupport = Role::firstOrCreate(['name' => 'it_support', 'guard_name' => 'web']);
        $userSupport = User::factory()->create();
        $userSupport->assignRole($roleSupport);

        $division = Division::create(['name' => 'IT']);
        $clientUser = User::factory()->create();
        $client = Client::create(['user_id' => $clientUser->id, 'division_id' => $division->id]);

        $ticket = Ticket::create([
            'client_id' => $client->id,
            'subject' => 'Resolved Ticket',
            'description' => 'Fixed issue',
            'priority' => 'medium',
            'status' => 'resolved',
        ]);

        $this->actingAs($userSupport);
        $page = new ListTickets;
        $page->moveStatus($ticket->id, 'in_progress');

        $this->assertEquals('resolved', $ticket->fresh()->status);

        $this->actingAs($userAdmin);
        $page->moveStatus($ticket->id, 'in_progress');

        $this->assertEquals('in_progress', $ticket->fresh()->status);
    }

    public function test_it_support_cannot_update_resolved_ticket_policy(): void
    {
        $roleSupport = Role::firstOrCreate(['name' => 'it_support', 'guard_name' => 'web']);
        $userSupport = User::factory()->create();
        $userSupport->assignRole($roleSupport);

        $division = Division::create(['name' => 'IT']);
        $clientUser = User::factory()->create();
        $client = Client::create(['user_id' => $clientUser->id, 'division_id' => $division->id]);

        $ticket = Ticket::create([
            'client_id' => $client->id,
            'subject' => 'Resolved Ticket',
            'description' => 'Fixed issue',
            'priority' => 'medium',
            'status' => 'resolved',
        ]);

        $this->assertFalse($userSupport->can('update', $ticket));
    }
}
