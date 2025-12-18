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
        
        // Skip if file is still in livewire-tmp (temporary upload location)
        // Also skip if it looks like an image (cover image) - we don't need file_size for images
        if (strpos($filePath, 'livewire-tmp') !== false) {
            return;
        }
        
        // Skip image files - we only need file_size/file_type for book files (PDF, EPUB)
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        $imageExtensions = ['png', 'jpg', 'jpeg', 'gif', 'webp', 'svg'];
        if (in_array($extension, $imageExtensions)) {
            return; // Don't process file_size for images
        }
        
        // Filament FileUpload stores files relative to the disk root
        // Check different possible locations
        $fileSize = null;
        $fileType = null;
        
        // Try local disk (private storage)
        if (Storage::disk('local')->exists($filePath)) {
            try {
                $fileSize = @Storage::disk('local')->size($filePath); // Suppress errors
                $fileType = $extension ?: strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
            } catch (\Exception $e) {
                // Silently skip if file is not accessible
                return;
            }
        } 
        // Try public disk
        elseif (Storage::disk('public')->exists($filePath)) {
            try {
                $fileSize = @Storage::disk('public')->size($filePath); // Suppress errors
                $fileType = $extension ?: strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
            } catch (\Exception $e) {
                // Silently skip if file is not accessible
                return;
            }
        }
        // Try with full path from storage/app/
        elseif (Storage::disk('local')->exists(ltrim($filePath, '/'))) {
            $finalPath = ltrim($filePath, '/');
            try {
                $fileSize = @Storage::disk('local')->size($finalPath); // Suppress errors
                $fileType = $extension ?: strtolower(pathinfo($finalPath, PATHINFO_EXTENSION));
                $data['file_name'] = $finalPath;
            } catch (\Exception $e) {
                // Silently skip if file is not accessible
                return;
            }
        }
        else {
            // File not found, skip silently
            return;
        }
        
        // Set properties if we successfully retrieved them
        if ($fileType) {
            $data['file_type'] = $fileType;
        }
        if ($fileSize !== null && $fileSize > 0) {
            $data['file_size'] = $fileSize;
        }
        if (!empty($filePath)) {
            $data['download_url'] = $filePath;
        }
    }
    
    protected function afterSave(): void
    {
        // After record is saved, update file properties if file was uploaded/changed
        // At this point, Filament has moved the file from livewire-tmp to final location
        $book = $this->record->fresh();
        if ($book->file_name) {
            // Check if file properties need to be updated
            $needsUpdate = empty($book->file_type) || empty($book->file_size) || empty($book->download_url);
            
            // Also check if file was just uploaded (might be in new location)
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

