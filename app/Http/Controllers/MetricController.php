<?php

namespace App\Http\Controllers;

use App\Models\Metric;
use App\Models\Division;
use Illuminate\Http\Request;

class MetricController extends Controller
{
    public function index()
    {
        $metrics = Metric::with('division')->get();
        $divisions = Division::all();
        return view('metrics.index', compact('metrics', 'divisions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:Leading,Lagging',
            'division_id' => 'required|exists:divisions,id',
            'unit' => 'required|string|max:50',
            'polarity' => 'required|in:Positive,Negative',
        ]);

        Metric::create($request->all());

        return redirect()->route('metrics.index')->with('success', 'Metrik berhasil ditambahkan.');
    }

    public function update(Request $request, Metric $metric)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:Leading,Lagging',
            'division_id' => 'required|exists:divisions,id',
            'unit' => 'required|string|max:50',
            'polarity' => 'required|in:Positive,Negative',
        ]);

        $metric->update($request->all());

        return redirect()->route('metrics.index')->with('success', 'Metrik berhasil diperbarui.');
    }

    public function destroy(Metric $metric)
    {
        // Simple check to prevent deleting metrics used in targets
        if (\App\Models\Target::where('metric_id', $metric->id)->exists()) {
            return redirect()->route('metrics.index')->with('error', 'Metrik tidak dapat dihapus karena sedang digunakan oleh Target/WIG.');
        }

        $metric->delete();
        return redirect()->route('metrics.index')->with('success', 'Metrik berhasil dihapus.');
    }
}
