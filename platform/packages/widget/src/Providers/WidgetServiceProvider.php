<?php

namespace Botble\Widget\Providers;

use BaseHelper;
use Botble\Base\Traits\LoadAndPublishDataTrait;
use Botble\Widget\Factories\WidgetFactory;
use Botble\Widget\Models\Widget;
use Botble\Widget\Repositories\Caches\WidgetCacheDecorator;
use Botble\Widget\Repositories\Eloquent\WidgetRepository;
use Botble\Widget\Repositories\Interfaces\WidgetInterface;
use Botble\Widget\WidgetGroupCollection;
use Botble\Widget\Widgets\Text;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Routing\Events\RouteMatched;
use Illuminate\Support\ServiceProvider;
use Theme;
use WidgetGroup;

class WidgetServiceProvider extends ServiceProvider
{
    use LoadAndPublishDataTrait;

    public function register(): void
    {
        $this->app->bind(WidgetInterface::class, function () {
            return new WidgetCacheDecorator(new WidgetRepository(new Widget()));
        });

        $this->app->bind('botble.widget', function (Application $app) {
            return new WidgetFactory($app);
        });

        $this->app->singleton('botble.widget-group-collection', function (Application $app) {
            return new WidgetGroupCollection($app);
        });

        $this->setNamespace('packages/widget')
            ->loadHelpers();
    }

    public function boot(): void
    {
        $this
            ->loadAndPublishConfigurations(['permissions'])
            ->loadRoutes()
            ->loadMigrations()
            ->loadAndPublishViews()
            ->loadAndPublishTranslations()
            ->publishAssets();

        $this->app->booted(function () {
            WidgetGroup::setGroup([
                'id' => 'primary_sidebar',
                'name' => trans('packages/widget::widget.primary_sidebar_name'),
                'description' => trans('packages/widget::widget.primary_sidebar_description'),
            ]);

            register_widget(Text::class);

            $widgetPath = theme_path(Theme::getThemeName() . '/widgets');
            $widgets = BaseHelper::scanFolder($widgetPath);
            if (! empty($widgets) && is_array($widgets)) {
                foreach ($widgets as $widget) {
                    $registration = $widgetPath . '/' . $widget . '/registration.php';
                    if ($this->app['files']->exists($registration)) {
                        $this->app['files']->requireOnce($registration);
                    }
                }
            }
        });

        $this->app['events']->listen(RouteMatched::class, function () {

            if (function_exists('admin_bar')) {
                admin_bar()->registerLink(trans('packages/widget::widget.name'), route('widgets.index'), 'appearance', 'menus.index');
            }
        });
    }
}
