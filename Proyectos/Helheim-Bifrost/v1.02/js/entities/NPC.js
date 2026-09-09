// js/entities/NPC.js
//
// Personaje no jugable con comportamiento ambiental simple: se queda
// quieto un rato (IDLE) y de vez en cuando da un paso hacia un tile
// cercano transitable (PATROL), sin alejarse mucho de su punto de
// origen (`wanderRadius`). Primera implementación real de una máquina
// de estados para NPCs (ítem 7 de ROADMAP-ARQUITECTURA.md) —
// deliberadamente simple (2 estados) porque todavía no hay diálogo ni
// interacción con el jugador; por ahora solo da vida ambiental al mapa.
//
// Reutiliza el mismo sistema de sprites reales con respaldo automático
// que ya usa Player.js: si `spriteKey` tiene una textura real cargada
// (ver PEOPLE_SPRITE_COMBOS en CharacterVisual.js) se anima con ella; si
// no, cae al dibujo a mano (buildCharacterVisual) sin romper nada.
const NPC_STATE = { IDLE: 'idle', PATROL: 'patrol' };

class NPC {
  constructor(scene, mapKey, homeX, homeY, spriteKey, wanderRadius = 3) {
    this.scene = scene;
    this.mapKey = mapKey;
    this.homeX = homeX;
    this.homeY = homeY;
    this.tileX = homeX;
    this.tileY = homeY;
    this.spriteKey = spriteKey;
    this.wanderRadius = wanderRadius;
    this.facing = 'down';
    this.state = NPC_STATE.IDLE;
    this.isMoving = false;
    this.destroyed = false;

    // Mismo detalle visual que Player.js: apariencia masculina 10% más
    // grande. Los NPCs no tienen un objeto "appearance" con .gender, solo
    // la spriteKey ya resuelta — se infiere del nombre (scaleForSpriteKey).
    const escala = scaleForSpriteKey(spriteKey);

    this.usingRealSprite = scene.textures.exists(spriteKey);
    if (this.usingRealSprite) {
      const frame = scene.textures.get(spriteKey).get(0);
      const yOffsetBase = (TILE_SIZE - frame.height) / 2;
      const yOffset = yOffsetBase + (frame.height / 2) * (1 - escala);
      this.visual = scene.add.sprite(0, yOffset, spriteKey, idleFrameForDirection(this.facing));
      this.visual.setScale(escala);
    } else {
      // Respaldo: silueta dibujada a mano con una apariencia neutra fija
      // (los NPCs ambientales no tienen "género elegido" como el
      // jugador, solo necesitan verse como una persona genérica).
      this.visual = buildCharacterVisual(scene, TILE_SIZE, {
        gender: 'boy', skinColor: '#c68642', hairColor: '#4a2c1a', eyeColor: '#5b4636',
      });
      applyFacingToVisual(this.visual, this.facing);
      this.visual.setScale(escala);
    }

    this.container = scene.add.container(
      homeX * TILE_SIZE + TILE_SIZE / 2,
      homeY * TILE_SIZE + TILE_SIZE / 2,
      [this.visual]
    );
    // Profundidad menor que el jugador (10) — si se solapan, el jugador
    // siempre se dibuja encima.
    this.container.setDepth(8);

    this.scheduleNextAction();
  }

  // Espera un rato en IDLE antes de intentar el próximo paso — el rango
  // de tiempo (1.5-4s) es a propósito irregular para que varios NPCs no
  // se muevan todos sincronizados al mismo tiempo.
  scheduleNextAction() {
    if (this.destroyed) return;
    const delay = 1500 + Math.random() * 2500;
    this.scene.time.delayedCall(delay, () => this.tryStep());
  }

  // Intenta dar un paso a una de las 4 direcciones, elegida al azar
  // entre las que sean válidas: transitable (isTileBlocked) y dentro del
  // radio de merodeo respecto al punto de origen. Si ninguna dirección
  // es válida (poco probable, pero posible si el radio es muy chico),
  // simplemente se queda en IDLE y reintenta más tarde.
  tryStep() {
    if (this.destroyed) return;
    if (this.isMoving) { this.scheduleNextAction(); return; }

    const direcciones = [
      [0, -1, 'up'], [0, 1, 'down'], [-1, 0, 'left'], [1, 0, 'right'],
    ];
    const opciones = direcciones.filter(([dx, dy]) => {
      const nx = this.tileX + dx;
      const ny = this.tileY + dy;
      if (isTileBlocked(this.mapKey, nx, ny)) return false;
      if (Math.abs(nx - this.homeX) > this.wanderRadius) return false;
      if (Math.abs(ny - this.homeY) > this.wanderRadius) return false;
      return true;
    });

    if (opciones.length === 0) {
      this.scheduleNextAction();
      return;
    }

    const [dx, dy, facing] = opciones[Math.floor(Math.random() * opciones.length)];
    this.facing = facing;
    this.state = NPC_STATE.PATROL;
    this.isMoving = true;

    if (this.usingRealSprite) {
      this.visual.play(`${this.spriteKey}_${facing}`, true);
    } else {
      applyFacingToVisual(this.visual, facing);
    }

    this.tileX += dx;
    this.tileY += dy;

    // Más lento que el jugador (150ms) — se siente ambiental, no apurado.
    this.scene.tweens.add({
      targets: this.container,
      x: this.tileX * TILE_SIZE + TILE_SIZE / 2,
      y: this.tileY * TILE_SIZE + TILE_SIZE / 2,
      duration: 300,
      onComplete: () => {
        this.isMoving = false;
        this.state = NPC_STATE.IDLE;
        if (this.usingRealSprite) {
          this.visual.anims.stop();
          this.visual.setFrame(idleFrameForDirection(this.facing));
        }
        this.scheduleNextAction();
      },
    });
  }

  destroy() {
    this.destroyed = true;
    this.container.destroy();
  }
}
