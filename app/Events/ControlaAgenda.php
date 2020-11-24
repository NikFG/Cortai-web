<?php

namespace App\Events;

use App\Models\Horario;
use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ControlaAgenda implements ShouldBroadcastNow {
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $horario;

    /**
     * Create a new event instance.
     *
     * @param Horario $horario
     */
    public function __construct(Horario $horario) {
        $this->horario = $horario;

    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn() {
        return new Channel('agenda.' . $this->horario->id);
    }

    public function broadcastWith() {
        return ['horario' => $this->horario, 'cabeleireiro' => $this->horario->cabeleireiro];
    }

    public function broadcastAs() {
        return 'ControlaAgenda';
    }
}
