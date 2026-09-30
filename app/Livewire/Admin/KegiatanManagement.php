<?php

namespace App\Livewire\Admin;

use App\Enums\StatusKegiatan;
use App\Models\BidangKegiatan;
use App\Models\Kegiatan;
use App\Models\Pemasukan;
use App\Models\Pengeluaran;
use App\Models\SubBidangKegiatan;
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
        return [
            'nama_kegiatan' => ['required', 'string', 'max:255'],
            'lokasi' => ['required', 'string', 'max:255'],
            'bidang_kegiatan_id' => ['required', 'exists:bidang_kegiatan,id'],
            'sub_bidang_kegiatan_id' => ['required', 'exists:sub_bidang_kegiatan,id'],
            'rencana_anggaran' => ['required', 'numeric', 'min:0', 'max:9999999999999'],
            'realisasi_anggaran' => ['nullable', 'numeric', 'min:0', 'max:9999999999999'],
            'status' => ['required', Rule::enum(StatusKegiatan::class)],
            'foto_progres' => ['nullable', 'image', 'max:2048'],
        ];
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

    public function openCreateModal(): void
    {
        $this->resetForm();
        $this->editingKegiatanId = null;
        $this->showModal = true;
    }

    public function openDetailModal(int $id): void
    {
        $this->detailKegiatanId = $id;
        $this->detailKegiatan = Kegiatan::with('subBidang.bidang')->findOrFail($id);
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
        $kegiatan = Kegiatan::with('subBidang.bidang')->findOrFail($id);

        $this->editingKegiatanId = $id;
        $this->nama_kegiatan = $kegiatan->nama_kegiatan;
        $this->lokasi = $kegiatan->lokasi;
        $this->bidang_kegiatan_id = (string) ($kegiatan->subBidang?->bidang_kegiatan_id ?? '');
        $this->sub_bidang_kegiatan_id = (string) ($kegiatan->sub_bidang_kegiatan_id ?? '');
        $this->rencana_anggaran = (string) $kegiatan->rencana_anggaran;
        $this->realisasi_anggaran = (string) ($kegiatan->realisasi_anggaran ?? '');
        $this->status = $kegiatan->status->value;
        $this->existing_foto_progres = $kegiatan->foto_progres;
        $this->foto_progres = null;

        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        $sub = SubBidangKegiatan::findOrFail($this->sub_bidang_kegiatan_id);
        if ((string) $sub->bidang_kegiatan_id !== (string) $this->bidang_kegiatan_id) {
            $this->addError('sub_bidang_kegiatan_id', 'Sub-bidang tidak termasuk dalam bidang yang dipilih.');

            return;
        }

        $totalPemasukan = Pemasukan::sum('jumlah');
        $totalPengeluaranNonKegiatan = Pengeluaran::whereNull('id_kegiatan')->sum('jumlah');
        $totalAnggaranKegiatanLain = Kegiatan::query();

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
            'sub_bidang_kegiatan_id' => $this->sub_bidang_kegiatan_id,
            'rencana_anggaran' => $this->rencana_anggaran,
            'realisasi_anggaran' => $this->realisasi_anggaran ?: null,
            'status' => $this->status,
            'foto_progres' => $path,
        ];

        if ($this->editingKegiatanId) {
            $kegiatan = Kegiatan::findOrFail($this->editingKegiatanId);
            $kegiatan->update($data);

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
                $nominal = $this->realisasi_anggaran ?: $kegiatan->rencana_anggaran;
                $this->syncOtomatisPengeluaran($kegiatan, $nominal);

                session()->flash('success', 'Data kegiatan berhasil diperbarui dan sebesar '.$this->formatRupiah($nominal).' telah dicatat sebagai pengeluaran (status selesai).');
            }
        } else {
            $kegiatan = Kegiatan::create($data);

            $perluCatat = $this->status === StatusKegiatan::SELESAI->value
                || ($this->status === StatusKegiatan::BERJALAN->value && $this->realisasi_anggaran);

            if ($perluCatat) {
                $nominal = $this->realisasi_anggaran ?: $kegiatan->rencana_anggaran;
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
        $pengeluaran = (clone $this->queryOtomatisPengeluaran($kegiatan))->first();

        $payload = [
            'jumlah' => $nominal,
            'tanggal' => $pengeluaran?->tanggal ?? date('Y-m-d'),
            'keterangan' => 'Realisasi Anggaran Kegiatan: '.$kegiatan->nama_kegiatan,
            'id_kegiatan' => $kegiatan->id,
        ];

        if ($pengeluaran) {
            $pengeluaran->update($payload);
        } else {
            Pengeluaran::create($payload);
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

    public function render()
    {
        $kegiatans = Kegiatan::query()
            ->with('subBidang.bidang')
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
            ->when($this->filterBidang, function ($query) {
                $query->whereHas('subBidang', function ($q) {
                    $q->where('bidang_kegiatan_id', $this->filterBidang);
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $bidangs = BidangKegiatan::orderBy('kode')->get();

        return view('livewire.admin.kegiatan-management', [
            'kegiatans' => $kegiatans,
            'bidangs' => $bidangs,
        ]);
    }
}
