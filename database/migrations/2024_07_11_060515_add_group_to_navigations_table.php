<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('navigations', function (Blueprint $table) {
            $table->string('group')->nullable()->index()->after('ugly_url');
        });

        \App\Models\Navigation::create([
            'name' => 'Dashboard',
            'icon' => 'HomeIcon',
            'href' => '/-/dashboard',
            'order' => 0,
            'authentication_required' => true,
        ]);
        $project = \App\Models\Navigation::create([
            'name' => 'Projects',
            'icon' => 'ClipboardIcon',
            'href' => '/-/projects',
            'order' => 1,
            'authentication_required' => true,
        ]);
        $project->conditions()->create([
            'parameter' => 'feature:projects',
            'comparator' => 'EQUALS',
            'value' => '1',
        ]);

        $email = \App\Models\Navigation::create([
            'name' => 'Email',
            'icon' => 'EnvelopeOpenIcon',
            'href' => '/-/postal',
            'order' => 9,
            'authentication_required' => true,
            'group' => 'communication',
        ]);
        $email->conditions()->create([
            'parameter' => 'feature:communication.email',
            'comparator' => 'EQUALS',
            'value' => '1',
        ]);

        $messaging = \App\Models\Navigation::create([
            'name' => 'Messaging',
            'icon' => 'ChatBubbleLeftRightIcon',
            'href' => '/-/inbox',
            'order' => 10,
            'authentication_required' => true,
            'group' => 'communication',
        ]);
        $messaging->conditions()->create([
            'parameter' => 'feature:communication.messaging',
            'comparator' => 'EQUALS',
            'value' => '1',
        ]);

        /** @var \App\Models\Navigation $crudNav */
        $crudNav = \App\Models\Navigation::create([
            'name' => 'CRUD',
            'icon' => 'CircleStackIcon',
            'href' => '/-/manage',
            'order' => 6,
            'group' => 'core',
            'authentication_required' => true,
        ]);
        $crudNav->conditions()->create([
            'parameter' => 'feature:automatic.crud',
            'comparator' => 'EQUALS',
            'value' => '1',
        ]);

        $models = \App\Services\Code::instancesOf(\App\Models\Crud::class);

        $crudClasses = array_values(array_filter(
            $models->getClasses(),
            fn ($class) => in_array(\App\Models\Crud::class, class_implements($class))
        ));

        asort($crudClasses);

        foreach ($crudClasses as $index => $class) {
            \App\Models\Navigation::create([
                'name' => \Illuminate\Support\Str::headline((new $class)->getTable()),
                'href' => '/-/manage/'.\Illuminate\Support\Str::slug(
                        \Illuminate\Support\Str::headline((new $class)->getTable())
                    ),
                'icon' => \Illuminate\Support\Str::studly(\Illuminate\Support\Str::headline(class_basename($class))).'Icon',
                'order' => $index,
                'authentication_required' => true,
                'parent_id' => $crudNav->id,
                'settings' => [
                    'title' => \Illuminate\Support\Str::headline(class_basename($class)),
                    'singular' => \Illuminate\Support\Str::studly(class_basename($class)),
                    'api_url' => route(((new $class)->getTable()).'.store'),
                ],
            ]);
        }

        \App\Models\Navigation::create([
            'name' => 'Tags',
            'icon' => 'TagIcon',
            'href' => '/-/tag-manager',
            'order' => 8,
            'group' => 'core',
            'authentication_required' => true,
        ]);
        \App\Models\Navigation::create([
            'name' => 'Settings',
            'icon' => 'Cog6ToothIcon',
            'href' => '/-/settings',
            'order' => 10,
            'authentication_required' => true,
            'group' => 'core',
        ]);

        $logic = \App\Models\Navigation::create([
            'name' => 'Logic',
            'icon' => 'VariableIcon',
            'href' => '/-/logic',
            'order' => 7,
            'authentication_required' => true,
            'group' => 'development',
        ]);

        $logic->conditions()->create([
            'parameter' => 'config:app.env',
            'comparator' => 'IN',
            'value' => 'dev,local',
        ]);

        \App\Models\Navigation::create([
            'name' => 'Login',
            'href' => '/login',
            'order' => 0,
            'authentication_required' => false,
        ]);

        $banking = \App\Models\Navigation::create([
            'name' => 'Banking',
            'icon' => 'WalletIcon',
            'href' => '/-/banking',
            'order' => 6,
            'authentication_required' => true,
            'group' => 'tools',
        ]);
        $banking->conditions()->create([
            'parameter' => 'feature:banking',
            'comparator' => 'EQUALS',
            'value' => '1',
        ]);
        $feeds = \App\Models\Navigation::create([
            'name' => 'Rss Feeds',
            'icon' => 'RssIcon',
            'href' => '/-/rss-feeds',
            'order' => 6,
            'authentication_required' => true,
            'group' => 'tools',
        ]);
        $feeds->conditions()->create([
            'parameter' => 'feature:feeds',
            'comparator' => 'EQUALS',
            'value' => '1',
        ]);

        $infrastructure = \App\Models\Navigation::create([
            'name' => 'Infrastructure',
            'icon' => 'ServerIcon',
            'href' => '/-/servers',
            'order' => 6,
            'authentication_required' => true,
            'group' => 'tools',
        ]);

        $infrastructure->conditions()->create([
            'parameter' => 'feature:App\\Features\\InfrastructureManagement',
            'comparator' => 'EQUALS',
            'value' => '1',
        ]);

        $fileManager = \App\Models\Navigation::create([
            'name' => 'File Manager',
            'icon' => 'ServerIcon',
            'href' => '/-/file-manager',
            'order' => 6,
            'authentication_required' => true,
            'group' => 'tools',
        ]);

        $fileManager->conditions()->create([
            'parameter' => 'feature:App\\Features\\FileManager',
            'comparator' => 'EQUALS',
            'value' => '1',
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('navigations', function (Blueprint $table) {
            $table->dropColumn('group');
        });
    }
};
