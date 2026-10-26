<?php

namespace App\Http\Controllers;

use App\Models\ServiceRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ServiceRequestController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        Gate::authorize('viewAny', ServiceRequest::class);

        if ($user->is_admin) {
            $requests = ServiceRequest::latest()->get();
        } else {
            $requests = ServiceRequest::where('user_id', $user->id)
                ->latest()
                ->get();
        }

        return view('requests.index', compact('requests'));
    }

    public function show(ServiceRequest $serviceRequest)
    {
        Gate::authorize('view', $serviceRequest);

        return view('requests.show', compact('serviceRequest'));
    }

    public function store(Request $request)
    {
        Gate::authorize('create', ServiceRequest::class);

        $validated = $request->validate([
            'item_name' => ['required', 'string', 'max:150'],
            'quantity' => ['required', 'integer', 'min:1'],
            'purpose' => ['required', 'string', 'max:2000'],
        ]);

        $user = auth()->user();

        $serviceRequest = new ServiceRequest();

$serviceRequest->user_id = $user->id;
$serviceRequest->requester_name = $user->name;
$serviceRequest->requester_email = $user->email;
$serviceRequest->item_name = $validated['item_name'];
$serviceRequest->quantity = $validated['quantity'];
$serviceRequest->purpose = $validated['purpose'];
$serviceRequest->status = 'pending';

$serviceRequest->save();

        return redirect()
            ->route('requests.index')
            ->with('success', 'Request created successfully.');
    }

    public function updateStatus(Request $request, ServiceRequest $serviceRequest)
    {
        Gate::authorize('updateStatus', $serviceRequest);

        $validated = $request->validate([
            'status' => ['required', 'in:pending,approved,rejected'],
        ]);

        $serviceRequest->status = $validated['status'];
        $serviceRequest->save();

        return redirect()
            ->route('requests.show', $serviceRequest)
            ->with('success', 'Request status updated successfully.');
    }
}