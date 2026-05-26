<?php

return [
    App\Providers\AppServiceProvider::class,
    App\Providers\TelescopeServiceProvider::class,
    App\Modules\Timetable\Providers\TimetableServiceProvider::class,
    App\Modules\Shifts\Providers\ShiftsServiceProvider::class,
    App\Modules\Calendar\Providers\CalendarServiceProvider::class,
    App\Modules\Booking\Providers\BookingServiceProvider::class,
    App\Modules\Project\Providers\ProjectServiceProvider::class,
];
