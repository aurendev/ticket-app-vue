<?php

namespace App\Http\Controllers;

use App\Models\Queue;
use App\Models\Ticket;
use Carbon\Carbon;
use Illuminate\Http\Request;

class TicketController extends Controller
{
  public function index()
  {
    $queues = Queue::with('tickets')->get();

    $this->verificateDate($queues[0], 2);
    $this->verificateDate($queues[1], 3);

    return  Queue::with('tickets')->get();
    
  }

  public function verificateDate($queue, $minutes)
  {
    $queue->tickets->map(function($ticket, $index) use($minutes){
      $now = Carbon::now();
      $created = new Carbon($ticket->created_at);

      if($created->diffInMinutes($now) >= ($ticket->number_in_queue * $minutes) ){
        $ticket->delete();
      }
    });
  }

  public function store()
  {
    $numberInQueue = Ticket::whereQueueId( request('queue_id'))->get()->count();

    $ticket = Ticket::create([
      'queue_id' => (int) request('queue_id'),
      'name' => request('name'),
      'uuid' => request('uuid'),
      'number_in_queue' => $numberInQueue + 1
    ]);


    return $ticket;
  }

  public function destroy()
  {
    

    foreach (request('ids') as $ticketId) {
      $ticket = Ticket::whereId($ticketId)->first();
      $ticket->delete();
    }


    return request('ids');

  }
}
