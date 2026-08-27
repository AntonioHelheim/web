// Personaje jugable que se mueve tile a tile (como en el Pokémon original de GB).
// Usa un Container de Phaser para que el cuerpo y el indicador de dirección
// se muevan juntos sin lógica extra.
class Player {
  constructor(scene, tileX, tileY, tileSize) {
    this.scene = scene;
    this.tileSize = tileSize;
    this.tileX = tileX;
    this.tileY = tileY;
    this.isMoving = false;
    this.facing = 'down';

    const body = scene.add.rectangle(0, 0, tileSize * 0.7, tileSize * 0.7, GB_PALETTE.darkest);
    body.setStrokeStyle(2, GB_PALETTE.lightest);

    this.indicator = scene.add.rectangle(0, tileSize * 0.2, tileSize * 0.16, tileSize * 0.16, GB_PALETTE.lightest);

    this.container = scene.add.container(this.worldX, this.worldY, [body, this.indicator]);
    this.container.setDepth(10);
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
    const d = this.tileSize * 0.2;
    const offsets = { up: [0, -d], down: [0, d], left: [-d, 0], right: [d, 0] };
    const [ox, oy] = offsets[facing];
    this.indicator.setPosition(ox, oy);
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

    this.scene.tweens.add({
      targets: this.container,
      x: this.worldX,
      y: this.worldY,
      duration: 150,
      onComplete: () => {
        this.isMoving = false;
        if (onArrive) onArrive(this.tileX, this.tileY);
      },
    });
    return true;
  }
}
