<?php

namespace Botble\Menu\Providers;

use Botble\Base\Traits\LoadAndPublishDataTrait;
use Botble\Menu\Models\Menu as MenuModel;
use Botble\Menu\Models\MenuLocation;
use Botble\Menu\Models\MenuNode;
use Botble\Menu\Repositories\Caches\MenuCacheDecorator;
use Botble\Menu\Repositories\Caches\MenuLocationCacheDecorator;
use Botble\Menu\Repositories\Caches\MenuNodeCacheDecorator;
use Botble\Menu\Repositories\Eloquent\MenuLocationRepository;
use Botble\Menu\Repositories\Eloquent\MenuNodeRepository;
use Botble\Menu\Repositories\Eloquent\MenuRepository;
use Botble\Menu\Repositories\Interfaces\MenuInterface;
use Botble\Menu\Repositories\Interfaces\MenuLocationInterface;
use Botble\Menu\Repositories\Interfaces\MenuNodeInterface;
use Illuminate\Routing\Events\RouteMatched;
use Illuminate\Support\ServiceProvider;

class MenuServiceProvider extends ServiceProvider
{
    use LoadAndPublishDataTrait;

    public function register(): void
    {
        $this->setNamespace('packages/menu')
            ->loadHelpers();
    }

    public function boot(): void
    {
        $this->app->bind(MenuInterface::class, function () {
            return new MenuCacheDecorator(
                new MenuRepository(new MenuModel())
            );
        });

        $this->app->bind(MenuNodeInterface::class, function () {
            return new MenuNodeCacheDecorator(
                new MenuNodeRepository(new MenuNode())
            );
        });

        $this->app->bind(MenuLocationInterface::class, function () {
            return new MenuLocationCacheDecorator(
                new MenuLocationRepository(new MenuLocation())
            );
        });

        $this
            ->loadAndPublishConfigurations(['permissions', 'general'])
            ->loadRoutes()
            ->loadAndPublishViews()
            ->loadAndPublishTranslations()
            ->loadMigrations()
            ->publishAssets();

        $this->app['events']->listen(RouteMatched::class, function () {

            if (function_exists('admin_bar')) {
                admin_bar()->registerLink(trans('packages/menu::menu.name'), route('menus.index'), 'appearance', 'menus.index');
            }
        });

        $this->app->register(EventServiceProvider::class);
        $this->app->register(CommandServiceProvider::class);
    }
}
