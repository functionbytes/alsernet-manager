<?php

namespace Modules\Helpdesk\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Modules\Helpdesk\Models\Ticket;
use Modules\Helpdesk\Models\TicketMessage;
use App\Models\User;

class NotificationService
{
    /**
     * Notify customer when ticket is created
     */
    public function notifyTicketCreated(Ticket $ticket): void
    {
        try {
            $data = [
                'ticket_number' => $ticket->ticket_number,
                'subject' => $ticket->subject,
                'category' => $ticket->category?->name ?? 'N/A',
                'priority' => $ticket->priority?->name ?? 'N/A',
                'status' => $ticket->status?->name ?? 'N/A'
            ];

            $this->sendEmail(
                $ticket->customer_email,
                'Ticket creado: ' . $ticket->ticket_number,
                'helpdesk.emails.ticket_created',
                array_merge(['ticket' => $ticket], $data)
            );

            Log::info('Ticket creation notification sent', [
                'ticket_id' => $ticket->id,
                'customer_email' => $ticket->customer_email
            ]);
        } catch (\Exception $e) {
            Log::error('Error sending ticket creation notification', [
                'ticket_id' => $ticket->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Notify agent when ticket is assigned
     */
    public function notifyTicketAssigned(Ticket $ticket, User $agent): void
    {
        try {
            $data = [
                'ticket_number' => $ticket->ticket_number,
                'subject' => $ticket->subject,
                'customer_name' => $ticket->customer_name,
                'priority' => $ticket->priority?->name ?? 'N/A',
                'category' => $ticket->category?->name ?? 'N/A',
                'agent_name' => $agent->name
            ];

            $this->sendEmail(
                $agent->email,
                'Ticket asignado: ' . $ticket->ticket_number,
                'helpdesk.emails.ticket_assigned',
                array_merge(['ticket' => $ticket, 'agent' => $agent], $data)
            );

            Log::info('Ticket assignment notification sent', [
                'ticket_id' => $ticket->id,
                'agent_id' => $agent->id
            ]);
        } catch (\Exception $e) {
            Log::error('Error sending ticket assignment notification', [
                'ticket_id' => $ticket->id,
                'agent_id' => $agent->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Notify relevant parties when new message is added
     */
    public function notifyNewMessage(TicketMessage $message): void
    {
        try {
            $ticket = $message->ticket;

            if (!$ticket) {
                return;
            }

            $messagePreview = substr(strip_tags($message->message), 0, 100);
            if (strlen($message->message) > 100) {
                $messagePreview .= '...';
            }

            $data = [
                'ticket_number' => $ticket->ticket_number,
                'message_preview' => $messagePreview,
                'sender' => $message->user?->name ?? 'Cliente',
                'is_internal' => $message->is_internal
            ];

            if (!$message->is_internal && $ticket->assigned_to) {
                $agent = User::find($ticket->assigned_to);
                if ($agent) {
                    $this->sendEmail(
                        $agent->email,
                        'Nuevo mensaje en ticket: ' . $ticket->ticket_number,
                        'helpdesk.emails.new_message',
                        array_merge(['ticket' => $ticket, 'message' => $message], $data)
                    );
                }
            }

            if ($message->is_internal) {
                $agents = User::whereHas('roles', function ($q) {
                    $q->where('name', 'helpdesk-agent');
                })->where('is_active', true)->get();

                foreach ($agents as $agent) {
                    $this->sendEmail(
                        $agent->email,
                        'Nota interna en ticket: ' . $ticket->ticket_number,
                        'helpdesk.emails.internal_note',
                        array_merge(['ticket' => $ticket, 'message' => $message], $data)
                    );
                }
            }

            Log::info('New message notification sent', [
                'ticket_id' => $ticket->id,
                'message_id' => $message->id,
                'is_internal' => $message->is_internal
            ]);
        } catch (\Exception $e) {
            Log::error('Error sending new message notification', [
                'message_id' => $message->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Notify about upcoming SLA breach
     */
    public function notifySlaWarning(Ticket $ticket): void
    {
        try {
            $timeRemaining = now()->diffInMinutes($ticket->due_at);

            $data = [
                'ticket_number' => $ticket->ticket_number,
                'subject' => $ticket->subject,
                'due_at' => $ticket->due_at,
                'time_remaining_minutes' => $timeRemaining
            ];

            if ($ticket->assigned_to) {
                $agent = User::find($ticket->assigned_to);
                if ($agent) {
                    $this->sendEmail(
                        $agent->email,
                        'Alerta SLA: ' . $ticket->ticket_number,
                        'helpdesk.emails.sla_warning',
                        array_merge(['ticket' => $ticket], $data)
                    );
                }
            }

            Log::info('SLA warning notification sent', [
                'ticket_id' => $ticket->id,
                'time_remaining' => $timeRemaining
            ]);
        } catch (\Exception $e) {
            Log::error('Error sending SLA warning notification', [
                'ticket_id' => $ticket->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Notify about SLA breach
     */
    public function notifySlaBreach(Ticket $ticket): void
    {
        try {
            $breachTime = $ticket->due_at->diffInMinutes(now());

            $data = [
                'ticket_number' => $ticket->ticket_number,
                'subject' => $ticket->subject,
                'due_at' => $ticket->due_at,
                'breach_time_minutes' => $breachTime
            ];

            if ($ticket->assigned_to) {
                $agent = User::find($ticket->assigned_to);
                if ($agent) {
                    $this->sendEmail(
                        $agent->email,
                        'SLA Incumplido: ' . $ticket->ticket_number,
                        'helpdesk.emails.sla_breach',
                        array_merge(['ticket' => $ticket], $data)
                    );
                }
            }

            $managers = User::whereHas('roles', function ($q) {
                $q->where('name', 'helpdesk-manager');
            })->where('is_active', true)->get();

            foreach ($managers as $manager) {
                $this->sendEmail(
                    $manager->email,
                    'SLA Incumplido: ' . $ticket->ticket_number,
                    'helpdesk.emails.sla_breach',
                    array_merge(['ticket' => $ticket], $data)
                );
            }

            Log::warning('SLA breach notification sent', [
                'ticket_id' => $ticket->id,
                'breach_time' => $breachTime
            ]);
        } catch (\Exception $e) {
            Log::error('Error sending SLA breach notification', [
                'ticket_id' => $ticket->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Notify customer when ticket is closed
     */
    public function notifyTicketClosed(Ticket $ticket): void
    {
        try {
            $data = [
                'ticket_number' => $ticket->ticket_number,
                'subject' => $ticket->subject,
                'closed_at' => $ticket->closed_at,
                'closed_by_name' => $ticket->closedBy?->name ?? 'Sistema'
            ];

            $this->sendEmail(
                $ticket->customer_email,
                'Ticket cerrado: ' . $ticket->ticket_number,
                'helpdesk.emails.ticket_closed',
                array_merge(['ticket' => $ticket], $data)
            );

            Log::info('Ticket closed notification sent', [
                'ticket_id' => $ticket->id,
                'customer_email' => $ticket->customer_email
            ]);
        } catch (\Exception $e) {
            Log::error('Error sending ticket closed notification', [
                'ticket_id' => $ticket->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Send custom notification to customer
     */
    public function notifyCustomer(Ticket $ticket, string $template, array $data = []): void
    {
        try {
            $subject = $data['subject'] ?? 'Actualización del ticket: ' . $ticket->ticket_number;

            $this->sendEmail(
                $ticket->customer_email,
                $subject,
                "helpdesk.emails.{$template}",
                array_merge(['ticket' => $ticket], $data)
            );

            Log::info('Custom customer notification sent', [
                'ticket_id' => $ticket->id,
                'template' => $template
            ]);
        } catch (\Exception $e) {
            Log::error('Error sending custom customer notification', [
                'ticket_id' => $ticket->id,
                'template' => $template,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Send email notification
     */
    private function sendEmail(string $to, string $subject, string $view, array $data): void
    {
        try {
            Mail::send($view, $data, function ($message) use ($to, $subject) {
                $message->to($to)
                    ->subject($subject);
            });
        } catch (\Exception $e) {
            Log::error('Error sending email', [
                'to' => $to,
                'subject' => $subject,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
}
