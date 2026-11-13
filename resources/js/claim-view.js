// Alpine.js state for the staff "full claim" pop-up in History (read only).

export default function claimView(claims) {
    return {
        openModal: false,
        selectedClaim: null,
        claims,

        open(id) {
            this.selectedClaim = this.claims.find((c) => c.id === id);
            this.openModal = true;
        },
        close() {
            this.openModal = false;
        },

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
        noteOf(key) {
            const d = this.item(key, 'description') || '';
            return d.startsWith('Other: ') ? d.slice(7) : '';
        },
        // Valid (not declined) Travelling lines added together; includes an old single Travelling entry.
        get travellingTotal() {
            if (!this.selectedClaim) return 0;
            return Object.entries(this.selectedClaim.items)
                .filter(([k, i]) => k.startsWith('travelling') && !i.disallowed)
                .reduce((a, [, i]) => a + i.amount, 0);
        },
        get hasTravelling() {
            return !!this.selectedClaim && Object.keys(this.selectedClaim.items).some((k) => k.startsWith('travelling'));
        },
        isOut(key) {
            return this.item(key, 'disallowed') === true;
        },
        reasonOf(key) {
            return this.item(key, 'disallow_reason') || '';
        },
        get hasDisallowed() {
            return !!this.selectedClaim && Object.values(this.selectedClaim.items).some((i) => i.disallowed);
        },
        get approvedTotal() {
            return this.selectedClaim ? this.selectedClaim.total_amount : 0;
        },
    };
}
