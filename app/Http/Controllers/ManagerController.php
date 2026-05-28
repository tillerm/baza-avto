<?php

namespace App\Http\Controllers;

use App\Http\Requests\ManagerRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;

class ManagerController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('CRM/Managers/Index', [
            'managers' => User::withCount('cars')
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('CRM/Managers/Edit', [
            'manager' => [
                'name' => '',
                'email' => '',
                'telegram_username' => '',
                'phone' => '',
            ],
            'isNew' => true,
        ]);
    }

    public function store(ManagerRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'telegram_username' => $validated['telegram_username'] ?: null,
            'phone' => $validated['phone'] ?: null,
            'password' => Hash::make($validated['password']),
        ]);

        return redirect()->route('crm.managers.index');
    }

    public function edit(User $manager): Response
    {
        return Inertia::render('CRM/Managers/Edit', [
            'manager' => $manager,
            'isNew' => false,
        ]);
    }

    public function update(ManagerRequest $request, User $manager): RedirectResponse
    {
        $validated = $request->validated();

        $payload = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'telegram_username' => $validated['telegram_username'] ?: null,
            'phone' => $validated['phone'] ?: null,
        ];

        if (! empty($validated['password'])) {
            $payload['password'] = Hash::make($validated['password']);
        }

        $manager->update($payload);

        return redirect()->route('crm.managers.index');
    }

    public function destroy(User $manager): RedirectResponse
    {
        if ($manager->cars()->exists() || $manager->supplies()->exists() || $manager->teamMember()->exists()) {
            return redirect()
                ->route('crm.managers.index')
                ->with('flash', [
                    'banner' => 'Нельзя удалить менеджера, у которого есть связанные авто, поступления или карточка в команде.',
                    'bannerStyle' => 'danger',
                ]);
        }

        $manager->delete();

        return redirect()->route('crm.managers.index');
    }
}
