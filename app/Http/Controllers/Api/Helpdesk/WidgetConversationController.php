<?php

namespace App\Http\Controllers\Api\Helpdesk;

use App\Http\Controllers\Controller;
use App\Models\Helpdesk\Conversation;
use App\Models\Helpdesk\ConversationItem;
use App\Models\Helpdesk\ConversationStatus;
use App\Models\Helpdesk\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class WidgetConversationController extends Controller
{
    /**
     * Get or create customer from widget session
     */
    protected function getOrCreateCustomer(Request $request)
    {
        $email = $request->input('customer.email');
        $name = $request->input('customer.name', 'Guest');

        if (! $email) {
            // Generate temporary guest identifier if no email provided
            $email = 'guest_'.uniqid().'@temporary.local';
        }

        $customer = Customer::firstOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'phone' => $request->input('customer.phone'),
                'language' => $request->input('customer.language', 'es'),
                'timezone' => $request->input('customer.timezone', 'America/Mexico_City'),
                'last_seen_at' => now(),
            ]
        );

        // Update last seen
        $customer->updateLastSeen();

        return $customer;
    }

    /**
     * Create a new conversation from widget
     *
     * POST /lc/api/conversations
     * Body: {
     *   customer: { name, email, phone? },
     *   message: string,
     *   subject?: string
     * }
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'customer.name' => 'required|string|max:255',
            'customer.email' => 'nullable|email|max:255',
            'customer.phone' => 'nullable|string|max:50',
            'message' => 'required|string|max:10000',
            'subject' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        // Get or create customer
        $customer = $this->getOrCreateCustomer($request);

        // Get default "open" status
        $openStatus = ConversationStatus::where('is_open', true)
            ->orderBy('order')
            ->first();

        if (! $openStatus) {
            return response()->json([
                'success' => false,
                'message' => 'No hay estados de conversación configurados. Por favor contacte al administrador.',
            ], 500);
        }

        // Create conversation
        $conversation = Conversation::create([
            'customer_id' => $customer->id,
            'status_id' => $openStatus->id,
            'subject' => $request->input('subject', 'Nueva conversación desde widget'),
            'priority' => 'normal',
            'last_message_at' => now(),
        ]);

        // Create first message
        $message = ConversationItem::create([
            'conversation_id' => $conversation->id,
            'author_id' => $customer->id,
            'type' => 'message',
            'body' => $request->input('message'),
            'is_internal' => false,
        ]);

        // Increment customer conversation count
        $customer->incrementConversationCount();

        // Load relationships for response
        $conversation->load(['customer', 'status', 'items.author', 'items.user']);

        // Broadcast new conversation to manager panel
        broadcast(new \App\Events\Helpdesk\ConversationCreated($conversation))->toOthers();

        return response()->json([
            'success' => true,
            'data' => [
                'conversation' => [
                    'id' => $conversation->id,
                    'subject' => $conversation->subject,
                    'status' => $conversation->status->name,
                    'created_at' => $conversation->created_at->toIso8601String(),
                ],
                'customer' => [
                    'email' => $conversation->customer->email,
                    'name' => $conversation->customer->name,
                ],
                'messages' => $conversation->items->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'type' => $item->type,
                        'body' => $item->body,
                        'is_from_customer' => $item->isFromCustomer(),
                        'is_from_agent' => $item->isFromAgent(),
                        'sender_name' => $item->sender_name,
                        'sender_avatar' => $item->sender_avatar,
                        'created_at' => $item->created_at->toIso8601String(),
                    ];
                }),
            ],
        ]);
    }

    /**
     * Get conversation details
     *
     * GET /lc/api/conversations/{id}
     */
    public function show(Request $request, $id)
    {
        $conversation = Conversation::with(['customer', 'status', 'assignee', 'items.author', 'items.user'])
            ->findOrFail($id);

        // Verify customer ownership (basic security)
        $customerEmail = $request->input('customer_email');
        if ($customerEmail && $conversation->customer->email !== $customerEmail) {
            return response()->json([
                'success' => false,
                'message' => 'No autorizado para ver esta conversación.',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'conversation' => [
                    'id' => $conversation->id,
                    'subject' => $conversation->subject,
                    'status' => [
                        'id' => $conversation->status->id,
                        'name' => $conversation->status->name,
                        'is_open' => $conversation->status->is_open,
                    ],
                    'assignee' => $conversation->assignee ? [
                        'id' => $conversation->assignee->id,
                        'name' => $conversation->assignee->name,
                    ] : null,
                    'created_at' => $conversation->created_at->toIso8601String(),
                ],
                'messages' => $conversation->items->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'type' => $item->type,
                        'body' => $item->body,
                        'html_body' => $item->html_body,
                        'is_from_customer' => $item->isFromCustomer(),
                        'is_from_agent' => $item->isFromAgent(),
                        'is_internal' => $item->is_internal,
                        'sender_name' => $item->sender_name,
                        'sender_avatar' => $item->sender_avatar,
                        'created_at' => $item->created_at->toIso8601String(),
                    ];
                })->filter(fn ($item) => ! $item['is_internal']), // Hide internal notes from customer
            ],
        ]);
    }

    /**
     * Send a message from widget
     *
     * POST /lc/api/conversations/{id}/messages
     * Body: {
     *   customer_email: string,
     *   message: string (optional if files provided),
     *   attachments[]: files (optional)
     * }
     */
    public function sendMessage(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'customer_email' => 'required|email',
            'message' => 'nullable|string|max:10000',
            'attachments' => 'nullable|array|max:5',
            'attachments.*' => 'file|max:10240|mimes:jpg,jpeg,png,pdf,doc,docx,txt,zip',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        // Validate that either message or attachments are provided
        if (! $request->input('message') && ! $request->hasFile('attachments')) {
            return response()->json([
                'success' => false,
                'message' => 'Debe proporcionar un mensaje o archivos adjuntos.',
            ], 422);
        }

        $conversation = Conversation::with(['customer', 'status'])
            ->findOrFail($id);

        // Verify customer ownership
        if ($conversation->customer->email !== $request->input('customer_email')) {
            return response()->json([
                'success' => false,
                'message' => 'No autorizado para enviar mensajes en esta conversación.',
            ], 403);
        }

        // Check if conversation is closed
        if (! $conversation->status->is_open) {
            // Reopen conversation if customer replies
            $conversation->reopen();
        }

        // Handle file uploads
        $attachmentUrls = [];
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('helpdesk/attachments/'.$conversation->id, 'public');
                $attachmentUrls[] = [
                    'url' => \Storage::url($path),
                    'name' => $file->getClientOriginalName(),
                    'size' => $file->getSize(),
                    'type' => $file->getMimeType(),
                ];
            }
        }

        // Create message
        $message = ConversationItem::create([
            'conversation_id' => $conversation->id,
            'author_id' => $conversation->customer_id,
            'type' => 'message',
            'body' => $request->input('message') ?: '📎 Archivo(s) adjunto(s)',
            'is_internal' => false,
            'attachment_urls' => $attachmentUrls,
        ]);

        // Update conversation last message timestamp
        $conversation->update([
            'last_message_at' => now(),
        ]);

        // Update customer last seen
        $conversation->customer->updateLastSeen();

        // Load message relationships
        $message->load(['author', 'user']);

        // Broadcast message to manager panel and widget
        broadcast(new \App\Events\Helpdesk\MessageReceived($conversation, $message))->toOthers();

        return response()->json([
            'success' => true,
            'data' => [
                'message' => [
                    'id' => $message->id,
                    'type' => $message->type,
                    'body' => $message->body,
                    'is_from_customer' => $message->isFromCustomer(),
                    'is_from_agent' => $message->isFromAgent(),
                    'sender_name' => $message->sender_name,
                    'sender_avatar' => $message->sender_avatar,
                    'attachments' => $message->attachment_urls,
                    'created_at' => $message->created_at->toIso8601String(),
                ],
            ],
        ]);
    }

    /**
     * Get messages for a conversation
     *
     * GET /lc/api/conversations/{id}/messages
     */
    public function getMessages(Request $request, $id)
    {
        $conversation = Conversation::with(['customer', 'items.author', 'items.user'])
            ->findOrFail($id);

        // Verify customer ownership
        $customerEmail = $request->input('customer_email');
        if ($customerEmail && $conversation->customer->email !== $customerEmail) {
            return response()->json([
                'success' => false,
                'message' => 'No autorizado para ver estos mensajes.',
            ], 403);
        }

        // Get messages (excluding internal notes)
        $messages = $conversation->items()
            ->where('is_internal', false)
            ->with(['author', 'user'])
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'messages' => $messages->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'type' => $item->type,
                        'body' => $item->body,
                        'html_body' => $item->html_body,
                        'is_from_customer' => $item->isFromCustomer(),
                        'is_from_agent' => $item->isFromAgent(),
                        'sender_name' => $item->sender_name,
                        'sender_avatar' => $item->sender_avatar,
                        'attachments' => $item->attachment_urls,
                        'created_at' => $item->created_at->toIso8601String(),
                    ];
                }),
            ],
        ]);
    }

    /**
     * Reply to a conversation as an agent (requires authentication and permissions)
     *
     * POST /api/helpdesk/conversations/{id}/reply
     * Body: {
     *   message: string,
     *   is_internal?: boolean (default: false)
     * }
     */
    public function replyAsAgent(Request $request, $id)
    {
        // Check permission
        if (! auth()->check()) {
            return response()->json([
                'success' => false,
                'message' => 'Autenticación requerida.',
            ], 401);
        }

        // Verify user has permission to manage conversations
        if (! auth()->user()->can('manager.helpdesk.conversations.index')) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permisos para responder conversaciones.',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'message' => 'required|string|max:10000',
            'is_internal' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $conversation = Conversation::with(['customer', 'status'])
            ->findOrFail($id);

        // Auto-assign conversation to the replying agent if not assigned
        if (! $conversation->assignee_id) {
            $conversation->assignTo(auth()->id());
        }

        // Check if this is the first response
        $isFirstResponse = ! $conversation->first_response_at;

        // Create message
        $message = ConversationItem::create([
            'conversation_id' => $conversation->id,
            'user_id' => auth()->id(),
            'type' => 'message',
            'body' => $request->input('message'),
            'is_internal' => $request->input('is_internal', false),
        ]);

        // Update conversation timestamps
        $updateData = ['last_message_at' => now()];

        if ($isFirstResponse) {
            $updateData['first_response_at'] = now();
        }

        $conversation->update($updateData);

        // Load message relationships
        $message->load(['author', 'user']);

        // Broadcast message to widget and other agents
        broadcast(new \App\Events\Helpdesk\MessageReceived($conversation, $message))->toOthers();

        return response()->json([
            'success' => true,
            'data' => [
                'message' => [
                    'id' => $message->id,
                    'type' => $message->type,
                    'body' => $message->body,
                    'is_from_customer' => $message->isFromCustomer(),
                    'is_from_agent' => $message->isFromAgent(),
                    'is_internal' => $message->is_internal,
                    'sender_name' => $message->sender_name,
                    'sender_avatar' => $message->sender_avatar,
                    'created_at' => $message->created_at->toIso8601String(),
                ],
            ],
        ]);
    }

    /**
     * Close a conversation from the widget
     *
     * POST /lc/api/conversations/{id}/close
     * Body: { customer_email: string }
     */
    public function close(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'customer_email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $conversation = Conversation::with(['customer', 'status'])
            ->findOrFail($id);

        // Verify customer ownership
        if ($conversation->customer->email !== $request->input('customer_email')) {
            return response()->json([
                'success' => false,
                'message' => 'No autorizado para cerrar esta conversación.',
            ], 403);
        }

        // Close the conversation
        $conversation->close();

        // Broadcast conversation closed event
        broadcast(new \App\Events\Helpdesk\ConversationUpdated($conversation))->toOthers();

        return response()->json([
            'success' => true,
            'message' => 'Conversación cerrada exitosamente.',
            'data' => [
                'conversation' => [
                    'id' => $conversation->id,
                    'status' => [
                        'id' => $conversation->status->id,
                        'name' => $conversation->status->name,
                        'is_open' => $conversation->status->is_open,
                    ],
                ],
            ],
        ]);
    }
}
