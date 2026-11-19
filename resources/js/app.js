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
    dias: 0, horas: 0, minutos: 0, segundos: 0, acabou: false,
    init() {
        this.tick();
        this.timer = setInterval(() => this.tick(), 1000);
    },
    destroy() { clearInterval(this.timer); },
    doisDigitos(n) { return String(n).padStart(2, '0'); },
    tick() {
        const falta = new Date(alvo).getTime() - Date.now();
        if (falta <= 0) { this.acabou = true; clearInterval(this.timer); return; }
        this.dias = Math.floor(falta / 864e5);
        this.horas = Math.floor((falta % 864e5) / 36e5);
        this.minutos = Math.floor((falta % 36e5) / 6e4);
        this.segundos = Math.floor((falta % 6e4) / 1e3);
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

const reais = new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' });

/**
 * Anima um número de "de" até "ate" e chama "aplicar" a cada quadro.
 */
function contarAte(de, ate, aplicar, duracao = 1400) {
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches || de === ate) {
        aplicar(ate);
        return;
    }
    const inicio = performance.now();
    const passo = (agora) => {
        const t = Math.min(1, (agora - inicio) / duracao);
        const suave = 1 - Math.pow(1 - t, 3);
        aplicar(de + (ate - de) * suave);
        if (t < 1) requestAnimationFrame(passo);
    };
    requestAnimationFrame(passo);
}

/**
 * Telão: consulta o estado a cada 2 segundos e anima as mudanças.
 */
Alpine.data('telao', ({ url, inicial }) => ({
    estado: inicial,
    arrecadadoAnimado: reais.format(inicial.arrecadado),
    telaCheia: false,
    cursorOculto: false,

    init() {
        this.timer = setInterval(() => this.atualizar(), 2000);
        document.addEventListener('visibilitychange', () => !document.hidden && this.atualizar());
        document.addEventListener('fullscreenchange', () => (this.telaCheia = !!document.fullscreenElement));
        document.addEventListener('keydown', (e) => e.key.toLowerCase() === 'f' && this.entrarTelaCheia());
        this.mostrarCursor();
    },

    async atualizar() {
        try {
            const resp = await fetch(url, { headers: { Accept: 'application/json' }, cache: 'no-store' });
            if (!resp.ok) return;
            const novo = await resp.json();
            const antes = this.estado.arrecadado;
            this.estado = novo;
            if (novo.arrecadado !== antes) {
                contarAte(antes, novo.arrecadado, (v) => (this.arrecadadoAnimado = reais.format(v)));
            }
        } catch (e) { /* sem internet por um instante: tenta de novo no próximo ciclo */ }
    },

    entrarTelaCheia() {
        document.documentElement.requestFullscreen?.().catch(() => {});
    },

    mostrarCursor() {
        this.cursorOculto = false;
        clearTimeout(this.timerCursor);
        this.timerCursor = setTimeout(() => (this.cursorOculto = true), 3000);
    },
}));

/**
 * Página de resultado: número que sobe até o total e imagem para as redes.
 */
Alpine.data('resultado', ({ total, dados }) => ({
    totalAnimado: reais.format(0),
    gerando: false,

    init() {
        const obs = new IntersectionObserver(([e]) => {
            if (e.isIntersecting) {
                contarAte(0, total, (v) => (this.totalAnimado = reais.format(v)), 1800);
                obs.disconnect();
            }
        });
        obs.observe(this.$refs.total);
    },

    async baixarImagem() {
        this.gerando = true;
        try {
            await document.fonts.ready;
            const c = document.createElement('canvas');
            c.width = 1080; c.height = 1350;
            const g = c.getContext('2d');
            const cor = { papel: '#f6f1ea', marinho: '#22307a', rosa: '#c8185f', tinta: '#1b1d36', sol: '#f2b632' };

            g.fillStyle = cor.papel; g.fillRect(0, 0, 1080, 1350);
            g.fillStyle = cor.marinho; g.fillRect(0, 1270, 1080, 80);

            const marca = await carregarImagem(dados.marca);
            g.drawImage(marca, 90, 96, 110, 110);

            g.fillStyle = cor.marinho;
            g.font = '900 54px "Big Shoulders Display"';
            g.textBaseline = 'alphabetic';
            g.fillText(dados.titulo.toUpperCase(), 225, 150, 770);
            g.font = '600 30px "Figtree Variable"';
            g.fillStyle = cor.tinta;
            g.fillText(dados.cidade, 225, 196);

            g.fillStyle = cor.rosa;
            g.font = '900 64px "Big Shoulders Display"';
            g.fillText('ARRECADAMOS', 90, 395);
            g.font = '900 190px "Big Shoulders Display"';
            g.fillText(reais.format(total), 84, 600, 912);

            g.fillStyle = cor.tinta;
            g.font = '600 46px "Figtree Variable"';
            quebrar(g, 'para o Hospital de Amor Amazônia, que trata pacientes com câncer pelo SUS.', 90, 700, 900, 60);

            g.fillStyle = cor.marinho;
            g.font = '900 120px "Big Shoulders Display"';
            g.fillText(String(dados.arrematados), 90, 990);
            g.fillText(String(dados.doadores), 560, 990);
            g.font = '600 36px "Figtree Variable"';
            g.fillStyle = cor.tinta;
            g.fillText('lotes arrematados', 90, 1040);
            g.fillText('doadores', 560, 1040);

            g.fillStyle = cor.rosa;
            g.font = '900 64px "Big Shoulders Display"';
            g.fillText(`OBRIGADO, ${dados.cidadeCurta.toUpperCase()}!`, 90, 1190, 900);

            const link = document.createElement('a');
            link.download = `resultado-${dados.slug}.png`;
            link.href = c.toDataURL('image/png');
            link.click();
        } finally {
            this.gerando = false;
        }
    },
}));

function carregarImagem(src) {
    return new Promise((ok, erro) => {
        const img = new Image();
        img.onload = () => ok(img);
        img.onerror = erro;
        img.src = src;
    });
}

function quebrar(g, texto, x, y, largura, altura) {
    let linha = '';
    for (const palavra of texto.split(' ')) {
        const teste = linha ? `${linha} ${palavra}` : palavra;
        if (g.measureText(teste).width > largura && linha) {
            g.fillText(linha, x, y);
            linha = palavra;
            y += altura;
        } else {
            linha = teste;
        }
    }
    g.fillText(linha, x, y);
}

window.Alpine = Alpine;
Alpine.start();
