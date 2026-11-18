// Alpine.js reactive claim calculator.
// Reads the same rate matrix as the server (config/claim_rates.php -> window.CLAIM_RATES).

export default function claimCalculator(old = {}, oldNotes = {}) {
    const config = window.CLAIM_RATES;
    const allowedExt = config.allowed_mimes.map((e) => e.toLowerCase()); // pdf, jpg, jpeg
    const allowedMime = ['application/pdf', 'image/jpeg'];
    const maxBytes = config.max_file_kb * 1024;

    return {
        rows: config.rows,
        form: Object.fromEntries(config.rows.map((r) => [r.key, old[r.key] ?? ''])),
        proofs: Object.fromEntries(config.rows.filter((r) => r.proof).map((r) => [r.key, []])), // receipts per claim line
        proofError: {},
        attempted: false,
        notes: { travelling_other: oldNotes.travelling_other ?? '' },
        files: [],
        previewFile: null,
        previewUrl: '',
        urls: {}, // object URLs so staff can preview a file on their own computer before submitting
        fileError: '',
        submitError: '',
        submitting: false,

        // ---------- helpers ----------
        // An amount box holds a number ("12.50") or amounts added together ("1.10 + 2.40").
        // Only digits, '.', '+' and spaces are read: nothing is evaluated as code.
        terms(key) {
            const raw = String(this.form[key] ?? '').trim();
            if (raw === '') return [];
            if (!/^\d+(\.\d{1,2})?(\s*\+\s*\d+(\.\d{1,2})?)*$/.test(raw)) {
                const n = Number(raw); // plain numbers from number boxes, e.g. 3.5
                return Number.isFinite(n) && n > 0 ? [n] : [];
            }
            return raw.split('+').map((t) => parseFloat(t));
        },
        num(key) {
            const v = this.terms(key).reduce((a, b) => a + b, 0);
            return v > 0 ? this.round(v) : 0;
        },
        // Typed something, but it is not a number or a sum of numbers.
        badAmount(key) {
            return String(this.form[key] ?? '').trim() !== '' && this.terms(key).length === 0 && !/^\s*0+(\.0+)?\s*$/.test(String(this.form[key]));
        },
        proofRows() {
            return this.rows.filter((r) => r.proof);
        },
        // Lines with an amount but no receipt yet.
        get missingProofs() {
            return this.proofRows().filter((r) => this.num(r.key) > 0 && !(this.proofs[r.key] || []).length);
        },
        round(v) {
            return Math.round((v + Number.EPSILON) * 100) / 100;
        },
        fmt(n) {
            return Number(n || 0).toLocaleString('en-MY', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2,
            });
        },

        amountFor(row) {
            const v = this.num(row.key);
            if (v <= 0) return 0;
            switch (row.type) {
                case 'per_unit': // meal, lodging, hardship, mileage tiers
                    return this.round(v * row.rate);
                case 'fixed_threshold': // lodging fixed allowance: flat amount when days > threshold
                    return v > row.threshold ? row.amount : 0;
                case 'direct': // receipt-backed claims
                    return this.round(v);
                default:
                    return 0;
            }
        },

        // ---------- reactive results ----------
        get amounts() {
            const out = {};
            this.rows.forEach((r) => (out[r.key] = this.amountFor(r)));
            return out;
        },
        // Fuel + Toll + Parking + Other
        get travellingTotal() {
            return this.round(
                Object.entries(this.amounts)
                    .filter(([k]) => k.startsWith('travelling_'))
                    .reduce((a, [, v]) => a + v, 0)
            );
        },
        get total() {
            return this.round(Object.values(this.amounts).reduce((a, b) => a + b, 0));
        },

        // ---------- attachments (no limit on the number of files) ----------
        extOf(name) {
            const i = name.lastIndexOf('.');
            return i === -1 ? '' : name.slice(i + 1).toLowerCase();
        },
        isPdf(file) {
            return this.extOf(file.name) === 'pdf';
        },
        addFiles(event) {
            this.fileError = '';
            const rejected = [];

            for (const file of Array.from(event.target.files)) {
                const ext = this.extOf(file.name);
                const okType = allowedExt.includes(ext) && (!file.type || allowedMime.includes(file.type));
                if (!okType) {
                    rejected.push(`${file.name} (only PDF or JPG)`);
                } else if (file.size > maxBytes) {
                    rejected.push(`${file.name} (over ${config.max_file_kb / 1024} MB)`);
                } else if (this.files.some((f) => f.name === file.name && f.size === file.size)) {
                    rejected.push(`${file.name} (already attached)`);
                } else {
                    this.files.push(file);
                }
            }

            if (rejected.length) this.fileError = 'Not added: ' + rejected.join(', ');
            this.syncInput();
        },
        fileKey(file) {
            return file.name + '|' + file.size;
        },
        urlFor(file) {
            const k = this.fileKey(file);
            if (!this.urls[k]) this.urls[k] = URL.createObjectURL(file);
            return this.urls[k];
        },
        openPreview(file) {
            this.previewFile = file;
            this.previewUrl = this.urlFor(file);
        },
        closePreview() {
            this.previewFile = null;
            this.previewUrl = '';
        },
        removeFile(index) {
            const gone = this.files[index];
            if (gone) {
                const k = this.fileKey(gone);
                if (this.previewFile && this.fileKey(this.previewFile) === k) this.closePreview();
                if (this.urls[k]) {
                    URL.revokeObjectURL(this.urls[k]);
                    delete this.urls[k];
                }
            }
            this.files.splice(index, 1);
            this.fileError = '';
            this.syncInput();
        },
        // Keep the native <input type="file"> in step with the pill list so a normal multipart POST works.
        syncInput() {
            const dt = new DataTransfer();
            this.files.forEach((f) => dt.items.add(f));
            this.$refs.fileInput.files = dt.files;
        },
        get fileStatus() {
            if (!this.files.length) return 'No file chosen';
            return this.files.length === 1 ? this.files[0].name : `${this.files.length} files chosen`;
        },

        // ---------- receipts per claim line ----------
        addProofs(key, event) {
            this.proofError[key] = '';
            const rejected = [];
            const list = this.proofs[key] || [];

            for (const file of Array.from(event.target.files)) {
                const ext = this.extOf(file.name);
                const okType = allowedExt.includes(ext) && (!file.type || allowedMime.includes(file.type));
                if (!okType) {
                    rejected.push(`${file.name} (only PDF or JPG)`);
                } else if (file.size > maxBytes) {
                    rejected.push(`${file.name} (over ${config.max_file_kb / 1024} MB)`);
                } else if (list.some((f) => f.name === file.name && f.size === file.size)) {
                    rejected.push(`${file.name} (already attached)`);
                } else {
                    list.push(file);
                }
            }

            this.proofs[key] = [...list];
            if (rejected.length) this.proofError[key] = 'Not added: ' + rejected.join(', ');
            this.syncProof(key);
        },
        removeProof(key, index) {
            const list = [...(this.proofs[key] || [])];
            const gone = list[index];
            if (gone) {
                const k = this.fileKey(gone);
                if (this.previewFile && this.fileKey(this.previewFile) === k) this.closePreview();
                if (this.urls[k]) {
                    URL.revokeObjectURL(this.urls[k]);
                    delete this.urls[k];
                }
            }
            list.splice(index, 1);
            this.proofs[key] = list;
            this.proofError[key] = '';
            this.syncProof(key);
        },
        // Keep each line's native <input type="file"> in step with its chip list so a normal multipart POST works.
        syncProof(key) {
            const input = document.getElementById('proof_' + key);
            if (!input) return;
            const dt = new DataTransfer();
            (this.proofs[key] || []).forEach((f) => dt.items.add(f));
            input.files = dt.files;
        },

        // ---------- submit guard ----------
        validate(event) {
            this.submitError = '';
            this.attempted = true;
            // Claim types are optional individually, but at least one must be filled in.
            const bad = this.rows.filter((r) => this.badAmount(r.key));
            if (bad.length) {
                this.submitError = 'Check the amount for: ' + bad.map((r) => r.label).join(', ') + '. Use a number such as 12.50, or amounts joined with + such as 1.10 + 2.40.';
            } else if (this.total <= 0) {
                this.submitError = 'Fill in at least one claim type before submitting.';
            } else if (this.missingProofs.length) {
                // Every line that needs a receipt must have one.
                this.submitError = 'Please attach a receipt or proof for: ' + this.missingProofs.map((r) => r.label).join(', ') + '.';
            }
            if (this.submitError) {
                event.preventDefault();
                return;
            }
            this.submitting = true;
        },
    };
}
