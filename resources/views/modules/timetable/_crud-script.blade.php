<script>
function crudTable({ storeUrl, updateBase, deleteBase, emptyForm }) {
    const csrf = document.querySelector('meta[name="csrf-token"]').content;

    return {
        showModal: false,
        editMode:  false,
        loading:   false,
        itemId:    null,
        form:      emptyForm(),
        errors:    {},

        openCreate() {
            this.editMode = false;
            this.itemId   = null;
            this.errors   = {};
            this.form     = emptyForm();
            this.showModal = true;
        },

        openEdit(item) {
            this.editMode  = true;
            this.itemId    = item.id;
            this.errors    = {};
            this.form      = Object.assign(emptyForm(), item);
            this.showModal = true;
        },

        closeModal() { this.showModal = false; },

        async save() {
            this.errors  = {};
            this.loading = true;
            const url    = this.editMode
                ? updateBase.replace('__ID__', this.itemId)
                : storeUrl;
            const method = this.editMode ? 'PUT' : 'POST';

            try {
                const res = await fetch(url, {
                    method,
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
                    body:    JSON.stringify(this.form),
                });

                if (res.status === 422) {
                    const d  = await res.json();
                    this.errors = Object.fromEntries(
                        Object.entries(d.errors ?? {}).map(([k, v]) => [k, Array.isArray(v) ? v[0] : v])
                    );
                    return;
                }
                if (!res.ok) throw new Error('HTTP ' + res.status);

                this.closeModal();
                window.location.reload();
            } catch(e) { console.error(e); }
            finally { this.loading = false; }
        },

        async deleteItem(id) {
            if (!confirm('Supprimer cet élément ?')) return;
            try {
                const res = await fetch(deleteBase.replace('__ID__', id), {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': csrf },
                });
                if (!res.ok) throw new Error('HTTP ' + res.status);
                window.location.reload();
            } catch(e) { console.error(e); }
        },
    };
}
</script>
