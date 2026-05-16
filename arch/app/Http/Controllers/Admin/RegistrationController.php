<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Registration;
use App\Services\RegistrationService;

class RegistrationController extends Controller
{
    protected $service;

    public function __construct(RegistrationService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        $registrations = Registration::with(['athlete', 'coach'])->latest()->get();
        return view('admin.registrations.index', compact('registrations'));
    }

    public function approve(Registration $registration)
    {
        $this->service->approve($registration);
        return back()->with('success', 'Approved');
    }

    public function reject(Registration $registration)
    {
        $this->service->reject($registration);
        return back()->with('error', 'Rejected');
    }
}
