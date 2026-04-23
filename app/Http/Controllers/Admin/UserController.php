<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Party;
use App\Models\Portfolio;
use App\Models\SecurityGroup;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(): View
    {
        $users = User::withCount(['businessUnits', 'portfolios', 'securityGroups'])
            ->orderBy('name')
            ->paginate(20);

        return view('admin.users.index', compact('users'));
    }

    public function create(): View
    {
        $parties       = Party::businessUnits()->authorized()->orderBy('long_name')->get();
        $portfolios    = Portfolio::orderBy('name')->get();
        $securityGroups = SecurityGroup::orderBy('name')->get();

        return view('admin.users.create', compact('parties', 'portfolios', 'securityGroups'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'                   => ['required', 'string', 'max:255'],
            'email'                  => ['required', 'email', 'max:255', 'unique:users,email'],
            'role'                   => ['required', Rule::in(['admin', 'trader', 'back_office'])],
            'password'               => ['required', 'string', 'min:8', 'confirmed'],
            'user_type'              => ['sometimes', Rule::in(['Internal', 'External', 'Licensed'])],
            'license_type'           => ['sometimes', 'nullable', Rule::in(['Full Access', 'Server', 'Read Only'])],
            'short_ref_name'         => ['sometimes', 'nullable', 'string', 'max:32'],
            'short_alias_name'       => ['sometimes', 'nullable', 'string', 'max:50'],
            'employee_id'            => ['sometimes', 'nullable', 'string', 'max:50'],
            'title'                  => ['sometimes', 'nullable', 'string', 'max:100'],
            'phone'                  => ['sometimes', 'nullable', 'string', 'max:50'],
            'address'                => ['sometimes', 'nullable', 'string', 'max:255'],
            'city'                   => ['sometimes', 'nullable', 'string', 'max:100'],
            'state'                  => ['sometimes', 'nullable', 'string', 'max:100'],
            'country'                => ['sometimes', 'nullable', 'string', 'max:100'],
            'password_never_expires' => ['boolean'],
            'status'                 => ['sometimes', Rule::in(['Authorized', 'Auth Pending', 'Do Not Use'])],
            'business_units'         => ['sometimes', 'array'],
            'business_units.*'       => ['exists:parties,id'],
            'portfolios'             => ['sometimes', 'array'],
            'portfolios.*'           => ['exists:portfolios,id'],
            'security_groups'        => ['sometimes', 'array'],
            'security_groups.*'      => ['exists:security_groups,id'],
        ]);

        $data['password']               = Hash::make($data['password']);
        $data['password_never_expires'] = $request->boolean('password_never_expires');

        $user = User::create($data);

        $user->businessUnits()->sync($request->input('business_units', []));
        $user->portfolios()->sync($request->input('portfolios', []));
        $user->securityGroups()->sync($request->input('security_groups', []));

        return redirect()->route('admin.users.index')
            ->with('success', 'User created successfully.');
    }

    public function show(User $user): View
    {
        $user->load(['businessUnits', 'portfolios', 'securityGroups', 'tradingLocations']);

        return view('admin.users.show', compact('user'));
    }

    public function edit(User $user): View
    {
        $user->load(['businessUnits', 'portfolios', 'securityGroups', 'tradingLocations']);

        $parties        = Party::businessUnits()->authorized()->orderBy('long_name')->get();
        $portfolios     = Portfolio::orderBy('name')->get();
        $securityGroups = SecurityGroup::orderBy('name')->get();

        $assignedBUs        = $user->businessUnits->pluck('id')->toArray();
        $assignedPortfolios = $user->portfolios->pluck('id')->toArray();
        $assignedSGs        = $user->securityGroups->pluck('id')->toArray();

        return view('admin.users.edit', compact(
            'user',
            'parties',
            'portfolios',
            'securityGroups',
            'assignedBUs',
            'assignedPortfolios',
            'assignedSGs'
        ));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'name'                   => ['required', 'string', 'max:255'],
            'email'                  => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'role'                   => ['required', Rule::in(['admin', 'trader', 'back_office'])],
            'user_type'              => ['sometimes', Rule::in(['Internal', 'External', 'Licensed'])],
            'license_type'           => ['sometimes', 'nullable', Rule::in(['Full Access', 'Server', 'Read Only'])],
            'short_ref_name'         => ['sometimes', 'nullable', 'string', 'max:32'],
            'short_alias_name'       => ['sometimes', 'nullable', 'string', 'max:50'],
            'employee_id'            => ['sometimes', 'nullable', 'string', 'max:50'],
            'title'                  => ['sometimes', 'nullable', 'string', 'max:100'],
            'phone'                  => ['sometimes', 'nullable', 'string', 'max:50'],
            'address'                => ['sometimes', 'nullable', 'string', 'max:255'],
            'city'                   => ['sometimes', 'nullable', 'string', 'max:100'],
            'state'                  => ['sometimes', 'nullable', 'string', 'max:100'],
            'country'                => ['sometimes', 'nullable', 'string', 'max:100'],
            'password_never_expires' => ['boolean'],
            'status'                 => ['sometimes', Rule::in(['Authorized', 'Auth Pending', 'Do Not Use'])],
            'business_units'         => ['sometimes', 'array'],
            'business_units.*'       => ['exists:parties,id'],
            'portfolios'             => ['sometimes', 'array'],
            'portfolios.*'           => ['exists:portfolios,id'],
            'security_groups'        => ['sometimes', 'array'],
            'security_groups.*'      => ['exists:security_groups,id'],
        ]);

        $data['password_never_expires'] = $request->boolean('password_never_expires');

        $user->update($data);

        if ($request->filled('password')) {
            $request->validate(['password' => ['string', 'min:8', 'confirmed']]);
            $user->update(['password' => Hash::make($request->password)]);
        }

        $user->businessUnits()->sync($request->input('business_units', []));
        $user->portfolios()->sync($request->input('portfolios', []));
        $user->securityGroups()->sync($request->input('security_groups', []));

        return redirect()->route('admin.users.index')
            ->with('success', 'User updated.');
    }

    public function destroy(User $user): RedirectResponse
    {
        $this->authorize('delete', $user);

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'User deleted.');
    }
}
