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
        
        // Automatically set file properties if file is uploaded
        if (!empty($data['file_name'])) {
            $this->setFileProperties($data);
        }
        
        return $data;
    }
    
    protected function setFileProperties(array &$data): void
    {
        if (empty($data['file_name'])) {
            return;
        }
        
        $filePath = $data['file_name'];
        
        // Filament FileUpload stores files relative to the disk root
        // For visibility='private', it's storage/app/
        // For visibility='public', it's storage/app/public/
        
        // Check different possible locations
        $fullPath = null;
        $finalPath = $filePath;
        
        // Try local disk (private storage)
        if (Storage::disk('local')->exists($filePath)) {
            $fullPath = Storage::disk('local')->path($filePath);
            $finalPath = $filePath;
        } 
        // Try public disk
        elseif (Storage::disk('public')->exists($filePath)) {
            $fullPath = Storage::disk('public')->path($filePath);
            $finalPath = $filePath;
        }
        // Try with full path from storage/app/
        elseif (Storage::disk('local')->exists(ltrim($filePath, '/'))) {
            $finalPath = ltrim($filePath, '/');
            $fullPath = Storage::disk('local')->path($finalPath);
            $data['file_name'] = $finalPath;
        }
        else {
            return; // File not found, skip
        }
        
        if (!$fullPath || !file_exists($fullPath)) {
            return;
        }
        
        // Get file extension and determine file type
        $extension = strtolower(pathinfo($finalPath, PATHINFO_EXTENSION));
        $data['file_type'] = $extension;
        
        // Get file size
        $data['file_size'] = filesize($fullPath);
        
        // Generate download URL - use the file path as stored in database
        $data['download_url'] = $finalPath;
    }
    
    protected function afterCreate(): void
    {
        // After record is created, update file properties if needed
        $book = $this->record;
        if ($book->file_name && (empty($book->file_type) || empty($book->file_size))) {
            $data = ['file_name' => $book->file_name];
            $this->setFileProperties($data);
            
            if (isset($data['file_type']) || isset($data['file_size']) || isset($data['download_url'])) {
                $updateData = [];
                if (isset($data['file_type'])) $updateData['file_type'] = $data['file_type'];
                if (isset($data['file_size'])) $updateData['file_size'] = $data['file_size'];
                if (isset($data['download_url'])) $updateData['download_url'] = $data['download_url'];
                
                if (!empty($updateData)) {
                    $book->update($updateData);
                }
            }
        }
    }
}

