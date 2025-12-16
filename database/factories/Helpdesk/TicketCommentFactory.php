<?php

namespace Database\Factories\Helpdesk;

use App\Models\Helpdesk\Ticket;
use App\Models\Helpdesk\TicketComment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Helpdesk\TicketComment>
 */
class TicketCommentFactory extends Factory
{
    protected $model = TicketComment::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $isFromAgent = $this->faker->boolean(60);

        return [
            'ticket_id' => Ticket::factory(),
            'user_id' => $isFromAgent ? User::factory() : null,
            'author_id' => ! $isFromAgent ? function (array $attributes) {
                return Ticket::find($attributes['ticket_id'])->customer_id;
            } : null,
            'body' => $this->faker->paragraph(),
            'html_body' => null,
            'is_internal' => $this->faker->boolean(30),
            'attachment_urls' => null,
            'mentioned_user_ids' => null,
        ];
    }

    /**
     * Indicate that the comment is from an agent.
     */
    public function fromAgent(): self
    {
        return $this->state([
            'user_id' => User::factory(),
            'author_id' => null,
        ]);
    }

    /**
     * Indicate that the comment is from a customer.
     */
    public function fromCustomer(): self
    {
        return $this->state([
            'user_id' => null,
        ]);
    }

    /**
     * Indicate that the comment is internal.
     */
    public function internal(): self
    {
        return $this->state([
            'is_internal' => true,
        ]);
    }

    /**
     * Indicate that the comment is external.
     */
    public function external(): self
    {
        return $this->state([
            'is_internal' => false,
        ]);
    }

    /**
     * Indicate that the comment has attachments.
     */
    public function withAttachments(int $count = 1): self
    {
        return $this->state([
            'attachment_urls' => array_map(
                fn () => $this->faker->url().'/'.$this->faker->word().'.pdf',
                range(1, $count)
            ),
        ]);
    }

    /**
     * Indicate that the comment has mentioned users.
     */
    public function withMentions(int $count = 1): self
    {
        return $this->state([
            'mentioned_user_ids' => array_map(
                fn () => User::factory()->create()->id,
                range(1, $count)
            ),
        ]);
    }
}
