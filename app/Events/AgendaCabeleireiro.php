<?php

namespace App\Events;

use App\Models\Horario;
use Carbon\Carbon;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AgendaCabeleireiro implements ShouldBroadcastNow {
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    private $horario;
    private $cabeleireiro_id;

    public function __construct(int $cabeleireiro_id, $data) {
        $formatada = Carbon::parse($data);
        $this->horario = Horario::where('cabeleireiro_id', $cabeleireiro_id)
            ->with('cliente')
            ->with('cabeleireiro')
            ->where('data', $formatada->format('Y-m-d'))
            ->get();
        $this->cabeleireiro_id = $cabeleireiro_id;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn() {
        return new PrivateChannel('agenda.' . $this->cabeleireiro_id);
    }

    public function broadcastWith() {
        return ['horarios' => $this->horario];
    }

    public function broadcastAs() {
        return 'AgendaCabeleireiro';
    }
}
