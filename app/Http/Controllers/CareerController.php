<?php

namespace App\Http\Controllers;

use App\Models\Career;
use App\Models\CareerApplication;
use App\Models\NavigationItem;
use App\Models\User;
use App\Settings\GeneralSettings;
use Filament\Notifications\Actions\Action as NotificationAction;
use Filament\Notifications\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class CareerController extends Controller
{
    public function __construct(private GeneralSettings $settings) {}

    /**
     * Show a single career detail page with application form.
     */
    public function show(string $slug)
    {
        // Extract the numeric ID from the trailing segment of the slug (e.g. "it-front-end-developer-1" → 1)
        $id = (int) last(explode('-', $slug));

        $career = Career::where('status', 'available')
            ->findOrFail($id);

        $navigation = NavigationItem::active()
            ->rootItems()
            ->with('children')
            ->get()
            ->map(fn ($item) => [
                'id'       => $item->id,
                'label'    => $item->label,
                'url'      => $item->url,
                'target'   => $item->target,
                'children' => $item->children->map(fn ($child) => [
                    'id'     => $child->id,
                    'label'  => $child->label,
                    'url'    => $child->url,
                    'target' => $child->target,
                ])->all(),
            ])->all();

        return Inertia::render('Careers/Show', [
            'career' => [
                'id'               => $career->id,
                'title'            => $career->title,
                'department'       => $career->department,
                'location'         => $career->location,
                'employment_type'  => $career->employment_type,
                'salary_range'     => $career->salary_range,
                'summary'          => $career->summary,
                'description'      => $career->description,
                'responsibilities' => $career->responsibilities,
                'requirements'     => $career->requirements,
                'created_at'       => $career->created_at->toIso8601String(),
                'slug'             => $career->slug,
            ],
            'navigation' => $navigation,
            'appName'    => $this->settings->company_name ?? config('app.name'),
            'companyLogo' => $this->settings->company_logo ? tenant_asset($this->settings->company_logo) : null,
        ]);
    }

    /**
     * Submit a job application.
     */
    public function apply(Request $request, int $id)
    {
        $career = Career::where('status', 'available')->findOrFail($id);

        // Rate limit: max 2 submissions per minute per IP address
        $rateLimitKey = 'career-apply:' . $request->ip();
        if (RateLimiter::tooManyAttempts($rateLimitKey, 2)) {
            $seconds = RateLimiter::availableIn($rateLimitKey);
            return back()->with(
                'error',
                "Too many submissions. Please wait {$seconds} second(s) before trying again."
            );
        }
        RateLimiter::hit($rateLimitKey, 60); // 60-second decay window

        $validated = $request->validate([
            'first_name'   => 'required|string|max:100',
            'last_name'    => 'required|string|max:100',
            'email'        => 'required|email|max:150',
            'phone'        => 'required|string|max:30',
            'resume'       => 'required|file|mimes:pdf,doc,docx|max:5120',
            'cover_letter' => 'nullable|string|max:3000',
        ]);

        // Check if this person already applied for this position (via email OR phone)
        $alreadyApplied = CareerApplication::where('career_id', $career->id)
            ->where(function ($q) use ($validated) {
                $q->where('email', strtolower($validated['email']))
                  ->orWhere('phone', $validated['phone']);
            })->exists();

        if ($alreadyApplied) {
            return back()->with(
                'error',
                'You have already submitted an application for this position. We will reach out if your profile is a match. You are welcome to apply for other open positions.'
            );
        }

        $resumePath = $request->file('resume')->store('career-applications', 'public');

        $application = CareerApplication::create([
            'career_id'    => $career->id,
            'first_name'   => $validated['first_name'],
            'last_name'    => $validated['last_name'],
            'email'        => strtolower($validated['email']),
            'phone'        => $validated['phone'],
            'resume_path'  => $resumePath,
            'cover_letter' => $validated['cover_letter'] ?? null,
            'status'       => 'new',
        ]);

        // Notify Admin/Manager users in Filament
        $users = User::role(['Admin', 'Manager'])->get();
        $viewUrl = '/admin/careers/' . $career->id;

        foreach ($users as $user) {
            Notification::make()
                ->title('New Job Application')
                ->body("{$application->first_name} {$application->last_name} applied for {$career->title}")
                ->icon('heroicon-o-briefcase')
                ->iconColor('success')
                ->actions([
                    NotificationAction::make('view')
                        ->label('View Application')
                        ->url($viewUrl),
                ])
                ->sendToDatabase($user);
        }

        return back()->with('success', 'Your application has been submitted! We will be in touch soon.');
    }
}
