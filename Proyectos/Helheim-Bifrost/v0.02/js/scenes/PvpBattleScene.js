const PVP_POLL_MS = 1000;

class PvpBattleScene extends Phaser.Scene {
  constructor() {
    super('PvpBattleScene');
  }

  init(data) {
    this.battleId = data.battleId;
    this.returnData = data.returnData;
    this.actionLocked = true;
    this.finished = false;
  }

  create() {
    const w = this.scale.width;
    const h = this.scale.height;

    this.add.rectangle(w / 2, h / 2, w, h, GB_PALETTE.light).setDepth(0);

    this.enemySprite = this.add.circle(w * 0.72, h * 0.28, 22, 0x888888).setDepth(1);
    this.playerSprite = this.add.rectangle(w * 0.25, h * 0.68, 40, 40, 0x888888).setDepth(1);

    this.enemyLabel = this.makeLabel(w * 0.72, h * 0.1, '...');
    this.playerLabel = this.makeLabel(w * 0.25, h * 0.5, '...');

    this.enemyHpBar = this.makeHpBar(w * 0.55, h * 0.14);
    this.playerHpBar = this.makeHpBar(w * 0.08, h * 0.54);

    this.logText = this.add.text(10, h - 54, 'Conectando con la batalla...', {
      fontFamily: 'monospace',
      fontSize: '11px',
      color: '#0f380f',
      wordWrap: { width: w - 20 },
    }).setDepth(2);

    this.attackBtn = this.makeButton(w - 150, h - 30, 'Atacar', () => this.sendAction('attack'));
    this.runBtn = this.makeButton(w - 60, h - 30, 'Huir', () => this.sendAction('run'));
    this.setButtonsEnabled(false);

    this.pollTimer = this.time.addEvent({ delay: PVP_POLL_MS, loop: true, callback: () => this.refreshState() });
    this.refreshState();

    this.events.on('shutdown', () => { if (this.pollTimer) this.pollTimer.remove(); });
  }

  makeLabel(x, y, text) {
    return this.add.text(x, y, text, { fontFamily: 'monospace', fontSize: '10px', color: '#0f380f' }).setDepth(2);
  }

  makeHpBar(x, y) {
    const bg = this.add.rectangle(x, y, 100, 8, 0x306230).setOrigin(0, 0.5).setDepth(2);
    const fill = this.add.rectangle(x, y, 100, 8, 0x9bbc0f).setOrigin(0, 0.5).setDepth(3);
    return { bg, fill };
  }

  updateHpBars(you, opponent) {
    this.playerHpBar.fill.width = 100 * Phaser.Math.Clamp(you.hp / you.maxHp, 0, 1);
    this.enemyHpBar.fill.width = 100 * Phaser.Math.Clamp(opponent.hp / opponent.maxHp, 0, 1);
  }

  makeButton(x, y, label, onClick) {
    const btn = this.add.rectangle(x, y, 80, 26, GB_PALETTE.lightest)
      .setStrokeStyle(2, GB_PALETTE.darkest)
      .setDepth(2)
      .setInteractive({ useHandCursor: true });
    const txt = this.add.text(x, y, label, {
      fontFamily: 'monospace', fontSize: '10px', color: '#0f380f',
    }).setOrigin(0.5).setDepth(3);
    btn.on('pointerdown', onClick);
    return { btn, txt };
  }

  setButtonsEnabled(enabled) {
    this.actionLocked = !enabled;
    const alpha = enabled ? 1 : 0.4;
    [this.attackBtn, this.runBtn].forEach(({ btn, txt }) => {
      btn.setAlpha(alpha);
      txt.setAlpha(alpha);
    });
  }

  async refreshState() {
    if (this.finished) return;
    try {
      const res = await fetch(`api/pvp_battle_state.php?battleId=${this.battleId}`, { credentials: 'include' });
      if (!res.ok) return;
      const data = await res.json();
      if (!this.sys.isActive()) return;
      this.applyState(data);
    } catch (err) {
      // Se reintenta en el siguiente sondeo.
    }
  }

  applyState(data) {
    this.playerLabel.setText(`${data.you.name}  HP ${data.you.hp}/${data.you.maxHp}`);
    this.enemyLabel.setText(`${data.opponent.name}  HP ${data.opponent.hp}/${data.opponent.maxHp}`);
    this.playerSprite.fillColor = data.you.color;
    this.enemySprite.fillColor = data.opponent.color;
    this.updateHpBars(data.you, data.opponent);
    this.logText.setText(data.lastAction || '');

    if (data.status === 'finished') {
      this.finished = true;
      if (this.pollTimer) this.pollTimer.remove();
      this.setButtonsEnabled(false);
      const resultMsg = data.winner === 'you'
        ? '¡Ganaste la batalla!'
        : data.winner === 'opponent'
          ? 'Perdiste la batalla.'
          : 'La batalla terminó.';
      this.logText.setText(`${data.lastAction || ''}  ${resultMsg}`);
      this.time.delayedCall(1800, () => this.closeBattle());
      return;
    }

    this.setButtonsEnabled(data.yourTurn);
  }

  async sendAction(action) {
    if (this.actionLocked) return;
    this.setButtonsEnabled(false);
    try {
      await fetch('api/battle_action.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        credentials: 'include',
        body: JSON.stringify({ battleId: this.battleId, action }),
      });
    } catch (err) {
      // El siguiente refreshState reflejará el estado real del servidor de todas formas.
    }
    this.refreshState();
  }

  closeBattle() {
    // Transición completa (no pausar/reanudar): el mismo mecanismo
    // confiable que usan los warps entre mapas.
    this.scene.start('OverworldScene', this.returnData);
  }
}
