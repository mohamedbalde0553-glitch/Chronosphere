import Sortable from 'sortablejs';
import Gantt from 'frappe-gantt';

const csrf = () => document.querySelector('meta[name="csrf-token"]')?.content ?? '';

/* ─── Kanban ─────────────────────────────────────────────── */
document.addEventListener('DOMContentLoaded', () => {
    initKanban();
    initGantt();
});

function initKanban() {
    const columns = document.querySelectorAll('[data-kanban-column]');
    if (!columns.length) return;

    const reorderUrl = document.getElementById('cs-board')?.dataset.reorderUrl;

    columns.forEach(col => {
        Sortable.create(col, {
            group:     'kanban',
            animation: 150,
            ghostClass: 'opacity-40',
            dragClass:  'shadow-2xl rotate-1',
            handle:    '.drag-handle',

            onEnd(evt) {
                const items = [];
                document.querySelectorAll('[data-kanban-column]').forEach(c => {
                    const status = c.dataset.kanbanColumn;
                    c.querySelectorAll('[data-task-id]').forEach((card, idx) => {
                        items.push({ id: parseInt(card.dataset.taskId), status, sort_order: idx });
                    });
                });

                fetch(reorderUrl, {
                    method:  'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf() },
                    body:    JSON.stringify({ items }),
                }).catch(console.error);
            },
        });
    });
}

/* ─── Gantt ──────────────────────────────────────────────── */
function initGantt() {
    const el = document.getElementById('cs-gantt');
    if (!el) return;

    const raw     = JSON.parse(el.dataset.tasks || '[]');
    const updateUrl = el.dataset.updateUrl;

    if (!raw.length) {
        el.innerHTML = '<p class="text-gray-400 text-sm text-center py-12">Aucune tâche avec des dates pour afficher le Gantt.</p>';
        return;
    }

    const tasks = raw.map(t => ({
        id:           String(t.id),
        name:         t.name,
        start:        t.start_date ?? t.due_date ?? new Date().toISOString().slice(0, 10),
        end:          t.due_date   ?? t.start_date ?? new Date().toISOString().slice(0, 10),
        progress:     t.progress ?? 0,
        dependencies: (t.dependencies ?? []).map(d => String(d.depends_on_task_id)).join(','),
    }));

    const gantt = new Gantt(el, tasks, {
        view_mode:       'Week',
        language:        'fr',
        date_format:     'YYYY-MM-DD',
        column_width:    30,
        bar_corner_radius: 3,
        arrow_curve:     5,

        on_date_change(task, start, end) {
            if (!updateUrl) return;
            const url = updateUrl.replace('__ID__', task.id);
            fetch(url, {
                method:  'PUT',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf() },
                body:    JSON.stringify({
                    start_date: start.toISOString().slice(0, 10),
                    due_date:   end.toISOString().slice(0, 10),
                }),
            }).catch(console.error);
        },

        on_progress_change(task, progress) {
            if (!updateUrl) return;
            const url = updateUrl.replace('__ID__', task.id);
            fetch(url, {
                method:  'PUT',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf() },
                body:    JSON.stringify({ progress }),
            }).catch(console.error);
        },

        on_click(task) {
            window.dispatchEvent(new CustomEvent('cs:gantt-task-click', { detail: { id: task.id } }));
        },
    });

    window.csGantt = gantt;

    // View mode switcher
    document.querySelectorAll('[data-gantt-view]').forEach(btn => {
        btn.addEventListener('click', () => {
            gantt.change_view_mode(btn.dataset.ganttView);
            document.querySelectorAll('[data-gantt-view]').forEach(b =>
                b.classList.toggle('bg-indigo-600', b === btn)
            );
        });
    });
}
