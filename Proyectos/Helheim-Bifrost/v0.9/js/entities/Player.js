// Personaje jugable que se mueve tile a tile (como en el Pokémon original de GB).
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
      gender: 'boy', skinColor: '#f1c27d', hairColor: '#2c1b18', eyeColor: '#3b2415',
    };

    const visual = buildCharacterVisual(scene, tileSize, this.appearance);
    this.visual = visual;
    this.indicator = scene.add.rectangle(0, tileSize * 0.34, tileSize * 0.14, tileSize * 0.14, GB_PALETTE.lightest);

    this.container = scene.add.container(this.worldX, this.worldY, [visual, this.indicator]);
    this.container.setDepth(10);
  }

  // Reconstruye solo la parte visual (cuerpo/pelo/ojos) sin tocar posición,
  // indicador de dirección ni la etiqueta de nombre — se usa al cambiar la
  // apariencia desde el menú en pleno juego.
  setAppearance(appearance) {
    this.appearance = appearance;
    this.visual.destroy(); // se quita solo del container al destruirse
    this.visual = buildCharacterVisual(this.scene, this.tileSize, appearance);
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
        if (onArrive) onArrive(this.tileX, this.tileY);
      },
    });
    return true;
  }

  // Simula el paso al caminar: el cuerpo rebota y se "aplasta/estira" un
  // poco (más peso que un simple vaivén), las piernas se mueven en tijera y
  // los brazos se balancean al contrario de las piernas — como al caminar
  // de verdad. El indicador de dirección rebota junto con el cuerpo.
  animateWalkStep() {
    const ts = this.tileSize;
    const scene = this.scene;
    this.stepPhase = this.stepPhase === 'left' ? 'right' : 'left';
    const bob = ts * 0.09;
    const lean = this.stepPhase === 'left' ? -3 : 3;

    scene.tweens.add({
      targets: this.visual,
      y: -bob,
      scaleX: 0.94,
      scaleY: 1.07,
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
    const legSwing = ts * 0.075;
    const armSwing = ts * 0.06;

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
  }
}
