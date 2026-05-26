import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import listPlugin from '@fullcalendar/list';
import interactionPlugin from '@fullcalendar/interaction';
import frLocale from '@fullcalendar/core/locales/fr';

let fcInstance = null;

document.addEventListener('DOMContentLoaded', () => {
    const el = document.getElementById('cs-booking');
    if (!el) return;

    const feedUrl    = el.dataset.feedUrl;
    const filterType = el.dataset.filterType ?? 'resource';
    const filterId   = el.dataset.filterId ?? '';
    const csrf       = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

    fcInstance = new Calendar(el, {
        plugins:     [dayGridPlugin, timeGridPlugin, listPlugin, interactionPlugin],
        locale:      frLocale,
        initialView: 'timeGridWeek',
        height:      'auto',
        editable:    true,
        selectable:  true,
        allDaySlot:  false,

        slotMinTime:  '07:00:00',
        slotMaxTime:  '22:00:00',
        slotDuration: '00:30:00',

        headerToolbar: {
            left:   'prev,next today',
            center: 'title',
            right:  'dayGridMonth,timeGridWeek,timeGridDay,listWeek',
        },

        buttonText: {
            today: "Aujourd'hui",
            month: 'Mois',
            week:  'Semaine',
            day:   'Jour',
            list:  'Liste',
        },

        events: {
            url:         feedUrl,
            method:      'GET',
            extraParams: { by: filterType, id: filterId },
        },

        eventClick(info) {
            window.dispatchEvent(new CustomEvent('cs:booking-click', {
                detail: {
                    id:            info.event.id,
                    start:         info.event.startStr,
                    end:           info.event.endStr,
                    title:         info.event.title.split(' — ')[0],
                    resource_id:   info.event.extendedProps.resource_id,
                    attendees:     info.event.extendedProps.attendees,
                    status:        info.event.extendedProps.status,
                    description:   info.event.extendedProps.description,
                },
            }));
        },

        select(info) {
            window.dispatchEvent(new CustomEvent('cs:slot-select', {
                detail: {
                    start: info.startStr.substring(0, 16),
                    end:   info.endStr.substring(0, 16),
                },
            }));
            fcInstance.unselect();
        },

        eventDrop(info) {
            patchBooking(info.event, csrf).catch(() => info.revert());
        },

        eventResize(info) {
            patchBooking(info.event, csrf).catch(() => info.revert());
        },
    });

    fcInstance.render();
    window.csBooking = fcInstance;
});

window.addEventListener('cs:booking-click', (e) => {
    document.querySelector('[x-data]')?._x_dataStack?.[0]?.openEditBooking(e.detail);
});
window.addEventListener('cs:slot-select', (e) => {
    document.querySelector('[x-data]')?._x_dataStack?.[0]?.openNewBooking(e.detail.start, e.detail.end);
});
window.addEventListener('cs:refresh-booking', () => {
    fcInstance?.refetchEvents();
});

function patchBooking(event, csrf) {
    const url = window.csBookingUrls.updateBase.replace('__ID__', event.id);
    return fetch(url, {
        method:  'PUT',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
        body:    JSON.stringify({
            start_at: event.startStr,
            end_at:   event.endStr,
        }),
    }).then(r => { if (!r.ok) throw new Error(); });
}
