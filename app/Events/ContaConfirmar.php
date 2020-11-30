<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ContaConfirmar implements ShouldBroadcastNow {
    use Dispatchable, InteractsWithSockets, SerializesModels;

    private $user_id;
    private $contagem;

    /**
     * Create a new event instance.
     *
     * @param int $user_id
     * @param int $contagem
     */
    public function __construct(int $user_id, int $contagem) {
        $this->user_id = $user_id;
        $this->contagem = $contagem;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn() {
        return new PrivateChannel('conta.' . $this->user_id);
    }

    public function broadcastWith() {
        return ['quantidade' => $this->contagem];
    }

    public function broadcastAs() {
        return 'ContaConfirmar';
    }
}
