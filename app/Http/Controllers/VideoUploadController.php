<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Pion\Laravel\ChunkUpload\Exceptions\UploadMissingFileException;
use Pion\Laravel\ChunkUpload\Handler\HandlerFactory;
use Pion\Laravel\ChunkUpload\Receiver\FileReceiver;
use Pion\Laravel\ChunkUpload\Save\ChunkSave;
use Pion\Laravel\ChunkUpload\Storage\ChunkStorage;
class VideoUploadController extends Controller
{
    public function upload(Request $request)
    {
        $receiver = new FileReceiver('file', $request, HandlerFactory::classFromRequest($request));
        if ($receiver->isUploaded() === false) {
            throw new UploadMissingFileException();
        }
        
        $save = $receiver->receive();
        if ($save->isFinished()) {
            $file = $save->getFile();
            $path = $file->store('video',['disk' => 'public']);
        }

        $handler = $save->handler();

        return response()->json([
            'success' => true,
            'message' => 'File uploaded successfully',
            "done" => $handler->getPercentageDone(),
        ]);
    }
}
