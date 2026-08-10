<?php

namespace App\Repositories;

use App\Models\Faq;

class FaqRepository
{
    public function getAll($perPage = 15)
    {
        return Faq::latest()->paginate($perPage);
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
