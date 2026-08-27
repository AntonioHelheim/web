// Paletas de opciones para personalizar el personaje. Son colores propios,
// no ligados a ningún personaje con derechos de autor.
const SKIN_TONES = ['#ffe0bd', '#f1c27d', '#e0ac69', '#c68642', '#8d5524'];
const HAIR_COLORS = ['#2c1b18', '#4a2c1a', '#a8721b', '#e8c268', '#8b3a1a', '#5b5b5b', '#3a6ea5', '#4c9a2a'];
const EYE_COLORS = ['#3b2415', '#274b8f', '#2f6b3a', '#5b4636', '#7a7a7a'];

class CharacterCreationScene extends Phaser.Scene {
  constructor() {
    super('CharacterCreationScene');
  }

  init(data) {
    // Todo lo demás que necesitará OverworldScene al terminar aquí.
    this.nextData = data;
  }

  create() {
    this.skinIndex = 1;
    this.hairIndex = 0;
    this.eyeIndex = 0;
    this.appearance = {
      gender: 'boy',
      skinColor: SKIN_TONES[this.skinIndex],
      hairColor: HAIR_COLORS[this.hairIndex],
      eyeColor: EYE_COLORS[this.eyeIndex],
    };
    this.stepObjects = [];

    this.add.rectangle(this.scale.width / 2, this.scale.height / 2, this.scale.width, this.scale.height, GB_PALETTE.dark).setDepth(0);
    this.title = this.add.text(this.scale.width / 2, 16, '', {
      fontFamily: 'monospace', fontSize: '12px', color: '#e6ffe6',
    }).setOrigin(0.5, 0).setDepth(10);

    this.showGenderStep();
  }

  clearStep() {
    this.stepObjects.forEach((obj) => obj.destroy());
    this.stepObjects = [];
  }

  makeChoiceButton(x, y, label, onClick) {
    const btn = this.add.rectangle(x, y, 96, 34, GB_PALETTE.lightest)
      .setStrokeStyle(2, GB_PALETTE.darkest)
      .setInteractive({ useHandCursor: true })
      .setDepth(10);
    const txt = this.add.text(x, y, label, {
      fontFamily: 'monospace', fontSize: '11px', color: '#0f380f',
    }).setOrigin(0.5).setDepth(11);
    btn.on('pointerdown', onClick);
    return [btn, txt];
  }

  makeCyclerRow(y, label, palette, currentIndex, onChange) {
    const objs = [];
    objs.push(this.add.text(30, y, label, {
      fontFamily: 'monospace', fontSize: '10px', color: '#e6ffe6',
    }).setOrigin(0, 0.5).setDepth(10));

    const prevBtn = this.add.text(150, y, '<', {
      fontFamily: 'monospace', fontSize: '16px', color: '#e6ffe6',
    }).setOrigin(0.5).setInteractive({ useHandCursor: true }).setDepth(10);
    prevBtn.on('pointerdown', () => onChange((currentIndex - 1 + palette.length) % palette.length));
    objs.push(prevBtn);

    const swatchColor = Phaser.Display.Color.HexStringToColor(palette[currentIndex]).color;
    objs.push(this.add.rectangle(180, y, 26, 26, swatchColor).setStrokeStyle(2, GB_PALETTE.darkest).setDepth(10));

    const nextBtn = this.add.text(210, y, '>', {
      fontFamily: 'monospace', fontSize: '16px', color: '#e6ffe6',
    }).setOrigin(0.5).setInteractive({ useHandCursor: true }).setDepth(10);
    nextBtn.on('pointerdown', () => onChange((currentIndex + 1) % palette.length));
    objs.push(nextBtn);

    return objs;
  }

  showGenderStep() {
    this.clearStep();
    this.title.setText('¿Tu personaje es chico o chica?');

    this.stepObjects.push(...this.makeChoiceButton(this.scale.width * 0.3, this.scale.height * 0.55, 'Chico', () => {
      this.appearance.gender = 'boy';
      this.showCustomizeStep();
    }));
    this.stepObjects.push(...this.makeChoiceButton(this.scale.width * 0.7, this.scale.height * 0.55, 'Chica', () => {
      this.appearance.gender = 'girl';
      this.showCustomizeStep();
    }));
  }

  showCustomizeStep() {
    this.clearStep();
    this.title.setText('Personaliza tu personaje');

    const preview = buildCharacterVisual(this, TILE_SIZE * 2.4, this.appearance);
    preview.setPosition(this.scale.width / 2, this.scale.height * 0.35);
    this.stepObjects.push(preview);

    const rowY = [this.scale.height * 0.6, this.scale.height * 0.7, this.scale.height * 0.8];
    this.stepObjects.push(...this.makeCyclerRow(rowY[0], 'Piel', SKIN_TONES, this.skinIndex, (i) => {
      this.skinIndex = i;
      this.appearance.skinColor = SKIN_TONES[i];
      this.showCustomizeStep();
    }));
    this.stepObjects.push(...this.makeCyclerRow(rowY[1], 'Pelo', HAIR_COLORS, this.hairIndex, (i) => {
      this.hairIndex = i;
      this.appearance.hairColor = HAIR_COLORS[i];
      this.showCustomizeStep();
    }));
    this.stepObjects.push(...this.makeCyclerRow(rowY[2], 'Ojos', EYE_COLORS, this.eyeIndex, (i) => {
      this.eyeIndex = i;
      this.appearance.eyeColor = EYE_COLORS[i];
      this.showCustomizeStep();
    }));

    this.stepObjects.push(...this.makeChoiceButton(this.scale.width * 0.28, this.scale.height * 0.93, 'Atrás', () => {
      this.showGenderStep();
    }));
    this.stepObjects.push(...this.makeChoiceButton(this.scale.width * 0.72, this.scale.height * 0.93, 'Confirmar', () => {
      this.confirm();
    }));
  }

  async confirm() {
    try {
      await fetch('api/save_appearance.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        credentials: 'include',
        body: JSON.stringify(this.appearance),
      });
    } catch (err) {
      // Si falla el guardado, igual dejamos entrar; podrá reintentarse después.
    }
    this.scene.start('OverworldScene', { ...this.nextData, appearance: this.appearance, characterCreated: true });
  }
}
