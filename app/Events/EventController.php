<?php

namespace App\Events;

use App\Entities\Asset;
use App\Repositories\AssetRepository;
use App\Repositories\ExamRepository;
use App\Repositories\QuestionRepository;
use Illuminate\Support\Facades\Storage;

class EventController
{
    //

    private $examRepository;
    private $questionRepository;
    private $assetRepository;

    public function __construct(
        QuestionRepository $questionRepository,
        ExamRepository $examRepository,
        AssetRepository $assetRepository
    ) {
        $this->examRepository            = $examRepository;
        $this->questionRepository        = $questionRepository;
        $this->assetRepository           = $assetRepository;
    }

    public function attachQuestion($examId, $questionId)
    {
        return $this->examRepository->attachQuestion($examId, $questionId);;
    }

    public function fileUpload($file, $data, $path = false)
    {
        if (Storage::disk('local')->exists($path)) {
            Storage::disk('local')->delete($path);
        }

        $path = Storage::disk('local')->put($path ?: Asset::ASSET_PATH, $file);

        $attributes = [
                'owner_id' => $data['owner_id'],
                'name' => $data['name'],
                'category_id' => $data['category_id'],
                'tag' => $data['tag'],
                'type' => $data['type'],
                'url' => $path,
        ];

        $asset = $this->assetRepository->add($attributes);

        return $asset;
    }

    public function autoMark($est)
    {
        if (Storage::disk('local')->exists($path)) {
            Storage::disk('local')->delete($path);
        }

        $path = Storage::disk('local')->put($path ?: Asset::ASSET_PATH, $file);

        $attributes = [
            'owner_id' => $data['owner_id'],
            'name' => $data['name'],
            'category_id' => $data['category_id'],
            'tag' => $data['tag'],
            'type' => $data['type'],
            'url' => $path,
        ];

        $asset = $this->assetRepository->add($attributes);

        return $asset;
    }
}
