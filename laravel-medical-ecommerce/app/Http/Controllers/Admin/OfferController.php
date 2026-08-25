<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Offer;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;

class OfferController extends Controller
{
    public function index()
    {
        $offers = Offer::latest()->paginate(15);

        return view('admin.offers.index', compact('offers'));
    }

    public function create()
    {
        return view('admin.offers.create', ['offer' => null]);
    }

    public function store(Request $request)
    {
        $data = $this->validatedData($request);

        if ($request->file('image') instanceof UploadedFile) {
            $data['image'] = $request->file('image')->store('offers', 'public');
        }

        Offer::create($data);

        return redirect()->route('admin.offers.index')
            ->with('success', 'Offer created successfully');
    }

    public function edit(Offer $offer)
    {
        return view('admin.offers.edit', compact('offer'));
    }

    public function update(Request $request, Offer $offer)
    {
        $data = $this->validatedData($request);

        if ($request->file('image') instanceof UploadedFile) {
            $data['image'] = $request->file('image')->store('offers', 'public');
        }

        $offer->update($data);

        return redirect()->route('admin.offers.index')
            ->with('success', 'Offer updated successfully');
    }

    public function destroy(Offer $offer)
    {
        $offer->delete();

        return redirect()->route('admin.offers.index')
            ->with('success', 'Offer deleted successfully');
    }

    private function validatedData(Request $request): array
    {
        $data = $request->validate([
            'title_ar' => 'required|string|max:255',
            'title_en' => 'required|string|max:255',
            'description_ar' => 'nullable|string',
            'description_en' => 'nullable|string',
            'discount_type' => 'required|in:percentage,fixed',
            'discount_value' => 'required|numeric|min:0',
            'target_segment' => 'required|in:all,new_user,loyal_patient,before_after_uploaded,has_completed_appointment',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
            'priority' => 'nullable|integer|min:0',
            'image' => 'nullable|image|max:10240',
            'is_active' => 'nullable|boolean',
        ]);

        $data['priority'] = (int) ($data['priority'] ?? 0);
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
