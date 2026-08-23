<?php

namespace App\Repositories;

use App\Models\Faq;
use Illuminate\Database\Eloquent\Collection;

class FaqRepository
{
    public function getAll($perPage = 15)
    {
        return Faq::latest()->paginate($perPage);
    }

    public function getActive(): Collection
    {
        return Faq::query()
            ->where('is_active', true)
            ->latest()
            ->get();
    }

    public function findById($id)
    {
        return Faq::findOrFail($id);
    }

    public function create(array $data)
    {
        return Faq::create($data);
    }

    public function update(Faq $faq, array $data)
    {
        $faq->update($data);

        return $faq;
    }

    public function delete(Faq $faq)
    {
        return $faq->delete();
    }
}
