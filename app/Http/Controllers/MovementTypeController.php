<?php

namespace App\Http\Controllers;

use App\Models\MovementType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MovementTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $types = MovementType::with('parent', 'children')
            ->orderBy('parent_type_id')
            ->orderBy('order')
            ->get()
            ->groupBy(function ($type) {
                return $type->parent_type_id ? 'subcategories' : 'main';
            });

        return view('settings.movement-types.index', [
            'mainTypes' => $types['main'] ?? collect(),
            'subTypes' => $types['subcategories'] ?? collect(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $parentTypes = MovementType::mainTypes()->active()->get();

        return view('settings.movement-types.create', compact('parentTypes'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:movement_types,code',
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'category' => 'required|in:main_type,expense_detail,income_detail,withdrawal_detail',
            'affects_balance' => 'required|in:-1,0,1',
            'icon' => 'nullable|string|max:10',
            'color' => 'nullable|string|max:20',
            'parent_type_id' => 'nullable|exists:movement_types,id',
            'is_active' => 'boolean',
            'order' => 'nullable|integer|min:0',
        ]);

        $validated['is_active'] = $request->has('is_active');
        $validated['order'] = $validated['order'] ?? 0;

        MovementType::create($validated);

        return redirect()->route('movement-types.index')
            ->with('success', 'Tipo de movimiento creado exitosamente');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(MovementType $movementType)
    {
        $parentTypes = MovementType::mainTypes()
            ->active()
            ->where('id', '!=', $movementType->id)
            ->get();

        return view('settings.movement-types.edit', compact('movementType', 'parentTypes'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, MovementType $movementType)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:movement_types,code,' . $movementType->id,
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'category' => 'required|in:main_type,expense_detail,income_detail,withdrawal_detail',
            'affects_balance' => 'required|in:-1,0,1',
            'icon' => 'nullable|string|max:10',
            'color' => 'nullable|string|max:20',
            'parent_type_id' => 'nullable|exists:movement_types,id',
            'is_active' => 'boolean',
            'order' => 'nullable|integer|min:0',
        ]);

        $validated['is_active'] = $request->has('is_active');
        $validated['order'] = $validated['order'] ?? $movementType->order;

        $movementType->update($validated);

        return redirect()->route('movement-types.index')
            ->with('success', 'Tipo de movimiento actualizado exitosamente');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(MovementType $movementType)
    {
        // Verificar que no tenga movimientos asociados
        if ($movementType->cashMovements()->exists()) {
            return redirect()->route('movement-types.index')
                ->with('error', 'No se puede eliminar este tipo porque tiene movimientos asociados');
        }

        // Verificar que no tenga hijos
        if ($movementType->children()->exists()) {
            return redirect()->route('movement-types.index')
                ->with('error', 'No se puede eliminar este tipo porque tiene subcategorías asociadas');
        }

        $movementType->delete();

        return redirect()->route('movement-types.index')
            ->with('success', 'Tipo de movimiento eliminado exitosamente');
    }

    /**
     * Toggle active status
     */
    public function toggleActive(MovementType $movementType)
    {
        $movementType->update(['is_active' => !$movementType->is_active]);

        return redirect()->route('movement-types.index')
            ->with('success', 'Estado actualizado exitosamente');
    }
}
