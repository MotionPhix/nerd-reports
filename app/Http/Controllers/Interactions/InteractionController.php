<?php

namespace App\Http\Controllers\Interactions;

use App\Http\Controllers\Controller;
use App\Services\InteractionService;
use App\Models\Interaction;
use App\Models\Contact;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class InteractionController extends Controller
{
  use AuthorizesRequests;

  public function __construct(
    protected InteractionService $interactionService
  ) {}

  /**
   * Display interactions index
   */
  public function index(Request $request)
  {
    $user = Auth::user();

    $filters = $request->only([
      'type', 'contact_id', 'project_id', 'date_from', 'date_to',
      'follow_up_required', 'overdue_follow_up', 'search',
      'order_by', 'order_direction'
    ]);

    $interactions = $this->interactionService->getUserInteractions($user, $filters);
    $overdueFollowUps = $this->interactionService->getOverdueFollowUps($user);
    $todayFollowUps = $this->interactionService->getTodayFollowUps($user);
    $upcomingFollowUps = $this->interactionService->getUpcomingFollowUps($user);
    $interactionStats = $this->interactionService->getUserInteractionStats($user);

    return Inertia::render('Interactions/Index', [
      'interactions' => $interactions,
      'overdueFollowUps' => $overdueFollowUps,
      'todayFollowUps' => $todayFollowUps,
      'upcomingFollowUps' => $upcomingFollowUps,
      'interactionStats' => $interactionStats,
      'filters' => $filters,
      'contacts' => Contact::with('firm')->get(),
      'projects' => Project::with('contact.firm')->get(),
      'interactionTypes' => [
        ['value' => 'phone_call', 'label' => 'Phone Call'],
        ['value' => 'email', 'label' => 'Email'],
        ['value' => 'meeting', 'label' => 'Meeting'],
        ['value' => 'video_call', 'label' => 'Video Call'],
        ['value' => 'whatsapp', 'label' => 'WhatsApp'],
        ['value' => 'sms', 'label' => 'SMS'],
        ['value' => 'in_person', 'label' => 'In Person'],
        ['value' => 'slack', 'label' => 'Slack'],
        ['value' => 'teams', 'label' => 'Microsoft Teams'],
        ['value' => 'other', 'label' => 'Other'],
      ]
    ]);
  }

  /**
   * Show interaction details
   */
  public function show(Interaction $interaction)
  {
    $this->authorize('view', $interaction);

    $interaction->load(['contact.firm', 'project', 'user']);

    return Inertia::render('Interactions/Show', [
      'interaction' => $interaction,
      'formattedDuration' => $interaction->getFormattedDuration(),
      'isOverdue' => $interaction->isOverdue(),
      'isDueToday' => $interaction->isDueToday(),
      'isDueSoon' => $interaction->isDueSoon(),
    ]);
  }

  /**
   * Show create interaction form
   */
  public function create(Request $request)
  {
    return Inertia::render('Interactions/Create', [
      'contacts' => Contact::with('firm')->get(),
      'projects' => Project::with('contact.firm')->get(),
      'users' => User::select(['id', 'first_name', 'last_name'])->get(),
      'defaultContact' => $request->contact_id,
      'defaultProject' => $request->project_id,
      'interactionTypes' => [
        ['value' => 'phone_call', 'label' => 'Phone Call'],
        ['value' => 'email', 'label' => 'Email'],
        ['value' => 'meeting', 'label' => 'Meeting'],
        ['value' => 'video_call', 'label' => 'Video Call'],
        ['value' => 'whatsapp', 'label' => 'WhatsApp'],
        ['value' => 'sms', 'label' => 'SMS'],
        ['value' => 'in_person', 'label' => 'In Person'],
        ['value' => 'slack', 'label' => 'Slack'],
        ['value' => 'teams', 'label' => 'Microsoft Teams'],
        ['value' => 'other', 'label' => 'Other'],
      ]
    ]);
  }

  /**
   * Store a new interaction
   */
  public function store(Request $request)
  {
    $request->validate([
      'contact_id' => 'required|exists:contacts,uuid',
      'project_id' => 'nullable|exists:projects,uuid',
      'type' => 'required|in:phone_call,email,meeting,video_call,whatsapp,sms,in_person,slack,teams,other',
      'subject' => 'required|string|max:255',
      'description' => 'nullable|string',
      'notes' => 'nullable|string',
      'duration_minutes' => 'nullable|integer|min:1|max:1440',
      'interaction_date' => 'required|date',
      'follow_up_required' => 'boolean',
      'follow_up_date' => 'nullable|date|after:interaction_date',
      'outcome' => 'nullable|string',
      'location' => 'nullable|string',
      'participants' => 'nullable|array',
    ]);

    try {
      $interaction = $this->interactionService->createInteraction($request->all());

      return redirect()->route('interactions.show', $interaction)
        ->with('success', 'Interaction recorded successfully!');
    } catch (\Exception $e) {
      return back()->withErrors(['error' => 'Failed to create interaction: ' . $e->getMessage()]);
    }
  }

  /**
   * Show edit interaction form
   */
  public function edit(Interaction $interaction)
  {
    $this->authorize('update', $interaction);

    $interaction->load(['contact.firm', 'project']);

    return Inertia::render('Interactions/Edit', [
      'interaction' => $interaction,
      'contacts' => Contact::with('firm')->get(),
      'projects' => Project::with('contact.firm')->get(),
      'users' => User::select(['id', 'first_name', 'last_name'])->get(),
      'interactionTypes' => [
        ['value' => 'phone_call', 'label' => 'Phone Call'],
        ['value' => 'email', 'label' => 'Email'],
        ['value' => 'meeting', 'label' => 'Meeting'],
        ['value' => 'video_call', 'label' => 'Video Call'],
        ['value' => 'whatsapp', 'label' => 'WhatsApp'],
        ['value' => 'sms', 'label' => 'SMS'],
        ['value' => 'in_person', 'label' => 'In Person'],
        ['value' => 'slack', 'label' => 'Slack'],
        ['value' => 'teams', 'label' => 'Microsoft Teams'],
        ['value' => 'other', 'label' => 'Other'],
      ]
    ]);
  }

  /**
   * Update an interaction
   */
  public function update(Request $request, Interaction $interaction)
  {
    $this->authorize('update', $interaction);

    $request->validate([
      'subject' => 'required|string|max:255',
      'description' => 'nullable|string',
      'notes' => 'nullable|string',
      'duration_minutes' => 'nullable|integer|min:1|max:1440',
      'interaction_date' => 'required|date',
      'follow_up_required' => 'boolean',
      'follow_up_date' => 'nullable|date|after:interaction_date',
      'outcome' => 'nullable|string',
      'location' => 'nullable|string',
      'participants' => 'nullable|array',
    ]);

    try {
      $interaction = $this->interactionService->updateInteraction($interaction, $request->all());

      return redirect()->route('interactions.show', $interaction)
        ->with('success', 'Interaction updated successfully!');
    } catch (\Exception $e) {
      return back()->withErrors(['error' => 'Failed to update interaction: ' . $e->getMessage()]);
    }
  }

  /**
   * Complete a follow-up
   */
  public function completeFollowUp(Request $request, Interaction $interaction)
  {
    $this->authorize('update', $interaction);

    $request->validate([
      'outcome' => 'nullable|string',
    ]);

    try {
      $this->interactionService->completeFollowUp($interaction, $request->outcome);

      return back()->with('success', 'Follow-up completed successfully!');
    } catch (\Exception $e) {
      return back()->withErrors(['error' => 'Failed to complete follow-up: ' . $e->getMessage()]);
    }
  }

  /**
   * Schedule a follow-up interaction
   */
  public function scheduleFollowUp(Request $request, Interaction $interaction)
  {
    $this->authorize('update', $interaction);

    $request->validate([
      'type' => 'required|in:phone_call,email,meeting,video_call,whatsapp,sms,in_person,slack,teams,other',
      'subject' => 'required|string|max:255',
      'interaction_date' => 'required|date|after:now',
      'description' => 'nullable|string',
    ]);

    try {
      $followUp = $this->interactionService->scheduleFollowUp($interaction, $request->all());

      return redirect()->route('interactions.show', $followUp)
        ->with('success', 'Follow-up scheduled successfully!');
    } catch (\Exception $e) {
      return back()->withErrors(['error' => 'Failed to schedule follow-up: ' . $e->getMessage()]);
    }
  }

  /**
   * Delete an interaction
   */
  public function destroy(Interaction $interaction)
  {
    $this->authorize('delete', $interaction);

    $interaction->delete();

    return redirect()->route('interactions.index')
      ->with('success', 'Interaction deleted successfully!');
  }

  /**
   * Get interactions for a contact
   */
  public function forContact(Contact $contact)
  {
    $interactions = $this->interactionService->getContactInteractions($contact);

    return response()->json(['interactions' => $interactions]);
  }

  /**
   * Get interactions for a project
   */
  public function forProject(Project $project)
  {
    $interactions = $this->interactionService->getProjectInteractions($project);

    return response()->json(['interactions' => $interactions]);
  }

  /**
   * Get interaction statistics
   */
  public function stats(Request $request)
  {
    $user = Auth::user();
    $startDate = $request->start_date ? \Carbon\Carbon::parse($request->start_date) : null;
    $endDate = $request->end_date ? \Carbon\Carbon::parse($request->end_date) : null;

    $stats = $this->interactionService->getUserInteractionStats($user, $startDate, $endDate);

    return response()->json(['stats' => $stats]);
  }
}
