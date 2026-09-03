// La tabla de combinaciones (APPEARANCE_PRESETS) vive en CharacterVisual.js
// (se carga antes que este archivo) — un solo lugar para no duplicarla.

class CharacterCreationScene extends Phaser.Scene {
  constructor() {
    super('CharacterCreationScene');
  }

  init(data) {
    // Todo lo demás que necesitará OverworldScene al terminar aquí.
    this.nextData = data;
    // editMode: true cuando se abre desde el menú en pleno juego, en vez
    // de la primera vez que se crea el personaje.
    this.editMode = !!data.editMode;
    this.initialAppearance = data.appearance || null;
    this.returnData = data.returnData || null; // mapa/posición/equipo para volver, en modo edición
  }

  create() {
    const seed = this.initialAppearance || { gender: 'boy', preset: 1 };
    this.appearance = {
      gender: seed.gender === 'girl' ? 'girl' : 'boy',
      preset: seed.preset >= 1 && seed.preset <= 3 ? seed.preset : 1,
    };
    Object.assign(this.appearance, APPEARANCE_PRESETS[this.appearance.gender][this.appearance.preset - 1]);

    this.stepObjects = [];
    this.transitioning = false;

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

  showGenderStep() {
    this.clearStep();
    this.title.setText('¿Tu personaje es chico o chica?');

    this.stepObjects.push(...this.makeChoiceButton(this.scale.width * 0.3, this.scale.height * 0.55, 'Chico', () => {
      this.appearance.gender = 'boy';
      this.appearance.preset = 1;
      Object.assign(this.appearance, APPEARANCE_PRESETS.boy[0]);
      this.showPresetStep();
    }));
    this.stepObjects.push(...this.makeChoiceButton(this.scale.width * 0.7, this.scale.height * 0.55, 'Chica', () => {
      this.appearance.gender = 'girl';
      this.appearance.preset = 1;
      Object.assign(this.appearance, APPEARANCE_PRESETS.girl[0]);
      this.showPresetStep();
    }));

    if (this.editMode) {
      this.stepObjects.push(...this.makeChoiceButton(this.scale.width * 0.5, this.scale.height * 0.8, 'Cancelar', () => {
        this.cancelEdit();
      }));
    }
  }

  // Muestra las 3 opciones preestablecidas del género elegido — ya no hay
  // color libre. Mientras no tengamos los sprites reales cargados, cada
  // opción se ve como una combinación de colores distinta (ver
  // APPEARANCE_PRESETS en CharacterVisual.js) — salvo que ya exista el
  // sprite real de esa combinación (ver spriteKeyForAppearance), en cuyo
  // caso se muestra ese directamente.
  showPresetStep() {
    this.clearStep();
    this.title.setText('Elige tu apariencia');

    const presets = APPEARANCE_PRESETS[this.appearance.gender];
    const count = presets.length;
    const spacing = this.scale.width / (count + 1);
    const previewY = this.scale.height * 0.4;

    presets.forEach((colors, i) => {
      const presetNum = i + 1;
      const x = spacing * (i + 1);
      const isSelected = this.appearance.preset === presetNum;

      const frame = this.add.rectangle(x, previewY, 74, 74, 0x000000, 0)
        .setStrokeStyle(isSelected ? 3 : 1, isSelected ? 0xe6ffe6 : GB_PALETTE.light)
        .setDepth(9);
      this.stepObjects.push(frame);

      const previewAppearance = { gender: this.appearance.gender, preset: presetNum, ...colors };
      const spriteKey = spriteKeyForAppearance(previewAppearance);
      const escala = scaleForGender(this.appearance.gender); // mismo detalle visual que en el juego
      let preview;
      if (this.textures.exists(spriteKey)) {
        // Sprite real ya cargado para esta opción: se muestra tal cual,
        // en pose de reposo mirando de frente (cuadro 0).
        preview = this.add.sprite(x, previewY, spriteKey, 0).setScale(1.8 * escala);
      } else {
        preview = buildCharacterVisual(this, TILE_SIZE * 1.7, previewAppearance);
        preview.setPosition(x, previewY);
        preview.setScale(escala);
      }
      this.stepObjects.push(preview);

      const label = isSelected ? `✓ Opción ${presetNum}` : `Opción ${presetNum}`;
      this.stepObjects.push(...this.makeChoiceButton(x, this.scale.height * 0.6, label, () => {
        this.appearance.preset = presetNum;
        Object.assign(this.appearance, colors);
        this.showPresetStep();
      }));
    });

    if (this.editMode) {
      this.stepObjects.push(...this.makeChoiceButton(this.scale.width * 0.18, this.scale.height * 0.93, 'Atrás', () => {
        this.showGenderStep();
      }));
      this.stepObjects.push(...this.makeChoiceButton(this.scale.width * 0.5, this.scale.height * 0.93, 'Cancelar', () => {
        this.cancelEdit();
      }));
      this.stepObjects.push(...this.makeChoiceButton(this.scale.width * 0.82, this.scale.height * 0.93, 'Confirmar', () => {
        this.confirm();
      }));
    } else {
      this.stepObjects.push(...this.makeChoiceButton(this.scale.width * 0.28, this.scale.height * 0.93, 'Atrás', () => {
        this.showGenderStep();
      }));
      this.stepObjects.push(...this.makeChoiceButton(this.scale.width * 0.72, this.scale.height * 0.93, 'Confirmar', () => {
        this.confirm();
      }));
    }
  }

  cancelEdit() {
    if (this.transitioning) return;
    this.transitioning = true;
    // Transición completa (no pausar/reanudar): mismo mecanismo confiable
    // que usan los warps entre mapas. Apariencia original, sin cambios.
    this.scene.start('OverworldScene', { ...this.returnData, appearance: this.initialAppearance });
  }

  async confirm() {
    if (this.transitioning) return; // evita doble-envío si se hace doble clic en "Confirmar"
    this.transitioning = true;

    try {
      // Solo se manda género + número de opción — el servidor resuelve
      // los colores por su cuenta (mismo espíritu que "el servidor no
      // confía en datos críticos del cliente", ROADMAP-ARQUITECTURA.md).
      const res = await fetch('api/save_appearance.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        credentials: 'include',
        body: JSON.stringify({
          gender: this.appearance.gender,
          preset: this.appearance.preset,
          csrf_token: window.BIFROST_CSRF_TOKEN,
        }),
      });
      const data = await res.json().catch(() => ({}));
      if (!res.ok || !data.ok) {
        // No bloqueamos al jugador por esto (puede reintentar guardando
        // manualmente más tarde), pero antes esto fallaba en silencio —
        // sin ningún rastro de por qué la próxima sesión iba a volver a
        // pedir crear personaje. Ahora al menos queda en la consola.
        console.warn(
          `Bifrost: no se pudo guardar la apariencia (HTTP ${res.status}). `
          + 'La próxima vez que inicies sesión puede volver a pedirte elegir personaje. '
          + `Respuesta: ${JSON.stringify(data)}`
        );
      } else {
        console.info('Bifrost: apariencia guardada correctamente en el servidor.');
      }
    } catch (err) {
      console.warn(
        'Bifrost: fallo de red al guardar la apariencia — la próxima vez que inicies '
        + 'sesión puede volver a pedirte elegir personaje.', err
      );
    }

    if (this.editMode) {
      this.scene.start('OverworldScene', { ...this.returnData, appearance: this.appearance });
      return;
    }

    const next = { ...this.nextData, appearance: this.appearance, characterCreated: true };
    this.scene.start(decideNextScene(next), next);
  }
}
