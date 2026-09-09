// Personaje jugable que se mueve tile a tile (como en los RPG clásicos de GB).
// El cuerpo visual viene de buildCharacterVisual() (ver CharacterVisual.js);
// aquí solo se maneja el movimiento y el pequeño indicador de dirección.
class Player {
  constructor(scene, tileX, tileY, tileSize, appearance) {
    this.scene = scene;
    this.tileSize = tileSize;
    this.tileX = tileX;
    this.tileY = tileY;
    this.isMoving = false;
    this.facing = 'down';
    this.appearance = appearance || {
      gender: 'boy', preset: 1, skinColor: '#f1c27d', hairColor: '#2c1b18', eyeColor: '#3b2415',
    };

    const visual = this.buildVisual(this.appearance, 'down');
    this.visual = visual;
    this.indicator = scene.add.rectangle(0, tileSize * 0.34, tileSize * 0.14, tileSize * 0.14, GB_PALETTE.lightest);

    this.container = scene.add.container(this.worldX, this.worldY, [visual, this.indicator]);
    this.container.setDepth(10);

    // Respiración sutil e infinita mientras no camina — sin esto el
    // personaje se veía completamente inmóvil entre pasos. Anima el
    // contenedor exterior (no `visual`), así que nunca compite con los
    // tweens del paso, que solo tocan `visual` y las piernas/brazos.
    this.idleTween = scene.tweens.add({
      targets: this.container,
      scaleY: 1.015,
      duration: 900,
      yoyo: true,
      repeat: -1,
      ease: 'Sine.easeInOut',
    });
  }

  // Crea el cuerpo visual: sprite real si ya está cargado para esta
  // combinación género+preset (ver PLAN-GRAPHICS-AUDIO.md), si no el
  // dibujo a mano de siempre (buildCharacterVisual). Actualiza
  // this.spriteKey/this.usingRealSprite para que el resto de los métodos
  // sepan cuál de los dos caminos seguir.
  buildVisual(appearance, facing) {
    this.spriteKey = spriteKeyForAppearance(appearance);
    this.usingRealSprite = this.scene.textures.exists(this.spriteKey);
    const escala = scaleForGender(appearance.gender);
    console.info(
      `Bifrost: apariencia solicitada género=${appearance.gender} preset=${appearance.preset} `
      + `-> clave de sprite "${this.spriteKey}" -> ${this.usingRealSprite ? 'usando sprite REAL' : 'usando DIBUJO A MANO (sin sprite real cargado para esta combinación)'} (escala ${escala}x)`
    );

    if (this.usingRealSprite) {
      const frame = this.scene.textures.get(this.spriteKey).get(0);
      // El sprite puede ser más alto que el tile (normal en este tipo de
      // arte — la cabeza asoma por arriba) — se centra el sobrante hacia
      // arriba para que los pies queden alineados con la base del tile.
      // Con escala != 1 (personajes masculinos, ver scaleForGender), el
      // sprite crece/encoge desde su propio centro — se ajusta el
      // offset para que los PIES sigan en el mismo lugar tras escalar
      // (que crezca hacia arriba, no que se hunda en el piso).
      const yOffsetBase = (this.tileSize - frame.height) / 2;
      const yOffset = yOffsetBase + (frame.height / 2) * (1 - escala);
      const sprite = this.scene.add.sprite(0, yOffset, this.spriteKey, idleFrameForDirection(facing));
      sprite.setScale(escala);
      return sprite;
    }
    const visual = buildCharacterVisual(this.scene, this.tileSize, appearance);
    visual.setScale(escala);
    applyFacingToVisual(visual, facing);
    return visual;
  }

  // Reconstruye solo la parte visual (cuerpo/pelo/ojos, o el sprite real)
  // sin tocar posición, indicador de dirección ni la etiqueta de nombre —
  // se usa al cambiar la apariencia desde el menú en pleno juego.
  setAppearance(appearance) {
    this.appearance = appearance;
    this.visual.destroy(); // se quita solo del container al destruirse
    this.visual = this.buildVisual(appearance, this.facing);
    this.container.addAt(this.visual, 0); // al fondo, detrás del indicador y la etiqueta
  }

  get worldX() {
    return this.tileX * this.tileSize + this.tileSize / 2;
  }

  get worldY() {
    return this.tileY * this.tileSize + this.tileSize / 2;
  }

  canMove() {
    return !this.isMoving;
  }

  setFacing(facing) {
    this.facing = facing;
    const d = this.tileSize * 0.3;
    const offsets = { up: [0, -d], down: [0, d], left: [-d, 0], right: [d, 0] };
    const [ox, oy] = offsets[facing];
    this.indicator.setPosition(ox, oy);

    if (this.usingRealSprite) {
      this.visual.anims.stop();
      this.visual.setFrame(idleFrameForDirection(facing));
    } else {
      applyFacingToVisual(this.visual, facing);
    }
  }

  // Snap inmediato sin animación (usado al cargar una partida guardada).
  teleport(tileX, tileY) {
    this.tileX = tileX;
    this.tileY = tileY;
    this.container.setPosition(this.worldX, this.worldY);
  }

