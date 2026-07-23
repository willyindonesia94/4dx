<?php

namespace App\Http\Controllers;

use App\Models\Target;
use App\Models\MasterWig;
use App\Models\MasterLm;
use App\Models\Metric;
use App\Models\Location;
use Illuminate\Http\Request;

class TargetController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        // 1. Load Master WIGs and their Master LMs and Matrix Targets
        $masterWigs = MasterWig::with(['metric', 'division', 'masterLms.metric', 'masterLms.targets.location'])->get();
        
        $metrics = Metric::all();
        $locations = Location::all();

        return view('targets.index', compact('masterWigs', 'metrics', 'locations'));
    }

    public function create(Request $request)
    {
        $type = $request->query('type', 'master_wig'); // master_wig, master_lm, target
        $master_wig_id = $request->query('master_wig_id');
        $master_lm_id = $request->query('master_lm_id');
        
        $metrics = Metric::all();
        $locations = Location::all();

        return view('targets.create', compact('type', 'metrics', 'locations', 'master_wig_id', 'master_lm_id'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        $type = $request->type;

        if ($type === 'master_wig') {
            $request->validate([
                'name' => 'required|string',
                'metric_id' => 'required|exists:metrics,id',
                'target_value' => 'required|numeric',
                'period' => 'required|string',
            ]);
            MasterWig::create([
                'name' => $request->name,
                'metric_id' => $request->metric_id,
                'target_value' => $request->target_value,
                'period' => $request->period,
                'division_id' => $user->division_id ?? 1,
                'created_by' => $user->id,
            ]);
        } elseif ($type === 'master_lm') {
            $request->validate([
                'master_wig_id' => 'required|exists:master_wigs,id',
                'name' => 'required|string',
                'metric_id' => 'required|exists:metrics,id',
            ]);
            MasterLm::create([
                'master_wig_id' => $request->master_wig_id,
                'name' => $request->name,
                'metric_id' => $request->metric_id,
                'created_by' => $user->id,
            ]);
        } elseif ($type === 'target') {
            $request->validate([
                'targetable_id' => 'required|exists:master_lms,id',
                'location_id' => 'required|exists:locations,id',
                'target_value' => 'required|numeric',
            ]);
            Target::create([
                'targetable_type' => MasterLm::class,
                'targetable_id' => $request->targetable_id,
                'location_id' => $request->location_id,
                'target_value' => $request->target_value,
                'status' => 'Belum Mulai'
            ]);
        }

        return redirect()->route('targets.index')->with('success', 'Data berhasil ditambahkan.');
    }

    public function edit(Target $target)
    {
        // For simplicity, we only edit Target allocation (the matrix value)
        $locations = Location::all();
        return view('targets.edit', compact('target', 'locations'));
    }

    public function update(Request $request, Target $target)
    {
        $request->validate([
            'target_value' => 'required|numeric',
            'status' => 'nullable|in:Belum Mulai,On Track,Delay,Selesai',
        ]);
        
        $target->update($request->only('target_value', 'status'));

        return redirect()->route('targets.index')->with('success', 'Alokasi Target berhasil diperbarui.');
    }

    public function destroy(Target $target)
    {
        $target->delete();
        return redirect()->route('targets.index')->with('success', 'Alokasi Target berhasil dihapus.');
    }
}
