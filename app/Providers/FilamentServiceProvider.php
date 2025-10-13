namespace App\Providers;

use Filament\Navigation\NavigationGroup;
use Filament\Navigation\NavigationItem;
use Filament\Facades\Filament;
use Illuminate\Support\ServiceProvider;

class FilamentServiceProvider extends ServiceProvider
{
    public function boot()
    {
        Filament::serving(function () {
            Filament::registerNavigationGroups([
                NavigationGroup::make('Dashboard')->sort(1),
                NavigationGroup::make('Private Sellers')->sort(2),
                NavigationGroup::make('Customers')->sort(3),
                NavigationGroup::make('Admin')->sort(4),
                // NavigationGroup::make('Members')->sort(5),
            ]);
        });
    }
}
