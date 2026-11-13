// Alpine.js state for the admin review pop-up.
// Admin can only view a claim, plus decline / reinstate single lines of a PENDING claim.

export default function adminReview(claims, baseUrl) {
    return {
        openModal: false,
        selectedClaim: null,
        rejecting: false,
        claims,
        activeKey: null, // the line whose small panel is open
        reason: '',
        busy: false,
        err: '',

        // ---------- opening / closing ----------
        open(id) {
            this.selectedClaim = this.claims.find((c) => c.id === id);
            this.rejecting = false;
            this.activeKey = null;
            this.err = '';
            this.openModal = true;
        },
        close() {
            this.openModal = false;
            this.rejecting = false;
            this.activeKey = null;
        },

        // ---------- display helpers ----------
        item(key, field) {
            const i = this.selectedClaim && this.selectedClaim.items[key];
            return i ? i[field] : null;
        },
        fmt(n) {
            return n === null || n === undefined
                ? ''
                : Number(n).toLocaleString('en-MY', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        },
        qty(key) {
            const v = this.item(key, 'unit_input');
            return v === null ? '' : Number(v).toLocaleString('en-MY', { maximumFractionDigits: 2 });
        },
        amt(key) {
            const v = this.item(key, 'amount');
            return v === null ? '—' : this.fmt(v);
        },
        claimTotal(id) {
            const c = this.claims.find((c) => c.id === id);
            return c ? c.total_amount : 0;
        },

        // ---------- line state ----------
        get items() {
            return this.selectedClaim ? Object.values(this.selectedClaim.items) : [];
        },
        get hasDisallowed() {
            return this.items.some((i) => i.disallowed);
        },
        get hasValid() {
            return this.items.some((i) => !i.disallowed);
        },
        isPending() {
            return !!this.selectedClaim && this.selectedClaim.status === 'pending';
        },
        canReview(key) {
            return this.isPending() && !!this.selectedClaim.items[key];
        },
        isOut(key) {
            return this.item(key, 'disallowed') === true;
        },
        reasonOf(key) {
            return this.item(key, 'disallow_reason') || '';
        },

        // ---------- disallow / restore ----------
        toggle(key) {
            if (!this.canReview(key)) return;
            this.err = '';
            this.reason = '';
            this.activeKey = this.activeKey === key ? null : key;
        },
        async send(key, action) {
            const line = this.selectedClaim.items[key];
            if (!line || this.busy) return;

            if (action === 'disallow' && !this.reason.trim()) {
                this.err = 'Please enter a reason for declining.';
                return;
            }

            this.busy = true;
            this.err = '';
            try {
                const res = await fetch(`${baseUrl}/${this.selectedClaim.id}/items/${line.id}/${action}`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({ reason: this.reason }),
                });
                const data = await res.json().catch(() => ({}));
                if (!res.ok) throw new Error((data.errors && data.errors.reason && data.errors.reason[0]) || data.message || 'Could not update this line.');

                // Replace the claim with the server's recalculated version.
                const idx = this.claims.findIndex((c) => c.id === data.claim.id);
                this.claims[idx] = data.claim;
                this.selectedClaim = this.claims[idx];
                this.activeKey = null;
                this.reason = '';
            } catch (e) {
                this.err = e.message;
            } finally {
                this.busy = false;
            }
        },
    };
}