  // Intenta moverse un tile en la dirección (dx, dy).
  // isBlocked(x, y) -> bool decide si el tile destino es transitable.
  // onArrive(x, y) se llama al terminar la animación de movimiento.
  move(dx, dy, isBlocked, onArrive) {
    if (!this.canMove() || (dx === 0 && dy === 0)) return false;

    if (dx === 1) this.setFacing('right');
    else if (dx === -1) this.setFacing('left');
    else if (dy === 1) this.setFacing('down');
    else if (dy === -1) this.setFacing('up');

    const targetX = this.tileX + dx;
    const targetY = this.tileY + dy;
    if (isBlocked(targetX, targetY)) return false;

    this.isMoving = true;
    this.tileX = targetX;
    this.tileY = targetY;

    this.animateWalkStep();

    this.scene.tweens.add({
      targets: this.container,
      x: this.worldX,
      y: this.worldY,
      duration: 150,
      onComplete: () => {
        this.isMoving = false;
        if (this.usingRealSprite) {
          this.visual.anims.stop();
          this.visual.setFrame(idleFrameForDirection(this.facing));
        }
        if (onArrive) onArrive(this.tileX, this.tileY);
      },
    });
    return true;
  }

  // Simula el paso al caminar: el cuerpo rebota y se "aplasta/estira" un
  // poco (más peso que un simple vaivén), las piernas se mueven en tijera y
  // los brazos se balancean al contrario de las piernas — como al caminar
  // de verdad. El indicador de dirección rebota junto con el cuerpo, y cae
  // una pequeña nube de polvo bajo el pie que pisa.
  animateWalkStep() {
    this.stepPhase = this.stepPhase === 'left' ? 'right' : 'left';

    // Sprite real: reproducir la animación de caminata de la dirección
    // actual (las 4 direcciones ya quedaron registradas en PreloadScene
    // vía defineCharacterAnimations). El resto de esta función es todo el
    // detalle del dibujo a mano (piernas, brazos, pelo) — no aplica acá.
    if (this.usingRealSprite) {
      this.visual.play(`${this.spriteKey}_${this.facing}`, true);
      this.scene.time.delayedCall(110, () => this.spawnFootDust());
      return;
    }

    const ts = this.tileSize;
    const scene = this.scene;
    const bob = ts * 0.1;
    const lean = this.stepPhase === 'left' ? -3.5 : 3.5;

    scene.tweens.add({
      targets: this.visual,
      y: -bob,
      scaleX: 0.93,
      scaleY: 1.08,
      angle: lean,
      duration: 75,
      yoyo: true,
      ease: 'Sine.easeOut',
    });

    // El indicador no es hijo de `visual`, así que se anima aparte con un
    // desplazamiento relativo (mantiene su posición según hacia dónde mira).
    scene.tweens.add({
      targets: this.indicator,
      y: `-=${bob}`,
      duration: 75,
      yoyo: true,
      ease: 'Sine.easeOut',
    });

    const { legLeft, legRight, armLeft, armRight } = this.visual;
    const legSwing = ts * 0.085;
    const armSwing = ts * 0.07;

    const forwardLeg = this.stepPhase === 'left' ? legLeft : legRight;
    const backLeg = this.stepPhase === 'left' ? legRight : legLeft;
    if (forwardLeg && backLeg) {
      scene.tweens.add({ targets: forwardLeg, y: forwardLeg.y - legSwing, duration: 75, yoyo: true, ease: 'Sine.easeOut' });
      scene.tweens.add({ targets: backLeg, y: backLeg.y + legSwing, duration: 75, yoyo: true, ease: 'Sine.easeOut' });
    }

    // Los brazos van al contrario que las piernas (brazo derecho adelanta
    // con pierna izquierda, y viceversa).
    const forwardArm = this.stepPhase === 'left' ? armRight : armLeft;
    const backArm = this.stepPhase === 'left' ? armLeft : armRight;
    if (forwardArm && backArm) {
      scene.tweens.add({ targets: forwardArm, y: forwardArm.y - armSwing, duration: 75, yoyo: true, ease: 'Sine.easeOut' });
      scene.tweens.add({ targets: backArm, y: backArm.y + armSwing, duration: 75, yoyo: true, ease: 'Sine.easeOut' });
    }

    // Pelo largo y lazo (si el personaje los tiene) se balancean un poco
    // al caminar, con un ligero retraso respecto al cuerpo — da sensación
    // de que el pelo "sigue" el movimiento en vez de ir pegado y rígido.
    const hairSway = ts * 0.045;
    const swayDir = this.stepPhase === 'left' ? -1 : 1;
    const { longHair, bow } = this.visual;
    if (longHair) {
      scene.tweens.add({
        targets: longHair, x: swayDir * hairSway, angle: swayDir * 4,
        duration: 90, delay: 20, yoyo: true, ease: 'Sine.easeOut',
      });
    }
    if (bow) {
      scene.tweens.add({
        targets: bow, x: bow.x + swayDir * hairSway * 0.6,
        duration: 90, delay: 20, yoyo: true, ease: 'Sine.easeOut',
      });
    }

    // La nube de polvo aparece al final del paso (cuando el pie "toca
    // suelo"), no al inicio — por eso el pequeño retraso.
    scene.time.delayedCall(110, () => this.spawnFootDust());
  }

  // Pequeña nube que aparece bajo el pie que pisa y se desvanece rápido —
  // le da al paso una sensación de contacto real con el suelo.
  spawnFootDust() {
    const ts = this.tileSize;
    const scene = this.scene;
    const sideOffset = this.stepPhase === 'left' ? -ts * 0.13 : ts * 0.13;
    const dust = scene.add.ellipse(
      this.container.x + sideOffset,
      this.container.y + ts * 0.46,
      ts * 0.16,
      ts * 0.08,
      0xe6ffe6
    ).setAlpha(0.3).setDepth(8);

    scene.tweens.add({
      targets: dust,
      scaleX: 2.1,
      scaleY: 1.7,
      y: dust.y + ts * 0.03,
      alpha: 0,
      duration: 260,
      ease: 'Sine.easeOut',
      onComplete: () => dust.destroy(),
    });
  }
}
