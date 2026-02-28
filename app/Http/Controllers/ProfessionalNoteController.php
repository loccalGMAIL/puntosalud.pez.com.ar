<?php

namespace App\Http\Controllers;

use App\Models\Professional;
use App\Models\ProfessionalNote;
use Illuminate\Http\Request;

class ProfessionalNoteController extends Controller
{
    public function index(Professional $professional)
    {
        $notes = $professional->internalNotes()->with('author:id,name')->get();

        return response()->json([
            'notes' => $notes->map(fn ($note) => [
                'id'         => $note->id,
                'content'    => $note->content,
                'author'     => $note->author?->name ?? 'Usuario',
                'created_at' => $note->created_at->toISOString(),
            ]),
        ]);
    }

    public function store(Request $request, Professional $professional)
    {
        $validated = $request->validate([
            'content' => 'required|string|max:500',
        ]);

        $note = $professional->internalNotes()->create([
            'user_id' => auth()->id(),
            'content' => $validated['content'],
        ]);

        $note->load('author:id,name');

        return response()->json([
            'success' => true,
            'note'    => [
                'id'         => $note->id,
                'content'    => $note->content,
                'author'     => $note->author?->name ?? 'Usuario',
                'created_at' => $note->created_at->toISOString(),
            ],
        ], 201);
    }

    public function destroy(ProfessionalNote $note)
    {
        $note->delete();

        return response()->json(['success' => true]);
    }
}
