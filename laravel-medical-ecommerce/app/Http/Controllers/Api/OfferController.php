<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\OfferResource;
use App\Services\OfferService;
use Illuminate\Http\Request;

class OfferController extends Controller
{
    public function __construct(private readonly OfferService $offerService)
    {
    }

    public function active(Request $request)
    {
        $offer = $this->offerService->getActiveForUser($request->user('sanctum'));

        return response()->json([
            'data' => $offer ? new OfferResource($offer) : null,
        ]);
    }
}
