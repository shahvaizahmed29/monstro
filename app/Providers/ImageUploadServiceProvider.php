<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class ImageUploadServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        function uploadImage($userId, $img, $imgPath){
            $fileName = $userId . '_' . time() . '.' . $img->getClientOriginalExtension();
            $img->storeAs($imgPath, $fileName, 'public');
            return $fileName;
        }
    }

}
