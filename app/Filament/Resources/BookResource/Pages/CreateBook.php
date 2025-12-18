<?php

namespace App\Filament\Resources\BookResource\Pages;

use App\Filament\Resources\BookResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Storage;

class CreateBook extends CreateRecord
{
    protected static string $resource = BookResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = auth()->id();
        $data['updated_by'] = auth()->id();
        
        // Don't set file properties here - file might still be in livewire-tmp
        // We'll set them in afterCreate() after Filament moves the file to final location
        
        // Remove any livewire-tmp paths from data to prevent errors
        // Filament will handle moving files from temp location
        if (isset($data['image_url']) && strpos($data['image_url'], 'livewire-tmp') !== false) {
            // Keep the value, Filament will process it
            // But don't try to access it yet
        }
        
        return $data;
    }
    
    protected function setFileProperties(array &$data): void
    {
        if (empty($data['file_name'])) {
            return;
        }
        
        $filePath = $data['file_name'];
        
        // Skip if file is still in livewire-tmp (temporary upload location)
        if (strpos($filePath, 'livewire-tmp') !== false) {
            return;
        }
        
        // Filament FileUpload stores files relative to the disk root
        // For visibility='private', it's storage/app/
        // For visibility='public', it's storage/app/public/
        
        $fileSize = null;
        $fileType = null;
        
        // Try local disk (private storage)
        if (Storage::disk('local')->exists($filePath)) {
            try {
                $fileSize = Storage::disk('local')->size($filePath);
                $fileType = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
            } catch (\Exception $e) {
                // File might not be accessible yet, skip
                return;
            }
        } 
        // Try public disk
        elseif (Storage::disk('public')->exists($filePath)) {
            try {
                $fileSize = Storage::disk('public')->size($filePath);
                $fileType = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
            } catch (\Exception $e) {
                // File might not be accessible yet, skip
                return;
            }
        }
        // Try with full path from storage/app/
        elseif (Storage::disk('local')->exists(ltrim($filePath, '/'))) {
            $finalPath = ltrim($filePath, '/');
            try {
                $fileSize = Storage::disk('local')->size($finalPath);
                $fileType = strtolower(pathinfo($finalPath, PATHINFO_EXTENSION));
                $data['file_name'] = $finalPath;
            } catch (\Exception $e) {
                // File might not be accessible yet, skip
                return;
            }
        }
        else {
            // File not found, skip
            return;
        }
        
        // Set properties if we successfully retrieved them
        if ($fileType) {
            $data['file_type'] = $fileType;
        }
        if ($fileSize !== null) {
            $data['file_size'] = $fileSize;
        }
        if (!empty($filePath)) {
            $data['download_url'] = $filePath;
        }
    }
    
    protected function afterCreate(): void
    {
        // After record is created, update file properties ONLY for book files (file_name)
        // At this point, Filament has moved the file from livewire-tmp to final location
        // Note: We skip file_size, file_type for images (image_url) - they're not needed
        $book = $this->record->fresh();
        if ($book->file_name) {
            // Only process file properties if it's not a temporary file
            if (strpos($book->file_name, 'livewire-tmp') === false) {
                $data = ['file_name' => $book->file_name];
                $this->setFileProperties($data);
                
                $updateData = [];
                if (isset($data['file_type']) && !empty($data['file_type'])) {
                    $updateData['file_type'] = $data['file_type'];
                }
                if (isset($data['file_size']) && !empty($data['file_size'])) {
                    $updateData['file_size'] = $data['file_size'];
                }
                if (isset($data['download_url']) && !empty($data['download_url'])) {
                    $updateData['download_url'] = $data['download_url'];
                }
                
                if (!empty($updateData)) {
                    $book->update($updateData);
                }
            }
        }
    }
}

