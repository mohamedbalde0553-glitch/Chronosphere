import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import listPlugin from '@fullcalendar/list';
import interactionPlugin from '@fullcalendar/interaction';
import frLocale from '@fullcalendar/core/locales/fr';

let fcInstance = null;

document.addEventListener('DOMContentLoaded', () => {
    const el = document.getElementById('cs-calendar');
    if (!el) return;

    const feedUrl  = el.dataset.feedUrl;
    const csrf     = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

    fcInstance = new Calendar(el, {
        plugins:     [dayGridPlugin, timeGridPlugin, listPlugin, interactionPlugin],
        locale:      frLocale,
        initialView: 'dayGridMonth',
        height:      'auto',
        editable:    true,
        selectable:  true,

        headerToolbar: {
            left:   'prev,next today',
            center: 'title',
            right:  'dayGridMonth,timeGridWeek,timeGridDay,listWeek',
        },

        buttonText: {
            today:        "Aujourd'hui",
            month:        'Mois',
            week:         'Semaine',
            day:          'Jour',
            list:         'Agenda',
        },

        events: {
            url:    feedUrl,
            method: 'GET',
            failure: () => console.error('Impossible de charger les événements.'),
        },

        // Click on existing event → open edit modal
        eventClick(info) {
            info.jsEvent.preventDefault();
            window.dispatchEvent(new CustomEvent('cs:event-click', {
                detail: {
                    id:          info.event.id,
                    title:       info.event.title,
                    start:       info.event.startStr,
                    end:         info.event.endStr,
                    allDay:      info.event.allDay,
                    color:       info.event.backgroundColor,
                    description: info.event.extendedProps.description ?? '',
                    location:    info.event.extendedProps.location ?? '',
                    calendar_id: info.event.extendedProps.calendar_id,
                    status:      info.event.extendedProps.status ?? 'confirmed',
                },
            }));
        },

        // Click on empty slot → open create modal
        dateClick(info) {
            const start = info.dateStr.length === 10
                ? info.dateStr + 'T09:00'
                : info.dateStr.substring(0, 16);
            const end = info.dateStr.length === 10
                ? info.dateStr + 'T10:00'
                : addHour(info.dateStr.substring(0, 16));

            window.dispatchEvent(new CustomEvent('cs:date-click', {
                detail: { start, end, allDay: info.allDay },
            }));
        },

        // Drag & drop
        eventDrop(info) {
            patchEvent(info.event, csrf).catch(() => info.revert());
        },

        // Resize
        eventResize(info) {
            patchEvent(info.event, csrf).catch(() => info.revert());
        },
    });

    fcInstance.render();
    window.csCalendar = fcInstance;
});

function patchEvent(event, csrf) {
    const eventId = event.id;
    const url = window.csEventUpdateBase.replace('__ID__', eventId);

    return fetch(url, {
        method:  'PUT',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
        body:    JSON.stringify({
            start_at:   event.startStr,
            end_at:     event.endStr || event.startStr,
            is_all_day: event.allDay,
        }),
    }).then(r => { if (!r.ok) throw new Error('Update failed'); });
}

function addHour(dtStr) {
    const d = new Date(dtStr);
    d.setHours(d.getHours() + 1);
    return d.toISOString().substring(0, 16);
}

// Refresh calendar after CRUD operations
window.addEventListener('cs:refresh-calendar', () => {
    fcInstance?.refetchEvents();
});
