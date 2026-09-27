<?php

namespace App\Livewire\Documents;

use App\Models\Document;
use App\Models\User;
use App\Support\Documents\DocumentStore;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithFileUploads;

/**
 * The "Documents" tile on My Profile (docs/build/00-build-plan.md
 * Step 0.9), promoted from "Coming in Phase 1" — same pattern as
 * D-024's other placeholder promotions. Deliberately scoped to the
 * signed-in user's own personnel documents; browsing OTHER employees'
 * files is a real Phase 1 Employee Records screen (no employee
 * directory exists yet to hang it on), not something to fake here.
 * App\Support\Documents\DocumentStore::verify() already exists for that
 * screen to call once it does.
 */
class MyDocuments extends Component
{
    use WithFileUploads;

    public string $category = '';

    public ?UploadedFile $file = null;

    public ?string $expiryDate = null;

    protected function rules(): array
    {
        return [
            'category' => 'required|string|max:255',
            'file' => 'required|file|max:10240',
            'expiryDate' => 'nullable|date',
        ];
    }

    #[Computed]
    public function documents()
    {
        return Document::where('documentable_type', User::class)
            ->where('documentable_id', Auth::id())
            ->latest()
            ->get();
    }

    public function upload(): void
    {
        $this->validate();

        // Narrows $this->file from ?UploadedFile to UploadedFile for
        // Larastan — validate() above already guarantees it's present.
        if (! $this->file instanceof UploadedFile) {
            return;
        }

        app(DocumentStore::class)->store(
            $this->file,
            Auth::user(),
            Auth::user(),
            $this->category,
            $this->expiryDate,
        );

        unset($this->documents);
        $this->reset(['category', 'file', 'expiryDate']);
    }

    public function render()
    {
        return view('livewire.documents.my-documents');
    }
}
