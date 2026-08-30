class BattleScene extends Phaser.Scene {
  constructor() {
    super('BattleScene');
  }

  init(data) {
    this.playerMon = data.playerMonster;
    this.enemyMon = data.enemyMonster;
    this.returnData = data.returnData;
    this.turnLocked = false;
  }

  create() {
    const w = this.scale.width;
    const h = this.scale.height;

    this.add.rectangle(w / 2, h / 2, w, h, GB_PALETTE.light).setDepth(0);

    // Enemigo arriba a la derecha, jugador abajo a la izquierda (como en el original).
    this.enemySprite = this.add.circle(w * 0.72, h * 0.28, 22, this.enemyMon.color).setDepth(1);
    this.playerSprite = this.add.rectangle(w * 0.25, h * 0.68, 40, 40, this.playerMon.color).setDepth(1);

    this.enemyLabel = this.makeLabel(w * 0.72, h * 0.1, this.enemyMon.name);
    this.playerLabel = this.makeLabel(w * 0.25, h * 0.5, this.playerMon.name);

    this.enemyHpBar = this.makeHpBar(w * 0.55, h * 0.14);
    this.playerHpBar = this.makeHpBar(w * 0.08, h * 0.54);
    this.updateHpBars();

    this.logText = this.add.text(10, h - 54, `Un ${this.enemyMon.name} salvaje apareció.`, {
      fontFamily: 'monospace',
      fontSize: '11px',
      color: '#0f380f',
      wordWrap: { width: w - 20 },
    }).setDepth(2);

    this.attackBtn = this.makeButton(w - 150, h - 30, 'Atacar', () => this.playerAttack());
    this.runBtn = this.makeButton(w - 60, h - 30, 'Huir', () => this.tryRun());
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
    const enemyPct = Phaser.Math.Clamp(this.enemyMon.hp / this.enemyMon.maxHp, 0, 1);
    const playerPct = Phaser.Math.Clamp(this.playerMon.hp / this.playerMon.maxHp, 0, 1);
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
  }

  playerAttack() {
    if (this.turnLocked) return;
    this.setButtonsEnabled(false);

    // La fórmula de daño vive en battleRules.js (sin depender de Phaser);
    // acá solo le pasamos el generador aleatorio de Phaser para que se
    // sienta consistente con el resto del juego.
    const dmg = calculateDamage(this.playerMon, this.enemyMon, (min, max) => Phaser.Math.Between(min, max));
    this.enemyMon.hp = applyDamage(this.enemyMon, dmg);
    this.logText.setText(`${this.playerMon.name} ataca. ${dmg} de daño.`);
    this.updateHpBars();

    if (isFainted(this.enemyMon)) {
      this.time.delayedCall(700, () => this.endBattle(true));
      return;
    }

    this.time.delayedCall(700, () => this.enemyAttack());
  }

  enemyAttack() {
    const dmg = calculateDamage(this.enemyMon, this.playerMon, (min, max) => Phaser.Math.Between(min, max));
    this.playerMon.hp = applyDamage(this.playerMon, dmg);
    this.logText.setText(`${this.enemyMon.name} contraataca. ${dmg} de daño.`);
    this.updateHpBars();

    if (isFainted(this.playerMon)) {
      this.time.delayedCall(700, () => this.endBattle(false));
      return;
    }

    this.setButtonsEnabled(true);
  }

  tryRun() {
    if (this.turnLocked) return;
    this.setButtonsEnabled(false);
    const escaped = attemptEscape();
    if (escaped) {
      this.logText.setText('Escapaste con éxito.');
      this.time.delayedCall(500, () => this.closeBattle());
    } else {
      this.logText.setText('¡No pudiste escapar!');
      this.time.delayedCall(600, () => this.enemyAttack());
    }
  }

  endBattle(playerWon) {
    if (playerWon) {
      this.logText.setText(`¡${this.enemyMon.name} fue derrotado!`);
    } else {
      // "Desmayo" estilo clásico: se recupera un poco de HP y vuelve al pueblo.
      this.playerMon.hp = faintRecoveryHp(this.playerMon);
      this.logText.setText(`${this.playerMon.name} no puede continuar...`);
    }
    this.time.delayedCall(1000, () => this.closeBattle());
  }

  closeBattle() {
    // this.playerMon ES this.returnData.party[0] (misma referencia), así
    // que el HP actualizado en batalla ya quedó reflejado ahí. Transición
    // completa (no pausar/reanudar): es el mismo mecanismo confiable que
    // usan los warps entre mapas.
    this.scene.start('OverworldScene', this.returnData);
  }
}
