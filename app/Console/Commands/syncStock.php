<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Modules\Media\Helpers\FileHelper;
use Modules\Media\Models\MediaFile;

class syncStock extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stock:load';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Load the Stock Images in the Database';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {

        $files = glob(public_path() . "/uploads/stock/**/*", GLOB_BRACE);

        foreach ($files as $file) {

            $lastFolder = basename(dirname($file));
            $path = "stock/" . $lastFolder . "/" . basename($file);
            $fileObj = MediaFile::where("file_path", $path)->first();

            if (!$fileObj) {
                $fileInfo = pathinfo($file);

                $fileObj = new MediaFile();
                $fileObj->file_name = $fileInfo['filename'];
                $fileObj->file_path = $path;
                $fileObj->file_size = filesize($file);
                $fileObj->file_type = mime_content_type($file);
                $fileObj->file_extension = $fileInfo['extension'];
                if (FileHelper::checkMimeIsImage(mime_content_type($file))) {
                    list($width, $height) = getimagesize($file);
                    $fileObj->file_width = $width;
                    $fileObj->file_height = $height;
                }
                $fileObj->save();
            }
        }
    }
}
