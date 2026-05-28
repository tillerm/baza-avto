<?php

use App\Http\Controllers\AboutController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\CarController;
use App\Http\Controllers\CarPhotoController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\CountryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\EngineController;
use App\Http\Controllers\EquipmentController;
use App\Http\Controllers\ModelController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\RequestController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\FaqController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ManagerController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\PublicNewsController;
use App\Http\Controllers\PublicTestimonialController;
use App\Http\Controllers\TeamMemberController;
use App\Http\Controllers\TestimonialController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/home', [HomeController::class, 'index'])->name('home');

Route::get('/', function () {
    return redirect()->route('home');
});

Route::get('/about', [AboutController::class, 'index'])->name('about');
Route::get('/faq', [FaqController::class, 'index'])->name('faq');
Route::get('/news', [PublicNewsController::class, 'index'])->name('news.index');
Route::get('/news/{id}', [PublicNewsController::class, 'show'])->name('news.show');
Route::get('/testimonials', [PublicTestimonialController::class, 'index'])->name('testimonials.index');

Route::prefix('/catalog')->group(function () {
    Route::get('/', [CatalogController::class, 'index'])->name('catalog.index');
    Route::get('/calculator', [CatalogController::class, 'calculator'])->name('catalog.calculator');
    Route::post('/calculator/delivery-estimate', [CatalogController::class, 'calculateDeliveryEstimate'])->name('catalog.calculator.delivery-estimate');
    Route::get('/{id}', [CatalogController::class, 'show'])->name('catalog.show');
    Route::post('/pin/{id}', [CatalogController::class, 'pin'])->middleware(['auth', 'verified'])->name('catalog.pin');
});

Route::post('/requests', [RequestController::class, 'store'])->name('requests.store');
Route::post('/leads/calculator', [LeadController::class, 'storeCalculator'])->name('leads.calculator.store');
Route::post('/leads/delivery-calculator', [LeadController::class, 'storeDeliveryCalculator'])->name('leads.delivery-calculator.store');

