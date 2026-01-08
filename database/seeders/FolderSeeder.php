<?php

namespace Database\Seeders;

use App\Models\Folder;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class FolderSeeder extends Seeder
{
    public function run(): void
    {
        $adminId = User::first()?->id ?? 1;

        $folders = [
            [
                'name' => 'Общий отдел',
                'children' => [
                    ['name' => 'Приказы'],
                    ['name' => 'Инструкции'],
                    ['name' => 'Учредительные документы'],
                ]
            ],
            [
                'name' => 'Бухгалтерия',
                'children' => [
                    ['name' => 'Отчетность', 'children' => [
                        ['name' => '2024 год'],
                        ['name' => '2025 год'],
                    ]],
                    ['name' => 'Счета-фактуры'],
                    ['name' => 'Акты сверки'],
                ]
            ],
            [
                'name' => 'Кадры',
                'children' => [
                    ['name' => 'Трудовые договоры'],
                    ['name' => 'Личные карточки'],
                ]
            ],
            [
                'name' => 'Архив',
                'status' => 0,
            ],
        ];

        foreach ($folders as $index => $data) {
            $this->createFolderRecursive($data, null, $index, $adminId);
        }
    }

    private function createFolderRecursive(array $data, $parentId = null, $index = 0, $userId = 1)
    {
        $folder = Folder::create([
            'name' => $data['name'],
            'slug' => Str::slug($data['name']) . '-' . Str::random(3),
            'parent_id' => $parentId,
            'order_index' => $index,
            'status' => $data['status'] ?? 1,
            'created_by' => $userId,
            'path' => $this->calculatePath($data['name'], $parentId),
        ]);

        if (isset($data['children'])) {
            foreach ($data['children'] as $childIndex => $childData) {
                $this->createFolderRecursive($childData, $folder->id, $childIndex, $userId);
            }
        }
    }

    private function calculatePath($name, $parentId)
    {
        if (!$parentId) {
            return $name;
        }
        $parent = Folder::find($parentId);
        return $parent->path . ' / ' . $name;
    }
}