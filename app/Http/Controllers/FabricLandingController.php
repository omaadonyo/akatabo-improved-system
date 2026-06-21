<?php

namespace App\Http\Controllers;

use App\Mail\CustomerQuotationMail;
use App\Models\CustomerQuotation;
use App\Models\Fabric;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class FabricLandingController extends Controller
{
    public function index()
    {
        $fabrics = Fabric::with('business')
            ->whereNotNull('selling_price_per_meter')
            ->where('selling_price_per_meter', '>', 0)
            ->latest()
            ->get();

        $categories = $fabrics->pluck('name')->unique()->values();

        return view('fabrics.index', compact('fabrics', 'categories'));
    }

    public function quote(Fabric $fabric)
    {
        if (! $fabric->selling_price_per_meter || $fabric->selling_price_per_meter <= 0) {
            abort(404);
        }

        $fabric->load('business');

        return view('fabrics.quote', compact('fabric'));
    }

    public function submit(Request $request)
    {
        $validated = $request->validate([
            'fabric_id' => ['required', 'exists:fabrics,id'],
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_email' => ['required', 'email', 'max:255'],
            'customer_phone' => ['nullable', 'string', 'max:255'],
            'customer_message' => ['nullable', 'string', 'max:2000'],
            'length_meters' => ['required', 'numeric', 'min:0.01', 'max:999999.99'],
            'width_meters' => ['nullable', 'numeric', 'min:0.01', 'max:999999.99'],
        ]);

        $fabric = Fabric::with('business')->findOrFail($validated['fabric_id']);

        $totalPrice = $fabric->selling_price_per_meter * $validated['length_meters'];

        $quotation = CustomerQuotation::create([
            'fabric_id' => $fabric->id,
            'business_id' => $fabric->business_id,
            'customer_name' => $validated['customer_name'],
            'customer_email' => $validated['customer_email'],
            'customer_phone' => $validated['customer_phone'] ?? null,
            'customer_message' => $validated['customer_message'] ?? null,
            'length_meters' => $validated['length_meters'],
            'width_meters' => $validated['width_meters'] ?? null,
            'total_price' => $totalPrice,
            'status' => 'pending',
        ]);

        $adminEmail = $fabric->business->email ?? $fabric->business->user?->email;

        if ($adminEmail) {
            Mail::to($adminEmail)->send(new CustomerQuotationMail($quotation));
        }

        return redirect()
            ->route('fabrics.quote', $fabric)
            ->with('success', 'Your quotation request has been submitted successfully! We will get back to you shortly.');
    }
}
