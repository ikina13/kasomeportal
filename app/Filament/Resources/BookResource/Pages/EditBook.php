<?php

namespace App\Filament\Resources\BookResource\Pages;

use App\Filament\Resources\BookResource;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Storage;
use App\Models\Book;

class EditBook extends EditRecord
{
    protected static string $resource = BookResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['updated_by'] = auth()->id();
        
        // Get the current record to check if file_name changed
        $record = $this->record;
        $oldFileName = $record->file_name;
        $newFileName = $data['file_name'] ?? null;
        
        // If file was uploaded/changed, update file properties
        if (!empty($newFileName) && $newFileName !== $oldFileName) {
            $this->setFileProperties($data);
        } elseif (!empty($oldFileName) && $newFileName === $oldFileName) {
            // File unchanged - but ensure file properties are set if they're missing
            if (empty($data['file_type']) && !empty($oldFileName)) {
                $extension = strtolower(pathinfo($oldFileName, PATHINFO_EXTENSION));
                $data['file_type'] = $extension;
            }
            if (empty($data['file_size']) && !empty($oldFileName)) {
                $filePath = $oldFileName;
                if (Storage::disk('local')->exists($filePath)) {
                    $data['file_size'] = Storage::disk('local')->size($filePath);
                } elseif (Storage::disk('public')->exists($filePath)) {
                    $data['file_size'] = Storage::disk('public')->size($filePath);
                }
            }
            if (empty($data['download_url']) && !empty($oldFileName)) {
                $data['download_url'] = $oldFileName;
            }
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
        
        // Generate download URL
        $data['download_url'] = $finalPath;
    }
    
    protected function afterSave(): void
    {
        // After record is saved, update file properties if file was uploaded
        $book = $this->record->fresh();
        if ($book->file_name && (empty($book->file_type) || empty($book->file_size) || empty($book->download_url))) {
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

