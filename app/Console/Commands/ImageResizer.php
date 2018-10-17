<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Constraint;
use Intervention\Image\Facades\Image;

class ImageResizer extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'images:resize';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Resize Images';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function handle() {
        $storage = Storage::disk( 'local' );
        $files = $storage->files( 'public/photos' );

        foreach ( $files as $file ) {
            $this->thumb( $storage, $file );
            $this->resize( $storage, $file );
        }
    }

    /**
     * @param Filesystem $storage
     * @param            $path
     *
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    protected function resize( Filesystem $storage, $path ) {
        $width = 1200;

        $image = $storage->get( $path );

        $image = Image::make( $image )
            ->resize(
                $width,
                null,
                function ( Constraint $constraint ) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                }
            )
            ->encode( 'jpg', 70 );

        $pathArray = explode( '/', $path );
        $fileName = array_pop( $pathArray );


        $resizedPath = '/public/resized/' . $fileName;
        $storage->put( $resizedPath, $image );

        $this->info( 'Generated: ' . $resizedPath );
    }

    /**
     * @param Filesystem $storage
     * @param            $path
     *
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    protected function thumb( Filesystem $storage, $path ) {
        $width = 400;
        $height = 400;

        $image = $storage->get( $path );

        $image = Image::make( $image )
            ->fit(
                $width,
                $height,
                function ( Constraint $constraint ) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                }
            )
            ->encode( 'jpg', 70 );

        $pathArray = explode( '/', $path );
        $fileName = array_pop( $pathArray );


        $resizedPath = '/public/thumbs/' . $fileName;
        $storage->put( $resizedPath, $image );

        $this->info( 'Generated: ' . $resizedPath );
    }
}
