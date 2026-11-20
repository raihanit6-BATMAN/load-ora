<?php

namespace App\Livewire\Goods;

use App\Models\ActivityLog;
use App\Models\Goods;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

class GoodsIndex extends Component
{
    public bool $showModal = false;
    public ?int $editingGoodsId = null;

    public string $name = '';
    public string $code = '';
    public string $unit = 'MT';
    public string $description = '';
    public string $status = 'active';

    public function openCreateModal(): void
    {
        Gate::authorize('create', Goods::class);

        $this->resetValidation();
        $this->reset(['editingGoodsId', 'name', 'code', 'description']);
        $this->unit = 'MT';
        $this->status = 'active';

        $this->showModal = true;
    }

    public function openEditModal(int $id): void
    {
        $goods = Goods::findOrFail($id);
        Gate::authorize('update', $goods);

        $this->editingGoodsId = $goods->id;
        $this->name = $goods->name;
        $this->code = (string) $goods->code;
        $this->unit = $goods->unit;
        $this->description = (string) $goods->description;
        $this->status = $goods->status;

        $this->showModal = true;
    }

    public function save(): void
    {
        $rules = [
            'name' => 'required|string|max:255|unique:goods,name,' . ($this->editingGoodsId ?? 'NULL') . ',id',
            'code' => 'nullable|string|max:50|unique:goods,code,' . ($this->editingGoodsId ?? 'NULL') . ',id',
            'unit' => 'required|string|max:20',
            'description' => 'nullable|string|max:500',
            'status' => 'required|in:active,inactive',
        ];

        $this->validate($rules);

        $data = [
            'name' => trim($this->name),
            'code' => $this->code ? strtoupper(trim($this->code)) : null,
            'unit' => trim($this->unit),
            'description' => $this->description ?: null,
            'status' => $this->status,
        ];

        if ($this->editingGoodsId) {
            $goods = Goods::findOrFail($this->editingGoodsId);
            Gate::authorize('update', $goods);
            $old = $goods->toArray();
            $goods->update($data);

            ActivityLog::record('goods.updated', 'goods', "Updated material {$goods->name}", $goods, $old, $goods->toArray());
            $this->dispatch('toast', message: 'Material updated successfully!', type: 'success');
        } else {
            Gate::authorize('create', Goods::class);
            $goods = Goods::create($data);

            ActivityLog::record('goods.created', 'goods', "Created material {$goods->name}", $goods, newValues: $goods->toArray());
            $this->dispatch('toast', message: 'Material category registered!', type: 'success');
        }

        $this->showModal = false;
    }

    public function delete(int $id): void
    {
        $goods = Goods::findOrFail($id);
        Gate::authorize('delete', $goods);

        $old = $goods->toArray();
        $goods->delete();

        ActivityLog::record('goods.deleted', 'goods', "Deleted material {$goods->name}", $goods, oldValues: $old);
        $this->dispatch('toast', message: 'Material category deleted.', type: 'info');
    }

    public function render()
    {
        $goodsList = Goods::withCount('dispatches')
            ->withSum('dispatches as total_loaded_tonnage', 'loading_quantity')
            ->withSum('dispatches as total_received_tonnage', 'total_received_quantity')
            ->orderBy('name')
            ->get();

        return view('livewire.goods.goods-index', [
            'goodsList' => $goodsList,
        ])->layout('layouts.app', ['title' => 'Goods & Aggregates']);
    }
}
