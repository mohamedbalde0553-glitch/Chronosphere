import { Calendar } from '@fullcalendar/core';
import timeGridPlugin from '@fullcalendar/timegrid';
import listPlugin from '@fullcalendar/list';
import interactionPlugin from '@fullcalendar/interaction';
import frLocale from '@fullcalendar/core/locales/fr';

let fcInstance = null;

document.addEventListener('DOMContentLoaded', () => {
    const el = document.getElementById('cs-shifts');
    if (!el) return;

    const feedUrl    = el.dataset.feedUrl;
    const filterType = el.dataset.filterType ?? 'department';
    const filterId   = el.dataset.filterId ?? '';
    const csrf       = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

    fcInstance = new Calendar(el, {
        plugins:     [timeGridPlugin, listPlugin, interactionPlugin],
        locale:      frLocale,
        initialView: 'timeGridWeek',
        height:      'auto',
        editable:    true,
        selectable:  true,
        allDaySlot:  true,

        slotMinTime: '06:00:00',
        slotMaxTime: '23:00:00',
        slotDuration: '00:30:00',

        headerToolbar: {
            left:   'prev,next today',
            center: 'title',
            right:  'timeGridWeek,timeGridDay,listWeek',
        },

        buttonText: {
            today: "Aujourd'hui",
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
            if (info.event.extendedProps.type === 'leave') return;
            window.dispatchEvent(new CustomEvent('cs:shift-click', {
                detail: {
                    id:             info.event.id,
                    start:          info.event.startStr,
                    end:            info.event.endStr,
                    employee_id:    info.event.extendedProps.employee_id,
                    shift_type_id:  info.event.extendedProps.shift_type_id,
                    status:         info.event.extendedProps.status,
                    notes:          info.event.extendedProps.notes,
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
            if (info.event.extendedProps.type === 'leave') { info.revert(); return; }
            patchShift(info.event, csrf).catch(() => info.revert());
        },

        eventResize(info) {
            patchShift(info.event, csrf).catch(() => info.revert());
        },
    });

    fcInstance.render();
    window.csShifts = fcInstance;
});

window.addEventListener('cs:shift-click', (e) => {
    document.querySelector('[x-data]')?._x_dataStack?.[0]?.openEditShift(e.detail);
});
window.addEventListener('cs:slot-select', (e) => {
    document.querySelector('[x-data]')?._x_dataStack?.[0]?.openNewShift(e.detail.start, e.detail.end);
});
window.addEventListener('cs:refresh-shifts', () => {
    fcInstance?.refetchEvents();
});

function patchShift(event, csrf) {
    const url = window.csShiftUrls.updateBase.replace('__ID__', event.id);
    return fetch(url, {
        method:  'PUT',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
        body:    JSON.stringify({
            start_at: event.startStr,
            end_at:   event.endStr,
        }),
    }).then(r => { if (!r.ok) throw new Error(); });
}
