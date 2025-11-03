<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Enum\ConversationType;
use App\Models\ConversationType as ConversationTypeModel;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $conversationTypes = ConversationType::cases();
        foreach ($conversationTypes as $type) {
            ConversationTypeModel::updateOrCreate(
                ['name' => $type->value],
                [
                    'name' => $type->value
                ]
            );
        }
    }
}
