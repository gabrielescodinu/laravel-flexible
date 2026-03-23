<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SiteSettingsController extends Controller
{
    public function edit(): View
    {
        return view('admin.settings.edit', [
            'ecommerceEnabled' => SiteSetting::ecommerceEnabled(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ecommerce_enabled' => ['nullable', 'boolean'],
        ]);

        SiteSetting::setValue('ecommerce_enabled', (bool) ($validated['ecommerce_enabled'] ?? false));

        return redirect()
            ->route('admin.settings.edit')
            ->with('success', 'Impostazioni aggiornate.');
    }
}
