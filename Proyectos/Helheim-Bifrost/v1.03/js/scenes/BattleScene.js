/**
 * BattleScene.js — batalla contra una criatura salvaje.
 *
 * Todo el cálculo (daño, huida, desmayo) lo resuelve el servidor
 * (api/wild_battle_action.php), no esta escena — ítem 3 de
 * ROADMAP-ARQUITECTURA.md. Esta clase solo pide la acción elegida y
 * dibuja el resultado que responda el servidor; no decide ninguna regla
 * por su cuenta. Compárese con el diseño anterior (pre-30/08/2026), donde
 * el daño se calculaba acá mismo y cualquiera podía alterar el JS del
 * navegador para volverse invencible.
 */
class BattleScene extends Phaser.Scene {
  constructor() {
    super('BattleScene');
  }

  init(data) {
    this.battleId = data.battleId;
    this.you = data.you;
    this.enemy = data.enemy;
    this.initialMessage = data.message || '';
    this.returnData = data.returnData;
    this.turnLocked = false;
  }

  create() {
    const w = this.scale.width;
    const h = this.scale.height;

    this.add.rectangle(w / 2, h / 2, w, h, GB_PALETTE.light).setDepth(0);

    // Enemigo arriba a la derecha, jugador abajo a la izquierda (como en el original).
    this.enemySprite = this.add.circle(w * 0.72, h * 0.28, 22, this.enemy.color).setDepth(1);
    this.playerSprite = this.add.rectangle(w * 0.25, h * 0.68, 40, 40, this.you.color).setDepth(1);

    this.enemyLabel = this.makeLabel(w * 0.72, h * 0.1, this.enemy.name);
    this.playerLabel = this.makeLabel(w * 0.25, h * 0.5, this.you.name);

    this.enemyHpBar = this.makeHpBar(w * 0.55, h * 0.14);
    this.playerHpBar = this.makeHpBar(w * 0.08, h * 0.54);
    this.updateHpBars();

    this.logText = this.add.text(10, h - 54, this.initialMessage, {
      fontFamily: 'monospace',
      fontSize: '11px',
      color: '#0f380f',
      wordWrap: { width: w - 20 },
    }).setDepth(2);

    this.attackBtn = this.makeButton(w - 150, h - 30, 'Atacar', () => this.sendAction('attack'));
    this.runBtn = this.makeButton(w - 60, h - 30, 'Huir', () => this.sendAction('run'));
  }

  makeLabel(x, y, text) {
    return this.add.text(x, y, `${text}`, {
      fontFamily: 'monospace',
      fontSize: '10px',
      color: '#0f380f',
    }).setDepth(2);
  }

  makeHpBar(x, y) {
    const bg = this.add.rectangle(x, y, 100, 8, 0x306230).setOrigin(0, 0.5).setDepth(2);
    const fill = this.add.rectangle(x, y, 100, 8, 0x9bbc0f).setOrigin(0, 0.5).setDepth(3);
    return { bg, fill, x, y };
  }

  updateHpBars() {
    const enemyPct = Phaser.Math.Clamp(this.enemy.hp / this.enemy.maxHp, 0, 1);
    const playerPct = Phaser.Math.Clamp(this.you.hp / this.you.maxHp, 0, 1);
    this.enemyHpBar.fill.width = 100 * enemyPct;
    this.playerHpBar.fill.width = 100 * playerPct;
  }

  makeButton(x, y, label, onClick) {
    const btn = this.add.rectangle(x, y, 80, 26, GB_PALETTE.lightest)
      .setStrokeStyle(2, GB_PALETTE.darkest)
      .setDepth(2)
      .setInteractive({ useHandCursor: true });
    const txt = this.add.text(x, y, label, {
      fontFamily: 'monospace',
      fontSize: '10px',
      color: '#0f380f',
    }).setOrigin(0.5).setDepth(3);
    btn.on('pointerdown', onClick);
    return { btn, txt };
  }

  setButtonsEnabled(enabled) {
    this.turnLocked = !enabled;
    const alpha = enabled ? 1 : 0.4;
    [this.attackBtn, this.runBtn].forEach(({ btn, txt }) => {
      btn.setAlpha(alpha);
      txt.setAlpha(alpha);
    });
  }

  // Única función que le pide algo al servidor: la acción elegida
  // ("attack" o "run"). El servidor calcula toda la ronda (ataque propio,
  // contraataque si corresponde, desmayo, huida) y devuelve el resultado
  // ya resuelto — acá solo se muestra.
  async sendAction(action) {
    if (this.turnLocked) return;
    this.setButtonsEnabled(false);

    try {
      const res = await fetch('api/wild_battle_action.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        credentials: 'include',
        body: JSON.stringify({ battleId: this.battleId, action, csrf_token: window.BIFROST_CSRF_TOKEN }),
      });
      const data = await res.json();

      if (!res.ok || !data.ok) {
        this.logText.setText(data.error || 'No se pudo procesar la acción.');
        this.setButtonsEnabled(true);
        return;
      }

      this.you = data.you;
      this.enemy = data.enemy;
      this.updateHpBars();
      this.logText.setText(data.message || '');

      if (data.status === 'finished') {
        this.time.delayedCall(1000, () => this.closeBattle());
        return;
      }

      this.setButtonsEnabled(true);
    } catch (err) {
      this.logText.setText('No se pudo conectar con el servidor.');
      this.setButtonsEnabled(true);
    }
  }

  closeBattle() {
    // this.you ya tiene el HP final que el servidor guardó en
    // saves.party_json — se refleja en el snapshot antes de volver, para
    // que el pueblo muestre el mismo HP sin esperar a la próxima carga.
    if (this.returnData.party) {
      this.returnData.party[0] = this.you;
    }
    // Transición completa (no pausar/reanudar): el mismo mecanismo
    // confiable que usan los warps entre mapas.
    this.scene.start('OverworldScene', this.returnData);
  }
}
