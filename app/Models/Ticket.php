<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
  use HasFactory;

  protected $fillable = ['name', 'uuid','queue_id', 'number_in_queue'];

  public function queue()
  {
    return $this->belongsTo(Ticket::class);
  }
}
