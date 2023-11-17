<?php

namespace App\Services\External;

use Exception;
use GdImage;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

define('THUMBNAIL_THRESHOLD', 256);

class RemoteImageService
{
    /**
     * @throws Exception
     */
    public function storeRemoteImage(string $url): StoreRemoteImageReply
    {
        $attachment = $this->createAttachment($url);

        $thumbnail = null;

        if (
            $attachment->width > THUMBNAIL_THRESHOLD
            || $attachment->height > THUMBNAIL_THRESHOLD
        ) {
            $thumbnail = $this->createThumbnail($attachment->image, $attachment->uuid);
        }

        imagedestroy($attachment->image);

        $attachment->image = null;

        return new StoreRemoteImageReply(
            $attachment,
            $thumbnail
        );
    }

    /**
     * @throws Exception
     */
    private function createAttachment(string $url): CreateAttachmentReply
    {
        $content = file_get_contents($url);
        if ($content === false) {
            throw new Exception('file_get_contents returned false');
        }

        $uuid = Str::uuid();

        $remoteImage = imagecreatefromstring($content);

        $imagePath = sprintf('%s/out.png', $uuid);

        ob_start();
        imagepng($remoteImage, null, 6, PNG_NO_FILTER);
        $imageContent = ob_get_clean();

        $imageFileSize = strlen($imageContent);

        Storage::put($imagePath, $imageContent);

        return new CreateAttachmentReply(
            $uuid,
            $imagePath,
            imagesx($remoteImage),
            imagesy($remoteImage),
            $imageFileSize,
            $remoteImage,
        );
    }

    private function createThumbnail(GdImage $image, string $uuid): CreateThumbnailReply
    {
        $thumbnailPath = sprintf('%s/thumbnail.png', $uuid);

        $imageWidth = imagesx($image);
        $imageHeight = imagesy($image);

        $thumbnailWidth = THUMBNAIL_THRESHOLD;
        $thumbnailHeight = THUMBNAIL_THRESHOLD;

        if ($imageWidth > $imageHeight) {
            $thumbnailHeight = round($imageHeight / $imageWidth * THUMBNAIL_THRESHOLD);
        } else {
            $thumbnailWidth = round($imageWidth / $imageHeight * THUMBNAIL_THRESHOLD);
        }

        $thumbnailImage = imagecreatetruecolor($thumbnailWidth, $thumbnailHeight);
        imagecopyresampled($thumbnailImage, $image, 0, 0, 0, 0, $thumbnailWidth, $thumbnailHeight, $imageWidth, $imageHeight);

        ob_start();
        imagepng($thumbnailImage, null, 6, PNG_NO_FILTER);
        $thumbnailImageContent = ob_get_clean();

        $thumbnailFileSize = strlen($thumbnailImageContent);

        Storage::put($thumbnailPath, $thumbnailImageContent);

        imagedestroy($thumbnailImage);

        return new CreateThumbnailReply(
            $thumbnailPath,
            $thumbnailWidth,
            $thumbnailHeight,
            $thumbnailFileSize
        );
    }
}

class StoreRemoteImageReply
{
    public CreateAttachmentReply $attachment;
    public ?CreateThumbnailReply $thumbnail;

    public function __construct(
        CreateAttachmentReply $attachment,
        ?CreateThumbnailReply $thumbnail
    )
    {
        $this->attachment = $attachment;
        $this->thumbnail = $thumbnail;
    }
}

class CreateAttachmentReply
{
    public string $uuid;
    public string $path;
    public int $width;
    public int $height;
    public int $fileSize;
    public ?GdImage $image;

    public function __construct(
        string   $uuid,
        string   $path,
        int      $width,
        int      $height,
        int      $fileSize,
        ?GdImage $image,
    )
    {
        $this->uuid = $uuid;
        $this->path = $path;
        $this->width = $width;
        $this->height = $height;
        $this->fileSize = $fileSize;
        $this->image = $image;
    }
}

class CreateThumbnailReply
{
    public string $path;
    public int $width;
    public int $height;
    public int $fileSize;

    public function __construct(
        string $path,
        int    $width,
        int    $height,
        int    $fileSize,
    )
    {
        $this->path = $path;
        $this->width = $width;
        $this->height = $height;
        $this->fileSize = $fileSize;
    }
}
