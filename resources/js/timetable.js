import { Calendar } from '@fullcalendar/core';
import timeGridPlugin from '@fullcalendar/timegrid';
import listPlugin from '@fullcalendar/list';
import interactionPlugin from '@fullcalendar/interaction';
import frLocale from '@fullcalendar/core/locales/fr';

let fcInstance = null;

document.addEventListener('DOMContentLoaded', () => {
    const el = document.getElementById('cs-timetable');
    if (!el) return;

    const feedUrl    = el.dataset.feedUrl;
    const filterType = el.dataset.filterType ?? 'group';
    const filterId   = el.dataset.filterId ?? '';
    const csrf       = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

    fcInstance = new Calendar(el, {
        plugins:     [timeGridPlugin, listPlugin, interactionPlugin],
        locale:      frLocale,
        initialView: 'timeGridWeek',
        height:      'auto',
        editable:    true,
        selectable:  true,
        allDaySlot:  false,

        slotMinTime: '07:00:00',
        slotMaxTime: '21:00:00',
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

        // Click on session → open edit modal
        eventClick(info) {
            window.dispatchEvent(new CustomEvent('cs:session-click', {
                detail: {
                    id:        info.event.id,
                    start:     info.event.startStr,
                    end:       info.event.endStr,
                    course_id: info.event.extendedProps.course_id,
                    room_id:   info.event.extendedProps.room_id,
                    notes:     info.event.extendedProps.notes,
                },
            }));
        },

        // Select time range → open create modal
        select(info) {
            window.dispatchEvent(new CustomEvent('cs:slot-select', {
                detail: {
                    start: info.startStr.substring(0, 16),
                    end:   info.endStr.substring(0, 16),
                },
            }));
            fcInstance.unselect();
        },

        // Drag & drop
        eventDrop(info) {
            patchSession(info.event, csrf).catch(() => info.revert());
        },

        // Resize
        eventResize(info) {
            patchSession(info.event, csrf).catch(() => info.revert());
        },
    });

    fcInstance.render();
    window.csTimetable = fcInstance;
});

// Listen for Alpine events
window.addEventListener('cs:session-click', (e) => {
    document.querySelector('[x-data]')?._x_dataStack?.[0]?.openEditSession(e.detail);
});
window.addEventListener('cs:slot-select', (e) => {
    document.querySelector('[x-data]')?._x_dataStack?.[0]?.openNewSession(e.detail.start, e.detail.end);
});
window.addEventListener('cs:refresh-timetable', () => {
    fcInstance?.refetchEvents();
});

function patchSession(event, csrf) {
    const url = window.csTimetableUrls.updateBase.replace('__ID__', event.id);
    return fetch(url, {
        method:  'PUT',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
        body:    JSON.stringify({
            start_at: event.startStr,
            end_at:   event.endStr,
        }),
    }).then(r => { if (!r.ok) throw new Error(); });
}
