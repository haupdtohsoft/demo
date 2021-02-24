<?php

namespace App\Events;


class EventHandler
{

    public function subscribe($events)
    {
        $events->listen('exam.attachQuestion', 'App\Events\EventController@attachQuestion');
        $events->listen('asset.fileUpload', 'App\Events\EventController@fileUpload');
        $events->listen('exam.mark', 'App\Events\EventController@autoMark');
    }
}
