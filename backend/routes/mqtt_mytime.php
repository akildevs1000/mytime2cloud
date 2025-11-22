<?php
// routes/api.php
use App\Http\Controllers\Mqtt\MytimeFaceDeviceController;
use Illuminate\Support\Facades\Route;


Route::prefix('face-device/{deviceId}')->group(function () {
    // Door
    Route::post('/open-door', [MytimeFaceDeviceController::class, 'openDoor']);
    Route::post('/close-door', [MytimeFaceDeviceController::class, 'closeDoor']);
    Route::get('/door-config', [MytimeFaceDeviceController::class, 'getDoorConfig']);
    Route::post('/door-config', [MytimeFaceDeviceController::class, 'setDoorConfig']);

    // Time
    Route::get('/time', [MytimeFaceDeviceController::class, 'getTime']);
    Route::post('/time', [MytimeFaceDeviceController::class, 'setTime']);

    // Personnel
    Route::post('/person', [MytimeFaceDeviceController::class, 'savePerson']);
    Route::delete('/person/{customId}', [MytimeFaceDeviceController::class, 'deletePerson']);
    Route::post('/persons/batch', [MytimeFaceDeviceController::class, 'batchSavePersons']);
    Route::post('/persons/batch-delete', [MytimeFaceDeviceController::class, 'batchDeletePersons']);
    Route::post('/persons/delete-all', [MytimeFaceDeviceController::class, 'deleteAllPersons']);
    Route::get('/person/{customId}', [MytimeFaceDeviceController::class, 'getPerson']);
    Route::post('/persons/search', [MytimeFaceDeviceController::class, 'searchPersonList']);

    // Snapshot & QR
    Route::post('/snapshot', [MytimeFaceDeviceController::class, 'snapshot']);
    Route::post('/qrcode', [MytimeFaceDeviceController::class, 'showQRCode']);

    // Ads
    Route::post('/ad', [MytimeFaceDeviceController::class, 'saveAd']);
    Route::delete('/ad', [MytimeFaceDeviceController::class, 'deleteAd']);

    // Strategy
    Route::post('/strategy', [MytimeFaceDeviceController::class, 'saveStrategy']);
    Route::delete('/strategy', [MytimeFaceDeviceController::class, 'deleteStrategies']);
    Route::post('/strategy/bind', [MytimeFaceDeviceController::class, 'bindStrategies']);
    Route::post('/strategy/unbind', [MytimeFaceDeviceController::class, 'unbindStrategies']);

    // Temperature
    Route::get('/temperature-config', [MytimeFaceDeviceController::class, 'getTemperatureConfig']);
    Route::post('/temperature-config', [MytimeFaceDeviceController::class, 'setTemperatureConfig']);

    // GPS
    Route::get('/gps', [MytimeFaceDeviceController::class, 'getGps']);
    Route::post('/gps', [MytimeFaceDeviceController::class, 'setGps']);

    // Sound
    Route::get('/sound-config', [MytimeFaceDeviceController::class, 'getSoundConfig']);
    Route::post('/sound-config', [MytimeFaceDeviceController::class, 'setSoundConfig']);

    // System
    Route::post('/reboot', [MytimeFaceDeviceController::class, 'reboot']);
    Route::post('/factory-reset', [MytimeFaceDeviceController::class, 'factoryReset']);
});
