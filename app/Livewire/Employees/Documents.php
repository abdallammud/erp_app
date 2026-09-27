<?php

namespace App\Livewire\Employees;

use App\Models\Employee;
use App\Support\Documents\DocumentStore;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithFileUploads;

/**
 * The "Documents" tile on My Profile — Build Plan Step 1.2. Attaches to
 * the signed-in user's Employee record, not the User directly (see
 * docs/build/DECISIONS.md D-038: not every employee has a login, so a
 * document repository keyed on User could never work for them — the
 * Employee record is the one that always exists). Supersedes the
 * Step 0.9 version of this tile, which used User as the documentable
 * before Employee existed.
 *
 * Deliberately scoped to the signed-in user's own personnel documents;
 * browsing OTHER employees' files is a real, later Phase 1 Employee
 * Records screen, not built here. App\Support\Documents\
 * DocumentStore::verify() already exists for that screen to call once
 * it does.
 *
 * `resolveEmployee()`/`resolveDocuments()` exist alongside the
 * `#[Computed]` properties for the same reason documented on
 * App\Livewire\Employees\PersonalDetails: Livewire's Computed
 * implementation forbids calling the attributed method directly
 * (throws `CannotCallComputedDirectlyException`), so `$this->employee`
 * (magic property) is the only legal access path — but Larastan can't
 * see that magic when it's read from an action method rather than
 * Blade. Splitting out a plain resolver sidesteps the false positive.
 */
class Documents extends Component
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
    public function employee(): ?Employee
    {
        return $this->resolveEmployee();
    }

    private function resolveEmployee(): ?Employee
    {
        return Auth::user()->employee;
    }

    #[Computed]
    public function documents(): Collection
    {
        return $this->resolveEmployee()?->documents()->latest()->get() ?? new Collection;
    }

    public function upload(): void
    {
        $employee = $this->resolveEmployee();

        if (! $employee) {
            return;
        }

        $this->validate();

        // Narrows $this->file from ?UploadedFile to UploadedFile for
        // Larastan — validate() above already guarantees it's present.
        if (! $this->file instanceof UploadedFile) {
            return;
        }

        app(DocumentStore::class)->store(
            $this->file,
            $employee,
            Auth::user(),
            $this->category,
            $this->expiryDate,
        );

        unset($this->documents);
        $this->reset(['category', 'file', 'expiryDate']);
    }

    public function render()
    {
        return view('livewire.employees.documents');
    }
}
