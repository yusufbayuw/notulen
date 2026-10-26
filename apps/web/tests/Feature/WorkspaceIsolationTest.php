<?php

namespace Tests\Feature;

use App\Models\Meeting;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkspaceIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_an_authenticated_user_cannot_view_another_organizations_dashboard_or_meeting(): void
    {
        $owner = User::create(['name' => 'Owner', 'email' => 'owner@example.test', 'password' => 'secret-password-123']);
        $outsider = User::create(['name' => 'Outsider', 'email' => 'outsider@example.test', 'password' => 'secret-password-456']);
        $org = Organization::create(['name' => 'Sekolah Satu', 'slug' => 'sekolah-satu', 'owner_id' => $owner->id]);
        $org->users()->attach($owner->id, ['role' => 'owner']);
        $meeting = Meeting::create(['organization_id' => $org->id, 'created_by' => $owner->id, 'title' => 'Rahasia', 'status' => 'queued']);

        $this->actingAs($outsider)->get('/w/sekolah-satu')->assertNotFound();
        $this->actingAs($outsider)->get('/w/sekolah-satu/meetings/'.$meeting->id)->assertNotFound();
        $this->actingAs($outsider)->post('/w/sekolah-satu/recordings', [])->assertNotFound();
        $this->actingAs($owner)->get('/w/sekolah-satu')->assertOk();
        $this->actingAs($owner)->get('/w/sekolah-satu/meetings/'.$meeting->id)->assertOk();
    }

    public function test_an_authenticated_member_cannot_access_meetings_from_a_different_workspace_by_id(): void
    {
        $u = User::create(['name' => 'Member', 'email' => 'member@example.test', 'password' => 'secret-password-123']);
        $org1 = Organization::create(['name' => 'Org A', 'slug' => 'org-a', 'owner_id' => $u->id]);
        $org2 = Organization::create(['name' => 'Org B', 'slug' => 'org-b', 'owner_id' => $u->id]);
        $org1->users()->attach($u->id, ['role' => 'owner']);
        $org2->users()->attach($u->id, ['role' => 'owner']);
        $meeting = Meeting::create(['organization_id' => $org2->id, 'created_by' => $u->id, 'title' => 'Org B only']);

        $this->actingAs($u)->get('/w/org-a/meetings/'.$meeting->id)->assertNotFound();
        $this->actingAs($u)->get('/w/org-b/meetings/'.$meeting->id)->assertOk();
    }
}
