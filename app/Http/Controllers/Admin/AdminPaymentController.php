<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\FlowService;
use Illuminate\Http\Request;

class AdminPaymentController extends Controller
{
    protected FlowService $flow;

    public function __construct(FlowService $flow)
    {
        $this->flow = $flow;
    }

    public function index()
    {
        $settings = Setting::getGroup('store');
        $flowConfigured = $this->flow->isConfigured();

        return view('admin.payments.index', compact('settings', 'flowConfigured'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'flow_api_key' => 'nullable|string|max:255',
            'flow_secret_key' => 'nullable|string|max:255',
            'transfer_discount_percentage' => 'nullable|numeric|min:0|max:100',
        ]);

        Setting::set('flow_api_key', trim((string) $request->input('flow_api_key', '')), 'text', 'store');
        Setting::set('flow_secret_key', trim((string) $request->input('flow_secret_key', '')), 'text', 'store');
        Setting::set('flow_sandbox', $request->has('flow_sandbox'), 'boolean', 'store');
        Setting::set('flow_active', $request->has('flow_active'), 'boolean', 'store');

        if ($request->filled('transfer_discount_percentage')) {
            Setting::set('transfer_discount_percentage', (float) $request->input('transfer_discount_percentage'), 'float', 'store');
        }

        return redirect()->back()->with('success', 'Configuración de pasarelas de pago guardada exitosamente.');
    }

    public function testFlow()
    {
        $result = $this->flow->testConnection();
        return response()->json($result);
    }
}
