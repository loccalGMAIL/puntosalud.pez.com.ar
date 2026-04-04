<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Services\SettingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingsCenterController extends Controller
{
    public function __construct(private SettingService $settings) {}

    public function index(): View
    {
        $settings = Setting::where('group', 'center')->pluck('value', 'key');

        return view('settings.center', compact('settings'));
    }

    public function toggle(): RedirectResponse
    {
        $current = $this->settings->get('center_active', '1');
        $newValue = $current === '1' ? '0' : '1';

        $this->settings->set('center_active', 'center', $newValue);

        $message = $newValue === '1'
            ? 'El sistema ha sido habilitado.'
            : 'El sistema ha sido bloqueado. Solo el Administrador puede ingresar.';

        return redirect()->route('settings.center')->with('success', $message);
    }

    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'center_name'     => 'required|string|max:255',
            'center_subtitle' => 'nullable|string|max:255',
            'center_address'  => 'nullable|string|max:255',
            'center_phone'    => 'nullable|string|max:100',
            'center_email'    => 'nullable|email|max:255',
            'logo'            => 'nullable|file|mimes:png,jpg,jpeg,webp,svg|max:2048',
            'login_bg'        => 'nullable|file|mimes:png,jpg,jpeg,webp|max:4096',
        ]);

        $textFields = ['center_name', 'center_subtitle', 'center_address', 'center_phone', 'center_email'];

        foreach ($textFields as $key) {
            $this->settings->set($key, 'center', $request->input($key, ''));
        }

        $centerDir = public_path('center');
        if (! is_dir($centerDir)) {
            mkdir($centerDir, 0755, true);
        }

        foreach (['logo', 'login_bg'] as $imageField) {
            if ($request->hasFile($imageField)) {
                // Eliminar archivos anteriores con ese nombre (cualquier extensión)
                foreach (['png', 'jpg', 'jpeg', 'webp', 'svg'] as $ext) {
                    $old = $centerDir . "/{$imageField}.{$ext}";
                    if (file_exists($old)) {
                        unlink($old);
                    }
                }

                $file = $request->file($imageField);
                $ext  = $file->getClientOriginalExtension();
                $file->move($centerDir, "{$imageField}.{$ext}");
            }
        }

        return redirect()->route('settings.center')->with('success', 'Configuración del centro guardada correctamente.');
    }
}
