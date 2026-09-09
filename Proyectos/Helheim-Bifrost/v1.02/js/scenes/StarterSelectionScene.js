// ⚠️ EN PAUSA: esta escena ya no está registrada en main.js/game.php.
// El juego ahora empieza sin compañeros a propósito — las criaturas se
// domesticarán con un sistema propio más adelante, que probablemente
// reutilice este patrón de "tarjetas" para elegir con cuál quedarte tras
// atraparla. Se deja el archivo como referencia, no se carga en el juego.
//
// Se mostraba solo si el jugador todavía no tenía equipo (party vacío).
// Elegir un inicial lo guardaba de inmediato en el servidor reutilizando
// api/save_game.php, así que no hacía falta ningún endpoint nuevo.
class StarterSelectionScene extends Phaser.Scene {
  constructor() {
    super('StarterSelectionScene');
  }

  init(data) {
    this.nextData = data;
  }

  create() {
    const w = this.scale.width;
    const h = this.scale.height;

    this.add.rectangle(w / 2, h / 2, w, h, GB_PALETTE.dark).setDepth(0);
    this.add.text(w / 2, 10, 'Elige tu compañero inicial', {
      fontFamily: 'monospace', fontSize: '11px', color: '#e6ffe6',
    }).setOrigin(0.5, 0).setDepth(10);

    this.statusText = this.add.text(w / 2, h - 14, '', {
      fontFamily: 'monospace', fontSize: '9px', color: '#e6ffe6',
    }).setOrigin(0.5, 1).setDepth(10);

    const positions = [w * 0.2, w * 0.5, w * 0.8];
    STARTER_KEYS.forEach((key, i) => this.buildCard(positions[i], key));
  }

  buildCard(cx, speciesKey) {
    const h = this.scale.height;
    const species = SPECIES[speciesKey];

    this.add.circle(cx, h * 0.32, 24, species.color).setStrokeStyle(2, GB_PALETTE.darkest).setDepth(2);
    this.add.text(cx, h * 0.32 + 32, species.name, {
      fontFamily: 'monospace', fontSize: '9px', color: '#e6ffe6',
    }).setOrigin(0.5).setDepth(2);
    this.add.text(cx, h * 0.32 + 46, `HP ${species.hp}  ATK ${species.atk}  DEF ${species.def}`, {
      fontFamily: 'monospace', fontSize: '7px', color: '#c9dfae',
    }).setOrigin(0.5).setDepth(2);
    this.add.text(cx, h * 0.32 + 62, species.description, {
      fontFamily: 'monospace', fontSize: '7px', color: '#e6ffe6',
      align: 'center', wordWrap: { width: 130 },
    }).setOrigin(0.5, 0).setDepth(2);

    const btn = this.add.rectangle(cx, h * 0.88, 84, 26, GB_PALETTE.lightest)
      .setStrokeStyle(2, GB_PALETTE.darkest)
      .setInteractive({ useHandCursor: true })
      .setDepth(2);
    const txt = this.add.text(cx, h * 0.88, 'Elegir', {
      fontFamily: 'monospace', fontSize: '10px', color: '#0f380f',
    }).setOrigin(0.5).setDepth(3);
    btn.on('pointerdown', () => this.choose(speciesKey));
  }

  async choose(speciesKey) {
    const monster = makeMonsterInstance(speciesKey);
    this.statusText.setText('Guardando...');
    try {
      await fetch('api/save_game.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        credentials: 'include',
        body: JSON.stringify({
          mapKey: this.nextData.mapKey,
          x: this.nextData.x,
          y: this.nextData.y,
          party: [monster],
          inventory: this.nextData.inventory,
          csrf_token: window.BIFROST_CSRF_TOKEN,
        }),
      });
    } catch (err) {
      // Si falla el guardado, igual dejamos entrar; se reintentará con la
      // próxima vez que se guarde manualmente (tecla S).
    }
    this.scene.start('OverworldScene', { ...this.nextData, party: [monster] });
  }
}
