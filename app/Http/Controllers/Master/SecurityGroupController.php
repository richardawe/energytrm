<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\SecurityGroup;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SecurityGroupController extends Controller
{
    public function index(): View
    {
        $securityGroups = SecurityGroup::orderBy('name')->paginate(25);

        return view('master.security-groups.index', compact('securityGroups'));
    }

    public function create(): View
    {
        return view('master.security-groups.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'        => ['required', 'string', 'max:100', 'unique:security_groups,name'],
            'description' => ['nullable', 'string'],
            'is_active'   => ['boolean'],
        ]);

        $data['is_active'] = $request->boolean('is_active', true);

        SecurityGroup::create($data);

        return redirect()->route('master.security-groups.index')
            ->with('success', 'Security group created.');
    }

    public function edit(SecurityGroup $securityGroup): View
    {
        return view('master.security-groups.edit', compact('securityGroup'));
    }

    public function update(Request $request, SecurityGroup $securityGroup): RedirectResponse
    {
        $data = $request->validate([
            'name'        => ['required', 'string', 'max:100', 'unique:security_groups,name,' . $securityGroup->id],
            'description' => ['nullable', 'string'],
            'is_active'   => ['boolean'],
        ]);

        $data['is_active'] = $request->boolean('is_active');

        $securityGroup->update($data);

        return redirect()->route('master.security-groups.index')
            ->with('success', 'Security group updated.');
    }

    public function destroy(SecurityGroup $securityGroup): RedirectResponse
    {
        $securityGroup->delete();

        return redirect()->route('master.security-groups.index')
            ->with('success', 'Security group deleted.');
    }
}
