<?php

namespace App\Http\Controllers;

use App\Http\Requests\TeamMemberRequest;
use App\Models\TeamMember;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class TeamMemberController extends Controller
{
    public function index(): Response
    {
        $teamMembers = TeamMember::orderByDesc('position')
            ->orderByDesc('id')
            ->with('user')
            ->get();

        return Inertia::render('CRM/Team/Index', [
            'teamMembers' => $teamMembers,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('CRM/Team/Edit', [
            'teamMember' => [
                'name' => '',
                'role' => '',
                'city' => '',
                'phone' => '',
                'telegram_username' => '',
                'description' => '',
                'photo' => null,
                'photo_focus_x' => 50,
                'photo_focus_y' => 50,
                'position' => 0,
                'is_active' => true,
                'user_id' => null,
            ],
            'managers' => $this->availableManagers(),
            'isNew' => true,
        ]);
    }

    public function store(TeamMemberRequest $request)
    {
        $validated = $request->validated();
        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo')->store('team', 'public');
        }
        TeamMember::create($validated);

        return redirect()->route('crm.team.index');
    }

    public function edit(TeamMember $team): Response
    {
        return Inertia::render('CRM/Team/Edit', [
            'teamMember' => $team->load('user'),
            'managers' => $this->availableManagers($team),
            'isNew' => false,
        ]);
    }

    public function update(TeamMemberRequest $request, TeamMember $team)
    {
        $validated = $request->validated();
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('team', 'public');
            if ($team->photo) {
                Storage::disk('public')->delete($team->photo);
            }
            $validated['photo'] = $photoPath;
        }
        $team->update($validated);

        return redirect()->route('crm.team.index');
    }

    public function destroy(TeamMember $team)
    {
        if ($team->photo) {
            Storage::disk('public')->delete($team->photo);
        }
        $team->delete();

        return redirect()->route('crm.team.index');
    }

    private function availableManagers(?TeamMember $teamMember = null)
    {
        return User::query()
            ->where(function (Builder $query) use ($teamMember) {
                $query->whereDoesntHave('teamMember');

                if ($teamMember?->user_id) {
                    $query->orWhere('id', $teamMember->user_id);
                }
            })
            ->orderBy('name')
            ->get(['id', 'name', 'phone', 'telegram_username']);
    }
}
