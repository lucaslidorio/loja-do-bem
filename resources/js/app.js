import Alpine from 'alpinejs';

const csrf = () => document.querySelector('meta[name="csrf-token"]')?.content ?? '';

/**
 * Botão "Tenho interesse": alterna no servidor e avisa a vitrine.
 */
Alpine.data('interesse', ({ url, ativo = false, total = 0 }) => ({
    ativo,
    total,
    enviando: false,

    async alternar() {
        if (this.enviando) return;
        this.enviando = true;

        // resposta otimista: o coração reage na hora
        const anterior = { ativo: this.ativo, total: this.total };
        this.ativo = !this.ativo;
        this.total = Math.max(0, this.total + (this.ativo ? 1 : -1));
        this.pulsar();

        try {
            const resp = await fetch(url, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrf(), Accept: 'application/json' },
                credentials: 'same-origin',
            });
            if (!resp.ok) throw new Error(resp.status);
            const dados = await resp.json();
            this.ativo = dados.interessado;
            this.total = dados.total;
        } catch (e) {
            Object.assign(this, anterior);
        } finally {
            this.enviando = false;
            this.$dispatch('interesse-alterado', { id: this.$el.dataset.lote, ativo: this.ativo });
        }
    },

    pulsar() {
        const icone = this.$refs.icone;
        if (!icone) return;
        icone.classList.remove('coracao-pulsa');
        void icone.offsetWidth;
        icone.classList.add('coracao-pulsa');
    },
}));

/**
 * Vitrine: busca, filtro por categoria e "minha lista", tudo no navegador.
 */
Alpine.data('vitrine', ({ meus = [] } = {}) => ({
    busca: '',
    categoria: '',
    soMeus: false,
    meus: new Set(meus.map(String)),
    visiveis: null,

    init() {
        const params = new URLSearchParams(location.search);
        this.categoria = params.get('categoria') ?? '';
        this.busca = params.get('busca') ?? '';
        this.$watch('busca', () => this.contar());
        this.$watch('categoria', () => { this.contar(); this.sincronizarUrl(); });
        this.$watch('soMeus', () => this.contar());
        this.$nextTick(() => this.contar());
    },

    normalizar(texto) {
        return (texto ?? '').toString().normalize('NFD').replace(/[̀-ͯ]/g, '').toLowerCase();
    },

    mostra(el) {
        const d = el.dataset;
        if (this.soMeus && !this.meus.has(d.id)) return false;
        if (this.categoria && d.categoria !== this.categoria) return false;
        const termo = this.normalizar(this.busca).trim();
        if (!termo) return true;
        return this.normalizar(d.busca).includes(termo);
    },

    contar() {
        this.visiveis = [...this.$root.querySelectorAll('[data-lote-item]')].filter((el) => this.mostra(el)).length;
    },

    marcar({ id, ativo }) {
        ativo ? this.meus.add(String(id)) : this.meus.delete(String(id));
        this.meus = new Set(this.meus);
        if (this.soMeus) this.contar();
    },

    limpar() {
        this.busca = '';
        this.categoria = '';
        this.soMeus = false;
    },

    sincronizarUrl() {
        const url = new URL(location.href);
        this.categoria ? url.searchParams.set('categoria', this.categoria) : url.searchParams.delete('categoria');
        history.replaceState(null, '', url);
    },
}));

/**
 * Contagem regressiva até o leilão.
 */
Alpine.data('contagem', ({ alvo }) => ({
    dias: 0, horas: 0, minutos: 0, acabou: false,
    init() {
        this.tick();
        this.timer = setInterval(() => this.tick(), 30_000);
    },
    destroy() { clearInterval(this.timer); },
    tick() {
        const falta = new Date(alvo).getTime() - Date.now();
        if (falta <= 0) { this.acabou = true; return; }
        this.dias = Math.floor(falta / 864e5);
        this.horas = Math.floor((falta % 864e5) / 36e5);
        this.minutos = Math.floor((falta % 36e5) / 6e4);
    },
}));

/**
 * Compartilhar: usa o menu nativo do celular; no computador, abre o WhatsApp.
 */
Alpine.data('compartilhar', ({ titulo, texto, url, whatsapp }) => ({
    copiado: false,
    async abrir() {
        if (navigator.share) {
            try { await navigator.share({ title: titulo, text: texto, url }); return; } catch (e) { if (e.name === 'AbortError') return; }
        }
        window.open(whatsapp, '_blank', 'noopener');
    },
    async copiar() {
        try {
            await navigator.clipboard.writeText(url);
            this.copiado = true;
            setTimeout(() => (this.copiado = false), 2200);
        } catch (e) { /* sem permissão: ignora */ }
    },
}));

window.Alpine = Alpine;
Alpine.start();
