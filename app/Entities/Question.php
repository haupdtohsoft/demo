<?php

namespace App\Entities;

use App\Traits\Entity\CategoryRelationship;
use App\MyModel;

class Question extends MyModel
{
    protected $model_class = Question::class;
}