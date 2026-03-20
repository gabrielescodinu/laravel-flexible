<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Media;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MediaController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));
        $type = (string) $request->query('type', 'all');

        $mediaQuery = Media::query()->latest();

        if ($search !== '') {
            $mediaQuery->where(function ($query) use ($search): void {
                $query
                    ->where('original_name', 'like', '%'.$search.'%')
                    ->orWhere('title', 'like', '%'.$search.'%')
                    ->orWhere('mime_type', 'like', '%'.$search.'%');
            });
        }

        if ($type === 'image') {
            $mediaQuery->where('mime_type', 'like', 'image/%');
        } elseif ($type === 'document') {
            $mediaQuery->where('mime_type', 'not like', 'image/%');
        }

        $media = $mediaQuery->paginate(24)->withQueryString();

        $stats = [
            'all' => Media::query()->count(),
            'images' => Media::query()->where('mime_type', 'like', 'image/%')->count(),
            'documents' => Media::query()->where('mime_type', 'not like', 'image/%')->count(),
        ];

        return view('admin.media.index', [
            'media' => $media,
            'search' => $search,
            'type' => $type,
            'stats' => $stats,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'files' => ['required', 'array', 'max:25'],
            'files.*' => [
                'required',
                'file',
                'max:51200',
                'mimes:jpg,jpeg,png,webp,gif,svg,pdf,doc,docx,xls,xlsx,ppt,pptx,txt,csv,zip',
            ],
        ]);

        $uploadedCount = 0;
        $uploadedBy = $request->user()?->id;

        foreach ($validated['files'] as $file) {
            $path = $file->store('media/'.date('Y/m'), 'public');
            $originalName = $file->getClientOriginalName();

            Media::query()->create([
                'disk' => 'public',
                'path' => $path,
                'file_name' => basename($path),
                'original_name' => $originalName,
                'extension' => strtolower((string) $file->getClientOriginalExtension()),
                'mime_type' => $file->getClientMimeType() ?: ($file->getMimeType() ?: 'application/octet-stream'),
                'size' => (int) ($file->getSize() ?? 0),
                'uploaded_by' => $uploadedBy,
                'title' => pathinfo($originalName, PATHINFO_FILENAME),
            ]);

            $uploadedCount++;
        }

        return redirect()
            ->route('admin.media.index')
            ->with('success', $uploadedCount.' file caricati con successo.');
    }

    public function destroy(Media $media): RedirectResponse
    {
        Storage::disk($media->disk)->delete($media->path);
        $media->delete();

        return back()->with('success', 'File eliminato con successo.');
    }
}
