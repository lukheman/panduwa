<?php

namespace App\Livewire\Admin;

use App\Enums\StatusKegiatan;
use App\Models\BidangKegiatan;
use App\Models\Kegiatan;
use App\Models\KegiatanBelanja;
use App\Models\Pemasukan;
use App\Models\Pengeluaran;
use App\Models\RekeningAnggaran;
use App\Models\SubBidangKegiatan;
use App\Models\SumberDana;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

#[Title('Perencanaan Kegiatan')]
class KegiatanManagement extends Component
{
    use WithFileUploads;
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url(as: 'dana')]
    public string $dana = SumberDana::KODE_DANA_DESA;

    #[Url(as: 'bidang')]
    public string $filterBidang = '';

    public string $nama_kegiatan = '';

    public string $lokasi = '';

    public string $bidang_kegiatan_id = '';

    public string $sub_bidang_kegiatan_id = '';

    public string $rencana_anggaran = '';

    public string $realisasi_anggaran = '';

    public string $status = 'perencanaan';

    public $foto_progres;

    public array $detailBelanja = [];

    public ?string $existing_foto_progres = null;

    public ?int $editingKegiatanId = null;

    public bool $showModal = false;

    public bool $showDeleteModal = false;

    public bool $showDetailModal = false;

    public ?int $deletingKegiatanId = null;

    public ?int $detailKegiatanId = null;

    public $detailKegiatan = null;

    protected function rules(): array
    {
        $rules = [
            'nama_kegiatan' => ['required', 'string', 'max:255'],
            'lokasi' => ['required', 'string', 'max:255'],
            'rencana_anggaran' => ['required', 'numeric', 'min:0', 'max:9999999999999'],
            'realisasi_anggaran' => ['nullable', 'numeric', 'min:0', 'max:9999999999999'],
            'status' => ['required', Rule::enum(StatusKegiatan::class)],
            'foto_progres' => ['nullable', 'image', 'max:2048'],
            'detailBelanja' => ['required', 'array', 'min:1'],
            'detailBelanja.*.rekening_id' => ['required', 'integer', 'exists:rekening_anggaran,id'],
            'detailBelanja.*.uraian' => ['required', 'string', 'max:255'],
            'detailBelanja.*.anggaran' => ['required', 'numeric', 'min:0'],
        ];

        if ($this->menggunakanBidang()) {
            $rules['bidang_kegiatan_id'] = ['required', 'exists:bidang_kegiatan,id'];
            $rules['sub_bidang_kegiatan_id'] = ['required', 'exists:sub_bidang_kegiatan,id'];
        }

        return $rules;
    }

    public function menggunakanBidang(): bool
    {
        return $this->dana === SumberDana::KODE_DANA_DESA;
    }

    public function updatedDana(): void
    {
        $this->dana = SumberDana::normalisasiKode($this->dana);
        $this->resetForm();
        $this->resetPage();
    }

    public function mount(): void
    {
        $this->dana = SumberDana::normalisasiKode($this->dana);
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedFilterBidang(): void
    {
        $this->resetPage();
    }

    public function updatedBidangKegiatanId(): void
    {
        $this->sub_bidang_kegiatan_id = '';
    }

    public function getSubBidangOptionsProperty()
    {
        if (! $this->bidang_kegiatan_id) {
            return collect();
        }

        return SubBidangKegiatan::where('bidang_kegiatan_id', $this->bidang_kegiatan_id)
            ->orderBy('kode')
            ->get();
    }

    public function getTotalDetailAnggaranProperty(): float
    {
        return collect($this->detailBelanja)->sum(fn (array $detail): float => (float) ($detail['anggaran'] ?? 0));
    }

    public function getSelisihDetailAnggaranProperty(): float
    {
        return (float) ($this->rencana_anggaran ?: 0) - $this->totalDetailAnggaran;
    }

    protected function detailBelanjaBaru(): array
    {
        return [['id' => null, 'rekening_id' => '', 'uraian' => '', 'anggaran' => '', 'realisasi' => 0]];
    }

    public function openCreateModal(): void
    {
        $this->resetForm();
        $this->editingKegiatanId = null;
        $this->showModal = true;
    }

    public function openDetailModal(int $id): void
    {
        $this->detailKegiatanId = $id;
        $this->detailKegiatan = Kegiatan::with([
            'subBidang.bidang',
            'sumberDana',
            'belanjaDetails' => fn ($query) => $query->withSum('pengeluarans', 'jumlah'),
        ])->findOrFail($id);
        $this->showDetailModal = true;
    }

    public function closeDetailModal(): void
    {
        $this->showDetailModal = false;
        $this->detailKegiatanId = null;
        $this->detailKegiatan = null;
    }

    public function openEditModal(int $id): void
    {
        $kegiatan = Kegiatan::with([
            'subBidang.bidang',
            'belanjaDetails' => fn ($query) => $query->withSum('pengeluarans', 'jumlah'),
        ])->findOrFail($id);

        $this->editingKegiatanId = $id;
        $this->dana = SumberDana::normalisasiKode($kegiatan->sumberDana?->kode ?? $this->dana);
        $this->nama_kegiatan = $kegiatan->nama_kegiatan;
        $this->lokasi = $kegiatan->lokasi;
        $this->bidang_kegiatan_id = (string) ($kegiatan->subBidang?->bidang_kegiatan_id ?? '');
        $this->sub_bidang_kegiatan_id = (string) ($kegiatan->sub_bidang_kegiatan_id ?? '');
        $this->rencana_anggaran = (string) $kegiatan->rencana_anggaran;
        $this->realisasi_anggaran = (string) ($kegiatan->realisasi_anggaran ?? '');
        $this->status = $kegiatan->status->value;
        $this->existing_foto_progres = $kegiatan->foto_progres;
        $this->foto_progres = null;
        $this->detailBelanja = $kegiatan->belanjaDetails->map(fn (KegiatanBelanja $detail): array => [
            'id' => $detail->id,
            'rekening_id' => (string) $detail->rekening_anggaran_id,
            'uraian' => $detail->uraian,
            'anggaran' => (string) $detail->anggaran,
            'realisasi' => (float) ($detail->pengeluarans_sum_jumlah ?? 0),
        ])->all() ?: $this->detailBelanjaBaru();

        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        $sumberDanaId = $this->sumberDanaId();

        if ($this->menggunakanBidang()) {
            $sub = SubBidangKegiatan::findOrFail($this->sub_bidang_kegiatan_id);
            if ((string) $sub->bidang_kegiatan_id !== (string) $this->bidang_kegiatan_id) {
                $this->addError('sub_bidang_kegiatan_id', 'Sub-bidang tidak termasuk dalam bidang yang dipilih.');

                return;
            }
        }
        foreach ($this->detailBelanja as $detail) {
            $childrenCount = RekeningAnggaran::withCount('children')->whereKey($detail['rekening_id'])->value('children_count');
            if ($childrenCount > 0) {
                $this->addError('detailBelanja', 'Detail belanja harus menggunakan rekening paling bawah.');

                return;
            }
        }

        $totalPemasukan = Pemasukan::query()->untukSumberDana($sumberDanaId)->sum('jumlah');
        $totalPengeluaranNonKegiatan = Pengeluaran::query()->untukSumberDana($sumberDanaId)->whereNull('id_kegiatan')->sum('jumlah');
        $totalAnggaranKegiatanLain = Kegiatan::query()->untukSumberDana($sumberDanaId);

        if ($this->editingKegiatanId) {
            $totalAnggaranKegiatanLain->where('id', '!=', $this->editingKegiatanId);
        }

        $totalAnggaranKegiatanLain = $totalAnggaranKegiatanLain->sum('rencana_anggaran');

        $sisaAnggaranTersedia = $totalPemasukan - $totalPengeluaranNonKegiatan - $totalAnggaranKegiatanLain;

        $allowedToSave = false;

        if ($this->editingKegiatanId) {
            $oldAnggaran = Kegiatan::where('id', $this->editingKegiatanId)->value('rencana_anggaran');
            if ($this->rencana_anggaran <= $oldAnggaran) {
                $allowedToSave = true;
            }
        }

        if (! $allowedToSave && $this->rencana_anggaran > $sisaAnggaranTersedia) {
            $this->addError('rencana_anggaran', 'Sisa anggaran desa yang belum dialokasikan hanya '.$this->formatRupiah($sisaAnggaranTersedia).'. Anda tidak dapat mengeset anggaran melebihi batas ini.');

            return;
        }

        $path = $this->existing_foto_progres;

        if ($this->foto_progres) {
            if ($path) {
                Storage::disk('public')->delete($path);
            }
            $path = $this->foto_progres->store('kegiatan', 'public');
        }

        $data = [
            'nama_kegiatan' => $this->nama_kegiatan,
            'lokasi' => $this->lokasi,
            'sub_bidang_kegiatan_id' => $this->menggunakanBidang() ? $this->sub_bidang_kegiatan_id : null,
            'rencana_anggaran' => $this->rencana_anggaran,
            'realisasi_anggaran' => $this->realisasi_anggaran ?: null,
            'status' => $this->status,
            'foto_progres' => $path,
            'sumber_dana_id' => $sumberDanaId,
        ];

        if ($this->editingKegiatanId) {
            $kegiatan = Kegiatan::findOrFail($this->editingKegiatanId);
            $kegiatan->update($data);
            if (! $this->saveDetailBelanja($kegiatan)) {
                return;
            }
            if ($this->status === StatusKegiatan::PERENCANAAN->value) {
                $this->hapusOtomatisPengeluaran($kegiatan);

                session()->flash('success', 'Data kegiatan berhasil diperbarui. Status masih perencanaan sehingga belum dicatat sebagai pengeluaran.');
            } elseif ($this->status === StatusKegiatan::BERJALAN->value) {
                if ($this->realisasi_anggaran) {
                    $this->syncOtomatisPengeluaran($kegiatan, $this->realisasi_anggaran);

                    session()->flash('success', 'Data kegiatan berhasil diperbarui dan realisasi anggaran sebesar '.$this->formatRupiah($this->realisasi_anggaran).' telah dicatat sebagai pengeluaran.');
                } else {
                    $this->hapusOtomatisPengeluaran($kegiatan);

                    session()->flash('success', 'Data kegiatan berhasil diperbarui. Belum ada realisasi sehingga belum dicatat sebagai pengeluaran.');
                }
            } else {
                $nominal = $this->realisasi_anggaran ?: min((float) $kegiatan->rencana_anggaran, $this->totalDetailAnggaran);
                $this->syncOtomatisPengeluaran($kegiatan, $nominal);

                session()->flash('success', 'Data kegiatan berhasil diperbarui dan sebesar '.$this->formatRupiah($nominal).' telah dicatat sebagai pengeluaran (status selesai).');
            }
        } else {
            $kegiatan = Kegiatan::create($data);
            if (! $this->saveDetailBelanja($kegiatan)) {
                return;
            }
            $perluCatat = $this->status === StatusKegiatan::SELESAI->value
                || ($this->status === StatusKegiatan::BERJALAN->value && $this->realisasi_anggaran);

            if ($perluCatat) {
                $nominal = $this->realisasi_anggaran ?: min((float) $kegiatan->rencana_anggaran, $this->totalDetailAnggaran);
                $this->syncOtomatisPengeluaran($kegiatan, $nominal);

                session()->flash('success', 'Data kegiatan berhasil ditambahkan dan sebesar '.$this->formatRupiah($nominal).' telah dicatat sebagai pengeluaran.');
            } else {
                session()->flash('success', 'Data kegiatan berhasil ditambahkan sebagai perencanaan. Belum dicatat sebagai pengeluaran.');
            }
        }

        $this->closeModal();
    }

    protected function queryOtomatisPengeluaran(Kegiatan $kegiatan)
    {
        return Pengeluaran::where('id_kegiatan', $kegiatan->id)
            ->where(function ($q) {
                $q->where('keterangan', 'like', 'Alokasi Dana Kegiatan:%')
                    ->orWhere('keterangan', 'like', 'Realisasi Anggaran Kegiatan:%');
            });
    }

    protected function syncOtomatisPengeluaran(Kegiatan $kegiatan, $nominal): void
    {
        $this->hapusOtomatisPengeluaran($kegiatan);
        $details = $kegiatan->belanjaDetails()->orderBy('id')->get();
        $remaining = (float) $nominal;

        foreach ($details as $index => $detail) {
            $amount = $index === $details->count() - 1 ? $remaining : min((float) $detail->anggaran, $remaining);
            $remaining -= $amount;

            if ($amount <= 0) {
                continue;
            }

            Pengeluaran::create([
                'jumlah' => $amount,
                'tanggal' => date('Y-m-d'),
                'keterangan' => 'Realisasi Anggaran Kegiatan: '.$kegiatan->nama_kegiatan,
                'id_kegiatan' => $kegiatan->id,
                'kegiatan_belanja_id' => $detail->id,
                'sumber_dana_id' => $kegiatan->sumber_dana_id,
                'rekening_anggaran_id' => $detail->rekening_anggaran_id,
            ]);
        }
    }

    protected function hapusOtomatisPengeluaran(Kegiatan $kegiatan): void
    {
        (clone $this->queryOtomatisPengeluaran($kegiatan))->delete();
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
        $this->resetValidation();
    }

    public function addDetailBelanja(): void
    {
        $this->detailBelanja[] = $this->detailBelanjaBaru()[0];
    }

    public function removeDetailBelanja(int $index): void
    {
        unset($this->detailBelanja[$index]);
        $this->detailBelanja = array_values($this->detailBelanja);
    }

    public function updatedDetailBelanja($value, ?string $key = null): void
    {
        if ($key === null || ! str_ends_with($key, '.rekening_id') || ! $value) {
            return;
        }

        $index = (int) explode('.', $key)[0];
        if (! isset($this->detailBelanja[$index])) {
            return;
        }

        $this->detailBelanja[$index]['uraian'] = (string) RekeningAnggaran::whereKey($value)->value('nama');
    }

    protected function saveDetailBelanja(Kegiatan $kegiatan): bool
    {
        $idsTetap = [];

        foreach ($this->detailBelanja as $detail) {
            $rekening = RekeningAnggaran::withCount('children')->findOrFail($detail['rekening_id']);
            if ($rekening->children_count > 0) {
                $this->addError('detailBelanja', 'Detail belanja harus menggunakan rekening paling bawah.');

                return false;
            }

            $atribut = [
                'rekening_anggaran_id' => $rekening->id,
                'kode_rekening' => $rekening->kode,
                'uraian' => $detail['uraian'],
                'anggaran' => $detail['anggaran'],
            ];

            $detailTersedia = ! empty($detail['id'])
                ? $kegiatan->belanjaDetails()->whereKey($detail['id'])->first()
                : null;

            if ($detailTersedia) {
                $detailTersedia->update($atribut);
                $idsTetap[] = $detailTersedia->id;
            } else {
                $idsTetap[] = $kegiatan->belanjaDetails()->create($atribut)->id;
            }
        }

        if ($idsTetap !== []) {
            $kegiatan->belanjaDetails()->whereNotIn('id', $idsTetap)->delete();
        } else {
            $kegiatan->belanjaDetails()->delete();
        }

        return true;
    }

    public function confirmDelete(int $id): void
    {
        $this->deletingKegiatanId = $id;
        $this->showDeleteModal = true;
    }

    public function deleteKegiatan(): void
    {
        if ($this->deletingKegiatanId) {
            $kegiatan = Kegiatan::find($this->deletingKegiatanId);
            if ($kegiatan && $kegiatan->foto_progres) {
                Storage::disk('public')->delete($kegiatan->foto_progres);
            }
            if ($kegiatan) {
                $kegiatan->delete();
                session()->flash('success', 'Data kegiatan berhasil dihapus.');
            }
        }

        $this->showDeleteModal = false;
        $this->deletingKegiatanId = null;
    }

    public function cancelDelete(): void
    {
        $this->showDeleteModal = false;
        $this->deletingKegiatanId = null;
    }

    protected function resetForm(): void
    {
        $this->nama_kegiatan = '';
        $this->lokasi = '';
        $this->bidang_kegiatan_id = '';
        $this->sub_bidang_kegiatan_id = '';
        $this->rencana_anggaran = '';
        $this->realisasi_anggaran = '';
        $this->status = 'perencanaan';
        $this->foto_progres = null;
        $this->existing_foto_progres = null;
        $this->editingKegiatanId = null;
        $this->detailBelanja = $this->detailBelanjaBaru();
    }

    public function formatRupiah($angka)
    {
        return 'Rp '.number_format($angka, 0, ',', '.');
    }

    public function getStatusBadgeVariant($status)
    {
        if ($status instanceof StatusKegiatan) {
            return $status->getColor();
        }

        return 'warning';
    }

    public function getStatusIcon($status)
    {
        if ($status instanceof StatusKegiatan) {
            return $status->getIcon();
        }

        return 'fas fa-calendar-alt';
    }

    protected function sumberDanaId(): ?int
    {
        return SumberDana::query()->where('kode', $this->dana)->value('id');
    }

    public function render()
    {
        $sumberDana = SumberDana::query()->where('kode', $this->dana)->first();

        $kegiatans = Kegiatan::query()
            ->untukSumberDana($sumberDana?->id)
            ->with(['subBidang.bidang', 'sumberDana'])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('nama_kegiatan', 'like', '%'.$this->search.'%')
                        ->orWhere('lokasi', 'like', '%'.$this->search.'%')
                        ->orWhereHas('subBidang', function ($sq) {
                            $sq->where('nama', 'like', '%'.$this->search.'%')
                                ->orWhere('kode', 'like', '%'.$this->search.'%');
                        })
                        ->orWhereHas('subBidang.bidang', function ($bq) {
                            $bq->where('nama', 'like', '%'.$this->search.'%')
                                ->orWhere('kode', 'like', '%'.$this->search.'%');
                        });
                });
            })
            ->when($this->menggunakanBidang() && $this->filterBidang, function ($query) {
                $query->whereHas('subBidang', function ($q) {
                    $q->where('bidang_kegiatan_id', $this->filterBidang);
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $bidangs = $this->menggunakanBidang()
            ? BidangKegiatan::orderBy('kode')->get()
            : collect();

        return view('livewire.admin.kegiatan-management', [
            'kegiatans' => $kegiatans,
            'bidangs' => $bidangs,
            'rekeningOptions' => RekeningAnggaran::query()->where('aktif', true)->where('kode', 'like', '5.%')->whereDoesntHave('children')->orderBy('kode')->get(),
            'sumberDana' => $sumberDana,
            'danaLabel' => SumberDana::labelUntukKode($this->dana),
            'menggunakanBidang' => $this->menggunakanBidang(),
        ]);
    }
}
