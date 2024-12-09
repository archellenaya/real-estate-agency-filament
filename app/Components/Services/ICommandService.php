<?php

namespace App\Components\Services;
use Illuminate\Console\Command;

interface ICommandService
{
  public function sendEmailNotifications(?Command $command=null);
}