Route::prefix('/crm')->middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified'])->group(function () {
    Route::get('/', function () {
        $user = auth()->user();
        $userId = $user?->id;

        $carsQuery = \App\Models\Car::query();
        if ($user && $user->isManager()) {
            $carsQuery->where(function ($ownedQuery) use ($userId) {
                $ownedQuery->where('manager_id', $userId)
                    ->orWhereHas('supply', function ($supplyQuery) use ($userId) {
                        $supplyQuery->where('user_id', $userId);
                    });
            });
        }

        $stats = [
            'carsTotal' => (clone $carsQuery)->count(),
            'carsSelling' => (clone $carsQuery)->where('status', \App\Enums\CarStatus::SELLING->name)->count(),
            'carsPresent' => (clone $carsQuery)->where('status', \App\Enums\CarStatus::PRESENT->name)->count(),
            'carsSold' => (clone $carsQuery)->where('status', \App\Enums\CarStatus::SOLD->name)->count(),
            'ordersTotal' => \App\Models\Order::count(),
            'requestsTotal' => \App\Models\Request::count(),
            'brandsTotal' => \App\Models\Brand::count(),
            'modelsTotal' => \App\Models\Model::count(),
            'enginesTotal' => \App\Models\Engine::count(),
            'equipmentsTotal' => \App\Models\Equipment::count(),
        ];

        return Inertia::render('CRM/Dashboard', compact('stats'));
    })->name('crm');

    Route::prefix('/countries')->group(function () {
        Route::get('/', [CountryController::class, 'index'])->name('crm.countries.index');
        Route::post('/', [CountryController::class, 'store'])->name('crm.countries.store');
        Route::get('/{id}', [CountryController::class, 'show'])->name('crm.countries.show');
        Route::put('/{id}', [CountryController::class, 'update'])->name('crm.countries.update');
        Route::delete('/{id}', [CountryController::class, 'destroy'])->name('crm.countries.destroy');
    });

    Route::prefix('/brands')->group(function () {
        Route::get('/', [BrandController::class, 'index'])->name('crm.brands.index');
        Route::post('/', [BrandController::class, 'store'])->name('crm.brands.store');
        Route::get('/{id}', [BrandController::class, 'show'])->name('crm.brands.show');
        Route::put('/{id}', [BrandController::class, 'update'])->name('crm.brands.update');
        Route::delete('/{id}', [BrandController::class, 'destroy'])->name('crm.brands.destroy');
    });

    Route::prefix('/models')->group(function () {
        Route::get('/', [ModelController::class, 'index'])->name('crm.models.index');
        Route::post('/', [ModelController::class, 'store'])->name('crm.models.store');
        Route::get('/{id}', [ModelController::class, 'show'])->name('crm.models.show');
        Route::put('/{id}', [ModelController::class, 'update'])->name('crm.models.update');
        Route::delete('/{id}', [ModelController::class, 'destroy'])->name('crm.models.destroy');
    });

    Route::prefix('/engines')->group(function () {
        Route::get('/', [EngineController::class, 'index'])->name('crm.engines.index');
        Route::get('/create', [EngineController::class, 'create'])->name('crm.engines.create');
        Route::post('/', [EngineController::class, 'store'])->name('crm.engines.store');
        Route::get('/{id}', [EngineController::class, 'show'])->name('crm.engines.show');
        Route::get('/{id}/edit', [EngineController::class, 'edit'])->name('crm.engines.edit');
        Route::put('/{id}', [EngineController::class, 'update'])->name('crm.engines.update');
        Route::delete('/{id}', [EngineController::class, 'destroy'])->name('crm.engines.destroy');
    });

    Route::prefix('/equipments')->group(function () {
        Route::get('/', [EquipmentController::class, 'index'])->name('crm.equipments.index');
        Route::get('/create', [EquipmentController::class, 'create'])->name('crm.equipments.create');
        Route::post('/', [EquipmentController::class, 'store'])->name('crm.equipments.store');
        Route::get('/{id}', [EquipmentController::class, 'show'])->name('crm.equipments.show');
        Route::get('/{id}/edit', [EquipmentController::class, 'edit'])->name('crm.equipments.edit');
        Route::put('/{id}', [EquipmentController::class, 'update'])->name('crm.equipments.update');
        Route::delete('/{id}', [EquipmentController::class, 'destroy'])->name('crm.equipments.destroy');
    });

    Route::prefix('/cars')->group(function () {
        Route::get('/', [CarController::class, 'index'])->name('crm.cars.index');
        Route::get('/create', function() {
            $brands = \App\Models\Brand::orderBy('name')->get();
            $models = \App\Models\Model::orderBy('name')->get();
            $engines = \App\Models\Engine::orderBy('name')->get();
            $equipments = \App\Models\Equipment::orderBy('name')->get();

            $defaultCountryId = \App\Models\Country::orderBy('name')->value('id');
            $fuels = \App\Enums\Fuel::array();
            $types = \App\Enums\Type::array();
            $bodies = \App\Enums\Body::array();

            $wizard = [
                'createdBrandId' => session('wizard_created_brand_id'),
                'createdModelId' => session('wizard_created_model_id'),
                'createdEngineId' => session('wizard_created_engine_id'),
                'createdEquipmentId' => session('wizard_created_equipment_id'),
                'createdCarId' => session('wizard_created_car_id'),
            ];

            return Inertia::render('CRM/Cars/Create', compact(
                'brands',
                'models',
                'engines',
                'equipments',
                'defaultCountryId',
                'fuels',
                'types',
                'bodies',
                'wizard',
            ));
        })->name('crm.cars.create');
        Route::post('/', [CarController::class, 'store'])->name('crm.cars.store');
        Route::get('/{id}', [CarController::class, 'show'])->name('crm.cars.show');
        Route::get('/{id}/edit', [CarController::class, 'edit'])->name('crm.cars.edit');
        Route::put('/{id}', [CarController::class, 'update'])->name('crm.cars.update');
        Route::put('/{id}/sold', [CarController::class, 'markSold'])->name('crm.cars.sold');
        Route::get('/download/report', [CarController::class, 'downloadSellingCarsReport'])->name('crm.cars.download');
    });

    Route::prefix('/photos')->group(function () {
        Route::post('/', [CarPhotoController::class, 'store'])->name('crm.photos.store');
        Route::put('/{id}/primary', [CarPhotoController::class, 'makePrimary'])->name('crm.photos.primary');
        Route::delete('/{id}', [CarPhotoController::class, 'delete'])->name('crm.photos.destroy');
    });

    Route::prefix('/customers')->group(function () {
        Route::get('/', [CustomerController::class, 'index'])->name('crm.customers.index');
        Route::get('/create', [CustomerController::class, 'create'])->name('crm.customers.create');
        Route::post('/', [CustomerController::class, 'store'])->name('crm.customers.store');
        Route::get('/{id}', [CustomerController::class, 'show'])->name('crm.customers.show');
        Route::get('/{id}/edit', [CustomerController::class, 'edit'])->name('crm.customers.edit');
        Route::put('/{id}', [CustomerController::class, 'update'])->name('crm.customers.update');
        Route::delete('/{id}', [CustomerController::class, 'destroy'])->name('crm.customers.destroy');
        Route::get('/download/report', [CustomerController::class, 'downloadCustomersReport'])->name('crm.customers.download');
    });

    Route::prefix('/orders')->group(function () {
        Route::get('/', [OrderController::class, 'index'])->name('crm.orders.index');
        Route::get('/create', [OrderController::class, 'create'])->name('crm.orders.create');
        Route::post('/', [OrderController::class, 'store'])->name('crm.orders.store');
        Route::get('/{id}', [OrderController::class, 'show'])->name('crm.orders.show');
        Route::get('/{id}/edit', [OrderController::class, 'edit'])->name('crm.orders.edit');
        Route::put('/{id}', [OrderController::class, 'update'])->name('crm.orders.update');
        Route::get('/{id}/download/contract', [OrderController::class, 'downloadContract'])->name('crm.orders.download.contract');
        Route::delete('/{id}', [OrderController::class, 'destroy'])->name('crm.orders.destroy');
        Route::get('/download/report', [OrderController::class, 'downloadOrdersReport'])->name('crm.orders.download');
        Route::get('/download/analysis', [OrderController::class, 'downloadAnalysisOrdersReport'])->name('crm.orders.download.analysis');
    });

    Route::prefix('/requests')->group(function () {
        Route::get('/', [RequestController::class, 'index'])->name('crm.requests.index');
        Route::get('/{id}', [RequestController::class, 'show'])->name('crm.requests.show');
        Route::get('/{id}/edit', [RequestController::class, 'edit'])->name('crm.requests.edit');
        Route::put('/{id}', [RequestController::class, 'update'])->name('crm.requests.update');
        Route::put('/{id}/status', [RequestController::class, 'nextStatus'])->name('crm.requests.status.next');
        Route::delete('/{id}', [RequestController::class, 'destroy'])->name('crm.requests.destroy');
    });

    Route::prefix('/news')->group(function () {
        Route::get('/', [NewsController::class, 'index'])->name('crm.news.index');
        Route::get('/create', [NewsController::class, 'create'])->name('crm.news.create');
        Route::get('/sources', [NewsController::class, 'sources'])->name('crm.news.sources.index');
        Route::post('/sources', [NewsController::class, 'storeSource'])->name('crm.news.sources.store');
        Route::put('/sources/{id}', [NewsController::class, 'toggleSource'])->name('crm.news.sources.toggle');
        Route::post('/', [NewsController::class, 'storeNews'])->name('crm.news.store');
        Route::put('/{id}/approve', [NewsController::class, 'approve'])->name('crm.news.approve');
        Route::get('/{id}/edit', [NewsController::class, 'edit'])->name('crm.news.edit');
        Route::put('/{id}', [NewsController::class, 'update'])->name('crm.news.update');
        Route::delete('/{id}', [NewsController::class, 'destroy'])->name('crm.news.destroy');
    });

    Route::resource('/team', TeamMemberController::class)
        ->middleware('not-manager')
        ->names('crm.team');

    Route::resource('/managers', ManagerController::class)
        ->parameters(['managers' => 'manager'])
        ->except(['show'])
        ->names('crm.managers');

    Route::resource('/testimonials', TestimonialController::class)
        ->middleware('not-manager')
        ->names('crm.testimonials');
});
